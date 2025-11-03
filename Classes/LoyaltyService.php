<?php

namespace Modules\Software\Classes;

use Modules\Software\Models\GymMember;
use Modules\Software\Models\LoyaltyPointRule;
use Modules\Software\Models\LoyaltyCampaign;
use Modules\Software\Models\LoyaltyTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * LoyaltyService
 * 
 * Core business logic for the loyalty points system.
 * Handles earning, redeeming, and managing loyalty points for gym members.
 * 
 * @package Modules\Software\Classes
 */
class LoyaltyService
{
    /**
     * Award loyalty points to a member based on money spent
     * 
     * @param GymMember $member The member earning points
     * @param float $amount The amount of money spent
     * @param string $sourceType Type of source (e.g., 'subscription', 'order')
     * @param int|null $sourceId ID of the source record
     * @param LoyaltyPointRule|null $rule Optional specific rule to use
     * @return LoyaltyTransaction|null Created transaction or null on failure
     * @throws Exception
     */
    public function earn($member, $amount, $sourceType, $sourceId = null, $rule = null)
    {
        try {
            DB::beginTransaction();

            // Get active rule if not provided
            if (!$rule) {
                $rule = LoyaltyPointRule::active()
                    ->where('branch_setting_id', $member->branch_setting_id ?? 1)
                    ->first();
            }

            if (!$rule) {
                throw new Exception('No active loyalty point rule found');
            }

            // Calculate base points
            $basePoints = $rule->calculatePointsFromMoney($amount);

            if ($basePoints <= 0) {
                DB::rollBack();
                return null; // No points to award
            }

            // Check for active campaign
            $campaign = $this->getActiveCampaign($member->branch_setting_id ?? 1);
            $finalPoints = $basePoints;
            $campaignId = null;

            if ($campaign && $campaign->isRunning()) {
                $finalPoints = $campaign->applyMultiplier($basePoints);
                $campaignId = $campaign->id;
            }

            // Calculate expiry date
            $expiresAt = $rule->getExpiryDate();

            // Create transaction record
            $transaction = LoyaltyTransaction::create([
                'member_id' => $member->id,
                'rule_id' => $rule->id,
                'campaign_id' => $campaignId,
                'points' => $finalPoints,
                'type' => LoyaltyTransaction::TYPE_EARN,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'amount_spent' => $amount,
                'expires_at' => $expiresAt,
                'is_expired' => false,
                'reason' => $campaign 
                    ? "Earned from {$sourceType} (Campaign: {$campaign->name} - {$campaign->multiplier}x)"
                    : "Earned from {$sourceType}",
            ]);

            // Update member balance
            $member->loyalty_points_balance += $finalPoints;
            $member->last_points_update = now();
            $member->save();

            DB::commit();

            // Log::info("Loyalty points earned", [
            //     'member_id' => $member->id,
            //     'points' => $finalPoints,
            //     'base_points' => $basePoints,
            //     'campaign' => $campaign ? $campaign->name : null,
            //     'amount' => $amount,
            // ]);

            return $transaction;

        } catch (Exception $e) {
            DB::rollBack();
            // Log::error("Failed to award loyalty points", [
            //     'member_id' => $member->id,
            //     'amount' => $amount,
            //     'error' => $e->getMessage(),
            // ]);
            throw $e;
        }
    }

    /**
     * Redeem loyalty points for a discount
     * 
     * @param GymMember $member The member redeeming points
     * @param int $points Number of points to redeem
     * @param string|null $reason Optional reason for redemption
     * @param string|null $sourceType Optional source type
     * @param int|null $sourceId Optional source ID
     * @return array ['transaction' => LoyaltyTransaction, 'value' => float] or null
     * @throws Exception
     */
    public function redeem($member, $points, $reason = null, $sourceType = null, $sourceId = null)
    {
        try {
            DB::beginTransaction();

            // Validate points availability
            if ($member->loyalty_points_balance < $points) {
                throw new Exception("Insufficient loyalty points. Available: {$member->loyalty_points_balance}, Requested: {$points}");
            }

            if ($points <= 0) {
                throw new Exception("Points to redeem must be greater than zero");
            }

            // Get active rule
            $rule = LoyaltyPointRule::active()
                ->where('branch_setting_id', $member->branch_setting_id ?? 1)
                ->first();

            if (!$rule) {
                throw new Exception('No active loyalty point rule found');
            }

            // Calculate monetary value
            $value = $rule->calculateMoneyFromPoints($points);

            // Create transaction record (negative points for redemption)
            $transaction = LoyaltyTransaction::create([
                'member_id' => $member->id,
                'rule_id' => $rule->id,
                'campaign_id' => null,
                'points' => -$points, // Negative for redemption
                'type' => LoyaltyTransaction::TYPE_REDEEM,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'reason' => $reason ?? "Redeemed for discount",
                'amount_spent' => null,
                'expires_at' => null,
                'is_expired' => false,
            ]);

            // Update member balance
            $member->loyalty_points_balance -= $points;
            $member->last_points_update = now();
            $member->save();

            DB::commit();

            Log::info("Loyalty points redeemed", [
                'member_id' => $member->id,
                'points' => $points,
                'value' => $value,
            ]);

            return [
                'transaction' => $transaction,
                'value' => $value,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to redeem loyalty points", [
                'member_id' => $member->id,
                'points' => $points,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Manually add or deduct points (admin action)
     * 
     * @param GymMember $member The member
     * @param int $points Positive to add, negative to deduct
     * @param string|null $reason Reason for manual adjustment
     * @param int|null $createdBy Admin user ID
     * @return LoyaltyTransaction Created transaction
     * @throws Exception
     */
    public function addManual($member, $points, $reason = null, $createdBy = null)
    {
        try {
            DB::beginTransaction();

            if ($points == 0) {
                throw new Exception("Points must be non-zero");
            }

            // If deducting, check sufficient balance
            if ($points < 0 && $member->loyalty_points_balance < abs($points)) {
                throw new Exception("Insufficient points balance for deduction");
            }

            // Create transaction record
            $transaction = LoyaltyTransaction::create([
                'member_id' => $member->id,
                'rule_id' => null,
                'campaign_id' => null,
                'points' => $points,
                'type' => LoyaltyTransaction::TYPE_MANUAL,
                'source_type' => 'manual',
                'source_id' => null,
                'reason' => $reason ?? ($points > 0 ? 'Manual points addition' : 'Manual points deduction'),
                'amount_spent' => null,
                'expires_at' => null, // Manual points don't expire by default
                'is_expired' => false,
                'created_by' => $createdBy,
            ]);

            // Update member balance
            $member->loyalty_points_balance += $points;
            $member->last_points_update = now();
            $member->save();

            DB::commit();

            Log::info("Manual loyalty points adjustment", [
                'member_id' => $member->id,
                'points' => $points,
                'reason' => $reason,
                'created_by' => $createdBy,
            ]);

            return $transaction;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to manually adjust loyalty points", [
                'member_id' => $member->id,
                'points' => $points,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Expire old loyalty points
     * 
     * This method should be called by a scheduled command daily
     * 
     * @return int Number of points expired
     */
    public function expirePoints()
    {
        try {
            DB::beginTransaction();

            // Get all transactions that should expire
            $expiredTransactions = LoyaltyTransaction::shouldExpire()->get();

            $totalPointsExpired = 0;
            $memberUpdates = [];

            foreach ($expiredTransactions as $transaction) {
                // Only expire positive (earned) points
                if ($transaction->points > 0) {
                    $transaction->markAsExpired();
                    $totalPointsExpired += $transaction->points;

                    // Track member balance updates
                    if (!isset($memberUpdates[$transaction->member_id])) {
                        $memberUpdates[$transaction->member_id] = 0;
                    }
                    $memberUpdates[$transaction->member_id] += $transaction->points;
                }
            }

            // Update member balances
            foreach ($memberUpdates as $memberId => $pointsToDeduct) {
                $member = GymMember::find($memberId);
                if ($member) {
                    $member->loyalty_points_balance -= $pointsToDeduct;
                    $member->last_points_update = now();
                    $member->save();
                }
            }

            DB::commit();

            Log::info("Loyalty points expired", [
                'transactions_count' => $expiredTransactions->count(),
                'total_points' => $totalPointsExpired,
                'members_affected' => count($memberUpdates),
            ]);

            return $totalPointsExpired;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to expire loyalty points", [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get member's points history
     * 
     * @param GymMember $member
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHistory($member, $limit = 50)
    {
        return LoyaltyTransaction::forMember($member->id)
            ->with(['rule', 'campaign', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get member's active (non-expired) points breakdown
     * 
     * @param GymMember $member
     * @return array
     */
    public function getActivePointsBreakdown($member)
    {
        $transactions = LoyaltyTransaction::forMember($member->id)
            ->active()
            ->where('points', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        return [
            'total_active' => $transactions->sum('points'),
            'transactions' => $transactions,
            'expiring_soon' => $transactions->filter(function ($t) {
                return $t->expires_at && $t->expires_at->diffInDays(now()) <= 30;
            }),
        ];
    }

    /**
     * Get currently active campaign for a branch
     * 
     * @param int $branchId
     * @return LoyaltyCampaign|null
     */
    protected function getActiveCampaign($branchId)
    {
        return LoyaltyCampaign::current()
            ->where('branch_setting_id', $branchId)
            ->orderBy('multiplier', 'desc')
            ->first();
    }

    /**
     * Calculate how many points a member would earn from an amount
     * 
     * @param float $amount
     * @param int|null $branchId
     * @return array ['base_points' => int, 'bonus_points' => int, 'total_points' => int, 'campaign' => string|null]
     */
    public function calculatePotentialPoints($amount, $branchId = null)
    {
        $rule = LoyaltyPointRule::active()
            ->where('branch_setting_id', $branchId ?? 1)
            ->first();

        if (!$rule) {
            return ['base_points' => 0, 'bonus_points' => 0, 'total_points' => 0, 'campaign' => null];
        }

        $basePoints = $rule->calculatePointsFromMoney($amount);
        $campaign = $this->getActiveCampaign($branchId ?? 1);

        if ($campaign && $campaign->isRunning()) {
            $totalPoints = $campaign->applyMultiplier($basePoints);
            $bonusPoints = $totalPoints - $basePoints;

            return [
                'base_points' => $basePoints,
                'bonus_points' => $bonusPoints,
                'total_points' => $totalPoints,
                'campaign' => $campaign->name,
                'multiplier' => $campaign->multiplier,
            ];
        }

        return [
            'base_points' => $basePoints,
            'bonus_points' => 0,
            'total_points' => $basePoints,
            'campaign' => null,
        ];
    }

    /**
     * Calculate monetary value of points
     * 
     * @param int $points
     * @param int|null $branchId
     * @return float
     */
    public function calculatePointsValue($points, $branchId = null)
    {
        $rule = LoyaltyPointRule::active()
            ->where('branch_setting_id', $branchId ?? 1)
            ->first();

        if (!$rule) {
            return 0;
        }

        return $rule->calculateMoneyFromPoints($points);
    }
}


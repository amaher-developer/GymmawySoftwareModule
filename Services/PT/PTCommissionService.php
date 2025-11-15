<?php

namespace Modules\Software\Services\PT;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Software\Models\GymPTClassTrainer;
use Modules\Software\Models\GymPTCommission;
use Modules\Software\Models\GymPTMember;
use Modules\Software\Models\GymPTMemberAttendee;
use Modules\Software\Models\GymPTTrainer;

/**
 * PTCommissionService
 *
 * Handles commission calculations for PT sessions, ledger creation,
 * and settlement flows (marking commissions as paid).
 *
 * @example
 * $service = app(PTCommissionService::class);
 * $commission = $service->recordForAttendance($attendee);
 * $service->settleCommission($commission, auth()->id());
 */
class PTCommissionService
{
    /**
     * Create or fetch a commission entry for the provided attendee.
     *
     * @param  GymPTMemberAttendee  $attendee
     * @param  Carbon|null  $sessionDate
     * @param  GymPTClassTrainer|null  $classTrainer
     * @return GymPTCommission|null
     */
    public function recordForAttendance(
        GymPTMemberAttendee $attendee,
        ?Carbon $sessionDate = null,
        ?GymPTClassTrainer $classTrainer = null
    ): ?GymPTCommission {
        $attendee->loadMissing(['pt_member']);
        $member = $attendee->pt_member;

        if (!$member) {
            return null;
        }

        $classTrainer = $classTrainer ?? $member->classTrainer;
        $slot = $sessionDate ?? $attendee->session_date;

        $existing = GymPTCommission::where('pt_member_attendee_id', $attendee->id)->first();
        if ($existing) {
            return $existing;
        }

        $rate = $this->resolveCommissionRate($member, $classTrainer);

        if ($rate <= 0) {
            return null;
        }

        $baseAmount = $this->calculatePerSessionBase($member, $classTrainer);
        if ($baseAmount <= 0) {
            return null;
        }

        return DB::transaction(function () use ($attendee, $member, $slot, $classTrainer, $rate, $baseAmount) {
            return GymPTCommission::create([
                'branch_setting_id' => $member->branch_setting_id
                    ?? $classTrainer?->branch_setting_id
                    ?? $attendee->branch_setting_id,
                'trainer_id' => $classTrainer?->trainer_id ?? $member->pt_trainer_id,
                'pt_member_id' => $member->id,
                'pt_member_attendee_id' => $attendee->id,
                'session_id' => null,
                'session_date' => $slot,
                'commission_rate' => $rate,
                'commission_amount' => round($baseAmount * ($rate / 100), 2),
                'status' => 'pending',
            ]);
        });
    }

    /**
     * Calculate the per-session base amount used for commission calculations.
     *
     * @param  GymPTMember  $member
     * @param  GymPTClassTrainer|null  $classTrainer
     * @return float
     */
    public function calculatePerSessionBase(
        GymPTMember $member,
        ?GymPTClassTrainer $classTrainer = null
    ): float {
        $totalSessions = $classTrainer?->session_count
            ?? $member->total_sessions
            ?? $member->classes
            ?? 0;

        $totalSessions = max((int) $totalSessions, 1);

        $netAmount = $member->amount_before_discount ?? $member->paid_amount;
        if ($netAmount <= 0) {
            $netAmount = ($member->amount_paid ?? 0) + ($member->amount_remaining ?? 0);
        }

        $netAmount -= ($member->discount ?? $member->discount_value ?? 0);

        return round(max($netAmount, 0) / $totalSessions, 2);
    }

    /**
     * Determine commission rate for the member/trainer context.
     *
     * @param  GymPTMember  $member
     * @param  GymPTClassTrainer|null  $classTrainer
     * @return float
     */
    public function resolveCommissionRate(
        GymPTMember $member,
        ?GymPTClassTrainer $classTrainer = null
    ): float {
        $rate = $classTrainer?->commission_rate;

        if ($rate === null) {
            $rate = $member->trainer_percentage ?? 0;
        }

        return (float) $rate;
    }

    /**
     * Mark a commission as paid and persist payout metadata.
     *
     * @param  GymPTCommission  $commission
     * @param  int|null  $paidByUserId
     * @return GymPTCommission
     */
    public function settleCommission(GymPTCommission $commission, ?int $paidByUserId = null): GymPTCommission
    {
        if ($commission->status === 'paid') {
            return $commission;
        }

        $commission->status = 'paid';
        $commission->paid_by = $paidByUserId;
        $commission->paid_at = now();
        $commission->save();

        return $commission;
    }

    /**
     * Bulk-mark commissions as paid for a trainer.
     *
     * @param  GymPTTrainer  $trainer
     * @param  array<int>  $commissionIds
     * @param  int|null  $paidByUserId
     * @return Collection<int, GymPTCommission>
     */
    public function settleCommissionsForTrainer(
        GymPTTrainer $trainer,
        array $commissionIds,
        ?int $paidByUserId = null
    ): Collection {
        $commissions = GymPTCommission::query()
            ->where('trainer_id', $trainer->id)
            ->whereIn('id', $commissionIds)
            ->get();

        return $commissions->map(function (GymPTCommission $commission) use ($paidByUserId) {
            return $this->settleCommission($commission, $paidByUserId);
        });
    }

    /**
     * Summarise pending commissions by trainer or by class.
     *
     * @param  array<string, mixed>  $filters
     * @return array{total_amount: float, total_count: int, grouped: Collection}
     */
    public function summarizePending(array $filters = []): array
    {
        $query = GymPTCommission::query()
            ->where('status', 'pending')
            ->with([
                'trainer',
                'member',
                'attendee.pt_member',
                'attendee.pt_member.pt_class',
                'attendee.pt_member.classTrainer',
            ]);

        if ($branchId = $filters['branch_setting_id'] ?? null) {
            $query->where('branch_setting_id', $branchId);
        }

        if ($trainerId = $filters['trainer_id'] ?? null) {
            $query->where('trainer_id', $trainerId);
        }

        if ($classId = $filters['class_id'] ?? null) {
            $query->whereHas('attendee.pt_member', function ($q) use ($classId) {
                $q->where('class_id', $classId)
                    ->orWhere('pt_class_id', $classId);
            });
        }

        if ($from = $filters['from'] ?? null) {
            $query->where(function ($q) use ($from) {
                $q->whereDate('session_date', '>=', $from)
                    ->orWhere(function ($nested) use ($from) {
                        $nested->whereNull('session_date')
                            ->whereDate('created_at', '>=', $from);
                    });
            });
        }

        if ($to = $filters['to'] ?? null) {
            $query->where(function ($q) use ($to) {
                $q->whereDate('session_date', '<=', $to)
                    ->orWhere(function ($nested) use ($to) {
                        $nested->whereNull('session_date')
                            ->whereDate('created_at', '<=', $to);
                    });
            });
        }

        $commissions = $query->get();

        $grouped = $commissions->groupBy('trainer_id')->map(function (Collection $items) {
            /** @var Collection<int, GymPTCommission> $items */
            return [
                'trainer' => $items->first()->trainer,
                'total_amount' => $items->sum('commission_amount'),
                'total_count' => $items->count(),
                'commissions' => $items,
            ];
        });

        return [
            'total_amount' => $commissions->sum('commission_amount'),
            'total_count' => $commissions->count(),
            'grouped' => $grouped,
        ];
    }

    /**
     * Helper to detach commissions when an attendance record is deleted or voided.
     *
     * @param  GymPTMemberAttendee  $attendee
     * @return int number of removed commissions
     */
    public function destroyForAttendance(GymPTMemberAttendee $attendee): int
    {
        return GymPTCommission::query()
            ->where('pt_member_attendee_id', $attendee->id)
            ->orWhere(function ($query) use ($attendee) {
                $query->whereNull('pt_member_attendee_id')
                    ->where('pt_member_id', $attendee->pt_member_id)
                    ->where('session_date', $attendee->session_date);
            })
            ->delete();
    }
}


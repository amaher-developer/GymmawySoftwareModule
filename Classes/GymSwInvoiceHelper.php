<?php

namespace Modules\Software\Classes;

use Illuminate\Support\Facades\Log;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Models\GymStoreOrder;
use Modules\Software\Models\GymSwInvoice;
use Modules\Software\Services\GymSwInvoiceService;

/**
 * Thin static façade around GymSwInvoiceService.
 * Every call site is a single line; all try/catch and calculation
 * logic lives here so controllers stay readable.
 */
class GymSwInvoiceHelper
{
    // ── Store order: sale ────────────────────────────────────────────────────

    /**
     * Create a sales invoice for a completed store order.
     * Returns the new invoice ID to pass to GymMoneyBox::create(['invoice_id' => ...]).
     * Returns null silently on any error so the order flow is never blocked.
     *
     * @param  GymStoreOrder  $order
     * @param  int|null       $branchSettingId
     * @return int|null
     */
    public static function forStoreOrder(GymStoreOrder $order, ?int $branchSettingId = null): ?int
    {
        try {
            $vatAmount = round((float) ($order->vat ?? 0), 2);
            $subtotal  = round((float) ($order->amount_before_discount ?? 0) - (float) ($order->discount_value ?? 0), 2);
            $total     = round($subtotal + $vatAmount, 2);

            if ($total <= 0) return null;

            $invoice = (new GymSwInvoiceService())->createSalesInvoice([
                'member_id'         => $order->member_id ?? null,
                'subtotal'          => $subtotal,
                'vat_amount'        => $vatAmount,
                'total'             => $total,
                'amount_paid'       => (float) ($order->amount_paid ?? 0),
                'branch_setting_id' => $branchSettingId,
                'issued_at'         => $order->created_at ?? now(),
            ]);

            return $invoice->id;
        } catch (\Throwable $e) {
            Log::error('GymSwInvoiceHelper::forStoreOrder failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
            return null;
        }
    }

    // ── Store order: refund ──────────────────────────────────────────────────

    /**
     * Issue a credit note when a store order is refunded.
     * Links to the original sale invoice if one exists; falls back to a
     * standalone credit note otherwise.
     *
     * @param  GymStoreOrder  $order
     * @param  float          $refundAmount   Actual amount being refunded
     * @param  int|null       $branchSettingId
     * @return void
     */
    public static function refundStoreOrder(GymStoreOrder $order, float $refundAmount, ?int $branchSettingId = null): void
    {
        try {
            $vatAmount = round((float) ($order->vat ?? 0), 2);
            $subtotal  = round($refundAmount - $vatAmount, 2);

            $service = new GymSwInvoiceService();

            $original = GymSwInvoice::where('member_id', $order->member_id)
                ->whereHas('moneyBoxes', fn ($q) => $q->where('store_order_id', $order->id))
                ->where('type', 'sales')
                ->latest()
                ->first();

            if ($original) {
                $service->createCreditNote($original, [
                    'subtotal'          => $subtotal,
                    'vat_amount'        => $vatAmount,
                    'total'             => round($refundAmount, 2),
                    'branch_setting_id' => $branchSettingId,
                ]);
            } else {
                $service->createVendorRefundNote([
                    'subtotal'          => $subtotal,
                    'vat_amount'        => $vatAmount,
                    'total'             => round($refundAmount, 2),
                    'branch_setting_id' => $branchSettingId,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('GymSwInvoiceHelper::refundStoreOrder failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    // ── Training plan payment ────────────────────────────────────────────────

    /**
     * Create a sales invoice for a training plan payment.
     * Returns the invoice ID to store on the GymMoneyBox, or null on failure.
     *
     * @param  int        $memberId
     * @param  float      $price          Price before discount
     * @param  float      $discount       Discount amount
     * @param  float      $vatAmount      VAT amount (absolute, not %)
     * @param  float      $amountPaid     Amount actually collected now
     * @param  int|null   $branchSettingId
     * @return int|null
     */
    public static function forTrainingPlan(
        int    $memberId,
        float  $price,
        float  $discount,
        float  $vatAmount,
        float  $amountPaid,
        ?int   $branchSettingId = null
    ): ?int {
        try {
            $subtotal = round($price - $discount, 2);
            $total    = round($subtotal + $vatAmount, 2);

            if ($total <= 0) return null;

            $invoice = (new GymSwInvoiceService())->createSalesInvoice([
                'member_id'         => $memberId,
                'subtotal'          => $subtotal,
                'vat_amount'        => $vatAmount,
                'total'             => $total,
                'amount_paid'       => round($amountPaid, 2),
                'branch_setting_id' => $branchSettingId,
                'issued_at'         => now(),
            ]);

            return $invoice->id;
        } catch (\Throwable $e) {
            Log::error('GymSwInvoiceHelper::forTrainingPlan failed', [
                'member_id' => $memberId,
                'error'     => $e->getMessage(),
            ]);
            return null;
        }
    }

    // ── Member subscription edit (upgrade / downgrade) ───────────────────────

    /**
     * Create an invoice for a subscription upgrade or downgrade.
     * Upgrade (operation=0/Add): new sales invoice for the additional amount.
     * Downgrade (operation=1/Sub): credit note linked to the original invoice,
     * or a standalone credit note if no original is found.
     *
     * @param  int       $memberId
     * @param  int       $memberSubscriptionId   The sw_gym_member_subscription.id
     * @param  float     $amount                 Absolute diff amount (money-box amount)
     * @param  float     $vatAmount              VAT portion of $amount
     * @param  int       $operation              TypeConstants::Add (0) or Sub (1)
     * @param  int|null  $branchSettingId
     * @return int|null  Invoice ID on success, null on failure
     */
    public static function forSubscriptionEdit(
        int    $memberId,
        int    $memberSubscriptionId,
        float  $amount,
        float  $vatAmount,
        int    $operation,
        ?int   $branchSettingId = null
    ): ?int {
        try {
            if (round($amount, 2) <= 0) return null;

            $subtotal = round($amount - $vatAmount, 2);
            $service  = new GymSwInvoiceService();

            if ($operation === TypeConstants::Add) {
                $invoice = $service->createSalesInvoice([
                    'member_id'         => $memberId,
                    'subtotal'          => $subtotal,
                    'vat_amount'        => $vatAmount,
                    'total'             => round($amount, 2),
                    'amount_paid'       => round($amount, 2),
                    'branch_setting_id' => $branchSettingId,
                    'issued_at'         => now(),
                ]);
            } else {
                $original = GymSwInvoice::where('member_id', $memberId)
                    ->whereHas('moneyBoxes', fn ($q) => $q->where('member_subscription_id', $memberSubscriptionId))
                    ->where('type', 'sales')
                    ->latest()
                    ->first();

                if ($original) {
                    $invoice = $service->createCreditNote($original, [
                        'subtotal'          => $subtotal,
                        'vat_amount'        => $vatAmount,
                        'total'             => round($amount, 2),
                        'branch_setting_id' => $branchSettingId,
                    ]);
                } else {
                    $invoice = $service->createVendorRefundNote([
                        'subtotal'          => $subtotal,
                        'vat_amount'        => $vatAmount,
                        'total'             => round($amount, 2),
                        'branch_setting_id' => $branchSettingId,
                    ]);
                }
            }

            return $invoice->id;
        } catch (\Throwable $e) {
            Log::error('GymSwInvoiceHelper::forSubscriptionEdit failed', [
                'member_id'              => $memberId,
                'member_subscription_id' => $memberSubscriptionId,
                'error'                  => $e->getMessage(),
            ]);
            return null;
        }
    }

    // ── Vendor purchase order: refund ────────────────────────────────────────

    /**
     * Issue a standalone credit note when a vendor purchase order is refunded.
     *
     * @param  mixed      $order            GymStoreOrderVendor instance
     * @param  float      $refundAmount     Actual amount being refunded (VAT-inclusive)
     * @param  float      $vatPct           VAT percentage (e.g. 14.0)
     * @param  int|null   $branchSettingId
     * @return void
     */
    public static function refundVendorOrder($order, float $refundAmount, float $vatPct, ?int $branchSettingId = null): void
    {
        try {
            $vatAmount = round(($refundAmount * ($vatPct / 100)) / (1 + ($vatPct / 100)), 2);

            (new GymSwInvoiceService())->createVendorRefundNote([
                'subtotal'          => round($refundAmount - $vatAmount, 2),
                'vat_amount'        => $vatAmount,
                'total'             => round($refundAmount, 2),
                'vat_rate'          => $vatPct,
                'branch_setting_id' => $branchSettingId,
            ]);
        } catch (\Throwable $e) {
            Log::error('GymSwInvoiceHelper::refundVendorOrder failed', [
                'order_id' => $order->id ?? null,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}

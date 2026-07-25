<?php

namespace Modules\Software\Observers;

use Illuminate\Support\Facades\Log;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Models\GymMemberSubscription;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Models\GymNonMember;
use Modules\Software\Models\GymPTMember;
use Modules\Software\Models\GymSwInvoice;
use Modules\Software\Services\GymSwInvoiceService;

class GymMoneyBoxObserver
{
    public function created(GymMoneyBox $moneyBox): void
    {
        if (round((float) $moneyBox->amount, 2) <= 0) return;
        if ($moneyBox->invoice_id) return;
        if ($moneyBox->is_store_balance) return;

        $op = (int) $moneyBox->operation;

        if ($moneyBox->member_subscription_id) {
            if ($op === TypeConstants::Add) {
                $this->handleSubscriptionPayment($moneyBox);
            } elseif ($op === TypeConstants::Sub) {
                $this->handleSubscriptionRefund($moneyBox, 'member_subscription_id');
            }
        } elseif ($moneyBox->member_pt_subscription_id) {
            if ($op === TypeConstants::Add) {
                $this->handlePTSubscriptionPayment($moneyBox);
            } elseif ($op === TypeConstants::Sub) {
                $this->handleSubscriptionRefund($moneyBox, 'member_pt_subscription_id');
            }
        } elseif ($moneyBox->non_member_subscription_id) {
            if ($op === TypeConstants::Add) {
                $this->handleNonMemberPayment($moneyBox);
            } elseif ($op === TypeConstants::Sub) {
                $this->handleSubscriptionRefund($moneyBox, 'non_member_subscription_id');
            }
        } elseif (in_array((int) $moneyBox->type, [TypeConstants::CreateMoneyBoxAdd, TypeConstants::CreateMoneyBoxWithdraw])) {
            $this->handleManualEntry($moneyBox);
        }
    }

    // ── Member subscription ──────────────────────────────────────────────────

    private function handleSubscriptionPayment(GymMoneyBox $moneyBox): void
    {
        try {
            $existingLinked = GymMoneyBox::where('member_subscription_id', $moneyBox->member_subscription_id)
                ->whereNotNull('invoice_id')
                ->where('id', '!=', $moneyBox->id)
                ->first();

            $service = new GymSwInvoiceService();

            if (! $existingLinked) {
                $sub = GymMemberSubscription::find($moneyBox->member_subscription_id);
                if (! $sub) return;

                $subtotal  = round((float) $sub->amount_before_discount - (float) ($sub->discount_value ?? 0), 2);
                $vatAmount = round((float) ($sub->vat ?? 0), 2);
                $total     = round($subtotal + $vatAmount, 2);

                $invoice = $service->createSalesInvoice([
                    'member_id'         => $moneyBox->member_id,
                    'subtotal'          => $subtotal,
                    'vat_amount'        => $vatAmount,
                    'vat_rate'          => $sub->vat_percentage ?? 14.00,
                    'total'             => $total,
                    'amount_paid'       => (float) $moneyBox->amount,
                    'branch_setting_id' => $moneyBox->branch_setting_id ?? null,
                    'issued_at'         => $moneyBox->created_at ?? now(),
                ]);

                $moneyBox->invoice_id = $invoice->id;
                $moneyBox->saveQuietly();
            } else {
                $invoice = GymSwInvoice::find($existingLinked->invoice_id);
                if ($invoice) {
                    $service->recordPayment($invoice, (float) $moneyBox->amount, $moneyBox);
                }
            }
        } catch (\Throwable $e) {
            Log::error('GymMoneyBoxObserver: member subscription invoice failed', [
                'money_box_id'           => $moneyBox->id,
                'member_subscription_id' => $moneyBox->member_subscription_id,
                'error'                  => $e->getMessage(),
            ]);
        }
    }

    // ── PT member subscription ───────────────────────────────────────────────

    private function handlePTSubscriptionPayment(GymMoneyBox $moneyBox): void
    {
        try {
            $existingLinked = GymMoneyBox::where('member_pt_subscription_id', $moneyBox->member_pt_subscription_id)
                ->whereNotNull('invoice_id')
                ->where('id', '!=', $moneyBox->id)
                ->first();

            $service = new GymSwInvoiceService();

            if (! $existingLinked) {
                $ptMember = GymPTMember::find($moneyBox->member_pt_subscription_id);
                if (! $ptMember) return;

                $subtotal  = round((float) ($ptMember->amount_before_discount ?? 0) - (float) ($ptMember->discount_value ?? $ptMember->discount ?? 0), 2);
                $vatAmount = round((float) ($ptMember->vat ?? 0), 2);
                $total     = round($subtotal + $vatAmount, 2);

                // Fall back to moneyBox amount when model fields are empty (legacy records)
                if ($total <= 0) {
                    $vatAmount = round((float) ($moneyBox->vat ?? 0), 2);
                    $subtotal  = round((float) $moneyBox->amount - $vatAmount, 2);
                    $total     = (float) $moneyBox->amount;
                }

                $invoice = $service->createSalesInvoice([
                    'member_id'         => $moneyBox->member_id,
                    'subtotal'          => $subtotal,
                    'vat_amount'        => $vatAmount,
                    'total'             => $total,
                    'amount_paid'       => (float) $moneyBox->amount,
                    'branch_setting_id' => $moneyBox->branch_setting_id ?? null,
                    'issued_at'         => $moneyBox->created_at ?? now(),
                ]);

                $moneyBox->invoice_id = $invoice->id;
                $moneyBox->saveQuietly();
            } else {
                $invoice = GymSwInvoice::find($existingLinked->invoice_id);
                if ($invoice) {
                    $service->recordPayment($invoice, (float) $moneyBox->amount, $moneyBox);
                }
            }
        } catch (\Throwable $e) {
            Log::error('GymMoneyBoxObserver: PT subscription invoice failed', [
                'money_box_id'              => $moneyBox->id,
                'member_pt_subscription_id' => $moneyBox->member_pt_subscription_id,
                'error'                     => $e->getMessage(),
            ]);
        }
    }

    // ── Non-member subscription ──────────────────────────────────────────────

    private function handleNonMemberPayment(GymMoneyBox $moneyBox): void
    {
        try {
            $existingLinked = GymMoneyBox::where('non_member_subscription_id', $moneyBox->non_member_subscription_id)
                ->whereNotNull('invoice_id')
                ->where('id', '!=', $moneyBox->id)
                ->first();

            $service = new GymSwInvoiceService();

            if (! $existingLinked) {
                $nonMember = GymNonMember::find($moneyBox->non_member_subscription_id);
                if (! $nonMember) return;

                $subtotal  = round((float) ($nonMember->amount_before_discount ?? $nonMember->price ?? 0) - (float) ($nonMember->discount_value ?? 0), 2);
                $vatAmount = round((float) ($nonMember->vat ?? 0), 2);
                $total     = round($subtotal + $vatAmount, 2);

                // Fall back to moneyBox amount when model fields are empty
                if ($total <= 0) {
                    $vatAmount = round((float) ($moneyBox->vat ?? 0), 2);
                    $subtotal  = round((float) $moneyBox->amount - $vatAmount, 2);
                    $total     = (float) $moneyBox->amount;
                }

                $invoice = $service->createSalesInvoice([
                    'member_id'         => $moneyBox->member_id,
                    'subtotal'          => $subtotal,
                    'vat_amount'        => $vatAmount,
                    'total'             => $total,
                    'amount_paid'       => (float) $moneyBox->amount,
                    'branch_setting_id' => $moneyBox->branch_setting_id ?? null,
                    'issued_at'         => $moneyBox->created_at ?? now(),
                ]);

                $moneyBox->invoice_id = $invoice->id;
                $moneyBox->saveQuietly();
            } else {
                $invoice = GymSwInvoice::find($existingLinked->invoice_id);
                if ($invoice) {
                    $service->recordPayment($invoice, (float) $moneyBox->amount, $moneyBox);
                }
            }
        } catch (\Throwable $e) {
            Log::error('GymMoneyBoxObserver: non-member invoice failed', [
                'money_box_id'               => $moneyBox->id,
                'non_member_subscription_id' => $moneyBox->non_member_subscription_id,
                'error'                      => $e->getMessage(),
            ]);
        }
    }

    // ── Subscription refund (any FK type) ───────────────────────────────────

    private function handleSubscriptionRefund(GymMoneyBox $moneyBox, string $fkColumn): void
    {
        try {
            $fkValue = $moneyBox->$fkColumn;

            $linkedBox = GymMoneyBox::where($fkColumn, $fkValue)
                ->whereNotNull('invoice_id')
                ->where('id', '!=', $moneyBox->id)
                ->first();

            $refundAmount = round((float) $moneyBox->amount, 2);
            $vatAmount    = round((float) ($moneyBox->vat ?? 0), 2);
            $subtotal     = round($refundAmount - $vatAmount, 2);
            $service      = new GymSwInvoiceService();

            if ($linkedBox && $linkedBox->invoice_id) {
                $original = GymSwInvoice::find($linkedBox->invoice_id);
                if ($original) {
                    $service->createCreditNote($original, [
                        'subtotal'          => $subtotal,
                        'vat_amount'        => $vatAmount,
                        'total'             => $refundAmount,
                        'branch_setting_id' => $moneyBox->branch_setting_id ?? null,
                    ]);
                    return;
                }
            }

            // No original invoice found — issue a standalone credit note
            $service->createVendorRefundNote([
                'subtotal'          => $subtotal,
                'vat_amount'        => $vatAmount,
                'total'             => $refundAmount,
                'branch_setting_id' => $moneyBox->branch_setting_id ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('GymMoneyBoxObserver: subscription refund credit note failed', [
                'money_box_id' => $moneyBox->id,
                'fk_column'    => $fkColumn,
                'error'        => $e->getMessage(),
            ]);
        }
    }

    // ── Manual moneybox add / withdraw ───────────────────────────────────────

    private function handleManualEntry(GymMoneyBox $moneyBox): void
    {
        try {
            $vatAmount = round((float) ($moneyBox->vat ?? 0), 2);
            $amount    = round((float) $moneyBox->amount, 2);
            $subtotal  = round($amount - $vatAmount, 2);
            $service   = new GymSwInvoiceService();

            if ((int) $moneyBox->operation === TypeConstants::Add) {
                $invoice = $service->createSalesInvoice([
                    'subtotal'          => $subtotal,
                    'vat_amount'        => $vatAmount,
                    'total'             => $amount,
                    'amount_paid'       => $amount,
                    'branch_setting_id' => $moneyBox->branch_setting_id ?? null,
                    'issued_at'         => $moneyBox->created_at ?? now(),
                    'notes'             => $moneyBox->notes,
                ]);
            } else {
                $invoice = $service->createPurchaseInvoice([
                    'subtotal'          => $subtotal,
                    'vat_amount'        => $vatAmount,
                    'total'             => $amount,
                    'amount_paid'       => $amount,
                    'branch_setting_id' => $moneyBox->branch_setting_id ?? null,
                    'issued_at'         => $moneyBox->created_at ?? now(),
                    'notes'             => $moneyBox->notes,
                ]);
            }

            $moneyBox->invoice_id = $invoice->id;
            $moneyBox->saveQuietly();
        } catch (\Throwable $e) {
            Log::error('GymMoneyBoxObserver: manual entry invoice failed', [
                'money_box_id' => $moneyBox->id,
                'type'         => $moneyBox->type,
                'error'        => $e->getMessage(),
            ]);
        }
    }
}

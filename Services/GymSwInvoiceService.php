<?php

namespace Modules\Software\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Billing\Models\SwBillingInvoice;
use Modules\Billing\Services\ZatcaPhase2Service;
use Modules\Generic\Models\Setting;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Models\GymSwInvoice;
use Modules\Software\Models\GymSwInvoiceSequence;

class GymSwInvoiceService
{
    // ── Status resolution ────────────────────────────────────────────────────

    private function resolveStatus(float $amountPaid, float $total): string
    {
        if ($amountPaid <= 0)      return 'draft';
        if ($amountPaid >= $total) return 'paid';
        return 'partial';
    }

    // ── Invoice creation ─────────────────────────────────────────────────────

    /**
     * Create a sales invoice representing the FULL value of the service sold.
     *
     * Accepted keys in $data:
     *   subtotal          float   required — price before VAT
     *   vat_amount        float   optional — if provided alongside `total`, skips recalculation
     *   total             float   optional — full amount with VAT
     *   vat_rate          float   optional — VAT % (default 14.00)
     *   amount_paid       float   optional — how much was collected right now (default 0)
     *   member_id         int     optional
     *   branch_setting_id int     optional
     *   notes             string  optional
     *   issued_at         string  optional
     *   due_at            string  optional
     */
    public function createSalesInvoice(array $data): GymSwInvoice
    {
        $invoice = DB::transaction(function () use ($data) {
            $seq = GymSwInvoiceSequence::nextFor('sales');
            $num = GymSwInvoice::generateNumber('INV', $seq);

            [$subtotal, $vatAmount, $total, $vatRate] = $this->resolveAmounts($data);

            $amountPaid = round((float) ($data['amount_paid'] ?? 0), 2);
            $status     = $this->resolveStatus($amountPaid, $total);

            return GymSwInvoice::create([
                'type'              => 'sales',
                'invoice_number'    => $num,
                'sequence_number'   => $seq,
                'prefix'            => 'INV',
                'branch_setting_id' => $data['branch_setting_id'] ?? null,
                'member_id'         => $data['member_id'] ?? null,
                'subtotal'          => $subtotal,
                'vat_rate'          => $vatRate,
                'vat_amount'        => $vatAmount,
                'total'             => $total,
                'amount_paid'       => $amountPaid,
                'notes'             => $data['notes'] ?? null,
                'issued_at'         => $data['issued_at'] ?? now(),
                'due_at'            => $data['due_at'] ?? null,
                'status'            => $status,
            ]);
        });

        $this->submitToZatca($invoice);

        return $invoice;
    }

    /**
     * Create a purchase invoice.
     * Same parameter contract as createSalesInvoice().
     */
    public function createPurchaseInvoice(array $data): GymSwInvoice
    {
        return DB::transaction(function () use ($data) {
            $seq = GymSwInvoiceSequence::nextFor('purchase');
            $num = GymSwInvoice::generateNumber('PINV', $seq);

            [$subtotal, $vatAmount, $total, $vatRate] = $this->resolveAmounts($data);

            $amountPaid = round((float) ($data['amount_paid'] ?? 0), 2);
            $status     = $this->resolveStatus($amountPaid, $total);

            return GymSwInvoice::create([
                'type'              => 'purchase',
                'invoice_number'    => $num,
                'sequence_number'   => $seq,
                'prefix'            => 'PINV',
                'branch_setting_id' => $data['branch_setting_id'] ?? null,
                'supplier_id'       => $data['supplier_id'] ?? null,
                'subtotal'          => $subtotal,
                'vat_rate'          => $vatRate,
                'vat_amount'        => $vatAmount,
                'total'             => $total,
                'amount_paid'       => $amountPaid,
                'notes'             => $data['notes'] ?? null,
                'issued_at'         => $data['issued_at'] ?? now(),
                'status'            => $status,
            ]);
        });
    }

    // ── Payment recording ────────────────────────────────────────────────────

    /**
     * Record a (partial or full) payment against an invoice.
     * Optionally links the GymMoneyBox entry to the invoice via invoice_id.
     */
    public function recordPayment(GymSwInvoice $invoice, float $amount, ?GymMoneyBox $moneyBox = null): void
    {
        DB::transaction(function () use ($invoice, $amount, $moneyBox) {
            $invoice->amount_paid = round((float) $invoice->amount_paid + $amount, 2);
            $invoice->status      = $this->resolveStatus((float) $invoice->amount_paid, (float) $invoice->total);
            $invoice->save();

            if ($moneyBox && ! $moneyBox->invoice_id) {
                $moneyBox->invoice_id = $invoice->id;
                $moneyBox->saveQuietly();
            }
        });
    }

    // ── Credit notes ─────────────────────────────────────────────────────────

    /**
     * Issue a credit note against an existing sales invoice.
     * The credit note is a pure accounting document — it does NOT create
     * a GymMoneyBox entry. The caller is responsible for the cash side.
     */
    public function createCreditNote(GymSwInvoice $originalInvoice, array $data): GymSwInvoice
    {
        $creditNote = DB::transaction(function () use ($originalInvoice, $data) {
            $seq = GymSwInvoiceSequence::nextFor('credit_note');
            $num = GymSwInvoice::generateNumber('CN', $seq);

            $data['vat_rate'] = (float) $originalInvoice->vat_rate;
            [$subtotal, $vatAmount, $total] = $this->resolveAmounts($data);

            return GymSwInvoice::create([
                'type'                 => 'credit_note',
                'invoice_number'       => $num,
                'sequence_number'      => $seq,
                'prefix'               => 'CN',
                'branch_setting_id'    => $data['branch_setting_id'] ?? $originalInvoice->branch_setting_id,
                'member_id'            => $data['member_id'] ?? $originalInvoice->member_id,
                'reference_invoice_id' => $originalInvoice->id,
                'subtotal'             => $subtotal,
                'vat_rate'             => $data['vat_rate'],
                'vat_amount'           => $vatAmount,
                'total'                => $total,
                'amount_paid'          => $total,
                'notes'                => $data['notes'] ?? null,
                'issued_at'            => now(),
                'status'               => 'paid',
            ]);
        });

        $this->submitToZatca($creditNote, $originalInvoice->invoice_number);

        return $creditNote;
    }

    // ── Vendor refund note ───────────────────────────────────────────────────

    /**
     * Create a standalone purchase-refund credit note (no reference invoice).
     * Used when a vendor purchase order is deleted/refunded.
     */
    public function createVendorRefundNote(array $data): GymSwInvoice
    {
        return DB::transaction(function () use ($data) {
            $seq = GymSwInvoiceSequence::nextFor('credit_note');
            $num = GymSwInvoice::generateNumber('CN', $seq);

            [$subtotal, $vatAmount, $total, $vatRate] = $this->resolveAmounts($data);

            return GymSwInvoice::create([
                'type'              => 'credit_note',
                'invoice_number'    => $num,
                'sequence_number'   => $seq,
                'prefix'            => 'CN',
                'branch_setting_id' => $data['branch_setting_id'] ?? null,
                'supplier_id'       => $data['supplier_id'] ?? null,
                'subtotal'          => $subtotal,
                'vat_rate'          => $vatRate,
                'vat_amount'        => $vatAmount,
                'total'             => $total,
                'amount_paid'       => $total,
                'notes'             => $data['notes'] ?? null,
                'issued_at'         => now(),
                'status'            => 'paid',
            ]);
        });
    }

    // ── PDF generation ───────────────────────────────────────────────────────

    /**
     * Generate (or re-generate) the PDF for an invoice.
     *
     * @return string  Relative storage path (e.g. "gym_sw_invoices/INV-00001.pdf")
     */
    public function generatePdf(GymSwInvoice $invoice): string
    {
        $invoice->loadMissing(['member', 'moneyBoxes', 'originalInvoice', 'zatcaBillingInvoice']);

        $settings = Setting::find($invoice->branch_setting_id);

        $html = view('software::gym_sw_invoices.pdf', [
            'invoice'  => $invoice,
            'settings' => $settings,
        ])->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'margin_left'       => 15,
            'margin_right'      => 15,
            'margin_top'        => 16,
            'margin_bottom'     => 20,
            'margin_header'     => 9,
            'margin_footer'     => 9,
            'default_font'      => 'dejavusans',
            'default_font_size' => 11,
        ]);

        $mpdf->SetDirectionality('rtl');
        $mpdf->WriteHTML($html);

        $relativePath = 'gym_sw_invoices/' . $invoice->invoice_number . '.pdf';
        Storage::put($relativePath, $mpdf->Output('', 'S'));

        $invoice->pdf_path = $relativePath;
        $invoice->saveQuietly();

        return $relativePath;
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    // ── ZATCA Phase 2 ─────────────────────────────────────────────────────────

    private function submitToZatca(GymSwInvoice $invoice, ?string $referenceInvoiceNumber = null): void
    {
        if (!config('sw_billing.zatca_enabled') || !config('sw_billing.auto_invoice')) {
            return;
        }

        // Only sales and credit notes are submitted; purchase invoices are supplier documents
        if (!in_array($invoice->type, ['sales', 'credit_note'])) {
            return;
        }

        try {
            $member = $invoice->member_id ? GymMember::find($invoice->member_id) : null;

            $billing                           = new SwBillingInvoice();
            $billing->invoice_number           = $invoice->invoice_number;
            $billing->invoice_type             = $invoice->type === 'credit_note' ? 'credit_note' : 'simplified';
            $billing->reference_invoice_number = $referenceInvoiceNumber;
            $billing->member_id                = $invoice->member_id;
            $billing->amount                   = (float) $invoice->subtotal;
            $billing->vat_amount               = (float) $invoice->vat_amount;
            $billing->total_amount             = (float) $invoice->total;
            $billing->buyer_name               = $member?->name ?? 'عميل';
            $billing->buyer_tax_number         = $member?->national_id ?? null;
            $billing->save();

            $invoice->zatca_billing_invoice_id = $billing->id;
            $invoice->saveQuietly();

            ZatcaPhase2Service::signAndSubmit($billing);
        } catch (\Throwable $e) {
            Log::error('GymSwInvoice ZATCA submission failed', [
                'invoice_id'   => $invoice->id,
                'invoice_type' => $invoice->type,
                'error'        => $e->getMessage(),
            ]);
        }
    }

    private function resolveAmounts(array $data): array
    {
        if (isset($data['vat_rate'])) {
            $vatRate = (float) $data['vat_rate'];
        } else {
            $settingId = $data['branch_setting_id'] ?? \Modules\Generic\Models\GenericModel::getCurrentBranchId();
            $setting   = Setting::find($settingId);
            $vatRate   = (float) data_get($setting, 'vat_details.vat_percentage', 15.00);
        }

        if (isset($data['vat_amount']) && isset($data['total'])) {
            return [
                round((float) $data['subtotal'], 2),
                round((float) $data['vat_amount'], 2),
                round((float) $data['total'], 2),
                $vatRate,
            ];
        }

        $computed = GymSwInvoice::calculateVat((float) $data['subtotal'], $vatRate);
        return [$computed['subtotal'], $computed['vat_amount'], $computed['total'], $vatRate];
    }
}

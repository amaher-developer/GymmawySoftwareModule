<?php

namespace Modules\Software\Http\Controllers\Front;

use Illuminate\Http\Request;
use Modules\Billing\Models\SwBillingInvoice;
use Modules\Billing\Services\ZatcaPhase2Service;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymSwInvoice;
use Modules\Software\Services\GymSwInvoiceService;
use Illuminate\Support\Facades\Log;

class GymSwInvoiceFrontController extends GymGenericFrontController
{
    protected GymSwInvoiceService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new GymSwInvoiceService();
    }

    public function index(Request $request)
    {
        $query = GymSwInvoice::branch();

        if ($request->filled('type'))      $query->where('type', $request->type);
        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('member_id')) $query->where('member_id', $request->member_id);
        if ($request->filled('date_from')) $query->whereDate('issued_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('issued_at', '<=', $request->date_to);

        $invoices = $query->latest()->paginate(20);

        $title = trans('sw.invoices');
        return view('software::gym_sw_invoices.index', compact('invoices', 'title'));
    }

    public function show(int $id)
    {
        $invoice = GymSwInvoice::with(['member', 'moneyBoxes', 'statusLogs', 'originalInvoice', 'creditNotes', 'zatcaBillingInvoice'])->findOrFail($id);
        $title   = $invoice->invoice_number;
        return view('software::gym_sw_invoices.show', compact('invoice', 'title'));
    }

    public function cancel(int $id)
    {
        $invoice = GymSwInvoice::findOrFail($id);

        if ($invoice->status === 'cancelled') {
            return redirect()->route('sw.gymSwInvoices.show', $id);
        }

        // For ZATCA compliance, sales invoices must be reversed via a credit note.
        // We create the CN now so it can be submitted to ZATCA when integration is enabled.
        if ($invoice->type === 'sales') {
            try {
                $this->service->createCreditNote($invoice, [
                    'subtotal'          => (float) $invoice->subtotal,
                    'vat_amount'        => (float) $invoice->vat_amount,
                    'total'             => (float) $invoice->total,
                    'vat_rate'          => (float) $invoice->vat_rate,
                    'branch_setting_id' => $invoice->branch_setting_id,
                    'notes'             => trans('sw.cancelled_invoice_cn_note', ['number' => $invoice->invoice_number]),
                ]);
            } catch (\Throwable $e) {
                Log::error('GymSwInvoiceFrontController::cancel — credit note creation failed', [
                    'invoice_id' => $invoice->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        $invoice->status = 'cancelled';
        $invoice->save();

        session()->flash('sweet_flash_message', [
            'title'   => trans('admin.done'),
            'message' => trans('sw.invoice_cancelled'),
            'type'    => 'success',
        ]);

        return redirect()->route('sw.gymSwInvoices.show', $id);
    }

    public function pdf(int $id)
    {
        $invoice = GymSwInvoice::findOrFail($id);
        $path    = $this->service->generatePdf($invoice);

        return \Illuminate\Support\Facades\Storage::download($path, $invoice->invoice_number . '.pdf');
    }

    public function bulkSubmitZatca(Request $request)
    {
        if (!config('sw_billing.zatca_enabled')) {
            return response()->json(['success' => false, 'message' => trans('sw.zatca_disabled')], 403);
        }

        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => trans('sw.no_invoices_selected')], 422);
        }

        $success = 0;
        $failed  = 0;
        $errors  = [];

        foreach ($ids as $id) {
            try {
                $invoice = GymSwInvoice::with(['member', 'zatcaBillingInvoice', 'originalInvoice'])->find($id);

                if (!$invoice || !in_array($invoice->type, ['sales', 'credit_note']) || $invoice->status === 'cancelled') {
                    $failed++;
                    continue;
                }

                $billing = $invoice->zatcaBillingInvoice;

                if (!$billing) {
                    $member  = $invoice->member;
                    $billing = new SwBillingInvoice();
                    $billing->invoice_number           = $invoice->invoice_number;
                    $billing->invoice_type             = $invoice->type === 'credit_note' ? 'credit_note' : 'simplified';
                    $billing->reference_invoice_number = $invoice->originalInvoice->invoice_number ?? null;
                    $billing->member_id                = $invoice->member_id;
                    $billing->amount                   = (float) $invoice->subtotal;
                    $billing->vat_amount               = (float) $invoice->vat_amount;
                    $billing->total_amount             = (float) $invoice->total;
                    $billing->buyer_name               = $member?->name ?? 'عميل';
                    $billing->buyer_tax_number         = $member?->national_id ?? null;
                    $billing->save();

                    $invoice->zatca_billing_invoice_id = $billing->id;
                    $invoice->saveQuietly();
                }

                $result = ZatcaPhase2Service::signAndSubmit($billing);

                if ($result['success']) {
                    $success++;
                } else {
                    $failed++;
                    $errors[] = $invoice->invoice_number . ': ' . ($result['status'] ?? '');
                }

            } catch (\Throwable $e) {
                $failed++;
                Log::error('GymSwInvoiceFrontController::bulkSubmitZatca failed', [
                    'invoice_id' => $id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'success' => $failed === 0,
            'message' => trans('sw.zatca_bulk_result', ['success' => $success, 'failed' => $failed]),
            'errors'  => $errors,
        ]);
    }

    public function submitZatca(int $id)
    {
        if (!config('sw_billing.zatca_enabled')) {
            return response()->json(['success' => false, 'message' => trans('sw.zatca_disabled')], 403);
        }

        $invoice = GymSwInvoice::with(['member', 'zatcaBillingInvoice'])->findOrFail($id);

        if (!in_array($invoice->type, ['sales', 'credit_note'])) {
            return response()->json(['success' => false, 'message' => trans('sw.zatca_not_applicable')], 422);
        }

        try {
            // Re-use existing SwBillingInvoice or create a new one
            $billing = $invoice->zatcaBillingInvoice;

            if (!$billing) {
                $member  = $invoice->member;
                $billing = new SwBillingInvoice();
                $billing->invoice_number           = $invoice->invoice_number;
                $billing->invoice_type             = $invoice->type === 'credit_note' ? 'credit_note' : 'simplified';
                $billing->reference_invoice_number = $invoice->originalInvoice->invoice_number ?? null;
                $billing->member_id                = $invoice->member_id;
                $billing->amount                   = (float) $invoice->subtotal;
                $billing->vat_amount               = (float) $invoice->vat_amount;
                $billing->total_amount             = (float) $invoice->total;
                $billing->buyer_name               = $member?->name ?? 'عميل';
                $billing->buyer_tax_number         = $member?->national_id ?? null;
                $billing->save();

                $invoice->zatca_billing_invoice_id = $billing->id;
                $invoice->saveQuietly();
            }

            $result = ZatcaPhase2Service::signAndSubmit($billing);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'status'  => $result['status'],
                    'message' => trans('sw.zatca_submitted_successfully'),
                ]);
            }

            return response()->json([
                'success' => false,
                'status'  => $result['status'],
                'message' => trans('sw.zatca_submission_failed'),
            ], 422);

        } catch (\Throwable $e) {
            Log::error('GymSwInvoiceFrontController::submitZatca failed', [
                'invoice_id' => $id,
                'error'      => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

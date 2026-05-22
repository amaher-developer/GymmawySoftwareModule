<?php

namespace Modules\Software\Http\Controllers\Front;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Billing\Models\SwBillingInvoice;
use Modules\Billing\Services\ZatcaPhase2Service;
use Modules\Software\Exports\GymSwInvoicesReportExport;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymSwInvoice;
use Modules\Software\Services\GymSwInvoiceService;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
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
        $allInvoices = $this->buildFilteredCollection($request);
        $insights    = $this->calculateInsights($allInvoices);

        $query = GymSwInvoice::branch();
        if ($request->filled('type'))      $query->where('type', $request->type);
        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('member_id')) $query->where('member_id', $request->member_id);
        if ($request->filled('date_from')) $query->whereDate('issued_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('issued_at', '<=', $request->date_to);

        $invoices = $query->with('zatcaBillingInvoice')->latest()->paginate(20);

        $title = trans('sw.invoices');
        return view('software::gym_sw_invoices.index', compact('invoices', 'insights', 'title'));
    }

    public function exportExcel(Request $request)
    {
        $allInvoices = $this->buildFilteredCollection($request);
        $insights    = $this->calculateInsights($allInvoices);

        $data = [
            'insights'   => $insights,
            'invoices'   => $allInvoices,
            'date_from'  => $request->date_from,
            'date_to'    => $request->date_to,
        ];

        $fileName = 'invoices-report-' . now()->format('Y-m-d') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new GymSwInvoicesReportExport(['data' => $data, 'lang' => $this->lang, 'settings' => $this->mainSettings]),
            $fileName
        );
    }

    public function exportReportPDF(Request $request)
    {
        $allInvoices = $this->buildFilteredCollection($request);
        $insights    = $this->calculateInsights($allInvoices);

        $data = [
            'insights'  => $insights,
            'invoices'  => $allInvoices,
            'date_from' => $request->date_from,
            'date_to'   => $request->date_to,
        ];

        $title    = trans('sw.invoices_report');
        $fileName = 'invoices-report-' . now()->format('Y-m-d');

        if ($this->lang === 'ar') {
            try {
                $mpdf = new Mpdf([
                    'mode'             => 'utf-8',
                    'format'           => 'A4',
                    'orientation'      => 'P',
                    'margin_left'      => 15,
                    'margin_right'     => 15,
                    'margin_top'       => 16,
                    'margin_bottom'    => 16,
                    'default_font'     => 'dejavusans',
                    'default_font_size'=> 10,
                ]);

                $html = view('software::Front.gym_sw_invoices_report_pdf', compact('data', 'title'))->render();
                $mpdf->WriteHTML($html);

                return response($mpdf->Output($fileName . '.pdf', 'D'), 200, [
                    'Content-Type'        => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $fileName . '.pdf"',
                ]);
            } catch (\Exception $e) {
                Log::error('mPDF failed for invoices report: ' . $e->getMessage());
            }
        }

        $pdf = PDF::loadView('software::Front.gym_sw_invoices_report_pdf', compact('data', 'title'))
            ->setPaper([0, 0, 595, 842], 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => false, 'defaultFont' => 'DejaVu Sans']);

        return $pdf->download($fileName . '.pdf');
    }

    private function buildFilteredCollection(Request $request, array $columns = ['id', 'invoice_number', 'type', 'status', 'total', 'amount_paid', 'issued_at']): Collection
    {
        $query = GymSwInvoice::branch()->select($columns);

        if ($request->filled('type'))      $query->where('type', $request->type);
        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('member_id')) $query->where('member_id', $request->member_id);
        if ($request->filled('date_from')) $query->whereDate('issued_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('issued_at', '<=', $request->date_to);

        return $query->latest()->get();
    }

    private function calculateInsights(Collection $invoices): array
    {
        return [
            'total_count'     => $invoices->count(),
            'total_amount'    => $invoices->sum('total'),
            'total_paid'      => $invoices->sum('amount_paid'),
            'total_remaining' => $invoices->sum('amount_remaining'),
            'by_type' => [
                'sales'       => ['count' => $invoices->where('type', 'sales')->count(),       'amount' => $invoices->where('type', 'sales')->sum('total')],
                'purchase'    => ['count' => $invoices->where('type', 'purchase')->count(),    'amount' => $invoices->where('type', 'purchase')->sum('total')],
                'credit_note' => ['count' => $invoices->where('type', 'credit_note')->count(), 'amount' => $invoices->where('type', 'credit_note')->sum('total')],
            ],
            'by_status' => [
                'draft'     => ['count' => $invoices->where('status', 'draft')->count(),     'amount' => $invoices->where('status', 'draft')->sum('total')],
                'partial'   => ['count' => $invoices->where('status', 'partial')->count(),   'amount' => $invoices->where('status', 'partial')->sum('total')],
                'paid'      => ['count' => $invoices->where('status', 'paid')->count(),      'amount' => $invoices->where('status', 'paid')->sum('total')],
                'cancelled' => ['count' => $invoices->where('status', 'cancelled')->count(), 'amount' => $invoices->where('status', 'cancelled')->sum('total')],
            ],
        ];
    }

    public function show(int $id)
    {
        $invoice = GymSwInvoice::with(['member', 'moneyBoxes.pay_type', 'statusLogs', 'originalInvoice', 'creditNotes', 'zatcaBillingInvoice'])->findOrFail($id);
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

                $billing = $this->resolveOrCreateBillingInvoice($invoice);
                $result  = ZatcaPhase2Service::signAndSubmit($billing);

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

        $invoice = GymSwInvoice::with(['member', 'zatcaBillingInvoice', 'originalInvoice'])->findOrFail($id);

        if (!in_array($invoice->type, ['sales', 'credit_note'])) {
            return response()->json(['success' => false, 'message' => trans('sw.zatca_not_applicable')], 422);
        }

        try {
            $billing = $this->resolveOrCreateBillingInvoice($invoice);
            $result  = ZatcaPhase2Service::signAndSubmit($billing);

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

    /**
     * Return the linked SwBillingInvoice, falling back to an existing row by
     * invoice_number (repairs a broken FK), or creating a fresh record.
     * Repairs the zatca_billing_invoice_id link if it was missing.
     */
    private function resolveOrCreateBillingInvoice(GymSwInvoice $invoice): SwBillingInvoice
    {
        // 1. Already linked via FK
        if ($invoice->zatcaBillingInvoice) {
            return $invoice->zatcaBillingInvoice;
        }

        // 2. Orphaned row — link was never saved back (e.g. previous exception)
        $existing = SwBillingInvoice::where('invoice_number', $invoice->invoice_number)->first();
        if ($existing) {
            $invoice->zatca_billing_invoice_id = $existing->id;
            $invoice->saveQuietly();
            return $existing;
        }

        // 3. Brand new
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
        $billing->created_at               = $invoice->issued_at ?? now();
        $billing->save();

        $invoice->zatca_billing_invoice_id = $billing->id;
        $invoice->saveQuietly();

        return $billing;
    }
}

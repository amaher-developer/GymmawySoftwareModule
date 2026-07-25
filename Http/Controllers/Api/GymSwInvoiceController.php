<?php

namespace Modules\Software\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Modules\Generic\Http\Controllers\Api\GenericApiController;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Http\Requests\GymSwCreateCreditNoteRequest;
use Modules\Software\Http\Requests\GymSwCreatePurchaseInvoiceRequest;
use Modules\Software\Http\Requests\GymSwCreateSalesInvoiceRequest;
use Modules\Software\Http\Resources\GymSwInvoiceResource;
use Modules\Software\Models\GymSwInvoice;
use Modules\Software\Services\GymSwInvoiceService;

class GymSwInvoiceController extends GenericApiController
{
    protected GymSwInvoiceService $invoiceService;

    public function __construct()
    {
        parent::__construct();
        $this->invoiceService = new GymSwInvoiceService();
    }

    // ── Listing ──────────────────────────────────────────────────────────────

    /**
     * GET /api/gym-sw-invoices
     * Filterable by: type, status, member_id, date_from, date_to
     */
    public function index(Request $request): JsonResponse
    {
        $query = GymSwInvoice::query();

        if ($request->filled('type'))      $query->where('type', $request->type);
        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('member_id')) $query->where('member_id', $request->member_id);
        if ($request->filled('date_from')) $query->whereDate('issued_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('issued_at', '<=', $request->date_to);

        $invoices = $query->latest()->paginate(20);

        $this->successResponse();
        $this->return['data']       = GymSwInvoiceResource::collection($invoices);
        $this->return['pagination'] = [
            'current_page' => $invoices->currentPage(),
            'last_page'    => $invoices->lastPage(),
            'total'        => $invoices->total(),
        ];

        return response()->json($this->return);
    }

    // ── Single record ────────────────────────────────────────────────────────

    /** GET /api/gym-sw-invoices/{id} */
    public function show(int $id): JsonResponse
    {
        $invoice = GymSwInvoice::with(['moneyBoxes', 'statusLogs', 'originalInvoice'])->findOrFail($id);

        $this->successResponse();
        $this->return['data'] = new GymSwInvoiceResource($invoice);

        return response()->json($this->return);
    }

    // ── Creation ─────────────────────────────────────────────────────────────

    /** POST /api/gym-sw-invoices/sales */
    public function storeSales(GymSwCreateSalesInvoiceRequest $request): JsonResponse
    {
        $invoice = $this->invoiceService->createSalesInvoice(
            array_merge($request->validated(), [
                'branch_setting_id' => $request->get('branch_setting_id'),
            ])
        );

        $this->return['status']  = Response::HTTP_CREATED;
        $this->return['success'] = true;
        $this->return['message'] = 'Invoice created successfully.';
        $this->return['data']    = new GymSwInvoiceResource($invoice);

        return response()->json($this->return, Response::HTTP_CREATED);
    }

    /** POST /api/gym-sw-invoices/purchase */
    public function storePurchase(GymSwCreatePurchaseInvoiceRequest $request): JsonResponse
    {
        $invoice = $this->invoiceService->createPurchaseInvoice(
            array_merge($request->validated(), [
                'branch_setting_id' => $request->get('branch_setting_id'),
            ])
        );

        $this->return['status']  = Response::HTTP_CREATED;
        $this->return['success'] = true;
        $this->return['message'] = 'Purchase invoice created successfully.';
        $this->return['data']    = new GymSwInvoiceResource($invoice);

        return response()->json($this->return, Response::HTTP_CREATED);
    }

    /** POST /api/gym-sw-invoices/{id}/credit-note */
    public function storeCreditNote(GymSwCreateCreditNoteRequest $request, int $id): JsonResponse
    {
        $original   = GymSwInvoice::findOrFail($id);
        $creditNote = $this->invoiceService->createCreditNote($original, array_merge($request->validated(), [
            'branch_setting_id' => $request->get('branch_setting_id'),
            'user_id'           => $request->get('user_id', 1),
        ]));

        $this->return['status']  = Response::HTTP_CREATED;
        $this->return['success'] = true;
        $this->return['message'] = 'Credit note created successfully.';
        $this->return['data']    = new GymSwInvoiceResource($creditNote);

        return response()->json($this->return, Response::HTTP_CREATED);
    }

    // ── Payment recording ────────────────────────────────────────────────────

    /**
     * PATCH /api/gym-sw-invoices/{id}/payment
     * Body: { "amount": 500.00, "money_box_id": 123 }
     */
    public function recordPayment(Request $request, int $id): JsonResponse
    {
        $request->validate(['amount' => 'required|numeric|min:0.01']);

        $invoice  = GymSwInvoice::findOrFail($id);
        $moneyBox = $request->filled('money_box_id')
            ? GymMoneyBox::find($request->money_box_id)
            : null;

        $this->invoiceService->recordPayment($invoice, (float) $request->amount, $moneyBox);

        $this->successResponse();
        $this->return['data'] = new GymSwInvoiceResource($invoice->fresh());

        return response()->json($this->return);
    }

    // ── Cancellation ─────────────────────────────────────────────────────────

    /** POST /api/gym-sw-invoices/{id}/cancel */
    public function cancel(int $id): JsonResponse
    {
        $invoice = GymSwInvoice::findOrFail($id);

        if ($invoice->status === 'cancelled') {
            $this->return['status']  = Response::HTTP_UNPROCESSABLE_ENTITY;
            $this->return['success'] = false;
            $this->return['message'] = 'Invoice is already cancelled.';
            return response()->json($this->return, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $invoice->status = 'cancelled';
        $invoice->save();

        $this->successResponse();
        $this->return['message'] = 'Invoice cancelled successfully.';
        $this->return['data']    = new GymSwInvoiceResource($invoice->fresh());

        return response()->json($this->return);
    }

    // ── PDF download ─────────────────────────────────────────────────────────

    /** GET /api/gym-sw-invoices/{id}/pdf */
    public function downloadPdf(int $id)
    {
        $invoice = GymSwInvoice::findOrFail($id);
        $path    = $this->invoiceService->generatePdf($invoice);

        return Storage::download($path, $invoice->invoice_number . '.pdf');
    }

    // ── Member invoices ──────────────────────────────────────────────────────

    /** GET /api/gym-sw-invoices/member/{memberId} */
    public function memberInvoices(int $memberId): JsonResponse
    {
        $invoices = GymSwInvoice::where('member_id', $memberId)
            ->latest()
            ->paginate(20);

        $this->successResponse();
        $this->return['data']       = GymSwInvoiceResource::collection($invoices);
        $this->return['pagination'] = [
            'current_page' => $invoices->currentPage(),
            'last_page'    => $invoices->lastPage(),
            'total'        => $invoices->total(),
        ];

        return response()->json($this->return);
    }
}

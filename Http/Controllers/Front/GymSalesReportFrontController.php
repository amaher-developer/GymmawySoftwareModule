<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Software\Classes\TypeConstants;
use Modules\Software\Exports\SalesReportExport;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Models\GymPaymentType;
use Modules\Software\Models\GymUser;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Sales Report Controller
 *
 * PURPOSE: Answer one question only: "How much did we sell?"
 * This is a SUMMARY report, NOT a list of invoices.
 *
 * DATA SOURCE: Sales invoices only (GymMoneyBox with revenue types)
 *
 * INCLUDED SALES:
 * - Cash sales
 * - Credit (postpaid) sales
 * - Sales using store balance
 *
 * EXCLUDED (IMPORTANT):
 * - Wallet top-ups (WalletTopUp type) - these are customer advances, NOT revenue
 * - Debt payments (DebtPayment type) - these are settlements, NOT revenue
 * - Money Box add/withdraw entries - these are cash flow operations, NOT sales
 *
 * RULES:
 * - Each sale = one invoice
 * - Invoice is the ONLY source of revenue
 * - Payment timing does NOT affect sales totals
 */
class GymSalesReportFrontController extends GymGenericFrontController
{
    public $fileName;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Sales Report Summary
     */
    public function index()
    {
        $title = trans('sw.sales_report');

        // Get filter inputs
        $from = request('from') ? Carbon::parse(request('from'))->format('Y-m-d') : Carbon::now()->startOfMonth()->format('Y-m-d');
        $to = request('to') ? Carbon::parse(request('to'))->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $user = request('user');

        $users = GymUser::branch()->where('is_test', 0)->get();
        $payment_types = GymPaymentType::branch()->orderBy('id')->get();

        // Define SALE types - these are actual revenue-generating transactions
        // EXCLUDES: WalletTopUp (customer advance), DebtPayment (settlement), MoneyBoxAdd/Withdraw (cash flow)
        $saleTypes = [
            // Member subscriptions
            TypeConstants::CreateMember,
            TypeConstants::RenewMember,
            TypeConstants::EditMember,
            TypeConstants::CreateMemberPayAmountRemainingForm,
            TypeConstants::CreateSubscription,
            TypeConstants::EditSubscription,

            // PT subscriptions
            TypeConstants::CreatePTMember,
            TypeConstants::RenewPTMember,
            TypeConstants::EditPTMember,
            TypeConstants::CreatePTMemberPayAmountRemainingForm,
            TypeConstants::CreatePTSubscription,
            TypeConstants::EditPTSubscription,

            // Activities (non-member/daily)
            TypeConstants::CreateActivity,
            TypeConstants::EditActivity,
            TypeConstants::CreateNonMember,
            TypeConstants::EditNonMember,

            // Store sales
            TypeConstants::CreateStoreOrder,
            TypeConstants::EditStoreOrder,
            TypeConstants::CashSale,
        ];

        // Base query for sales - revenue additions only (operation = 0)
        $salesQuery = GymMoneyBox::branch()
            ->whereIn('type', $saleTypes)
            ->where('operation', TypeConstants::Add) // Only additions (revenue)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        // Apply user filter if provided
        if (!empty($user)) {
            $salesQuery->where('user_id', (int)$user);
        }

        $sales = $salesQuery->get();

        // Calculate totals
        $totalSales = $sales->sum('amount');

        // Breakdown by payment method
        $salesByPaymentType = [];
        foreach ($payment_types as $paymentType) {
            $amount = $sales->where('payment_type', $paymentType->payment_id)->sum('amount');
            $salesByPaymentType[$paymentType->payment_id] = [
                'name' => $paymentType->name,
                'amount' => $amount
            ];
        }

        // Cash sales (payment_type = 0 or CASH_PAYMENT)
        $cashSales = $sales->where('payment_type', TypeConstants::CASH_PAYMENT)->sum('amount');

        // Online/Card sales (payment_type = 1 or ONLINE_PAYMENT)
        $onlineSales = $sales->where('payment_type', TypeConstants::ONLINE_PAYMENT)->sum('amount');

        // Bank transfer sales (payment_type = 2 or BANK_TRANSFER_PAYMENT)
        $bankSales = $sales->where('payment_type', TypeConstants::BANK_TRANSFER_PAYMENT)->sum('amount');

        // Sales using store balance (is_store_balance = 1)
        $storeBalanceSales = $sales->where('is_store_balance', 1)->sum('amount');

        // Credit/Postpaid sales - these are sales where amount_remaining > 0 at time of creation
        // We can identify these by checking member_subscription with amount_remaining
        $creditSales = $sales->filter(function($sale) {
            return isset($sale->member_subscription) &&
                   $sale->member_subscription &&
                   $sale->member_subscription->amount_remaining > 0;
        })->sum('amount');

        // Breakdown by sale category
        $subscriptionTypes = [
            TypeConstants::CreateMember,
            TypeConstants::RenewMember,
            TypeConstants::EditMember,
            TypeConstants::CreateMemberPayAmountRemainingForm,
            TypeConstants::CreateSubscription,
            TypeConstants::EditSubscription,
        ];
        $subscriptionSales = $sales->whereIn('type', $subscriptionTypes)->sum('amount');

        $ptTypes = [
            TypeConstants::CreatePTMember,
            TypeConstants::RenewPTMember,
            TypeConstants::EditPTMember,
            TypeConstants::CreatePTMemberPayAmountRemainingForm,
            TypeConstants::CreatePTSubscription,
            TypeConstants::EditPTSubscription,
        ];
        $ptSales = $sales->whereIn('type', $ptTypes)->sum('amount');

        $activityTypes = [
            TypeConstants::CreateActivity,
            TypeConstants::EditActivity,
            TypeConstants::CreateNonMember,
            TypeConstants::EditNonMember,
        ];
        $activitySales = $sales->whereIn('type', $activityTypes)->sum('amount');

        $storeTypes = [
            TypeConstants::CreateStoreOrder,
            TypeConstants::EditStoreOrder,
            TypeConstants::CashSale,
        ];
        $storeSales = $sales->whereIn('type', $storeTypes)->sum('amount');

        // Moneybox operations (separate query - these are NOT sales)
        $moneyboxQuery = GymMoneyBox::branch()
            ->whereIn('type', [
                TypeConstants::CreateMoneyBoxAdd,
                TypeConstants::CreateMoneyBoxWithdraw,
                TypeConstants::CreateMoneyBoxWithdrawEarnings,
            ])
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        if (!empty($user)) {
            $moneyboxQuery->where('user_id', (int)$user);
        }

        $moneyboxRecords = $moneyboxQuery->get();
        $moneyboxAdd = $moneyboxRecords->where('type', TypeConstants::CreateMoneyBoxAdd)->sum('amount');
        $moneyboxWithdraw = $moneyboxRecords->where('type', TypeConstants::CreateMoneyBoxWithdraw)->sum('amount');
        $moneyboxWithdrawEarnings = $moneyboxRecords->where('type', TypeConstants::CreateMoneyBoxWithdrawEarnings)->sum('amount');
        $netTotal = $totalSales + $moneyboxAdd - $moneyboxWithdraw - $moneyboxWithdrawEarnings;

        $search_query = request()->query();

        return view('software::Front.sales_report_front_list', compact(
            'title',
            'from',
            'to',
            'users',
            'payment_types',
            'totalSales',
            'salesByPaymentType',
            'cashSales',
            'onlineSales',
            'bankSales',
            'storeBalanceSales',
            'creditSales',
            'subscriptionSales',
            'ptSales',
            'activitySales',
            'storeSales',
            'moneyboxAdd',
            'moneyboxWithdraw',
            'moneyboxWithdrawEarnings',
            'netTotal',
            'search_query'
        ));
    }

    /**
     * Export Sales Report to Excel
     */
    public function exportExcel()
    {
        $from = request('from') ? Carbon::parse(request('from'))->format('Y-m-d') : Carbon::now()->startOfMonth()->format('Y-m-d');
        $to = request('to') ? Carbon::parse(request('to'))->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $user = request('user');

        // Same query logic as index
        $saleTypes = [
            TypeConstants::CreateMember, TypeConstants::RenewMember, TypeConstants::EditMember,
            TypeConstants::CreateMemberPayAmountRemainingForm, TypeConstants::CreateSubscription, TypeConstants::EditSubscription,
            TypeConstants::CreatePTMember, TypeConstants::RenewPTMember, TypeConstants::EditPTMember,
            TypeConstants::CreatePTMemberPayAmountRemainingForm, TypeConstants::CreatePTSubscription, TypeConstants::EditPTSubscription,
            TypeConstants::CreateActivity, TypeConstants::EditActivity,
            TypeConstants::CreateNonMember, TypeConstants::EditNonMember,
            TypeConstants::CreateStoreOrder, TypeConstants::EditStoreOrder, TypeConstants::CashSale,
        ];

        $salesQuery = GymMoneyBox::branch()
            ->whereIn('type', $saleTypes)
            ->where('operation', TypeConstants::Add)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        if (!empty($user)) {
            $salesQuery->where('user_id', (int)$user);
        }

        $sales = $salesQuery->get();
        $payment_types = GymPaymentType::branch()->orderBy('id')->get();

        // Calculate all the summary data
        $data = $this->calculateSummaryData($sales, $payment_types);
        $data['from'] = $from;
        $data['to'] = $to;
        $data = array_merge($data, $this->getMoneyboxData($from, $to, $user, $data['totalSales']));

        $this->fileName = 'sales-report-' . Carbon::now()->toDateTimeString();

        $notes = trans('sw.export_excel_sales_report');
        $this->userLog($notes, TypeConstants::ExportSalesReportExcel);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new SalesReportExport(['data' => $data, 'lang' => $this->lang, 'settings' => $this->mainSettings]),
            $this->fileName . '.xlsx'
        );
    }

    /**
     * Export Sales Report to PDF
     */
    public function exportPDF()
    {
        $from = request('from') ? Carbon::parse(request('from'))->format('Y-m-d') : Carbon::now()->startOfMonth()->format('Y-m-d');
        $to = request('to') ? Carbon::parse(request('to'))->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $user = request('user');

        $saleTypes = [
            TypeConstants::CreateMember, TypeConstants::RenewMember, TypeConstants::EditMember,
            TypeConstants::CreateMemberPayAmountRemainingForm, TypeConstants::CreateSubscription, TypeConstants::EditSubscription,
            TypeConstants::CreatePTMember, TypeConstants::RenewPTMember, TypeConstants::EditPTMember,
            TypeConstants::CreatePTMemberPayAmountRemainingForm, TypeConstants::CreatePTSubscription, TypeConstants::EditPTSubscription,
            TypeConstants::CreateActivity, TypeConstants::EditActivity,
            TypeConstants::CreateNonMember, TypeConstants::EditNonMember,
            TypeConstants::CreateStoreOrder, TypeConstants::EditStoreOrder, TypeConstants::CashSale,
        ];

        $salesQuery = GymMoneyBox::branch()
            ->whereIn('type', $saleTypes)
            ->where('operation', TypeConstants::Add)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        if (!empty($user)) {
            $salesQuery->where('user_id', (int)$user);
        }

        $sales = $salesQuery->get();
        $payment_types = GymPaymentType::branch()->orderBy('id')->get();

        $data = $this->calculateSummaryData($sales, $payment_types);
        $data['from'] = $from;
        $data['to'] = $to;
        $data = array_merge($data, $this->getMoneyboxData($from, $to, $user, $data['totalSales']));

        $this->fileName = 'sales-report-' . Carbon::now()->toDateTimeString();
        $title = trans('sw.sales_report');

        // Try mPDF for better Arabic support
        if ($this->lang == 'ar') {
            try {
                $mpdf = new Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4',
                    'orientation' => 'P',
                    'margin_left' => 15,
                    'margin_right' => 15,
                    'margin_top' => 16,
                    'margin_bottom' => 16,
                    'default_font' => 'dejavusans',
                    'default_font_size' => 10
                ]);

                $html = view('software::Front.sales_report_pdf', [
                    'data' => $data,
                    'title' => $title,
                    'lang' => $this->lang
                ])->render();

                $mpdf->WriteHTML($html);

                $notes = trans('sw.export_pdf_sales_report');
                $this->userLog($notes, TypeConstants::ExportSalesReportPDF);

                return response($mpdf->Output($this->fileName . '.pdf', 'D'), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $this->fileName . '.pdf"'
                ]);

            } catch (\Exception $e) {
                \Log::error('mPDF failed, falling back to DomPDF: ' . $e->getMessage());
            }
        }

        // DomPDF fallback
        $customPaper = array(0, 0, 595, 842); // A4 size
        $pdf = PDF::loadView('software::Front.sales_report_pdf', [
            'data' => $data,
            'title' => $title,
            'lang' => $this->lang
        ])
        ->setPaper($customPaper, 'portrait')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'DejaVu Sans',
        ]);

        $notes = trans('sw.export_pdf_sales_report');
        $this->userLog($notes, TypeConstants::ExportSalesReportPDF);

        return $pdf->download($this->fileName . '.pdf');
    }

    /**
     * Calculate summary data from sales collection
     */
    private function calculateSummaryData($sales, $payment_types)
    {
        $data = [];

        $data['totalSales'] = $sales->sum('amount');

        // By payment type
        $data['salesByPaymentType'] = [];
        foreach ($payment_types as $paymentType) {
            $data['salesByPaymentType'][$paymentType->payment_id] = [
                'name' => $paymentType->name,
                'amount' => $sales->where('payment_type', $paymentType->payment_id)->sum('amount')
            ];
        }

        $data['cashSales'] = $sales->where('payment_type', TypeConstants::CASH_PAYMENT)->sum('amount');
        $data['onlineSales'] = $sales->where('payment_type', TypeConstants::ONLINE_PAYMENT)->sum('amount');
        $data['bankSales'] = $sales->where('payment_type', TypeConstants::BANK_TRANSFER_PAYMENT)->sum('amount');
        $data['storeBalanceSales'] = $sales->where('is_store_balance', 1)->sum('amount');

        // By category
        $subscriptionTypes = [
            TypeConstants::CreateMember, TypeConstants::RenewMember, TypeConstants::EditMember,
            TypeConstants::CreateMemberPayAmountRemainingForm, TypeConstants::CreateSubscription, TypeConstants::EditSubscription,
        ];
        $data['subscriptionSales'] = $sales->whereIn('type', $subscriptionTypes)->sum('amount');

        $ptTypes = [
            TypeConstants::CreatePTMember, TypeConstants::RenewPTMember, TypeConstants::EditPTMember,
            TypeConstants::CreatePTMemberPayAmountRemainingForm, TypeConstants::CreatePTSubscription, TypeConstants::EditPTSubscription,
        ];
        $data['ptSales'] = $sales->whereIn('type', $ptTypes)->sum('amount');

        $activityTypes = [
            TypeConstants::CreateActivity, TypeConstants::EditActivity,
            TypeConstants::CreateNonMember, TypeConstants::EditNonMember,
        ];
        $data['activitySales'] = $sales->whereIn('type', $activityTypes)->sum('amount');

        $storeTypes = [
            TypeConstants::CreateStoreOrder, TypeConstants::EditStoreOrder, TypeConstants::CashSale,
        ];
        $data['storeSales'] = $sales->whereIn('type', $storeTypes)->sum('amount');

        return $data;
    }

    /**
     * Query moneybox operations for the given date range and user filter
     */
    private function getMoneyboxData($from, $to, $user = null, $totalSales = 0)
    {
        $moneyboxQuery = GymMoneyBox::branch()
            ->whereIn('type', [
                TypeConstants::CreateMoneyBoxAdd,
                TypeConstants::CreateMoneyBoxWithdraw,
                TypeConstants::CreateMoneyBoxWithdrawEarnings,
            ])
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        if (!empty($user)) {
            $moneyboxQuery->where('user_id', (int)$user);
        }

        $moneyboxRecords = $moneyboxQuery->get();

        $add = $moneyboxRecords->where('type', TypeConstants::CreateMoneyBoxAdd)->sum('amount');
        $withdraw = $moneyboxRecords->where('type', TypeConstants::CreateMoneyBoxWithdraw)->sum('amount');
        $withdrawEarnings = $moneyboxRecords->where('type', TypeConstants::CreateMoneyBoxWithdrawEarnings)->sum('amount');

        return [
            'moneyboxAdd' => $add,
            'moneyboxWithdraw' => $withdraw,
            'moneyboxWithdrawEarnings' => $withdrawEarnings,
            'netTotal' => $totalSales + $add - $withdraw - $withdrawEarnings,
        ];
    }
}

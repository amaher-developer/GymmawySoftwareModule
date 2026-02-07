<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Software\Classes\TypeConstants;
use Modules\Software\Exports\CustomerBalancesExport;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymMemberSubscription;
use Modules\Software\Models\GymPTMember;
use Modules\Software\Models\GymTrainingMember;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Customer Balances Report Controller
 *
 * PURPOSE: Answer one question only: "Who owes money and who has credit?"
 *
 * DATA SOURCES:
 * 1. Members table - store_balance field (wallet credit/debit)
 * 2. Member Subscriptions table - amount_remaining field (unpaid subscription amounts)
 * 3. PT Members table - amount_remaining field (unpaid PT subscription amounts)
 * 4. Training Members table - (total - amount_paid) for unpaid training amounts
 *
 * REQUIRED OUTPUT (PER CUSTOMER):
 * - Customer name
 * - Store balance (positive = credit, negative = debt)
 * - Remaining subscription amount (always debt - they owe us)
 * - Remaining PT amount (always debt - they owe us)
 * - Remaining Training amount (always debt - they owe us)
 *
 * REQUIRED TOTALS:
 * - Total store credit (positive store_balance)
 * - Total store debt (negative store_balance)
 * - Total remaining amounts (unpaid subscriptions, PT, training)
 *
 * RULES:
 * - Do NOT calculate revenue here
 * - Do NOT include Money Box logic
 * - This report is informational only
 */
class GymCustomerBalancesReportFrontController extends GymGenericFrontController
{
    public $fileName;
    public $limit = 20;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all remaining amounts data for members
     */
    private function getRemainingAmountsData()
    {
        // Get members with remaining subscription amounts
        $subscriptionRemaining = GymMemberSubscription::branch()
            ->select('member_id', DB::raw('SUM(amount_remaining) as total_remaining'))
            ->whereRaw('ROUND(amount_remaining, 0) > 0')
            ->groupBy('member_id')
            ->pluck('total_remaining', 'member_id')
            ->toArray();

        // Get members with remaining PT amounts
        $ptRemaining = GymPTMember::branch()
            ->select('member_id', DB::raw('SUM(amount_remaining) as total_remaining'))
            ->whereRaw('ROUND(amount_remaining, 0) > 0')
            ->groupBy('member_id')
            ->pluck('total_remaining', 'member_id')
            ->toArray();

        // Get members with remaining Training amounts (total - amount_paid)
        $trainingRemaining = GymTrainingMember::branch()
            ->select('member_id', DB::raw('SUM(COALESCE(total, 0) - COALESCE(amount_paid, 0)) as total_remaining'))
            ->whereRaw('ROUND(COALESCE(total, 0) - COALESCE(amount_paid, 0), 0) > 0')
            ->groupBy('member_id')
            ->pluck('total_remaining', 'member_id')
            ->toArray();

        // Combine all member IDs with any remaining amount
        $allMemberIds = array_unique(array_merge(
            array_keys($subscriptionRemaining),
            array_keys($ptRemaining),
            array_keys($trainingRemaining)
        ));

        return [
            'subscription' => $subscriptionRemaining,
            'pt' => $ptRemaining,
            'training' => $trainingRemaining,
            'allMemberIds' => $allMemberIds,
        ];
    }

    /**
     * Customer Balances Report
     */
    public function index()
    {
        $title = trans('sw.customer_balances_report');

        // Get filter inputs
        $search = request('search');
        $balanceType = request('balance_type'); // 'store_credit', 'store_debt', 'remaining', 'remaining_subscription', 'remaining_pt', 'remaining_training', or empty for all

        // Get all remaining amounts data
        $remainingData = $this->getRemainingAmountsData();
        $subscriptionRemaining = $remainingData['subscription'];
        $ptRemaining = $remainingData['pt'];
        $trainingRemaining = $remainingData['training'];
        $allMemberIdsWithRemaining = $remainingData['allMemberIds'];

        // Build query for members
        $query = GymMember::branch()
            ->where(function($q) use ($allMemberIdsWithRemaining) {
                $q->where('store_balance', '!=', 0)
                  ->orWhereIn('id', $allMemberIdsWithRemaining);
            });

        // Apply search filter
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        // Apply balance type filter
        if ($balanceType === 'store_credit') {
            $query->where('store_balance', '>', 0);
        } elseif ($balanceType === 'store_debt') {
            $query->where('store_balance', '<', 0);
        } elseif ($balanceType === 'remaining') {
            $query->whereIn('id', $allMemberIdsWithRemaining);
        } elseif ($balanceType === 'remaining_subscription') {
            $query->whereIn('id', array_keys($subscriptionRemaining));
        } elseif ($balanceType === 'remaining_pt') {
            $query->whereIn('id', array_keys($ptRemaining));
        } elseif ($balanceType === 'remaining_training') {
            $query->whereIn('id', array_keys($trainingRemaining));
        }

        // Get all records for totals calculation (before pagination)
        $allMembersQuery = GymMember::branch()
            ->where(function($q) use ($allMemberIdsWithRemaining) {
                $q->where('store_balance', '!=', 0)
                  ->orWhereIn('id', $allMemberIdsWithRemaining);
            });

        $allMembers = $allMembersQuery->get();

        // Calculate totals - Store Balance
        $totalStoreCredit = $allMembers->where('store_balance', '>', 0)->sum('store_balance');
        $totalStoreDebt = abs($allMembers->where('store_balance', '<', 0)->sum('store_balance'));

        // Calculate totals - Remaining Amounts (separate)
        $totalSubscriptionRemaining = array_sum($subscriptionRemaining);
        $totalPTRemaining = array_sum($ptRemaining);
        $totalTrainingRemaining = array_sum($trainingRemaining);
        $totalRemainingAmount = $totalSubscriptionRemaining + $totalPTRemaining + $totalTrainingRemaining;

        // Count statistics
        $customersWithStoreCredit = $allMembers->where('store_balance', '>', 0)->count();
        $customersWithStoreDebt = $allMembers->where('store_balance', '<', 0)->count();
        $customersWithRemaining = count($allMemberIdsWithRemaining);
        $customersWithSubscriptionRemaining = count($subscriptionRemaining);
        $customersWithPTRemaining = count($ptRemaining);
        $customersWithTrainingRemaining = count($trainingRemaining);
        $totalCustomers = $allMembers->count();

        // Paginate for display
        $members = $query->orderBy('name', 'asc')->paginate($this->limit);
        $total = $members->total();

        // Attach remaining amounts to members
        foreach ($members as $member) {
            $member->subscription_remaining = $subscriptionRemaining[$member->id] ?? 0;
            $member->pt_remaining = $ptRemaining[$member->id] ?? 0;
            $member->training_remaining = $trainingRemaining[$member->id] ?? 0;
            $member->remaining_amount = $member->subscription_remaining + $member->pt_remaining + $member->training_remaining;
        }

        $search_query = request()->query();

        return view('software::Front.customer_balances_report_front_list', compact(
            'title',
            'members',
            'total',
            'totalStoreCredit',
            'totalStoreDebt',
            'totalRemainingAmount',
            'totalSubscriptionRemaining',
            'totalPTRemaining',
            'totalTrainingRemaining',
            'customersWithStoreCredit',
            'customersWithStoreDebt',
            'customersWithRemaining',
            'customersWithSubscriptionRemaining',
            'customersWithPTRemaining',
            'customersWithTrainingRemaining',
            'totalCustomers',
            'search_query'
        ));
    }

    /**
     * Export Customer Balances Report to Excel
     */
    public function exportExcel()
    {
        $search = request('search');
        $balanceType = request('balance_type');

        // Get all remaining amounts data
        $remainingData = $this->getRemainingAmountsData();
        $subscriptionRemaining = $remainingData['subscription'];
        $ptRemaining = $remainingData['pt'];
        $trainingRemaining = $remainingData['training'];
        $allMemberIdsWithRemaining = $remainingData['allMemberIds'];

        // Build query
        $query = GymMember::branch()
            ->where(function($q) use ($allMemberIdsWithRemaining) {
                $q->where('store_balance', '!=', 0)
                  ->orWhereIn('id', $allMemberIdsWithRemaining);
            });

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        if ($balanceType === 'store_credit') {
            $query->where('store_balance', '>', 0);
        } elseif ($balanceType === 'store_debt') {
            $query->where('store_balance', '<', 0);
        } elseif ($balanceType === 'remaining') {
            $query->whereIn('id', $allMemberIdsWithRemaining);
        } elseif ($balanceType === 'remaining_subscription') {
            $query->whereIn('id', array_keys($subscriptionRemaining));
        } elseif ($balanceType === 'remaining_pt') {
            $query->whereIn('id', array_keys($ptRemaining));
        } elseif ($balanceType === 'remaining_training') {
            $query->whereIn('id', array_keys($trainingRemaining));
        }

        $members = $query->orderBy('name', 'asc')->get();

        // Attach remaining amounts to members
        foreach ($members as $member) {
            $member->subscription_remaining = $subscriptionRemaining[$member->id] ?? 0;
            $member->pt_remaining = $ptRemaining[$member->id] ?? 0;
            $member->training_remaining = $trainingRemaining[$member->id] ?? 0;
            $member->remaining_amount = $member->subscription_remaining + $member->pt_remaining + $member->training_remaining;
        }

        // Calculate totals
        $totalStoreCredit = $members->where('store_balance', '>', 0)->sum('store_balance');
        $totalStoreDebt = abs($members->where('store_balance', '<', 0)->sum('store_balance'));
        $totalSubscriptionRemaining = $members->sum('subscription_remaining');
        $totalPTRemaining = $members->sum('pt_remaining');
        $totalTrainingRemaining = $members->sum('training_remaining');
        $totalRemainingAmount = $totalSubscriptionRemaining + $totalPTRemaining + $totalTrainingRemaining;

        $data = [
            'members' => $members,
            'totalStoreCredit' => $totalStoreCredit,
            'totalStoreDebt' => $totalStoreDebt,
            'totalRemainingAmount' => $totalRemainingAmount,
            'totalSubscriptionRemaining' => $totalSubscriptionRemaining,
            'totalPTRemaining' => $totalPTRemaining,
            'totalTrainingRemaining' => $totalTrainingRemaining,
        ];

        $this->fileName = 'customer-balances-' . Carbon::now()->toDateTimeString();

        $notes = trans('sw.export_excel_customer_balances');
        $this->userLog($notes, TypeConstants::ExportCustomerBalancesExcel);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new CustomerBalancesExport(['data' => $data, 'lang' => $this->lang, 'settings' => $this->mainSettings]),
            $this->fileName . '.xlsx'
        );
    }

    /**
     * Export Customer Balances Report to PDF
     */
    public function exportPDF()
    {
        $search = request('search');
        $balanceType = request('balance_type');

        // Get all remaining amounts data
        $remainingData = $this->getRemainingAmountsData();
        $subscriptionRemaining = $remainingData['subscription'];
        $ptRemaining = $remainingData['pt'];
        $trainingRemaining = $remainingData['training'];
        $allMemberIdsWithRemaining = $remainingData['allMemberIds'];

        // Build query
        $query = GymMember::branch()
            ->where(function($q) use ($allMemberIdsWithRemaining) {
                $q->where('store_balance', '!=', 0)
                  ->orWhereIn('id', $allMemberIdsWithRemaining);
            });

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        if ($balanceType === 'store_credit') {
            $query->where('store_balance', '>', 0);
        } elseif ($balanceType === 'store_debt') {
            $query->where('store_balance', '<', 0);
        } elseif ($balanceType === 'remaining') {
            $query->whereIn('id', $allMemberIdsWithRemaining);
        } elseif ($balanceType === 'remaining_subscription') {
            $query->whereIn('id', array_keys($subscriptionRemaining));
        } elseif ($balanceType === 'remaining_pt') {
            $query->whereIn('id', array_keys($ptRemaining));
        } elseif ($balanceType === 'remaining_training') {
            $query->whereIn('id', array_keys($trainingRemaining));
        }

        $members = $query->orderBy('name', 'asc')->get();

        // Attach remaining amounts to members
        foreach ($members as $member) {
            $member->subscription_remaining = $subscriptionRemaining[$member->id] ?? 0;
            $member->pt_remaining = $ptRemaining[$member->id] ?? 0;
            $member->training_remaining = $trainingRemaining[$member->id] ?? 0;
            $member->remaining_amount = $member->subscription_remaining + $member->pt_remaining + $member->training_remaining;
        }

        // Calculate totals
        $totalStoreCredit = $members->where('store_balance', '>', 0)->sum('store_balance');
        $totalStoreDebt = abs($members->where('store_balance', '<', 0)->sum('store_balance'));
        $totalSubscriptionRemaining = $members->sum('subscription_remaining');
        $totalPTRemaining = $members->sum('pt_remaining');
        $totalTrainingRemaining = $members->sum('training_remaining');
        $totalRemainingAmount = $totalSubscriptionRemaining + $totalPTRemaining + $totalTrainingRemaining;

        $data = [
            'members' => $members,
            'totalStoreCredit' => $totalStoreCredit,
            'totalStoreDebt' => $totalStoreDebt,
            'totalRemainingAmount' => $totalRemainingAmount,
            'totalSubscriptionRemaining' => $totalSubscriptionRemaining,
            'totalPTRemaining' => $totalPTRemaining,
            'totalTrainingRemaining' => $totalTrainingRemaining,
        ];

        $this->fileName = 'customer-balances-' . Carbon::now()->toDateTimeString();
        $title = trans('sw.customer_balances_report');

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

                $html = view('software::Front.customer_balances_report_pdf', [
                    'data' => $data,
                    'title' => $title,
                    'lang' => $this->lang
                ])->render();

                $mpdf->WriteHTML($html);

                $notes = trans('sw.export_pdf_customer_balances');
                $this->userLog($notes, TypeConstants::ExportCustomerBalancesPDF);

                return response($mpdf->Output($this->fileName . '.pdf', 'D'), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $this->fileName . '.pdf"'
                ]);

            } catch (\Exception $e) {
                Log::error('mPDF failed, falling back to DomPDF: ' . $e->getMessage());
            }
        }

        // DomPDF fallback
        $customPaper = array(0, 0, 595, 842); // A4 size
        $pdf = PDF::loadView('software::Front.customer_balances_report_pdf', [
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

        $notes = trans('sw.export_pdf_customer_balances');
        $this->userLog($notes, TypeConstants::ExportCustomerBalancesPDF);

        return $pdf->download($this->fileName . '.pdf');
    }
}

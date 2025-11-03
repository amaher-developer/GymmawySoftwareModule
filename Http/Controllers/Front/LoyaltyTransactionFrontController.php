<?php

namespace Modules\Software\Http\Controllers\Front;

use Illuminate\Container\Container as Application;
use Modules\Software\Repositories\LoyaltyTransactionRepository;
use Modules\Software\Repositories\GymMemberRepository;
use Modules\Software\Models\LoyaltyTransaction;
use Modules\Software\Models\GymMember;
use Modules\Software\Classes\LoyaltyService;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * LoyaltyTransactionFrontController
 * 
 * Front controller for managing and viewing loyalty transactions
 */
class LoyaltyTransactionFrontController extends GymGenericFrontController
{
    public $loyaltyTransactionRepository;
    public $gymMemberRepository;
    public $loyaltyService;

    public function __construct()
    {
        parent::__construct();
        $this->loyaltyTransactionRepository = new LoyaltyTransactionRepository(new Application);
        $this->gymMemberRepository = new GymMemberRepository(new Application);
        $this->loyaltyService = new LoyaltyService();
    }

    /**
     * Display a listing of loyalty transactions
     */
    public function index()
    {
        $title = trans('sw.loyalty_transactions_list');
        
        $transactions = $this->loyaltyTransactionRepository
            ->with(['member', 'rule', 'campaign', 'creator'])
            ->orderBy('id', 'DESC');

        // Apply filters
        $transactions->when(request('search'), function ($query, $search) {
            $query->whereHas('member', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        });

        $transactions->when(request('member_id'), function ($query, $memberId) {
            $query->where('member_id', $memberId);
        });

        $transactions->when(request('type'), function ($query, $type) {
            $query->where('type', $type);
        });

        $transactions->when(request('date_from'), function ($query, $dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        });

        $transactions->when(request('date_to'), function ($query, $dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        });

        $search_query = request()->query();

        // Get statistics
        $stats = [
            'total_earned' => LoyaltyTransaction::where('type', 'earn')->sum('points'),
            'total_redeemed' => abs(LoyaltyTransaction::where('type', 'redeem')->sum('points')),
            'total_manual' => LoyaltyTransaction::where('type', 'manual')->sum('points'),
        ];

        if ($this->limit) {
            $transactions = $transactions->paginate($this->limit);
            $total = $transactions->total();
        } else {
            $transactions = $transactions->get();
            $total = $transactions->count();
        }

        return view('software::Front.loyalty_transaction_list', compact('transactions', 'title', 'total', 'search_query', 'stats'));
    }

    /**
     * Show manual adjustment form
     */
    public function createManual()
    {
        $title = trans('sw.manual_points_adjustment');
        $members = $this->gymMemberRepository->orderBy('name', 'ASC')->get();
        
        return view('software::Front.loyalty_transaction_manual_form', compact('title', 'members'));
    }

    /**
     * Store manual adjustment
     */
    public function storeManual(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:sw_gym_members,id',
            'points' => 'required|integer|not_in:0',
            'reason' => 'required|string|max:500',
        ]);

        try {
            $member = GymMember::findOrFail($validated['member_id']);
            
            $transaction = $this->loyaltyService->addManual(
                $member,
                $validated['points'],
                $validated['reason'],
                $this->user_sw->id ?? null
            );

            return redirect()
                ->route('sw.loyalty_transactions.index')
                ->with('success', trans('sw.manual_points_adjusted_successfully'));

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * View member's loyalty history
     */
    public function memberHistory($memberId)
    {
        $member = GymMember::findOrFail($memberId);
        $title = trans('sw.member_loyalty_history') . ': ' . $member->name;
        
        $transactions = $this->loyaltyTransactionRepository
            ->getMemberHistory($memberId, 100);

        $breakdown = $this->loyaltyService->getActivePointsBreakdown($member);

        return view('software::Front.loyalty_member_history', compact('member', 'transactions', 'breakdown', 'title'));
    }

    /**
     * Export transactions report
     */
    public function export(Request $request)
    {
        $transactions = LoyaltyTransaction::with(['member', 'rule', 'campaign'])
            ->when($request->member_id, function ($query, $memberId) {
                $query->where('member_id', $memberId);
            })
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $transactions->map(function ($t) {
            return [
                'ID' => $t->id,
                'Member' => $t->member->name ?? '',
                'Points' => $t->points,
                'Type' => $t->type,
                'Source' => $t->source_type ?? '',
                'Amount Spent' => $t->amount_spent ?? '',
                'Reason' => $t->reason ?? '',
                'Created At' => $t->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        return response()->json([
            'data' => $data,
            'filename' => 'loyalty_transactions_' . Carbon::now()->format('Y-m-d'),
        ]);
    }
}


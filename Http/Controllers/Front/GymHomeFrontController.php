<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Software\Http\Controllers\Front\GymGenericFrontController;
use Modules\Generic\Models\Setting;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Events\UserEvent;
use Modules\Software\Models\GymActivity;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymMemberAttendee;
use Modules\Software\Models\GymMemberSubscription;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Models\GymNonMember;
use Modules\Software\Models\GymNonMemberTime;
use Modules\Software\Models\GymPotentialMember;
use Modules\Software\Models\GymPTClass;
use Modules\Software\Models\GymPTClassTrainer;
use Modules\Software\Models\GymPTMemberAttendee;
use Modules\Software\Models\GymSubscription;
use Modules\Software\Models\GymUser;
use Modules\Software\Models\GymUserLog;
use Modules\Software\Models\GymStoreOrder;
use Modules\Software\Models\GymStoreProduct;
use Modules\Software\Models\GymPTMember;
use Modules\Software\Models\GymPTTrainer;
use Modules\Software\Models\GymPTSubscription;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Modules\Software\Services\PT\PTSessionService;

class GymHomeFrontController extends GymGenericFrontController
{
    protected PTSessionService $sessionService;

    function __construct()
    {
        parent::__construct();
        $this->sessionService = app(PTSessionService::class);
    }
    public $money_box_daily_sum = 0;
    public function home(){
        $activities = GymActivity::branch()->get();
        $subscriptions = GymSubscription::branch()->get();
        $money_box = GymMoneyBox::branch()->orderBy('created_at', 'desc')->first();
        $money_box_now = @GymMoneyBoxFrontController::amountAfter($money_box['amount'], $money_box['amount_before'], $money_box['operation']);

        $last_created_member = GymMember::branch()->select('id', 'name')->orderBy('created_at', 'desc')->first();
        $last_created_non_member = GymNonMember::branch()->select('id', 'name')->orderBy('created_at', 'desc')->first();
        $last_enter_member = GymMemberAttendee::branch()->with('member:id,name')->orderBy('created_at', 'desc')->first();


        $last_expired_members = GymMemberSubscription::branch()->with('member')->whereHas('member', function($q){
            $q->withoutTrashed();
        })->where('expire_date', '<', Carbon::now())->orderBy('expire_date', 'DESC')->limit(9)->get();
        $last_expiring_members = GymMemberSubscription::branch()->with('member')->whereHas('member', function($q){
            $q->withoutTrashed();
        })->where('expire_date', '>=', Carbon::now())->orderBy('expire_date', 'ASC')->limit(9)->get();
        $last_new_members = GymMemberSubscription::branch()->with(['member'])->whereHas('member', function($q){
            $q->withoutTrashed();
        })->where('expire_date', '>=', Carbon::now())->orderBy('joining_date', 'desc')->limit(9)->get();

        $birthday_members = GymMember::branch()->whereMonth('dob', Carbon::now()->format('m'))
            ->whereDay('dob', Carbon::now()->format('d'))
            ->orderBy('dob', 'asc')->limit(9)->get();

        $last_attendance_members = GymMemberAttendee::branch()->with('member.member_subscription_info')
            ->orderBy('created_at', 'desc')->limit(20)->get();

        $title = trans('sw.dashboard');
        $lang = $this->lang ?? 'ar';
        return view('software::Front.dashboard', compact(['title', 'subscriptions', 'activities', 'money_box_now', 'last_created_member', 'last_created_non_member', 'last_enter_member', 'last_expired_members', 'last_new_members', 'birthday_members', 'last_expiring_members', 'last_attendance_members', 'lang']));
    }
    public function home_mini(){

        $title = trans('sw.member_login');
//        $last_created_member = GymMember::select('id', 'name')->orderBy('id', 'desc')->first();
//        $last_created_non_member = GymNonMember::select('id', 'name')->orderBy('id', 'desc')->first();
        $last_enter_member = GymMemberAttendee::branch()->with('member:id,name')->orderBy('id', 'desc')->first();
        return view('software::Front.dashboard_mini', compact(['title', 'last_enter_member']));
    }
    public function home_pt_mini(){

        $title = trans('sw.pt_member_login');
        $last_enter_member = GymMemberAttendee::branch()
            ->with(['member:id,name,image'])
            ->where('type', TypeConstants::ATTENDANCE_TYPE_PT)
            ->orderBy('created_at', 'desc')
            ->first();

        $today = Carbon::today();
        $now = Carbon::now();
        $todayStart = $today->copy()->startOfDay();
        $todayEnd = $today->copy()->endOfDay();

        $assignments = GymPTClassTrainer::branch()
            ->with(['class', 'trainer'])
            ->where('is_active', true)
            ->get();

        $classesWithSchedule = GymPTClass::branch()
            ->with('pt_subscription')
            ->whereNotNull('schedule')
            ->where(function ($query) {
                $query->whereNull('is_active')
                    ->orWhere('is_active', true);
            })
            ->get();

        foreach ($classesWithSchedule as $classModel) {
            $already = $assignments->firstWhere('class_id', $classModel->id);
            if ($already) {
                continue;
            }
            $assignments->push($this->makeVirtualAssignment($classModel));
        }

        $timelineToday = $this->sessionService->resolveVirtualTimeline($assignments, $todayStart, $todayEnd);

        $attendanceToday = GymPTMemberAttendee::branch()
            ->whereBetween('session_date', [$todayStart, $todayEnd])
            ->get();

        $todaySessionsTotal = $timelineToday->count();
        $todaySessionsCompleted = $attendanceToday->count();
        $todaySessionsPending = max($todaySessionsTotal - $todaySessionsCompleted, 0);

        $uniqueMembersToday = $attendanceToday
            ->pluck('pt_member_id')
            ->filter()
            ->unique()
            ->count();

        $uniqueTrainersToday = $timelineToday
            ->pluck('trainer.id')
            ->filter()
            ->unique()
            ->count();

        $timelineNextSeven = $this->sessionService->resolveVirtualTimeline(
            $assignments,
            $todayStart,
            $todayStart->copy()->addDays(7)->endOfDay()
        );

        $sessionsNextSevenDays = $timelineNextSeven->count();

        $nextSessionEntry = $timelineNextSeven->first(function (object $entry) use ($now) {
            return $entry->slot->gte($now);
        });

        $nextSession = $nextSessionEntry
            ? (object) [
                'class' => $nextSessionEntry->class,
                'session_date' => $nextSessionEntry->slot->copy(),
                'trainer_name' => optional($nextSessionEntry->trainer)->name,
            ]
            : null;

        $sessionTotals = GymPTMember::branch()
            ->selectRaw('COALESCE(SUM(total_sessions), 0) as total_sessions_sum, COALESCE(SUM(remaining_sessions), 0) as remaining_sessions_sum')
            ->first();

        $totalSessionsSum = (int) data_get($sessionTotals, 'total_sessions_sum', 0);
        $remainingSessionsSum = (int) data_get($sessionTotals, 'remaining_sessions_sum', 0);
        $usedSessionsSum = max($totalSessionsSum - $remainingSessionsSum, 0);

        $stats = [
            'sessions_today' => $todaySessionsTotal,
            'sessions_completed_today' => $todaySessionsCompleted,
            'sessions_pending_today' => $todaySessionsPending,
            'unique_members_today' => $uniqueMembersToday,
            'unique_trainers_today' => $uniqueTrainersToday,
            'sessions_next_seven_days' => $sessionsNextSevenDays,
            'remaining_sessions_total' => $remainingSessionsSum,
            'used_sessions_total' => $usedSessionsSum,
            'next_session' => $nextSession,
        ];

        $calendarStart = $todayStart->copy()->subDay();
        $calendarEnd = $todayStart->copy()->addMonth()->endOfDay();

        $timelineCalendar = $this->sessionService->resolveVirtualTimeline($assignments, $calendarStart, $calendarEnd);

        $attendanceLookup = GymPTMemberAttendee::branch()
            ->with('pt_member')
            ->whereBetween('session_date', [$calendarStart, $calendarEnd])
            ->get()
            ->groupBy(function (GymPTMemberAttendee $attendee) {
                $member = $attendee->pt_member;
                if (!$member || !$attendee->session_date) {
                    return null;
                }

                $classId = $member->class_id ?? $member->pt_class_id ?? 0;
                $trainerId = $member->class_trainer_id ?? 0;

                return $this->buildTimelineKey($classId, $trainerId, $attendee->session_date);
            })
            ->filter(fn ($value, $key) => !is_null($key));

        $reservations = $timelineCalendar
            ->map(function (object $entry) use ($attendanceLookup) {
                $classModel = $entry->class;
                $assignment = $entry->class_trainer;
                $trainer = $entry->trainer;
                $slot = $entry->slot;

                $key = $this->buildTimelineKey($classModel->id, $assignment?->id ?? 0, $slot);
                $attendeeCount = isset($attendanceLookup[$key]) ? $attendanceLookup[$key]->count() : 0;
                $duration = $classModel->session_duration ?? 60;

                $trainerName = optional($trainer)->name ?? trans('sw.unassigned_trainer');

                return [
                    'title' => trim(($classModel->name ?? trans('sw.pt_class')) . ' - ' . $trainerName),
                    'start' => $slot->format('Y-m-d H:i:s'),
                    'end' => $slot->copy()->addMinutes($duration)->format('Y-m-d H:i:s'),
                    'background_color' => $classModel->class_color ?? '',
                    'pt_class_id' => $classModel->id,
                    'pt_trainer_id' => $assignment?->trainer_id,
                    'session_token' => $this->sessionService->encodeVirtualSessionId($classModel, $assignment, $slot),
                    'status' => $attendeeCount > 0 ? 'completed' : 'pending',
                    'attendee_count' => $attendeeCount,
                ];
            })
            ->values()
            ->toArray();

        return view('software::Front.dashboard_pt_mini', compact('title', 'last_enter_member', 'reservations', 'stats'));
    }

    protected function makeVirtualAssignment(GymPTClass $class): GymPTClassTrainer
    {
        $assignment = new GymPTClassTrainer([
            'id' => null,
            'class_id' => $class->id,
            'trainer_id' => null,
            'is_active' => true,
        ]);

        $assignment->setRelation('class', $class);

        return $assignment;
    }

    protected function buildTimelineKey(int $classId, int $trainerId, Carbon $slot): string
    {
        return "{$classId}|{$trainerId}|" . $slot->format('Y-m-d H:i:s');
    }

    public function home_fingerprint_mini(){

        $title = trans('sw.fingerprint');
//        $last_created_member = GymMember::select('id', 'name')->orderBy('id', 'desc')->first();
//        $last_created_non_member = GymNonMember::select('id', 'name')->orderBy('id', 'desc')->first();
        $last_enter_member = GymMemberAttendee::branch()->with('member:id,name')->orderBy('id', 'desc')->first();
        return view('software::Front.dashboard_fingerprint_mini', compact(['title', 'last_enter_member']));
    }
    public function statistics(Request $request){
        $title = trans('sw.statistics');
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');

        // Members Statistics
        $members_count = GymMember::branch()->count();
        $non_members_count = GymNonMember::branch()->count();
        $potential_members_count = GymPotentialMember::branch()->count();
        $admin_count = GymUser::branch()->count();
        $members_active_count = GymMember::branch()->with('member_subscription_info')->whereHas('member_subscription_info', function ($q){$q->where('status', TypeConstants::Active);})->count();
        $members_deactive_count = $members_count - $members_active_count;

        // New Members (Today or Date Range)
        if ($from_date && $to_date) {
            $new_members_count = GymMember::branch()->whereBetween('created_at', [$from_date . ' 00:00:00', $to_date . ' 23:59:59'])->count();
            $new_non_members_count = GymNonMember::branch()->whereBetween('created_at', [$from_date . ' 00:00:00', $to_date . ' 23:59:59'])->count();
        } else {
            $new_members_count = GymMember::branch()->whereDate('created_at', Carbon::today())->count();
            $new_non_members_count = GymNonMember::branch()->whereDate('created_at', Carbon::today())->count();
        }

        // Attendance Statistics
        if ($from_date && $to_date) {
            $attendance_count = \Modules\Software\Models\GymMemberAttendee::branch()->whereBetween('created_at', [$from_date . ' 00:00:00', $to_date . ' 23:59:59'])->count();
        } else {
            $attendance_count = \Modules\Software\Models\GymMemberAttendee::branch()->whereDate('created_at', Carbon::today())->count();
        }

        // Expiring Soon (Next 7 days)
        $expiring_soon_count = GymMember::branch()->with('member_subscription_info')->whereHas('member_subscription_info', function ($q){
            $q->where('status', TypeConstants::Active)
              ->whereBetween('expire_date', [Carbon::now(), Carbon::now()->addDays(7)]);
        })->count();

//        $members_count_monthly = GymMember::whereDate('created_at', '>=', Carbon::now()->startOfMonth()->subMonth()->toDateTimeString())->count();
//        $non_members_count_monthly = GymNonMember::whereDate('created_at', '>=', Carbon::now()->startOfMonth()->subMonth()->toDateTimeString())->count();
//        $activities_count = GymActivity::count();
//        $subscription_count = GymSubscription::count();

        $money_box = GymMoneyBox::branch()->select(['id', 'amount', 'operation', 'amount_before', 'created_at'])->with(['user' => function($q){$q->withTrashed();}, 'member_subscription' => function($q){$q->withTrashed();}])->orderBy('id', 'DESC');
        
        if ($from_date && $to_date) {
            $money_box = $money_box->whereBetween('created_at', [$from_date . ' 00:00:00', $to_date . ' 23:59:59']);
        } else {
            $money_box = $money_box->whereDate('created_at', Carbon::now()->toDateString());
        }
        
        $money_box = $money_box->get();

        $revenues = ($money_box->where('operation', 0)->sum('amount'));
        $expenses = ($money_box->where('operation', 1)->sum('amount'));

        $earnings = ($revenues - $expenses);

        $money_box_daily = @$money_box[0] ? $money_box[0] : GymMoneyBox::orderBy('id', 'desc')->first();
//        $money_box = GymMoneyBox::branch()->orderBy('id', 'desc')->first();

        $money_box_now = @GymMoneyBoxFrontController::amountAfter($money_box_daily['amount'], $money_box_daily['amount_before'], $money_box_daily['operation']);
//        $money_box_by_payment_types = GymMoneyBox::branch()
//            ->select('payment_type', DB::raw('sum(amount) as total'))
//            ->groupBy('payment_type')
//            ->get();

//        $money_box_daily = $money_box_all->filter(function ($item) {
//            if(Carbon::parse($item->created_at)->toDateString() == Carbon::today()->toDateString())
//                return $this->money_box_daily_sum += $item->amount;
//        })->values();
//        $money_box_daily_now = $this->money_box_daily_sum;

        // Subscriptions with member count (always show active)
        $subscriptions = GymSubscription::branch()->select('id', 'name_ar', 'name_en', 'price', 'workouts', 'period', 'created_at')
            ->withCount(['member_subscriptions' => function ($q){
                 $q->where('status', TypeConstants::Active);
            }])
            ->orderBy('member_subscriptions_count', 'desc')->get();

        // Member subscriptions for chart (filtered by date)
        $membersQuery = GymMemberSubscription::branch();
        if ($from_date && $to_date) {
            $membersQuery->whereBetween('created_at', [$from_date . ' 00:00:00', $to_date . ' 23:59:59']);
        }
        $members = $membersQuery->get()->groupBy(function($date) {
            return Carbon::parse($date->created_at)->format('m');
        });

        $new_members = implode(',', $this->getMemberCountByType($members, 0));
        $expired_members = implode(',', $this->getMemberCountByType($members, 2));
        
        // Logs (filtered by date)
        $logsQuery = GymUserLog::branch()->orderBy('id', 'desc');
        if ($from_date && $to_date) {
            $logsQuery->whereBetween('created_at', [$from_date . ' 00:00:00', $to_date . ' 23:59:59']);
        }
        $logs = $logsQuery->limit(20)->get();


        return view('software::Front.statistics', compact([
            'revenues', 'earnings', 'expenses'
            , 'title', 'logs', 'subscriptions'
            , 'money_box_now'
            , 'members_count', 'non_members_count', 'admin_count', 'potential_members_count', 'members_active_count', 'members_deactive_count'
            , 'new_members', 'expired_members'
            , 'new_members_count', 'new_non_members_count', 'attendance_count', 'expiring_soon_count'
            , 'from_date', 'to_date'
        ]));
    }

    private function getMemberCountByType($records = [], $type = 0){
        $recordCount = [];
        $recordArr = [];
        foreach ($records as $key => $value) {
            $value = $value->filter(function ($item) use ($type){
                if($item->status == $type)
                    return $item;
            });

            $recordCount[(int)$key] = count($value);
        }

        for($i = 1; $i <= 12; $i++){
            if(!empty($recordCount[$i])){
                $recordArr[$i] = $recordCount[$i];
            }else{
                $recordArr[$i] = 0;
            }
        }
        return $recordArr;
    }

    public function branchSwitch($id){
        if(@$this->user_sw->is_super_user){
            $this->user_sw->branch_setting_id = $id;
            $this->user_sw->save();

            $this->mainSettings = Setting::where('id',$id )->first();
            Cache::store('file')->put('mainSettings',$this->mainSettings, 600 );
        }
        return redirect()->route('home');
    }

    public function memberSubscriptionStatistics(Request $request){
        $title = trans('sw.member_subscription_statistics');
        
        // Get date filters
        $from_date = $request->get('from_date');
        $to_date = $request->get('to_date');
        
        // Build base query with date filters
        $query = GymMemberSubscription::branch();
        if ($from_date) {
            $query->where('created_at', '>=', Carbon::parse($from_date)->startOfDay());
        }
        if ($to_date) {
            $query->where('created_at', '<=', Carbon::parse($to_date)->endOfDay());
        }
        
        // Basic subscription counts
        $total_subscriptions = (clone $query)->count();
        $active_subscriptions = (clone $query)->where('status', TypeConstants::Active)->count();
        $expired_subscriptions = (clone $query)->where('status', TypeConstants::Expired)->count();
        $frozen_subscriptions = (clone $query)->where('status', TypeConstants::Freeze)->count();
        
        // Revenue calculations
        $subscription_revenue = (clone $query)
            ->where('status', TypeConstants::Active)
            ->sum('amount_paid');
        
        $monthly_revenue = GymMemberSubscription::branch()
            ->where('status', TypeConstants::Active)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount_paid');
        
        // New subscriptions this month
        $new_this_month = GymMemberSubscription::branch()
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        
        // Expiring soon (within 7 days)
        $expiring_soon = GymMemberSubscription::branch()
            ->where('status', TypeConstants::Active)
            ->whereBetween('expire_date', [Carbon::now(), Carbon::now()->addDays(7)])
            ->count();
        
        // Popular subscriptions with revenue
        $popular_subscriptions = GymSubscription::branch()
            ->withCount(['member_subscriptions' => function ($q) use ($from_date, $to_date){
                $q->where('status', TypeConstants::Active);
                if ($from_date) {
                    $q->where('created_at', '>=', Carbon::parse($from_date)->startOfDay());
                }
                if ($to_date) {
                    $q->where('created_at', '<=', Carbon::parse($to_date)->endOfDay());
                }
            }])
            ->with(['member_subscriptions' => function($q) use ($from_date, $to_date) {
                $q->where('status', TypeConstants::Active);
                if ($from_date) {
                    $q->where('created_at', '>=', Carbon::parse($from_date)->startOfDay());
                }
                if ($to_date) {
                    $q->where('created_at', '<=', Carbon::parse($to_date)->endOfDay());
                }
            }])
            ->get()
            ->map(function($subscription) {
                $subscription->revenue = $subscription->member_subscriptions->sum('amount_paid');
                return $subscription;
            })
            ->sortByDesc('revenue')
            ->take(10);
        
        // Chart data for monthly trends
        $new_subscriptions_chart = $this->getSubscriptionChartData(0, $from_date, $to_date); // New subscriptions
        $expired_subscriptions_chart = $this->getSubscriptionChartData(2, $from_date, $to_date); // Expired subscriptions
        $frozen_subscriptions_chart = $this->getSubscriptionChartData(3, $from_date, $to_date); // Frozen subscriptions
        
        return view('software::Front.member_subscription_statistics', compact([
            'title', 'total_subscriptions', 'active_subscriptions', 'expired_subscriptions', 
            'frozen_subscriptions', 'subscription_revenue', 'monthly_revenue', 
            'new_this_month', 'expiring_soon', 'popular_subscriptions',
            'new_subscriptions_chart', 'expired_subscriptions_chart', 'frozen_subscriptions_chart'
        ]));
    }

    public function storeStatistics(Request $request){
        $title = trans('sw.store_statistics');
        
        // Get date filters
        $from_date = $request->get('from_date');
        $to_date = $request->get('to_date');
        
        // Build base query with date filters
        $query = GymStoreOrder::branch();
        if ($from_date) {
            $query->where('created_at', '>=', Carbon::parse($from_date)->startOfDay());
        }
        if ($to_date) {
            $query->where('created_at', '<=', Carbon::parse($to_date)->endOfDay());
        }
        
        // Order counts (based on payment status)
        $total_orders = (clone $query)->count();
        $completed_orders = (clone $query)->where('amount_remaining', 0)->count();
        $pending_orders = (clone $query)->where('amount_remaining', '>', 0)->count();
        $cancelled_orders = 0; // No cancelled status in current structure
        
        // Revenue calculations
        $store_revenue = (clone $query)->sum('amount_paid');
        
        $monthly_revenue = GymStoreOrder::branch()
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount_paid');
        
        // Product statistics
        $total_products = GymStoreProduct::branch()->count();
        $low_stock_products = GymStoreProduct::branch()->where('quantity', '<=', 10)->where('quantity', '>', 0)->count();
        
        // Top products with sales data
        $top_products = GymStoreProduct::branch()
            ->with(['order_product'])
            ->get()
            ->map(function($product) {
                $product->sales_count = $product->order_product->count();
                $product->revenue = $product->order_product->sum(function($orderProduct) {
                    return $orderProduct->quantity * $orderProduct->price;
                });
                $product->category_name = '-'; // No category in current structure
                return $product;
            })
            ->sortByDesc('revenue')
            ->take(10);
        
        // Recent orders with payment status
        $recent_orders_query = GymStoreOrder::branch()
            ->with(['member', 'pay_type']);
        
        if ($from_date) {
            $recent_orders_query->where('created_at', '>=', Carbon::parse($from_date)->startOfDay());
        }
        if ($to_date) {
            $recent_orders_query->where('created_at', '<=', Carbon::parse($to_date)->endOfDay());
        }
        
        $recent_orders = $recent_orders_query
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function($order) {
                // Add status based on payment
                $order->status = $order->amount_remaining == 0 ? 'completed' : 'pending';
                $order->total = $order->amount_paid + $order->amount_remaining;
                
                // Ensure member exists
                if (!$order->member) {
                    $order->member = (object) ['name' => 'N/A', 'code' => 'N/A', 'image' => asset('uploads/settings/default.jpg')];
                }
                
                return $order;
            });
        
        // Chart data for order trends (monthly orders)
        $completed_orders_chart = $this->getStoreChartData('completed', $from_date, $to_date);
        $pending_orders_chart = $this->getStoreChartData('pending', $from_date, $to_date);
        $cancelled_orders_chart = implode(',', array_fill(0, 12, 0)); // No cancelled orders
        
        return view('software::Front.store_statistics', compact([
            'title', 'total_orders', 'completed_orders', 'pending_orders', 'cancelled_orders',
            'store_revenue', 'monthly_revenue', 'total_products', 'low_stock_products',
            'top_products', 'recent_orders', 'completed_orders_chart', 
            'pending_orders_chart', 'cancelled_orders_chart'
        ]));
    }

    private function getSubscriptionChartData($status, $from_date = null, $to_date = null){
        $query = GymMemberSubscription::branch();
        
        if ($from_date) {
            $query->where('created_at', '>=', Carbon::parse($from_date)->startOfDay());
        }
        if ($to_date) {
            $query->where('created_at', '<=', Carbon::parse($to_date)->endOfDay());
        }
        
        $subscriptions = $query->get()
            ->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('m');
            });

        $recordCount = [];
        $recordArr = [];
        
        foreach ($subscriptions as $key => $value) {
            $value = $value->filter(function ($item) use ($status){
                if($item->status == $status)
                    return $item;
            });
            $recordCount[(int)$key] = count($value);
        }

        for($i = 1; $i <= 12; $i++){
            if(!empty($recordCount[$i])){
                $recordArr[$i] = $recordCount[$i];
            }else{
                $recordArr[$i] = 0;
            }
        }
        return implode(',', $recordArr);
    }

    private function getStoreChartData($status, $from_date = null, $to_date = null){
        $query = GymStoreOrder::branch();
        
        if ($from_date) {
            $query->where('created_at', '>=', Carbon::parse($from_date)->startOfDay());
        }
        if ($to_date) {
            $query->where('created_at', '<=', Carbon::parse($to_date)->endOfDay());
        }
        
        $orders = $query->get();
        
        // Filter by payment status
        if($status == 'completed') {
            $orders = $orders->where('amount_remaining', 0);
        } elseif($status == 'pending') {
            $orders = $orders->where('amount_remaining', '>', 0);
        }
        
        $orders = $orders->groupBy(function($date) {
            return Carbon::parse($date->created_at)->format('m');
        });

        $recordCount = [];
        $recordArr = [];
        
        foreach ($orders as $key => $value) {
            $recordCount[(int)$key] = count($value);
        }

        for($i = 1; $i <= 12; $i++){
            if(!empty($recordCount[$i])){
                $recordArr[$i] = $recordCount[$i];
            }else{
                $recordArr[$i] = 0;
            }
        }
        return implode(',', $recordArr);
    }

    public function subscriptionStatisticsRefresh(){
        // Refresh logic for subscription statistics
        return response()->json(['status' => 'success']);
    }

    public function storeStatisticsRefresh(){
        // Refresh logic for store statistics
        return response()->json(['status' => 'success']);
    }

    public function ptSubscriptionStatistics(Request $request){
        $title = trans('sw.pt_subscription_statistics');
        
        // Get date filters
        $from_date = $request->get('from_date');
        $to_date = $request->get('to_date');
        
        // Build base query with date filters
        $query = GymPTMember::branch();
        if ($from_date) {
            $query->where('created_at', '>=', Carbon::parse($from_date)->startOfDay());
        }
        if ($to_date) {
            $query->where('created_at', '<=', Carbon::parse($to_date)->endOfDay());
        }
        
        // Get all PT members and calculate status
        $all_pt_members = $query->get();
        
        // Basic PT subscription counts (status is calculated dynamically)
        $total_pt_subscriptions = $all_pt_members->count();
        $active_pt_subscriptions = $all_pt_members->filter(function($member) {
            return $member->status == TypeConstants::Active;
        })->count();
        $expired_pt_subscriptions = $all_pt_members->filter(function($member) {
            return $member->status == TypeConstants::Expired;
        })->count();
        $frozen_pt_subscriptions = 0; // PT members don't have freeze status
        
        // Revenue calculations
        $pt_revenue = $all_pt_members->filter(function($member) {
            return $member->status == TypeConstants::Active;
        })->sum('amount_paid');
        
        $monthly_pt_revenue = GymPTMember::branch()
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->get()
            ->filter(function($member) {
                return $member->status == TypeConstants::Active;
            })
            ->sum('amount_paid');
        
        // Trainer and class statistics
        $total_trainers = GymPTTrainer::branch()->count();
        $total_pt_classes = GymPTClass::branch()->count();
        
        // Popular PT subscriptions with revenue
        $popular_pt_subscriptions = GymPTSubscription::branch()
            ->with(['pt_members'])
            ->get()
            ->map(function($subscription) {
                $active_members = $subscription->pt_members->filter(function($member) {
                    return $member->status == TypeConstants::Active;
                });
                $subscription->pt_members_count = $active_members->count();
                $subscription->revenue = $active_members->sum('amount_paid');
                return $subscription;
            })
            ->sortByDesc('revenue')
            ->take(10);
        
        // Top trainers with revenue
        $top_trainers = GymPTTrainer::branch()
            ->with(['pt_members'])
            ->get()
            ->map(function($trainer) {
                $active_members = $trainer->pt_members->filter(function($member) {
                    return $member->status == TypeConstants::Active;
                });
                $trainer->members_count = $active_members->count();
                $trainer->revenue = $active_members->sum('amount_paid');
                $trainer->classes_count = $active_members->sum('classes');
                return $trainer;
            })
            ->sortByDesc('revenue')
            ->take(10);
        
        // Chart data for monthly trends
        $new_pt_subscriptions_chart = $this->getPTSubscriptionChartData(0, $from_date, $to_date);
        $expired_pt_subscriptions_chart = $this->getPTSubscriptionChartData(2, $from_date, $to_date);
        $frozen_pt_subscriptions_chart = implode(',', array_fill(0, 12, 0)); // No frozen status for PT
        
        return view('software::Front.pt_subscription_statistics', compact([
            'title', 'total_pt_subscriptions', 'active_pt_subscriptions', 'expired_pt_subscriptions', 
            'frozen_pt_subscriptions', 'pt_revenue', 'monthly_pt_revenue', 
            'total_trainers', 'total_pt_classes', 'popular_pt_subscriptions', 'top_trainers',
            'new_pt_subscriptions_chart', 'expired_pt_subscriptions_chart', 'frozen_pt_subscriptions_chart'
        ]));
    }

    private function getPTSubscriptionChartData($status, $from_date = null, $to_date = null){
        $query = GymPTMember::branch();
        
        if ($from_date) {
            $query->where('created_at', '>=', Carbon::parse($from_date)->startOfDay());
        }
        if ($to_date) {
            $query->where('created_at', '<=', Carbon::parse($to_date)->endOfDay());
        }
        
        $subscriptions = $query->get()
            ->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('m');
            });

        $recordCount = [];
        $recordArr = [];
        
        foreach ($subscriptions as $key => $value) {
            $value = $value->filter(function ($item) use ($status){
                if($item->status == $status)
                    return $item;
            });
            $recordCount[(int)$key] = count($value);
        }

        for($i = 1; $i <= 12; $i++){
            if(!empty($recordCount[$i])){
                $recordArr[$i] = $recordCount[$i];
            }else{
                $recordArr[$i] = 0;
            }
        }
        return implode(',', $recordArr);
    }

    public function ptSubscriptionStatisticsRefresh(){
        // Refresh logic for PT subscription statistics
        return response()->json(['status' => 'success']);
    }

    public function nonMemberStatistics(Request $request){
        $title = trans('sw.non_member_statistics');
        
        // Get date filters
        $from_date = $request->get('from_date');
        $to_date = $request->get('to_date');
        
        // Build base query with date filters for non-members
        $query = GymNonMember::branch();
        if ($from_date) {
            $query->where('created_at', '>=', Carbon::parse($from_date)->startOfDay());
        }
        if ($to_date) {
            $query->where('created_at', '<=', Carbon::parse($to_date)->endOfDay());
        }
        
        // Build base query with date filters for sessions
        $session_query = GymNonMemberTime::branch();
        if ($from_date) {
            $session_query->where('created_at', '>=', Carbon::parse($from_date)->startOfDay());
        }
        if ($to_date) {
            $session_query->where('created_at', '<=', Carbon::parse($to_date)->endOfDay());
        }
        
        // Total non-members
        $total_non_members = (clone $query)->count();
        
        // Active sessions (sessions that haven't expired yet)
        $active_sessions = (clone $session_query)
            ->where('expire_date', '>=', Carbon::now())
            ->count();
        
        // Expired sessions
        $expired_sessions = (clone $session_query)
            ->where('expire_date', '<', Carbon::now())
            ->count();
        
        // Total sessions
        $total_sessions = (clone $session_query)->count();
        
        // Attendance rate
        $attended_sessions = (clone $session_query)
            ->whereNotNull('attended_at')
            ->count();
        $attendance_rate = $total_sessions > 0 ? round(($attended_sessions / $total_sessions) * 100, 2) : 0;
        
        // Revenue calculations
        $total_revenue = (clone $query)->sum('price');
        $monthly_revenue = GymNonMember::branch()
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('price');
        
        // Popular activities
        $popular_activities = GymActivity::branch()
            ->with(['non_member_times'])
            ->get()
            ->map(function($activity) {
                $activity->sessions_count = $activity->non_member_times->count();
                $activity->attended_count = $activity->non_member_times->where('attended_at', '!=', null)->count();
                $activity->attendance_rate = $activity->sessions_count > 0 
                    ? round(($activity->attended_count / $activity->sessions_count) * 100, 2) 
                    : 0;
                return $activity;
            })
            ->sortByDesc('sessions_count')
            ->take(10);
        
        // Recent non-members
        $recent_non_members = GymNonMember::branch()
            ->with(['non_member_times'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function($member) {
                $member->sessions_count = $member->non_member_times->count();
                $member->attended_count = $member->non_member_times->where('attended_at', '!=', null)->count();
                return $member;
            });
        
        // Chart data for monthly trends
        $new_non_members_chart = $this->getNonMemberChartData('members', $from_date, $to_date);
        $sessions_chart = $this->getNonMemberChartData('sessions', $from_date, $to_date);
        $attendance_chart = $this->getNonMemberChartData('attendance', $from_date, $to_date);
        
        return view('software::Front.non_member_statistics', compact([
            'title', 'total_non_members', 'active_sessions', 'expired_sessions', 
            'total_sessions', 'attendance_rate', 'total_revenue', 'monthly_revenue',
            'popular_activities', 'recent_non_members',
            'new_non_members_chart', 'sessions_chart', 'attendance_chart'
        ]));
    }

    private function getNonMemberChartData($type, $from_date = null, $to_date = null){
        if ($type == 'members') {
            $query = GymNonMember::branch();
            if ($from_date) {
                $query->where('created_at', '>=', Carbon::parse($from_date)->startOfDay());
            }
            if ($to_date) {
                $query->where('created_at', '<=', Carbon::parse($to_date)->endOfDay());
            }
            $data = $query->get()
                ->groupBy(function($date) {
                    return Carbon::parse($date->created_at)->format('m');
                });
        } elseif ($type == 'sessions') {
            $query = GymNonMemberTime::branch();
            if ($from_date) {
                $query->where('created_at', '>=', Carbon::parse($from_date)->startOfDay());
            }
            if ($to_date) {
                $query->where('created_at', '<=', Carbon::parse($to_date)->endOfDay());
            }
            $data = $query->get()
                ->groupBy(function($date) {
                    return Carbon::parse($date->created_at)->format('m');
                });
        } else { // attendance
            $query = GymNonMemberTime::branch()
                ->whereNotNull('attended_at');
            if ($from_date) {
                $query->where('created_at', '>=', Carbon::parse($from_date)->startOfDay());
            }
            if ($to_date) {
                $query->where('created_at', '<=', Carbon::parse($to_date)->endOfDay());
            }
            $data = $query->get()
                ->groupBy(function($date) {
                    return Carbon::parse($date->created_at)->format('m');
                });
        }

        $recordCount = [];
        $recordArr = [];
        
        foreach ($data as $key => $value) {
            $recordCount[(int)$key] = count($value);
        }

        for($i = 1; $i <= 12; $i++){
            if(!empty($recordCount[$i])){
                $recordArr[$i] = $recordCount[$i];
            }else{
                $recordArr[$i] = 0;
            }
        }
        return implode(',', $recordArr);
    }

    public function nonMemberStatisticsRefresh(){
        // Refresh logic for non-member statistics
        return response()->json(['status' => 'success']);
    }

}

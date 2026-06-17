<?php

namespace Modules\Software\Http\Controllers\Api;

use Modules\Generic\Classes\Constants;
use Modules\Generic\Models\Contact;
use Modules\Generic\Models\Setting;
// Firebase integration - optional, loaded dynamically if available
// use App\Modules\Notification\Http\Controllers\Api\FirebaseApiController;
use Modules\Software\Models\GymPushNotification;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Http\Resources\ActivityResource;
use Modules\Software\Http\Resources\AttendanceResource;
use Modules\Software\Http\Resources\BannerContentResource;
use Modules\Software\Http\Resources\BannerResource;
use Modules\Software\Http\Resources\CategoryWithSubscriptionResource;
use Modules\Software\Http\Resources\MemberResource;
use Modules\Software\Http\Resources\NotificationLogResource;
use Modules\Software\Http\Resources\NotificationResource;
use Modules\Software\Models\GymMemberNotificationLog;
use Modules\Software\Http\Resources\PTResource;
use Modules\Software\Http\Resources\SettingResource;
use Modules\Software\Http\Resources\StoreResource;
use Modules\Software\Http\Resources\SubscriptionResource;
use Modules\Software\Models\GymActivity;
use Modules\Software\Models\GymReservation;
use Modules\Software\Models\GymBanner;
use Modules\Software\Models\GymCategory;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymMemberAttendee;
use Modules\Software\Models\GymMemberSubscription;
use Modules\Software\Models\GymMemberSubscriptionFreeze;
use Modules\Software\Models\GymPTClass;
use Modules\Software\Models\GymPushToken;
use Modules\Software\Models\GymStoreProduct;
use Modules\Software\Models\GymSubscription;
use Modules\Software\Models\GymUserLog;
use Carbon\Carbon;
use Illuminate\Container\Container as Application;
use Modules\Generic\Http\Controllers\GenericController;
use Modules\Generic\Repositories\SettingRepository;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GymGenericApiController extends GenericController
{

    public $return = [];
    public $user_id;
    public $api_member;
    public $limit;
    public $lang;
    public $device_type;
    public $device_token;
    public $response;
    public $message;
    private $SettingRepository;


    public function __construct()
    {
        parent::__construct();
        $this->device_type = request('device_type');
        $this->device_token = request('device_token');
        $lang = \request('lang') ? \request('lang') : env('DEFAULT_LANG');
        $this->lang = isset($lang) && in_array($lang, explode(',', env('SYSTEM_LANG'))) ? $lang : env('DEFAULT_LANG');
        app()->setLocale($this->lang);
        $this->limit = 15;
        $this->SettingRepository = new SettingRepository(new Application);
    }


    public function splash()
    {
        $this->successResponse();
        $this->get_settings();
        $this->get_version();
        $this->checkExpiredToken();
        if(@request('device_token')) $this->updatePushToken();

        return $this->return;
    }
    public function checkExpiredToken(){
        $header = \request()->header('Authorization');
        $this->return['result']['is_expired'] = 0;
        if(@$header && !@Auth::guard('api')->user()->id){ $this->return['result']['is_expired'] = 1;}
    }
    public function setting()
    {
        $this->successResponse();
        $this->get_settings();
        $this->get_version();
        if(@request('device_token')) $this->updatePushToken();

        return $this->return;
    }

    public function home()
    {
//        $lang = request('lang');
//        $lang = isset($lang) && in_array($lang, explode(',', env('SYSTEM_LANG'))) ? $lang : env('DEFAULT_LANG');
        if(!$this->validateApiRequest())
            return $this->response;

        $this->checkExpiredToken();

        $app_welcome_member = trans('sw.app_welcome_member', ['name' => @Str::limit($this->SettingRepository->select('name_ar', 'name_en')->first()->name, 20)]);
        $app_welcome_msg = trans('sw.app_welcome_msg', ['name' => Auth::guard('api')->user() ? @strtok(@Auth::guard('api')->user()->name, " ").' ' : ' ']);
        $subscriptions = GymSubscription::branch()->where('is_mobile', 1)->where('category_id', null)->orderBy('id', 'desc')->limit(5)->get();
        $activities = GymActivity::branch()->where('is_mobile', 1)->orderBy('id', 'desc')->limit(5)->get();
        $stores = GymStoreProduct::branch()->where('is_mobile', 1)->orderBy('id', 'desc')->limit(5)->get();
        $trainings = GymPTClass::branch()->where('is_mobile', 1)->with(['pt_subscription.pt_trainers'])->orderBy('id', 'desc')->limit(5)->get();
        $banners = GymBanner::branch()->where('is_mobile', 1)->where('type', 1)->orderBy('id', 'desc')->limit(10)->get();
        $offers  = GymBanner::branch()->where('is_mobile', 1)->where('type', 4)->orderBy('id', 'desc')->limit(10)->get();
        $news    = GymBanner::branch()->where('is_mobile', 1)->where('type', 3)->orderBy('id', 'desc')->limit(10)->get();
        $events  = GymBanner::branch()->where('is_mobile', 1)->where('type', 2)->orderBy('id', 'desc')->limit(10)->get();

        $category_with_subscription = GymCategory::branch()->with(['subscriptions' => function ($q) {
            $q->limit(10);
        }])->where('is_subscription', true)->get();



        $this->return['result']['is_new_notifications'] =  rand(0,1);
        $this->return['result']['welcome_member'] =  $app_welcome_member;
        $this->return['result']['welcome_msg'] =  $app_welcome_msg;
        $this->return['result']['phones'] =  [@$this->SettingRepository->select('phone')->first()->phone];
        $this->return['result']['banners'] =  $banners->isNotEmpty() ? BannerResource::collection($banners) : [];
        $this->return['result']['is_offers'] =  $offers->isNotEmpty() ? 1 : 0;
        $this->return['result']['offers'] =  $offers->isNotEmpty() ? BannerResource::collection($offers) : [];
        $this->return['result']['is_news'] =  $news->isNotEmpty() ? 1 : 0;
        $this->return['result']['news'] =  $news->isNotEmpty() ? BannerResource::collection($news) : [];
        $this->return['result']['is_events'] =  $events->isNotEmpty() ? 1 : 0;
        $this->return['result']['events'] =  $events->isNotEmpty() ? BannerResource::collection($events) : [];
        $this->return['result']['subscriptions'] =  $subscriptions ?  SubscriptionResource::collection($subscriptions) : '';
        $this->return['result']['is_trainings'] =  1;
        $this->return['result']['trainings'] =  $trainings ?  PTResource::collection($trainings) : '';
        $this->return['result']['is_activities'] =  1;
        $this->return['result']['activities'] =  $activities ?  ActivityResource::collection($activities) : '';
        $this->return['result']['is_stores'] =  1;
        $this->return['result']['stores'] =  $stores ?  StoreResource::collection($stores) : '';
        $this->return['result']['is_category_with_subscription'] =  1;
        $this->return['result']['category_with_subscription'] =  $category_with_subscription ?  CategoryWithSubscriptionResource::collection($category_with_subscription) : '';

        // Gym capacity: max allowed vs current attendees in the last hour
        $capacitySetting = $this->SettingRepository->select('app_max_capacity_num')->first();
        $maxCapacity = (int)(@$capacitySetting->app_max_capacity_num ?? 0);
        $currentAttendance = GymMemberAttendee::where('created_at', '>=', Carbon::now()->subHour())->count();
        $this->return['result']['app_max_capacity_num']    = $maxCapacity;
        $this->return['result']['current_attendance_count'] = $currentAttendance;
        $this->return['result']['is_capacity_available']   = $maxCapacity > 0 ? ($currentAttendance < $maxCapacity ? 1 : 0) : 1;

        // Today's PT classes – scoped to the logged-in member's active PT memberships
        // Today's PT classes – always return general classes; if logged in also return member's own
        $todayDayOfWeek = (int) Carbon::now()->dayOfWeek; // 0=Sunday … 6=Saturday
        $todayStr       = Carbon::today()->toDateString();
        $authMember     = Auth::guard('api')->user();

        // ── General: all active mobile PT classes scheduled for today (no trainer list) ──
        $generalPTClasses = GymPTClass::where('is_active', true)
            ->where('is_mobile', 1)
            ->with(['pt_subscription', 'activeClassTrainers.trainer'])
            ->get()
            ->map(function ($ptClass) use ($todayDayOfWeek) {
                $workDays = $ptClass->schedule['work_days'] ?? [];
                $slot = $workDays[$todayDayOfWeek] ?? null;
                if (!$slot || empty($slot['status'])) return null;
                $startTime = !empty($slot['start']) ? Carbon::parse($slot['start'])->format('g:i A') : null;
                $firstTrainer = @$ptClass->activeClassTrainers ? $ptClass->activeClassTrainers->first() : null;
                return [
                    'id'         => $ptClass->id,
                    'name'       => $ptClass->name ?? (@$ptClass->pt_subscription->name ?? '-'),
                    'image'      => @$ptClass->pt_subscription->image ?? null,
                    'start_time' => $startTime,
                    'trainer_name'  => @$firstTrainer->trainer->name ?? '-',
                    'trainer_image' => @$firstTrainer->trainer->image ?? null,
                ];
            })->filter()->values();

        $this->return['result']['is_today_pt_classes'] = $generalPTClasses->isNotEmpty() ? 1 : 0;
        $this->return['result']['today_pt_classes']    = $generalPTClasses->isNotEmpty() ? $generalPTClasses : [];

        // ── Member-specific: member's own PT classes today with trainer & session info ──
        $memberPTClasses = collect();
        if ($authMember) {
            $ptMembers = \Modules\Software\Models\GymPTMember::with([
                    'class.activeClassTrainers.trainer',
                    'class.pt_subscription',
                    'legacyClass.activeClassTrainers.trainer',
                    'legacyClass.pt_subscription',
                    'classTrainer.trainer',
                    'trainer',
                ])
                ->where('member_id', $authMember->id)
                ->where(function ($q) use ($todayStr) {
                    $q->whereDate('joining_date', '<=', $todayStr)
                      ->whereDate('expire_date',  '>=', $todayStr);
                })
                ->get();

            $memberPTClasses = $ptMembers->map(function ($ptMember) use ($todayDayOfWeek) {
                $ptClass = $ptMember->class ?? $ptMember->legacyClass ?? null;
                if (!$ptClass) return null;

                $workDays = $ptClass->schedule['work_days'] ?? [];
                $slot = $workDays[$todayDayOfWeek] ?? null;
                if (!$slot || empty($slot['status'])) return null;

                $startTime = !empty($slot['start']) ? Carbon::parse($slot['start'])->format('g:i A') : null;

                $trainerList = [];

                // 1) Prefer the trainer explicitly assigned to this PT member.
                if ($ptMember->trainer) {
                    $trainerList[] = [
                        'name'  => $ptMember->trainer->name  ?? '-',
                        'image' => $ptMember->trainer->image ?? null,
                    ];
                }

                // 2) If available, use class-trainer assignment for this member.
                if (empty($trainerList) && @$ptMember->classTrainer && @$ptMember->classTrainer->trainer) {
                    $trainerList[] = [
                        'name'  => $ptMember->classTrainer->trainer->name  ?? '-',
                        'image' => $ptMember->classTrainer->trainer->image ?? null,
                    ];
                }

                // 3) Fallback to active class trainers.
                if (empty($trainerList)) {
                    $trainers = $ptClass->activeClassTrainers ?? collect([]);
                    if ($trainers->isNotEmpty()) {
                        foreach ($trainers as $ct) {
                            if (@$ct->trainer) {
                                $trainerList[] = [
                                    'name'  => $ct->trainer->name  ?? '-',
                                    'image' => $ct->trainer->image ?? null,
                                ];
                            }
                        }
                    }
                }

                $totalSessions     = (int)($ptMember->total_sessions     ?? $ptMember->classes ?? 0);
                $remainingSessions = (int)($ptMember->remaining_sessions ?? 0);
                $usedSessions      = $totalSessions - $remainingSessions;

                return [
                    'id'                 => $ptClass->id,
                    'name'               => $ptClass->name ?? (@$ptClass->pt_subscription->name ?? '-'),
                    'image'              => @$ptClass->pt_subscription->image ?? null,
                    'start_time'         => $startTime,
                    'trainer_name'       => !empty($trainerList) ? $trainerList[0]['name']  : '-',
                    'trainer_image'      => !empty($trainerList) ? $trainerList[0]['image'] : null,
                    'total_sessions'     => $totalSessions,
                    'used_sessions'      => $usedSessions >= 0 ? $usedSessions : 0,
                    'remaining_sessions' => $remainingSessions,
                ];
            })->filter()->values();
        }

        $this->return['result']['is_my_today_pt_classes'] = $memberPTClasses->isNotEmpty() ? 1 : 0;
        $this->return['result']['my_today_pt_classes']    = $memberPTClasses->isNotEmpty() ? $memberPTClasses : [];

        // Feature-toggle flags driven by environment keys
        $this->return['result']['hide_count_members'] = (int) env('HIDE_COUNT_MEMBERS', 0);
        $this->return['result']['hide_upgrade']        = (int) env('HIDE_UPGRADE', 0);

        if(@request('device_token')) $this->updatePushToken();
        return $this->successResponse();
    }

    public function banner($id){
        $banner = GymBanner::branch()->where("id", $id)->first();
        $banners = GymBanner::branch()->where("id", '!=',$id)->limit(4)->get();

        $this->return['result']['banner'] =  $banner ? new BannerContentResource($banner) : '';
        $this->return['result']['banners'] =  $banners ? BannerResource::collection($banners) : [];
        return $this->successResponse();
    }
    public function banners(){
        
        $member = Auth::guard('api')->user();

        if (!$member && ($deviceToken = @request()->bearerToken())) {
            $pushToken = GymPushToken::where('token', $deviceToken)->first();
            if ($pushToken && $pushToken->member_id) {
                $member = GymMember::find($pushToken->member_id);
            }
        }
        $code = @$member->code;

        $notifications = GymMemberNotificationLog::where(function ($q) use ($code) {
                $q->where('codes', '')->orWhereNull('codes');
                if ($code) {
                    $c = (string) $code;
                    $q->orWhere('codes', $c)
                      ->orWhere('codes', 'LIKE', $c . ',%')
                      ->orWhere('codes', 'LIKE', '%,' . $c . ',%')
                      ->orWhere('codes', 'LIKE', '%,' . $c);
                }
            })
            ->orderBy('id', 'desc')
            ->paginate($this->limit);

        $this->getPaginateAttribute($notifications);
        $this->return['result']['banners'] = $notifications ? NotificationLogResource::collection($notifications) : [];
        return $this->successResponse();
    }

    public function gallery(){
        $images = array_values($this->SettingRepository->select('images')->first()->images ?? []);
        $result = [];
        foreach($images as $i => $image){
            $result[$i] = asset('uploads/settings/gyms/'.$image);
        }
        $this->return['result']['gallery'] = $result;
        return $this->successResponse();
    }

    public function branches()
    {
        $branches = Setting::select('id', 'name_ar', 'name_en')->get();

        $this->return['result']['branches'] = $branches->map(function ($branch) {
            return [
                'id' => $branch->id,
                'name' => $branch->name,
            ];
        })->values();

        return $this->successResponse();
    }

    public function login(Request $request)
    {
        $phone = request('phone');
        $code = request('code');
        $device_token = request('device_token');
        $device_id = request('device_id');
        $branch_setting_id = request('branch_setting_id');

        if (!$this->validateApiRequest(['phone', 'code', 'device_type'])) return $this->response;

        $member = GymMember::with(['member_subscription_info.subscription' => function ($q){
            $q->withTrashed();
        }, 'member_attendees' => function ($q) {
            $q->orderBy('id', 'desc')->limit(20);
        }])//->withCount('member_attendees')
            ->where('phone' , $phone)
            ->where(DB::raw('CAST(code AS SIGNED)'), (int)$code)
            ->when($branch_setting_id, function ($q) use ($branch_setting_id) {
                $q->where('branch_setting_id', $branch_setting_id);
            })
            ->first();

        if($member && (($member->code == $code) || (@$member->national_id == $code))){
            if($member->is_blocked){
                return $this->falseResponse(trans('sw.block_account_msg'));
            }else {
                $enable_device_binding = $this->SettingRepository->select('enable_device_binding')->first()->enable_device_binding;
                if($enable_device_binding && $device_id && $member->device_id && ($member->device_id != $device_id)){
                    return $this->falseResponse(trans('sw.device_already_linked'));
                }

                $token = Str::random(60);
                $forceFill = ['api_token' => hash('sha256', $token)];
                if($enable_device_binding && $device_id && !$member->device_id){
                    $forceFill['device_id'] = $device_id;
                }
                $member->forceFill($forceFill)->save();

                $this->return['result']['token'] = $token;
                $this->return['result']['member'] = new MemberResource($member);

                return $this->successResponse();
            }
        }
        return $this->falseResponse(trans('auth.invalid_login'));
    }

    public function memberQrToken()
    {
        $member = GymMember::find(@Auth::guard('api')->user()->id);
        if(!$member){
            return $this->falseResponse(trans('auth.invalid_login'));
        }

        // Block QR generation if no active subscription
        $hasActive = GymMemberSubscription::where('member_id', $member->id)
            ->where('status', TypeConstants::Active)
            ->exists();
        if (!$hasActive) {
            return $this->falseResponse(trans('sw.qr_subscription_required'));
        }

        // If the member already has a non-expired token, return it instead of generating a new one
        if($member->qr_token && $member->qr_token_expires_at && Carbon::now()->lt($member->qr_token_expires_at)){
            $this->return['result']['qr_token'] = $member->qr_token;
            $this->return['result']['expires_in'] = Carbon::now()->diffInSeconds($member->qr_token_expires_at, false);

            return $this->successResponse();
        }

        $expiry_seconds = (int) ($this->SettingRepository->select('qr_expiry_seconds')->first()->qr_expiry_seconds ?: 60);

        $token = Str::random(40);
        $member->forceFill([
            'qr_token' => $token,
            'qr_token_expires_at' => Carbon::now()->addSeconds($expiry_seconds),
        ])->save();

        $this->return['result']['qr_token'] = $token;
        $this->return['result']['expires_in'] = $expiry_seconds;

        return $this->successResponse();
    }
    public function memberBlock(){
        $member = GymMember::where(['id' => @Auth::guard('api')->user()->id])->first();
        if($member){
            $member->is_blocked = 1;
            $member->save();
            $this->successResponse();
        }else
            $this->falseResponse();

        return $this->response;
    }
    public function memberInfo(Request $request){

        $member_subscriptions = GymMemberSubscription::branch()->with(['subscription' => function ($q) {
            $q->withTrashed();
        }])
            ->where('member_id', @Auth::guard('api')->user()->id)
            ->limit(TypeConstants::RENEW_MEMBERSHIPS_MAX_NUM)
            ->orderBy('id', 'desc')
            ->get();

        $member = GymMember::with(['member_subscription_info' => function ($q) {
            $q->reorder()->orderByRaw('CASE status
                WHEN ' . TypeConstants::Active  . ' THEN 1
                WHEN ' . TypeConstants::Freeze  . ' THEN 2
                WHEN ' . TypeConstants::Coming  . ' THEN 3
                WHEN ' . TypeConstants::Expired . ' THEN 4
                ELSE 5 END')->orderBy('id', 'desc');
        }, 'member_attendees' => function ($q) {
            $q->orderBy('id', 'desc')->limit(20);
        }])->where(['id' => @Auth::guard('api')->user()->id])->first();
        $this->return['result']['member'] = new MemberResource($member);
        $this->return['result']['is_attendees'] = 1;
        $this->return['result']['is_training_plans'] = 1;
        $this->return['result']['is_training_tracks'] = 1;
        $this->return['result']['is_pt_trainings'] = 1;
        return $this->successResponse();
    }

    public function get_member()
    {
        $member = @\request()->user();
        $this->return['result']['member'] = $member = GymMember::whereId(@$member->id)->first();
        if (!$this->return['result']['member'])
            $this->return['result']['member'] = new GymMember();
    }

    public function attendances(){
        $member_id = @Auth::guard('api')->user()->id;
        $attendances = GymMemberAttendee::where('member_id', $member_id)->orderBy('id', 'desc')->paginate($this->limit);
        $this->getPaginateAttribute($attendances);
        $this->return['result']['attendances'] =  $attendances ?  AttendanceResource::collection($attendances) : [];
        return $this->successResponse();
    }

    public function previousSubscriptions(){
        $member_id = @Auth::guard('api')->user()->id;
        $subscriptions = GymMemberSubscription::with(['subscription' => function($q){ $q->withTrashed(); }])
            ->where('member_id', $member_id)
            ->orderBy('id', 'desc')
            ->get();
        $result = [];
        foreach($subscriptions as $sub){
            $result[] = [
                'id'           => $sub->id,
                'name'         => @$sub->subscription ? $sub->subscription->name : ($sub->subscription_id ? '#'.$sub->subscription_id : '-'),
                'price'        => number_format((float)$sub->price, 2),
                'duration'     => @$sub->subscription ? $sub->subscription->period . ' ' . trans('sw.days') : '-',
                'start_date'   => Carbon::parse($sub->joining_date)->translatedFormat('d F Y'),
                'end_date'     => Carbon::parse($sub->expire_date)->translatedFormat('d F Y'),
                'status'       => $sub->status_name ?? '-',
                'status_value' => $sub->status_value ?? 0,
                'amount_paid'  => number_format((float)($sub->amount_paid ?? $sub->price), 2),
                'amount_remaining' => number_format((float)($sub->amount_remaining ?? 0), 2),
            ];
        }
        $this->return['result']['subscriptions'] = $result;
        return $this->successResponse();
    }

    public function attendanceSummary(){
        $member_id = @Auth::guard('api')->user()->id;
        $now = Carbon::now();

        $total = GymMemberAttendee::where('member_id', $member_id)->count();
        $this_month = GymMemberAttendee::where('member_id', $member_id)
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->count();
        $this_week = GymMemberAttendee::where('member_id', $member_id)
            ->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])
            ->count();

        // Monthly chart: last 6 months
        $monthly_chart = [];
        for($i = 5; $i >= 0; $i--){
            $month = $now->copy()->subMonths($i);
            $count = GymMemberAttendee::where('member_id', $member_id)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            $monthly_chart[] = [
                'month' => $month->translatedFormat('M'),
                'count' => $count,
            ];
        }

        $this->return['result']['total']         = $total;
        $this->return['result']['this_month']    = $this_month;
        $this->return['result']['this_week']     = $this_week;
        $this->return['result']['monthly_chart'] = $monthly_chart;
        return $this->successResponse();
    }

    public function get_settings()
    {
        // Select only columns that exist + JSON columns (social_media contains all social links)
        $this->return['result']['settings'] = new SettingResource($this->SettingRepository->select('phone', 'support_email', 'address_ar', 'address_en', 'latitude', 'longitude', 'social_media', 'ios_version', 'android_version', 'terms_ar', 'terms_en', 'enable_dynamic_qr', 'qr_expiry_seconds')->first());
        return $this->return;
    }

    public function get_version()
    {
        $setting = $this->SettingRepository->select('android_version', 'ios_version')->first();
        $this->return['result']['ios_version'] = $setting->ios_version;
        $this->return['result']['android_version'] = $setting->android_version;

        return $this->return;
    }



    public function updatePushToken()
    {
        $this->validateApiRequest(['device_token']);
        if(in_array(request('device_type'), [Constants::ANDROID, Constants::IOS]))
            $device_type = request('device_type');
        else
            $device_type = Constants::ANDROID;

        $device_token = request('device_token');

        // Resolve member id safely even when this endpoint is called without auth middleware.
        $memberId = @Auth::guard('api')->user()->id;
        if (!$memberId) {
            $token = request()->bearerToken();
            if (!$token) {
                $token = request('token');
            }
            if ($token) {
                $token = trim((string) preg_replace('/^Bearer\s+/i', '', (string) $token));
                $memberId = GymMember::where('api_token', hash('sha256', $token))->value('id');
            }
        }

        $record = GymPushToken::where('token', $device_token)->first();
        if (!$record) {
            GymPushToken::create([
                'device_type' => $device_type,
                'token' => $device_token,
                'member_id' => $memberId,
            ]);
            
            // Try to add token to Firebase topic if class exists
            try {
                if (class_exists('App\Modules\Notification\Http\Controllers\Api\FirebaseApiController')) {
                    (new \App\Modules\Notification\Http\Controllers\Api\FirebaseApiController())->addTokenToTopic($device_token, $device_type);
                }
            } catch (\Exception $e) {
                // Firebase integration not available - continue without it
                \Log::info('Firebase integration not available', ['error' => $e->getMessage()]);
            }
        } else {
            // Do not clear existing member binding with null updates.
            if ($memberId) {
                $record->update(['member_id' => $memberId]);
            }
        }
        $this->successResponse();
        return $this->response;
    }

    public function contact(Request $request)
    {
        if (!$this->validateApiRequest(['phone', 'message'])) return $this->response;

        $inputs = $inputs_ = $request->only(['phone', 'message']);
        foreach ($inputs_ as $key => $input) {
            if (empty($input))
                unset($inputs[$key]);
        }
        $data = array(
            'phone' => $request->phone,
            'msg' => $request->message
        );
        $setting = Setting::first();
        Mail::send('emails.contact_us', $data, function ($message) use ($data, $setting) {
            $message->from(@$data['email'] ?? 'noreply@gymmawy.com', $setting->name);
            $message->to($setting->support_email, trans('global.contact_us'))->subject(trans('global.contact_us'));
        });

        $this->message = trans('sw.contact_add_successfully');
        $this->successResponse();
        return $this->response;


    }



    public function myNotifications(){
        $member = @Auth::guard('api')->user();
        if (!empty($member->id)) {
            $notifications = GymPushNotification::where('member_id', $member->id)->orWhere('member_id', null)->orderBy('id', 'desc')->get();
        } else {
            $notifications = GymPushNotification::whereNull('member_id')->orWhere('member_id', 0)->orderBy('id', 'desc')->get();
        }
        $this->return['notifications'] = $notifications ? NotificationResource::collection($notifications) : [];
        return $this->successResponse();
    }

    public function logErrors(Request $request)
    {
        mail('eng.a7med.ma7er@gmail.com', 'Gymmawy ' . $request->subject, $request->body);
        $this->successResponse();
        return $this->return;
    }


    protected function requestHasUser($key = 'user_id', $action = '', $action_data = [])
    {
        if (!request($key)) {
//            $this->return['error'] = 'missing user_id';
//            $this->return['action'] = $action;
//            $this->return['action_data'] = $action_data;

            $this->return['status'] = Response::HTTP_BAD_REQUEST;
            $this->return['success'] = false;
            $this->return['message'] = $action_data;
            return FALSE;
        }
        return TRUE;
    }




    protected function validateApiRequest($required = [], $action = '', $action_data = [])
    {
        $missing = [];
//        $required[] = 'lang';
//        $required[] = 'device_type';

        foreach ($required as $item) {
            $input = request($item);
            if ((!isset($input)) || $input == '') $missing[] = $item;
        }
        if ($missing) {
            $error = 'missing ' . implode(', ', $missing);
            $this->response= $this->falseResponse($error, $action , $action_data );
            return FALSE;
        }
        return TRUE;
    }

    public function falseResponse($error = '', $action = '', $action_data = [])
    {

//        $this->return['action'] = $action;
//        $this->return['action_data'] = $action_data;
//        $this->return['error'] = $error;
        $this->return['status'] = Response::HTTP_BAD_REQUEST;
        $this->return['success'] = false;
        $this->return['message'] = $error;
//        return $this->response = response()->json($this->return)->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR, $error);
        return $this->response = response()->json($this->return)->setStatusCode(Response::HTTP_BAD_REQUEST, $error);

    }

    public function successResponse($action = '', $action_data = [])
    {
        if (request()->has('need_member') && request('need_member') == 1)
            $this->get_member();

        if (request()->has('need_settings') && request('need_settings') == 1)
            $this->get_settings();

        $this->return['status'] = Response::HTTP_OK;
        $this->return['success'] = true;
        $this->return['message'] = @$this->message ? $this->message : Response::$statusTexts[Response::HTTP_OK];
        return $this->response = response()->json($this->return)->setStatusCode(Response::HTTP_OK, Response::$statusTexts[Response::HTTP_OK]);

    }


    public function returnPaginationData(&$pagination_result)
    {
        $next = ($pagination_result->currentPage() >= $pagination_result->lastPage()) ? -1 : ($pagination_result->currentPage());
        $pagination_result = $pagination_result->toArray()['data'];
        $this->return['result']['page'] = $next;
    }
    public function getPaginateAttribute($records){
        $this->return['result']['current_page'] = $records->currentPage();
        $this->return['result']['next_page'] = $records->currentPage()+1;
        $this->return['result']['pages_count'] = ceil(($records->total() / $this->limit));
    }


    public function memberActivities()
    {
        $member = Auth::guard('api')->user();
        if (!$member) {
            return $this->falseResponse(trans('sw.not_authorized'));
        }

        $activeSubscriptions = GymMemberSubscription::with(['subscription.activities.activity.trainer'])
            ->where('member_id', $member->id)
            ->whereIn('status', [TypeConstants::Active, TypeConstants::Freeze, TypeConstants::Coming])
            ->get();

        // Aggregate training_times per activity across all active subscriptions
        $activityMap = [];
        foreach ($activeSubscriptions as $ms) {
            $sub = $ms->subscription;
            if (!$sub) continue;
            foreach ($sub->activities ?? [] as $pivot) {
                $activity = $pivot->activity ?? null;
                if (!$activity) continue;
                $id = $activity->id;
                if (!isset($activityMap[$id])) {
                    $activityMap[$id] = ['activity' => $activity, 'total_sessions' => 0];
                }
                $activityMap[$id]['total_sessions'] += (int)($pivot->training_times ?? 0);
            }
        }

        // Count used sessions (confirmed + attended) per activity for this member
        $activityIds = array_keys($activityMap);
        $usedCounts = $activityIds
            ? GymReservation::where('member_id', $member->id)
                ->where('client_type', 'member')
                ->whereIn('activity_id', $activityIds)
                ->whereIn('status', ['confirmed', 'attended'])
                ->selectRaw('activity_id, COUNT(*) as cnt')
                ->groupBy('activity_id')
                ->pluck('cnt', 'activity_id')
            : collect([]);

        $activities = [];
        foreach ($activityMap as $id => $item) {
            $activity  = $item['activity'];
            $total     = $item['total_sessions'];
            $used      = (int)($usedCounts[$id] ?? 0);
            $remaining = $total > 0 ? max(0, $total - $used) : null;
            $details = $activity->reservation_details;
            $hasSchedule = false;
            if ($details && isset($details['work_days']) && is_array($details['work_days'])) {
                foreach ($details['work_days'] as $day) {
                    if (!empty($day['status'])) { $hasSchedule = true; break; }
                }
            }
            $sessionsExceeded = $total > 0 && $used >= $total;
            $activities[] = [
                'id'                   => $activity->id,
                'name'                 => $activity->name,
                'image'                => $activity->image,
                'reservation_duration' => (int)($activity->reservation_duration ?? 60),
                'reservation_limit'    => (int)($activity->reservation_limit ?? 0),
                'trainer_name'         => $activity->trainer ? $activity->trainer->name : '',
                'trainer_image'        => $activity->trainer && $activity->trainer->image_name ? asset('uploads/pt_trainers/' . $activity->trainer->image_name) : '',
                'total_sessions'       => $total,
                'used_sessions'        => $used,
                'remaining_sessions'   => $remaining,
                'has_schedule'         => $hasSchedule,
                'can_reserve'          => $hasSchedule && !$sessionsExceeded,
            ];
        }

        $this->return['result']['activities'] = $activities;
        return $this->successResponse();
    }

    public function memberActivitySlots()
    {
        $activityId = request('activity_id');
        $date = request('reservation_date');
        $duration = request('duration');

        $activity = GymActivity::find($activityId);
        if (!$activity) {
            return $this->falseResponse(trans('sw.not_found'));
        }

        $duration = (int)($duration ?? $activity->reservation_duration ?? 60);
        $interval = 30;
        $reservationLimit = (int)($activity->reservation_limit ?? 0);
        $hasLimit = $reservationLimit > 0;

        $dateCarbon = Carbon::parse($date);
        $dayOfWeek = $dateCarbon->dayOfWeek;
        $dateStr = $dateCarbon->format('Y-m-d');

        $reservationDetails = $activity->reservation_details;
        $startOfDay = '08:00';
        $endOfDay = '20:00';

        if ($reservationDetails && isset($reservationDetails['work_days'][$dayOfWeek])) {
            $dayConfig = $reservationDetails['work_days'][$dayOfWeek];
            if (empty($dayConfig['status'])) {
                $this->return['result']['slots'] = [];
                $this->return['result']['day_available'] = false;
                return $this->successResponse();
            }
            $startOfDay = $dayConfig['start'] ?? '08:00';
            $endOfDay = $dayConfig['end'] ?? '20:00';
        }

        $cursor = Carbon::parse("$dateStr $startOfDay");
        $endDay = Carbon::parse("$dateStr $endOfDay");
        $slots = [];
        while ($cursor->copy()->addMinutes($duration) <= $endDay) {
            $slots[] = ['start' => $cursor->format('H:i'), 'end' => $cursor->copy()->addMinutes($duration)->format('H:i')];
            $cursor->addMinutes($interval);
        }

        $existing = \Modules\Software\Models\GymReservation::where('activity_id', $activityId)
            ->whereDate('reservation_date', $dateStr)
            ->whereNotIn('status', ['cancelled', 'missed'])
            ->get(['start_time', 'end_time']);

        $result = [];
        foreach ($slots as $slot) {
            $overlaps = 0;
            foreach ($existing as $ex) {
                try {
                    $aS = Carbon::createFromFormat('H:i', substr($ex->start_time, 0, 5));
                    $aE = Carbon::createFromFormat('H:i', substr($ex->end_time, 0, 5));
                    $sT = Carbon::createFromFormat('H:i', $slot['start']);
                    $eT = Carbon::createFromFormat('H:i', $slot['end']);
                    if ($sT->lt($aE) && $eT->gt($aS)) $overlaps++;
                } catch (\Exception $e) {}
            }
            $available = $hasLimit ? $overlaps < $reservationLimit : true;
            $result[] = [
                'start_time'       => $slot['start'],
                'end_time'         => $slot['end'],
                'available'        => $available,
                'current_bookings' => $overlaps,
            ];
        }

        $this->return['result']['slots'] = $result;
        $this->return['result']['day_available'] = true;
        $this->return['result']['duration'] = $duration;
        return $this->successResponse();
    }

    public function memberActivityBook()
    {
        $member = Auth::guard('api')->user();
        if (!$member) {
            return $this->falseResponse(trans('sw.not_authorized'));
        }

        $activityId  = request('activity_id');
        $date        = request('reservation_date');
        $startTime   = request('start_time');
        $endTime     = request('end_time');
        $notes       = request('notes', '');

        if (!$activityId || !$date || !$startTime || !$endTime) {
            return $this->falseResponse(trans('sw.required_fields'));
        }

        // 1. Verify the member has an active subscription covering this activity
        $activeSubscription = GymMemberSubscription::with(['subscription.activities'])
            ->where('member_id', $member->id)
            ->where('expire_date', '>=', Carbon::today())
            ->get()
            ->first(function ($ms) use ($activityId) {
                return ($ms->subscription->activities ?? collect())
                    ->contains('activity_id', $activityId);
            });

        if (!$activeSubscription) {
            return $this->falseResponse(trans('sw.subscription_expired_or_not_found'));
        }

        // 2. Check session limit: sum training_times across all active subscriptions for this activity
        $allActiveSubs = GymMemberSubscription::with(['subscription.activities'])
            ->where('member_id', $member->id)
            ->where('expire_date', '>=', Carbon::today())
            ->get();

        $totalSessions = 0;
        foreach ($allActiveSubs as $ms) {
            foreach ($ms->subscription->activities ?? [] as $pivot) {
                if ($pivot->activity_id == $activityId) {
                    $totalSessions += (int)($pivot->training_times ?? 0);
                }
            }
        }

        if ($totalSessions > 0) {
            $usedSessions = GymReservation::where('member_id', $member->id)
                ->where('client_type', 'member')
                ->where('activity_id', $activityId)
                ->whereIn('status', ['confirmed', 'attended'])
                ->count();

            if ($usedSessions >= $totalSessions) {
                return $this->falseResponse(trans('sw.activity_sessions_exceeded'));
            }
        }

        \Modules\Software\Models\GymReservation::unguarded(function () use ($member, $activityId, $date, $startTime, $endTime, $notes) {
            \Modules\Software\Models\GymReservation::create([
                'client_type'       => 'member',
                'member_id'         => $member->id,
                'activity_id'       => $activityId,
                'reservation_date'  => Carbon::parse($date)->format('Y-m-d'),
                'start_time'        => $startTime,
                'end_time'          => $endTime,
                'status'            => 'confirmed',
                'notes'             => $notes,
                'branch_setting_id' => $member->branch_setting_id,
            ]);
        });

        $this->message = trans('sw.reserved_success_msg');
        return $this->successResponse();
    }

    public function memberReservations()
    {
        $member = Auth::guard('api')->user();
        if (!$member) {
            return $this->falseResponse(trans('sw.not_authorized'));
        }

        $reservations = GymReservation::with('activity')
            ->where('member_id', $member->id)
            ->where('client_type', 'member')
            ->orderBy('reservation_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->limit(50)
            ->get();

        $today = Carbon::today();
        $result = [];
        foreach ($reservations as $r) {
            $activityName = $r->activity ? $r->activity->name : '-';
            $dateStr = $r->reservation_date ? $r->reservation_date->format('Y-m-d') : '';
            $canCancel = $r->status === 'confirmed'
                && $dateStr
                && Carbon::parse($dateStr)->gte($today);
            $result[] = [
                'id'            => $r->id,
                'activity_name' => $activityName,
                'date'          => $dateStr,
                'start_time'    => substr($r->start_time ?? '', 0, 5),
                'end_time'      => substr($r->end_time ?? '', 0, 5),
                'status'        => $r->status ?? 'confirmed',
                'can_cancel'    => $canCancel,
                'notes'         => $r->notes ?? '',
            ];
        }

        $this->return['result']['reservations'] = $result;
        return $this->successResponse();
    }

    public function memberActivityCancel()
    {
        $member = Auth::guard('api')->user();
        if (!$member) {
            return $this->falseResponse(trans('sw.not_authorized'));
        }

        $id = request('reservation_id');
        $reservation = GymReservation::where('id', $id)
            ->where('member_id', $member->id)
            ->where('client_type', 'member')
            ->first();

        if (!$reservation) {
            return $this->falseResponse(trans('sw.not_found'));
        }

        $dateStr = $reservation->reservation_date ? $reservation->reservation_date->format('Y-m-d') : null;
        if ($reservation->status !== 'confirmed' || !$dateStr || Carbon::parse($dateStr)->lt(Carbon::today())) {
            return $this->falseResponse(trans('sw.not_authorized'));
        }

        $reservation->update(['status' => 'cancelled', 'cancelled_at' => Carbon::now()]);
        $this->message = trans('sw.reservation_cancelled');
        return $this->successResponse();
    }

    public function memberSubscriptionFreeze(){
        $member_id =  @Auth::guard('api')->user()->id;
        $memberInfo = GymMember::with(['member_subscription_info' => function ($q) {
            $q->reorder()->orderByRaw('CASE status
                WHEN ' . TypeConstants::Active  . ' THEN 1
                WHEN ' . TypeConstants::Freeze  . ' THEN 2
                WHEN ' . TypeConstants::Coming  . ' THEN 3
                WHEN ' . TypeConstants::Expired . ' THEN 4
                ELSE 5 END')->orderBy('id', 'desc');
        }])->where(['id' => $member_id])->first();

        //$memberInfo = GymMemberSubscription::branch()->with(['member', 'subscription'])->where('member_id', $member_id)->orderBy('id', 'desc')->first();
        $memberInfo = @$memberInfo->member_subscription_info;
        if($memberInfo && ($memberInfo->number_times_freeze > 0) && ($memberInfo->status == TypeConstants::Active) ){
            $memberInfo->status = TypeConstants::Freeze;
            $memberInfo->number_times_freeze = ($memberInfo->number_times_freeze - 1);
            $memberInfo->start_freeze_date = Carbon::now();
            $memberInfo->end_freeze_date = Carbon::now()->addDays((int)$memberInfo->freeze_limit);
            $memberInfo->expire_date = Carbon::parse($memberInfo->expire_date)->addDays((int)$memberInfo->freeze_limit);

            $memberInfo->save();

            // persist freeze record
            GymMemberSubscriptionFreeze::create([
                'member_id' => $memberInfo->member_id,
                'member_subscription_id' => $memberInfo->id,
                'start_date' => Carbon::parse($memberInfo->start_freeze_date)->toDateString(),
                'end_date' => Carbon::parse($memberInfo->end_freeze_date)->toDateString(),
                'status' => 'active',
                'freeze_limit' => (int)$memberInfo->freeze_limit,
                'reason' => null,
                'admin_note' => null,
            ]);

            $notes = str_replace(':name', @$memberInfo->member->name, trans('sw.freeze_member'));
            $notes = str_replace(':membership', @$memberInfo->subscription->name, $notes);

            GymUserLog::insert([
                'user_id' => @$member_id,
                'notes' => $notes,
                'type' => TypeConstants::FreezeMember
            ]);

            $this->successResponse();
        }else
            $this->falseResponse();

        return $this->response;
    }
}


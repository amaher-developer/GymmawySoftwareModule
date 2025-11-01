<?php

namespace Modules\Software\Http\Controllers\Api;

use Modules\Generic\Classes\Constants;
use Modules\Generic\Models\Contact;
use Modules\Generic\Models\Setting;
// Firebase integration - optional, loaded dynamically if available
// use App\Modules\Notification\Http\Controllers\Api\FirebaseApiController;
use App\Modules\Notification\Models\PushNotification;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Http\Resources\ActivityResource;
use Modules\Software\Http\Resources\AttendanceResource;
use Modules\Software\Http\Resources\BannerContentResource;
use Modules\Software\Http\Resources\BannerResource;
use Modules\Software\Http\Resources\CategoryWithSubscriptionResource;
use Modules\Software\Http\Resources\MemberResource;
use Modules\Software\Http\Resources\NotificationResource;
use Modules\Software\Http\Resources\PTResource;
use Modules\Software\Http\Resources\SettingResource;
use Modules\Software\Http\Resources\StoreResource;
use Modules\Software\Http\Resources\SubscriptionResource;
use Modules\Software\Models\GymActivity;
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

        $app_welcome_member = trans('sw.app_welcome_member', ['name' => @$this->SettingRepository->select('name_ar', 'name_en')->first()->name]);
        $app_welcome_msg = trans('sw.app_welcome_msg', ['name' => Auth::guard('api')->user() ? @strtok(@Auth::guard('api')->user()->name, " ").' ' : ' ']);
        $subscriptions = GymSubscription::where('is_mobile', 1)->where('category_id', null)->orderBy('id', 'desc')->limit(5)->get();
        $activities = GymActivity::where('is_mobile', 1)->orderBy('id', 'desc')->limit(5)->get();
        $stores = GymStoreProduct::where('is_mobile', 1)->orderBy('id', 'desc')->limit(5)->get();
        $trainings = GymPTClass::where('is_mobile', 1)->with(['pt_subscription.pt_trainers'])->orderBy('id', 'desc')->limit(5)->get();
        $banners = GymBanner::where('is_mobile', 1)->orderBy('id', 'desc')->limit(5)->get();

        $category_with_subscription = GymCategory::with(['subscriptions' => function ($q) {
            $q->limit(10);
        }])->where('is_subscription', true)->get();


        $this->return['result']['is_new_notifications'] =  rand(0,1);
        $this->return['result']['welcome_member'] =  $app_welcome_member;
        $this->return['result']['welcome_msg'] =  $app_welcome_msg;
        $this->return['result']['phones'] =  [@Setting::first()->phone];
        $this->return['result']['banners'] =  $stores ?  BannerResource::collection($banners) : '';
        $this->return['result']['subscriptions'] =  $subscriptions ?  SubscriptionResource::collection($subscriptions) : '';
        $this->return['result']['is_trainings'] =  1;
        $this->return['result']['trainings'] =  $trainings ?  PTResource::collection($trainings) : '';
        $this->return['result']['is_activities'] =  1;
        $this->return['result']['activities'] =  $activities ?  ActivityResource::collection($activities) : '';
        $this->return['result']['is_stores'] =  1;
        $this->return['result']['stores'] =  $stores ?  StoreResource::collection($stores) : '';
        $this->return['result']['is_category_with_subscription'] =  1;
        $this->return['result']['category_with_subscription'] =  $category_with_subscription ?  CategoryWithSubscriptionResource::collection($category_with_subscription) : '';


        if(@request('device_token')) $this->updatePushToken();
        return $this->successResponse();
    }

    public function banner($id){
        $banner = GymBanner::where("id", $id)->first();
        $banners = GymBanner::where("id", '!=',$id)->limit(4)->get();

        $this->return['result']['banner'] =  $banner ? new BannerContentResource($banner) : '';
        $this->return['result']['banners'] =  $banners ? BannerResource::collection($banners) : [];
        return $this->successResponse();
    }
    public function banners(){
        $banners = GymBanner::orderBy("id", "desc")->paginate($this->limit);
        $this->getPaginateAttribute($banners);
        $this->return['result']['banners'] =  $banners ?  BannerResource::collection($banners) : [];
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

    public function login(Request $request)
    {
        $phone = request('phone');
        $code = request('code');
        $device_token = request('device_token');

        if (!$this->validateApiRequest(['phone', 'code', 'device_type'])) return $this->response;

        $member = GymMember::with(['member_subscription_info.subscription' => function ($q){
            $q->withTrashed();
        }, 'member_attendees' => function ($q) {
            $q->orderBy('id', 'desc')->limit(20);
        }])//->withCount('member_attendees')
            ->where('phone' , $phone)
            ->where(DB::raw('CAST(code AS SIGNED)'), (int)$code)
            ->first();

        if($member && (($member->code == $code) || (@$member->national_id == $code))){
            if($member->is_blocked){
                return $this->falseResponse(trans('sw.block_account_msg'));
            }else {
                $token = Str::random(60);
                $member->forceFill([
                    'api_token' => hash('sha256', $token),
                ])->save();

                $this->return['result']['token'] = $token;
                $this->return['result']['member'] = new MemberResource($member);

                return $this->successResponse();
            }
        }
        return $this->falseResponse(trans('auth.invalid_login'));
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

        $member = GymMember::with(['member_subscription_info' => function($query) use ($member_subscriptions){
            if((@count($member_subscriptions) > 1) && (@$member_subscriptions[0]->status  == TypeConstants::Coming))
                $query->where('id',  $member_subscriptions[1]->id);
            $query->orderBy('id', 'desc');
        }, 'member_attendees' => function ($q) {
            $q->orderBy('id', 'desc')->limit(20);
        }])//->withCount('member_attendees')
            ->where(['id' => @Auth::guard('api')->user()->id])->first();
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

    public function get_settings()
    {
        // Select only columns that exist + JSON columns (social_media contains all social links)
        $this->return['result']['settings'] = new SettingResource($this->SettingRepository->select('phone', 'support_email', 'address_ar', 'address_en', 'latitude', 'longitude', 'social_media', 'ios_version', 'android_version', 'terms_ar', 'terms_en')->first());
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
        $record = GymPushToken::where('token', $device_token)->first();
        if (!$record) {
            GymPushToken::create([
                'device_type' => $device_type,
                'token' => $device_token,
                'member_id' => @Auth::guard('api')->user()->id
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
            $record->update(['member_id' => @Auth::guard('api')->user()->id]);
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
        $member = @\request()->user();
        if (!empty($member->id)) {
            $notifications = PushNotification::where('member_id', @$member->id)->orWhere('member_id', null)->orderBy('id', 'desc')->paginate($this->limit);
            $this->return['notifications'] = $notifications ? NotificationResource::collection($notifications) : [];
            return $this->successResponse();
        } else {
            return $this->falseResponse(trans('auth.failed'));
        }
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


    public function memberSubscriptionFreeze(){
        $member_id =  @Auth::guard('api')->user()->id;
        $memberInfo = GymMemberSubscription::branch()->with(['member', 'subscription'])->where('member_id', $member_id)->orderBy('id', 'desc')->first();
        $is_freeze = $this->SettingRepository->select('is_freeze')->first()->is_freeze;
        if(@$is_freeze && $memberInfo && ($memberInfo->number_times_freeze > 0) && ($memberInfo->status == TypeConstants::Active) ){
            $memberInfo->status = TypeConstants::Freeze;
            $memberInfo->number_times_freeze = ($memberInfo->number_times_freeze - 1);
            $memberInfo->start_freeze_date = Carbon::now();
            $memberInfo->end_freeze_date = Carbon::now()->addDays($memberInfo->freeze_limit);
            $memberInfo->expire_date = Carbon::parse($memberInfo->expire_date)->addDays($memberInfo->freeze_limit);

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

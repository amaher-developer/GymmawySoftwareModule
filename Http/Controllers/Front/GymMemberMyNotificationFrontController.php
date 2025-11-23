<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Generic\Classes\Constants;
use App\Modules\Notification\Http\Controllers\Admin\OneSignalController;
use App\Modules\Notification\Http\Controllers\Api\FirebaseApiController;
use App\Modules\Notification\Models\PushNotification;
use Modules\Generic\Models\Setting;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymMemberNotificationLog;
use Modules\Software\Models\GymPushNotification;
use Modules\Software\Models\GymPushToken;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class GymMemberMyNotificationFrontController extends GymGenericFrontController
{
    public $GymUserRepository;

    public function __construct()
    {
        parent::__construct();
    }
    public function index()
    {
        $title = trans('sw.notification_logs');

        $logs = GymMemberNotificationLog::branch()->with(['user']);
        if(!$this->user_sw->is_super_user)
            $logs->where('user_id', $this->user_sw->id);
        $logs->orderBy('id', 'DESC');
        $logs = $logs->paginate($this->limit);
        $total = $logs->total();


        return view('software::Front.my_notification_log_front_list', compact('logs','title', 'total'));
    }

    public function create()
    {
        $title = trans('sw.p_application', ['name' => @env('APP_NAME_'.strtoupper($this->lang))]);
//        $members = new MemberNotification();
//        $members = $members->getMembersList();
        $members = GymPushToken::branch()->with('member')->where('member_id' ,'!=', '')->get();
//        $members = GymMember::all();

        return view('software::Front.my_notification_front_create', ['title'=>$title, 'members' => $members]);
    }

    public function store(Request $request)
    {
        $user_inputs = $request->except(['_token']);
        $msg = $user_inputs['message'];
        $data['title'] = $user_inputs['title'];
        $data['body'] = $msg;

        $data['image'] =  $this->mainSettings->logo ? $this->mainSettings->logo : 'https://gymmawy.com/resources/assets/new_front/img/logo/default.png';
        $data['sound'] = 'default';
        $data['badge'] = '1';
        $data['e'] = 1;
        switch ($user_inputs['type']) {
            case TypeConstants::NOTIFICATION_EXTERNAL_URL:
                $data['url'] = $user_inputs['url'];
                break;
        }

        if($msg){
            if(@$user_inputs['member_codes'] && (count(@$user_inputs['member_codes']) > 0) && !@$user_inputs['member_code_all']) {
                $members = GymMember::branch()->whereIn('code', $user_inputs['member_codes'])->pluck('id');
                $result = $this->pushToMember($data, $members);
            }

            if(@$user_inputs['member_code_all']){
                $result = $this->push($data);
            }

            if(@$result->message_id) {
                GymPushNotification::create([
                    'title' => @$user_inputs['title'],
                    'body' => $data,
                    'notification_id' => @$result->message_id,
                    'branch_setting_id' => @$this->user_sw->branch_setting_id
                ]);

                // save log to notifications log
                GymMemberNotificationLog::create([
                    'user_id' => Auth::guard('sw')->user()->id,
                    'codes' => @$user_inputs['member_codes'] ? implode(',', @$user_inputs['member_codes']) : '',
                    "content" => $msg,
                    "response" => @$result ? json_encode($result) : false,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now(),
                    "branch_setting_id" => @$this->user_sw->branch_setting_id,
                ]);
            }
        }

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_processed'),
            'type' => 'success'
        ]);
        return redirect(route('sw.createMyNotification'));
    }




    public function push($data)
    {
        foreach([Constants::ANDROID, Constants::IOS] as $device_type) {
            $push = (new FirebaseApiController())->pushToTopic($data, $device_type);
        }

        $response = json_decode($push);
        return @$response;
    }
    public function pushToMember($data, $memberIds = [])
    {
        $push = (new FirebaseApiController())->push($memberIds, $data);
        $response = json_decode($push);
        return @$response;
    }

    public function show(GymPushNotification $notification)
    {
        $stats = OneSignalController::getNotificationStats($notification->notification_id);
        return view('software::Front.notification_front_show', [
            'title' => $notification->title,
            'stats' => json_decode($stats),
            'notification' => $notification
        ]);
    }



}


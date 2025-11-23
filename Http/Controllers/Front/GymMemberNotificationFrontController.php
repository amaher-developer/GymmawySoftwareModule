<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Software\Classes\MemberNotification;
use Modules\Software\Classes\SMS;
use Modules\Software\Classes\SMSGymmawy;
use Modules\Generic\Models\Setting;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Http\Controllers\Api\GymMemberApiController;
use Modules\Software\Http\Requests\GymSMSRequest;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymMemberNotificationLog;
use Modules\Software\Models\GymNonMember;
use Modules\Software\Models\GymSMSLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class GymMemberNotificationFrontController extends GymGenericFrontController
{
    public $GymUserRepository;

    public function __construct()
    {
        parent::__construct();
    }


    public function create()
    {
        $title = trans('sw.g_application');
        $members = new MemberNotification();
        $members = $members->getMembersList();
        $members = @$members->subscriptions;
//        $members = GymMember::all();

        return view('software::Front.notification_front_create', ['title'=>$title, 'members' => $members]);
    }

    public function store(Request $request)
    {
        $user_inputs = $request->except(['_token']);
        $msg = $user_inputs['message'];
        if((count($user_inputs['member_codes']) > 0) && $msg){
            $members = GymMember::whereIn('code', $user_inputs['member_codes'])->get();
            foreach($members as $member){
                // send notify for renew to gymmawy
                $sendNotify = new GymMemberApiController();
                $result = $sendNotify->sendMsgForOneMemberToGymmawy($member, $msg);
            }
            // save log to notifications log
            GymMemberNotificationLog::create([
                'user_id' => Auth::guard('sw')->user()->id,
                'codes' => implode(',', $user_inputs['member_codes']),
                "content" => $msg,
                "response" => @$result ? $result : false,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now(),
                "branch_setting_id" => @$this->user_sw->branch_setting_id,
            ]);
        }



        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_processed'),
            'type' => 'success'
        ]);
        return redirect(route('sw.createNotification'));
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


        return view('software::Front.notification_log_front_list', compact('logs','title', 'total'));
    }
    public function phonesByAjax(){
        $phones = [];
        $type = request('type');
        if($type == 2){
            $phones = GymNonMember::branch()->pluck('phone')->toArray();
        }else if($type == 3){
            $phones = GymMember::branch()->pluck('phone')->toArray();
        }
        return implode(', ', $phones);
    }





}


<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Software\Classes\SMSFactory;
use Modules\Software\Classes\SMSGymmawy;
use Modules\Generic\Models\Setting;
use Modules\Software\Http\Requests\GymSMSRequest;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymNonMember;
use Modules\Software\Models\GymSMSLog;


class GymSMSFrontController extends GymGenericFrontController
{
    public $GymUserRepository;

    public function __construct()
    {
        parent::__construct();
    }


    public function create()
    {
        $title = trans('sw.sms_add');
        $mainSettings = Setting::branch()->first();
        $smsPoints = $this->formatSmsPoints(0);

        try {
            if($mainSettings['sms_internal_gateway']){
                $sms = (new SMSGymmawy())->getBalance();
            }else{
                $sms = (new SMSFactory(@env('SMS_GATEWAY')))->getBalance();
            }
            $smsPoints = $this->formatSmsPoints($sms);
        } catch (\Throwable $e) {
            \Log::warning('Unable to fetch SMS balance: '.$e->getMessage());
        }

        return view('software::Front.sms_front_create', ['title'=>$title, 'smsPoints' => $smsPoints]);
    }

    public function store(GymSMSRequest $request)
    {

        $user_inputs = $request->except(['_token']);
        $sms = new SMSFactory(@env('SMS_GATEWAY'));
        $sms->send($user_inputs['phones'], $user_inputs['message']);
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_processed'),
            'type' => 'success'
        ]);
        return redirect(route('sw.createSMS'));

    }

    public function index()
    {
        $title = trans('sw.sms_logs');

        $logs = GymSMSLog::branch()->with(['user']);
        if(!$this->user_sw->is_super_user)
            $logs->where('user_id', $this->user_sw->id);
        $logs->orderBy('id', 'DESC');
        $logs = $logs->paginate($this->limit);
        $total = $logs->total();


        return view('software::Front.sms_log_front_list', compact('logs','title', 'total'));
    }
    public function phonesByAjax(){
        $phones = [];
        $type = request('type');
        if($type == 2){
            $phones = GymNonMember::branch()->pluck('phone')->toArray();
        }else if($type == 3){
            $phones = GymMember::branch()->pluck('phone')->toArray();
        }else if($type == 4){
            $phones = GymMember::branch()->pluck('phone')->whereHas('member_subscription_info', function ($q) {
                $q->whereRaw('sw_gym_member_subscription.id IN (select MAX(a2.id) from sw_gym_member_subscription as a2 join sw_gym_members as u2 on u2.id = a2.member_id group by u2.id) and sw_gym_member_subscription.status = 1');
                //$q->where('status', (int)$status);
            })->toArray();
        }
        return implode(', ', $phones);
    }





}


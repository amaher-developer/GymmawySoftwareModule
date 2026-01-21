<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Software\Classes\SMSFactory;
use Modules\Software\Classes\SMSGymmawy;
use Modules\Generic\Models\Setting;
use Modules\Software\Http\Requests\GymSMSRequest;
use Modules\Software\Http\Requests\GymTelegramRequest;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymNonMember;
use Modules\Software\Models\GymSMSLog;

use Illuminate\Http\Request;
use Modules\Generic\Classes\Constants;
use Modules\Software\Classes\TypeConstants;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Laravel\Facades\Telegram;

class GymTelegramFrontController extends GymGenericFrontController
{
    public $GymUserRepository;

    public function __construct()
    {
        parent::__construct();
    }


    public function create()
    {
        $title = trans('sw.telegram_add');
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
            \Log::warning('Unable to fetch SMS balance for telegram: '.$e->getMessage());
        }

        return view('software::Front.telegram_front_create', ['title'=>$title, 'smsPoints' => $smsPoints]);
    }

    public function store(GymTelegramRequest $request)
    {
        $user_inputs = $request->except(['_token']);
        $sms = new SMSFactory(@env('SMS_GATEWAY'));
        $sms->send($user_inputs['phones'], $user_inputs['message']);
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_processed'),
            'type' => 'success'
        ]);
        return redirect(route('sw.createTelegram'));
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
            $phones = GymMember::branch()->with('member_subscription_info')->whereHas('member_subscription_info', function ($q) {
                //$q->whereRaw('sw_gym_member_subscription.id IN (select MAX(a2.id) from sw_gym_member_subscription as a2 join sw_gym_members as u2 on u2.id = a2.member_id group by u2.id) and sw_gym_member_subscription.status = 0');
                $q->where('status', TypeConstants::Active);
            })->get()->pluck('phone')->toArray();
        }
        return implode(', ', $phones);
    }


    public function updatedActivity()
    {
        $activity = Telegram::getUpdates();
        dd($activity);
    }



    public function sendMessage()
    {
        return view('telegramView');
    }

    public function storeMessage(GymTelegramRequest $request)
    {
        $text =  $request->message;
        $image = $request->file('image');
        if($image){
            Telegram::sendPhoto([
                'chat_id' => @env('TELEGRAM_CHANNEL_ID'),
                'photo' => InputFile::createFromContents(file_get_contents($image->getRealPath()), str_random(10) . '.' . $image->getClientOriginalExtension()),
                'caption' => strip_tags($text)
            ]);
        }else{
            Telegram::sendMessage([
                'chat_id' => @env('TELEGRAM_CHANNEL_ID'),
                'parse_mode' => 'HTML',
                'text' => strip_tags($text, '<b><strong><i><em><u><s><strike><del>')
            ]);
        }


        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_send'),
            'type' => 'success'
        ]);
        return redirect(route('sw.createTelegram'));
    }



}


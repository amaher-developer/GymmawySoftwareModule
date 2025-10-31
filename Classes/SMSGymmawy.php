<?php

namespace Modules\Software\Classes;


use Modules\Generic\Models\Setting;
use Modules\Software\Models\GymSMSLog;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;

class SMSGymmawy  {


    private $sms_url;
    private $token;
    private $setting;

    public function __construct()
    {
        $this->setting = Setting::first();
        $this->sms_url = env('APP_URL_MASTER').'api/';
        $this->token = $this->setting->token;
    }


    public function send($phoneNumber, $msg)
    {
//        if(substr($phoneNumber, 0, 2) == "01") $phone = str_replace("01", "201", $phoneNumber);
//        elseif(substr($phoneNumber, 0, 2) == "05") $phone = str_replace("05", "9665", $phoneNumber);
//        else $phone = $phoneNumber;

        $client = new Client();
        $res = $client->post('https://gymmawy.com/api/'.'client-send-sms',
            ['verify' => false, 'json' => [
            'phones' => $phoneNumber,
            'token' => $this->token,
            "message" => $msg
            ]]);
        $result = $res->getBody();

        // save log to sms log
        GymSMSLog::create([
            'user_id' => Auth::guard('sw')->user()->id,
            'phones' => $phoneNumber,
            "content" => $msg,
            "response" => ($result),
            "created_at" => Carbon::now(),
            "updated_at" => Carbon::now(),
        ]);

        return (json_decode($result));
    }

    public function getBalance()
    {
        $client = new Client();
        $res = $client->post('https://gymmawy.com/api/'.'client-get-sms-balance', ['verify' => false, 'json' => ['token' => $this->token]]);
        $result = $res->getBody();
        return (json_decode($result));
    }


}

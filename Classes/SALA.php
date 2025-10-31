<?php

namespace Modules\Software\Classes;


use Modules\Generic\Models\Setting;
use Modules\Software\Models\GymSMSLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class SALA  {


    private $sms_url;
    private $sms_username;
    private $sms_password;
    private $sms_api_token;
    private $sms_sender;
    private $setting;

    public function __construct()
    {
        $this->setting = Setting::first();
        $this->sms_url = 'https://api2.smsala.com/api/SendSMS';
        $this->sms_sender = $this->setting->sms_sender_id;
        $this->sms_username = $this->setting->sms_username;
        $this->sms_password = $this->setting->sms_password;
        $this->sms_api_token = $this->setting->sms_password;
    }


    public function send($phoneNumber, $msg): string
    {
        $phones = explode(',', $phoneNumber);
        $phone = [];
        if(is_array($phones)){
            foreach ($phones as $p){
                if(@ltrim(trim($p), '0')) {
                    $phone[] = @ltrim(@env('APP_COUNTRY_CODE'), '00') . ltrim(trim($p), '0');
                }
            }
        }else{
            $phone = [@ltrim(@env('APP_COUNTRY_CODE'), '00').ltrim($phoneNumber, '0')];
        }

        $app_id = $this->sms_username;//"api key";
        $app_sec = $this->sms_password;//"api secret";
        $app_token = $this->sms_api_token;//"api secret";
        $app_sender_id = $this->sms_sender;//"api secret";
        $base_url = "https://api2.smsala.com/";

        $request_sent = [];
        foreach ($phone as $i => $p){
            $request_sent[$i]['apiToken'] = $app_token;
            $request_sent[$i]['messageType'] = "2";
            $request_sent[$i]['messageEncoding'] = "8";
            $request_sent[$i]['destinationAddress'] = $p;
            $request_sent[$i]['sourceAddress'] = $app_sender_id;
            $request_sent[$i]['messageText'] = ($msg);
        }
//        $request_sent = array_map('json_encode', $request_sent);

        $response = Http::withOptions([
            'verify' => false, // Disable SSL verification
        ])->post($base_url.'SendSmsV2', $request_sent
        //  [
        //'api_id' => $app_id,
        //'api_password' => $app_sec,
//            'sms_type' => 'T',
//            'encoding' => 'T',
//            'sender_id' => $app_sender_id,
//            'phonenumber' => @implode(',',$phone),
//            'textmessage' => $msg,
//            "ValidityPeriodInSeconds" => 60,
//            'uid' => 'xyz',
//            'callback_url' => 'https://xyz.com/',
//            "V1" => null,
//            "V2" => null,
//            "V3" => null,
//            "V4" => null,
//            "V5" => null,
//            "templateid" =>  null,
//            "pe_id" => NULL,
//            "template_id" => NULL,
        //]
        );

        $result = $response->json();
        $status_code = $response->status();

        if ($status_code == 200) {
            if (isset($result[0]['MessageId']) && (@$result[0]['Status'] == 'Success')) {
                // save log to sms log
                GymSMSLog::create([
                    'user_id' => @Auth::guard('sw')->user()->id,
                    'phones' => $phoneNumber,
                    "content" => $msg,
                    "response" => ($result),
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now(),
                ]);
            }
        }

        return true;
    }



    public function getBalance()
    {
        $app_id = $this->sms_username;
        $app_sec = $this->sms_password;
        $app_token = $this->sms_api_token;//"api secret";
        $base_url = "https://api2.smsala.com/";
        $response = Http::withOptions([
            'verify' => false, // Disable SSL verification
        ])->get($base_url.'CheckBalance', [
//            'api_id' => $app_id,
//            'api_password' => $app_sec,
            'apiToken' => $app_token,
        ]);

        $result = $response->json();
        return @($result['ReturnData']['Balance']/0.2);

    }






}

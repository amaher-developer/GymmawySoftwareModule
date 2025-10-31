<?php

namespace Modules\Software\Classes;


use Modules\Generic\Models\Setting;
use Modules\Software\Models\GymSMSLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SMSEG  {


    private $sms_url;
    private $sms_username;
    private $sms_password;
    private $sms_sender;
    private $setting;

    public function __construct()
    {
        $this->setting = Setting::first();
        $this->sms_url = 'https://smssmartegypt.com/sms/api';
        $this->sms_sender = $this->setting->sms_sender_id;
        $this->sms_username = $this->setting->sms_username;
        $this->sms_password = $this->setting->	sms_password;
    }


    public function send($phoneNumber, $msg): string
    {
//        if(substr($phoneNumber, 0, 2) == "01") $phone = str_replace("01", "201", $phoneNumber);
//        elseif(substr($phoneNumber, 0, 2) == "05") $phone = str_replace("05", "9665", $phoneNumber);
//        else $phone = $phoneNumber;
        return false;
//        dd($phoneNumber, $msg);
        $ch = curl_init();
        $certificate_location = env('CERTIFICATE_LOCATION');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $certificate_location);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $certificate_location);
        $options = array(
            CURLOPT_URL            => $this->sms_url,
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
            ),
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode(array(
                'mobiles' => $phoneNumber,
                'sendername' => $this->sms_sender,
                'username' => $this->sms_username,
                'password' => $this->sms_password,
                "message" => $msg,
            )),
            CURLOPT_RETURNTRANSFER => true
        );

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response);

        // save log to sms log
        GymSMSLog::create([
            'user_id' => Auth::guard('sw')->user()->id,
            'phones' => $phoneNumber,
            "content" => $msg,
            "response" => $result,
            "created_at" => Carbon::now(),
            "updated_at" => Carbon::now(),
        ]);

        return (@$result);
    }

    public function getBalance()
    {
        $ch = curl_init();
        $certificate_location = env('CERTIFICATE_LOCATION');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $certificate_location);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $certificate_location);
        $options = array(
            CURLOPT_URL            => $this->sms_url.'/getBalance?username='.$this->sms_username.'&password='.$this->sms_password,
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
            ),
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode(array(
            )),
            CURLOPT_RETURNTRANSFER => true
        );

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response);
        return (@$result);
    }


}

<?php

namespace Modules\Software\Classes;


use Modules\Generic\Models\Setting;
use Modules\Software\Models\GymSMSLog;
use Modules\Software\Models\GymWALog;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;

class WA {


    private $wa_url;
    private $wa_version;
    private $wa_user_token;
    private $country_code;
    private $wa_user_phone_id;
    private $setting;

    public function __construct()
    {
        $this->setting = Setting::first();
        $this->wa_version = 'v15.0';
        $this->wa_user_token = @env('WA_USER_TOKEN') ?? '';
        $this->wa_user_phone_id = @env('WA_USER_PHONE_ID') ?? '';
        $this->country_code = @env('APP_COUNTRY_CODE') ?? '2';
        $this->wa_url = 'https://graph.facebook.com/'.$this->wa_version.'/'.$this->wa_user_phone_id;
    }


    public function sendText($phoneNumber, $msg): string
    {
//        if(substr($phoneNumber, 0, 2) == "01") $phone = $this->str_replace_first("01", "201", $phoneNumber);
//        elseif(substr($phoneNumber, 0, 2) == "05") $phone = $this->str_replace_first("05", "9665", $phoneNumber);
//        else $phone = $phoneNumber;

        $phone = $this->country_code.$phoneNumber;
//        $client = new Client();
//        $res = $client->post($this->wa_url.'/messages',
//            [
//                'Content-Type: application/json',
//                'Cache-Control: no-cache',
//                'Connection: keep-alive',
//                'Authorization: Bearer '.$this->wa_user_token,
//              'json' => [
//                  'access_token' => $this->wa_user_token,
//                'to' => $phone,
//                'messaging_product' => "whatsapp",
//                'text' => ["body" => $msg]
//            ]]);
//        $result = $res->getBody();


        $ch = curl_init();
        $certificate_location = env('CERTIFICATE_LOCATION');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $certificate_location);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $certificate_location);
        $options = array(
            CURLOPT_URL            => $this->wa_url.'/messages',
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Authorization: Bearer '.$this->wa_user_token,
            ),
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode(array(
                'to' => $phone,
                'access_token' => $this->wa_user_token,
                'messaging_product' => "whatsapp",
                'text' => ["body" => $msg]
            )),
            CURLOPT_RETURNTRANSFER => true
        );

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response);
        $message_id =  @$result->messages ? @$result->messages[0]->id : '';
        // save log to sms log
        GymWALog::create([
            'user_id' => Auth::guard('sw')->user()->id,
            'status' => $message_id ? 1 : 0,
            'phone' => $phoneNumber,
            "content" => $msg,
            "message_id" => $message_id,
            "created_at" => Carbon::now(),
            "updated_at" => Carbon::now(),
        ]);
        return $message_id;
    }

    public function sendImage($phoneNumber, $imagePath): string
    {
//        if(substr($phoneNumber, 0, 2) == "01") $phone = $this->str_replace_first("01", "201", $phoneNumber);
//        elseif(substr($phoneNumber, 0, 2) == "05") $phone = $this->str_replace_first("05", "9665", $phoneNumber);
//        else $phone = $phoneNumber;

        $phone = $this->country_code.$phoneNumber;

        $ch = curl_init();
        $certificate_location = env('CERTIFICATE_LOCATION');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $certificate_location);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $certificate_location);
        $options = array(
            CURLOPT_URL            => $this->wa_url.'/messages',
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Authorization: Bearer '.$this->wa_user_token,
            ),
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode(array(
                'to' => $phone,
                'access_token' => $this->wa_user_token,
                'messaging_product' => "whatsapp",
                "type" => "image",
                "image" => [
                    "link" => $imagePath
                ]
            )),
            CURLOPT_RETURNTRANSFER => true
        );

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response);
        $message_id = @$result->messages ? @$result->messages[0]->id : '';
        // save log to wa log
        GymWALog::create([
            'user_id' => Auth::guard('sw')->user()->id,
            'phone' => $phoneNumber,
            'status' => $message_id ? 1 : 0,
            "content" => $imagePath,
            "message_id" => $message_id,
            "created_at" => Carbon::now(),
            "updated_at" => Carbon::now(),
        ]);

        return $message_id;
    }

    public function sendTextWithTemplate($phoneNumber, $template, $data): string
    {
//        if(substr($phoneNumber, 0, 2) == "01") $phone = $this->str_replace_first("01", "201", $phoneNumber);
//        elseif(substr($phoneNumber, 0, 2) == "05") $phone = $this->str_replace_first("05", "9665", $phoneNumber);
//        else $phone = $phoneNumber;
        $template = @$this->setting->wa_details['templates'][$template];
        $phone = $this->country_code.$phoneNumber;

        $params = json_encode(array(
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phone,
            "type" => "template",
            "template" => [
                "name" => $template,
                "language" => [
                    "code" => "AR"
                ],
                "components" => [[
                    "type" => "body",
                    "parameters" => $data
                ]
                ]

            ]


        ));

        $ch = curl_init();
        $certificate_location = env('CERTIFICATE_LOCATION');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $certificate_location);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $certificate_location);
        $options = array(
            CURLOPT_URL            => $this->wa_url.'/messages',
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Authorization: Bearer '.$this->wa_user_token,
            ),
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $params,
            CURLOPT_RETURNTRANSFER => true
        );

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response);

        $message_id =  @$result->messages ? @$result->messages[0]->id : '';
        // save log to sms log
//        GymWALog::create([
//            'user_id' => Auth::guard('sw')->user()->id,
//            'status' => $message_id ? 1 : 0,
//            'phone' => $phoneNumber,
//            "content" => $msg,
//            "message_id" => $message_id,
//            "created_at" => Carbon::now(),
//            "updated_at" => Carbon::now(),
//        ]);
        return $message_id;
    }

    public function sendTextImageWithTemplate($phoneNumber, $template, $data, $image_url): string
    {
//        if(substr($phoneNumber, 0, 2) == "01") $phone = $this->str_replace_first("01", "201", trim($phoneNumber));
//        elseif(substr($phoneNumber, 0, 2) == "05") $phone = $this->str_replace_first("05", "9665", trim($phoneNumber));
//        else $phone = $phoneNumber;
        $phone = $this->country_code.$phoneNumber;
        $template = @$this->setting->wa_details['templates'][$template];
        $params = json_encode(array(
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phone,
            "type" => "template",
            "template" => [
                "name" => $template,
                "language" => [
                    "code" => "AR"
                ],
                "components" => [
                    [
                        "type" => "header",
                        "parameters" => [
                            [
                                "type" => "image",
                                "image" => [
                                    "link" => $image_url
                                ]
                            ]
                        ]
                    ],
                    [
                        "type" => "body",
                        "parameters" => $data
                    ]
                ]
            ]
        ));

        $ch = curl_init();
        $certificate_location = env('CERTIFICATE_LOCATION');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $certificate_location);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $certificate_location);
        $options = array(
            CURLOPT_URL            => $this->wa_url.'/messages',
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Authorization: Bearer '.$this->wa_user_token,
            ),
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $params,
            CURLOPT_RETURNTRANSFER => true
        );

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response);

        $message_id =  @$result->messages ? @$result->messages[0]->id : '';
        $message_id =  (string)$message_id;
        // save log to sms log
        GymWALog::create([
            'user_id' => Auth::guard('sw')->user()->id,
            'status' => @$message_id ? 1 : 0,
            'phone' => $phoneNumber,
            "message_id" => @$message_id,
            "content" => $data,
        ]);

        return $message_id;
    }


    private function str_replace_first($search, $replace, $subject)
    {
        $search = '/'.preg_quote($search, '/').'/';
        return preg_replace($search, $replace, $subject, 1);
    }
}

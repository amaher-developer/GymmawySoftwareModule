<?php

namespace Modules\Software\Classes;


use Modules\Generic\Models\Setting;
use Modules\Software\Models\GymWALog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class FBCams {


    private $wa_url;
    private $fp_token;
    private $setting;

    public function __construct()
    {
        $this->setting = Setting::first();
        $this->fp_token = @env('FP_TOKEN') ?? '';
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

        return $result;
    }


    private function str_replace_first($search, $replace, $subject)
    {
        $search = '/'.preg_quote($search, '/').'/';
        return preg_replace($search, $replace, $subject, 1);
    }
}

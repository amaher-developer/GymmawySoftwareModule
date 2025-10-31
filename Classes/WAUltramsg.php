<?php

namespace Modules\Software\Classes;


use Modules\Generic\Models\Setting;
use Modules\Software\Models\GymWALog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class WAUltramsg {


    private $wa_url;
    private $wa_instance;
    private $wa_user_token;
    private $curl;
    private $setting;

    public function __construct()
    {
        $this->setting = Setting::first();
        
        // Check if setting exists and wa_details is not null
        if ($this->setting && $this->setting->wa_details && is_array($this->setting->wa_details)) {
            $this->wa_instance = $this->setting->wa_details['wa_ultra_instance_id'] ?? null;
            $this->wa_user_token = $this->setting->wa_details['wa_ultra_token'] ?? null;
        } else {
            $this->wa_instance = null;
            $this->wa_user_token = null;
        }
        
        $this->wa_url = "https://api.ultramsg.com/".$this->wa_instance."/messages";

        $this->curl = curl_init();
    }
    public function statistics()
    {
        // Check if we have valid configuration
        if (!$this->wa_instance || !$this->wa_user_token) {
            return (object)['error' => 'WhatsApp configuration not found'];
        }
        
        $curl = $this->curl;
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->wa_url."/statistics?token=".$this->wa_user_token,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
//            echo "cURL Error #:" . $err;
            return $err;
        } else {
            return json_decode($response);
        }
    }

    public function sendText($phoneNumber, $msg)
    {
        // Check if we have valid configuration
        if (!$this->wa_instance || !$this->wa_user_token) {
            return (object)['error' => 'WhatsApp configuration not found'];
        }
        
//        if(substr($phoneNumber, 0, 2) == "01") $phone = $this->str_replace_first("01", "201", $phoneNumber);
//        elseif(substr($phoneNumber, 0, 2) == "05") $phone = $this->str_replace_first("05", "9665", $phoneNumber);
//        else $phone = $phoneNumber;
        $phone = '+'.@env('APP_COUNTRY_CODE').$phoneNumber;

        $params=array(
            'token' => $this->wa_user_token,
            'to' => $phone,
            'body' => $msg,
            'priority' => 1,
            'referenceId' => ''
        );
        $curl = $this->curl;
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->wa_url.'/chat',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        if ($err) {
//            echo "cURL Error #:" . $err;
            return "0";
        } else {
            return json_decode($response);
        }
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
//        return $message_id;
    }

    public function sendImage($phoneNumber, $msg, $image)
    {
        // Check if we have valid configuration
        if (!$this->wa_instance || !$this->wa_user_token) {
            return (object)['error' => 'WhatsApp configuration not found'];
        }
        
//        if(substr($phoneNumber, 0, 2) == "01") $phone = $this->str_replace_first("01", "201", $phoneNumber);
//        elseif(substr($phoneNumber, 0, 2) == "05") $phone = $this->str_replace_first("05", "9665", $phoneNumber);
//        else $phone = $phoneNumber;

        $phone = '+'.@env('APP_COUNTRY_CODE').$phoneNumber;

        $params=array(
            'token' => $this->wa_user_token,
            'to' => $phone,
            'image' => $image,
            'caption' => $msg,
            'nocache' => '',
            'referenceId' => ''
        );
        $curl = $this->curl;
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->wa_url."/image",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded"
            ),
        ));


        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        if ($err) {
//            echo "cURL Error #:" . $err;
            return "0";
        } else {
            return json_decode($response);
        }

    }


    private function str_replace_first($search, $replace, $subject)
    {
        $search = '/'.preg_quote($search, '/').'/';
        return preg_replace($search, $replace, $subject, 1);
    }
}

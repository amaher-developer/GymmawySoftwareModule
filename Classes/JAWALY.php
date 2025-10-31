<?php

namespace Modules\Software\Classes;


use Modules\Generic\Models\Setting;
use Modules\Software\Models\GymSMSLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class JAWALY  {


    private $sms_url;
    private $sms_username;
    private $sms_password;
    private $sms_sender;
    private $setting;

    public function __construct()
    {
        $this->setting = Setting::first();
        $this->sms_url = 'https://api-sms.4jawaly.com/api/v1/account/area/sms/send';
        $this->sms_sender = $this->setting->sms_sender_id;
        $this->sms_username = $this->setting->sms_username;
        $this->sms_password = $this->setting->	sms_password;
    }


    public function send($phoneNumber, $msg): string
    {
//        if(substr($phoneNumber, 0, 2) == "01") $phone = str_replace("01", "201", $phoneNumber);
//        elseif(substr($phoneNumber, 0, 2) == "05") $phone = str_replace("05", "9665", $phoneNumber);
//        else $phone = $phoneNumber;
        $phones = explode(',', $phoneNumber);
        $phone = [];
        if(is_array($phones)){
            foreach ($phones as $p){
                if(@ltrim(trim($p), '0')) {
                    $phone[] = @env('APP_COUNTRY_CODE') . ltrim(trim($p), '0');
                }
            }
        }else{
            $phone = [@env('APP_COUNTRY_CODE').ltrim($phoneNumber, '0')];
        }

        $app_id = $this->sms_username;//"api key";
        $app_sec = $this->sms_password;//"api secret";
        $app_hash = base64_encode("{$app_id}:{$app_sec}");

        $messages = [
            "messages" => [
                [
                    "text" => $msg,
                    "numbers" => $phone,
                    "sender" => $this->sms_sender
                ]
            ]
        ];

        $url = $this->sms_url ;
        $headers = [
            "Accept: application/json",
            "Content-Type: application/json",
            "Authorization: Basic {$app_hash}"
        ];

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($messages));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $result = json_decode($response, true);

        if ($status_code == 200) {
            if (isset($result["code"]) && ($result["code"] == 200)) {
                // save log to sms log
                GymSMSLog::create([
                    'user_id' => @Auth::guard('sw')->user()->id,
                    'phones' => $phoneNumber,
                    "content" => $msg,
                    "response" => ($result),
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now(),
                ]);
//                echo "تم الارسال بنجاح  " . " job id:" . $result["job_id"];
            }
        }
//        elseif ($status_code == 400) {
//            echo $result["message"];
//        } elseif ($status_code == 422) {
//            echo "نص الرسالة فارغ";
//        } else {
//            echo "محظور بواسطة كلاودفلير. Status code: {$status_code}";
//        }



        return true;
    }



    public function getBalance()
    {
        $app_id = $this->sms_username;
        $app_sec = $this->sms_password;
        $app_hash  = base64_encode("$app_id:$app_sec");
        $base_url = "https://api-sms.4jawaly.com/api/v1/";
        $query = [];
        $query["is_active"] = 1; // get active only
        $query["order_by"] = "id"; // package_points, current_points, expire_at or id (default)
        $query["order_by_type"] = "desc"; // desc or asc
        $query["page"] = 1 ;
        $query["page_size"] = 10 ;
        $query["return_collection"] = 1; // if you want to get all collection
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $base_url.'account/area/me/packages?'.http_build_query($query),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Basic '.$app_hash
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response);

        return @$result->total_balance;

    }






}

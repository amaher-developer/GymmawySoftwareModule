<?php

namespace Modules\Software\Classes;


use Modules\Generic\Models\Setting;
use Modules\Software\Models\GymSMSLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class Taqnyat  {


    private $base_url = 'https://api.taqnyat.sa';
    private $sms_api_token;
    private $sms_sender;
    private $setting;

    public function __construct()
    {
        $this->setting = Setting::first();
        $this->sms_sender = $this->setting->sms_sender_id;
        $this->sms_api_token = $this->setting->sms_password;
    }


    public function send($phoneNumber, $msg): string
    {
        $phones = explode(',', $phoneNumber);
        $recipients = [];

        if(is_array($phones)){
            foreach ($phones as $p){
                $p = trim($p);
                if($p) {
                    // Format: international format without "00" or "+" symbol
                    $formatted = ltrim($p, '0+');
                    // Add country code if not present
                    if(!str_starts_with($formatted, ltrim(@env('APP_COUNTRY_CODE'), '00'))) {
                        $formatted = ltrim(@env('APP_COUNTRY_CODE'), '00') . $formatted;
                    }
                    $recipients[] = $formatted;
                }
            }
        }else{
            $recipients = [ltrim(@env('APP_COUNTRY_CODE'), '00').ltrim($phoneNumber, '0')];
        }

        if(empty($recipients)) {
            return false;
        }

        $response = Http::withOptions([
            'verify' => false,
        ])->withHeaders([
            'Authorization' => 'Bearer ' . $this->sms_api_token,
            'Content-Type' => 'application/json',
        ])->post($this->base_url . '/v1/messages', [
            'recipients' => $recipients,
            'body' => $msg,
            'sender' => $this->sms_sender,
        ]);

        $result = $response->json();
        $status_code = $response->status();

        if ($status_code == 201 || $status_code == 200) {
            if (isset($result['messageId']) && $result['statusCode'] == 201) {
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
        try {
            $response = Http::withOptions([
                'verify' => false,
            ])->timeout(10)->withoutRedirecting()->withHeaders([
                'Authorization' => 'Bearer ' . $this->sms_api_token,
            ])->get($this->base_url . '/account/balance');

            if (!$response->successful()) {
                \Log::warning('Taqnyat getBalance failed: ' . $response->status() . ' - ' . $response->body());
                return (object)['data' => (object)['points' => 0]];
            }

            $result = $response->json();

            // Response: { "balance": "2044.000", "currency": "SAR", ... }
            if (isset($result['balance'])) {
                // Convert SAR balance to points (assuming same rate as SALA: 0.2 SAR per point)
                return (object)['data' => (object)['points' => (int)((float)$result['balance'] / 0.2)]];
            }

            return (object)['data' => (object)['points' => 0]];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error('Taqnyat getBalance ConnectionException: ' . $e->getMessage());
            return (object)['data' => (object)['points' => 0]];
        } catch (\Exception $e) {
            \Log::error('Taqnyat getBalance Exception: ' . $e->getMessage());
            return (object)['data' => (object)['points' => 0]];
        }
    }


}

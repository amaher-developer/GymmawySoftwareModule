<?php

namespace Modules\Software\Classes;



use Modules\Generic\Models\Setting;

class MemberNotification  {

    private $setting;
    private $notification_url;

    function __construct()
    {
        $this->setting = Setting::first();
        $this->notification_url = 'https://gymmawy.com/api';
    }

    public function getMembersList()
    {
        $ch = curl_init();
        $certificate_location = env('CERTIFICATE_LOCATION');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $certificate_location);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $certificate_location);
        $options = array(
            CURLOPT_URL            => $this->notification_url.'/gym-subscription-get-members-list',
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
            ),
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode(array(
                'client_token' => $this->setting->token
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

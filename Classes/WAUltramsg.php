<?php

namespace Modules\Software\Classes;


use Modules\Generic\Models\Setting;
use Modules\Software\Models\GymWALog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use UltraMsg\WhatsAppApi;

class WAUltramsg {

    private $wa_instance;
    private $wa_user_token;
    private $setting;
    private $client;

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

        if ($this->wa_instance && $this->wa_user_token) {
            $this->client = new WhatsAppApi($this->wa_user_token, $this->wa_instance);
        }
    }

    public function statistics()
    {
        if (!$this->client) {
            return (object)['error' => 'WhatsApp configuration not found'];
        }

        return $this->toObject($this->client->getMessageStatistics());
    }

    public function sendText($phoneNumber, $msg)
    {
        if (!$this->client) {
            return (object)['error' => 'WhatsApp configuration not found'];
        }

        $phone = '+'.@env('APP_COUNTRY_CODE').$phoneNumber;
        $result = $this->toObject($this->client->sendChatMessage($phone, $msg, 1));

        $this->log($phoneNumber, $msg, $result);

        return $result;
    }

    public function sendImage($phoneNumber, $msg, $image)
    {
        if (!$this->client) {
            return (object)['error' => 'WhatsApp configuration not found'];
        }

        $phone = '+'.@env('APP_COUNTRY_CODE').$phoneNumber;
        $result = $this->toObject($this->client->sendImageMessage($phone, $image, $msg, 1));

        $this->log($phoneNumber, $msg, $result);

        return $result;
    }

    /**
     * Normalize the SDK's array/JSON response into an object so callers can
     * keep using property access (e.g. $result->error, $result->id).
     */
    private function toObject($data)
    {
        $obj = is_array($data) ? json_decode(json_encode($data)) : (is_object($data) ? $data : (object)['raw' => $data]);

        // The SDK returns its own connection-level errors (e.g. instance not
        // found) under an "Error" key, while the live API uses lowercase
        // "error" - normalize so callers only need to check one.
        if (isset($obj->Error) && !isset($obj->error)) {
            $obj->error = $obj->Error;
        }

        return $obj;
    }

    private function log($phoneNumber, $msg, $result)
    {
        try {
            $messageId = @$result->id ?: null;
            $success = empty(@$result->error);

            GymWALog::create([
                'branch_setting_id' => $this->setting->id ?? 1,
                'user_id' => optional(Auth::guard('sw')->user())->id,
                'status' => $success ? 1 : 0,
                'phone' => $phoneNumber,
                'content' => $msg,
                'message_id' => $messageId,
            ]);
        } catch (\Exception $e) {
            // Never let a logging failure break the actual send flow
            // (e.g. automated notifications running without an authenticated user).
        }
    }

    private function str_replace_first($search, $replace, $subject)
    {
        $search = '/'.preg_quote($search, '/').'/';
        return preg_replace($search, $replace, $subject, 1);
    }
}

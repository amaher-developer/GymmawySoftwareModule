<?php

namespace Modules\Software\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GymSubscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name_ar' => 'required',
            'name_en' => 'required',
            'price' => 'required',
            'period' => 'required',
            'workouts' => 'required',
            'freeze_limit' => 'required',
            'number_times_freeze' => 'required',
            'sound_active' => 'mimes:application/octet-stream,audio/mpeg,mpga,mp3,wav',
            'sound_expired' => 'mimes:application/octet-stream,audio/mpeg,mpga,mp3,wav',

        ];
    }
}


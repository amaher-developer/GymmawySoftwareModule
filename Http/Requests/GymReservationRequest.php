<?php

namespace Modules\Software\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GymReservationRequest extends FormRequest
{
    public function authorize()
    {
        return true; // already protected by auth middleware
    }

    public function rules()
    {
        return [
            'client_type'      => 'required|in:member,non_member',
            'member_id'        => 'nullable|integer',
            'non_member_id'    => 'nullable|integer',
            'activity_id'      => 'required|integer',
            'reservation_date' => 'required|date',
            'start_time'       => 'required',
            'end_time'         => 'required',
            'notes'            => 'nullable|string',
        ];
    }
}


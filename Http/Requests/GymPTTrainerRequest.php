<?php

namespace Modules\Software\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GymPTTrainerRequest extends FormRequest
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
        $phone = '';
        if(request('trainer')) $phone = ',phone,'.intval(request('trainer'));
        return [
            'name' => 'required',
            'phone'=> 'required|unique:sw_gym_pt_trainers'.$phone,
//            'reservation_details' => 'required|array',
//            'class_ids' => 'required|array',
            'reservation_details' => 'array',
            'class_ids' => 'array',
            'percentage' => 'required',
        ];
    }
}


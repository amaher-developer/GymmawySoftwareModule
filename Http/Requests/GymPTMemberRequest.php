<?php

namespace Modules\Software\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GymPTMemberRequest extends FormRequest
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
        $arr = [
            'pt_subscription_id' => 'required',
            'pt_class_id' => 'required',
            'pt_trainer_id' => 'required',
            'trainer_percentage' => 'required',
            'amount_paid' => 'required',
        ];
        if(\Request::route()->getName() != 'sw.editPTMember') $arr['member_id'] = 'required';

        return $arr;

    }
}


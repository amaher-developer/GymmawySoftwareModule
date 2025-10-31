<?php

namespace Modules\Software\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GymPotentialMemberRequest extends FormRequest
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
        if(request('member')) $phone = ',phone,'.intval(request('member'));
        return [
            'name' => 'required',
            'phone' => 'required|unique:sw_gym_potential_members'.$phone
        ];
    }
}

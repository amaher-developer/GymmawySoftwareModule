<?php

namespace Modules\Software\Http\Requests;

use Modules\Software\Models\GymMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GymMemberRequest extends FormRequest
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
        // check if this phone already deleted we change number to add this number again to us with out effect on order and flow
        $checkDeletePhone = GymMember::where('phone', request('phone'))->onlyTrashed()->first();
        if($checkDeletePhone){
            $checkDeletePhone->phone = $checkDeletePhone->phone.'-'.time();
            $checkDeletePhone->save();
        }
        $phone = $code = $fp_id = '';
        if(request('member')) $phone = ',phone,'.intval(request('member'));
        $arr = [
            'name'=> 'required',
            'gender'=> 'required',
            'phone'=> 'required',
            Rule::unique('sw_gym_members', 'phone')
                ->where('branch_setting_id', @$this->user_sw->branch_setting_id),
//            'fp_id'=> 'unique:sw_gym_members'.$fp_id,
            'code'=> 'numeric',
            Rule::unique('sw_gym_members', 'code')
                ->where('branch_setting_id', @$this->user_sw->branch_setting_id),
            'address'=> 'required',
            'additional_info' => 'nullable|string',
//            'dob'=> 'required|date',
//            'amount_paid'=> 'required|numeric',
        ];
        if((@env('APP_ZK_GATE') == true) && request('member') && request('fp_id')){
            $fp_id = ',fp_id,'.intval(request('member'));
            $arr['fp_id'] = 'unique:sw_gym_members'.$fp_id;
        }

        if(request('dob')) $arr['dob'] = 'date';
        if(request('member') && request('code')){
            $code = ',code,'.intval(request('member'));
            $arr['code'] = 'required|unique:sw_gym_members'.$code;
        }

//        if(\Request::route()->getName() != 'sw.editMember') $arr['subscription_id'] = 'required';

        return $arr;
    }

}


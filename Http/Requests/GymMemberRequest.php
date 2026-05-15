<?php

namespace Modules\Software\Http\Requests;

use Modules\Software\Models\GymMember;
use Modules\Generic\Models\GenericModel;
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
        //return auth('sw')->check();
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
        $checkDeletePhone = GymMember::branch()->where('phone', request('phone'))->onlyTrashed()->first();
        if($checkDeletePhone){
            $checkDeletePhone->phone = $checkDeletePhone->phone.'-'.time();
            $checkDeletePhone->save();
        }
        $phone = $code = $fp_id = '';
        if(request('member')) $phone = ',phone,'.intval(request('member'));
        $branchId = GenericModel::getCurrentBranchId();
        $memberId = request('member') ? intval(request('member')) : null;
        $arr = [
            'name'=> 'required',
            'gender'=> 'required',
            // 'phone'=> 'required|unique:sw_gym_members'.$phone,
//            'fp_id'=> 'unique:sw_gym_members'.$fp_id,
            // 'code'=> 'numeric|unique:sw_gym_members'.$code,
            'phone' => [
                'required',
                Rule::unique('sw_gym_members')
                    ->ignore($memberId)
                    ->where(function ($query) use ($branchId) {
                        return $query->where('branch_setting_id', $branchId);
                    }),
            ],

            'code' => [
                'nullable',
                'numeric',
                Rule::unique('sw_gym_members')
                    ->ignore($memberId)
                    ->where(function ($query) use ($branchId) {
                        return $query->where('branch_setting_id', $branchId);
                    }),
            ],
            'fp_id' => [
                'nullable',
                'numeric',
                Rule::unique('sw_gym_members')
                    ->ignore($memberId)
                    ->where(function ($query) use ($branchId) {
                        return $query->where('branch_setting_id', $branchId);
                    }),
            ],
            'address'=> 'required',
            'additional_info' => 'nullable|string',
//            'dob'=> 'required|date',
//            'amount_paid'=> 'required|numeric',
        ];
        if((@env('APP_ZK_GATE') == true) && request('member') && request('fp_id')){
            $fp_id = ',fp_id,'.intval(request('member'));
            // $arr['fp_id'] = 'unique:sw_gym_members'.$fp_id;
        }

        if(request('dob')) $arr['dob'] = 'date';
        if(request('member') && request('code')){
            $code = ',code,'.intval(request('member'));
            // $arr['code'] = 'required|unique:sw_gym_members'.$code;
        }

//        if(\Request::route()->getName() != 'sw.editMember') $arr['subscription_id'] = 'required';
        return $arr;
    }

}


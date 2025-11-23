<?php

namespace Modules\Software\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GymUserRequest extends FormRequest
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

        $phone = $email =  $fp_id = '';
        if(request('user')) $phone = ',phone,'.intval(request('user'));
        if(request('user')) $email = ',email,'.intval(request('user'));
        if(request('user')) $fp_id = ',fp_id,'.intval(request('user'));
        $return = [
            'name' => 'required',
            'email'=> 'required|unique:sw_gym_users'.$email,
            'phone'=> 'required|unique:sw_gym_users'.$phone,
//            'branch_setting_id'=> 'required',
//            'salary' => 'double',
//            'fp_id'=> 'unique:sw_gym_users'.$fp_id,
//            'permissions' => 'required|array',
        ];
        if(request('fp_id')) $return['fp_id'] = 'unique:sw_gym_users'.$fp_id;
        if(\Request::route()->getName() == 'sw.createUser') $return['password'] = 'required';

        return $return;
    }
}


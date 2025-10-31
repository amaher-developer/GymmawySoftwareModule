<?php

namespace Modules\Software\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GymPTClassRequest extends FormRequest
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
            'pt_subscription_id' => 'required',
            'classes' => 'required',
            'price' => 'required',
            'name_ar' => 'required|max:80',
            'name_en' => 'required|max:80',
            'content_ar' => 'max:250',
            'content_en' => 'max:250',
        ];
    }
}

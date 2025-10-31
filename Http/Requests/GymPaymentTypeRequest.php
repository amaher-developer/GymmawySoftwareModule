<?php

namespace Modules\Software\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GymPaymentTypeRequest extends FormRequest
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
//            'name_ar' => 'required',
//            'name_en' => 'required',
//            'price' => 'required|numeric',
//            'content_ar' => 'max:250',
//            'content_en' => 'max:250',
        ];
    }
}

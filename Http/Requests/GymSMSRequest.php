<?php

namespace Modules\Software\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GymSMSRequest extends FormRequest
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
            'phones' => 'required',
            'message'=> 'required',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ];
    }

    public function messages()
    {
        return [
            'image.image' => trans('validation.image'),
            'image.mimes' => trans('validation.mimes', ['values' => 'JPEG, JPG, PNG, GIF, WEBP']),
            'image.max' => trans('validation.max.file', ['max' => '5MB']),
        ];
    }
}


<?php

namespace Modules\Software\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GymTrainingMedicineRequest extends FormRequest
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
            'name_ar' => 'required_without:name_en|string|max:255',
            'name_en' => 'required_without:name_ar|string|max:255',
            'dose' => 'nullable|string|max:255',
            'status' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'name_ar' => trans('sw.medicine_name_ar'),
            'name_en' => trans('sw.medicine_name_en'),
            'dose' => trans('sw.medicine_dose'),
            'status' => trans('sw.status'),
        ];
    }
}


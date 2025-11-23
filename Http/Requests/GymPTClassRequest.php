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
            'pt_subscription_id' => 'required|integer|exists:sw_gym_pt_subscriptions,id',
            'name_ar' => 'required|string|max:80',
            'name_en' => 'required|string|max:80',
            'content_ar' => 'nullable|string|max:250',
            'content_en' => 'nullable|string|max:250',
            'price' => 'required|numeric|min:0',
            'total_sessions' => 'required|integer|min:1',
            'max_members' => 'nullable|integer|min:1',
            'class_type' => 'required|in:private,group,mixed',
            'pricing_type' => 'required|in:per_member,per_group',
            'is_active' => 'nullable|boolean',
            'is_system' => 'nullable|boolean',
            'is_mobile' => 'nullable|boolean',
            'is_web' => 'nullable|boolean',
            'class_color' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:255',
            'schedule' => 'nullable',
            'class_trainers' => 'nullable|array',
            'class_trainers.*.id' => 'nullable|integer|exists:sw_gym_pt_class_trainers,id',
            'class_trainers.*.trainer_id' => 'nullable|integer|exists:sw_gym_pt_trainers,id',
            'class_trainers.*.session_count' => 'nullable|integer|min:0',
            'class_trainers.*.commission_rate' => 'nullable|numeric|min:0|max:100',
            'class_trainers.*.session_type' => 'nullable|string|max:50',
            'class_trainers.*.is_active' => 'nullable|boolean',
            'class_trainers.*.schedule' => 'nullable',
            'class_trainers.*.date_from' => 'nullable|date',
            'class_trainers.*.date_to' => 'nullable|date',
        ];
    }
}


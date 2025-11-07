<?php

namespace Modules\Software\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GymTrainingPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'type' => 'required|integer|in:1,2',
            'content' => 'required|string',
            'subscription_category_id' => 'nullable|integer',
        ];
    }
}

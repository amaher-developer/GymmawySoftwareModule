<?php

namespace Modules\Software\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GymUserTransactionRequest extends FormRequest
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
        $rules = [
            'employee_id' => 'required|exists:sw_gym_users,id',
            'transaction_type' => 'required|in:monthly_salary,commission_private_training,commission_subscription_sales,bonus,advance,penalty_deduction',
            'amount' => 'required|numeric|min:0',
            'financial_month' => 'required|date_format:Y-m',
            'notes' => 'nullable|string',
            'deduction_month' => 'nullable|date_format:Y-m',
            'payment_type' => 'required|exists:sw_gym_payment_types,payment_id',
        ];

        // Make deduction_month required if transaction_type is advance
        if ($this->transaction_type == 'advance') {
            $rules['deduction_month'] = 'required|date_format:Y-m';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'employee_id.required' => trans('sw.employee_required'),
            'employee_id.exists' => trans('sw.employee_not_found'),
            'transaction_type.required' => trans('sw.transaction_type_required'),
            'amount.required' => trans('sw.amount_required'),
            'amount.numeric' => trans('sw.amount_must_be_numeric'),
            'amount.min' => trans('sw.amount_must_be_positive'),
            'financial_month.required' => trans('sw.financial_month_required'),
            'financial_month.date_format' => trans('sw.invalid_date_format'),
            'deduction_month.required' => trans('sw.deduction_month_required_for_advance'),
            'deduction_month.date_format' => trans('sw.invalid_date_format'),
            'payment_type.required' => trans('sw.payment_type_required'),
            'payment_type.exists' => trans('sw.payment_type_invalid'),
        ];
    }
}



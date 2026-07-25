<?php

namespace Modules\Software\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GymSwCreateSalesInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id' => 'nullable|integer',
            'subtotal'  => 'required|numeric|min:0',
            'vat_rate'  => 'nullable|numeric|min:0|max:100',
            'notes'     => 'nullable|string',
            'issued_at' => 'nullable|date',
            'due_at'    => 'nullable|date',
        ];
    }
}

<?php

namespace Modules\Software\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GymSwCreateCreditNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'original_invoice_id' => 'required|exists:gym_sw_invoices,id',
            'subtotal'            => 'required|numeric|min:0',
            'notes'               => 'nullable|string',
        ];
    }
}

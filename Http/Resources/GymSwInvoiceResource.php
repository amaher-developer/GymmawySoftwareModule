<?php

namespace Modules\Software\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class GymSwInvoiceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                   => $this->id,
            'type'                 => $this->type,
            'invoice_number'       => $this->invoice_number,
            'prefix'               => $this->prefix,
            'sequence_number'      => $this->sequence_number,
            'branch_setting_id'    => $this->branch_setting_id,
            'member_id'            => $this->member_id,
            'supplier_id'          => $this->supplier_id,
            'reference_invoice_id' => $this->reference_invoice_id,

            'original_invoice_number' => $this->when(
                $this->type === 'credit_note' && $this->relationLoaded('originalInvoice'),
                fn () => optional($this->originalInvoice)->invoice_number
            ),

            'subtotal'         => (float) $this->subtotal,
            'vat_rate'         => (float) $this->vat_rate,
            'vat_amount'       => (float) $this->vat_amount,
            'total'            => (float) $this->total,
            'amount_paid'      => (float) $this->amount_paid,
            'amount_remaining' => $this->amount_remaining,

            'status'   => $this->status,
            'pdf_path' => $this->pdf_path
                ? url(Storage::url($this->pdf_path))
                : null,

            'notes'     => $this->notes,
            'issued_at' => $this->issued_at?->format('Y-m-d H:i:s'),
            'due_at'    => $this->due_at?->format('Y-m-d H:i:s'),

            'money_boxes_count' => $this->when(
                $this->relationLoaded('moneyBoxes'),
                fn () => $this->moneyBoxes->count()
            ),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}

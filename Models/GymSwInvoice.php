<?php

namespace Modules\Software\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Billing\Models\SwBillingInvoice;
use Modules\Generic\Models\GenericModel;

class GymSwInvoice extends GenericModel
{
    use SoftDeletes;

    protected $table = 'gym_sw_invoices';

    protected $fillable = [
        'type',
        'invoice_number',
        'sequence_number',
        'prefix',
        'branch_setting_id',
        'member_id',
        'supplier_id',
        'reference_invoice_id',
        'subtotal',
        'vat_rate',
        'vat_amount',
        'total',
        'amount_paid',
        'pdf_path',
        'zatca_billing_invoice_id',
        'status',
        'notes',
        'issued_at',
        'due_at',
    ];

    protected $casts = [
        'subtotal'    => 'decimal:2',
        'vat_rate'    => 'decimal:2',
        'vat_amount'  => 'decimal:2',
        'total'       => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'issued_at'   => 'datetime',
        'due_at'      => 'datetime',
        'status'      => 'string',
        'type'        => 'string',
    ];

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }

    /** Amount still outstanding. */
    public function getAmountRemainingAttribute(): float
    {
        return round((float) $this->total - (float) $this->amount_paid, 2);
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function member()
    {
        return $this->belongsTo(\Modules\Software\Models\GymMember::class, 'member_id');
    }

    public function zatcaBillingInvoice()
    {
        return $this->belongsTo(SwBillingInvoice::class, 'zatca_billing_invoice_id');
    }

    public function moneyBoxes()
    {
        return $this->hasMany(GymMoneyBox::class, 'invoice_id');
    }

    public function statusLogs()
    {
        return $this->hasMany(GymSwInvoiceStatusLog::class, 'invoice_id');
    }

    public function creditNotes()
    {
        return $this->hasMany(GymSwInvoice::class, 'reference_invoice_id');
    }

    public function originalInvoice()
    {
        return $this->belongsTo(GymSwInvoice::class, 'reference_invoice_id');
    }

    // ── Static helpers ───────────────────────────────────────────────────────

    public static function generateNumber(string $prefix, int $seq): string
    {
        return $prefix . '-' . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    public static function calculateVat(float $subtotal, float $rate = 14.00): array
    {
        $vatAmount = round($subtotal * ($rate / 100), 2);
        $total     = round($subtotal + $vatAmount, 2);

        return [
            'subtotal'   => round($subtotal, 2),
            'vat_amount' => $vatAmount,
            'total'      => $total,
        ];
    }
}

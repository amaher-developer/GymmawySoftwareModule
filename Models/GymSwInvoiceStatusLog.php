<?php

namespace Modules\Software\Models;

use Illuminate\Database\Eloquent\Model;

class GymSwInvoiceStatusLog extends Model
{
    protected $table = 'gym_sw_invoice_status_logs';

    const UPDATED_AT = null;

    protected $fillable = [
        'invoice_id',
        'from_status',
        'to_status',
        'changed_by',
        'notes',
    ];

    public function invoice()
    {
        return $this->belongsTo(GymSwInvoice::class, 'invoice_id');
    }
}

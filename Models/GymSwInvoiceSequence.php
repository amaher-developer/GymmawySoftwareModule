<?php

namespace Modules\Software\Models;

use Illuminate\Support\Facades\DB;
use Modules\Generic\Models\GenericModel;

class GymSwInvoiceSequence extends GenericModel
{
    protected $table = 'gym_sw_invoice_sequences';

    protected $fillable = ['type', 'prefix', 'last_number'];

    /**
     * Atomically increment and return the next sequence number for a given type.
     * MUST be called inside a DB::transaction().
     *
     * @param  string  $type  'sales' | 'purchase' | 'credit_note'
     * @return int
     */
    public static function nextFor(string $type): int
    {
        $row = DB::table('gym_sw_invoice_sequences')
            ->where('type', $type)
            ->lockForUpdate()
            ->first();

        $next = $row->last_number + 1;

        DB::table('gym_sw_invoice_sequences')
            ->where('type', $type)
            ->update(['last_number' => $next, 'updated_at' => now()]);

        return $next;
    }
}

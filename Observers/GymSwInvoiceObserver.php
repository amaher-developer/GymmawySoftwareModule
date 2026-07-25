<?php

namespace Modules\Software\Observers;

use Illuminate\Support\Facades\Auth;
use Modules\Software\Models\GymSwInvoice;
use Modules\Software\Models\GymSwInvoiceStatusLog;

class GymSwInvoiceObserver
{
    /**
     * Record a status transition before the model is saved.
     * Fires only when the `status` attribute is dirty (changed).
     */
    public function updating(GymSwInvoice $invoice): void
    {
        if (! $invoice->isDirty('status')) {
            return;
        }

        GymSwInvoiceStatusLog::create([
            'invoice_id'  => $invoice->id,
            'from_status' => $invoice->getOriginal('status'),
            'to_status'   => $invoice->status,
            'changed_by'  => Auth::guard('sw')->id(),
            'notes'       => null,
        ]);
    }
}

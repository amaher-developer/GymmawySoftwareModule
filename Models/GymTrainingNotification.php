<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymTrainingNotification extends GenericModel
{
    use SoftDeletes;
    /**
     * NOTE: This table does NOT have branch_setting_id or tenant_id columns
     * Tenant isolation through member relationship (GymMember has branch_setting_id)
     *
     * Always query through the member relationship to ensure proper tenant isolation.
     */

    
    protected $table = 'sw_gym_training_notifications';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    /**
     * Get the member
     */
    public function member()
    {
        return $this->belongsTo(GymMember::class, 'member_id');
    }

    /**
     * Get the user
     */
    public function user()
    {
        return $this->belongsTo(GymUser::class, 'user_id');
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 0);
    }
}



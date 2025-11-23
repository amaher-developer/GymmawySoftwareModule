<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymTrainingNotification extends GenericModel
{
    use SoftDeletes;
    
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



<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymTrainingMemberLog extends GenericModel
{
    use SoftDeletes;
    
    protected $table = 'sw_gym_training_member_logs';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'branch_setting_id',
        'member_id',
        'training_id',
        'training_type',
        'action',
        'notes',
        'reference_id',
        'meta',
        'created_by',
    ];

    /**
     * Get the member
     */
    public function member()
    {
        return $this->belongsTo(GymMember::class, 'member_id');
    }

    /**
     * Get the user who created the log
     */
    public function creator()
    {
        return $this->belongsTo(GymUser::class, 'created_by');
    }

    /**
     * Scope for branch filtering
     */
    public function scopeBranch($query)
    {
        $user_sw = auth('sw')->user();
        return $query->where('branch_setting_id', $user_sw->branch_setting_id ?? 1);
    }
}


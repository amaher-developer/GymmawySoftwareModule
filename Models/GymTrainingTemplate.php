<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymTrainingTemplate extends GenericModel
{
    use SoftDeletes;
    
    protected $table = 'sw_gym_training_templates';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    protected $casts = [
        'content' => 'array',
        'is_public' => 'boolean',
    ];

    /**
     * Get the creator
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
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }
}


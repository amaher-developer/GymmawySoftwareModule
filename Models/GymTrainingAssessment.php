<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymTrainingAssessment extends GenericModel
{
    use SoftDeletes;
    
    protected $table = 'sw_gym_training_assessments';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    protected $casts = [
        'answers' => 'array',
    ];

    /**
     * Get the member that owns the assessment
     */
    public function member()
    {
        return $this->belongsTo(GymMember::class, 'member_id');
    }

    /**
     * Get the trainer that created the assessment
     */
    public function trainer()
    {
        return $this->belongsTo(GymUser::class, 'trainer_id');
    }

    /**
     * Scope for branch filtering
     */
    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }
}



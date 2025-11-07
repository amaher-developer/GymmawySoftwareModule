<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymTrainingFeedback extends GenericModel
{
    use SoftDeletes;
    
    protected $table = 'sw_gym_training_feedback';
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
     * Get the plan
     */
    public function plan()
    {
        return $this->belongsTo(GymTrainingPlan::class, 'plan_id');
    }

    /**
     * Get the user (trainer or member)
     */
    public function user()
    {
        return $this->belongsTo(GymUser::class, 'user_id');
    }
}


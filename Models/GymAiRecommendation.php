<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymAiRecommendation extends GenericModel
{
    use SoftDeletes;
    /**
     * NOTE: This table does NOT have branch_setting_id or tenant_id columns
     * Tenant isolation through member relationship (GymMember has branch_setting_id)
     *
     * Always query through the member relationship to ensure proper tenant isolation.
     */

    
    protected $table = 'sw_ai_recommendations';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    protected $casts = [
        'context_data' => 'array',
        'ai_response' => 'array',
    ];

    /**
     * Get the member
     */
    public function member()
    {
        return $this->belongsTo(GymMember::class, 'member_id');
    }

    /**
     * Get the trainer
     */
    public function trainer()
    {
        return $this->belongsTo(GymUser::class, 'trainer_id');
    }
}



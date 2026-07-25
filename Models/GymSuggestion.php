<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

class GymSuggestion extends GenericModel
{
    protected $table = 'sw_gym_suggestions';
    protected $guarded = ['id'];

    public function member()
    {
        return $this->belongsTo(GymMember::class, 'member_id');
    }
}

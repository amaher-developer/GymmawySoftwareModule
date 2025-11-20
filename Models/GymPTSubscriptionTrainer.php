<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

class GymPTSubscriptionTrainer extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_pt_subscription_trainer';
    protected $guarded = ['id'];
    protected $appends = [];
    protected $casts = ['reservation_details' => 'json'];
    public static $uploads_path='uploads/subscriptions/';
    public static $thumbnails_uploads_path='uploads/subscriptions/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }
    public function pt_trainer()
    {
        return $this->belongsTo(GymPTTrainer::class, 'pt_trainer_id');
    }
    public function pt_class()
    {
        return $this->belongsTo(GymPTClass::class, 'pt_class_id');
    }

    public function toArray()
    {
        return parent::toArray();
        $to_array_attributes = [];
        foreach ($this->relations as $key => $relation) {
            $to_array_attributes[$key] = $relation;
        }
        foreach ($this->appends as $key => $append) {
            $to_array_attributes[$key] = $append;
        }
        return $to_array_attributes;
    }

}

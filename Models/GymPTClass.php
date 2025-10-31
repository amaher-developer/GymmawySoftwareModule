<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;

class GymPTClass extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_pt_classes';
    protected $guarded = ['id'];
    protected $casts = [
        'is_system' => 'boolean',
        'reservation_details' => 'json',
    ];

    public function scopeIsSystem($query)
    {
        return $query->where('is_system', true);
    }
    protected $appends = ['name'];

    public static $uploads_path='uploads/classes/';
    public static $thumbnails_uploads_path='uploads/classes/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1);
    }

    public function getNameAttribute()
    {
        $lang = 'name_'. $this->lang;
        return $this->$lang;
    }
    public function getContentAttribute()
    {
        $lang = 'content_'. $this->lang;
        return $this->$lang;
    }

    public function pt_subscription()
    {
        return $this->belongsTo(GymPTSubscription::class, 'pt_subscription_id');
    }

    public function pt_members()
    {
        return $this->hasMany(GymPTMember::class, 'pt_class_id');
    }
    public function pt_subscription_trainer()
    {
        return $this->hasMany(GymPTSubscriptionTrainer::class, 'pt_class_id');
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

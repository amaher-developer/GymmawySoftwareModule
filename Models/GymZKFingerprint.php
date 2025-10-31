<?php

namespace Modules\Software\Models;
use Modules\Generic\Models\GenericModel;
use Illuminate\Database\Eloquent\Model;

class GymZKFingerprint extends GenericModel
{

//    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_zk_fingerprints';
    protected $guarded = ['id'];
    protected $appends = [];
    protected $casts = ['details' => 'json'];

    public static $uploads_path='uploads/users/';
    public static $thumbnails_uploads_path='uploads/users/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1);
    }
    public function user(){
        return $this->belongsTo(GymUser::class, 'user_id');
    }
    public function member(){
        return $this->belongsTo(GymMember::class, 'member_id');
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

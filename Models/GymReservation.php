<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymReservation extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_reservations';
    protected $guarded = ['id'];
    protected $appends = [];
    public static $uploads_path='uploads/reservations/';
    public static $thumbnails_uploads_path='uploads/reservations/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1);
    }
    public function member()
    {
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

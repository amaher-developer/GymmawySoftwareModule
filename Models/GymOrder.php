<?php

namespace Modules\Software\Models;

use App\Modules\Access\Models\User;
use Modules\Generic\Models\GenericModel;
use DateTime;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymOrder extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_orders';
    protected $guarded = ['id'];
    protected $appends = ['duration'];
    protected $casts = ['details' => 'json'];
    public static $uploads_path='uploads/gymorders/';
    public static $thumbnails_uploads_path='uploads/gymorders/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }
    public function user()
    {
        return $this->belongsTo(GymUser::class, 'user_id');
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


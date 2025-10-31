<?php

namespace Modules\Software\Models;

use App\Modules\Access\Models\User;
use Modules\Generic\Models\GenericModel;
use Modules\Software\Events\UserEvent;
use Illuminate\Database\Eloquent\Model;

class GymUserNotification extends GenericModel
{

//    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_user_notifications';
    protected $guarded = ['id'];
    protected $appends = [];
    public static $uploads_path='uploads/users/';
    public static $thumbnails_uploads_path='uploads/users/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1);
    }
    public function user(){
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

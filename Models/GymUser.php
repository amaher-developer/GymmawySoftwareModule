<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\HasApiTokens;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class GymUser extends Authenticatable
{
    use Notifiable;

//    use EntrustUserTrait;
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_users';
    protected $guarded = ['id'];
    protected $appends = ['image_thumbnail', 'start_time_work', 'end_time_work'];
    public static $uploads_path = 'uploads/users/';
    public static $thumbnails_uploads_path = 'uploads/users/thumbnails/';
    protected $casts = [
        'permissions' => 'json',
    ];
    
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Apply global scope to ALL queries for tenant isolation
     * This prevents IDOR (Insecure Direct Object Reference) attacks
     */
    // public static function booted()
    // {
    //     static::addGlobalScope('branch', function ($query) {
    //         $branchId = GenericModel::getCurrentBranchId();
    //         $query->where('branch_setting_id', $branchId);
    //     });
    // }

    /**
     * Manual branch and tenant scope
     * Filters by branch_setting_id and optionally tenant_id
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $branchId - Default: 1
     * @param int $tenantId - Default: 1
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBranch($query, $branchId = 1, $tenantId = 1)
    {
        $query->where('branch_setting_id', $branchId);

        // Only filter by tenant_id if the column exists in the table
        if (Schema::hasColumn($this->getTable(), 'tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        return $query;
    }

    // Commented out to avoid double hashing - passwords should be hashed before being saved
    // public function setPasswordAttribute($value)
    // {
    //     $this->attributes['password'] = Hash::make($value);
    // }

    public function getStartTimeWorkAttribute($time)
    {
        return Carbon::parse($time)->format('h:i a');
    }

    public function getEndTimeWorkAttribute($time)
    {
        return Carbon::parse($time)->format('h:i a');
    }

    public function getImageAttribute()
    {
        $image = $this->getRawOriginal('image');
        if ($image)
            return asset(self::$uploads_path . $image);

        return asset('uploads/settings/default.jpg');
    }


    public function getImageThumbnailAttribute()
    {
        if ($this->image)
            return str_replace(self::$uploads_path, self::$thumbnails_uploads_path, $this->image);
        return asset('uploads/settings/default.jpg');
    }

    public function user_zk_fingerprints()
    {
        return $this->hasMany(GymZKFingerprint::class,'user_id');
    }
    public function user_attendees()
    {
        return $this->hasMany(GymUserAttendee::class,'user_id');
    }

    public function user_zk_fingerprint(){
        return $this->hasOne(GymZKFingerprint::class, 'user_id')->orderBy('id', 'desc');
    }
    
    public function permission_group(){
        return $this->belongsTo(GymUserPermission::class, 'permission_group_id');
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


<?php

namespace Modules\Software\Models;

use Modules\Generic\Models\GenericModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymNonMember extends GenericModel
{

    protected $dates = ['deleted_at'];

    protected $table = 'sw_gym_non_members';
    protected $guarded = ['id'];
    protected $appends = [];
    protected $casts = ['activities' => 'json', 'contract_files' => 'json'];
    public static $uploads_path='uploads/nonmembers/';
    public static $thumbnails_uploads_path='uploads/nonmembers/thumbnails/';


    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }
    public function non_member_times()
    {
        return $this->hasMany(GymNonMemberTime::class, 'non_member_id');
    }

    /**
     * Get the ZATCA invoice associated with the non-member purchase.
     */
    public function zatcaInvoice()
    {
        return $this->hasOne(\Modules\Billing\Models\SwBillingInvoice::class, 'non_member_id');
    }
    public function getSignatureFileAttribute()
    {
        $signature_file = $this->getRawOriginal('signature_file');
        if($signature_file)
            return asset(GymOrder::$uploads_path.$signature_file);

        return null;
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


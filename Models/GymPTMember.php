<?php

namespace Modules\Software\Models;

use Carbon\Carbon;
use Modules\Billing\Models\SwBillingInvoice;
use Modules\Generic\Models\GenericModel;
use Modules\Software\Classes\TypeConstants;

class GymPTMember extends GenericModel
{
    protected $table = 'sw_gym_pt_members';

    protected $guarded = ['id'];

    protected $appends = ['status_name', 'status', 'sessions_total', 'sessions_used', 'sessions_remaining'];

    protected $casts = [
        'contract_files' => 'json',
        'start_date' => 'date',
        'end_date' => 'date',
        'joining_date' => 'datetime',
        'expire_date' => 'datetime',
        'is_active' => 'boolean',
        'total_sessions' => 'integer',
        'remaining_sessions' => 'integer',
        'discount' => 'float',
        'paid_amount' => 'float',
    ];

    public static $uploads_path = 'uploads/members/';
    public static $thumbnails_uploads_path = 'uploads/members/thumbnails/';

    public function scopeBranch($query)
    {
        return $query->where('branch_setting_id', parent::getCurrentBranchId());
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function member()
    {
        return $this->belongsTo(GymMember::class, 'member_id');
    }

    public function subscription()
    {
        return $this->belongsTo(GymPTSubscription::class, 'pt_subscription_id');
    }

    public function pt_subscription()
    {
        return $this->subscription();
    }

    public function class()
    {
        return $this->belongsTo(GymPTClass::class, 'class_id');
    }

    public function legacyClass()
    {
        return $this->belongsTo(GymPTClass::class, 'pt_class_id');
    }

    public function pt_class()
    {
        return $this->legacyClass();
    }

    public function getResolvedClassAttribute()
    {
        if ($this->relationLoaded('class') && $this->getRelation('class')) {
            return $this->getRelation('class');
        }

        if ($this->relationLoaded('legacyClass') && $this->getRelation('legacyClass')) {
            return $this->getRelation('legacyClass');
        }

        return $this->class ?? $this->legacyClass;
    }

    public function classTrainer()
    {
        return $this->belongsTo(GymPTClassTrainer::class, 'class_trainer_id');
    }

    public function pt_subscription_trainer()
    {
        return $this->classTrainer();
    }

    public function trainer()
    {
        return $this->belongsTo(GymPTTrainer::class, 'pt_trainer_id');
    }

    public function pt_trainer()
    {
        return $this->trainer();
    }

    public function attendees()
    {
        return $this->hasMany(GymPTMemberAttendee::class, 'pt_member_id');
    }

    public function pt_member_attendees()
    {
        return $this->attendees();
    }

    public function commissions()
    {
        return $this->hasMany(GymPTCommission::class, 'pt_member_id');
    }

    public function moneyBox()
    {
        return $this->hasOne(GymMoneyBox::class, 'member_pt_subscription_id');
    }

    public function zatcaInvoice()
    {
        return $this->hasOne(SwBillingInvoice::class, 'member_pt_subscription_id')->latest('id');
    }

    public function getSignatureFileAttribute()
    {
        $signatureFile = $this->getRawOriginal('signature_file');
        if ($signatureFile) {
            return asset(GymOrder::$uploads_path . $signatureFile);
        }

        return null;
    }

    public function getStatusNameAttribute()
    {
        $status = $this->status;
        if ($status === TypeConstants::Active) {
            return trans('sw.active');
        }

        if ($status === TypeConstants::Coming) {
            return trans('sw.coming');
        }

        return trans('sw.expire');
    }

    public function getStatusAttribute()
    {
        $remaining = $this->remaining_sessions ?? $this->getRawOriginal('remaining_sessions');
        $total = $this->total_sessions ?? $this->getRawOriginal('total_sessions') ?? $this->getRawOriginal('classes');
        $visits = $this->getRawOriginal('visits');

        $hasConsumedAllSessions = false;
        if ($total !== null) {
            if ($remaining !== null) {
                $hasConsumedAllSessions = (int) $remaining <= 0;
            } elseif ($visits !== null) {
                $hasConsumedAllSessions = (int) $visits >= (int) $total;
            }
        }

        $endDate = $this->end_date ?? ($this->expire_date ? Carbon::parse($this->expire_date)->toDateString() : null);
        $startDate = $this->start_date ?? ($this->joining_date ? Carbon::parse($this->joining_date)->toDateString() : null);
        $today = Carbon::now()->toDateString();

        if ($endDate && $endDate < $today) {
            return TypeConstants::Expired;
        }

        if ($hasConsumedAllSessions) {
            return TypeConstants::Expired;
        }

        if ($startDate && $startDate > $today) {
            return TypeConstants::Coming;
        }

        return TypeConstants::Active;
    }

    public function getSessionsTotalAttribute(): ?int
    {
        $total = $this->total_sessions ?? $this->getRawOriginal('total_sessions');
        if ($total !== null) {
            return (int) $total;
        }

        $legacyTotal = $this->getRawOriginal('classes');
        return $legacyTotal !== null ? (int) $legacyTotal : null;
    }

    public function getSessionsUsedAttribute(): int
    {
        $total = $this->sessions_total;
        $remaining = $this->remaining_sessions ?? $this->getRawOriginal('remaining_sessions');

        if ($total !== null && $remaining !== null) {
            return max((int) $total - (int) $remaining, 0);
        }

        $legacyVisits = $this->getRawOriginal('visits');
        if ($legacyVisits !== null) {
            return (int) $legacyVisits;
        }

        return 0;
    }

    public function getSessionsRemainingAttribute(): ?int
    {
        $remaining = $this->remaining_sessions ?? $this->getRawOriginal('remaining_sessions');
        if ($remaining !== null) {
            return (int) $remaining;
        }

        $total = $this->sessions_total;
        if ($total === null) {
            return null;
        }

        return max((int) $total - $this->sessions_used, 0);
    }

    public function toArray()
    {
        return parent::toArray();
    }
}


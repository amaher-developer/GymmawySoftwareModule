<?php

namespace Modules\Software\Http\Resources;

use Modules\Software\Classes\TypeConstants;
use Illuminate\Http\Resources\Json\JsonResource;
use Milon\Barcode\DNS1D;

class MemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $qrcodes_folder = base_path('uploads/barcodes/'.$this->code.'.png');
        $qrcodes_path = asset('uploads/barcodes/'.$this->code.'.png');

        if( $this->code && !file_exists($qrcodes_folder)) {
            $qrcodes_folder = base_path('uploads/barcodes/');
            $d = new DNS1D();
            $d->setStorPath($qrcodes_folder);
            $img = $d->getBarcodePNGPath($this->code, TypeConstants::BarcodeType);
            $qrcodes_path = (asset($img));
        }
        $freeze_check = 0;
        if((@$this->member_subscription_info->number_times_freeze > 0) && (@$this->member_subscription_info->status == TypeConstants::Active)){
            $freeze_check = 1;
        }

        return
            [
                'id' => $this->id,
                'name' => $this->name,
                'phone' => $this->phone,
                'image' => $this->image,
                'invitations' => (int)$this->invitations ?? 0,
                'code_url' => @$qrcodes_path,
                'code' => $this->code,
                'subscription_id' => @$this->member_subscription_info->subscription->id,
                'subscription_name' => @$this->member_subscription_info->subscription->name,
                'amount_paid' => @abs(round(@$this->member_subscription_info->amount_paid, 2)) . ' ' . env('APP_CURRENCY_'.strtoupper($this->lang)) . ' ' ,
                'amount_remaining' => @abs(round(@$this->member_subscription_info->amount_remaining, 2)) . ' ' . env('APP_CURRENCY_'.strtoupper($this->lang)) . ' ' ,
                'joining_date' => @$this->member_subscription_info->joining_date,
                'expire_date' => @$this->member_subscription_info->expire_date,
//                'attendees_count' => (string)count(@$this->member_attendees) ?? "0",//@$this->member_attendees_count ?? 0,
                'attendees_count' => (string)@$this->member_subscription_info->visits ?? "0",//@$this->member_attendees_count ?? 0,
                'membership_status' => @$this->member_subscription_info->status_name ?? trans('sw.active'),
                'freeze_check' => @$freeze_check,
                'attendees' => @$this->member_attendees,
            ];
    }
}



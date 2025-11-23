<?php

namespace Modules\Software\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ZKNewMemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return
            [
                'id' => $this->id,
                'fp_id' => $this->fp_id,
                'name' => $this->name,
                'code' => (int)$this->code,
                'phone' => $this->phone,
                'fp_uid' =>  @$this->code,
                'uid' => @env('APP_ZK_GATE') == true ? @$id = md5((int)$this->code) : (int)$this->code,
                'cardno' => @$this->member_zk_fingerprint->cardno,
                'details' => @$this->member_zk_fingerprint->details,
                'expire_date' => @$this->member_subscription_info->expire_date,
            ];
    }
}



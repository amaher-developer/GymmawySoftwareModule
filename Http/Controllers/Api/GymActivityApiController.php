<?php

namespace Modules\Software\Http\Controllers\Api;

use Modules\Software\Http\Resources\ActivityContentResource;
use Modules\Software\Http\Resources\ActivityResource;
use Modules\Software\Models\GymActivity;
use Modules\Software\Models\GymPotentialMember;

class GymActivityApiController extends GymGenericApiController
{
    public function activities(){
        $activities = GymActivity::orderBy("id", "desc");
        if(@request('device_type'))
            $activities = $activities->where('is_mobile', 1);
        else
            $activities = $activities->where('is_web', 1);
        $activities = $activities->paginate($this->limit);
        $this->getPaginateAttribute($activities);
        $this->return['result']['activities'] =  $activities ?  ActivityResource::collection($activities) : [];
        return $this->successResponse();
    }
    public function activity($id){
        $activity = GymActivity::where("id", $id)->first();
        $activities = GymActivity::where("id", '!=',$id)->where('is_mobile', 1)->limit(4)->get();

        $this->return['result']['activity'] =  $activity ? new ActivityContentResource($activity) : '';
        $this->return['result']['activities'] =  $activities ? ActivityResource::collection($activities) : [];

        return $this->successResponse();
    }
    public function activityReservation($id){
        $member_id = @$this->api_member->id;
        if(!$member_id){
            if (!$this->validateApiRequest(['name', 'phone'])) return $this->response;
        }
        if (!$this->validateApiRequest(['id'])) return $this->response;

        GymPotentialMember::updateOrCreate(
            ['activity_id' => $id,  'name' => @request('name'), 'phone' => @request('phone')]
            ,['activity_id' => $id, 'name' => @request('name'), 'phone' => @request('phone')]
        );

        $this->message = trans('sw.reserved_success_msg');
        return $this->successResponse();
    }
}


<?php

namespace Modules\Software\Http\Controllers\Api;



use Modules\Software\Http\Resources\PTContentResource;
use Modules\Software\Http\Resources\PTResource;
use Modules\Software\Http\Resources\TrainerPlanContentResource;
use Modules\Software\Http\Resources\TrainerTrackContentResource;
use Modules\Software\Http\Resources\TrainingPlanResource;
use Modules\Software\Http\Resources\TrainingTrackResource;
use Modules\Software\Models\GymPotentialMember;
use Modules\Software\Models\GymPTClass;
use Modules\Software\Models\GymTrainingMember;
use Modules\Software\Models\GymTrainingTrack;
use Illuminate\Support\Facades\Auth;

class GymTrainingApiController extends GymGenericApiController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function plans(){
        $type = @request('type');
        $member = @\request()->user();
        $plans = GymTrainingMember::with(['member'])->where('member_id', @$member->id)->when($type, function ($q) use ($type){ $q->where('type', $type);})->orderBy("id", "desc")->paginate($this->limit);
        $this->getPaginateAttribute($plans);
        $this->return['result']['plans'] =  $plans ?  TrainingPlanResource::collection($plans) : [];
        return $this->successResponse();
    }
    public function plan($id){
        $member = @\request()->user();
        if (!$this->validateApiRequest(['id'])) return $this->response;
        $plan = GymTrainingMember::with(['member'])->where("id", $id)->where('member_id', @$member->id)->first();
        $this->return['result']['plan'] =  $plan ? new TrainerPlanContentResource($plan) : '';

        return $this->successResponse();
    }


    public function tracks(){
        $member = @\request()->user();
        $tracks = GymTrainingTrack::with(['member'])->where('member_id', @$member->id)->orderBy("id", "desc")->paginate($this->limit);
        $this->getPaginateAttribute($tracks);
        $this->return['result']['tracks'] =  $tracks ?  TrainingTrackResource::collection($tracks) : [];
        return $this->successResponse();
    }
    public function track($id){
        $member = @\request()->user();
        if (!$this->validateApiRequest(['id'])) return $this->response;
        $track = GymTrainingTrack::with(['member'])->where('member_id', @$member->id)->where("id", $id)->first();
        $this->return['result']['track'] =  $track ? new TrainerTrackContentResource($track) : '';

        return $this->successResponse();
    }

}


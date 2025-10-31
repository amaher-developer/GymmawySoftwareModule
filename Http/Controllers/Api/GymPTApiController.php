<?php

namespace Modules\Software\Http\Controllers\Api;



use Modules\Software\Http\Resources\PTClassResource;
use Modules\Software\Http\Resources\PTContentResource;
use Modules\Software\Http\Resources\PTResource;
use Modules\Software\Models\GymPotentialMember;
use Modules\Software\Models\GymPTClass;
use Modules\Software\Models\GymPTMember;
use Modules\Software\Models\GymPTSubscriptionTrainer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class GymPTApiController extends GymGenericApiController
{
    public function trainings(){
        $trainings = GymPTClass::with(['pt_subscription.pt_trainers']);
//        if(@request('device_type'))
            $trainings = $trainings->where('is_mobile', 1);
//        else
//            $trainings = $trainings->where('is_web', 1);
        $trainings = $trainings->orderBy("id", "desc")->paginate($this->limit);
        $this->getPaginateAttribute($trainings);
        $this->return['result']['trainings'] =  $trainings ?  PTResource::collection($trainings) : [];
        return $this->successResponse();
    }
    public function training($id){
        if (!$this->validateApiRequest(['id'])) return $this->response;
        $training = GymPTClass::with(['pt_subscription', 'pt_subscription_trainer.pt_trainer'])->where("id", $id)->first();
        $this->return['result']['training'] =  $training ? new PTContentResource($training) : '';

        return $this->successResponse();
    }
    public function trainingReservation($id){
        $member_id = @$this->api_member->id;
        if(!$member_id){
            if (!$this->validateApiRequest(['name', 'phone'])) return $this->response;
        }
        if (!$this->validateApiRequest(['id'])) return $this->response;

        $class = GymPTClass::with(['pt_subscription'])->where("id", $id)->first();

        GymPotentialMember::updateOrCreate(
            ['pt_class_id' => $id, 'pt_subscription_id' => @$class->pt_subscription->id, 'name' => @request('name'), 'phone' => @request('phone')]
        ,['pt_class_id' => $id, 'pt_subscription_id' => @$class->pt_subscription->id, 'name' => @request('name'), 'phone' => @request('phone')]
        );

        $this->message = trans('sw.reserved_success_msg');
        return $this->successResponse();
    }
    public function trainingClasses(){
        if (!$this->validateApiRequest(['date'])) return $this->response;
        $date = request('date');
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $records = [];
        $pt_member = GymPTMember::where('member_id', Auth::guard('api')->user()->id)
            ->whereDate('joining_date', '<=', Carbon::parse($date)->toDateString())
            ->whereDate('expire_date', '>=', Carbon::parse($date)->toDateString())
            ->get();
        if($pt_member->count() > 0) {
            $pt_subscription_trainers = GymPTSubscriptionTrainer::with(['pt_trainer', 'pt_class.pt_subscription'])->where('id', '!=', 0);
            $pt_subscription_trainers->where(function ($query) use ($pt_member) {
                foreach ($pt_member as $member) {
                    $query->orWhere(function ($q) use ($member) {
                        $q->where('pt_class_id', $member->pt_class_id)->where('pt_trainer_id', $member->pt_trainer_id);
                    });
                }
            });
//        $pt_subscription_trainers->where(function($query) use ($date) {
//            $query->whereDate('date_from', '<=', Carbon::parse($date)->toDateString())
//                   ->whereDate('date_to', '>=', Carbon::parse($date)->toDateString());
//        });
            $pt_subscription_trainers = $pt_subscription_trainers->get();

            $records = [];
            $i = 0;
            foreach ($pt_subscription_trainers as $pt_subscription_trainer) {
                if (@$pt_subscription_trainer->reservation_details['work_days'][$dayOfWeek]) {
                    $records[$i]['title'] = @($pt_subscription_trainer->pt_class->pt_subscription->name);
                    $records[$i]['trainer_name'] = @($pt_subscription_trainer->pt_trainer->name);
                    $records[$i]['trainer_image'] = @($pt_subscription_trainer->pt_trainer->image);
                    $records[$i]['period'] = Carbon::parse($pt_subscription_trainer->reservation_details['work_days'][$dayOfWeek]['start'])->format('g:i A');//Carbon::parse($pt_subscription_trainer->reservation_details['work_days'][$dayOfWeek]['end'])->diffInHours($pt_subscription_trainer->reservation_details['work_days'][$dayOfWeek]['start']) . ' ' . trans('sw.hour');
                    $records[$i]['date'] = @$date;
                    $i++;
                }
            }
        }

        $this->return['result']['classes'] =  @$records ? $records : [];

        return $this->successResponse();
    }
}

<?php

namespace Modules\Software\Http\Controllers\Api;

use Modules\Software\Http\Controllers\Front\GymNotificationFrontController;
use Modules\Software\Http\Resources\CategoryResource;
use Modules\Software\Http\Resources\SubscriptionContentResource;
use Modules\Software\Http\Resources\SubscriptionResource;
use Modules\Software\Models\GymCategory;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymPotentialMember;
use Modules\Software\Models\GymSubscription;

class GymSubscriptionApiController extends GymGenericApiController
{
    public function subscriptions(){
        $category_id = @request('category_id');
        $categories = GymCategory::where('is_subscription', true)->limit(10)->get();
        $subscriptions = GymSubscription::orderBy("id", "desc");
        if($category_id)
            $subscriptions = $subscriptions->where('category_id', $category_id);
        else
            $subscriptions = $subscriptions->where('category_id', NULL);
//        if(@request('device_type'))
        $subscriptions = $subscriptions->where('is_mobile', 1);
//        else
//            $subscriptions = $subscriptions->where('is_web', 1);
        $subscriptions = $subscriptions->paginate($this->limit);
        $this->getPaginateAttribute($subscriptions);
        $this->return['result']['subscriptions'] =  $subscriptions ?  SubscriptionResource::collection($subscriptions) : [];
        $this->return['result']['categories'] =  $categories ?  CategoryResource::collection($categories) : [];
        return $this->successResponse();
    }
    public function subscription($id){
        $subscription = GymSubscription::with(['activities.activity' => function($q){
            $q->withTrashed();
        }])->where("id", $id)->first();
        $this->return['result']['subscription'] =  $subscription ? new SubscriptionContentResource($subscription) : '';
        return $this->successResponse();
    }
    public function subscriptionReservation($id){
        $member_id = @$this->api_member->id;
        $phone = @request('phone');
        $name = @request('name');
        if(!$member_id){
            if (!$this->validateApiRequest(['name', 'phone'])) return $this->response;
        }else{
            $member = GymMember::where('id', $member_id)->first();
            $phone = $member->phone;
            $name = $member->name;
        }
        if (!$this->validateApiRequest(['id'])) return $this->response;

        $queryUpdate = ['subscription_id' => $id,  'name' => $name, 'phone' => $phone];
        if(@$member) $queryUpdate['member_id'] = $member->id;

        GymPotentialMember::updateOrCreate(
            $queryUpdate ,
            $queryUpdate
        );

        $notify = new GymNotificationFrontController();
        $notify->appToUsers(['title' => trans('sw.app_subscription_short_msg'), 'content'=> trans('sw.app_subscription_msg'), 'url' => route('sw.listPotentialMember')]);

        $this->message = trans('sw.reserved_success_msg');
        return $this->successResponse();
    }
}

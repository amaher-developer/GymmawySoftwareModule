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
use Modules\Software\Services\SubscriptionPricingService;

class GymSubscriptionApiController extends GymGenericApiController
{
    public function subscriptions(){
        $category_id = @request('category_id');
        $categories = GymCategory::branch()->where('is_subscription', true)->limit(10)->get();
        $subscriptions = GymSubscription::branch()->orderBy("id", "desc");
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
        $subscription = GymSubscription::branch()->with(['activities.activity' => function($q){
            $q->withTrashed();
        }])->where("id", $id)->first();
        $this->return['result']['subscription'] =  $subscription ? new SubscriptionContentResource($subscription) : '';
        return $this->successResponse();
    }
    /**
     * POST /api/subscription/{id}/calculate-price
     * Body: { option_ids: [1, 3, 5] }
     */
    public function calculatePrice($id)
    {
        $subscription = GymSubscription::branch()->where('id', $id)->first();

        if (!$subscription) {
            return $this->falseResponse('subscription not found');
        }

        $optionIds = array_values(array_filter(array_map('intval', (array) request('option_ids', []))));
        $pricing   = (new SubscriptionPricingService())->calculate($subscription, $optionIds);

        $setting      = \Modules\Generic\Models\Setting::select('vat_details')->first();
        $vatPct       = (float) @$setting->vat_details['vat_percentage'];
        $currency     = env('APP_CURRENCY_' . strtoupper($this->lang));
        $total        = $pricing['total'];
        $totalWithVat = round($total + ($total * $vatPct / 100), 2);

        $this->return['result'] = [
            'base_price'       => $pricing['base_price'],
            'options_total'    => $pricing['options_total'],
            'total'            => $total,
            'vat_percentage'   => $vatPct,
            'total_with_vat'   => $totalWithVat,
            'currency'         => $currency,
            'selected_options' => $pricing['selected_options'],
        ];

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

        $subscription = GymSubscription::where('id', $id)->first();

        $queryUpdate = ['subscription_id' => $id,  'name' => $name, 'phone' => $phone];
        if(@$member) $queryUpdate['member_id'] = $member->id;

        GymPotentialMember::updateOrCreate(
            $queryUpdate ,
            $queryUpdate
        );

        GymNotificationFrontController::pushNotificationAsync([
            'title'             => trans('sw.app_subscription_short_msg'),
            'content'           => trans('sw.app_subscription_msg'),
            'url'               => route('sw.listPotentialMember'),
            'branch_setting_id' => @$subscription->branch_setting_id,
        ]);

        $this->message = trans('sw.reserved_success_msg');
        return $this->successResponse();
    }
}


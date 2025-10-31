<?php

namespace Modules\Software\Http\Controllers\Api;

use Modules\Software\Http\Resources\StoreContentResource;
use Modules\Software\Http\Resources\StoreResource;
use Modules\Software\Models\GymStoreProduct;

class GymStoreApiController extends GymGenericApiController
{

    public function stores(){
        $stores = GymStoreProduct::orderBy("id", "desc");
        if(@request('device_type'))
            $stores = $stores->where('is_mobile', 1);
        else
            $stores = $stores->where('is_web', 1);
        $stores = $stores->paginate($this->limit);
        $this->getPaginateAttribute($stores);
        $this->return['result']['stores'] =  $stores ?  StoreResource::collection($stores) : [];
        return $this->successResponse();
    }
    public function store($id){
        $store = GymStoreProduct::where("id", $id)->first();
        $stores = GymStoreProduct::where("id", '!=', $id)->limit(5)->get();
        $this->return['result']['store'] =  $store ? new StoreContentResource($store) : '';
        $this->return['result']['stores'] =  $store ?  StoreResource::collection($stores) : [];
        return $this->successResponse();
    }
}

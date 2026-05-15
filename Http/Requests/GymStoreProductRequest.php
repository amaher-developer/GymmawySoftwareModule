<?php

namespace Modules\Software\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Generic\Models\GenericModel;
use Modules\Software\Models\GymStoreProduct;

class GymStoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $checkDeleteCode = GymStoreProduct::where('code', request('code'))->onlyTrashed()->first();
        if($checkDeleteCode){
            $checkDeleteCode->code = $checkDeleteCode->code.'-deleted';
            $checkDeleteCode->save();
        }


        $productId = $this->route('product')
            ?? $this->route('store_product')
            ?? $this->route('storeProduct')
            ?? $this->route('id')
            ?? request('product_id')
            ?? request('id');

        $branchId = GenericModel::getCurrentBranchId();

        return [
            'name_ar' => 'required',
            'name_en' => 'required',
            'price' => 'required',
            'sku' => 'nullable|string|max:191',
            'code' => [
                'required',
                'string',
                'max:191',
                Rule::unique('sw_gym_store_products', 'code')
                    ->ignore($productId ? intval($productId) : null)
                    ->where(function ($query) use ($branchId) {
                        return $query->where('branch_setting_id', $branchId);
                    }),
            ],
//            'quantity' => 'required',
            'image' => 'mimes:jpeg,jpg,png,gif|max:500',
            'content_ar' => 'max:250',
            'content_en' => 'max:250',
        ];
    }
}


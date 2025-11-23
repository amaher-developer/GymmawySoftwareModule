@extends('software::layouts.form')
@section('breadcrumb')
    <!--begin::Breadcrumb-->
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
        <!--begin::Item-->
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.dashboard') }}" class="text-muted text-hover-primary">{{ trans('sw.home') }}</a>
        </li>
        <!--end::Item-->
        <!--begin::Item-->
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-300 w-5px h-2px"></span>
        </li>
        <!--end::Item-->
        <!--begin::Item-->
        <li class="breadcrumb-item text-gray-900">{{ $title }}</li>
        <!--end::Item-->
    </ul>
    <!--end::Breadcrumb-->
@endsection
@section('form_title') {{ @$title }} @endsection
@section('styles')
    <style>
        .code-input {
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            border: 2px dashed rgba(0, 0, 0, 0.1);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .code-input:focus {
            border-color: rgba(13, 110, 253, 0.45);
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
    </style>
@endsection
@section('page_body')
    @php
        $productScannerIndex = isset($products)
            ? $products->mapWithKeys(function ($product) {
                $code = (string) $product->code;
                $normalized = ltrim($code, '0');
                $padded = str_pad($normalized !== '' ? $normalized : $code, 14, '0', STR_PAD_LEFT);
                $keys = array_unique(array_filter([
                    $code,
                    $normalized !== '' ? $normalized : null,
                    $padded,
                    strtoupper($code),
                    strtoupper($normalized),
                    strtoupper($padded),
                ]));

                $map = [];
                foreach ($keys as $key) {
                    $map[$key] = $code;
                }
                return $map;
            })
            : collect();
    @endphp
    <!--begin::Store Product Form-->
    <form method="post" action="" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
        {{csrf_field()}}
        
        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!--begin::Product Details-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{$title}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.name_in_arabic')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="name_ar" class="form-control mb-2" 
                               placeholder="{{ trans('sw.enter_name_in_arabic')}}" 
                               value="{{ old('name_ar', $product->name_ar) }}" 
                               id="name_ar" required />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.name_in_english')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="name_en" class="form-control mb-2" 
                               placeholder="{{ trans('sw.enter_name_in_english')}}" 
                               value="{{ old('name_en', $product->name_en) }}" 
                               id="name_en" required />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.price')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="number" name="price" class="form-control mb-2" 
                               placeholder="{{ trans('sw.enter_price')}}" 
                               value="{{ old('price', $product->price) }}" 
                               id="price" step="0.01" required />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->

                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <label class="form-label">{{ trans('sw.sku') }}</label>
                        <input type="text" name="sku" class="form-control mb-2"
                               placeholder="{{ trans('sw.sku')}}"
                               value="{{ old('sku', $product->sku) }}" />
                    </div>
                    <!--end::Input group-->

                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <label class="required form-label">{{ trans('sw.code') }}</label>
                        <div class="position-relative">
                            <input type="text" name="code" class="form-control mb-2 code-input"
                                   placeholder="{{ trans('sw.code')}}"
                                   value="{{ old('code', $product->code) }}" required/>
                            <div class="form-text text-muted">{{ trans('sw.code_hint') }}</div>
                        </div>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.store_category')}}</label>
                        <!--end::Label-->
                        <!--begin::Select-->
                        <select name="store_category_id" id="store_category_id" class="form-select form-select-solid" data-control="select2" data-placeholder="{{ trans('sw.select_store_category')}}" data-allow-clear="true">
                            <option value="">{{ trans('sw.select_store_category')}}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                    @if(old('store_category_id', $product->store_category_id ?? $product->category_id) == $category->id) selected @endif>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <!--end::Select-->
                    </div>
                    <!--end::Input group-->
                    
                    @if(@$mainSettings->active_mobile)
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.upload_image')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input id="gym_image" name="image" type="file" class="form-control mb-2">
                        <label for="gym_image" style="cursor: pointer;">
                            <img id="preview" @if($product->image) src="{{$product->image}}"
                                 @else src="https://gymmawy.com/resources/assets/new_front/img/blank-image.svg" @endif
                                 style="height: 120px;width: 120px;object-fit: contain;border: 1px solid #c2cad8;"
                                 alt="preview image"/>
                        </label>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.content_in_arabic')}}</label>
                        <!--end::Label-->
                        <!--begin::Textarea-->
                        <textarea id="content_ar" maxlength="250"
                                  placeholder="{{ trans('sw.enter_content_in_arabic')}}"
                                  name="content_ar" type="text"
                                  class="form-control mb-2">{{ old('content_ar', $product->content_ar) }}</textarea>
                        <!--end::Textarea-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.content_in_english')}}</label>
                        <!--end::Label-->
                        <!--begin::Textarea-->
                        <textarea id="content_en" maxlength="250"
                                  placeholder="{{ trans('sw.enter_content_in_english')}}"
                                  name="content_en" type="text"
                                  class="form-control mb-2">{{ old('content_en', $product->content_en) }}</textarea>
                        <!--end::Textarea-->
                    </div>
                    <!--end::Input group-->
                    @endif
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Product Details-->



            @if(\Request::route()->getName() == 'sw.createStoreProduct')
                <!--begin::Purchase Bill Details-->
                <div class="card card-flush py-4">
                    <!--begin::Card header-->
                    <div class="card-header">
                        <div class="card-title">
                            <div class="d-flex align-items-center">
                                <i class="ki-outline ki-shopping-cart fs-2 me-3"></i>
                                <h3 class="fw-bold">{{ trans('sw.purchases_bill')}}</h3>
                            </div>
                        </div>
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="form-label">{{ trans('sw.quantity')}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input id="quantity" value="{{ old('quantity', $product->quantity) }}"
                                   placeholder="{{ trans('sw.enter_quantity')}}"
                                   name="quantity" type="number" class="form-control mb-2">
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                        
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="form-label">{{ trans('sw.vendor_name')}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input id="vendor_name" value="{{ old('vendor_name', $product->vendor_name) }}"
                                   placeholder="{{ trans('sw.enter_vendor_name')}}"
                                   name="vendor_name" type="text" class="form-control mb-2">
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                        
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="form-label">{{ trans('sw.vendor_phone')}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input id="vendor_phone" value="{{ old('vendor_phone', $product->vendor_phone) }}"
                                   placeholder="{{ trans('sw.enter_vendor_phone')}}"
                                   name="vendor_phone" type="text" class="form-control mb-2">
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                        
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="form-label">{{ trans('sw.vendor_address')}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input id="vendor_address" value="{{ old('vendor_address', $product->vendor_address) }}"
                                   placeholder="{{ trans('sw.enter_vendor_address')}}"
                                   name="vendor_address" type="text" class="form-control mb-2">
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                        
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="form-label">{{ trans('sw.amount_paid')}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input name="vendor_amount" class="form-control mb-2" type="number" id="vendor_amount"
                                   step="0.01" placeholder="{{ trans('sw.enter_amount_paid')}}">
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                        
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="form-label">{{ trans('sw.payment_type')}}</label>
                            <!--end::Label-->
                            <!--begin::Select-->
                            <select class="form-select form-select-solid mb-2" name="vendor_payment_type" id="vendor_payment_type">
                                @foreach($payment_types as $payment_type)
                                    <option value="{{$payment_type->payment_id}}"
                                            @if(@old('payment_type',$order->payment_type) == $payment_type->payment_id) selected="" @endif>{{$payment_type->name}}</option>
                                @endforeach
                            </select>
                            <!--end::Select-->
                        </div>
                        <!--end::Input group-->
                        
                        @if(@$mainSettings->vat_details['vat_percentage'])
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="form-check form-check-custom form-check-solid">
                                <input name="vendor_is_vat" class="form-check-input" type="checkbox" value="1"
                                       id="vendor_is_vat" checked />
                                <span class="form-check-label fw-semibold text-gray-700">{{ trans('sw.including_vat')}}</span>
                            </label>
                            <!--end::Label-->
                        </div>
                        <!--end::Input group-->
                        @endif
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Purchase Bill Details-->
            @endif

            @if(@$mainSettings->active_mobile)
                <!--begin::Visibility Settings-->
                <div class="card card-flush py-4">
                    <!--begin::Card header-->
                    <div class="card-header">
                        <div class="card-title">
                            <h3 class="fw-bold">{{ trans('sw.visible_invisible')}}</h3>
                        </div>
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row">
                            <div class="d-flex flex-wrap gap-5">
                                <!--begin::Checkbox is_system first, default checked -->
                                <label class="form-check form-check-custom form-check-solid">
                                    <input type="checkbox" id="inlineCheckbox23" name="is_system" value="1" 
                                           class="form-check-input" @if(old('is_system', @$product->is_system ?? 1)) checked @endif />
                                    <span class="form-check-label fw-semibold text-gray-700">{{ trans('sw.system')}}</span>
                                </label>
                                <!--end::Checkbox-->
                                <!--begin::Checkbox-->
                                <label class="form-check form-check-custom form-check-solid">
                                    <input type="checkbox" id="inlineCheckbox21" name="is_mobile" value="1" 
                                           class="form-check-input" @if(@$product->is_mobile) checked="" @endif />
                                    <span class="form-check-label fw-semibold text-gray-700">{{ trans('sw.mobile')}}</span>
                                </label>
                                <!--end::Checkbox-->
                                
                                <!--begin::Checkbox-->
                                <label class="form-check form-check-custom form-check-solid">
                                    <input type="checkbox" id="inlineCheckbox22" name="is_web" value="1" 
                                           class="form-check-input" @if(@$product->is_web) checked="" @endif />
                                    <span class="form-check-label fw-semibold text-gray-700">{{ trans('sw.web')}}</span>
                                </label>
                                <!--end::Checkbox-->
                            </div>
                        </div>
                        <!--end::Input group-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Visibility Settings-->
            @endif

            <!--begin::Form actions-->
            <div class="d-flex justify-content-end">
                <button type="reset" class="btn btn-light me-5">{{ trans('admin.reset')}}</button>
                <button type="submit" class="btn btn-primary">
                    <i class="ki-outline ki-check fs-2"></i>
                    {{ trans('global.save')}}
                </button>
            </div>
            <!--end::Form actions-->
        </div>
    </form>
@endsection

@section('scripts')
    <script>
        const codeInput = document.querySelector('input[name="code"]');
        if (typeof window.originalBarcodeScanner === 'undefined') {
            window.originalBarcodeScanner = window.barcode_scanner;
        }

        if (codeInput) {
            window.barcodeScannerOverride = true;
            window.handleBarcodeOverride = function(rawValue) {
                const trimmed = (rawValue || '').toString().trim();
                if (!trimmed) {
                    return;
                }

                const candidates = Array.from(new Set([
                    trimmed,
                    trimmed.toUpperCase(),
                    /^\d+$/.test(trimmed) ? trimmed.padStart(14, '0') : null,
                    /^\d+$/.test(trimmed.toUpperCase()) ? trimmed.toUpperCase().padStart(14, '0') : null,
                ].filter(Boolean)));

                let matchedCode = null;
                if (window.productScannerIndex) {
                    for (const key of candidates) {
                        if (window.productScannerIndex[key]) {
                            matchedCode = window.productScannerIndex[key];
                            break;
                        }
                    }
                }

                codeInput.value = (matchedCode || trimmed).replaceAll('undefined', '');
                const event = new Event('input', { bubbles: true });
                codeInput.dispatchEvent(event);
            };

            window.productScannerIndex = @json($productScannerIndex);
            window.disableMemberScanner = true;
        }
    </script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 for category dropdown
            $('#store_category_id').select2({
                placeholder: "{{ trans('sw.select_store_category')}}",
                allowClear: true,
                width: '100%'
            });
        });

        $("#gym_image").change(function () {
            let input = this;
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#preview').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        });

    </script>
@endsection



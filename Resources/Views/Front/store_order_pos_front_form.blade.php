@extends('software::layouts.list')
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

@section('list_title') {{ @$title }} @endsection

@section('styles')
<style>
    .product-card {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    .page-bg {
        background-color: #f9f9f9;
    }
    .member-balance-positive {
        color: #1f9254;
        font-weight: 600;
    }
    .member-balance-negative {
        color: #b42318;
        font-weight: 600;
    }
</style>
@endsection

@section('page_body')
    @php
        $productScannerList = $products->map(function ($product) {
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

            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'image' => $product->image,
                'code' => $code,
                'normalized_code' => $normalized !== '' ? $normalized : $code,
                'padded_code' => $padded,
                'keys' => $keys,
            ];
        });

        $productScannerIndex = $productScannerList->flatMap(function ($product) {
            $map = [];
            foreach ($product['keys'] as $key) {
                $map[$key] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'image' => $product['image'],
                    'code' => $product['code'],
                    'normalized_code' => $product['normalized_code'],
                    'padded_code' => $product['padded_code'],
                ];
            }
            return $map;
        });
    @endphp
    <!--begin::Container-->
    <div id="kt_content_container" class="container-xxl">
        <!--begin::Layout-->
        <div class="d-flex flex-column flex-xl-row">
            <!--begin::Content-->
            <div class="d-flex flex-row-fluid me-xl-9 mb-10 mb-xl-0">
                <!--begin::Pos food-->
                <div class="card card-flush card-p-0 bg-transparent border-0">
                    <!--begin::Body-->
                    <div class="card-body">
                        <!--begin::Nav-->
                        <ul class="nav nav-pills d-flex flex-wrap justify-content-start nav-pills-custom gap-3 mb-6">
                            <!--begin::Item - All Products-->
                            <li class="nav-item mb-3 me-0">
                                <!--begin::Nav link-->
                                <a class="nav-link nav-link-border-solid btn btn-outline btn-flex btn-active-color-primary flex-column flex-stack pt-9 pb-7 page-bg show active" data-bs-toggle="pill" href="#kt_pos_products_all" style="width: 138px;height: 180px">
                                    <!--begin::Icon-->
                                    <div class="nav-icon mb-3">
                                        <i class="ki-outline ki-grid fs-2hx"></i>
                                    </div>
                                    <!--end::Icon-->
                                    <!--begin::Info-->
                                    <div class="">
                                        <span class="text-gray-800 fw-bold fs-2 d-block">{{ trans('sw.all')}}</span>
                                        <span class="text-gray-500 fw-semibold fs-7">{{ $products->count() }} {{ trans('sw.products')}}</span>
                                    </div>
                                    <!--end::Info-->
                                </a>
                                <!--end::Nav link-->
                            </li>
                            <!--end::Item-->
                            
                            <!--begin::Item - No Category-->
                            <li class="nav-item mb-3 me-0">
                                <!--begin::Nav link-->
                                <a class="nav-link nav-link-border-solid btn btn-outline btn-flex btn-active-color-primary flex-column flex-stack pt-9 pb-7 page-bg" data-bs-toggle="pill" href="#kt_pos_products_uncategorized" style="width: 138px;height: 180px">
                                    <!--begin::Icon-->
                                    <div class="nav-icon mb-3">
                                        <i class="ki-outline ki-questionnaire-tablet fs-2hx"></i>
                                    </div>
                                    <!--end::Icon-->
                                    <!--begin::Info-->
                                    <div class="">
                                        <span class="text-gray-800 fw-bold fs-2 d-block">{{ trans('sw.no_category')}}</span>
                                        <span class="text-gray-500 fw-semibold fs-7">{{ $products->filter(function($p) { return empty($p->store_category_id) && empty($p->category_id); })->count() }} {{ trans('sw.items')}}</span>
                                    </div>
                                    <!--end::Info-->
                                </a>
                                <!--end::Nav link-->
                            </li>
                            <!--end::Item-->
                            
                            @foreach($categories as $category)
                            <!--begin::Item-->
                            <li class="nav-item mb-3 me-0">
                                <!--begin::Nav link-->
                                <a class="nav-link nav-link-border-solid btn btn-outline btn-flex btn-active-color-primary flex-column flex-stack pt-9 pb-7 page-bg" data-bs-toggle="pill" href="#kt_pos_products_{{ $category->id }}" style="width: 138px;height: 180px">
                                    <!--begin::Icon-->
                                    <div class="nav-icon mb-3">
                                        @if($category->image)
                                            <img src="{{ $category->image_url }}" alt="{{ $category->name }}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;" />
                                        @else
                                            <i class="ki-outline ki-category fs-2hx"></i>
                                        @endif
                                    </div>
                                    <!--end::Icon-->
                                    <!--begin::Info-->
                                    <div class="">
                                        <span class="text-gray-800 fw-bold fs-2 d-block">{{ $category->name }}</span>
                                        <span class="text-gray-500 fw-semibold fs-7">{{ $products->filter(function($p) use ($category) { return ($p->store_category_id == $category->id || $p->category_id == $category->id); })->count() }} {{ trans('sw.items')}}</span>
                                    </div>
                                    <!--end::Info-->
                                </a>
                                <!--end::Nav link-->
                            </li>
                            <!--end::Item-->
                            @endforeach
                        </ul>
                        <!--end::Nav-->
                        
                        <!--begin::Tab Content-->
                        <div class="tab-content">
                            <!--begin::Tab pane - All Products-->
                            <div class="tab-pane fade show active" id="kt_pos_products_all">
                                <!--begin::Wrapper-->
                                <div class="d-flex flex-wrap d-grid gap-5 gap-xxl-9">
                                    @foreach($products as $product)
                                    <!--begin::Card-->
                                    <div class="card card-flush flex-row-fluid p-6 pb-5 mw-100 product-card" 
                                         data-product-id="{{ $product->id }}"
                                         data-product-name="{{ $product->name }}"
                                         data-product-price="{{ $product->price }}"
                                         data-product-image="{{ $product->image }}"
                                         onclick="addToCart(this)">
                                        <!--begin::Body-->
                                        <div class="card-body text-center">
                                            <!--begin::Product img-->
                                            <img src="{{ $product->image }}" class="rounded-3 mb-4 w-150px h-150px w-xxl-200px h-xxl-200px object-fit-cover" alt="" />
                                            <!--end::Product img-->
                                            <!--begin::Info-->
                                            <div class="mb-2">
                                                <!--begin::Title-->
                                                <div class="text-center">
                                                    <span class="fw-bold text-gray-800 cursor-pointer text-hover-primary fs-3 fs-xl-1">{{ $product->name }}</span>
                                                    <span class="text-gray-500 fw-semibold d-block fs-6 mt-n1">{{ trans('sw.qty')}}: {{ $product->quantity }}</span>
                                                </div>
                                                <!--end::Title-->
                                            </div>
                                            <!--end::Info-->
                                            <!--begin::Total-->
                                            <span class="text-success text-end fw-bold fs-1">{{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}{{ number_format($product->price, 2) }}</span>
                                            <!--end::Total-->
                                        </div>
                                        <!--end::Body-->
                                    </div>
                                    <!--end::Card-->
                                    @endforeach
                                </div>
                                <!--end::Wrapper-->
                            </div>
                            <!--end::Tab pane-->
                            
                            <!--begin::Tab pane - Uncategorized Products-->
                            <div class="tab-pane fade" id="kt_pos_products_uncategorized">
                                <!--begin::Wrapper-->
                                <div class="d-flex flex-wrap d-grid gap-5 gap-xxl-9">
                                    @foreach($products->filter(function($p) { return empty($p->store_category_id) && empty($p->category_id); }) as $product)
                                    <!--begin::Card-->
                                    <div class="card card-flush flex-row-fluid p-6 pb-5 mw-100 product-card" 
                                         data-product-id="{{ $product->id }}"
                                         data-product-name="{{ $product->name }}"
                                         data-product-price="{{ $product->price }}"
                                         data-product-image="{{ $product->image }}"
                                         onclick="addToCart(this)">
                                        <!--begin::Body-->
                                        <div class="card-body text-center">
                                            <!--begin::Product img-->
                                            <img src="{{ $product->image }}" class="rounded-3 mb-4 w-150px h-150px w-xxl-200px h-xxl-200px object-fit-cover" alt="" />
                                            <!--end::Product img-->
                                            <!--begin::Info-->
                                            <div class="mb-2">
                                                <!--begin::Title-->
                                                <div class="text-center">
                                                    <span class="fw-bold text-gray-800 cursor-pointer text-hover-primary fs-3 fs-xl-1">{{ $product->name }}</span>
                                                    <span class="text-gray-500 fw-semibold d-block fs-6 mt-n1">{{ trans('sw.qty')}}: {{ $product->quantity }}</span>
                                                </div>
                                                <!--end::Title-->
                                            </div>
                                            <!--end::Info-->
                                            <!--begin::Total-->
                                            <span class="text-success text-end fw-bold fs-1">{{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}{{ number_format($product->price, 2) }}</span>
                                            <!--end::Total-->
                                        </div>
                                        <!--end::Body-->
                                    </div>
                                    <!--end::Card-->
                                    @endforeach
                                </div>
                                <!--end::Wrapper-->
                            </div>
                            <!--end::Tab pane-->
                            
                            @foreach($categories as $category)
                            <!--begin::Tab pane-->
                            <div class="tab-pane fade" id="kt_pos_products_{{ $category->id }}">
                                <!--begin::Wrapper-->
                                <div class="d-flex flex-wrap d-grid gap-5 gap-xxl-9">
                                    @foreach($products->filter(function($p) use ($category) { return ($p->store_category_id == $category->id || $p->category_id == $category->id); }) as $product)
                                    <!--begin::Card-->
                                    <div class="card card-flush flex-row-fluid p-6 pb-5 mw-100 product-card" 
                                         data-product-id="{{ $product->id }}"
                                         data-product-name="{{ $product->name }}"
                                         data-product-price="{{ $product->price }}"
                                         data-product-image="{{ $product->image }}"
                                         onclick="addToCart(this)">
                                        <!--begin::Body-->
                                        <div class="card-body text-center">
                                            <!--begin::Product img-->
                                            <img src="{{ $product->image }}" class="rounded-3 mb-4 w-150px h-150px w-xxl-200px h-xxl-200px object-fit-cover" alt="" />
                                            <!--end::Product img-->
                                            <!--begin::Info-->
                                            <div class="mb-2">
                                                <!--begin::Title-->
                                                <div class="text-center">
                                                    <span class="fw-bold text-gray-800 cursor-pointer text-hover-primary fs-3 fs-xl-1">{{ $product->name }}</span>
                                                    <span class="text-gray-500 fw-semibold d-block fs-6 mt-n1">{{ trans('sw.qty')}}: {{ $product->quantity }}</span>
                                                </div>
                                                <!--end::Title-->
                                            </div>
                                            <!--end::Info-->
                                            <!--begin::Total-->
                                            <span class="text-success text-end fw-bold fs-1">{{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}{{ number_format($product->price, 2) }}</span>
                                            <!--end::Total-->
                                        </div>
                                        <!--end::Body-->
                                    </div>
                                    <!--end::Card-->
                                    @endforeach
                                </div>
                                <!--end::Wrapper-->
                            </div>
                            <!--end::Tab pane-->
                            @endforeach
                        </div>
                        <!--end::Tab Content-->
                    </div>
                    <!--end: Card Body-->
                </div>
                <!--end::Pos food-->
            </div>
            <!--end::Content-->
            
            <!--begin::Sidebar-->
            <div class="flex-row-auto w-xl-450px">
                <!--begin::Pos order-->
                <div class="card card-flush bg-body" id="kt_pos_form">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <h3 class="card-title fw-bold text-gray-800 fs-2qx">{{ trans('sw.current_order')}}</h3>
                        <!--begin::Toolbar-->
                        <div class="card-toolbar">
                            <a href="javascript:void(0)" onclick="clearCart()" class="btn btn-light-primary fs-4 fw-bold py-4">{{ trans('sw.clear_all')}}</a>
                        </div>
                        <!--end::Toolbar-->
                    </div>
                    <!--end::Header-->
                    
                    <!--begin::Body-->
                    <div class="card-body pt-0">
                        <form method="post" action="{{ route('sw.storeStoreOrderPOS') }}" id="store_order_form">
                            @csrf
                            
                            <!--begin::Member Selection-->
                            <div class="mb-8">
                                <label class="form-label fw-bold">{{ trans('sw.member')}}</label>
                                <select name="member_id" id="member_id" class="form-select" data-control="select2" data-placeholder="{{ trans('sw.select_member')}}">
                                    <option value="">{{ trans('sw.select_member')}}</option>
                                    @foreach($members as $member)
                                    <option value="{{ $member->id }}" data-member-code="{{ $member->code }}">{{ $member->name }} - {{ $member->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="card bg-light-secondary p-5 mb-8 d-none" id="member_info_card">
                                <div class="row">
                                    <div class="col-6 mb-4">
                                        <strong>{{ trans('sw.name')}}:</strong>
                                        <span id="pos_member_name">-</span>
                                    </div>
                                    <div class="col-6 mb-4">
                                        <strong>{{ trans('sw.phone')}}:</strong>
                                        <span id="pos_member_phone">-</span>
                                    </div>
                                    <div class="col-6">
                                        <strong>{{ trans('sw.balance')}}:</strong>
                                        <span id="pos_member_balance">-</span>
                                    </div>
                                    <div class="col-6 d-flex justify-content-between align-items-center d-none" id="store_member_use_balance_wrapper">
                                        <strong class="me-2" title="{{ trans('sw.amount_paid_validate_must_less_balance')}}">{{ trans('sw.use_balance')}}</strong>
                                        <div class="form-check form-switch form-check-custom form-check-solid">
                                            <input type="checkbox" class="form-check-input" name="store_member_use_balance" id="store_member_use_balance" value="1">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info d-none" id="use_balance_notice">
                                <i class="ki-outline ki-wallet fs-2 me-2"></i>
                                {{ trans('sw.use_balance') }} - {{ trans('sw.amount_paid_validate_must_less_balance') }}
                            </div>
                            <!--end::Member Selection-->
                            
                            <!--begin::Table container-->
                            <div class="table-responsive mb-8" style="max-height: 400px; overflow-y: auto;">
                                <!--begin::Table-->
                                <table class="table align-middle gs-0 gy-4 my-0" id="cart_table">
                                    <!--begin::Table head-->
                                    <thead>
                                        <tr>
                                            <th class="min-w-175px"></th>
                                            <th class="w-125px"></th>
                                            <th class="w-60px"></th>
                                        </tr>
                                    </thead>
                                    <!--end::Table head-->
                                    <!--begin::Table body-->
                                    <tbody id="cart_items">
                                        <tr class="text-center">
                                            <td colspan="3" class="text-gray-500 py-10">{{ trans('sw.no_items_in_cart')}}</td>
                                        </tr>
                                    </tbody>
                                    <!--end::Table body-->
                                </table>
                                <!--end::Table-->
                            </div>
                            <!--end::Table container-->
                            @if ($errors->any())
                            <div class="alert alert-danger fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div data-field="amount_paid" data-validator="notEmpty">
                                        @if ($errors->has('amount_paid'))
                                            {{ $errors->first('amount_paid') }}
                                        @endif
                                    </div>
                                </div>
                            @endif
                            <!--begin::Summary-->
                            <div class="d-flex flex-stack bg-success rounded-3 p-6 mb-11">
                                <!--begin::Content-->
                                <div class="fs-6 fw-bold text-white">
                                    <span class="d-block lh-1 mb-2">{{ trans('sw.subtotal')}}</span>
                                    <span class="d-block mb-2">{{ trans('sw.discount')}}</span>
                                    @if(@$mainSettings->active_loyalty)
                                    <span class="d-block mb-2" id="loyalty_discount_label" style="display: none;">
                                        <i class="ki-outline ki-gift me-1"></i>{{ trans('sw.loyalty_discount')}}
                                    </span>
                                    @endif
                                    <span class="d-block mb-9">{{ trans('sw.vat')}} ({{ @$mainSettings->vat_details['vat_percentage'] ?? 0 }}%)</span>
                                    <span class="d-block fs-2qx lh-1">{{ trans('sw.total')}}</span>
                                </div>
                                <!--end::Content-->
                                <!--begin::Content-->
                                <div class="fs-6 fw-bold text-white text-end">
                                    <span class="d-block lh-1 mb-2" id="subtotal_display">{{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}0.00</span>
                                    <span class="d-block mb-2" id="discount_display">-{{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}0.00</span>
                                    @if(@$mainSettings->active_loyalty)
                                    <span class="d-block mb-2" id="loyalty_discount_display" style="display: none;">-{{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}0.00</span>
                                    @endif
                                    <span class="d-block mb-9" id="vat_display">{{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}0.00</span>
                                    <span class="d-block fs-2qx lh-1" id="total_display">{{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}0.00</span>
                                </div>
                                <!--end::Content-->
                            </div>
                            <!--end::Summary-->
                            
                            @if(@$mainSettings->active_loyalty)
                            <!--begin::Loyalty Points Earning Info-->
                            <div class="alert alert-dismissible bg-light-success border border-success border-dashed d-flex flex-column flex-sm-row p-5 mb-8" id="loyalty_earning_info" style="display: none !important;">
                                <i class="ki-outline ki-gift fs-2hx text-success me-4 mb-5 mb-sm-0"></i>
                                <div class="d-flex flex-column pe-0 pe-sm-10">
                                    <h5 class="mb-1">{{ trans('sw.points_earning_info')}}</h5>
                                    <span class="text-gray-700" id="loyalty_earning_text">{!! trans('sw.you_will_earn_points', ['points' => '<span id="estimated_earning_points" class="fw-bold text-success">0</span>'])!!}</span>
                                    <span class="text-gray-600 fs-7" id="loyalty_earning_rate"></span>
                                </div>
                            </div>
                            <!--end::Loyalty Points Earning Info-->
                            @endif
                            
                            <!--begin::Discount Input-->
                            <div class="mb-8">
                                <label class="form-label fw-bold">{{ trans('sw.discount')}}</label>
                                <div class="input-group">
                                    <input type="number" name="discount_value" id="discount_value" class="form-control" value="0" min="0" onchange="calculateTotal(); calculateLoyaltyDiscount();">
                                    <select name="discount_type" id="discount_type" class="form-select w-100px" onchange="calculateTotal(); calculateLoyaltyDiscount();">
                                        <option value="1">{{ trans('sw.percentage')}}</option>
                                        <option value="0">{{ trans('sw.fixed')}}</option>
                                    </select>
                                </div>
                            </div>
                            <!--end::Discount Input-->
                            
                            @if(@$mainSettings->active_loyalty)
                            <!--begin::Loyalty Points Redemption-->
                            <div class="mb-8" id="loyalty_redemption_section" style="display: none;">
                                <div class="card bg-light-primary border-primary border-dashed">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-4">
                                            <i class="ki-outline ki-gift fs-2x text-primary me-3"></i>
                                            <div>
                                                <h4 class="mb-0">{{ trans('sw.redeem_loyalty_points')}}</h4>
                                                <p class="text-muted mb-0 fs-7">{{ trans('sw.available_points')}}: <span id="member_available_points" class="fw-bold text-primary">0</span></p>
                                                <p class="text-muted mb-0 fs-7">{{ trans('sw.points_value')}}: <span id="points_value_rate" class="fw-bold">0</span> {{ trans('sw.points')}} = <span id="money_value_rate" class="fw-bold">1</span> {{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}</p>
                                            </div>
                                        </div>
                                        
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">{{ trans('sw.points_to_redeem')}}</label>
                                                <input type="number" name="loyalty_points_redeem" id="loyalty_points_redeem" class="form-control" value="0" min="0" max="0" onchange="calculateLoyaltyDiscount()">
                                                <div class="form-text">{{ trans('sw.loyalty_points_redeem_help')}}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">{{ trans('sw.discount_value')}}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">{{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}</span>
                                                    <input type="text" id="loyalty_discount_value" class="form-control fw-bold" value="0.00" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end::Loyalty Points Redemption-->
                            @endif
                            
                            <!--begin::Payment Method-->
                            <div class="m-0">
                                <!--begin::Title-->
                                <h1 class="fw-bold text-gray-800 mb-5">{{ trans('sw.payment_method')}}</h1>
                                <!--end::Title-->
                                
                                <!--begin::Radio group-->
                                <div class="d-flex flex-equal gap-5 gap-xxl-9 px-0 mb-12" data-kt-buttons="true" data-kt-buttons-target="[data-kt-button]">
                                    @foreach($payment_types as $payment_type)
                                    <!--begin::Radio-->
                                    <label class="btn bg-light btn-color-gray-600 btn-active-text-gray-800 border border-3 border-gray-100 border-active-primary btn-active-light-primary w-100 px-4 @if($loop->first) active @endif" data-kt-button="true">
                                        <!--begin::Input-->
                                        <input class="btn-check" type="radio" name="payment_type" value="{{ $payment_type->payment_id }}" @if($loop->first) checked @endif />
                                        <!--end::Input-->
                                        <!--begin::Icon-->
                                        <i class="ki-outline ki-dollar fs-2hx mb-2 pe-0"></i>
                                        <!--end::Icon-->
                                        <!--begin::Title-->
                                        <span class="fs-7 fw-bold d-block">{{ $payment_type->name }}</span>
                                        <!--end::Title-->
                                    </label>
                                    <!--end::Radio-->
                                    @endforeach
                                </div>
                                <!--end::Radio group-->
                                
                                <!--begin::Hidden Inputs for Products-->
                                <div id="products_inputs"></div>
                                <!--end::Hidden Inputs-->
                                
                                <!--begin::Hidden Inputs for Calculations-->
                                <input type="hidden" name="amount_before_discount" id="amount_before_discount" value="0">
                                <input type="hidden" name="amount_paid" id="amount_paid" value="0">
                                <input type="hidden" name="amount_remaining" id="amount_remaining" value="0">
                                <!--end::Hidden Inputs-->
                                
                                <!--begin::Actions-->
                                <button type="submit" class="btn btn-primary fs-1 w-100 py-4" id="submit_order_btn">
                                    <span class="indicator-label">{{ trans('sw.complete_order')}}</span>
                                    <span class="indicator-progress">{{ trans('sw.please_wait')}}...
                                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                    </span>
                                </button>
                                <!--end::Actions-->
                            </div>
                            <!--end::Payment Method-->
                        </form>
                    </div>
                    <!--end: Card Body-->
                </div>
                <!--end::Pos order-->
            </div>
            <!--end::Sidebar-->
        </div>
        <!--end::Layout-->
    </div>
    <!--end::Container-->
@endsection

@section('scripts')
<script>
    window.storePosScanner = true;
    window.disableMemberScanner = true;
    window.productsIndexByCode = @json($productScannerIndex);
    window.productsIndexList = @json($productScannerList);
</script>
<script>
    let cart = [];
    const vatRate = {{ @$mainSettings->vat_details['vat_percentage'] ?? 0 }} / 100;
    const currencySymbol = '{{ $lang == "ar" ? (env("APP_CURRENCY_AR") ?? "") : (env("APP_CURRENCY_EN") ?? "") }}';
    const storePostpaidEnabled = {{ @$mainSettings->store_postpaid ? 'true' : 'false' }};
    let selectedMemberBalance = 0;
    let selectedMemberId = null;
    function addProductData(productData) {
        if (!productData || !productData.id) {
            return;
        }

        const existingItem = cart.find(item => item.id === productData.id);
        if (existingItem) {
            existingItem.quantity++;
        } else {
            cart.push({
                id: productData.id,
                name: productData.name,
                price: parseFloat(productData.price),
                image: productData.image,
                quantity: 1
            });
        }

        updateCart();
    }

    function addToCart(element) {
        const productId = $(element).data('product-id');
        const productName = $(element).data('product-name');
        const productPrice = parseFloat($(element).data('product-price'));
        const productImage = $(element).data('product-image');
        
        // Check if product already in cart
        const existingItem = cart.find(item => item.id === productId);
        
        if (existingItem) {
            existingItem.quantity++;
        } else {
            cart.push({
                id: productId,
                name: productName,
                price: productPrice,
                image: productImage,
                quantity: 1
            });
        }
        
        updateCart();
    }

    window.handleStorePosScan = function(rawCode) {
        if (!rawCode) {
            return;
        }

        const trimmed = rawCode.trim();
        if (!trimmed) {
            return;
        }

        const upper = trimmed.toUpperCase();
        const candidates = Array.from(new Set([
            trimmed,
            upper,
        ]));

        if (/^\d+$/.test(trimmed)) {
            candidates.push(trimmed.padStart(14, '0'));
        }

        if (/^\d+$/.test(upper)) {
            candidates.push(upper.padStart(14, '0'));
        }

        let product = null;
        if (window.productsIndexByCode) {
            for (const key of candidates) {
                if (window.productsIndexByCode[key]) {
                    product = window.productsIndexByCode[key];
                    break;
                }
            }
        }

        if (!product && Array.isArray(window.productsIndexList)) {
            product = window.productsIndexList.find(p =>
                candidates.includes(p.code) ||
                candidates.includes(p.normalized_code) ||
                candidates.includes(p.padded_code)
            );
        }

        if (!product) {
            Swal.fire({
                text: "{{ trans('sw.code_not_found') ?? trans('sw.no_record_found') }}",
                icon: "warning",
                buttonsStyling: false,
                confirmButtonText: "{{ trans('sw.ok') ?? 'OK' }}",
                customClass: {
                    confirmButton: "btn btn-primary"
                }
            });
            return;
        }

        addProductData(product);
    }
    
    function removeFromCart(productId) {
        cart = cart.filter(item => item.id !== productId);
        updateCart();
    }
    
    function updateQuantity(productId, change) {
        const item = cart.find(item => item.id === productId);
        if (item) {
            item.quantity += change;
            if (item.quantity <= 0) {
                removeFromCart(productId);
            } else {
                updateCart();
            }
        }
    }
    
    function clearCart() {
        cart = [];
        updateCart();
    }
    
    function updateCart() {
        const cartItemsContainer = $('#cart_items');
        cartItemsContainer.empty();
        
        if (cart.length === 0) {
            cartItemsContainer.append(`
                <tr class="text-center">
                    <td colspan="3" class="text-gray-500 py-10">{{ trans('sw.no_items_in_cart')}}</td>
                </tr>
            `);
        } else {
            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                cartItemsContainer.append(`
                    <tr data-product-id="${item.id}">
                        <td class="pe-0">
                            <div class="d-flex align-items-center">
                                <img src="${item.image}" class="w-50px h-50px rounded-3 me-3" alt="" />
                                <span class="fw-bold text-gray-800 cursor-pointer text-hover-primary fs-6 me-1">${item.name}</span>
                            </div>
                        </td>
                        <td class="pe-0">
                            <!--begin::Dialer-->
                            <div class="position-relative d-flex align-items-center">
                                <!--begin::Decrease control-->
                                <button type="button" class="btn btn-icon btn-sm btn-light btn-icon-gray-500" onclick="updateQuantity(${item.id}, -1)">
                                    <i class="ki-outline ki-minus fs-3x"></i>
                                </button>
                                <!--end::Decrease control-->
                                <!--begin::Input control-->
                                <input type="text" class="form-control border-0 text-center px-0 fs-3 fw-bold text-gray-800 w-30px" value="${item.quantity}" readonly />
                                <!--end::Input control-->
                                <!--begin::Increase control-->
                                <button type="button" class="btn btn-icon btn-sm btn-light btn-icon-gray-500" onclick="updateQuantity(${item.id}, 1)">
                                    <i class="ki-outline ki-plus fs-3x"></i>
                                </button>
                                <!--end::Increase control-->
                            </div>
                            <!--end::Dialer-->
                        </td>
                        <td class="text-end">
                            <span class="fw-bold text-primary fs-2">${currencySymbol}${itemTotal.toFixed(2)}</span>
                        </td>
                    </tr>
                `);
            });
        }
        
        // Update hidden inputs for form submission
        updateProductInputs();
        
        // Calculate total
        calculateTotal();
    }
    
    function updateProductInputs() {
        const productsInputsContainer = $('#products_inputs');
        productsInputsContainer.empty();
        
        cart.forEach((item, index) => {
            const priceValue = parseFloat(item.price).toFixed(2);
            productsInputsContainer.append(`
                <input type="hidden" name="products[id][]" value="${item.id}">
                <input type="hidden" name="products[price][]" value="${priceValue}">
                <input type="hidden" name="products[quantity][]" value="${item.quantity}">
            `);
        });
    }
    
    let memberLoyaltyPoints = 0;
    let loyaltyPointToMoneyRate = 0;
    let loyaltyMoneyToPointRate = {{ @$mainSettings->active_loyalty ? '0' : '0' }}; // Will be loaded from server
    
    // Load loyalty earning rate on page load
    @if(@$mainSettings->active_loyalty)
    $(document).ready(function() {
        $.ajax({
            url: '{{ route('sw.getMemberLoyaltyInfo') }}',
            type: 'GET',
            data: { member_id: 0 }, // Get rule without member
            success: function(response) {
                if (response.success && response.point_to_money_rate) {
                    loyaltyMoneyToPointRate = 1 / response.point_to_money_rate; // Convert to money_to_point_rate
                    $('#loyalty_earning_rate').text('{{ trans('sw.earning_rate', ['rate' => '']) }}'.replace('1 نقطة', loyaltyMoneyToPointRate.toFixed(2) + ' {{ trans('sw.points') }}').replace('1 point', loyaltyMoneyToPointRate.toFixed(2) + ' {{ trans('sw.points') }}'));
                }
            }
        });
    });
    @endif
    
    function calculateTotal() {
        let subtotal = 0;
        
        cart.forEach(item => {
            subtotal += item.price * item.quantity;
        });
        
        const discountValue = parseFloat($('#discount_value').val()) || 0;
        const discountType = parseInt($('#discount_type').val());
        
        let discountAmount = 0;
        if (discountType === 1) {
            // Percentage
            discountAmount = subtotal * (discountValue / 100);
        } else {
            // Fixed
            discountAmount = discountValue;
        }
        
        // Calculate loyalty discount
        const loyaltyPointsRedeem = parseInt($('#loyalty_points_redeem').val()) || 0;
        const loyaltyDiscountAmount = parseFloat($('#loyalty_discount_value').val()) || 0;
        
        const afterDiscount = subtotal - discountAmount - loyaltyDiscountAmount;
        const vatAmount = afterDiscount * vatRate;
        const total = afterDiscount + vatAmount;
        
        // Update display
        $('#subtotal_display').text(currencySymbol + subtotal.toFixed(2));
        $('#discount_display').text('-' + currencySymbol + discountAmount.toFixed(2));
        
        // Show/hide loyalty discount
        if (loyaltyPointsRedeem > 0 && loyaltyDiscountAmount > 0) {
            $('#loyalty_discount_label').show();
            $('#loyalty_discount_display').text('-' + currencySymbol + loyaltyDiscountAmount.toFixed(2)).show();
        } else {
            $('#loyalty_discount_label').hide();
            $('#loyalty_discount_display').hide();
        }
        
        $('#vat_display').text(currencySymbol + vatAmount.toFixed(2));
        $('#total_display').text(currencySymbol + total.toFixed(2));
        
        // Update hidden fields
        $('#amount_before_discount').val(subtotal.toFixed(2));
        $('#amount_paid').val(total.toFixed(2));
        $('#amount_remaining').val(0);
        
        // Adjust amount_paid if using store balance
        const useStoreBalance = $('#store_member_use_balance').is(':checked');
        if (useStoreBalance && selectedMemberId) {
            $('#amount_paid').val(0);
        }

        // Calculate and display estimated loyalty points earning
        @if(@$mainSettings->active_loyalty)
        if (loyaltyMoneyToPointRate > 0 && total > 0) {
            const estimatedPoints = Math.floor(total / loyaltyMoneyToPointRate);
            if (estimatedPoints > 0) {
                $('#estimated_earning_points').text(estimatedPoints);
                $('#loyalty_earning_info').slideDown();
            } else {
                $('#loyalty_earning_info').slideUp();
            }
        } else {
            $('#loyalty_earning_info').slideUp();
        }
        @endif
    }
    
    function calculateLoyaltyDiscount() {
        let pointsToRedeem = parseInt($('#loyalty_points_redeem').val()) || 0;
        
        // Validate points availability
        if (pointsToRedeem > memberLoyaltyPoints) {
            alert('{{ trans('sw.insufficient_loyalty_points') }}');
            $('#loyalty_points_redeem').val(memberLoyaltyPoints);
            pointsToRedeem = memberLoyaltyPoints;
        }
        
        if (pointsToRedeem < 0) {
            $('#loyalty_points_redeem').val(0);
            pointsToRedeem = 0;
        }
        
        // Calculate maximum usable discount (subtotal after regular discount)
        let subtotal = 0;
        cart.forEach(item => {
            subtotal += item.price * item.quantity;
        });
        
        const discountValue = parseFloat($('#discount_value').val()) || 0;
        const discountType = parseInt($('#discount_type').val());
        
        let discountAmount = 0;
        if (discountType === 1) {
            // Percentage
            discountAmount = subtotal * (discountValue / 100);
        } else {
            // Fixed
            discountAmount = discountValue;
        }
        
        const maxUsableDiscount = Math.max(0, subtotal - discountAmount);
        
        // Calculate maximum redeemable points based on max discount
        let maxRedeemablePoints = 0;
        if (loyaltyPointToMoneyRate > 0 && maxUsableDiscount > 0) {
            maxRedeemablePoints = Math.floor(maxUsableDiscount / loyaltyPointToMoneyRate);
        }
        
        // Cap redemption points to maximum usable (can't redeem more than purchase amount)
        if (pointsToRedeem > maxRedeemablePoints && maxRedeemablePoints > 0) {
            pointsToRedeem = maxRedeemablePoints;
            $('#loyalty_points_redeem').val(pointsToRedeem);
            
            // Show warning message
            if (maxRedeemablePoints < memberLoyaltyPoints) {
                alert('{{ trans('sw.loyalty_points_capped_to_purchase') }}');
            }
        }
        
        // Calculate discount value (capped to max usable)
        let calculatedDiscountValue = loyaltyPointToMoneyRate > 0 ? (pointsToRedeem * loyaltyPointToMoneyRate) : 0;
        if (calculatedDiscountValue > maxUsableDiscount) {
            calculatedDiscountValue = maxUsableDiscount;
        }
        
        $('#loyalty_discount_value').val(calculatedDiscountValue.toFixed(2));
        
        // Recalculate total
        calculateTotal();
    }
    
    // Load loyalty points and rate when member is selected
    function resetMemberInfoCard() {
        selectedMemberBalance = 0;
        selectedMemberId = null;
        $('#pos_member_name').text('-');
        $('#pos_member_phone').text('-');
        $('#pos_member_balance').text('-').removeClass('member-balance-positive member-balance-negative');
        $('#member_info_card').addClass('d-none');
        $('#store_member_use_balance').prop('checked', false);
        $('#use_balance_notice').addClass('d-none');
        $('#store_member_use_balance').prop('disabled', true);
        $('#store_member_use_balance_wrapper').addClass('d-none');
    }
    
    function updateMemberInfoCard(member) {
        $('#pos_member_name').text(member.name || '-');
        $('#pos_member_phone').text(member.phone || '-');
        
        const balanceElement = $('#pos_member_balance');
        balanceElement.removeClass('member-balance-positive member-balance-negative');
        const balanceValue = parseFloat(member.store_balance) || 0;
        selectedMemberBalance = balanceValue;
        
        balanceElement.text(balanceValue.toFixed(2));
        // Always enable use_balance_wrapper if postpaid is enabled
        if (storePostpaidEnabled) {
            $('#store_member_use_balance_wrapper').removeClass('d-none');
            $('#store_member_use_balance').prop('disabled', false);
        } else if (balanceValue > 0) {
            balanceElement.addClass('member-balance-positive');
            $('#store_member_use_balance_wrapper').removeClass('d-none');
            $('#store_member_use_balance').prop('disabled', false);
        } else {
            balanceElement.addClass('member-balance-negative');
            $('#store_member_use_balance_wrapper').addClass('d-none');
            $('#store_member_use_balance').prop('checked', false).prop('disabled', true);
            $('#use_balance_notice').addClass('d-none');
        }

        $('#member_info_card').removeClass('d-none');
    }
    
    $('#member_id').on('change', function() {
        const memberId = $(this).val();
        selectedMemberId = memberId || null;
        $('#store_member_use_balance').prop('checked', false);
        $('#use_balance_notice').addClass('d-none');
        
        if (!memberId) {
            resetMemberInfoCard();
        } else {
            const memberCode = $('#member_id option:selected').data('member-code');
            $.get('{{ route('sw.getStoreMemberAjax') }}', { member_id: memberCode || memberId })
                .done(function(result) {
                    if (result && result.id) {
                        updateMemberInfoCard(result);
                    } else {
                        resetMemberInfoCard();
                    }
                })
                .fail(function() {
                    resetMemberInfoCard();
                });
        }
        
        if (memberId && {{ @$mainSettings->active_loyalty ? 'true' : 'false' }}) {
            // Load member loyalty points
            $.ajax({
                url: '{{ route('sw.getMemberLoyaltyInfo') }}',
                type: 'GET',
                data: { member_id: memberId },
                success: function(response) {
                    if (response.success) {
                        memberLoyaltyPoints = response.points || 0;
                        loyaltyPointToMoneyRate = response.point_to_money_rate || 0;
                        
                        $('#member_available_points').text(response.points_formatted || '0');
                        $('#points_value_rate').text(response.points_for_one_currency || '0');
                        $('#money_value_rate').text('1');
                        $('#loyalty_points_redeem').attr('max', memberLoyaltyPoints);
                        
                        if (memberLoyaltyPoints > 0) {
                            $('#loyalty_redemption_section').slideDown();
                        } else {
                            $('#loyalty_redemption_section').slideUp();
                            $('#loyalty_points_redeem').val(0);
                            calculateLoyaltyDiscount();
                        }
                    }
                },
                error: function() {
                    console.log('Failed to load loyalty points');
                }
            });
        } else {
            $('#loyalty_redemption_section').slideUp();
            $('#loyalty_points_redeem').val(0);
            memberLoyaltyPoints = 0;
            loyaltyPointToMoneyRate = 0;
            calculateLoyaltyDiscount();
        }
    });
    
    $('#store_member_use_balance').on('change', function() {
        if ($(this).is(':checked')) {
            $('#use_balance_notice').removeClass('d-none');
        } else {
            $('#use_balance_notice').addClass('d-none');
        }
        // Trigger total recalculation to update amount_paid
        calculateTotal();
    });
    
    // Form submission
    $('#store_order_form').on('submit', function(e) {
        if (cart.length === 0) {
            e.preventDefault();
            Swal.fire({
                text: "{{ trans('sw.please_add_products_to_cart')}}",
                icon: "warning",
                buttonsStyling: false,
                confirmButtonText: "{{ trans('sw.ok')}}",
                customClass: {
                    confirmButton: "btn btn-primary"
                }
            });
            return false;
        }

        const useStoreBalance = $('#store_member_use_balance').is(':checked');
        const currentOrderTotal = parseFloat($('#total_display').text().replace(currencySymbol, '')) || 0;

        if (useStoreBalance && !storePostpaidEnabled && selectedMemberBalance < currentOrderTotal) {
            e.preventDefault();
            Swal.fire({
                text: "{{ trans('sw.amount_paid_validate_must_less_balance')}}",
                icon: "warning",
                buttonsStyling: false,
                confirmButtonText: "{{ trans('sw.ok')}}",
                customClass: {
                    confirmButton: "btn btn-primary"
                }
            });
            return false;
        }
        
        const submitButton = $('#submit_order_btn');
        submitButton.attr('data-kt-indicator', 'on');
        submitButton.prop('disabled', true);
    });
</script>
@endsection




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
</style>
@endsection

@section('page_body')
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
                                    <option value="{{ $member->id }}">{{ $member->name }} - {{ $member->code }}</option>
                                    @endforeach
                                </select>
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
                            
                            <!--begin::Summary-->
                            <div class="d-flex flex-stack bg-success rounded-3 p-6 mb-11">
                                <!--begin::Content-->
                                <div class="fs-6 fw-bold text-white">
                                    <span class="d-block lh-1 mb-2">{{ trans('sw.subtotal')}}</span>
                                    <span class="d-block mb-2">{{ trans('sw.discount')}}</span>
                                    <span class="d-block mb-9">{{ trans('sw.vat')}} ({{ @$mainSettings->vat_details['vat_percentage'] ?? 0 }}%)</span>
                                    <span class="d-block fs-2qx lh-1">{{ trans('sw.total')}}</span>
                                </div>
                                <!--end::Content-->
                                <!--begin::Content-->
                                <div class="fs-6 fw-bold text-white text-end">
                                    <span class="d-block lh-1 mb-2" id="subtotal_display">{{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}0.00</span>
                                    <span class="d-block mb-2" id="discount_display">-{{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}0.00</span>
                                    <span class="d-block mb-9" id="vat_display">{{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}0.00</span>
                                    <span class="d-block fs-2qx lh-1" id="total_display">{{ $lang == 'ar' ? (env('APP_CURRENCY_AR') ?? '') : (env('APP_CURRENCY_EN') ?? '') }}0.00</span>
                                </div>
                                <!--end::Content-->
                            </div>
                            <!--end::Summary-->
                            
                            <!--begin::Discount Input-->
                            <div class="mb-8">
                                <label class="form-label fw-bold">{{ trans('sw.discount')}}</label>
                                <div class="input-group">
                                    <input type="number" name="discount_value" id="discount_value" class="form-control" value="0" min="0" onchange="calculateTotal()">
                                    <select name="discount_type" id="discount_type" class="form-select w-100px" onchange="calculateTotal()">
                                        <option value="1">{{ trans('sw.percentage')}}</option>
                                        <option value="0">{{ trans('sw.fixed')}}</option>
                                    </select>
                                </div>
                            </div>
                            <!--end::Discount Input-->
                            
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
    let cart = [];
    const vatRate = {{ @$mainSettings->vat_details['vat_percentage'] ?? 0 }} / 100;
    const currencySymbol = '{{ $lang == "ar" ? (env("APP_CURRENCY_AR") ?? "") : (env("APP_CURRENCY_EN") ?? "") }}';
    
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
        
        const afterDiscount = subtotal - discountAmount;
        const vatAmount = afterDiscount * vatRate;
        const total = afterDiscount + vatAmount;
        
        // Update display
        $('#subtotal_display').text(currencySymbol + subtotal.toFixed(2));
        $('#discount_display').text('-' + currencySymbol + discountAmount.toFixed(2));
        $('#vat_display').text(currencySymbol + vatAmount.toFixed(2));
        $('#total_display').text(currencySymbol + total.toFixed(2));
        
        // Update hidden fields
        $('#amount_before_discount').val(subtotal.toFixed(2));
        $('#amount_paid').val(total.toFixed(2));
        $('#amount_remaining').val(0);
    }
    
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
        
        const submitButton = $('#submit_order_btn');
        submitButton.attr('data-kt-indicator', 'on');
        submitButton.prop('disabled', true);
    });
</script>
@endsection


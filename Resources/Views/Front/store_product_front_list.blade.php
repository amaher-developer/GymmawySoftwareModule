@extends('software::layouts.list')
@section('list_title') {{ @$title }} @endsection
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
@section('styles')
    <style>
        /* Actions column styling */
        .actions-column {
            min-width: 120px;
            text-align: right;
        }

        .actions-column .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .actions-column .d-flex {
            gap: 0.25rem;
        }
    </style>
@endsection
@section('page_body')

<!--begin::Store Products-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <!--begin::Card title-->
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-shop fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title}}</span>
            </div>
        </div>
        <!--end::Card title-->
        <div class="card-toolbar">
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <!--begin::Add Store Product-->
                @if(in_array('createStoreProduct', (array)$swUser->permissions) || $swUser->is_super_user)
                    <a href="{{route('sw.createStoreProduct')}}" class="btn btn-sm btn-flex btn-light-primary">
                        <i class="ki-outline ki-plus fs-6"></i>
                        {{ trans('admin.add')}}
                    </a>
                @endif
                <!--end::Add Store Product-->
            </div>
        </div>
    </div>
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body pt-0">
        <!--begin::Search-->
        <div class="d-flex align-items-center position-relative my-1 mb-5">
            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
            <form class="d-flex" action="" method="get" style="max-width: 400px;">
                <input type="text" name="search" class="form-control form-control-solid ps-12" value="{{ request('search') }}" placeholder="{{ trans('sw.search_on')}}">
                <button class="btn btn-primary" type="submit">
                    <i class="ki-outline ki-magnifier fs-3"></i>
                </button>
            </form>
        </div>
        <!--end::Search-->

        <!--begin::Total count-->
        <div class="d-flex align-items-center mb-5">
            <div class="symbol symbol-50px me-5">
                <div class="symbol-label bg-light-primary">
                    <i class="ki-outline ki-chart-simple fs-2x text-primary"></i>
                </div>
            </div>
            <div class="d-flex flex-column">
                <span class="fs-6 fw-semibold text-gray-900">{{ trans('admin.total_count')}}</span>
                <span class="fs-2 fw-bold text-primary">{{ $total }}</span>
            </div>
        </div>
        <!--end::Total count-->

        @if(count($products) > 0)
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_store_products_table">
                <thead>
                    <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-200px text-nowrap">
                            <i class="ki-outline ki-tag fs-6 me-2"></i>{{ trans('sw.name')}}
                        </th>
                        <th class="min-w-150px text-nowrap">
                            <i class="ki-outline ki-category fs-6 me-2"></i>{{ trans('sw.store_category')}}
                        </th>
                        <th class="min-w-100px text-nowrap">
                            <i class="ki-outline ki-dollar fs-6 me-2"></i>{{ trans('sw.price')}}
                        </th>
                        <th class="min-w-100px text-nowrap">
                            <i class="ki-outline ki-sort fs-6 me-2"></i>{{ trans('sw.quantity')}}
                        </th>
                        <th class="min-w-120px text-nowrap">
                            <i class="ki-outline ki-pc fs-6 me-2"></i>{{ trans('sw.system')}}
                        </th>
                        <th class="text-end min-w-70px actions-column">
                            <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('admin.actions')}}
                        </th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach($products as $key=> $product)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <!--begin::Avatar-->
                                    <div class="symbol symbol-50px me-3">
                                        <div class="symbol-label fs-3 bg-light-primary text-primary">
                                            <i class="ki-outline ki-tag fs-2"></i>
                                        </div>
                                    </div>
                                    <!--end::Avatar-->
                                    <div>
                                        <!--begin::Title-->
                                        <div class="text-gray-800 text-hover-primary fs-5 fw-bold">
                                            {{ $product->name }}
                                        </div>
                                        <!--end::Title-->
                                    </div>
                                </div>
                            </td>
                            <td class="pe-0">
                                @php
                                    $category = $product->store_category ?? $product->category ?? null;
                                @endphp
                                @if($category)
                                    <div class="d-flex align-items-center">
                                        @if($category->image)
                                            <div class="symbol symbol-35px me-2">
                                                <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="rounded" />
                                            </div>
                                        @endif
                                        <span class="badge badge-light-success fs-7">{{ $category->name }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-500 fs-7">{{ trans('sw.no_category')}}</span>
                                @endif
                            </td>
                            <td class="pe-0">
                                <span class="fw-bold text-primary">{{ number_format($product->price, 2) }}</span>
                            </td>
                            <td class="pe-0">
                                <span class="fw-bold @if(@$product->quantity < 10) text-danger @else text-success @endif">
                                    {{ (int)$product->quantity }}
                                </span>
                                @if(@$product->quantity < 10)
                                    <span class="badge badge-light-danger fs-7 ms-2">{{ trans('sw.low_stock')}}</span>
                                @endif
                            </td>
                            <td class="pe-0">
                                @if(@$product->is_system)
                                    <div class="d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{ trans('sw.visible_in_system') }}">
                                        <span class="bullet bullet-dot bg-success me-2"></span>
                                        <span class="badge badge-light-success rounded-pill">
                                            <i class="ki-outline ki-check-circle fs-6 me-1"></i>{{ trans('sw.visible') }}
                                        </span>
                                    </div>
                                @else
                                    <div class="d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{ trans('sw.hidden_in_system') }}">
                                        <span class="bullet bullet-dot bg-danger me-2"></span>
                                        <span class="badge badge-light-danger rounded-pill">
                                            <i class="ki-outline ki-eye-slash fs-6 me-1"></i>{{ trans('sw.hidden') }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="text-end actions-column">
                                <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">
                                    @if(in_array('storePurchasesBill', (array)$swUser->permissions) || $swUser->is_super_user)
                                        <!--begin::Purchase-->
                                        <a data-target="#modalVendor" data-toggle="modal" href="#"
                                           onclick="vendorModel({{@$product->id}}, '{{@$product->name}}')"
                                           class="btn btn-icon btn-bg-light btn-active-color-success btn-sm" title="{{ trans('sw.purchases_bill')}}">
                                            <i class="ki-outline ki-plus fs-2"></i>
                                        </a>
                                        <!--end::Purchase-->
                                    @endif
                                    
                                    @if(in_array('editStoreProduct', (array)$swUser->permissions) || $swUser->is_super_user)
                                        <!--begin::Edit-->
                                        <a href="{{route('sw.editStoreProduct',$product->id)}}"
                                           class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" title="{{ trans('admin.edit')}}">
                                            <i class="ki-outline ki-pencil fs-2"></i>
                                        </a>
                                        <!--end::Edit-->
                                    @endif
                                    
                                    @if(in_array('deleteStoreProduct', (array)$swUser->permissions) || $swUser->is_super_user)
                                        @if(request('trashed'))
                                            <!--begin::Enable-->
                                            <a title="{{ trans('admin.enable')}}"
                                               href="{{route('sw.deleteStoreProduct',$product->id)}}"
                                               class="confirm_delete btn btn-icon btn-bg-light btn-active-color-success btn-sm" title="{{ trans('admin.enable')}}">
                                                <i class="ki-outline ki-check-circle fs-2"></i>
                                            </a>
                                            <!--end::Enable-->
                                        @else
                                            <!--begin::Delete-->
                                            <a title="{{ trans('admin.disable')}}"
                                               href="{{route('sw.deleteStoreProduct',$product->id)}}"
                                               class="confirm_delete btn btn-icon btn-bg-light btn-active-color-danger btn-sm" title="{{ trans('admin.disable')}}">
                                                <i class="ki-outline ki-trash fs-2"></i>
                                            </a>
                                            <!--end::Delete-->
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            <!--end::Table-->
            
            <!--begin::Pagination-->
            <div class="d-flex flex-stack flex-wrap pt-10">
                <div class="fs-6 fw-semibold text-gray-700">
                    {{ trans('sw.showing_entries', [
                        'from' => $products->firstItem() ?? 0,
                        'to' => $products->lastItem() ?? 0,
                        'total' => $products->total()
                    ]) }}
                </div>
                <ul class="pagination">
                    {!! $products->appends($search_query)->render() !!}
                </ul>
            </div>
            <!--end::Pagination-->
        @else
            <!--begin::Empty State-->
            <div class="text-center py-10">
                <div class="symbol symbol-100px mb-5">
                    <div class="symbol-label fs-2x fw-semibold text-success bg-light-success">
                        <i class="ki-outline ki-shop fs-2"></i>
                    </div>
                </div>
                <h4 class="text-gray-800 fw-bold">{{ trans('sw.no_record_found')}}</h4>
            </div>
            <!--end::Empty State-->
        @endif
    </div>
    <!--end::Card body-->
</div>
<!--end::Store Products-->




    <!-- start model pay -->
    <div class="modal" id="modalVendor">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">
                        <i class="ki-outline ki-shopping-cart fs-2 me-2"></i>{{ trans('sw.purchases_bill')}}
                    </h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Product Info -->
                    <div class="mb-4">
                        <div class="alert alert-info mb-0">
                            <i class="ki-outline ki-information fs-3 me-2"></i>{{ trans('sw.product')}}: 
                            <span id="vendor_product_name" class="fw-bold"></span>
                        </div>
                    </div>
                    
                    <div id="modalVendorResult"></div>
                    
                    <form id="form_vendor" action="" method="POST">
                        <input name="vendor_product_id" value="" type="hidden" id="vendor_product_id">
                        
                        <!-- Purchase Details Section -->
                        <div class="mb-6">
                            <h6 class="fw-bold text-gray-800 mb-4">
                                <i class="ki-outline ki-setting-2 fs-4 me-2"></i>{{ trans('sw.purchase_details')}}
                            </h6>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ trans('sw.quantity')}} <span class="text-danger">*</span></label>
                                    <input name="vendor_quantity" class="form-control form-control-solid" type="number" 
                                           id="vendor_quantity" step="0.01" min="0"
                                           placeholder="{{ trans('sw.enter_quantity')}}" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ trans('sw.amount')}} <span class="text-danger">*</span></label>
                                    <input name="vendor_amount" class="form-control form-control-solid" type="number" 
                                           id="vendor_amount" step="0.01" min="0"
                                           placeholder="{{ trans('sw.enter_amount_paid')}}" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ trans('sw.payment_type')}} <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-solid" name="vendor_payment_type" id="vendor_payment_type" required>
                                        @foreach($payment_types as $payment_type)
                                            <option value="{{$payment_type->payment_id}}" 
                                                    @if(@old('payment_type',$order->payment_type) == $payment_type->payment_id) selected @endif>
                                                {{$payment_type->name}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ trans('sw.vat_settings')}}</label>
                                    <div class="form-check">
                                        <input name="vendor_is_vat" class="form-check-input" type="checkbox" 
                                               id="vendor_is_vat" checked style="transform: scale(1.2);">
                                        <label class="form-check-label fw-bold text-primary" for="vendor_is_vat">
                                            <i class="ki-outline ki-information fs-4 me-2"></i>{{ trans('sw.including_vat')}}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Vendor Information Section -->
                        <div class="mb-6">
                            <h6 class="fw-bold text-gray-800 mb-4">
                                <i class="ki-outline ki-user fs-4 me-2"></i>{{ trans('sw.vendor_information')}}
                            </h6>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ trans('sw.vendor_name')}}</label>
                                    <input name="vendor_name" class="form-control form-control-solid" type="text" 
                                           id="vendor_name" placeholder="{{ trans('sw.enter_vendor_name')}}">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ trans('sw.vendor_phone')}}</label>
                                    <input name="vendor_phone" class="form-control form-control-solid" type="text" 
                                           id="vendor_phone" placeholder="{{ trans('sw.enter_vendor_phone')}}">
                                </div>
                                
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">{{ trans('sw.vendor_address')}}</label>
                                    <input name="vendor_address" class="form-control form-control-solid" type="text" 
                                           id="vendor_address" placeholder="{{ trans('sw.enter_vendor_address')}}">
                                </div>
                                
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">{{ trans('sw.notes')}}</label>
                                    <textarea name="vendor_notes" class="form-control form-control-solid" rows="3" 
                                              id="vendor_notes" placeholder="{{ trans('sw.enter_notes')}}"></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal" type="button">
                        {{ trans('sw.cancel')}}
                    </button>
                    <button class="btn btn-primary" id="form_vendor_btn" type="button">
                        <i class="ki-outline ki-check fs-2 me-2"></i>{{ trans('sw.process_purchase')}}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- End model pay -->
@endsection

@section('scripts')
    @parent

    <script>

        $(document).on('click', '#export', function (event) {
            event.preventDefault();
            $.ajax({
                url: $(this).attr('url'),
                cache: false,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    var a = document.createElement("a");
                    a.href = response.file;
                    a.download = response.name;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                },
                error: function (request, error) {
                    swal("Operation failed", "Something went wrong.", "error");
                    console.error("Request: " + JSON.stringify(request));
                    console.error("Error: " + JSON.stringify(error));
                }
            });

        });

        $("#filter_form").slideUp();
        $(".filter_trigger_button").click(function () {
            $("#filter_form").slideToggle(300);
        });

        $(document).on('click', '.remove_filter', function (event) {
            event.preventDefault();
            var filter = $(this).attr('id');
            $("#" + filter).val('');
            $("#filter_form").submit();
        });

        $(document).on('click', '#form_vendor_btn', function (event) {
            event.preventDefault();
            let product_id = $('#vendor_product_id').val();
            let amount = $('#vendor_amount').val();
            let vendor_is_vat = 0;
            if ($('#vendor_is_vat').is(':checked')) {
                vendor_is_vat = 1;
            }
            let quantity = $('#vendor_quantity').val();
            let payment_type = $('#vendor_payment_type').val();
            let vendor_name = $('#vendor_name').val();
            let vendor_phone = $('#vendor_phone').val();
            let vendor_address = $('#vendor_address').val();
            let notes = $('#vendor_notes').val();
            $('#modalVendorResult').show();
            if(!product_id || !amount || !quantity || !payment_type){
                $('#modalVendorResult').html('<div class="alert alert-danger">{{ trans('sw.error_login')}}</div>');
            }else {
                $.ajax({
                    url: '{{route('sw.storePurchasesBill')}}',
                    cache: false,
                    type: 'POST',
                    dataType: 'text',
                    data: {
                        product_id: product_id
                        , amount: amount
                        , vendor_is_vat: vendor_is_vat
                        , payment_type: payment_type
                        , quantity: quantity
                        , vendor_name: vendor_name
                        , vendor_phone: vendor_phone
                        , vendor_address: vendor_address
                        , notes: notes
                        , _token: "{{csrf_token()}}"
                    },
                    success: function (response) {
                        if (response == 1) {
                            // $("#global-loader").hide();
                            $('#modalVendor').modal('hide');

                            swal({
                                title: trans_done,
                                text: '{{ trans('admin.successfully_edited')}}',
                                type: "success",
                                timer: 2000,
                                confirmButtonText: '{{ trans('admin.done')}}',
                            }).then(() => {
                                // This will reload the page when the user clicks "OK"
                                window.location.reload();
                            });


                        }else{
                            $('#modalVendorResult').html('<div class="alert alert-danger">' + response + '</div>');
                        }

                    },
                    error: function (request, error) {
                        swal("Operation failed", "Something went wrong.", "error");
                        console.error("Request: " + JSON.stringify(request));
                        console.error("Error: " + JSON.stringify(error));
                    }
                });
            }
        });

        function vendorModel(product_id, product_name){
            $('#vendor_product_id').val(product_id);
            $('#vendor_product_name').html(product_name);
            $('#modalVendor').show();
        }
    </script>

@endsection

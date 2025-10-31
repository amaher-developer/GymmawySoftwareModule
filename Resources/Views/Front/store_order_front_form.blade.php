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
@section('form_title') {{ @$title }}@endsection
@section('form_add_button') @if(@$last_order_id) <a href="{{route('sw.showStoreOrder', @$last_order_id)}}"
                                                    class="btn btn-primary" type="button">
    <i class="ki-outline ki-file-text fs-2"></i>
    {{ trans('sw.last_invoice')}}</a> @endif @endsection
@section('page_body')

    <!--begin::Store Order Form-->
    <form method="post" action="" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
        {{csrf_field()}}
        
        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!--begin::Order Details-->
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
                    <!--begin::Member Section-->
                    <div class="mb-10 fv-row">
                        <label class="form-label">{{ trans('sw.member_id')}}</label>
                        <input type="text" name="member_id" class="form-control" 
                               placeholder="{{ trans('sw.enter_member_id')}}" 
                               value="{{ old('member_id', $order->member_id) }}" 
                               id="member_id" />
                    </div>
                    <!--end::Member Section-->
                    
                    <!--begin::Member Info Card-->
                    <div class="card bg-light-secondary p-5">
                        <div class="row">
                            <div class="col-6 mb-5">
                                <strong>{{ trans('sw.name')}}:</strong> <span id="store_member_name">-</span>
                            </div>
                            <div class="col-6 mb-5">
                                <strong>{{ trans('sw.phone')}}:</strong> <span id="store_member_phone">-</span>
                            </div>
                            <div class="col-6">
                                <strong>{{ trans('sw.balance')}}:</strong> <span id="store_member_balance">-</span>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <strong class="me-2" title="{{ trans('sw.amount_paid_validate_must_less_balance')}}">
                                        {{ trans('sw.use_balance')}} 
                                        <i class="ki-outline ki-information-2 fs-7"></i>:
                                    </strong> 
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input type="checkbox" name="store_member_use_balance" 
                                               class="form-check-input" 
                                               id="store_member_use_balance" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Member Info Card-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Order Details-->

            <!--begin::Cart-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{ trans('sw.cart')}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Product Selection-->
                    <div class="row mb-10">
                        <div class="col-md-5">
                            <label class="required form-label">{{ trans('sw.choose_products')}}</label>
                            <select id="products" class="form-select" data-placeholder="{{ trans('admin.choose')}}...">
                                <option></option>
                                @foreach($products as $product)
                                    <option value="{{$product->id}}" data-product-name="{{$product->name}}"
                                            data-max-quantity="{{$product->quantity}}"
                                            data-price="{{$product->price}}">{{$product->id}} - {{$product->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ trans('sw.quantity')}}</label>
                            <div id="spinner">
                                <div class="input-group">
                                    <button type="button" class="btn btn-icon btn-light-primary btn-sm spinner-up">
                                        <i class="ki-outline ki-plus"></i>
                                    </button>
                                    <input type="number" name="quantity" id="quantity"
                                           placeholder="{{ trans('sw.quantity')}}" value="1"
                                           class="spinner-input form-control text-center">
                                    <button type="button" class="btn btn-icon btn-light-danger btn-sm spinner-down">
                                        <i class="ki-outline ki-minus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-primary w-100" id="add_to_cart">
                                <i class="ki-outline ki-plus fs-2"></i>
                                {{ trans('sw.add_to_card')}}
                            </button>
                        </div>
                    </div>
                    <!--end::Product Selection-->
                    
                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-10"></div>
                    <!--end::Separator-->

                    <!--begin::Cart Section-->
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-hover align-middle gs-0 gy-4 mb-0" id="cart_table">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 min-w-200px rounded-start">{{ trans('sw.store_product')}}</th>
                                    <th class="min-w-100px">{{ trans('sw.price')}}</th>
                                    <th class="min-w-100px">{{ trans('sw.quantity')}}</th>
                                    <th class="min-w-100px">{{ trans('sw.total_price')}}</th>
                                    <th class="min-w-100px text-end pe-4 rounded-end">{{ trans('admin.actions')}}</th>
                                </tr>
                            </thead>
                            <tbody id="cart_result">
                                <tr id="empty_cart">
                                    <td colspan="5" class="text-center text-muted py-10">
                                        <i class="ki-outline ki-basket-ok fs-3x"></i>
                                        <p class="mt-3">{{ trans('sw.cart_empty')}}</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!--end::Cart Section-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Cart-->
        </div>
        <!--end::Main column-->

        <!--begin::Aside column-->
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-400px mb-7 ms-lg-10">
            <!--begin::Summary card-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{ trans('sw.summary')}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <div class="d-flex flex-column gap-5">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-5">{{ trans('sw.invoice_total')}}:</span>
                            <span class="fw-bolder fs-4 text-primary" id="total_price">0.00</span>
                        </div>
                        <input type="hidden" id="total_price_value" name="total_price_value" value="0">
                        <input type="hidden" id="total_vat" name="vat" value="0">
                        
                        @if(@$mainSettings->vat_details['vat_percentage'])
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">{{ trans('sw.vat_total')}} ({{@$mainSettings->vat_details['vat_percentage'].'%'}}):</span>
                            <span class="fw-bold text-warning" id="total_vat_show">0.00</span>
                        </div>
                        <div class="separator separator-dashed"></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-4">{{ trans('sw.invoice_total_required')}}:</span>
                            <span class="fw-bolder fs-2 text-success" id="total_price_vat">0.00</span>
                        </div>
                        @endif
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Summary card-->
            <!--begin::Payment card-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{ trans('sw.payment')}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    @if((in_array('editStoreDiscount', (array)$swUser->permissions)) || $swUser->is_super_user)
                    <div class="mb-10 fv-row">
                        <label class="form-label">{{ trans('sw.discount_value')}}</label>
                        <input class="form-control" autocomplete="off"
                               placeholder="{{ trans('sw.discount_value')}}"
                               name="discount_value"
                               id="discount_value"
                               min="0"
                               max="0"
                               type="number" step="0.01">
                    </div>
                    @endif

                    @if((count($discounts) > 0) && ((in_array('editStoreDiscountGroup', (array)$swUser->permissions)) || $swUser->is_super_user))
                    <div class="mb-10 fv-row">
                        <label class="form-label">{{ trans('sw.discount')}}</label>
                        <select id="group_discount_id" name="group_discount_id" class="form-select" data-placeholder="{{ trans('sw.choose')}}">
                            <option></option>
                            <option value="0" type="0" amount="0">{{ trans('sw.choose')}}</option>
                            @foreach($discounts as $discount)
                                <option value="{{$discount->id}}" type="{{$discount->type}}" amount="{{$discount->amount}}">{{$discount->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    
                    <div class="mb-10 fv-row">
                        <label class="form-label">{{ trans('sw.amount_paid')}}</label>
                        <input id="create_amount_paid" class="form-control" name="amount_paid"
                               placeholder="{{ trans('sw.enter_amount_paid')}}" type="hidden" step="0.01" min="0"/>
                        <div class="form-control form-control-solid" id="create_amount_paid_show">-</div>
                        <input id="create_amount_remaining" type="hidden" class="form-control" name="amount_remaining" 
                               value="" placeholder="{{ trans('sw.amount_remaining')}}" disabled step="0.01" min="0"/>
                    </div>

                    <div class="mb-10 fv-row">
                        <label class="form-label">{{ trans('sw.paid_type')}}</label>
                        <select class="form-select" name="payment_type" id="pay_payment_type" data-control="select2" data-hide-search="true">
                            @foreach($payment_types as $payment_type)
                                <option value="{{$payment_type->payment_id}}"
                                        @if(@old('payment_type',$order->payment_type) == $payment_type->payment_id) selected="" @endif>{{$payment_type->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Payment card-->
            <!--begin::Form Actions-->
            <div class="d-flex justify-content-end mt-5">
                <button type="reset" class="btn btn-light me-3">{{ trans('admin.reset')}}</button>
                <button type="submit" class="btn btn-primary">
                    <i class="ki-outline ki-check fs-2"></i>
                    {{ trans('global.save')}}
                </button>
            </div>
            <!--end::Form Actions-->
        </div>
        <!--end::Aside column-->
    </form>
    <!--end::Store Order Form-->
@endsection
@section('scripts')
    @parent
    <script type="text/javascript"
            src="{{asset('resources/assets/admin/global/plugins/fuelux/js/spinner.min.js')}}"></script>

    <script>
        // Initialize Select2 for dropdowns
        $(document).ready(function() {
            // Initialize products select
            $('#products').select2({
                placeholder: "{{ trans('admin.choose')}}...",
                allowClear: true,
                width: '100%'
            });

            // Initialize group discount select if exists
            if ($('#group_discount_id').length) {
                $('#group_discount_id').select2({
                    placeholder: "{{ trans('sw.choose')}}",
                    width: '100%'
                });
            }
        });
    </script>
    
    <script>

        $('#member_id').keyup(function () {
            let member_id = $('#member_id').val();

            $.get("{{route('sw.getStoreMemberAjax')}}", {member_id: member_id},
                function (result) {
                    if (result) {
                        $('#store_member_name').html(result.name);
                        $('#store_member_phone').html(result.phone);
                        $('#member_id').val(result.code);
                        if(result.balance > 0){
                            $('#store_member_balance').removeClass('member_balance_less').addClass('member_balance_more').html(result.balance);
                        }else{
                            $('#store_member_balance').removeClass('member_balance_more').addClass('member_balance_less').html(result.balance);
                        }

                    } else {
                        $('#store_member_name').html('-');
                        $('#store_member_phone').html('-');
                        $('#store_member_balance').html('-').removeClass('member_balance_more member_balance_less');
                    }
                }
            );
        });

        @if(@env('STORE_ACTIVE_QUANTITY') == true)
        $("#products").select2().on('change', function (e) {
            var quantity = $('option:selected', this).attr('data-max-quantity');
            $('#quantity').attr({
                "max": quantity || 0
            });

            $('#spinner').spinner({value: 0, step: 1, min: (quantity > 0 ? 1 : 0), max: (quantity || 0)});
        });

        @else
        $("#products").select2();
        $('#spinner').spinner({value: 0, step: 1, min: 1});
        @endif

        $('#add_to_cart').click(function (e) {
            let count_tr = $('#cart_result tr').length;

            let product_id = $('#products').val();
            let product_price = parseFloat($('#products option:selected').attr('data-price')) || 0;
            let product_name = $('#products option:selected').attr('data-product-name');
            let product_quantity = parseFloat($('#quantity').val()) || 0;
            let total_price = parseFloat(Number(product_quantity) * Number(product_price)).toFixed(2);
            let all_total_price = parseFloat($('#total_price').html());
            let total_price_final = Number(all_total_price) + Number(total_price) ;
            total_price_final = parseFloat(total_price_final).toFixed(2);

            if (product_id && product_quantity) {
                $('#empty_cart').remove();
                $('#cart_result').append('<tr id="tr_product_' + product_id + '">' +
                    '<td class="ps-4">' + product_name + '</td>' +
                    '<td>' + product_price + '</td>' +
                    '<td>' + product_quantity + '</td>' +
                    '<td class="fw-bold">' + total_price + '</td>' +
                    '<td class="text-end pe-4">' +
                        '<button type="button" onclick="product_remove(this);return false;" data-price="' + total_price + '" ' +
                        'class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" title="{{ trans('admin.delete')}}">' +
                        '<i class="ki-outline ki-trash fs-2"></i>' +
                        '</button>' +
                    '</td>' +
                    '<input type="hidden" name="products[id][]" value="' + product_id + '">' +
                    '<input type="hidden" name="products[price][]" value="' + product_price + '">' +
                    '<input type="hidden" name="products[quantity][]" value="' + product_quantity + '">' +
                    '</tr>');
                $('#total_price').html(total_price_final);
                $('#total_price_value').val(total_price_final);

                let total_price_vat = total_price_final;
                @if(@$mainSettings->vat_details['vat_percentage'])
                    let vat = parseFloat((total_price_final) * ({{@$mainSettings->vat_details['vat_percentage'] / 100}})).toFixed(2);
                    total_price_vat = parseFloat(Number(total_price_final) + Number(vat)).toFixed(2);
                    $('#total_price_vat').html(total_price_vat);
                    $('#total_vat').val(vat);
                    $('#total_vat_show').html(vat);
                @endif

                $('#create_amount_paid').val(total_price_vat).attr('max', total_price_vat);
                $('#create_amount_paid_show').text(total_price_vat);
                $('#discount_value').attr('max', total_price_final);
                // $('#create_amount_remaining').val(0);
            }

        });

        function product_remove(e) {
            let row = $(e).closest('tr');
            let product_price = parseFloat(row.find('td:eq(3)').text()) || 0;
            row.remove();

            let cart_result = $('#cart_result').html().trim();
            let all_total_price = 0;
            $('#cart_result tr').each(function() {
                all_total_price += parseFloat($(this).find('td:eq(3)').text()) || 0;
            });
            
            let discount_value = parseFloat($('#discount_value').val()) || 0;
            let total_price_value = all_total_price;
            $('#total_price').html(total_price_value.toFixed(2));
            $('#total_price_value').val(total_price_value.toFixed(2));

            let total_price_vat = 0;
            @if(@$mainSettings->vat_details['vat_percentage'])
            let vat = parseFloat(total_price_value * ({{@$mainSettings->vat_details['vat_percentage'] / 100}})).toFixed(2);
            total_price_vat = parseFloat(Number(total_price_value) + Number(vat)).toFixed(2);
            
            $('#total_price_vat').html(total_price_vat);
            $('#total_vat').val(vat);
            $('#total_vat_show').html(vat);
            @else
                total_price_vat = total_price_value.toFixed(2);
            @endif

            if ($('#cart_result tr').length === 0) {
                $('#cart_result').html('<tr id="empty_cart">' +
                    '<td colspan="5" class="text-center text-muted py-10">' +
                    '<i class="ki-outline ki-basket-ok fs-3x"></i>' +
                    '<p class="mt-3">{{ trans('sw.cart_empty')}}</p>' +
                    '</td>' +
                    '</tr>');
            }

            $('#create_amount_paid').val(total_price_vat).attr('max', total_price_vat);
            $('#create_amount_paid_show').text(total_price_vat);
            $('#discount_value').attr('max', total_price_value).val(0);
            $('#create_amount_remaining').val(0);
            return false;
        }



        $("#create_amount_paid").change(function () {
            let vat = 0;
            let selectedActivitiesPriceWithVat = 0;
            let valueDiscount = 0;

            let selectedActivitiesPrice = $('#total_price_value').val() || 0;
            let valueAmountPaid = $('#create_amount_paid').val() || 0;
            valueAmountPaid = parseFloat(valueAmountPaid).toFixed(2);

            valueDiscount = $('#discount_value').val() || 0;
            valueDiscount = parseFloat(valueDiscount).toFixed(2);

            @if(@$mainSettings->vat_details['vat_percentage'])
                vat = (Number(selectedActivitiesPrice) - Number(valueDiscount)) * ({{@$mainSettings->vat_details['vat_percentage'] / 100}});
                vat = parseFloat(vat).toFixed(2);
            @endif

            selectedActivitiesPriceWithVat = Number(selectedActivitiesPrice) - Number(valueDiscount) + Number(vat);
            selectedActivitiesPriceWithVat = parseFloat(selectedActivitiesPriceWithVat).toFixed(2);
            let create_amount_remaining = selectedActivitiesPriceWithVat - valueAmountPaid;
            create_amount_remaining = parseFloat(create_amount_remaining).toFixed(2);
            $('#create_amount_remaining').val(create_amount_remaining);

        });


        $('#discount_value, #group_discount_id').change(function () {
            discount_value();
        });

        function discount_value(discount_amount = null) {
            let vat = 0;
            let priceWithVat = 0;
            let price = 0;
            let total_price = 0;

            price = $('#total_price_value').val() || 0;
            price = parseFloat(price);

            let discount_value = 0;
            if(discount_amount === null)
                discount_value = $('#discount_value').val();
            else
                discount_value = discount_amount

            @if(@$mainSettings->vat_details['vat_percentage'])
                vat = ( price - discount_value ) * ({{@$mainSettings->vat_details['vat_percentage'] / 100}});
                vat = Number(vat);
            @endif

            priceWithVat =  Number(price) + Number(vat) - Number(discount_value);
            
            if((discount_value > 0) && (price > 0)){
                total_price = Number(price) - Number(discount_value);
                $('#total_price').text(total_price.toFixed(2));
            } else {
                 $('#total_price').text(price.toFixed(2));
            }

            $('#total_price_vat').text(priceWithVat.toFixed(2));
            $('#total_vat_show').text(vat.toFixed(2));
            $('#create_amount_paid').val(priceWithVat.toFixed(2)).attr('max', priceWithVat.toFixed(2));
            $('#create_amount_paid_show').text(priceWithVat.toFixed(2));
            $('#create_amount_remaining').val(0);
        }
        $('#group_discount_id').on('change', function (event){
            let discount_id = $(this).find(":selected").val();
            let type = parseInt($(this).find(":selected").attr('type'));
            let amount = $(this).find(":selected").attr('amount');
            let price = (parseFloat($('#total_price_value').val()));
            let result = 0;
            if((type === 0) || (discount_id === 0)){
                $('#discount_value').val(amount);
                discount_value(amount);
            }else{
                result = parseFloat(Number(price) * (Number(amount)/100));
                $('#discount_value').val(result.toFixed(2));
                discount_value(result);
            }
        });
    </script>
@endsection

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
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.listNonMember') }}" class="text-muted text-hover-primary">{{ trans('sw.daily_clients')}}</a>
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

@section('page_body')
    <!--begin::Non Member Form-->
    <form method="post" action="" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
        {{csrf_field()}}
        
        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!--begin::Member Details-->
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
                    <div class="row">
                        <div class="col-lg-7">
                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="required form-label">{{ trans('sw.name')}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" name="name" class="form-control mb-2" 
                                       placeholder="{{ trans('sw.enter_name')}}" 
                                       value="{{ old('name', $member->name) }}" 
                                       required />
                                <!--end::Input-->
                            </div>
                            <!--end::Input group-->
                            
                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="required form-label">{{ trans('sw.phone')}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" name="phone" class="form-control mb-2" 
                                       placeholder="{{ trans('sw.enter_phone')}}" 
                                       value="{{ old('phone', $member->phone) }}" 
                                       required />
                                <!--end::Input-->
                            </div>
                            <!--end::Input group-->
                            
                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="form-label">{{ trans('sw.national_id')}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" name="national_id" class="form-control mb-2" 
                                       placeholder="{{ trans('sw.enter_national_id')}}" 
                                       value="{{ old('national_id', $member->national_id) }}" />
                                <!--end::Input-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="required form-label">{{ trans('sw.activities')}}</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <select class="form-select form-select-solid mb-2" id="activities" name="activities[]" multiple="multiple" data-placeholder="{{ trans('sw.activities')}}" required>
                                    <option></option>
                                    @foreach($activities as $activity)
                                        <option price="{{$activity->price}}" value='{{$activity->id}}' @if(is_array($selectedActivities) && in_array($activity->name, $selectedActivities)) selected="" @endif>{{$activity->name}}</option>
                                    @endforeach
                                </select>
                                <!--end::Select-->
                            </div>
                            <!--end::Input group-->
                        </div>

                        <div class="col-lg-5">
                            <!--begin::Summary-->
                            <div class="card bg-light-secondary p-5">
                                <div id="myTotal" class="d-flex justify-content-between fs-5 mb-3">
                                    <span class="fw-bold">{{ trans('sw.price')}}:</span>
                                    <span>{{(float)@$member->amount_before_discount}}</span>
                                </div>
                                <input type="hidden" value="{{@old('amount_before_discount', @$member->amount_before_discount)}}" id="myTotalInput">
                                <div id="myTotalAfterDiscount" class="d-flex justify-content-between fs-5 mb-3" @if(!$member->discount_value) style="display: none;" @endif>
                                    <span class="fw-bold">{{ trans('sw.after_discount')}}:</span>
                                    <span>{{(float)@$member->amount_before_discount - @$member->discount_value}}</span>
                                </div>
                                @if(@$mainSettings->vat_details['vat_percentage'])
                                <div id="myTotalWithVat" class="d-flex justify-content-between fs-5">
                                    <span class="fw-bold">{{ trans('sw.include_vat', ['vat' => @$mainSettings->vat_details['vat_percentage']])}}:</span>
                                    <span>{{(float)@($member->price + $member->amount_remaining + $member->discount_amount) }}</span>
                                </div>
                                @endif
                            </div>
                            <!--end::Summary-->

                            @php $invoice = $member->zatcaInvoice ?? null; @endphp
                            @if(config('sw_billing.zatca_enabled') && data_get($billingSettings ?? [], 'sections.non_members', true) && $member->id && $invoice)
                                <div class="card bg-light-primary p-5 mt-5">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-4">
                                        <div>
                                            <div class="text-muted fw-semibold">{{ trans('sw.invoice_number') ?? __('Invoice Number') }}</div>
                                            <div class="fw-bold fs-5">{{ $invoice->invoice_number }}</div>
                                        </div>
                                        <div>
                                            <div class="text-muted fw-semibold">{{ trans('sw.status') ?? __('Status') }}</div>
                                            <span class="badge {{ $invoice->zatca_status === 'approved' ? 'badge-light-success' : 'badge-light-warning' }} fw-bold text-uppercase">
                                                {{ $invoice->zatca_status }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="text-muted fw-semibold">{{ trans('sw.invoice_total_required') }}</div>
                                            <div class="fw-bold fs-5">{{ number_format($invoice->total_amount, 2) }} {{ trans('sw.app_currency') }}</div>
                                        </div>
                                        @if(!empty($invoice->zatca_qr_code))
                                            <div class="text-center">
                                                <div class="text-muted fw-semibold mb-2">{{ __('QR Code') }}</div>
                                                <img src="data:image/png;base64,{{ $invoice->zatca_qr_code }}" alt="ZATCA QR" style="height:100px;width:100px;"/>
                                            </div>
                                        @endif
                                    </div>
                                    <a href="{{ route('sw.showOrderSubscriptionNonMember', $member->id) }}" class="btn btn-sm btn-light-primary mt-4">
                                        <i class="ki-outline ki-eye fs-3"></i> {{ trans('global.view') ?? __('View') }} {{ trans('sw.invoice') }}
                                    </a>
                                </div>
                            @endif

                             @if((is_array($swUser->permissions) && in_array('editNonMemberDiscount', $swUser->permissions)) || $swUser->is_super_user)
                             <!--begin::Input group-->
                            <div class="mb-10 fv-row mt-5">
                                <!--begin::Label-->
                                <label class="form-label">{{ trans('sw.discount_value')}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input class="form-control mb-2" autocomplete="off" placeholder="{{ trans('sw.discount_value')}}"
                                       name="discount_value"
                                       id="discount_value"
                                       value="{{ old('discount_value', @($member->discount_value)) }}"
                                       min="0"
                                       max="{{@($member->amount_before_discount)}}"
                                       type="number" step="0.01">
                                <!--end::Input-->
                            </div>
                            <!--end::Input group-->
                            @endif
                            
                            @if((count($discounts) > 0) && ((is_array($swUser->permissions) && in_array('editNonMemberDiscountGroup', $swUser->permissions)) || $swUser->is_super_user))
                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="form-label">{{ trans('sw.discount')}}</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <select id="group_discount_id" name="group_discount_id" class="form-select form-select-solid mb-2" data-placeholder="{{ trans('sw.choose')}}">
                                    <option></option>
                                    <option value="0" type="0" amount="0">{{ trans('sw.choose')}}</option>
                                    @foreach($discounts as $discount)
                                        <option value="{{$discount->id}}" type="{{$discount->type}}" amount="{{$discount->amount}}">{{$discount->name}}</option>
                                    @endforeach
                                </select>
                                <!--end::Select-->
                            </div>
                            <!--end::Input group-->
                            @endif

                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="required form-label">{{ trans('sw.amount_paid')}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input id="amount_paid" class="form-control mb-2" name="price" value="{{ old('price', @($member->price)) }}"
                                       max="{{@round(($member->amount_before_discount + ($member->amount_before_discount*(@$mainSettings->vat_details['vat_percentage']/100)) - $member->discount_value), 2)}}"
                                       placeholder="{{ trans('sw.enter_amount_paid')}}" type="number" step="0.01" min="0"/>
                                <!--end::Input-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="required form-label">{{ trans('sw.amount_remaining')}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input id="amount_remaining" class="form-control mb-2" name="amount_remaining" value="{{@old('amount_remaining', @$member->amount_remaining)}}"
                                       placeholder="{{ trans('sw.enter_amount_remaining')}}" disabled step="0.01" type="number" min="0"/>
                                <!--end::Input-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="required form-label">{{ trans('sw.payment_type')}}</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <select class="form-select form-select-solid mb-2" name="payment_type">
                                    @foreach($payment_types as $payment_type)
                                        <option value="{{$payment_type->payment_id}}" @if(@old('payment_type',$member->member_subscription_info->payment_type) == $payment_type->payment_id) selected="" @endif>{{$payment_type->name}}</option>
                                    @endforeach
                                </select>
                                <!--end::Select-->
                            </div>
                            <!--end::Input group-->
                            
                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="form-label">{{ trans('sw.notes')}}</label>
                                <!--end::Label-->
                                <!--begin::Textarea-->
                                <textarea rows="2" maxlength="255" name="notes" class="form-control mb-2">{{@old('notes', @$member->notes)}}</textarea>
                                <!--end::Textarea-->
                            </div>
                            <!--end::Input group-->
                        </div>
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Member Details-->

             @if(Route::current()->getName() == 'sw.editNonMember')
            <!--begin::Previous Payment Info-->
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{ trans('sw.payment_history')}}</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">{{ trans('sw.prev_amount_paid')}}</label>
                            <div id="prev_amount_paid" class="fw-bold">{{@old('price', @$member->price)}}</div>
                            <input value="{{@old('price', @$member->price)}}" type="hidden" id="prev_amount_paid_input">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ trans('sw.diff_amount_paid')}}</label>
                            <div id="diff_amount_paid" class="fw-bold">0</div>
                            <input value="0" type="hidden" id="diff_amount_paid_input" name="diff_amount_paid_input">
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Previous Payment Info-->
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
    @parent
    <script>
        // Initialize Select2 for dropdowns
        $(document).ready(function() {
            let amount_paid_manually_edited = {{ isset($member->id) ? 'true' : 'false' }};

            // Initialize activities multi-select
            $('#activities').select2({
                placeholder: "{{ trans('sw.activities')}}",
                allowClear: true,
                width: '100%'
            });

            // Initialize discount group select if exists
            if ($('#group_discount_id').length) {
                $('#group_discount_id').select2({
                    placeholder: "{{ trans('sw.choose')}}",
                    width: '100%'
                });
            }

            $('#activities').change(function () {
                let selectedActivitiesPrice = 0;
                $.each($("#activities option:selected"), function () {
                    selectedActivitiesPrice += parseFloat($(this).attr('price'));
                });
                $('#myTotalInput').val(selectedActivitiesPrice.toFixed(2));
                amount_paid_manually_edited = false;
                calculate_price();
            });

            $("#amount_paid").change(function () {
                amount_paid_manually_edited = true;
                calculate_price();
            });

            $("#discount_value, #group_discount_id").change(function () {
                amount_paid_manually_edited = false;
                calculate_price();
            });
            
            function calculate_price() {
                let price = parseFloat($('#myTotalInput').val()) || 0;
                let discount_value = parseFloat($('#discount_value').val()) || 0;
                
                let group_discount_option = $('#group_discount_id').find(":selected");
                if (group_discount_option.length > 0 && group_discount_option.val() && group_discount_option.val() !== '0') {
                    let type = parseInt(group_discount_option.attr('type'));
                    let amount = parseFloat(group_discount_option.attr('amount'));
                    if (type === 1) { // Percentage
                        discount_value = parseFloat(Number(price) * (Number(amount)/100));
                    } else { // Fixed
                        discount_value = amount;
                    }
                    $('#discount_value').val(discount_value.toFixed(2));
                }
                
                let price_after_discount = price - discount_value;
                let vat = 0;
                @if(@$mainSettings->vat_details['vat_percentage'])
                    vat = price_after_discount * ({{@$mainSettings->vat_details['vat_percentage'] / 100}});
                @endif
                let total_with_vat = price_after_discount + vat;

                $('#myTotal span:last-child').text(price.toFixed(2));
                if (discount_value > 0) {
                    $('#myTotal').css('text-decoration', 'line-through');
                    $('#myTotalAfterDiscount').show().find('span:last-child').text(price_after_discount.toFixed(2));
                } else {
                    $('#myTotal').css('text-decoration', 'none');
                    $('#myTotalAfterDiscount').hide();
                }

                $('#myTotalWithVat span:last-child').text(total_with_vat.toFixed(2));

                let amount_paid;
                if (!amount_paid_manually_edited) {
                    amount_paid = total_with_vat;
                } else {
                    amount_paid = parseFloat($('#amount_paid').val()) || 0;
                }
                $('#amount_paid').val(amount_paid.toFixed(2)).attr('max', total_with_vat.toFixed(2));

                let amount_remaining = total_with_vat - amount_paid;
                $('#amount_remaining').val(amount_remaining.toFixed(2));

                @if(Route::current()->getName() == 'sw.editNonMember')
                let prev_amount_paid_input = parseFloat($('#prev_amount_paid_input').val()) || 0;
                let diff = amount_paid - prev_amount_paid_input;
                $('#diff_amount_paid').text(diff.toFixed(2));
                $('#diff_amount_paid_input').val(diff.toFixed(2));
                @endif
            }

            // Initial calculation on page load
            calculate_price();
        });
    </script>
@endsection

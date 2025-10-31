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

@section('page_body')
    <!--begin::PT Member Create Form-->
    <form method="post" action="" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
        {{csrf_field()}}
        
        <!--begin::Aside column-->
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-400px mb-7 me-lg-10">
            <!--begin::PT Member card-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{ trans('sw.pt_member')}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.member_id')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input id="member_id" value="{{ old('member_id', $member->member_id) }}"
                               placeholder="{{ trans('sw.enter_member_id')}}"
                               name="member_id" type="text" class="form-control mb-2" required>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::PT Member card-->
            <!--begin::Member Info card-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{ trans('sw.member_info')}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <div class="d-flex flex-column gap-5">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">{{ trans('sw.name')}}:</span>
                            <span id="pt_member_name">-</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">{{ trans('sw.barcode')}}:</span>
                            <span id="pt_member_barcode">-</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">{{ trans('sw.membership')}}:</span>
                            <span id="pt_member_membership">-</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">{{ trans('sw.expire_date')}}:</span>
                            <span id="pt_member_expire_date">-</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">{{ trans('sw.amount_remaining')}}:</span>
                            <span id="pt_member_amount_remaining">-</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">{{ trans('sw.status')}}:</span>
                            <span id="pt_member_status_name">-</span>
                        </div>
                    </div>
                </div>
                <!--end::Card body-->
                            </div>
            <!--end::Member Info card-->
                            </div>
        <!--end::Aside column-->
        
        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!--begin::Subscription Details-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{ trans('sw.subscription_details')}}</h2>
                    </div>
            </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <div class="row">
                        <div class="col-lg-6">
                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="required form-label">{{ trans('sw.pt_subscription')}}</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <select id="pt_subscription_id" name="pt_subscription_id" class="form-select select2" data-placeholder="{{ trans('admin.choose')}}..." required>
                                    <option></option>
                        @foreach($subscriptions as $subscription)
                                        <option value="{{$subscription->id}}" @if($subscription->id == old('pt_subscription_id', $member->pt_subscription_id)) selected="" @endif>{{$subscription->name}}</option>
                        @endforeach
                    </select>
                                <!--end::Select-->
                </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="required form-label">{{ trans('sw.pt_class')}}</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <select id="pt_class_id" name="pt_class_id" class="form-select select2" data-placeholder="{{ trans('admin.choose')}}..." required>
                                    <option></option>
                        @foreach($classes as $class)
                                        <option data-price="{{$class->price}}"  data-trainers="{{@implode(',', array_filter($class->pt_subscription_trainer->pluck('pt_trainer_id')->toArray()))}}" class="classes_of_subscription_{{$class->pt_subscription_id}}" value="{{$class->id}}" @if($class->id == old('pt_class_id', $member->pt_class_id)) selected="" @endif>{{$class->name}}</option>
                        @endforeach
                    </select>
                                <!--end::Select-->
                                <div id="class_limit_msg" class="mt-2"></div>
                </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="required form-label">{{ trans('sw.pt_trainer')}}</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <select id="pt_trainer_id" name="pt_trainer_id" class="form-select select2" data-placeholder="{{ trans('admin.choose')}}..." required>
                                    <option></option>
                        @foreach($trainers as $trainer)
                                        <option data-percentage="{{$trainer->percentage ?? 0}}" value="{{$trainer->id}}" @if($trainer->id == old('pt_trainer_id', $member->pt_trainer_id)) selected="" @endif>{{$trainer->name}}</option>
                        @endforeach
                    </select>
                                <!--end::Select-->
                </div>
                            <!--end::Input group-->
            </div>
                        <div class="col-lg-6">
                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <label class="form-label">{{ trans('sw.trainer_percentage_for_member')}} </label>
                                <div class="input-group">
                                    <input class="form-control" placeholder="{{ trans('sw.enter_percentage')}}"
                               name="trainer_percentage" id="trainer_percentage"
                               value="{{ old('trainer_percentage', $member->trainer_percentage) }}"
                               type="number" min="0" max="100" >
                                    <span class="input-group-text">%</span>
                    </div>
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <label class="required form-label">{{ trans('sw.membership_date')}}</label>
                                <div class="input-group date-picker input-daterange">
                                    <input type="text" class="form-control" id="editCustomStartDate"  name="joining_date" value="{{ old('joining_date', $member->joining_date) ?? \Carbon\Carbon::now()->format('Y-m-d') }}" autocomplete="off" placeholder="{{ trans('sw.joining_date')}}" required>
                                    <span class="input-group-text">{{ trans('sw.to')}}</span>
                                    <input type="text" class="form-control" id="editCustomExpireDate"  name="expire_date" value="" autocomplete="off" placeholder="{{ trans('sw.expire_date')}}" required>
                </div>
            </div>
                            <!--end::Input group-->
                    </div>
                </div>
            </div>
                <!--end::Card body-->
            </div>
            <!--end::Subscription Details-->

            <!--begin::Payment Details-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{ trans('sw.payment_details')}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <div class="row">
                        <div class="col-lg-6">
                            <!--begin::Input group-->
                            <div class="mb-10 fv-row" @if(!(in_array('editPTMemberDiscount', (array)$swUser->permissions) || $swUser->is_super_user)) style="display: none;" @endif>
                                <label class="form-label">{{ trans('sw.discount_value')}} </label>
                                <input class="form-control" autocomplete="off" placeholder="{{ trans('sw.discount_value')}}"
                                       name="discount_value" id="discount_value" value="0" min="0" type="number" step="0.01">
                </div>
                            <!--end::Input group-->
                @if((count($discounts) > 0) && ((in_array('editPTMemberDiscountGroup', (array)$swUser->permissions)) || $swUser->is_super_user))
                             <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <label class="form-label">{{ trans('sw.discount')}} </label>
                                <select id="group_discount_id" name="group_discount_id" class="form-select select2" data-placeholder="{{ trans('admin.choose')}}...">
                                    <option></option>
                        @foreach($discounts as $discount)
                            <option value="{{$discount->id}}" type="{{$discount->type}}" amount="{{$discount->amount}}">{{$discount->name}}</option>
                        @endforeach
                    </select>
                </div>
                            <!--end::Input group-->
                @endif
                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <label class="required form-label">{{ trans('sw.amount_paid')}}</label>
                                <input id="create_amount_paid" class="form-control" name="amount_paid" value="{{ old('amount_paid', isset($member->id) ? @($member->pt_class->price - $member->amount_remaining) : '0.00') }}"
                           placeholder="{{ trans('sw.enter_amount_paid')}}" type="number" step="0.01" min="0"/>
                </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <label class="required form-label">{{ trans('sw.payment_type')}}</label>
                                <select class="form-select select2" name="payment_type" data-placeholder="{{ trans('admin.choose')}}...">
                        @foreach($payment_types as $payment_type)
                            <option value="{{$payment_type->payment_id}}" @if(@old('payment_type',$member->member_subscription_info->payment_type) == $payment_type->payment_id) selected="" @endif >{{$payment_type->name}}</option>
                        @endforeach
                    </select>
                </div>
                            <!--end::Input group-->
                        </div>
                        <div class="col-lg-6">
                            <!--begin::Price-->
                            <div class="mb-10 fv-row d-flex flex-column gap-5 p-5 bg-light-secondary rounded">
                                <div id="myTotal" class="d-flex justify-content-between fs-5">
                                    <span class="fw-bold">{{ trans('sw.price')}}</span>
                                    <span>0.00</span>
                                </div>
                                <div id="myTotalAfterDiscount" class="d-flex justify-content-between fs-5" style="display: none;">
                                    <span class="fw-bold">{{ trans('sw.after_discount')}}</span>
                                    <span>0.00</span>
                                </div>
                                @if(@$mainSettings->vat_details['vat_percentage'])
                                <div id="myTotalWithVat" class="d-flex justify-content-between fs-5">
                                    <span class="fw-bold">{{ trans('sw.include_vat', ['vat' => @$mainSettings->vat_details['vat_percentage']])}}</span>
                                    <span>0.00</span>
                                </div>
                                @endif
                            </div>
                            <!--end::Price-->
                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <label class="required form-label">{{ trans('sw.amount_remaining')}}</label>
                                <input id="create_amount_remaining" class="form-control" name="amount_remaining" value="{{ old('amount_remaining', isset($member->id) ? @$member->amount_remaining : '0.00') }}"
                                       placeholder="{{ trans('sw.enter_amount_remaining')}}" disabled type="number" min="0"  step="0.01"/>
            </div>
                            <!--end::Input group-->
                             <!--begin::Input group-->
                             <div class="mb-10 fv-row">
                                <label class="form-label">{{ trans('sw.notes')}} </label>
                    <textarea rows="2" maxlength="255" name="notes" class="form-control">{{@old('notes', @$member->member_subscription_info->notes)}}</textarea>
                </div>
                            <!--end::Input group-->
                    </div>
                </div>
            </div>
                <!--end::Card body-->
            </div>
            <!--end::Payment Details-->

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
        $(document).ready(function() {
            let amount_paid_manually_edited = {{ isset($member->id) ? 'true' : 'false' }};

            $('.input-daterange').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true
        });

        pt_subscription_id = $("#pt_subscription_id").val();
        $("#pt_class_id option").hide();
            $("#pt_class_id option[value='']").show();
        $("#pt_class_id option.classes_of_subscription_" + pt_subscription_id).show();

        $("#pt_trainer_id option").hide();
            $("#pt_trainer_id option[value='']").show();

        $("#pt_subscription_id").change(function (e) {
            let pt_subscription_id = $("#pt_subscription_id").val();
                $("#pt_class_id").val(null).trigger('change');
            $("#pt_class_id option").hide();
            $("#pt_class_id option.classes_of_subscription_" + pt_subscription_id).show();
                $("#pt_class_id option[value='']").show();
        });

        $("#pt_trainer_id").change(function (e) {
            let trainer_percentage = $('#pt_trainer_id option:selected').attr('data-percentage');
            $('#trainer_percentage').val(trainer_percentage);
        });

        $('#member_id').keyup(function () {
            let member_id = $('#member_id').val();

            $.get("{{route('sw.getPTMemberAjax')}}", {  member_id: member_id },
                function(result){
                    if(result){
                        $('#pt_member_name').html(result.name);
                        $('#pt_member_barcode').html(result.code);
                        $('#pt_member_membership').html(result.member_subscription_info.subscription.name);
                        $('#pt_member_expire_date').html(result.member_subscription_info.expire_date_str);
                        $('#pt_member_amount_remaining').html(result.member_subscription_info.amount_remaining);
                        $('#pt_member_status_name').html(result.member_subscription_info.status_name);
                            $('#editCustomExpireDate').val(result.member_subscription_info.expire_date_str);
                    }else{
                        $('#pt_member_name').html('-');
                        $('#pt_member_barcode').html('-');
                        $('#pt_member_membership').html('-');
                        $('#pt_member_expire_date').html('-');
                        $('#pt_member_amount_remaining').html('-');
                        $('#pt_member_status_name').html('-');
                            $('#editCustomExpireDate').val('{{\Carbon\Carbon::now()->toDateString()}}');
                    }
                }
            );
        });

        $("#create_amount_paid").change(function () {
                amount_paid_manually_edited = true;
                calculate_price(false);
            });

            $("#discount_value, #group_discount_id").change(function () {
                calculate_price(false);
        });

        $('#pt_class_id').change(function () {
                amount_paid_manually_edited = false;
                calculate_price(true);

            let element = $('#pt_class_id').find('option:selected');
            let trainers = element.attr("data-trainers");
            $.get("{{route('sw.getPTTrainerAjax')}}", {  trainers: trainers },
                function(result){
                    $('#pt_trainer_id').html(result).show();
                }
            );

            var pt_class_id_selected = $('#pt_class_id').find(":selected").val();
            class_limit_member(pt_class_id_selected);
            });

            function calculate_price(is_class_change = false) {
                let price = parseFloat($('#pt_class_id option:selected').attr('data-price')) || 0;
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
                if (is_class_change || !amount_paid_manually_edited) {
                    amount_paid = total_with_vat;
                } else {
                    amount_paid = parseFloat($('#create_amount_paid').val()) || 0;
                }
                $('#create_amount_paid').val(amount_paid.toFixed(2)).attr('max', total_with_vat.toFixed(2));
                
                let amount_remaining = total_with_vat - amount_paid;
                $('#create_amount_remaining').val(amount_remaining.toFixed(2));
            }

        function class_limit_member(pt_class_id){
            if(pt_class_id){
                $.get("{{route('sw.ptClassActiveMemberAjax')}}", {pt_class_id: pt_class_id},
                    function(result){
                            var class_limit_msg = `<div class="alert alert-info">{{ trans('sw.active_members_now', ['member_count' => '@@member_count'])}}</div>`;
                        class_limit_msg = class_limit_msg.replace('@@member_count', result)
                        $('#class_limit_msg').html(class_limit_msg);
                    }
                );
            }
        }
        class_limit_member('{{$member->pt_class_id}}');

            // Initial calculation
            calculate_price(false);
        });
    </script>
@endsection

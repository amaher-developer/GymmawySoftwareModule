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
        .subscription_class_li {
            display: none;
        }
        .class_trainer_li {
            display: none;
        }
        .tag-green {
            background-color: #4caf50 !important;
            color: #fff;
        }
        .tag-orange {
            background-color: #fd7e14 !important;
            color: #fff;
        }
        .tag-purple {
            background-color: #8500ff !important;
            color: #fff;
            font-size: unset;
        }
        .tag {
            color: #14112d;
            background-color: #ecf0fa;
            border-radius: 3px;
            padding: 0 .5rem;
            line-height: 2em;
            display: -ms-inline-flexbox;
            display: inline-flex;
            cursor: default;
            font-weight: 400;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        .member-info li{
            list-style-type: none;
            line-height: 34px;
        }
        .member-info{
            background: #9e9e9e73;
            border-radius: 8px !important;
            margin: 0px;
            padding: 10px 0;
        }
        #myTotalAfterDiscount {
            display: none;
        }
    </style>
@endsection
@section('page_body')
    <!--begin::PT Member Edit Form-->
    <form method="post" action="" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
        {{csrf_field()}}
        
        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!--begin::PT Member Details-->
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
                        <label class="required form-label">{{ trans('sw.member_id')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input id="member_id" value="{{ old('member_id', $member->member->code) }}" disabled
                               placeholder="{{ trans('sw.enter_member_id')}}"
                               name="member_id" type="text" class="form-control mb-2" required>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Member Info-->
                    <div class="mb-10 fv-row">
                        <div class="member-info row">
                            <div class="col-md-6">
                                <b>{{ trans('sw.name')}}:</b> <span id="pt_member_name">{{$member->member->name}}</span>
                            </div>
                            <div class="col-md-6">
                                <b>{{ trans('sw.barcode')}}:</b> <span id="pt_member_barcode">{{$member->member->code}}</span>
                            </div>
                            <div class="col-md-6">
                            <b>{{ trans('sw.membership')}}:</b> <span id="pt_member_membership">{{$member->member->member_subscription_info->subscription->name}}</span>
                        </li>
                        <li class="col-md-6">
                            <b>{{ trans('sw.expire_date')}}:</b> <span id="pt_member_expire_date">{{$member->member->member_subscription_info->expire_date}}</span>
                        </li>
                        <li class="col-md-6">
                            <b>{{ trans('sw.amount_remaining')}}:</b> <span id="pt_member_amount_remaining">{{$member->member->member_subscription_info->amount_remaining}}</span>
                        </li>
                        <li class="col-md-6">
                            <b>{{ trans('sw.status')}}:</b> <span id="pt_member_status_name">{{$member->member->member_subscription_info->status_name}}</span>
                        </li>
                    </ul>


                </span>
            </div>

            <div class="form-group col-md-12 clearfix"><hr/></div>

            <div class="form-group col-md-12">
                <label class="col-md-3  control-label">{{ trans('sw.pt_subscription')}} <span class="required">*</span></label>
                <div class="col-md-9">
                    <select id="pt_subscription_id" name="pt_subscription_id" class="form-control select2" required>
                        <option value="">{{ trans('admin.choose')}}...</option>
                        @foreach($subscriptions as $subscription)
                            <option value="{{$subscription->id}}" data-trainers="{{@implode(',', array_filter($subscription->pt_trainers->pluck('id')->toArray()))}}" @if($subscription->id == old('pt_subscription_id', $member->pt_subscription_id)) selected="" @endif required>{{$subscription->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group col-md-12">
                <label class="col-md-3  control-label">{{ trans('sw.pt_class')}} <span class="required">*</span></label>
                <div class="col-md-9">
                    <select id="pt_class_id" name="pt_class_id" class="form-control select2" required>
                        <option value="">{{ trans('admin.choose')}}...</option>
                        @foreach($classes as $class)
                            <option data-price="{{$class->price}}" class="classes_of_subscription_{{$class->pt_subscription_id}}" value="{{$class->id}}" @if($class->id == old('pt_class_id', $member->pt_class_id)) selected="" @endif required>{{$class->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group col-md-12">
                <label class="col-md-3" ></label>
                <div class="col-md-9">
                    <div id="class_limit_msg"></div>
                </div>
            </div>
            <div class="form-group col-md-12">
                <label class="col-md-3  control-label">{{ trans('sw.pt_trainer')}} <span class="required">*</span></label>
                <div class="col-md-9">
                    <select id="pt_trainer_id" name="pt_trainer_id" class="form-control select2" required>
                        <option value="">{{ trans('admin.choose')}}...</option>
                        @foreach($trainers as $trainer)
                            <option  value="{{$trainer->id}}" @if($trainer->id == old('pt_trainer_id', $member->pt_trainer_id)) selected="" @endif required>{{$trainer->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group col-md-12">
                <label class="col-md-3  control-label">{{ trans('sw.trainer_percentage_for_member')}} </label>
                <div class="col-md-3">
                    <div class="input-group "  >
                        <input class="form-control  form-control-inline input-medium "  placeholder="{{ trans('sw.enter_percentage')}}"
                               name="trainer_percentage" id="trainer_percentage"
                               value="{{ old('trainer_percentage', $member->trainer_percentage) }}"
                               type="number" min="0" max="100" >
                        <span class="input-group-btn">
                        <button class="btn default" type="button">%</button>
                    </span>
                    </div>
                </div>
            </div>

            <div class="form-group col-md-12 clearfix"><br/></div>

{{--            <div class="form-group col-md-12">--}}
{{--                <label class="col-md-3  control-label">{{ trans('sw.joining_date')}} </label>--}}
{{--                <div class="col-md-3">--}}
{{--                    <div class="input-group input-medium date date-picker" data-date-format="yyyy-mm-dd" >--}}

{{--                    <input class="form-control form-control-inline input-medium " autocomplete="off" placeholder="{{ trans('sw.joining_date')}}"--}}
{{--                           name="joining_date"--}}
{{--                           value="{{ old('joining_date', \Carbon\Carbon::parse($member->joining_date)->format('Y-m-d')) ?? \Carbon\Carbon::now()->format('Y-m-d') }}"--}}
{{--                           type="text" >--}}
{{--                    <span class="input-group-btn">--}}
{{--                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>--}}
{{--                    </span>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}



            <div class="form-group col-md-12">
                <label class="col-md-3  control-label">{{ trans('sw.membership_date')}} <span class="required">*</span></label>

                <div  class="col-md-9" style="margin-bottom: 7px;">
                    <div class="input-group date date-picker input-daterange" id="daterange" data-date-format="yyyy-mm-dd" >
                        <input type="text" class="form-control" id="editCustomStartDate"  name="joining_date" data-date-format="yyyy-mm-dd"  value="{{ old('joining_date', \Carbon\Carbon::parse($member->joining_date)->format('Y-m-d')) ?? \Carbon\Carbon::now()->format('Y-m-d') }}" autocomplete="off" title="{{ trans('sw.joining_date')}}" placeholder="{{ trans('sw.joining_date')}}" required>
                        <span class="input-group-addon">
                                </span>
                        <input type="text" class="form-control" id="editCustomExpireDate"  name="expire_date"  data-date-format="yyyy-mm-dd"   value="{{ old('joining_date', \Carbon\Carbon::parse($member->expire_date)->format('Y-m-d')) ?? \Carbon\Carbon::now()->format('Y-m-d') }}" autocomplete="off" title="{{ trans('sw.expire_date')}}" placeholder="{{ trans('sw.expire_date')}}" required>
                    </div>
                </div>
            </div>

            <div class="form-group col-md-12 clearfix"><hr/></div>

            <div class="form-group col-md-12">

                <label class="col-md-3  control-label">{{ trans('sw.price')}}</label>
                <div class="col-md-3">
                    <span class="tag tag-green font-weight-bolder rounded-3"  id="myTotal" @if($member->discount_value) style="text-decoration: line-through" @endif>{{ trans('sw.price')}} = {{(float)@$member->pt_class->price  }}</span>
                    <span class="tag tag-purple font-weight-bolder rounded-3" id="myTotalAfterDiscount" @if($member->discount_value) style="display: inline;" @endif>{{ trans('sw.after_discount')}} = {{(float)@$member->pt_class->price - @$member->discount_value}}</span>

                </div>
                <div class="col-md-2"></div>
                <label class="col-md-2 control-label">{{ trans('sw.discount_value')}} </label>
                <div class="col-md-2">
                    <input class="form-control form-control-inline " autocomplete="off" placeholder="{{ trans('sw.discount_value')}}"
                           name="discount_value"
                           id="discount_value"
                           value="{{ old('discount_value', $member->discount_value) ?? 0 }}"
                           min="0"
                           max="{{@($member->pt_class->price)}}"
                           type="number"  step="0.01">
                </div>
                {{--                <div class="col-md-2">--}}
                {{--                    <select id="discount_type" name="discount_type" class="form-control select2" required>--}}
                {{--                        <option value="1" @if($member->discount_type == 1) selected="" @endif>{{ trans('sw.amount')}}</option>--}}
                {{--                        <option value="2" @if($member->discount_type == 2) selected="" @endif>{{ trans('sw.percentage')}}</option>--}}
                {{--                    </select>--}}
                {{--                </div>--}}

            </div>
            @if(@$mainSettings->vat_details['vat_percentage'])
                <div class="form-group total_amount_renew_div  col-md-12">
                    <label class="col-md-3  control-label">{{ trans('sw.include_vat', ['vat' => @$mainSettings->vat_details['vat_percentage']])}}  </label>

                    <div class="col-md-3">
                        <span class="tag tag-orange font-weight-bolder rounded-3" id="myTotalWithVat">{{ trans('sw.price')}} = {{@round($member->amount_before_discount - $member->discount_value, 2) +round($member->vat)}}</span>

                    </div>
                    <div class="col-md-2"></div>

                </div>
            @endif
            <div class="form-group col-md-12">
                <label class="col-md-3 control-label">{{ trans('sw.amount_paid')}} <span class="required">*</span></label>
                <div class="col-md-3">
                    <input id="create_amount_paid" class="form-control" name="amount_paid" value="{{ old('amount_paid', @($member->amount_paid)) }}"
                           max="{{@round($member->amount_before_discount - $member->discount_value, 2) +round($member->vat)}}"
                           placeholder="{{ trans('sw.enter_amount_paid')}}" type="number" min="0"  step="0.01"/>
                </div>
                <div class="col-md-2"></div>
                <label class="col-md-2 control-label">{{ trans('sw.amount_remaining')}} <span class="required">*</span></label>
                <div class="col-md-2">
                    <input  id="create_amount_remaining" class="form-control" name="amount_remaining" value="{{@old('amount_remaining', @$member->amount_remaining)}}"
                            placeholder="{{ trans('sw.enter_amount_remaining')}}" disabled type="number" min="0"  step="0.01"/>
                </div>
            </div>



            <div class="form-group col-md-12">
                <label class="col-md-3 control-label">{{ trans('sw.notes')}} </label>
                <div class="col-md-3">
                    <textarea rows="2" maxlength="255" name="notes" class="form-control">{{@old('notes', @$member->notes)}}</textarea>
                </div>

            </div>


            <div style="clear: both;float: none"><hr/></div>
            <div style="color: darkgray;">
                <div class="form-group total_amount_renew_div col-md-12">
                    <label class="col-md-6 control-label">{{ trans('sw.prev_amount_paid')}} </label>
                    <div class="col-md-6">
                        <p id="prev_amount_paid">0</p>
                        <input value="" type="hidden" id="prev_amount_paid_input">
                    </div>
                </div>
                <div class="form-group total_amount_renew_div col-md-12">
                    <label class="col-md-6 control-label">{{ trans('sw.diff_amount_paid')}} </label>
                    <div class="col-md-6">
                        <p id="diff_amount_paid">0</p>
                    </div>
                </div>
            </div>

        </div><!-- end total amount paid and amount remaining  div -->


            <div class="form-actions" style="clear:both;">
                <div class="row">
                    <div class="col-md-offset-3 col-md-9">
                        <button type="submit" class="btn green">{{ trans('global.save')}}</button>
                        <input type="reset" class="btn default" value="{{ trans('admin.reset')}}">
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
@section('sub_scripts')
    <script src="{{asset('resources/assets/admin/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"
            type="text/javascript"></script>
    <script src="{{asset('/')}}resources/assets/admin/global/scripts/metronic.js" type="text/javascript"></script>
    <script src="{{asset('/')}}resources/assets/admin/pages/scripts/components-pickers.js"></script>
    <script>

        jQuery(document).ready(function() {
            ComponentsPickers.init();
        });
    </script>
    <script>

        pt_subscription_id = $("#pt_subscription_id").val();
        pt_class_id = $("#pt_class_id").val();
        $("#pt_class_id option").hide();
        $("#pt_class_id option:first").show();
        $("#pt_class_id option.classes_of_subscription_" + pt_subscription_id).show();

        $("#pt_trainer_id option").hide();
        $("#pt_trainer_id option:first").show();


        $('#prev_amount_paid_input').val('{{$member->amount_paid}}');
        $('#prev_amount_paid').html('{{$member->amount_paid}}');

        $("#pt_subscription_id").change(function (e) {
            var pt_subscription_id = $("#pt_subscription_id").val();
            $("#pt_class_id option").hide();
            $("#pt_class_id option:selected").removeAttr('selected');
            $("#pt_class_id option.classes_of_subscription_" + pt_subscription_id).show();
            $("#pt_class_id option:first").show();
            $("#pt_class_id option:first").attr('selected', true);


            var element = $('#pt_subscription_id').find('option:selected');
            var trainers = element.attr("data-trainers");
            $.get("{{route('sw.getPTTrainerAjax')}}", {  trainers: trainers },
                function(result){
                    $('#pt_trainer_id').html(result).show();
                }
            );

        });

        $("#pt_trainer_id").change(function (e) {
            let trainer_percentage = $('#pt_trainer_id option:selected').attr('data-percentage');
            $('#trainer_percentage').val(trainer_percentage);
        });

        $('#member_id').keyup(function () {
            var member_id = $('#member_id').val();

            $.get("{{route('sw.getPTMemberAjax')}}", {  member_id: member_id },
                function(result){
                    if(result){
                        $('#pt_member_name').html(result.name);
                        $('#pt_member_barcode').html(result.code);
                        $('#pt_member_membership').html(result.member_subscription_info.subscription.name);
                        $('#pt_member_expire_date').html(result.member_subscription_info.expire_date);
                        $('#pt_member_amount_remaining').html(result.member_subscription_info.amount_remaining);
                        $('#pt_member_status_name').html(result.member_subscription_info.status_name);
                    }else{
                        $('#pt_member_name').html('-');
                        $('#pt_member_barcode').html('-');
                        $('#pt_member_membership').html('-');
                        $('#pt_member_expire_date').html('-');
                        $('#pt_member_amount_remaining').html('-');
                        $('#pt_member_status_name').html('-');
                    }
                }
            );
        });



        $("#create_amount_paid").change(function () {
            let selectedMembershipPrice = 0;
            $.each($("#pt_class_id option:selected"), function () {
                selectedMembershipPrice = selectedMembershipPrice + (parseFloat($(this).attr('data-price')));
            });
            let vat = 0;
            let selectedMembershipPriceWithVat = 0;
            let valueAmountPaid = $('#create_amount_paid').val();

            let valueDiscount = 0;
            valueDiscount = $('#discount_value').val();
            @if(@$mainSettings->vat_details['vat_percentage'])
                vat = (parseFloat(selectedMembershipPrice)- parseFloat(valueDiscount)) * ({{@$mainSettings->vat_details['vat_percentage'] / 100}});
            @endif
            selectedMembershipPriceWithVat = parseFloat(selectedMembershipPrice) - parseFloat(valueDiscount) + vat;

            $('#create_amount_remaining').val(Number(selectedMembershipPriceWithVat - valueAmountPaid ).toFixed(2));
            // $('#create_amount_paid').attr('max', Number(selectedMembershipPriceWithVat - valueAmountPaid).toFixed(2));

            let prev_amount_paid_input = $('#prev_amount_paid_input').val();
            $('#diff_amount_paid').html(Number(valueAmountPaid - prev_amount_paid_input).toFixed(2));
        });

        $('#pt_class_id').change(function () {
            let selectedMembershipPrice = 0;
            $.each($("#pt_class_id option:selected"), function () {
                selectedMembershipPrice = selectedMembershipPrice + (parseFloat($(this).attr('data-price')));
            });
            let selectedMembershipPriceWithVat = 0;
            let vat = 0;
            @if(@$mainSettings->vat_details['vat_percentage'])
                vat = selectedMembershipPrice * ({{@$mainSettings->vat_details['vat_percentage'] / 100}});
            @endif
                selectedMembershipPriceWithVat = Number(parseFloat(selectedMembershipPrice) + parseFloat(vat)).toFixed(2);

            $('#myTotal').text("{{ trans('sw.price')}} = " + parseFloat(selectedMembershipPrice).toFixed(2)).css('text-decoration', 'unset');
            $('#myTotalWithVat').val(selectedMembershipPriceWithVat).attr('max', selectedMembershipPriceWithVat).text("{{ trans('sw.price')}} = " + selectedMembershipPriceWithVat).css('text-decoration', 'unset');
            $('#create_amount_paid').val(selectedMembershipPriceWithVat).attr('max', selectedMembershipPriceWithVat);
            $('#create_amount_remaining').val(0);
            $('#discount_value').attr('max', selectedMembershipPriceWithVat).attr('disabled', false).val(0);
            $('#myTotalAfterDiscount').hide();

            var pt_class_id_selected = $('#pt_class_id').find(":selected").val();
            class_limit_member(pt_class_id_selected);

            let prev_amount_paid_input = $('#prev_amount_paid_input').val();
            $('#diff_amount_paid').html(Number(selectedMembershipPriceWithVat - prev_amount_paid_input).toFixed(2));
        });


        $('#discount_value').change(function () {
            let price = (parseFloat($('#pt_class_id option:selected').attr('data-price')));
            let vat = 0;
            let priceWithVat = 0;

            let discount_value = $('#discount_value').val();

            @if(@$mainSettings->vat_details['vat_percentage'])
                vat = (parseFloat(price)-parseFloat(discount_value)) * ({{@$mainSettings->vat_details['vat_percentage'] / 100}});
            @endif
                priceWithVat = parseFloat(price - discount_value + vat);
                discount_value = parseFloat(discount_value);
            if((discount_value > 0) && (price > 0)){
                $('#myTotal').css('text-decoration', 'line-through');
                $('#myTotalAfterDiscount').show().text("{{ trans('sw.after_discount')}} = " + Number(price - discount_value).toFixed(2));
            }else{
                $('#myTotalAfterDiscount').hide();
                $('#myTotal').css('text-decoration', 'unset');
            }
            $('#myTotalWithVat').text("{{ trans('sw.price')}} = " + parseFloat(priceWithVat).toFixed(2));
            $('#create_amount_paid').val(parseFloat(priceWithVat).toFixed(2)).attr('max', parseFloat(priceWithVat).toFixed(2));
            $('#create_amount_remaining').val(0);

            let prev_amount_paid_input = $('#prev_amount_paid_input').val();
            $('#diff_amount_paid').html(Number(priceWithVat - prev_amount_paid_input).toFixed(2));

        });

        function class_limit_member(pt_class_id){
            if(pt_class_id){
                $.get("{{route('sw.ptClassActiveMemberAjax')}}", {pt_class_id: pt_class_id},
                    function(result){
                        var class_limit_msg = '<div class="alert alert-info">{{ trans('sw.active_members_now', ['member_count' => '@@member_count'])}}</div>';
                        class_limit_msg = class_limit_msg.replace('@@member_count', result)
                        $('#class_limit_msg').html(class_limit_msg);
                    }
                );
            }
        }
        class_limit_member('{{$member->pt_class_id}}');

    </script>
@endsection

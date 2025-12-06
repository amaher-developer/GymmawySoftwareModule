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
            <a href="{{ route('sw.listMember') }}" class="text-muted text-hover-primary">{{ trans('sw.subscribed_clients')}}</a>
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
    <!---Internal Fileupload css-->
    <link href="{{asset('/')}}resources/assets/new_front/global/scripts/css/fileupload.css" rel="stylesheet"
          type="text/css"/>

        <style>
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

        #myTotalAfterDiscount {
            display: none;
        }
    </style>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
@endsection
@section('page_body')
    @php
        $billingSettings = $billingSettings ?? [];
        $invoice = $invoice ?? null;
    @endphp
    <!--begin::Member Create Form-->
    <form method="post" action="" class="form" enctype="multipart/form-data">
        {{csrf_field()}}
        
        <div class="row g-7">
            <!-- begin::Left Column - Basic Information -->
            <div class="col-lg-8">
                <!--begin::Basic Information Card-->
                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h3 class="fw-bold"><i class="ki-outline ki-profile-user fs-2 me-2"></i>{{ trans('sw.basic_information')}}</h3>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row g-5">
                            <!-- Identification Code -->
                            <div class="col-md-12">
                                <label class="form-label required">{{ trans('sw.identification_code')}}</label>
                                <div class="input-group">
                                    <input name="code" onkeydown="return event.key!=='Enter';" value="{{ (int)$member->code ? old('code', $member->code): $maxId }}"
                                           type="text" class="form-control" min="0"
                                           id="subscribedClientInputCode"
                                           placeholder="{{ trans('sw.enter_identification_code')}}" disabled required>
                                    <button type="button" onclick="editBarCodeInput();" id="editBarcodeBtn" class="btn btn-primary">
                                        <i class="ki-outline ki-pencil"></i> {{ trans('sw.edit')}}
                                    </button>
                                </div>
                            </div>
                            
                            @if(@env('APP_ZK_GATE') == true)
                            <!-- Fingerprint -->
                            <div class="col-md-12">
                                <label class="form-label">{{ trans('sw.fingerprint')}}</label>
                                <div class="input-group">
                                    <input class="form-control" placeholder="{{ trans('sw.fingerprint_id_data')}}"
                                           name="fp_id" min="0"
                                           value="{{ old('fp_id', $member->fp_id) }}"
                                           type="text">
                                    <span class="input-group-text">
                                        <i class="material-icons">fingerprint</i>
                                    </span>
                                </div>
                            </div>
                            @endif
                            
                            <!-- Name -->
                            <div class="col-md-12">
                                <label class="form-label required">{{ trans('sw.name')}}</label>
                                <input name="name" value="{{ old('name', $member->name) }}" type="text" class="form-control"
                                       id="unsubscribedClientInputName" placeholder="{{ trans('sw.enter_name')}}" required>
                            </div>
                            
                            <!-- Gender -->
                            <div class="col-md-12">
                                <label class="form-label required">{{ trans('sw.gender')}}</label>
                                <div class="d-flex gap-5 mt-3">
                                    <div class="form-check form-check-custom form-check-solid">
                                        <input class="form-check-input" type="radio" name="gender" value="{{\Modules\Software\Classes\TypeConstants::MALE}}" id="genderMale" required>
                                        <label class="form-check-label" for="genderMale">
                                            {{ trans('sw.male')}}
                                        </label>
                                    </div>
                                    <div class="form-check form-check-custom form-check-solid">
                                        <input class="form-check-input" type="radio" name="gender" value="{{\Modules\Software\Classes\TypeConstants::FEMALE}}" id="genderFemale" required>
                                        <label class="form-check-label" for="genderFemale">
                                            {{ trans('sw.female')}}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Phone -->
                            <div class="col-md-6">
                                <label class="form-label required">{{ trans('sw.phone')}}</label>
                                <input name="phone" value="{{ old('phone', $member->phone) }}" type="text" class="form-control"
                                       id="subscribedClientInputPhone"
                                       placeholder="{{ trans('sw.enter_phone')}}" required>
                            </div>
                            
                            <!-- Email -->
                            <div class="col-md-6">
                                <label class="form-label">{{ trans('sw.email')}}</label>
                                <input name="email" value="{{ old('email', $member->email) }}" type="email" class="form-control"
                                       placeholder="{{ trans('sw.enter_email')}}">
                            </div>
                            
                            <!-- National ID -->
                            <div class="col-md-6">
                                <label class="form-label">{{ trans('sw.national_id')}}</label>
                                <input name="national_id" value="{{ old('national_id', @$member->national_id) }}" type="text" class="form-control"
                                       id="national_id"
                                       placeholder="{{ trans('sw.enter_national_id')}}">
                            </div>
                            
                            <!-- Date of Birth -->
                            <div class="col-md-6">
                                <label class="form-label">{{ trans('sw.date_of_birth')}}</label>
                                <div class="input-group date" data-date-format="yyyy-mm-dd">
                                    <input class="form-control" autocomplete="off" placeholder="{{ trans('sw.date_of_birth')}}"
                                           name="dob"
                                           value="{{ old('dob', $member->dob) }}"
                                           type="text">
                                    <span class="input-group-text">
                                        <i class="ki-outline ki-calendar"></i>
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Address -->
                            <div class="col-md-12">
                                <label class="form-label required">{{ trans('sw.address')}}</label>
                                <input name="address" value="{{ old('address', $member->address) }}" type="text" class="form-control"
                                       id="subscribedClientInputAddress"
                                       placeholder="{{ trans('sw.enter_address')}}" required>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Basic Information Card-->
                
                <!--begin::Additional Information Card-->
                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h3 class="fw-bold"><i class="ki-outline ki-information fs-2 me-2"></i>{{ trans('sw.additional_information')}}</h3>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row g-5">
                            <!-- Sale User -->
                            <div class="col-md-6">
                                <label class="form-label">{{ trans('sw.sale_user')}}</label>
                                <select id="sale_user_id" name="sale_user_id" class="form-select select2" data-control="select2" data-placeholder="{{ trans('sw.choose')}}">
                                    <option value="">{{ trans('sw.choose')}}</option>
                                    @foreach($users as $user)
                                        <option value="{{$user->id}}">{{$user->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            @if(count($channels) > 0)
                            <!-- Sale Channel -->
                            <div class="col-md-6">
                                <label class="form-label">{{ trans('sw.sale_channels')}}</label>
                                <select id="sale_channel_id" name="sale_channel_id" class="form-select select2" data-control="select2" data-placeholder="{{ trans('sw.choose')}}">
                                    <option value="">{{ trans('sw.choose')}}</option>
                                    @foreach($channels as $channel)
                                        <option value="{{$channel->id}}">{{$channel->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            
                            <!-- Invitations -->
                            <div class="col-md-6">
                                <label class="form-label">{{ trans('sw.invitations_num')}}</label>
                                <input class="form-control" placeholder="{{ trans('sw.invitations_num')}}"
                                       name="invitations"
                                       value="{{ old('invitations', $member->invitations) }}"
                                       type="number">
                            </div>
                        <!-- Additional Information -->
                        <div class="col-md-12">
                            <label class="form-label">{{ trans('sw.additional_information')}}</label>
                            <textarea class="form-control" placeholder="{{ trans('sw.additional_information')}}" name="additional_info" rows="3">{{ old('additional_info', $member->additional_info) }}</textarea>
                        </div>
                        </div>
                    </div>
                </div>
                <!--end::Additional Information Card-->
                
                <!--begin::Membership & Subscription Card-->
                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h3 class="fw-bold"><i class="ki-outline ki-calendar-tick fs-2 me-2"></i>{{ trans('sw.membership')}}</h3>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row g-5">
                            <!-- Membership Selection -->
                            <div class="col-md-12">
                                <label class="form-label required">{{ trans('sw.membership')}}</label>
                                <select id="membership" name="subscription_id" class="form-select select2" data-control="select2">
                                    @foreach($subscriptions as $subscription)
                                        @php
                                            $periodDays = is_numeric($subscription->period) ? (int)$subscription->period : 0;
                                            $defaultExpire = \Carbon\Carbon::now()->addDays($periodDays)->toDateString();
                                        @endphp
                                        <option value="{{$subscription->id}}"
                                                price="{{$subscription->price}}"
                                                expire_date="{{$defaultExpire}}"
                                                period="{{@$subscription->period}}"
                                                discount_value="{{@$subscription->default_discount_value}}"
                                                discount_type="{{@$subscription->default_discount_type}}"
                                                required>
                                            {{$subscription->name}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Membership Dates -->
                            <div class="col-md-12">
                                <label class="form-label required">{{ trans('sw.membership_date')}}</label>
                                <div class="input-group date date-picker input-daterange" data-date-format="yyyy-mm-dd">
                                    <input type="text" class="form-control" id="editCustomStartDate" name="joining_date" data-date-format="yyyy-mm-dd" value="" autocomplete="off" placeholder="{{ trans('sw.joining_date')}}" required>
                                    <span class="input-group-text">~</span>
                                    <input type="text" class="form-control" id="editCustomExpireDate" name="expire_date" data-date-format="yyyy-mm-dd" value="" autocomplete="off" placeholder="{{ trans('sw.expire_date')}}" required>
                                </div>
                            </div>
                            
                            <!-- Discount Message -->
                            <div class="col-md-12">
                                <div id="discount_subscription_message"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Membership & Subscription Card-->
                
                <!--begin::Payment Information Card-->
                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h3 class="fw-bold"><i class="ki-outline ki-dollar fs-2 me-2"></i>{{ trans('sw.payment_information')}}</h3>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row g-5">
                            <!-- Price Display -->
                            <div class="col-md-12">
                                <div class="d-flex flex-wrap gap-3 align-items-center">
                                    <span class="badge badge-lg badge-light-success fw-bold" id="myTotal">{{ trans('sw.price')}} = {{(float)@$member->member_subscription_info->subscription->price}}</span>
                                    <span class="badge badge-lg badge-light-primary fw-bold" id="myTotalAfterDiscount" @if($member->discount_value) style="display: inline;" @endif>{{ trans('sw.after_discount')}} = {{(float)@$member->member_subscription_info->subscription->price - @$member->discount_value}}</span>
                                    @if(@$mainSettings->vat_details['vat_percentage'])
                                    <span class="badge badge-lg badge-light-warning fw-bold" id="myTotalWithVat">{{ trans('sw.including_vat')}} = {{(float)@$member->member_subscription_info->subscription->price}}</span>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Discount Controls -->
                            @if((in_array('editMemberDiscount', (array)$swUser->permissions)) || $swUser->is_super_user)
                            <div class="col-md-6">
                                <label class="form-label">{{ trans('sw.discount_value')}}</label>
                                <input class="form-control" autocomplete="off" placeholder="{{ trans('sw.discount_value')}}"
                                       name="discount_value"
                                       id="discount_value"
                                       value="0"
                                       min="0"
                                       max="{{@($member->member_subscription_info->subscription->price)}}"
                                       type="number" step="0.01">
                            </div>
                            @endif
                            
                            @if((count($discounts) > 0) && ((in_array('editMemberDiscountGroup', (array)$swUser->permissions)) || $swUser->is_super_user))
                            <div class="col-md-6">
                                <label class="form-label">{{ trans('sw.discount')}}</label>
                                <select id="group_discount_id" name="group_discount_id" class="form-select select2" data-control="select2">
                                    <option value="0" type="0" amount="0">{{ trans('sw.choose')}}</option>
                                    @foreach($discounts as $discount)
                                        <option value="{{$discount->id}}" type="{{$discount->type}}" amount="{{$discount->amount}}">{{$discount->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            
                            @if(@$mainSettings->vat_details['vat_percentage'])
                            <div class="col-md-12">
                                <div class="notice d-flex bg-light-info rounded border-info border border-dashed p-6">
                                    <i class="ki-outline ki-information fs-2tx text-info me-4"></i>
                                    <div class="d-flex flex-stack flex-grow-1">
                                        <div class="fw-semibold">
                                            <div class="fs-6 text-gray-700">{{ trans('sw.include_vat', ['vat' => @$mainSettings->vat_details['vat_percentage']])}}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            
                            <!-- Amount Paid & Remaining -->
                            <div class="col-md-6">
                                <label class="form-label required">{{ trans('sw.amount_paid')}}</label>
                                <input id="create_amount_paid" class="form-control" name="amount_paid" value="{{ old('amount_paid', @($member->amount_paid)) }}"
                                       max="{{@($member->member_subscription_info->subscription->price - $member->discount_value)}}"
                                       placeholder="{{ trans('sw.enter_amount_paid')}}" type="number" step="0.01" min="0" required/>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label required">{{ trans('sw.amount_remaining')}}</label>
                                <input id="create_amount_remaining" class="form-control" name="amount_remaining" value="{{@old('amount_remaining', @$member->amount_remaining)}}"
                                       placeholder="{{ trans('sw.enter_amount_remaining')}}" disabled step="0.01" type="number" min="0"/>
                            </div>
                            
                            <!-- Payment Type -->
                            <div class="col-md-6">
                                <label class="form-label required">{{ trans('sw.payment_type')}}</label>
                                <select class="form-select" name="payment_type" data-control="select2">
                                    @foreach($payment_types as $payment_type)
                                        <option value="{{$payment_type->payment_id}}" @if(@old('$payment_type',$member->member_subscription_info->payment_type) == $payment_type->payment_id) selected="" @endif>{{$payment_type->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Notes -->
                            <div class="col-md-12">
                                <label class="form-label">{{ trans('sw.notes')}}</label>
                                <textarea rows="3" maxlength="255" name="notes" class="form-control" placeholder="{{ trans('sw.notes')}}">{{@old('notes', @$member->member_subscription_info->notes)}}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Payment Information Card-->

              @if(config('sw_billing.zatca_enabled') && data_get($billingSettings, 'sections.members', true) && $member->id && $invoice)
                <div class="card bg-light-primary border border-dashed border-primary mb-7">
                    <div class="card-body py-5">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-5">
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
                        @if(optional($member->member_subscription_info)->id)
                            <a href="{{ route('sw.showOrderSubscription', $member->member_subscription_info->id) }}" class="btn btn-sm btn-light-primary mt-4">
                                <i class="ki-outline ki-eye fs-3"></i> {{ trans('global.view') ?? __('View') }} {{ trans('sw.invoice') }}
                            </a>
                        @endif
                    </div>
                </div>
                @endif
                
                @if(@$mainSettings->active_loyalty)
                <!--begin::Loyalty Points Earning Info-->
                <div class="alert alert-dismissible bg-light-success border border-success border-dashed d-flex flex-column flex-sm-row p-5 mb-5" id="member_loyalty_earning_info" style="display: none !important;">
                    <i class="ki-outline ki-gift fs-2hx text-success me-4 mb-5 mb-sm-0"></i>
                    <div class="d-flex flex-column pe-0 pe-sm-10">
                        <h5 class="mb-1">{{ trans('sw.points_earning_info')}}</h5>
                        <span class="text-gray-700">{!! trans('sw.you_will_earn_points', ['points' => '<span id="member_estimated_earning_points" class="fw-bold text-success">0</span>'])!!}</span>
                        <span class="text-gray-600 fs-7" id="member_loyalty_earning_rate"></span>
                    </div>
                </div>
                <!--end::Loyalty Points Earning Info-->
                @endif
                
                <!--begin::Form Actions-->
                <div class="d-flex justify-content-end gap-3">
                    <button type="reset" class="btn btn-light">{{ trans('admin.reset')}}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ki-outline ki-check fs-2"></i>
                        {{ trans('global.save')}}
                    </button>
                </div>
                <!--end::Form Actions-->
            </div>
            <!-- end::Left Column -->
            
            <!-- begin::Right Column - Member Photo -->
            <div class="col-lg-4">
                <div class="card card-flush">
                    <div class="card-header">
                        <div class="card-title">
                            <h3 class="fw-bold"><i class="ki-outline ki-picture fs-2 me-2"></i>{{ trans('sw.the_image')}}</h3>
                        </div>
                    </div>
                    <div class="card-body text-center pt-0">
                        <div class="mb-5">
                            <a class="btn btn-sm btn-light-primary modal-effect" data-effect="effect-newspaper" onclick="startWebCam()"
                               data-toggle="modal" href="#modalCamera">
                                <i class="ki-outline ki-camera fs-3"></i>
                                {{ trans('sw.camera_msg')}}
                            </a>
                        </div>
                        <input id="SubscribedClientsInputPhoto"
                               data-default-file="{{asset('uploads/settings')}}/default.jpg"
                               name="image" type="file" class="dropify" data-height="300"
                               accept=".jpg, .png, image/jpeg, image/png"/>
                        <input type="hidden" name="image" id="photo_camera">
                    </div>
                </div>
            </div>
            <!-- end::Right Column -->
        </div>
    </form>




    <!-- Modal Camera with effects -->
    <div class="modal" id="modalCamera">
        <div class="modal-dialog modal-dialog-scrollable " role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">{{ trans('sw.camera_snapshot')}}</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                                aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body text-center">
                    <div class="img-fluid" id="my_camera"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-primary" onclick="take_snapshot()" data-dismiss="modal" type="button">
                        {{ trans('sw.camera_snapshot')}}
                    </button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal"
                            type="button">  {{ trans('sw.exist')}}</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Camera with effects-->
@endsection


@section('sub_scripts')
    <script src="{{asset('resources/assets/new_front/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"
            type="text/javascript"></script>
{{--            type="text/javascript"></script>--}}
{{--    <script src="{{asset('/')}}resources/assets/new_front/global/scripts/metronic.js" type="text/javascript"></script>--}}
{{--    <script src="{{asset('/')}}resources/assets/new_front/pages/scripts/components-pickers.js"></script>--}}
    <script>
        // Declare variables at the top
        let selectedMembershipPrice = 0;
        let selectedMembershipExpireDate = '';
        let loyaltyMoneyToPointRate = 0;

        $("#membership").select2();

        jQuery(document).ready(function() {
            // Initialize date pickers
            if (typeof ComponentsPickers !== 'undefined') {
                ComponentsPickers.init();
            }
            
            // Initialize birthdate picker specifically
            $('input[name="dob"]').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                orientation: "bottom auto",
                endDate: new Date() // Can't select future dates for birthdate
            });
            
            @if(@$mainSettings->active_loyalty)
            // Load loyalty earning rate
            $.ajax({
                url: '{{ route('sw.getMemberLoyaltyInfo') }}',
                type: 'GET',
                data: { member_id: 0 },
                success: function(response) {
                    if (response.success && response.money_to_point_rate) {
                        loyaltyMoneyToPointRate = response.money_to_point_rate;
                        $('#member_loyalty_earning_rate').text('{{ trans('sw.earning_rate', ['rate' => '']) }}'.replace(':rate عملة', loyaltyMoneyToPointRate.toFixed(2) + ' {{ trans('sw.app_currency') }}').replace(':rate currency', loyaltyMoneyToPointRate.toFixed(2) + ' {{ trans('sw.app_currency') }}'));
                        // Calculate initial points
                        calculateMemberLoyaltyPoints();
                    }
                }
            });
            @endif
        });
        
        function calculateMemberLoyaltyPoints() {
            if (loyaltyMoneyToPointRate > 0) {
                const amountPaid = parseFloat($('#create_amount_paid').val()) || 0;
                console.log('Calculating loyalty points:', amountPaid, 'Rate:', loyaltyMoneyToPointRate);
                if (amountPaid > 0) {
                    const estimatedPoints = Math.floor(amountPaid / loyaltyMoneyToPointRate);
                    console.log('Estimated points:', estimatedPoints);
                    if (estimatedPoints > 0) {
                        $('#member_estimated_earning_points').text(estimatedPoints);
                        $('#member_loyalty_earning_info').slideDown();
                    } else {
                        $('#member_loyalty_earning_info').slideUp();
                    }
                } else {
                    $('#member_loyalty_earning_info').slideUp();
                }
            } else {
                console.log('Loyalty rate not loaded yet or zero');
            }
        }
        
        getPriceMemberShip();

            // getRandomBarcode();

        function getRandomBarcode() {
            min = Math.ceil({{$maxId+5}});
            max = Math.floor({{$maxId+15}});
            number = (Math.floor(Math.random() * (max - min + 1)) + min);
            document.getElementById("subscribedClientInputCode").value = number;

        }

        $("#create_amount_paid").on('change input keyup', function () {
            console.log('Amount paid changed, current value:', $(this).val());
            
            selectedMembershipPrice = 0;
            $.each($("#membership option:selected"), function () {
                selectedMembershipPrice = selectedMembershipPrice + (parseFloat($(this).attr('price')));
            });

            let vat = 0;
            let selectedMembershipPriceWithVat = 0;
            let valueAmountPaid = parseFloat($('#create_amount_paid').val()) || 0;

            let valueDiscount = 0;
            valueDiscount = $('#discount_value').val();

            @if(@$mainSettings->vat_details['vat_percentage'])
                vat = (parseFloat(selectedMembershipPrice)- parseFloat(valueDiscount)) * ({{@$mainSettings->vat_details['vat_percentage'] / 100}});
            @endif
            selectedMembershipPriceWithVat = parseFloat(selectedMembershipPrice) - parseFloat(valueDiscount) + vat;

            $('#create_amount_remaining').val(Number(selectedMembershipPriceWithVat - valueAmountPaid ).toFixed(2));
            $('#create_amount_paid').attr('max', Number(selectedMembershipPriceWithVat).toFixed(2));
            
            // Calculate loyalty points
            console.log('Calling calculateMemberLoyaltyPoints from amount_paid change');
            calculateMemberLoyaltyPoints();
        });

        $('#membership').change(function () {
            selectedMembershipPrice = 0;
            $.each($("#membership option:selected"), function () {
                selectedMembershipPrice = selectedMembershipPrice + (parseFloat($(this).attr('price')));
                selectedMembershipExpireDate = $(this).attr('expire_date');
            });
            let selectedMembershipPriceWithVat = 0;
            let vat = 0;
            @if(@$mainSettings->vat_details['vat_percentage'])
                vat = selectedMembershipPrice * ({{@$mainSettings->vat_details['vat_percentage'] / 100}});
            @endif
            selectedMembershipPriceWithVat = parseFloat(selectedMembershipPrice + vat).toFixed(2);
            $('#myTotal').text("{{ trans('sw.price')}} = " + parseFloat(selectedMembershipPrice).toFixed(2)).css('text-decoration', 'unset');
            $('#myTotalWithVat').val(selectedMembershipPriceWithVat).attr('max', selectedMembershipPriceWithVat).text("{{ trans('sw.including_vat')}} = " + selectedMembershipPriceWithVat).css('text-decoration', 'unset');
            $('#create_amount_paid').val(selectedMembershipPriceWithVat).attr('max', selectedMembershipPriceWithVat);
            $('#create_amount_remaining').val(0);
            $('#discount_value').attr('max', selectedMembershipPriceWithVat).attr('disabled', false).val(0);
            $('#myTotalAfterDiscount').hide();

            $('#editCustomExpireDate').val(selectedMembershipExpireDate);
            $('#editCustomStartDate').val('{{\Carbon\Carbon::now()->toDateString()}}');

            apply_discount_subscription();
            
            // Calculate loyalty points
            calculateMemberLoyaltyPoints();
        });

        $('#editCustomStartDate').change(function () {
            let joining_date = $("#editCustomStartDate").val();
            let period = $('#membership option:selected').attr('period');
            setCustomExpireDate(joining_date, period);
        });
        function setCustomExpireDate(joining_date, period){
            let valid_days = parseInt(period);
            let end_date = new Date(joining_date); // pass start date here
            end_date.setDate(end_date.getDate() + valid_days);
            $('#editCustomExpireDate').val(  end_date.getFullYear() + '-' + ((end_date.getMonth() + 1) < 10 ? '0' + (end_date.getMonth() + 1) : (end_date.getMonth() + 1)) + '-' + end_date.getDate() );
        }

        function getPriceMemberShip() {
            selectedMembershipPrice = 0;
            $.each($("#membership option:selected"), function () {
                selectedMembershipPrice = selectedMembershipPrice + (parseFloat($(this).attr('price')));
                selectedMembershipExpireDate = $(this).attr('expire_date');
            });
            let vat = 0;
            let selectedMembershipPriceWithVat = 0;
            @if(@$mainSettings->vat_details['vat_percentage'])
                vat = selectedMembershipPrice * ({{@$mainSettings->vat_details['vat_percentage'] / 100}});
            @endif
            selectedMembershipPriceWithVat = parseFloat(selectedMembershipPrice + vat).toFixed(2);

            $('#create_amount_paid').val(selectedMembershipPriceWithVat).attr('max', selectedMembershipPriceWithVat);
            $('#create_amount_remaining').val(0);
            $('#myTotal').text("{{ trans('sw.price')}} = " + parseFloat(selectedMembershipPrice).toFixed(2));
            $('#myTotalWithVat').text("{{ trans('sw.including_vat')}} = " + selectedMembershipPriceWithVat);
            $('#discount_value').attr('max', selectedMembershipPriceWithVat).attr('disabled', false).val(0);

            $('#editCustomExpireDate').val(selectedMembershipExpireDate);
            $('#editCustomStartDate').val('{{\Carbon\Carbon::now()->toDateString()}}');
            
            // Calculate loyalty points
            calculateMemberLoyaltyPoints();

        }

        function editBarCodeInput(){
            $('#subscribedClientInputCode').prop('disabled', false); // If checked enable item
            $('#editBarcodeBtn').hide();
        }

        $('#discount_value').change(function () {
            discount_value();
        });
        function discount_value(discount_amount = null) {
            // $('#discount_value').change(function () {
            let price = (parseFloat($('#membership option:selected').attr('price')));
            let vat = 0;
            let priceWithVat = 0;
            let discount_value = 0;
            if(discount_amount === null)
                discount_value = $('#discount_value').val();
            else
                discount_value = discount_amount

            @if(@$mainSettings->vat_details['vat_percentage'])
                vat = (parseFloat(price) - parseFloat(discount_value)) * ({{@$mainSettings->vat_details['vat_percentage'] / 100}});
            @endif
                priceWithVat = parseFloat(price - discount_value + vat);
            // let create_amount_remaining = $('#create_amount_remaining').val();
            discount_value = parseFloat(discount_value);
            if ((discount_value > 0) && (price > 0)) {
                $('#myTotal').css('text-decoration', 'line-through');
                $('#myTotalAfterDiscount').show().text("{{ trans('sw.after_discount')}} = " + parseFloat(price - discount_value).toFixed(2));
            } else {
                $('#myTotalAfterDiscount').hide();
                $('#myTotal').css('text-decoration', 'unset');
            }

            $('#myTotalWithVat').text("{{ trans('sw.including_vat')}} = " + parseFloat(priceWithVat).toFixed(2));
            $('#create_amount_paid').val(parseFloat(priceWithVat).toFixed(2)).attr('max', parseFloat(priceWithVat).toFixed(2));
            $('#create_amount_remaining').val(0);
            
            // Calculate loyalty points
            calculateMemberLoyaltyPoints();

            // });
        }
        $('#group_discount_id').on('change', function (event){
            let discount_id = $(this).find(":selected").val();
            let type = parseInt($(this).find(":selected").attr('type'));
            let amount = $(this).find(":selected").attr('amount');
            let price = (parseFloat($('#membership option:selected').attr('price')));
            let result = 0;
            if((type === 0) || (discount_id === 0)){
                $('#discount_value').val(amount);
                discount_value(amount);
            }else{
                result = parseFloat(Number(price) * (Number(amount)/100)).toFixed(2);
                $('#discount_value').val(result);
                discount_value(result);
            }
        });

        apply_discount_subscription();
        function apply_discount_subscription(){
            let type = (parseInt($('#membership option:selected').attr('discount_type')));
            let amount = (parseFloat($('#membership option:selected').attr('discount_value')));
            let price = (parseFloat($('#membership option:selected').attr('price')));
            let result = 0;
            $('#discount_subscription_message').html('');
            if(amount) {
                let discount_message = '{{ trans('sw.discount_subscription_message', ['amount'=> ':amount', 'type' => ':type'])}}';

                if (type === 0) {
                    $('#discount_value').val(amount);
                    discount_value(amount);
                    discount_message = discount_message.replace(':type', '');
                } else {
                    result = parseFloat(Number(price) * (Number(amount) / 100)).toFixed(2);
                    $('#discount_value').val(result);
                    discount_value(result);
                    discount_message = discount_message.replace(':type', '%');
                }
                discount_message = discount_message.replace(':amount', amount);
                $('#discount_subscription_message').html('<div class="alert alert-danger">'+discount_message+'</div>');
                $('#group_discount_id').val(0);
            }
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.25/webcam.min.js"></script>
    <script>
        function startWebCam() {
            Webcam.set({
                width: 450,
                height: 360,
                image_format: 'png',
                jpeg_quality: 100
            });

            Webcam.attach('#my_camera');


        }

        function take_snapshot() {
            var path_mp3 = 'https://demo.egym.site/assets/mp3';
            var shutter = new Audio();
            shutter.autoplay = true;
            shutter.src = navigator.userAgent.match(/Firefox/) ? 'shutter.ogg' : path_mp3 + '/' + 'shutter.mp3';

            Webcam.snap(function (data_uri) {
                $('.dropify-preview').attr('style', 'display:block');
                $('.dropify-render').html('<img src="' + data_uri + '" style="max-height: 200px;">');
                $('#photo_camera').val(data_uri);
                shutter.play();

            });
        }
    </script>

    <!--Internal Fileuploads js-->
    <script src="{{asset('/')}}resources/assets/new_front/global/scripts/js/fileupload.js"></script>
    <script src="{{asset('/')}}resources/assets/new_front/global/scripts/js/file-upload.js"></script>


    <script>
        $('.dropify-infos-message').html("{{ trans('sw.upload_image')}}");
        $('.dropify-message p:first').html("{{ trans('sw.upload_image')}}");
        $('.dropify-clear').html("{{ trans('sw.remove')}}");
    </script>

    <script>
        // showing modal with effect
        $('.modal-effect').on('click', function (e) {
            e.preventDefault();
            var effect = $(this).attr('data-effect');
            $('#modalCamera').addClass(effect);

        });
    </script>

@endsection



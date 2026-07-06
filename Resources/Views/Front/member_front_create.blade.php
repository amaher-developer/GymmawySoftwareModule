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
        /* ── Option-group compact UI ── */
        .pos-pill{display:inline-flex;align-items:center;padding:3px 10px;border:1.5px solid #e4e6ef;border-radius:20px;font-size:12px;background:#f5f8fa;cursor:pointer;transition:all .15s;user-select:none;white-space:nowrap;}
        .pos-pill:hover{border-color:#009ef7;color:#009ef7;}
        .pos-pill.active{background:#009ef7;color:#fff;border-color:#009ef7;}
        .pos-prod-thumb{width:36px;height:36px;object-fit:cover;border-radius:4px;flex-shrink:0;}
        .pos-product-grid::-webkit-scrollbar,.pos-option-list::-webkit-scrollbar{width:4px;}
        .pos-product-grid::-webkit-scrollbar-thumb,.pos-option-list::-webkit-scrollbar-thumb{background:#d1d3e0;border-radius:4px;}
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
    <form method="post" action="{{ route('sw.createMember') }}" class="form" enctype="multipart/form-data">
        {{csrf_field()}}

        @if ($errors->any())
            <div class="alert alert-danger mb-7">
                <ul class="mb-0 ps-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
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
                                            $defaultExpire = \Carbon\Carbon::now()->addDays($periodDays > 0 ? $periodDays - 1 : 0)->toDateString();
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

                <!--begin::Member Activities Card-->
                <div class="card card-flush mb-7" id="member_activities_card" style="display:none">
                    <div class="card-header">
                        <div class="card-title">
                            <h3 class="fw-bold"><i class="ki-outline ki-basketball fs-2 me-2"></i>{{ trans('sw.select_activities_for_member') }}</h3>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="text-muted fs-7 mb-3" id="member_activities_hint"></div>
                        <div id="member_activities_body"></div>
                    </div>
                </div>
                <!--end::Member Activities Card-->

                <!--begin::Subscription Options Card-->
                <div class="card card-flush mb-7" id="pos_option_groups_card" style="display:none">
                    <div class="card-header">
                        <div class="card-title">
                            <h3 class="fw-bold">
                                <i class="ki-outline ki-category fs-2 me-2"></i>
                                {{ trans('sw.option_groups') }}
                            </h3>
                        </div>
                    </div>
                    <div class="card-body pt-3" id="pos_option_groups_body">
                        <div class="text-center py-4">
                            <span class="spinner-border spinner-border-sm text-primary"></span>
                        </div>
                    </div>
                </div>
                {{-- Hidden inputs for selected option IDs — populated by JS --}}
                <div id="pos_option_ids_container"></div>
                <!--end::Subscription Options Card-->

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
                                {{-- Options price breakdown (shown when options with surcharges are selected) --}}
                                <div id="pos_options_breakdown" class="mt-3" style="display:none"></div>
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
                            @else
                            <input type="hidden" name="discount_value" id="discount_value" value="0">
                            @endif

                            @if((count($discounts ?? []) > 0) && ((in_array('editMemberDiscountGroup', (array)$swUser->permissions)) || $swUser->is_super_user))
                            <div class="col-md-6">
                                <label class="form-label">{{ trans('sw.discount')}}</label>
                                <select id="group_discount_id" name="group_discount_id" class="form-select select2" data-control="select2">
                                    <option value="0" type="0" amount="0">{{ trans('sw.choose')}}</option>
                                    @foreach($discounts as $discount)
                                        <option value="{{$discount->id}}" type="{{$discount->type}}" amount="{{$discount->amount}}">{{$discount->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            @else
                            <input type="hidden" name="group_discount_id" value="0">
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

                            @php
                                $hasAnyGateway = !empty($mainSettings->payments['tabby']['merchant_code'] ?? null)
                                    || !empty($mainSettings->payments['tamara']['token'] ?? null)
                                    || !empty($mainSettings->payments['paymob']['api_key'] ?? null)
                                    || (!empty($mainSettings->payments['paytabs']['profile_id'] ?? null) && !empty($mainSettings->payments['paytabs']['server_key'] ?? null));
                            @endphp
                            @if($hasAnyGateway)
                            <!-- Payment Gateway Cards -->
                            <div class="col-12" id="payment_gateway_section">
                                <div class="pgw-section">
                                    <div class="pgw-section-title">
                                        <i class="ki-outline ki-send"></i>
                                        {{ trans('sw.send_payment_link') }}
                                    </div>
                                    <div class="pgw-grid">

                                        @if(!empty($mainSettings->payments['tabby']['merchant_code'] ?? null))
                                        <div class="pgw-card" id="tabby_payment_option">
                                            <input type="checkbox" name="send_tabby_link" id="send_tabby_link" value="1" class="pgw-checkbox" style="display:none"/>
                                            <div class="pgw-check"></div>
                                            <div class="pgw-logo-wrap">
                                                <img src="{{ asset('resources/assets/new_front/images/tabby-logo.webp') }}" alt="Tabby" class="pgw-logo">
                                            </div>
                                            <div class="pgw-methods">
                                                <span class="badge badge-light-success fs-8 fw-semibold px-2 py-1">{{ trans('sw.buy_now_pay_later') }}</span>
                                            </div>
                                            <div class="pgw-desc">{{ trans('sw.tabby_payment_description') }}</div>
                                        </div>
                                        @endif

                                        @if(!empty($mainSettings->payments['tamara']['token'] ?? null))
                                        <div class="pgw-card" id="tamara_payment_option">
                                            <input type="checkbox" name="send_tamara_link" id="send_tamara_link" value="1" class="pgw-checkbox" style="display:none"/>
                                            <div class="pgw-check"></div>
                                            <div class="pgw-logo-wrap">
                                                <img src="{{ asset('resources/assets/new_front/images/tamara-logo.svg') }}" alt="Tamara" class="pgw-logo">
                                            </div>
                                            <div class="pgw-methods">
                                                <span class="badge badge-light-warning fs-8 fw-semibold px-2 py-1">{{ trans('sw.buy_now_pay_later') }}</span>
                                            </div>
                                            <div class="pgw-desc">{{ trans('sw.tamara_payment_description') }}</div>
                                        </div>
                                        @endif

                                        @if(!empty($mainSettings->payments['paymob']['api_key'] ?? null))
                                        <div class="pgw-card" id="paymob_payment_option">
                                            <input type="checkbox" name="send_paymob_link" id="send_paymob_link" value="1" class="pgw-checkbox" style="display:none"/>
                                            <div class="pgw-check"></div>
                                            <div class="pgw-logo-wrap">
                                                <img src="{{ asset('resources/assets/new_front/images/paymob.png') }}" alt="Paymob" class="pgw-logo">
                                            </div>
                                            <div class="pgw-methods">
                                                <img src="{{ asset('resources/assets/new_front/images/visa_logo.svg') }}" alt="Visa" class="pgw-method-icon">
                                                <img src="{{ asset('resources/assets/new_front/images/mada-logo.svg') }}" alt="Mada" class="pgw-method-icon">
                                                <img src="{{ asset('resources/assets/new_front/images/apple-pay-logo.svg') }}" alt="Apple Pay" class="pgw-method-icon">
                                                <img src="{{ asset('resources/assets/new_front/images/american_express_logo.svg') }}" alt="Amex" class="pgw-method-icon">
                                            </div>
                                            <div class="pgw-desc">{{ trans('sw.paymob_payment_description') }}</div>
                                        </div>
                                        @endif

                                        @if(!empty($mainSettings->payments['paytabs']['profile_id'] ?? null) && !empty($mainSettings->payments['paytabs']['server_key'] ?? null))
                                        <div class="pgw-card" id="paytabs_payment_option">
                                            <input type="checkbox" name="send_paytabs_link" id="send_paytabs_link" value="1" class="pgw-checkbox" style="display:none"/>
                                            <div class="pgw-check"></div>
                                            <div class="pgw-logo-wrap">
                                                <img src="{{ asset('resources/assets/new_front/images/paytabs-logo.svg') }}" alt="PayTabs" class="pgw-logo">
                                            </div>
                                            <div class="pgw-methods">
                                                <img src="{{ asset('resources/assets/new_front/images/visa_logo.svg') }}" alt="Visa" class="pgw-method-icon">
                                                <img src="{{ asset('resources/assets/new_front/images/mada-logo.svg') }}" alt="Mada" class="pgw-method-icon">
                                                <img src="{{ asset('resources/assets/new_front/images/apple-pay-logo.svg') }}" alt="Apple Pay" class="pgw-method-icon">
                                                <img src="{{ asset('resources/assets/new_front/images/american_express_logo.svg') }}" alt="Amex" class="pgw-method-icon">
                                            </div>
                                            <div class="pgw-desc">{{ trans('sw.paytabs_payment_description') }}</div>
                                        </div>
                                        @endif

                                    </div>
                                </div>
                            </div>
                            @endif

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
                <!--begin::Options Summary Card-->
                <div id="pos_side_summary" class="card card-flush mt-5" style="display:none">
                    <div class="card-header py-4">
                        <div class="card-title">
                            <h5 class="fw-bold mb-0">
                                <i class="ki-outline ki-receipt-square fs-3 me-2 text-success"></i>
                                {{ trans('sw.price_breakdown') }}
                            </h5>
                        </div>
                    </div>
                    <div class="card-body pt-0 pb-4" id="pos_side_summary_body">
                    </div>
                </div>
                <!--end::Options Summary Card-->
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
        var posOptionsTotal = 0; // server-confirmed options total (0 when no options selected)
        let loyaltyMoneyToPointRate = 0;
        var posOptionsUrl       = '{{ route("sw.subscription.options", ":id") }}';
        var memberActivitiesUrl = '{{ route("sw.subscription.memberActivities", ":id") }}';
        var posCalcPriceUrl     = '{{ route("sw.subscription.calculatePrice", ":id") }}';
        var SW_VAT_PCT          = {{ (float)(@$mainSettings->vat_details['vat_percentage'] ?? 0) }};
        var _posCalcXhr         = null; // track in-flight calculate-price XHR to abort stale ones

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

        var _amountPaidUpdating = false; // guard against re-entrancy when discount_value() sets .val()

        $("#create_amount_paid").on('change input keyup', function () {
            if (_amountPaidUpdating) return; // programmatic update — skip to avoid loop

            let basePrice = 0;
            $.each($("#membership option:selected"), function () {
                basePrice += (parseFloat($(this).attr('price')) || 0);
            });
            // Always include confirmed options total in the expected total
            let totalBeforeDiscount = basePrice + posOptionsTotal;

            let vat = 0;
            let totalWithVat = 0;
            let valueAmountPaid = parseFloat($('#create_amount_paid').val()) || 0;
            let valueDiscount   = parseFloat($('#discount_value').val()) || 0;

            @if(@$mainSettings->vat_details['vat_percentage'])
                vat = (totalBeforeDiscount - valueDiscount) * ({{@$mainSettings->vat_details['vat_percentage'] / 100}});
                vat = parseFloat(vat.toFixed(2));
            @endif
            totalWithVat = parseFloat(totalBeforeDiscount - valueDiscount + vat);

            $('#create_amount_remaining').val(Number(totalWithVat - valueAmountPaid).toFixed(2));
            $('#create_amount_paid').attr('max', Number(totalWithVat).toFixed(2));

            calculateMemberLoyaltyPoints();
        });

        $('#membership').change(function () {
            selectedMembershipPrice = 0;
            $.each($("#membership option:selected"), function () {
                selectedMembershipPrice = selectedMembershipPrice + (parseFloat($(this).attr('price')) || 0);
                selectedMembershipExpireDate = $(this).attr('expire_date');
            });
            let selectedMembershipPriceWithVat = 0;
            let vat = 0;
            @if(@$mainSettings->vat_details['vat_percentage'])
                vat = selectedMembershipPrice * ({{@$mainSettings->vat_details['vat_percentage'] / 100}});
                vat = parseFloat(vat.toFixed(2));
            @endif
            selectedMembershipPriceWithVat = parseFloat(selectedMembershipPrice + vat).toFixed(2);
            $('#myTotal').text("{{ trans('sw.price')}} = " + parseFloat(selectedMembershipPrice).toFixed(2)).css('text-decoration', 'unset');
            $('#myTotalWithVat').val(selectedMembershipPriceWithVat).attr('max', selectedMembershipPriceWithVat).text("{{ trans('sw.including_vat')}} = " + selectedMembershipPriceWithVat).css('text-decoration', 'unset');
            _amountPaidUpdating = true;
            $('#create_amount_paid').val(selectedMembershipPriceWithVat).attr('max', selectedMembershipPriceWithVat);
            $('#create_amount_remaining').val(0);
            _amountPaidUpdating = false;
            $('#discount_value').attr('max', selectedMembershipPriceWithVat).attr('disabled', false).val(0);
            $('#myTotalAfterDiscount').hide();

            $('#editCustomExpireDate').val(selectedMembershipExpireDate);
            $('#editCustomStartDate').val('{{\Carbon\Carbon::now()->toDateString()}}');

            apply_discount_subscription();

            // Calculate loyalty points
            calculateMemberLoyaltyPoints();

            // Reset options total and load option groups for the new subscription
            posOptionsTotal = 0;
            $('#pos_option_ids_container').empty();
            $('#pos_options_breakdown').hide();
            $('#pos_side_summary').hide();
            posLoadOptionGroups($(this).val());
            loadMemberActivities($(this).val());
        });

        $('#editCustomStartDate').change(function () {
            let joining_date = $("#editCustomStartDate").val();
            let period = $('#membership option:selected').attr('period');
            setCustomExpireDate(joining_date, period);
        });
        function setCustomExpireDate(joining_date, period){
            let valid_days = parseInt(period);
            let end_date = new Date(joining_date); // pass start date here
            end_date.setDate(end_date.getDate() + (valid_days > 0 ? valid_days - 1 : 0));
            $('#editCustomExpireDate').val(  end_date.getFullYear() + '-' + ((end_date.getMonth() + 1) < 10 ? '0' + (end_date.getMonth() + 1) : (end_date.getMonth() + 1)) + '-' + end_date.getDate() );
        }

        function getPriceMemberShip() {
            selectedMembershipPrice = 0;
            $.each($("#membership option:selected"), function () {
                selectedMembershipPrice = selectedMembershipPrice + (parseFloat($(this).attr('price')) || 0);
                selectedMembershipExpireDate = $(this).attr('expire_date');
            });
            let vat = 0;
            let selectedMembershipPriceWithVat = 0;
            @if(@$mainSettings->vat_details['vat_percentage'])
                vat = selectedMembershipPrice * ({{@$mainSettings->vat_details['vat_percentage'] / 100}});
                vat = parseFloat(vat.toFixed(2));
            @endif
            selectedMembershipPriceWithVat = parseFloat(selectedMembershipPrice + vat).toFixed(2);

            _amountPaidUpdating = true;
            $('#create_amount_paid').val(selectedMembershipPriceWithVat).attr('max', selectedMembershipPriceWithVat);
            $('#create_amount_remaining').val(0);
            _amountPaidUpdating = false;
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
            // base subscription price + any option add-ons confirmed by server
            let basePrice = parseFloat($('#membership option:selected').attr('price')) || 0;
            let price = basePrice + posOptionsTotal;
            // Always keep #myTotal badge in sync (base + options, before discount/VAT)
            $('#myTotal').text("{{ trans('sw.price')}} = " + price.toFixed(2));
            let vat = 0;
            let priceWithVat = 0;
            let discount_value = 0;
            if(discount_amount === null)
                discount_value = parseFloat($('#discount_value').val()) || 0;
            else
                discount_value = parseFloat(discount_amount) || 0;

            @if(@$mainSettings->vat_details['vat_percentage'])
                vat = (parseFloat(price) - parseFloat(discount_value)) * ({{@$mainSettings->vat_details['vat_percentage'] / 100}});
                vat = parseFloat(vat.toFixed(2));
            @endif
                priceWithVat = parseFloat(price - discount_value + vat);
            // let create_amount_remaining = $('#create_amount_remaining').val();
            discount_value = parseFloat(discount_value) || 0;
            if ((discount_value > 0) && (price > 0)) {
                $('#myTotal').css('text-decoration', 'line-through');
                $('#myTotalAfterDiscount').show().text("{{ trans('sw.after_discount')}} = " + parseFloat(price - discount_value).toFixed(2));
            } else {
                $('#myTotalAfterDiscount').hide();
                $('#myTotal').css('text-decoration', 'unset');
            }

            $('#myTotalWithVat').text("{{ trans('sw.including_vat')}} = " + parseFloat(priceWithVat).toFixed(2));
            _amountPaidUpdating = true;
            $('#create_amount_paid').val(parseFloat(priceWithVat).toFixed(2)).attr('max', parseFloat(priceWithVat).toFixed(2));
            $('#create_amount_remaining').val(0);
            _amountPaidUpdating = false;

            // Calculate loyalty points
            calculateMemberLoyaltyPoints();
        }
        $('#group_discount_id').on('change', function (event){
            let discount_id = $(this).find(":selected").val();
            let type = parseInt($(this).find(":selected").attr('type')) || 0;
            let amount = parseFloat($(this).find(":selected").attr('amount')) || 0;
            let price = parseFloat($('#membership option:selected').attr('price')) || 0;
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

        // ── Member Activities Selection ─────────────────────────────────────
        function loadMemberActivities(subId) {
            var $card = $('#member_activities_card');
            var $body = $('#member_activities_body');

            if (!subId) { $card.hide(); $body.empty(); return; }

            $body.html('<div class="text-center py-3"><span class="spinner-border spinner-border-sm text-primary"></span></div>');
            $card.show();

            $.ajax({
                url: memberActivitiesUrl.replace(':id', subId),
                method: 'GET',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept': 'application/json' },
                dataType: 'json',
                success: function (res) {
                    var activities = res.activities || [];
                    if (activities.length === 0) { $card.hide(); $body.empty(); return; }
                    $card.show();
                    renderMemberActivities(activities, res.activity_limit);
                },
                error: function () { $card.hide(); $body.empty(); }
            });
        }

        function renderMemberActivities(activities, activityLimit) {
            var $body = $('#member_activities_body');
            var hasLimit = !!activityLimit;

            $('#member_activities_hint').text(hasLimit
                ? '{{ trans("sw.activity_limit_hint") }}'.replace(':limit', activityLimit)
                : '');

            var $row = $('<div class="row g-3">');
            activities.forEach(function (activity, idx) {
                var checked = !hasLimit || idx < activityLimit;
                var $col = $('<div class="col-md-6">');
                var $wrap = $('<div class="form-check form-check-custom form-check-solid p-3 bg-light rounded">');
                var $input = $('<input type="checkbox" class="form-check-input member-activity-check">')
                    .attr('name', 'member_activity_ids[]')
                    .attr('id', 'member_activity_' + activity.activity_id)
                    .val(activity.activity_id)
                    .prop('checked', checked);
                var $label = $('<label class="form-check-label ms-1">')
                    .attr('for', 'member_activity_' + activity.activity_id)
                    .html('<span class="fw-bold">' + activity.name + '</span>'
                        + (activity.trainer_name ? '<span class="text-muted fs-8 d-block"><i class="bi bi-person-badge me-1"></i>' + activity.trainer_name + '</span>' : '')
                        + '<span class="text-muted fs-8 d-block"><i class="bi bi-repeat me-1"></i>{{ trans("sw.training_times") }}: ' + (activity.training_times || 0) + '</span>');
                $wrap.append($input).append($label);
                $col.append($wrap);
                $row.append($col);
            });
            $body.empty().append($row);

            enforceMemberActivityLimit(activityLimit);
            $body.off('change', '.member-activity-check').on('change', '.member-activity-check', function () {
                enforceMemberActivityLimit(activityLimit);
            });
        }

        function enforceMemberActivityLimit(activityLimit) {
            if (!activityLimit) return;
            var checkedCount = $('.member-activity-check:checked').length;
            $('.member-activity-check:not(:checked)').prop('disabled', checkedCount >= activityLimit);
        }

        // ── POS Subscription Option Groups ────────────────────────────────────
        function posLoadOptionGroups(subId) {
            var $card = $('#pos_option_groups_card');
            var $body = $('#pos_option_groups_body');
            $('#pos_option_ids_container').empty();
            posOptionsTotal = 0;

            if (!subId) { $card.hide(); return; }

            $body.html('<div class="text-center py-5"><span class="spinner-border spinner-border-sm text-primary"></span></div>');
            $card.show();

            $.ajax({
                url: posOptionsUrl.replace(':id', subId),
                method: 'GET',
                data: { channel: 1 },
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept': 'application/json' },
                dataType: 'json',
                success: function(res) {
                    var groups = res.option_groups || [];
                    if (groups.length === 0) { $card.hide(); return; }
                    $card.show();
                    $body.empty().append(posRenderGroups(groups));
                },
                error: function() { $card.hide(); }
            });
        }

        function posRenderGroups(groups, preSelectedIds) {
            preSelectedIds = preSelectedIds || [];
            var $row = $('<div class="row g-3">');
            groups.forEach(function(group) {
                var isSingle  = group.selection_type === 'single';
                var isRequired= group.is_required;
                var optCount  = (group.options || []).length;
                var isProduct = group.source_type === 'product';
                var isPill    = !isProduct && optCount <= 6;

                var $col = $('<div class="col-md-6">');

                // ── Header ──────────────────────────────────────────────────
                var $hdr = $('<div class="d-flex flex-wrap align-items-center gap-1 mb-1">');
                $hdr.append($('<span class="fw-semibold fs-7">').text(group.name_ar));
                if (isRequired) $hdr.append($('<span class="badge badge-light-danger fs-9 px-1">').text('{{ trans("sw.mandatory") }}'));
                $hdr.append($('<span class="badge badge-light-secondary fs-9 px-1">').text(
                    isSingle ? '{{ trans("sw.single") }}' : '{{ trans("sw.multiple") }}'
                ));
                $col.append($hdr);

                if (isPill) {
                    // ── Pill / chip row ─────────────────────────────────────
                    var $pills = $('<div class="d-flex flex-wrap gap-1">');
                    (group.options || []).forEach(function(opt) {
                        var price = parseFloat(opt.price_modifier || 0);
                        var name;
                        if (opt.product) {
                            name = opt.product['display_name_{{ $lang }}'] || opt.product['name_{{ $lang }}'] || opt.product.name_ar || '';
                        } else if (opt.activity) {
                            name = opt.activity['name_{{ $lang }}'] || opt.activity.name_ar || '';
                        } else {
                            name = opt['name_{{ $lang }}'] || opt.name_ar || '';
                        }
                        var $pill = $('<label class="pos-pill">');
                        var $inp  = $('<input class="d-none pos-option-input">')
                            .attr('type', isSingle ? 'radio' : 'checkbox')
                            .attr('name', 'create_grp_' + group.id)
                            .attr('data-group-id', group.id)
                            .val(opt.id)
                            .on('change', function() {
                                if (isSingle) {
                                    $pills.find('.pos-pill').removeClass('active');
                                    $('#pos_option_groups_body .pos-option-input[data-group-id="' + group.id + '"]').not(this).prop('checked', false);
                                }
                                $(this).closest('.pos-pill').toggleClass('active', $(this).is(':checked'));
                                posUpdatePrice();
                            });
                        var lbl = name + (price !== 0 ? ' (' + (price > 0 ? '+' : '') + Math.round(price) + ')' : '');
                        $pill.append($inp).append($('<span>').text(lbl));
                        if (preSelectedIds.indexOf(opt.id) !== -1) { $inp.prop('checked', true); $pill.addClass('active'); }
                        $pills.append($pill);
                    });
                    $col.append($pills);

                } else if (isProduct) {
                    // ── Image thumbnail grid ────────────────────────────────
                    if (optCount > 6) {
                        $col.append(
                            $('<input type="text" class="form-control form-control-sm mb-1" placeholder="بحث...">').on('input', function() {
                                var q = $(this).val().toLowerCase();
                                $(this).next('.pos-product-grid').find('.pos-prod-item').each(function() {
                                    $(this).toggle($(this).data('name').toLowerCase().indexOf(q) !== -1);
                                });
                            })
                        );
                    }
                    var $grid = $('<div class="row g-1 pos-product-grid" style="max-height:200px;overflow-y:auto;padding:2px;">');
                    (group.options || []).forEach(function(opt) {
                        var price  = parseFloat(opt.price_modifier || 0);
                        var name   = '', imgSrc = null;
                        if (opt.product) {
                            name   = opt.product['display_name_{{ $lang }}'] || opt.product['name_{{ $lang }}'] || opt.product.name_ar || '';
                            imgSrc = opt.product.image || null;
                        } else if (opt.activity) {
                            name   = opt.activity['name_{{ $lang }}'] || opt.activity.name_ar || '';
                            imgSrc = opt.activity.image || null;
                        } else {
                            name = opt['name_{{ $lang }}'] || opt.name_ar || '';
                        }
                        var $cell  = $('<div class="col-6 pos-prod-item">').data('name', name);
                        var $label = $('<label class="d-flex align-items-center gap-1 p-1 rounded border-hover-primary cursor-pointer" style="min-height:44px;">');
                        var $inp   = $('<input class="form-check-input pos-option-input flex-shrink-0 mt-0">')
                            .attr('type', isSingle ? 'radio' : 'checkbox')
                            .attr('name', 'create_grp_' + group.id)
                            .attr('data-group-id', group.id)
                            .val(opt.id)
                            .on('change', function() {
                                if (isSingle) $('#pos_option_groups_body .pos-option-input[data-group-id="' + group.id + '"]').not(this).prop('checked', false);
                                posUpdatePrice();
                            });
                        if (preSelectedIds.indexOf(opt.id) !== -1) $inp.prop('checked', true);
                        $label.append($inp);
                        if (imgSrc) $label.append($('<img>').attr('src', imgSrc).addClass('pos-prod-thumb'));
                        var $info = $('<div class="overflow-hidden lh-sm">');
                        $info.append($('<div class="fs-9 text-truncate" style="max-width:80px;" title="' + name + '">').text(name));
                        if (price !== 0) $info.append($('<span class="badge badge-light-primary px-1" style="font-size:10px;">').text((price > 0 ? '+' : '') + Math.round(price)));
                        $label.append($info);
                        $cell.append($label);
                        $grid.append($cell);
                    });
                    $col.append($grid);

                } else {
                    // ── Large scrollable list + search ──────────────────────
                    $col.append(
                        $('<input type="text" class="form-control form-control-sm mb-1" placeholder="بحث / Search...">').on('input', function() {
                            var q = $(this).val().toLowerCase();
                            $(this).siblings('.pos-option-list').find('.pos-option-item').each(function() {
                                $(this).toggle($(this).text().toLowerCase().indexOf(q) !== -1);
                            });
                        })
                    );
                    var $list = $('<div class="d-flex flex-column gap-1 pos-option-list" style="max-height:180px;overflow-y:auto;">');
                    (group.options || []).forEach(function(opt) {
                        var price = parseFloat(opt.price_modifier || 0);
                        var name  = opt['name_{{ $lang }}'] || opt.name_ar || '';
                        if (opt.product) name = opt.product['display_name_{{ $lang }}'] || opt.product['name_{{ $lang }}'] || opt.product.name_ar || '';
                        else if (opt.activity) name = opt.activity['name_{{ $lang }}'] || opt.activity.name_ar || '';
                        var $label = $('<label class="d-flex align-items-center gap-2 cursor-pointer p-1 rounded border-hover-primary">');
                        var $inp   = $('<input class="form-check-input pos-option-input mt-0">')
                            .attr('type', isSingle ? 'radio' : 'checkbox')
                            .attr('name', 'create_grp_' + group.id)
                            .attr('data-group-id', group.id)
                            .val(opt.id)
                            .on('change', function() {
                                if (isSingle) $('#pos_option_groups_body .pos-option-input[data-group-id="' + group.id + '"]').not(this).prop('checked', false);
                                posUpdatePrice();
                            });
                        if (preSelectedIds.indexOf(opt.id) !== -1) $inp.prop('checked', true);
                        $label.append($inp).append($('<span class="flex-grow-1 fs-8">').text(name));
                        if (price !== 0) $label.append($('<span class="badge badge-light-primary fs-9">').text((price > 0 ? '+' : '') + Math.round(price)));
                        $list.append($('<div class="pos-option-item">').append($label));
                    });
                    $col.append($list);
                }

                $row.append($col);
            });
            return $row;
        }

        function posUpdatePrice() {
            var subId = $('#membership').val();
            if (!subId) return;

            var optionIds = [];
            $('#pos_option_groups_body .pos-option-input:checked').each(function() {
                optionIds.push(parseInt($(this).val()));
            });

            // Update hidden option_ids inputs for form submission
            var $container = $('#pos_option_ids_container');
            $container.empty();
            optionIds.forEach(function(id) {
                $container.append($('<input type="hidden" name="option_ids[]">').val(id));
            });

            // Ask server for confirmed pricing breakdown (abort any stale in-flight request)
            if (_posCalcXhr) { _posCalcXhr.abort(); _posCalcXhr = null; }
            _posCalcXhr = $.ajax({
                url: posCalcPriceUrl.replace(':id', subId),
                method: 'POST',
                data: { option_ids: optionIds },
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept': 'application/json' },
                dataType: 'json',
                success: function(res) {
                    _posCalcXhr = null;
                    posOptionsTotal         = parseFloat(res.options_total) || 0;
                    selectedMembershipPrice = (res.base_price != null) ? parseFloat(res.base_price) : selectedMembershipPrice;

                    // Rebuild the breakdown panel
                    var $bd   = $('#pos_options_breakdown');
                    var baseP = selectedMembershipPrice;
                    var optsP = posOptionsTotal;

                    var selectedOpts = res.selected_options || [];
                    if (selectedOpts.length > 0) {
                        var html = '<div class="p-3 rounded" style="background:#f0fdf4;border:1px dashed #16a34a">'
                            + '<div class="fw-bold text-success mb-2 fs-7"><i class="bi bi-receipt me-1"></i>{{ trans("sw.price_breakdown") }}</div>'
                            + '<div class="d-flex justify-content-between text-muted fs-7 mb-1">'
                            + '<span>{{ trans("sw.base_price") }}</span><span>' + baseP.toFixed(2) + ' {{ trans("sw.app_currency") }}</span></div>';

                        selectedOpts.forEach(function(o) {
                            var mod  = parseFloat(o.price_modifier || 0);
                            var name = o['name_{{ $lang }}'] || o.name_ar || o.name_en || '';
                            if (!name) return;
                            var modLabel = mod === 0 ? '{{ trans("sw.app_currency") == "ر.س" ? "مجاناً" : "Free" }}'
                                : (mod > 0 ? '+' : '') + mod.toFixed(2) + ' {{ trans("sw.app_currency") }}';
                            html += '<div class="d-flex justify-content-between text-success fs-7 mb-1">'
                                + '<span><i class="bi bi-check2 me-1"></i>' + $('<span>').text(name).html() + '</span>'
                                + '<span>' + modLabel + '</span></div>';
                        });

                        var subtotalBd = baseP + optsP;
                        html += '<div class="d-flex justify-content-between fw-bold border-top border-success mt-2 pt-2">'
                            + '<span>{{ trans("sw.total") }}</span>'
                            + '<span>' + subtotalBd.toFixed(2) + ' {{ trans("sw.app_currency") }}</span></div>';

                        if (SW_VAT_PCT > 0) {
                            var vatAmtBd = parseFloat((subtotalBd * SW_VAT_PCT / 100).toFixed(2));
                            html += '<div class="d-flex justify-content-between text-muted fs-7 mt-1">'
                                + '<span>{{ trans("sw.vat") }} (' + SW_VAT_PCT + '%)</span>'
                                + '<span>+' + vatAmtBd.toFixed(2) + ' {{ trans("sw.app_currency") }}</span></div>';
                            html += '<div class="d-flex justify-content-between fw-bold text-primary mt-1">'
                                + '<span>{{ trans("sw.total_after_vat") }}</span>'
                                + '<span>' + (subtotalBd + vatAmtBd).toFixed(2) + ' {{ trans("sw.app_currency") }}</span></div>';
                        }

                        html += '</div>';
                        $bd.html(html).show();
                    } else {
                        $bd.hide();
                    }

                    posUpdateSideSummary(res, baseP, optsP);
                    discount_value(); // refresh badges and amount_paid
                },
                error: function(xhr) {
                    if (xhr.statusText === 'abort') return;
                    _posCalcXhr = null;
                    console.warn('calculate-price failed', xhr.status, xhr.responseText);
                    posOptionsTotal = 0;
                    $('#pos_option_groups_body .pos-option-input:checked').each(function() {
                        var $label = $(this).closest('label');
                        var badgeText = $label.find('.badge').text().replace(/[^0-9.\-]/g, '');
                        posOptionsTotal += parseFloat(badgeText) || 0;
                    });
                    discount_value();
                }
            });
        }
        function posUpdateSideSummary(res, baseP, optsP) {
            var $card = $('#pos_side_summary');
            var $body = $('#pos_side_summary_body');
            var selectedOpts = res.selected_options || [];

            // Hide if no options were selected at all
            if (!selectedOpts.length) { $card.hide(); return; }

            var subName = $('#membership option:selected').text().trim();
            var html = '';

            // Subscription name row
            html += '<div class="d-flex justify-content-between py-2 border-bottom">'
                + '<span class="text-muted fs-7">{{ trans("sw.membership") }}</span>'
                + '<span class="fw-semibold fs-7 text-end">' + $('<span>').text(subName).html() + '</span></div>';

            // Base price row
            html += '<div class="d-flex justify-content-between py-2 border-bottom">'
                + '<span class="text-muted fs-7">{{ trans("sw.base_price") }}</span>'
                + '<span class="fs-7">' + baseP.toFixed(2) + ' {{ trans("sw.app_currency") }}</span></div>';

            // One row per selected option (even if price_modifier = 0)
            selectedOpts.forEach(function(o) {
                var mod  = parseFloat(o.price_modifier || 0);
                var name = o['name_{{ $lang }}'] || o.name_ar || o.name_en || '';
                if (!name) return;
                var modText = mod === 0
                    ? '{{ trans("sw.app_currency") == "ر.س" ? "مجاناً" : "Free" }}'
                    : (mod > 0 ? '+' : '') + mod.toFixed(2) + ' {{ trans("sw.app_currency") }}';
                html += '<div class="d-flex justify-content-between align-items-center py-2 border-bottom">'
                    + '<span class="fs-7 text-gray-700"><i class="bi bi-check2-circle text-success me-1"></i>' + $('<span>').text(name).html() + '</span>'
                    + '<span class="badge badge-light-' + (mod > 0 ? 'success' : 'info') + ' fs-8">' + modText + '</span></div>';
            });

            // Subtotal (before VAT)
            var subtotal = baseP + optsP;

            // VAT row (only when VAT > 0)
            if (SW_VAT_PCT > 0) {
                var vatAmt = parseFloat((subtotal * SW_VAT_PCT / 100).toFixed(2));
                html += '<div class="d-flex justify-content-between align-items-center py-2 border-bottom text-muted">'
                    + '<span class="fs-7">{{ trans("sw.vat") }} (' + SW_VAT_PCT + '%)</span>'
                    + '<span class="fs-7">+' + vatAmt.toFixed(2) + ' {{ trans("sw.app_currency") }}</span></div>';

                // Total after VAT
                html += '<div class="d-flex justify-content-between align-items-center pt-3 mt-1 fw-bold fs-6">'
                    + '<span class="text-dark">{{ trans("sw.total") }}</span>'
                    + '<span class="text-success">' + (subtotal + vatAmt).toFixed(2) + ' {{ trans("sw.app_currency") }}</span></div>';
            } else {
                html += '<div class="d-flex justify-content-between align-items-center pt-3 mt-1 fw-bold fs-6">'
                    + '<span class="text-dark">{{ trans("sw.total") }}</span>'
                    + '<span class="text-success">' + subtotal.toFixed(2) + ' {{ trans("sw.app_currency") }}</span></div>';
            }

            $body.html(html);
            $card.show();
        }
        // ── End POS Subscription Option Groups ───────────────────────────────

        apply_discount_subscription();
        function apply_discount_subscription(){
            let type = (parseInt($('#membership option:selected').attr('discount_type')));
            let amount = (parseFloat($('#membership option:selected').attr('discount_value')));
            let price = (parseFloat($('#membership option:selected').attr('price')));
            let result = 0;
            $('#discount_subscription_message').html('');
            if(amount) {
                let discount_message = '{{ trans('sw.discount_subscription_message', ['amount'=> ':amount', 'type' => ':type'])}}';

                if (type === 1) {
                    result = parseFloat(Number(price) * (Number(amount) / 100)).toFixed(2);
                    $('#discount_value').val(result);
                    discount_value(result);
                    discount_message = discount_message.replace(':amount', amount);
                    discount_message = discount_message.replace(':type', '% (' + result + ')');
                } else if (type === 2) {
                    $('#discount_value').val(amount);
                    discount_value(amount);
                    discount_message = discount_message.replace(':amount', amount);
                    discount_message = discount_message.replace(':type', ' ({{ trans('sw.fixed_amount') }})');
                }
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

    <script>
        // ===== Payment Gateway Pre-Submit Flow =====
        var pw_check_status_url = '{{ route('sw.checkMemberPaymentStatus', ':id') }}';
        var pw_resend_url = '{{ route('sw.resendMemberPaymentLink', ':id') }}';
        // ===== Payment Gateway Pre-Submit Flow (uses shared pw* functions from master layout) =====
        function pwGetActiveGateway() {
            if ($('#send_tabby_link').is(':checked')) return 'tabby';
            if ($('#send_tamara_link').is(':checked')) return 'tamara';
            if ($('#send_paymob_link').is(':checked')) return 'paymob';
            if ($('#send_paytabs_link').is(':checked')) return 'paytabs';
            return null;
        }

        function pwHasCommunicationChannel() {
            var phone = $('input[name="phone"]').val() || '';
            var email = $('input[name="email"]').val() || '';
            return (pw_active_wa && phone.trim().length > 0)
                || (pw_active_sms && phone.trim().length > 0)
                || email.trim().length > 0;
        }

        // Phone existence check before form submit
        var phoneCheckUrl = '{{ route('sw.checkMemberPhoneExists') }}';
        var phoneAlreadyConfirmed = false;

        function checkPhoneThenSubmit(e, $form) {
            if (phoneAlreadyConfirmed) return true;
            var phone = $form.find('input[name="phone"]').val().trim();
            if (!phone) return true;

            e.preventDefault();
            $.ajax({
                url: phoneCheckUrl,
                type: 'GET',
                data: { phone: phone },
                success: function (data) {
                    if (data.exists) {
                        swal({
                            title: '{{ trans('sw.error') }}',
                            text: '{{ trans('sw.phone_already_exists') }}',
                            type: 'error',
                            confirmButtonText: '{{ trans('sw.ok') }}'
                        });
                    } else {
                        phoneAlreadyConfirmed = true;
                        $form.submit();
                    }
                },
                error: function () {
                    // If check fails, allow form to proceed
                    phoneAlreadyConfirmed = true;
                    $form.submit();
                }
            });
            return false;
        }

        // Intercept form submit when a payment gateway is checked
        $('form.form').on('submit', function (e) {
            var $form = $(this);
            // Run phone existence check first (only on new member, not edit)
            @if(!$member->id)
            if (!phoneAlreadyConfirmed) {
                return checkPhoneThenSubmit(e, $form);
            }
            @endif

            var gateway = pwGetActiveGateway();
            if (!gateway) return true; // no gateway selected, submit normally

            if (!pwHasCommunicationChannel()) {
                e.preventDefault();
                swal({
                    title: '{{ trans('sw.payment_no_communication_title') }}',
                    text: '{{ trans('sw.payment_no_communication_desc') }}',
                    type: 'warning',
                    confirmButtonText: '{{ trans('sw.payment_continue_without') }}',
                    showCancelButton: true,
                    cancelButtonText: '{{ trans('admin.cancel') }}'
                }).then(function (confirm) {
                    if (confirm) {
                        $('#send_tabby_link, #send_tamara_link, #send_paymob_link, #send_paytabs_link').prop('checked', false);
                        $('form.form').off('submit').submit();
                    }
                });
                return false;
            }

            // Step 1: send payment link first (no member/subscription created yet)
            e.preventDefault();
            var $btn = $form.find('[type=submit]').prop('disabled', true).addClass('disabled');

            var payload = {
                gateway:         gateway,
                subscription_id: $form.find('[name=subscription_id]').val(),
                discount_value:  $form.find('[name=discount_value]').val() || 0,
                name:            $form.find('[name=name]').val(),
                phone:           $form.find('[name=phone]').val(),
                email:           $form.find('[name=email]').val(),
                city:            $form.find('[name=city]').val(),
                address:         $form.find('[name=address]').val(),
                _token:          $form.find('[name=_token]').val()
            };

            $.ajax({
                url:  pw_new_member_check_send_url,
                type: 'POST',
                data: payload,
                success: function (data) {
                    $btn.prop('disabled', false).removeClass('disabled');
                    if (!data.status) {
                        swal('{{ trans('sw.error') }}', data.msg || '{{ trans('sw.something_went_wrong') }}', 'warning');
                        return;
                    }

                    // Step 2: open waiting modal — poll by invoice ID
                    pwOpenModal(
                        data.invoice_id,
                        data.sent_via,
                        null,
                        gateway,
                        function () {
                            // Step 3: payment confirmed or "complete" — submit form as normal (no gateway)
                            $('#send_tabby_link, #send_tamara_link, #send_paymob_link, #send_paytabs_link').prop('checked', false);
                            $('form.form').off('submit').submit();
                        },
                        pw_check_invoice_url,
                        data.payment_url || null,
                        data.member_phone || null
                    );
                },
                error: function () {
                    $btn.prop('disabled', false).removeClass('disabled');
                    swal('{{ trans('sw.error') }}', '{{ trans('sw.something_went_wrong') }}', 'error');
                }
            });

            return false;
        });

        // Init payment gateway cards (mutual exclusivity)
        if (typeof window.initPgwCards === 'function') {
            window.initPgwCards('#payment_gateway_section');
        }
    </script>

@endsection



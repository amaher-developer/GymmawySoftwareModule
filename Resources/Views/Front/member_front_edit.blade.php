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
            #myTotalAfterDiscount {
                display: none;
            }

            /* Actions column styling */
            .actions-column {
                min-width: 200px !important;
                white-space: nowrap;
            }

            .actions-column .d-flex {
                gap: 0.25rem;
                flex-wrap: wrap;
            }

            .actions-column .btn {
                margin: 0;
                padding: 0.375rem;
                width: 32px;
                height: 32px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            @media (max-width: 1200px) {
                .actions-column {
                    min-width: 150px !important;
                }
            }

            @media (max-width: 992px) {
                .actions-column {
                    min-width: 120px !important;
                }
            }
    </style>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

@endsection
@section('page_body')


    <!--begin::Member Edit Form-->
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
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.the_image')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <a class="modal-effect" data-effect="effect-newspaper" onclick="startWebCam()"
                           data-toggle="modal" href="#modalCamera"> <i class="fa fa-camera text-muted"
                                                                       title="{{ trans('sw.camera_msg')}}"
                                                                       aria-hidden="true"></i></a>
                        <input id="SubscribedClientsInputPhoto"
                               data-default-file="{{@$member->image}}"
                               name="image" type="file" class="dropify mb-2" data-height="200"
                               accept=".jpg, .png, image/jpeg, image/png"/>
                        <input type="hidden" name="image" id="photo_camera">
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Section Header-->
                    <div class="mb-10">
                        <h4 class="form-section"><i class="fa fa-user"></i> {{ trans('sw.member_info')}}</h4>
                    </div>
                    <!--end::Section Header-->
                    
                    <!--begin::Identification Code-->
                    <div class="row mb-5">
                        <label class="col-md-3 col-form-label">{{ trans('sw.identification_code')}} <span class="required"></span></label>
                        <div class="col-md-6">
                            <input name="code" onkeydown="return event.key!=='Enter';" value="{{ old('code', $member->code) }}"
                                   type="text" class="form-control" min="0"
                                   id="subscribedClientInputCode"
                                   placeholder="{{ trans('sw.enter_identification_code')}}" disabled required>
                        </div>
                        <div class="col-md-3">
                            <input type="button" onclick="editBarCodeInput();" id="editBarcodeBtn" class="btn green rounded-3" value="{{ trans('sw.edit')}}">
                        </div>
                    </div>
                    <!--end::Identification Code-->

                    <!--begin::Fingerprint-->
                    @if(@env('APP_ZK_GATE') == true)
                    <div class="row mb-5">
                        <label class="col-md-3 col-form-label">{{ trans('sw.fingerprint')}} </label>
                        <div class="col-md-9">
                            <div class="input-group">
                                <input class="form-control" placeholder="{{ trans('sw.fingerprint_id_data')}}"
                                       name="fp_id" min="0"
                                       value="{{ old('fp_id', $member->fp_id) }}"
                                       type="text">
                                <span class="input-group-btn">
                                    <button class="btn default" type="button"><i class="material-icons" style="font-size: inherit !important;">fingerprint</i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!--end::Fingerprint-->

                    <!--begin::Name-->
                    <div class="row mb-5">
                        <label class="col-md-3 col-form-label">{{ trans('sw.name')}} <span class="required"></span></label>
                        <div class="col-md-9">
                            <input name="name" value="{{ old('name', $member->name) }}" type="text" class="form-control"
                                   id="unsubscribedClientInputName" placeholder="{{ trans('sw.enter_name')}}" required>
                        </div>
                    </div>
                    <!--end::Name-->

                    <!--begin::Gender-->
                    <div class="row mb-5">
                        <label class="col-md-3 col-form-label">{{ trans('sw.gender')}} <span class="required"></span></label>
                        <div class="col-md-9">
                            <div class="d-flex gap-5">
                                <label class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="radio" name="gender" id="optionsRadios25" 
                                           value="{{\Modules\Software\Classes\TypeConstants::MALE}}" 
                                           @if(\Modules\Software\Classes\TypeConstants::MALE == $member->gender) checked="" @endif required>
                                    <span class="form-check-label">{{ trans('sw.male')}}</span>
                                </label>
                                <label class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="radio" name="gender" id="optionsRadios26" 
                                           value="{{\Modules\Software\Classes\TypeConstants::FEMALE}}" 
                                           @if(\Modules\Software\Classes\TypeConstants::FEMALE == $member->gender) checked="" @endif required>
                                    <span class="form-check-label">{{ trans('sw.female')}}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <!--end::Gender-->

                    <!--begin::Phone-->
                    <div class="row mb-5">
                        <label class="col-md-3 col-form-label">{{ trans('sw.phone')}} <span class="required"></span></label>
                        <div class="col-md-9">
                            <input name="phone" value="{{ old('phone', $member->phone) }}" type="text" class="form-control"
                                   id="subscribedClientInputPhone"
                                   placeholder="{{ trans('sw.enter_phone')}}" required>
                        </div>
                    </div>
                    <!--end::Phone-->

                    <!--begin::Email-->
                    <div class="row mb-5">
                        <label class="col-md-3 col-form-label">{{ trans('sw.email')}} </label>
                        <div class="col-md-9">
                            <input name="email" value="{{ old('email', $member->email) }}" type="text" class="form-control"
                                   placeholder="{{ trans('sw.enter_email')}}">
                        </div>
                    </div>
                    <!--end::Email-->

                    <!--begin::National ID-->
                    <div class="row mb-5">
                        <label class="col-md-3 col-form-label">{{ trans('sw.national_id')}} </label>
                        <div class="col-md-9">
                            <input name="national_id" value="{{ old('national_id', @$member->national_id) }}" type="text" class="form-control"
                                   id="national_id"
                                   placeholder="{{ trans('sw.enter_national_id')}}">
                        </div>
                    </div>
                    <!--end::National ID-->

                    <!--begin::Address-->
                    <div class="row mb-5">
                        <label class="col-md-3 col-form-label">{{ trans('sw.address')}} <span class="required"></span></label>
                        <div class="col-md-9">
                            <input name="address" value="{{ old('address', $member->address) }}" type="text" class="form-control"
                                   id="subscribedClientInputAddress"
                                   placeholder="{{ trans('sw.enter_address')}}" required>
                        </div>
                    </div>
                    <!--end::Address-->

                    <!--begin::Date of Birth-->
                    <div class="row mb-5">
                        <label class="col-md-3 col-form-label">{{ trans('sw.date_of_barth')}} </label>
                        <div class="col-md-7">
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                <input class="form-control" autocomplete="off" placeholder="{{ trans('sw.date_of_barth')}}"
                                       name="dob"
                                       value="{{ old('dob', $member->dob) }}"
                                       type="text">
                                <span class="input-group-btn">
                                    <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <!--end::Date of Birth-->

                    <!--begin::Sale User-->
                    <div class="row mb-5">
                        <label class="col-md-3 col-form-label">{{ trans('sw.sale_user')}} </label>
                        <div class="col-md-9">
                            <select id="sale_user_id" name="sale_user_id" class="form-control select2">
                                <option value="">{{ trans('sw.choose')}}</option>
                                @foreach($users as $user)
                                    <option value="{{$user->id}}" @if($user->id == $member->sale_user_id) selected="" @endif>{{$user->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <!--end::Sale User-->

                    <!--begin::Sale Channels-->
                    @if(count($channels) > 0)
                    <div class="row mb-5">
                        <label class="col-md-3 col-form-label">{{ trans('sw.sale_channels')}} </label>
                        <div class="col-md-9">
                            <select id="sale_channel_id" name="sale_channel_id" class="form-control select2">
                                <option value="">{{ trans('sw.choose')}}</option>
                                @foreach($channels as $channel)
                                    <option value="{{$channel->id}}" @if($channel->id == $member->sale_channel_id) selected="" @endif>{{$channel->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif
                    <!--end::Sale Channels-->

                    <!--begin::Additional Info-->
                    <div class="row mb-5">
                        <label class="col-md-3 col-form-label">{{ trans('sw.additional_information')}} </label>
                        <div class="col-md-9">
                            <textarea class="form-control" placeholder="{{ trans('sw.additional_information')}}" name="additional_info" rows="3">{{ old('additional_info', $member->additional_info) }}</textarea>
                        </div>
                    </div>
                    <!--end::Additional Info-->

                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-10"></div>
                    <!--end::Separator-->

                    <!--begin::Section Header-->
                    <div class="mb-10">
                        <h4 class="form-section"><i class="fa fa-list"></i> {{ trans('sw.membership_info')}}</h4>
                    </div>
                    <!--end::Section Header-->

                    <!--begin::Membership Table-->
                    <div class="table-responsive border-top userlist-table">
                        <table class="table card-table table-striped table-vcenter text-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th scope="col"><i class="fa fa-list"></i> {{ trans('sw.membership')}}</th>
                                    <th scope="col"><i class="fa fa-sort-numeric-asc"></i> {{ trans('sw.workouts')}}</th>
                                    <th scope="col"><i class="fa fa-sort-numeric-asc"></i> {{ trans('sw.number_of_visits')}}</th>
                                    <th scope="col"><i class="fa fa-sign-in"></i> {{ trans('sw.joining_date')}}</th>
                                    <th scope="col"><i class="fa fa-sign-out"></i> {{ trans('sw.expire_date')}}</th>
                                    <th scope="col">{{ trans('sw.status')}}</th>
                                    <th scope="col"><i class="fa fa-dollar"></i> {{ trans('sw.amount_remaining')}}</th>
                                    <th scope="col"><i class="fa fa-calendar"></i> {{ trans('sw.date')}}</th>
                                    <th scope="col" class="text-end actions-column"><i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('admin.actions')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($member_subscriptions as $key=> $member_subscription)
                                    <tr>
                                        <td>{{ @$member_subscription->subscription->name }}</td>
                                        <td>{{ @$member_subscription->workouts }}</td>
                                        <td>{{ @$member_subscription->visits }}</td>
                                        <td>{{ @\Carbon\Carbon::parse($member_subscription->joining_date)->toDateString() }}</td>
                                        <td>{{ @\Carbon\Carbon::parse($member_subscription->expire_date)->toDateString() }}</td>
                                        <td>
                                            <span class="badge @if(@$member_subscription->status == 0) badge-success @elseif(@$member_subscription->status == 1) badge-info @elseif(@$member_subscription->status == 2) badge-danger @endif">{!! @$member_subscription->statusName !!}</span>
                                        </td>
                                        <td>{{ @number_format($member_subscription->amount_remaining, 2) }}</td>
                                        <td>
                                            <i class="fa fa-calendar text-muted"></i> {{ @\Carbon\Carbon::parse($member_subscription->created_at)->format('Y-m-d') }}
                                            <br/>
                                            <i class="fa fa-clock-o text-muted"></i> {{ @\Carbon\Carbon::parse($member_subscription->created_at)->format('h:i a') }}
                                        </td>
                                        <td class="text-end actions-column">
                                            <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">
                                                <!--begin::Invoice-->
                                                <a href="{{route('sw.showOrderSubscription',$member_subscription->id)}}" 
                                                   class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm btn-indigo" 
                                                   title="{{ trans('sw.invoice')}}">
                                                    <i class="ki-outline ki-document fs-2"></i>
                                                </a>
                                                <!--end::Invoice-->
                                                
                                                <!--begin::Pay-->
                                                @if(in_array('createMemberPayAmountRemainingForm', (array)$swUser->permissions) || $swUser->is_super_user)
                                                    @if(@round($member_subscription->amount_remaining, 2) > 0)
                                                        <a data-target="#modalPay" 
                                                           data-toggle="modal" 
                                                           href="#"
                                                           id="{{@$member_subscription->id}}"
                                                           class="btn btn-icon btn-bg-light btn-active-color-warning btn-sm btn-indigo"
                                                           title="{{ trans('sw.pay_remaining')}}">
                                                            <i class="ki-outline ki-dollar fs-2"></i>
                                                        </a>
                                                    @endif
                                                @endif
                                                <!--end::Pay-->
                                                
                                                <!--begin::Edit-->
                                                @if(($key < 2) && (in_array('editMember', (array)$swUser->permissions) || $swUser->is_super_user))
                                                    <a class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
                                                       onclick="edit_member_membership('{{@$member_subscription->id}}')"
                                                       expire_date="{{\Carbon\Carbon::parse(@$member_subscription->expire_date)->toDateString()}}"
                                                       start_date="{{\Carbon\Carbon::parse(@$member_subscription->joining_date)->toDateString()}}"
                                                       amount_paid="{{@$member_subscription->amount_paid}}"
                                                       discount_value="{{@$member_subscription->discount_value}}"
                                                       expire_msg="{{ trans('sw.expire_date_msg', ['date' => @\Carbon\Carbon::parse($member_subscription->expire_date)->toDateString()])}}"
                                                       expire_color="@if(@$member_subscription->status == 0) green @else red @endif"
                                                       id="edit_member_{{@$member_subscription->id}}"
                                                       prev_amount_paid="{{$member_subscription->amount_paid}}"
                                                       title="{{ trans('sw.edit')}}">
                                                        <i class="ki-outline ki-pencil fs-2"></i>
                                                    </a>
                                                @endif
                                                <!--end::Edit-->

                                                <!--begin::Delete Actions-->
                                                @if((@\Carbon\Carbon::parse($member_subscription->joining_date)->toDateString() > \Carbon\Carbon::now()->toDateString())
                                                    && (!$loop->last) && (in_array('deleteMember', (array)$swUser->permissions) || $swUser->is_super_user))
                                                    @if(request('trashed'))
                                                        <a href="{{route('sw.deleteMemberSubscription',$member_subscription->id)}}"
                                                           class="confirm_delete btn btn-icon btn-bg-light btn-active-color-success btn-sm"
                                                           title="{{ trans('admin.enable')}}">
                                                            <i class="ki-outline ki-check-circle fs-2"></i>
                                                        </a>
                                                    @else
                                                        <a href="{{route('sw.deleteMemberSubscription',$member_subscription->id).'?refund=0'}}"
                                                           class="confirm_delete btn btn-icon btn-bg-light btn-active-color-secondary btn-sm"
                                                           data-swal-text="{{ trans('sw.disable_without_refund')}}"
                                                           title="{{ trans('sw.disable_without_refund')}}">
                                                            <i class="ki-outline ki-trash fs-2"></i>
                                                        </a>
                                                        <a href="{{route('sw.deleteMemberSubscription',$member_subscription->id).'?refund=1'}}"
                                                           class="confirm_delete btn btn-icon btn-bg-light btn-active-color-danger btn-sm"
                                                           data-swal-text="{{ trans('sw.disable_with_refund', ['amount' => $member_subscription->amount_paid])}}"
                                                           title="{{ trans('sw.disable_with_refund', ['amount' => $member_subscription->amount_paid])}}">
                                                            <i class="ki-outline ki-trash fs-2"></i>
                                                        </a>
                                                        @endif
                                                    @endif
                                                <!--end::Delete Actions-->
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!--end::Membership Table-->

                    <!--begin::Form Actions-->
                    <div class="d-flex justify-content-end mt-10">
                        <button type="reset" class="btn btn-light me-3">{{ trans('admin.reset')}}</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">{{ trans('global.save')}}</span>
                        </button>
                    </div>
                    <!--end::Form Actions-->

                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Member Details-->
            </div>
            <!--end::Main column-->
    </form>
    <!--end::Member Edit Form-->




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


    <!-- start model pay -->
    <div class="modal" id="modalPay">
        <div class="modal-dialog" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">{{ trans('sw.pay_remaining')}}</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h6>{{ trans('sw.amount_paid')}}</h6>
                    <div id="modalPayResult"></div>
                    <form id="form_pay" action="" method="GET">
                        <div class="row">
                            <div class="form-group col-lg-6">
                                <input name="amount_paid" class="form-control" type="number" id="amount_paid"  step="0.01"
                                       placeholder="{{ trans('sw.enter_amount_paid')}}">
                            </div><!-- end pay qty  -->
                            <div class="form-group col-lg-6">
                                <select class="form-control" name="payment_type" id="pay_payment_type">
                                    @foreach($payment_types as $payment_type)
                                        <option value="{{$payment_type->payment_id}}" @if(@old('payment_type',$order->payment_type) == $payment_type->payment_id) selected="" @endif>{{$payment_type->name}}</option>
                                    @endforeach
{{--                                    <option value="{{\Modules\Software\Classes\TypeConstants::CASH_PAYMENT}}" >{{ trans('sw.payment_cash')}}</option>--}}
{{--                                    <option value="{{\Modules\Software\Classes\TypeConstants::ONLINE_PAYMENT}}" >{{ trans('sw.payment_online')}}</option>--}}
{{--                                    <option value="{{\Modules\Software\Classes\TypeConstants::BANK_TRANSFER_PAYMENT}}" >{{ trans('sw.payment_bank_transfer')}}</option>--}}
                                </select>
                            </div><!-- end pay qty  -->
                        </div>
                        <br/>
                        <button class="btn ripple btn-primary rounded-3" id="form_pay_btn"
                                type="submit">{{ trans('sw.pay')}}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- End model pay -->


    <!-- Start Modal Edit Membership -->
    <div class="modal fade" id="modelEditMembership" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <!--begin::Modal header-->
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('sw.edit_membership')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <!--end::Modal header-->
                
                <!--begin::Modal body-->
                <div class="modal-body">
                    <!--begin::Membership Header-->
                    <div class="row mb-5">
                        <div class="col-md-5">
                            <h6 class="fw-bold">{{ trans('sw.membership')}}</h6>
                        </div>
                        <div class="col-md-7 @if($lang == 'ar') text-left @else text-end @endif">
                            <h6 id="membership_expire_date_msg"></h6>
                        </div>
                    </div>
                    <!--end::Membership Header-->

                    <!--begin::Select Membership-->
                    <div class="row mb-5">
                        <div class="col-md-12">
                            <label class="form-label">{{ trans('sw.membership')}}</label>
                            <select id="EditMembershipSelect" name="subscription_id" class="form-control select2">
                            </select>
                        </div>
                    </div>
                    <!--end::Select Membership-->

                    <!--begin::Date Range-->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">{{ trans('sw.membership_date')}}</label>
                            <div class="input-group date date-picker input-daterange" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control" id="start_date_membership"
                                       name="start_date" data-date-format="yyyy-mm-dd" value="" 
                                       autocomplete="off" placeholder="{{ trans('sw.start_date')}}">
                                <span class="input-group-text">{{ trans('sw.to')}}</span>
                                <input type="text" class="form-control" id="expire_date_membership"
                                       name="expire_date" data-date-format="yyyy-mm-dd" value="" 
                                       autocomplete="off" placeholder="{{ trans('sw.expire_date')}}">
                            </div>
                            <span id="error_expire_date" class="text-danger d-block mt-1"></span>
                        </div>
                    </div>
                    <!--end::Date Range-->

                    <!--begin::Workouts-->
                    <div class="row mb-5">
                        <label class="col-md-3 col-form-label">{{ trans('sw.workouts')}}</label>
                        <div class="col-md-9">
                            <div class="input-group">
                                <input id="EditMembershipWorkouts" name="workouts" type="number" 
                                       min="0" value="" class="form-control" placeholder="0"/>
                                <span class="input-group-text">
                                    <i class="fa fa-sort-numeric-asc"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <!--end::Workouts-->

                    <!--begin::Freeze Limit-->
                    <div class="row mb-5">
                        <label class="col-md-3 col-form-label">{{ trans('sw.freeze_limit')}}</label>
                        <div class="col-md-9">
                            <input id="EditMembershipFreezeLimit" value="" min="0"
                                   name="freeze_limit" type="number" class="form-control" 
                                   placeholder="{{ trans('sw.freeze_limit')}}">
                        </div>
                    </div>
                    <!--end::Freeze Limit-->

                    <!--begin::Number Times Freeze-->
                    <div class="row mb-5">
                        <label class="col-md-3 col-form-label">{{ trans('sw.number_times_freeze')}}</label>
                        <div class="col-md-9">
                            <input id="EditMembershipNumberTimesFreeze" value="" min="0"
                                   name="number_times_freeze" type="number" class="form-control" 
                                   placeholder="{{ trans('sw.number_times_freeze')}}">
                        </div>
                    </div>
                    <!--end::Number Times Freeze-->

                    <!--begin::Max Extension Days-->
                    <div class="row mb-5">
                        <label class="col-md-3 col-form-label">{{ trans('sw.max_extension_days')}}</label>
                        <div class="col-md-9">
                            <input id="EditMembershipMaxExtensionDays" value="" min="0"
                                   name="max_extension_days" type="number" class="form-control" 
                                   placeholder="{{ trans('sw.max_extension_days')}}">
                        </div>
                    </div>
                    <!--end::Max Extension Days-->

                    <!--begin::Max Freeze+Extension Sum-->
                    <div class="row mb-5">
                        <label class="col-md-3 col-form-label">{{ trans('sw.max_freeze_extension_sum')}}</label>
                        <div class="col-md-9">
                            <input id="EditMembershipMaxFreezeExtensionSum" value="" min="0"
                                   name="max_freeze_extension_sum" type="number" class="form-control"
                                   placeholder="{{ trans('sw.max_freeze_extension_sum')}}">
                        </div>
                    </div>
                    <!--end::Max Freeze+Extension Sum-->

                    <!--begin::Invitations-->
                    <div class="row mb-5">
                        <label class="col-md-3 col-form-label">{{ trans('sw.invitations_num')}}</label>
                        <div class="col-md-9">
                            <input id="EditMembershipInvitations" value="" min="0"
                                   name="invitations" type="number" class="form-control"
                                   placeholder="{{ trans('sw.invitations_num')}}">
                        </div>
                    </div>
                    <!--end::Invitations-->

                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-5"></div>
                    <!--end::Separator-->

                    <!--begin::Price Display-->
                    <div class="row mb-5">
                        <label class="col-md-3 col-form-label">{{ trans('sw.price')}}</label>
                        <div class="col-md-9">
                            <span class="badge badge-success me-2" id="myTotal">
                                {{ trans('sw.price')}} = {{(float)@$member->member_subscription_info->subscription->price}}
                            </span>
                            <span class="badge badge-purple" id="myTotalAfterDiscount" @if($member->discount_value) style="display: inline;" @endif>
                                {{ trans('sw.after_discount')}} = {{(float)@$member->member_subscription_info->subscription->price - @$member->discount_value}}
                            </span>
                        </div>
                    </div>
                    <!--end::Price Display-->

                    <!--begin::Discount Subscription Message-->
                    <div class="row mb-5">
                        <div class="col-md-12">
                            <div id="edit_discount_subscription_message"></div>
                        </div>
                    </div>
                    <!--end::Discount Subscription Message-->

                    <!--begin::Discount Section-->
                    <div class="row mb-5" @if((in_array('editMemberDiscount', (array)$swUser->permissions)) || $swUser->is_super_user) style="display: flex" @else style="display: none" @endif>
                        <label class="col-md-3 col-form-label">{{ trans('sw.discount_value')}}</label>
                        <div class="col-md-3">
                            <input class="form-control" autocomplete="off" 
                                   placeholder="{{ trans('sw.discount_value')}}"
                                   name="discount_value"
                                   id="discount_value"
                                   value=""
                                   min="0"
                                   max=""
                                   type="number" step="0.01">
                        </div>
                        
                        @if((count($discounts) > 0) && ((in_array('editMemberDiscountGroup', (array)$swUser->permissions)) || $swUser->is_super_user))
                        <label class="col-md-3 col-form-label">{{ trans('sw.discount')}}</label>
                        <div class="col-md-3">
                            <select id="group_discount_id" name="group_discount_id" class="form-control select2">
                                <option value="0" type="0" amount="0">{{ trans('sw.choose')}}</option>
                                @foreach($discounts as $discount)
                                    <option value="{{$discount->id}}" type="{{$discount->type}}" amount="{{$discount->amount}}">{{$discount->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>
                    <!--end::Discount Section-->

                    <!--begin::VAT Display-->
                    @if(@$mainSettings->vat_details['vat_percentage'])
                    <div class="row mb-5">
                        <label class="col-md-3 col-form-label">{{ trans('sw.include_vat', ['vat' => @$mainSettings->vat_details['vat_percentage']])}}</label>
                        <div class="col-md-9">
                            <span class="badge badge-warning" @if($member->discount_value) style="text-decoration: line-through" @endif id="myTotalWithVat">
                                {{ trans('sw.price')}} = {{(float)@$member->member_subscription_info->subscription->price}}
                            </span>
                        </div>
                    </div>
                    @endif
                    <!--end::VAT Display-->

                    <!--begin::Amount Paid-->
                    <div class="row mb-5">
                        <label class="col-md-3 col-form-label">{{ trans('sw.amount_paid')}} <span class="required"></span></label>
                        <div class="col-md-9">
                            <input id="create_amount_paid" class="form-control" name="amount_paid" 
                                   value="{{ old('amount_paid', @($member->amount_paid)) }}"
                                   max="{{@($member->member_subscription_info->subscription->price - $member->discount_value)}}"
                                   placeholder="{{ trans('sw.enter_amount_paid')}}" 
                                   type="number" step="0.01" min="0"/>
                            <span id="amount_paid_error" class="text-danger d-block mt-1"></span>
                        </div>
                    </div>
                    <!--end::Amount Paid-->

                    <!--begin::Amount Remaining-->
                    <div class="row mb-5">
                        <label class="col-md-3 col-form-label">{{ trans('sw.amount_remaining')}} <span class="required"></span></label>
                        <div class="col-md-9">
                            <input id="create_amount_remaining" class="form-control" name="amount_remaining"
                                   value="{{@old('amount_remaining', @$member->amount_remaining)}}"
                                   placeholder="{{ trans('sw.enter_amount_remaining')}}"
                                   disabled type="number" step="0.01" min="0"/>
                        </div>
                    </div>
                    <!--end::Amount Remaining-->


                    <!--begin::Notes-->
                    <div class="row mb-5">
                        <label class="col-md-3 col-form-label">{{ trans('sw.notes')}}</label>
                        <div class="col-md-9">
                            <textarea rows="2" maxlength="255" name="notes" id="EditMembershipNotes" 
                                      class="form-control" placeholder="{{ trans('sw.notes')}}">{{@old('notes', @$member->notes)}}</textarea>
                        </div>
                    </div>
                    <!--end::Notes-->

                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-5"></div>
                    <!--end::Separator-->

                    <!--begin::Previous Payment Info-->
                    <div class="row mb-3 text-muted">
                        <label class="col-md-6 col-form-label">{{ trans('sw.prev_amount_paid')}}</label>
                        <div class="col-md-6">
                            <p id="prev_amount_paid" class="mb-0">0</p>
                            <input value="" type="hidden" id="prev_amount_paid_input">
                        </div>
                    </div>
                    <!--end::Previous Payment Info-->

                    <!--begin::Diff Payment Info-->
                    <div class="row mb-3 text-muted">
                        <label class="col-md-6 col-form-label">{{ trans('sw.diff_amount_paid')}}</label>
                        <div class="col-md-6">
                            <p id="diff_amount_paid" class="mb-0">0</p>
                        </div>
                    </div>
                    <!--end::Diff Payment Info-->
                    
                    <!--begin::Payment Type-->
                    <div class="row mb-5" id="payment_type_row" style="display: none;">
                        <label class="col-md-3 col-form-label">{{ trans('sw.payment_type')}} <span class="required"></span></label>
                        <div class="col-md-9">
                            <select id="edit_payment_type" name="payment_type" class="form-control">
                                @foreach($payment_types as $payment_type)
                                    <option value="{{$payment_type->payment_id}}">{{$payment_type->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <!--end::Payment Type-->

                    <!--begin::Tabby Payment Option-->
                    @if(!empty($mainSettings->payments['tabby']['merchant_code'] ?? null))
                    <div class="row mb-5" id="edit_tabby_payment_row" style="display: none;">
                        <div class="col-md-12">
                            <label class="form-label">{{ trans('sw.tabby_payment')}}</label>
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="send_tabby_link" id="edit_send_tabby_link" value="1"/>
                                <label class="form-check-label" for="edit_send_tabby_link">
                                    {{ trans('sw.send_tabby_payment_link')}}
                                </label>
                            </div>
                            <div class="text-muted fs-7 mt-1">{{ trans('sw.tabby_payment_description')}}</div>
                        </div>
                    </div>
                    @endif
                    <!--end::Tabby Payment Option-->

                    <!--begin::Tamara Payment Option-->
                    @if(!empty($mainSettings->payments['tamara']['token'] ?? null))
                    <div class="row mb-5" id="edit_tamara_payment_row" style="display: none;">
                        <div class="col-md-12">
                            <label class="form-label">{{ trans('sw.tamara_payment')}}</label>
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="send_tamara_link" id="edit_send_tamara_link" value="1"/>
                                <label class="form-check-label" for="edit_send_tamara_link">
                                    {{ trans('sw.send_tamara_payment_link')}}
                                </label>
                            </div>
                            <div class="text-muted fs-7 mt-1">{{ trans('sw.tamara_payment_description')}}</div>
                        </div>
                    </div>
                    @endif
                    <!--end::Tamara Payment Option-->

                    <!--begin::Paymob Payment Option-->
                    @if(!empty($mainSettings->payments['paymob']['api_key'] ?? null))
                    <div class="row mb-5" id="edit_paymob_payment_row" style="display: none;">
                        <div class="col-md-12">
                            <label class="form-label">{{ trans('sw.paymob_payment')}}</label>
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="send_paymob_link" id="edit_send_paymob_link" value="1"/>
                                <label class="form-check-label" for="edit_send_paymob_link">
                                    {{ trans('sw.send_paymob_payment_link')}}
                                </label>
                            </div>
                            <div class="text-muted fs-7 mt-1">{{ trans('sw.paymob_payment_description')}}</div>
                        </div>
                    </div>
                    @endif
                    <!--end::Paymob Payment Option-->

                    <!--begin::PayTabs Payment Option-->
                    @if(!empty($mainSettings->payments['paytabs']['profile_id'] ?? null) && !empty($mainSettings->payments['paytabs']['server_key'] ?? null))
                    <div class="row mb-5" id="edit_paytabs_payment_row" style="display: none;">
                        <div class="col-md-12">
                            <label class="form-label">{{ trans('sw.paytabs_payment')}}</label>
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="send_paytabs_link" id="edit_send_paytabs_link" value="1"/>
                                <label class="form-check-label" for="edit_send_paytabs_link">
                                    {{ trans('sw.send_paytabs_payment_link')}}
                                </label>
                            </div>
                            <div class="text-muted fs-7 mt-1">{{ trans('sw.paytabs_payment_description')}}</div>
                        </div>
                    </div>
                    @endif
                    <!--end::PayTabs Payment Option-->

                </div>
                <!--end::Modal body-->

                <!--begin::Modal footer-->
                <div class="modal-footer">
                    <input value="" id="edit_membership_id" type="hidden">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        {{ trans('sw.exist')}}
                    </button>
                    <button type="button" id="btn_edit_membership" class="btn btn-primary" onclick="submitMembershipData()">
                        <span class="indicator-label">{{ trans('sw.edit_membership')}}</span>
                    </button>
                </div>
                <!--end::Modal footer-->
            </div>
        </div>
    </div>
    <!-- End Modal Edit Membership -->
@endsection


@section('sub_scripts')
    <script src="{{asset('resources/assets/new_front/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"
            type="text/javascript"></script>
{{--            type="text/javascript"></script>--}}
{{--    <script src="{{asset('/')}}resources/assets/new_front/global/scripts/metronic.js" type="text/javascript"></script>--}}
{{--    <script src="{{asset('/')}}resources/assets/new_front/pages/scripts/components-pickers.js"></script>--}}
<script   src="{{asset('resources/assets/new_front/global/scripts/software/renew_member.js')}}"></script>

<script>

        jQuery(document).ready(function() {
            ComponentsPickers.init();
        });
        
        $('.btn-indigo').off('click').on('click', function (e) {
            var that = $(this);
            var attr_id = that.attr('id');
            $('#modalPayResult').hide();
            $('#amount_paid').val('');
            $('#pay_id').remove();
            $('#form_pay').append('<input value="' + attr_id + '"  id="pay_id" name="pay_id"  hidden>');
        });

        function edit_member_membership(id) {
            let attr_id = id;
            let date_attr = $('#edit_member_'+id);
            // let date_attr = $('#modelEditMembership');
            // $('#edit_member_'+id).modal('show');
            $('#modelEditMembership').modal('show');
            $('#edit_membership_id').val(attr_id);

            let expire_date_msg = date_attr.attr('expire_msg');
            let expire_date_color = date_attr.attr('expire_color');
            let prev_amount_paid = date_attr.attr('prev_amount_paid');
            let expire_message = '<span style="color: '+expire_date_color+'">'+expire_date_msg+'</span>';
            $('#membership_expire_date_msg').html(expire_message);

            $('#prev_amount_paid_input').val(prev_amount_paid);
            $('#prev_amount_paid').html(prev_amount_paid);

            getMemberships(attr_id);


            return false;
        }
        function getMemberships(id){
            var url = member_subscription_renew_url;
            var myurl = url.replace(':id', id);
            $.ajax({
                url: myurl,
                type: "get",
                success: (data) => {
                    // console.log(data);
                    var data = data;
                    var output = '';
                    var data_length = data.membership.length;
                    for (var i = 0; i < data_length; i++) {
                        var d = new Date();
                        var period = data.membership[i]['period'];
                        // if(data.member.member_subscription_info.subscription_id == data.membership[i]['id'])
                        //     period = 0;

                        var start_attr = d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate();
                        d.setDate(d.getDate() + parseInt(period));
                        var expire_attr = d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate();
                        var membership_selected = '';
                            if(data.membership[i]['id'] == data.member_membership['subscription_id']){
                            output += '<option start_date="' + data.member_membership['joining_date'] + '" expire_date="' + data.member_membership['expire_date'] + '" period="' + period + '" IsChangeable="' + data.membership[i]['is_expire_changeable'] + '"  title="' + data.membership[i]['price'] + '" price="' + data.membership[i]['price'] + '" workouts="' + data.membership[i]['workouts'] + '" freeze_limit="' + data.member_membership['freeze_limit'] + '" number_times_freeze="' + data.member_membership['number_times_freeze'] + '" max_extension_days="' + (data.member_membership['max_extension_days'] ?? 0) + '" max_freeze_extension_sum="' + (data.member_membership['max_freeze_extension_sum'] ?? 0) + '" invitations="' + (data.member_membership['invitations'] ?? 0) + '" discount_type="' + (data.membership[i]['default_discount_type'] || 0) + '" discount_value="' + (data.membership[i]['default_discount_value'] || 0) + '"  value="' + data.membership[i]['id'] + '"  selected="" >' + data.membership[i]['name'] + ' </option>';
                        }else{
                            output += '<option start_date="' + start_attr + '" expire_date="' + expire_attr + '"  period="' + period + '" IsChangeable="' + data.membership[i]['is_expire_changeable'] + '"  title="' + data.membership[i]['price'] + '" price="' + data.membership[i]['price'] + '"  workouts="' + data.membership[i]['workouts'] + '"  freeze_limit="' + data.membership[i]['freeze_limit'] + '" number_times_freeze="' + data.membership[i]['number_times_freeze'] + '" max_extension_days="' + (data.membership[i]['max_extension_days'] ?? 0) + '" max_freeze_extension_sum="' + (data.membership[i]['max_freeze_extension_sum'] ?? 0) + '" invitations="' + (data.membership[i]['invitations'] ?? 0) + '" discount_type="' + (data.membership[i]['default_discount_type'] || 0) + '" discount_value="' + (data.membership[i]['default_discount_value'] || 0) + '"  value="' + data.membership[i]['id'] + '"  >' + data.membership[i]['name'] + ' </option>';
                        }
                    }
                    $('#EditMembershipSelect').html(output).trigger('change.select2');

                    setMembershipDate(data.member_membership);
                },
                error: (reject) => {
                    var response = $.parseJSON(reject.responseText);
                    console.log(response);
                }
            });
            return false;
        }

        $(document).on('click', '#form_pay_btn', function (event) {
            event.preventDefault();
            id = $('#pay_id').val();
            amount_paid = $('#amount_paid').val();
            payment_type = $('#pay_payment_type').val();
            $('#modalPayResult').show();
            $.ajax({
                url: '{{route('sw.createMemberPayAmountRemainingForm')}}',
                cache: false,
                type: 'GET',
                dataType: 'text',
                data: {id: id, amount_paid: amount_paid, payment_type: payment_type},
                success: function (response) {
                    if (response == '1') {
                        $('#modalPayResult').html('<div class="alert alert-success">{{ trans('admin.successfully_paid')}}</div>');
                        location.reload();
                    } else {
                        $('#modalPayResult').html('<div class="alert alert-danger">' + response + '</div>');
                    }

                },
                error: function (request, error) {
                    swal("Operation failed", "Something went wrong.", "error");
                    console.error("Request: " + JSON.stringify(request));
                    console.error("Error: " + JSON.stringify(error));
                }
            });

        });
        $(document).on('click', '.confirm_process', function (event) {
            id = $(this).attr('member_id');
            route = "{{route('sw.freezeMember', ['id' => 'member_id'])}}";
            url = route.replace('member_id', id);
            swal({
                title: "{{ trans('admin.are_you_sure')}}",
                text: "",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "{{ trans('admin.yes')}}",
                cancelButtonText: "{{ trans('admin.no_cancel')}}",
                showLoaderOnConfirm: true,
//                ,closeOnConfirm: false,
//                closeOnCancel: false
                preConfirm: function (isConfirm) {
                    setTimeout(function () {
                        if (isConfirm) {
                            {{--swal("{{ trans('admin.completed')}}", "{{ trans('admin.completed_successfully')}}", "success");--}}
                                window.location.href = url;
                        }
//            });
                    }, 2000)
                },
                allowOutsideClick: false
            }).then(function (isConfirm) {

            });

//                    .then(function () {
//
        });

        $(document).on('click', '.confirm_delete', function (event) {
            var tr = $(this).parent().parent();
            event.preventDefault();
            url = $(this).attr('href');
            swal({
                title: "{{ trans('admin.are_you_sure')}}",
                text: $(this).attr('data-swal-text'),
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "{{ trans('admin.yes')}}",
                cancelButtonText: "{{ trans('admin.no_cancel')}}",
                showLoaderOnConfirm: true,
//                ,closeOnConfirm: false,
//                closeOnCancel: false
                preConfirm: function (isConfirm) {
                    return new Promise(function (resolve, reject) {
                        setTimeout(function () {
                            if (isConfirm) {
                                $.ajax({
                                    url: url,
                                    type: 'GET',
                                    success: function () {
                                        swal("{{ trans('admin.completed')}}", "{{ trans('admin.completed_successfully')}}", "success");

                                        tr.remove();
                                        location.reload();
                                    },
                                    error: function (request, error) {
                                        swal("{{ trans('operation_failed')}}", "{{ trans('admin.something_wrong')}}", "error");
                                        console.error("Request: " + JSON.stringify(request));
                                        console.error("Error: " + JSON.stringify(error));
                                    }
                                });
                            } else {
                                swal("{{ trans('admin.cancelled')}}", "{{ trans('admin.everything_still')}}", "info");
                            }
//            });
                        }, 2000)
                    })
                },
                allowOutsideClick: false
            }).then(function (isConfirm) {

            });

//                    .then(function () {
//
        });


    </script>
<script>
    var selectedMembershipPrice = 0;
    var selectedMembershipStartDate = '';
    var selectedMembershipExpireDate = '';
    var selectedMembershipWorkouts = 0;
    var selectedMembershipFreezeLimit = 0;
    var selectedMembershipNumberTimesFreeze = 0;
    var selectedMembershipMaxExtensionDays = 0;
    var selectedMembershipMaxFreezeExtensionSum = 0;
    var selectedMembershipInvitations = 0;

    $("#EditMembershipSelect").select2({
        dropdownParent: $('#modelEditMembership'),
        width: '100%'
    });

    function setMembershipDate(record) {
        selectedMembershipPrice = 0;
        $.each($("#EditMembershipSelect option:selected"), function () {
            selectedMembershipPrice = selectedMembershipPrice + parseFloat($(this).attr('price')).toFixed(2);
            selectedMembershipStartDate = $(this).attr('start_date');
            selectedMembershipExpireDate = $(this).attr('expire_date');
            selectedMembershipWorkouts = $(this).attr('workouts');
            selectedMembershipFreezeLimit = $(this).attr('freeze_limit');
            selectedMembershipNumberTimesFreeze = $(this).attr('number_times_freeze');
            selectedMembershipMaxExtensionDays = $(this).attr('max_extension_days');
            selectedMembershipMaxFreezeExtensionSum = $(this).attr('max_freeze_extension_sum');
            selectedMembershipInvitations = $(this).attr('invitations');
        });

        var totalPrice =  Number(record['amount_before_discount']) ;
        var totalPriceAfterDiscount =  Number(record['amount_before_discount']) -  Number(record['discount_value']) + Number(record['vat']);
        $('#create_amount_paid').val(record['amount_paid']).attr('max', parseFloat(totalPriceAfterDiscount).toFixed(2));
        $('#create_amount_remaining').val(parseFloat(record['amount_remaining']).toFixed(2));
        $('#myTotal').text("{{ trans('sw.price')}} = " + parseFloat(totalPrice).toFixed(2));
        $('#myTotalWithVat').text("{{ trans('sw.price')}} = " + parseFloat(totalPriceAfterDiscount).toFixed(2));
        $('#discount_value').attr('max', parseFloat(totalPriceAfterDiscount).toFixed(2)).attr('disabled', false).val(parseFloat(record['discount_value']).toFixed(2));
        if(record['discount_value']){
            $('#myTotal').css('text-decoration', 'line-through');
            $('#myTotalAfterDiscount').show().text("{{ trans('sw.after_discount')}} = " + parseFloat(totalPrice - record['discount_value']).toFixed(2));
        }
        var notes =  String(record['notes']) ;

        $('#start_date_membership').val(selectedMembershipStartDate);
        $('#expire_date_membership').val(selectedMembershipExpireDate);
        $('#EditMembershipWorkouts').val(selectedMembershipWorkouts);
        $('#EditMembershipFreezeLimit').val(selectedMembershipFreezeLimit);
        $('#EditMembershipNumberTimesFreeze').val(selectedMembershipNumberTimesFreeze);
        $('#EditMembershipMaxExtensionDays').val(selectedMembershipMaxExtensionDays);
        $('#EditMembershipMaxFreezeExtensionSum').val(selectedMembershipMaxFreezeExtensionSum);
        $('#EditMembershipInvitations').val(selectedMembershipInvitations);
        $('#EditMembershipNotes').val(notes);
        if(record['payment_type']) {
            $('#edit_payment_type').val(record['payment_type']);
        }

    }

    function submitMembershipData(){
        var id = $('#edit_membership_id').val()
        var data = {
         'member_subscription_id': id,
         'subscription_id': $('#EditMembershipSelect').val(),
         'joining_date': $('#start_date_membership').val(),
         'expire_date': $('#expire_date_membership').val(),
         'workouts': $('#EditMembershipWorkouts').val(),
         'number_times_freeze': $('#EditMembershipNumberTimesFreeze').val(),
         'freeze_limit': $('#EditMembershipFreezeLimit').val(),
         'max_extension_days': $('#EditMembershipMaxExtensionDays').val(),
         'max_freeze_extension_sum': $('#EditMembershipMaxFreezeExtensionSum').val(),
         'invitations': $('#EditMembershipInvitations').val(),
         'discount_value': $('#discount_value').val(),
         'group_discount_id': $('#group_discount_id').val(),
         'amount_paid': $('#create_amount_paid').val(),
         'notes': $('#EditMembershipNotes').val(),
         'payment_type': $('#edit_payment_type').val(),
         'send_tabby_link': $('#edit_send_tabby_link').is(':checked') ? 1 : 0,
         'send_tamara_link': $('#edit_send_tamara_link').is(':checked') ? 1 : 0,
         'send_paymob_link': $('#edit_send_paymob_link').is(':checked') ? 1 : 0,
         'send_paytabs_link': $('#edit_send_paytabs_link').is(':checked') ? 1 : 0,
         "_token": "{{ csrf_token() }}"
        }

        var url = member_subscription_edit_url;
        var myurl = url.replace(':id', id);
        $.ajax({
            url: myurl,
            data: data,
            type: "post",
            success: (data) => {
                console.log(data);
                if (data.status === true) {
                    // $("#global-loader").hide();
                    $('#modelEditMembership').modal('hide');
                    var lang = 'ar';
                    var isRtl = (lang === 'ar');

                    swal({
                        title: trans_done,
                        text: trans_successfully_processed,
                        type: "success",
                        timer: 4000,
                        confirmButtonText: 'Ok',
                    });
                } else {

                    $('#modelEditMembership').modal('show');
                    if (data.code === "amount_paid")
                        $('#amount_paid_error').text(data.msg);
                    else if (data.code === "expire_date")
                        $('#error_expire_date').text(data.msg);
                    else if (data.code === "start_date")
                        $('#error_start_date').text(data.msg);

                }
            },
            error: (reject) => {
                var response = $.parseJSON(reject.responseText);
                console.log(response);
            }
        });
        return false;

    }


    function editBarCodeInput(){
        $('#subscribedClientInputCode').prop('disabled', false); // If checked enable item
        $('#editBarcodeBtn').hide();
    }

    function togglePaymentTypeVisibility() {
        let diffAmount = parseFloat($('#diff_amount_paid').text()) || 0;
        if (diffAmount !== 0) {
            $('#payment_type_row').show();
        } else {
            $('#payment_type_row').hide();
        }
        // Show Tabby option only when diff amount paid > 0
        if (diffAmount > 0) {
            $('#edit_tabby_payment_row').show();
        } else {
            $('#edit_tabby_payment_row').hide();
            $('#edit_send_tabby_link').prop('checked', false);
        }
        // Show Tamara option only when diff amount paid > 0
        if (diffAmount > 0) {
            $('#edit_tamara_payment_row').show();
        } else {
            $('#edit_tamara_payment_row').hide();
            $('#edit_send_tamara_link').prop('checked', false);
        }
        // Show Paymob option only when diff amount paid > 0
        if (diffAmount > 0) {
            $('#edit_paymob_payment_row').show();
        } else {
            $('#edit_paymob_payment_row').hide();
            $('#edit_send_paymob_link').prop('checked', false);
        }
        // Show PayTabs option only when diff amount paid > 0
        if (diffAmount > 0) {
            $('#edit_paytabs_payment_row').show();
        } else {
            $('#edit_paytabs_payment_row').hide();
            $('#edit_send_paytabs_link').prop('checked', false);
        }
    }

    $("#create_amount_paid").change(function () {
        selectedMembershipPrice = 0;
        $.each($("#EditMembershipSelect option:selected"), function () {
            selectedMembershipPrice = selectedMembershipPrice + (parseFloat($(this).attr('price')).toFixed(2));
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
        selectedMembershipPriceWithVat = Number(selectedMembershipPriceWithVat).toFixed(2);

        $('#create_amount_remaining').val(Number(selectedMembershipPriceWithVat - valueAmountPaid ).toFixed(2));
        // $('#create_amount_paid').attr('max', Number(selectedMembershipPriceWithVat - valueAmountPaid).toFixed(2));

        let prev_amount_paid_input = $('#prev_amount_paid_input').val();
        $('#diff_amount_paid').html(Number(valueAmountPaid - prev_amount_paid_input).toFixed(2));
        togglePaymentTypeVisibility();
    });

    $('#EditMembershipSelect').change(function () {
        selectedMembershipPrice = 0;
        $.each($("#EditMembershipSelect option:selected"), function () {
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
        $('#myTotalWithVat').val(selectedMembershipPriceWithVat).attr('max', selectedMembershipPriceWithVat).text("{{ trans('sw.price')}} = " + selectedMembershipPriceWithVat).css('text-decoration', 'unset');
        $('#create_amount_paid').val(selectedMembershipPriceWithVat).attr('max', selectedMembershipPriceWithVat);
        $('#create_amount_remaining').val(0);
        $('#discount_value').attr('max', selectedMembershipPriceWithVat).attr('disabled', false).val(0);
        $('#myTotalAfterDiscount').hide();

        $('#expire_date_membership').val(selectedMembershipExpireDate);

        let prev_amount_paid_input = $('#prev_amount_paid_input').val();
        $('#diff_amount_paid').html(Number(selectedMembershipPriceWithVat - prev_amount_paid_input).toFixed(2));
        togglePaymentTypeVisibility();

        apply_discount_subscription_edit();
    });

    $('#start_date_membership').change(function () {
        let joining_date = $("#start_date_membership").val();
        let period = $('#EditMembershipSelect option:selected').attr('period');
        setCustomExpireDate(joining_date, period);
    });
    function setCustomExpireDate(joining_date, period){
        let valid_days = parseInt(period);
        let end_date = new Date(joining_date); // pass start date here
        end_date.setDate(end_date.getDate() + valid_days);
        $('#expire_date_membership').val(  end_date.getFullYear() + '-' + ((end_date.getMonth() + 1) < 10 ? '0' + (end_date.getMonth() + 1) : (end_date.getMonth() + 1)) + '-' + end_date.getDate() );
    }

    $('#discount_value').change(function () {
        discount_value();
    });
    function discount_value(discount_amount = null) {
        // $('#discount_value').change(function () {
        let price = (parseFloat($('#EditMembershipSelect option:selected').attr('price')));
        let vat = 0;
        let priceWithVat = 0;

        let discount_value = 0;
        if(discount_amount === null)
            discount_value = $('#discount_value').val();
        else
            discount_value = discount_amount

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
        $('#diff_amount_paid').html(Number(parseFloat(priceWithVat).toFixed(2) - prev_amount_paid_input).toFixed(2));
        togglePaymentTypeVisibility();
    // });
    }

    $('#group_discount_id').on('change', function (event){
        let discount_id = $(this).find(":selected").val();
        let type = parseInt($(this).find(":selected").attr('type'));
        let amount = $(this).find(":selected").attr('amount');
        let price = (parseFloat($('#EditMembershipSelect option:selected').attr('price')));
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

    function apply_discount_subscription_edit(){
        let type = parseInt($('#EditMembershipSelect option:selected').attr('discount_type'));
        let amount = parseFloat($('#EditMembershipSelect option:selected').attr('discount_value'));
        let price = parseFloat($('#EditMembershipSelect option:selected').attr('price'));
        let result = 0;
        $('#edit_discount_subscription_message').html('');
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
            $('#edit_discount_subscription_message').html('<div class="alert alert-danger">'+discount_message+'</div>');
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



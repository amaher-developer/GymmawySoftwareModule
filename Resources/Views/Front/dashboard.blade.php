@extends('software::layouts.list')
@section('breadcrumb')
    <!--begin::Breadcrumb-->
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
        <!--begin::Item-->
        <li class="breadcrumb-item text-gray-900">{{ $title }}</li>
        <!--end::Item-->
    </ul>
    <!--end::Breadcrumb-->
@endsection
@section('list_title') {{ @$title }} @endsection
@section('styles')
    <style>
        .normal_search {
            height: 60px;
        }

        .scan_barcode_manual {
            height: 60px;
            font-size: 28px;
            line-height: 60px;
        }

        .details {
            width: 60%;
        }

        .list-group-item {
            overflow: hidden;
        }
    </style>
@endsection
@section('page_body')
    @if(\Carbon\Carbon::parse($mainSettings->sw_end_date)->subDays(2)->toDateString() <= \Carbon\Carbon::now()->toDateString())
        <div class="alert alert-danger d-flex align-items-center mb-5">
            <i class="fa fa-warning fs-2 me-3"></i>
            <div>{!! trans('sw.subscription_expire_date_msg', ['date'=> \Carbon\Carbon::parse($mainSettings->sw_end_date)->toDateString(), 'url' => route('sw.listSwPayment')]) !!}</div>
        </div>
    @endif

    <!--begin::Dashboard-->
    <div class="row g-5">
        <!--begin::Quick Actions-->
        <div class="col-12">
            <div class="card card-flush">
                <!--begin::Card header-->
                <div class="card-header" style="display: none;">
                    <div class="card-title">
                        <h3 class="fw-bold">{{ trans('sw.quick_actions')}}</h3>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-8">
                    <!--begin::Actions Grid-->
                    <div class="row g-1">
                        @if($swUser && @$mainSettings->active_subscription && (in_array('listSubscription', (array)($swUser->permissions ?? [])) || $swUser->is_super_user))
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                                <a href="{{route('sw.listSubscription')}}" class="btn btn-light-primary btn-sm w-100 d-flex flex-column align-items-center justify-content-center p-2" style="height: 90px;">
                                    <i class="ki-outline ki-credit-cart fs-1 mb-2"></i>
                                    <span class="fw-bold fs-7 text-center">{{ trans('sw.memberships')}}</span>
                                </a>
                            </div>
                        @endif
                        @if($swUser && (@$mainSettings->active_activity || @$mainSettings->active_activity_reservation) && (in_array('listActivity', (array)($swUser->permissions ?? [])) || $swUser->is_super_user))
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                                <a href="{{route('sw.listActivity')}}" class="btn btn-light-info btn-sm w-100 d-flex flex-column align-items-center justify-content-center p-2" style="height: 90px;">
                                    <i class="ki-outline ki-list fs-1 mb-2"></i>
                                    <span class="fw-bold fs-7 text-center">{{ trans('sw.activities')}}</span>
                                </a>
                            </div>
                        @endif
                        @if($swUser && @$mainSettings->active_subscription && (in_array('listMember', (array)($swUser->permissions ?? [])) || $swUser->is_super_user))
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                                <a href="{{route('sw.listMember')}}" class="btn btn-light-danger btn-sm w-100 d-flex flex-column align-items-center justify-content-center p-2" style="height: 90px;">
                                    <i class="ki-outline ki-people fs-1 mb-2"></i>
                                    <span class="fw-bold fs-7 text-center">{{ trans('sw.subscribed_clients')}}</span>
                                </a>
                            </div>
                        @endif
                        @if($swUser && @$mainSettings->active_subscription && (in_array('createMember', (array)($swUser->permissions ?? [])) || $swUser->is_super_user))
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                                <a href="{{route('sw.createMember')}}" class="btn btn-light-success btn-sm w-100 d-flex flex-column align-items-center justify-content-center p-2" style="height: 90px;">
                                    <i class="ki-outline ki-user-plus fs-1 mb-2"></i>
                                    <span class="fw-bold fs-7 text-center">{{ trans('sw.member_add')}}</span>
                                </a>
                            </div>
                        @endif
                        @if($swUser && (@$mainSettings->active_activity || @$mainSettings->active_activity_reservation) && (in_array('listNonMember', (array)($swUser->permissions ?? [])) || $swUser->is_super_user))
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                                <a href="{{route('sw.listNonMember')}}" class="btn btn-light-warning btn-sm w-100 d-flex flex-column align-items-center justify-content-center p-2" style="height: 90px;">
                                    <i class="ki-outline ki-people fs-1 mb-2"></i>
                                    <span class="fw-bold fs-7 text-center">{{ trans('sw.daily_clients')}}</span>
                                </a>
                            </div>
                        @endif
                        @if($swUser && (@$mainSettings->active_activity || @$mainSettings->active_activity_reservation) && (in_array('createNonMember', (array)($swUser->permissions ?? [])) || $swUser->is_super_user))
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                                <a href="{{route('sw.createNonMember')}}" class="btn btn-light-secondary btn-sm w-100 d-flex flex-column align-items-center justify-content-center p-2" style="height: 90px;">
                                    <i class="ki-outline ki-user-plus fs-1 mb-2"></i>
                                    <span class="fw-bold fs-7 text-center">{{ trans('sw.non_member_add')}}</span>
                                </a>
                            </div>
                        @endif
                        @if($swUser && (in_array('listPotentialMember', (array)($swUser->permissions ?? [])) || $swUser->is_super_user))
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                                <a href="{{route('sw.listPotentialMember')}}" class="btn btn-light-primary btn-sm w-100 d-flex flex-column align-items-center justify-content-center p-2" style="height: 90px;">
                                    <i class="ki-outline ki-people fs-1 mb-2"></i>
                                    <span class="fw-bold fs-7 text-center">{{ trans('sw.potential_clients')}}</span>
                                </a>
                            </div>
                        @endif
                        @if($swUser && (in_array('createStoreOrderPOS', (array)($swUser->permissions ?? [])) || $swUser->is_super_user) && $mainSettings->active_store)
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                                <a href="{{route('sw.createStoreOrderPOS')}}" class="btn btn-light-dark btn-sm w-100 d-flex flex-column align-items-center justify-content-center p-2" style="height: 90px;">
                                    <i class="ki-outline ki-basket fs-1 mb-2"></i>
                                    <span class="fw-bold fs-7 text-center">{{ trans('sw.sell_products')}}</span>
                                </a>
                            </div>
                        @endif
                        @if($swUser && (in_array('listReports', (array)($swUser->permissions ?? [])) || $swUser->is_super_user))
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                                <a href="{{route('sw.listReports')}}" class="btn btn-light-info btn-sm w-100 d-flex flex-column align-items-center justify-content-center p-2" style="height: 90px;">
                                    <i class="ki-outline ki-chart-simple fs-1 mb-2"></i>
                                    <span class="fw-bold fs-7 text-center">{{ trans('sw.reports')}}</span>
                                </a>
                            </div>
                        @endif
                        @if($swUser && (in_array('listMoneyBox', (array)($swUser->permissions ?? [])) || $swUser->is_super_user))
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                                <a href="{{route('sw.listMoneyBox').'?from='.date('m/d/Y').'&'.'to='.date('m/d/Y')}}" class="btn btn-light-success btn-sm w-100 d-flex flex-column align-items-center justify-content-center p-2" style="height: 90px;">
                                    <i class="ki-outline ki-chart-simple fs-1 mb-2"></i>
                                    <span class="fw-bold fs-7 text-center">{{ trans('sw.money_report')}}</span>
                                </a>
                            </div>
                        @endif
                        @if($swUser && (in_array('listPTMember', (array)($swUser->permissions ?? [])) || $swUser->is_super_user) && $mainSettings->active_pt)
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                                <a href="{{route('sw.listPTMember')}}" class="btn btn-light-primary btn-sm w-100 d-flex flex-column align-items-center justify-content-center p-2" style="height: 90px;">
                                    <i class="ki-outline ki-shield-tick fs-1 mb-2"></i>
                                    <span class="fw-bold fs-7 text-center">{{ trans('sw.pt')}}</span>
                                </a>
                            </div>
                        @endif
                        @if($swUser && (in_array('listTrainingPlan', (array)($swUser->permissions ?? [])) || $swUser->is_super_user) && $mainSettings->active_training)
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                                <a href="{{route('sw.listTrainingMemberLog')}}" class="btn btn-light-info btn-sm w-100 d-flex flex-column align-items-center justify-content-center p-2" style="height: 90px;">
                                    <i class="ki-outline ki-calendar fs-1 mb-2"></i>
                                    <span class="fw-bold fs-7 text-center">{{ trans('sw.training')}}</span>
                                </a>
                            </div>
                        @endif
                        @if($swUser && (in_array('editSetting', (array)($swUser->permissions ?? [])) || $swUser->is_super_user))
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                                <a href="{{route('sw.editSetting')}}" class="btn btn-light-secondary btn-sm w-100 d-flex flex-column align-items-center justify-content-center p-2" style="height: 90px;">
                                    <i class="ki-outline ki-setting-2 fs-1 mb-2"></i>
                                    <span class="fw-bold fs-7 text-center">{{ trans('sw.settings')}}</span>
                                </a>
                            </div>
                        @endif
                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                            <a href="{{route('sw.customerLogin')}}" target="_blank" class="btn btn-light-warning btn-sm w-100 d-flex flex-column align-items-center justify-content-center p-2" style="height: 90px;">
                                <i class="ki-outline ki-global fs-1 mb-2"></i>
                                <span class="fw-bold fs-7 text-center">{{ trans('sw.member_login')}}</span>
                            </a>
                        </div>
                    </div>
                    <!--end::Actions Grid-->
                </div>
                <!--end::Card body-->
            </div>
        </div>
        <!--end::Quick Actions-->

        <!--begin::Member Check-in-->
        <div class="col-lg-6">
            <div class="card card-flush h-100">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="fw-bold">{{ trans('sw.member_login')}}</h3>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Input group-->
                    <div class="mb-10">
                        <label class="form-label">{{ trans('sw.check_in_by_id')}}</label>
                        <div class="input-group">
                            <input type="text" class="form-control scan_barcode_manual" placeholder="{{ trans('sw.check_in_by_id')}}" name="scan_barcode_manual" id="scan_barcode_manual">
                            <button class="btn btn-primary normal_search" id="Normal_search" onclick="scanBarcodeManual();" type="button">
                                <i class="ki-outline ki-barcode fs-1"></i>
                            </button>
                        </div>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Checkbox-->
                    <div class="form-check form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" value="1" id="scan_barcode_enquiry">
                        <label class="form-check-label" for="scan_barcode_enquiry">
                            {{ trans('sw.enquiry_only')}}
                        </label>
                    </div>
                    <!--end::Checkbox-->
                </div>
                <!--end::Card body-->
            </div>
        </div>
        <!--end::Member Check-in-->

        @if($swUser && (in_array('showMoneyBox', (array)($swUser->permissions ?? [])) || $swUser->is_super_user))
        <!--begin::Money Box-->
        <div class="col-lg-6">
            <div class="card card-flush h-100">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="fw-bold">{{ trans('sw.moneybox')}}</h3>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-50px me-5">
                            <div class="symbol-label bg-light-success">
                                <i class="ki-outline ki-dollar fs-2x text-success"></i>
                            </div>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.total_amount_in_fund')}}</span>
                            <span class="fs-2 fw-bold text-success">{{number_format($money_box_now ?? 0, 2)}}</span>
                        </div>
                    </div>
                </div>
                <!--end::Card body-->
            </div>
        </div>
        <!--end::Money Box-->
        @endif
    </div>
    <!--end::Dashboard-->

    <!--begin::Statistics-->
    <div class="row g-5 mt-5">
        <!--begin::Last Enter Member-->
        <div class="col-lg-4 col-md-6">
            <div class="card card-flush h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="symbol symbol-50px me-5">
                        <div class="symbol-label bg-light-primary">
                            <i class="ki-outline ki-user fs-2x text-primary"></i>
                        </div>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.last_enter_member')}}</span>
                        <span class="fs-4 fw-bold text-primary" id="barcode_last_enter_member">{{@$last_enter_member->member->name}}</span>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Last Enter Member-->

        <!--begin::Last Created Member-->
        <div class="col-lg-4 col-md-6">
            <div class="card card-flush h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="symbol symbol-50px me-5">
                        <div class="symbol-label bg-light-success">
                            <i class="ki-outline ki-user fs-2x text-success"></i>
                        </div>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.last_created_member')}}</span>
                        <span class="fs-4 fw-bold text-success">{{@$last_created_member->name}}</span>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Last Created Member-->

        <!--begin::Last Created Non-Member-->
        <div class="col-lg-4 col-md-6">
            <div class="card card-flush h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="symbol symbol-50px me-5">
                        <div class="symbol-label bg-light-warning">
                            <i class="ki-outline ki-user fs-2x text-warning"></i>
                        </div>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.last_created_non_member')}}</span>
                        <span class="fs-4 fw-bold text-warning">{{@$last_created_non_member->name}}</span>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Last Created Non-Member-->
    </div>
    <!--end::Statistics-->

    <!--begin::Data Tables-->
    <div class="row g-5 mt-5">
        <!--begin::Subscriptions & Activities-->
        <div class="col-lg-6">
            <div class="card card-flush h-100">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="fw-bold">{{ trans('sw.subscriptions_details')}}</h3>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Tabs-->
                    <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#subscriptions_tab">
                                <i class="ki-outline ki-credit-cart me-2"></i>{{ trans('sw.memberships')}}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#activities_tab">
                                <i class="ki-outline ki-list me-2"></i>{{ trans('sw.activities')}}
                            </a>
                        </li>
                    </ul>
                    <!--end::Tabs-->
                    <!--begin::Tab Content-->
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="subscriptions_tab">
                            <div class="table-responsive">
                                <table class="table table-striped table-row-bordered gy-5 gs-7" id="subscriptions_table">
                                    <thead>
                                        <tr class="fw-semibold fs-6 text-gray-800">
                                            <th>{{ trans('sw.title')}}</th>
                                            <th>{{ trans('sw.price')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($subscriptions as $subscription)
                                            <tr>
                                                <td>{{$subscription->name}}</td>
                                                <td>{{number_format($subscription->price ?? 0, 2)}}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="activities_tab">
                            <div class="table-responsive">
                                <table class="table table-striped table-row-bordered gy-5 gs-7" id="activities_table">
                                    <thead>
                                        <tr class="fw-semibold fs-6 text-gray-800">
                                            <th>{{ trans('sw.title')}}</th>
                                            <th>{{ trans('sw.price')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($activities as $activity)
                                            <tr>
                                                <td>{{$activity->name}}</td>
                                                <td>{{number_format($activity->price ?? 0, 2)}}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!--end::Tab Content-->
                </div>
                <!--end::Card body-->
            </div>
        </div>
        <!--end::Subscriptions & Activities-->

        <!--begin::Members Details-->
        <div class="col-lg-6">
            <div class="card card-flush h-100">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="fw-bold">{{ trans('sw.members_details')}}</h3>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Tabs-->
                    <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#attendance_tab">
                                <i class="ki-outline ki-clock me-2"></i>{{ trans('sw.last_attend_date')}}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#recent_tab">
                                <i class="ki-outline ki-user-plus me-2"></i>{{ trans('sw.recently')}}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#expired_tab">
                                <i class="ki-outline ki-people me-2"></i>{{ trans('sw.expired')}}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#birthday_tab">
                                <i class="ki-outline ki-cake me-2"></i>{{ trans('sw.birthdays')}}
                                <span class="badge badge-warning ms-2">{{@(int)count($birthday_members)}}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#expiring_tab">
                                <i class="ki-outline ki-people me-2"></i>{{ trans('sw.memberships_expiring')}}
                            </a>
                        </li>
                    </ul>
                    <!--end::Tabs-->
                    <!--begin::Tab Content-->
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="attendance_tab">
                            <div class="table-responsive">
                                <table class="table table-striped table-row-bordered gy-5 gs-7" id="attendance_table">
                                    <thead>
                                        <tr class="fw-semibold fs-6 text-gray-800">
                                            <th>{{ trans('sw.name')}}</th>
                                            <th>{{ trans('sw.entry_date')}}</th>
                                            <th>{{ trans('sw.amount_remaining')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($last_attendance_members as $member)
                                            @if(@$member->member)
                                                <tr>
                                                    <td>{{@$member->member->name}}</td>
                                                    <td>
                                                        <i class="ki-outline ki-calendar text-muted me-1"></i>{{ @$member->created_at->format('Y-m-d') }}
                                                        <br/>
                                                        <i class="ki-outline ki-clock text-muted me-1"></i>{{ @$member->created_at->format('h:i a') }}
                                                    </td>
                                                    <td @if(round($member->member->member_subscription_info->amount_remaining ?? 0, 2) > 0) class="text-danger fw-bold" @endif>
                                                        {{round($member->member->member_subscription_info->amount_remaining ?? 0, 2)}}
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="recent_tab">
                            <div class="table-responsive">
                                <table class="table table-striped table-row-bordered gy-5 gs-7" id="recent_table">
                                    <thead>
                                        <tr class="fw-semibold fs-6 text-gray-800">
                                            <th>{{ trans('sw.name')}}</th>
                                            <th>{{ trans('sw.joining_date')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($last_new_members as $member)
                                            @if(@$member->member)
                                                <tr>
                                                    <td>{{@$member->member->name}}</td>
                                                    <td>{{\Carbon\Carbon::parse($member->joining_date)->toDateString()}}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="expired_tab">
                            <div class="table-responsive">
                                <table class="table table-striped table-row-bordered gy-5 gs-7" id="expired_table">
                                    <thead>
                                        <tr class="fw-semibold fs-6 text-gray-800">
                                            <th>{{ trans('sw.name')}}</th>
                                            <th>{{ trans('sw.expire_date')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($last_expired_members as $member)
                                            @if($member->member)
                                                <tr>
                                                    <td>{{@$member->member->name}}</td>
                                                    <td>{{\Carbon\Carbon::parse($member->expire_date)->toDateString()}}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="birthday_tab">
                            <div class="table-responsive">
                                <table class="table table-striped table-row-bordered gy-5 gs-7" id="birthday_table">
                                    <thead>
                                        <tr class="fw-semibold fs-6 text-gray-800">
                                            <th>{{ trans('sw.name')}}</th>
                                            <th>{{ trans('sw.date_of_barth')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($birthday_members as $member)
                                            <tr>
                                                <td>{{@$member->name}}</td>
                                                <td>{{\Carbon\Carbon::parse($member->dob)->toDateString()}}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="expiring_tab">
                            <div class="table-responsive">
                                <table class="table table-striped table-row-bordered gy-5 gs-7" id="expiring_table">
                                    <thead>
                                        <tr class="fw-semibold fs-6 text-gray-800">
                                            <th>{{ trans('sw.name')}}</th>
                                            <th>{{ trans('sw.expire_date')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($last_expiring_members as $member)
                                            @if($member->member)
                                                <tr>
                                                    <td>{{@$member->member->name}}</td>
                                                    <td>{{\Carbon\Carbon::parse($member->expire_date)->toDateString()}}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!--end::Tab Content-->
                </div>
                <!--end::Card body-->
            </div>
        </div>
        <!--end::Members Details-->
    </div>
    <!--end::Data Tables-->
@endsection
@section('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js" defer></script>
    <script>
        // Optimize: Lazy initialize DataTables only when tabs are shown
        // This prevents initializing 7 tables at once, reducing scripting time from ~938ms to ~200ms
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') {
                return;
            }

            // Shared configuration for all tables
            var tableConfig = {
                scrollY: '200px',
                scrollCollapse: true,
                paging: false,
                bFilter: false,
                bInfo: false,
                @if(($lang ?? 'ar') == 'ar')
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json',
                }
                @endif
            };

            // Track which tables have been initialized
            var initializedTables = {};

            // Function to initialize a table if not already initialized
            function initTableIfNeeded(tableId) {
                if (initializedTables[tableId] || !$('#' + tableId).length) {
                    return;
                }
                
                try {
                    $('#' + tableId).DataTable(tableConfig);
                    initializedTables[tableId] = true;
                } catch (e) {
                    console.warn('Failed to initialize table: ' + tableId, e);
                }
            }

            // Initialize tables for visible tabs on page load
            $('.tab-pane.active').each(function() {
                var tableId = $(this).find('table').attr('id');
                if (tableId) {
                    initTableIfNeeded(tableId);
                }
            });

            // Lazy initialize tables when their tab is shown
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                // Get the target tab pane from the href attribute
                var target = $(this).attr('href');
                if (target && target.startsWith('#')) {
                    var $tabPane = $(target);
                    var tableId = $tabPane.find('table').attr('id');
                    if (tableId) {
                        initTableIfNeeded(tableId);
                        // Adjust columns after initialization
                        setTimeout(function() {
                            if ($.fn.dataTable && $.fn.dataTable.tables) {
                                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
                            }
                        }, 100);
                    }
                }
            });
        });
    </script>
@endsection


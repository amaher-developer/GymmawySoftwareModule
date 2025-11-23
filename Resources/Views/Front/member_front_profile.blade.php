@php
    $lang = app()->getLocale();
    $has_coming = false;
    if(@$member->member_subscription_info_has_active){
        (($member->member_subscription_info->status = \Modules\Software\Classes\TypeConstants::Coming) && (@$member->member_subscription_info->id != @$member->member_subscription_info_has_active->id)) ? $has_coming = true : $has_coming = false;
        $member->member_subscription_info = $member->member_subscription_info_has_active;
    }
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" direction="{{ $lang == 'ar' ? 'rtl' : 'ltr' }}" style="direction: {{ $lang == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <title>{{$member->name}}</title>
    <link rel="shortcut icon" href="{{asset('resources/assets/new_front/images/favicon.ico')}}" type="image/x-icon"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    @if($lang == 'ar')
        <link href="{{asset('resources/assets/new_front/plugins/global/plugins.bundle.rtl.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('resources/assets/new_front/css/style.bundle.rtl.css')}}" rel="stylesheet" type="text/css" />
    @else
        <link href="{{asset('resources/assets/new_front/plugins/global/plugins.bundle.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('resources/assets/new_front/css/style.bundle.css')}}" rel="stylesheet" type="text/css" />
    @endif
    <style>
        body {
            background-color: #f1f3fa;
            font-size: 16px !important;
        }
        .card-title h2, .card-title h3 {
            font-size: 1.5rem !important;
        }
        .card-body, .card-footer {
            font-size: 1rem !important;
        }
        .fs-6 {
            font-size: 1rem !important;
        }
        .fs-7 {
            font-size: 0.95rem !important;
        }
        .text-muted {
            font-size: 1rem !important;
        }
        .fw-bold, .fw-semibold {
            font-size: 1rem !important;
        }
        .table {
            font-size: 1rem !important;
        }
        .table th {
            font-size: 0.95rem !important;
        }
        .table td {
            font-size: 1rem !important;
        }
        .badge {
            font-size: 0.95rem !important;
            padding: 0.5rem 0.75rem !important;
        }
        .alert {
            font-size: 1rem !important;
        }
        .alert h4 {
            font-size: 1.25rem !important;
        }
        .nav-link {
            font-size: 1.1rem !important;
        }
        h3.fw-bold {
            font-size: 1.4rem !important;
        }
        .d-flex.flex-column span {
            font-size: 1rem !important;
        }
        .fw-bold.fs-6 {
            font-size: 1.1rem !important;
        }
        .text-muted.fw-semibold.fs-7 {
            font-size: 1rem !important;
        }
        .table th {
            font-size: 1rem !important;
            padding: 1rem 0.75rem !important;
        }
        .table td {
            padding: 1rem 0.75rem !important;
        }
        .fs-4 {
            font-size: 1.5rem !important;
        }
        .fs-5 {
            font-size: 1.25rem !important;
        }
        .card-title {
            font-size: 1.5rem !important;
        }
        .btn {
            font-size: 1rem !important;
            padding: 0.625rem 1rem !important;
        }
        .alert {
            padding: 1.25rem !important;
        }
        .separator {
            margin: 2rem 0 !important;
        }
        .py-3 {
            padding-top: 1rem !important;
            padding-bottom: 1rem !important;
        }
        .py-4 {
            padding-top: 1.25rem !important;
            padding-bottom: 1.25rem !important;
        }
        .mb-3 {
            margin-bottom: 1.25rem !important;
        }
        .mb-5 {
            margin-bottom: 2rem !important;
        }
    </style>
</head>
<body class="p-10">

<!--begin::Layout-->
<div class="d-flex flex-column flex-lg-row">
    <!--begin::Sidebar-->
    <div class="flex-column flex-lg-row-auto w-lg-300px w-xl-400px mb-10 mb-lg-0">
        <!--begin::Card-->
        <div class="card card-flush">
            <!--begin::Card header-->
            <div class="card-header">
                <div class="card-title">
                    <h2 class="mb-0">{{ trans('sw.member_details') }}</h2>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body text-center pt-0">
                <!--begin::Avatar-->
                <div class="symbol symbol-100px symbol-lg-160px symbol-fixed-auto mb-5">
                    <img src="{{$member->image}}" alt="image" class="rounded-circle"/>
                </div>
                <!--end::Avatar-->
                <!--begin::Name-->
                <h3 class="fs-2 text-gray-800 fw-bold mb-1">{{$member->name}}</h3>
                <!--end::Name-->
                <!--begin::Code-->
                <div class="fs-6 text-muted fw-semibold mb-5">{{$member->code}}</div>
                <!--end::Code-->
                <!--begin::Status-->
                <div class="d-flex flex-wrap flex-center mb-5">
                    <div class="badge @if(@$member->member_subscription_info->status == 0) badge-light-success @elseif(@$member->member_subscription_info->status == 1) badge-light-info @elseif(@$member->member_subscription_info->status == 2) badge-light-danger @endif fw-bold fs-5 py-2 px-4">
                        {{@$member->member_subscription_info->statusName}}
                    </div>
                    @if($has_coming)
                        <div class="badge badge-light-warning fw-bold fs-5 py-2 px-4 ms-2">{{ trans('sw.coming')}}</div>
                    @endif
                </div>
                <!--end::Status-->
            </div>
            <!--end::Card body-->
            <!--begin::Card footer-->
            <div class="card-footer d-flex flex-column pt-0">
                <div class="d-flex flex-stack py-4">
                    <div class="fw-bold fs-5">{{ trans('sw.phone') }}</div>
                    <div class="fw-semibold text-muted fs-5">{{ $member->phone }}</div>
                </div>
                <div class="d-flex flex-stack py-4 border-top">
                    <div class="fw-bold fs-5">{{ trans('sw.address') }}</div>
                    <div class="fw-semibold text-muted fs-5">{{ $member->address }}</div>
                </div>
                 <div class="d-flex flex-stack py-4 border-top">
                    <div class="fw-bold fs-5">{{ trans('sw.store_credit') }}</div>
                    <div class="fw-semibold @if(@$member->store_balance > 0) text-success @else text-danger @endif fs-5">{{ @number_format(@$member->store_balance, 2)  }}</div>
                </div>
                @if(@$mainSettings->active_loyalty)
                <div class="d-flex flex-stack py-4 border-top bg-light-primary" style="border-radius: 0.475rem; margin: 0 -1.5rem; padding: 1rem 1.5rem !important;">
                    <div class="d-flex align-items-center">
                        <i class="ki-outline ki-gift fs-2 text-primary me-3"></i>
                        <div class="fw-bold fs-5">{{ trans('sw.loyalty_points') }}</div>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="fw-bold text-primary fs-3">{{ number_format($member->loyalty_points_balance ?? 0) }}</span>
                        <a target="_blank" href="{{ route('sw.loyalty_transactions.member_history', $member->id) }}" class="btn btn-sm btn-light-primary ms-3" title="{{ trans('sw.view_full_history') }}">
                            <i class="ki-outline ki-eye fs-6"></i>
                        </a>
                    </div>
                </div>
                @endif
            </div>
            <!--end::Card footer-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Sidebar-->

    <!--begin::Content-->
    <div class="flex-lg-row-fluid ms-lg-10">
        <!--begin::Subscription details card-->
        <div class="card card-flush mb-5">
            <div class="card-header">
                <h3 class="card-title">{{ trans('sw.subscription_details') }}</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex flex-stack py-3">
                            <span class="text-muted fw-semibold fs-5">{{ trans('sw.membership') }}:</span>
                            <span class="fw-bold fs-5">{{ @$member->member_subscription_info->subscription->name }}</span>
                        </div>
                        <div class="d-flex flex-stack py-3">
                            <span class="text-muted fw-semibold fs-5">{{ trans('sw.joining_date') }}:</span>
                            <span class="fw-bold fs-5">{{ @\Carbon\Carbon::parse($member->member_subscription_info->joining_date)->toDateString()  }}</span>
                        </div>
                        <div class="d-flex flex-stack py-3">
                            <span class="text-muted fw-semibold fs-5">{{ trans('sw.expire_date') }}:</span>
                            <span class="fw-bold fs-5">{{ @\Carbon\Carbon::parse($member->member_subscription_info->expire_date)->toDateString()  }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                         <div class="d-flex flex-stack py-3">
                            <span class="text-muted fw-semibold fs-5">{{ trans('sw.amount_paid') }}:</span>
                            <span class="fw-bold fs-5">{{ @$member->member_subscription_info->amount_paid }}</span>
                        </div>
                        <div class="d-flex flex-stack py-3">
                            <span class="text-muted fw-semibold fs-5">{{ trans('sw.amount_remaining') }}:</span>
                            <span class="fw-bold text-danger fs-5">{{ @number_format($member->member_subscription_info->amount_remaining, 2)  }}</span>
                        </div>
                        <div class="d-flex flex-stack py-3">
                            <span class="text-muted fw-semibold fs-5">{{ trans('sw.total_amount_remaining') }}:</span>
                            <span class="fw-bold text-danger fs-5">{{ @number_format($member->member_remain_amount_subscriptions->sum('amount_remaining'), 2)  }}</span>
                        </div>
                    </div>
                </div>
                <div class="separator separator-dashed my-5"></div>
                <h3 class="fw-bold text-gray-800 mb-3">{{ trans('sw.freeze_info') }}</h3>
                <div class="row">
                    <div class="col-md-4">
                        <div class="d-flex flex-column py-3">
                            <span class="text-muted fw-semibold fs-5 mb-2">{{ trans('sw.number_times_freeze') }}</span>
                            <span class="fw-bold fs-4">{{ @(int)$member->member_subscription_info->subscription->number_times_freeze }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex flex-column py-3">
                            <span class="text-muted fw-semibold fs-5 mb-2">{{ trans('sw.freeze_limit') }}</span>
                            <span class="fw-bold fs-4">{{ @$member->member_subscription_info->freeze_limit  }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex flex-column py-3">
                            <span class="text-muted fw-semibold fs-5 mb-2">{{ trans('sw.number_times_freeze_reminder') }}</span>
                            <span class="fw-bold fs-4">{{ @$member->member_subscription_info->number_times_freeze  }}</span>
                        </div>
                    </div>
                </div>
                @if(@$member->member_subscription_info->status == \Modules\Software\Classes\TypeConstants::Freeze)
                    @php
                        $active_freeze = @$member->member_subscription_info->freezes()
                            ->whereIn('status', ['active', 'approved'])
                            ->orderBy('id', 'desc')
                            ->first();
                    @endphp
                    @if($active_freeze)
                    <div class="alert alert-info d-flex align-items-center p-5 mb-5">
                        <i class="ki-outline ki-information-5 fs-2x text-primary me-4"></i>
                        <div class="d-flex flex-column">
                            <h4 class="mb-1">{{ trans('sw.current_freeze') }}</h4>
                            <span>{{ trans('sw.freeze_active_period') }}</span>
                        </div>
                    </div>
                    <div class="row mb-5">
                        <div class="col-md-4">
                            <div class="d-flex flex-column py-3">
                                <span class="text-muted fw-semibold fs-5 mb-2">{{ trans('sw.start_freeze_date') }}</span>
                                <span class="fw-bold fs-4">{{ @\Carbon\Carbon::parse($active_freeze->start_date)->toDateString() }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex flex-column py-3">
                                <span class="text-muted fw-semibold fs-5 mb-2">{{ trans('sw.end_freeze_date') }}</span>
                                <span class="fw-bold fs-4">{{ @\Carbon\Carbon::parse($active_freeze->end_date)->toDateString() }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex flex-column py-3">
                                <span class="text-muted fw-semibold fs-5 mb-2">{{ trans('sw.freeze_reminder_days') }}</span>
                                @php
                                    $end_date = \Carbon\Carbon::parse($active_freeze->end_date)->startOfDay();
                                    $now = \Illuminate\Support\Carbon::now()->startOfDay();
                                    $days_remaining = $end_date->isPast() ? 0 : max(0, (int) $now->diffInDays($end_date, false));
                                @endphp
                                <span class="fw-bold fs-4">{{ $days_remaining }}</span>
                            </div>
                        </div>
                    </div>
                    @if($active_freeze->reason)
                    <div class="mb-5">
                        <div class="d-flex flex-column py-3">
                            <span class="text-muted fw-semibold fs-5 mb-2">{{ trans('sw.reason') }}</span>
                            <span class="fw-bold fs-5">{{ $active_freeze->reason }}</span>
                        </div>
                    </div>
                    @endif
                    @if($active_freeze->admin_note)
                    <div class="mb-5">
                        <div class="d-flex flex-column py-3">
                            <span class="text-muted fw-semibold fs-5 mb-2">{{ trans('sw.admin_notes') }}</span>
                            <span class="fw-semibold text-gray-600 fs-5">{{ $active_freeze->admin_note }}</span>
                        </div>
                    </div>
                    @endif
                    @endif
                @endif
                
                @php
                    $freeze_history = @$member->member_subscription_info->freezes()
                        ->orderBy('created_at', 'desc')
                        ->get();
                @endphp
                @if($freeze_history && $freeze_history->count() > 0)
                <div class="separator separator-dashed my-5"></div>
                <h3 class="fw-bold text-gray-800 mb-5">{{ trans('sw.freeze_history') ?? 'Freeze History' }}</h3>
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed gy-5">
                        <thead>
                            <tr class="text-start text-gray-400 fw-bold text-uppercase gs-0">
                                <th>{{ trans('sw.start_freeze_date') }}</th>
                                <th>{{ trans('sw.end_freeze_date') }}</th>
                                <th>{{ trans('sw.status') }}</th>
                                <th>{{ trans('sw.duration') ?? 'Duration' }}</th>
                                @if($freeze_history->where('reason', '!=', null)->count() > 0 || $freeze_history->where('admin_note', '!=', null)->count() > 0)
                                <th>{{ trans('sw.details') ?? 'Details' }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                            @foreach($freeze_history as $freeze)
                            <tr>
                                <td class="fs-5">{{ \Carbon\Carbon::parse($freeze->start_date)->toDateString() }}</td>
                                <td class="fs-5">{{ \Carbon\Carbon::parse($freeze->end_date)->toDateString() }}</td>
                                <td>
                                    @if($freeze->status == 'active')
                                        <span class="badge badge-light-success fs-5">{{ trans('sw.active') }}</span>
                                    @elseif($freeze->status == 'approved')
                                        <span class="badge badge-light-info fs-5">{{ trans('sw.approved') ?? 'Approved' }}</span>
                                    @elseif($freeze->status == 'completed')
                                        <span class="badge badge-light-primary fs-5">{{ trans('sw.completed') ?? 'Completed' }}</span>
                                    @elseif($freeze->status == 'pending')
                                        <span class="badge badge-light-warning fs-5">{{ trans('sw.pending') ?? 'Pending' }}</span>
                                    @elseif($freeze->status == 'rejected')
                                        <span class="badge badge-light-danger fs-5">{{ trans('sw.rejected') ?? 'Rejected' }}</span>
                                    @endif
                                </td>
                                <td class="fs-5">
                                    {{ (int) \Carbon\Carbon::parse($freeze->start_date)->diffInDays(\Carbon\Carbon::parse($freeze->end_date)) }} {{ trans('sw.days') }}
                                </td>
                                @if($freeze_history->where('reason', '!=', null)->count() > 0 || $freeze_history->where('admin_note', '!=', null)->count() > 0)
                                <td class="fs-5">
                                    @if($freeze->reason)
                                        <div class="mb-2">
                                            <span class="text-muted fs-5">{{ trans('sw.reason') }}:</span>
                                            <span class="fw-semibold">{{ Str::limit($freeze->reason, 30) }}</span>
                                        </div>
                                    @endif
                                    @if($freeze->admin_note)
                                        <div>
                                            <span class="text-muted fs-5">{{ trans('sw.admin_notes') }}:</span>
                                            <span class="fw-semibold text-gray-600">{{ Str::limit($freeze->admin_note, 30) }}</span>
                                        </div>
                                    @endif
                                </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
        <!--end::Subscription details card-->

        <!--begin::Tabs-->
        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">
            <li class="nav-item">
                <a class="nav-link text-active-primary ms-0 me-10 py-5 active" data-bs-toggle="tab" href="#kt_member_subscriptions_tab">{{ trans('sw.subscriptions') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-active-primary ms-0 me-10 py-5" data-bs-toggle="tab" href="#kt_member_attendance_tab">{{ trans('sw.attendance') }}</a>
            </li>
             @if($member_credit_transactions->count() > 0)
            <li class="nav-item">
                <a class="nav-link text-active-primary ms-0 me-10 py-5" data-bs-toggle="tab" href="#kt_member_credit_tab">{{ trans('sw.balance_transactions') }}</a>
            </li>
            @endif
        </ul>
        <!--end::Tabs-->

        <!--begin::Tab content-->
        <div class="tab-content">
            <!--begin::Subscriptions tab-->
            <div class="tab-pane fade show active" id="kt_member_subscriptions_tab" role="tabpanel">
                <div class="card card-flush">
                    <div class="card-header">
                        <h3 class="card-title">{{ trans('sw.subscriptions_history') }}</h3>
                        <div class="card-toolbar">
                             <a href="{{route('sw.reportSubscriptionMemberList').'?search='.$member->code}}"  target="_blank" class="btn btn-sm btn-light-primary">{{ trans('global.view_all')}}</a>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                         <div class="table-responsive">
                            <table class="table align-middle table-row-dashed gy-5">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold text-uppercase gs-0">
                                        <th>{{ trans('sw.membership')}}</th>
                                        <th>{{ trans('sw.status')}}</th>
                                        <th>{{ trans('sw.joining_date')}}</th>
                                        <th>{{ trans('sw.expire_date')}}</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    @foreach($member->member_subscriptions as $member_subscription)
                                    <tr>
                                        <td class="fs-5">{{@$member_subscription->subscription->name}}</td>
                                        <td>
                                            @if(@$member_subscription->status == 0) <span class="badge badge-light-success fs-5">{{ trans('sw.active')}}</span>
                                            @elseif(@$member_subscription->status == 1) <span class="badge badge-light-info fs-5">{{ trans('sw.frozen')}}</span>
                                            @elseif(@$member_subscription->status == 2) <span class="badge badge-light-danger fs-5">{{ trans('sw.expire')}}</span>
                                            @elseif(@$member_subscription->status == 3) <span class="badge badge-light-warning fs-5">{{ trans('sw.coming')}}</span>
                                            @endif
                                        </td>
                                        <td class="fs-5">{{\Carbon\Carbon::parse(@$member_subscription->joining_date)->toDateString()}}</td>
                                        <td class="fs-5">{{\Carbon\Carbon::parse(@$member_subscription->expire_date)->toDateString()}}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Subscriptions tab-->
            
            <!--begin::Attendance tab-->
            <div class="tab-pane fade" id="kt_member_attendance_tab" role="tabpanel">
                 <div class="card card-flush">
                    <div class="card-header">
                        <h3 class="card-title">{{ trans('sw.last_attend_date') }}</h3>
                         <div class="card-toolbar">
                             <a href="{{route('sw.reportTodayMemberList').'?search='.@$member->code}}" target="_blank" class="btn btn-sm btn-light-primary">{{ trans('global.view_all')}}</a>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        @if($member->member_attendees->count() > 0 )
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed gy-5">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold text-uppercase gs-0">
                                        <th>{{ trans('sw.date') }}</th>
                                        <th>{{ trans('sw.time') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    @foreach($member->member_attendees as $member_attend)
                                    <tr>
                                        <td class="fs-5">{{ @$member_attend->created_at->format('Y-m-d') }}</td>
                                        <td class="fs-5">{{ @$member_attend->created_at->format('h:i a') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                            <div class="text-center p-10">
                                <h4 class="text-muted">{{ trans('sw.no_record_found') }}</h4>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <!--end::Attendance tab-->
            
            <!--begin::Credit tab-->
            @if($member_credit_transactions->count() > 0 )
            <div class="tab-pane fade" id="kt_member_credit_tab" role="tabpanel">
                <div class="card card-flush">
                     <div class="card-header">
                        <h3 class="card-title">{{ trans('sw.balance_transactions') }}</h3>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed gy-5">
                                <thead>
                                     <tr class="text-start text-gray-400 fw-bold text-uppercase gs-0">
                                        <th>{{ trans('sw.operation') }}</th>
                                        <th>{{ trans('sw.amount') }}</th>
                                        <th>{{ trans('sw.date') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    @foreach($member_credit_transactions as $member_credit_transaction)
                                    <tr>
                                        <td>
                                            @if($member_credit_transaction->operation == 2) <span class="badge badge-light-primary fs-5">{{ trans('sw.use_balance')}}</span>
                                            @elseif($member_credit_transaction->operation == 1) <span class="badge badge-light-danger fs-5">{{ trans('sw.store_refund')}}</span>
                                            @else <span class="badge badge-light-success fs-5">{{ trans('sw.add_credit')}}</span>
                                            @endif
                                        </td>
                                        <td class="fs-5">{{number_format($member_credit_transaction->amount, 2)}}</td>
                                        <td class="fs-5">{{ @$member_credit_transaction->created_at->format('Y-m-d') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <!--end::Credit tab-->

        </div>
        <!--end::Tab content-->
    </div>
    <!--end::Content-->
</div>
<!--end::Layout-->

<script src="{{asset('resources/assets/new_front/plugins/global/plugins.bundle.js')}}"></script>
<script src="{{asset('resources/assets/new_front/js/scripts.bundle.js')}}"></script>

</body>
</html>



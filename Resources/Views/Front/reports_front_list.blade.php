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
        .report-card {
            transition: all 0.3s ease;
            border: 1px solid #e4e6ef;
            height: 100%;
            cursor: pointer;
        }
        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-color: #009ef7;
        }
        .report-card-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 15px;
        }
        .report-card-title {
            font-size: 15px;
            font-weight: 600;
            color: #181c32;
            margin: 0;
        }
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #181c32;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f1f2;
        }
    </style>
@endsection
@section('page_body')
    <div class="container-fluid">
        <!--begin::Financial Reports Section-->
        @if((in_array('listMoneyBox', (array)$swUser->permissions) || $swUser->is_super_user) || 
            (in_array('reportMoneyboxTax', (array)$swUser->permissions) || $swUser->is_super_user) ||
            (config('sw_billing.zatca_enabled') && (in_array('reportZatcaInvoices', (array)$swUser->permissions) || $swUser->is_super_user)) ||
            (in_array('reportOnlinePaymentTransactionList', (array)$swUser->permissions) || $swUser->is_super_user) ||
            (in_array('listMoneyBoxDaily', (array)$swUser->permissions) || $swUser->is_super_user))
        <div class="mb-10">
            <h3 class="section-title">
                <i class="ki-outline ki-chart-simple fs-2 me-2 text-primary"></i>
                {{ trans('sw.financial_reports') ?? 'Financial Reports' }}
            </h3>
            <div class="row g-5">
                @if(in_array('listMoneyBox', (array)$swUser->permissions) || $swUser->is_super_user)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <a href="{{route('sw.listMoneyBox').'?from='.date('m/d/Y').'&'.'to='.date('m/d/Y')}}" class="text-decoration-none">
                        <div class="card report-card">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-8">
                                <div class="report-card-icon bg-light-success">
                                    <i class="ki-outline ki-chart-simple text-success"></i>
                                </div>
                                <h5 class="report-card-title text-center">{{ trans('sw.money_report')}}</h5>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
                @if(in_array('reportMoneyboxTax', (array)$swUser->permissions) || $swUser->is_super_user)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <a href="{{route('sw.reportMoneyboxTax').'?from='.date('m/d/Y').'&'.'to='.date('m/d/Y')}}" class="text-decoration-none">
                        <div class="card report-card">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-8">
                                <div class="report-card-icon bg-light-info">
                                    <i class="ki-outline ki-chart fs-2 text-info"></i>
                                </div>
                                <h5 class="report-card-title text-center">{{ trans('sw.moneybox_tax')}}</h5>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
                @if(config('sw_billing.zatca_enabled') && (in_array('reportZatcaInvoices', (array)$swUser->permissions) || $swUser->is_super_user))
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <a href="{{ route('sw.reportZatcaInvoices') }}" class="text-decoration-none">
                        <div class="card report-card">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-8">
                                <div class="report-card-icon bg-light-primary">
                                    <i class="ki-outline ki-file-down text-primary"></i>
                                </div>
                                <h5 class="report-card-title text-center">{{ trans('sw.zatca_invoices_report') }}</h5>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
                @if(in_array('reportOnlinePaymentTransactionList', (array)$swUser->permissions) || $swUser->is_super_user)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <a href="{{ route('sw.reportOnlinePaymentTransactionList') }}" class="text-decoration-none">
                        <div class="card report-card">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-8">
                                <div class="report-card-icon bg-light-success">
                                    <i class="ki-outline ki-credit-cart fs-2 text-success"></i>
                                </div>
                                <h5 class="report-card-title text-center">{{ trans('sw.online_transaction_report') }}</h5>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
                @if(in_array('listMoneyBoxDaily', (array)$swUser->permissions) || $swUser->is_super_user)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <a href="{{ route('sw.listMoneyBoxDaily') }}" class="text-decoration-none">
                        <div class="card report-card">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-8">
                                <div class="report-card-icon bg-light-warning">
                                    <i class="ki-outline ki-calendar fs-2 text-warning"></i>
                                </div>
                                <h5 class="report-card-title text-center">{{ trans('sw.money_daily_report') }}</h5>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif
        <!--end::Financial Reports Section-->

        <!--begin::Membership Reports Section-->
        @if((in_array('reportDetailMemberList', (array)$swUser->permissions) || $swUser->is_super_user) ||
            (in_array('reportRenewMemberList', (array)$swUser->permissions) || $swUser->is_super_user) ||
            (in_array('reportExpireMemberList', (array)$swUser->permissions) || $swUser->is_super_user) ||
            (in_array('reportSubscriptionMemberList', (array)$swUser->permissions) || $swUser->is_super_user) ||
            (in_array('reportFreezeMemberList', (array)$swUser->permissions) || $swUser->is_super_user) ||
            (in_array('reportPTSubscriptionMemberList', (array)$swUser->permissions) || $swUser->is_super_user))
        <div class="mb-10">
            <h3 class="section-title">
                <i class="ki-outline ki-profile-user fs-2 me-2 text-primary"></i>
                {{ trans('sw.membership_reports') ?? 'Membership Reports' }}
            </h3>
            <div class="row g-5">
                @if(in_array('reportDetailMemberList', (array)$swUser->permissions) || $swUser->is_super_user)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <a href="{{route('sw.reportDetailMemberList')}}" class="text-decoration-none">
                        <div class="card report-card">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-8">
                                <div class="report-card-icon bg-light-danger">
                                    <i class="ki-outline ki-people fs-2 text-danger"></i>
                                </div>
                                <h5 class="report-card-title text-center">{{ trans('sw.memberships_detail_report')}}</h5>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
                @if(in_array('reportRenewMemberList', (array)$swUser->permissions) || $swUser->is_super_user)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <a href="{{route('sw.reportRenewMemberList')}}" class="text-decoration-none">
                        <div class="card report-card">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-8">
                                <div class="report-card-icon bg-light-primary">
                                    <i class="ki-outline ki-arrows-circle fs-2 text-primary"></i>
                                </div>
                                <h5 class="report-card-title text-center">{{ trans('sw.memberships_renewal_report')}}</h5>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
                @if(in_array('reportExpireMemberList', (array)$swUser->permissions) || $swUser->is_super_user)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <a href="{{route('sw.reportExpireMemberList')}}" class="text-decoration-none">
                        <div class="card report-card">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-8">
                                <div class="report-card-icon bg-light-warning">
                                    <i class="ki-outline ki-time fs-2 text-warning"></i>
                                </div>
                                <h5 class="report-card-title text-center">{{ trans('sw.memberships_expire_report')}}</h5>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
                @if(in_array('reportSubscriptionMemberList', (array)$swUser->permissions) || $swUser->is_super_user)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <a href="{{route('sw.reportSubscriptionMemberList')}}" class="text-decoration-none">
                        <div class="card report-card">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-8">
                                <div class="report-card-icon bg-light-danger">
                                    <i class="ki-outline ki-credit-cart fs-2 text-danger"></i>
                                </div>
                                <h5 class="report-card-title text-center">{{ trans('sw.report_subscriptions')}}</h5>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
                @if(in_array('reportFreezeMemberList', (array)$swUser->permissions) || $swUser->is_super_user)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <a href="{{route('sw.reportFreezeMemberList')}}" class="text-decoration-none">
                        <div class="card report-card">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-8">
                                <div class="report-card-icon bg-light-info">
                                    <i class="ki-outline ki-pause-circle fs-2 text-info"></i>
                                </div>
                                <h5 class="report-card-title text-center">{{ trans('sw.freeze_members_report')}}</h5>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
                @if(in_array('reportPTSubscriptionMemberList', (array)$swUser->permissions) || $swUser->is_super_user)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <a href="{{route('sw.reportPTSubscriptionMemberList')}}" class="text-decoration-none">
                        <div class="card report-card">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-8">
                                <div class="report-card-icon bg-light-primary">
                                    <i class="ki-outline ki-security-user fs-2 text-primary"></i>
                                </div>
                                <h5 class="report-card-title text-center">{{ trans('sw.report_pt_subscriptions')}}</h5>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif
        <!--end::Membership Reports Section-->

        <!--begin::Attendance Reports Section-->
        @if((in_array('reportTodayMemberList', (array)$swUser->permissions) || $swUser->is_super_user) ||
            (in_array('reportTodayPTMemberList', (array)$swUser->permissions) || $swUser->is_super_user) ||
            (in_array('reportTodayNonMemberList', (array)$swUser->permissions) || $swUser->is_super_user) ||
            (in_array('reportUserAttendeesList', (array)$swUser->permissions) || $swUser->is_super_user))
        <div class="mb-10">
            <h3 class="section-title">
                <i class="ki-outline ki-calendar-tick fs-2 me-2 text-primary"></i>
                {{ trans('sw.attendance_reports') ?? 'Attendance Reports' }}
            </h3>
            <div class="row g-5">
                @if(in_array('reportTodayMemberList', (array)$swUser->permissions) || $swUser->is_super_user)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <a href="{{route('sw.reportTodayMemberList')}}" class="text-decoration-none">
                        <div class="card report-card">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-8">
                                <div class="report-card-icon bg-light-success">
                                    <i class="ki-outline ki-arrow-right fs-2 text-success"></i>
                                </div>
                                <h5 class="report-card-title text-center">{{ trans('sw.client_attendees_today')}}</h5>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
                @if(in_array('reportTodayPTMemberList', (array)$swUser->permissions) || $swUser->is_super_user)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <a href="{{route('sw.reportTodayPTMemberList')}}" class="text-decoration-none">
                        <div class="card report-card">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-8">
                                <div class="report-card-icon bg-light-info">
                                    <i class="ki-outline ki-arrow-right fs-2 text-info"></i>
                                </div>
                                <h5 class="report-card-title text-center">{{ trans('sw.client_pt_attendees_today')}}</h5>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
                @if(in_array('reportTodayNonMemberList', (array)$swUser->permissions) || $swUser->is_super_user)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <a href="{{route('sw.reportTodayNonMemberList')}}" class="text-decoration-none">
                        <div class="card report-card">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-8">
                                <div class="report-card-icon bg-light-warning">
                                    <i class="ki-outline ki-arrow-right fs-2 text-warning"></i>
                                </div>
                                <h5 class="report-card-title text-center">{{ trans('sw.non_client_attendees_today')}}</h5>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
                @if(in_array('reportUserAttendeesList', (array)$swUser->permissions) || $swUser->is_super_user)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <a href="{{route('sw.reportUserAttendeesList')}}" class="text-decoration-none">
                        <div class="card report-card">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-8">
                                <div class="report-card-icon bg-light-primary">
                                    <i class="ki-outline ki-user fs-2 text-primary"></i>
                                </div>
                                <h5 class="report-card-title text-center">{{ trans('sw.attendees_report')}}</h5>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif
        <!--end::Attendance Reports Section-->

        <!--begin::Store Reports Section-->
        @if(in_array('reportStoreList', (array)$swUser->permissions) || $swUser->is_super_user)
        <div class="mb-10">
            <h3 class="section-title">
                <i class="ki-outline ki-basket fs-2 me-2 text-primary"></i>
                {{ trans('sw.store_reports') ?? 'Store Reports' }}
            </h3>
            <div class="row g-5">
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <a href="{{route('sw.reportStoreList')}}" class="text-decoration-none">
                        <div class="card report-card">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-8">
                                <div class="report-card-icon bg-light-warning">
                                    <i class="ki-outline ki-basket fs-2 text-warning"></i>
                                </div>
                                <h5 class="report-card-title text-center">{{ trans('sw.store_report')}}</h5>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        @endif
        <!--end::Store Reports Section-->

        <!--begin::Loyalty Reports Section-->
        @if(@$mainSettings->active_loyalty && $swUser && ($swUser->is_super_user || isset($permissionsMap['listLoyaltyTransaction'])))
        <div class="mb-10">
            <h3 class="section-title">
                <i class="ki-outline ki-gift fs-2 me-2 text-primary"></i>
                {{ trans('sw.loyalty_reports') ?? 'Loyalty Reports' }}
            </h3>
            <div class="row g-5">
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <a href="{{ route('sw.loyalty_transactions.index') }}" class="text-decoration-none">
                        <div class="card report-card">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-8">
                                <div class="report-card-icon bg-light-primary">
                                    <i class="ki-outline ki-gift fs-2 text-primary"></i>
                                </div>
                                <h5 class="report-card-title text-center">{{ trans('sw.loyalty_transactions') }}</h5>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        @endif
        <!--end::Loyalty Reports Section-->

        <!--begin::AI Reports Section-->
        @if(@$mainSettings->active_ai && $swUser && ($swUser->is_super_user || 
            isset($permissionsMap['aiReportsDashboard']) ||
            isset($permissionsMap['aiReportsJobs']) ||
            isset($permissionsMap['aiReportsInsights'])))
        <div class="mb-10">
            <h3 class="section-title">
                <i class="ki-outline ki-abstract-26 fs-2 me-2 text-primary"></i>
                {{ trans('sw.ai_reports') ?? 'AI Reports' }}
            </h3>
            <div class="row g-5">
                @if($swUser->is_super_user || isset($permissionsMap['aiReportsDashboard']))
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <a href="{{ route('ai.reports.dashboard') }}" class="text-decoration-none">
                        <div class="card report-card">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-8">
                                <div class="report-card-icon bg-light-primary">
                                    <i class="ki-outline ki-chart-simple fs-2 text-primary"></i>
                                </div>
                                <h5 class="report-card-title text-center">{{ trans('sw.ai_dashboard') }}</h5>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
                @if($swUser->is_super_user || isset($permissionsMap['aiReportsJobs']))
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <a href="{{ route('ai.reports.jobs') }}" class="text-decoration-none">
                        <div class="card report-card">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-8">
                                <div class="report-card-icon bg-light-info">
                                    <i class="ki-outline ki-briefcase fs-2 text-info"></i>
                                </div>
                                <h5 class="report-card-title text-center">{{ trans('sw.ai_jobs') }}</h5>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
                @if($swUser->is_super_user || isset($permissionsMap['aiReportsInsights']))
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <a href="{{ route('ai.reports.insights') }}" class="text-decoration-none">
                        <div class="card report-card">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-8">
                                <div class="report-card-icon bg-light-success">
                                    <i class="ki-outline ki-chart fs-2 text-success"></i>
                                </div>
                                <h5 class="report-card-title text-center">{{ trans('sw.ai_insights') }}</h5>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif
        <!--end::AI Reports Section-->

        <!--begin::System Reports Section-->
        @if(in_array('listUserLog', (array)$swUser->permissions) || $swUser->is_super_user)
        <div class="mb-10">
            <h3 class="section-title">
                <i class="ki-outline ki-file fs-2 me-2 text-primary"></i>
                {{ trans('sw.system_reports') ?? 'System Reports' }}
            </h3>
            <div class="row g-5">
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <a href="{{route('sw.listUserLog')}}" class="text-decoration-none">
                        <div class="card report-card">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-8">
                                <div class="report-card-icon bg-light-warning">
                                    <i class="ki-outline ki-document fs-2 text-warning"></i>
                                </div>
                                <h5 class="report-card-title text-center">{{ trans('sw.logs')}}</h5>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        @endif
        <!--end::System Reports Section-->
    </div>
@endsection

@section('scripts')
    @parent
@endsection



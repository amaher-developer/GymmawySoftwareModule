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
        .short-btn {
            min-width: 140px;
            height: 120px;
            /*color: white;*/
        }

        .short-btn i {
            line-height: 40px !important;
        }

        .tile {
            border-radius: 25px !important;
        }

        .name {
            position: initial !important;
            margin-left: 10px !important;
            font-weight: bold !important;
        }

        .tile-object {
            text-align: center;
        }
    </style>
@endsection
@section('page_body')
    <div class="row">


        {{--        <h4 class="col-lg-12 text-center">{{ trans('sw.no_record_found')}}</h4>--}}

        <div class="slimScrollDiv">
            <div class=" tiles" style="max-height: 300px; padding-right: 20px;padding-bottom: 20px;"
                 data-always-visible="1" data-rail-visible="0">
                {{--            <a href="http://localhost/gym/demo/ar/member" class="icon-btn short-btn">--}}
                {{--                <i class="fa fa-group"></i>--}}
                {{--                <div class="short-btn-div">--}}
                {{--                    العملاء المشتركين--}}
                {{--                </div>--}}
                {{--            </a>--}}
                @if(in_array('listMoneyBox', (array)$swUser->permissions) || $swUser->is_super_user)

                    <a href="{{route('sw.listMoneyBox').'?from='.date('m/d/Y').'&'.'to='.date('m/d/Y')}}">
                        <div class="tile bg-green-dark">
                            <div class="tile-body">
                                <i class="fa fa-bar-chart-o"></i>
                            </div>
                            <div class="tile-object">
                                <div class="name">
                                    {{ trans('sw.money_report')}}
                                </div>
                            </div>
                        </div>
                    </a>
                @endif
                @if(in_array('listMoneyBox', (array)$swUser->permissions) || $swUser->is_super_user)
                    <a href="{{route('sw.reportMoneyboxTax').'?from='.date('m/d/Y').'&'.'to='.date('m/d/Y')}}">
                        <div class="tile bg-green-jungle">
                            <div class="tile-body">
                                <i class="icon-graph"></i>
                            </div>
                            <div class="tile-object">
                                <div class="name">
                                    {{ trans('sw.moneybox_tax')}}
                                </div>
                            </div>
                        </div>
                    </a>
                @endif
                @if(config('sw_billing.zatca_enabled') && (in_array('reportZatcaInvoices', (array)$swUser->permissions) || $swUser->is_super_user))
                    <a href="{{ route('sw.reportZatcaInvoices') }}">
                        <div class="tile bg-blue-hoki">
                            <div class="tile-body">
                                <i class="fa fa-file-text"></i>
                            </div>
                            <div class="tile-object">
                                <div class="name">
                                    {{ trans('sw.zatca_invoices_report') }}
                                </div>
                            </div>
                        </div>
                    </a>
                @endif
                @if(in_array('listUserLog', (array)$swUser->permissions) || $swUser->is_super_user)

                    <a href="{{route('sw.listUserLog')}}">
                        <div class="tile bg-yellow">
                            <div class="tile-body">
                                <i class="icon-doc"></i>
                            </div>
                            <div class="tile-object">
                                <div class="name">
                                    {{ trans('sw.logs')}}
                                </div>
                            </div>
                        </div>
                    </a>
                @endif
                @if(in_array('reportTodayMemberList', (array)$swUser->permissions) || $swUser->is_super_user)

                    <a href="{{route('sw.reportTodayMemberList')}}">
                        <div class="tile bg-green">
                            <div class="tile-body">
                                <i class="fa fa-sign-in"></i>
                            </div>
                            <div class="tile-object">
                                <div class="name">
                                    {{ trans('sw.client_attendees_today')}}
                                </div>
                            </div>
                        </div>
                    </a>
                @endif
                @if(in_array('reportTodayPTMemberList', (array)$swUser->permissions) || $swUser->is_super_user)

                    <a href="{{route('sw.reportTodayPTMemberList')}}">
                        <div class="tile bg-green-haze">
                            <div class="tile-body">
                                <i class="fa fa-sign-in"></i>
                            </div>
                            <div class="tile-object">
                                <div class="name">
                                    {{ trans('sw.client_pt_attendees_today')}}
                                </div>
                            </div>
                        </div>
                    </a>
                @endif
                @if(in_array('reportUserAttendeesList', (array)$swUser->permissions) || $swUser->is_super_user)

                    <a href="{{route('sw.reportUserAttendeesList')}}">
                        <div class="tile bg-green-jungle">
                            <div class="tile-body">
                                <i class="fa fa-user"></i>
                            </div>
                            <div class="tile-object">
                                <div class="name">
                                    {{ trans('sw.attendees_report')}}
                                </div>
                            </div>
                        </div>
                    </a>
                @endif
                @if(in_array('reportDetailMemberList', (array)$swUser->permissions) || $swUser->is_super_user)

                    <a href="{{route('sw.reportDetailMemberList')}}">
                        <div class="tile bg-red">
                            <div class="tile-body">
                                <i class="icon-users"></i>
                            </div>
                            <div class="tile-object">
                                <div class="name">
                                    {{ trans('sw.memberships_detail_report')}}
                                </div>
                            </div>
                        </div>
                    </a>
                @endif
                @if(in_array('reportRenewMemberList', (array)$swUser->permissions) || $swUser->is_super_user)

                    {{--            <div style="float: none;clear: both"></div>--}}
                    <a href="{{route('sw.reportRenewMemberList')}}">
                        <div class="tile bg-blue">
                            <div class="tile-body">
                                <i class="icon-refresh"></i>
                            </div>
                            <div class="tile-object">
                                <div class="name">
                                    {{ trans('sw.memberships_renewal_report')}}
                                </div>
                            </div>
                        </div>
                    </a>
                @endif
                @if(in_array('reportExpireMemberList', (array)$swUser->permissions) || $swUser->is_super_user)

                    {{--            <div style="float: none;clear: both"></div>--}}
                    <a href="{{route('sw.reportExpireMemberList')}}">
                        <div class="tile bg-red-mint">
                            <div class="tile-body">
                                <i class="fa fa-clock-o"></i>
                            </div>
                            <div class="tile-object">
                                <div class="name">
                                    {{ trans('sw.memberships_expire_report')}}
                                </div>
                            </div>
                        </div>
                    </a>
                @endif

{{--                <div class="tile bg-green-haze">--}}
{{--                    <div class="tile-body">--}}
{{--                        <i class="fa fa-calendar"></i>--}}
{{--                    </div>--}}
{{--                    <div class="tile-object">--}}
{{--                        <div class="name">--}}
{{--                            شهري اجمالي--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
                @if(in_array('reportSubscriptionMemberList', (array)$swUser->permissions) || $swUser->is_super_user)

                    <a href="{{route('sw.reportSubscriptionMemberList')}}">
                        <div class="tile bg-red-sunglo">
                            <div class="tile-body">
                                <i class="icon-credit-card"></i>
                            </div>
                            <div class="tile-object">
                                <div class="name">
                                    {{ trans('sw.report_subscriptions')}}
                                </div>
                            </div>
                        </div>
                    </a>
                @endif
{{--                <div class="tile bg-yellow-lemon">--}}
{{--                    <div class="tile-body">--}}
{{--                        <i class="icon-wallet"></i>--}}
{{--                    </div>--}}
{{--                    <div class="tile-object">--}}
{{--                        <div class="name">--}}
{{--                            بنود الصرف--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}

                @if(in_array('reportStoreList', (array)$swUser->permissions) || $swUser->is_super_user)
                    <a href="{{route('sw.reportStoreList')}}">
                    <div class="tile bg-purple">
                        <div class="tile-body">
                            <i class="icon-basket"></i>
                        </div>
                        <div class="tile-object">
                            <div class="name">
                                {{ trans('sw.store_report')}}
                            </div>
                        </div>
                    </div>
                    </a>
                @endif

{{--                <div class="tile bg-red-sunglo">--}}
{{--                    <div class="tile-body">--}}
{{--                        <i class="fa fa-calendar"></i>--}}
{{--                    </div>--}}
{{--                    <div class="tile-object">--}}
{{--                        <div class="name">--}}
{{--                            تقارير الحجوزات--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="tile bg-blue-hoki">--}}
{{--                    <div class="tile-body">--}}
{{--                        <i class="fa fa-shield"></i>--}}
{{--                    </div>--}}
{{--                    <div class="tile-object">--}}
{{--                        <div class="name">--}}
{{--                            التدريبات الخاصة--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="tile bg-blue-steel">--}}
{{--                    <div class="tile-body">--}}
{{--                        <i class="fa fa-calendar"></i>--}}
{{--                    </div>--}}
{{--                    <div class="tile-object">--}}
{{--                        <div class="name">--}}
{{--                            الخطط التدريبية--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="tile bg-red">--}}
{{--                    <div class="tile-body">--}}
{{--                        <i class="fa fa-user"></i>--}}
{{--                    </div>--}}
{{--                    <div class="tile-object">--}}
{{--                        <div class="name">--}}
{{--                            تقارير الموظفين--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}

            </div>
        </div>

    </div>
@endsection

@section('scripts')
    @parent


@endsection

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
    <link rel="stylesheet" type="text/css"
          href="{{asset('/')}}resources/assets/new_front/global/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css"/>

    <style>

        /* Actions column styling */
        .actions-column {
            min-width: 120px;
            text-align: right;
        }

        .actions-column .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .actions-column .d-flex {
            gap: 0.25rem;
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

            margin: 0 15px;
        }
        .ckbox input{
            width: 20px;
            height: 20px;
        }
        .ckbox span{
            vertical-align: text-top;
        }
        .sch-day-name {
            color: black;
            font-weight: bold;
        }
    </style>
    <link rel="stylesheet" type="text/css" href="{{asset('resources/assets/new_front/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('resources/assets/new_front/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css')}}"/>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
@endsection
@section('page_body')

<!--begin::PT Trainer Subscriptions-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <!--begin::Card title-->
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-user-tick fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ trans('sw.pt_trainer_subscriptions')}}</span>
            </div>
        </div>
        <!--end::Card title-->
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <!--begin::Add PT Trainer Subscription-->
                @if(in_array('createPTTrainerSubscription', (array)$swUser->permissions) || $swUser->is_super_user)
                    <a href="{{route('sw.createPTTrainerSubscription')}}" class="btn btn-sm btn-flex btn-light-primary">
                        <i class="ki-outline ki-plus fs-6"></i>
                        {{ trans('admin.add')}}
                    </a>
                    @endif
                <!--end::Add PT Trainer Subscription-->
            </div>
                        </div>
        <!--end::Card toolbar-->
    </div>
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body pt-0">
        <!--begin::Search-->
        <div class="d-flex align-items-center position-relative my-1 mb-5">
            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
            <form class="d-flex" action="" method="get" style="max-width: 400px;">
                <input type="text" name="search" class="form-control form-control-solid ps-12" value="@php echo @strip_tags($_GET['search']) @endphp" placeholder="{{ trans('sw.search_on')}}">
                <button class="btn btn-primary" type="submit">
                    <i class="ki-outline ki-magnifier fs-3"></i>
                </button>
            </form>
        </div>
        <!--end::Search-->

        <!--begin::Total count-->
        <div class="d-flex align-items-center mb-5">
            <div class="symbol symbol-50px me-5">
                <div class="symbol-label bg-light-primary">
                    <i class="ki-outline ki-chart-simple fs-2x text-primary"></i>
                </div>
                    </div>
            <div class="d-flex flex-column">
                <span class="fs-6 fw-semibold text-gray-900">{{ trans('admin.total_count')}}</span>
                <span class="fs-2 fw-bold text-primary">{{ $total }}</span>
                                </div>
                            </div>
        <!--end::Total count-->

        @if(count($subscriptions) > 0)
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_pt_trainer_subscriptions_table">
                <thead>
                <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-80px text-nowrap">
                        <i class="ki-outline ki-hash fs-6 me-2"></i>
                        #
                    </th>
                    <th class="min-w-200px text-nowrap">
                        <i class="ki-outline ki-user fs-6 me-2"></i>
                        {{ trans('sw.trainer')}}
                    </th>
                    <th class="min-w-150px text-nowrap">
                        <i class="ki-outline ki-calendar fs-6 me-2"></i>
                        {{ trans('sw.subscription_period')}}
                    </th>
                    <th class="min-w-100px text-nowrap">
                        <i class="ki-outline ki-dollar fs-6 me-2"></i>
                        {{ trans('sw.price')}}
                    </th>
                    <th class="min-w-100px text-nowrap">
                        <i class="ki-outline ki-check-circle fs-6 me-2"></i>
                                                {{ trans('sw.status')}}
                                            </th>
                    <th class="min-w-150px text-nowrap">
                        <i class="ki-outline ki-calendar fs-6 me-2"></i>
                        {{ trans('sw.date')}}
                                            </th>
                    <th class="text-end min-w-70px text-nowrap actions-column">
                        <i class="ki-outline ki-setting-2 fs-6 me-2"></i>
                        {{ trans('admin.actions')}}
                                            </th>
                                        </tr>
                                        </thead>
                <tbody class="fw-semibold text-gray-600">
                @foreach($subscriptions as $key=> $subscription)
                                        <tr>
                                            <td>
                            <div class="d-flex align-items-center">
                                <!--begin::Avatar-->
                                <div class="symbol symbol-50px me-3">
                                    <div class="symbol-label fs-3 bg-light-primary text-primary">
                                        <i class="ki-outline ki-hash fs-2"></i>
                                    </div>
                                </div>
                                <!--end::Avatar-->
                                <div>
                                    <!--begin::Title-->
                                    <div class="text-gray-800 text-hover-primary fs-5 fw-bold">
                                        #{{ $subscription->id }}
                                                </div>
                                    <!--end::Title-->
                                                </div>
                                                </div>
                                            </td>
                                            <td>
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                    @if($subscription->trainer && $subscription->trainer->image)
                                        <div class="symbol-label">
                                            <img src="{{asset('uploads/trainers/'.$subscription->trainer->image)}}" alt="{{$subscription->trainer->name ?? 'N/A'}}" class="w-100">
                                        </div>
                                    @else
                                        <div class="symbol-label fs-3 bg-light-info text-info">
                                            <i class="ki-outline ki-user fs-2"></i>
                                        </div>
                                    @endif
                                                </div>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-800 text-hover-primary mb-1">{{ $subscription->trainer->name ?? 'N/A' }}</span>
                                    <span class="text-muted fs-7">{{ $subscription->trainer->phone ?? 'N/A' }}</span>
                                                </div>
                                                </div>
                                            </td>
                                            <td>
                            <div class="d-flex flex-column">
                                <span class="text-gray-800 mb-1">
                                    <i class="ki-outline ki-calendar-2 fs-6 me-1 text-primary"></i>
                                    {{ $subscription->start_date }} - {{ $subscription->end_date }}
                                                            </span>
                                <span class="text-muted fs-7">
                                    {{ (int) \Carbon\Carbon::parse($subscription->start_date)->diffInDays(\Carbon\Carbon::parse($subscription->end_date)) }} {{ trans('sw.days')}}
                                                            </span>
                                                </div>
                                            </td>
                                            <td>
                            <div class="badge badge-light-success fs-7 fw-bold">
                                {{ number_format($subscription->price, 2) }}
                                                </div>
                                            </td>
                                            <td>
                            @if($subscription->status == 1)
                                <div class="badge badge-light-success fs-7 fw-bold">{{ trans('sw.active')}}</div>
                            @else
                                <div class="badge badge-light-danger fs-7 fw-bold">{{ trans('sw.inactive')}}</div>
                            @endif
                                            </td>
                        <td class="text-end pe-0">
                            <span class="text-muted fw-semibold text-muted d-block fs-7">
                                <i class="ki-outline ki-calendar fs-6 me-2"></i>
                                {{ $subscription->created_at->format('Y-m-d H:i') }}
                                                            </span>
                                            </td>
                        <td class="text-end actions-column">
                            <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">
                                @if(in_array('showPTTrainerSubscription', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <a href="{{route('sw.showPTTrainerSubscription',$subscription->id)}}" class="btn btn-icon btn-bg-light btn-active-color-info btn-sm" title="{{ trans('sw.view')}}">
                                        <i class="ki-outline ki-eye fs-2"></i>
                                    </a>
                                @endif
                                @if(in_array('editPTTrainerSubscription', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <a href="{{route('sw.editPTTrainerSubscription',$subscription->id)}}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" title="{{ trans('admin.edit')}}">
                                        <i class="ki-outline ki-pencil fs-2"></i>
                                    </a>
                                @endif
                                @if(in_array('deletePTTrainerSubscription', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <a href="{{route('sw.deletePTTrainerSubscription',$subscription->id)}}" class="confirm_delete btn btn-icon btn-bg-light btn-active-color-danger btn-sm" title="{{ trans('admin.delete')}}">
                                        <i class="ki-outline ki-trash fs-2"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                                        </tr>
                @endforeach
                                        </tbody>
                                    </table>
            </div>
            <!--end::Table-->

            <!--begin::Pagination-->
            <div class="d-flex flex-stack flex-wrap pt-10">
                <div class="fs-6 fw-semibold text-gray-700">
                    {{ trans('sw.showing_entries', [
                        'from' => $subscriptions->firstItem() ?? 0,
                        'to' => $subscriptions->lastItem() ?? 0,
                        'total' => $subscriptions->total()
                    ]) }}
                </div>
                <ul class="pagination">
                    {!! $subscriptions->appends($search_query)->render() !!}
                </ul>
            </div>
            <!--end::Pagination-->
        @else
            <!--begin::Empty State-->
            <div class="text-center py-10">
                <div class="symbol symbol-100px mb-5">
                    <div class="symbol-label fs-2x fw-semibold text-success bg-light-success">
                        <i class="ki-outline ki-user fs-2x text-success"></i>
                    </div>
                </div>
                <div class="fs-1 fw-bold text-gray-900 mb-3">{{ trans('sw.no_data_found')}}</div>
                <div class="fs-6 text-gray-600">{{ trans('sw.no_data_found_desc')}}</div>
            </div>
            <!--end::Empty State-->
        @endif
    </div>
    <!--end::Card body-->
</div>
<!--end::PT Trainer Subscriptions-->
@endsection

@section('scripts')
    @parent
    <script src="{{asset('resources/assets/new_front/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"
            type="text/javascript"></script>
    <script type="text/javascript"
            src="{{asset('/')}}resources/assets/new_front/global/plugins/bootstrap-daterangepicker/moment.min.js"></script>
    <script type="text/javascript"
            src="{{asset('/')}}resources/assets/new_front/global/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>
    <script src="{{asset('/')}}resources/assets/new_front/global/scripts/metronic.js" type="text/javascript"></script>
    <script src="{{asset('/')}}resources/assets/new_front/pages/scripts/components-pickers.js"></script>
    <script>
        jQuery(document).ready(function () {
            ComponentsPickers.init();
        });
    </script>
@endsection


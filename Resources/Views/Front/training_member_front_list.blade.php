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
    <link rel="stylesheet" type="text/css" href="{{asset('/')}}resources/assets/admin/global/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css"/>
    <style>
        .avatar-md {
            padding: 12px;
            background-color: lightgrey;
        }

        .rounded-circle {
            border-radius: 20% !important;
        }

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
    </style>
@endsection
@section('page_body')

<!--begin::Training Members-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <!--begin::Card title-->
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-gym fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title}}</span>    
            </div>
        </div>
        <!--end::Card title-->
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
        <div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3">
        @if(in_array('createTrainingPlan', (array)$swUser->permissions) || $swUser->is_super_user)
                    <a href="{{route('sw.createTrainingPlan', ['type' => \Modules\Software\Classes\TypeConstants::TRAINING_PLAN_TYPE])}}" class="btn btn-sm btn-flex btn-light-primary">
                        <i class="ki-outline ki-plus fs-6"></i>
                        {{ trans('sw.add_plan_training')}}
                    </a>
                    <a href="{{route('sw.createTrainingPlan', ['type' => \Modules\Software\Classes\TypeConstants::DIET_PLAN_TYPE])}}" class="btn btn-sm btn-flex btn-light-primary">
                        <i class="ki-outline ki-plus fs-6"></i>
                        {{ trans('sw.add_plan_diet')}}
                    </a>
                @endif
                <!--begin::Export-->
                @if((count(array_intersect(@(array)$swUser->permissions, ['exportTrainingMemberPDF', 'exportTrainingMemberExcel'])) > 0) || $swUser->is_super_user)
                    <div class="m-0">
                        <button class="btn btn-sm btn-flex btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            <i class="ki-outline ki-exit-down fs-6"></i>
                            {{ trans('sw.download')}}
                        </button>
                        <div class="menu menu-sub menu-sub-dropdown w-200px" data-kt-menu="true">
                            @if(in_array('exportTrainingMemberExcel', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportTrainingMemberExcel')}}" class="menu-link px-3">
                                        <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                        {{ trans('sw.excel_export')}}
                                    </a>
                                </div>
                            @endif
                            @if(in_array('exportTrainingMemberPDF', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportTrainingMemberPDF')}}" class="menu-link px-3">
                                        <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                        {{ trans('sw.pdf_export')}}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                <!--end::Export-->
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
                <input type="text" name="search" class="form-control form-control-solid ps-12" value="{{ request('search') }}" placeholder="{{ trans('sw.search_on')}}">
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

        @if(count($members) > 0)
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_training_members_table">
                <thead>
                <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-80px text-nowrap">
                        <i class="ki-outline ki-hash fs-6 me-2"></i>
                        #
                    </th>
                    <th class="min-w-200px text-nowrap">
                        <i class="ki-outline ki-user fs-6 me-2"></i>
                        {{ trans('sw.member')}}
                    </th>
                    <th class="min-w-150px text-nowrap">
                        <i class="ki-outline ki-gym fs-6 me-2"></i>
                        {{ trans('sw.training')}}
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
                @foreach($members as $key=> $member)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <!--begin::Avatar-->
                                <div class="symbol symbol-50px me-3">
                                    <div class="symbol-label fs-3 bg-light-primary text-primary">
                                        <i class="ki-outline ki-user fs-2"></i>
                                    </div>
                                </div>
                                <!--end::Avatar-->
                                <div>
                                    <!--begin::Title-->
                                    <div class="text-gray-800 text-hover-primary fs-5 fw-bold">
                                        #{{ $member->id }}
                                    </div>
                                    <!--end::Title-->
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                    @if($member->member->image)
                                        <div class="symbol-label">
                                            <img src="{{asset('uploads/members/'.$member->member->image)}}" alt="{{$member->member->name}}" class="w-100">
                                        </div>
                                    @else
                                        <div class="symbol-label fs-3 bg-light-info text-info">
                                            <i class="ki-outline ki-user fs-2"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-800 text-hover-primary mb-1">{{ $member->member->name }}</span>
                                    <span class="text-muted fs-7">{{ $member->member->phone }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-3">
                                @if($member->type == \Modules\Software\Classes\TypeConstants::DIET_PLAN_TYPE)
                                            <img alt="{{$member->type_name}}" title="{{$member->type_name}}" class="symbol-label rounded-circle" src="{{asset('resources/assets/front/images/diet_training.png')}}">
                                        @else
                                            <img alt="{{$member->type_name}}" title="{{$member->type_name}}" class="symbol-label rounded-circle" src="{{asset('resources/assets/front/images/bar_training.png')}}">
                                        @endif
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-800 text-hover-primary mb-1">{{ $member->training->name ?? '' }}</span>
                                    @if(@$member->training->description)
                                        <span class="text-muted fs-7">{{ Str::limit($member->training->description, 30) }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-end pe-0">
                            <div class="d-flex flex-column">
                                <div class="text-muted fw-bold d-flex align-items-center">
                                    <i class="ki-outline ki-calendar fs-6 text-muted me-2"></i>
                                    <span>{{ $member->created_at->format('Y-m-d') }}</span>
                                </div>
                                <div class="text-muted fs-7 d-flex align-items-center">
                                    <i class="ki-outline ki-time fs-6 text-muted me-2"></i>
                                    <span>{{ $member->created_at->format('h:i a') }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="text-end actions-column">
                            <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">
                                
                                @if(in_array('editTrainingMember', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <a href="{{route('sw.editTrainingMember',$member->id)}}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" title="{{ trans('admin.edit') }}">
                                        <i class="ki-outline ki-pencil fs-2"></i>
                                    </a>
                                @endif
                                @if(in_array('deleteTrainingMember', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <a href="{{route('sw.deleteTrainingMember',$member->id)}}" class="confirm_delete btn btn-icon btn-bg-light btn-active-color-danger btn-sm" title="{{ trans('admin.disable') }}">
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
                        'from' => $members->firstItem() ?? 0,
                        'to' => $members->lastItem() ?? 0,
                        'total' => $members->total()
                    ]) }}
                </div>
                <ul class="pagination">
                    {!! $members->appends($search_query)->render() !!}
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
<!--end::Training Members-->
@endsection

@section('scripts')
    @parent
    <script src="{{asset('resources/assets/admin/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"
            type="text/javascript"></script>
    <script type="text/javascript"
            src="{{asset('/')}}resources/assets/admin/global/plugins/bootstrap-daterangepicker/moment.min.js"></script>
    <script type="text/javascript"
            src="{{asset('/')}}resources/assets/admin/global/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>
    <script src="{{asset('/')}}resources/assets/admin/global/scripts/metronic.js" type="text/javascript"></script>
    <script src="{{asset('/')}}resources/assets/admin/pages/scripts/components-pickers.js"></script>
    <script>
        jQuery(document).ready(function () {
            ComponentsPickers.init();
        });
    </script>
@endsection
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
    <link rel="stylesheet" type="text/css" href="{{asset('/')}}resources/assets/admin/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.css"/>
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
    </style>
@endsection
@section('page_body')

<!--begin::Training Files-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <!--begin::Card title-->
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-folder fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title}}</span>    
            </div>
        </div>
        <!--end::Card title-->
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <!--begin::Filter-->
                <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_training_files_filter_collapse">
                    <i class="ki-outline ki-filter fs-6"></i>
                    {{ trans('sw.filter')}}
                </button>
                <!--end::Filter-->
                <!--begin::Export-->
                @if((count(array_intersect(@(array)$swUser->permissions, ['exportTrainingFilePDF', 'exportTrainingFileExcel'])) > 0) || $swUser->is_super_user)
                    <div class="m-0">
                        <button class="btn btn-sm btn-flex btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            <i class="ki-outline ki-exit-down fs-6"></i>
                            {{ trans('sw.download')}}
                        </button>
                        <div class="menu menu-sub menu-sub-dropdown w-200px" data-kt-menu="true">
                            @if(in_array('exportTrainingFileExcel', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportTrainingFileExcel')}}" class="menu-link px-3">
                                        <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                        {{ trans('sw.excel_export')}}
                                    </a>
                                </div>
                            @endif
                            @if(in_array('exportTrainingFilePDF', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportTrainingFilePDF')}}" class="menu-link px-3">
                                        <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                        {{ trans('sw.pdf_export')}}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                <!--end::Export-->
                <!--begin::Add Training File-->
                @if(in_array('createBlockMember', (array)$swUser->permissions) || $swUser->is_super_user)
                    <a href="{{route('sw.createTrainingFile')}}" class="btn btn-sm btn-flex btn-light-primary">
                        <i class="ki-outline ki-plus fs-6"></i>
                        {{ trans('admin.add')}}
                    </a>
                @endif
                <!--end::Add Training File-->
            </div>
        </div>
        <!--end::Card toolbar-->
    </div>
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body pt-0">
        <!--begin::Filter-->
        <div class="collapse" id="kt_training_files_filter_collapse">
            <div class="card card-body mb-5">
                <form id="form_filter" action="" method="get">
                    <div class="row g-6">
                        <div class="col-md-12">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.date_range')}}</label>
                            <div class="input-group date-picker input-daterange">
                                <input type="text" class="form-control" name="from" id="from_date" value="{{ request('from') }}" placeholder="{{ trans('sw.from')}}" autocomplete="off">
                                <span class="input-group-text">{{ trans('sw.to')}}</span>
                                <input type="text" class="form-control" name="to" id="to_date" value="{{ request('to') }}" placeholder="{{ trans('sw.to')}}" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-5">
                        <button type="reset" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6">{{ trans('admin.reset')}}</button>
                        <button type="submit" class="btn btn-primary fw-semibold px-6">
                            <i class="ki-outline ki-check fs-6"></i>
                            {{ trans('sw.filter')}}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!--end::Filter-->
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
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_training_files_table">
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
                        <i class="ki-outline ki-folder fs-6 me-2"></i>
                        {{ trans('sw.file')}}
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
                                        <i class="ki-outline ki-hash fs-2"></i>
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
                            @if($member->file)
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-50px me-3">
                                        <div class="symbol-label fs-3 bg-light-success text-success">
                                            <i class="ki-outline ki-folder fs-2"></i>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <a href="{{asset('uploads/training_files/'.$member->file)}}" target="_blank" class="text-gray-800 text-hover-primary mb-1">
                                            {{ trans('sw.view_file')}}
                                        </a>
                                    </div>
                                </div>
                            @else
                                <span class="badge badge-light-secondary">{{ trans('sw.no_file')}}</span>
                            @endif
                        </td>
                        <td class="text-end pe-0">
                            <span class="text-muted fw-semibold text-muted d-block fs-7">
                                <i class="ki-outline ki-calendar fs-6 me-2"></i>
                                {{ $member->created_at->format('Y-m-d H:i') }}
                            </span>
                        </td>
                        <td class="text-end actions-column">
                            <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">
                                @if(in_array('showTrainingFile', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <a href="{{route('sw.showTrainingFile',$member->id)}}" class="btn btn-sm btn-light btn-active-light-primary">
                                        <i class="ki-outline ki-eye fs-6"></i>
                                    </a>
                                @endif
                                @if(in_array('editTrainingFile', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <a href="{{route('sw.editTrainingFile',$member->id)}}" class="btn btn-sm btn-light btn-active-light-primary">
                                        <i class="ki-outline ki-pencil fs-6"></i>
                                    </a>
                                @endif
                                @if(in_array('deleteTrainingFile', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <a href="{{route('sw.deleteTrainingFile',$member->id)}}" class="confirm_delete btn btn-sm btn-light-danger">
                                        <i class="ki-outline ki-trash fs-6"></i>
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
                        <i class="ki-outline ki-file fs-2x text-success"></i>
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
<!--end::Training Files-->
@endsection

@section('scripts')
    @parent
    <script src="{{asset('resources/assets/admin/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"
            type="text/javascript"></script>
    <script>
        jQuery(document).ready(function () {
            $('.input-daterange').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                clearBtn: true,
                orientation: 'bottom auto'
            });

            $('button[type="reset"]').on('click', function() {
                setTimeout(() => {
                    $(this).closest('form').find('select').trigger('change');
                }, 100);
            });
        });
    </script>
@endsection
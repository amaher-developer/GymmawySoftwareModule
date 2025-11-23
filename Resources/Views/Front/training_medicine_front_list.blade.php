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
    <link rel="stylesheet" type="text/css" href="{{asset('/')}}resources/assets/new_front/global/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css"/>
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

<!--begin::Training Medicines-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <!--begin::Card title-->
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-heart fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title}}</span>    
            </div>
        </div>
        <!--end::Card title-->
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <!--begin::Add Training Medicine-->
                @if(in_array('createTrainingMedicine', (array)$swUser->permissions) || $swUser->is_super_user)
                    <a href="{{route('sw.createTrainingMedicine')}}" class="btn btn-sm btn-flex btn-light-primary">
                        <i class="ki-outline ki-plus fs-6"></i>
                        {{ trans('admin.add')}}
                    </a>
                @endif
                <!--end::Add Training Medicine-->
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

        @if(count($medicines) > 0)
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_training_medicines_table">
                <thead>
                <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-100px text-nowrap">
                        <i class="ki-outline ki-hash fs-6 me-2"></i>
                        #
                    </th>
                    <th class="min-w-200px text-nowrap">
                        <i class="ki-outline ki-heart fs-6 me-2"></i>
                        {{ trans('sw.medicine')}}
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
                @foreach($medicines as $key=> $medicine)
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
                                        #{{ $medicine->id }}
                                    </div>
                                    <!--end::Title-->
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-3">
                                    <div class="symbol-label fs-3 bg-light-success text-success">
                                        <i class="ki-outline ki-heart fs-2"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-800 text-hover-primary mb-1">{{ $medicine->name }}</span>
                                    @if($medicine->description)
                                        <span class="text-muted fs-7">{{ Str::limit($medicine->description, 50) }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-end pe-0">
                            <span class="text-muted fw-semibold text-muted d-block fs-7">
                                <i class="ki-outline ki-calendar fs-6 me-2"></i>
                                {{ $medicine->created_at->format('Y-m-d H:i') }}
                            </span>
                        </td>
                        <td class="text-end actions-column">
                            <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">
                                @if(in_array('showTrainingMedicine', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <a href="{{route('sw.showTrainingMedicine',$medicine->id)}}" class="btn btn-sm btn-light btn-active-light-primary">
                                        <i class="ki-outline ki-eye fs-6"></i>
                                    </a>
                                @endif
                                @if(in_array('editTrainingMedicine', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <a href="{{route('sw.editTrainingMedicine',$medicine->id)}}" class="btn btn-sm btn-light btn-active-light-primary">
                                        <i class="ki-outline ki-pencil fs-6"></i>
                                    </a>
                                @endif
                                @if(in_array('deleteTrainingMedicine', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <a href="{{route('sw.deleteTrainingMedicine',$medicine->id)}}" class="confirm_delete btn btn-sm btn-light-danger">
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
                    Showing {{ $medicines->firstItem() ?? 0 }} to {{ $medicines->lastItem() ?? 0 }} of {{ $medicines->total() }} entries
                </div>
                <ul class="pagination">
                    {!! $medicines->appends($search_query)->render() !!}
                </ul>
            </div>
            <!--end::Pagination-->
        @else
            <!--begin::Empty State-->
            <div class="text-center py-10">
                <div class="symbol symbol-100px mb-5">
                    <div class="symbol-label fs-2x fw-semibold text-success bg-light-success">
                        <i class="ki-outline ki-medicine fs-2x text-success"></i>
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
<!--end::Training Medicines-->
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


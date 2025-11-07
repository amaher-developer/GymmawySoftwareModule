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

@section('page_body')

<!--begin::Medicines-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-pill fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title}}</span>
            </div>
        </div>
        <div class="card-toolbar">
            <div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3">
                
                <!--begin::Filter-->
                <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_medicines_filter_collapse">
                    <i class="ki-outline ki-filter fs-6"></i>
                    {{ trans('sw.filter')}}
                </button>
                <!--end::Filter-->

                <!--begin::Add Medicine-->
                @if(in_array('createTrainingMedicine', (array)$swUser->permissions) || $swUser->is_super_user)
                    <a href="{{route('sw.createTrainingMedicine')}}" class="btn btn-sm btn-flex btn-primary">
                        <i class="ki-outline ki-plus fs-6"></i>
                        {{ trans('sw.add_medicine')}}
                    </a>
                @endif
                <!--end::Add Medicine-->
                
                <!--begin::Export-->
                @if((count(array_intersect(@(array)$swUser->permissions, ['exportTrainingMedicinePDF', 'exportTrainingMedicineExcel'])) > 0) || $swUser->is_super_user)
                    <div class="m-0">
                        <button class="btn btn-sm btn-flex btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            <i class="ki-outline ki-exit-down fs-6"></i>
                            {{ trans('sw.download')}}
                        </button>
                        <div class="menu menu-sub menu-sub-dropdown w-200px" data-kt-menu="true">
                            @if(in_array('exportTrainingMedicineExcel', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3">
                                        <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                        {{ trans('sw.excel_export')}}
                                    </a>
                                </div>
                            @endif
                            @if(in_array('exportTrainingMedicinePDF', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3">
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
    </div>
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body pt-0">
        <!--begin::Filter-->
        <div class="collapse" id="kt_medicines_filter_collapse">
            <div class="card card-body mb-5">
                <form id="form_filter" action="" method="get">
                    <div class="row g-6">
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.status')}}</label>
                            <select name="status" class="form-select form-select-solid">
                                <option value="">{{ trans('sw.all')}}</option>
                                <option value="1" @if(request('status') == '1') selected @endif>{{ trans('sw.active')}}</option>
                                <option value="0" @if(request('status') == '0') selected @endif>{{ trans('sw.inactive')}}</option>
                            </select>
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
            <form class="d-flex" action="{{ route('sw.listTrainingMedicine') }}" method="get" style="max-width: 400px;">
                <input type="text" name="q" class="form-control form-control-solid ps-12" value="{{ request('q') }}" placeholder="{{ trans('sw.search_medicines')}}...">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <button class="btn btn-primary" type="submit">
                    <i class="ki-outline ki-magnifier fs-3"></i>
                </button>
            </form>
        </div>
        <!--end::Search-->

        <!--begin::Stats Cards-->
        <div class="row g-5 g-xl-8 mb-8">
            <div class="col-xl-4">
                <div class="card card-flush h-xl-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="symbol symbol-50px me-5">
                            <div class="symbol-label bg-light-primary">
                                <i class="ki-outline ki-pill fs-2x text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-gray-900 fw-bold fs-2">{{ $total }}</div>
                            <div class="text-gray-400 fw-semibold">{{ trans('sw.total_medicines') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card card-flush h-xl-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="symbol symbol-50px me-5">
                            <div class="symbol-label bg-light-success">
                                <i class="ki-outline ki-check-circle fs-2x text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-gray-900 fw-bold fs-2">{{ \Modules\Software\Models\GymTrainingMedicine::where('branch_setting_id', $swUser->branch_setting_id ?? 1)->where('status', 1)->count() }}</div>
                            <div class="text-gray-400 fw-semibold">{{ trans('sw.active_medicines') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card card-flush h-xl-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="symbol symbol-50px me-5">
                            <div class="symbol-label bg-light-danger">
                                <i class="ki-outline ki-cross-circle fs-2x text-danger"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-gray-900 fw-bold fs-2">{{ \Modules\Software\Models\GymTrainingMedicine::where('branch_setting_id', $swUser->branch_setting_id ?? 1)->where('status', 0)->count() }}</div>
                            <div class="text-gray-400 fw-semibold">{{ trans('sw.inactive_medicines') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Stats Cards-->

        @if(count($medicines) > 0)
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_medicines_table">
                    <thead>
                        <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-50px text-nowrap">
                                <i class="ki-outline ki-barcode fs-6 me-2"></i>{{ trans('sw.id')}}
                            </th>
                            <th class="min-w-200px text-nowrap">
                                <i class="ki-outline ki-pill fs-6 me-2"></i>{{ trans('sw.medicine_name_en')}}
                            </th>
                            <th class="min-w-200px text-nowrap">
                                <i class="ki-outline ki-pill fs-6 me-2"></i>{{ trans('sw.medicine_name_ar')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-status fs-6 me-2"></i>{{ trans('sw.status')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.created_at')}}
                            </th>
                            <th class="text-end min-w-100px">
                                <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('admin.actions')}}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @foreach($medicines as $medicine)
                        <tr>
                            <td>
                                <span class="fw-bold text-gray-800 fs-6">#{{ $medicine->id }}</span>
                            </td>
                            <td>
                                <div class="text-gray-800 text-hover-primary fs-5 fw-bold">
                                    {{ $medicine->name_en }}
                                </div>
                            </td>
                            <td>
                                <div class="text-gray-800 fs-5 fw-bold">
                                    {{ $medicine->name_ar }}
                                </div>
                            </td>
                            <td>
                                @if($medicine->status)
                                <span class="badge badge-success">
                                    <i class="ki-outline ki-check fs-7"></i> {{ trans('sw.active')}}
                                </span>
                                @else
                                <span class="badge badge-danger">
                                    <i class="ki-outline ki-cross fs-7"></i> {{ trans('sw.inactive')}}
                                </span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <div class="text-muted fw-bold d-flex align-items-center">
                                        <i class="ki-outline ki-calendar fs-6 text-muted me-2"></i>
                                        <span>{{ $medicine->created_at->format('Y-m-d') }}</span>
                                    </div>
                                    <div class="text-muted fs-7 d-flex align-items-center">
                                        <i class="ki-outline ki-time fs-6 text-muted me-2"></i>
                                        <span>{{ $medicine->created_at->format('h:i a') }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end align-items-center gap-1">
                                    @if(in_array('editTrainingMedicine', (array)$swUser->permissions) || $swUser->is_super_user)
                                        <!--begin::Edit-->
                                        <a href="{{route('sw.editTrainingMedicine',$medicine->id)}}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
                                           title="{{ trans('admin.edit')}}">
                                            <i class="ki-outline ki-pencil fs-2"></i>
                                        </a>
                                        <!--end::Edit-->
                                    @endif

                                    @if(in_array('deleteTrainingMedicine', (array)$swUser->permissions) || $swUser->is_super_user)
                                        <!--begin::Delete-->
                                        <a title="{{ trans('admin.delete')}}"
                                           data-swal-text="{{ trans('sw.delete_confirm')}}"
                                           href="{{route('sw.deleteTrainingMedicine',$medicine->id)}}"
                                           class="confirm_delete btn btn-icon btn-bg-light btn-active-color-danger btn-sm">
                                            <i class="ki-outline ki-trash fs-2"></i>
                                        </a>
                                        <!--end::Delete-->
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
                        'from' => $medicines->firstItem() ?? 0,
                        'to' => $medicines->lastItem() ?? 0,
                        'total' => $medicines->total()
                    ]) }}
                </div>
                <ul class="pagination">
                    {!! $medicines->appends(request()->except('page'))->render() !!}
                </ul>
            </div>
            <!--end::Pagination-->
        @else
            <!--begin::Empty State-->
            <div class="text-center py-10">
                <div class="symbol symbol-100px mb-5">
                    <div class="symbol-label fs-2x fw-semibold text-primary bg-light-primary">
                        <i class="ki-outline ki-pill fs-2x"></i>
                    </div>
                </div>
                <h4 class="text-gray-800 fw-bold">{{ trans('sw.no_medicines_found')}}</h4>
                <p class="text-muted">{{ trans('sw.no_medicines_found_desc')}}</p>
                <a href="{{route('sw.createTrainingMedicine')}}" class="btn btn-primary mt-3">
                    <i class="ki-outline ki-plus fs-2"></i>
                    {{ trans('sw.add_first_medicine')}}
                </a>
            </div>
            <!--end::Empty State-->
        @endif
    </div>
    <!--end::Card body-->
</div>
<!--end::Medicines-->
@endsection

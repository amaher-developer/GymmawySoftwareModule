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

<!--begin::Training Plans-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-notepad fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title}}</span>
            </div>
        </div>
        <div class="card-toolbar">
            <div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3">
                
                <!--begin::Filter-->
                <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_plans_filter_collapse">
                    <i class="ki-outline ki-filter fs-6"></i>
                    {{ trans('sw.filter')}}
                </button>
                <!--end::Filter-->

                <!--begin::Add Plan-->
                @if(in_array('createTrainingPlan', (array)$swUser->permissions) || $swUser->is_super_user)
                    <a href="{{route('sw.createTrainingPlan')}}" class="btn btn-sm btn-flex btn-primary">
                        <i class="ki-outline ki-plus fs-6"></i>
                        {{ trans('sw.add_plan')}}
                    </a>
                @endif
                <!--end::Add Plan-->
                
                <!--begin::Export-->
                @if((count(array_intersect(@(array)$swUser->permissions, ['exportTrainingPlanPDF', 'exportTrainingPlanExcel'])) > 0) || $swUser->is_super_user)
                    <div class="m-0">
                        <button class="btn btn-sm btn-flex btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            <i class="ki-outline ki-exit-down fs-6"></i>
                            {{ trans('sw.download')}}
                        </button>
                        <div class="menu menu-sub menu-sub-dropdown w-200px" data-kt-menu="true">
                            @if(in_array('exportTrainingPlanExcel', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3">
                                        <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                        {{ trans('sw.excel_export')}}
                                    </a>
                                </div>
                            @endif
                            @if(in_array('exportTrainingPlanPDF', (array)$swUser->permissions) || $swUser->is_super_user)
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
        <div class="collapse" id="kt_plans_filter_collapse">
            <div class="card card-body mb-5">
                <form id="form_filter" action="" method="get">
                    <div class="row g-6">
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.plan_type')}}</label>
                            <select name="type" class="form-select form-select-solid">
                                <option value="">{{ trans('sw.all')}}</option>
                                <option value="1" @if(request('type') == '1') selected @endif>{{ trans('sw.training_plan')}}</option>
                                <option value="2" @if(request('type') == '2') selected @endif>{{ trans('sw.diet_plan')}}</option>
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
            <form class="d-flex" action="{{ route('sw.listTrainingPlan') }}" method="get" style="max-width: 400px;">
                <input type="text" name="q" class="form-control form-control-solid ps-12" value="{{ request('q') }}" placeholder="{{ trans('sw.search_plans')}}...">
                @if(request('type'))
                    <input type="hidden" name="type" value="{{ request('type') }}">
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
                                <i class="ki-outline ki-notepad fs-2x text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-gray-900 fw-bold fs-2">{{ $total }}</div>
                            <div class="text-gray-400 fw-semibold">{{ trans('sw.total_plans') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card card-flush h-xl-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="symbol symbol-50px me-5">
                            <div class="symbol-label bg-light-success">
                                <i class="la la-dumbbell fs-2x text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-gray-900 fw-bold fs-2">{{ \Modules\Software\Models\GymTrainingPlan::where('branch_setting_id', $swUser->branch_setting_id ?? 1)->where('type', 1)->count() }}</div>
                            <div class="text-gray-400 fw-semibold">{{ trans('sw.training_plans') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card card-flush h-xl-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="symbol symbol-50px me-5">
                            <div class="symbol-label bg-light-warning">
                                <i class="ki-outline ki-apple fs-2x text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-gray-900 fw-bold fs-2">{{ \Modules\Software\Models\GymTrainingPlan::where('branch_setting_id', $swUser->branch_setting_id ?? 1)->where('type', 2)->count() }}</div>
                            <div class="text-gray-400 fw-semibold">{{ trans('sw.diet_plans') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Stats Cards-->

        @if(count($plans) > 0)
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_plans_table">
                    <thead>
                        <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-50px text-nowrap">
                                <i class="ki-outline ki-barcode fs-6 me-2"></i>{{ trans('sw.id')}}
                            </th>
                            <th class="min-w-300px text-nowrap">
                                <i class="ki-outline ki-notepad fs-6 me-2"></i>{{ trans('sw.plan_title')}}
                            </th>
                            <th class="min-w-125px text-nowrap">
                                <i class="ki-outline ki-category fs-6 me-2"></i>{{ trans('sw.type')}}
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
                        @foreach($plans as $plan)
                        <tr>
                            <td>
                                <span class="fw-bold text-gray-800 fs-6">#{{ $plan->id }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <!--begin::Icon-->
                                    <div class="symbol symbol-50px me-3">
                                        <div class="symbol-label {{ $plan->type == 1 ? 'bg-light-success' : 'bg-light-warning' }}">
                                            <i class="{{ $plan->type == 1 ? 'la la-dumbbell text-success' : 'la la-apple text-warning' }} fs-2x"></i>
                                        </div>
                                    </div>
                                    <!--end::Icon-->
                                    <div>
                                        <div class="text-gray-800 text-hover-primary fs-5 fw-bold">
                                            {{ $plan->title }}
                                        </div>
                                        <div class="text-muted fs-7">{{ Str::limit($plan->content, 60) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($plan->type == 1)
                                <span class="badge badge-success">
                                    <i class="la la-dumbbell fs-7"></i> {{ trans('sw.training_plan')}}
                                </span>
                                @else
                                <span class="badge badge-warning">
                                    <i class="ki-outline ki-apple fs-7"></i> {{ trans('sw.diet_plan')}}
                                </span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <div class="text-muted fw-bold d-flex align-items-center">
                                        <i class="ki-outline ki-calendar fs-6 text-muted me-2"></i>
                                        <span>{{ $plan->created_at->format('Y-m-d') }}</span>
                                    </div>
                                    <div class="text-muted fs-7 d-flex align-items-center">
                                        <i class="ki-outline ki-time fs-6 text-muted me-2"></i>
                                        <span>{{ $plan->created_at->format('h:i a') }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end align-items-center gap-1">
                                    @if(in_array('editTrainingPlan', (array)$swUser->permissions) || $swUser->is_super_user)
                                        <!--begin::Edit-->
                                        <a href="{{route('sw.editTrainingPlan',$plan->id)}}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
                                           title="{{ trans('admin.edit')}}">
                                            <i class="ki-outline ki-pencil fs-2"></i>
                                        </a>
                                        <!--end::Edit-->
                                    @endif

                                    @if(in_array('deleteTrainingPlan', (array)$swUser->permissions) || $swUser->is_super_user)
                                        <!--begin::Delete-->
                                        <a title="{{ trans('admin.delete')}}"
                                           data-swal-text="{{ trans('sw.delete_confirm')}}"
                                           href="{{route('sw.deleteTrainingPlan',$plan->id)}}"
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
                        'from' => $plans->firstItem() ?? 0,
                        'to' => $plans->lastItem() ?? 0,
                        'total' => $plans->total()
                    ]) }}
                </div>
                <ul class="pagination">
                    {!! $plans->appends(request()->except('page'))->render() !!}
                </ul>
            </div>
            <!--end::Pagination-->
        @else
            <!--begin::Empty State-->
            <div class="text-center py-10">
                <div class="symbol symbol-100px mb-5">
                    <div class="symbol-label fs-2x fw-semibold text-primary bg-light-primary">
                        <i class="ki-outline ki-notepad fs-2x"></i>
                    </div>
                </div>
                <h4 class="text-gray-800 fw-bold">{{ trans('sw.no_plans_found')}}</h4>
                <p class="text-muted">{{ trans('sw.no_plans_found_desc')}}</p>
                <a href="{{route('sw.createTrainingPlan')}}" class="btn btn-primary mt-3">
                    <i class="ki-outline ki-plus fs-2"></i>
                    {{ trans('sw.add_first_plan')}}
                </a>
            </div>
            <!--end::Empty State-->
        @endif
    </div>
    <!--end::Card body-->
</div>
<!--end::Training Plans-->
@endsection

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

<!--begin::Member Training Management-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-user fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title}}</span>
            </div>
        </div>
        <div class="card-toolbar">
            <div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3">
                
                <!--begin::Filter-->
                <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_members_filter_collapse">
                    <i class="ki-outline ki-filter fs-6"></i>
                    {{ trans('sw.filter')}}
                </button>
                <!--end::Filter-->

                <!--begin::Export-->
                @if((count(array_intersect(@(array)$swUser->permissions, ['exportMemberPDF', 'exportMemberExcel'])) > 0) || $swUser->is_super_user)
                    <div class="m-0">
                        <button class="btn btn-sm btn-flex btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            <i class="ki-outline ki-exit-down fs-6"></i>
                            {{ trans('sw.download')}}
                        </button>
                        <div class="menu menu-sub menu-sub-dropdown w-200px" data-kt-menu="true">
                            @if(in_array('exportMemberExcel', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3">
                                        <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                        {{ trans('sw.excel_export')}}
                                    </a>
                                </div>
                            @endif
                            @if(in_array('exportMemberPDF', (array)$swUser->permissions) || $swUser->is_super_user)
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
        <div class="collapse" id="kt_members_filter_collapse">
            <div class="card card-body mb-5">
                <form id="form_filter" action="" method="get">
                    <div class="row g-6">
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.gender')}}</label>
                            <select name="gender" class="form-select form-select-solid">
                                <option value="">{{ trans('sw.all')}}</option>
                                <option value="male" @if(request('gender') == 'male') selected @endif>{{ trans('sw.male')}}</option>
                                <option value="female" @if(request('gender') == 'female') selected @endif>{{ trans('sw.female')}}</option>
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
            <form class="d-flex" action="{{ route('sw.listTrainingMemberLog') }}" method="get" style="max-width: 400px;">
                <input type="text" name="q" class="form-control form-control-solid ps-12" value="{{ request('q') }}" placeholder="{{ trans('sw.search_members')}}...">
                @if(request('gender'))
                    <input type="hidden" name="gender" value="{{ request('gender') }}">
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
                                <i class="ki-outline ki-user fs-2x text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-gray-900 fw-bold fs-2">{{ $total }}</div>
                            <div class="text-gray-400 fw-semibold">{{ trans('sw.total_members') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card card-flush h-xl-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="symbol symbol-50px me-5">
                            <div class="symbol-label bg-light-success">
                                <i class="ki-outline ki-calendar fs-2x text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-gray-900 fw-bold fs-2">{{ \Modules\Software\Models\GymTrainingMemberLog::whereDate('created_at', today())->count() }}</div>
                            <div class="text-gray-400 fw-semibold">{{ trans('sw.today_activities') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card card-flush h-xl-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="symbol symbol-50px me-5">
                            <div class="symbol-label bg-light-info">
                                <i class="ki-outline ki-chart-line fs-2x text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-gray-900 fw-bold fs-2">{{ \Modules\Software\Models\GymTrainingMemberLog::count() }}</div>
                            <div class="text-gray-400 fw-semibold">{{ trans('sw.total_logs') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Stats Cards-->

        @if(count($members) > 0)
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_members_table">
                    <thead>
                        <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-barcode fs-6 me-2"></i>{{ trans('sw.identification_code')}}
                            </th>
                            <th class="min-w-200px text-nowrap">
                                <i class="ki-outline ki-user fs-6 me-2"></i>{{ trans('sw.name')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-phone fs-6 me-2"></i>{{ trans('sw.phone')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-chart-line fs-6 me-2"></i>{{ trans('sw.activities')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.date')}}
                            </th>
                            <th class="text-end min-w-100px">
                                <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('admin.actions')}}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @foreach($members as $member)
                        <tr>
                            <td>
                                <span class="fw-bold text-gray-800 fs-6">{{ $member->code }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <!--begin::Avatar-->
                                    <div class="symbol symbol-50px me-3">
                                        @if(isset($member->image) && $member->image)
                                        <img alt="avatar" class="rounded-circle" src="{{asset('uploads/members/'.$member->image)}}">
                                        @else
                                        <div class="symbol-label fs-3 bg-light-primary text-primary">
                                            {{ substr($member->name, 0, 1) }}
                                        </div>
                                        @endif
                                    </div>
                                    <!--end::Avatar-->
                                    <div>
                                        <!--begin::Title-->
                                        <div class="text-gray-800 text-hover-primary fs-5 fw-bold">
                                            {{ $member->name }}
                                        </div>
                                        @if($member->email)
                                            <div class="text-muted fs-7">
                                                <i class="ki-outline ki-sms fs-6 me-1"></i> {{$member->email}}
                                            </div>
                                        @endif
                                        <!--end::Title-->
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="fw-bold">{{ $member->phone }}</span>
                            </td>
                            <td>
                                <span class="badge badge-light-info">
                                    {{ \Modules\Software\Models\GymTrainingMemberLog::where('member_id', $member->id)->count() }}
                                </span>
                            </td>
                            <td>
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
                            <td class="text-end">
                                <div class="d-flex justify-content-end align-items-center gap-1">
                                    <!--begin::Manage-->
                                    <a href="{{route('sw.showTrainingMemberLog',$member->id)}}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
                                       title="{{ trans('sw.manage_training')}}">
                                        <i class="ki-outline ki-arrow-right fs-2"></i>
                                    </a>
                                    <!--end::Manage-->
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
                    {!! $members->appends(request()->except('page'))->render() !!}
                </ul>
            </div>
            <!--end::Pagination-->
        @else
            <!--begin::Empty State-->
            <div class="text-center py-10">
                <div class="symbol symbol-100px mb-5">
                    <div class="symbol-label fs-2x fw-semibold text-primary bg-light-primary">
                        <i class="ki-outline ki-user fs-2x"></i>
                    </div>
                </div>
                <h4 class="text-gray-800 fw-bold">{{ trans('sw.no_members_found')}}</h4>
                <p class="text-muted">{{ trans('sw.no_members_found_desc')}}</p>
            </div>
            <!--end::Empty State-->
        @endif
    </div>
    <!--end::Card body-->
</div>
<!--end::Member Training Management-->
@endsection

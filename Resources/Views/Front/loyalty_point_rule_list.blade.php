@extends('software::layouts.list')
@section('list_title') {{ @$title }} @endsection
@section('breadcrumb')
    <!--begin::Breadcrumb-->
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
        <!--begin::Item-->
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.dashboard') }}" class="text-muted text-hover-primary">{{ trans('sw.home')}}</a>
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

<!--begin::Loyalty Point Rules-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <!--begin::Card title-->
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-award fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title}}</span>
            </div>
        </div>
        <!--end::Card title-->
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <!--begin::Add Rule-->
                <a href="{{route('sw.loyalty_point_rules.create')}}" class="btn btn-sm btn-flex btn-light-primary">
                    <i class="ki-outline ki-plus fs-6"></i>
                    {{ trans('sw.add')}}
                </a>
                <!--end::Add Rule-->
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

        <!--begin::Table-->
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                <!--begin::Table head-->
                <thead>
                    <tr class="fw-bold text-muted bg-light">
                        <th class="ps-4 min-w-50px rounded-start">#</th>
                        <th class="min-w-200px">{{ trans('sw.name') }}</th>
                        <th class="min-w-150px">{{ trans('sw.money_to_point_rate') }}</th>
                        <th class="min-w-150px">{{ trans('sw.point_to_money_rate') }}</th>
                        <th class="min-w-100px">{{ trans('sw.expires_after_days') }}</th>
                        <th class="min-w-100px">{{ trans('sw.status') }}</th>
                        <th class="min-w-120px text-end pe-4 rounded-end actions-column">{{ trans('sw.actions') }}</th>
                    </tr>
                </thead>
                <!--end::Table head-->
                <!--begin::Table body-->
                <tbody>
                    @forelse($rules as $rule)
                        <tr>
                            <td class="ps-4">
                                <span class="text-gray-800 fw-bold">{{ $rule->id }}</span>
                            </td>
                            <td>
                                <span class="text-gray-800 fw-bold">{{ $rule->name }}</span>
                            </td>
                            <td>
                                <span class="badge badge-light-info">{{ $rule->money_to_point_rate }} {{ trans('sw.egp') }}</span>
                            </td>
                            <td>
                                <span class="badge badge-light-success">{{ $rule->point_to_money_rate }} {{ trans('sw.egp') }}</span>
                            </td>
                            <td>
                                @if($rule->expires_after_days)
                                    <span class="text-gray-800">{{ $rule->expires_after_days }} {{ trans('sw.days') }}</span>
                                @else
                                    <span class="text-muted">{{ trans('sw.never') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($rule->is_active)
                                    <span class="badge badge-light-success">{{ trans('sw.active') }}</span>
                                @else
                                    <span class="badge badge-light-danger">{{ trans('sw.inactive') }}</span>
                                @endif
                            </td>
                            <td class="text-end pe-4 actions-column">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('sw.loyalty_point_rules.edit', $rule->id) }}" 
                                       class="btn btn-icon btn-light-primary btn-sm"
                                       data-bs-toggle="tooltip" 
                                       title="{{ trans('sw.edit') }}">
                                        <i class="ki-outline ki-pencil fs-5"></i>
                                    </a>
                                    <a href="{{ route('sw.loyalty_point_rules.destroy', $rule->id) }}" 
                                       class="btn btn-icon btn-light-danger btn-sm"
                                       data-bs-toggle="tooltip" 
                                       title="{{ trans('sw.delete') }}"
                                       onclick="return confirm('{{ trans('sw.are_you_sure') }}')">
                                        <i class="ki-outline ki-trash fs-5"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-10">
                                <div class="text-gray-400 fw-semibold fs-4">
                                    <i class="ki-outline ki-information fs-2x mb-2"></i>
                                    <div>{{ trans('sw.no_data_found') }}</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <!--end::Table body-->
            </table>
        </div>
        <!--end::Table-->
        
        <!--begin::Pagination-->
        @if(method_exists($rules, 'links'))
            <div class="d-flex flex-stack flex-wrap pt-10">
                <div class="fs-6 fw-semibold text-gray-700">
                    {{ trans('sw.showing') }} {{ $rules->firstItem() ?? 0 }} {{ trans('sw.to') }} {{ $rules->lastItem() ?? 0 }} {{ trans('sw.of') }} {{ $rules->total() }} {{ trans('sw.entries') }}
                </div>
                <div>
                    {{ $rules->links() }}
                </div>
            </div>
        @endif
        <!--end::Pagination-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Loyalty Point Rules-->

@endsection



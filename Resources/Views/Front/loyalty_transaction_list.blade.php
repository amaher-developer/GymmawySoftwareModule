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
@section('page_body')

<!--begin::Statistics Row-->
<div class="row g-5 g-xl-8 mb-5">
    <!--begin::Col-->
    <div class="col-md-4">
        <div class="card bg-success hoverable card-xl-stretch">
            <div class="card-body">
                <i class="ki-outline ki-arrow-up text-white fs-2x ms-n1"></i>
                <div class="text-white fw-bold fs-2 mb-2 mt-5">{{ number_format($stats['total_earned']) }}</div>
                <div class="fw-semibold text-white">{{ trans('sw.total_earned') }}</div>
            </div>
        </div>
    </div>
    <!--end::Col-->
    <!--begin::Col-->
    <div class="col-md-4">
        <div class="card bg-danger hoverable card-xl-stretch">
            <div class="card-body">
                <i class="ki-outline ki-arrow-down text-white fs-2x ms-n1"></i>
                <div class="text-white fw-bold fs-2 mb-2 mt-5">{{ number_format($stats['total_redeemed']) }}</div>
                <div class="fw-semibold text-white">{{ trans('sw.total_redeemed') }}</div>
            </div>
        </div>
    </div>
    <!--end::Col-->
    <!--begin::Col-->
    <div class="col-md-4">
        <div class="card bg-info hoverable card-xl-stretch">
            <div class="card-body">
                <i class="ki-outline ki-pencil text-white fs-2x ms-n1"></i>
                <div class="text-white fw-bold fs-2 mb-2 mt-5">{{ number_format($stats['total_manual']) }}</div>
                <div class="fw-semibold text-white">{{ trans('sw.total_manual') }}</div>
            </div>
        </div>
    </div>
    <!--end::Col-->
</div>
<!--end::Statistics Row-->

<!--begin::Loyalty Transactions-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <!--begin::Card title-->
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-chart-line-star fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title}}</span>
            </div>
        </div>
        <!--end::Card title-->
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <!--begin::Manual Adjustment-->
                <a href="{{route('sw.loyalty_transactions.create_manual')}}" class="btn btn-sm btn-flex btn-light-warning">
                    <i class="ki-outline ki-pencil fs-6"></i>
                    {{ trans('sw.manual_adjustment')}}
                </a>
                <!--end::Manual Adjustment-->
            </div>
        </div>
        <!--end::Card toolbar-->
    </div>
    <!--end::Card header-->
    
    <!--begin::Card body-->
    <div class="card-body pt-0">
        <!--begin::Filter Row-->
        <div class="d-flex flex-wrap gap-3 mb-5">
            <!--begin::Search-->
            <div class="d-flex align-items-center position-relative">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <form class="d-flex" action="" method="get">
                    <input type="text" name="search" class="form-control form-control-solid ps-12" value="{{ request('search') }}" placeholder="{{ trans('sw.search_on')}}">
                    <button class="btn btn-primary" type="submit">
                        <i class="ki-outline ki-magnifier fs-3"></i>
                    </button>
                </form>
            </div>
            <!--end::Search-->
            
            <!--begin::Type Filter-->
            <form action="" method="get">
                <select name="type" class="form-select form-select-solid" onchange="this.form.submit()">
                    <option value="">{{ trans('sw.all_types') }}</option>
                    <option value="earn" {{ request('type') == 'earn' ? 'selected' : '' }}>{{ trans('sw.earn') }}</option>
                    <option value="redeem" {{ request('type') == 'redeem' ? 'selected' : '' }}>{{ trans('sw.redeem') }}</option>
                    <option value="manual" {{ request('type') == 'manual' ? 'selected' : '' }}>{{ trans('sw.manual') }}</option>
                </select>
            </form>
            <!--end::Type Filter-->
        </div>
        <!--end::Filter Row-->

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
                        <th class="min-w-200px">{{ trans('sw.member') }}</th>
                        <th class="min-w-100px">{{ trans('sw.points') }}</th>
                        <th class="min-w-100px">{{ trans('sw.type') }}</th>
                        <th class="min-w-150px">{{ trans('sw.source') }}</th>
                        <th class="min-w-200px">{{ trans('sw.reason') }}</th>
                        <th class="min-w-150px rounded-end">{{ trans('sw.created_at') }}</th>
                    </tr>
                </thead>
                <!--end::Table head-->
                <!--begin::Table body-->
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td class="ps-4">
                                <span class="text-gray-800 fw-bold">{{ $transaction->id }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="d-flex justify-content-start flex-column">
                                        <span class="text-gray-900 fw-bold">{{ $transaction->member->name ?? trans('sw.deleted') }}</span>
                                        @if($transaction->member)
                                            <span class="text-muted fw-semibold text-muted d-block fs-7">
                                                {{ trans('sw.balance') }}: {{ number_format($transaction->member->loyalty_points_balance) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($transaction->points > 0)
                                    <span class="badge badge-light-success fs-6">
                                        <i class="ki-outline ki-arrow-up fs-7 me-1"></i>
                                        +{{ $transaction->points }}
                                    </span>
                                @else
                                    <span class="badge badge-light-danger fs-6">
                                        <i class="ki-outline ki-arrow-down fs-7 me-1"></i>
                                        {{ $transaction->points }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($transaction->type == 'earn')
                                    <span class="badge badge-light-primary">{{ trans('sw.earn') }}</span>
                                @elseif($transaction->type == 'redeem')
                                    <span class="badge badge-light-warning">{{ trans('sw.redeem') }}</span>
                                @else
                                    <span class="badge badge-light-info">{{ trans('sw.manual') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-gray-800">{{ $transaction->source_type ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="text-gray-800">{{ $transaction->reason ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="text-gray-800">{{ $transaction->created_at->format('Y-m-d H:i') }}</span>
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
        @if(method_exists($transactions, 'links'))
            <div class="d-flex flex-stack flex-wrap pt-10">
                <div class="fs-6 fw-semibold text-gray-700">
                    {{ trans('sw.showing') }} {{ $transactions->firstItem() ?? 0 }} {{ trans('sw.to') }} {{ $transactions->lastItem() ?? 0 }} {{ trans('sw.of') }} {{ $transactions->total() }} {{ trans('sw.entries') }}
                </div>
                <div>
                    {{ $transactions->links() }}
                </div>
            </div>
        @endif
        <!--end::Pagination-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Loyalty Transactions-->

@endsection

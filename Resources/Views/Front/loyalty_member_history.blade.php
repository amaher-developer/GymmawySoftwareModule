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
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.loyalty_transactions.index') }}" class="text-muted text-hover-primary">{{ trans('sw.loyalty_transactions_list')}}</a>
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

<!--begin::Member Info Card-->
<div class="card card-flush mb-5">
    <div class="card-body py-5">
        <div class="row g-5">
            <div class="col-md-4">
                <div class="d-flex flex-column">
                    <span class="text-muted fw-bold fs-7">{{ trans('sw.member_name') }}</span>
                    <span class="text-gray-900 fw-bold fs-3">{{ $member->name }}</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex flex-column">
                    <span class="text-muted fw-bold fs-7">{{ trans('sw.current_balance') }}</span>
                    <span class="text-primary fw-bold fs-2">{{ number_format($member->loyalty_points_balance) }} <span class="fs-4">{{ trans('sw.points') }}</span></span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex flex-column">
                    <span class="text-muted fw-bold fs-7">{{ trans('sw.last_update') }}</span>
                    <span class="text-gray-900 fw-bold fs-6">{{ $member->last_points_update ? $member->last_points_update->format('Y-m-d H:i') : '-' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end::Member Info Card-->

<!--begin::Points Breakdown Card-->
<div class="card card-flush mb-5">
    <div class="card-header">
        <h3 class="card-title">{{ trans('sw.points_breakdown') }}</h3>
    </div>
    <div class="card-body py-5">
        <div class="row g-5">
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-50px me-5">
                        <span class="symbol-label bg-light-success">
                            <i class="ki-outline ki-check-circle fs-2x text-success"></i>
                        </span>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="text-muted fw-bold fs-7">{{ trans('sw.active_points') }}</span>
                        <span class="text-gray-900 fw-bold fs-2">{{ number_format($breakdown['total_active']) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-50px me-5">
                        <span class="symbol-label bg-light-warning">
                            <i class="ki-outline ki-time fs-2x text-warning"></i>
                        </span>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="text-muted fw-bold fs-7">{{ trans('sw.expiring_soon') }}</span>
                        <span class="text-warning fw-bold fs-2">{{ number_format($breakdown['expiring_soon']->sum('points')) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end::Points Breakdown Card-->

<!--begin::Transaction History-->
<div class="card card-flush">
    <div class="card-header align-items-center py-5">
        <div class="card-title">
            <div class="d-flex align-items-center">
                <i class="ki-outline ki-chart-simple fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ trans('sw.transaction_history')}}</span>
            </div>
        </div>
    </div>
    
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                <thead>
                    <tr class="fw-bold text-muted bg-light">
                        <th class="ps-4 min-w-50px rounded-start">#</th>
                        <th class="min-w-100px">{{ trans('sw.points') }}</th>
                        <th class="min-w-100px">{{ trans('sw.type') }}</th>
                        <th class="min-w-150px">{{ trans('sw.source') }}</th>
                        <th class="min-w-200px">{{ trans('sw.reason') }}</th>
                        <th class="min-w-150px">{{ trans('sw.date') }}</th>
                        <th class="min-w-150px rounded-end">{{ trans('sw.expires_at') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td class="ps-4">
                                <span class="text-gray-800 fw-bold">{{ $transaction->id }}</span>
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
                            <td><span class="text-gray-800">{{ $transaction->source_type ? trans('sw.loyalty_source_' . $transaction->source_type) : '-' }}</span></td>
                            <td>@php
                                    $reasonText = $transaction->reason ?? '';
                                    if (str_starts_with($reasonText, '__loyalty_reason_earned_campaign|')) {
                                        $parts = explode('|', $reasonText, 3);
                                        $reasonText = trans('sw.loyalty_reason_earned_campaign', ['campaign' => $parts[1] ?? '', 'multiplier' => $parts[2] ?? '']);
                                    } elseif (str_starts_with($reasonText, '__')) {
                                        $reasonText = trans('sw.' . ltrim($reasonText, '_'));
                                    }
                                @endphp<span class="text-gray-800">{{ $reasonText ?: '-' }}</span></td>
                            <td><span class="text-gray-800">{{ $transaction->created_at->format('Y-m-d H:i') }}</span></td>
                            <td>
                                @if($transaction->expires_at)
                                    <span class="badge badge-light-{{ $transaction->expires_at->isPast() ? 'danger' : 'warning' }}">
                                        {{ $transaction->expires_at->format('Y-m-d') }}
                                    </span>
                                @else
                                    <span class="text-muted">{{ trans('sw.never') }}</span>
                                @endif
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
            </table>
        </div>
    </div>
</div>
<!--end::Transaction History-->

@endsection



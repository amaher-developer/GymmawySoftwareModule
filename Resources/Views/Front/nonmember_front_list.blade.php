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
    <link rel="stylesheet" type="text/css" href="{{asset('/')}}resources/assets/new_front/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.css"/>
    <style>
        /* Actions column styling */
        .actions-column {
            min-width: 140px;
            text-align: right;
            white-space: nowrap;
        }

        .actions-column .menu-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .actions-column .menu-link i {
            font-size: 1rem;
        }

        .invoice-block {
            text-align: center;
        }

        @media (min-width: 768px) {
            .modal-xl {
                width: 90%;
                max-width: 1200px;
            }
        }
    </style>
@endsection
@section('page_body')

<!--begin::Non Members-->
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
                <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_nonmembers_filter_collapse">
                    <i class="ki-outline ki-filter fs-6"></i>
                    {{ trans('sw.filter')}}
                </button>
                <!--end::Filter-->
                
                <!--begin::Add Member-->
                @if(in_array('createNonMember', (array)$swUser->permissions) || $swUser->is_super_user)
                    <a href="{{route('sw.createNonMember')}}" class="btn btn-sm btn-flex btn-light-primary">
                        <i class="ki-outline ki-plus fs-6"></i>
                        {{ trans('admin.add')}}
                    </a>
                @endif
                <!--end::Add Member-->
                
                <!--begin::Export-->
                @if((count(array_intersect(@(array)$swUser->permissions, ['exportNonMemberPDF', 'exportNonMemberExcel'])) > 0) || $swUser->is_super_user)
                    <div class="m-0">
                        <button class="btn btn-sm btn-flex btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            <i class="ki-outline ki-exit-down fs-6"></i>
                            {{ trans('sw.download')}}
                        </button>
                        <div class="menu menu-sub menu-sub-dropdown w-200px" data-kt-menu="true">
                            @if(in_array('exportNonMemberExcel', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportNonMemberExcel', $search_query)}}" class="menu-link px-3">
                                        <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                        {{ trans('sw.excel_export')}}
                                    </a>
                                </div>
                            @endif
                            @if(in_array('exportNonMemberPDF', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportNonMemberPDF', $search_query)}}" class="menu-link px-3">
                                        <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                        {{ trans('sw.pdf_export')}}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                <!--end::Export-->
                
                @if($active_activity_reservation)
                <!--begin::Calendar Button-->
                <a href="{{route('sw.listReservation')}}" class="btn btn-sm btn-flex btn-light-info">
                    <i class="ki-outline ki-calendar fs-6"></i>
                    {{ trans('sw.activities_calender')}}
                </a>
                <!--end::Calendar Button-->
                @endif
                
                
            </div>
        </div>
    </div>
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body pt-0">
        <!--begin::Filter-->
        <div class="collapse" id="kt_nonmembers_filter_collapse">
            <div class="card card-body mb-5">
                <form id="form_filter" action="" method="get">
                    <div class="row g-6">
                        <div class="col-md-6">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.date_range')}}</label>
                            <div class="input-group date-picker input-daterange">
                                <input type="text" class="form-control" name="from" id="from_date" value="{{ $formatted_from_date }}" placeholder="{{ trans('sw.from')}}" autocomplete="off">
                                <span class="input-group-text">{{ trans('sw.to')}}</span>
                                <input type="text" class="form-control" name="to" id="to_date" value="{{ $formatted_to_date }}" placeholder="{{ trans('sw.to')}}" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.activity')}}</label>
                            <select name="activity" class="form-select form-select-solid">
                                <option value="">{{ trans('admin.choose')}}...</option>
                                @foreach($activities as $activity)
                                    <option value="{{$activity->name}}" @if(request('activity') == $activity->name) selected="" @endif>{{$activity->name}}</option>
                                @endforeach
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
            <form class="d-flex" action="" method="get" style="max-width: 400px;">
                <input type="text" name="search" class="form-control form-control-solid ps-12" value="{{ $formatted_search }}" placeholder="{{ trans('sw.search_on')}}">
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
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_non_members_table">
                <thead>
                    <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-200px text-nowrap">
                            <i class="ki-outline ki-user fs-6 me-2"></i>{{ trans('sw.name')}}
                        </th>
                        <th class="min-w-100px text-nowrap">
                            <i class="ki-outline ki-phone fs-6 me-2"></i>{{ trans('sw.phone')}}
                        </th>
                        <th class="min-w-200px text-nowrap">
                            <i class="ki-outline ki-list fs-6 me-2"></i>{{ trans('sw.activities')}}
                        </th>
                        <th class="min-w-100px text-nowrap">
                            <i class="ki-outline ki-dollar fs-6 me-2"></i>{{ trans('sw.price')}}
                        </th>
                        <th class="min-w-100px text-nowrap">
                            <i class="ki-outline ki-dollar fs-6 me-2"></i>{{ trans('sw.amount_remaining')}}
                        </th>
                        <th class="min-w-100px">
                            <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.date')}}
                        </th>
                        <th class="text-end min-w-70px actions-column">
                            <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('admin.actions')}}
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
                                            {{ $member->name }}
                                        </div>
                                        @if($member->national_id)
                                            <div class="text-muted fs-7">
                                                <i class="ki-outline ki-credit-cart fs-6 me-1"></i> {{$member->national_id}}
                                            </div>
                                        @endif
                                        @if(@$member->notes)
                                            <div class="text-muted fs-7">
                                                <span class="badge badge-light-info" style="cursor: pointer;" data-target="#pt_subscription_notes_{{$member->id}}" data-toggle="modal">
                                                    <i class="ki-outline ki-information-5 fs-6 me-1"></i> {{ trans('sw.notes')}}
                                                </span>
                                            </div>
                                        @endif
                                        <!--end::Title-->
                                    </div>
                                </div>
                            </td>
                            <td class="pe-0">
                                <span class="fw-bold">{{ $member->phone }}</span>
                            </td>
                            <td class="pe-0">
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($member->activities ?? [] as $activity)
                                        <span class="badge badge-primary badge-lg">{{ $activity['name'] ?? '' }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="pe-0">
                                <span class="fw-bold">{{ number_format($member->price, 2) }}</span>
                            </td>
                            <td class="pe-0">
                                <span class="fw-bold">{{ @number_format($member->amount_remaining, 2) }}</span>
                            </td>
                            <td class="pe-0">
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
                                <a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                    {{ trans('admin.actions') }}
                                    <i class="ki-outline ki-down fs-5 ms-1"></i>
                                </a>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px py-4" data-kt-menu="true">
                                    <div class="menu-item px-3">
                                        <a href="https://web.whatsapp.com/send?phone={{ ((substr( $member->phone, 0, 1 ) === '+') || (substr( $member->phone, 0, 2 ) === '00')) ? $member->phone : '+'.env('APP_COUNTRY_CODE').$member->phone}}"
                                           target="_blank" class="menu-link px-3" title="{{ trans('sw.whatsapp')}}">
                                            <i class="ki-outline ki-message-text-2 text-success"></i>
                                            <span>{{ trans('sw.whatsapp')}}</span>
                                        </a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a href="{{route('sw.showOrderSubscriptionNonMember',$member->id)}}"
                                           class="menu-link px-3" title="{{ trans('sw.invoice')}}">
                                            <i class="ki-outline ki-document text-primary"></i>
                                            <span>{{ trans('sw.invoice')}}</span>
                                        </a>
                                    </div>

                                    @if(@$member->amount_remaining > 0 && (in_array('createNonMemberPayAmountRemainingForm', (array)$swUser->permissions) || $swUser->is_super_user))
                                    <div class="menu-item px-3">
                                        <a href="javascript:void(0)"
                                           data-target="#modalPayNonMember" data-toggle="modal"
                                           data-id="{{$member->id}}"
                                           data-name="{{$member->name}}"
                                           data-amount="{{$member->amount_remaining}}"
                                           class="menu-link px-3 btn-pay-nonmember"
                                           title="{{ trans('sw.pay_remaining')}}">
                                            <i class="ki-outline ki-dollar text-warning"></i>
                                            <span>{{ trans('sw.pay_remaining')}}</span>
                                        </a>
                                    </div>
                                    @endif

                                    @if($active_activity_reservation)
                                        @if($member->reservations_count > 0)
                                        <div class="menu-item px-3">
                                            <a href="javascript:void(0)" class="menu-link px-3 position-relative"
                                               title="{{ trans('sw.upcoming_reservations') }} ({{ $member->reservations_count }})"
                                               data-bs-toggle="modal"
                                               data-bs-target="#upcomingReservationsModal{{ $member->id }}">
                                                <i class="ki-outline ki-calendar-tick text-primary"></i>
                                                <span>{{ trans('sw.upcoming_reservations') }}</span>
                                                <span class="badge badge-circle bg-danger ms-2">{{ $member->reservations_count }}</span>
                                            </a>
                                        </div>
                                        @endif

                                        @if((in_array('createReservation', (array)$swUser->permissions ?? []) || @$swUser->is_super_user) && !empty($member->activities))
                                        <div class="menu-item px-3">
                                            <a href="javascript:void(0)"
                                               class="menu-link px-3"
                                               title="{{ trans('sw.quick_booking') }}"
                                               data-bs-toggle="modal"
                                               data-bs-target="#quickBookModal{{ $member->id }}"
                                               onclick="openQuickBookModal({{ $member->id }}, {{ json_encode($member->activities) }})">
                                                <i class="ki-outline ki-calendar-add text-success"></i>
                                                <span>{{ trans('sw.quick_booking') }}</span>
                                            </a>
                                        </div>
                                        @endif
                                    @endif

                                    @if(in_array('editNonMember', (array)$swUser->permissions) || $swUser->is_super_user)
                                    <div class="menu-item px-3">
                                        <a href="{{route('sw.editNonMember',$member->id)}}"
                                           class="menu-link px-3" title="{{ trans('admin.edit')}}">
                                            <i class="ki-outline ki-pencil text-primary"></i>
                                            <span>{{ trans('admin.edit')}}</span>
                                        </a>
                                    </div>
                                    @endif

                                    @if(in_array('deleteNonMember', (array)$swUser->permissions) || $swUser->is_super_user)
                                        @if(request('trashed'))
                                        <div class="menu-item px-3">
                                            <a title="{{ trans('admin.enable')}}"
                                               href="{{route('sw.deleteNonMember',$member->id)}}"
                                               class="menu-link px-3 confirm_delete" title="{{ trans('admin.enable')}}">
                                                <i class="ki-outline ki-check-circle text-success"></i>
                                                <span>{{ trans('admin.enable')}}</span>
                                            </a>
                                        </div>
                                        @else
                                        <div class="menu-item px-3">
                                            <a title="{{ trans('admin.disable')}}"
                                               data-swal-text="{{ trans('sw.disable_with_refund', ['amount' => $member->price])}}"
                                               href="{{route('sw.deleteNonMember',$member->id).'?refund=1&total_amount='.@$member->price}}"
                                               data-swal-amount="{{@$member->price}}"
                                               class="menu-link px-3 confirm_delete" title="{{ trans('admin.disable')}}">
                                                <i class="ki-outline ki-trash text-danger"></i>
                                                <span>{{ trans('admin.disable')}}</span>
                                            </a>
                                        </div>
                                        @endif
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
                        <i class="ki-outline ki-user fs-2"></i>
                    </div>
                </div>
                <h4 class="text-gray-800 fw-bold">{{ trans('sw.no_record_found')}}</h4>
            </div>
            <!--end::Empty State-->
        @endif
    </div>
    <!--end::Card body-->
</div>
<!--end::Non Members-->


@endsection

    @if($active_activity_reservation)
    <!--begin::Upcoming Reservations Modal for Each Member-->
    @foreach($members as $member)
        @if($member->reservations_count > 0)
            <div class="modal fade" id="upcomingReservationsModal{{ $member->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="fw-bold">{{ trans('sw.upcoming_reservations') }}</h2>
                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                <i class="ki-outline ki-cross fs-1"></i>
                            </div>
                        </div>
                        <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                            <!--begin::Member Info-->
                            <div class="d-flex align-items-center mb-5">
                                <div class="symbol symbol-50px me-3">
                                    <div class="symbol-label bg-light-primary">
                                        <i class="ki-outline ki-user fs-2x text-primary"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-gray-800 fw-bold fs-5">{{ $member->name }}</div>
                                    @if($member->phone)
                                        <div class="text-muted fs-7">
                                            <i class="ki-outline ki-phone fs-6 me-1"></i> {{ $member->phone }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <!--end::Member Info-->
                            
                            <!--begin::Reservations List-->
                            <div class="separator separator-dashed my-5"></div>
                            <div class="mb-5">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h3 class="fw-bold text-gray-800 fs-6">
                                        {{ trans('sw.reservations') }} ({{ $member->member_reservations->count() }})
                                    </h3>
                                    <a href="{{ route('sw.listReservation') }}?non_member_id={{ $member->id }}" class="btn btn-sm btn-light-primary">
                                        <i class="ki-outline ki-eye fs-6"></i> {{ trans('sw.view_all') }}
                                    </a>
                                </div>
                                
                                <div class="d-flex flex-column gap-3">
                                    @foreach($member->member_reservations as $reservation)
                                        <div class="card card-flush border border-gray-300 border-dashed">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center gap-4">
                                                        <!--begin::Date-->
                                                        <div class="text-center">
                                                            <div class="text-gray-500 fw-semibold fs-7 mb-1">{{ trans('sw.date') }}</div>
                                                            <span class="badge badge-{{ $reservation->status == 'confirmed' ? 'success' : ($reservation->status == 'pending' ? 'warning' : 'primary') }} badge-lg">
                                                                {{ $reservation->reservation_date->format('Y-m-d') }}
                                                            </span>
                                                        </div>
                                                        <!--end::Date-->
                                                        
                                                        <!--begin::Time-->
                                                        <div class="text-center">
                                                            <div class="text-gray-500 fw-semibold fs-7 mb-1">{{ trans('sw.time') }}</div>
                                                            <div class="text-gray-800 fw-bold fs-6">
                                                                <i class="ki-outline ki-time fs-5 text-primary me-1"></i>
                                                                {{ $reservation->start_time }} - {{ $reservation->end_time }}
                                                            </div>
                                                        </div>
                                                        <!--end::Time-->
                                                        
                                                        <!--begin::Activity-->
                                                        @if($reservation->activity)
                                                            <div class="text-center">
                                                                <div class="text-gray-500 fw-semibold fs-7 mb-1">{{ trans('sw.activity') }}</div>
                                                                <span class="badge badge-light-info badge-lg">
                                                                    <i class="ki-outline ki-list fs-5 me-1"></i>
                                                                    {{ $reservation->activity->{'name_'.($lang ?? 'ar')} ?? $reservation->activity->name }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                        <!--end::Activity-->
                                                    </div>
                                                    
                                                    <!--begin::Status & Actions-->
                                                    <div class="text-end">
                                                        <div class="mb-2">
                                                            <select class="form-select form-select-sm reservation-status-select" 
                                                                    data-reservation-id="{{ $reservation->id }}"
                                                                    data-old-value="{{ $reservation->status }}"
                                                                    style="min-width: 120px;">
                                                                <option value="pending" @selected($reservation->status == 'pending')>{{ trans('sw.pending') }}</option>
                                                                <option value="confirmed" @selected($reservation->status == 'confirmed')>{{ trans('sw.confirmed') }}</option>
                                                                <option value="attended" @selected($reservation->status == 'attended')>{{ trans('sw.attended') }}</option>
                                                                <option value="cancelled" @selected($reservation->status == 'cancelled')>{{ trans('sw.cancelled') }}</option>
                                                                <option value="missed" @selected($reservation->status == 'missed')>{{ trans('sw.missed') }}</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-icon btn-light-primary reservation-edit-btn" 
                                                                    title="{{ trans('admin.edit') }}"
                                                                    data-reservation-id="{{ $reservation->id }}"
                                                                    data-member-id="{{ $member->id }}">
                                                                <i class="ki-outline ki-pencil fs-4"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <!--end::Status & Actions-->
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <!--end::Reservations List-->
                        </div>
                        <div class="modal-footer flex-center">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('sw.close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
    <!--end::Upcoming Reservations Modal-->
    
    <!--begin::Quick Book Modal for Each Member-->
    @foreach($members as $member)
        @if(!empty($member->activities))
            <div class="modal fade" id="quickBookModal{{ $member->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="fw-bold">
                                <i class="ki-outline ki-calendar-tick fs-2 me-2 text-success"></i>
                                {{ trans('sw.quick_booking') }} - {{ $member->name }}
                            </h2>
                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                <i class="ki-outline ki-cross fs-1"></i>
                            </div>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="qb_nonmember_id_{{ $member->id }}" value="{{ $member->id }}">
                            <input type="hidden" id="qb_reservation_id_{{ $member->id }}" value="">
                            
                            <!--begin::Help Text-->
                            <div class="alert alert-light-info d-flex align-items-center p-4 mb-5">
                                <i class="ki-outline ki-information-5 fs-2x text-info me-3"></i>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-gray-800">{{ trans('sw.quick_booking_title') }}</span>
                                    <span class="text-muted fs-7 mt-1">{{ trans('sw.select_activity_and_time') }}</span>
                                </div>
                            </div>
                            <!--end::Help Text-->
                            
                            <!--begin::Input group-->
                            <div class="mb-5 fv-row">
                                <label class="required form-label">
                                    <i class="ki-outline ki-gym fs-6 me-1"></i>
                                    {{ trans('sw.activity') }}
                                </label>
                                <select id="qb_activity_{{ $member->id }}" class="form-select form-select-solid qb-activity-select" data-member-id="{{ $member->id }}" data-placeholder="{{ trans('sw.select_activity') }}">
                                    <option value="">{{ trans('sw.select_activity') }}</option>
                                    @foreach($member->processed_activities ?? [] as $activity)
                                        <option value="{{ $activity['id'] }}" data-duration="{{ $activity['duration_minutes'] }}">{{ $activity['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="mb-5 fv-row">
                                <label class="required form-label">
                                    <i class="ki-outline ki-calendar fs-6 me-1"></i>
                                    {{ trans('sw.date') }}
                                </label>
                                <input type="date" id="qb_date_{{ $member->id }}" class="form-control form-control-solid qb-date-input" data-member-id="{{ $member->id }}" min="{{ date('Y-m-d') }}" />
                                <div class="form-text">
                                    <i class="ki-outline ki-information-2 fs-7 me-1"></i>
                                    {{ trans('sw.select_date_for_slots') }}
                                </div>
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="mb-5 fv-row">
                                <label class="form-label">
                                    <i class="ki-outline ki-time fs-6 me-1"></i>
                                    {{ trans('sw.duration') }}
                                </label>
                                <select id="qb_duration_{{ $member->id }}" class="form-select form-select-solid qb-duration-select" data-member-id="{{ $member->id }}">
                                    <option value="30">30 {{ trans('sw.minutes') }}</option>
                                    <option value="45">45 {{ trans('sw.minutes') }}</option>
                                    <option value="60" selected>60 {{ trans('sw.minutes') }}</option>
                                    <option value="90">90 {{ trans('sw.minutes') }}</option>
                                    <option value="120">120 {{ trans('sw.minutes') }}</option>
                                </select>
                                <div class="form-text">
                                    <i class="ki-outline ki-information-2 fs-7 me-1"></i>
                                    {{ trans('sw.select_duration_help') }}
                                </div>
                            </div>
                            <!--end::Input group-->

                            <!--begin::Button-->
                            <div class="mb-5">
                                <button type="button" class="btn btn-light-primary w-100 qb-load-slots-btn" data-member-id="{{ $member->id }}">
                                    <i class="ki-outline ki-magnifier fs-2"></i>
                                    {{ trans('sw.show_available_slots') }}
                                </button>
                            </div>
                            <!--end::Button-->

                            <!--begin::Slots-->
                            <div id="qb_slots_{{ $member->id }}" class="mb-5">
                                <div class="slots-empty-state">
                                    <i class="ki-outline ki-calendar-tick"></i>
                                    <div class="empty-title">{{ trans('sw.select_activity_date_to_show_slots') }}</div>
                                    <div class="empty-subtitle">{{ trans('sw.choose_activity_and_date_first') }}</div>
                                </div>
                            </div>
                            <!--end::Slots-->

                            <!--begin::Input group-->
                            <div class="mb-5 fv-row">
                                <label class="form-label">
                                    <i class="ki-outline ki-note-text fs-6 me-1"></i>
                                    {{ trans('sw.notes') }}
                                </label>
                                <textarea id="qb_notes_{{ $member->id }}" class="form-control form-control-solid" rows="3" placeholder="{{ trans('sw.enter_notes_placeholder') }}"></textarea>
                                <div class="form-text">
                                    <i class="ki-outline ki-information-2 fs-7 me-1"></i>
                                    {{ trans('sw.notes_optional_help') }}
                                </div>
                            </div>
                            <!--end::Input group-->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('sw.cancel') }}</button>
                            <button type="button" class="btn btn-success qb-book-btn" data-member-id="{{ $member->id }}">
                                <i class="ki-outline ki-check-circle fs-2"></i>
                                <span class="qb-book-btn-text">{{ trans('sw.book_now') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
    <!--end::Quick Book Modal for Each Member-->
    
    <!--begin::Quick Booking Modal (General)-->
    <div class="modal fade" id="quickBookingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">{{ trans('sw.quick_booking') }}</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>
                <div class="modal-body">
                    <!--begin::Help Text-->
                    <div class="alert alert-light-info d-flex align-items-center p-4 mb-5">
                        <i class="ki-outline ki-information-5 fs-2x text-info me-3"></i>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-gray-800">{{ trans('sw.quick_booking_title') }}</span>
                            <span class="text-muted fs-7 mt-1">{{ trans('sw.quick_booking_description') }}</span>
                        </div>
                    </div>
                    <!--end::Help Text-->
                    
                    <!--begin::Input group-->
                    <div class="mb-5 fv-row">
                        <label class="required form-label">
                            <i class="ki-outline ki-gym fs-6 me-1"></i>
                            {{ trans('sw.activity') }}
                        </label>
                        <select id="qb_activity" class="form-select form-select-solid" data-control="select2" data-placeholder="{{ trans('sw.select_activity') }}">
                            <option value="">{{ trans('sw.select_activity') }}</option>
                            @foreach($activities ?? [] as $a)
                                <option value="{{ $a->id }}" data-duration="{{ $a->duration_minutes ?? 60 }}">{{ $a->{'name_'.($lang ?? 'ar')} ?? $a->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!--end::Input group-->

                    <!--begin::Input group-->
                    <div class="mb-5 fv-row">
                        <label class="required form-label">
                            <i class="ki-outline ki-calendar fs-6 me-1"></i>
                            {{ trans('sw.date') }}
                        </label>
                        <input type="date" id="qb_date" class="form-control form-control-solid" min="{{ date('Y-m-d') }}" />
                        <div class="form-text">
                            <i class="ki-outline ki-information-2 fs-7 me-1"></i>
                            {{ trans('sw.select_date_for_slots') }}
                        </div>
                    </div>
                    <!--end::Input group-->

                    <!--begin::Input group-->
                    <div class="mb-5 fv-row">
                        <label class="form-label">
                            <i class="ki-outline ki-time fs-6 me-1"></i>
                            {{ trans('sw.duration') }}
                        </label>
                        <select id="qb_duration" class="form-select form-select-solid">
                            <option value="30">30 {{ trans('sw.minutes') }}</option>
                            <option value="45">45 {{ trans('sw.minutes') }}</option>
                            <option value="60" selected>60 {{ trans('sw.minutes') }}</option>
                            <option value="90">90 {{ trans('sw.minutes') }}</option>
                            <option value="120">120 {{ trans('sw.minutes') }}</option>
                        </select>
                        <div class="form-text">
                            <i class="ki-outline ki-information-2 fs-7 me-1"></i>
                            {{ trans('sw.select_duration_help') }}
                        </div>
                    </div>
                    <!--end::Input group-->

                    <!--begin::Button-->
                    <div class="mb-5">
                        <button type="button" id="qb_load_slots" class="btn btn-light-primary w-100">
                            <i class="ki-outline ki-magnifier fs-2"></i>
                            {{ trans('sw.show_available_slots') }}
                        </button>
                    </div>
                    <!--end::Button-->

                    <!--begin::Slots-->
                    <div id="qb_slots" class="mb-5">
                        <div class="text-center text-muted py-5">
                            <i class="ki-outline ki-calendar-tick fs-3x text-muted mb-3"></i>
                            <div class="fs-7">{{ trans('sw.select_activity_date_to_show_slots') }}</div>
                        </div>
                    </div>
                    <!--end::Slots-->

                    <div class="separator separator-dashed my-7">
                        <span class="text-muted fs-7 fw-semibold">{{ trans('sw.client_details') }}</span>
                    </div>

                    <!--begin::Input group-->
                    <div class="mb-5 fv-row">
                        <label class="required form-label">
                            <i class="ki-outline ki-profile-user fs-6 me-1"></i>
                            {{ trans('sw.non_member') }}
                        </label>
                        <select id="qb_nonmember" class="form-select form-select-solid" 
                                data-control="select2" 
                                data-placeholder="{{ trans('sw.select_non_member') }}"
                                data-allow-clear="true"
                                data-search-enabled="true"
                                data-minimum-results-for-search="0">
                            <option value="">{{ trans('sw.select_non_member') }}</option>
                            @foreach($nonMembers ?? [] as $nm)
                                <option value="{{ $nm->id }}">{{ $nm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!--end::Input group-->

                    <!--begin::Input group-->
                    <div class="mb-5 fv-row">
                        <label class="form-label">
                            <i class="ki-outline ki-note-text fs-6 me-1"></i>
                            {{ trans('sw.notes') }}
                        </label>
                        <textarea id="qb_notes" class="form-control form-control-solid" rows="3" placeholder="{{ trans('sw.enter_notes_placeholder') }}"></textarea>
                        <div class="form-text">
                            <i class="ki-outline ki-information-2 fs-7 me-1"></i>
                            {{ trans('sw.notes_optional_help') }}
                        </div>
                    </div>
                    <!--end::Input group-->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ trans('admin.cancel') }}</button>
                    <button type="button" id="qb_book" class="btn btn-success">
                        <i class="ki-outline ki-check-circle fs-2"></i>
                        {{ trans('sw.book_now') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!--end::Quick Booking Modal-->
    @endif

    <!-- start modal pay non-member -->
    <div class="modal" id="modalPayNonMember">
        <div class="modal-dialog" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">{{ trans('sw.pay_remaining')}}</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                                aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h6 id="payNonMemberName" style="font-weight: bolder"></h6>
                    <p class="text-muted mb-3">{{ trans('sw.amount_remaining')}}: <span id="payNonMemberAmountRemaining" class="fw-bold text-primary"></span></p>
                    <div id="modalPayNonMemberResult"></div>
                    <form id="form_pay_nonmember" action="" method="GET">
                        <input type="hidden" name="pay_nonmember_id" id="pay_nonmember_id" value="">
                        <div class="row">
                            <div class="form-group col-lg-6">
                                <label class="form-label">{{ trans('sw.amount_paid')}}</label>
                                <input name="amount_paid_nonmember" class="form-control" type="number" id="amount_paid_nonmember" step="0.01"
                                       placeholder="{{ trans('sw.enter_amount_paid')}}">
                            </div>
                            <div class="form-group col-lg-6">
                                <label class="form-label">{{ trans('sw.payment_type')}}</label>
                                <select class="form-control" name="payment_type_nonmember" id="payment_type_nonmember">
                                    @foreach($payment_types as $payment_type)
                                        <option value="{{$payment_type->payment_id}}">{{$payment_type->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <br/>
                        <button class="btn ripple btn-primary rounded-3" id="form_pay_nonmember_btn"
                                type="submit">{{ trans('sw.pay')}}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- end modal pay non-member -->

@section('scripts')
    @parent
    <script src="{{asset('resources/assets/new_front/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"
            type="text/javascript"></script>
    <script>
        $(document).on('click', '#export', function (event) {
            event.preventDefault();
            $.ajax({
                url: $(this).attr('url'),
                cache: false,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    var a = document.createElement("a");
                    a.href = response.file;
                    a.download = response.name;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                },
                error: function (request, error) {
                    Swal.fire({
                        title: '{{ trans('sw.error') }}',
                        text: 'Something went wrong.',
                        icon: 'error',
                        confirmButtonText: 'Ok'
                    });
                    console.error("Request: " + JSON.stringify(request));
                    console.error("Error: " + JSON.stringify(error));
                }
            });
        });

        $("#filter_form").slideUp();
        $(".filter_trigger_button").click(function () {
            $("#filter_form").slideToggle(300);
        });

        $(document).on('click', '.remove_filter', function (event) {
            event.preventDefault();
            var filter = $(this).attr('id');
            $("#" + filter).val('');
            $("#form_filter").submit();
        });

        jQuery(document).ready(function () {
            var today = new Date();
            $('.input-daterange').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                clearBtn: true,
                orientation: 'bottom auto',
                defaultDate: { year: today.getFullYear(), month: today.getMonth(), day: today.getDate() },
                defaultViewDate: { year: today.getFullYear(), month: today.getMonth(), day: today.getDate() }
            });

            $('button[type="reset"]').on('click', function() {
                setTimeout(() => {
                    $(this).closest('form').find('select').trigger('change');
                }, 100);
            });
        });

        // Non-member pay remaining amount
        $('.btn-pay-nonmember').off('click').on('click', function (e) {
            var that = $(this);
            var id = that.data('id');
            var name = that.data('name');
            var amount = that.data('amount');
            $('#modalPayNonMemberResult').hide();
            $('#amount_paid_nonmember').val('');
            $('#pay_nonmember_id').val(id);
            $('#payNonMemberName').text(name);
            $('#payNonMemberAmountRemaining').text(parseFloat(amount).toFixed(2));
        });

        $(document).on('click', '#form_pay_nonmember_btn', function (event) {
            event.preventDefault();
            let id = $('#pay_nonmember_id').val();
            let amount_paid = $('#amount_paid_nonmember').val();
            let payment_type = $('#payment_type_nonmember').val();
            $('#modalPayNonMemberResult').show();
            $.ajax({
                url: '{{route('sw.createNonMemberPayAmountRemainingForm')}}',
                cache: false,
                type: 'GET',
                dataType: 'text',
                data: {id: id, amount_paid: amount_paid, payment_type: payment_type},
                success: function (response) {
                    if (response == '1') {
                        $('#modalPayNonMemberResult').html('<div class="alert alert-success">{{ trans('admin.successfully_paid')}}</div>');
                        setTimeout(function(){
                            location.reload();
                        }, 1500);
                    } else {
                        $('#modalPayNonMemberResult').html('<div class="alert alert-danger">' + response + '</div>');
                    }
                },
                error: function (request, error) {
                    Swal.fire({
                        title: '{{ trans('sw.error') }}',
                        text: 'Something went wrong.',
                        icon: 'error',
                        confirmButtonText: 'Ok'
                    });
                    console.error("Request: " + JSON.stringify(request));
                    console.error("Error: " + JSON.stringify(error));
                }
            });
        });
    </script>

@if($active_activity_reservation)
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
/* Time Slots Styling */
.time-slots-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 0.75rem;
    padding: 1rem 0;
}

.slot-btn {
    min-width: 140px;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    font-weight: 600;
    border-radius: 0.65rem;
    transition: all 0.3s ease;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    text-align: center;
    border: 2px solid;
    background: transparent;
}

.slot-btn i {
    font-size: 1.1rem;
}

/* Available Slot */
.slot-free {
    border-color: #50cd89;
    color: #50cd89;
    background-color: rgba(80, 205, 137, 0.08);
}

.slot-free:hover {
    background-color: rgba(80, 205, 137, 0.15);
    border-color: #47b875;
    color: #47b875;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(80, 205, 137, 0.2);
}

.slot-free.active {
    background: linear-gradient(135deg, #50cd89 0%, #47b875 100%);
    color: #ffffff;
    border-color: #47b875;
    box-shadow: 0 4px 16px rgba(80, 205, 137, 0.4);
    transform: translateY(-2px);
}

.slot-free.active::before {
    content: "\2713";
    margin-left: -0.5rem;
    font-weight: bold;
}

/* Busy/Occupied Slot */
.slot-busy {
    border-color: #e4e6ef;
    color: #a1a5b7;
    background-color: #f5f8fa;
    cursor: not-allowed;
    opacity: 0.65;
    position: relative;
}

.slot-busy::after {
    content: "";
    position: absolute;
    top: 50%;
    left: 10%;
    right: 10%;
    height: 2px;
    background: #a1a5b7;
    transform: rotate(-5deg);
}

/* Empty State */
.slots-empty-state {
    text-align: center;
    padding: 3rem 1rem;
    background: linear-gradient(135deg, #f5f8fa 0%, #ffffff 100%);
    border-radius: 0.65rem;
    border: 2px dashed #e4e6ef;
}

.slots-empty-state i {
    font-size: 4rem;
    color: #e4e6ef;
    margin-bottom: 1rem;
    display: block;
}

.slots-empty-state .empty-title {
    font-size: 1rem;
    font-weight: 600;
    color: #5e6278;
    margin-bottom: 0.5rem;
}

.slots-empty-state .empty-subtitle {
    font-size: 0.875rem;
    color: #a1a5b7;
}

/* Error State */
.slots-error-state {
    text-align: center;
    padding: 3rem 1rem;
    background: linear-gradient(135deg, #fff5f8 0%, #ffffff 100%);
    border-radius: 0.65rem;
    border: 2px solid #f1416c;
}

.slots-error-state i {
    font-size: 4rem;
    color: #f1416c;
    margin-bottom: 1rem;
    display: block;
}

.slots-error-state .error-title {
    font-size: 1rem;
    font-weight: 600;
    color: #f1416c;
    margin-bottom: 0.5rem;
}

.slots-error-state .error-subtitle {
    font-size: 0.875rem;
    color: #a1a5b7;
}

/* Loading State */
.slots-loading-state {
    text-align: center;
    padding: 3rem 1rem;
}

.slots-loading-state .spinner-border {
    width: 3rem;
    height: 3rem;
    border-width: 0.3rem;
    color: #50cd89;
}

/* Responsive */
@media (max-width: 768px) {
    .time-slots-container {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 0.5rem;
    }
    
    .slot-btn {
        min-width: 120px;
        padding: 0.625rem 0.875rem;
        font-size: 0.85rem;
    }
}
</style>

<script>
// Move event handlers outside DOMContentLoaded to ensure they work with dynamically loaded modals
$(document).ready(function() {
    // Initialize Select2 for quick booking activity dropdown
    $('#qb_activity').select2({
        placeholder: '{{ trans('sw.select_activity') }}',
        allowClear: true,
        minimumResultsForSearch: 0,
        language: {
            searching: function() {
                return '{{ trans('sw.searching') }}...';
            },
            noResults: function() {
                return '{{ trans('sw.no_results_found') }}';
            }
        }
    });

    // Initialize Select2 for quick booking non-member dropdown
    $('#qb_nonmember').select2({
        placeholder: '{{ trans('sw.select_non_member') }}',
        allowClear: true,
        minimumResultsForSearch: 0,
        language: {
            searching: function() {
                return '{{ trans('sw.searching') }}...';
            },
            noResults: function() {
                return '{{ trans('sw.no_results_found') }}';
            }
        }
    });

    // Load slots in booking panel
    $('#qb_load_slots').on('click', function(){
        const activity_id = $('#qb_activity').val();
        const date = $('#qb_date').val();
        const duration = $('#qb_duration').val();

        if(!activity_id || !date) {
            Swal.fire({
                title: '{{ trans('sw.error') }}',
                text: '{{ trans('sw.select_activity_date_first') }}',
                icon: 'error',
                confirmButtonText: 'Ok'
            });
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true).html('<i class="ki-outline ki-loading fs-2"></i> {{ trans('sw.loading') }}...');
        $('#qb_slots').html('<div class="col-12 text-center py-5"><span class="spinner-border spinner-border-sm"></span> {{ trans('sw.loading') }}...</div>');

        fetch("{{ route('sw.reservation.slots') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                activity_id: activity_id, 
                reservation_date: date, 
                duration: duration
            })
        })
        .then(r => r.json())
        .then(resp => {
            btn.prop('disabled', false).html('<i class="ki-outline ki-magnifier fs-2"></i> {{ trans('sw.show_available_slots') }}');
            $('#qb_slots').empty();
            
            if (resp.slots && resp.slots.length > 0) {
                const slotsContainer = $('<div class="row g-2"></div>');
                resp.slots.forEach(function(slot){
                    const col = $('<div class="col-auto mb-2"></div>');
                    if(slot.available){
                        col.html(`<button type="button" class="btn btn-outline-success slot-btn slot-free qb-select-slot" data-start="${slot.start_time}" data-end="${slot.end_time}">${slot.start_time} - ${slot.end_time}</button>`);
                    } else {
                        col.html(`<button type="button" class="btn slot-btn slot-busy" disabled>${slot.start_time} - ${slot.end_time}</button>`);
                    }
                    slotsContainer.append(col);
                });
                $('#qb_slots').html(slotsContainer);
            } else {
                $('#qb_slots').html(`
                    <div class="text-center text-muted py-5">
                        <i class="ki-outline ki-information-5 fs-3x text-muted mb-3"></i>
                        <div class="fs-7 fw-semibold">{{ trans('sw.no_slots_available') }}</div>
                        <div class="fs-8 mt-2">{{ trans('sw.try_different_date') }}</div>
                    </div>
                `);
            }
        })
        .catch(() => {
            btn.prop('disabled', false).html('<i class="ki-outline ki-magnifier fs-2"></i> {{ trans('sw.show_available_slots') }}');
            $('#qb_slots').html(`
                <div class="text-center text-danger py-5">
                    <i class="ki-outline ki-cross-circle fs-3x text-danger mb-3"></i>
                    <div class="fs-7 fw-semibold">{{ trans('sw.error_loading_slots') }}</div>
                </div>
            `);
        });
    });

    // Choose slot
    $(document).on('click', '.qb-select-slot', function(){
        $('.qb-select-slot').removeClass('active');
        $(this).addClass('active');
    });

    // Book now
    $('#qb_book').on('click', function(){
        const activity_id = $('#qb_activity').val();
        const date = $('#qb_date').val();
        const selected = $('.qb-select-slot.active');
        
        if(!activity_id || !date) {
            Swal.fire({
                title: '{{ trans('sw.error') }}',
                text: '{{ trans('sw.select_activity_date_first') }}',
                icon: 'error',
                confirmButtonText: 'Ok'
            });
            return;
        }
        
        if(selected.length === 0) {
            Swal.fire({
                title: '{{ trans('sw.error') }}',
                text: '{{ trans('sw.select_slot') }}',
                icon: 'error',
                confirmButtonText: 'Ok'
            });
            return;
        }

        const non_member_id = $('#qb_nonmember').val();
        if(!non_member_id) {
            Swal.fire({
                title: '{{ trans('sw.error') }}',
                text: '{{ trans('sw.please_select_non_member') }}',
                icon: 'error',
                confirmButtonText: 'Ok'
            });
            return;
        }
        
        const start_time = selected.data('start');
        const end_time = selected.data('end');
        const notes = $('#qb_notes').val();

        const payload = {
            client_type: 'non_member',
            member_id: null,
            non_member_id: non_member_id,
            activity_id: activity_id,
            reservation_date: date,
            start_time: start_time,
            end_time: end_time,
            notes: notes
        };

        const btn = $(this);
        btn.prop('disabled', true).html('<i class="ki-outline ki-loading fs-2"></i> {{ trans('sw.booking') }}...');

        fetch("{{ route('sw.reservation.ajaxCreate') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(async r => {
            if(r.status === 422){
                const j = await r.json();
                btn.prop('disabled', false).html('<i class="ki-outline ki-check-circle fs-2"></i> {{ trans('sw.book_now') }}');
                Swal.fire({
                    title: '{{ trans('sw.error') }}',
                    text: j.message || '{{ trans('sw.slot_conflict') }}',
                    icon: 'error',
                    confirmButtonText: 'Ok'
                });
                return;
            }
            return r.json();
        })
        .then(res => {
            if(res && res.success){
                Swal.fire({
                    title: '{{ trans('admin.done') }}',
                    text: '{{ trans('sw.reservation_created') }}',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    $('#quickBookingModal').modal('hide');
                                            location.reload();
                });
                                        }
        })
        .catch(() => {
            btn.prop('disabled', false).html('<i class="ki-outline ki-check-circle fs-2"></i> {{ trans('sw.book_now') }}');
                                        Swal.fire({
                title: '{{ trans('sw.error') }}',
                text: '{{ trans('sw.booking_failed') }}',
                icon: 'error',
                confirmButtonText: 'Ok'
            });
        });
    });

    // Reset modal when closed
    $('#quickBookingModal').on('hidden.bs.modal', function () {
        $('#qb_activity').val(null).trigger('change');
        $('#qb_date').val('');
        $('#qb_duration').val('60');
        $('#qb_nonmember').val(null).trigger('change');
        $('#qb_notes').val('');
        $('#qb_slots').html(`
            <div class="text-center text-muted py-5">
                <i class="ki-outline ki-calendar-tick fs-3x text-muted mb-3"></i>
                <div class="fs-7">{{ trans('sw.select_activity_date_to_show_slots') }}</div>
            </div>
        `);
        $('.qb-select-slot').removeClass('active');
    });

// Function to open quick book modal for specific member
function openQuickBookModal(memberId, activities) {
    console.log('Opening modal for member:', memberId);
    // Wait for modal to be shown, then initialize Select2
    setTimeout(function() {
        const select = $(`#qb_activity_${memberId}`);
        if (select.length === 0) {
            console.error('Select element not found for member:', memberId);
            return;
        }
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.select2({
            placeholder: '{{ trans('sw.select_activity') }}',
            allowClear: true,
            minimumResultsForSearch: 0,
            dropdownParent: $(`#quickBookModal${memberId}`),
            language: {
                searching: function() {
                    return '{{ trans('sw.searching') }}...';
                },
                noResults: function() {
                    return '{{ trans('sw.no_results_found') }}';
                }
            }
        });
        console.log('Select2 initialized for member:', memberId);
    }, 300);
}

});

// Load slots for specific member modal - MUST be outside DOMContentLoaded
$(document).on('click', '.qb-load-slots-btn', function(e){
    e.preventDefault();
    e.stopPropagation();
    
    const memberId = $(this).data('member-id');
    console.log('Button clicked! Loading slots for member:', memberId);
    
    // Get values - handle Select2
    let activity_id;
    const activitySelect = $(`#qb_activity_${memberId}`);
    console.log('Activity select element:', activitySelect.length, activitySelect);
    
    if (activitySelect.length === 0) {
        console.error('Activity select not found for member:', memberId);
        Swal.fire({
            title: '{{ trans('sw.error') }}',
            text: 'Activity select not found',
            icon: 'error',
            confirmButtonText: 'Ok'
        });
                                return false;
    }
    
    if (activitySelect.hasClass('select2-hidden-accessible')) {
        activity_id = activitySelect.select2('val');
                            } else {
        activity_id = activitySelect.val();
    }
    
    const date = $(`#qb_date_${memberId}`).val();
    const duration = $(`#qb_duration_${memberId}`).val();
    
    console.log('Form values:', {activity_id, date, duration});

        if(!activity_id || !date) {
            swal({
                title: '{{ trans('sw.error') }}',
                text: '{{ trans('sw.select_activity_date_first') }}',
                type: 'error',
                confirmButtonText: 'Ok'
            });
            return false;
        }

        const btn = $(this);
        const originalHtml = btn.html();
        btn.prop('disabled', true).html('<i class="ki-outline ki-loading fs-2"></i> {{ trans('sw.loading') }}...');
        $(`#qb_slots_${memberId}`).html(`
            <div class="slots-loading-state">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">{{ trans('sw.loading') }}...</span>
                </div>
                <div class="text-muted mt-3 fw-semibold">{{ trans('sw.loading_slots') }}...</div>
            </div>
        `);
        
        console.log('Sending request to:', "{{ route('sw.reservation.slots') }}");

            $.ajax({
            url: "{{ route('sw.reservation.slots') }}",
            type: 'POST',
                dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                activity_id: activity_id, 
                reservation_date: date, 
                duration: duration
            },
            success: function(resp) {
                console.log('Response received:', resp);
                btn.prop('disabled', false).html('<i class="ki-outline ki-magnifier fs-2"></i> {{ trans('sw.show_available_slots') }}');
                $(`#qb_slots_${memberId}`).empty();
            
                // Check if day is available
                if (resp.day_available === false) {
                    $(`#qb_slots_${memberId}`).html(`
                        <div class="slots-empty-state">
                            <i class="ki-outline ki-calendar-tick"></i>
                            <div class="empty-title">{{ trans('sw.day_not_available_for_reservation') }}</div>
                            <div class="empty-subtitle">{{ trans('sw.please_select_different_date') }}</div>
                        </div>
                    `);
                    return;
                }

                if (resp.slots && resp.slots.length > 0) {
                    const slotsContainer = $('<div class="time-slots-container"></div>');
                    let availableCount = 0;
                    let occupiedCount = 0;
                    
                    resp.slots.forEach(function(slot){
                        const slotBtn = $('<button type="button" class="slot-btn"></button>');
                        const hasLimit = resp.has_limit || false;
                        const limit = resp.reservation_limit || 0;
                        const current = slot.current_bookings || 0;
                        const remaining = slot.remaining_slots;
                        
                        // Build time text with capacity info if limit exists
                        let timeText = `<span><i class="ki-outline ki-time fs-6"></i> ${slot.start_time} - ${slot.end_time}</span>`;
                        
                        if (hasLimit && slot.available) {
                            // Show remaining slots info
                            timeText += `<small class="d-block mt-1" style="font-size: 0.75rem; opacity: 0.8;">
                                ${remaining > 0 ? remaining + ' {{ trans("sw.slots_remaining") }}' : '{{ trans("sw.last_slot") }}'}
                            </small>`;
                        } else if (hasLimit && !slot.available) {
                            // Show limit reached
                            timeText += `<small class="d-block mt-1" style="font-size: 0.75rem; opacity: 0.8;">
                                {{ trans("sw.limit_reached") }} (${current}/${limit})
                            </small>`;
                        }
                        
                        if(slot.available){
                            availableCount++;
                            slotBtn.addClass('slot-free qb-select-slot-member')
                                   .attr('data-start', slot.start_time)
                                   .attr('data-end', slot.end_time)
                                   .attr('data-member-id', memberId)
                                   .html(timeText);
                        } else {
                            occupiedCount++;
                            slotBtn.addClass('slot-busy')
                                   .prop('disabled', true)
                                   .html(timeText);
                        }
                        
                        slotsContainer.append(slotBtn);
                    });
                    
                    // Add summary header with capacity info
                    const summaryHtml = resp.has_limit 
                        ? `
                            <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-light-primary rounded">
                                <div class="d-flex align-items-center gap-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge badge-circle badge-light-success"></span>
                                        <span class="text-gray-700 fw-semibold">{{ trans('sw.available') }}: ${availableCount}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge badge-circle badge-light-secondary"></span>
                                        <span class="text-gray-700 fw-semibold">{{ trans('sw.occupied') }}: ${occupiedCount}</span>
                                    </div>
                                </div>
                                <div class="text-gray-600 fw-semibold">
                                    <i class="ki-outline ki-user fs-6 me-1"></i>
                                    {{ trans('sw.reservation_limit') }}: ${resp.reservation_limit}
                                </div>
                            </div>
                        `
                        : `
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge badge-circle badge-light-success"></span>
                                        <span class="text-gray-700 fw-semibold">{{ trans('sw.available') }}: ${availableCount}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge badge-circle badge-light-secondary"></span>
                                        <span class="text-gray-700 fw-semibold">{{ trans('sw.occupied') }}: ${occupiedCount}</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    
                    const summary = $(summaryHtml);
                    $(`#qb_slots_${memberId}`).append(summary).append(slotsContainer);
                    
                    // If editing a reservation, auto-select the matching time slot
                    const currentReservationId = $(`#qb_reservation_id_${memberId}`).val();
                    if (currentReservationId) {
                        // Get reservation times from modal data attributes
                        const reservationStartTime = $(`#quickBookModal${memberId}`).data('reservation-start-time');
                        const reservationEndTime = $(`#quickBookModal${memberId}`).data('reservation-end-time');
                        
                        if (reservationStartTime && reservationEndTime) {
                            // Small delay to ensure DOM is fully rendered
                            setTimeout(function() {
                                const matchingSlot = $(`.qb-select-slot-member[data-member-id="${memberId}"][data-start="${reservationStartTime}"][data-end="${reservationEndTime}"]`);
                                if (matchingSlot.length > 0) {
                                    // Remove active class from all slots first
                                    $(`.qb-select-slot-member[data-member-id="${memberId}"]`).removeClass('active');
                                    // Add active class and click the matching slot
                                    matchingSlot.first().addClass('active').click();
                                }
                            }, 100);
                        }
                    }
                } else {
                    $(`#qb_slots_${memberId}`).html(`
                        <div class="slots-empty-state">
                            <i class="ki-outline ki-calendar-tick"></i>
                            <div class="empty-title">{{ trans('sw.no_slots_available') }}</div>
                            <div class="empty-subtitle">{{ trans('sw.try_different_date') }}</div>
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error loading slots:', {xhr, status, error});
                btn.prop('disabled', false).html('<i class="ki-outline ki-magnifier fs-2"></i> {{ trans('sw.show_available_slots') }}');
                let errorMsg = '{{ trans('sw.error_loading_slots') }}';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                $(`#qb_slots_${memberId}`).html(`
                    <div class="slots-error-state">
                        <i class="ki-outline ki-cross-circle"></i>
                        <div class="error-title">${errorMsg}</div>
                        <div class="error-subtitle">${error || '{{ trans('sw.please_try_again') }}'}</div>
                    </div>
                `);
            }
        });
});

// Choose slot for member modal
$(document).on('click', '.qb-select-slot-member', function(){
        const memberId = $(this).data('member-id');
        $(`.qb-select-slot-member[data-member-id="${memberId}"]`).removeClass('active');
        $(this).addClass('active');
});

// Book now for specific member (create or update)
$(document).on('click', '.qb-book-btn', function(){
    const memberId = $(this).data('member-id');
    const reservationId = $(`#qb_reservation_id_${memberId}`).val();
    const activity_id = $(`#qb_activity_${memberId}`).val();
    const date = $(`#qb_date_${memberId}`).val();
    const selected = $(`.qb-select-slot-member[data-member-id="${memberId}"].active`);
    const non_member_id = $(`#qb_nonmember_id_${memberId}`).val();
    
    if(!activity_id || !date) {
        swal({
            title: '{{ trans('sw.error') }}',
            text: '{{ trans('sw.select_activity_date_first') }}',
            type: 'error',
            confirmButtonText: 'Ok'
        });
        return;
    }
    
    if(selected.length === 0) {
        swal({
            title: '{{ trans('sw.error') }}',
            text: '{{ trans('sw.select_slot') }}',
            type: 'error',
            confirmButtonText: 'Ok'
        });
        return;
    }
    
    const start_time = selected.data('start');
    const end_time = selected.data('end');
    const notes = $(`#qb_notes_${memberId}`).val();

    const payload = {
        client_type: 'non_member',
        member_id: null,
        non_member_id: non_member_id,
        activity_id: activity_id,
        reservation_date: date,
        start_time: start_time,
        end_time: end_time,
        notes: notes
    };

    const btn = $(this);
    const btnText = btn.find('.qb-book-btn-text');
    const isUpdate = reservationId && reservationId !== '';
    const url = isUpdate 
        ? "{{ route('sw.reservation.ajaxUpdate', ':id') }}".replace(':id', reservationId)
        : "{{ route('sw.reservation.ajaxCreate') }}";
    
    btn.prop('disabled', true);
    btnText.text('{{ trans('sw.booking') }}...');

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    })
    .then(async r => {
        if(r.status === 422){
            const j = await r.json();
            btn.prop('disabled', false);
            btnText.text(isUpdate ? '{{ trans('sw.update') }}' : '{{ trans('sw.book_now') }}');
                Swal.fire({
                    title: '{{ trans('sw.error') }}',
                    text: j.message || '{{ trans('sw.slot_conflict') }}',
                    icon: 'error',
                    confirmButtonText: 'Ok'
                });
            return;
        }
        return r.json();
    })
    .then(res => {
        if(res && res.success){
            // Close modal immediately after successful reservation
            $(`#quickBookModal${memberId}`).modal('hide');
            
            Swal.fire({
                title: '{{ trans('admin.done') }}',
                text: isUpdate ? '{{ trans('admin.successfully_edited') }}' : '{{ trans('sw.reservation_created') }}',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        }
    })
    .catch(() => {
        btn.prop('disabled', false);
        btnText.text(isUpdate ? '{{ trans('sw.update') }}' : '{{ trans('sw.book_now') }}');
        swal({
            title: '{{ trans('sw.error') }}',
            text: '{{ trans('sw.booking_failed') }}',
            type: 'error',
            confirmButtonText: 'Ok'
        });
    });
});

// Store initial status when page loads and when modal opens
$(document).ready(function(){
    // Store initial values for all status selects
    $('.reservation-status-select').each(function(){
        const currentVal = $(this).val();
        if (!$(this).data('old-value')) {
            $(this).data('old-value', currentVal);
        }
    });
    
    // Re-store values when modal is shown
    $('[id^="upcomingReservationsModal"]').on('shown.bs.modal', function(){
        $(this).find('.reservation-status-select').each(function(){
            const select = $(this);
            const currentVal = select.val();
            const oldValueAttr = select.attr('data-old-value');
            
            // Use attribute value if available, otherwise use current value
            if (oldValueAttr) {
                select.data('old-value', oldValueAttr);
            } else {
                select.data('old-value', currentVal);
            }
            
            console.log('Modal opened - stored old-value:', select.data('old-value'), 'for reservation:', select.data('reservation-id'));
        });
    });
});

// Change reservation status in upcoming reservations modal
$(document).on('change', '.reservation-status-select', function(e){
    console.log('=== STATUS SELECT CHANGE EVENT TRIGGERED ===');
    e.preventDefault();
    e.stopPropagation();
    
    const reservationId = $(this).data('reservation-id');
    const select = $(this);
    const newStatus = select.val();
    let oldValue = select.data('old-value') || select.attr('data-old-value');
    
    console.log('Initial values:', { reservationId, newStatus, oldValue });
    
    // If old-value is not set, get it from the select's initial value
    if (!oldValue) {
        oldValue = select.find('option[selected]').val() || select.val();
        select.data('old-value', oldValue);
        console.log('Old value not found, using:', oldValue);
    }
    
    console.log('Status changed:', { reservationId, newStatus, oldValue, selectElement: select });
    
    // If status didn't change, do nothing
    if (newStatus === oldValue) {
        console.log('Status unchanged, ignoring');
        select.val(oldValue); // Revert to old value
        return;
    }
    
    console.log('Showing confirmation dialog...');
    
    // Show confirmation dialog with Yes/No buttons using SweetAlert2
    Swal.fire({
        title: '{{ trans('admin.are_you_sure') }}',
        text: '{{ trans('sw.change_status_confirmation') }}',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DD6B55',
        confirmButtonText: '{{ trans('admin.yes') }}',
        cancelButtonText: '{{ trans('sw.no') }}',
        allowOutsideClick: false,
        reverseButtons: true
    }).then((result) => {
        console.log('Confirmation result:', result);
        if (!result.isConfirmed) {
            // User cancelled, revert to old value
            console.log('User cancelled, reverting to:', oldValue);
            select.val(oldValue);
            return;
        }
        
        // Determine which action to use based on new status
        let url = '';
        
        if (newStatus === 'confirmed') {
            url = "{{ route('sw.reservation.confirm', ':id') }}".replace(':id', reservationId);
        } else if (newStatus === 'cancelled') {
            url = "{{ route('sw.reservation.cancel', ':id') }}".replace(':id', reservationId);
        } else if (newStatus === 'attended') {
            url = "{{ route('sw.reservation.attend', ':id') }}".replace(':id', reservationId);
        } else if (newStatus === 'missed') {
            url = "{{ route('sw.reservation.missed', ':id') }}".replace(':id', reservationId);
        } else if (newStatus === 'pending') {
            // Revert to old value
            select.val(oldValue);
            Swal.fire({
                title: '{{ trans('admin.info') }}',
                text: '{{ trans('sw.pending_status_not_supported') }}',
                icon: 'info',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
        
        if (!url) {
            select.val(oldValue);
            Swal.fire({
                title: '{{ trans('sw.error') }}',
                text: '{{ trans('sw.invalid_status') }}',
                icon: 'error',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
        
        // Disable select during request
        select.prop('disabled', true);
        
        console.log('Sending AJAX request to:', url);
        
        $.ajax({
            url: url,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function(response){
                console.log('AJAX success response:', response);
                
                if(response && response.success && response.status){
                    // Update old value to new status
                    select.data('old-value', response.status);
                    
                    // Update select value to match new status
                    select.val(response.status);
                    
                    // Update badge color if badge exists
                    const card = select.closest('.card');
                    if (card.length) {
                        const badge = card.find('.badge').first();
                        if (badge.length) {
                            const colors = {
                                'confirmed': 'success',
                                'pending': 'warning',
                                'cancelled': 'danger',
                                'attended': 'primary',
                                'missed': 'secondary'
                            };
                            badge.removeClass('badge-success badge-warning badge-danger badge-primary badge-secondary badge-dark')
                                  .addClass('badge-' + (colors[response.status] || 'dark'));
                        }
                    }
                    
                    // Show success message and close modal
                    Swal.fire({
                        title: '{{ trans('admin.done') }}',
                        text: '{{ trans('sw.status_changed_successfully') }}',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // Close the upcoming reservations modal
                        const modalId = select.closest('[id^="upcomingReservationsModal"]').attr('id');
                        if (modalId) {
                            $('#' + modalId).modal('hide');
                        }
                    });
                } else {
                    // Revert to old value
                    select.val(oldValue);
                    Swal.fire({
                        title: '{{ trans('sw.error') }}',
                        text: response.message || '{{ trans('sw.status_change_failed') }}',
                        icon: 'error',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr, status, error){
                console.error('AJAX error:', { xhr, status, error });
                // Revert to old value
                select.val(oldValue);
                
                let errorMsg = '{{ trans('sw.status_change_failed') }}';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    title: '{{ trans('sw.error') }}',
                    text: errorMsg,
                    icon: 'error',
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            complete: function(){
                select.prop('disabled', false);
            }
        });
    });
});

// Edit reservation button - opens quick modal with reservation data
$(document).on('click', '.reservation-edit-btn', function(){
    const reservationId = $(this).data('reservation-id');
    const memberId = $(this).data('member-id');
    
    // Close upcoming reservations modal
    $(`#upcomingReservationsModal${memberId}`).modal('hide');
    
    // Fetch reservation data
    $.ajax({
        url: "{{ route('sw.reservation.ajaxGet', ':id') }}".replace(':id', reservationId),
        type: 'GET',
        success: function(res){
            if(res.success && res.data){
                const data = res.data;
                
                // Set reservation ID
                $(`#qb_reservation_id_${memberId}`).val(data.id);
                
                // Populate form fields
                $(`#qb_activity_${memberId}`).val(data.activity_id).trigger('change');
                $(`#qb_date_${memberId}`).val(data.reservation_date);
                
                // Calculate duration from start and end time
                const start = data.start_time.split(':');
                const end = data.end_time.split(':');
                const startMinutes = parseInt(start[0]) * 60 + parseInt(start[1]);
                const endMinutes = parseInt(end[0]) * 60 + parseInt(end[1]);
                const duration = endMinutes - startMinutes;
                $(`#qb_duration_${memberId}`).val(duration);
                
                $(`#qb_notes_${memberId}`).val(data.notes || '');
                
                // Update button text
                $(`#quickBookModal${memberId} .qb-book-btn-text`).text('{{ trans('sw.update') }}');
                
                // Store reservation time in modal data attributes for slot selection after loading
                $(`#quickBookModal${memberId}`).data('reservation-start-time', data.start_time);
                $(`#quickBookModal${memberId}`).data('reservation-end-time', data.end_time);
                
                // Open quick modal first
                $(`#quickBookModal${memberId}`).modal('show');
                
                // Wait for modal to be fully shown, then automatically load slots
                $(`#quickBookModal${memberId}`).one('shown.bs.modal', function() {
                    // Trigger slots loading after a short delay to ensure select2 is ready
                    setTimeout(function(){
                        // Click load slots button
                        $(`#quickBookModal${memberId} .qb-load-slots-btn`).click();
                    }, 300);
                });
            }
        },
        error: function(){
            Swal.fire({
                title: '{{ trans('sw.error') }}',
                text: '{{ trans('sw.failed_to_load_reservation') }}',
                icon: 'error',
                confirmButtonText: 'Ok'
            });
        }
    });
});

// Reset member modal when closed
@foreach($members as $member)
        @if(!empty($member->activities))
        $('#quickBookModal{{ $member->id }}').on('hidden.bs.modal', function () {
            $('#qb_reservation_id_{{ $member->id }}').val('');
            $('#qb_activity_{{ $member->id }}').val(null).trigger('change');
            $('#qb_date_{{ $member->id }}').val('');
            $('#qb_duration_{{ $member->id }}').val('60');
            $('#qb_notes_{{ $member->id }}').val('');
            $('#qb_slots_{{ $member->id }}').html(`
                <div class="slots-empty-state">
                    <i class="ki-outline ki-calendar-tick"></i>
                    <div class="empty-title">{{ trans('sw.select_activity_date_to_show_slots') }}</div>
                    <div class="empty-subtitle">{{ trans('sw.choose_activity_and_date_first') }}</div>
                </div>
            `);
            $(`.qb-select-slot-member[data-member-id="{{ $member->id }}"]`).removeClass('active');
            $(`#quickBookModal{{ $member->id }} .qb-book-btn-text`).text('{{ trans('sw.book_now') }}');
        });
        @endif
    @endforeach

// Auto-update duration when activity is selected
$(document).on('change', '.qb-activity-select', function(){
    const memberId = $(this).data('member-id');
    const selectedOption = $(this).find('option:selected');
    const duration = selectedOption.data('duration');
    if (duration) {
        $(`#qb_duration_${memberId}`).val(duration);
    }
});
    </script>
@endif
@endsection



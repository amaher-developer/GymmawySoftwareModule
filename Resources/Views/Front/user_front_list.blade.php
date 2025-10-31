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
            width: 48px !important;
            height: 48px !important;
            font-size: 24px !important;
        }

        .rounded-circle {
            border-radius: 50% !important;
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

<!--begin::Users-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <!--begin::Card title-->
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-user fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title}}</span>    
            </div>
        </div>
        <!--end::Card title-->
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
            <div class="d-flex align-items-center gap-2 gap-lg-3 flex-wrap">
                <!--begin::Employee Transactions-->
                @if(in_array('listUserTransaction', (array)$swUser->permissions) || $swUser->is_super_user)
                    <a href="{{route('sw.listUserTransaction')}}" class="btn btn-sm btn-flex btn-light-success">
                        <i class="ki-outline ki-dollar fs-6"></i>
                        {{ trans('sw.employee_transactions')}}
                    </a>
                @endif
                <!--end::Employee Transactions-->
                
                <!--begin::Permission Groups-->
                @if(in_array('listUserPermission', (array)$swUser->permissions) || $swUser->is_super_user)
                    <a href="{{route('sw.listUserPermission')}}" class="btn btn-sm btn-flex btn-light-info">
                        <i class="ki-outline ki-shield-tick fs-6"></i>
                        {{ trans('sw.permission_groups')}}
                    </a>
                @endif
                <!--end::Permission Groups-->
                
                <!--begin::Add User-->
                @if(in_array('createUser', (array)$swUser->permissions) || $swUser->is_super_user)
                    <a href="{{route('sw.createUser')}}" class="btn btn-sm btn-flex btn-light-primary">
                        <i class="ki-outline ki-plus fs-6"></i>
                        {{ trans('admin.add')}}
                    </a>
                @endif
                <!--end::Add User-->
                
                <!--begin::Export-->
                @if((count(array_intersect(@(array)$swUser->permissions, ['exportUserPDF', 'exportUserExcel'])) > 0) || $swUser->is_super_user)
                    <div class="m-0">
                        <button class="btn btn-sm btn-flex btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            <i class="ki-outline ki-exit-down fs-6"></i>
                            {{ trans('sw.download')}}
                        </button>
                        <div class="menu menu-sub menu-sub-dropdown w-200px" data-kt-menu="true">
                            @if(in_array('exportUserExcel', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportUserExcel')}}" class="menu-link px-3">
                                        <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                        {{ trans('sw.excel_export')}}
                                    </a>
                                </div>
                            @endif
                            @if(in_array('exportUserPDF', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportUserPDF')}}" class="menu-link px-3">
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

        @if(count($users) > 0)
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_users_table">
                <thead>
                    <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-100px text-nowrap">
                            <i class="ki-outline ki-barcode fs-6 me-2"></i>{{ trans('sw.identification_code')}}
                        </th>
                        <th class="min-w-100px text-nowrap">
                            <i class="ki-outline ki-qr-code fs-6 me-2"></i>{{ trans('sw.barcode')}}
                        </th>
                        <th class="min-w-200px text-nowrap">
                            <i class="ki-outline ki-user fs-6 me-2"></i>{{ trans('sw.name')}}
                        </th>
                        <th class="min-w-200px text-nowrap">
                            <i class="ki-outline ki-sms fs-6 me-2"></i>{{ trans('sw.email')}}
                        </th>
                        <th class="min-w-100px text-nowrap">
                            <i class="ki-outline ki-phone fs-6 me-2"></i>{{ trans('sw.phone')}}
                        </th>
                        <th class="min-w-100px text-nowrap">
                            <i class="ki-outline ki-time fs-6 me-2"></i>{{ trans('sw.start_time_work')}}
                        </th>
                        <th class="min-w-100px text-nowrap">
                            <i class="ki-outline ki-time fs-6 me-2"></i>{{ trans('sw.end_time_work')}}
                        </th>
                        <th class="text-end min-w-70px text-nowrap actions-column">
                            <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('admin.actions')}}
                        </th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach($users as $key=> $user)
                        <tr>
                            <td class="pe-0">
                                <span class="fw-bold">{{ @str_pad(($user->id), 14, 0, STR_PAD_LEFT) }}</span>
                            </td>
                            <td class="pe-0">
                                <a download="{{@$user->id}}.png"
                                   href="{{route('sw.downloadCode', 'code='.@str_pad(($user->id), 14, 0, STR_PAD_LEFT))}}">
                                    <span>{!! \DNS1D::getBarcodeHTML(@str_pad(($user->id), 14, 0, STR_PAD_LEFT), \Modules\Software\Classes\TypeConstants::BarcodeType,1.5,15) !!}</span>
                                </a>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <!--begin::Avatar-->
                                    <div class="symbol symbol-50px me-3">
                                        <img alt="avatar" class="rounded-circle" src="{{$user->image}}" />
                                    </div>
                                    <!--end::Avatar-->
                                    <div>
                                        <!--begin::Title-->
                                        <div class="text-gray-800 text-hover-primary fs-5 fw-bold">
                                            {{ $user->name }}
                                        </div>
                                        <!--end::Title-->
                                    </div>
                                </div>
                            </td>
                            <td class="pe-0">
                                <span class="fw-bold">{{ $user->email }}</span>
                            </td>
                            <td class="pe-0">
                                <span class="fw-bold">{{ $user->phone }}</span>
                            </td>
                            <td class="pe-0">
                                <div class="d-flex align-items-center">
                                    <i class="ki-outline ki-time fs-6 text-muted me-2"></i>
                                    <span>{{ @$user->start_time_work }}</span>
                                </div>
                            </td>
                            <td class="pe-0">
                                <div class="d-flex align-items-center">
                                    <i class="ki-outline ki-time fs-6 text-muted me-2"></i>
                                    <span>{{ @$user->end_time_work }}</span>
                                </div>
                            </td>
                            <td class="text-end actions-column">
                                <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">
                                    @if(in_array('editUser', (array)$swUser->permissions) || $swUser->is_super_user)
                                        <!--begin::Edit-->
                                        <a href="{{route('sw.editUser',$user->id)}}"
                                           class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" title="{{ trans('admin.edit')}}">
                                            <i class="ki-outline ki-pencil fs-2"></i>
                                        </a>
                                        <!--end::Edit-->
                                    @endif
                                    
                                    @if(in_array('deleteUser', (array)$swUser->permissions) || $swUser->is_super_user)
                                        @if(request('trashed'))
                                            <!--begin::Enable-->
                                            <a title="{{ trans('admin.enable')}}"
                                               href="{{route('sw.deleteUser',$user->id)}}"
                                               class="confirm_delete btn btn-icon btn-bg-light btn-active-color-success btn-sm" title="{{ trans('admin.enable')}}">
                                                <i class="ki-outline ki-check-circle fs-2"></i>
                                            </a>
                                            <!--end::Enable-->
                                        @else
                                            <!--begin::Delete-->
                                            <a title="{{ trans('admin.disable')}}"
                                               href="{{route('sw.deleteUser',$user->id)}}"
                                               class="confirm_delete btn btn-icon btn-bg-light btn-active-color-danger btn-sm" title="{{ trans('admin.disable')}}">
                                                <i class="ki-outline ki-trash fs-2"></i>
                                            </a>
                                            <!--end::Delete-->
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
                        'from' => $users->firstItem() ?? 0,
                        'to' => $users->lastItem() ?? 0,
                        'total' => $users->total()
                    ]) }}
                </div>
                <ul class="pagination">
                    {!! $users->appends($search_query)->render() !!}
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
<!--end::Users-->


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
                    swal("Operation failed", "Something went wrong.", "error");
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
            $("#filter_form").submit();
        });

        jQuery(document).ready(function () {
            ComponentsPickers.init();
        });


        $(document).on('click', '.confirm_process', function (event) {
            id = $(this).attr('member_id');
            route = "{{route('sw.freezeMember', ['id' => 'member_id'])}}";
            url = route.replace('member_id', id);
            swal({
                title: "{{ trans('admin.are_you_sure')}}",
                text: "",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "{{ trans('admin.yes')}}",
                cancelButtonText: "{{ trans('admin.no_cancel')}}",
                showLoaderOnConfirm: true,
//                ,closeOnConfirm: false,
//                closeOnCancel: false
                preConfirm: function (isConfirm) {
                    setTimeout(function () {
                        if (isConfirm) {
                            {{--swal("{{ trans('admin.completed')}}", "{{ trans('admin.completed_successfully')}}", "success");--}}
                                window.location.href = url;
                        }
//            });
                    }, 2000)
                },
                allowOutsideClick: false
            }).then(function (isConfirm) {

            });

//                    .then(function () {
//
        });
    </script>

@endsection

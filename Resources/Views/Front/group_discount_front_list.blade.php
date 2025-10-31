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
    <link rel="stylesheet" type="text/css" href="{{asset('/')}}resources/assets/admin/global/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css"/>
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

<!--begin::Group Discounts-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-percentage fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ trans('sw.group_discounts')}}</span>
            </div>
        </div>
        <div class="card-toolbar">
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <!--begin::Add Group Discount-->
                @if(in_array('createGroupDiscount', (array)$swUser->permissions) || $swUser->is_super_user)
                    <a href="{{route('sw.createGroupDiscount')}}" class="btn btn-sm btn-flex btn-light-primary">
                        <i class="ki-outline ki-plus fs-6"></i>
                        {{ trans('admin.add')}}
                    </a>
                @endif
                <!--end::Add Group Discount-->
            </div>
        </div>
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

        @if(count($group_discounts) > 0)
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_group_discounts_table">
                <thead>
                    <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-200px">
                            <i class="ki-outline ki-list fs-6 me-2"></i>{{ trans('sw.name')}}
                        </th>
                        <th class="min-w-150px">
                            <i class="ki-outline ki-list fs-6 me-2"></i>{{ trans('sw.discount_type')}}
                        </th>
                        <th class="min-w-100px">
                            <i class="ki-outline ki-dollar fs-6 me-2"></i>{{ trans('sw.value')}}
                        </th>
                        <th class="min-w-300px">
                            <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('sw.visible_invisible')}}
                        </th>
                        <th class="text-end min-w-70px actions-column">
                            <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('admin.actions')}}
                        </th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach($group_discounts as $key=> $group_discount)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <!--begin::Avatar-->
                                    <div class="symbol symbol-50px me-3">
                                        <div class="symbol-label fs-3 bg-light-primary text-primary">
                                            <i class="ki-outline ki-percentage fs-2"></i>
                                        </div>
                                    </div>
                                    <!--end::Avatar-->
                                    <div>
                                        <!--begin::Title-->
                                        <div class="text-gray-800 text-hover-primary fs-5 fw-bold">
                                            {{ $group_discount->name }}
                                        </div>
                                        <!--end::Title-->
                                    </div>
                                </div>
                            </td>
                            <td class="pe-0">
                                <span class="badge badge-{{ $group_discount->type == 0 ? 'success' : 'info' }} fs-7">
                                    <i class="ki-outline ki-{{ $group_discount->type == 0 ? 'dollar' : 'percentage' }} fs-6 me-1"></i>
                                    {{ $group_discount->type == 0 ? trans('sw.amount2') : trans('sw.percentage2') }}
                                </span>
                            </td>
                            <td class="pe-0">
                                <span class="fw-bold text-primary">
                                    {{ $group_discount->amount }} {{ ($group_discount->type == 0 ? (env('APP_CURRENCY_EN') ?? ' $ ') : ' % ') }}
                                </span>
                            </td>
                            <td class="pe-0">
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge badge-{{$group_discount->is_member ? 'primary' : 'secondary'}} fs-7">
                                        <i class="ki-outline ki-user fs-6 me-1"></i>{{ trans('sw.subscribed_clients')}}
                                    </span>
                                    <span class="badge badge-{{$group_discount->is_non_member ? 'primary' : 'secondary'}} fs-7">
                                        <i class="ki-outline ki-user-cross fs-6 me-1"></i>{{ trans('sw.daily_clients')}}
                                    </span>
                                    <span class="badge badge-{{$group_discount->is_pt_member ? 'primary' : 'secondary'}} fs-7">
                                        <i class="ki-outline ki-user fs-6 me-1"></i>{{ trans('sw.pt')}}
                                    </span>
                                    <span class="badge badge-{{$group_discount->is_training_member ? 'primary' : 'secondary'}} fs-7">
                                        <i class="ki-outline ki-gym fs-6 me-1"></i>{{ trans('sw.training_plans')}}
                                    </span>
                                    <span class="badge badge-{{$group_discount->is_store ? 'primary' : 'secondary'}} fs-7">
                                        <i class="ki-outline ki-shop fs-6 me-1"></i>{{ trans('sw.store')}}
                                    </span>
                                </div>
                            </td>
                            <td class="text-end actions-column">
                                <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">
                                    @if(in_array('editGroupDiscount', (array)$swUser->permissions) || $swUser->is_super_user)
                                        <!--begin::Edit-->
                                        <a href="{{route('sw.editGroupDiscount',$group_discount->id)}}"
                                           class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" title="{{ trans('admin.edit')}}">
                                            <i class="ki-outline ki-pencil fs-2"></i>
                                        </a>
                                        <!--end::Edit-->
                                    @endif
                                    
                                    @if(in_array('deleteGroupDiscount', (array)$swUser->permissions) || $swUser->is_super_user)
                                        @if(request('trashed'))
                                            <!--begin::Enable-->
                                            <a title="{{ trans('admin.enable')}}"
                                               href="{{route('sw.deleteGroupDiscount',$group_discount->id)}}"
                                               class="confirm_delete btn btn-icon btn-bg-light btn-active-color-success btn-sm" title="{{ trans('admin.enable')}}">
                                                <i class="ki-outline ki-check-circle fs-2"></i>
                                            </a>
                                            <!--end::Enable-->
                                        @else
                                            <!--begin::Delete-->
                                            <a title="{{ trans('admin.disable')}}"
                                               href="{{route('sw.deleteGroupDiscount',$group_discount->id)}}"
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
                    Showing {{ $group_discounts->firstItem() ?? 0 }} to {{ $group_discounts->lastItem() ?? 0 }} of {{ $group_discounts->total() }} entries
                </div>
                <ul class="pagination">
                    {!! $group_discounts->appends($search_query)->render() !!}
                </ul>
            </div>
            <!--end::Pagination-->
        @else
            <!--begin::Empty State-->
            <div class="text-center py-10">
                <div class="symbol symbol-100px mb-5">
                    <div class="symbol-label fs-2x fw-semibold text-success bg-light-success">
                        <i class="ki-outline ki-percentage fs-2"></i>
                    </div>
                </div>
                <h4 class="text-gray-800 fw-bold">{{ trans('sw.no_record_found')}}</h4>
            </div>
            <!--end::Empty State-->
        @endif
    </div>
    <!--end::Card body-->
</div>
<!--end::Group Discounts-->
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

    </script>

@endsection

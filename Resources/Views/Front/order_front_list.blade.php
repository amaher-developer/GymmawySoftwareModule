@extends('software::layouts.list')
@section('list_title') {{ @$title }} @endsection
@section('styles')
    @if($lang == 'ar')
        <link rel="stylesheet" type="text/css" href="{{asset('/')}}/resources/assets/new_front/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap-rtl.css"/>
    @else
        <link rel="stylesheet" type="text/css" href="{{asset('/')}}/resources/assets/new_front/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css"/>
    @endif
@endsection
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
@section('list_add_button')
    {{--    <a href="{{route('createUserGymOrder')}}" class="btn btn-lg btn-success">{{ trans('global.gym_order_add')}} </a>--}}
         @if(request('trashed'))
             <a href="{{route('listUserGymOrder')}}" class="btn btn-lg btn-info">{{ trans('admin.enabled')}}</a>
         @else
             <a href="{{route('listUserGymOrder')}}?trashed=1"
                class="btn btn-lg btn-danger">{{ trans('admin.disabled')}}</a>
         @endif
    {{--<a href="" url="{{request()->fullUrlWithQuery(['export'=>1])}}" id="export" class="btn red btn-outline"><i class="icon-paper-clip"></i> {{ trans('admin.export')}}</a>--}}
@endsection
@section('page_body')

<!--begin::Orders-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <!--begin::Card title-->
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-shopping-cart fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ trans('sw.orders')}}</span>
            </div>
        </div>
        <!--end::Card title-->
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <!--begin::Toggle Status-->
                @if(request('trashed'))
                    <a href="{{route('listUserGymOrder')}}" class="btn btn-sm btn-flex btn-light-success">
                        <i class="ki-outline ki-check-circle fs-6"></i> {{ trans('admin.enabled')}}
                    </a>
                @else
                    <a href="{{route('listUserGymOrder')}}?trashed=1" class="btn btn-sm btn-flex btn-light-danger">
                        <i class="ki-outline ki-cross-circle fs-6"></i> {{ trans('admin.disabled')}}
                    </a>
                @endif
                <!--end::Toggle Status-->
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
        <table class="table align-middle table-row-dashed fs-6 gy-5 ajax-sourced_" id="kt_orders_table">
            <thead>
                <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-50px">
                        <i class="ki-outline ki-hash fs-6 me-2"></i>#
                    </th>
                    <th class="min-w-200px">
                        <i class="ki-outline ki-user fs-6 me-2"></i>{{ trans('admin.member_name')}}
                    </th>
                    <th class="min-w-100px">
                        <i class="ki-outline ki-dollar fs-6 me-2"></i>{{ trans('admin.subscription_price')}}
                    </th>
                    <th class="min-w-150px">
                        <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('global.from_date')}}
                    </th>
                    <th class="min-w-150px">
                        <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('global.to_date')}}
                    </th>
                    <th class="min-w-100px">
                        <i class="ki-outline ki-time fs-6 me-2"></i>{{ trans('global.duration')}}
                        <span class="fs-8 text-muted">({{ trans('global.by_day')}})</span>
                    </th>
                    <th class="text-end min-w-70px">
                        <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('admin.actions')}}
                    </th>
                </tr>
            </thead>
        </table>
        <!--end::Table-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Orders-->

{{--        <table class="table table-striped table-bordered table-hover">--}}
{{--            <thead>--}}
{{--            <tr class="">--}}
{{--                <th>#</th>--}}
{{--                <th>{{ trans('admin.member_name')}}</th>--}}
{{--                <th>{{ trans('admin.subscription_price')}}</th>--}}
{{--                <th>{{ trans('global.from_date')}}</th>--}}
{{--                <th>{{ trans('global.to_date')}}</th>--}}
{{--                <th>{{ trans('global.duration')}} <span style="font-size: 10px">({{ trans('global.by_day')}})</span></th>--}}
{{--                <th>{{ trans('admin.actions')}}</th>--}}
{{--            </tr>--}}
{{--            </thead>--}}
{{--            <tbody>--}}
{{--            @foreach($gymorders as $key=> $gymorder)--}}
{{--                <tr>--}}
{{--                    <td> {{ $gymorder->id }}</td>--}}
{{--                    <td> {{ $gymorder->member->name }}</td>--}}
{{--                    <td> {{ $gymorder->price }}</td>--}}
{{--                    <td> {{ $gymorder->date_from }}</td>--}}
{{--                    <td> {{ $gymorder->date_to }}</td>--}}
{{--                    <td> {{ $gymorder->duration }}</td>--}}
{{--                    <td>--}}
{{--                        <a href="{{route('createUserGymOrder',$gymorder->member_id)}}" class="btn btn-sm purple ">--}}
{{--                            <i class="fa fa-file-o"></i> {{ trans('admin.order_add')}}--}}
{{--                        </a>--}}
{{--                        --}}{{--                    <a href="{{route('editUserGymOrder',$gymorder->id)}}" class="btn btn-sm yellow">--}}
{{--                        --}}{{--                        <i class="fa fa-edit"></i> {{ trans('admin.edit')}}--}}
{{--                        --}}{{--                    </a>--}}
{{--                        --}}{{--                    @if(request('trashed'))--}}
{{--                        --}}{{--                        <a title="{{ trans('admin.enable')}}" href="{{route('deleteUserGymOrder',$gymorder->id)}}" class="confirm_delete btn btn-xs green">--}}
{{--                        --}}{{--                            <i class="fa fa-check-circle"></i>--}}
{{--                        --}}{{--                        </a>--}}
{{--                        --}}{{--                    @else--}}
{{--                        --}}{{--                        <a title="{{ trans('admin.disable')}}" href="{{route('deleteUserGymOrder',$gymorder->id)}}" class="confirm_delete btn btn-xs red">--}}
{{--                        --}}{{--                            <i class="fa fa-times"></i>--}}
{{--                        --}}{{--                        </a>--}}
{{--                        --}}{{--                    @endif--}}
{{--                    </td>--}}
{{--                </tr>--}}
{{--            @endforeach--}}
{{--            </tbody>--}}
{{--        </table>--}}
{{--        <div class="col-lg-5 col-md-5 col-md-offset-5">--}}
{{--            {!! $gymorders->appends($search_query)->render()  !!}--}}
{{--        </div>--}}
    </div>
@endsection

@section('scripts')
    @parent

{{--    <script--}}
{{--            src="{{asset(config('master.assets.admin.path'))}}/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"--}}
{{--            type="text/javascript"></script>--}}
{{--    <script--}}
{{--            src="{{asset(config('master.assets.admin.path'))}}/app-assets/js/scripts/tables/datatables-extensions/datatables-sources.min.js"--}}
{{--            type="text/javascript"></script>--}}

    <script type="text/javascript" src="{{asset('/')}}/resources/assets/new_front/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="{{asset('/')}}/resources/assets/new_front/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js"></script>
{{--    <script src="{{asset('/')}}/resources/assets/new_front/global/scripts/datatable.js"></script>--}}
    <script>



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

        $(".ajax-sourced_")
            .DataTable({
                @if($lang == 'ar')
                language: {
                    "sEmptyTable":     "ليست هناك بيانات متاحة في الجدول",
                    "sLoadingRecords": "جارٍ التحميل...",
                    "sProcessing":   "جارٍ التحميل...",
                    "sLengthMenu":   "أظهر _MENU_ مدخلات",
                    "sZeroRecords":  "لم يعثر على أية سجلات",
                    "sInfo":         "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
                    "sInfoEmpty":    "يعرض 0 إلى 0 من أصل 0 سجل",
                    "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
                    "sInfoPostFix":  "",
                    "sSearch":       "ابحث:",
                    "sUrl":          "",
                    "oPaginate": {
                        "sFirst":    "الأول",
                        "sPrevious": "السابق",
                        "sNext":     "التالي",
                        "sLast":     "الأخير"
                    },
                    "oAria": {
                        "sSortAscending":  ": تفعيل لترتيب العمود تصاعدياً",
                        "sSortDescending": ": تفعيل لترتيب العمود تنازلياً"
                    }
                },
                @endif
                ajax: "{{route('showAllUserGymOrder')}}?trashed={{@(int)$_GET['trashed']}}",
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'member.name', name: 'member.name'},
                    {data: 'price', name: 'price'},
                    {data: 'date_from', name: 'date_from'},
                    {data: 'date_to', name: 'date_to'},
                    {data: 'duration', name: 'duration'},
                ],
                columnDefs: [
                    {
                        "render": function (data, type, row) {
                            var show = '<a href="{{route('createUserGymOrder',':member_id')}}" class="btn btn-sm purple ">\n' +
                                '                            <i class="fa fa-file-o"></i> {{ trans('admin.order_add_for_member')}}\n' +
                                '                        </a>';
                            if (row['deleted_at']) {
                                show += '<a title="{{ trans('admin.enable')}}"\n' +
                                    '                            href="{{route('deleteUserGymOrder',':id')}}"\n' +
                                    '                        class="confirm_delete btn btn-sm green">\n' +
                                    '                                <i class="fa fa-check-circle"></i> {{ trans('admin.enable')}}\n' +
                                    '                                </a>';
                            } else {
                                show += '<a title="{{ trans('admin.disable')}}"\n' +
                                    '                            href="{{route('deleteUserGymOrder',':id')}}"\n' +
                                    '                        class="confirm_delete btn btn-sm red">\n' +
                                    '                                <i class="fa fa-times"></i> {{ trans('admin.disable')}}\n' +
                                    '                                </a>';
                            }

                            return show.replace(/:id/g, row['id']).replace(/:member_id/g, row['member_id']);
                        },
                        "targets": 6,
                    },
                ]
            });

    </script>

@endsection


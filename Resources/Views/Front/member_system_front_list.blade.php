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
    @if($lang == 'ar')
        <link rel="stylesheet" type="text/css" href="{{asset('/')}}/resources/assets/admin/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap-rtl.css"/>
    @else
        <link rel="stylesheet" type="text/css" href="{{asset('/')}}/resources/assets/admin/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css"/>
    @endif
@endsection
@section('list_add_button')
    <a href="{{route('createUserMember')}}" class="btn btn-lg btn-success">{{ trans('global.add_member')}} </a>
     @if(request('trashed'))
            <a href="{{route('listUserMember')}}" class="btn btn-lg btn-info">{{ trans('admin.enabled')}}</a>
        @else
            <a href="{{route('listUserMember')}}?trashed=1" class="btn btn-lg btn-danger">{{ trans('admin.disabled')}}</a>
        @endif
            {{--<a href="" url="{{request()->fullUrlWithQuery(['export'=>1])}}" id="export" class="btn red btn-outline"><i class="icon-paper-clip"></i> {{ trans('admin.export')}}</a>--}}
@endsection
@section('page_body')

<!--begin::Member System-->
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
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <!--begin::Add Member-->
                <a href="{{route('createUserMember')}}" class="btn btn-sm btn-flex btn-light-primary">
                    <i class="ki-outline ki-plus fs-6"></i>
                    {{ trans('global.add_member')}}
                </a>
                <!--end::Add Member-->
                
                <!--begin::Status Toggle-->
                @if(request('trashed'))
                    <a href="{{route('listUserMember')}}" class="btn btn-sm btn-flex btn-light-info">
                        <i class="ki-outline ki-check fs-6"></i>
                        {{ trans('admin.enabled')}}
                    </a>
                @else
                    <a href="{{route('listUserMember')}}?trashed=1" class="btn btn-sm btn-flex btn-light-danger">
                        <i class="ki-outline ki-cross fs-6"></i>
                        {{ trans('admin.disabled')}}
                    </a>
                @endif
                <!--end::Status Toggle-->
            </div>
        </div>
    </div>
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body pt-0">
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
            <table class="table align-middle table-row-dashed fs-6 gy-5 ajax-sourced_">
                <thead>
                <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-80px text-nowrap">
                        <i class="ki-outline ki-hash fs-6 me-2"></i>
                        #
                    </th>
                    <th class="min-w-150px text-nowrap">
                        <i class="ki-outline ki-user fs-6 me-2"></i>
                        {{ trans('global.name')}}
                    </th>
                    <th class="min-w-120px text-nowrap">
                        <i class="ki-outline ki-phone fs-6 me-2"></i>
                        {{ trans('global.phone')}}
                    </th>
                    <th class="min-w-120px text-nowrap">
                        <i class="ki-outline ki-calendar fs-6 me-2"></i>
                        {{ trans('global.date')}}
                    </th>
                    <th class="min-w-100px text-nowrap text-end">
                        <i class="ki-outline ki-setting-2 fs-6 me-2"></i>
                        {{ trans('admin.actions')}}
                    </th>
                </tr>
                </thead>
            </table>
        </div>
        <!--end::Table-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Member System-->

@endsection

@section('scripts')
    @parent


    <script type="text/javascript" src="{{asset('/')}}/resources/assets/admin/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="{{asset('/')}}/resources/assets/admin/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js"></script>

    <script>

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
                ajax: "{{route('showAllUserMember')}}?trashed={{@(int)$_GET['trashed']}}",
                columns: [
                    {data: 'id', name: 'id'},
                    {data: "name", name: "name"},
                    {data: 'phone', name: 'phone'},
                ],
                columnDefs: [
                    {
                        "render": function (data, type, row) {
                            var date = new Date(row['updated_at']);
                            var d = date.getDate();
                            var m =  date.getMonth();
                            m += 1;  // JavaScript months are 0-11
                            var y = date.getFullYear();
                            return  y + "-" + m + "-" + d;
                        },
                        "targets": 3,
                    },{
                        "render": function (data, type, row) {
                            var show = '<a href="{{route('editUserMember',':id')}}" class="btn btn-sm yellow">\n' +
                                '                            <i class="fa fa-edit"></i> {{ trans('admin.edit')}}\n' +
                                '                        </a>\n';

                            if (row['deleted_at']) {
                                show += '<a title="{{ trans('admin.enable')}}"\n' +
                                    '                            href="{{route('deleteUserMember',':id')}}"\n' +
                                    '                        class="confirm_delete btn btn-sm green">\n' +
                                    '                                <i class="fa fa-check-circle"></i> {{ trans('admin.enable')}}\n' +
                                    '                                </a>';
                            } else {
                                show += '<a title="{{ trans('admin.disable')}}"\n' +
                                    '                            href="{{route('deleteUserMember',':id')}}"\n' +
                                    '                        class="confirm_delete btn btn-sm red">\n' +
                                    '                                <i class="fa fa-times"></i> {{ trans('admin.disable')}}\n' +
                                    '                                </a>';
                            }

                            return show.replace(/:id/g, row['id']);
                        },
                        "targets": 4,
                    },
                ]
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


    </script>

@endsection
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
    <link rel="stylesheet" type="text/css" href="{{asset('/')}}resources/assets/admin/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.css"/>
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

<!--begin::Training Tracks-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-gym fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ $title}}</span>    
            </div>
        </div>
        <div class="card-toolbar">
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <!--begin::Filter-->
                <button type="button" class="btn btn-sm btn-flex btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_training_tracks_filter_collapse">
                    <i class="ki-outline ki-filter fs-6"></i>
                    {{ trans('sw.filter')}}
                </button>
                <!--end::Filter-->
                
                <!--begin::Add Training Track-->
                @if(in_array('createTrainingTrack', (array)$swUser->permissions) || $swUser->is_super_user)
                    <a href="{{route('sw.createTrainingTrack')}}" class="btn btn-sm btn-flex btn-light-primary">
                        <i class="ki-outline ki-plus fs-6"></i>
                        {{ trans('admin.add')}}
                    </a>
                @endif
                <!--end::Add Training Track-->
                
                <!--begin::Export-->
                @if((count(array_intersect(@(array)$swUser->permissions, ['exportTrainingTrackPDF', 'exportTrainingTrackExcel'])) > 0) || $swUser->is_super_user)
                    <div class="m-0">
                        <button class="btn btn-sm btn-flex btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            <i class="ki-outline ki-exit-down fs-6"></i>
                            {{ trans('sw.download')}}
                        </button>
                        <div class="menu menu-sub menu-sub-dropdown w-200px" data-kt-menu="true">
                            @if(in_array('exportTrainingTrackExcel', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportTrainingTrackExcel')}}" class="menu-link px-3">
                                        <i class="ki-outline ki-file-down fs-6 me-2"></i>
                                        {{ trans('sw.excel_export')}}
                                    </a>
                                </div>
                            @endif
                            @if(in_array('exportTrainingTrackPDF', (array)$swUser->permissions) || $swUser->is_super_user)
                                <div class="menu-item px-3">
                                    <a href="{{route('sw.exportTrainingTrackPDF')}}" class="menu-link px-3">
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
        <div class="collapse" id="kt_training_tracks_filter_collapse">
            <div class="card card-body mb-5">
                <form id="form_filter" action="" method="get">
                    <div class="row g-6">
                        <div class="col-md-12">
                            <label class="form-label fs-6 fw-semibold">{{ trans('sw.date_range')}}</label>
                            <div class="input-group date-picker input-daterange">
                                <input type="text" class="form-control" name="from" id="from_date" value="{{ request('from') }}" placeholder="{{ trans('sw.from')}}" autocomplete="off">
                                <span class="input-group-text">{{ trans('sw.to')}}</span>
                                <input type="text" class="form-control" name="to" id="to_date" value="{{ request('to') }}" placeholder="{{ trans('sw.to')}}" autocomplete="off">
                            </div>
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

        @if(count($tracks) > 0)
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_training_tracks_table">
                <thead>
                    <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-100px">
                            <i class="ki-outline ki-barcode fs-6 me-2"></i>{{ trans('sw.barcode')}}
                        </th>
                        <th class="min-w-200px">
                            <i class="ki-outline ki-user fs-6 me-2"></i>{{ trans('sw.name')}}
                        </th>
                        <th class="min-w-150px">
                            <i class="ki-outline ki-weight fs-6 me-2"></i>{{ trans('sw.height')}} / {{ trans('sw.weight')}}
                        </th>
                        <th class="min-w-100px">
                            <i class="ki-outline ki-information fs-6 me-2"></i>{{ trans('sw.details')}}
                        </th>
                        <th class="min-w-150px">
                            <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.date')}}
                        </th>
                        <th class="text-end min-w-70px actions-column">
                            <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('admin.actions')}}
                        </th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach($tracks as $key=> $track)
                        <tr>
                            <td class="pe-0">
                                <span class="fw-bold">{{ str_pad(@$track->member->code, 14, 0, STR_PAD_LEFT) }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <!--begin::Avatar-->
                                    <div class="symbol symbol-50px me-3">
                                        <img alt="avatar" class="rounded-circle" src="{{@$track->member->image}}" />
                                    </div>
                                    <!--end::Avatar-->
                                    <div>
                                        <!--begin::Title-->
                                        <div class="text-gray-800 text-hover-primary fs-5 fw-bold">
                                            {{ $track->member->name }}
                                        </div>
                                        <!--end::Title-->
                                    </div>
                                </div>
                            </td>
                            <td class="pe-0">
                                <div class="d-flex flex-column">
                                    <div class="fw-bold d-flex align-items-center">
                                        <i class="ki-outline ki-ruler fs-6 text-muted me-2"></i>
                                        <span>{{ $track->height }}</span>
                                    </div>
                                    <div class="fw-bold d-flex align-items-center">
                                        <i class="ki-outline ki-weight fs-6 text-muted me-2"></i>
                                        <span>{{ $track->weight }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="pe-0">
                                <button type="button" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" 
                                        data-bs-toggle="modal" data-bs-target="#modal_track_note_{{$track->id}}" 
                                        title="{{ trans('sw.details')}}">
                                    <i class="ki-outline ki-information fs-2"></i>
                                </button>
                            </td>
                            <!-- notes -->
                            <div id="modal_track_note_{{$track->id}}" class="modal fade" role="dialog">
                                <div class="modal-dialog">

                                    <!-- Modal content-->
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">{{ trans('sw.details')}}</h4>
                                        </div>
                                        <div class="modal-body">

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h4><b>{{ trans('sw.height')}}:</b> {{$track->height}}</h4>
                                                    <p></p>

                                                    <p>
                                                        <b>{{ trans('sw.neck_circumference')}}:</b> {{@$track->neck_circumference ?? '-'}}
                                                    </p>
                                                    <p>
                                                        <b>{{ trans('sw.arm_circumference')}}:</b> {{@$track->arm_circumference ?? '-'}}
                                                    </p>
                                                    <p>
                                                        <b>{{ trans('sw.pelvic_circumference')}}:</b> {{@$track->pelvic_circumference ?? '-'}}
                                                    </p>

                                                </div>
                                                <div class="col-md-6">
                                                    <h4><b>{{ trans('sw.weight')}}:</b> {{$track->weight}}</h4>
                                                    <p></p>
                                                    <p>
                                                        <b>{{ trans('sw.chest_circumference')}}:</b> {{@$track->chest_circumference ?? '-'}}
                                                    </p>
                                                    <p>
                                                        <b>{{ trans('sw.abdominal_circumference')}}:</b> {{@$track->abdominal_circumference ?? '-'}}
                                                    </p>
                                                    <p>
                                                        <b>{{ trans('sw.thigh_circumference')}}:</b> {{@$track->thigh_circumference ?? '-'}}
                                                    </p>
                                                </div>
                                            </div>

                                            <div><hr/>
                                                <h4 class="modal-title">{{ trans('sw.notes')}}</h4>
                                            </div>
                                            <p>{!! strip_tags($track->notes, '<p><br>') !!}</p>
                                        </div>
                                    </div>

                                </div>
                            </div>


                            <td class="pe-0">
                                <div class="d-flex flex-column">
                                    <div class="text-muted fw-bold d-flex align-items-center">
                                        <i class="ki-outline ki-calendar fs-6 text-muted me-2"></i>
                                        <span>{{ \Carbon\Carbon::parse($track->date)->format('Y-m-d') }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end actions-column">
                                <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">
                                    @if(in_array('editTrainingTrack', (array)$swUser->permissions) || $swUser->is_super_user)
                                        <!--begin::Edit-->
                                        <a href="{{route('sw.editTrainingTrack',$track->id)}}"
                                           class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" title="{{ trans('admin.edit')}}">
                                            <i class="ki-outline ki-pencil fs-2"></i>
                                        </a>
                                        <!--end::Edit-->
                                    @endif
                                    
                                    @if(in_array('deleteTrainingTrack', (array)$swUser->permissions) || $swUser->is_super_user)
                                        @if(request('trashed'))
                                            <!--begin::Enable-->
                                            <a title="{{ trans('admin.enable')}}"
                                               href="{{route('sw.deleteTrainingTrack',$track->id)}}"
                                               class="confirm_delete btn btn-icon btn-bg-light btn-active-color-success btn-sm" title="{{ trans('admin.enable')}}">
                                                <i class="ki-outline ki-check-circle fs-2"></i>
                                            </a>
                                            <!--end::Enable-->
                                        @else
                                            <!--begin::Delete-->
                                            <a title="{{ trans('admin.disable')}}"
                                               href="{{route('sw.deleteTrainingTrack',$track->id)}}"
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
                        'from' => $tracks->firstItem() ?? 0,
                        'to' => $tracks->lastItem() ?? 0,
                        'total' => $tracks->total()
                    ]) }}
                </div>
                <ul class="pagination">
                    {!! $tracks->appends($search_query)->render() !!}
                </ul>
            </div>
            <!--end::Pagination-->
        @else
            <!--begin::Empty State-->
            <div class="text-center py-10">
                <div class="symbol symbol-100px mb-5">
                    <div class="symbol-label fs-2x fw-semibold text-success bg-light-success">
                        <i class="ki-outline ki-gym fs-2"></i>
                    </div>
                </div>
                <h4 class="text-gray-800 fw-bold">{{ trans('sw.no_record_found')}}</h4>
            </div>
            <!--end::Empty State-->
        @endif
    </div>
    <!--end::Card body-->
</div>
<!--end::Training Tracks-->
@endsection

@section('scripts')
    @parent
    <script src="{{asset('resources/assets/admin/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"
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
                    swal("Operation failed", "Something went wrong.", "error");
                    console.error("Request: " + JSON.stringify(request));
                    console.error("Error: " + JSON.stringify(error));
                }
            });

        });

        $(document).on('click', '.remove_filter', function (event) {
            event.preventDefault();
            var filter = $(this).attr('id');
            $("#" + filter).val('');
            $("#form_filter").submit();
        });
        jQuery(document).ready(function () {
            $('.input-daterange').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                clearBtn: true,
                orientation: 'bottom auto'
            });
            
            $('button[type="reset"]').on('click', function() {
                setTimeout(() => {
                    $(this).closest('form').find('select').trigger('change');
                }, 100);
            });
        });


    </script>

@endsection

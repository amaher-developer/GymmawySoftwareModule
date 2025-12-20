@extends('software::layouts.form')
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
@section('form_title') {{ @$title }} @endsection
@section('styles')
    <!---Internal Fileupload css-->

    <link rel="stylesheet" type="text/css" href="{{asset('/')}}resources/assets/new_front/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
    <link rel="stylesheet" type="text/css" href="{{asset('/')}}resources/assets/new_front/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
    <link href="{{asset('/')}}resources/assets/new_front/global/scripts/css/fileupload.css" rel="stylesheet"
          type="text/css"/>

        <style>
        .tag-orange {
            background-color: #fd7e14 !important;
            color: #fff;
        }
        .tag {
            color: #14112d;
            background-color: #ecf0fa;
            border-radius: 3px;
            padding: 0 .5rem;
            line-height: 2em;
            display: -ms-inline-flexbox;
            display: inline-flex;
            cursor: default;
            font-weight: 400;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        .tab-content>.tab-pane {
            padding-top: 20px;
        }
        .ckbox input{
            width: 20px;
            height: 20px;
        }
        .ckbox span{
            vertical-align: text-top;
        }

        /* Excel Import Custom Styles */
        .dashboard-stat {
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        .dashboard-stat.blue {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }

        .dashboard-stat.green {
            background: linear-gradient(135deg, #0ba360 0%, #3cba92 100%);
            color: #fff;
        }

        .dashboard-stat.red {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: #fff;
        }

        .dashboard-stat.purple {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: #fff;
        }

        .dashboard-stat .visual {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.2;
        }

        .dashboard-stat .visual i {
            font-size: 60px;
        }

        .dashboard-stat .details {
            position: relative;
            z-index: 1;
        }

        .dashboard-stat .number {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .dashboard-stat .desc {
            font-size: 14px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .note.note-info {
            background-color: #e8f4f8;
            border-left: 4px solid #3498db;
            padding: 20px;
            border-radius: 4px;
        }

        .note.note-info h4 {
            color: #2980b9;
            margin-top: 0;
        }

        .alert.alert-warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            color: #856404;
        }

        .alert.alert-info {
            background-color: #d1ecf1;
            border-left: 4px solid #17a2b8;
            color: #0c5460;
        }

        .badge.badge-danger {
            background-color: #dc3545;
            padding: 5px 10px;
            border-radius: 12px;
        }

        .table-responsive {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 4px;
        }

        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }

        .portlet.light.bordered {
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .portlet.light.bordered:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        }

        .btn-success {
            background: linear-gradient(135deg, #0ba360 0%, #3cba92 100%);
            border: none;
            font-weight: 600;
            padding: 12px 30px;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(11, 163, 96, 0.4);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .dropify-wrapper {
            border: 2px dashed #3498db !important;
            border-radius: 8px !important;
            transition: all 0.3s ease;
        }

        .dropify-wrapper:hover {
            border-color: #2980b9 !important;
            background-color: #f8f9fa !important;
        }

        .mt-3 {
            margin-top: 1rem !important;
        }

        .mt-4 {
            margin-top: 1.5rem !important;
        }

        .mb-0 {
            margin-bottom: 0 !important;
        }

        @media (max-width: 768px) {
            .dashboard-stat .number {
                font-size: 24px;
            }

            .dashboard-stat .visual i {
                font-size: 40px;
            }
        }
    </style>
@endsection
@section('page_body')

    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation-triangle"></i> {{ session('warning') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa fa-times-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <form method="post" action="{{route('sw.uploadExcelStore')}}" class="form-horizontal" role="form" enctype="multipart/form-data" id="uploadExcelForm">
                {{csrf_field()}}
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-upload font-blue-steel"></i>
                            <span class="caption-subject font-blue-steel bold">{{ trans('sw.upload_excel_file')}}</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="form-group">
                            <label>{{ trans('sw.select_excel_file')}}</label>
                            <input
                                data-default-file="{{asset('uploads/settings/excel_icon.png')}}"
                                name="excel_data"
                                type="file"
                                class="dropify"
                                data-height="250"
                                accept=".xlsx,.xls"
                                data-allowed-file-extensions="xlsx xls"
                                data-max-file-size="5M"
                                required
                            />
                            <small class="form-text text-muted">
                                <i class="fa fa-info-circle"></i>
                                {{ trans('sw.allowed_types')}}: .xlsx, .xls |
                                {{ trans('sw.max_size')}}: 5MB
                            </small>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-success btn-block" id="uploadBtn">
                                <i class="fa fa-upload"></i> {{ trans('sw.upload_and_import')}}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-6">
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-info-circle font-blue-steel"></i>
                        <span class="caption-subject font-blue-steel bold">{{ trans('sw.instructions')}}</span>
                    </div>
                </div>
                <div class="portlet-body">
                    <div class="note note-info">
                        <h4 class="block"><i class="fa fa-file-excel-o"></i> {{ trans('sw.excel_template_instructions')}}</h4>
                        <p><strong>{{ trans('sw.required_columns')}}:</strong></p>
                        <ul>
                            <li>name <span class="badge badge-danger">{{ trans('sw.required')}}</span></li>
                            <li>phone <span class="badge badge-danger">{{ trans('sw.required')}}</span></li>
                            <li>subscription_code <span class="badge badge-danger">{{ trans('sw.required')}}</span></li>
                            <li>joining_date <span class="badge badge-danger">{{ trans('sw.required')}}</span></li>
                            <li>expire_date <span class="badge badge-danger">{{ trans('sw.required')}}</span></li>
                        </ul>

                        <p><strong>{{ trans('sw.optional_columns')}}:</strong></p>
                        <p class="small">member_code, email, gender, dob, national_id, address, sale_channel, fp_id, fp_uid, workouts, visits, amount_paid, amount_remaining, vat_percentage, discount_value, discount_type, payment_type, status</p>

                        <div class="alert alert-warning mt-3">
                            <i class="fa fa-calendar"></i> <strong>{{ trans('sw.date_format')}}:</strong> Y-m-d ({{ trans('sw.example')}}: 2024-01-15)
                        </div>

                        <div class="alert alert-info mt-2">
                            <i class="fa fa-list"></i> <strong>{{ trans('sw.gender_values')}}:</strong> male, female<br>
                            <i class="fa fa-list"></i> <strong>{{ trans('sw.status_values')}}:</strong> active, expired<br>
                            <i class="fa fa-list"></i> <strong>{{ trans('sw.discount_type_values')}}:</strong> fixed, percentage
                        </div>
                    </div>

                    <div class="mt-3">
                        <a href="{{ asset('Modules/Software/Imports/EXCEL_TEMPLATE_EXAMPLE.xlsx') }}" class="btn btn-primary btn-block" download>
                            <i class="fa fa-download"></i> {{ trans('sw.download_template_example')}}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Import Statistics Display --}}
    @if(session('import_stats'))
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-bar-chart font-blue-steel"></i>
                            <span class="caption-subject font-blue-steel bold">{{ trans('sw.import_statistics')}}</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        @php $stats = session('import_stats'); @endphp

                        <div class="row">
                            <div class="col-md-3">
                                <div class="dashboard-stat blue">
                                    <div class="visual">
                                        <i class="fa fa-file-excel-o"></i>
                                    </div>
                                    <div class="details">
                                        <div class="number">{{ $stats['total_rows'] }}</div>
                                        <div class="desc">{{ trans('sw.total_rows')}}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="dashboard-stat green">
                                    <div class="visual">
                                        <i class="fa fa-check-circle"></i>
                                    </div>
                                    <div class="details">
                                        <div class="number">{{ $stats['successful_rows'] }}</div>
                                        <div class="desc">{{ trans('sw.successful_rows')}}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="dashboard-stat red">
                                    <div class="visual">
                                        <i class="fa fa-times-circle"></i>
                                    </div>
                                    <div class="details">
                                        <div class="number">{{ $stats['failed_rows'] }}</div>
                                        <div class="desc">{{ trans('sw.failed_rows')}}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="dashboard-stat purple">
                                    <div class="visual">
                                        <i class="fa fa-percent"></i>
                                    </div>
                                    <div class="details">
                                        <div class="number">
                                            {{ $stats['total_rows'] > 0 ? round(($stats['successful_rows'] / $stats['total_rows']) * 100, 1) : 0 }}%
                                        </div>
                                        <div class="desc">{{ trans('sw.success_rate')}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Error Details --}}
                        @if(!empty($stats['errors']) && count($stats['errors']) > 0)
                            <div class="mt-4">
                                <h4 class="text-danger">
                                    <i class="fa fa-exclamation-triangle"></i> {{ trans('sw.error_details')}} ({{ count($stats['errors']) }})
                                </h4>
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="10%">{{ trans('sw.row_number')}}</th>
                                                <th width="20%">{{ trans('sw.phone')}}</th>
                                                <th width="70%">{{ trans('sw.error_message')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($stats['errors'] as $error)
                                                <tr>
                                                    <td><span class="badge badge-danger">{{ $error['row_number'] }}</span></td>
                                                    <td>{{ $error['member_phone'] }}</td>
                                                    <td>{{ $error['error_message'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection


@section('sub_scripts')
    <script src="{{asset('resources/assets/new_front/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"
            type="text/javascript"></script>
    <script src="{{asset('/')}}resources/assets/new_front/global/scripts/metronic.js" type="text/javascript"></script>
    <script src="{{asset('/')}}resources/assets/new_front/pages/scripts/components-pickers.js"></script>

    <script type="text/javascript" src="{{asset('/')}}resources/assets/new_front/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
    <script type="text/javascript" src="{{asset('/')}}resources/assets/new_front/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
    <script>

        jQuery(document).ready(function() {
            ComponentsPickers.init();
        });


    </script>


    <!--Internal Fileuploads js-->
    <script src="{{asset('/')}}resources/assets/new_front/global/scripts/js/fileupload.js"></script>
    <script src="{{asset('/')}}resources/assets/new_front/global/scripts/js/file-upload.js"></script>


    <script>
        $('.dropify-infos-message').html("{{ trans('sw.upload_image')}}");
        $('.dropify-message p:first').html("{{ trans('sw.upload_image')}}");
        $('.dropify-clear').html("{{ trans('sw.remove')}}");
    </script>

    <script>
        // showing modal with effect
        $('.modal-effect').on('click', function (e) {
            e.preventDefault();
            var effect = $(this).attr('data-effect');
            $('#modalCamera').addClass(effect);
        });

        // Excel file upload validation and submit handling
        $(document).ready(function() {
            $('#uploadExcelForm').on('submit', function(e) {
                var fileInput = $('input[name="excel_data"]')[0];

                if (fileInput.files.length === 0) {
                    e.preventDefault();
                    alert('{{ trans('sw.please_select_excel_file') }}');
                    return false;
                }

                var file = fileInput.files[0];
                var fileName = file.name;
                var fileSize = file.size;
                var fileExtension = fileName.split('.').pop().toLowerCase();

                // Validate file extension
                if (fileExtension !== 'xlsx' && fileExtension !== 'xls') {
                    e.preventDefault();
                    alert('{{ trans('sw.invalid_file_type') }}');
                    return false;
                }

                // Validate file size (5MB = 5242880 bytes)
                if (fileSize > 5242880) {
                    e.preventDefault();
                    alert('{{ trans('sw.file_too_large') }}');
                    return false;
                }

                // Show loading state
                var uploadBtn = $('#uploadBtn');
                uploadBtn.prop('disabled', true);
                uploadBtn.html('<i class="fa fa-spinner fa-spin"></i> {{ trans('sw.uploading') }}...');

                // Form will submit normally after this
                return true;
            });

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>

@endsection



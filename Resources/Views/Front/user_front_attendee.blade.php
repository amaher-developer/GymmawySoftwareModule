@extends('software::layouts.list')
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
@section('list_title') {{ @$title }} @endsection
@section('styles')
<style>
    .normal_search {
        height: 60px;
    }
    .u_scan_barcode_manual {
        height: 60px;
        font-size: 28px;
        line-height: 60px;
    }
    .short-btn {
        min-width: 140px;
        height: 120px;
    }
    .short-btn i{
        line-height: 40px !important;
    }
    .number{
        font-size: 20px !important;
    }
    .details{
        width: 60%;
    }
    .text-muted {
        color: #ffffff;
    }
    #geo_check_in_btn {
        height: 60px;
        font-size: 18px;
    }
</style>
@endsection
@section('page_body')

    @if(\Carbon\Carbon::parse($mainSettings->sw_end_date)->toDateString() <= \Carbon\Carbon::now()->toDateString())
        <div class="alert alert-danger d-flex align-items-center mb-5">
            <i class="fa fa-warning fs-2 me-3"></i>
            <div>{!! trans('sw.subscription_expire_date_msg', ['date'=> \Carbon\Carbon::parse($mainSettings->sw_end_date)->toDateString(), 'url' => route('sw.listSwPayment')]) !!}</div>
        </div>
    @endif

    <!--begin::Dashboard-->
    <div class="row g-5">
        <!--begin::Employee Check-in Card-->
        <div class="col-lg-8">
            <div class="card card-flush">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="fw-bold">{{ trans('sw.employee_check_in_out') }}</h3>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Input group-->
                    <!-- <div class="mb-10">
                        <label class="form-label">{{ trans('sw.scan_employee_id') }}</label>
                        <div class="input-group">
                            <input type="text" class="form-control u_scan_barcode_manual" placeholder="{{ trans('sw.check_in_by_id')}}" name="u_scan_barcode_manual" id="u_scan_barcode_manual">
                            <button class="btn btn-primary normal_search" id="Normal_search" onclick="user_barcode_scanner();" type="button">
                                <i class="ki-outline ki-barcode fs-1"></i>
                            </button>
                        </div>
                    </div> -->
                    <!--end::Input group-->

                    <!--begin::Geolocation Check-in-->
                    <!-- <div class="separator separator-dashed my-5"></div> -->
                    <div class="mb-5">
                        <label class="form-label">{{ trans('sw.location_based_check_in') }}</label>
                        <button class="btn btn-success w-100" id="geo_check_in_btn" onclick="geoCheckIn();" type="button">
                            <i class="ki-outline ki-geolocation fs-1 me-2"></i>
                            <span id="geo_btn_text">{{ trans('sw.check_in_with_location') }}</span>
                        </button>
                        <div id="geo_status" class="mt-3" style="display:none;">
                            <div class="alert alert-info">
                                <span id="geo_status_text"></span>
                            </div>
                        </div>
                    </div>
                    <!--end::Geolocation Check-in-->
                </div>
                <!--end::Card body-->
            </div>
        </div>
        <!--end::Employee Check-in Card-->

        <!--begin::Last Employee-->
        <div class="col-lg-4">
            <div class="card card-flush h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="symbol symbol-50px me-5">
                        <div class="symbol-label bg-light-primary">
                            <i class="ki-outline ki-user fs-2x text-primary"></i>
                        </div>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.last_enter_employee') }}</span>
                        <span class="fs-4 fw-bold text-primary" id="barcode_last_enter_employee">{{@$last_enter_member->user->name}}</span>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Last Employee-->
    </div>
    <!--end::Dashboard-->

    <!--begin::Statistics-->
    <div class="row g-5 mt-5">
        <!--begin::Check-in Today-->
        <div class="col-lg-6">
            <div class="card card-flush h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="symbol symbol-50px me-5">
                        <div class="symbol-label bg-light-success">
                            <i class="ki-outline ki-exit-left  fs-2x text-success"></i>
                        </div>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.check_in_today') }}</span>
                        <span class="fs-2 fw-bold text-success" id="check_in">
                            <i class="ki-outline ki-loading fs-2x"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Check-in Today-->

        <!--begin::Check-out Today-->
        <div class="col-lg-6">
            <div class="card card-flush h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="symbol symbol-50px me-5">
                        <div class="symbol-label bg-light-danger">
                            <i class="ki-outline ki-exit-left fs-2x text-danger"></i>
                        </div>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.check_out_today') }}</span>
                        <span class="fs-2 fw-bold text-danger" id="check_out">
                            <i class="ki-outline ki-loading fs-2x"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Check-out Today-->
    </div>
    <!--end::Statistics-->


@endsection
@section('scripts')
<script>
    function user_barcode_scanner() {
        let value = $('#u_scan_barcode_manual').val();
        if(value < 0)
            return;
        var mycode = value;
        var enquiry = $("#scan_barcode_enquiry").is( ":checked" ) ? 1 : 0;

        $.ajax({
            url: '{{route('sw.userAttendeesStore')}}',
            type: "get",
            data: {
                code: mycode,
                enquiry: enquiry
            },
            beforeSend: function () {
                $('#Normal_search').html('<i class="ki-outline ki-loading fs-2x"></i>');
            },
            success: (data) => {
                var data = data;
                if(data.status === true){
                    $('.client_img').css("color", "#4caf50b8");
                    $('#client_img').attr('src',  data.user.image);
                }else{
                    $('.client_img').css("color", "#f44336c9");
                    $('#client_img').attr('src',  default_avatar_image);
                }
                $('#modalAttends').modal('show');
                load_new_posts();
                $('#myData').hide();
                $('#p_messages').text(data.msg);
                $('#u_scan_barcode_manual').val('');

                if(data.check_in){
                    $('#check_in').html(data.check_in);
                }
                if(data.check_out){
                    $('#check_out').html(data.check_out);
                }
            },
            error: (reject) => {
                var response = $.parseJSON(reject.responseText);
                console.log(response);

                // Display error message to user
                $('.client_img').css("color", "#f44336c9");
                $('#client_img').attr('src', default_avatar_image);
                $('#modalAttends').modal('show');
                load_new_posts();
                $('#myData').hide();
                $('#p_messages').text(response.msg || '{{ trans('sw.check_in_failed') }}');
                $('#u_scan_barcode_manual').val('');
            },
            complete: function() {
                $('#Normal_search').html('<i class="ki-outline ki-scan"></i>');
            }
        });
    }

    function geoCheckIn() {
        if (!navigator.geolocation) {
            showGeoStatus('{{ trans('sw.geolocation_not_supported') }}', 'danger');
            return;
        }

        setGeoButtonLoading(true);
        showGeoStatus('{{ trans('sw.getting_your_location') }}', 'info');

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const latitude = position.coords.latitude;
                const longitude = position.coords.longitude;

                $.ajax({
                    url: '{{ route("sw.attendanceGeofenceCheck") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        latitude: latitude,
                        longitude: longitude
                    },
                    success: function(response) {
                        setGeoButtonLoading(false);
                        if (response.status) {
                            showGeoStatus(response.message || '{{ trans('sw.check_in_successful') }}', 'success');
                            if(response.check_in) {
                                $('#check_in').html(response.check_in);
                            }
                            if(response.check_out) {
                                $('#check_out').html(response.check_out);
                            }
                        } else {
                            showGeoStatus(response.message || '{{ trans('sw.check_in_failed') }}', 'danger');
                        }
                    },
                    error: function(xhr) {
                        setGeoButtonLoading(false);
                        let errorMsg = '{{ trans('sw.check_in_failed') }}';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.status === 403) {
                            errorMsg = '{{ trans('sw.outside_allowed_area_short') }}';
                        }
                        showGeoStatus(errorMsg, 'danger');
                    }
                });
            },
            function(error) {
                setGeoButtonLoading(false);
                let errorMsg = '';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMsg = '{{ trans('sw.location_permission_denied') }}';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMsg = '{{ trans('sw.location_unavailable') }}';
                        break;
                    case error.TIMEOUT:
                        errorMsg = '{{ trans('sw.location_timeout') }}';
                        break;
                    default:
                        errorMsg = '{{ trans('sw.location_error') }}';
                }
                showGeoStatus(errorMsg, 'danger');
            },
            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            }
        );
    }

    function setGeoButtonLoading(loading) {
        const btn = document.getElementById('geo_check_in_btn');
        const btnText = document.getElementById('geo_btn_text');
        if (loading) {
            btn.disabled = true;
            btnText.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>{{ trans('sw.processing') }}';
        } else {
            btn.disabled = false;
            btnText.textContent = '{{ trans('sw.check_in_with_location') }}';
        }
    }

    function showGeoStatus(message, type) {
        const statusDiv = document.getElementById('geo_status');
        const statusText = document.getElementById('geo_status_text');
        const alertDiv = statusDiv.querySelector('.alert');

        alertDiv.className = 'alert alert-' + type;
        statusText.textContent = message;
        statusDiv.style.display = 'block';

        if (type === 'success') {
            setTimeout(function() {
                statusDiv.style.display = 'none';
            }, 5000);
        }
    }

    $(document).ready(function() {
        $('#u_scan_barcode_manual').focus();

        $('#u_scan_barcode_manual').keypress(function(e) {
            if(e.which == 13) {
                user_barcode_scanner();
            }
        });
    });
</script>
@endsection

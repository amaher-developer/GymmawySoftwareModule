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
@section('page_body')
    <!--begin::Layout-->
    <div class="d-flex flex-column flex-lg-row">
        <!--begin::Sidebar-->
        <div class="flex-column flex-lg-row-auto w-lg-250px w-xl-300px mb-10">
            <!--begin::Card-->
            <div class="card card-flush">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h4 class="text-dark fw-bold">
                            <i class="ki-outline ki-wallet fs-2 me-2 text-primary"></i>
                            {{ trans('sw.sms_your_balance')}}
                        </h4>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Stats-->
                    <div class="d-flex flex-column text-center">
                        <div class="d-flex align-items-center mb-2">
                             <div class="symbol symbol-50px me-5">
                                 <div class="symbol-label bg-light-success">
                                     <i class="ki-outline ki-messages fs-2x text-success"></i>
                                 </div>
                             </div>
                             <div class="d-flex flex-column">
                                 <span class="fs-2 fw-bold text-primary">{{$smsPoints}}</span>
                                 <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.points')}}</span>
                             </div>
                         </div>
                    </div>
                    <!--end::Stats-->
                </div>
                <!--end::Card body-->
                <!--begin::Card footer-->
                <div class="card-footer">
                    <a href="{{route('sw.listSMSLog')}}" class="btn btn-light-primary w-100">
                        <i class="ki-outline ki-list-ol fs-6"></i>
                        {{ trans('sw.sms_logs')}}
                    </a>
                </div>
                <!--end::Card footer-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Sidebar-->
        <!--begin::Content-->
        <div class="flex-lg-row-fluid ms-lg-10">
             <form role="form" action="{{route('sw.storeSMS')}}" method="post" class="form" onsubmit="return confirm('{{ trans('admin.are_you_sure')}}');">
                {{csrf_field()}}
                <!--begin::Card-->
                <div class="card card-flush">
                    <!--begin::Card header-->
                    <div class="card-header">
                        <div class="card-title">
                            <h4 class="text-dark fw-bold">
                                <i class="ki-outline ki-send fs-2 me-2 text-primary"></i>
                                {{ $title }}
                            </h4>
                        </div>
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Row - Client Type-->
                        <div class="fv-row mb-10">
                            <label class="form-label required">{{ trans('sw.clients')}}</label>
                            <div class="d-flex flex-wrap gap-5">
                                <div class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="radio" name="client_type" id="optionsRadios4" value="1" checked>
                                    <label class="form-check-label" for="optionsRadios4">{{ trans('sw.new_entities')}}</label>
                                </div>
                                <div class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="radio" name="client_type" id="optionsRadios5" value="2">
                                    <label class="form-check-label" for="optionsRadios5">{{ trans('sw.daily_clients')}}</label>
                                </div>
                                <div class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="radio" name="client_type" id="optionsRadios6" value="3">
                                    <label class="form-check-label" for="optionsRadios6">{{ trans('sw.subscribed_clients')}}</label>
                                </div>
                                <div class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="radio" name="client_type" id="optionsRadios7" value="4">
                                    <label class="form-check-label" for="optionsRadios7">{{ trans('sw.subscribed_clients_active')}}</label>
                                </div>
                            </div>
                        </div>
                        <!--end::Row-->
                        <!--begin::Row - Phones-->
                        <div class="fv-row mb-10">
                            <label class="form-label required">{{ trans('sw.phone')}}</label>
                            <div class="position-relative">
                                <textarea class="form-control" rows="3" name="phones" id="phones" required></textarea>
                                <div class="position-absolute top-50 end-0 translate-middle-y me-5" style="display: none;" id="spinner">
                                    <span class="spinner-border spinner-border-sm"></span>
                                </div>
                            </div>
                            <div class="form-text">01234567890, 01234567891, 01234567892 ...</div>
                        </div>
                        <!--end::Row-->
                        <!--begin::Row - Message-->
                        <div class="fv-row mb-10">
                            <label class="form-label required">{{ trans('sw.message')}}</label>
                            <textarea class="form-control" rows="5" name="message" id="message" required></textarea>
                        </div>
                        <!--end::Row-->
                    </div>
                    <!--end::Card body-->
                    <!--begin::Card footer-->
                    <div class="card-footer d-flex justify-content-end py-6">
                        <button type="reset" class="btn btn-light me-5">{{ trans('admin.reset')}}</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">{{ trans('global.send')}}</span>
                            <span class="indicator-progress">Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                    <!--end::Card footer-->
                </div>
                <!--end::Card-->
            </form>
        </div>
        <!--end::Content-->
    </div>
    <!--end::Layout-->
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            // Add event listener for client type radio buttons
            $('input[name="client_type"]').on('change', function() {
                client_type($(this).val());
            });

            // SMS character counter logic
            $('#message').on('keyup input', function() {
                var message = $(this).val();
                var charCount = message.length;
                var smsPart = 1;
                var charsLeft = 0;

                // Simple check for non-GSM characters (like Arabic). A more robust check might be needed for other languages.
                var isUnicode = /[^\u0000-\u007f]/.test(message);

                if (isUnicode) {
                    // UCS-2 encoding (e.g., Arabic)
                    if (charCount <= 70) {
                        smsPart = 1;
                        charsLeft = 70 - charCount;
                    } else {
                        smsPart = Math.ceil(charCount / 67);
                        charsLeft = (smsPart * 67) - charCount;
                    }
                } else {
                    // GSM-7 encoding (e.g., English)
                    if (charCount <= 160) {
                        smsPart = 1;
                        charsLeft = 160 - charCount;
                    } else {
                        smsPart = Math.ceil(charCount / 153);
                        charsLeft = (smsPart * 153) - charCount;
                    }
                }

                $('#char-count').text(charCount);
                $('#sms-part').text(smsPart);
                $('#chars-left').text(charsLeft);
            });
        });

        function client_type(id) {
            if (id) {
                $('#spinner').show();
                $('#phones').prop('disabled', true);
                $.ajax({
                    url: '{{route('sw.phonesByAjax')}}',
                    type: 'GET',
                    data: {type: id},
                    success: function (response) {
                        document.getElementById('phones').value = response;
                        $('#spinner').hide();
                        $('#phones').prop('disabled', false);
                    },
                    error: function (request, error) {
                        swal("{{ trans('admin.operation_failed')}}", "{{ trans('admin.something_wrong')}}", "error");
                        console.error("Request: " + JSON.stringify(request));
                        console.error("Error: " + JSON.stringify(error));
                        $('#spinner').hide();
                        $('#phones').prop('disabled', false);
                    }
                });
            }
        }
    </script>
@endsection

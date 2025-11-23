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
                            <i class="ki-outline ki-information fs-2 me-2 text-primary"></i>
                            {{ trans('sw.m_steps')}}
                        </h4>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Notice-->
                    <div class="notice d-flex bg-light-info rounded border-info border border-dashed p-6">
                        <!--begin::Icon-->
                        <i class="ki-outline ki-question-2 fs-2tx text-info me-4"></i>
                        <!--end::Icon-->
                        <!--begin::Wrapper-->
                        <div class="d-flex flex-stack flex-grow-1">
                            <!--begin::Content-->
                            <div class="fw-semibold">
                                <div class="fs-6 text-gray-700">
                                    <ol class="ps-3">
                                        <li>{!! trans('sw.w_ultra_step_1') !!}</li>
                                        <li>{{ trans('sw.w_ultra_step_2')}}</li>
                                        <li>{{ trans('sw.w_ultra_step_3')}}</li>
                                    </ol>
                                </div>
                            </div>
                            <!--end::Content-->
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Notice-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->

             <!--begin::Card-->
            <div class="card card-flush mt-7">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h4 class="text-dark fw-bold">
                            <i class="ki-outline ki-shield-tick fs-2 me-2 text-primary"></i>
                            {{ trans('sw.token_status')}}
                        </h4>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                     @if(@$token_check_error)
                        <div class="notice d-flex bg-light-danger rounded border-danger border border-dashed p-6">
                            <i class="ki-outline ki-shield-cross fs-2tx text-danger me-4"></i>
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-semibold">
                                    <h4 class="text-gray-900 fw-bold">{{ trans('sw.token_invalid')}}</h4>
                                    <div class="fs-6 text-gray-700">{{ trans('sw.token_invalid_desc')}}</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="notice d-flex bg-light-success rounded border-success border border-dashed p-6">
                            <i class="ki-outline ki-shield-tick fs-2tx text-success me-4"></i>
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-semibold">
                                    <h4 class="text-gray-900 fw-bold">{{ trans('sw.token_valid')}}</h4>
                                    <div class="fs-6 text-gray-700">{{ trans('sw.token_valid_desc')}}</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Sidebar-->
        <!--begin::Content-->
        <div class="flex-lg-row-fluid ms-lg-10">
            @if(in_array('storeWAUltraToken', (array)$swUser->permissions) || $swUser->is_super_user)
            <!--begin::Settings Form-->
            <form action="{{route('sw.storeWAUltraToken')}}" method="post" class="form mb-10">
                {{csrf_field()}}
                <div class="card card-flush">
                    <div class="card-header">
                        <div class="card-title">
                            <h4 class="text-dark fw-bold">
                                <i class="ki-outline ki-gear fs-2 me-2 text-primary"></i>
                                {{ trans('sw.wa_ultra_settings')}}
                            </h4>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row">
                            <div class="col-md-5 fv-row">
                                <label class="required form-label">Instance ID</label>
                                <input name="instance_id" value="{{@$mainSettings->wa_details['wa_ultra_instance_id']}}" type="text" class="form-control" required>
                            </div>
                            <div class="col-md-5 fv-row">
                                <label class="required form-label">Token</label>
                                <input name="token" value="{{@$mainSettings->wa_details['wa_ultra_token']}}" type="text" class="form-control" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <span class="indicator-label">{{ trans('global.save')}}</span>
                                    <span class="indicator-progress">Please wait...
                                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <!--end::Settings Form-->
            @endif

            <!--begin::Message Form-->
            <form role="form" action="{{route('sw.storeWAUltra')}}" method="post"  onsubmit="return confirm('{{ trans('admin.are_you_sure')}}');" enctype="multipart/form-data" class="form">
                {{csrf_field()}}
                <div class="card card-flush">
                    <div class="card-header">
                         <div class="card-title">
                            <h4 class="text-dark fw-bold">
                                <i class="ki-outline ki-send fs-2 me-2 text-primary"></i>
                                {{ trans('sw.send_wa_message')}}
                            </h4>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <!-- Client Type -->
                        <div class="fv-row mb-10">
                            <label class="form-label required">{{ trans('sw.clients')}}</label>
                            <div class="d-flex flex-wrap gap-5">
                                <div class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="radio" name="client_type" value="1" id="client_type_1" checked>
                                    <label class="form-check-label" for="client_type_1">{{ trans('sw.new_entities')}}</label>
                                </div>
                                <div class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="radio" name="client_type" value="2" id="client_type_2">
                                    <label class="form-check-label" for="client_type_2">{{ trans('sw.daily_clients')}}</label>
                                </div>
                                <div class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="radio" name="client_type" value="3" id="client_type_3">
                                    <label class="form-check-label" for="client_type_3">{{ trans('sw.subscribed_clients')}}</label>
                                </div>
                                <div class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="radio" name="client_type" value="4" id="client_type_4">
                                    <label class="form-check-label" for="client_type_4">{{ trans('sw.subscribed_clients_active')}}</label>
                                </div>
                            </div>
                        </div>
                        <!-- Phones -->
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
                        <!-- Message -->
                        <div class="fv-row mb-10">
                            <label class="form-label required">{{ trans('sw.message')}}</label>
                            <textarea class="form-control" rows="5"  name="message" id="message" required></textarea>
                             <div class="form-text mt-2" id="char-counter">
                                <span id="char-count">0</span> Characters
                            </div>
                        </div>
                        <!-- Image Upload -->
                        <div class="fv-row">
                            <label class="form-label">{{ trans('sw.upload_image')}}</label>
                            <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url('{{@$mainSettings->logo}}')">
                                <div class="image-input-wrapper w-125px h-125px" style="background-image: url('{{@$mainSettings->logo}}');"></div>
                                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change image">
                                    <i class="ki-outline ki-pencil fs-7"></i>
                                    <input type="file" name="image" accept=".png, .jpg, .jpeg" />
                                    <input type="hidden" name="avatar_remove" />
                                </label>
                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel image">
                                    <i class="ki-outline ki-cross fs-2"></i>
                                </span>
                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove image">
                                    <i class="ki-outline ki-cross fs-2"></i>
                                </span>
                            </div>
                            <div class="form-text">Allowed file types: png, jpg, jpeg.</div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end py-6">
                        <button type="reset" class="btn btn-light me-5">{{ trans('admin.reset')}}</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">{{ trans('global.send')}}</span>
                            <span class="indicator-progress">Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                </div>
            </form>
            <!--end::Message Form-->
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

            // Character counter logic
            $('#message').on('keyup input', function() {
                $('#char-count').text($(this).val().length);
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
                        $('#phones').val(response);
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



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
@section('list_add_button') <a href="{{route('sw.listMyNotificationLog')}}" class="btn btn-sm btn-flex btn-light-primary">
    <i class="ki-outline ki-list-ol fs-6"></i>
    {{ trans('sw.notification_logs')}}</a>
@endsection
@section('page_body')
    <form role="form" action="{{route('sw.storeMyNotification')}}" method="post" enctype="multipart/form-data" onsubmit="return confirm('{{ trans('admin.are_you_sure')}}');" class="form d-flex flex-column flex-lg-row">
    {{csrf_field()}}
    <!--begin::Aside column-->
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
            <!--begin::Info-->
            <div class="card card-flush py-4">
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
                                        <li>{{ trans('sw.n_step_1a')}}</li>
                                        <li>{{ trans('sw.n_step_2a')}}</li>
                                        <li>{{ trans('sw.n_step_4')}}</li>
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
            <!--end::Info-->
            <!--begin::App links-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h4 class="text-dark fw-bold">
                            <i class="ki-outline ki-rocket fs-2 me-2 text-primary"></i>
                            {{ trans('sw.app_download')}}
                        </h4>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <a href="{{$mainSettings->android_app}}" target="_blank" class="d-flex align-items-center border border-dashed border-gray-300 rounded p-4 mb-4">
                        <i class="fab fa-google-play fs-2x text-success me-4"></i>
                        <div class="d-flex flex-column">
                            <span class="fs-6 fw-bold text-gray-800">{{ trans('sw.android_app_download')}}</span>
                            <span class="fs-7 text-muted">{{ trans('sw.download_from_google_play')}}</span>
                        </div>
                    </a>
                    <a href="{{$mainSettings->ios_app}}" target="_blank" class="d-flex align-items-center border border-dashed border-gray-300 rounded p-4">
                        <i class="fab fa-apple fs-2x text-gray-900 me-4"></i>
                        <div class="d-flex flex-column">
                            <span class="fs-6 fw-bold text-gray-800">{{ trans('sw.ios_app_download')}}</span>
                            <span class="fs-7 text-muted">{{ trans('sw.download_from_app_store')}}</span>
                        </div>
                    </a>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::App links-->
        </div>
        <!--end::Aside column-->
        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!--begin::Notification form-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h4 class="text-dark fw-bold">
                            <i class="ki-outline ki-notification-on fs-2 me-2 text-primary"></i>
                            {{ trans('sw.send_notification')}}
                        </h4>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Row - Title and Message-->
                    <div class="row fv-row mb-7">
                        <div class="col-md-12 fv-row mb-7">
                            <label class="form-label required">{{ trans('sw.title')}}</label>
                            <input type="text" class="form-control" name="title" id="title" required>
                        </div>
                        <div class="col-md-12 fv-row">
                            <label class="form-label required">{{ trans('sw.message')}}</label>
                            <textarea class="form-control" rows="5" name="message" id="message" required></textarea>
                        </div>
                    </div>
                    <!--end::Row-->

                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-4"></div>
                    <!--end::Separator-->

                    <!--begin::Row - Image Upload-->
                    <div class="row fv-row mb-7">
                        <div class="col-md-12">
                            <label class="form-label">{{ trans('sw.upload_image')}}</label>
                            <!--begin::Image input-->
                            <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url('{{$mainSettings->logo}}')">
                                <!--begin::Preview existing avatar-->
                                <div class="image-input-wrapper w-125px h-125px" style="background-image: url('{{$mainSettings->logo}}');"></div>
                                <!--end::Preview existing avatar-->
                                <!--begin::Label-->
                                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change image">
                                    <i class="ki-outline ki-pencil fs-7"></i>
                                    <!--begin::Inputs-->
                                    <input type="file" name="image" accept=".png, .jpg, .jpeg" />
                                    <input type="hidden" name="avatar_remove" />
                                    <!--end::Inputs-->
                                </label>
                                <!--end::Label-->
                                <!--begin::Cancel-->
                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel image">
                                    <i class="ki-outline ki-cross fs-2"></i>
                                </span>
                                <!--end::Cancel-->
                                <!--begin::Remove-->
                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove image">
                                    <i class="ki-outline ki-cross fs-2"></i>
                                </span>
                                <!--end::Remove-->
                            </div>
                            <!--end::Image input-->
                            <div class="form-text">Allowed file types: png, jpg, jpeg.</div>
                        </div>
                    </div>
                    <!--end::Row-->

                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-4"></div>
                    <!--end::Separator-->

                    <!--begin::Row - Type and URL-->
                    <div class="row fv-row mb-7">
                        <div class="col-md-6 fv-row">
                            <label class="form-label required" for="type">{{ trans('sw.type')}}</label>
                            <select required id="type" name="type" class="form-select">
                                <option value="">{{ trans('sw.notification_select_type_msg')}}</option>
                                <option value="{{\Modules\Software\Classes\TypeConstants::NOTIFICATION_EXTERNAL_URL}}">{{ trans('sw.url')}}</option>
                                <option value="{{\Modules\Software\Classes\TypeConstants::NOTIFICATION_GENERAL_MESSAGE}}">{{ trans('sw.message')}}</option>
                            </select>
                        </div>
                        <div class="col-md-6 fv-row" id="url_row" style="display: none;">
                            <label class="form-label" for="url">{{ trans('sw.notification_url')}}</label>
                            <input id="url" value="{{ old('url') }}" name="url" type="url" class="form-control">
                        </div>
                    </div>
                    <!--end::Row-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Notification form-->

            <!--begin::Members-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h4 class="text-dark fw-bold">
                            <i class="ki-outline ki-user fs-2 me-2 text-primary"></i>
                            {{ trans('sw.clients')}}
                        </h4>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    @if(count($members) > 0)
                        <!--begin::Send to all checkbox-->
                        <div class="form-check form-check-custom form-check-solid mb-5">
                            <input class="form-check-input" type="checkbox" value="1" name="member_code_all" id="member_code_all"/>
                            <label class="form-check-label fw-bold text-gray-700 fs-6" for="member_code_all">
                                {{ trans('sw.send_to_all_clients')}}
                            </label>
                        </div>
                        <!--end::Send to all checkbox-->

                        <!--begin::Members select-->
                        <div id="member_select_wrapper">
                            <label class="form-label">{{ trans('sw.or_select_specific_clients')}}</label>
                            <select class="form-select" id="member_codes" name="member_codes[]" data-control="select2" data-placeholder="{{ trans('sw.select_clients')}}" data-allow-clear="true" multiple="multiple">
                                <option></option>
                                @foreach($members as $member)
                                    <option value="{{@$member->member->code}}">{{@$member->member->name}} ({{@$member->member->code}})</option>
                                @endforeach
                            </select>
                        </div>
                        <!--end::Members select-->
                    @else
                        <!--begin::Empty state-->
                        <div class="text-center p-7">
                            <i class="ki-outline ki-users fs-4x text-muted"></i>
                            <p class="fs-6 fw-semibold text-muted mt-3">{{ trans('sw.no_record_found')}}</p>
                        </div>
                        <!--end::Empty state-->
                    @endif
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Members-->

            <!--begin::Form Actions-->
            <div class="d-flex justify-content-end">
                <button type="reset" class="btn btn-light me-5">{{ trans('admin.reset')}}</button>
                <button type="submit" class="btn btn-primary">
                    <span class="indicator-label">{{ trans('global.send')}}</span>
                    <span class="indicator-progress">Please wait...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                    </span>
                </button>
            </div>
            <!--end::Form Actions-->
        </div>
        <!--end::Main column-->
    </form>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            // Handle notification type change
            $('#type').on('change', function (event) {
                if ($(this).val() == "{{\Modules\Software\Classes\TypeConstants::NOTIFICATION_EXTERNAL_URL}}") {
                    $('#url_row').show();
                    $('#url').prop('required', true);
                } else {
                    $('#url_row').hide();
                    $('#url').prop('required', false).val('');
                }
            });

            // Handle send to all clients checkbox
            $('#member_code_all').on('change', function() {
                if ($(this).is(':checked')) {
                    // Disable member selection when "Send to all" is checked
                    $('#member_codes').val(null).trigger('change').prop('disabled', true);
                    $('#member_select_wrapper').css('opacity', '0.5').css('pointer-events', 'none');
                } else {
                    // Enable member selection and clear it when "Send to all" is unchecked
                    $('#member_codes').prop('disabled', false);
                    $('#member_codes').val([]).trigger('change');
                    $('#member_select_wrapper').css('opacity', '1').css('pointer-events', 'auto');
                }
            });

            // Handle member selection - uncheck "Send to all" if members are selected
            $('#member_codes').on('select2:select', function() {
                if ($(this).val() && $(this).val().length > 0) {
                    $('#member_code_all').prop('checked', false);
                    $('#member_select_wrapper').css('opacity', '1').css('pointer-events', 'auto');
                }
            });

            // Initialize KTImageInput
             KTImageInput.init();
        });
    </script>
@endsection



@extends('software::layouts.form')
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
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('listNotification') }}" class="text-muted text-hover-primary">{{ trans('admin.notifications')}}</a>
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
@section('page_body')
    @if(session('notification_recipients'))
        <div class="alert alert-success">
            Notification Sent to <strong>{{ session('notification_recipients') }}</strong> Customers
        </div>
    @elseif(session('notification_error'))
        <div class="alert alert-warning">
            <strong>{{ session('notification_error') }}</strong>
        </div>
    @endif

    <!--begin::Notification Form-->
    <form method="post" action="" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
        @if(request('test'))
            <input type="hidden" name="test" value="1">
        @endif
        {{csrf_field()}}
        
        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!--begin::Notification Details-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{$title}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">Notification Title</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="title" class="form-control mb-2" 
                               placeholder="Enter notification title" 
                               value="{{ old('title') }}" 
                               id="title" required />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">Notification Body</label>
                        <!--end::Label-->
                        <!--begin::Textarea-->
                        <textarea name="body" class="form-control mb-2" 
                                  placeholder="Enter notification body" 
                                  id="body">{{ old('body') }}</textarea>
                        <!--end::Textarea-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">Type</label>
                        <!--end::Label-->
                        <!--begin::Select-->
                        <select name="type" class="form-select mb-2" id="type" required>
                            <option value="">Choose One Of The Following Types...</option>
                            <option value="{{\App\Modules\Notification\Http\enums\NotificationType::external_url}}">URL</option>
                            <option value="{{\App\Modules\Notification\Http\enums\NotificationType::general_message}}">Message</option>
                        </select>
                        <!--end::Select-->
                    </div>
                    <!--end::Input group-->
                    
                    <div id="types"></div>
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">Notification URL</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="url" class="form-control mb-2" 
                               placeholder="Enter notification URL" 
                               value="{{ old('url') }}" 
                               id="url" disabled />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Notification Details-->
            
            <!--begin::Form Actions-->
            <div class="d-flex justify-content-end">
                <!--begin::Button-->
                <button type="reset" class="btn btn-light me-5">Reset</button>
                <!--end::Button-->
                <!--begin::Button-->
                <button type="submit" class="btn btn-primary">
                    <span class="indicator-label">Submit</span>
                    <span class="indicator-progress">Please wait... 
                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
                <!--end::Button-->
            </div>
            <!--end::Form Actions-->
        </div>
        <!--end::Main column-->
    </form>
    <!--end::Notification Form-->
    <div style="height: 200px"></div>
@endsection

@section('scripts')
    @parent
    <script>
        $(document).on('changed.bs.select', '#type', function (event) {
            switch ($(this).val()) {
                case '1':
                    $('#url').removeAttr('disabled');
                    break;
                case '2':
                    $('#url').attr('disabled', 'disabled');
            }
        });
    </script>

@endsection

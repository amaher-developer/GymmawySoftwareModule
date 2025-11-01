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
        <li class="breadcrumb-item text-gray-900">{{ $title }}</li>
        <!--end::Item-->
    </ul>
    <!--end::Breadcrumb-->
@endsection
@section('form_title') {{ @$title }} @endsection
@section('styles')
    {{-- Required CSS Libraries --}}
    <link rel="stylesheet" type="text/css" href="{{asset('/')}}resources/assets/admin/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
    <link href="{{asset('/')}}resources/assets/admin/global/scripts/css/fileupload.css" rel="stylesheet"
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
    </style>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
@endsection
@section('page_body')




    <!--begin::User Form-->
    <form method="post" action="" class="form" enctype="multipart/form-data">
        {{csrf_field()}}
        
        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!--begin::User Details-->
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
                        <label class="form-label">{{ trans('sw.the_image')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input id="SubscribedClientsInputPhoto"
                               data-default-file="{{asset('uploads/settings')}}/default.jpg"
                               name="image" type="file" class="dropify mb-2" data-height="200"
                               accept=".jpg, .png, image/jpeg, image/png"/>
                        <input type="hidden" name="image" id="photo_camera">
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.name')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="name" class="form-control mb-2" 
                               placeholder="{{ trans('sw.name')}}" 
                               value="{{ old('name', $user->name) }}" 
                               required />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.job_title')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="title" class="form-control mb-2" 
                               placeholder="{{ trans('sw.job_title')}}" 
                               value="{{ old('title', $user->title) }}" />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
{{--            <div class="form-group col-md-12">--}}
{{--                <label class="col-md-3  control-label">{{ trans('sw.branch')}} <span class="required">*</span></label>--}}
{{--                <div class="col-md-9">--}}
{{--                    <select id="branch_setting_id" name="branch_setting_id" class="form-control select2" required>--}}
{{--                        <option value="">{{ trans('sw.choose')}}...</option>--}}
{{--                        @foreach($branches as $branch)--}}
{{--                        <option value="{{$branch->id}}" @if($branch->id == old('branch_setting_id', @$user->branch_setting_id)) selected="" @endif>{{$branch->name}}</option>--}}
{{--                        @endforeach--}}
{{--                    </select>--}}
{{--                </div>--}}
{{--            </div>--}}
                    <!--begin::Section: Basic Information-->
                    <div class="separator separator-dashed my-10"></div>
                    <div class="mb-10">
                        <h4 class="text-dark fw-bold mb-5">{{ trans('sw.basic_information') ?? 'Basic Information' }}</h4>
                    </div>
                    <!--end::Section: Basic Information-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.email')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input name="email" value="{{ old('email', $user->email) }}" type="email" class="form-control"
                           placeholder="{{ trans('sw.enter_email')}}" required>
                        <!--end::Input-->
                </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.password')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input name="password" value="" type="password" class="form-control" autocomplete="new-password"
                           placeholder="{{ trans('sw.enter_password')}}">
                        <!--end::Input-->
                        <!--begin::Helper text-->
                        <!-- <div class="form-text">{{ trans('sw.leave_empty_to_keep_current_password') ?? 'Leave empty to keep current password' }}</div> -->
                        <!--end::Helper text-->
                </div>
                    <!--end::Input group-->
                    
                    <!--begin::Row-->
                    <div class="row mb-10">
                        <div class="col-md-6 fv-row">
                            <!--begin::Label-->
                            <label class="required form-label">{{ trans('sw.phone')}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                    <input name="phone" value="{{ old('phone', $user->phone) }}" type="text" class="form-control"
                           placeholder="{{ trans('sw.enter_phone')}}" required>
                            <!--end::Input-->
                </div>
                        <div class="col-md-6 fv-row">
                            <!--begin::Label-->
                            <label class="form-label">{{ trans('sw.address')}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                    <input name="address" value="{{ old('address', $user->address) }}" type="text" class="form-control"
                                   placeholder="{{ trans('sw.enter_address')}}">
                            <!--end::Input-->
                </div>
            </div>
                    <!--end::Row-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.salary')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input name="salary" value="{{ old('salary', $user->salary) }}" type="number" class="form-control"
                               placeholder="{{ trans('sw.enter_salary')}}" step="0.01">
                        <!--end::Input-->
                </div>
                    <!--end::Input group-->

                    <!--begin::Section: Additional Information-->
                    <div class="separator separator-dashed my-10"></div>
                    <div class="mb-10">
                        <h4 class="text-dark fw-bold mb-5">{{ trans('sw.additional_information') ?? 'Additional Information' }}</h4>
            </div>
                    <!--end::Section: Additional Information-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.fingerprint')}}</label>
                        <!--end::Label-->
                        <!--begin::Input group-->
                        <div class="input-group">
                            <input class="form-control" placeholder="{{ trans('sw.fingerprint_id_data')}}"
                               name="fp_id" min="0"
                               value="{{ old('fp_id', $user->fp_id) }}"
                                   type="number">
                            <span class="input-group-text">
                                <i class="material-icons">fingerprint</i>
                        </span>
                    </div>
                        <!--end::Input group-->
                </div>
                    <!--end::Input group-->
                    
                    <!--begin::Row-->
                    <div class="row mb-10">
                        <div class="col-md-6 fv-row">
                            <!--begin::Label-->
                            <label class="required form-label">{{ trans('sw.start_time_work')}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input class="form-control timepicker timepicker-no-seconds" autocomplete="off" 
                                   placeholder="{{ trans('sw.start_time_work')}}"
                           name="start_time_work"
                           value="{{ old('start_time_work', $user->start_time_work) }}"
                                   type="text" required>
                            <!--end::Input-->
                    </div>
                        <div class="col-md-6 fv-row">
                            <!--begin::Label-->
                            <label class="required form-label">{{ trans('sw.end_time_work')}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input class="form-control timepicker timepicker-no-seconds" autocomplete="off" 
                                   placeholder="{{ trans('sw.end_time_work')}}"
                           name="end_time_work"
                           value="{{ old('end_time_work', $user->end_time_work) }}"
                                   type="text" required>
                            <!--end::Input-->
                </div>
            </div>
                    <!--end::Row-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::User Details-->



            @include('software::layouts.permissions')
       <div class="clearfix"><br/></div>
            <div class=" card-flush py-4">
<!--begin::Form Actions-->
<div class="d-flex justify-content-end mt-5">
            <!--begin::Button-->
            <button type="reset" class="btn btn-light me-5">{{ trans('admin.reset')}}</button>
            <!--end::Button-->
            <!--begin::Button-->
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label">{{ trans('global.save')}}</span>
                <span class="indicator-progress">Please wait... 
                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                </span>
            </button>
            <!--end::Button-->
        </div>
        <!--end::Form Actions-->        
            </div>
        </div>
        </div>
        

        

        
        
    </form>




    <!-- Modal Camera with effects -->
    <div class="modal" id="modalCamera">
        <div class="modal-dialog modal-dialog-scrollable " role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">{{ trans('sw.camera_snapshot')}}</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                                aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body text-center">
                    <div class="img-fluid" id="my_camera"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-primary" onclick="take_snapshot()" data-dismiss="modal" type="button">
                        {{ trans('sw.camera_snapshot')}}
                    </button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal"
                            type="button">  {{ trans('sw.exist')}}</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Camera with effects-->
@endsection


@section('sub_scripts')
    {{-- Required JavaScript Libraries --}}
    <script src="{{asset('/')}}resources/assets/admin/global/scripts/metronic.js" type="text/javascript"></script>
    <script src="{{asset('/')}}resources/assets/admin/pages/scripts/components-pickers.js"></script>
    <script type="text/javascript" src="{{asset('/')}}resources/assets/admin/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
    
    {{-- File Upload Scripts --}}
    <script src="{{asset('/')}}resources/assets/admin/global/scripts/js/fileupload.js"></script>
    <script src="{{asset('/')}}resources/assets/admin/global/scripts/js/file-upload.js"></script>

    {{-- Form Initialization --}}
    <script>
        $(document).ready(function() {
            // Initialize time pickers and components
            ComponentsPickers.init();
            
            // Initialize dropify file upload
        $('.dropify-infos-message').html("{{ trans('sw.upload_image')}}");
        $('.dropify-message p:first').html("{{ trans('sw.upload_image')}}");
        $('.dropify-clear').html("{{ trans('sw.remove')}}");
            
            // Initialize bootstrap tabs
            $('.nav-tabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                // Tab has been activated, any additional logic can go here
            });
        });

        // Camera modal effect handler
        $('.modal-effect').on('click', function (e) {
            e.preventDefault();
            var effect = $(this).attr('data-effect');
            $('#modalCamera').addClass(effect);
        });
        
        // Permission group selector handler
        $(document).ready(function() {
            $('#permission_group_id').select2({
                placeholder: "{{ trans('sw.select_permission_group')}}",
                allowClear: true,
                width: '100%'
            });
            
            $('#permission_group_id').on('change', function() {
                var selectedOption = $(this).find('option:selected');
                var permissions = selectedOption.data('permissions');
                
                if (permissions && permissions.length > 0) {
                    // Uncheck all permissions first
                    $('input[name="permissions[]"]').prop('checked', false);
                    
                    // Check permissions from the group
                    permissions.forEach(function(permission) {
                        $('input[name="permissions[]"][value="' + permission + '"]').prop('checked', true);
                    });
                } else {
                    // If "Custom Permissions" is selected, keep current selections
                    // User can manually check/uncheck as needed
                }
            });
        });
    </script>

@endsection


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
            <a href="{{ route('sw.listPTClass') }}" class="text-muted text-hover-primary">{{ trans('sw.pt_classes')}}</a>
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
    <link rel="stylesheet" type="text/css" href="{{asset('resources/assets/admin/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('resources/assets/admin/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css')}}"/>

    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <link href="{{asset('resources/assets/admin/global/plugins/bootstrap-colorpicker/css/colorpicker.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('resources/assets/admin/global/plugins/jquery-minicolors/jquery.minicolors.css')}}" rel="stylesheet" type="text/css" />
<style>
    .form-check-custom {
        padding-bottom: 10px;
    }
    </style>
    @endsection
@section('form_title') {{ @$title }} @endsection
@section('page_body')
    <!--begin::PT Class Form-->
    <form method="post" action="" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
        {{csrf_field()}}
        
        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!--begin::Class Details-->
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
                    <div class="row">
                        <div class="col-lg-6">
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.pt_subscription')}}</label>
                        <!--end::Label-->
                        <!--begin::Select-->
                                <select name="pt_subscription_id" class="form-select mb-2 select2" required>
                            <option value="">{{ trans('admin.choose')}}...</option>
                            @foreach($subscriptions as $subscription)
                                <option value="{{$subscription->id}}"
                                        @if($subscription->id == old('pt_subscription_id', $class->pt_subscription_id)) selected @endif>
                                    {{$subscription->name}}
                                </option>
                            @endforeach
                        </select>
                        <!--end::Select-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.pt_classes_num')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="number" name="classes" class="form-control mb-2" 
                               placeholder="{{ trans('sw.pt_classes_num')}}" 
                               value="{{ old('classes', $class->classes) }}" 
                                       required />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.price')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="number" name="price" class="form-control mb-2" 
                               placeholder="{{ trans('sw.enter_price')}}" 
                               value="{{ old('price', $class->price) }}" 
                                       min="0" step="0.01" required />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.pt_members_num_limit')}}</label>
                        <!--end::Label-->
                        <!--begin::Switch-->
                                <div class="form-check form-switch form-check-custom form-check-solid">
                                    <input type="checkbox" class="form-check-input" 
                                   id="check_member_limit" 
                                   @if(@$class->member_limit) checked @endif />
                        </div>
                        <!--end::Switch-->
                        
                        <!--begin::Spinner-->
                                <div id="spinner1_container" style="display: none;">
                            <div class="input-group">
                                        <input type="number" value="{{ old('member_limit', @$class->member_limit) }}" class="form-control" id="member_limit" name="member_limit" max="1000">
                            </div>
                        </div>
                        <!--end::Spinner-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.workouts_per_day')}}</label>
                        <!--end::Label-->
                        <!--begin::Switch-->
                                <div class="form-check form-switch form-check-custom form-check-solid">
                                    <input type="checkbox" name="check_workouts_per_day" @if(@$class->workouts_per_day) checked @endif class="form-check-input" id="check_workouts_per_day">
                        </div>
                        <!--end::Switch-->
                        
                        <!--begin::Spinner-->
                                <div id="spinner2_container" style="display: none;">
                            <div class="input-group">
                                        <input type="number" value="{{ old('workouts_per_day', @$class->workouts_per_day) }}" class="form-control" id="workouts_per_day" name="workouts_per_day" max="10">
                            </div>
                        </div>
                        <!--end::Spinner-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.time_day')}}</label>
                        <!--end::Label-->
                        <!--begin::Switch-->
                                <div class="form-check form-switch form-check-custom form-check-solid">
                                    <input type="checkbox" @if(@$class->start_time_day && @$class->end_time_day) checked @endif class="form-check-input" name="time_day" id="time_day">
                        </div>
                        <!--end::Switch-->
                        
                        <!--begin::Time pickers-->
                                <div class="row" id="time_day_container" style="display: none;">
                            <div class="col-md-6">
                                        <label class="form-label">{{ trans('sw.time_from')}}</label>
                                <div class="input-group">
                                            <input name="start_time_day" id="start_time_day" value="{{ old('start_time_day', @$class->start_time_day) }}" type="text" class="form-control timepicker timepicker-no-seconds">
                                            <span class="input-group-text">
                                                <i class="ki-outline ki-time"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                        <label class="form-label">{{ trans('sw.time_to')}}</label>
                                <div class="input-group">
                                            <input type="text" name="end_time_day" id="end_time_day" value="{{ old('end_time_day', @$class->end_time_day) }}" class="form-control timepicker timepicker-no-seconds">
                                            <span class="input-group-text">
                                                <i class="ki-outline ki-time"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <!--end::Time pickers-->
                    </div>
                    <!--end::Input group-->

                        </div>
                        <div class="col-lg-6">
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.name_in_arabic')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input id="name_ar" value="{{ old('name_ar', $class->name_ar) }}"
                               placeholder="{{ trans('sw.enter_name_in_arabic')}}"
                               name="name_ar" type="text" class="form-control mb-2" required>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.name_in_english')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input id="name_en" value="{{ old('name_en', $class->name_en) }}"
                               placeholder="{{ trans('sw.enter_name_in_english')}}"
                               name="name_en" type="text" class="form-control mb-2" required>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->

                    @if(@$mainSettings->active_mobile)
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.content_in_arabic')}}</label>
                        <!--end::Label-->
                        <!--begin::Textarea-->
                        <textarea id="content_ar" maxlength="250"
                                  placeholder="{{ trans('sw.enter_content_in_arabic')}}"
                                  name="content_ar" type="text" class="form-control mb-2">{{ old('content_ar', $class->content_ar) }}</textarea>
                        <!--end::Textarea-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.content_in_english')}}</label>
                        <!--end::Label-->
                        <!--begin::Textarea-->
                        <textarea id="content_en" maxlength="250"
                                  placeholder="{{ trans('sw.enter_content_in_english')}}"
                                  name="content_en" type="text" class="form-control mb-2">{{ old('content_en', $class->content_en) }}</textarea>
                        <!--end::Textarea-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.visible_invisible')}}</label>
                        <!--end::Label-->
                        <!--begin::Checkboxes-->
                        <div class="d-flex flex-wrap gap-5">
                            <!--begin::Checkbox is_system first, default checked -->
                            <label class="form-check form-check-custom form-check-solid">
                                <input type="checkbox" name="is_system" value="1" 
                                       class="form-check-input" @if(old('is_system', @$class->is_system ?? 1)) checked @endif />
                                <span class="form-check-label fw-semibold text-gray-700">{{ trans('sw.system')}}</span>
                            </label>
                            <!--end::Checkbox-->
                            <!--begin::Checkbox-->
                            <label class="form-check form-check-custom form-check-solid">
                                    <input type="checkbox" name="is_mobile" value="1" 
                                   class="form-check-input" @if(@$class->is_mobile) checked="" @endif />
                                <span class="form-check-label fw-semibold text-gray-700">{{ trans('sw.mobile')}}</span>
                            </label>
                            <!--end::Checkbox-->
                            
                            <!--begin::Checkbox-->
                            <label class="form-check form-check-custom form-check-solid">
                                    <input type="checkbox" name="is_web" value="1" 
                                   class="form-check-input" @if(@$class->is_web) checked="" @endif />
                                <span class="form-check-label fw-semibold text-gray-700">{{ trans('sw.web')}}</span>
                            </label>
                            <!--end::Checkbox-->
                        </div>
                        <!--end::Checkboxes-->
                    </div>
                    <!--end::Input group-->
                    @endif
                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="form-label">{{ trans('sw.class_color') }}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" class="form-control" name="class_color" value="{{old('class_color', $class->class_color ?? '#bbbbbb')}}" id="minicolors-input">
                                <!--end::Input-->
                            </div>
                            <!--end::Input group-->
                        </div>
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Class Details-->

            <!--begin::Class Days-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{ trans('sw.class_days')}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                    <th class="w-100px">{{ trans('sw.status')}}</th>
                                    <th>{{ trans('sw.day')}}</th>
                                    <th>{{ trans('sw.time_from')}}</th>
                                    <th>{{ trans('sw.time_to')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $days = [
                                        6 => 'sat', 0 => 'sun', 1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thurs', 5 => 'fri'
                                    ];
                                @endphp
                                @foreach($days as $dayNum => $dayKey)
                                <tr>
                                    <td>
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input name="reservation_details[work_days][{{$dayNum}}][status]"
                                                   value="1" {{@$class->reservation_details['work_days'][$dayNum]['status'] ? "checked" : ""}}
                                                   type="checkbox" class="form-check-input">
                                        </div>
                                    </td>
                                    <td>{{ trans('sw.' . $dayKey)}}</td>
                                    <td>
                                        <div class="input-group">
                                            <input name="reservation_details[work_days][{{$dayNum}}][start]"
                                                   value="{{@$class->reservation_details['work_days'][$dayNum]['start']}}"
                                                   type="text" class="form-control timepicker timepicker-24">
                                            <span class="input-group-text"><i class="ki-outline ki-time"></i></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <input type="text" name="reservation_details[work_days][{{$dayNum}}][end]"
                                                   value="{{@$class->reservation_details['work_days'][$dayNum]['end']}}"
                                                   class="form-control timepicker timepicker-24">
                                            <span class="input-group-text"><i class="ki-outline ki-time"></i></span>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Class Days-->

            <!--begin::Form actions-->
            <div class="d-flex justify-content-end">
                <button type="reset" class="btn btn-light me-5">{{ trans('admin.reset')}}</button>
                <button type="submit" class="btn btn-primary">
                    <i class="ki-outline ki-check fs-2"></i>
                    {{ trans('global.save')}}
                </button>
            </div>
            <!--end::Form actions-->
        </div>
    </form>
@endsection
@section('scripts')
    <script type="text/javascript" src="{{asset('resources/assets/admin/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('resources/assets/admin/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js')}}"></script>

    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <script src="{{asset('resources/assets/admin/global/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.js')}}" type="text/javascript"></script>
    <script src="{{asset('resources/assets/admin/global/plugins/jquery-minicolors/jquery.minicolors.min.js')}}" type="text/javascript"></script>
    <!-- END PAGE LEVEL PLUGINS -->

    <script>
        $(document).ready(function() {

            $('#minicolors-input').minicolors();

        $('.timepicker-no-seconds').timepicker({
            autoclose: true,
            minuteStep: 5
        });

        $('.timepicker-24').timepicker({
            autoclose: true,
            minuteStep: 5,
            showSeconds: false,
            showMeridian: false
        });

            function toggleVisibility(checkbox, container) {
                if (checkbox.is(':checked')) {
                    container.slideDown();
                    container.find('input').removeAttr("disabled").attr("required", true);
            } else {
                    container.slideUp();
                    container.find('input').attr("disabled", true).removeAttr("required");
                }
            }

            // Time Day Switch
            toggleVisibility($('#time_day'), $('#time_day_container'));
            $('#time_day').on('change', function () {
                toggleVisibility($(this), $('#time_day_container'));
            });

            // Workouts per Day Switch
            toggleVisibility($('#check_workouts_per_day'), $('#spinner2_container'));
            $('#check_workouts_per_day').on('change', function () {
                toggleVisibility($(this), $('#spinner2_container'));
            });

            // Member Limit Switch
            toggleVisibility($('#check_member_limit'), $('#spinner1_container'));
            $('#check_member_limit').on('change', function () {
                toggleVisibility($(this), $('#spinner1_container'));
            });
        });
    </script>
@endsection

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

    <link rel="stylesheet" type="text/css"
          href="{{asset('resources/assets/new_front/global/plugins/bootstrap-summernote/summernote.css')}}">

<style>
    .member-info li{
        list-style-type: none;
        line-height: 34px;
    }
    .member-info{
        background: #9e9e9e73;
        border-radius: 8px !important;
        margin: 0px;
        padding: 10px 0;
    }
</style>
@endsection
@section('page_body')
    <!--begin::Training Task Form-->
    <form method="post" action="" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
        {{csrf_field()}}
        
        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!--begin::Member Details-->
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
                        <label class="required form-label">{{ trans('sw.member_id')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="member_id" class="form-control mb-2" 
                               placeholder="{{ trans('sw.enter_member_id')}}" 
                               value="{{ old('member_id', @$member->member->code) }}" 
                               id="member_id" required />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Member Info-->
                    <div class="mb-10 fv-row">
                        <div class="member-info row">
                            <div class="col-md-6">
                                <b>{{ trans('sw.name')}}:</b> <span id="store_member_name">{{@$member->member->name ? @$member->member->name : '-'}}</span>
                            </div>
                            <div class="col-md-6">
                                <b>{{ trans('sw.phone')}}:</b> <span id="store_member_phone">{{@$member->member->phone ? @$member->member->phone : '-'}}</span>
                            </div>
                        </div>
                    </div>
                    <!--end::Member Info-->
                    
                    <div class="separator separator-dashed my-5"></div>
                    
                    <!--begin::Input group-->
                    <div class="row mb-10">
                        <div class="col-md-6">
                            <label class="required form-label">{{ trans('sw.height')}}</label>
                            <input type="number" name="height" class="form-control mb-2" 
                                   placeholder="{{ trans('sw.enter_height')}}" 
                                   value="{{ old('height', $member->height) }}" 
                                   id="height" min="0" max="200" step="0.01" required />
                        </div>
                        <div class="col-md-6">
                            <label class="required form-label">{{ trans('sw.weight')}}</label>
                            <input type="number" name="weight" class="form-control mb-2" 
                                   placeholder="{{ trans('sw.enter_weight')}}" 
                                   value="{{ old('weight', $member->weight) }}" 
                                   id="weight" min="0" max="200" step="0.01" required />
                        </div>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="row mb-10">
                        <div class="col-md-6">
                            <label class="form-label">{{ trans('sw.neck_circumference')}}</label>
                            <input type="number" name="neck_circumference" class="form-control mb-2" 
                                   placeholder="{{ trans('sw.enter_neck_circumference')}}" 
                                   value="{{ old('neck_circumference', $member->neck_circumference) }}" 
                                   id="neck_circumference" min="0" max="200" step="0.01" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ trans('sw.chest_circumference')}}</label>
                            <input type="number" name="chest_circumference" class="form-control mb-2" 
                                   placeholder="{{ trans('sw.enter_chest_circumference')}}" 
                                   value="{{ old('chest_circumference', $member->chest_circumference) }}" 
                                   id="chest_circumference" min="0" max="200" step="0.01" />
                        </div>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="row mb-10">
                        <div class="col-md-6">
                            <label class="form-label">{{ trans('sw.arm_circumference')}}</label>
                            <input id="arm_circumference" value="{{ old('arm_circumference', $member->arm_circumference) }}"
                                   placeholder="{{ trans('sw.enter_arm_circumference')}}" min="0" max="200" step="0.01"
                                   name="arm_circumference" type="number" class="form-control mb-2">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ trans('sw.abdominal_circumference')}}</label>
                            <input id="abdominal_circumference" value="{{ old('abdominal_circumference', $member->abdominal_circumference) }}"
                                   placeholder="{{ trans('sw.enter_abdominal_circumference')}}" min="0" max="200" step="0.01"
                                   name="abdominal_circumference" type="number" class="form-control mb-2">
                        </div>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="row mb-10">
                        <div class="col-md-6">
                            <label class="form-label">{{ trans('sw.pelvic_circumference')}}</label>
                            <input id="pelvic_circumference" value="{{ old('pelvic_circumference', $member->pelvic_circumference) }}"
                                   placeholder="{{ trans('sw.enter_pelvic_circumference')}}" min="0" max="200" step="0.01"
                                   name="pelvic_circumference" type="number" class="form-control mb-2">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ trans('sw.thigh_circumference')}}</label>
                            <input id="thigh_circumference" value="{{ old('thigh_circumference', $member->thigh_circumference) }}"
                                   placeholder="{{ trans('sw.enter_thigh_circumference')}}" min="0" max="200" step="0.01"
                                   name="thigh_circumference" type="number" class="form-control mb-2">
                        </div>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.report')}}</label>
                        <!--end::Label-->
                        <!--begin::Textarea-->
                        <textarea id="content"  data-bv-trigger="keyup change"
                                  data-bv-notempty-message="{{ trans('generic::global.required')}}"
                                  class="form-control input-data summernote-textarea-ar mb-2"
                                  placeholder="{{ trans('sw.enter_report')}}"
                                  name="notes" type="text"  rows="10" required>{!! old('notes', $member->notes) !!}</textarea>
                        <!--end::Textarea-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.date')}}</label>
                        <!--end::Label-->
                        <!--begin::Date picker-->
                        <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                            <input class="form-control mb-2" autocomplete="off" placeholder="{{ trans('sw.date')}}"
                                   name="date"
                                   value="{{ old('date', @\Carbon\Carbon::parse($member->date)->toDateString()) }}"
                                   type="text" required>
                            <span class="input-group-btn">
                                <button class="btn btn-light" type="button"><i class="fa fa-calendar"></i></button>
                            </span>
                        </div>
                        <!--end::Date picker-->
                    </div>
                    <!--end::Input group-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Member Details-->

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


@section('sub_scripts')

    <script type="text/javascript"
            src="{{asset('resources/assets/new_front/global/plugins/bootstrap-summernote/summernote.min.js')}}"></script>
    <script>

        $('#member_id').keyup(function () {
            let member_id = $('#member_id').val();

            $.get("{{route('sw.getStoreMemberAjax')}}", {  member_id: member_id },
                function(result){
                    if(result){
                        $('#store_member_name').html(result.name);
                        $('#store_member_phone').html(result.phone);
                    }else{
                        $('#store_member_name').html('-');
                        $('#store_member_phone').html('-');
                    }
                }
            );
        });
    </script>
    <script>
        jQuery(document).ready(function() {
            ComponentsPickers.init();
        });

        $('.summernote-textarea').summernote({
            toolbar: [
                // [groupName, [list of button]]
                ['insert', ['link', 'table', 'hr', 'picture']],
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['fontsize', ['fontsize']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']]
            ],
            height: 200,
            focus: true,
            callbacks: {
                // onImageUpload: function (files, editor, welEditable) {
                //     // upload image to server and create imgNode...
                //     sendFile(files[0], editor, welEditable);
                // }
            }
        });
        $('.summernote-textarea-ar').summernote({
            popover: {
                image: [
                    ['imagesize', ['imageSize100', 'imageSize50', 'imageSize25']],
                    ['float', ['floatLeft', 'floatRight', 'floatNone']],
                    ['remove', ['removeMedia']]
                ]
            },
            toolbar: [
                // [groupName, [list of button]]
                ['insert', ['link', 'table', 'hr']],
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['fontsize', ['fontsize']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']]
            ],
            height: 200,
            focus: true,
            direction: 'rtl',
            callbacks: {
                // onImageUpload: function (files, editor, welEditable) {
                //     // upload image to server and create imgNode...
                //     sendFile(files[0], editor, welEditable);
                // }
            }
        });
    </script>
@endsection



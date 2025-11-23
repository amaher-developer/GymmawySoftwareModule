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
        .diet_plan_div{
            display: none;
        }
        .training_plan_div {
            display: none;
        }
    </style>
@endsection
@section('page_body')
    <!--begin::Training Member Form-->
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
                        <input type="text" name="barcode" class="form-control mb-2" 
                               placeholder="{{ trans('sw.enter_member_id')}}" 
                               value="{{ old('barcode', @$member->member->code) }}" 
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
                    
                    <!--begin::Input group-->
                    <div class="row mb-10">
                        <div class="col-md-6">
                            <label class="required form-label">{{ trans('sw.height')}}</label>
                            <input type="number" name="height" class="form-control mb-2" 
                                   placeholder="{{ trans('sw.enter_height')}}" 
                                   value="{{ old('height', $member->height) }}" 
                                   id="height" min="0" max="200" required />
                        </div>
                        <div class="col-md-6">
                            <label class="required form-label">{{ trans('sw.weight')}}</label>
                            <input type="number" name="weight" class="form-control mb-2" 
                                   placeholder="{{ trans('sw.enter_weight')}}" 
                                   value="{{ old('weight', $member->weight) }}" 
                                   id="weight" min="0" max="200" required />
                        </div>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.suffers_diseases')}}</label>
                        <!--end::Label-->
                        <!--begin::Textarea-->
                        <textarea name="diseases" class="form-control mb-2" 
                                  placeholder="{{ trans('sw.no_data_found')}}" 
                                  id="content" rows="4">{!! old('diseases', $member->diseases) !!}</textarea>
                        <!--end::Textarea-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.notes')}}</label>
                        <!--end::Label-->
                        <!--begin::Textarea-->
                        <textarea id="notes"
                                  placeholder="{{ trans('sw.enter_notes')}}"
                                  name="notes" type="text" class="form-control mb-2" rows="4">{!! old('notes', $member->notes) !!}</textarea>
                        <!--end::Textarea-->
                    </div>
                    <!--end::Input group-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Member Details-->

            <!--begin::Plan Details-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="fw-bold">{{ trans('sw.plan')}} <span class="required">*</span></h3>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.title')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input id="title" value="{{ old('title', $member->title) }}"
                               placeholder="{{ trans('sw.enter_title')}}" min="0" max="200"
                               name="title" type="text" class="form-control mb-2" required>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <div class="row">
                            <div class="col-md-6">
                                <!--begin::Label-->
                                <label class="required form-label">{{ trans('sw.plan')}}</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <select name="plan_id" id="plan_id" class="form-select form-select-solid mb-2 select2me" data-placeholder="{{ trans('admin.choose')}}..." required>
                                    <option value=""></option>
                                    @foreach($plans as $plan)
                                        <option value="{{$plan->id}}" @if($plan->id == @$member->plan_id) selected @endif  data-plan="{{$plan->content}}">
                                            {{$plan->title}}
                                        </option>
                                    @endforeach
                                </select>
                                <!--end::Select-->
                            </div>
                            <div class="col-md-6">
                                <!--begin::Label-->
                                <label class="required form-label">{{ trans('sw.plan_details')}}</label>
                                <!--end::Label-->
                                <!--begin::Textarea-->
                                <textarea id="plan_details"  data-bv-trigger="keyup change"
                                          data-bv-notempty-message="{{ trans('generic::global.required')}}"
                                          class="form-control input-data summernote-textarea-ar mb-2"
                                          placeholder="{{ trans('sw.plan')}}"
                                          name="plan_details" type="text"  rows="4" required>{!! old('plan_details', $member->plan_details) !!}</textarea>
                                <!--end::Textarea-->
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Plan Details-->

{{--            <div style="clear: both;float: none"><hr/></div>--}}







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

        $('.select2me').select2({
            placeholder: "{{ trans('admin.choose')}}",
            allowClear: true
        });


        $('#plan_id').on('change', function() {
            let plan = ( jQuery(this).children(":selected").attr("data-plan"));
            // $('#training_plan_details').html(training_plan);
            $('#plan_details').summernote('editor.pasteHTML', plan);
        });

        // $('#diet_plan_id').onchange(function (){
        //     alert('ssssss');
        //     let diet_plan = $('#diet_plan_id option:selected').attr('data-plan');
        //     $('#diet_plan_details').html(diet_plan);
        // })
        // $('#training_plan_id').on('change', 'select', function () {
        //     let training_plan = $('#training_plan_id option:selected').attr('data-plan');
        //     $('#training_plan_details').html(training_plan);
        // })



    </script>

    <script>
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




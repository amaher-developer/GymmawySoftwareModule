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
          href="{{asset('resources/assets/admin/global/plugins/bootstrap-summernote/summernote.css')}}">
@endsection
@section('page_body')
    <!--begin::Training Track Form-->
    <form method="post" action="" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
        {{csrf_field()}}
        
        <!--begin::Aside column-->
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-400px mb-7 me-lg-10">
            <!--begin::Member card-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{ trans('sw.member')}}</h2>
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
                    <div class="d-flex flex-column gap-5">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">{{ trans('sw.name')}}:</span>
                            <span id="store_member_name">{{@$member->member->name ? @$member->member->name : '-'}}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">{{ trans('sw.phone')}}:</span>
                            <span id="store_member_phone">{{@$member->member->phone ? @$member->member->phone : '-'}}</span>
                        </div>
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Member card-->
            <!--begin::Measurements card-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{ trans('sw.measurements')}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <div class="row">
                        <div class="col-6">
                            <label class="required form-label">{{ trans('sw.height')}}</label>
                            <input type="number" name="height" class="form-control mb-2" 
                                   placeholder="{{ trans('sw.enter_height')}}" 
                                   value="{{ old('height', $member->height) }}" 
                                   min="0" max="200" step="0.01" required />
                        </div>
                        <div class="col-6">
                            <label class="required form-label">{{ trans('sw.weight')}}</label>
                            <input type="number" name="weight" class="form-control mb-2" 
                                   placeholder="{{ trans('sw.enter_weight')}}" 
                                   value="{{ old('weight', $member->weight) }}" 
                                   min="0" max="200" step="0.01" required />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label">{{ trans('sw.neck_circumference')}}</label>
                            <input type="number" name="neck_circumference" class="form-control mb-2" 
                                   placeholder="{{ trans('sw.enter_neck_circumference')}}" 
                                   value="{{ old('neck_circumference', $member->neck_circumference) }}" 
                                   min="0" max="200" step="0.01" />
                        </div>
                        <div class="col-6">
                            <label class="form-label">{{ trans('sw.chest_circumference')}}</label>
                            <input type="number" name="chest_circumference" class="form-control mb-2" 
                                   placeholder="{{ trans('sw.enter_chest_circumference')}}" 
                                   value="{{ old('chest_circumference', $member->chest_circumference) }}" 
                                   min="0" max="200" step="0.01" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label">{{ trans('sw.arm_circumference')}}</label>
                            <input value="{{ old('arm_circumference', $member->arm_circumference) }}"
                                   placeholder="{{ trans('sw.enter_arm_circumference')}}" min="0" max="200" step="0.01"
                                   name="arm_circumference" type="number" class="form-control mb-2">
                        </div>
                        <div class="col-6">
                            <label class="form-label">{{ trans('sw.abdominal_circumference')}}</label>
                            <input value="{{ old('abdominal_circumference', $member->abdominal_circumference) }}"
                                   placeholder="{{ trans('sw.enter_abdominal_circumference')}}" min="0" max="200" step="0.01"
                                   name="abdominal_circumference" type="number" class="form-control mb-2">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label">{{ trans('sw.pelvic_circumference')}}</label>
                            <input value="{{ old('pelvic_circumference', $member->pelvic_circumference) }}"
                                   placeholder="{{ trans('sw.enter_pelvic_circumference')}}" min="0" max="200" step="0.01"
                                   name="pelvic_circumference" type="number" class="form-control mb-2">
                        </div>
                        <div class="col-6">
                            <label class="form-label">{{ trans('sw.thigh_circumference')}}</label>
                            <input value="{{ old('thigh_circumference', $member->thigh_circumference) }}"
                                   placeholder="{{ trans('sw.enter_thigh_circumference')}}" min="0" max="200" step="0.01"
                                   name="thigh_circumference" type="number" class="form-control mb-2">
                        </div>
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Measurements card-->
        </div>
        <!--end::Aside column-->
        
        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!--begin::Report Details-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{ trans('sw.report_details')}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.report')}}</label>
                        <!--end::Label-->
                        <!--begin::Textarea-->
                        <textarea id="content" class="form-control summernote-textarea mb-2"
                                  placeholder="{{ trans('sw.enter_report')}}"
                                  name="notes" rows="10" required>{!! old('notes', $member->notes) !!}</textarea>
                        <!--end::Textarea-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.date')}}</label>
                        <!--end::Label-->
                        <!--begin::Date picker-->
                        <input class="form-control" autocomplete="off" placeholder="{{ trans('sw.date')}}"
                                   name="date" id="kt_datepicker"
                                   value="{{ old('date', @\Carbon\Carbon::parse($member->date)->toDateString()) }}"
                                   type="text" required>
                        <!--end::Date picker-->
                    </div>
                    <!--end::Input group-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Report Details-->

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
    @parent
    <script type="text/javascript"
            src="{{asset('resources/assets/admin/global/plugins/bootstrap-summernote/summernote.min.js')}}"></script>
    <script>
        $(document).ready(function() {
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
            
            $("#kt_datepicker").flatpickr();

            $('.summernote-textarea').summernote({
                toolbar: [
                    ['insert', ['link', 'table', 'hr']],
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['fontsize', ['fontsize']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']]
                ],
                height: 200,
                focus: true
            });
        });
    </script>
@endsection

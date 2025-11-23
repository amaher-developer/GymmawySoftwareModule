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
{{--    <link rel="stylesheet" type="text/css"--}}
{{--          href="{{asset('/')}}resources/assets/new_front//global/plugins/select2/select2.css"/>--}}
{{--    <link href="{{asset('/')}}resources/assets/new_front/global/css/plugins-rtl.css" rel="stylesheet" type="text/css"/>--}}
{{--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />--}}
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

        margin: 0 15px;
    }
</style>
@endsection
@section('page_body')


    <!--begin::Member Information Form-->
    <form method="post" action="" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
        {{csrf_field()}}
        <input type="hidden" name="member" value="{{@$member->id}}">
        
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
                        <label class="required form-label">{{ trans('sw.name')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="name" class="form-control mb-2" 
                               placeholder="{{ trans('sw.enter_name')}}" 
                               value="{{ old('name', $member->name) }}" 
                               id="name"  />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.phone')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="phone" class="form-control mb-2" 
                               placeholder="{{ trans('sw.enter_phone')}}" 
                               value="{{ old('phone', $member->phone) }}" 
                               id="phone" required />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.national_id')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="national_id" class="form-control mb-2" 
                               placeholder="{{ trans('sw.enter_national_id')}}" 
                               value="{{ old('national_id', @$member->national_id) }}" 
                               id="national_id" />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.notes')}}</label>
                        <!--end::Label-->
                        <!--begin::Textarea-->
                        <textarea name="notes" class="form-control mb-2" 
                                  placeholder="{{ trans('sw.enter_notes')}}" 
                                  id="notes" rows="4">{{ old('notes', @$member->notes) }}</textarea>
                        <!--end::Textarea-->
                    </div>
                    <!--end::Input group-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Member Details-->
            
            <!--begin::Form Actions-->
            <div class="d-flex justify-content-end">
                <!--begin::Button-->
                <button type="reset" class="btn btn-light me-5">{{ trans('admin.reset')}}</button>
                <!--end::Button-->
                <!--begin::Button-->
                <button type="submit" class="btn btn-primary">
                    <span class="indicator-label">{{ trans('global.save')}}</span>
                    <span class="indicator-progress">Please wait... 
                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
                <!--end::Button-->
            </div>
            <!--end::Form Actions-->
        </div>
        <!--end::Main column-->
    </form>
    <!--end::Member Information Form-->
@endsection


@section('sub_scripts')
{{--    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">--}}




    <script>


        var selectedActivitiesPrice = 0;
        var vat = 0;
        $('#activities').change(function () {
            selectedActivitiesPrice = 0;
            $.each($("#activities option:selected"), function () {
                selectedActivitiesPrice = selectedActivitiesPrice + (parseFloat($(this).attr('price')));
            });
            selectedActivitiesPrice = parseFloat(selectedActivitiesPrice);
            @if(@$mainSettings->vat_details['vat_percentage'])
                vat = selectedActivitiesPrice * ({{@$mainSettings->vat_details['vat_percentage'] / 100}});
                selectedActivitiesPrice = selectedActivitiesPrice + parseFloat(vat);
            @endif
            $('#myTotal').text("{{ trans('sw.price')}} = " + Number(selectedActivitiesPrice).toFixed(2));

        });

    </script>


    {{--    <script src="{{asset('/')}}resources/assets/new_front/global/plugins/jquery.min.js" type="text/javascript"></script>--}}

{{--        <script type="text/javascript"--}}
{{--                src="{{asset('resources/assets/new_front/global/plugins/select2/js/select2.full.min.js')}}"></script>--}}
{{--    <script src="{{asset('/')}}resources/assets/new_front/global/scripts/metronic.js" type="text/javascript"></script>--}}
{{--    <script src="{{asset('/')}}resources/assets/new_front/pages/scripts/components-dropdowns.js"></script>--}}
{{--    <script type="text/javascript"--}}
{{--            src="{{asset('/')}}resources/assets/new_front/global/plugins/select2/select2.js"></script>--}}
{{--    <script>--}}
{{--        var ComponentsDropdowns = function () {--}}
{{--            var handleSelect2 = function () {--}}
{{--                $("#select2_sample5").select2({--}}
{{--                    tags: ["red", "green", "blue", "yellow", "pink"]--}}
{{--                });--}}
{{--            }--}}

{{--            return {--}}
{{--                //main function to initiate the module--}}
{{--                init: function () {--}}
{{--                    handleSelect2();--}}
{{--                }--}}
{{--            };--}}

{{--        }();--}}

{{--            ComponentsDropdowns.init();--}}
{{--    </script>--}}
@endsection



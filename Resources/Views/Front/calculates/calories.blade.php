@extends('software::layouts.list')
@section('list_title'){{ $title }} @endsection
@section('breadcrumb')
    <!--begin::Breadcrumb-->
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
        <!--begin::Item-->
        <li class="breadcrumb-item text-muted">
            <a href="{{route('sw.dashboard')}}" class="text-muted text-hover-primary"> {{ trans('sw.home') }} </a>
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
    <style>
        .m-unit{
            font-weight: normal;
            font-size: 10px;
        }
    </style>
@endsection

@section('page_body')
    <!-- BEGIN PAGE CONTENT-->
    <div class="row">
        <div class="col-md-3">
            @include('software::Front.calculates.calculate_side_bar')
        </div>
        <div class="col-md-9">


            <div class="box_detail booking" style="background-color: white">
               
                <!--begin::Calories Calculation Form-->
                <form id="submit_calculate_calories_form" class="form d-flex flex-column flex-lg-row" autocomplete="off">
                    
                    <!--begin::Main column-->
                    <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                        <!--begin::Calculation Details-->
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
                                    <label class="required form-label">{{ trans('sw.gender')}}</label>
                                    <!--end::Label-->
                                    <!--begin::Radio group-->
                                    <div class="d-flex flex-wrap gap-5">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input type="radio" class="form-check-input" name="gender" id="gender" value="1" required>
                                            <label class="form-check-label" for="gender_male">
                                                {{ trans('sw.male')}}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input type="radio" class="form-check-input" name="gender" id="gender" value="2" required>
                                            <label class="form-check-label" for="gender">
                                                {{ trans('sw.female')}}
                                            </label>
                                        </div>
                                    </div>
                                    <!--end::Radio group-->
                                </div>
                                <!--end::Input group-->
                                <div class="row">
                                    <div class="col-md-6">
                                        <!--begin::Input group-->
                                        <div class="mb-10 fv-row">
                                            <!--begin::Label-->
                                            <label class="required form-label">{{ trans('sw.age')}} <span class="m-unit">({{ trans('sw.year')}})</span></label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="number" name="age" value="25" id="age" placeholder="" class="form-control mb-2" required>
                                            <!--end::Input-->
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                    <div class="col-md-6">
                                        <!--begin::Input group-->
                                        <div class="mb-10 fv-row">
                                            <!--begin::Label-->
                                            <label class="required form-label">{{ trans('sw.weight')}} <span class="m-unit">({{ trans('sw.kg')}})</span></label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="number" value="60" name="weight" id="weight" placeholder="" class="form-control mb-2" required>
                                            <!--end::Input-->
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <!--begin::Input group-->
                                        <div class="mb-10 fv-row">
                                            <!--begin::Label-->
                                            <label class="required form-label">{{ trans('sw.height')}} <span class="m-unit">({{ trans('sw.cm')}})</span></label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="number" value="180" name="height" id="height" class="form-control mb-2" required>
                                            <!--end::Input-->
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                    <div class="col-md-6">
                                        <!--begin::Input group-->
                                        <div class="mb-10 fv-row">
                                            <!--begin::Label-->
                                            <label class="required form-label">{{ trans('sw.activity')}}</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <select name="activity" id="activity" class="form-select form-select-solid mb-2" required>
                                                <option value="">{{ trans('sw.choose')}}</option>
                                                <option value="1.200 ">{{ trans('sw.activity_1')}}</option>
                                                <option value="1.375 ">{{ trans('sw.activity_2')}}</option>
                                                <option value="1.550 ">{{ trans('sw.activity_3')}}</option>
                                                <option value="1.725 ">{{ trans('sw.activity_4')}}</option>
                                                <option value="1.900 ">{{ trans('sw.activity_5')}}</option>
                                            </select>
                                            <!--end::Select-->
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                </div>
                                
                                <!--begin::Form actions-->
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary" id="submit_calculate_calories">
                                        <i class="ki-outline ki-calculator fs-2"></i>
                                        {{ trans('sw.calculate')}}
                                    </button>
                                </div>
                                <!--end::Form actions-->
                            </div>
                            <!--end::Card body-->
                        </div>
                        <!--end::Calculation Details-->
                    </div>
                    <!--end::Main column-->
                </form>
                <!--end::Calories Calculation Form-->
            </div>

            <br/><br/>
            <div id="result"></div>
            <!-- /accordion payment -->
        </div>
    </div>
    <!-- END PAGE CONTENT-->


@endsection
@section('scripts')
    <script>
        $("#submit_calculate_calories_form").submit(function(){
                age = $('#age').val();
                height = $('#height').val();
                weight = $('#weight').val();
                activity = $('#activity').val();
                gender = $('#gender').val();
                $.ajax({
                    url: "{{route('sw.calculateCaloriesResult')}}",
                    type: 'POST',
                    data: {
                        gender: gender,
                        age: age,
                        height: height,
                        weight: weight,
                        activity: activity,
                        _token: "{{csrf_token()}}"
                    },
                    dataType: "text",
                    success: function (response) {
                        document.getElementById("result").innerHTML = response;

                    },
                    error: function (request, error) {

                        console.error("Request: " + JSON.stringify(request));
                        console.error("Error: " + JSON.stringify(error));
                    }
                });

            return false;
        });
    </script>
@endsection

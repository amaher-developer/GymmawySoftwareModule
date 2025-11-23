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
          
                <!--begin::Water Calculation Form-->
                <form id="submit_calculate_water_form" class="form d-flex flex-column flex-lg-row" autocomplete="off">
                    
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
                                <div class="row">
                                    <div class="col-md-6">
                                        <!--begin::Input group-->
                                        <div class="mb-10 fv-row">
                                            <!--begin::Label-->
                                            <label class="required form-label">{{ trans('sw.weight')}} <span class="m-unit">({{ trans('sw.kg')}})</span></label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="number" value="60" name="water_weight" id="water_weight" class="form-control mb-2" required>
                                            <!--end::Input-->
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                </div>
                                
                                <!--begin::Form actions-->
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary" id="submit_calculate_water">
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
                <!--end::Water Calculation Form-->
            </div>

            <br/><br/>
            <div id="water_result"></div>
            <!-- /accordion payment -->
        </div>
    </div>
    <!-- END PAGE CONTENT-->



@endsection
@section('scripts')
    <script>
        $("#submit_calculate_water_form").submit(function(){
            water_weight = $('#water_weight').val();
                $.ajax({
                    url: "{{route('sw.calculateWaterResult')}}",
                    type: 'POST',
                    data: {
                        water_weight: water_weight,
                        _token: "{{csrf_token()}}"
                    },
                    dataType: "text",
                    success: function (response) {
                        document.getElementById("water_result").innerHTML = response;

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



@extends('software::layouts.list')
@section('list_title'){{ $title }} @endsection
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
            <!-- END PAGE CONTENT-->


                            <div class="box_detail booking" style="background-color: white">
                          
                                <!--begin::IBW Calculation Form-->
                                <form id="submit_calculate_ibw_form" class="form d-flex flex-column flex-lg-row" autocomplete="off">
                                    
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
                                                            <label class="required form-label">{{ trans('sw.height')}} <span class="m-unit">({{ trans('sw.cm')}})</span></label>
                                                            <!--end::Label-->
                                                            <!--begin::Input-->
                                                            <input type="number" value="180" name="ibw_height" id="ibw_height" class="form-control mb-2" required>
                                                            <!--end::Input-->
                                                        </div>
                                                        <!--end::Input group-->
                                                    </div>
                                                </div>
                                                
                                                <!--begin::Form actions-->
                                                <div class="d-flex justify-content-end">
                                                    <button type="submit" class="btn btn-primary" id="submit_calculate_ibw">
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
                                <!--end::IBW Calculation Form-->
                            </div>

                            <br/><br/>
                            <div id="ibw_result"></div>
                            <!-- /accordion payment -->

                        </div>
                        <!-- /col -->

        </div>



@endsection
@section('scripts')
    <script>
        $("#submit_calculate_ibw_form").submit(function(){
                ibw_height = $('#ibw_height').val();
                $.ajax({
                    url: "{{route('calculateIBWResult')}}",
                    type: 'POST',
                    data: {
                        ibw_height: ibw_height,
                        _token: "{{csrf_token()}}"
                    },
                    dataType: "text",
                    success: function (response) {
                        document.getElementById("ibw_result").innerHTML = response;

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

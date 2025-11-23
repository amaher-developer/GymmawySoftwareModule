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
              
                <!--begin::VAT Percentage Calculation Form-->
                <form id="submit_calculate_vat_percentage_form" class="form d-flex flex-column flex-lg-row" autocomplete="off">
                    
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
                                    <div class="col-md-1">
                                        <!--begin::Radio-->
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input type="radio" name="total_price_type" class="form-check-input total_price_type" id="total_price_type" value="1" onclick="total_price_check(1)">
                                        </div>
                                        <!--end::Radio-->
                                    </div>
                                    <div class="col-md-6">
                                        <!--begin::Input group-->
                                        <div class="mb-10 fv-row">
                                            <!--begin::Label-->
                                            <label class="required form-label">{{ trans('sw.total_price')}} <span class="m-unit">({{ trans('sw.excluding_vat')}})</span></label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="number" value="" step="0.01" name="total_price_without_vat" id="total_price_without_vat" class="form-control mb-2" required>
                                            <!--end::Input-->
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-1">
                                        <!--begin::Radio-->
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input type="radio" name="total_price_type" class="form-check-input total_price_type" id="total_price_type2" value="2" onclick="total_price_check(2)">
                                        </div>
                                        <!--end::Radio-->
                                    </div>
                                    <div class="col-md-6">
                                        <!--begin::Input group-->
                                        <div class="mb-10 fv-row">
                                            <!--begin::Label-->
                                            <label class="required form-label">{{ trans('sw.total_price')}} <span class="m-unit">({{ trans('sw.including_vat')}})</span></label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="number" value="" step="0.01" name="total_price_with_vat" id="total_price_with_vat" class="form-control mb-2" required>
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
                                            <label class="required form-label">{{ trans('sw.vat')}} <span class="m-unit">(%)</span></label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="number" value="15" name="vat" id="vat" class="form-control mb-2" required>
                                            <!--end::Input-->
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                </div>
                                
                                <!--begin::Form actions-->
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary" id="submit_calculate_vat">
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
                <!--end::VAT Percentage Calculation Form-->
            </div>

            <br/><br/>
            <div id="vat_result"></div>
            <!-- /accordion payment -->
        </div>
    </div>
    <!-- END PAGE CONTENT-->



@endsection
@section('scripts')
    <script>
        $("#submit_calculate_vat_percentage_form").submit(function(){
            var total_price_without_vat = $('#total_price_without_vat').val();
            var total_price_with_vat = $('#total_price_with_vat').val();
            var total_price_type = document.querySelector("input[name=total_price_type]:checked").value;
            var vat = $('#vat').val();
                $.ajax({
                    url: "{{route('sw.calculateVatPercentageResult')}}",
                    type: 'POST',
                    data: {
                        total_price_without_vat: total_price_without_vat,
                        total_price_with_vat: total_price_with_vat,
                        vat: vat,
                        total_price_type: total_price_type,
                        _token: "{{csrf_token()}}"
                    },
                    dataType: "text",
                    success: function (response) {
                        document.getElementById("vat_result").innerHTML = response;

                    },
                    error: function (request, error) {

                        console.error("Request: " + JSON.stringify(request));
                        console.error("Error: " + JSON.stringify(error));
                    }
                });

            return false;
        });

        function total_price_check(price_type = null){
            if(price_type == 1){
                document.getElementById("total_price_with_vat").disabled = true;
                document.getElementById("total_price_without_vat").disabled = false;
            }else if (price_type == 2){
                document.getElementById("total_price_with_vat").disabled = false;
                document.getElementById("total_price_without_vat").disabled = true;
            }else{
                document.getElementById("total_price_with_vat").disabled = true;
                document.getElementById("total_price_without_vat").disabled = true;
            }
        }
        total_price_check(3);
    </script>
@endsection



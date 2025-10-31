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
            <a href="{{ route('listUserGymOrder') }}" class="text-muted text-hover-primary">{{ trans('global.gym_orders')}}</a>
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
@section('page_body')
    <!--begin::Order Form-->
    <form method="post" action="" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
        {{csrf_field()}}
        
        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!--begin::Order Details-->
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
                    <div class="row mb-10">
                        <div class="col-md-6">
                            <label class="required form-label">{{ trans('global.subscriptions')}}</label>
                            <select name="subscription_id" class="form-select mb-2" id="subscription_id" required>
                                <option value="">{{ trans('global.choose_subscription')}}</option>
                                @foreach($subscriptions as $subscription)
                                    <option value="{{$subscription->id}}" price="{{$subscription->price}}"
                                            @if($subscription->id == @$gym->city_id) selected @endif>
                                        {{$subscription->name}} - {{$subscription->duration}} {{ trans('admin.days')}}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ trans('global.price')}}</label>
                            <input type="number" name="price" class="form-control mb-2" 
                                   value="{{ old('price', $gymorder->price) }}" 
                                   id="price" />
                        </div>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="row mb-10">
                        <div class="col-md-6">
                            <label class="required form-label">{{ trans('global.start_date')}}</label>
                            <div class="input-group date date-picker" data-date-format="mm/dd/yyyy">
                                <input type="text" name="date_from" class="form-control mb-2" 
                                       value="{{ old('date_from', $gymorder->date_from) ?? date('m/d/Y') }}" 
                                       id="from" readonly required />
                                <span class="input-group-btn">
                                    <button class="btn btn-light" type="button">
                                        <i class="fa fa-calendar"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ trans('global.to_date')}}</label>
                            <div class="input-group date date-picker" data-date-format="mm/dd/yyyy">
                                <input type="text" name="date_to" class="form-control mb-2" 
                                       value="{{ old('date_from', $gymorder->date_to) }}" 
                                       id="to" readonly />
                                <span class="input-group-btn">
                                    <button class="btn btn-light" type="button">
                                        <i class="fa fa-calendar"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Order Details-->

            <!--begin::Form actions-->
            <div class="d-flex justify-content-end">
                <button type="reset" class="btn btn-light me-5">{{ trans('admin.reset')}}</button>
                <button type="submit" class="btn btn-primary">
                    <i class="ki-outline ki-check fs-2"></i>
                    {{ trans('admin.submit')}}
                </button>
            </div>
            <!--end::Form actions-->
        </div>
    </form>
@endsection

@section('sub_scripts')
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script>


        $("#subscription_id").change(function (e) {
            var subscription_id = $("#subscription_id").val();
            var price = $('option:selected', this).attr('price');
            $("#price").attr('value', price);
        });

        $(function () {
            var dateFormat = "dd/mm/y",
                from = $("#from")
                    .datepicker({
                        // defaultDate: "+1w",
                        // dateFormat: 'dd/mm/y',//check change
                        changeMonth: true,
                        numberOfMonths: 3
                    })
                    .on("change", function () {
                        to.datepicker("option", "minDate", getDate(this));
                    }),
                to = $("#to").datepicker({
                    // defaultDate: "+1w",
                    // dateFormat: 'dd/mm/y',//check change
                    changeMonth: true,
                    numberOfMonths: 3
                }).on("change", function () {
                    from.datepicker("option", "maxDate", getDate(this));
                });

            function getDate(element) {
                var date;
                try {
                    date = $.datepicker.parseDate(dateFormat, element.value);
                } catch (error) {
                    date = null;
                }

                return date;
            }
        });
    </script>
@endsection

@extends('software::layouts.list')
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
@section('list_title') {{ @$title }} @endsection
@section('styles')
<style>
    .normal_search {
        height: 60px;
    }
    .scan_barcode_manual {
        height: 60px;
        font-size: 28px;
        line-height: 60px;
    }
    .short-btn {
        min-width: 140px;
        height: 120px;
    }
    .short-btn i{
        line-height: 40px !important;
    }
    .number{
        font-size: 20px !important;
    }
    .details{
        width: 60%;
    }
</style>
@endsection
@section('page_body')

    @if(\Carbon\Carbon::parse($mainSettings->sw_end_date)->toDateString() <= \Carbon\Carbon::now()->toDateString())
        <div class="Metronic-alerts alert alert-danger fade in"><i class="fa-lg fa fa-warning"></i>  {!! trans('sw.subscription_expire_date_msg', ['date'=> \Carbon\Carbon::parse($mainSettings->sw_end_date)->toDateString(), 'url' => route('sw.listSwPayment')]) !!}</div>
    @endif

    <!--begin::Dashboard-->
    <div class="row g-5">
        <!--begin::Member Check-in-->
        <div class="col-lg-8">
            <div class="card card-flush h-100">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="fw-bold">{{ trans('sw.member_login')}}</h3>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Input group-->
                    <div class="mb-10">
                        <label class="form-label">{{ trans('sw.check_in_by_id')}}</label>
                        <div class="input-group">
                            <input type="text" class="form-control scan_barcode_manual" placeholder="{{ trans('sw.check_in_by_id')}}" name="scan_barcode_manual" id="scan_barcode_manual">
                            <button class="btn btn-primary normal_search" id="Normal_search" onclick="scanBarcodeManual();" type="button">
                                <i class="ki-outline ki-barcode fs-1"></i>
                            </button>
                        </div>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Checkbox-->
                    <div class="form-check form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" value="1" id="scan_barcode_enquiry">
                        <label class="form-check-label" for="scan_barcode_enquiry">
                            {{ trans('sw.enquiry_only')}}
                        </label>
                    </div>
                    <!--end::Checkbox-->
                </div>
                <!--end::Card body-->
            </div>
        </div>
        <!--end::Member Check-in-->

        <!--begin::Last Enter Member-->
        <div class="col-lg-4">
            <div class="card card-flush h-100">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="fw-bold">{{ trans('sw.last_enter_member')}}</h3>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-50px me-5">
                            <div class="symbol-label bg-light-primary">
                                <i class="ki-outline ki-user fs-2x text-primary"></i>
                            </div>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fs-6 fw-semibold text-gray-900">{{ trans('sw.last_enter_member')}}</span>
                            <span class="fs-4 fw-bold text-primary" id="barcode_last_enter_member">
                                {{@$last_enter_member->member->name}}
                            </span>
                        </div>
                    </div>
                </div>
                <!--end::Card body-->
            </div>
        </div>
        <!--end::Last Enter Member-->
    </div>
    <!--end::Dashboard-->



@endsection
@section('scripts')
<script>
    // setTimeout(function(){
    //     window.location.reload();
    // }, this.timeOfSec ?? 10000);

</script>

@endsection

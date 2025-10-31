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
@section('styles')

@endsection
@section('list_title') {{ @$title }} @endsection
@section('page_body')

<!--begin::Helper Tools-->
<div class="card card-flush">
    <!--begin::Card header-->
    {{-- <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-setting-2 fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ trans('sw.helper_tools')}}</span>
            </div>
        </div>
    </div> --}}
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body pt-0">
  

        <!-- BEGIN PAGE CONTENT-->
        <div class="row">
            <div class="col-md-3">
                @include('software::Front.calculates.calculate_side_bar')
            </div>
            <div class="col-md-9">

            </div>
        </div>
        <!-- END PAGE CONTENT-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Helper Tools-->

@endsection
@section('scripts')

@endsection

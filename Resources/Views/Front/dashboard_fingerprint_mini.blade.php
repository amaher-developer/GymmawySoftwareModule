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

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

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

    <div class="page-content-inner">

        <div class="row">

            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center" >
                <img style="width: 200px; padding: 20px" src="{{asset('resources/assets/new_front/img/fp_image.png')}}">
            </div>

            <div style="clear: both;float: none"></div>

            <!-- END BUTTONS WITH ICONS PORTLET-->
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

                <div class="clearfix">
                    <div class="panel panel-danger">
                        <!-- Default panel contents -->
                        <div class="panel-body">
                            {{--                            <h4><i class="fa fa-link"></i> {{ trans('sw.short_links')}}</h4>--}}
                        </div>
                        <div class="scroller row" style="max-height: 300px; padding-right: 20px;" data-always-visible="1" data-rail-visible="0">

                            <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12" style="padding-bottom: 20px;">
                                <div class="dashboard-stat blue-madison">
                                    <div class="visual">
                                        <i class="fa fa-sign-in"></i>
                                    </div>
                                    <div class="details">
                                        <div class="number" id="barcode_last_enter_member">
                                            {{@$last_enter_member->member->name}}
                                        </div>
                                    </div>
                                    <span class="more">
                                {{ trans('sw.last_enter_member')}} <i class="m-icon-swapright m-icon-white"></i>
                            </span>
                                </div>
                            </div>
{{--                            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="padding-bottom: 20px;">--}}
{{--                                <div class="dashboard-stat red-intense">--}}
{{--                                    <div class="visual">--}}
{{--                                        <i class="fa fa-user-plus"></i>--}}
{{--                                    </div>--}}
{{--                                    <div class="details">--}}
{{--                                        <div class="number">--}}
{{--                                            {{@$last_created_member->name}}--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <span class="more">--}}
{{--                                {{ trans('sw.last_created_member')}} <i class="m-icon-swapright m-icon-white"></i>--}}
{{--                            </span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="padding-bottom: 20px;">--}}
{{--                                <div class="dashboard-stat green-haze">--}}
{{--                                    <div class="visual">--}}
{{--                                        <i class="fa fa-user"></i>--}}
{{--                                    </div>--}}
{{--                                    <div class="details">--}}
{{--                                        <div class="number">--}}
{{--                                            {{@$last_created_non_member->name}}--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <span class="more" >--}}
{{--                                {{ trans('sw.last_created_non_member')}} <i class="m-icon-swapright m-icon-white"></i>--}}
{{--                            </span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
                        </div>
                    </div>
                </div>
            </div>
            <!-- END BUTTONS WITH ICONS PORTLET-->


        </div>
    </div>



@endsection
@section('scripts')


<script>
    // Reuse the pusher instance created by notifications.blade.php (already connected).
    // Subscribing a new channel on the same connection avoids a second WebSocket.
    var fpChannel = pusher.subscribe('fp-member');
    fpChannel.bind('member-attendance', function (data) {
        barcode_scanner(data.code);
        setTimeout(function () {
            if (typeof notifytone === 'function') notifytone();
        }, 1500);
    });
</script>

@endsection


<!DOCTYPE html >
<!--[if IE 8]>
<html lang="{{app()->getLocale('lang')}}" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]>
<html lang="{{app()->getLocale('lang')}}" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="{{app()->getLocale('lang')}}" @if(app()->getLocale('lang')=='ar') dir="rtl" @endif >
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
    <meta charset="utf-8">
    <title>{{@$mainSettings->name}}</title>

    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">


    <meta name="robots" content="noindex">
    <meta name="robots" content="nofollow">
    <meta name="googlebot" content="noindex">

    <link rel="shortcut icon" href="{{@$mainSettings->logo}}">

    <!-- Fonts START -->
    <link
        href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|PT+Sans+Narrow|Source+Sans+Pro:200,300,400,600,700,900&amp;subset=all"
        rel="stylesheet" type="text/css">
    <!-- Fonts END -->

    <!-- Global styles START -->
    <link href="{{asset('resources/assets/admin/global/plugins/font-awesome/css/font-awesome.min.css')}}"
          rel="stylesheet">
    @if(app()->getLocale('lang')=='ar')
    <link href="{{asset('resources/assets/admin/global/plugins/bootstrap/css/bootstrap-rtl.min.css')}}"
          rel="stylesheet">
    <!-- Global styles END -->
    <!-- Theme styles START -->
    <link href="{{asset('resources/assets/admin/global/css/components-rtl.css')}}" rel="stylesheet">
    <link href="{{asset('resources/assets/admin/frontend/css/style-rtl.css')}}" rel="stylesheet">
    @else

        <link href="{{asset('resources/assets/admin/global/plugins/bootstrap/css/bootstrap.min.css')}}"
              rel="stylesheet">
        <!-- Global styles END -->
        <!-- Theme styles START -->
        <link href="{{asset('resources/assets/admin/global/css/components.css')}}" rel="stylesheet">
        <link href="{{asset('resources/assets/admin/frontend/css/style.css')}}" rel="stylesheet">
    @endif
    <link href="{{asset('resources/assets/admin/pages/css/portfolio.css')}}" rel="stylesheet">
    <link href="{{asset('resources/assets/admin/frontend/css/style-responsive.css')}}" rel="stylesheet">
    <link href="{{asset('resources/assets/admin/frontend/css/themes/red.css')}}" rel="stylesheet" id="style-color">
    <link href="{{asset('resources/assets/admin/frontend/css/custom.css')}}" rel="stylesheet">
    <!-- Theme styles END -->
    <style>
        .card {
            position: relative;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-direction: column;
            flex-direction: column;
            min-width: 0;
            word-wrap: break-word;
            background-color: #fff;
            background-clip: border-box;
            border: 1px solid rgba(0, 0, 0, .125);
            border-radius: 0.25rem;
        }

        .card-body {
            -ms-flex: 1 1 auto;
            flex: 1 1 auto;
            min-height: 1px;
            padding: 1.25rem;
        }

        .breadcrumb > li + li:before {
            content: "/ ";
        }
    </style>
    @yield('styles')
</head>
<!-- Head END -->

<!-- Body BEGIN -->
<body class="corporate">

<!-- BEGIN TOP BAR -->
<div class="pre-header">
    <div class="container">
        <div class="row">
            <!-- BEGIN TOP BAR LEFT PART -->
            <div class="col-md-6 col-sm-6 additional-shop-info">
                <ul class="list-unstyled list-inline">
                    <li><i class="fa fa-phone"></i><span> {{@$mainSettings->phone}}</span></li>
                    <li><i class="fa fa-envelope-o"></i><span> {{@$mainSettings->support_email}}</span></li>
                </ul>
            </div>
            <!-- END TOP BAR LEFT PART -->

        </div>
    </div>
</div>
<!-- END TOP BAR -->
<!-- BEGIN HEADER -->
<div class="header">
    <div class="container">
        <a class="site-logo" href=""><img src="{{@$mainSettings->logo}}" style="max-width: 64px;height: auto;object-fit: contain;" alt="{{$mainSettings->name}}"></a>
        <a href="javascript:void(0);" class="mobi-toggler"><i class="fa fa-bars"></i></a>
        @if(\Request::route()->getName() != 'sw.customerLogin')
        <div class="header-navigation pull-right font-transform-inherit">
            <ul>
                <li @if(\Request::route()->getName() == 'sw.showCustomerProfile') class="active" @endif><a href="{{route('sw.showCustomerProfile')}}">{{trans('admin.home')}}</a></li>
                <li @if(\Request::route()->getName() == 'sw.customerSubscriptions') class="active" @endif><a href="{{route('sw.customerSubscriptions')}}">{{trans('sw.memberships')}}</a></li>
                <li @if(\Request::route()->getName() == 'sw.customerActivities') class="active" @endif><a href="{{route('sw.customerActivities')}}">{{trans('sw.activities')}}</a></li>
                @if(@$mainSettings->active_pt)<li @if(\Request::route()->getName() == 'sw.customerPT') class="active" @endif><a href="{{route('sw.customerPT')}}">{{trans('sw.pt')}}</a></li>@endif
                @if(@$mainSettings->active_training)<li @if(\Request::route()->getName() == 'sw.customerTraining') class="active" @endif><a href="{{route('sw.customerTraining')}}">{{trans('sw.training_plans')}}</a></li>@endif
                @if(@$mainSettings->active_training)<li @if(\Request::route()->getName() == 'sw.customerTracking') class="active" @endif><a href="{{route('sw.customerTracking')}}">{{trans('sw.training_tracks')}}</a></li>@endif
{{--                <li @if(\Request::route()->getName() == 'sw.customerReview') class="active" @endif><a href="{{route('sw.customerReview')}}">{{trans('sw.review')}}</a></li>--}}

            </ul>
        </div>
    @endif
    </div>
</div>
<!-- Header END -->


@yield('content')

<!-- BEGIN PRE-FOOTER -->
<div class="pre-footer">
    <div class="container">
        <div class="row">
            <!-- BEGIN BOTTOM ABOUT BLOCK -->
            <div class="col-md-6 col-sm-6 pre-footer-col">
                <h2>{{trans('sw.about')}}</h2>
                <p>{{@strip_tags($mainSettings->about)}}</p>

            </div>
            <!-- END BOTTOM ABOUT BLOCK -->

            <!-- BEGIN BOTTOM CONTACTS -->
            <div class="col-md-6 col-sm-6 pre-footer-col">
                <h2>{{trans('sw.contacts')}}</h2>
                <address class="margin-bottom-40">
                    {{@$mainSettings->address}}<br/>
                    {{trans('sw.phone')}}: {{@$mainSettings->phone}}<br>
                    {{trans('sw.email')}}: <a
                        href="mailto:{{@$mainSettings->support_email}}">{{@$mainSettings->support_email}}</a><br>
                </address>

            </div>
            <!-- END BOTTOM CONTACTS -->

            <!-- BEGIN TWITTER BLOCK -->
        {{--            <div class="col-md-4 col-sm-6 pre-footer-col">--}}
        {{--                <h2 class="margin-bottom-0">Latest Tweets</h2>--}}
        {{--                <a class="twitter-timeline" href="https://twitter.com/twitterapi" data-tweet-limit="2" data-theme="dark" data-link-color="#57C8EB" data-widget-id="455411516829736961" data-chrome="noheader nofooter noscrollbar noborders transparent">Loading tweets by @keenthemes...</a>--}}
        {{--            </div>--}}
        <!-- END TWITTER BLOCK -->
        </div>
    </div>
</div>
<!-- END PRE-FOOTER -->

<!-- BEGIN FOOTER -->
<div class="footer">
    <div class="container">
        <div class="row">
            <!-- BEGIN COPYRIGHT -->
            <div class="col-md-6 col-sm-6 padding-top-10">
                <p style="font-weight: bold;color: #fff !important;font-size: 16px;"> {{trans('sw.dev_des')}} <a
                        href="https://demo.gymmawy.com" target="_blank" style="text-decoration: none;"><img
                            style="width: 24px;"
                            src="https://kythara.gymmawy.com/resources/assets/front/img/logo/favicon.ico"> {{trans('sw.gymmawy')}}
                    </a></p>
            </div>
            <!-- END COPYRIGHT -->
            <!-- BEGIN PAYMENTS -->
            <div class="col-md-6 col-sm-6">
                <ul class="social-footer list-unstyled list-inline pull-right">
                    @if(@$mainSettings->facebook)
                        <li><a href="{{@$mainSettings->facebook}}" target="_blank"><i class="fa fa-facebook"></i></a>
                        </li>@endif
                    @if(@$mainSettings->instagram)
                        <li><a href="{{@$mainSettings->instagram}}" target="_blank"><i class="fa fa-instagram"></i></a>
                        </li>@endif
                    @if(@$mainSettings->twitter)
                        <li><a href="{{@$mainSettings->twitter}}" target="_blank"><i class="fa fa-twitter"></i></a>
                        </li>@endif
                </ul>
            </div>
            <!-- END PAYMENTS -->
        </div>
    </div>
</div>
<!-- END FOOTER -->
<script src="{{asset('/')}}resources/assets/admin/global/scripts/metronic.js" type="text/javascript"></script>

<script src="{{asset('resources/assets/admin/global/plugins/jquery.min.js')}}" type="text/javascript"></script>
<script src="{{asset('resources/assets/admin/global/plugins/jquery-migrate.min.js')}}" type="text/javascript"></script>
<script src="{{asset('resources/assets/admin/global/plugins/bootstrap/js/bootstrap.min.js')}}"
        type="text/javascript"></script>
<script src="{{asset('resources/assets/admin/frontend/scripts/back-to-top.js')}}" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
@yield('scripts')
</body>
<!-- END BODY -->
</html>

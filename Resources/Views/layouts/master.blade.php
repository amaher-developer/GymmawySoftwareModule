<!DOCTYPE html>
<!--[if IE 8]>
<html lang="{{app()->getLocale('lang')}}" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]>
<html lang="{{app()->getLocale('lang')}}" class="ie9 no-js" > <![endif]-->
<!--[if !IE]><!-->
<html lang="{{app()->getLocale('lang')}}" @if(app()->getLocale('lang')=='ar') dir="rtl" direction="rtl"
      style="direction:rtl;" @endif   data-bs-theme-mode="light">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<!--begin::Head-->
<head>
    <noscript>
        <meta http-equiv='refresh' content='0;url={{ route('noJs') }}'>
    </noscript>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{asset('resources/assets/new_front/img/logo/favicon.ico')}}">

    <meta charset="utf-8"/>
    <title>{{$mainSettings->name}}</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <meta content="" name="description"/>
    <meta content="" name="author"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{asset('resources/assets/new_front/images/favicon.ico')}}" type="image/x-icon"/>
    <link rel="apple-touch-icon" href="{{asset('resources/assets/new_front/images/favicon.ico')}}" type="image/x-icon"/>

    <!--begin::Fonts(mandatory for all pages)-->
    <style>
        /* Inter Font - Local */
        @font-face {
            font-family: 'Inter';
            src: url('{{asset("public/fonts/inter/Inter-Light.woff2")}}') format('woff2');
            font-weight: 300;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Inter';
            src: url('{{asset("public/fonts/inter/Inter-Regular.woff2")}}') format('woff2');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Inter';
            src: url('{{asset("public/fonts/inter/Inter-Medium.woff2")}}') format('woff2');
            font-weight: 500;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Inter';
            src: url('{{asset("public/fonts/inter/Inter-SemiBold.woff2")}}') format('woff2');
            font-weight: 600;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Inter';
            src: url('{{asset("public/fonts/inter/Inter-Bold.woff2")}}') format('woff2');
            font-weight: 700;
            font-style: normal;
            font-display: swap;
        }
    </style>
    <!--end::Fonts-->

    @if($lang == 'ar')
        <style>
            /* Droid Arabic Kufi Font - Local */
            @font-face {
                font-family: 'Droid Arabic Kufi';
                src: url('{{asset("public/fonts/droid-arabic-kufi/DroidKufi-Regular.woff2")}}') format('woff2'),
                     url('{{asset("public/fonts/droid-arabic-kufi/DroidKufi-Regular.woff")}}') format('woff'),
                     url('{{asset("public/fonts/droid-arabic-kufi/DroidKufi-Regular.ttf")}}') format('truetype');
                font-weight: 400;
                font-style: normal;
                font-display: swap;
            }
            @font-face {
                font-family: 'Droid Arabic Kufi';
                src: url('{{asset("public/fonts/droid-arabic-kufi/DroidKufi-Bold.woff2")}}') format('woff2'),
                     url('{{asset("public/fonts/droid-arabic-kufi/DroidKufi-Bold.woff")}}') format('woff'),
                     url('{{asset("public/fonts/droid-arabic-kufi/DroidKufi-Bold.ttf")}}') format('truetype');
                font-weight: 700;
                font-style: normal;
                font-display: swap;
            }
            body, .menu-title, .title, .nav-link, .sub-menu span, .user-info h4, .user-info span {
                font-family: 'Droid Arabic Kufi', Arial, sans-serif !important;
            }
        </style>
        <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
        <link href="{{asset('resources/assets/new_front')}}/plugins/global/plugins.bundle.rtl.css" rel="stylesheet"
              type="text/css"/>
        <link href="{{asset('resources/assets/new_front')}}/css/style.bundle.rtl.css" rel="stylesheet" type="text/css"/>
        <!--end::Global Stylesheets Bundle-->

        <style>
            .page-bar .page-breadcrumb > li > a, .page-bar .page-breadcrumb > li > span {
                float: left;
            }

            .control-label {
                text-align: right !important;
            }

            .mt-checkbox > span:after {
                left: 1px !important;
                bottom: 6px !important;
                width: 15px !important;
                height: 8px !important;
                right: 0px !important;
            }

            .bootstrap-select.btn-group .dropdown-toggle .filter-option {
                text-align: right !important;
            }
        </style>
    @else
        <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
        <link href="{{asset('resources/assets/new_front')}}/plugins/global/plugins.bundle.css" rel="stylesheet"
              type="text/css"/>
        <link href="{{asset('resources/assets/new_front')}}/css/style.bundle.css" rel="stylesheet" type="text/css"/>
        <!--end::Global Stylesheets Bundle-->

        <style>
            .page-bar .page-breadcrumb > li > a, .page-bar .page-breadcrumb > li > span {
                float: right;
            }

            .control-label {
                text-align: left !important;
            }

            .mt-checkbox > span:after {
                left: 3px !important;
                bottom: 0px !important;
                width: 9px !important;
                height: 16px !important;
                right: 0px !important;
                top: -2px !important;
            }

            .input-icon > i {
                left: 0 !important;
                right: auto;
            }

            .bootstrap-select.btn-group .dropdown-toggle .filter-option {
                text-align: left !important;
            }
        </style>
    @endif


    <link rel="stylesheet" href="{{asset('resources/assets/new_front/global/scripts/css/datepicker3.css')}}">
    <link rel="stylesheet" href="{{asset('resources/assets/new_front/global/scripts/software/custom_qr_scanner.css')}}">
    <link rel="stylesheet" href="{{asset('resources/assets/new_front/global/scripts/software/renew_member.css')}}">
    <style>
        .modal-content-demo .modal-body h6 {
            color: #242f48;
            font-size: 15px;
            margin-bottom: 15px;
        }

        .modal-title {
            margin-bottom: 0;
            line-height: 1.5;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 700;
            color: #242f48;
            line-height: 1;
        }

        .details {
            display: inline-flex;;
            width: 160px;
            white-space: nowrap;
            overflow: hidden !important;
            text-overflow: ellipsis;
        }

        .fa-file-excel-o {
            color: green !important;
        }

        .fa-file-pdf-o {
            color: red !important;
        }
        .m-header-link {
            height: inherit !important;
        }
        button.close {
            border: none;
            background: none;
        }
    </style>

    @yield('styles')

<!--
    <style>
        .filter_trigger_button {
            margin-bottom: 20px;
        }

        .widget-bg-color-lite-gray {
            background-color: #EEEEEE !important;
        }
        .text-left {
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .table-vcenter {
            overflow-x: initial !important;
        }

        @media screen and ( max-width: 520px ){

            li.page-item {
                display: none;
            }

            .page-item:first-child,
            .page-item:last-child,
            .page-item.active {
                display: block;
                float: right;
            }
        }
        .maher-nav-img {
            margin-top: -5px;
            margin-left: 5px;
            height: 29px;
            width: 29px;
            display: inline-block;
            object-fit: cover;
        }
    </style>
    <style>
        .card {
            background-color: white;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
            margin: auto;
            text-align: center;
        }

        .card .title {
            color: grey;
            font-size: 18px;
        }

        .card button {
            border: none;
            outline: 0;
            display: inline-block;
            padding: 8px;
            color: white;
            background-color: #000;
            text-align: center;
            cursor: pointer;
            width: 100%;
            font-size: 18px;
        }

        .card a {
            text-decoration: none;
            font-size: 18px;
        }

        .card button:hover, a:hover {
            opacity: 0.7;
        }
        .card img {
            margin: 20px;
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50% !important;
            -webkit-border-radius: 50%;
            border-radius: 50%;
            -webkit-transition: -webkit-box-shadow 0.3s ease;
            transition: box-shadow 0.3s ease;
            -webkit-box-shadow: 0px 0px 0px 8px;
            box-shadow: 0px 0px 0px 8px;
            color: #f44336c9;
        }
        .card li {
            list-style-type: none;
            text-align: @if($lang == 'ar') right @else left @endif;
            line-height: 30px;
        }
        .card .pt_membership_a {
            margin: 5px;
            border: 2px solid #ffc107;
            padding: 5px;
            font-size: 14px;
            /* color: black; */
            border-radius: 25px !important;
        }

        .img-circle a:focus, a:hover{
            color: #337ab7;
        }
        .branch-header-title {
            color: #c5c5c5;
            height: 46px;
            padding-top: 15px;
            width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .swal2-container {
            z-index: 9999999999 !important;
        }
        .error_att_time_msg {
            float: left;
            /* text-align: center; */
            direction: ltr;
            width: 100%;
        }
    </style>
-->


    <meta name="robots" content="noindex">
    <meta name="robots" content="nofollow">
    <meta name="googlebot" content="noindex">

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-HBV899STLP"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('js', new Date());

        gtag('config', 'G-HBV899STLP');
    </script>


</head>
<!--end::Head-->

<!--begin::Body-->
<body id="kt_body"
      class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed toolbar-tablet-and-mobile-fixed aside-enabled aside-fixed"
      style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">
<!--begin::Theme mode setup on page load-->
<script>var defaultThemeMode = "light";
    var themeMode;
    if (document.documentElement) {
        if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
            themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
        } else {
            if (localStorage.getItem("data-bs-theme") !== null) {
                themeMode = localStorage.getItem("data-bs-theme");
            } else {
                themeMode = defaultThemeMode;
            }
        }
        if (themeMode === "system") {
            themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
        }
        document.documentElement.setAttribute("data-bs-theme", themeMode);
    }</script>
<!--end::Theme mode setup on page load-->
<!--begin::Main-->
<!--begin::Root-->
<div class="d-flex flex-column flex-root">
    <!--begin::Page-->
    <div class="page d-flex flex-row flex-column-fluid">
        <!--begin::Aside-->
        <div id="kt_aside" class="aside aside-dark aside-hoverable" data-kt-drawer="true" data-kt-drawer-name="aside"
             data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true"
             data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="start"
             data-kt-drawer-toggle="#kt_aside_mobile_toggle">
            <!--begin::Brand-->
            <div class="aside-logo flex-column-auto" id="kt_aside_logo">
                <!--begin::Logo-->
                <a href="{{route('home')}}">
                    <img alt="Logo" src="{{$mainSettings->logo_white}}"
                         class="h-25px logo"/>
                </a>
                <!--end::Logo-->
                <!--begin::Aside toggler-->
                <div id="kt_aside_toggle" class="btn btn-icon w-auto px-0 btn-active-color-primary aside-toggle me-n2"
                     data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body"
                     data-kt-toggle-name="aside-minimize">
                    <i class="ki-outline ki-double-left fs-1 rotate-180"></i>
                </div>
                <!--end::Aside toggler-->
            </div>
            <!--end::Brand-->



            <!--begin::Aside menu-->
            @include('software::layouts.side-bar')
            <!--end::Aside menu-->


            <!--begin::Footer-->
            {{--                        <div class="aside-footer flex-column-auto pb-7 px-5" id="kt_aside_footer">--}}
            {{--                            <a href="https://preview.keenthemes.com/html/metronic/docs" class="btn btn-custom btn-primary w-100"--}}
            {{--                               data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-dismiss-="click"--}}
            {{--                               title="200+ in-house components and 3rd-party plugins">--}}
            {{--                                <span class="btn-label">Docs & Components</span>--}}
            {{--                                <i class="ki-outline ki-document btn-icon fs-2"></i>--}}
            {{--                            </a>--}}
            {{--                        </div>--}}
            <!--end::Footer-->

        </div>
        <!--end::Aside-->


        <!--begin::Wrapper-->
        <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
            <!--begin::Header-->
            <div id="kt_header" style="" class="header align-items-stretch">
                <!--begin::Container-->
                <div class="container-fluid d-flex align-items-stretch justify-content-between">
                    <!--begin::Aside mobile toggle-->
                    <div class="d-flex align-items-center d-lg-none ms-n4 me-1" title="Show aside menu">
                        <div class="btn btn-icon btn-active-color-white" id="kt_aside_mobile_toggle">
                            <i class="ki-outline ki-burger-menu fs-1"></i>
                        </div>
                    </div>
                    <!--end::Aside mobile toggle-->
                    <!--begin::Mobile logo-->
                    <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
                        <a href="index.html" class="d-lg-none">
                            <img alt="Logo" src="{{asset('resources/assets/new_front/')}}/media/logos/demo13-small.svg"
                                 class="h-25px"/>
                        </a>
                    </div>
                    <!--end::Mobile logo-->
                    <!--begin::Wrapper-->
                    <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1">
                        <!--begin::Navbar-->
                        <div class="d-flex align-items-stretch" id="kt_header_nav">
                            <!--begin::Menu wrapper-->
                            <div class="header-menu align-items-stretch" data-kt-drawer="true"
                                 data-kt-drawer-name="header-menu" data-kt-drawer-activate="{default: true, lg: false}"
                                 data-kt-drawer-overlay="true"
                                 data-kt-drawer-width="{default:'200px', '300px': '250px'}"
                                 data-kt-drawer-direction="end" data-kt-drawer-toggle="#kt_header_menu_mobile_toggle"
                                 data-kt-swapper="true" data-kt-swapper-mode="prepend"
                                 data-kt-swapper-parent="{default: '#kt_body', lg: '#kt_header_nav'}">
                                @if(count($branches) > 1)
                                    <div
                                        class="menu menu-rounded menu-column menu-lg-row menu-root-here-bg-desktop menu-active-bg menu-state-primary menu-title-gray-800 menu-arrow-gray-500 align-items-stretch my-5 my-lg-0 px-2 px-lg-0 fw-semibold fs-6"
                                        id="#kt_header_menu" data-kt-menu="true">
                                        <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                                             data-kt-menu-placement="bottom-start"
                                             class="menu-item menu-here-bg menu-lg-down-accordion me-0 me-lg-2">
                                        <span class="menu-link py-3">
                                            {{@$mainSettings->name}}
                                        </span>
                                        </div>
                                    </div>
                                @endif
                                <!--begin::Menu-->
                            </div>
                            <!--end::Menu wrapper-->
                        </div>
                        <!--end::Navbar-->
                        <!--begin::Toolbar wrapper-->
                        <div class="topbar d-flex align-items-stretch flex-shrink-0">

                            <!--begin::dashboardMini-->
                            <div class="d-flex align-items-stretch">
                                <!--begin::drawer toggle-->
                                <div class="topbar-item px-3 px-lg-4" id="kt_activities_toggle">
                                    <a href="{{route('sw.dashboardMini')}}" class="m-header-link"><i class="ki-outline ki-barcode fs-1"></i></a>
                                </div>
                                <!--end::drawer toggle-->
                            </div>
                            <!--end::dashboardMini-->
                            <!--begin::dashboardPTMini-->
                            <div class="d-flex align-items-stretch">
                                <!--begin::drawer toggle-->
                                <div class="topbar-item px-3 px-lg-4" id="kt_activities_toggle">
                                    <a href="{{route('sw.dashboardPTMini')}}" class="m-header-link"><i class="ki-outline ki-scan-barcode fs-1"></i></a>
                                </div>
                                <!--end::drawer toggle-->
                            </div>
                            <!--end::dashboardPTMini-->
                            <!--begin::userAttendees-->
                            <div class="d-flex align-items-stretch">
                                <!--begin::drawer toggle-->
                                <div class="topbar-item px-3 px-lg-4" id="kt_activities_toggle">
                                    <a href="{{route('sw.userAttendees')}}" class="m-header-link"><i class="ki-outline ki-user fs-1"></i></a>
                                </div>
                                <!--end::drawer toggle-->
                            </div>
                            <!--end::userAttendees-->
                            <!--begin::listHelperTools-->
                            <div class="d-flex align-items-stretch">
                                <!--begin::drawer toggle-->
                                <div class="topbar-item px-3 px-lg-4" id="kt_activities_toggle">
                                    <a href="{{route('sw.listHelperTools')}}" class="m-header-link"><i class="ki-outline ki-wrench fs-1"></i></a>
                                </div>
                                <!--end::drawer toggle-->
                            </div>
                            <!--end::listHelperTools-->


                            <!--begin::language-->
                            <div class="d-flex align-items-center">
                                <!--begin::Menu toggle-->
                                <a href="#" class="topbar-item px-3 px-lg-4"
                                   data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent"
                                   data-kt-menu-placement="bottom-end">
                                    <i class="fa fa-language fs-1"></i>
                                </a>
                                <!--begin::Menu toggle-->
                                <!--begin::Menu-->
                                <div
                                    class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px"
                                    data-kt-menu="true" >
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3 my-0">
                                        <a href="{{preg_replace('/'.request()->segment(1).'/', 'ar', strtolower(request()->fullUrl()),1)}}" class="menu-link px-3 py-2" >
													<span class="menu-icon" data-kt-element="icon">
														<i class="ki-outline fs-2">ع</i>
													</span>
                                            <span class="menu-title">العربيه</span>
                                        </a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3 my-0">
                                        <a href="{{preg_replace('/'.request()->segment(1).'/', 'en', strtolower(request()->fullUrl()),1)}}" class="menu-link px-3 py-2" >
													<span class="menu-icon" data-kt-element="icon">
														<i class="ki-outline fs-2">E</i>
													</span>
                                            <span class="menu-title">English</span>
                                        </a>
                                    </div>
                                    <!--end::Menu item-->
                                </div>
                                <!--end::Menu-->
                            </div>
                            <!--end::language-->

                            @if($swUser && $swUser->is_super_user && (count($branches) > 1))

                                <!--begin::branches-->
                                <div class="d-flex align-items-center">
                                    <!--begin::Menu toggle-->
                                    <a href="#" class="topbar-item px-3 px-lg-4"
                                       data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent"
                                       data-kt-menu-placement="bottom-end">
                                        <i class="ki-outline  ki-geolocation-home fs-1"></i>
                                    </a>
                                    <!--begin::Menu toggle-->
                                    <!--begin::Menu-->
                                    <div
                                        class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-250px"
                                        data-kt-menu="true" >
                                        <!--begin::Menu item-->
                                        @foreach($branches as $branch)
                                            <div class="menu-item px-3 my-0" @if(@$swUser && @$swUser->branch_setting_id == $branch->id) style="background-color: #dddddd;" @endif>
                                                <a href="{{route('sw.branchSwitch', $branch->id)}}" class="menu-link px-3 py-2" data-kt-element="mode">
													<span class="menu-icon" data-kt-element="icon">
														<i class="ki-outline ki-geolocation-home fs-2"></i>
													</span>
                                                    <span class="menu-title">{{$branch->name}}</span>
                                                </a>
                                            </div>
                                            <!--end::Menu item-->
                                        @endforeach
                                        <!--begin::Menu item-->
                                    </div>
                                    <!--end::Menu-->
                                </div>
                                <!--end::branches-->
                            @endif


                            <!--begin::Notifications-->
                            <div class="d-flex align-items-stretch">
                                <!--begin::Menu wrapper-->
                                <div class="topbar-item px-3 px-lg-4 position-relative" data-kt-menu-trigger="click"
                                     data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end"
                                     data-kt-menu-flip="bottom">
                                    <i class="ki-outline  ki-notification fs-1"></i>
                                    @if(($unreadNotificationsCount ?? 0) > 0)
                                        <span
                                            class="bullet bullet-dot bg-success h-6px w-6px position-absolute translate-middle top-0 mt-4 start-50 animation-blink"></span>
                                    @endif
                                </div>
                                <!--begin::Menu-->
                                <div class="menu menu-sub menu-sub-dropdown menu-column w-350px w-lg-375px"
                                     data-kt-menu="true" id="kt_menu_notifications">
                                    <!--begin::Heading-->
                                    <div class="d-flex flex-column bgi-no-repeat rounded-top"
                                         style="background-image:url('{{asset('resources/assets/media/misc/menu-header-bg.jpg')}}')">
                                        <!--begin::Title-->
                                        <h3 class="fw-semibold px-9 mt-10 mb-6">{{trans('sw.notifications')}}
                                            <span class="fs-8 opacity-75 ps-3 bold">{!! trans_choice('sw.notifications_message', $unreadNotificationsCount ?? 0) !!}</span></h3>
                                        <!--end::Title-->
                                    </div>
                                    <!--end::Heading-->
                                    <!--begin::Tab panel-->
                                    <div>
                                        <!--begin::Items-->
                                        <div class="scroll-y mh-325px my-5 px-8">
                                            @if(($unreadNotificationsCount ?? 0) == 0)
                                                <!--begin::Item-->
                                                <div class="d-flex flex-stack py-4">
                                                    <!--begin::Section-->
                                                    <div class="d-flex align-items-center">
                                                        <!--begin::Symbol-->
                                                        <div class="symbol symbol-35px me-4">
																	<span class="symbol-label bg-light-primary">
																		<i class="ki-outline ki-abstract-28 fs-2 text-primary"></i>
																	</span>
                                                        </div>
                                                        <!--end::Symbol-->
                                                        <!--begin::Title-->
                                                        <div class="mb-0 me-2">
                                                            <a href="#"
                                                               class="fs-6 text-gray-800 text-hover-primary fw-bold">{{trans('sw.no_notifications')}}</a>
                                                            {{--                                                                <div class="text-gray-500 fs-7"></div>--}}
                                                        </div>
                                                        <!--end::Title-->
                                                    </div>
                                                    <!--end::Section-->
                                                </div>
                                                <!--end::Item-->
                                            @else
                                                @foreach($unreadNotifications ?? [] as $notification)
                                                    <!--begin::Item-->
                                                    <div class="d-flex flex-stack py-4">
                                                        <!--begin::Section-->
                                                        <div class="d-flex align-items-center">
                                                            <!--begin::Symbol-->
                                                            <div class="symbol symbol-35px me-4">
																	<span class="symbol-label bg-light-primary">
																		<i class="ki-outline ki-abstract-28 fs-2 text-primary"></i>
																	</span>
                                                            </div>
                                                            <!--end::Symbol-->
                                                            <!--begin::Title-->
                                                            <div class="mb-0 me-2">
                                                                <a href="javascript:;" onclick="markAsRead('{{$notification->id}}', '{{@$notification->data['data']['body']}}', '{{@$notification->data['data']['url']}}');"
                                                                   class="fs-6 text-gray-800 text-hover-primary fw-bold">{{$notification->data['data']['title']}}</a>
                                                                <div class="text-gray-500 fs-7">Phase 1 development</div>
                                                            </div>
                                                            <!--end::Title-->
                                                        </div>
                                                        <!--end::Section-->
                                                        <!--begin::Label-->
                                                        <span class="badge badge-light fs-8">{{$notification->formatted_time ?? ''}}</span>
                                                        <!--end::Label-->
                                                    </div>
                                                    <!--end::Item-->
                                                @endforeach
                                            @endif
                                        </div>
                                        <!--end::Items-->
                                        <!--begin::View more-->
                                        <div class="py-3 text-center border-top">
                                            <a class="btn btn-color-gray-600 btn-active-color-primary" href="{{route('sw.reportUserNotificationsList')}}">{{trans('global.view_all')}}
                                                <i class="ki-outline ki-arrow-right fs-5"></i></a>
                                        </div>
                                        <!--end::View more-->
                                    </div>
                                    <!--end::Tab panel-->
                                </div>
                                <!--end::Menu-->
                                <!--end::Menu wrapper-->
                            </div>
                            <!--end::Notifications-->
                            <!--begin::Theme mode-->
                            <div class="d-flex align-items-center">
                                <!--begin::Menu toggle-->
                                <a href="#" class="topbar-item px-3 px-lg-4"
                                   data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent"
                                   data-kt-menu-placement="bottom-end">
                                    <i class="ki-outline ki-night-day theme-light-show fs-1"></i>
                                    <i class="ki-outline ki-moon theme-dark-show fs-1"></i>
                                </a>
                                <!--begin::Menu toggle-->
                                <!--begin::Menu-->
                                <div
                                    class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px"
                                    data-kt-menu="true" data-kt-element="theme-mode-menu">
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3 my-0">
                                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode"
                                           data-kt-value="light">
													<span class="menu-icon" data-kt-element="icon">
														<i class="ki-outline ki-night-day fs-2"></i>
													</span>
                                            <span class="menu-title">Light</span>
                                        </a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3 my-0">
                                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode"
                                           data-kt-value="dark">
													<span class="menu-icon" data-kt-element="icon">
														<i class="ki-outline ki-moon fs-2"></i>
													</span>
                                            <span class="menu-title">Dark</span>
                                        </a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    {{--                                    <div class="menu-item px-3 my-0">--}}
                                    {{--                                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode"--}}
                                    {{--                                           data-kt-value="system">--}}
                                    {{--													<span class="menu-icon" data-kt-element="icon">--}}
                                    {{--														<i class="ki-outline ki-screen fs-2"></i>--}}
                                    {{--													</span>--}}
                                    {{--                                            <span class="menu-title">System</span>--}}
                                    {{--                                        </a>--}}
                                    {{--                                    </div>--}}
                                    <!--end::Menu item-->
                                </div>
                                <!--end::Menu-->
                            </div>
                            <!--end::Theme mode-->
                            <!--begin::User-->
                            <div class="d-flex align-items-stretch" id="kt_header_user_menu_toggle">
                                <!--begin::Menu wrapper-->
                                <div
                                    class="topbar-item cursor-pointer symbol px-3 px-lg-5 me-n3 me-lg-n5 symbol-30px symbol-md-35px"
                                    data-kt-menu-trigger="click" data-kt-menu-attach="parent"
                                    data-kt-menu-placement="bottom-end" data-kt-menu-flip="bottom">
                                    <img src="{{@$swUser && @$swUser->image ? @$swUser->image : asset('resources/assets/new_front/img/avatar_placeholder_white.png')}}"
                                         alt="metronic"/>
                                </div>
                                <!--begin::User account menu-->
                                <div
                                    class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px"
                                    data-kt-menu="true">
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <div class="menu-content d-flex align-items-center px-3">
                                            <!--begin::Avatar-->
                                            <div class="symbol symbol-50px me-5">
                                                <img alt="Logo"
                                                     src="{{@$swUser && @$swUser->image ? @$swUser->image : asset('resources/assets/new_front/img/avatar_placeholder_white.png')}}"/>
                                            </div>
                                            <!--end::Avatar-->
                                            <!--begin::Username-->
                                            <div class="d-flex flex-column">
                                                <div class="fw-bold d-flex align-items-center fs-5">{{@$swUser->name}}
                                                    <span class="badge badge-light-success fw-bold fs-8 px-2 py-1 ms-2">{{@$swUser->title}}</span>
                                                </div>
                                                <a  class="fw-semibold text-muted text-hover-primary fs-7">{{@$swUser->email}}</a>
                                            </div>
                                            <!--end::Username-->
                                        </div>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu separator-->
                                    <div class="separator my-2"></div>
                                    <!--end::Menu separator-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-5">
                                        <a href="{{route('sw.editUserProfile')}}" class="menu-link px-5">{{trans('admin.my_info')}}</a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    {{--                                    <div class="menu-item px-5">--}}
                                    {{--                                        <a href="apps/projects/list.html" class="menu-link px-5">--}}
                                    {{--                                            <span class="menu-text">My Projects</span>--}}
                                    {{--                                            <span class="menu-badge">--}}
                                    {{--														<span--}}
                                    {{--                                                            class="badge badge-light-danger badge-circle fw-bold fs-7">3</span>--}}
                                    {{--													</span>--}}
                                    {{--                                        </a>--}}
                                    {{--                                    </div>--}}
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    {{--                                    <div class="menu-item px-5" data-kt-menu-trigger="{default: 'click', lg: 'hover'}"--}}
                                    {{--                                         data-kt-menu-placement="left-start" data-kt-menu-offset="-15px, 0">--}}
                                    {{--                                        <a href="#" class="menu-link px-5">--}}
                                    {{--                                            <span class="menu-title">My Subscription</span>--}}
                                    {{--                                            <span class="menu-arrow"></span>--}}
                                    {{--                                        </a>--}}
                                    {{--                                        <!--begin::Menu sub-->--}}
                                    {{--                                        <div class="menu-sub menu-sub-dropdown w-175px py-4">--}}
                                    {{--                                            <!--begin::Menu item-->--}}
                                    {{--                                            <div class="menu-item px-3">--}}
                                    {{--                                                <a href="account/referrals.html" class="menu-link px-5">Referrals</a>--}}
                                    {{--                                            </div>--}}
                                    {{--                                            <!--end::Menu item-->--}}
                                    {{--                                            <!--begin::Menu item-->--}}
                                    {{--                                            <div class="menu-item px-3">--}}
                                    {{--                                                <a href="account/billing.html" class="menu-link px-5">Billing</a>--}}
                                    {{--                                            </div>--}}
                                    {{--                                            <!--end::Menu item-->--}}
                                    {{--                                            <!--begin::Menu item-->--}}
                                    {{--                                            <div class="menu-item px-3">--}}
                                    {{--                                                <a href="account/statements.html" class="menu-link px-5">Payments</a>--}}
                                    {{--                                            </div>--}}
                                    {{--                                            <!--end::Menu item-->--}}
                                    {{--                                            <!--begin::Menu item-->--}}
                                    {{--                                            <div class="menu-item px-3">--}}
                                    {{--                                                <a href="account/statements.html"--}}
                                    {{--                                                   class="menu-link d-flex flex-stack px-5">Statements--}}
                                    {{--                                                    <span class="ms-2 lh-0" data-bs-toggle="tooltip"--}}
                                    {{--                                                          title="View your statements">--}}
                                    {{--															<i class="ki-outline ki-information-5 fs-5"></i>--}}
                                    {{--														</span></a>--}}
                                    {{--                                            </div>--}}
                                    {{--                                            <!--end::Menu item-->--}}
                                    {{--                                            <!--begin::Menu separator-->--}}
                                    {{--                                            <div class="separator my-2"></div>--}}
                                    {{--                                            <!--end::Menu separator-->--}}
                                    {{--                                            <!--begin::Menu item-->--}}
                                    {{--                                            <div class="menu-item px-3">--}}
                                    {{--                                                <div class="menu-content px-3">--}}
                                    {{--                                                    <label--}}
                                    {{--                                                        class="form-check form-switch form-check-custom form-check-solid">--}}
                                    {{--                                                        <input class="form-check-input w-30px h-20px" type="checkbox"--}}
                                    {{--                                                               value="1" checked="checked" name="notifications"/>--}}
                                    {{--                                                        <span--}}
                                    {{--                                                            class="form-check-label text-muted fs-7">Notifications</span>--}}
                                    {{--                                                    </label>--}}
                                    {{--                                                </div>--}}
                                    {{--                                            </div>--}}
                                    {{--                                            <!--end::Menu item-->--}}
                                    {{--                                        </div>--}}
                                    {{--                                        <!--end::Menu sub-->--}}
                                    {{--                                    </div>--}}
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-5">
                                        <a href="{{route('sw.listSwPayment')}}" class="menu-link px-5">{{trans('sw.billing')}}</a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu separator-->
                                    <div class="separator my-2"></div>
                                    <!--end::Menu separator-->
                                    <!--begin::Menu item-->
                                    {{--                                    <div class="menu-item px-5" data-kt-menu-trigger="{default: 'click', lg: 'hover'}"--}}
                                    {{--                                         data-kt-menu-placement="left-start" data-kt-menu-offset="-15px, 0">--}}
                                    {{--                                        <a href="#" class="menu-link px-5">--}}
                                    {{--													<span class="menu-title position-relative">Language--}}
                                    {{--													<span--}}
                                    {{--                                                        class="fs-8 rounded bg-light px-3 py-2 position-absolute translate-middle-y top-50 end-0">English--}}
                                    {{--													<img class="w-15px h-15px rounded-1 ms-2"--}}
                                    {{--                                                         src="{{asset('resources/assets/new_front/')}}/media/flags/united-states.svg"--}}
                                    {{--                                                         alt=""/></span></span>--}}
                                    {{--                                        </a>--}}
                                    {{--                                        <!--begin::Menu sub-->--}}
                                    {{--                                        <div class="menu-sub menu-sub-dropdown w-175px py-4">--}}
                                    {{--                                            <!--begin::Menu item-->--}}
                                    {{--                                            <div class="menu-item px-3">--}}
                                    {{--                                                <a href="account/settings.html" class="menu-link d-flex px-5 active">--}}
                                    {{--														<span class="symbol symbol-20px me-4">--}}
                                    {{--															<img class="rounded-1"--}}
                                    {{--                                                                 src="{{asset('resources/assets/new_front/')}}/media/flags/united-states.svg"--}}
                                    {{--                                                                 alt=""/>--}}
                                    {{--														</span>English</a>--}}
                                    {{--                                            </div>--}}
                                    {{--                                            <!--end::Menu item-->--}}
                                    {{--                                            <!--begin::Menu item-->--}}
                                    {{--                                            <div class="menu-item px-3">--}}
                                    {{--                                                <a href="account/settings.html" class="menu-link d-flex px-5">--}}
                                    {{--														<span class="symbol symbol-20px me-4">--}}
                                    {{--															<img class="rounded-1"--}}
                                    {{--                                                                 src="{{asset('resources/assets/new_front/')}}/media/flags/spain.svg"--}}
                                    {{--                                                                 alt=""/>--}}
                                    {{--														</span>Spanish</a>--}}
                                    {{--                                            </div>--}}
                                    {{--                                            <!--end::Menu item-->--}}
                                    {{--                                            <!--begin::Menu item-->--}}
                                    {{--                                            <div class="menu-item px-3">--}}
                                    {{--                                                <a href="account/settings.html" class="menu-link d-flex px-5">--}}
                                    {{--														<span class="symbol symbol-20px me-4">--}}
                                    {{--															<img class="rounded-1"--}}
                                    {{--                                                                 src="{{asset('resources/assets/new_front/')}}/media/flags/germany.svg"--}}
                                    {{--                                                                 alt=""/>--}}
                                    {{--														</span>German</a>--}}
                                    {{--                                            </div>--}}
                                    {{--                                            <!--end::Menu item-->--}}
                                    {{--                                            <!--begin::Menu item-->--}}
                                    {{--                                            <div class="menu-item px-3">--}}
                                    {{--                                                <a href="account/settings.html" class="menu-link d-flex px-5">--}}
                                    {{--														<span class="symbol symbol-20px me-4">--}}
                                    {{--															<img class="rounded-1"--}}
                                    {{--                                                                 src="{{asset('resources/assets/new_front/')}}/media/flags/japan.svg"--}}
                                    {{--                                                                 alt=""/>--}}
                                    {{--														</span>Japanese</a>--}}
                                    {{--                                            </div>--}}
                                    {{--                                            <!--end::Menu item-->--}}
                                    {{--                                            <!--begin::Menu item-->--}}
                                    {{--                                            <div class="menu-item px-3">--}}
                                    {{--                                                <a href="account/settings.html" class="menu-link d-flex px-5">--}}
                                    {{--														<span class="symbol symbol-20px me-4">--}}
                                    {{--															<img class="rounded-1"--}}
                                    {{--                                                                 src="{{asset('resources/assets/new_front/')}}/media/flags/france.svg"--}}
                                    {{--                                                                 alt=""/>--}}
                                    {{--														</span>French</a>--}}
                                    {{--                                            </div>--}}
                                    {{--                                            <!--end::Menu item-->--}}
                                    {{--                                        </div>--}}
                                    {{--                                        <!--end::Menu sub-->--}}
                                    {{--                                    </div>--}}
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    {{--                                    <div class="menu-item px-5 my-1">--}}
                                    {{--                                        <a href="account/settings.html" class="menu-link px-5">Account Settings</a>--}}
                                    {{--                                    </div>--}}
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-5">
                                        <a href="{{ route('sw.logout') }}" class="menu-link px-5">{{trans('admin.logout')}}</a>
                                    </div>
                                    <!--end::Menu item-->
                                </div>
                                <!--end::User account menu-->
                                <!--end::Menu wrapper-->
                            </div>
                            <!--begin::Heaeder menu toggle-->
                            <div class="d-flex align-items-stretch d-lg-none px-3 me-n3" title="Show header menu">
                                <div class="topbar-item" id="kt_header_menu_mobile_toggle">
                                    <i class="ki-outline ki-burger-menu-2 fs-1"></i>
                                </div>
                            </div>
                            <!--end::Heaeder menu toggle-->
                        </div>
                        <!--end::Toolbar wrapper-->
                    </div>
                    <!--end::Wrapper-->
                </div>
                <!--end::Container-->
            </div>
            <!--end::Header-->

            @yield('content')

            <!--begin::Footer-->
            <div class="footer py-4 d-flex flex-lg-column" id="kt_footer">
                <!--begin::Container-->
                {{--                <div class="container-fluid d-flex flex-column flex-md-row align-items-center justify-content-between">--}}
                {{--                    <!--begin::Copyright-->--}}
                {{--                    <div class="text-gray-900 order-2 order-md-1">--}}
                {{--                        <span class="text-muted fw-semibold me-1">2025&copy;</span>--}}
                {{--                        <a href="" target="_blank" class="text-gray-800 text-hover-primary">Keenthemes</a>--}}
                {{--                    </div>--}}
                {{--                    <!--end::Copyright-->--}}
                {{--                    <!--begin::Menu-->--}}
                {{--                    <ul class="menu menu-gray-600 menu-hover-primary fw-semibold order-1">--}}
                {{--                        <li class="menu-item">--}}
                {{--                            <a href="https://keenthemes.com" target="_blank" class="menu-link px-2">About</a>--}}
                {{--                        </li>--}}
                {{--                        <li class="menu-item">--}}
                {{--                            <a href="https://devs.keenthemes.com" target="_blank" class="menu-link px-2">Support</a>--}}
                {{--                        </li>--}}
                {{--                        <li class="menu-item">--}}
                {{--                            <a href="https://1.envato.market/EA4JP" target="_blank" class="menu-link px-2">Purchase</a>--}}
                {{--                        </li>--}}
                {{--                    </ul>--}}
                {{--                    <!--end::Menu-->--}}
                {{--                </div>--}}
                <!--end::Container-->
            </div>
            <!--end::Footer-->
        </div>
        <!--end::Wrapper-->

    </div>
    <!--end::Page-->
</div>
<!--end::Root-->

<!--end::Main-->
<!--begin::Scrolltop-->
<div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
    <i class="ki-outline ki-arrow-up"></i>
</div>
<!--end::Scrolltop-->


@include('software::layouts.modules')

{{--    <input id="barcode_input_global" class="barcode_input pos-fixed t--100 l--50"--}}
{{--           placeholder="ادخل الباركود" autofocus type="text">--}}
<input id="barcode_input_global" class="barcode_input" style="position: fixed;top: -100px;left: -50px" value=""
       placeholder="{{trans('sw.enter_barcode')}}" autofocus type="text">


<!--begin::Javascript-->

<!--begin::Global Javascript Bundle(mandatory for all pages)-->
<script src="{{asset('resources/assets/new_front/')}}/plugins/global/plugins.bundle.js"></script>
<script src="{{asset('resources/assets/new_front/')}}/js/scripts.bundle.js"></script>
<!-- Fix: Ensure KTThemeMode is initialized before KTThemeModeUser uses it -->
<script>
    (function() {
        // Wait for scripts.bundle.js to load
        function patchKTThemeModeUser() {
            // Check if KTThemeModeUser exists and hasn't been patched yet
            if (typeof KTThemeModeUser !== 'undefined' && typeof KTThemeModeUser.init === 'function') {
                // Check if already patched
                if (KTThemeModeUser._patched) return;
                KTThemeModeUser._patched = true;
                
                // Save original init
                var originalInit = KTThemeModeUser.init;
                
                // Override init to wait for KTThemeMode
                KTThemeModeUser.init = function() {
                    function waitForKTThemeMode() {
                        // Check if KTThemeMode exists and has the 'on' method
                        if (typeof KTThemeMode === 'undefined' || typeof KTThemeMode.on !== 'function') {
                            setTimeout(waitForKTThemeMode, 10);
                            return;
                        }
                        // KTThemeMode is ready, call original init
                        try {
                            originalInit.call(this);
                        } catch(e) {
                            console.warn('KTThemeModeUser.init error:', e);
                        }
                    }
                    waitForKTThemeMode();
                };
            } else {
                // Keep checking if not ready yet
                setTimeout(patchKTThemeModeUser, 10);
            }
        }
        
        // Start patching immediately after script loads
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(patchKTThemeModeUser, 50);
            });
        } else {
            setTimeout(patchKTThemeModeUser, 50);
        }
    })();
</script>
<!--end::Global Javascript Bundle-->

<!-- BEGIN CORE PLUGINS -->
<script src="{{asset('resources/assets/new_front/global/plugins/jquery.min.js')}}" type="text/javascript"></script>
<script src="{{asset('resources/assets/new_front/global/plugins/bootstrap/js/bootstrap.min.js')}}"
        type="text/javascript"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/velocity/1.2.2/velocity.min.js" type="text/javascript" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/velocity/1.2.2/velocity.ui.min.js" type="text/javascript" defer></script>

<script src="{{asset('resources/assets/new_front/global/plugins/js.cookie.min.js')}}" type="text/javascript" defer></script>
<script src="{{asset('resources/assets/new_front/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js')}}"
        type="text/javascript" defer></script>
<script src="{{asset('resources/assets/new_front/global/plugins/jquery.blockui.min.js')}}" type="text/javascript" defer></script>
<script src="{{asset('resources/assets/new_front/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js')}}"
        type="text/javascript" defer></script>


<!-- BEGIN Sweet Alert SCRIPTS -->
<link href="{{asset('resources/assets/new_front/global/plugins/sweet-alerts/sweetalert_2.css')}}"
      rel="stylesheet"
      type="text/css"/>
<script src="{{asset('resources/assets/new_front/global/plugins/sweet-alerts/sweetalert_2.js')}}"
        type="text/javascript" defer></script>
@include('generic::flash')
@include('software::layouts.notifications')
@include('software::layouts.backup')
<!--Start of Tawk.to Script-->
{{--<script type="text/javascript">--}}
{{--    var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();--}}
{{--    (function () {--}}
{{--        var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];--}}
{{--        s1.async = true;--}}
{{--        s1.src = 'https://embed.tawk.to/5c404111ab5284048d0d571d/default';--}}
{{--        s1.charset = 'UTF-8';--}}
{{--        s1.setAttribute('crossorigin', '*');--}}
{{--        s0.parentNode.insertBefore(s1, s0);--}}
{{--    })();--}}
{{--</script>--}}
<!--End of Tawk.to Script-->

<!-- Internal Select2.min js -->
<script src="{{asset('resources/assets/new_front/global/scripts/js/select2.min.js')}}" defer></script>
<script src="{{asset('resources/assets/new_front/global/scripts/js/bootstrap-datepicker.js')}}" defer></script>

{{--<script src="{{asset('resources/assets/new_front/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js')}}" type="text/javascript"></script>--}}
<script src="{{asset('/')}}resources/assets/new_front/global/scripts/metronic.js" type="text/javascript" defer></script>

<!-- QR Scanner -->
{{--<script type="module">--}}
{{--    import QrScanner from "{{asset('resources/assets/new_front/global/scripts/qr/qr-scanner.min.js')}}";--}}
{{--    QrScanner.WORKER_PATH = "{{asset('resources/assets/new_front/global/scripts/qr/qr-scanner-worker.min.js')}}"--}}
{{--</script>--}}
{{--<script type="module" language="JavaScript"  src="{{asset('resources/assets/new_front/global/scripts/software/custom_qr_scanner.js')}}"></script>--}}
<script type="text/javascript" language="JavaScript"
        src="{{asset('resources/assets/new_front/global/scripts/software/custom_qr_scanner_logic.js')}}" defer></script>


<!-- Start Renew Member -->
<script>


    var member_subscription_renew_url = "{{route('sw.memberSubscriptionRenew', ':id')}}";
    var member_subscription_edit_url = "{{route('sw.memberSubscriptionEdit', ':id')}}";
    var member_subscription_renew_store_url = "{{route('sw.memberSubscriptionRenewStore', ':id')}}";
    {{--var member_subscription_renew_current_url = "{{route('sw.listMember').'?'.urldecode(ltrim(strstr(\Request::getRequestUri(), '?'), '?'))}}";--}}
    var member_subscription_renew_csrf_token = "{{csrf_token()}}";
    var trans_expire_date_must_after_today = '{{trans('sw.expire_date_must_after_today')}}';
    var trans_amount_paid_must_less_membership = '{{trans('sw.amount_paid_must_less_membership')}}';
    var trans_done = '{{trans('admin.done')}}';
    var trans_successfully_processed = '{{trans('admin.successfully_processed')}}';
    var trans_operation_failed = '{{trans('admin.operation_failed')}}';
    var trans_are_you_sure = '{{trans('admin.are_you_sure')}}';
    var trans_no_please = '{{trans('admin.no_cancel')}}';
    var trans_yes = '{{trans('admin.yes')}}';
    var trans_price = '{{trans('sw.price')}}';
    var trans_after_discount = '{{trans('sw.after_discount')}}';
    var get_vat_percentage = '{{@$mainSettings->vat_details['vat_percentage']}}';
    var trans_discount_value_must_more_zero = '{{trans('sw.trans_discount_value_must_more_zero')}}';

    var member_attendees_url = '{{route('sw.memberAttendees')}}';
    var pt_member_attendees_url = '{{route('sw.memberPTAttendees')}}';
    var member_subscription_unfreeze_url = '{{route('sw.unfreezeMember')}}';
    var activity_membership_member_attendees_url = '{{route('sw.memberActivityMembershipAttendees')}}';
    var member_invitation_attendees_url = '{{route('sw.memberInvitationAttendees')}}';
    var trans_old_membership = '{{trans('sw.old_membership')}}';
    var trans_renew_membership = '{{trans('sw.renew_membership')}}';
    var trans_unfreeze_membership = '{{trans('sw.unfreeze_membership')}}';
    var default_avatar_image = '{{asset('uploads/settings/default.jpg')}}';

    var lang = '{{$lang}}';
    var isRtl = (lang === 'ar');
    var path_mp3 = '{{asset(\Modules\Software\Models\GymSubscription::$uploads_path)}}';
    var current_lang = '{{$lang}}';
    var active_loyalty = {{ @$mainSettings->active_loyalty ? 'true' : 'false' }};

</script>
@if((\Request::route()->getName() !=  'sw.editMember') && (\Request::route()->getName() !=  'sw.listMember'))
    <script type="module" src="{{asset('resources/assets/new_front/global/scripts/software/renew_member.js')}}"></script>
    <!-- End Renew-->
@endif
<script>
    // Fix modal backdrop not being removed after modal close
    $(document).on('hidden.bs.modal', '.modal', function () {
        // Remove all backdrop elements if they still exist
        $('.modal-backdrop').remove();
        // Remove modal-open class from body
        $('body').removeClass('modal-open');
        // Remove padding from body that Bootstrap adds for scrollbar
        var bodyPadding = $('body').css('padding-right');
        if (bodyPadding && bodyPadding !== '0px' && bodyPadding !== '0') {
            $('body').css('padding-right', '');
        }
    });
    
    // Additional cleanup for Bootstrap 5 modals (vanilla JS)
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        document.addEventListener('hidden.bs.modal', function (event) {
            // Remove backdrop if exists
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            // Remove modal-open class
            document.body.classList.remove('modal-open');
            // Remove padding if it was added by Bootstrap
            const bodyPadding = window.getComputedStyle(document.body).getPropertyValue('padding-right');
            if (bodyPadding && bodyPadding !== '0px' && bodyPadding !== '0') {
                document.body.style.paddingRight = '';
            }
        });
    }
    
    $(".modal").each(function (l) {
        $(this).on("show.bs.modal", function (l) {
            var o = $(this).attr("data-easein");
            if (typeof velocity !== 'undefined' && $(".modal-dialog").length) {
            "shake" == o ? $(".modal-dialog").velocity("callout." + o) : "pulse" == o ? $(".modal-dialog").velocity("callout." + o) : "tada" == o ? $(".modal-dialog").velocity("callout." + o) : "flash" == o ? $(".modal-dialog").velocity("callout." + o) : "bounce" == o ? $(".modal-dialog").velocity("callout." + o) : "swing" == o ? $(".modal-dialog").velocity("callout." + o) : $(".modal-dialog").velocity("transition." + o)
            }
        })
    });


    function generateBarcode() {
        $('#modelBarcode').modal('hide');
        document.getElementById("generateBarcode").submit();

        swal({
            title: trans_done,
            text: trans_successfully_processed,
            type: "success",
            timer: 4000,
            confirmButtonText: 'Ok',
        });

    }
</script>
<script src="{{asset('resources/assets/new_front/global/plugins/bootbox/bootbox.min.js')}}" type="text/javascript" defer></script>
<script>
    $('#site_on_off').click(function () {
        bootbox.dialog({
            message: "{{$mainSettings->under_maintenance == 0 ? trans('sw.off_site_msg') : trans('sw.on_site_msg')}}",
            title: "{{$mainSettings->under_maintenance == 0 ? trans('sw.off_site') : trans('sw.on_site_msg')}}",
            buttons: {
                @if($mainSettings->under_maintenance == 1)
                success: {
                    label: "{{trans('sw.yes')}}",
                    className: "green",
                    callback: function () {

                        $.ajax({
                            url: "{{route('siteOn')}}",
                            type: "get",
                            success: (data) => {
                                window.location.reload();
                            },
                            error: (reject) => {
                                var response = $.parseJSON(reject.responseText);
                                console.log(response);
                            }
                        });


                    }
                },
                @else
                danger: {
                    label: "{{trans('sw.yes')}}",
                    className: "red",
                    callback: function () {
                        $.ajax({
                            url: "{{route('siteOff')}}",
                            type: "get",
                            success: (data) => {
                                window.location.reload();
                            },
                            error: (reject) => {
                                var response = $.parseJSON(reject.responseText);
                                console.log(response);
                            }
                        });
                    }
                },
                @endif
                main: {
                    label: "{{trans('sw.no')}}",
                    className: "gray",
                }
            }
        });

    });


    function customStartDateChange() {
        let joining_date = $("#customStartDate").val();
        let period = $('#select_membership option:selected').attr('period');
        setCustomExpireDate(joining_date, period);
    }

    function setCustomExpireDate(joining_date, period) {
        let valid_days = parseInt(period);
        let end_date = new Date(joining_date); // pass start date here
        end_date.setDate(end_date.getDate() + valid_days);
        $('#customExpireDate').val(end_date.getFullYear() + '-' + ((end_date.getMonth() + 1) < 10 ? '0' + (end_date.getMonth() + 1) : (end_date.getMonth() + 1)) + '-' + end_date.getDate());
    }

</script>
@yield('scripts')

<!--end::Javascript-->
</body>
<!--end::Body-->
</html>




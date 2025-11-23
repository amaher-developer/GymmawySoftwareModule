@extends('software::Web.master')

@section('content')
    <div class="main">
        <div class="container">
            <ul class="breadcrumb">
                <li><a href="{{route('sw.showCustomerProfile')}}">{{trans('admin.home')}}</a></li>
                <li class="active"><a href="#">{{$title}}</a></li>
            </ul>
            <!-- BEGIN SIDEBAR & CONTENT -->
            <div class="row margin-bottom-40">
                <!-- BEGIN CONTENT -->
                <div class="col-md-12 col-sm-12">
                    <div class="content-page">
                        <div class="row margin-bottom-30">
                            <!-- BEGIN CAROUSEL -->
                            <div class="col-md-3 ">
                                @include('software::Web.__side_menu')
                            </div>
                            <!-- END CAROUSEL -->

                        @if(@count($tracks) > 0)
                            <!-- BEGIN LEFT SIDEBAR -->
                            <div class="col-md-9 col-sm-9 blog-posts">
                                @foreach($tracks as $track)
                                    <div class="row">


                                        <div class="col-md-8 col-sm-8">
                                            <h2>{{$track->name}}</h2>
                                            <p>{{strip_tags($track->notes)}}</p>
                                            <br>
                                            <div class="row front-lists-v2 margin-bottom-15">
                                                <div class="col-md-6">
                                                    <ul class="list-unstyled">
                                                        <li><i class="fa fa-calendar"></i> <b>{{trans('sw.date')}}:</b> {{\Carbon\Carbon::parse($track->date)->toDateString()}}</li>
                                                        <li><i class="fa fa-sort-numeric-asc"></i> <b>{{trans('sw.height')}}:</b> {{$track->height}}</li>
                                                        <li><i class="fa fa-sort-numeric-asc"></i> <b>{{trans('sw.neck_circumference')}}:</b> {{@$track->neck_circumference ?? '-'}}</li>
                                                        <li><i class="fa fa-sort-numeric-asc"></i> <b>{{trans('sw.arm_circumference')}}:</b> {{@$track->arm_circumference ?? '-'}}</li>
                                                        <li><i class="fa fa-sort-numeric-asc"></i> <b>{{trans('sw.pelvic_circumference')}}:</b> {{@$track->pelvic_circumference ?? '-'}}</li>
                                                    </ul>
                                                </div>
                                                <div class="col-md-6">
                                                    <ul class="list-unstyled">
                                                        <li><br/></li>
                                                        <li><i class="fa fa-balance-scale"></i> <b>{{trans('sw.weight')}}:</b> {{$track->weight}}</li>
                                                        <li><i class="fa fa-sort-numeric-asc"></i> <b>{{trans('sw.chest_circumference')}}:</b> {{@$track->chest_circumference ?? '-'}}</li>
                                                        <li><i class="fa fa-sort-numeric-asc"></i> <b>{{trans('sw.abdominal_circumference')}}:</b> {{@$track->abdominal_circumference ?? '-'}}</li>
                                                        <li><i class="fa fa-sort-numeric-asc"></i> <b>{{trans('sw.thigh_circumference')}}:</b> {{@$track->thigh_circumference ?? '-'}}</li>
                                                        {{--                                        <li><i class="fa fa-star"></i> Awesome UI</li>--}}
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <hr class="blog-post-sep">
                                @endforeach

                                <ul class="pagination">
                                    {!! $tracks->appends($search_query)->render()  !!}
                                </ul>
                            </div>
                            <!-- END LEFT SIDEBAR -->

                        @else
                            <!-- BEGIN LEFT SIDEBAR -->
                                <div class="col-md-9 col-sm-9 blog-posts">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="content-page page-404">
                                            <div class="number">
                                                404
                                            </div>
                                            <div class="details">
                                                <h3>{{trans('sw.no_record_found')}}</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- END LEFT SIDEBAR -->
                            @endif

                        </div>


                    </div>
                </div>
                <!-- END CONTENT -->
            </div>
            <!-- BEGIN SIDEBAR & CONTENT -->
        </div>
    </div>






{{--    <div class="page-content-wrapper">--}}
{{--        <div class="page-content" style="margin: 0px">--}}
{{--            <!-- BEGIN SAMPLE PORTLET CONFIGURATION MODAL FORM-->--}}
{{--            <div class="modal fade" id="portlet-config" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">--}}
{{--                <div class="modal-dialog">--}}
{{--                    <div class="modal-content">--}}
{{--                        <div class="modal-header">--}}
{{--                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>--}}
{{--                            <h4 class="modal-title">Modal title</h4>--}}
{{--                        </div>--}}
{{--                        <div class="modal-body">--}}
{{--                            Widget settings form goes here--}}
{{--                        </div>--}}
{{--                        <div class="modal-footer">--}}
{{--                            <button type="button" class="btn blue">Save changes</button>--}}
{{--                            <button type="button" class="btn default" data-dismiss="modal">Close</button>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <!-- /.modal-content -->--}}
{{--                </div>--}}
{{--                <!-- /.modal-dialog -->--}}
{{--            </div>--}}
{{--            <!-- /.modal -->--}}
{{--            <!-- END SAMPLE PORTLET CONFIGURATION MODAL FORM-->--}}
{{--            <!-- BEGIN STYLE CUSTOMIZER -->--}}
{{--            <div class="theme-panel hidden-xs hidden-sm">--}}
{{--                <div class="toggler">--}}
{{--                </div>--}}
{{--                <div class="toggler-close">--}}
{{--                </div>--}}
{{--                <div class="theme-options">--}}
{{--                    <div class="theme-option theme-colors clearfix">--}}
{{--						<span>--}}
{{--						THEME COLOR </span>--}}
{{--                        <ul>--}}
{{--                            <li class="color-default current tooltips" data-style="default" data-container="body" data-original-title="Default">--}}
{{--                            </li>--}}
{{--                            <li class="color-darkblue tooltips" data-style="darkblue" data-container="body" data-original-title="Dark Blue">--}}
{{--                            </li>--}}
{{--                            <li class="color-blue tooltips" data-style="blue" data-container="body" data-original-title="Blue">--}}
{{--                            </li>--}}
{{--                            <li class="color-grey tooltips" data-style="grey" data-container="body" data-original-title="Grey">--}}
{{--                            </li>--}}
{{--                            <li class="color-light tooltips" data-style="light" data-container="body" data-original-title="Light">--}}
{{--                            </li>--}}
{{--                            <li class="color-light2 tooltips" data-style="light2" data-container="body" data-html="true" data-original-title="Light 2">--}}
{{--                            </li>--}}
{{--                        </ul>--}}
{{--                    </div>--}}
{{--                    <div class="theme-option">--}}
{{--						<span>--}}
{{--						Layout </span>--}}
{{--                        <select class="layout-option form-control input-sm">--}}
{{--                            <option value="fluid" selected="selected">Fluid</option>--}}
{{--                            <option value="boxed">Boxed</option>--}}
{{--                        </select>--}}
{{--                    </div>--}}
{{--                    <div class="theme-option">--}}
{{--						<span>--}}
{{--						Header </span>--}}
{{--                        <select class="page-header-option form-control input-sm">--}}
{{--                            <option value="fixed" selected="selected">Fixed</option>--}}
{{--                            <option value="default">Default</option>--}}
{{--                        </select>--}}
{{--                    </div>--}}
{{--                    <div class="theme-option">--}}
{{--						<span>--}}
{{--						Top Menu Dropdown</span>--}}
{{--                        <select class="page-header-top-dropdown-style-option form-control input-sm">--}}
{{--                            <option value="light" selected="selected">Light</option>--}}
{{--                            <option value="dark">Dark</option>--}}
{{--                        </select>--}}
{{--                    </div>--}}
{{--                    <div class="theme-option">--}}
{{--						<span>--}}
{{--						Sidebar Mode</span>--}}
{{--                        <select class="sidebar-option form-control input-sm">--}}
{{--                            <option value="fixed">Fixed</option>--}}
{{--                            <option value="default" selected="selected">Default</option>--}}
{{--                        </select>--}}
{{--                    </div>--}}
{{--                    <div class="theme-option">--}}
{{--						<span>--}}
{{--						Sidebar Menu </span>--}}
{{--                        <select class="sidebar-menu-option form-control input-sm">--}}
{{--                            <option value="accordion" selected="selected">Accordion</option>--}}
{{--                            <option value="hover">Hover</option>--}}
{{--                        </select>--}}
{{--                    </div>--}}
{{--                    <div class="theme-option">--}}
{{--						<span>--}}
{{--						Sidebar Style </span>--}}
{{--                        <select class="sidebar-style-option form-control input-sm">--}}
{{--                            <option value="default" selected="selected">Default</option>--}}
{{--                            <option value="light">Light</option>--}}
{{--                        </select>--}}
{{--                    </div>--}}
{{--                    <div class="theme-option">--}}
{{--						<span>--}}
{{--						Sidebar Position </span>--}}
{{--                        <select class="sidebar-pos-option form-control input-sm">--}}
{{--                            <option value="left" selected="selected">Left</option>--}}
{{--                            <option value="right">Right</option>--}}
{{--                        </select>--}}
{{--                    </div>--}}
{{--                    <div class="theme-option">--}}
{{--						<span>--}}
{{--						Footer </span>--}}
{{--                        <select class="page-footer-option form-control input-sm">--}}
{{--                            <option value="fixed">Fixed</option>--}}
{{--                            <option value="default" selected="selected">Default</option>--}}
{{--                        </select>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <!-- END STYLE CUSTOMIZER -->--}}
{{--            <!-- BEGIN PAGE HEADER-->--}}
{{--            <h3 class="page-title">--}}
{{--                Old User Profile <small>old user profile sample</small>--}}
{{--            </h3>--}}
{{--            <div class="page-bar">--}}
{{--                <ul class="page-breadcrumb">--}}
{{--                    <li>--}}
{{--                        <i class="fa fa-home"></i>--}}
{{--                        <a href="index.html">Home</a>--}}
{{--                        <i class="fa fa-angle-right"></i>--}}
{{--                    </li>--}}
{{--                    <li>--}}
{{--                        <a href="#">Pages</a>--}}
{{--                        <i class="fa fa-angle-right"></i>--}}
{{--                    </li>--}}
{{--                    <li>--}}
{{--                        <a href="#">Old User Profile</a>--}}
{{--                    </li>--}}
{{--                </ul>--}}
{{--                <div class="page-toolbar">--}}
{{--                    <div class="btn-group pull-right">--}}
{{--                        <button type="button" class="btn btn-fit-height grey-salt dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">--}}
{{--                            Actions <i class="fa fa-angle-down"></i>--}}
{{--                        </button>--}}
{{--                        <ul class="dropdown-menu pull-right" role="menu">--}}
{{--                            <li>--}}
{{--                                <a href="#">Action</a>--}}
{{--                            </li>--}}
{{--                            <li>--}}
{{--                                <a href="#">Another action</a>--}}
{{--                            </li>--}}
{{--                            <li>--}}
{{--                                <a href="#">Something else here</a>--}}
{{--                            </li>--}}
{{--                            <li class="divider">--}}
{{--                            </li>--}}
{{--                            <li>--}}
{{--                                <a href="#">Separated link</a>--}}
{{--                            </li>--}}
{{--                        </ul>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <!-- END PAGE HEADER-->--}}
{{--            <!-- BEGIN PAGE CONTENT-->--}}
{{--            <div class="row profile">--}}
{{--                <div class="col-md-12">--}}
{{--                    <!--BEGIN TABS-->--}}
{{--                    <div class="tabbable tabbable-custom tabbable-full-width">--}}
{{--                        <ul class="nav nav-tabs">--}}
{{--                            <li class="active">--}}
{{--                                <a href="#tab_1_1" data-toggle="tab">--}}
{{--                                    Overview </a>--}}
{{--                            </li>--}}
{{--                            <li>--}}
{{--                                <a href="#tab_1_3" data-toggle="tab">--}}
{{--                                    Account </a>--}}
{{--                            </li>--}}
{{--                            <li>--}}
{{--                                <a href="#tab_1_4" data-toggle="tab">--}}
{{--                                    Projects </a>--}}
{{--                            </li>--}}
{{--                            <li>--}}
{{--                                <a href="#tab_1_6" data-toggle="tab">--}}
{{--                                    Help </a>--}}
{{--                            </li>--}}
{{--                        </ul>--}}
{{--                        <div class="tab-content">--}}
{{--                            <div class="tab-pane active" id="tab_1_1">--}}
{{--                                <div class="row">--}}
{{--                                    <div class="col-md-3">--}}
{{--                                        <ul class="list-unstyled profile-nav">--}}
{{--                                            <li>--}}
{{--                                                <img src="http://localhost/gym/gymmawy/resources/assets/new_front/img/logo/default.png" class="img-responsive" alt=""/>--}}
{{--                                                <a href="#" class="profile-edit">--}}
{{--                                                    edit </a>--}}
{{--                                            </li>--}}
{{--                                            <li>--}}
{{--                                                <a href="#">--}}
{{--                                                    Projects </a>--}}
{{--                                            </li>--}}
{{--                                            <li>--}}
{{--                                                <a href="#">--}}
{{--                                                    Messages <span>--}}
{{--												3 </span>--}}
{{--                                                </a>--}}
{{--                                            </li>--}}
{{--                                            <li>--}}
{{--                                                <a href="#">--}}
{{--                                                    Friends </a>--}}
{{--                                            </li>--}}
{{--                                            <li>--}}
{{--                                                <a href="#">--}}
{{--                                                    Settings </a>--}}
{{--                                            </li>--}}
{{--                                        </ul>--}}
{{--                                    </div>--}}
{{--                                    <div class="col-md-9">--}}
{{--                                        <div class="row">--}}
{{--                                            <div class="col-md-8 profile-info">--}}
{{--                                                <h1>John Doe</h1>--}}
{{--                                                <p>--}}
{{--                                                    Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt laoreet dolore magna aliquam tincidunt erat volutpat laoreet dolore magna aliquam tincidunt erat volutpat.--}}
{{--                                                </p>--}}
{{--                                                <p>--}}
{{--                                                    <a href="#">--}}
{{--                                                        www.mywebsite.com </a>--}}
{{--                                                </p>--}}
{{--                                                <ul class="list-inline">--}}
{{--                                                    <li>--}}
{{--                                                        <i class="fa fa-map-marker"></i> Spain--}}
{{--                                                    </li>--}}
{{--                                                    <li>--}}
{{--                                                        <i class="fa fa-calendar"></i> 18 Jan 1982--}}
{{--                                                    </li>--}}
{{--                                                    <li>--}}
{{--                                                        <i class="fa fa-briefcase"></i> Design--}}
{{--                                                    </li>--}}
{{--                                                    <li>--}}
{{--                                                        <i class="fa fa-star"></i> Top Seller--}}
{{--                                                    </li>--}}
{{--                                                    <li>--}}
{{--                                                        <i class="fa fa-heart"></i> BASE Jumping--}}
{{--                                                    </li>--}}
{{--                                                </ul>--}}
{{--                                            </div>--}}
{{--                                            <!--end col-md-8-->--}}
{{--                                            <div class="col-md-4">--}}
{{--                                                <div class="portlet sale-summary">--}}
{{--                                                    <div class="portlet-title">--}}
{{--                                                        <div class="caption">--}}
{{--                                                            Sales Summary--}}
{{--                                                        </div>--}}
{{--                                                        <div class="tools">--}}
{{--                                                            <a class="reload" href="javascript:;">--}}
{{--                                                            </a>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="portlet-body">--}}
{{--                                                        <ul class="list-unstyled">--}}
{{--                                                            <li>--}}
{{--																<span class="sale-info">--}}
{{--																TODAY SOLD <i class="fa fa-img-up"></i>--}}
{{--																</span>--}}
{{--                                                                <span class="sale-num">--}}
{{--																23 </span>--}}
{{--                                                            </li>--}}
{{--                                                            <li>--}}
{{--																<span class="sale-info">--}}
{{--																WEEKLY SALES <i class="fa fa-img-down"></i>--}}
{{--																</span>--}}
{{--                                                                <span class="sale-num">--}}
{{--																87 </span>--}}
{{--                                                            </li>--}}
{{--                                                            <li>--}}
{{--																<span class="sale-info">--}}
{{--																TOTAL SOLD </span>--}}
{{--                                                                <span class="sale-num">--}}
{{--																2377 </span>--}}
{{--                                                            </li>--}}
{{--                                                            <li>--}}
{{--																<span class="sale-info">--}}
{{--																EARNS </span>--}}
{{--                                                                <span class="sale-num">--}}
{{--																$37.990 </span>--}}
{{--                                                            </li>--}}
{{--                                                        </ul>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                            <!--end col-md-4-->--}}
{{--                                        </div>--}}
{{--                                        <!--end row-->--}}
{{--                                        <div class="tabbable tabbable-custom tabbable-custom-profile">--}}
{{--                                            <ul class="nav nav-tabs">--}}
{{--                                                <li class="active">--}}
{{--                                                    <a href="#tab_1_11" data-toggle="tab">--}}
{{--                                                        Latest Customers </a>--}}
{{--                                                </li>--}}
{{--                                                <li>--}}
{{--                                                    <a href="#tab_1_22" data-toggle="tab">--}}
{{--                                                        Feeds </a>--}}
{{--                                                </li>--}}
{{--                                            </ul>--}}
{{--                                            <div class="tab-content">--}}
{{--                                                <div class="tab-pane active" id="tab_1_11">--}}
{{--                                                    <div class="portlet-body">--}}
{{--                                                        <table class="table table-striped table-bordered table-advance table-hover">--}}
{{--                                                            <thead>--}}
{{--                                                            <tr>--}}
{{--                                                                <th>--}}
{{--                                                                    <i class="fa fa-briefcase"></i> Company--}}
{{--                                                                </th>--}}
{{--                                                                <th class="hidden-xs">--}}
{{--                                                                    <i class="fa fa-question"></i> Descrition--}}
{{--                                                                </th>--}}
{{--                                                                <th>--}}
{{--                                                                    <i class="fa fa-bookmark"></i> Amount--}}
{{--                                                                </th>--}}
{{--                                                                <th>--}}
{{--                                                                </th>--}}
{{--                                                            </tr>--}}
{{--                                                            </thead>--}}
{{--                                                            <tbody>--}}
{{--                                                            <tr>--}}
{{--                                                                <td>--}}
{{--                                                                    <a href="#">--}}
{{--                                                                        Pixel Ltd </a>--}}
{{--                                                                </td>--}}
{{--                                                                <td class="hidden-xs">--}}
{{--                                                                    Server hardware purchase--}}
{{--                                                                </td>--}}
{{--                                                                <td>--}}
{{--                                                                    52560.10$ <span class="label label-success label-sm">--}}
{{--																Paid </span>--}}
{{--                                                                </td>--}}
{{--                                                                <td>--}}
{{--                                                                    <a class="btn default btn-xs green-stripe" href="#">--}}
{{--                                                                        View </a>--}}
{{--                                                                </td>--}}
{{--                                                            </tr>--}}
{{--                                                            <tr>--}}
{{--                                                                <td>--}}
{{--                                                                    <a href="#">--}}
{{--                                                                        Smart House </a>--}}
{{--                                                                </td>--}}
{{--                                                                <td class="hidden-xs">--}}
{{--                                                                    Office furniture purchase--}}
{{--                                                                </td>--}}
{{--                                                                <td>--}}
{{--                                                                    5760.00$ <span class="label label-warning label-sm">--}}
{{--																Pending </span>--}}
{{--                                                                </td>--}}
{{--                                                                <td>--}}
{{--                                                                    <a class="btn default btn-xs blue-stripe" href="#">--}}
{{--                                                                        View </a>--}}
{{--                                                                </td>--}}
{{--                                                            </tr>--}}
{{--                                                            <tr>--}}
{{--                                                                <td>--}}
{{--                                                                    <a href="#">--}}
{{--                                                                        FoodMaster Ltd </a>--}}
{{--                                                                </td>--}}
{{--                                                                <td class="hidden-xs">--}}
{{--                                                                    Company Anual Dinner Catering--}}
{{--                                                                </td>--}}
{{--                                                                <td>--}}
{{--                                                                    12400.00$ <span class="label label-success label-sm">--}}
{{--																Paid </span>--}}
{{--                                                                </td>--}}
{{--                                                                <td>--}}
{{--                                                                    <a class="btn default btn-xs blue-stripe" href="#">--}}
{{--                                                                        View </a>--}}
{{--                                                                </td>--}}
{{--                                                            </tr>--}}
{{--                                                            <tr>--}}
{{--                                                                <td>--}}
{{--                                                                    <a href="#">--}}
{{--                                                                        WaterPure Ltd </a>--}}
{{--                                                                </td>--}}
{{--                                                                <td class="hidden-xs">--}}
{{--                                                                    Payment for Jan 2013--}}
{{--                                                                </td>--}}
{{--                                                                <td>--}}
{{--                                                                    610.50$ <span class="label label-danger label-sm">--}}
{{--																Overdue </span>--}}
{{--                                                                </td>--}}
{{--                                                                <td>--}}
{{--                                                                    <a class="btn default btn-xs red-stripe" href="#">--}}
{{--                                                                        View </a>--}}
{{--                                                                </td>--}}
{{--                                                            </tr>--}}
{{--                                                            <tr>--}}
{{--                                                                <td>--}}
{{--                                                                    <a href="#">--}}
{{--                                                                        Pixel Ltd </a>--}}
{{--                                                                </td>--}}
{{--                                                                <td class="hidden-xs">--}}
{{--                                                                    Server hardware purchase--}}
{{--                                                                </td>--}}
{{--                                                                <td>--}}
{{--                                                                    52560.10$ <span class="label label-success label-sm">--}}
{{--																Paid </span>--}}
{{--                                                                </td>--}}
{{--                                                                <td>--}}
{{--                                                                    <a class="btn default btn-xs green-stripe" href="#">--}}
{{--                                                                        View </a>--}}
{{--                                                                </td>--}}
{{--                                                            </tr>--}}
{{--                                                            <tr>--}}
{{--                                                                <td>--}}
{{--                                                                    <a href="#">--}}
{{--                                                                        Smart House </a>--}}
{{--                                                                </td>--}}
{{--                                                                <td class="hidden-xs">--}}
{{--                                                                    Office furniture purchase--}}
{{--                                                                </td>--}}
{{--                                                                <td>--}}
{{--                                                                    5760.00$ <span class="label label-warning label-sm">--}}
{{--																Pending </span>--}}
{{--                                                                </td>--}}
{{--                                                                <td>--}}
{{--                                                                    <a class="btn default btn-xs blue-stripe" href="#">--}}
{{--                                                                        View </a>--}}
{{--                                                                </td>--}}
{{--                                                            </tr>--}}
{{--                                                            <tr>--}}
{{--                                                                <td>--}}
{{--                                                                    <a href="#">--}}
{{--                                                                        FoodMaster Ltd </a>--}}
{{--                                                                </td>--}}
{{--                                                                <td class="hidden-xs">--}}
{{--                                                                    Company Anual Dinner Catering--}}
{{--                                                                </td>--}}
{{--                                                                <td>--}}
{{--                                                                    12400.00$ <span class="label label-success label-sm">--}}
{{--																Paid </span>--}}
{{--                                                                </td>--}}
{{--                                                                <td>--}}
{{--                                                                    <a class="btn default btn-xs blue-stripe" href="#">--}}
{{--                                                                        View </a>--}}
{{--                                                                </td>--}}
{{--                                                            </tr>--}}
{{--                                                            </tbody>--}}
{{--                                                        </table>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                                <!--tab-pane-->--}}
{{--                                                <div class="tab-pane" id="tab_1_22">--}}
{{--                                                    <div class="tab-pane active" id="tab_1_1_1">--}}
{{--                                                        <div class="scroller" data-height="290px" data-always-visible="1" data-rail-visible1="1">--}}
{{--                                                            <ul class="feeds">--}}
{{--                                                                <li>--}}
{{--                                                                    <div class="col1">--}}
{{--                                                                        <div class="cont">--}}
{{--                                                                            <div class="cont-col1">--}}
{{--                                                                                <div class="label label-success">--}}
{{--                                                                                    <i class="fa fa-bell-o"></i>--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                            <div class="cont-col2">--}}
{{--                                                                                <div class="desc">--}}
{{--                                                                                    You have 4 pending tasks. <span class="label label-danger label-sm">--}}
{{--																					Take action <i class="fa fa-share"></i>--}}
{{--																					</span>--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                    <div class="col2">--}}
{{--                                                                        <div class="date">--}}
{{--                                                                            Just now--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                </li>--}}
{{--                                                                <li>--}}
{{--                                                                    <a href="#">--}}
{{--                                                                        <div class="col1">--}}
{{--                                                                            <div class="cont">--}}
{{--                                                                                <div class="cont-col1">--}}
{{--                                                                                    <div class="label label-success">--}}
{{--                                                                                        <i class="fa fa-bell-o"></i>--}}
{{--                                                                                    </div>--}}
{{--                                                                                </div>--}}
{{--                                                                                <div class="cont-col2">--}}
{{--                                                                                    <div class="desc">--}}
{{--                                                                                        New version v1.4 just lunched!--}}
{{--                                                                                    </div>--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                        </div>--}}
{{--                                                                        <div class="col2">--}}
{{--                                                                            <div class="date">--}}
{{--                                                                                20 mins--}}
{{--                                                                            </div>--}}
{{--                                                                        </div>--}}
{{--                                                                    </a>--}}
{{--                                                                </li>--}}
{{--                                                                <li>--}}
{{--                                                                    <div class="col1">--}}
{{--                                                                        <div class="cont">--}}
{{--                                                                            <div class="cont-col1">--}}
{{--                                                                                <div class="label label-danger">--}}
{{--                                                                                    <i class="fa fa-bolt"></i>--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                            <div class="cont-col2">--}}
{{--                                                                                <div class="desc">--}}
{{--                                                                                    Database server #12 overloaded. Please fix the issue.--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                    <div class="col2">--}}
{{--                                                                        <div class="date">--}}
{{--                                                                            24 mins--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                </li>--}}
{{--                                                                <li>--}}
{{--                                                                    <div class="col1">--}}
{{--                                                                        <div class="cont">--}}
{{--                                                                            <div class="cont-col1">--}}
{{--                                                                                <div class="label label-info">--}}
{{--                                                                                    <i class="fa fa-bullhorn"></i>--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                            <div class="cont-col2">--}}
{{--                                                                                <div class="desc">--}}
{{--                                                                                    New order received. Please take care of it.--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                    <div class="col2">--}}
{{--                                                                        <div class="date">--}}
{{--                                                                            30 mins--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                </li>--}}
{{--                                                                <li>--}}
{{--                                                                    <div class="col1">--}}
{{--                                                                        <div class="cont">--}}
{{--                                                                            <div class="cont-col1">--}}
{{--                                                                                <div class="label label-success">--}}
{{--                                                                                    <i class="fa fa-bullhorn"></i>--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                            <div class="cont-col2">--}}
{{--                                                                                <div class="desc">--}}
{{--                                                                                    New order received. Please take care of it.--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                    <div class="col2">--}}
{{--                                                                        <div class="date">--}}
{{--                                                                            40 mins--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                </li>--}}
{{--                                                                <li>--}}
{{--                                                                    <div class="col1">--}}
{{--                                                                        <div class="cont">--}}
{{--                                                                            <div class="cont-col1">--}}
{{--                                                                                <div class="label label-warning">--}}
{{--                                                                                    <i class="fa fa-plus"></i>--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                            <div class="cont-col2">--}}
{{--                                                                                <div class="desc">--}}
{{--                                                                                    New user registered.--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                    <div class="col2">--}}
{{--                                                                        <div class="date">--}}
{{--                                                                            1.5 hours--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                </li>--}}
{{--                                                                <li>--}}
{{--                                                                    <div class="col1">--}}
{{--                                                                        <div class="cont">--}}
{{--                                                                            <div class="cont-col1">--}}
{{--                                                                                <div class="label label-success">--}}
{{--                                                                                    <i class="fa fa-bell-o"></i>--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                            <div class="cont-col2">--}}
{{--                                                                                <div class="desc">--}}
{{--                                                                                    Web server hardware needs to be upgraded. <span class="label label-inverse label-sm">--}}
{{--																					Overdue </span>--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                    <div class="col2">--}}
{{--                                                                        <div class="date">--}}
{{--                                                                            2 hours--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                </li>--}}
{{--                                                                <li>--}}
{{--                                                                    <div class="col1">--}}
{{--                                                                        <div class="cont">--}}
{{--                                                                            <div class="cont-col1">--}}
{{--                                                                                <div class="label label-default">--}}
{{--                                                                                    <i class="fa fa-bullhorn"></i>--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                            <div class="cont-col2">--}}
{{--                                                                                <div class="desc">--}}
{{--                                                                                    New order received. Please take care of it.--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                    <div class="col2">--}}
{{--                                                                        <div class="date">--}}
{{--                                                                            3 hours--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                </li>--}}
{{--                                                                <li>--}}
{{--                                                                    <div class="col1">--}}
{{--                                                                        <div class="cont">--}}
{{--                                                                            <div class="cont-col1">--}}
{{--                                                                                <div class="label label-warning">--}}
{{--                                                                                    <i class="fa fa-bullhorn"></i>--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                            <div class="cont-col2">--}}
{{--                                                                                <div class="desc">--}}
{{--                                                                                    New order received. Please take care of it.--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                    <div class="col2">--}}
{{--                                                                        <div class="date">--}}
{{--                                                                            5 hours--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                </li>--}}
{{--                                                                <li>--}}
{{--                                                                    <div class="col1">--}}
{{--                                                                        <div class="cont">--}}
{{--                                                                            <div class="cont-col1">--}}
{{--                                                                                <div class="label label-info">--}}
{{--                                                                                    <i class="fa fa-bullhorn"></i>--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                            <div class="cont-col2">--}}
{{--                                                                                <div class="desc">--}}
{{--                                                                                    New order received. Please take care of it.--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                    <div class="col2">--}}
{{--                                                                        <div class="date">--}}
{{--                                                                            18 hours--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                </li>--}}
{{--                                                                <li>--}}
{{--                                                                    <div class="col1">--}}
{{--                                                                        <div class="cont">--}}
{{--                                                                            <div class="cont-col1">--}}
{{--                                                                                <div class="label label-default">--}}
{{--                                                                                    <i class="fa fa-bullhorn"></i>--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                            <div class="cont-col2">--}}
{{--                                                                                <div class="desc">--}}
{{--                                                                                    New order received. Please take care of it.--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                    <div class="col2">--}}
{{--                                                                        <div class="date">--}}
{{--                                                                            21 hours--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                </li>--}}
{{--                                                                <li>--}}
{{--                                                                    <div class="col1">--}}
{{--                                                                        <div class="cont">--}}
{{--                                                                            <div class="cont-col1">--}}
{{--                                                                                <div class="label label-info">--}}
{{--                                                                                    <i class="fa fa-bullhorn"></i>--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                            <div class="cont-col2">--}}
{{--                                                                                <div class="desc">--}}
{{--                                                                                    New order received. Please take care of it.--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                    <div class="col2">--}}
{{--                                                                        <div class="date">--}}
{{--                                                                            22 hours--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                </li>--}}
{{--                                                                <li>--}}
{{--                                                                    <div class="col1">--}}
{{--                                                                        <div class="cont">--}}
{{--                                                                            <div class="cont-col1">--}}
{{--                                                                                <div class="label label-default">--}}
{{--                                                                                    <i class="fa fa-bullhorn"></i>--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                            <div class="cont-col2">--}}
{{--                                                                                <div class="desc">--}}
{{--                                                                                    New order received. Please take care of it.--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                    <div class="col2">--}}
{{--                                                                        <div class="date">--}}
{{--                                                                            21 hours--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                </li>--}}
{{--                                                                <li>--}}
{{--                                                                    <div class="col1">--}}
{{--                                                                        <div class="cont">--}}
{{--                                                                            <div class="cont-col1">--}}
{{--                                                                                <div class="label label-info">--}}
{{--                                                                                    <i class="fa fa-bullhorn"></i>--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                            <div class="cont-col2">--}}
{{--                                                                                <div class="desc">--}}
{{--                                                                                    New order received. Please take care of it.--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                    <div class="col2">--}}
{{--                                                                        <div class="date">--}}
{{--                                                                            22 hours--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                </li>--}}
{{--                                                                <li>--}}
{{--                                                                    <div class="col1">--}}
{{--                                                                        <div class="cont">--}}
{{--                                                                            <div class="cont-col1">--}}
{{--                                                                                <div class="label label-default">--}}
{{--                                                                                    <i class="fa fa-bullhorn"></i>--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                            <div class="cont-col2">--}}
{{--                                                                                <div class="desc">--}}
{{--                                                                                    New order received. Please take care of it.--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                    <div class="col2">--}}
{{--                                                                        <div class="date">--}}
{{--                                                                            21 hours--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                </li>--}}
{{--                                                                <li>--}}
{{--                                                                    <div class="col1">--}}
{{--                                                                        <div class="cont">--}}
{{--                                                                            <div class="cont-col1">--}}
{{--                                                                                <div class="label label-info">--}}
{{--                                                                                    <i class="fa fa-bullhorn"></i>--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                            <div class="cont-col2">--}}
{{--                                                                                <div class="desc">--}}
{{--                                                                                    New order received. Please take care of it.--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                    <div class="col2">--}}
{{--                                                                        <div class="date">--}}
{{--                                                                            22 hours--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                </li>--}}
{{--                                                                <li>--}}
{{--                                                                    <div class="col1">--}}
{{--                                                                        <div class="cont">--}}
{{--                                                                            <div class="cont-col1">--}}
{{--                                                                                <div class="label label-default">--}}
{{--                                                                                    <i class="fa fa-bullhorn"></i>--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                            <div class="cont-col2">--}}
{{--                                                                                <div class="desc">--}}
{{--                                                                                    New order received. Please take care of it.--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                    <div class="col2">--}}
{{--                                                                        <div class="date">--}}
{{--                                                                            21 hours--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                </li>--}}
{{--                                                                <li>--}}
{{--                                                                    <div class="col1">--}}
{{--                                                                        <div class="cont">--}}
{{--                                                                            <div class="cont-col1">--}}
{{--                                                                                <div class="label label-info">--}}
{{--                                                                                    <i class="fa fa-bullhorn"></i>--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                            <div class="cont-col2">--}}
{{--                                                                                <div class="desc">--}}
{{--                                                                                    New order received. Please take care of it.--}}
{{--                                                                                </div>--}}
{{--                                                                            </div>--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                    <div class="col2">--}}
{{--                                                                        <div class="date">--}}
{{--                                                                            22 hours--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                </li>--}}
{{--                                                            </ul>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                                <!--tab-pane-->--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <!--tab_1_2-->--}}
{{--                            <div class="tab-pane" id="tab_1_3">--}}
{{--                                <div class="row profile-account">--}}
{{--                                    <div class="col-md-3">--}}
{{--                                        <ul class="ver-inline-menu tabbable margin-bottom-10">--}}
{{--                                            <li class="active">--}}
{{--                                                <a data-toggle="tab" href="#tab_1-1">--}}
{{--                                                    <i class="fa fa-cog"></i> Personal info </a>--}}
{{--                                                <span class="after">--}}
{{--												</span>--}}
{{--                                            </li>--}}
{{--                                            <li>--}}
{{--                                                <a data-toggle="tab" href="#tab_2-2">--}}
{{--                                                    <i class="fa fa-picture-o"></i> Change Avatar </a>--}}
{{--                                            </li>--}}
{{--                                            <li>--}}
{{--                                                <a data-toggle="tab" href="#tab_3-3">--}}
{{--                                                    <i class="fa fa-lock"></i> Change Password </a>--}}
{{--                                            </li>--}}
{{--                                            <li>--}}
{{--                                                <a data-toggle="tab" href="#tab_4-4">--}}
{{--                                                    <i class="fa fa-eye"></i> Privacity Settings </a>--}}
{{--                                            </li>--}}
{{--                                        </ul>--}}
{{--                                    </div>--}}
{{--                                    <div class="col-md-9">--}}
{{--                                        <div class="tab-content">--}}
{{--                                            <div id="tab_1-1" class="tab-pane active">--}}
{{--                                                <form role="form" action="#">--}}
{{--                                                    <div class="form-group">--}}
{{--                                                        <label class="control-label">First Name</label>--}}
{{--                                                        <input type="text" placeholder="John" class="form-control"/>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="form-group">--}}
{{--                                                        <label class="control-label">Last Name</label>--}}
{{--                                                        <input type="text" placeholder="Doe" class="form-control"/>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="form-group">--}}
{{--                                                        <label class="control-label">Mobile Number</label>--}}
{{--                                                        <input type="text" placeholder="+1 646 580 DEMO (6284)" class="form-control"/>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="form-group">--}}
{{--                                                        <label class="control-label">Interests</label>--}}
{{--                                                        <input type="text" placeholder="Design, Web etc." class="form-control"/>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="form-group">--}}
{{--                                                        <label class="control-label">Occupation</label>--}}
{{--                                                        <input type="text" placeholder="Web Developer" class="form-control"/>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="form-group">--}}
{{--                                                        <label class="control-label">About</label>--}}
{{--                                                        <textarea class="form-control" rows="3" placeholder="We are KeenThemes!!!"></textarea>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="form-group">--}}
{{--                                                        <label class="control-label">Website Url</label>--}}
{{--                                                        <input type="text" placeholder="http://www.mywebsite.com" class="form-control"/>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="margiv-top-10">--}}
{{--                                                        <a href="#" class="btn green">--}}
{{--                                                            Save Changes </a>--}}
{{--                                                        <a href="#" class="btn default">--}}
{{--                                                            Cancel </a>--}}
{{--                                                    </div>--}}
{{--                                                </form>--}}
{{--                                            </div>--}}
{{--                                            <div id="tab_2-2" class="tab-pane">--}}
{{--                                                <p>--}}
{{--                                                    Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod.--}}
{{--                                                </p>--}}
{{--                                                <form action="#" role="form">--}}
{{--                                                    <div class="form-group">--}}
{{--                                                        <div class="fileinput fileinput-new" data-provides="fileinput">--}}
{{--                                                            <div class="fileinput-new thumbnail" style="width: 200px; height: 150px;">--}}
{{--                                                                <img src="http://www.placehold.it/200x150/EFEFEF/AAAAAA&amp;text=no+image" alt=""/>--}}
{{--                                                            </div>--}}
{{--                                                            <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px;">--}}
{{--                                                            </div>--}}
{{--                                                            <div>--}}
{{--																<span class="btn default btn-file">--}}
{{--																<span class="fileinput-new">--}}
{{--																Select image </span>--}}
{{--																<span class="fileinput-exists">--}}
{{--																Change </span>--}}
{{--																<input type="file" name="...">--}}
{{--																</span>--}}
{{--                                                                <a href="#" class="btn default fileinput-exists" data-dismiss="fileinput">--}}
{{--                                                                    Remove </a>--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                        <div class="clearfix margin-top-10">--}}
{{--															<span class="label label-danger">--}}
{{--															NOTE! </span>--}}
{{--                                                            <span>--}}
{{--															Attached image thumbnail is supported in Latest Firefox, Chrome, Opera, Safari and Internet Explorer 10 only </span>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="margin-top-10">--}}
{{--                                                        <a href="#" class="btn green">--}}
{{--                                                            Submit </a>--}}
{{--                                                        <a href="#" class="btn default">--}}
{{--                                                            Cancel </a>--}}
{{--                                                    </div>--}}
{{--                                                </form>--}}
{{--                                            </div>--}}
{{--                                            <div id="tab_3-3" class="tab-pane">--}}
{{--                                                <form action="#">--}}
{{--                                                    <div class="form-group">--}}
{{--                                                        <label class="control-label">Current Password</label>--}}
{{--                                                        <input type="password" class="form-control"/>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="form-group">--}}
{{--                                                        <label class="control-label">New Password</label>--}}
{{--                                                        <input type="password" class="form-control"/>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="form-group">--}}
{{--                                                        <label class="control-label">Re-type New Password</label>--}}
{{--                                                        <input type="password" class="form-control"/>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="margin-top-10">--}}
{{--                                                        <a href="#" class="btn green">--}}
{{--                                                            Change Password </a>--}}
{{--                                                        <a href="#" class="btn default">--}}
{{--                                                            Cancel </a>--}}
{{--                                                    </div>--}}
{{--                                                </form>--}}
{{--                                            </div>--}}
{{--                                            <div id="tab_4-4" class="tab-pane">--}}
{{--                                                <form action="#">--}}
{{--                                                    <table class="table table-bordered table-striped">--}}
{{--                                                        <tr>--}}
{{--                                                            <td>--}}
{{--                                                                Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus..--}}
{{--                                                            </td>--}}
{{--                                                            <td>--}}
{{--                                                                <label class="uniform-inline">--}}
{{--                                                                    <input type="radio" name="optionsRadios1" value="option1"/>--}}
{{--                                                                    Yes </label>--}}
{{--                                                                <label class="uniform-inline">--}}
{{--                                                                    <input type="radio" name="optionsRadios1" value="option2" checked/>--}}
{{--                                                                    No </label>--}}
{{--                                                            </td>--}}
{{--                                                        </tr>--}}
{{--                                                        <tr>--}}
{{--                                                            <td>--}}
{{--                                                                Enim eiusmod high life accusamus terry richardson ad squid wolf moon--}}
{{--                                                            </td>--}}
{{--                                                            <td>--}}
{{--                                                                <label class="uniform-inline">--}}
{{--                                                                    <input type="checkbox" value=""/> Yes </label>--}}
{{--                                                            </td>--}}
{{--                                                        </tr>--}}
{{--                                                        <tr>--}}
{{--                                                            <td>--}}
{{--                                                                Enim eiusmod high life accusamus terry richardson ad squid wolf moon--}}
{{--                                                            </td>--}}
{{--                                                            <td>--}}
{{--                                                                <label class="uniform-inline">--}}
{{--                                                                    <input type="checkbox" value=""/> Yes </label>--}}
{{--                                                            </td>--}}
{{--                                                        </tr>--}}
{{--                                                        <tr>--}}
{{--                                                            <td>--}}
{{--                                                                Enim eiusmod high life accusamus terry richardson ad squid wolf moon--}}
{{--                                                            </td>--}}
{{--                                                            <td>--}}
{{--                                                                <label class="uniform-inline">--}}
{{--                                                                    <input type="checkbox" value=""/> Yes </label>--}}
{{--                                                            </td>--}}
{{--                                                        </tr>--}}
{{--                                                    </table>--}}
{{--                                                    <!--end profile-settings-->--}}
{{--                                                    <div class="margin-top-10">--}}
{{--                                                        <a href="#" class="btn green">--}}
{{--                                                            Save Changes </a>--}}
{{--                                                        <a href="#" class="btn default">--}}
{{--                                                            Cancel </a>--}}
{{--                                                    </div>--}}
{{--                                                </form>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <!--end col-md-9-->--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <!--end tab-pane-->--}}
{{--                            <div class="tab-pane" id="tab_1_4">--}}
{{--                                <div class="row">--}}
{{--                                    <div class="col-md-12">--}}
{{--                                        <div class="add-portfolio">--}}
{{--											<span>--}}
{{--											502 Items sold this week </span>--}}
{{--                                            <a href="#" class="btn icn-only green">--}}
{{--                                                Add a new Project <i class="m-icon-swapright m-icon-white"></i>--}}
{{--                                            </a>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <!--end add-portfolio-->--}}
{{--                                <div class="row portfolio-block">--}}
{{--                                    <div class="col-md-5">--}}
{{--                                        <div class="portfolio-text">--}}
{{--                                            <img src="../../assets/admin/pages/media/profile/logo_metronic.jpg" alt=""/>--}}
{{--                                            <div class="portfolio-text-info">--}}
{{--                                                <h4>Metronic - Responsive Template</h4>--}}
{{--                                                <p>--}}
{{--                                                    Lorem ipsum dolor sit consectetuer adipiscing elit.--}}
{{--                                                </p>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <div class="col-md-5 portfolio-stat">--}}
{{--                                        <div class="portfolio-info">--}}
{{--                                            Today Sold <span>--}}
{{--											187 </span>--}}
{{--                                        </div>--}}
{{--                                        <div class="portfolio-info">--}}
{{--                                            Total Sold <span>--}}
{{--											1789 </span>--}}
{{--                                        </div>--}}
{{--                                        <div class="portfolio-info">--}}
{{--                                            Earns <span>--}}
{{--											$37.240 </span>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <div class="col-md-2">--}}
{{--                                        <div class="portfolio-btn">--}}
{{--                                            <a href="#" class="btn bigicn-only">--}}
{{--											<span>--}}
{{--											Manage </span>--}}
{{--                                            </a>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <!--end row-->--}}
{{--                                <div class="row portfolio-block">--}}
{{--                                    <div class="col-md-5 col-sm-12 portfolio-text">--}}
{{--                                        <img src="../../assets/admin/pages/media/profile/logo_azteca.jpg" alt=""/>--}}
{{--                                        <div class="portfolio-text-info">--}}
{{--                                            <h4>Metronic - Responsive Template</h4>--}}
{{--                                            <p>--}}
{{--                                                Lorem ipsum dolor sit consectetuer adipiscing elit.--}}
{{--                                            </p>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <div class="col-md-5 portfolio-stat">--}}
{{--                                        <div class="portfolio-info">--}}
{{--                                            Today Sold <span>--}}
{{--											24 </span>--}}
{{--                                        </div>--}}
{{--                                        <div class="portfolio-info">--}}
{{--                                            Total Sold <span>--}}
{{--											660 </span>--}}
{{--                                        </div>--}}
{{--                                        <div class="portfolio-info">--}}
{{--                                            Earns <span>--}}
{{--											$7.060 </span>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <div class="col-md-2 col-sm-12 portfolio-btn">--}}
{{--                                        <a href="#" class="btn bigicn-only">--}}
{{--										<span>--}}
{{--										Manage </span>--}}
{{--                                        </a>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <!--end row-->--}}
{{--                                <div class="row portfolio-block">--}}
{{--                                    <div class="col-md-5 portfolio-text">--}}
{{--                                        <img src="../../assets/admin/pages/media/profile/logo_conquer.jpg" alt=""/>--}}
{{--                                        <div class="portfolio-text-info">--}}
{{--                                            <h4>Metronic - Responsive Template</h4>--}}
{{--                                            <p>--}}
{{--                                                Lorem ipsum dolor sit consectetuer adipiscing elit.--}}
{{--                                            </p>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <div class="col-md-5 portfolio-stat">--}}
{{--                                        <div class="portfolio-info">--}}
{{--                                            Today Sold <span>--}}
{{--											24 </span>--}}
{{--                                        </div>--}}
{{--                                        <div class="portfolio-info">--}}
{{--                                            Total Sold <span>--}}
{{--											975 </span>--}}
{{--                                        </div>--}}
{{--                                        <div class="portfolio-info">--}}
{{--                                            Earns <span>--}}
{{--											$21.700 </span>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <div class="col-md-2 portfolio-btn">--}}
{{--                                        <a href="#" class="btn bigicn-only">--}}
{{--										<span>--}}
{{--										Manage </span>--}}
{{--                                        </a>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <!--end row-->--}}
{{--                            </div>--}}
{{--                            <!--end tab-pane-->--}}
{{--                            <div class="tab-pane" id="tab_1_6">--}}
{{--                                <div class="row">--}}
{{--                                    <div class="col-md-3">--}}
{{--                                        <ul class="ver-inline-menu tabbable margin-bottom-10">--}}
{{--                                            <li class="active">--}}
{{--                                                <a data-toggle="tab" href="#tab_1">--}}
{{--                                                    <i class="fa fa-briefcase"></i> General Questions </a>--}}
{{--                                                <span class="after">--}}
{{--												</span>--}}
{{--                                            </li>--}}
{{--                                            <li>--}}
{{--                                                <a data-toggle="tab" href="#tab_2">--}}
{{--                                                    <i class="fa fa-group"></i> Membership </a>--}}
{{--                                            </li>--}}
{{--                                            <li>--}}
{{--                                                <a data-toggle="tab" href="#tab_3">--}}
{{--                                                    <i class="fa fa-leaf"></i> Terms Of Service </a>--}}
{{--                                            </li>--}}
{{--                                            <li>--}}
{{--                                                <a data-toggle="tab" href="#tab_1">--}}
{{--                                                    <i class="fa fa-info-circle"></i> License Terms </a>--}}
{{--                                            </li>--}}
{{--                                            <li>--}}
{{--                                                <a data-toggle="tab" href="#tab_2">--}}
{{--                                                    <i class="fa fa-tint"></i> Payment Rules </a>--}}
{{--                                            </li>--}}
{{--                                            <li>--}}
{{--                                                <a data-toggle="tab" href="#tab_3">--}}
{{--                                                    <i class="fa fa-plus"></i> Other Questions </a>--}}
{{--                                            </li>--}}
{{--                                        </ul>--}}
{{--                                    </div>--}}
{{--                                    <div class="col-md-9">--}}
{{--                                        <div class="tab-content">--}}
{{--                                            <div id="tab_1" class="tab-pane active">--}}
{{--                                                <div id="accordion1" class="panel-group">--}}
{{--                                                    <div class="panel panel-default">--}}
{{--                                                        <div class="panel-heading">--}}
{{--                                                            <h4 class="panel-title">--}}
{{--                                                                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion1" href="#accordion1_1">--}}
{{--                                                                    1. Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry ? </a>--}}
{{--                                                            </h4>--}}
{{--                                                        </div>--}}
{{--                                                        <div id="accordion1_1" class="panel-collapse collapse in">--}}
{{--                                                            <div class="panel-body">--}}
{{--                                                                Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="panel panel-default">--}}
{{--                                                        <div class="panel-heading">--}}
{{--                                                            <h4 class="panel-title">--}}
{{--                                                                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion1" href="#accordion1_2">--}}
{{--                                                                    2. Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry ? </a>--}}
{{--                                                            </h4>--}}
{{--                                                        </div>--}}
{{--                                                        <div id="accordion1_2" class="panel-collapse collapse">--}}
{{--                                                            <div class="panel-body">--}}
{{--                                                                Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="panel panel-success">--}}
{{--                                                        <div class="panel-heading">--}}
{{--                                                            <h4 class="panel-title">--}}
{{--                                                                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion1" href="#accordion1_3">--}}
{{--                                                                    3. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor ? </a>--}}
{{--                                                            </h4>--}}
{{--                                                        </div>--}}
{{--                                                        <div id="accordion1_3" class="panel-collapse collapse">--}}
{{--                                                            <div class="panel-body">--}}
{{--                                                                Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="panel panel-warning">--}}
{{--                                                        <div class="panel-heading">--}}
{{--                                                            <h4 class="panel-title">--}}
{{--                                                                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion1" href="#accordion1_4">--}}
{{--                                                                    4. Wolf moon officia aute, non cupidatat skateboard dolor brunch ? </a>--}}
{{--                                                            </h4>--}}
{{--                                                        </div>--}}
{{--                                                        <div id="accordion1_4" class="panel-collapse collapse">--}}
{{--                                                            <div class="panel-body">--}}
{{--                                                                3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="panel panel-danger">--}}
{{--                                                        <div class="panel-heading">--}}
{{--                                                            <h4 class="panel-title">--}}
{{--                                                                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion1" href="#accordion1_5">--}}
{{--                                                                    5. Leggings occaecat craft beer farm-to-table, raw denim aesthetic ? </a>--}}
{{--                                                            </h4>--}}
{{--                                                        </div>--}}
{{--                                                        <div id="accordion1_5" class="panel-collapse collapse">--}}
{{--                                                            <div class="panel-body">--}}
{{--                                                                3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="panel panel-default">--}}
{{--                                                        <div class="panel-heading">--}}
{{--                                                            <h4 class="panel-title">--}}
{{--                                                                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion1" href="#accordion1_6">--}}
{{--                                                                    6. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth ? </a>--}}
{{--                                                            </h4>--}}
{{--                                                        </div>--}}
{{--                                                        <div id="accordion1_6" class="panel-collapse collapse">--}}
{{--                                                            <div class="panel-body">--}}
{{--                                                                3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="panel panel-default">--}}
{{--                                                        <div class="panel-heading">--}}
{{--                                                            <h4 class="panel-title">--}}
{{--                                                                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion1" href="#accordion1_7">--}}
{{--                                                                    7. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft ? </a>--}}
{{--                                                            </h4>--}}
{{--                                                        </div>--}}
{{--                                                        <div id="accordion1_7" class="panel-collapse collapse">--}}
{{--                                                            <div class="panel-body">--}}
{{--                                                                3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                            <div id="tab_2" class="tab-pane">--}}
{{--                                                <div id="accordion2" class="panel-group">--}}
{{--                                                    <div class="panel panel-warning">--}}
{{--                                                        <div class="panel-heading">--}}
{{--                                                            <h4 class="panel-title">--}}
{{--                                                                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#accordion2_1">--}}
{{--                                                                    1. Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry ? </a>--}}
{{--                                                            </h4>--}}
{{--                                                        </div>--}}
{{--                                                        <div id="accordion2_1" class="panel-collapse collapse in">--}}
{{--                                                            <div class="panel-body">--}}
{{--                                                                <p>--}}
{{--                                                                    Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.--}}
{{--                                                                </p>--}}
{{--                                                                <p>--}}
{{--                                                                    Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.--}}
{{--                                                                </p>--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="panel panel-danger">--}}
{{--                                                        <div class="panel-heading">--}}
{{--                                                            <h4 class="panel-title">--}}
{{--                                                                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#accordion2_2">--}}
{{--                                                                    2. Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry ? </a>--}}
{{--                                                            </h4>--}}
{{--                                                        </div>--}}
{{--                                                        <div id="accordion2_2" class="panel-collapse collapse">--}}
{{--                                                            <div class="panel-body">--}}
{{--                                                                Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="panel panel-success">--}}
{{--                                                        <div class="panel-heading">--}}
{{--                                                            <h4 class="panel-title">--}}
{{--                                                                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#accordion2_3">--}}
{{--                                                                    3. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor ? </a>--}}
{{--                                                            </h4>--}}
{{--                                                        </div>--}}
{{--                                                        <div id="accordion2_3" class="panel-collapse collapse">--}}
{{--                                                            <div class="panel-body">--}}
{{--                                                                Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="panel panel-default">--}}
{{--                                                        <div class="panel-heading">--}}
{{--                                                            <h4 class="panel-title">--}}
{{--                                                                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#accordion2_4">--}}
{{--                                                                    4. Wolf moon officia aute, non cupidatat skateboard dolor brunch ? </a>--}}
{{--                                                            </h4>--}}
{{--                                                        </div>--}}
{{--                                                        <div id="accordion2_4" class="panel-collapse collapse">--}}
{{--                                                            <div class="panel-body">--}}
{{--                                                                3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="panel panel-default">--}}
{{--                                                        <div class="panel-heading">--}}
{{--                                                            <h4 class="panel-title">--}}
{{--                                                                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#accordion2_5">--}}
{{--                                                                    5. Leggings occaecat craft beer farm-to-table, raw denim aesthetic ? </a>--}}
{{--                                                            </h4>--}}
{{--                                                        </div>--}}
{{--                                                        <div id="accordion2_5" class="panel-collapse collapse">--}}
{{--                                                            <div class="panel-body">--}}
{{--                                                                3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="panel panel-default">--}}
{{--                                                        <div class="panel-heading">--}}
{{--                                                            <h4 class="panel-title">--}}
{{--                                                                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#accordion2_6">--}}
{{--                                                                    6. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth ? </a>--}}
{{--                                                            </h4>--}}
{{--                                                        </div>--}}
{{--                                                        <div id="accordion2_6" class="panel-collapse collapse">--}}
{{--                                                            <div class="panel-body">--}}
{{--                                                                3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="panel panel-default">--}}
{{--                                                        <div class="panel-heading">--}}
{{--                                                            <h4 class="panel-title">--}}
{{--                                                                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#accordion2_7">--}}
{{--                                                                    7. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft ? </a>--}}
{{--                                                            </h4>--}}
{{--                                                        </div>--}}
{{--                                                        <div id="accordion2_7" class="panel-collapse collapse">--}}
{{--                                                            <div class="panel-body">--}}
{{--                                                                3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                            <div id="tab_3" class="tab-pane">--}}
{{--                                                <div id="accordion3" class="panel-group">--}}
{{--                                                    <div class="panel panel-danger">--}}
{{--                                                        <div class="panel-heading">--}}
{{--                                                            <h4 class="panel-title">--}}
{{--                                                                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion3" href="#accordion3_1">--}}
{{--                                                                    1. Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry ? </a>--}}
{{--                                                            </h4>--}}
{{--                                                        </div>--}}
{{--                                                        <div id="accordion3_1" class="panel-collapse collapse in">--}}
{{--                                                            <div class="panel-body">--}}
{{--                                                                <p>--}}
{{--                                                                    Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et.--}}
{{--                                                                </p>--}}
{{--                                                                <p>--}}
{{--                                                                    Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et.--}}
{{--                                                                </p>--}}
{{--                                                                <p>--}}
{{--                                                                    Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.--}}
{{--                                                                </p>--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="panel panel-success">--}}
{{--                                                        <div class="panel-heading">--}}
{{--                                                            <h4 class="panel-title">--}}
{{--                                                                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion3" href="#accordion3_2">--}}
{{--                                                                    2. Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry ? </a>--}}
{{--                                                            </h4>--}}
{{--                                                        </div>--}}
{{--                                                        <div id="accordion3_2" class="panel-collapse collapse">--}}
{{--                                                            <div class="panel-body">--}}
{{--                                                                Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="panel panel-default">--}}
{{--                                                        <div class="panel-heading">--}}
{{--                                                            <h4 class="panel-title">--}}
{{--                                                                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion3" href="#accordion3_3">--}}
{{--                                                                    3. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor ? </a>--}}
{{--                                                            </h4>--}}
{{--                                                        </div>--}}
{{--                                                        <div id="accordion3_3" class="panel-collapse collapse">--}}
{{--                                                            <div class="panel-body">--}}
{{--                                                                Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="panel panel-default">--}}
{{--                                                        <div class="panel-heading">--}}
{{--                                                            <h4 class="panel-title">--}}
{{--                                                                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion3" href="#accordion3_4">--}}
{{--                                                                    4. Wolf moon officia aute, non cupidatat skateboard dolor brunch ? </a>--}}
{{--                                                            </h4>--}}
{{--                                                        </div>--}}
{{--                                                        <div id="accordion3_4" class="panel-collapse collapse">--}}
{{--                                                            <div class="panel-body">--}}
{{--                                                                3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="panel panel-default">--}}
{{--                                                        <div class="panel-heading">--}}
{{--                                                            <h4 class="panel-title">--}}
{{--                                                                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion3" href="#accordion3_5">--}}
{{--                                                                    5. Leggings occaecat craft beer farm-to-table, raw denim aesthetic ? </a>--}}
{{--                                                            </h4>--}}
{{--                                                        </div>--}}
{{--                                                        <div id="accordion3_5" class="panel-collapse collapse">--}}
{{--                                                            <div class="panel-body">--}}
{{--                                                                3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="panel panel-default">--}}
{{--                                                        <div class="panel-heading">--}}
{{--                                                            <h4 class="panel-title">--}}
{{--                                                                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion3" href="#accordion3_6">--}}
{{--                                                                    6. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth ? </a>--}}
{{--                                                            </h4>--}}
{{--                                                        </div>--}}
{{--                                                        <div id="accordion3_6" class="panel-collapse collapse">--}}
{{--                                                            <div class="panel-body">--}}
{{--                                                                3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="panel panel-default">--}}
{{--                                                        <div class="panel-heading">--}}
{{--                                                            <h4 class="panel-title">--}}
{{--                                                                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion3" href="#accordion3_7">--}}
{{--                                                                    7. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft ? </a>--}}
{{--                                                            </h4>--}}
{{--                                                        </div>--}}
{{--                                                        <div id="accordion3_7" class="panel-collapse collapse">--}}
{{--                                                            <div class="panel-body">--}}
{{--                                                                3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <!--end tab-pane-->--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <!--END TABS-->--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <!-- END PAGE CONTENT-->--}}
{{--        </div>--}}
{{--    </div>--}}
{{--    <!-- END CONTENT -->--}}

@stop

@section('scripts')
    <script>
        $('[data-toggle="tooltip"]').tooltip();
        $(document).on('click', '.confirm_delete', function (event) {
            var tr = $(this).parent().parent();
            event.preventDefault();
            url = $(this).attr('href');
            swal({
                title: "{{trans('admin.are_you_sure')}}",
                text: "",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "{{trans('admin.yes')}}",
                cancelButtonText: "{{trans('admin.no_cancel')}}",
                showLoaderOnConfirm: true,
//                ,closeOnConfirm: false,
//                closeOnCancel: false
                preConfirm: function (isConfirm) {
                    return new Promise(function (resolve, reject) {
                        setTimeout(function () {
                            if (isConfirm) {
                                $.ajax({
                                    url: url,
                                    type: 'GET',
                                    success: function () {
                                        swal("{{trans('completed')}}", "{{trans('admin.completed_successfully')}}", "success");

                                        tr.remove();
                                    },
                                    error: function (request, error) {
                                        swal("{{trans('operation_failed')}}", "{{trans('admin.something_wrong')}}", "error");
                                        console.error("Request: " + JSON.stringify(request));
                                        console.error("Error: " + JSON.stringify(error));
                                    }
                                });
                            } else {
                                swal("{{trans('admin.cancelled')}}", "{{trans('admin.everything_still')}}", "info");
                            }
//            });
                        }, 2000)
                    })
                },
                allowOutsideClick: false
            }).then(function (isConfirm) {

            });

//                    .then(function () {
//
        });



    </script>
@endsection



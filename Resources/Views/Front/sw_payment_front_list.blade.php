@extends('software::layouts.list')
@section('list_title') {{ @$title }} @endsection
@section('breadcrumb')
    <!--begin::Breadcrumb-->
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
        <!--begin::Item-->
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.dashboard') }}" class="text-muted text-hover-primary">{{ trans('sw.home')}}</a>
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
    <link href="{{asset('resources/assets/admin/pages/css/pricing-table-rtl.css')}}" rel="stylesheet" type="text/css" />
    <style>
        .form-section {
            margin: 30px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #e7ecf1;
        }
        .through {
            text-decoration: line-through;
        }

        /* Actions column styling */
        .actions-column {
            min-width: 200px !important;
            white-space: nowrap;
        }

        .actions-column .d-flex {
            gap: 0.25rem;
            flex-wrap: wrap;
        }

        .actions-column .btn {
            margin: 0;
            padding: 0.375rem;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 1200px) {
            .actions-column {
                min-width: 150px !important;
            }
        }

        @media (max-width: 992px) {
            .actions-column {
                min-width: 120px !important;
            }
        }
    </style>
@endsection
@section('page_body')
            @if(\Carbon\Carbon::parse($mainSettings->sw_end_date)->toDateString() <= \Carbon\Carbon::now()->toDateString())
                <div class="Metronic-alerts alert alert-danger fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button><i class="fa-lg fa fa-warning"></i>  {!! trans('sw.subscription_expire_date_msg', ['date'=> \Carbon\Carbon::parse($mainSettings->sw_end_date)->toDateString(), 'url' => route('sw.listSwPayment')]) !!}</div>
            @else
                <div class="Metronic-alerts alert alert-success fade in"><i class="fa-lg fa fa-calendar"></i>  <b>{{ trans('sw.subscription_expire_date')}}:</b> {{\Carbon\Carbon::parse($mainSettings->sw_end_date)->toDateString()}}</div>
            @endif


            <!-- BEGIN PAGE CONTENT-->
            <div class="clearfix"><br></div>
            <h4 class="form-section"><i class="fa fa-list"></i> {{ trans('sw.price_table')}}</h4>
            <div class="row">
                <div class="col-md-12">
                    <!-- BEGIN INLINE NOTIFICATIONS PORTLET-->
                    <div class="portlet">

                        <div class="portlet-body">
                            <div class="row margin-bottom-40">
                                <!-- Pricing -->
                                @foreach($packages as $index => $package)
                                <div class="col-md-3">
                                    <div class="pricing hover-effect @if($index == 2) pricing-active @endif">
                                        <div class="pricing-head @if($index == 2) pricing-head-active @endif">
                                            <h3>{{$package['name_'.$lang]}}
                                            </h3>
                                            <h4>{{$package['price']}}<i>@if(@$package['before_discount_value']) <i style="text-decoration: line-through;color: lightgray;font-size: 30px;vertical-align: bottom;">{{@$package['before_discount_value']}}</i> @endif {{@$package['price_unit']}}</i>
                                                <span style="font-size: 12px">+ {{ trans('sw.transaction_fees')}}</span>
                                            </h4>
                                        </div>
                                        <div class="pricing-footer">
                                            <p>
{{--                                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut non libero magna psum olor .--}}
                                            </p>

                                            @if(@$package['paypal_url'])
                                            <a href="{{$package['paypal_url']}}" class="btn yellow-crusta">
                                                {{ trans('sw.subscribe')}} <i class="m-icon-swapright m-icon-white"></i>
                                            </a>
                                            @else
                                            <a href="https://gymmawy.com/api/client-software-create-payment/?ref={{sha1(time())}}&p={{$index+1}}&ct={{$getSettings->token}}" class="btn yellow-crusta">
                                                {{ trans('sw.subscribe')}} <i class="m-icon-swapright m-icon-white"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach

                                <!--//End Pricing -->
                            </div>
                        </div>
                    </div>
                    <!-- END INLINE NOTIFICATIONS PORTLET-->
                </div>
            </div>

            <div class="clearfix"><br></div>



            <!-- BEGIN PAGE CONTENT-->
            <div class="row">
                <div class="col-md-12">
                    <!-- BEGIN INLINE NOTIFICATIONS PORTLET-->
                    <div class="portlet">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-cogs"></i> {{ trans('sw.subscriptions')}}
                            </div>
                        </div>
                        <div class="Metronic-alerts alert alert-success fade in"><i class="fa-lg fa fa-list-alt"></i>  <b>{{ trans('sw.you_are_now_subscribed_to')}}:</b> {{ trans('sw.subscriptions_p_'.($my_package ?? 1))}}</div>

{{--                        <div class="portlet-body">--}}
{{--                            <div class="row margin-bottom-40">--}}
{{--                                <!-- Pricing -->--}}
{{--                                <div class="col-md-3">--}}
{{--                                    <div class="pricing hover-effect @if(@$my_package && ($my_package == 1)) pricing-active @endif">--}}
{{--                                        <div class="pricing-head">--}}
{{--                                            <h3>{{ trans('sw.subscriptions_p_1')}}--}}
{{--                                            </h3>--}}

{{--                                        </div>--}}
{{--                                        <ul class="pricing-content list-unstyled">--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_1')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_2')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_3')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_11')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_6')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_7')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_8')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_9')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_12')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_20')}}</li>--}}
{{--                                            <li  class="through">{{ trans('sw.subscriptions_f_4')}}</li>--}}
{{--                                            <li  class="through">{{ trans('sw.subscriptions_f_17')}}</li>--}}
{{--                                            <li  class="through">{{ trans('sw.subscriptions_f_5_a')}}</li>--}}
{{--                                            <li  class="through">{{ trans('sw.subscriptions_f_22')}}</li>--}}
{{--                                            <li  class="through">{{ trans('sw.subscriptions_f_23')}}</li>--}}
{{--                                            <li  class="through">{{ trans('sw.subscriptions_f_16')}}</li>--}}
{{--                                            <li  class="through">{{ trans('sw.subscriptions_f_19')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_13')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_14')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_15')}}</li>--}}
{{--                                            <li class="through">{{ trans('sw.subscriptions_f_10')}}</li>--}}
{{--                                            <li class="through">{{ trans('sw.subscriptions_f_25')}}</li>--}}
{{--                                            <li class="through">{{ trans('sw.subscriptions_f_18')}}</li>--}}
{{--                                            <li class="through">{{ trans('sw.subscriptions_f_21')}}</li>--}}
{{--                                            <li class="through">{{ trans('sw.subscriptions_f_24')}}</li>--}}
{{--                                            <li class="through">{{ trans('sw.subscriptions_f_25')}}</li>--}}
{{--                                            <li class="through">{{ trans('sw.subscriptions_f_26')}}</li>--}}

{{--                                        </ul>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="col-md-3">--}}
{{--                                    <div class="pricing hover-effect @if(@$my_package && ($my_package == 2)) pricing-active @endif">--}}
{{--                                        <div class="pricing-head">--}}
{{--                                            <h3>{{ trans('sw.subscriptions_p_2')}}--}}
{{--                                            </h3>--}}

{{--                                        </div>--}}
{{--                                        <ul class="pricing-content list-unstyled">--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_1')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_2')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_3')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_11')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_6')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_7')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_8')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_9')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_13')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_14')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_15')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_12')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_20')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_4')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_17')}}</li>--}}
{{--                                            <li class="through">{{ trans('sw.subscriptions_f_5_b')}}</li>--}}
{{--                                            <li class="through">{{ trans('sw.subscriptions_f_22')}}</li>--}}
{{--                                            <li class="through">{{ trans('sw.subscriptions_f_23')}}</li>--}}
{{--                                            <li class="through">{{ trans('sw.subscriptions_f_16')}}</li>--}}
{{--                                            <li class="through">{{ trans('sw.subscriptions_f_19')}}</li>--}}
{{--                                            <li class="through">{{ trans('sw.subscriptions_f_10')}}</li>--}}
{{--                                            <li class="through">{{ trans('sw.subscriptions_f_25')}}</li>--}}
{{--                                            <li class="through">{{ trans('sw.subscriptions_f_18')}}</li>--}}
{{--                                            <li class="through">{{ trans('sw.subscriptions_f_21')}}</li>--}}
{{--                                            <li class="through">{{ trans('sw.subscriptions_f_24')}}</li>--}}
{{--                                            <li class="through">{{ trans('sw.subscriptions_f_25')}}</li>--}}
{{--                                            <li class="through">{{ trans('sw.subscriptions_f_26')}}</li>--}}
{{--                                        </ul>--}}

{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="col-md-3">--}}
{{--                                    <div class="pricing hover-effect @if(@$my_package && ($my_package == 3)) pricing-active @endif">--}}
{{--                                        <div class="pricing-head">--}}
{{--                                            <h3>{{ trans('sw.subscriptions_p_3')}}--}}
{{--                                            </h3>--}}

{{--                                        </div>--}}
{{--                                        <ul class="pricing-content list-unstyled">--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_1')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_2')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_3')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_11')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_6')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_7')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_8')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_9')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_13')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_14')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_15')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_12')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_20')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_4')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_17')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_5_b')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_22')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_23')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_16')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_19')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_10')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_25')}}</li>--}}
{{--                                            <li class="through">{{ trans('sw.subscriptions_f_18')}}</li>--}}
{{--                                            <li class="through">{{ trans('sw.subscriptions_f_21')}}</li>--}}
{{--                                            <li class="through">{{ trans('sw.subscriptions_f_24')}}</li>--}}
{{--                                            <li class="through">{{ trans('sw.subscriptions_f_25')}}</li>--}}
{{--                                            <li class="through">{{ trans('sw.subscriptions_f_26')}}</li>--}}
{{--                                        </ul>--}}

{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="col-md-3">--}}
{{--                                    <div class="pricing hover-effect @if(@$my_package && ($my_package == 4)) pricing-active @endif" >--}}
{{--                                        <div class="pricing-head">--}}
{{--                                            <h3>{{ trans('sw.subscriptions_p_4')}}--}}
{{--                                            </h3>--}}

{{--                                        </div>--}}
{{--                                        <ul class="pricing-content list-unstyled">--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_1')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_2')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_3')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_11')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_6')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_7')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_8')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_9')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_13')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_14')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_15')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_12')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_20')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_4')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_17')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_5_b')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_22')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_23')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_18')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_19')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_10')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_25')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_16')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_21')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_24')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_25')}}</li>--}}
{{--                                            <li>{{ trans('sw.subscriptions_f_26')}}</li>--}}
{{--                                        </ul>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <!--//End Pricing -->--}}
{{--                            </div>--}}
{{--                        </div>--}}
                    </div>
                    <!-- END INLINE NOTIFICATIONS PORTLET-->
                </div>
            </div>


            @if(count($orders) > 0)
            <!-- END PAGE CONTENT-->
            <div class="clearfix"><br></div>
            <h4 class="form-section"><i class="fa fa-list"></i> {{ trans('sw.list_invoices')}}</h4>

            @if(count($orders) > 0)
                <!--begin::Table-->
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_sw_payment_table">
                        <thead>
                            <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-50px text-nowrap">#</th>
                                <th class="min-w-100px text-nowrap">
                                    <i class="ki-outline ki-dollar fs-6 me-2"></i>{{ trans('sw.price')}}
                                </th>
                                <th class="min-w-100px text-nowrap">
                                    <i class="ki-outline ki-status fs-6 me-2"></i>{{ trans('sw.status')}}
                                </th>
                                <th class="min-w-100px text-nowrap">
                                    <i class="ki-outline ki-calendar fs-6 me-2"></i>{{ trans('sw.date')}}
                                </th>
                                <th class="text-end actions-column">
                                    <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('admin.actions')}}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                            @foreach($orders as $key=> $order)
                                @if(@$order['response']['success'] == 'true')
                                    <tr>
                                        <td class="pe-0">
                                            <span class="fw-bold">#{{ $order['id'] }}</span>
                                        </td>
                                        <td class="pe-0">
                                            <span class="fw-bold">{{ number_format($order['response']['amount_cents']/100) }} {{@$order['response']['currency']}}</span>
                                        </td>
                                        <td class="pe-0">
                                            @if(@$order['response']['success'] == 'true') 
                                                <span class="badge badge-success">{{ trans('sw.successful')}}</span> 
                                            @else 
                                                <span class="badge badge-danger">{{ trans('sw.declined')}}</span> 
                                            @endif
                                        </td>
                                        <td class="pe-0">
                                            <div class="d-flex flex-column">
                                                <div class="text-muted fw-bold d-flex align-items-center">
                                                    <i class="ki-outline ki-calendar fs-6 text-muted me-2"></i>
                                                    <span>{{\Carbon\Carbon::parse(@$order['created_at'])->format('Y-m-d') }}</span>
                                                </div>
                                                <div class="text-muted fs-7 d-flex align-items-center">
                                                    <i class="ki-outline ki-time fs-6 text-muted me-2"></i>
                                                    <span>{{\Carbon\Carbon::parse(@$order['created_at'])->format('h:i a') }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end actions-column">
                                            <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">
                                                <a href="{{route('sw.showPaymentOrder',$order['id'])}}"
                                                   class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm"
                                                   title="{{ trans('admin.view')}}">
                                                    <i class="ki-outline ki-eye fs-2"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!--end::Table-->
                {{--                <div class="col-lg-5 col-md-5 col-md-offset-5  text-center">--}}
                {{--                    {!! $orders->appends($search_query)->render()  !!}--}}
                {{--                </div>--}}
            @else
                <h4 class="col-lg-12 text-center">{{ trans('sw.no_record_found')}}</h4>
            @endif
            @endif
@endsection

@section('scripts')
    @parent


@endsection

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
    <link href="{{asset('resources/assets/new_front/pages/css/pricing-table-rtl.css')}}" rel="stylesheet" type="text/css" />
    <style>
        /* Modern Page Styles */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }

        /* Subscription Alert Styles */
        .subscription-alert {
            border-radius: 15px;
            border: none;
            padding: 20px 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            animation: fadeInUp 0.6s ease-out;
        }

        .subscription-alert.alert-danger {
            background: #fff3f3;
            border-left: 4px solid #f44336;
            color: #c62828;
        }

        .subscription-alert.alert-success {
            background: #f1f8f4;
            border-left: 4px solid #4caf50;
            color: #2e7d32;
        }

        .subscription-alert i {
            font-size: 24px;
            margin-right: 15px;
            animation: pulse 2s ease-in-out infinite;
        }

        /* Pricing Cards */
        .pricing-container {
            margin-bottom: 40px;
        }

        .pricing {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            animation: fadeInUp 0.6s ease-out;
            animation-fill-mode: both;
            position: relative;
        }

        .pricing:nth-child(1) { animation-delay: 0.1s; }
        .pricing:nth-child(2) { animation-delay: 0.2s; }
        .pricing:nth-child(3) { animation-delay: 0.3s; }

        .pricing.hover-effect:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 20px 50px rgba(255, 152, 0, 0.3);
        }

        .pricing-active {
            transform: scale(1.05);
            box-shadow: 0 15px 40px rgba(255, 152, 0, 0.4) !important;
            border: 3px solid #ff9800;
        }

        .pricing-active::before {
            content: '⭐ ACTIVE';
            position: absolute;
            top: 20px;
            right: -35px;
            background: #4caf50;
            color: white;
            padding: 5px 40px;
            transform: rotate(45deg);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            box-shadow: 0 2px 6px rgba(76, 175, 80, 0.3);
            z-index: 10;
        }

        .pricing-head {
            background: #f5f5f5;
            color: #333;
            padding: 40px 20px;
            text-align: center;
            position: relative;
            border-bottom: 2px solid #e0e0e0;
        }

        .pricing-head-active {
            background: #ff9800;
            color: white;
            border-bottom: 2px solid #f57c00;
        }

        .pricing-head h3 {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 15px 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            position: relative;
            z-index: 1;
        }

        .pricing-head h4 {
            font-size: 42px;
            font-weight: 700;
            margin: 0;
            position: relative;
            z-index: 1;
        }

        .pricing-head h4 i {
            font-size: 18px;
            opacity: 0.9;
        }

        .pricing-head span {
            display: block;
            font-size: 12px;
            margin-top: 10px;
            opacity: 0.9;
        }

        .pricing-content {
            padding: 30px 20px;
            min-height: 200px;
        }

        .pricing-content ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .pricing-content ul li {
            padding: 12px 0;
            border-bottom: 1px solid #f5f5f5;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            color: #424242;
            transition: all 0.3s ease;
        }

        .pricing-content ul li:last-child {
            border-bottom: none;
        }

        .pricing-content ul li:hover {
            padding-left: 5px;
            color: #ff9800;
        }

        .pricing-content ul li i {
            color: #4caf50;
            font-size: 18px;
            min-width: 20px;
        }

        .pricing-content ul li.feature-disabled {
            opacity: 0.4;
        }

        .pricing-content ul li.feature-disabled i {
            color: #e0e0e0;
        }

        .pricing-footer {
            padding: 30px 20px;
            text-align: center;
        }

        .pricing-footer .btn {
            width: 100%;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .pricing-footer .btn.yellow-crusta {
            background: #ff9800;
            color: white;
        }

        .pricing-footer .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 152, 0, 0.4);
        }

        /* Current Subscription Card */
        .current-subscription {
            background: #f1f8f4;
            border-left: 5px solid #4caf50;
            border-radius: 15px;
            padding: 20px 30px;
            margin: 30px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            animation: fadeInUp 0.6s ease-out;
            animation-delay: 0.5s;
            animation-fill-mode: both;
        }

        .current-subscription i {
            font-size: 24px;
            color: #4caf50;
            margin-right: 15px;
        }

        .current-subscription b {
            color: #2e7d32;
            font-size: 18px;
        }

        /* Section Titles */
        .form-section {
            margin: 40px 0 30px 0;
            padding: 20px 0;
            border-bottom: 3px solid transparent;
            border-image: linear-gradient(90deg, #ff9800 0%, transparent 100%);
            border-image-slice: 1;
            animation: fadeInUp 0.6s ease-out;
            position: relative;
        }

        .form-section i {
            color: #ff9800;
            margin-right: 10px;
            font-size: 24px;
        }

        /* Modern Table Styles */
        .table-responsive {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            animation: fadeInUp 0.6s ease-out;
            animation-delay: 0.6s;
            animation-fill-mode: both;
        }

        .table {
            margin: 0;
        }

        .table thead tr {
            background: #f5f5f5;
            border-radius: 10px;
        }

        .table thead th {
            padding: 20px 15px;
            font-weight: 700;
            color: #424242;
            border: none;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 12px;
        }

        .table thead th:first-child {
            border-radius: 10px 0 0 10px;
        }

        .table thead th:last-child {
            border-radius: 0 10px 10px 0;
        }

        .table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #f5f5f5;
        }

        .table tbody tr:hover {
            background: #fff9f0;
            transform: scale(1.01);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .table tbody td {
            padding: 20px 15px;
            vertical-align: middle;
            border: none;
        }

        /* Status Badges */
        .badge {
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .badge-success {
            background: #4caf50;
            color: white;
            box-shadow: 0 2px 4px rgba(76, 175, 80, 0.2);
        }

        .badge-danger {
            background: #f44336;
            color: white;
            box-shadow: 0 2px 4px rgba(244, 67, 54, 0.2);
        }

        /* Action Buttons */
        .actions-column {
            min-width: 100px !important;
            white-space: nowrap;
        }

        .btn-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-bg-light {
            background: #f5f5f5;
            color: #424242;
        }

        .btn-bg-light:hover {
            background: #ff9800;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(255, 152, 0, 0.3);
        }

        /* Date/Time Display */
        .date-time-display {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .date-time-display .date-row,
        .date-time-display .time-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .date-time-display i {
            color: #ff9800;
        }

        /* Price Display */
        .price-display {
            font-size: 18px;
            font-weight: 700;
            color: #4caf50;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .price-display::before {
            content: '';
            width: 5px;
            height: 20px;
            background: #4caf50;
            border-radius: 5px;
        }

        /* Portlet Enhancement */
        .portlet {
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            animation: fadeInUp 0.6s ease-out;
            margin-bottom: 30px;
        }

        .portlet-title {
            background: #f5f5f5;
            color: #333;
            padding: 20px 30px;
            border-bottom: 2px solid #e0e0e0;
        }

        .portlet-title .caption {
            font-size: 18px;
            font-weight: 700;
        }

        .portlet-title .caption i {
            margin-right: 10px;
            font-size: 22px;
        }

        .portlet-body {
            padding: 30px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .pricing {
                margin-bottom: 20px;
            }

            .pricing-head h3 {
                font-size: 22px;
            }

            .pricing-head h4 {
                font-size: 32px;
            }

            .table-responsive {
                padding: 10px;
            }

            .table thead th,
            .table tbody td {
                padding: 10px 8px;
                font-size: 12px;
            }

            .price-display {
                font-size: 14px;
            }
        }

        /* Loading Animation */
        .pricing-shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            animation: fadeInUp 0.6s ease-out;
        }

        .empty-state i {
            font-size: 64px;
            color: #e0e0e0;
            margin-bottom: 20px;
        }

        .empty-state h4 {
            color: #757575;
            font-weight: 600;
        }
    </style>
@endsection
@section('page_body')
            <!-- Subscription Status Alert -->
            @if(\Carbon\Carbon::parse($mainSettings->sw_end_date)->toDateString() <= \Carbon\Carbon::now()->toDateString())
                <div class="subscription-alert alert alert-danger">
                    <i class="fa fa-warning"></i>
                    {!! trans('sw.subscription_expire_date_msg', ['date'=> \Carbon\Carbon::parse($mainSettings->sw_end_date)->toDateString(), 'url' => route('sw.listSwPayment')]) !!}
                </div>
            @else
                <div class="subscription-alert alert alert-success">
                    <i class="fa fa-calendar"></i>
                    <b>{{ trans('sw.subscription_expire_date')}}:</b> {{\Carbon\Carbon::parse($mainSettings->sw_end_date)->toDateString()}}
                </div>
            @endif

            <!-- Pricing Table Section -->
            <div class="clearfix"><br></div>
            <h4 class="form-section">
                <i class="fa fa-shopping-cart"></i> {{ trans('sw.price_table')}}
            </h4>

            <div class="pricing-container">
                <div class="row margin-bottom-40">
                    @foreach($packages as $index => $package)
                    <div class="col-md-4 col-sm-6">
                        <div class="pricing hover-effect ">
                            <div class="pricing-head ">
                        <!-- <div class="pricing hover-effect @if(($index + 1) == $my_package) pricing-active @endif">
                            <div class="pricing-head @if(($index + 1) == $my_package) pricing-head-active @endif"> -->
                                <h3>{{$package['name_'.$lang]}}</h3>
                                <h4>
                                    {{$package['price']}}
                                    @if(@$package['before_discount_value'])
                                        <i style="text-decoration: line-through;color: rgba(255,255,255,0.6);font-size: 24px;">
                                            {{@$package['before_discount_value']}}
                                        </i>
                                    @endif
                                    <i>{{@$package['price_unit']}}</i>
                                </h4>
                                <span>+ {{ trans('sw.transaction_fees')}}</span>
                            </div>
                            <div class="pricing-content">
                                <ul>
                                    @php
                                        // Package tier for this card (1-based index)
                                        $packageTier = $my_package;

                                        // Define features for each package based on actual system capabilities
                                        $features = [
                                            ['icon' => 'fa-users', 'text' => trans('sw.member_management'), 'tier' => 1],
                                            ['icon' => 'fa-calendar-alt', 'text' => trans('sw.attendance_system'), 'tier' => 1],
                                            ['icon' => 'fa-credit-card', 'text' => trans('sw.subscription_management'), 'tier' => 1],
                                            ['icon' => 'fa-calculator', 'text' => trans('sw.moneybox_reports'), 'tier' => 1],
                                            ['icon' => 'fa-shopping-cart', 'text' => trans('sw.store_pos_system'), 'tier' => 1],
                                            ['icon' => 'fa-bell', 'text' => trans('sw.sms_notifications'), 'tier' => 1],
                                            ['icon' => 'fa-globe', 'text' => trans('sw.website_app'), 'tier' => 2],
                                            ['icon' => 'fa-dumbbell', 'text' => trans('sw.pt_system'), 'tier' => 2],
                                            ['icon' => 'fa-heartbeat', 'text' => trans('sw.training_plans'), 'tier' => 2],
                                            ['icon' => 'fa-mobile', 'text' => trans('sw.mobile_app'), 'tier' => 2],
                                            ['icon' => 'fa-lock', 'text' => trans('sw.gate_integration'), 'tier' => 3],
                                            ['icon' => 'fa-credit-card-alt', 'text' => trans('sw.payment_integration'), 'tier' => 3],
                                            ['icon' => 'fa-whatsapp', 'text' => trans('sw.whatsapp_integration'), 'tier' => 3],
                                            ['icon' => 'fa-star', 'text' => trans('sw.loyalty_system'), 'tier' => 3],
                                            ['icon' => 'fa-line-chart', 'text' => trans('sw.ai_reports'), 'tier' => 3],
                                        ];
                                    @endphp
                                    @foreach($features as $feature)
                                        @php
                                            // Feature is enabled if package tier is >= feature tier
                                            $featureEnabled = $packageTier >= $feature['tier'];
                                        @endphp
                                        <li class="{{ $featureEnabled ? '' : 'feature-disabled' }}">
                                            <i class="fa {{ $feature['icon'] }}"></i>
                                            <span>{{ $feature['text'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="pricing-footer">
                                @if(@$package['paymob_url'])
                                    <!-- Paymob Payment Button -->
                                    <a href="{{$package['paymob_url']}}" class="btn yellow-crusta mb-2" target="_blank">
                                        <i class="fa fa-credit-card"></i> {{ trans('sw.pay_with_paymob')}}
                                    </a>
                                @endif

                                @if(@$package['paypal_url'])
                                    <!-- PayPal Payment Button -->
                                    <a href="{{$package['paypal_url']}}" class="btn yellow-crusta mb-2">
                                        <i class="fa fa-paypal"></i> {{ trans('sw.pay_with_paypal')}}
                                    </a>
                                @endif

                                @if(!@$package['paymob_url'] && !@$package['paypal_url'])
                                    <!-- Default Payment Button -->
                                    <a href="https://gymmawy.com/api/client-software-create-payment/?ref={{sha1(time())}}&p={{$index+1}}&ct={{$getSettings->token}}" class="btn yellow-crusta">
                                        <i class="fa fa-shopping-cart"></i> {{ trans('sw.subscribe')}}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="clearfix"><br></div>

            <!-- Current Subscription -->
            <div class="portlet">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-star"></i> {{ trans('sw.subscriptions')}}
                    </div>
                </div>
                <div class="portlet-body">
                    <div class="current-subscription">
                        <i class="fa fa-check-circle"></i>
                        <b>{{ trans('sw.you_are_now_subscribed_to')}}:</b>
                        {{ trans('sw.subscriptions_p_'.($my_package ?? 1))}}
                    </div>
                </div>
            </div>

            <!-- Invoices List -->
            @if(count($orders) > 0)
            <div class="clearfix"><br></div>
            <h4 class="form-section">
                <i class="fa fa-file-text"></i> {{ trans('sw.list_invoices')}}
            </h4>

            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_sw_payment_table">
                    <thead>
                        <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-50px text-nowrap">
                                <i class="fa fa-hashtag"></i> #
                            </th>
                            <th class="min-w-150px text-nowrap">
                                <i class="fa fa-cube"></i> {{ trans('sw.package')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="fa fa-money"></i> {{ trans('sw.price')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="fa fa-info-circle"></i> {{ trans('sw.status')}}
                            </th>
                            <th class="min-w-100px text-nowrap">
                                <i class="fa fa-calendar"></i> {{ trans('sw.date')}}
                            </th>
                            <th class="text-end actions-column">
                                <i class="fa fa-cogs"></i> {{ trans('admin.actions')}}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @foreach($orders as $key=> $order)
                            <tr>
                                <td class="pe-0">
                                    <span class="fw-bold text-primary">#{{ $order['id'] }}</span>
                                </td>
                                <td class="pe-0">
                                    <span class="fw-semibold">{{ @$order['title'] ?? '-' }}</span>
                                </td>
                                <td class="pe-0">
                                    <div class="price-display">
                                        @if(isset($order['response']['amount_cents']))
                                            {{ number_format($order['response']['amount_cents']/100) }} {{@$order['response']['currency']}}
                                        @elseif(isset($order['price']))
                                            {{ number_format($order['price']) }} {{ trans('sw.egp') }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                </td>
                                <td class="pe-0">
                                    @if(@$order['response']['success'] == 'true' || @$order['status'] == 'paid')
                                        <span class="badge badge-success">
                                            <i class="fa fa-check"></i> {{ trans('sw.successful')}}
                                        </span>
                                    @else
                                        <span class="badge badge-danger">
                                            <i class="fa fa-times"></i> {{ trans('sw.declined')}}
                                        </span>
                                    @endif
                                </td>
                                <td class="pe-0">
                                    <div class="date-time-display">
                                        <div class="date-row">
                                            <i class="fa fa-calendar"></i>
                                            <span>
                                                @if(isset($order['response']['created_at']))
                                                    {{\Carbon\Carbon::parse(@$order['response']['created_at'])->format('Y-m-d') }}
                                                @elseif(isset($order['created_at']))
                                                    {{\Carbon\Carbon::parse(@$order['created_at'])->format('Y-m-d') }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>
                                        <div class="time-row">
                                            <i class="fa fa-clock-o"></i>
                                            <span>
                                                @if(isset($order['response']['created_at']))
                                                    {{\Carbon\Carbon::parse(@$order['response']['created_at'])->format('h:i a') }}
                                                @elseif(isset($order['created_at']))
                                                    {{\Carbon\Carbon::parse(@$order['created_at'])->format('h:i a') }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end actions-column">
                                    <div class="d-flex justify-content-end align-items-center gap-1">
                                        <a href="{{route('sw.showPaymentOrder',$order['id'])}}"
                                           class="btn btn-icon btn-bg-light"
                                           title="{{ trans('admin.view')}}">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <div class="empty-state">
                    <i class="fa fa-file-text-o"></i>
                    <h4>{{ trans('sw.no_record_found')}}</h4>
                </div>
            @endif
@endsection

@section('scripts')
    @parent

    <script>
        // Add smooth scroll animations
        document.addEventListener('DOMContentLoaded', function() {
            // Observe elements for scroll animations
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, {
                threshold: 0.1
            });

            // Observe all animated elements
            document.querySelectorAll('.pricing, .portlet, .table-responsive').forEach(el => {
                observer.observe(el);
            });

            // Add click animation to pricing cards
            document.querySelectorAll('.pricing').forEach(card => {
                card.addEventListener('click', function(e) {
                    if (!e.target.closest('.btn')) {
                        this.style.transform = 'scale(0.98)';
                        setTimeout(() => {
                            this.style.transform = '';
                        }, 200);
                    }
                });
            });

            // Animate table rows on load
            const rows = document.querySelectorAll('.table tbody tr');
            rows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateX(0)';
                }, 100 * index);
            });
        });
    </script>
@endsection

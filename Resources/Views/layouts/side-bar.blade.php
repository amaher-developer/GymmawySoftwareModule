@php
    $identifier = request()->segment(3);
@endphp
<style>
    .sub-menu span {
        font-size: 13px;
    }


    .app-sidebar__user {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-align: center;
        -ms-flex-align: center;
        align-items: center;
        color: #a8a8a8;
        width: 100%;
        display: inline-block;
        background-size: cover;
        background-position: left;
    }

    .app-sidebar__user .user-pro-body {
        display: block;
        padding: 15px 0;
    }

    .app-sidebar__user .user-pro-body img {
        display: block;
        margin: 0 auto 0px;
        border: 2px solid #c9d2e8;
        box-shadow: 0px 5px 5px 0px rgba(44, 44, 44, 0.2);
        padding: 3px;
        background: #fff;
    }

    .brround {
        border-radius: 50%;
    }

    .avatar-status {
        content: '';
        position: absolute;
        bottom: 0;
        left: 5px;
        width: 6px;
        height: 6px;
        background-color: #949eb7;
        box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.95);
        border-radius: 100%;
        bottom: 4px;
    }

    .profile-status {
        content: '' !important;
        position: absolute !important;
        bottom: 0 !important;
        left: 103px !important;
        width: 12px !important;
        height: 12px !important;
        background-color: #22c03c !important;
        box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.95) !important;
        border-radius: 100% !important;
        top: 73px;
        animation: pulse 2s infinite !important;
        animation-duration: .9s;
        animation-iteration-count: infinite;
        animation-timing-function: ease-out;
        border: 2px solid #fff;
    }

    .avatar {
        position: relative;
        width: 36px;
        height: 36px;
        border-radius: 100% !important;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 600;
        font-size: 16px;
        background-color: #0162e8;
        object-fit: cover;
    }

    .bg-green {
        background: #32c5d2 !important;
    }

    .app-sidebar__user .user-info {
        margin: 0 auto;
        text-align: center;
    }

    .user-info {
        margin-bottom: 10px !important;
    }

    .app-sidebar__user .user-info h4 {
        font-size: 15px;
    }

    .avatar-xl {
        width: 72px !important;
        height: 72px !important;
        font-size: 36px !important;
    }


    .page-sidebar-closed .page-sidebar .page-sidebar-menu.page-sidebar-menu-closed .app-sidebar__user {
        display: none;
    }


    element.style {
        white-space: nowrap;
        /* width: 50px; */
        overflow: hidden;
        text-overflow: ellipsis;
    }

    a.nav-link.nav-toggle {
        white-space: nowrap;
        /* width: 50px; */
        overflow: hidden;
        text-overflow: clip;
    }
</style>

{{-- <div class="app-sidebar__user "> --}}
{{--    <div class="dropdown user-pro-body"> --}}
{{--        <div class=""> --}}
{{--            <img alt="user-img" class="avatar avatar-xl brround mCS_img_loaded" --}}
{{--                 src="{{$swUser->image ? $swUser->image : asset('resources/assets/front/img/avatar_placeholder_white.png')}}" --}}
{{--                 style=" --}}
{{--    width: 60px; --}}
{{--    height: 60px; --}}
{{-- "> --}}
{{--            --}}{{--            <span class="avatar-status profile-status bg-green"></span> --}}
{{--        </div> --}}
{{--        <div class="user-info"> --}}
{{--            <h4 class="font-weight-semibold mt-3 mb-0" style=" --}}
{{--    margin-top: 1rem !important; --}}
{{--    font-weight: 500 !important; --}}
{{-- ">{{$swUser->name}}</h4> --}}
{{--            <span class="mb-0 text-muted" style=" --}}
{{--    color: #f2f5fb !important; --}}
{{--    font-size: 13px; --}}
{{-- ">{{@$swUser->title}}</span> --}}
{{--        </div> --}}
{{--    </div> --}}
{{-- </div> --}}









<div class="aside-menu flex-column-fluid">
    <!--begin::Aside Menu-->
    <div class="hover-scroll-overlay-y" id="kt_aside_menu_wrapper" data-kt-scroll="true"
        data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto"
        data-kt-scroll-dependencies="#kt_aside_logo, #kt_aside_footer" data-kt-scroll-wrappers="#kt_aside_menu"
        data-kt-scroll-offset="0">




        <div class="aside-toolbar flex-column-auto" id="kt_aside_toolbar">
            <!--begin::Aside user-->
            <!--begin::User-->
            <div class="aside-user d-flex align-items-sm-center justify-content-center py-5">
                <!--begin::Symbol-->
                <div class="symbol symbol-50px">
                    <img src="{{ @$swUser->image ? @$swUser->image : asset('resources/assets/front/img/avatar_placeholder_white.png') }}"
                        alt="">
                </div>
                <!--end::Symbol-->

                <!--begin::Wrapper-->
                <div class="aside-user-info flex-wrap ms-5">
                    <!--begin::Section-->
                    <div class="d-flex">
                        <!--begin::Info-->
                        <div class="flex-grow-1 me-2">
                            <!--begin::Username-->
                            <a href="#" class="text-white text-hover-primary fs-6 fw-bold">{{ @$swUser->name }}</a>
                            <!--end::Username-->

                            <!--begin::Description-->
                            <span class="text-gray-600 fw-semibold d-block fs-8 mb-1">{{ @$swUser->title }}</span>
                            <!--end::Description-->

                            <!--begin::Label-->
                            <div class="d-flex align-items-center text-success fs-9">
                                <span class="bullet bullet-dot bg-success me-1"></span> {{trans('sw.online')}}
                            </div>
                            <!--end::Label-->
                        </div>
                        <!--end::Info-->

                    </div>
                    <!--end::Section-->
                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::User-->

        </div>
        <!--end::Aside search-->



        <!--begin::Menu-->
        <div class="menu menu-column menu-title-gray-800 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500"
            id="#kt_aside_menu" data-kt-menu="true">

            <div class="menu-item">
                <!--begin:Menu link-->
                <a class="menu-link @if (Request::is(($lang ?? 'ar') . '')) active @endif" href="{{ url('/') }}">
                    <span class="menu-icon">
                        <i class="ki-outline ki-home fs-2"></i>
                    </span>
                    <span class="menu-title">{{ trans('sw.dashboard') }}</span>
                </a>
                <!--end:Menu link-->
            </div>

            @if ($swUser && (in_array('statistics', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
                <!--begin:Menu item-->
                <div data-kt-menu-trigger="click"
                    class="menu-item menu-accordion  @if (Request::is(($lang ?? 'ar') . '/statistics*')) show @endif">
                    <!--begin:Menu link-->
                    <span class="menu-link @if (Request::is(($lang ?? 'ar') . '/statistics*')) show @endif">
                        <span class="menu-icon">
                            <i class="ki-outline ki-chart-simple fs-2"></i>
                        </span>
                        <span class="menu-title">{{ trans('sw.statistics') }}</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <!--end:Menu link-->

                    <!--begin:Menu sub-->
                    <div class="menu-sub menu-sub-accordion">
                        <!--begin:Menu item-->
                        <div class="menu-item">
                            <!--begin:Menu link-->
                            <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/statistics') && !Request::is(($lang ?? 'ar') . '/statistics/*')) active @endif"
                                href="{{ route('sw.statistics') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">{{ trans('sw.general_statistics') }}</span>
                            </a>
                            <!--end:Menu link-->
                        </div>
                        <!--end:Menu item-->

                        <!--begin:Menu item-->
                        <div class="menu-item">
                            <!--begin:Menu link-->
                            <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/statistics/member-subscription')) active @endif"
                                href="{{ route('sw.memberSubscriptionStatistics') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">{{ trans('sw.member_subscription_statistics') }}</span>
                            </a>
                            <!--end:Menu link-->
                        </div>
                        <!--end:Menu item-->

                        <!--begin:Menu item-->
                        <div class="menu-item">
                            <!--begin:Menu link-->
                            <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/statistics/store')) active @endif"
                                href="{{ route('sw.storeStatistics') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">{{ trans('sw.store_statistics') }}</span>
                            </a>
                            <!--end:Menu link-->
                        </div>
                        <!--end:Menu item-->

                        <!--begin:Menu item-->
                        <div class="menu-item">
                            <!--begin:Menu link-->
                            <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/statistics/pt-subscription')) active @endif"
                                href="{{ route('sw.ptSubscriptionStatistics') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">{{ trans('sw.pt_subscription_statistics') }}</span>
                            </a>
                            <!--end:Menu link-->
                        </div>
                        <!--end:Menu item-->

                        <!--begin:Menu item-->
                        <div class="menu-item">
                            <!--begin:Menu link-->
                            <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/statistics/non-member')) active @endif"
                                href="{{ route('sw.nonMemberStatistics') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">{{ trans('sw.non_member_statistics') }}</span>
                            </a>
                            <!--end:Menu link-->
                        </div>
                        <!--end:Menu item-->
                    </div><!--end:Menu sub-->
                </div>
                <!--end:Menu item-->
            @endif

            <div class="menu-item">
                <!--begin:Menu link-->
                <a class="menu-link" href="{{ url('/') }}" id="side_notification">
                    <span class="menu-icon">
                        <i class="ki-outline ki-notification fs-2"></i>
                    </span>
                    <span class="menu-title">{{ trans('sw.notifications') }}</span>
                </a>
                <!--end:Menu link-->
            </div>

            @if ($swUser && (count(array_intersect(@(array) ($swUser->permissions ?? []), [
                        'listNonMember',
                        'listMember',
                        'listPotentialMember',
                        'listReservationMember',
                    ])) > 0 || $swUser->is_super_user))
                <!--begin:Menu item-->
                <div data-kt-menu-trigger="click"
                    class="menu-item menu-accordion  @if (Request::is(($lang ?? 'ar') . '/moneybox/order/subscription*') ||
                            Request::is(($lang ?? 'ar') . '/member*') ||
                            Request::is(($lang ?? 'ar') . '/non-member*') ||
                            Request::is(($lang ?? 'ar') . '/potential-member*') ||
                            Request::is(($lang ?? 'ar') . '/reservation-member*')) show @endif">
                    <!--begin:Menu link-->
                    <span class="menu-link  @if (Request::is(($lang ?? 'ar') . '/moneybox/order/subscription*') ||
                            Request::is(($lang ?? 'ar') . '/member*') ||
                            Request::is(($lang ?? 'ar') . '/non-member*') ||
                            Request::is(($lang ?? 'ar') . '/potential-member*') ||
                            Request::is(($lang ?? 'ar') . '/reservation-member*')) show @endif">
                        <span class="menu-icon">
                            <i class="ki-outline ki-people fs-2"></i>
                        </span>
                        <span class="menu-title">{{ trans('sw.clients') }}</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <!--end:Menu link-->

                    @if ($swUser && (
                        (@$mainSettings->active_subscription && in_array('listMember', (array) ($swUser->permissions ?? []))) ||
                            $swUser->is_super_user))
                        <!--begin:Menu sub-->
                        <div class="menu-sub menu-sub-accordion">
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <!--begin:Menu link-->
                                <a class="menu-link  @if (Request::is(($lang ?? 'ar') . '/member*')) active @endif"
                                    href="{{ route('sw.listMember') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">{{ trans('sw.subscribed_clients') }}</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                    @endif

                    @if ($swUser && (
                        (@$mainSettings->active_activity && in_array('listNonMember', (array) ($swUser->permissions ?? []))) ||
                            $swUser->is_super_user))
                        <!--begin:Menu item-->
                        <div class="menu-item">
                            <!--begin:Menu link-->
                            <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/non-member*')) active @endif"
                                href="{{ route('sw.listNonMember') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">{{ trans('sw.daily_clients') }}</span>
                            </a>
                            <!--end:Menu link-->
                        </div>
                        <!--end:Menu item-->
                    @endif

                    @if ($swUser && (in_array('listPotentialMember', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
                        <!--begin:Menu item-->
                        <div class="menu-item">
                            <!--begin:Menu link-->
                            <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/potential-member*')) active @endif "
                                href="{{ route('sw.listPotentialMember') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">{{ trans('sw.potential_clients') }}</span>
                            </a>
                            <!--end:Menu link-->
                        </div>
                        <!--end:Menu item-->
                    @endif

                    @if ($swUser && (
                        ($mainSettings->active_mobile || $mainSettings->active_website) &&
                            (in_array('listReservationMember', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user)))
                        <!--begin:Menu item-->
                        <div class="menu-item">
                            <!--begin:Menu link-->
                            <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/reservation-member*')) active @endif"
                                href="{{ route('sw.listReservationMember') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">{{ trans('sw.reservation_clients') }}</span>
                            </a>
                            <!--end:Menu link-->
                        </div>
                        <!--end:Menu item-->
                    @endif


                </div><!--end:Menu sub-->
        </div>
        <!--end:Menu item-->
        @endif

        @if ($mainSettings->active_store)
            <!--begin:Menu item-->
            <div data-kt-menu-trigger="click"
                class="menu-item menu-accordion  @if (Request::is(($lang ?? 'ar') . '/store*')) show @endif">
                <!--begin:Menu link-->
                <span class="menu-link @if (Request::is(($lang ?? 'ar') . '/store*')) show @endif">
                    <span class="menu-icon">
                        <i class="ki-outline ki-shop fs-2"></i>
                    </span>
                    <span class="menu-title">{{ trans('sw.store') }}</span>
                    <span class="menu-arrow"></span>
                </span>
                <!--end:Menu link-->

                @if ($swUser && (in_array('createStoreOrder', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
                    <!--begin:Menu sub-->
                    <div class="menu-sub menu-sub-accordion">
                        <!--begin:Menu item-->
                        <div class="menu-item">
                            <!--begin:Menu link-->
                            <a class="menu-link  @if (Request::is(($lang ?? 'ar') . '/store/order/create-pos*')) active @endif"
                                href="{{ route('sw.createStoreOrderPOS') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">{{ trans('sw.sell_products') }}</span>
                            </a>
                            <!--end:Menu link-->
                        </div>
                        <!--end:Menu item-->
                @endif

                @if ($swUser && (in_array('listStoreProducts', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link  @if (Request::is(($lang ?? 'ar') . '/store/product*')) active @endif"
                            href="{{ route('sw.listStoreProducts') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">{{ trans('sw.list_products') }}</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                @endif

                @if ($swUser && (in_array('listStoreOrders', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link  @if (Request::is(($lang ?? 'ar') . '/store/order*') && !Request::is(($lang ?? 'ar') . '/store/order/create-pos')) active @endif"
                            href="{{ route('sw.listStoreOrders') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">{{ trans('sw.sales_invoices') }}</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                @endif

                @if ($swUser && (in_array('listStoreOrderVendor', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/store/vendor/order*')) active @endif "
                            href="{{ route('sw.listStoreOrderVendor') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">{{ trans('sw.purchase_invoices') }}</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                @endif

                @if ($swUser && (in_array('listStoreCategory', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/store/category*')) active @endif "
                            href="{{ route('sw.listStoreCategory') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">{{ trans('sw.store_categories') }}</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                @endif


            </div><!--end:Menu sub-->
    </div>
    <!--end:Menu item-->
    @endif

    @if ($mainSettings->active_pt)
        <!--begin:Menu item-->
        <div data-kt-menu-trigger="click"
            class="menu-item menu-accordion  @if (Request::is(($lang ?? 'ar') . '/pt*')) show @endif">
            <!--begin:Menu link-->
            <span class="menu-link @if (Request::is(($lang ?? 'ar') . '/pt*')) show @endif">
                <span class="menu-icon">
                    <i class="ki-outline ki-security-user fs-2"></i>
                </span>
                <span class="menu-title">{{ trans('sw.pt') }}</span>
                <span class="menu-arrow"></span>
            </span>
            <!--end:Menu link-->

            @if ($swUser && (in_array('listPTSubscription', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
                <!--begin:Menu sub-->
                <div class="menu-sub menu-sub-accordion">
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/pt/subscription*')) active @endif"
                            href="{{ route('sw.listPTSubscription') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">{{ trans('sw.pt_subscriptions') }}</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
            @endif

            @if ($swUser && (in_array('listPTClass', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/pt/class*')) active @endif"
                        href="{{ route('sw.listPTClass') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ trans('sw.pt_classes') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($swUser && (in_array('listPTMember', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/pt/member*')) active @endif "
                        href="{{ route('sw.listPTMember') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ trans('sw.pt_members') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif


        </div><!--end:Menu sub-->
</div>
<!--end:Menu item-->
@endif

@if ($mainSettings->active_training)
    <!--begin:Menu item-->
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion  @if (Request::is(($lang ?? 'ar') . '/training*')) show @endif">
        <!--begin:Menu link-->
        <span class="menu-link @if (Request::is(($lang ?? 'ar') . '/training*')) show @endif">
            <span class="menu-icon">
                <i class="ki-outline ki-calendar-tick fs-2"></i>
            </span>
            <span class="menu-title">{{ trans('sw.training') }}</span>
            <span class="menu-arrow"></span>
        </span>
        <!--end:Menu link-->

        @if ($swUser && (in_array('listTrainingPlan', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
            <!--begin:Menu sub-->
            <div class="menu-sub menu-sub-accordion">
                <!--begin:Menu item - Training Plans-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/training/plan*')) active @endif"
                        href="{{ route('sw.listTrainingPlan') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ trans('sw.training_plans') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->

                <!--begin:Menu item - Member Training Management-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/training/member-log*')) active @endif"
                        href="{{ route('sw.listTrainingMemberLog') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ trans('sw.training_member_logs') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->

                <!--begin:Menu item - Medicines-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/training/medicine*')) active @endif"
                        href="{{ route('sw.listTrainingMedicine') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ trans('sw.training_medicines') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            </div>
            <!--end:Menu sub-->
        @endif
    </div>
    <!--end:Menu item-->
@endif

{{-- moved Activities, Subscriptions, Users under Settings submenu --}}

<div data-kt-menu-trigger="click" class="menu-item menu-accordion  @if (Request::is(($lang ?? 'ar') . '/moneybox*') && !Request::is(($lang ?? 'ar') . '/moneybox')) show @endif">
    <!--begin:Menu link-->
    <span class="menu-link @if (Request::is(($lang ?? 'ar') . '/moneybox*') && !Request::is(($lang ?? 'ar') . '/moneybox')) show @endif">
        <span class="menu-icon">
            <i class="ki-outline ki-wallet fs-2"></i>
        </span>
        <span class="menu-title">{{ trans('sw.moneybox') }}</span>
        <span class="menu-arrow"></span>
    </span>
    <!--end:Menu link-->

    @if ($swUser && (in_array('createMoneyBoxAdd', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
        <!--begin:Menu sub-->
        <div class="menu-sub menu-sub-accordion">
            <!--begin:Menu item-->
            <div class="menu-item">
                <!--begin:Menu link-->
                <a class="menu-link  @if (Request::is(($lang ?? 'ar') . '/moneybox/add')) active @endif"
                    href="{{ route('sw.createMoneyBoxAdd') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ trans('sw.add_to_money_box') }}</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->
    @endif

    @if ($swUser && (in_array('createMoneyBoxWithdraw', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
        <!--begin:Menu item-->
        <div class="menu-item">
            <!--begin:Menu link-->
            <a class="menu-link  @if (Request::is(($lang ?? 'ar') . '/moneybox/withdraw')) active @endif"
                href="{{ route('sw.createMoneyBoxWithdraw') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.withdraw_from_money_box') }}</span>
            </a>
            <!--end:Menu link-->
        </div>
        <!--end:Menu item-->
    @endif

    @if ($swUser && (in_array('createMoneyBoxWithdrawEarnings', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
        <!--begin:Menu item-->
        <div class="menu-item">
            <!--begin:Menu link-->
            <a class="menu-link  @if (Request::is(($lang ?? 'ar') . '/moneybox/withdraw-earnings')) active @endif"
                href="{{ route('sw.createMoneyBoxWithdrawEarnings') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.withdraw_earning') }}</span>
            </a>
            <!--end:Menu link-->
        </div>
        <!--end:Menu item-->
    @endif
   
</div><!--end:Menu sub-->
</div>

<div data-kt-menu-trigger="click" class="menu-item menu-accordion  @if (Request::is(($lang ?? 'ar') . '/user/log*') || Request::is(($lang ?? 'ar') . '/moneybox') || Request::is(($lang ?? 'ar') . '/moneybox/daily') || Request::is(($lang ?? 'ar') . '/ai/reports*') || Request::is(($lang ?? 'ar') . '/loyalty/transactions*')) show @endif">
    <!--begin:Menu link-->
    <span class="menu-link  @if (Request::is(($lang ?? 'ar') . '/user/log*') || Request::is(($lang ?? 'ar') . '/moneybox') || Request::is(($lang ?? 'ar') . '/moneybox/daily') || Request::is(($lang ?? 'ar') . '/ai/reports*') || Request::is(($lang ?? 'ar') . '/loyalty/transactions*')) show @endif">
        <span class="menu-icon">
            <i class="ki-outline ki-graph-up  fs-2"></i>
        </span>
        <span class="menu-title">{{ trans('sw.reports') }}</span>
        <span class="menu-arrow"></span>
    </span>
    <!--end:Menu link-->

    @if ($swUser && (in_array('listMoneyBox', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
        <!--begin:Menu sub-->
        <div class="menu-sub menu-sub-accordion">
            <!--begin:Menu item-->
            <div class="menu-item">
                <!--begin:Menu link-->
                <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/moneybox')) active @endif"
                    href="{{ route('sw.listMoneyBox') . '?from=' . date('m/d/Y') . '&' . 'to=' . date('m/d/Y') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ trans('sw.money_report') }}</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->
    @endif

    @if ($swUser && (in_array('reportMoneyboxTax', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
        <!--begin:Menu item-->
        <div class="menu-item">
            <!--begin:Menu link-->
            <a class="menu-link   @if (Request::is(($lang ?? 'ar') . '/user/log/moneybox-tax')) active @endif"
                href="{{ route('sw.reportMoneyboxTax') . '?from=' . date('m/d/Y') . '&' . 'to=' . date('m/d/Y') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.moneybox_tax') }}</span>
            </a>
            <!--end:Menu link-->
        </div>
        <!--end:Menu item-->
    @endif

    @if ($swUser && (in_array('reportRenewMemberList', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
        <!--begin:Menu item-->
        <div class="menu-item">
            <!--begin:Menu link-->
            <a class="menu-link  @if (Request::is(($lang ?? 'ar') . '/user/log/renew')) active @endif "
                href="{{ route('sw.reportRenewMemberList') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.memberships_renewal_report') }}</span>
            </a>
            <!--end:Menu link-->
        </div>
        <!--end:Menu item-->
    @endif

    @if ($swUser && (in_array('reportExpireMemberList', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
        <!--begin:Menu item-->
        <div class="menu-item">
            <!--begin:Menu link-->
            <a class="menu-link  @if (Request::is(($lang ?? 'ar') . '/user/log/expire')) active @endif"
                href="{{ route('sw.reportExpireMemberList') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.memberships_expire_report') }}</span>
            </a>
            <!--end:Menu link-->
        </div>
        <!--end:Menu item-->
    @endif

    @if ($swUser && (in_array('reportDetailMemberList', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
        <!--begin:Menu item-->
        <div class="menu-item">
            <!--begin:Menu link-->
            <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/user/log/detail')) active @endif"
                href="{{ route('sw.reportDetailMemberList') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.memberships_detail_report') }}</span>
            </a>
            <!--end:Menu link-->
        </div>
        <!--end:Menu item-->
    @endif

    @if ($swUser && (in_array('reportSubscriptionMemberList', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
        <!--begin:Menu item-->
        <div class="menu-item">
            <!--begin:Menu link-->
            <a class="menu-link  @if (Request::is(($lang ?? 'ar') . '/user/log/subscription')) active @endif"
                href="{{ route('sw.reportSubscriptionMemberList') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.report_subscriptions') }}</span>
            </a>
            <!--end:Menu link-->
        </div>
        <!--end:Menu item-->
    @endif

    @if ($swUser && (in_array('reportFreezeMemberList', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
        <!--begin:Menu item-->
        <div class="menu-item">
            <!--begin:Menu link-->
            <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/user/log/freeze-members')) active @endif"
                href="{{ route('sw.reportFreezeMemberList') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.freeze_members_report') }}</span>
            </a>
            <!--end:Menu link-->
        </div>
        <!--end:Menu item-->
    @endif

    @if ($swUser && (in_array('reportPTSubscriptionMemberList', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
        <!--begin:Menu item-->
        <div class="menu-item">
            <!--begin:Menu link-->
            <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/user/log/pt-subscription')) active @endif"
                href="{{ route('sw.reportPTSubscriptionMemberList') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.report_pt_subscriptions') }}</span>
            </a>
            <!--end:Menu link-->
        </div>
        <!--end:Menu item-->
    @endif

    @if ($swUser && (in_array('reportTodayMemberList', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
        <!--begin:Menu item-->
        <div class="menu-item">
            <!--begin:Menu link-->
            <a class="menu-link  @if (Request::is(($lang ?? 'ar') . '/user/log/today')) active @endif"
                href="{{ route('sw.reportTodayMemberList') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.client_attendees_today') }}</span>
            </a>
            <!--end:Menu link-->
        </div>
        <!--end:Menu item-->
    @endif

    @if ($swUser && (in_array('reportTodayPTMemberList', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
        <!--begin:Menu item-->
        <div class="menu-item">
            <!--begin:Menu link-->
            <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/user/log/pt-today')) active @endif"
                href="{{ route('sw.reportTodayPTMemberList') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.client_pt_attendees_today') }}</span>
            </a>
            <!--end:Menu link-->
        </div>
        <!--end:Menu item-->
    @endif

    @if ($swUser && (in_array('reportTodayNonMemberList', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
        <!--begin:Menu item-->
        <div class="menu-item">
            <!--begin:Menu link-->
            <a class="menu-link  @if (Request::is(($lang ?? 'ar') . '/user/log/non-member-today')) active @endif"
                href="{{ route('sw.reportTodayNonMemberList') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.non_client_attendees_today') }}</span>
            </a>
            <!--end:Menu link-->
        </div>
        <!--end:Menu item-->
    @endif

    @if ($swUser && (in_array('reportUserAttendeesList', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
        <!--begin:Menu item-->
        <div class="menu-item">
            <!--begin:Menu link-->
            <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/user/log/user-attendees')) active @endif"
                href="{{ route('sw.reportUserAttendeesList') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.attendees_report') }}</span>
            </a>
            <!--end:Menu link-->
        </div>
        <!--end:Menu item-->
    @endif

    @if ($swUser && (in_array('reportStoreList', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
        <!--begin:Menu item-->
        <div class="menu-item">
            <!--begin:Menu link-->
            <a class="menu-link  @if (Request::is(($lang ?? 'ar') . '/user/log/store')) active @endif "
                href="{{ route('sw.reportStoreList') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.store_report') }}</span>
            </a>
            <!--end:Menu link-->
        </div>
        <!--end:Menu item-->
    @endif
    @if ($swUser && config('sw_billing.zatca_enabled') && (in_array('reportZatcaInvoices', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
        <div class="menu-item">
            <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/user/log/zatca-invoices')) active @endif"
                href="{{ route('sw.reportZatcaInvoices') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.zatca_invoices_report') }}</span>
            </a>
        </div>
    @endif
    @if ($swUser && (in_array('reportOnlinePaymentTransactionList', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
        <!--begin:Menu item-->
        <div class="menu-item">
            <!--begin:Menu link-->
            <a class="menu-link  @if (Request::is(($lang ?? 'ar') . '/user/log/online-payment-transaction')) active @endif"
                href="{{ route('sw.reportOnlinePaymentTransactionList') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.online_transaction_report') }}</span>
            </a>
            <!--end:Menu link-->
        </div>
        <!--end:Menu item-->
    @endif
    @if ($swUser && (in_array('listMoneyBoxDaily', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
        <!--begin:Menu item-->
        <div class="menu-item">
            <!--begin:Menu link-->
            <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/moneybox/daily')) active @endif "
                href="{{ route('sw.listMoneyBoxDaily') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.money_daily_report') }}</span>
            </a>
            <!--end:Menu link-->
        </div>
        <!--end:Menu item-->
    @endif

    <!--begin:Menu item-->
    <div class="menu-item">
        <!--begin:Menu link-->
        <a class="menu-link  @if (Request::is(($lang ?? 'ar') . '/user/log')) active @endif" href="{{ route('sw.listUserLog') }}">
            <span class="menu-bullet">
                <span class="bullet bullet-dot"></span>
            </span>
            <span class="menu-title">{{ trans('sw.logs') }}</span>
        </a>
        <!--end:Menu link-->
    </div>
    <!--end:Menu item-->

    @if ($swUser && @$mainSettings->active_loyalty && ($swUser->is_super_user || in_array('listLoyaltyTransaction', (array) ($swUser->permissions ?? []))))
        <!--begin:Menu item-->
        <div class="menu-item">
            <!--begin:Menu link-->
            <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/loyalty/transactions*')) active @endif" href="{{ route('sw.loyalty_transactions.index') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.loyalty_transactions') }}</span>
            </a>
            <!--end:Menu link-->
        </div>
        <!--end:Menu item-->
    @endif

    @if ($swUser && ($swUser->is_super_user || count(array_intersect(@(array) ($swUser->permissions ?? []), ['aiReportsDashboard','aiReportsJobs','aiReportsInsights'])) > 0))
        <!--begin:Menu item-->
        <div class="menu-item">
            <!--begin:Menu link-->
            <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/ai/reports/dashboard')) active @endif" href="{{ route('ai.reports.dashboard') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.ai_dashboard') }}</span>
            </a>
            <!--end:Menu link-->
        </div>
        <!--end:Menu item-->

        <!--begin:Menu item-->
        @if ($swUser->is_super_user || in_array('aiReportsJobs', (array) ($swUser->permissions ?? [])))
        <div class="menu-item">
            <!--begin:Menu link-->
            <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/ai/reports/jobs')) active @endif" href="{{ route('ai.reports.jobs') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.ai_jobs') }}</span>
            </a>
            <!--end:Menu link-->
        </div>
        @endif
        <!--end:Menu item-->

        <!--begin:Menu item-->
        @if ($swUser->is_super_user || in_array('aiReportsInsights', (array) ($swUser->permissions ?? [])))
        <div class="menu-item">
            <!--begin:Menu link-->
            <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/ai/reports/insights')) active @endif" href="{{ route('ai.reports.insights') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.ai_insights') }}</span>
            </a>
            <!--end:Menu link-->
        </div>
        @endif
        <!--end:Menu item-->

        <!--begin:Menu item-->
        <!-- <div class="menu-item">
            <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/ai/reports/risk')) active @endif" href="{{ route('ai.reports.risk') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.ai_risk_assessment') }}</span>
            </a>
        </div>
        <!--end:Menu item-->
        <!--begin:Menu item-->
        <!-- <div class="menu-item">
            <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/ai/reports/performance')) active @endif" href="{{ route('ai.reports.performance') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.ai_performance_dashboard') }}</span>
            </a>
        </div> -->
        <!--end:Menu item-->

        <!--begin:Menu item-->
        <!-- <div class="menu-item">
            <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/ai/reports/watchlist')) active @endif" href="{{ route('ai.reports.watchlist') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.ai_watchlist') }}</span>
            </a>
        </div> -->
        <!--end:Menu item-->

        <!--begin:Menu item-->
        <!-- <div class="menu-item">
            <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/ai/reports/risk-drivers')) active @endif" href="{{ route('ai.reports.risk_drivers') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.ai_risk_drivers') }}</span>
            </a>
        </div> -->
        <!--end:Menu item-->

        <!--begin:Menu item-->
        <!-- <div class="menu-item">
            <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/ai/reports/freshness')) active @endif" href="{{ route('ai.reports.freshness') }}">
                <span class="menu-bullet">
                    <span class="bullet bullet-dot"></span>
                </span>
                <span class="menu-title">{{ trans('sw.ai_freshness') }}</span>
            </a>
        </div> -->
        <!-- end:Menu item -->
    @endif


</div><!--end:Menu sub-->
</div>

@if (@$mainSettings->active_website || @$mainSettings->active_mobile)

    <!--begin:Menu item-->
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion  @if (Request::is(($lang ?? 'ar') . '/banner*')) show @endif">
        <!--begin:Menu link-->
        <span class="menu-link  @if (Request::is(($lang ?? 'ar') . '/banner*')) show @endif">
            <span class="menu-icon">
                <i class="ki-outline ki-social-media  fs-2"></i>
            </span>
            <span class="menu-title">{{ trans('sw.media') }}</span>
            <span class="menu-arrow"></span>
        </span>
        <!--end:Menu link-->

        @if ($swUser && (in_array('listBanner', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
            <!--begin:Menu sub-->
            <div class="menu-sub menu-sub-accordion">
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/banner')) active @endif"
                        href="{{ route('sw.listBanner') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ trans('sw.banners') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
        @endif

        @if ($swUser && (in_array('listGallery', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
            <!--begin:Menu item-->
            <div class="menu-item">
                <!--begin:Menu link-->
                <a class="menu-link   @if (Request::is(($lang ?? 'ar') . '/banner/gallery')) active @endif"
                    href="{{ route('sw.listGallery') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ trans('sw.gallery') }}</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->
        @endif


    </div><!--end:Menu sub-->
    </div>
    <!--end:Menu item-->

@endif

@if ($swUser && $swUser->is_super_user &&
        ($mainSettings->active_sms ||
            $mainSettings->active_telegram ||
            $mainSettings->active_wa ||
            $mainSettings->active_notification ||
            $mainSettings->active_mobile))

    <!--begin:Menu item-->
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion  @if (Request::is(($lang ?? 'ar') . '/event-notification*') ||
                Request::is(($lang ?? 'ar') . '/sms*') ||
                Request::is(($lang ?? 'ar') . '/m-notification*') ||
                Request::is(($lang ?? 'ar') . '/telegram*') ||
                Request::is(($lang ?? 'ar') . '/wa*') ||
                Request::is(($lang ?? 'ar') . '/my-notification*')
                ) show @endif">
        <!--begin:Menu link-->
        <span class="menu-link  @if (Request::is(($lang ?? 'ar') . '/event-notification*') ||
                Request::is(($lang ?? 'ar') . '/sms*') ||
                Request::is(($lang ?? 'ar') . '/m-notification*') ||
                Request::is(($lang ?? 'ar') . '/telegram*') ||
                Request::is(($lang ?? 'ar') . '/wa*') ||
                Request::is(($lang ?? 'ar') . '/my-notification*')
                
                ) show @endif">
            <span class="menu-icon">
                <i class="ki-outline ki-message-notif fs-2"></i>
            </span>
            <span class="menu-title">{{ trans('sw.messages') }}</span>
            <span class="menu-arrow"></span>
        </span>
        <!--end:Menu link-->
        <div class="menu-sub menu-sub-accordion">

            @if ($mainSettings->active_sms)
                <!--begin:Menu sub-->
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link  @if (Request::is(($lang ?? 'ar') . '/sms*')) active @endif"
                        href="{{ route('sw.createSMS') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ trans('sw.sms_add') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($mainSettings->active_telegram)
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link  @if (Request::is(($lang ?? 'ar') . '/telegram*')) active @endif"
                        href="{{ route('sw.createTelegram') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ trans('sw.telegram_add') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif


            @if ($mainSettings->active_wa)
                @if (@env('WA_GATEWAY') == 'ULTRA')
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/wa-ultra*')) active @endif"
                            href="{{ route('sw.createWAUltra') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">{{ trans('sw.wa_add') }}</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                @else
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link  @if (Request::is(($lang ?? 'ar') . '/wa*')) active @endif"
                            href="{{ route('sw.createWA') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">{{ trans('sw.wa_add') }}</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                @endif
            @endif

            @if ($mainSettings->active_mobile)
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link  @if (Request::is(($lang ?? 'ar') . '/my-notification*')) active @endif"
                        href="{{ route('sw.createMyNotification') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ trans('sw.app_add') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($mainSettings->active_notification && !$mainSettings->active_mobile)
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link  @if (Request::is(($lang ?? 'ar') . '/m-notification*')) active @endif"
                        href="{{ route('sw.createNotification') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ trans('sw.g_application') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif


            @if ($swUser && $swUser->is_super_user)
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/event-notification*')) active @endif"
                        href="{{ route('sw.editEventNotification') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ trans('sw.message_settings') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif


        </div><!--end:Menu sub-->
    </div>
    <!--end:Menu item-->
@endif

@if ($swUser && $swUser->is_super_user)
    <!--begin:Menu item-->
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion  @if (Request::is(($lang ?? 'ar') . '/setting') || Request::is(($lang ?? 'ar') . '/setting*') || Request::is(($lang ?? 'ar') . '/block-member*')
         ||
                Request::is(($lang ?? 'ar') . '/subscription*') ||
                Request::is(($lang ?? 'ar') . '/activity*') ||
                Request::is(($lang ?? 'ar') . '/user*') ||
                Request::is(($lang ?? 'ar') . '/pt/trainer*') ||
                Request::is(($lang ?? 'ar') . '/loyalty/rules*') ||
                Request::is(($lang ?? 'ar') . '/loyalty/campaigns*') ) show @endif">
        <!--begin:Menu link-->
        <span class="menu-link  @if (Request::is(($lang ?? 'ar') . '/setting') || Request::is(($lang ?? 'ar') . '/setting*') || Request::is(($lang ?? 'ar') . '/block-member*')
         ||
                Request::is(($lang ?? 'ar') . '/subscription*') ||
                Request::is(($lang ?? 'ar') . '/activity*') ||
                Request::is(($lang ?? 'ar') . '/user*') || 
                Request::is(($lang ?? 'ar') . '/pt/trainer*') ||
                Request::is(($lang ?? 'ar') . '/loyalty/rules*') ||
                Request::is(($lang ?? 'ar') . '/loyalty/campaigns*')) show @endif">
            <span class="menu-icon">
                <i class="ki-outline ki-setting-2 fs-2"></i>
            </span>
            <span class="menu-title">{{ trans('sw.settings') }}</span>
            <span class="menu-arrow"></span>
        </span>
        <!--end:Menu link-->

        <!--begin:Menu sub-->
        <div class="menu-sub menu-sub-accordion">
            <!--begin:Menu item-->
            <div class="menu-item">
                <!--begin:Menu link-->
                <a class="menu-link  @if (Request::is(($lang ?? 'ar') . '/setting')) active @endif"
                    href="{{ route('sw.editSetting') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ trans('sw.settings') }}</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->



            {{-- Users (moved under Settings) --}}
            @if ($swUser && (in_array('listUser', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
                <div class="menu-item">
                    <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/user*') && !Request::is(($lang ?? 'ar') . '/user/log*') && !Request::is(($lang ?? 'ar') . '/user/log/renew') && !Request::is(($lang ?? 'ar') . '/user/log/today')) active @endif"
                        href="{{ route('sw.listUser') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ trans('sw.users') }}</span>
                    </a>
                </div>
            @endif
           
            @if ($mainSettings->active_pt && $swUser && (in_array('listPTTrainer', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
                <div class="menu-item">
                    <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/pt/trainer*')) active @endif"
                        href="{{ route('sw.listPTTrainer') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ trans('sw.pt_trainers') }}</span>
                    </a>
                </div>
            @endif
           

            {{-- Subscriptions (moved under Settings) --}}
            @if ($swUser && ((@$mainSettings->active_subscription && in_array('listSubscription', (array) ($swUser->permissions ?? []))) || $swUser->is_super_user))
                <div class="menu-item">
                    <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/subscription*')) active @endif"
                        href="{{ route('sw.listSubscription') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ trans('sw.memberships') }}</span>
                    </a>
                </div>
            @endif
 {{-- Activities (moved under Settings) --}}
            @if ($swUser && ((@$mainSettings->active_activity && in_array('listActivity', (array) ($swUser->permissions ?? []))) || $swUser->is_super_user))
                <div class="menu-item">
                    <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/activity*')) active @endif"
                        href="{{ route('sw.listActivity') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ trans('sw.activities') }}</span>
                    </a>
                </div>
            @endif
            
            @if ($swUser && (in_array('listPaymentType', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link  @if (Request::is(($lang ?? 'ar') . '/setting/payment-type*')) active @endif "
                        href="{{ route('sw.listPaymentType') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ trans('sw.payment_types') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif


            @if ($swUser && (in_array('listMoneyBoxType', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/setting/money-box-type*')) active @endif "
                        href="{{ route('sw.listMoneyBoxType') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ trans('sw.money_box_types') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if ($swUser && (in_array('listGroupDiscount', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/setting/group-discount*')) active @endif"
                        href="{{ route('sw.listGroupDiscount') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ trans('sw.group_discounts') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            <!-- @if ($swUser && (in_array('listStoreGroup', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
                <div class="menu-item">
                    <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/setting/store-group*')) active @endif "
                        href="{{ route('sw.listStoreGroup') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ trans('sw.store_groups') }}</span>
                    </a>
                </div>
            @endif -->


            @if ($swUser && (in_array('listSaleChannel', (array) ($swUser->permissions ?? [])) || $swUser->is_super_user))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/setting/sale-channel*')) active @endif"
                        href="{{ route('sw.listSaleChannel') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ trans('sw.sale_channels') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            <!--begin:Menu item-->
            <div class="menu-item">
                <!--begin:Menu link-->
                <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/block-member*')) active @endif"
                    href="{{ route('sw.listBlockMember') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ trans('sw.block_list') }}</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->

            @if (@$mainSettings->active_loyalty && ($swUser->is_super_user || in_array('listLoyaltyPointRule', (array) ($swUser->permissions ?? []))))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/loyalty/rules*')) active @endif"
                        href="{{ route('sw.loyalty_point_rules.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ trans('sw.loyalty_point_rules') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif

            @if (@$mainSettings->active_loyalty && ($swUser->is_super_user || in_array('listLoyaltyCampaign', (array) ($swUser->permissions ?? []))))
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/loyalty/campaigns*')) active @endif"
                        href="{{ route('sw.loyalty_campaigns.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ trans('sw.loyalty_campaigns') }}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            @endif


        </div><!--end:Menu sub-->
    </div>
    <!--end:Menu item-->
@endif
</div>
<!--end::Menu-->
</div>
</div>
























{{-- <li class="nav-item">
    <a href="{{route('web.templates')}}" target="_blank" class="nav-link nav-toggle">
        <i class="icon-star"></i>
        <span class="title">{{trans('sw.your_website')}}</span>
    </a>
</li> --}}
{{-- @if ($swUser->is_super_user) --}}
{{--    <li class="nav-item @if (Request::is(($lang ?? 'ar') . '/sms*')) active open @endif"> --}}
{{--        <a href="{{route('sw.createSMS')}}" class="nav-link nav-toggle"> --}}
{{--            <i class="icon-screen-smartphone"></i> --}}
{{--            <span class="title">{{trans('sw.sms_add')}}</span> --}}
{{--        </a> --}}
{{--    </li> --}}
{{-- @endif --}}
{{-- @if ($swUser->is_super_user) --}}
{{--    <li class="nav-item "> --}}
{{--        <a href="#" data-target="#modelBackup" data-toggle="modal" class="nav-link nav-toggle"> --}}
{{--            <i class="fa fa-database"></i> --}}
{{--            <span class="title">{{trans('sw.backup_database')}}</span> --}}
{{--        </a> --}}
{{--    </li> --}}
{{-- @endif --}}
{{-- @if ($swUser->is_super_user) --}}
{{--    <li class="nav-item "> --}}
{{--        <a class="nav-link nav-toggle" id="site_on_off"> --}}
{{--            <i class="icon-globe"></i> --}}
{{--            <span class="title">{{trans('sw.off_site')}}</span> --}}
{{--            {!! $mainSettings->under_maintenance ? '<span class="badge badge-danger side-badge">'.trans('sw.website_off').'</span>' : '<span class="badge badge-success side-badge">'.trans('sw.website_on').'</span>' !!} --}}
{{--        </a> --}}
{{--    </li> --}}
{{-- @endif --}}




@if (\Illuminate\Support\Facades\Auth::user() && \Illuminate\Support\Facades\Auth::user()->gym)
    {{-- <li aria-haspopup="true" class="nav-item @if (Request::is(($lang ?? 'ar') . '/user/gym/subscription*') || Request::is(($lang ?? 'ar') . '/user/gym/member*') || Request::is(($lang ?? 'ar') . '/user/gym/order*')) active open @endif"> --}}
    {{--    <a href="javascript:;" --}}
    {{--       class="nav-link nav-toggle @if (Request::is(($lang ?? 'ar') . '/user/gym/subscription*') || Request::is(($lang ?? 'ar') . '/user/gym/member*') || Request::is(($lang ?? 'ar') . '/user/gym/order*')) open @endif"> --}}
    {{--        <i class="icon-settings"></i> --}}
    {{--        <span class="title"> {{trans('global.gym_management')}}</span> --}}
    {{--        <span class="selected"></span> --}}
    {{--        <span class="arrow @if (Request::is(($lang ?? 'ar') . '/user/gym/subscription*') || Request::is(($lang ?? 'ar') . '/user/gym/member*') || Request::is(($lang ?? 'ar') . '/user/gym/order*')) open @endif"></span> --}}
    {{--    </a> --}}


    {{--    <ul class="sub-menu"> --}}
    {{--        <li class="nav-item  {{Request::is(($lang ?? 'ar').'/user/gym/subscription*') ? 'active open' : '' }}"> --}}
    {{--            <a href="" class="nav-link "> --}}
    {{--                <i class="icon-notebook "></i> --}}
    {{--                <span class="title">{{trans('global.subscriptions')}}</span> --}}
    {{--            </a> --}}
    {{--        </li> --}}
    {{--        <li class="nav-item  {{Request::is(($lang ?? 'ar').'/user/gym/member*') ? 'active open' : '' }}"> --}}
    {{--            <a href="" class="nav-link "> --}}
    {{--                <i class="icon-notebook "></i> --}}
    {{--                <span class="title">{{trans('global.members')}}</span> --}}
    {{--            </a> --}}
    {{--        </li> --}}
    {{--        <li class="nav-item  {{Request::is(($lang ?? 'ar').'/user/gym/order*') ? 'active open' : '' }}"> --}}
    {{--            <a href="" class="nav-link "> --}}
    {{--                <i class="icon-notebook "></i> --}}
    {{--                <span class="title">{{trans('global.orders')}}</span> --}}
    {{--            </a> --}}
    {{--        </li> --}}
    {{--    </ul> --}}
    {{-- </li> --}}
@endif
<!-- END MEGA MENU -->

@php
    $identifier = request()->segment(3);
    //$sidebarMetricsEnabled = config('app.debug');
    //$sidebarRenderStartedAt = $sidebarMetricsEnabled ? microtime(true) : null;

    $showSettingsMenu = $isSuperUser
        || isset($permissionsMap['listUser'])
        || isset($permissionsMap['listPTTrainer'])
        || isset($permissionsMap['listPaymentType'])
        || isset($permissionsMap['listMoneyBoxType'])
        || isset($permissionsMap['listGroupDiscount'])
        || isset($permissionsMap['listSaleChannel'])
        || isset($permissionsMap['listBlockMember'])
        || (isset($permissionsMap['listSubscription']) && @$mainSettings->active_subscription)
        || ((isset($permissionsMap['listActivity']) || isset($permissionsMap['listReservation'])) && (@$mainSettings->active_activity || @$mainSettings->active_activity_reservation))
        || (isset($permissionsMap['listReservation']) && @$mainSettings->active_activity_reservation)
        || ((isset($permissionsMap['listLoyaltyPointRule']) || isset($permissionsMap['listLoyaltyCampaign'])) && @$mainSettings->active_loyalty);
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
{{--                 src="{{$swUser->image ? $swUser->image : asset('resources/assets/new_front/img/avatar_placeholder_white.png')}}" --}}
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
                    <img src="{{ @$swUser->image ? @$swUser->image : asset('resources/assets/new_front/img/avatar_placeholder_white.png') }}"
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

            @php
                $localeKey = $lang ?? app()->getLocale() ?? 'en';
                $serviceMenus = collect(config('manassa.software_menus', []))
                    ->map(function ($section, $key) use ($localeKey) {
                        return [
                            'key' => $key,
                            'feature_flag' => $section['feature_flag'] ?? null,
                            'icon' => $section['icon'] ?? 'ki-outline ki-element-3',
                            'label' => $section['label'][$localeKey] ?? $section['label']['en'] ?? \Illuminate\Support\Str::headline($key),
                            'links' => collect($section['links'] ?? [])->map(function ($link) use ($localeKey) {
                                return [
                                    'route' => $link['route'] ?? null,
                                    'permissions' => $link['permissions'] ?? [],
                                    'label' => $link['label'][$localeKey] ?? $link['label']['en'] ?? $link['route'] ?? '',
                                ];
                            })->toArray(),
                        ];
                    });
            @endphp

            @if($serviceMenus->isNotEmpty())
                <div class="menu-content px-3 py-3">
                    <span class="menu-section text-muted text-uppercase fs-8">{{ __('Software Systems') }}</span>
                </div>
                @foreach($serviceMenus as $section)
                    @php
                        $flagEnabled = $section['feature_flag'] ? (bool) data_get($mainSettings, $section['feature_flag']) : true;
                        $visibleLinks = collect($section['links'])->filter(function ($link) use ($permissionsMap, $isSuperUser) {
                            if (! $link['route'] || ! Route::has($link['route'])) {
                                return false;
                            }

                            if ($isSuperUser || empty($link['permissions'])) {
                                return true;
                            }

                            foreach ($link['permissions'] as $permission) {
                                if (isset($permissionsMap[$permission])) {
                                    return true;
                                }
                            }

                            return false;
                        })->values();

                        $accordionActive = $visibleLinks->contains(function ($link) {
                            return request()->routeIs($link['route']) || request()->routeIs($link['route'].'.*');
                        });
                    @endphp

                    @if($swUser && $flagEnabled && $visibleLinks->isNotEmpty())
                        <div data-kt-menu-trigger="click"
                             class="menu-item menu-accordion {{ $accordionActive ? 'show' : '' }}">
                            <span class="menu-link {{ $accordionActive ? 'show' : '' }}">
                                <span class="menu-icon">
                                    <i class="{{ $section['icon'] }} fs-2"></i>
                                </span>
                                <span class="menu-title">{{ $section['label'] }}</span>
                                <span class="menu-arrow"></span>
                            </span>

                            <div class="menu-sub menu-sub-accordion">
                                @foreach($visibleLinks as $link)
                                    <div class="menu-item">
                                        <a class="menu-link {{ request()->routeIs($link['route']) || request()->routeIs($link['route'].'.*') ? 'active' : '' }}"
                                           href="{{ route($link['route']) }}">
                                            <span class="menu-bullet">
                                                <span class="bullet bullet-dot"></span>
                                            </span>
                                            <span class="menu-title">{{ $link['label'] }}</span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif

            @if ($swUser && (isset($permissionsMap['statistics']) || $isSuperUser))
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
                @if (@$mainSettings->active_subscription)
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
                @endif
                <!--end:Menu item-->

                <!--begin:Menu item-->
                @if (@$mainSettings->active_subscription)
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
                @endif
                <!--end:Menu item-->

                <!--begin:Menu item-->
                @if (@$mainSettings->active_store)
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
                @endif
                <!--end:Menu item-->

                <!--begin:Menu item-->
                @if (@$mainSettings->active_pt)
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
                @endif
                <!--end:Menu item-->

                <!--begin:Menu item-->
                @if (@$mainSettings->active_activity || @$mainSettings->active_activity_reservation)
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
                @endif
                        <!--end:Menu item-->
                    </div><!--end:Menu sub-->
                </div>
                <!--end:Menu item-->
            @endif

{{-- @if ($swUser && (isset($permissionsMap['reportUserNotificationsList']) || $isSuperUser))--}}
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
           {{-- @endif--}}

            @if ($swUser && (
                    isset($permissionsMap['listNonMember']) ||
                    isset($permissionsMap['listMember']) ||
                    isset($permissionsMap['listPotentialMember']) ||
                    isset($permissionsMap['listReservationMember']) ||
                    $isSuperUser
                ))
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

                    <!--begin:Menu sub-->
                    <div class="menu-sub menu-sub-accordion">
                        @if (@$mainSettings->active_subscription && ($swUser  && (isset($permissionsMap['listMember']) ||
                                $isSuperUser)))
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

                        @if ((@$mainSettings->active_activity || @$mainSettings->active_activity_reservation) && ($swUser && (isset($permissionsMap['listNonMember']) ||
                                $isSuperUser)))
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

                        @if ($swUser && (isset($permissionsMap['listPotentialMember']) || $isSuperUser))
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
                                (isset($permissionsMap['listReservationMember']) || $isSuperUser)))
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
                    </div>
                    <!--end:Menu sub-->
        </div>
        <!--end:Menu item-->
        @endif


    @if ($mainSettings->active_pt && $swUser && (
            isset($permissionsMap['listPTSubscription']) ||
            isset($permissionsMap['listPTClass']) ||
            isset($permissionsMap['listPTMember']) ||
            $isSuperUser
        ))
        <!--begin:Menu item-->
        <div data-kt-menu-trigger="click"
            class="menu-item menu-accordion  @if (Request::is(($lang ?? 'ar') . '/pt*') && !Request::is(($lang ?? 'ar') . '/pt/trainer*')) show @endif">
            <!--begin:Menu link-->
            <span class="menu-link @if (Request::is(($lang ?? 'ar') . '/pt*') && !Request::is(($lang ?? 'ar') . '/pt/trainer*')) show @endif">
                <span class="menu-icon">
                    <i class="ki-outline ki-security-user fs-2"></i>
                </span>
                <span class="menu-title">{{ trans('sw.pt') }}</span>
                <span class="menu-arrow"></span>
            </span>
            <!--end:Menu link-->

            <!--begin:Menu sub-->
            <div class="menu-sub menu-sub-accordion">
                @if ($swUser && (isset($permissionsMap['listPTSubscription']) || $isSuperUser))
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

                @if ($swUser && (isset($permissionsMap['listPTClass']) || $isSuperUser))
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

                @if ($swUser && (isset($permissionsMap['listPTMember']) || $isSuperUser))
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

                @if ($swUser && (isset($permissionsMap['listPTMember']) || isset($permissionsMap['listPTClass']) || $isSuperUser))
                    <div class="menu-item">
                        <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/pt/sessions*')) active @endif"
                           href="{{ route('sw.listPTSessions') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">{{ trans('sw.pt_sessions2') }}</span>
                        </a>
                    </div>
                @endif

                <!-- @if ($swUser && (isset($permissionsMap['listPTMember']) || $isSuperUser))
                    <div class="menu-item">
                        <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/home-pt-mini')) active @endif"
                           href="{{ route('sw.dashboardPTMini') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">{{ trans('sw.client_pt_attendees_today') }}</span>
                        </a>
                    </div>
                @endif

                @if ($swUser && (isset($permissionsMap['reportTodayPTMemberList']) || $isSuperUser))
                    <div class="menu-item">
                        <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/user/log/pt-today*')) active @endif"
                           href="{{ route('sw.reportTodayPTMemberList') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">{{ trans('sw.report_pt_attendance') }}</span>
                        </a>
                    </div>
                @endif -->

                <!-- @if ($swUser && (isset($permissionsMap['listPTTrainer']) || $isSuperUser))
                    <div class="menu-item">
                        <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/pt/trainer*')) active @endif"
                           href="{{ route('sw.listPTTrainer') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">{{ trans('sw.pt_trainer_payouts') }}</span>
                        </a>
                    </div>
                @endif -->
            </div>
            <!--end:Menu sub-->
</div>
<!--end:Menu item-->
@endif

{{-- moved Activities, Subscriptions, Users under Settings submenu --}}

@if ($swUser && (
        isset($permissionsMap['createMoneyBoxAdd']) ||
        isset($permissionsMap['createMoneyBoxWithdraw']) ||
        isset($permissionsMap['createMoneyBoxWithdrawEarnings']) ||
        $isSuperUser
    ))
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

    <!--begin:Menu sub-->
    <div class="menu-sub menu-sub-accordion">
        @if ($swUser && (isset($permissionsMap['createMoneyBoxAdd']) || $isSuperUser))
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

        @if ($swUser && (isset($permissionsMap['createMoneyBoxWithdraw']) || $isSuperUser))
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

        @if ($swUser && (isset($permissionsMap['createMoneyBoxWithdrawEarnings']) || $isSuperUser))
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
    </div>
    <!--end:Menu sub-->
</div>
@endif

@if ($swUser && (
        isset($permissionsMap['listMoneyBox']) ||
        isset($permissionsMap['reportMoneyboxTax']) ||
        isset($permissionsMap['reportZatcaInvoices']) ||
        isset($permissionsMap['reportRenewMemberList']) ||
        isset($permissionsMap['reportExpireMemberList']) ||
        isset($permissionsMap['reportDetailMemberList']) ||
        isset($permissionsMap['reportSubscriptionMemberList']) ||
        isset($permissionsMap['reportPTSubscriptionMemberList']) ||
        isset($permissionsMap['reportTodayMemberList']) ||
        isset($permissionsMap['reportTodayPTMemberList']) ||
        isset($permissionsMap['reportTodayNonMemberList']) ||
        isset($permissionsMap['reportUserAttendeesList']) ||
        isset($permissionsMap['reportStoreList']) ||
        isset($permissionsMap['reportOnlinePaymentTransactionList']) ||
        isset($permissionsMap['listMoneyBoxDaily']) ||
        isset($permissionsMap['listLoyaltyTransaction']) ||
        (@$mainSettings->active_ai && (
            isset($permissionsMap['aiReportsDashboard']) ||
            isset($permissionsMap['aiReportsJobs']) ||
            isset($permissionsMap['aiReportsInsights'])
        )) ||
        $isSuperUser
    ))
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

    <!--begin:Menu sub-->
    <div class="menu-sub menu-sub-accordion">
        @if ($swUser && (isset($permissionsMap['listMoneyBox']) || $isSuperUser))
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

    @if ($swUser && (isset($permissionsMap['reportMoneyboxTax']) || $isSuperUser))
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

    @if ($swUser && @$mainSettings->active_subscription && (isset($permissionsMap['reportRenewMemberList']) || $isSuperUser))
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

    @if ($swUser && @$mainSettings->active_subscription && (isset($permissionsMap['reportExpireMemberList']) || $isSuperUser))
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

    @if ($swUser && @$mainSettings->active_subscription && (isset($permissionsMap['reportDetailMemberList']) || $isSuperUser))
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

    @if ($swUser && @$mainSettings->active_subscription && (isset($permissionsMap['reportSubscriptionMemberList']) || $isSuperUser))
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

    @if ($swUser && @$mainSettings->active_subscription && (isset($permissionsMap['reportFreezeMemberList']) || $isSuperUser))
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

    @if ($swUser && @$mainSettings->active_pt && (isset($permissionsMap['reportPTSubscriptionMemberList']) || $isSuperUser))
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

    @if ($swUser && @$mainSettings->active_subscription && (isset($permissionsMap['reportTodayMemberList']) || $isSuperUser))
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

    @if ($swUser && @$mainSettings->active_pt && (isset($permissionsMap['reportTodayPTMemberList']) || $isSuperUser))
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

    @if ($swUser && (@$mainSettings->active_activity || @$mainSettings->active_activity_reservation) && (isset($permissionsMap['reportTodayNonMemberList']) || $isSuperUser))
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

    @if ($swUser && (isset($permissionsMap['reportUserAttendeesList']) || $isSuperUser))
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

    @if ($swUser && @$mainSettings->active_store && (isset($permissionsMap['reportStoreList']) || $isSuperUser))
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
    @if ($swUser && config('sw_billing.zatca_enabled') && (isset($permissionsMap['reportZatcaInvoices']) || $isSuperUser))
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
    @if ($swUser && (isset($permissionsMap['reportOnlinePaymentTransactionList']) || $isSuperUser))
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
    @if ($swUser && (isset($permissionsMap['listMoneyBoxDaily']) || $isSuperUser))
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

    @if ($swUser && @$mainSettings->active_loyalty && ($isSuperUser || isset($permissionsMap['listLoyaltyTransaction'])))
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

    @if (@$mainSettings->active_ai && $swUser && ($isSuperUser || 
            isset($permissionsMap['aiReportsDashboard']) ||
            isset($permissionsMap['aiReportsJobs']) ||
            isset($permissionsMap['aiReportsInsights'])
        ))
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
        @if ($isSuperUser || isset($permissionsMap['aiReportsJobs']))
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
        @if ($isSuperUser || isset($permissionsMap['aiReportsInsights']))
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
        <! end:Menu item-->
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
<!--end:Menu item-->
@endif

@if (@$mainSettings->active_mobile && $swUser && (
        isset($permissionsMap['listBanner']) ||
        isset($permissionsMap['listGallery']) ||
        $isSuperUser
    ))

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

        <!--begin:Menu sub-->
        <div class="menu-sub menu-sub-accordion">
            @if ($swUser && (isset($permissionsMap['listBanner']) || $isSuperUser))
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

            @if ($swUser && (isset($permissionsMap['listGallery']) || $isSuperUser))
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
        </div>
        <!--end:Menu sub-->
    </div>
    <!--end:Menu item-->

@endif

@if ($swUser && (
        $isSuperUser ||
        ($mainSettings->active_sms && isset($permissionsMap['createSMS'])) ||
        ($mainSettings->active_telegram && isset($permissionsMap['createTelegram'])) ||
        ($mainSettings->active_wa && (isset($permissionsMap['createWA']) || isset($permissionsMap['createWAUltra']))) ||
        ($mainSettings->active_notification && isset($permissionsMap['createNotification'])) ||
        ($mainSettings->active_mobile && isset($permissionsMap['createMyNotification'])) ||
        isset($permissionsMap['editEventNotification'])
    ))

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

            @if ($mainSettings->active_sms && ($swUser && (isset($permissionsMap['createSMS']) || $isSuperUser)))
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

            @if ($mainSettings->active_telegram && ($swUser && (isset($permissionsMap['createTelegram']) || $isSuperUser)))
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


            @if ($mainSettings->active_wa && ($swUser && (isset($permissionsMap['createWA']) || isset($permissionsMap['createWAUltra']) || $isSuperUser)))
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

            @if ($mainSettings->active_mobile && ($swUser && (isset($permissionsMap['createMyNotification']) || $isSuperUser)))
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

            @if ($mainSettings->active_notification && !$mainSettings->active_mobile && ($swUser && (isset($permissionsMap['createNotification']) || $isSuperUser)))
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


            @if ($swUser && ($isSuperUser || isset($permissionsMap['editEventNotification'])))
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

@if ($swUser && $showSettingsMenu)
    <!--begin:Menu item-->
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion  @if (Request::is(($lang ?? 'ar') . '/setting') || Request::is(($lang ?? 'ar') . '/setting*') || Request::is(($lang ?? 'ar') . '/block-member*')
         ||
                Request::is(($lang ?? 'ar') . '/subscription*') ||
                Request::is(($lang ?? 'ar') . '/activity*') ||
                Request::is(($lang ?? 'ar') . '/reservation*') ||
                Request::is(($lang ?? 'ar') . '/user*') ||
                Request::is(($lang ?? 'ar') . '/pt/trainer*') ||
                Request::is(($lang ?? 'ar') . '/loyalty/rules*') ||
                Request::is(($lang ?? 'ar') . '/loyalty/campaigns*') ) show @endif">
        <!--begin:Menu link-->
        <span class="menu-link  @if (Request::is(($lang ?? 'ar') . '/setting') || Request::is(($lang ?? 'ar') . '/setting*') || Request::is(($lang ?? 'ar') . '/block-member*')
         ||
                Request::is(($lang ?? 'ar') . '/subscription*') ||
                Request::is(($lang ?? 'ar') . '/activity*') ||
                Request::is(($lang ?? 'ar') . '/reservation*') ||
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
            @if ($swUser && $isSuperUser)
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
            @endif



            {{-- Users (moved under Settings) --}}
            @if ($swUser && (isset($permissionsMap['listUser']) || $isSuperUser))
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
           
            @if ($mainSettings->active_pt && $swUser && (isset($permissionsMap['listPTTrainer']) || $isSuperUser))
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
            @if (@$mainSettings->active_subscription && ($swUser && (isset($permissionsMap['listSubscription']) || $isSuperUser)))  
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
            @if ((@$mainSettings->active_activity || @$mainSettings->active_activity_reservation) && ($swUser && (isset($permissionsMap['listActivity']) || $isSuperUser)    ))
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
            
            
            @if ($swUser && @$mainSettings->active_activity_reservation && (isset($permissionsMap['listReservation']) || isset($permissionsMap['createReservation']) || isset($permissionsMap['editReservation']) || $isSuperUser))
                <div class="menu-item">
                    <a class="menu-link @if (Request::is(($lang ?? 'ar') . '/reservation*')) active @endif"
                        href="{{ route('sw.listReservation') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ trans('sw.reservations') }}</span>
                    </a>
                </div>
            @endif
            
            @if ($swUser && (isset($permissionsMap['listPaymentType']) || $isSuperUser))
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


            @if ($swUser && (isset($permissionsMap['listMoneyBoxType']) || $isSuperUser))
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

            @if ($swUser && (isset($permissionsMap['listGroupDiscount']) || $isSuperUser))
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

            <!-- @if ($swUser && (isset($permissionsMap['listStoreGroup']) || $isSuperUser))
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


            @if ($swUser && (isset($permissionsMap['listSaleChannel']) || $isSuperUser))
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
            @if ($swUser && (isset($permissionsMap['listBlockMember']) || $isSuperUser))
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
            @endif
            <!--end:Menu item-->

            @if (@$mainSettings->active_loyalty && ($isSuperUser || isset($permissionsMap['listLoyaltyPointRule'])))
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

            @if (@$mainSettings->active_loyalty && ($isSuperUser || isset($permissionsMap['listLoyaltyCampaign'])))
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


@php
/*
if($sidebarMetricsEnabled && $sidebarRenderStartedAt){
    \Log::debug('sidebar blade render timing', [
        'duration_ms' => round((microtime(true) - $sidebarRenderStartedAt) * 1000, 2),
        'route' => optional(request()->route())->getName(),
    ]);
}
    */
@endphp


























@extends('software::layouts.form')
@section('breadcrumb')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.dashboard') }}" class="text-muted text-hover-primary">{{ trans('sw.home') }}</a>
        </li>
        <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.editSetting') }}" class="text-muted text-hover-primary">{{ trans('sw.settings') }}</a>
        </li>
        <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
        <li class="breadcrumb-item text-gray-900">{{ $title }}</li>
    </ul>
@endsection
@section('list_title') {{ @$title }} @endsection
@section('form_title') {{ @$title }} @endsection

@section('page_body')
<div id="kt_content_container" class="container-xxl">
<div class="card card-flush">
    <div class="card-body">
        <form action="{{ route('sw.editIntegrations') }}" method="post" id="integrations_form">
            {{ csrf_field() }}

            <!--begin:::Tabs-->
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-transparent fs-4 fw-semibold mb-15">
                <li class="nav-item">
                    <a class="nav-link text-active-primary d-flex align-items-center pb-5 active" data-bs-toggle="tab" href="#tab-payments">
                        <i class="ki-outline ki-credit-cart fs-2 me-2"></i>{{ trans('sw.payment_gateways') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-active-primary d-flex align-items-center pb-5" data-bs-toggle="tab" href="#tab-notifications">
                        <i class="ki-outline ki-notification fs-2 me-2"></i>{{ trans('sw.notifications_integrations') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-active-primary d-flex align-items-center pb-5" data-bs-toggle="tab" href="#tab-appconfig">
                        <i class="ki-outline ki-setting-2 fs-2 me-2"></i>{{ trans('sw.app_config') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-active-primary d-flex align-items-center pb-5" data-bs-toggle="tab" href="#tab-ai">
                        <i class="ki-outline ki-abstract-26 fs-2 me-2"></i>{{ trans('sw.ai_settings') }}
                    </a>
                </li>
            </ul>
            <!--end:::Tabs-->

            <div class="tab-content">

                {{-- ===================== PAYMENT GATEWAYS TAB ===================== --}}
                <div class="tab-pane fade show active" id="tab-payments" role="tabpanel">
                    <div class="mb-10">
                        <h4 class="text-dark fw-bold mb-2">
                            <i class="ki-outline ki-credit-cart fs-2 me-2 text-primary"></i>{{ trans('sw.payment_gateways') }}
                        </h4>
                        <p class="text-muted fs-6">{{ trans('sw.payment_gateways_description') }}</p>
                    </div>

                    {{-- PAYMOB --}}
                    <div class="card card-bordered mb-8">
                        <div class="card-header min-h-50px">
                            <h3 class="card-title fw-bold text-dark">
                                <i class="ki-outline ki-wallet fs-2 me-2 text-info"></i>{{ trans('sw.paymob_settings') }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row mb-5">
                                <div class="col-md-6 fv-row">
                                    <label class="form-label">{{ trans('sw.paymob_api_key') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[paymob][api_key]"
                                           value="{{ $paymentsSettings['paymob']['api_key'] ?? '' }}"
                                           placeholder="API Key">
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="form-label">{{ trans('sw.paymob_hmac_secret') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[paymob][hmac_secret]"
                                           value="{{ $paymentsSettings['paymob']['hmac_secret'] ?? '' }}"
                                           placeholder="HMAC Secret">
                                </div>
                            </div>
                            <div class="row mb-5">
                                <div class="col-md-4 fv-row">
                                    <label class="form-label">{{ trans('sw.paymob_integration_id') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[paymob][integration_id]"
                                           value="{{ $paymentsSettings['paymob']['integration_id'] ?? '' }}"
                                           placeholder="Integration ID">
                                </div>
                                <div class="col-md-4 fv-row">
                                    <label class="form-label">{{ trans('sw.paymob_iframe_id') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[paymob][iframe_id]"
                                           value="{{ $paymentsSettings['paymob']['iframe_id'] ?? '' }}"
                                           placeholder="iFrame ID">
                                </div>
                                <div class="col-md-4 fv-row">
                                    <label class="form-label">{{ trans('sw.paymob_currency') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[paymob][currency]"
                                           value="{{ $paymentsSettings['paymob']['currency'] ?? 'SAR' }}"
                                           placeholder="SAR">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- PAYMOB INTENTION (Flash / Unified Checkout) --}}
                    <div class="card card-bordered mb-8">
                        <div class="card-header min-h-50px">
                            <h3 class="card-title fw-bold text-dark">
                                <i class="ki-outline ki-wallet fs-2 me-2 text-info"></i>{{ trans('sw.paymob_intention_settings') }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row mb-5">
                                <div class="col-md-6 fv-row">
                                    <label class="form-label">{{ trans('sw.paymob_intention_secret_key') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[paymob_intention][secret_key]"
                                           value="{{ $paymentsSettings['paymob_intention']['secret_key'] ?? '' }}"
                                           placeholder="Secret Key">
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="form-label">{{ trans('sw.paymob_intention_public_key') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[paymob_intention][public_key]"
                                           value="{{ $paymentsSettings['paymob_intention']['public_key'] ?? '' }}"
                                           placeholder="Public Key">
                                </div>
                            </div>
                            <div class="row mb-5">
                                <div class="col-md-4 fv-row">
                                    <label class="form-label">{{ trans('sw.paymob_integration_id') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[paymob_intention][integration_id]"
                                           value="{{ $paymentsSettings['paymob_intention']['integration_id'] ?? '' }}"
                                           placeholder="Integration ID">
                                </div>
                                <div class="col-md-4 fv-row">
                                    <label class="form-label">{{ trans('sw.paymob_hmac_secret') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[paymob_intention][hmac_secret]"
                                           value="{{ $paymentsSettings['paymob_intention']['hmac_secret'] ?? '' }}"
                                           placeholder="HMAC Secret">
                                </div>
                                <div class="col-md-4 fv-row">
                                    <label class="form-label">{{ trans('sw.paymob_currency') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[paymob_intention][currency]"
                                           value="{{ $paymentsSettings['paymob_intention']['currency'] ?? 'SAR' }}"
                                           placeholder="SAR">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 fv-row d-flex align-items-end">
                                    <div class="form-check form-switch mt-7">
                                        <input type="hidden" name="payments[paymob_intention][is_test]" value="0">
                                        <input class="form-check-input" type="checkbox"
                                               name="payments[paymob_intention][is_test]"
                                               value="1"
                                               {{ ($paymentsSettings['paymob_intention']['is_test'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold">{{ trans('sw.paymob_intention_is_test') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TABBY --}}
                    <div class="card card-bordered mb-8">
                        <div class="card-header min-h-50px">
                            <h3 class="card-title fw-bold text-dark">
                                <i class="ki-outline ki-wallet fs-2 me-2 text-success"></i>{{ trans('sw.tabby_settings') }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row mb-5">
                                <div class="col-md-6 fv-row">
                                    <label class="form-label">{{ trans('sw.tabby_public_key') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[tabby][public_key]"
                                           value="{{ $paymentsSettings['tabby']['public_key'] ?? '' }}"
                                           placeholder="pk_test_...">
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="form-label">{{ trans('sw.tabby_secret_key') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[tabby][secret_key]"
                                           value="{{ $paymentsSettings['tabby']['secret_key'] ?? '' }}"
                                           placeholder="sk_test_...">
                                </div>
                            </div>
                            <div class="row mb-5">
                                <div class="col-md-4 fv-row">
                                    <label class="form-label">{{ trans('sw.tabby_merchant_code') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[tabby][merchant_code]"
                                           value="{{ $paymentsSettings['tabby']['merchant_code'] ?? '' }}"
                                           placeholder="merchant_code">
                                </div>
                                <div class="col-md-4 fv-row">
                                    <label class="form-label">{{ trans('sw.tabby_currency') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[tabby][currency]"
                                           value="{{ $paymentsSettings['tabby']['currency'] ?? 'SAR' }}"
                                           placeholder="SAR">
                                </div>
                                <div class="col-md-4 fv-row">
                                    <label class="form-label">{{ trans('sw.tabby_city') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[tabby][city]"
                                           value="{{ $paymentsSettings['tabby']['city'] ?? '' }}"
                                           placeholder="Jazan">
                                </div>
                            </div>
                            <div class="row mb-5">
                                <div class="col-md-6 fv-row">
                                    <label class="form-label">{{ trans('sw.tabby_address') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[tabby][address]"
                                           value="{{ $paymentsSettings['tabby']['address'] ?? '' }}"
                                           placeholder="Address">
                                </div>
                                <div class="col-md-6 fv-row d-flex align-items-end">
                                    <div class="form-check form-switch mt-7">
                                        <input type="hidden" name="payments[tabby][is_test]" value="0">
                                        <input class="form-check-input" type="checkbox"
                                               name="payments[tabby][is_test]"
                                               value="1"
                                               {{ ($paymentsSettings['tabby']['is_test'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold">{{ trans('sw.tabby_is_test') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TAMARA --}}
                    <div class="card card-bordered mb-8">
                        <div class="card-header min-h-50px">
                            <h3 class="card-title fw-bold text-dark">
                                <i class="ki-outline ki-wallet fs-2 me-2 text-warning"></i>{{ trans('sw.tamara_settings') }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row mb-5">
                                <div class="col-md-6 fv-row">
                                    <label class="form-label">{{ trans('sw.tamara_api_url') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[tamara][api_url]"
                                           value="{{ $paymentsSettings['tamara']['api_url'] ?? '' }}"
                                           placeholder="https://api.tamara.co">
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="form-label">{{ trans('sw.tamara_token') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[tamara][token]"
                                           value="{{ $paymentsSettings['tamara']['token'] ?? '' }}"
                                           placeholder="Token">
                                </div>
                            </div>
                            <div class="row mb-5">
                                <div class="col-md-6 fv-row">
                                    <label class="form-label">{{ trans('sw.tamara_notification_token') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[tamara][notification_token]"
                                           value="{{ $paymentsSettings['tamara']['notification_token'] ?? '' }}"
                                           placeholder="Notification Token">
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="form-label">{{ trans('sw.tamara_merchant_url') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[tamara][merchant_url]"
                                           value="{{ $paymentsSettings['tamara']['merchant_url'] ?? '' }}"
                                           placeholder="https://yoursite.com">
                                </div>
                            </div>
                            <div class="row mb-5">
                                <div class="col-md-6 fv-row">
                                    <div class="form-check form-switch mt-2">
                                        <input type="hidden" name="payments[tamara][is_test]" value="0">
                                        <input class="form-check-input" type="checkbox"
                                               name="payments[tamara][is_test]"
                                               value="1"
                                               {{ ($paymentsSettings['tamara']['is_test'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold">{{ trans('sw.tamara_is_test') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- PAYTABS --}}
                    <div class="card card-bordered mb-8">
                        <div class="card-header min-h-50px">
                            <h3 class="card-title fw-bold text-dark">
                                <i class="ki-outline ki-wallet fs-2 me-2 text-primary"></i>{{ trans('sw.paytabs_settings') }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row mb-5">
                                <div class="col-md-6 fv-row">
                                    <label class="form-label">{{ trans('sw.paytabs_profile_id') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[paytabs][profile_id]"
                                           value="{{ $paymentsSettings['paytabs']['profile_id'] ?? '' }}"
                                           placeholder="150281">
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="form-label">{{ trans('sw.paytabs_server_key') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[paytabs][server_key]"
                                           value="{{ $paymentsSettings['paytabs']['server_key'] ?? '' }}"
                                           placeholder="SXXX-XXXX-XXXX">
                                </div>
                            </div>
                            <div class="row mb-5">
                                <div class="col-md-3 fv-row">
                                    <label class="form-label">{{ trans('sw.paytabs_client_key') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[paytabs][client_key]"
                                           value="{{ $paymentsSettings['paytabs']['client_key'] ?? '' }}"
                                           placeholder="CXXX-XXXX-XXXX">
                                </div>
                                <div class="col-md-3 fv-row">
                                    <label class="form-label">{{ trans('sw.paytabs_merchant_id') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[paytabs][merchant_id]"
                                           value="{{ $paymentsSettings['paytabs']['merchant_id'] ?? '' }}"
                                           placeholder="88091">
                                </div>
                                <div class="col-md-3 fv-row">
                                    <label class="form-label">{{ trans('sw.paytabs_region') }}</label>
                                    <select class="form-select form-select-solid" name="payments[paytabs][region]">
                                        @foreach(['SAU' => 'Saudi Arabia', 'ARE' => 'UAE', 'EGY' => 'Egypt', 'OMN' => 'Oman', 'JOR' => 'Jordan', 'KWT' => 'Kuwait', 'BHR' => 'Bahrain'] as $code => $name)
                                            <option value="{{ $code }}" {{ ($paymentsSettings['paytabs']['region'] ?? 'SAU') === $code ? 'selected' : '' }}>
                                                {{ $name }} ({{ $code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 fv-row">
                                    <label class="form-label">{{ trans('sw.paytabs_currency') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[paytabs][currency]"
                                           value="{{ $paymentsSettings['paytabs']['currency'] ?? 'SAR' }}"
                                           placeholder="SAR">
                                </div>
                            </div>
                            <div class="row mb-5">
                                <div class="col-md-12 fv-row">
                                    <label class="form-label">{{ trans('sw.paytabs_base_url') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[paytabs][base_url]"
                                           value="{{ $paymentsSettings['paytabs']['base_url'] ?? '' }}"
                                           placeholder="https://secure.paytabs.sa">
                                    <div class="form-text text-muted">{{ trans('sw.paytabs_base_url_hint') }}</div>
                                </div>
                            </div>
                            <div class="row mb-5">
                                <div class="col-md-6 fv-row">
                                    <label class="form-label">{{ trans('sw.paytabs_minimum_amount') }}</label>
                                    <input type="number" step="0.01" class="form-control form-control-solid"
                                           name="payments[paytabs][minimum_amount]"
                                           value="{{ $paymentsSettings['paytabs']['minimum_amount'] ?? '1' }}"
                                           placeholder="1">
                                </div>
                                <div class="col-md-6 fv-row d-flex align-items-end">
                                    <div class="form-check form-switch mt-7">
                                        <input type="hidden" name="payments[paytabs][is_test]" value="0">
                                        <input class="form-check-input" type="checkbox"
                                               name="payments[paytabs][is_test]"
                                               value="1"
                                               {{ ($paymentsSettings['paytabs']['is_test'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold">{{ trans('sw.paytabs_is_test') }}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-5">
                                <div class="col-md-4 fv-row">
                                    <label class="form-label">{{ trans('sw.paytabs_country') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[paytabs][country]"
                                           value="{{ $paymentsSettings['paytabs']['country'] ?? 'SA' }}"
                                           placeholder="SA">
                                </div>
                                <div class="col-md-4 fv-row">
                                    <label class="form-label">{{ trans('sw.paytabs_city') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[paytabs][city]"
                                           value="{{ $paymentsSettings['paytabs']['city'] ?? '' }}"
                                           placeholder="Riyadh">
                                </div>
                                <div class="col-md-4 fv-row">
                                    <label class="form-label">{{ trans('sw.paytabs_address') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="payments[paytabs][address]"
                                           value="{{ $paymentsSettings['paytabs']['address'] ?? '' }}"
                                           placeholder="N/A">
                                </div>
                            </div>
                            <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-4">
                                <i class="ki-outline ki-information-5 fs-2tx text-primary me-4"></i>
                                <div class="fs-7 text-gray-700">
                                    <strong>{{ trans('sw.paytabs_callback_url') }}:</strong>
                                    <code>{{ url('paytabs/callback') }}</code>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                {{-- end tab-payments --}}

                {{-- ===================== NOTIFICATIONS TAB ===================== --}}
                <div class="tab-pane fade" id="tab-notifications" role="tabpanel">
                    <div class="mb-10">
                        <h4 class="text-dark fw-bold mb-2">
                            <i class="ki-outline ki-notification fs-2 me-2 text-primary"></i>{{ trans('sw.notifications_integrations') }}
                        </h4>
                        <p class="text-muted fs-6">{{ trans('sw.notifications_integrations_description') }}</p>
                    </div>

                    {{-- TELEGRAM --}}
                    <div class="card card-bordered mb-8">
                        <div class="card-header min-h-50px">
                            <h3 class="card-title fw-bold text-dark">
                                <i class="ki-outline ki-send fs-2 me-2 text-info"></i>{{ trans('sw.telegram_settings') }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row mb-5">
                                <div class="col-md-4 fv-row">
                                    <label class="form-label">{{ trans('sw.telegram_bot_token') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="integrations_extra[telegram][bot_token]"
                                           value="{{ $integrationsSettings['telegram']['bot_token'] ?? '' }}"
                                           placeholder="123456:AAF...">
                                </div>
                                <div class="col-md-4 fv-row">
                                    <label class="form-label">{{ trans('sw.telegram_username') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="integrations_extra[telegram][username]"
                                           value="{{ $integrationsSettings['telegram']['username'] ?? '' }}"
                                           placeholder="@YourBot">
                                </div>
                                <div class="col-md-4 fv-row">
                                    <label class="form-label">{{ trans('sw.telegram_channel_id') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="integrations_extra[telegram][channel_id]"
                                           value="{{ $integrationsSettings['telegram']['channel_id'] ?? '' }}"
                                           placeholder="-1001234567890">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- WHATSAPP --}}
                    <div class="card card-bordered mb-8">
                        <div class="card-header min-h-50px">
                            <h3 class="card-title fw-bold text-dark">
                                <i class="ki-outline ki-message-text-2 fs-2 me-2 text-success"></i>{{ trans('sw.whatsapp_settings') }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row mb-5">
                                <div class="col-md-6 fv-row">
                                    <label class="form-label">{{ trans('sw.wa_user_token') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="integrations_extra[wa][user_token]"
                                           value="{{ $integrationsSettings['wa']['user_token'] ?? '' }}"
                                           placeholder="Token">
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="form-label">{{ trans('sw.wa_phone_id') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="integrations_extra[wa][phone_id]"
                                           value="{{ $integrationsSettings['wa']['phone_id'] ?? '' }}"
                                           placeholder="Phone ID">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- FIREBASE --}}
                    <div class="card card-bordered mb-8">
                        <div class="card-header min-h-50px">
                            <h3 class="card-title fw-bold text-dark">
                                <i class="ki-outline ki-technology-4 fs-2 me-2 text-warning"></i>{{ trans('sw.firebase_settings') }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row mb-5">
                                <div class="col-md-4 fv-row">
                                    <label class="form-label">{{ trans('sw.firebase_token') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="integrations_extra[firebase][token]"
                                           value="{{ $integrationsSettings['firebase']['token'] ?? '' }}"
                                           placeholder="ya29.a0...">
                                </div>
                                <div class="col-md-4 fv-row">
                                    <label class="form-label">{{ trans('sw.firebase_project_id') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="integrations_extra[firebase][project_id]"
                                           value="{{ $integrationsSettings['firebase']['project_id'] ?? '' }}"
                                           placeholder="my-project">
                                </div>
                                <div class="col-md-4 fv-row">
                                    <label class="form-label">{{ trans('sw.firebase_json_file') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="integrations_extra[firebase][json_file]"
                                           value="{{ $integrationsSettings['firebase']['json_file'] ?? '' }}"
                                           placeholder="firebase-config">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- end tab-notifications --}}

                {{-- ===================== APP CONFIG TAB ===================== --}}
                <div class="tab-pane fade" id="tab-appconfig" role="tabpanel">
                    <div class="mb-10">
                        <h4 class="text-dark fw-bold mb-2">
                            <i class="ki-outline ki-setting-2 fs-2 me-2 text-primary"></i>{{ trans('sw.app_config') }}
                        </h4>
                        <p class="text-muted fs-6">{{ trans('sw.app_config_description') }}</p>
                    </div>

                    {{-- GENERAL APP --}}
                    <div class="card card-bordered mb-8">
                        <div class="card-header min-h-50px">
                            <h3 class="card-title fw-bold text-dark">
                                <i class="ki-outline ki-globe fs-2 me-2 text-primary"></i>{{ trans('sw.app_general_settings') }}
                            </h3>
                        </div>
                        <div class="card-body">
                            @php
                                $countryCodeVal = $appConfig['country_code'] ?? env('APP_COUNTRY_CODE');
                                $currencyArVal = $appConfig['currency_ar'] ?? env('APP_CURRENCY_AR');
                                $currencyEnVal = $appConfig['currency_en'] ?? env('APP_CURRENCY_EN');
                                $timezoneVal = $appConfig['timezone'] ?? env('APP_TIMEZONE');
                                $timezoneDbVal = $appConfig['timezone_db'] ?? env('APP_TIMEZONE_DB');

                                $countryCodeOptions = [
                                    '966' => 'Saudi Arabia (+966)',
                                    '20' => 'Egypt (+20)',
                                    '971' => 'UAE (+971)',
                                    '965' => 'Kuwait (+965)',
                                    '974' => 'Qatar (+974)',
                                    '973' => 'Bahrain (+973)',
                                    '968' => 'Oman (+968)',
                                    '962' => 'Jordan (+962)',
                                    '961' => 'Lebanon (+961)',
                                    '964' => 'Iraq (+964)',
                                    '212' => 'Morocco (+212)',
                                    '213' => 'Algeria (+213)',
                                    '216' => 'Tunisia (+216)',
                                    '218' => 'Libya (+218)',
                                    '249' => 'Sudan (+249)',
                                ];

                                $currencyArOptions = ['ريال', 'ر.س', 'ج.م', 'جنيه', 'درهم', 'دينار', 'ليرة'];
                                $currencyEnOptions = ['SAR', 'rial', 'EGP', 'AED', 'KWD', 'QAR', 'BHD', 'OMR', 'JOD', 'USD', 'EUR'];

                                $timezoneOptions = [
                                    'Asia/Riyadh', 'Africa/Cairo', 'Asia/Dubai', 'Asia/Kuwait', 'Asia/Qatar',
                                    'Asia/Bahrain', 'Asia/Muscat', 'Asia/Amman', 'Asia/Beirut', 'Asia/Baghdad',
                                    'Africa/Casablanca', 'UTC',
                                ];

                                $timezoneDbOptions = ['+00:00', '+01:00', '+02:00', '+03:00', '+04:00', '+05:00'];
                            @endphp
                            <div class="row mb-5">
                                <div class="col-md-6 fv-row">
                                    <label class="form-label">{{ trans('sw.app_url') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="app_config[url]"
                                           value="{{ $appConfig['url'] ?? '' }}"
                                           placeholder="https://yoursite.com/">
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="form-label">{{ trans('sw.app_country_code') }}</label>
                                    <select class="form-select form-select-solid" name="app_config[country_code]">
                                        @foreach($countryCodeOptions as $value => $label)
                                            <option value="{{ $value }}" {{ (string)$countryCodeVal === (string)$value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                        @if($countryCodeVal !== null && $countryCodeVal !== '' && !array_key_exists((string)$countryCodeVal, $countryCodeOptions))
                                            <option value="{{ $countryCodeVal }}" selected>{{ $countryCodeVal }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-5">
                                <div class="col-md-3 fv-row">
                                    <label class="form-label">{{ trans('sw.app_currency_ar') }}</label>
                                    <select class="form-select form-select-solid" dir="rtl" name="app_config[currency_ar]">
                                        @foreach($currencyArOptions as $option)
                                            <option value="{{ $option }}" {{ $currencyArVal === $option ? 'selected' : '' }}>{{ $option }}</option>
                                        @endforeach
                                        @if($currencyArVal !== null && $currencyArVal !== '' && !in_array($currencyArVal, $currencyArOptions))
                                            <option value="{{ $currencyArVal }}" selected>{{ $currencyArVal }}</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3 fv-row">
                                    <label class="form-label">{{ trans('sw.app_currency_en') }}</label>
                                    <select class="form-select form-select-solid" name="app_config[currency_en]">
                                        @foreach($currencyEnOptions as $option)
                                            <option value="{{ $option }}" {{ $currencyEnVal === $option ? 'selected' : '' }}>{{ $option }}</option>
                                        @endforeach
                                        @if($currencyEnVal !== null && $currencyEnVal !== '' && !in_array($currencyEnVal, $currencyEnOptions))
                                            <option value="{{ $currencyEnVal }}" selected>{{ $currencyEnVal }}</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3 fv-row">
                                    <label class="form-label">{{ trans('sw.app_timezone') }}</label>
                                    <select class="form-select form-select-solid" name="app_config[timezone]">
                                        @foreach($timezoneOptions as $option)
                                            <option value="{{ $option }}" {{ $timezoneVal === $option ? 'selected' : '' }}>{{ $option }}</option>
                                        @endforeach
                                        @if($timezoneVal !== null && $timezoneVal !== '' && !in_array($timezoneVal, $timezoneOptions))
                                            <option value="{{ $timezoneVal }}" selected>{{ $timezoneVal }}</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3 fv-row">
                                    <label class="form-label">{{ trans('sw.app_timezone_db') }}</label>
                                    <select class="form-select form-select-solid" name="app_config[timezone_db]">
                                        @foreach($timezoneDbOptions as $option)
                                            <option value="{{ $option }}" {{ $timezoneDbVal === $option ? 'selected' : '' }}>{{ $option }}</option>
                                        @endforeach
                                        @if($timezoneDbVal !== null && $timezoneDbVal !== '' && !in_array($timezoneDbVal, $timezoneDbOptions))
                                            <option value="{{ $timezoneDbVal }}" selected>{{ $timezoneDbVal }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-5">
                                <div class="col-md-6 fv-row">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox"
                                               name="app_config[web_payment_subscription]"
                                               value="1"
                                               {{ ($appConfig['web_payment_subscription'] ?? 0) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold">{{ trans('sw.app_web_payment_subscription') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- PUSHER --}}
                    <div class="card card-bordered mb-8">
                        <div class="card-header min-h-50px">
                            <h3 class="card-title fw-bold text-dark">
                                <i class="ki-outline ki-radio fs-2 me-2 text-danger"></i>{{ trans('sw.pusher_settings') }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row mb-5">
                                <div class="col-md-3 fv-row">
                                    <label class="form-label">{{ trans('sw.pusher_app_id') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="integrations_extra[pusher][app_id]"
                                           value="{{ $integrationsSettings['pusher']['app_id'] ?? '' }}"
                                           placeholder="App ID">
                                </div>
                                <div class="col-md-3 fv-row">
                                    <label class="form-label">{{ trans('sw.pusher_app_key') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="integrations_extra[pusher][app_key]"
                                           value="{{ $integrationsSettings['pusher']['app_key'] ?? '' }}"
                                           placeholder="App Key">
                                </div>
                                <div class="col-md-3 fv-row">
                                    <label class="form-label">{{ trans('sw.pusher_app_secret') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="integrations_extra[pusher][app_secret]"
                                           value="{{ $integrationsSettings['pusher']['app_secret'] ?? '' }}"
                                           placeholder="App Secret">
                                </div>
                                <div class="col-md-3 fv-row">
                                    <label class="form-label">{{ trans('sw.pusher_app_cluster') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="integrations_extra[pusher][app_cluster]"
                                           value="{{ $integrationsSettings['pusher']['app_cluster'] ?? 'eu' }}"
                                           placeholder="eu">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- FACEBOOK LOGIN --}}
                    <div class="card card-bordered mb-8">
                        <div class="card-header min-h-50px">
                            <h3 class="card-title fw-bold text-dark">
                                <i class="ki-outline ki-facebook fs-2 me-2 text-primary"></i>{{ trans('sw.facebook_login_settings') }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row mb-5">
                                <div class="col-md-6 fv-row">
                                    <label class="form-label">{{ trans('sw.facebook_client_id') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="integrations_extra[facebook][client_id]"
                                           value="{{ $integrationsSettings['facebook']['client_id'] ?? '' }}"
                                           placeholder="App ID">
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="form-label">{{ trans('sw.facebook_client_secret') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="integrations_extra[facebook][client_secret]"
                                           value="{{ $integrationsSettings['facebook']['client_secret'] ?? '' }}"
                                           placeholder="App Secret">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- GOOGLE LOGIN --}}
                    <div class="card card-bordered mb-8">
                        <div class="card-header min-h-50px">
                            <h3 class="card-title fw-bold text-dark">
                                <i class="ki-outline ki-google fs-2 me-2 text-danger"></i>{{ trans('sw.google_login_settings') }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row mb-5">
                                <div class="col-md-6 fv-row">
                                    <label class="form-label">{{ trans('sw.google_client_id') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="integrations_extra[google][client_id]"
                                           value="{{ $integrationsSettings['google']['client_id'] ?? '' }}"
                                           placeholder="Client ID">
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="form-label">{{ trans('sw.google_client_secret') }}</label>
                                    <input type="text" class="form-control form-control-solid"
                                           name="integrations_extra[google][client_secret]"
                                           value="{{ $integrationsSettings['google']['client_secret'] ?? '' }}"
                                           placeholder="Client Secret">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- end tab-appconfig --}}

                {{-- ===================== AI EMPLOYEE (Claude) TAB ===================== --}}
                <div class="tab-pane fade" id="tab-ai" role="tabpanel">
                    <div class="mb-10">
                        <h4 class="text-dark fw-bold mb-2">
                            <i class="ki-outline ki-abstract-26 fs-2 me-2 text-primary"></i>
                            إعدادات الموظف الذكي — Claude AI
                        </h4>
                        <p class="text-muted fs-6">
                            إعدادات الاتصال بـ Anthropic Claude — المحرك الذكي للموظف الافتراضي على واتساب.
                        </p>
                    </div>

                    {{-- Claude / Anthropic --}}
                    <div class="card card-bordered mb-8">
                        <div class="card-header min-h-50px">
                            <h3 class="card-title fw-bold text-dark">
                                <i class="ki-outline ki-technology-4 fs-2 me-2 text-warning"></i>
                                Anthropic Claude
                            </h3>
                        </div>
                        <div class="card-body">

                            <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-4 mb-6">
                                <i class="ki-outline ki-information-5 fs-2 text-warning me-3"></i>
                                <div class="fs-7 text-gray-700">
                                    هذا المفتاح يُستخدم من قِبَل <strong>الموظف الذكي</strong> للرد على رسائل واتساب.
                                    احصل على مفتاح من <a href="https://console.anthropic.com" target="_blank">console.anthropic.com</a>.
                                    نوصي بنموذج <code>claude-haiku-4-5-20251001</code> للتوفير في التكلفة.
                                </div>
                            </div>

                            <div class="row g-5">
                                <div class="col-md-8">
                                    <label class="form-label required fw-bold">Anthropic API Key</label>
                                    <input type="password" class="form-control form-control-solid"
                                           name="integrations_extra[ai][anthropic_key]"
                                           value="{{ $integrationsSettings['ai']['anthropic_key'] ?? '' }}"
                                           placeholder="sk-ant-...">
                                    <div class="form-text text-muted">مفتاح سري — لا تشاركه مع أحد.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">النموذج</label>
                                    <select class="form-select form-select-solid" name="integrations_extra[ai][claude_model]">
                                        @php
                                            $claudeModels = [
                                                'claude-haiku-4-5-20251001' => 'Haiku 4.5 — اقتصادي (موصى به)',
                                                'claude-sonnet-5'           => 'Sonnet 5 — متوازن',
                                                'claude-opus-4-8'           => 'Opus 4.8 — الأقوى',
                                            ];
                                        @endphp
                                        @foreach($claudeModels as $val => $label)
                                            <option value="{{ $val }}"
                                                {{ ($integrationsSettings['ai']['claude_model'] ?? 'claude-haiku-4-5-20251001') === $val ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="separator separator-dashed my-6"></div>

                            {{-- Quick link to AI Employee --}}
                            @if(Route::has('sw.ai_employee.index'))
                            <div class="d-flex align-items-center gap-3">
                                <a href="{{ route('sw.ai_employee.index') }}" class="btn btn-light-primary btn-sm">
                                    <i class="ki-outline ki-robot fs-4 me-1"></i>
                                    إدارة الموظف الذكي
                                </a>
                                <a href="{{ route('sw.ai_employee.knowledge') }}" class="btn btn-light-warning btn-sm">
                                    <i class="ki-outline ki-book fs-4 me-1"></i>
                                    قاعدة المعرفة
                                </a>
                                <a href="{{ route('sw.ai_employee.conversations') }}" class="btn btn-light-info btn-sm">
                                    <i class="ki-outline ki-message-text-2 fs-4 me-1"></i>
                                    المحادثات
                                </a>
                            </div>
                            @endif

                        </div>
                    </div>
                </div>
                {{-- end tab-ai --}}

                <script>
                    function addAiEmail() {
                        const row = document.createElement('div');
                        row.className = 'd-flex align-items-center gap-2 mb-2 ai-email-row';
                        row.innerHTML = `
                            <input type="email" name="integrations_extra[ai][notify_emails][]"
                                   class="form-control form-control-solid"
                                   placeholder="email@example.com">
                            <button type="button" class="btn btn-icon btn-light-danger btn-sm flex-shrink-0"
                                    onclick="this.closest('.ai-email-row').remove()">
                                <i class="ki-outline ki-cross fs-6"></i>
                            </button>
                        `;
                        document.getElementById('ai-emails-list').appendChild(row);
                    }

                    function addAiPhone() {
                        const row = document.createElement('div');
                        row.className = 'd-flex align-items-center gap-2 mb-2 ai-phone-row';
                        row.innerHTML = `
                            <input type="tel" name="integrations_extra[ai][notify_phones][]"
                                   class="form-control form-control-solid"
                                   placeholder="+966XXXXXXXXX">
                            <button type="button" class="btn btn-icon btn-light-danger btn-sm flex-shrink-0"
                                    onclick="this.closest('.ai-phone-row').remove()">
                                <i class="ki-outline ki-cross fs-6"></i>
                            </button>
                        `;
                        document.getElementById('ai-phones-list').appendChild(row);
                    }
                </script>

            </div>
            {{-- end tab-content --}}

            <!--begin::Form Actions-->
            <div class="d-flex justify-content-end mt-5">
                <a href="{{ route('sw.editSetting') }}" class="btn btn-light me-3">{{ trans('admin.back') ?? 'Back' }}</a>
                <button type="submit" class="btn btn-primary">
                    <span class="indicator-label">{{ trans('global.save') }}</span>
                    <span class="indicator-progress">{{ trans('admin.please_wait') ?? 'Please wait...' }}
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                    </span>
                </button>
            </div>
            <!--end::Form Actions-->
        </form>
    </div>
</div>
</div>
@endsection

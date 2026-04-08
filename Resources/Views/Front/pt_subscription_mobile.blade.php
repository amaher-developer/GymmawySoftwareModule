<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
    @php
        $isRtl     = app()->getLocale() === 'ar';
        $textAlign = $isRtl ? 'right' : 'left';

        $paymentsConfig = @$mainSettings->payments ?? [];
        $tabbyEnabled   = !empty($paymentsConfig['tabby']['public_key']);
        $tamaraEnabled  = !empty($paymentsConfig['tamara']['token']);
        $paytabsEnabled = !empty($paymentsConfig['paytabs']['server_key']);
        $paymobEnabled  = !empty($paymentsConfig['paymob']['api_key']);

        $vatPercentage  = @$mainSettings->vat_details['vat_percentage'] ?? 0;
    @endphp
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f8f8;
            margin: 0;
            padding: 15px;
            direction: {{ $isRtl ? 'rtl' : 'ltr' }};
            text-align: {{ $textAlign }};
            color: #333;
        }
        .pt-header { margin-bottom: 15px; }
        .pt-header h4 { font-size: 18px; margin: 0 0 6px; }
        .pt-header .sub-name { font-size: 13px; color: #888; margin: 0; }
        .section-title { font-size: 15px; font-weight: 600; margin: 18px 0 10px; }
        .class-card {
            background: #fff;
            border: 2px solid #eee;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: border-color .2s;
        }
        .class-card.selected { border-color: #f97d04; }
        .class-card input[type="radio"] { display: none; }
        .class-card-header { display: flex; align-items: center; gap: 10px; }
        .class-radio-dot {
            width: 20px; height: 20px;
            border: 2px solid #ccc;
            border-radius: 50%;
            flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
        }
        .class-card.selected .class-radio-dot { border-color: #f97d04; }
        .class-radio-dot-inner {
            width: 10px; height: 10px;
            background: #f97d04;
            border-radius: 50%;
            display: none;
        }
        .class-card.selected .class-radio-dot-inner { display: block; }
        .class-name { font-size: 15px; font-weight: 600; flex: 1; }
        .class-meta { font-size: 12px; color: #888; margin-top: 4px; padding-{{ $isRtl ? 'right' : 'left' }}: 30px; }
        .class-price { font-size: 16px; font-weight: bold; color: #f97d04; margin-top: 4px; padding-{{ $isRtl ? 'right' : 'left' }}: 30px; }

        /* Trainer section (shown after class select) */
        .trainer-section { margin-top: 10px; padding-{{ $isRtl ? 'right' : 'left' }}: 30px; display: none; }
        .trainer-section.visible { display: block; }
        .trainer-option {
            display: flex; align-items: center; gap: 10px;
            border: 1px solid #ddd; border-radius: 8px;
            padding: 8px 10px; margin-bottom: 8px; cursor: pointer;
            background: #fafafa;
        }
        .trainer-option.selected { border-color: #f97d04; background: #fff8f0; }
        .trainer-option input[type="radio"] { width: 18px; height: 18px; accent-color: #f97d04; flex-shrink: 0; }
        .trainer-name { font-size: 14px; font-weight: 600; }
        .trainer-schedule { font-size: 11px; color: #888; margin-top: 2px; }

        /* Price summary */
        .price-box {
            background: #f5f5f5; border-radius: 8px; padding: 10px;
            font-size: 14px; color: #333; line-height: 1.9; margin-bottom: 14px;
        }
        .price-box .price-total { font-size: 16px; font-weight: bold; color: #f97d04; }

        /* Payment options */
        .payment-option {
            border-radius: 10px; border: 1px solid #f97d04;
            padding: 12px; margin-bottom: 10px;
            display: flex; align-items: flex-start; gap: 10px;
        }
        .payment-option input[type="radio"] { margin-top: 4px; width: 20px; height: 20px; flex-shrink: 0; }
        .payment-option .payment-details { flex: 1; }
        .payment-option label { font-weight: bold; font-size: 14px; cursor: pointer; display: block; margin-bottom: 5px; }
        .payment-option img { width: 80px; padding: 5px; border: 1px solid #ccc; border-radius: 5px; margin-top: 5px; }
        .payment-option .policy-msg { font-size: 11px; color: #666; }

        /* Guest form */
        .highlight-text { border-radius: 10px; border: 1px solid #ddd; padding: 10px; margin-bottom: 12px; }
        .form-control { width: 100%; padding: 10px; margin-bottom: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        .gender-row { display: flex; align-items: center; gap: 15px; padding: 8px 0; }
        .gender-row label { font-size: 14px; margin: 0; }
        .gender-row input[type="radio"] { width: 18px; height: 18px; }

        .btn-pay {
            width: 100%; padding: 14px;
            background: #f97d04; color: #fff;
            border: none; border-radius: 8px;
            font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 10px;
        }
        .btn-pay:disabled { background: #ccc; cursor: not-allowed; }
        .btn-pay:active:not(:disabled) { background: #e06c00; }
        ::placeholder { color: #bbb !important; }
        .no-trainers { color: #888; font-size: 13px; padding: 6px 0; }
    </style>
</head>
<body>

    @if(\Session::has('error'))
        <script>window._toastrError = {!! json_encode(\Session::get('error')) !!};</script>
    @endif

    {{-- Header --}}
    <div class="pt-header">
        <h4>{{ $ptSubscription->name }}</h4>
    </div>

    <form method="post" action="{{ route('sw.pt-invoice-mobile.submit') }}" id="ptForm">
        {{ csrf_field() }}
        <input type="hidden" name="token"              value="{{ request('token') }}">
        <input type="hidden" name="pt_class_id"        id="hiddenClassId"        value="">
        <input type="hidden" name="pt_class_trainer_id" id="hiddenClassTrainerId" value="">
        <input type="hidden" name="amount"             id="hiddenAmount"         value="">
        <input type="hidden" name="vat_percentage"     value="{{ $vatPercentage }}">

        {{-- CLASS SELECTION --}}
        <h5 class="section-title">{{ trans('sw.pt_choose_class') }}</h5>

        @forelse($ptSubscription->classes as $class)
        @php
            $originalPrice  = (float) ($class->price ?? 0);
            $priceBeforeVat = round($originalPrice, 2);
            $vatAmount      = round(($vatPercentage / 100) * $priceBeforeVat, 2);
            $priceWithVat   = round($priceBeforeVat + $vatAmount, 2);
            $scheduleArr    = is_array($class->schedule) ? $class->schedule : [];
        @endphp
        <div class="class-card" id="classCard{{ $class->id }}" onclick="selectClass({{ $class->id }}, {{ $priceWithVat }})">
            <input type="radio" name="_class_radio" value="{{ $class->id }}">
            <div class="class-card-header">
                <div class="class-radio-dot"><div class="class-radio-dot-inner"></div></div>
                <span class="class-name">{{ $class->name }}</span>
            </div>
            <div class="class-meta">
                @if($class->total_sessions)
                    {{ $class->total_sessions }} {{ trans('sw.pt_sessions') }}
                    @if($class->max_members) &nbsp;·&nbsp; {{ trans('front.max') }}: {{ $class->max_members }} @endif
                @endif
                @if(!empty($scheduleArr))
                    &nbsp;·&nbsp;
                    @php
                        $dayNames = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
                        $days = collect($scheduleArr)->filter()->keys()->map(function($d) use ($dayNames) {
                            return trans('front.'.$d) ?? $d;
                        })->implode(', ');
                    @endphp
                    {{ $days }}
                @endif
            </div>
            <div class="class-price">
                {{ number_format($priceBeforeVat, 2) }} {{ trans('front.pound_unit') }}
                @if($vatPercentage > 0)
                    <small style="font-size:11px;color:#888;"> + {{ trans('front.vat') }} {{ number_format($vatAmount, 2) }}</small>
                @endif
            </div>

            {{-- Trainer list (hidden until class selected) --}}
            <div class="trainer-section" id="trainers{{ $class->id }}">
                <div style="font-size:13px;font-weight:600;margin-bottom:8px;">{{ trans('sw.pt_choose_trainer') }}</div>
                @if($class->activeClassTrainers->isEmpty())
                    <p class="no-trainers">{{ trans('sw.pt_no_trainers') }}</p>
                @else
                    @foreach($class->activeClassTrainers as $ct)
                    @if($ct->trainer)
                    @php
                        $wh = is_array($ct->trainer->work_hours) ? $ct->trainer->work_hours : [];
                        $schedStr = collect($wh)->filter()->map(function($h, $d) {
                            return $d . ': ' . (is_array($h) ? implode('-', $h) : $h);
                        })->implode('  |  ');
                    @endphp
                    <div class="trainer-option" id="trainerOpt{{ $ct->id }}"
                         onclick="selectTrainer(event, {{ $class->id }}, {{ $ct->id }})">
                        <input type="radio" name="_trainer_radio_{{ $class->id }}" value="{{ $ct->id }}"
                               id="tr{{ $ct->id }}">
                        <div>
                            <div class="trainer-name">{{ $ct->trainer->name }}</div>
                            @if($schedStr)
                                <div class="trainer-schedule">{{ $schedStr }}</div>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endforeach
                @endif
            </div>
        </div>
        @empty
            <p style="color:#888;font-size:13px;">{{ trans('front.no_data') }}</p>
        @endforelse

        {{-- PRICE SUMMARY (shown after selection) --}}
        <div id="priceSummary" style="display:none;">
            <div class="price-box">
                {{ trans('front.price') }}: <span id="sumBase">-</span> {{ trans('front.pound_unit') }}<br>
                @if($vatPercentage > 0)
                    <small>{{ trans('front.vat') }} ({{ $vatPercentage }}%): <span id="sumVat">-</span> {{ trans('front.pound_unit') }}</small><br>
                @endif
                <span class="price-total">{{ trans('global.total') }}: <span id="sumTotal">-</span> {{ trans('front.pound_unit') }}</span>
            </div>
        </div>

        {{-- GUEST INFO --}}
        @if(!$currentUser)
            <h5 class="section-title">{{ trans('front.register_info') }}:</h5>
            <div class="highlight-text">
                <input type="text"  name="name"    class="form-control" placeholder="{{ trans('front.name') }}"  value="{{ old('name') }}"  required>
                <input type="text"  name="phone"   class="form-control" placeholder="{{ trans('front.phone') }}" value="{{ old('phone') }}" required>
                <div class="gender-row">
                    <input type="radio" name="gender" value="1" id="male_m"   {{ old('gender') == 1 ? 'checked' : '' }} required>
                    <label for="male_m">{{ trans('front.male') }}</label>
                    <input type="radio" name="gender" value="2" id="female_m" {{ old('gender') == 2 ? 'checked' : '' }}>
                    <label for="female_m">{{ trans('front.female') }}</label>
                </div>
                <label style="font-size:13px;color:#555;margin-bottom:2px;display:block;">{{ trans('front.birthdate') }}</label>
                <input type="date" name="dob"     class="form-control" value="{{ old('dob') }}" required>
                <input type="text" name="address" class="form-control" placeholder="{{ trans('front.address') }}" value="{{ old('address') }}" required>
            </div>
        @else
            <input type="hidden" name="name"    value="{{ $currentUser->name }}">
            <input type="hidden" name="phone"   value="{{ $currentUser->phone }}">
            <input type="hidden" name="gender"  value="{{ $currentUser->gender }}">
            <input type="hidden" name="dob"     value="{{ $currentUser->dob ? \Carbon\Carbon::parse($currentUser->dob)->format('Y-m-d') : '' }}">
            <input type="hidden" name="address" value="{{ $currentUser->address }}">
        @endif

        {{-- JOINING DATE --}}
        <h5 class="section-title">{{ trans('front.register_info_joining_date') }}:</h5>
        <div class="highlight-text">
            <label style="font-size:13px;color:#555;margin-bottom:2px;display:block;">
                {{ trans('front.register_info_joining_date') }}
            </label>
            <input type="date" name="joining_date" class="form-control"
                   value="{{ old('joining_date', \Carbon\Carbon::now()->format('Y-m-d')) }}"
                   min="{{ \Carbon\Carbon::now()->format('Y-m-d') }}"
                   max="{{ \Carbon\Carbon::now()->addMonths(12)->format('Y-m-d') }}"
                   required>
        </div>

        {{-- PAYMENT METHOD --}}
        <h5 class="section-title">{{ trans('front.choose_payment_methods') }}:</h5>

        @if($tabbyEnabled)
        <div class="payment-option">
            <input type="radio" name="payment_method" value="2" id="tabby_m" {{ old('payment_method') == '2' ? 'checked' : '' }}>
            <div class="payment-details">
                <label for="tabby_m">{{ trans('front.tabby_installment_msg') }}</label>
                <img src="{{ asset('resources/assets/new_front/images/tabby-logo.webp') }}" onerror="this.style.display='none'" alt="Tabby">
                <span class="policy-msg">{{ trans('front.tabby_policy_msg') }}</span>
            </div>
        </div>
        @endif

        @if($tamaraEnabled)
        <div class="payment-option">
            <input type="radio" name="payment_method" value="4" id="tamara_m" {{ old('payment_method') == '4' ? 'checked' : '' }}>
            <div class="payment-details">
                <label for="tamara_m">{{ trans('front.tamara_installment_msg') }}</label>
                <img src="https://cdn.tamara.co/assets/png/tamara-logo-badge-{{ app()->getLocale() == 'ar' ? 'ar' : 'en' }}.png" alt="Tamara">
                <span class="policy-msg">{{ trans('front.tamara_policy_msg') }}</span>
            </div>
        </div>
        @endif

        @if($paytabsEnabled)
        <div class="payment-option">
            <input type="radio" name="payment_method" value="5" id="paytabs_m" {{ old('payment_method') == '5' ? 'checked' : '' }}>
            <div class="payment-details">
                <label for="paytabs_m">{{ trans('front.paytabs_payment_msg') }}</label>
                <p style="margin:5px 0 0;">
                    <img style="height:40px;width:auto;padding:5px;margin:4px 2px;border:1px solid #ccc;border-radius:5px;object-fit:contain;"
                         src="{{ asset('resources/assets/new_front/images/paytabs-logo.svg') }}" alt="PayTabs">
                    <img style="height:40px;width:auto;padding:5px;margin:4px 2px;border:1px solid #ccc;border-radius:5px;object-fit:contain;"
                         src="{{ asset('resources/assets/new_front/images/visa_logo.svg') }}" alt="Visa">
                    <img style="height:40px;width:auto;padding:5px;margin:4px 2px;border:1px solid #ccc;border-radius:5px;object-fit:contain;"
                         src="{{ asset('resources/assets/new_front/images/mastercard-logo.svg') }}" alt="Mastercard">
                </p>
                <span class="policy-msg">{{ trans('front.paytabs_policy_msg') }}</span>
            </div>
        </div>
        @endif

        @if($paymobEnabled)
        <div class="payment-option">
            <input type="radio" name="payment_method" value="6" id="paymob_m" {{ old('payment_method') == '6' ? 'checked' : '' }}>
            <div class="payment-details">
                <label for="paymob_m">{{ trans('front.paymob_payment_msg') }}</label>
                <p style="margin:5px 0 0;">
                    <img style="height:40px;width:auto;padding:5px;margin:4px 2px;border:1px solid #ccc;border-radius:5px;object-fit:contain;"
                         src="{{ asset('resources/assets/new_front/images/visa_logo.svg') }}" alt="Visa">
                    <img style="height:40px;width:auto;padding:5px;margin:4px 2px;border:1px solid #ccc;border-radius:5px;object-fit:contain;"
                         src="{{ asset('resources/assets/new_front/images/mastercard-logo.svg') }}" alt="Mastercard">
                </p>
                <span class="policy-msg">{{ trans('front.paymob_policy_msg') }}</span>
            </div>
        </div>
        @endif

        <button type="submit" class="btn-pay" id="payBtn" disabled>{{ trans('front.pay_now') }}</button>
    </form>

    <div style="padding-bottom:60px;"></div>

    {{-- Tamara widget --}}
    @if($tamaraEnabled)
    <script>
        window.tamaraWidgetConfig = {
            lang: '{{ app()->getLocale() }}',
            country: '{{ $mainSettings->payments["tamara"]["country"] ?? "SA" }}',
            publicKey: '{{ $mainSettings->payments["tamara"]["public_key"] ?? "" }}'
        };
    </script>
    <script defer src="https://cdn.tamara.co/widget-v2/tamara-widget.js"></script>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
    <script>
        var vatPct   = {{ $vatPercentage }};
        var selectedClassId    = null;
        var selectedTrainerId  = null;
        var selectedClassPrice = 0;

        function selectClass(classId, priceWithVat) {
            // Deselect all
            document.querySelectorAll('.class-card').forEach(function(el) {
                el.classList.remove('selected');
            });
            document.querySelectorAll('.trainer-section').forEach(function(el) {
                el.classList.remove('visible');
            });

            // Select this card
            var card = document.getElementById('classCard' + classId);
            card.classList.add('selected');
            document.getElementById('trainers' + classId).classList.add('visible');

            selectedClassId = classId;
            selectedTrainerId = null;
            selectedClassPrice = priceWithVat;

            document.getElementById('hiddenClassId').value = classId;
            document.getElementById('hiddenClassTrainerId').value = '';
            document.getElementById('hiddenAmount').value = priceWithVat;

            updatePriceSummary(priceWithVat);
            checkPayBtn();
        }

        function selectTrainer(event, classId, ctId) {
            event.stopPropagation();

            // Deselect all trainer options for this class
            document.querySelectorAll('#trainers' + classId + ' .trainer-option').forEach(function(el) {
                el.classList.remove('selected');
            });

            var opt = document.getElementById('trainerOpt' + ctId);
            opt.classList.add('selected');
            document.getElementById('tr' + ctId).checked = true;

            selectedTrainerId = ctId;
            document.getElementById('hiddenClassTrainerId').value = ctId;
            checkPayBtn();
        }

        function updatePriceSummary(totalWithVat) {
            var base = vatPct > 0
                ? (totalWithVat / (1 + vatPct / 100)).toFixed(2)
                : totalWithVat.toFixed(2);
            var vat  = (totalWithVat - parseFloat(base)).toFixed(2);

            document.getElementById('sumBase').textContent  = parseFloat(base).toFixed(2);
            document.getElementById('sumTotal').textContent = parseFloat(totalWithVat).toFixed(2);
            var vatEl = document.getElementById('sumVat');
            if (vatEl) vatEl.textContent = vat;

            document.getElementById('priceSummary').style.display = 'block';
        }

        function checkPayBtn() {
            var hasPayment = !!document.querySelector('input[name="payment_method"]:checked');
            var ready = selectedClassId && selectedTrainerId && hasPayment;
            document.getElementById('payBtn').disabled = !ready;
        }

        document.querySelectorAll('input[name="payment_method"]').forEach(function(el) {
            el.addEventListener('change', checkPayBtn);
        });

        // Toastr
        toastr.options = {
            closeButton: true, progressBar: true,
            positionClass: '{{ app()->getLocale() === "ar" ? "toast-top-left" : "toast-top-right" }}',
            timeOut: 6000,
        };
        if (typeof window._toastrError !== 'undefined') {
            toastr.error(window._toastrError);
        }
        var qError = new URLSearchParams(window.location.search).get('error');
        if (qError) toastr.error(decodeURIComponent(qError));
    </script>
</body>
</html>

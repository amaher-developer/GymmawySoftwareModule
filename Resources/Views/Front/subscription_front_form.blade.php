@extends('software::layouts.form')
@section('breadcrumb')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.dashboard') }}" class="text-muted text-hover-primary">{{ trans('sw.home') }}</a>
        </li>
        <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.listSubscription') }}" class="text-muted text-hover-primary">{{ trans('sw.memberships')}}</a>
        </li>
        <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
        <li class="breadcrumb-item text-gray-900">{{ $title }}</li>
    </ul>
@endsection
@section('form_title') {{ @$title }} @endsection
@section('page_body')
@php
    $oldProducts    = old('products_json');
    $initProducts   = $oldProducts ? (json_decode($oldProducts, true) ?? []) : ($existingProductsJs ?? []);
    $oldGroups      = old('groups_json');
    $initGroups     = $oldGroups   ? (json_decode($oldGroups,   true) ?? []) : ($existingGroupsJs   ?? []);
    if (!is_array($initProducts)) $initProducts = [];
    if (!is_array($initGroups))   $initGroups   = [];
    // Build a map for quick lookup: product_id => {id, is_replaceable}
    $initProductMap = collect($initProducts)->keyBy('product_id');
@endphp

    {{-- Toast notification container --}}
    <div id="sw-toast-container" aria-live="polite" aria-atomic="true"
         style="position:fixed;bottom:1.5rem;{{ app()->getLocale()=='ar' ? 'left' : 'right' }}:1.5rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem;"></div>

    <!--begin::Subscription Form-->
    <form method="post" action="" class="form" enctype="multipart/form-data" id="subscriptionMainForm">
        {{csrf_field()}}

        {{-- Hidden fields serialized on submit --}}
        <input type="hidden" name="products_json" id="hidden_products" value="{{ htmlspecialchars(json_encode($initProducts), ENT_QUOTES) }}" />
        <input type="hidden" name="groups_json"   id="hidden_groups"   value="{{ htmlspecialchars(json_encode($initGroups),   ENT_QUOTES) }}" />

        {{-- ── Tab Navigation ──────────────────────────────────────────── --}}
        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-6 fs-6 border-bottom" id="subscriptionTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-general-btn" data-bs-toggle="tab" data-bs-target="#tab_general" type="button" role="tab">
                    <i class="bi bi-info-circle me-1"></i>{{ trans('sw.tab_general') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-activities-btn" data-bs-toggle="tab" data-bs-target="#tab_activities" type="button" role="tab">
                    <i class="bi bi-list-check me-1"></i>{{ trans('sw.tab_activities') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-products-btn" data-bs-toggle="tab" data-bs-target="#tab_products" type="button" role="tab">
                    <i class="bi bi-box-seam me-1"></i>{{ trans('sw.tab_products') }}
                    <span class="badge badge-circle badge-light-primary ms-2 fs-8" id="badge_products">{{ count($initProducts) }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-customization-btn" data-bs-toggle="tab" data-bs-target="#tab_customization" type="button" role="tab">
                    <i class="bi bi-sliders me-1"></i>{{ trans('sw.tab_customization') }}
                    <span class="badge badge-circle badge-light-success ms-2 fs-8" id="badge_groups">{{ count($initGroups) }}</span>
                </button>
            </li>
            {{-- <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-history-btn" data-bs-toggle="tab" data-bs-target="#tab_history" type="button" role="tab">
                    <i class="bi bi-clock-history me-1"></i>{{ trans('sw.tab_history') }}
                </button>
            </li> --}}
        </ul>

        {{-- ── Tab Content ──────────────────────────────────────────────── --}}
        <div class="tab-content" id="subscriptionTabsContent">

            {{-- ══════════════════════════════════════════════════════════════
                 TAB 1 — General
            ══════════════════════════════════════════════════════════════ --}}
            <div class="tab-pane fade show active" id="tab_general" role="tabpanel">
                <div class="d-flex flex-column gap-7">

                    <!--begin::Basic Information Section-->
                    <div class="card card-flush py-4">
                        <div class="card-header">
                            <div class="card-title">
                                <h2><i class="bi bi-info-circle me-2"></i>{{ trans('sw.basic_information')}}</h2>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-5">
                                <div class="col-md-6">
                                    <div class="fv-row">
                                        <label class="required form-label">{{ trans('sw.name_in_arabic')}}</label>
                                        <input type="text" name="name_ar" class="form-control" dir="rtl"
                                               placeholder="{{ trans('sw.enter_name_in_arabic')}}"
                                               value="{{ old('name_ar', $subscription->getRawOriginal('name_ar')) }}" required />
                                        @error('name_ar') <div class="text-danger fs-7 mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fv-row">
                                        <label class="required form-label">{{ trans('sw.name_in_english')}}</label>
                                        <input type="text" name="name_en" class="form-control"
                                               placeholder="{{ trans('sw.enter_name_in_english')}}"
                                               value="{{ old('name_en', $subscription->getRawOriginal('name_en')) }}" required />
                                        @error('name_en') <div class="text-danger fs-7 mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fv-row">
                                        <label class="form-label">{{ trans('sw.content_in_arabic')}}</label>
                                        <textarea name="content_ar" class="form-control" rows="3" dir="rtl"
                                                  placeholder="{{ trans('sw.content_in_arabic')}}">{{ old('content_ar', $subscription->getRawOriginal('content_ar')) }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fv-row">
                                        <label class="form-label">{{ trans('sw.content_in_english')}}</label>
                                        <textarea name="content_en" class="form-control" rows="3"
                                                  placeholder="{{ trans('sw.content_in_english')}}">{{ old('content_en', $subscription->getRawOriginal('content_en')) }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fv-row">
                                        <label class="form-label">{{ trans('sw.subscription_categories')}}</label>
                                        <select name="subscription_category_id" id="sub_cat_select" class="form-select form-select-solid">
                                            <option value="" data-image="{{ asset('resources/assets/new_front/img/blank-image.svg') }}">
                                                {{ trans('sw.select_category')}}
                                            </option>
                                            @foreach($categories ?? [] as $category)
                                                <option value="{{ $category->id }}"
                                                        data-image="{{ $category->image_url }}"
                                                        {{ old('subscription_category_id', $subscription->subscription_category_id) == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fv-row">
                                        <label class="form-label">{{ trans('sw.image')}}</label>
                                        <input type="file" name="image" id="gym_image" class="form-control" accept="image/*" />
                                        @if(@$subscription->image_name)
                                            <div class="mt-3">
                                                <img id="preview" src="{{ asset('uploads/subscriptions/' . $subscription->image_name) }}"
                                                     alt="preview" style="max-height:120px;border-radius:8px;" />
                                            </div>
                                        @else
                                            <img id="preview" src="" alt="preview" style="max-height:120px;border-radius:8px;display:none;" />
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Basic Information Section-->

                    <!--begin::Pricing & Duration Section-->
                    <div class="card card-flush py-4">
                        <div class="card-header">
                            <div class="card-title">
                                <h2><i class="bi bi-currency-dollar me-2"></i>{{ trans('sw.pricing_and_duration')}}</h2>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-5">
                                <div class="col-md-6">
                                    <div class="fv-row">
                                        <label class="required form-label">{{ trans('sw.price')}}</label>
                                        <input type="number" name="price" class="form-control"
                                               placeholder="{{ trans('sw.enter_price')}}"
                                               value="{{ old('price', $subscription->price) }}"
                                               step="0.01" min="0" id="subscription_price_input" required />
                                        @php $vatPercentage = data_get($mainSettings ?? [], 'vat_details.vat_percentage', 0); @endphp
                                        @if($vatPercentage > 0)
                                            <div class="mt-2">
                                                <small class="text-muted" style="font-size:0.85rem;">
                                                    {{ trans('sw.after_vat') }}: <span id="subscription_price_with_vat" class="fw-semibold">0.00</span>
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fv-row">
                                        <label class="required form-label">{{ trans('sw.period')}} ({{ trans('sw.days')}})</label>
                                        <input type="number" name="period" class="form-control"
                                               placeholder="{{ trans('sw.enter_period')}}"
                                               value="{{ old('period', $subscription->period) }}"
                                               min="1" required />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fv-row">
                                        <label class="form-label">{{ trans('sw.default_discount_value')}}</label>
                                        <input type="number" name="default_discount_value" class="form-control"
                                               placeholder="{{ trans('sw.enter_default_discount_value')}}"
                                               value="{{ old('default_discount_value', $subscription->default_discount_value) }}"
                                               step="0.01" min="0" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fv-row">
                                        <label class="form-label">{{ trans('sw.default_discount_type')}}</label>
                                        <select name="default_discount_type" class="form-select">
                                            <option value="">{{ trans('sw.select_discount_type')}}</option>
                                            <option value="1" {{ old('default_discount_type', $subscription->default_discount_type) == 1 ? 'selected' : '' }}>{{ trans('sw.percentage')}}</option>
                                            <option value="2" {{ old('default_discount_type', $subscription->default_discount_type) == 2 ? 'selected' : '' }}>{{ trans('sw.fixed_amount')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fv-row">
                                        <label class="form-label">{{ trans('sw.invitations_num') }}</label>
                                        <input type="number" name="invitations" class="form-control"
                                               placeholder="{{ trans('sw.invitations_num') }}"
                                               value="{{ old('invitations', $subscription->invitations ?? 0) }}"
                                               min="0" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Pricing & Duration Section-->

                    <!--begin::Workout Settings Section-->
                    <div class="card card-flush py-4">
                        <div class="card-header">
                            <div class="card-title">
                                <h2><i class="bi bi-activity me-2"></i>{{ trans('sw.workout_settings')}}</h2>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-5">
                                <div class="col-md-6">
                                    <div class="fv-row">
                                        <label class="form-label">{{ trans('sw.workouts')}}</label>
                                        <input type="number" name="workouts" class="form-control"
                                               placeholder="{{ trans('sw.enter_workouts')}}"
                                               value="{{ old('workouts', $subscription->workouts) }}" min="0" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fv-row">
                                        <label class="form-label">{{ trans('sw.workouts_per_day')}}</label>
                                        <input type="number" name="workouts_per_day" id="workouts_per_day" class="form-control"
                                               placeholder="{{ trans('sw.enter_workouts_per_day')}}"
                                               value="{{ old('workouts_per_day', $subscription->workouts_per_day) }}"
                                               min="0" {{ old('workouts_per_day', $subscription->workouts_per_day) ? '' : 'disabled' }} />
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check form-check-custom form-check-solid form-switch mb-5">
                                        <input class="form-check-input" type="checkbox" name="check_workouts_per_day"
                                               id="check_workouts_per_day" value="1"
                                               {{ old('check_workouts_per_day', $subscription->workouts_per_day) ? 'checked' : '' }} />
                                        <label class="form-check-label fw-bold text-gray-700" for="check_workouts_per_day">
                                            {{ trans('sw.enable_workouts_per_day')}}
                                        </label>
                                    </div>
                                </div>
                                @php
                                    $startTimeValue = old('start_time_day', $subscription->start_time_day);
                                    if ($startTimeValue) { try { $startTimeValue = \Carbon\Carbon::parse($startTimeValue)->format('H:i'); } catch (\Exception $e) { $startTimeValue = ''; } }
                                    $endTimeValue = old('end_time_day', $subscription->end_time_day);
                                    if ($endTimeValue) { try { $endTimeValue = \Carbon\Carbon::parse($endTimeValue)->format('H:i'); } catch (\Exception $e) { $endTimeValue = ''; } }
                                @endphp
                                <div class="col-md-6">
                                    <div class="fv-row">
                                        <label class="form-label">{{ trans('sw.start_time_day')}}</label>
                                        <input type="time" name="start_time_day" id="start_time_day" class="form-control"
                                               value="{{ $startTimeValue }}" {{ $startTimeValue ? '' : 'disabled' }} />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fv-row">
                                        <label class="form-label">{{ trans('sw.end_time_day')}}</label>
                                        <input type="time" name="end_time_day" id="end_time_day" class="form-control"
                                               value="{{ $endTimeValue }}" {{ $endTimeValue ? '' : 'disabled' }} />
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check form-check-custom form-check-solid form-switch mb-5">
                                        <input class="form-check-input" type="checkbox" name="time_day"
                                               id="time_day" value="1"
                                               {{ old('time_day', $subscription->start_time_day) ? 'checked' : '' }} />
                                        <label class="form-check-label fw-bold text-gray-700" for="time_day">
                                            {{ trans('sw.enable_time_restrictions')}}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="fv-row">
                                        <label class="form-label">{{ trans('sw.available_days')}}</label>
                                        <div class="d-flex flex-wrap gap-3 mt-3" id="week_days_container">
                                            @php
                                                $weekDays = ['saturday'=>trans('sw.saturday'),'sunday'=>trans('sw.sunday'),'monday'=>trans('sw.monday'),'tuesday'=>trans('sw.tuesday'),'wednesday'=>trans('sw.wednesday'),'thursday'=>trans('sw.thursday'),'friday'=>trans('sw.friday')];
                                                $timeWeekValue = $subscription->time_week;
                                                if (is_string($timeWeekValue)) { $decodedTimeWeek = $timeWeekValue !== '' ? json_decode($timeWeekValue, true) : []; }
                                                elseif (is_array($timeWeekValue)) { $decodedTimeWeek = $timeWeekValue; }
                                                else { $decodedTimeWeek = []; }
                                                if (!is_array($decodedTimeWeek)) { $decodedTimeWeek = []; }
                                                $selectedDays = old('time_week', $decodedTimeWeek ?: []);
                                            @endphp
                                            @foreach($weekDays as $key => $day)
                                                <div class="form-check form-check-custom form-check-solid day-checkbox-item">
                                                    <input class="form-check-input week-day-check" type="checkbox"
                                                           name="time_week[]" value="{{ $key }}" id="day_{{ $key }}"
                                                           {{ in_array($key, (array)$selectedDays) ? 'checked' : '' }}
                                                           {{ $selectedDays ? '' : 'disabled' }} />
                                                    <label class="form-check-label fw-bold" for="day_{{ $key }}">{{ $day }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check form-check-custom form-check-solid form-switch mb-5">
                                        <input class="form-check-input" type="checkbox" name="check_time_week"
                                               id="check_time_week" value="1"
                                               {{ old('check_time_week', $subscription->time_week) ? 'checked' : '' }} />
                                        <label class="form-check-label fw-bold text-gray-700" for="check_time_week">
                                            {{ trans('sw.enable_specific_days')}}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Workout Settings Section-->

                    <!--begin::Freeze Settings Section-->
                    <div class="card card-flush py-4">
                        <div class="card-header">
                            <div class="card-title">
                                <h2><i class="bi bi-snow me-2"></i>{{ trans('sw.freeze_settings')}}</h2>
                            </div>
                            <div class="card-toolbar">
                                <a href="#" id="toggleFreezeHelp" class="btn btn-sm btn-light-primary">
                                    <i class="bi bi-question-circle me-2"></i>{{ trans('sw.read_more') }}
                                </a>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div id="freeze_help_box" class="alert alert-info mb-7 d-none" role="alert">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-snow me-3 fs-2"></i>
                                    <div>
                                        <div class="fw-semibold mb-2">{{ trans('sw.freeze_help_title') }}</div>
                                        <ul class="mb-0 ps-4">
                                            <li>{{ trans('sw.freeze_help_freeze_limit_desc') }}</li>
                                            <li>{{ trans('sw.freeze_help_number_times_freeze_desc') }}</li>
                                            <li>{{ trans('sw.freeze_help_max_extension_days_desc') }}</li>
                                            <li>{{ trans('sw.freeze_help_max_freeze_extension_sum_desc') }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-5">
                                <div class="col-md-6">
                                    <div class="fv-row">
                                        <label class="form-label">{{ trans('sw.freeze_limit')}} ({{ trans('sw.days')}})
                                            <a href="#" class="ms-1 text-muted small freeze-def-toggle" data-target="#def_freeze_limit"><i class="bi bi-info-circle"></i></a>
                                        </label>
                                        <input type="number" name="freeze_limit" class="form-control"
                                               placeholder="{{ trans('sw.enter_freeze_limit')}}"
                                               value="{{ old('freeze_limit', $subscription->freeze_limit ?? 0) }}" min="0" />
                                        <div id="def_freeze_limit" class="form-text d-none">{{ trans('sw.field_help_freeze_limit') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fv-row">
                                        <label class="form-label">{{ trans('sw.number_times_freeze')}}
                                            <a href="#" class="ms-1 text-muted small freeze-def-toggle" data-target="#def_number_times_freeze"><i class="bi bi-info-circle"></i></a>
                                        </label>
                                        <input type="number" name="number_times_freeze" class="form-control"
                                               placeholder="{{ trans('sw.enter_number_times_freeze')}}"
                                               value="{{ old('number_times_freeze', $subscription->number_times_freeze ?? 0) }}" min="0" />
                                        <div id="def_number_times_freeze" class="form-text d-none">{{ trans('sw.field_help_number_times_freeze') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fv-row">
                                        <label class="form-label">{{ trans('sw.max_extension_days') }}
                                            <a href="#" class="ms-1 text-muted small freeze-def-toggle" data-target="#def_max_extension_days"><i class="bi bi-info-circle"></i></a>
                                        </label>
                                        <input type="number" name="max_extension_days" class="form-control"
                                               placeholder="{{ trans('sw.enter_max_extension_days') }}"
                                               value="{{ old('max_extension_days', $subscription->max_extension_days ?? 0) }}" min="0" />
                                        <div id="def_max_extension_days" class="form-text d-none">{{ trans('sw.field_help_max_extension_days') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fv-row">
                                        <label class="form-label">{{ trans('sw.max_freeze_extension_sum') }}
                                            <a href="#" class="ms-1 text-muted small freeze-def-toggle" data-target="#def_max_freeze_extension_sum"><i class="bi bi-info-circle"></i></a>
                                        </label>
                                        <input type="number" name="max_freeze_extension_sum" class="form-control"
                                               placeholder="{{ trans('sw.enter_max_freeze_extension_sum') }}"
                                               value="{{ old('max_freeze_extension_sum', $subscription->max_freeze_extension_sum ?? 0) }}" min="0" />
                                        <div id="def_max_freeze_extension_sum" class="form-text d-none">{{ trans('sw.field_help_max_freeze_extension_sum') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Freeze Settings Section-->

                    <!--begin::Additional Settings Section-->
                    <div class="card card-flush py-4">
                        <div class="card-header">
                            <div class="card-title">
                                <h2><i class="bi bi-gear me-2"></i>{{ trans('sw.additional_settings')}}</h2>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-5">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">{{ trans('sw.availability')}}</label>
                                    <div class="d-flex flex-wrap gap-5 mt-3">
                                        <div class="form-check form-check-custom form-check-solid form-check-lg">
                                            <input class="form-check-input" type="checkbox" name="is_system" id="is_system" value="1"
                                                   {{ old('is_system', $subscription->is_system ?? 1) ? 'checked' : '' }} />
                                            <label class="form-check-label fw-bold" for="is_system">
                                                <i class="bi bi-shield-lock text-danger me-2"></i>{{ trans('sw.available_on_system')}}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-custom form-check-solid form-check-lg">
                                            <input class="form-check-input" type="checkbox" name="is_web" id="is_web" value="1"
                                                   {{ old('is_web', $subscription->is_web) ? 'checked' : '' }} />
                                            <label class="form-check-label fw-bold" for="is_web">
                                                <i class="bi bi-globe text-primary me-2"></i>{{ trans('sw.available_on_web')}}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-custom form-check-solid form-check-lg">
                                            <input class="form-check-input" type="checkbox" name="is_mobile" id="is_mobile" value="1"
                                                   {{ old('is_mobile', $subscription->is_mobile) ? 'checked' : '' }} />
                                            <label class="form-check-label fw-bold" for="is_mobile">
                                                <i class="bi bi-phone text-success me-2"></i>{{ trans('sw.available_on_mobile')}}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-custom form-check-solid form-check-lg">
                                            <input class="form-check-input" type="checkbox" name="is_expire_changeable" id="is_expire_changeable" value="1"
                                                   {{ old('is_expire_changeable', $subscription->is_expire_changeable) ? 'checked' : '' }} />
                                            <label class="form-check-label fw-bold" for="is_expire_changeable">
                                                <i class="bi bi-calendar-check text-warning me-2"></i>{{ trans('sw.is_expire_changeable')}}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Additional Settings Section-->

                </div>{{-- /d-flex flex-column gap-7 --}}
            </div>{{-- /tab_general --}}

            {{-- ══════════════════════════════════════════════════════════════
                 TAB 2 — Activities
            ══════════════════════════════════════════════════════════════ --}}
            <div class="tab-pane fade" id="tab_activities" role="tabpanel">
                <div class="card card-flush py-4">
                    <div class="card-header">
                        <div class="card-title">
                            <h2><i class="bi bi-list-check me-2"></i>{{ trans('sw.activities')}}</h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row g-4 mb-5">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">{{ trans('sw.activity_limit') }}</label>
                                <input type="number" name="activity_limit" class="form-control" min="1"
                                       value="{{ old('activity_limit', @$subscription->activity_limit) }}"
                                       placeholder="{{ trans('sw.activity_limit_placeholder') }}" />
                                <div class="form-text">{{ trans('sw.activity_limit_hint') }}</div>
                            </div>
                        </div>
                        @if(@$activities && count($activities) > 0)
                            <div class="row g-4 mb-5">
                                <div class="col-md-7">
                                    <div class="position-relative">
                                        <i class="bi bi-search position-absolute ms-3" style="top:50%;transform:translateY(-50%);"></i>
                                        <input type="text" id="activities_search" class="form-control ps-10"
                                               placeholder="{{ trans('sw.search_on') }}" autocomplete="off" />
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <select id="activities_trainer_filter" class="form-select">
                                        <option value="">{{ trans('sw.trainer') }} — {{ trans('admin.choose') }}...</option>
                                        @foreach($trainers ?? [] as $t)
                                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif
                        <div id="activities_container">
                            @if(@$activities && count($activities) > 0)
                                @foreach($activities as $activity)
                                    @php
                                        $activitySubscription = $subscription->activities->where('activity_id', $activity->id)->first();
                                        $trainingTimes = $activitySubscription ? $activitySubscription->training_times : 0;
                                    @endphp
                                    <div class="d-flex align-items-center mb-4 p-4 bg-light rounded activity-item"
                                         data-name="{{ mb_strtolower($activity->name ?? '') }}"
                                         data-trainer-id="{{ $activity->trainer_id ?? '' }}">
                                        <div class="form-check form-check-custom form-check-solid me-4">
                                            <input class="form-check-input activity-check" type="checkbox"
                                                   id="activity_{{ $activity->id }}"
                                                   {{ $trainingTimes > 0 ? 'checked' : '' }} />
                                            <label class="form-check-label fw-bold" for="activity_{{ $activity->id }}">
                                                {{ $activity->name }}
                                            </label>
                                            @if($activity->trainer)
                                                <span class="text-muted fs-8 d-block">
                                                    <i class="bi bi-person-badge me-1"></i>{{ $activity->trainer->name }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <input type="number" class="form-control training-times"
                                                   name="activities[{{ $activity->id }}]"
                                                   value="{{ $trainingTimes > 0 ? $trainingTimes : '' }}"
                                                   placeholder="{{ trans('sw.training_times')}}"
                                                   min="0"
                                                   {{ $trainingTimes > 0 ? '' : 'disabled' }} />
                                        </div>
                                    </div>
                                @endforeach
                                <div id="activities_no_match" class="alert alert-info d-none">
                                    {{ trans('sw.no_record_found') }}
                                </div>
                            @else
                                <div class="alert alert-info">
                                    {{ trans('sw.no_activities_available')}}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>{{-- /tab_activities --}}

            {{-- ══════════════════════════════════════════════════════════════
                 TAB 3 — Products (checkbox list, like Activities)
            ══════════════════════════════════════════════════════════════ --}}
            <div class="tab-pane fade" id="tab_products" role="tabpanel">
                <div class="card card-flush py-4">
                    <div class="card-header">
                        <div class="card-title">
                            <h2><i class="bi bi-box-seam me-2"></i>{{ trans('sw.tab_products') }}</h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        @if(($allProducts ?? collect())->isEmpty())
                            <div class="alert alert-info">{{ trans('sw.no_store_products_hint') }}</div>
                        @else
                            <div class="position-relative mb-5">
                                <i class="bi bi-search position-absolute ms-3" style="top:50%;transform:translateY(-50%);"></i>
                                <input type="text" id="products_search" class="form-control ps-10"
                                       placeholder="{{ trans('sw.search_on') }}" autocomplete="off" />
                            </div>
                            <div class="products-scrollable">
                                @foreach($allProducts as $prod)
                                    @php
                                        $ep            = $initProductMap->get($prod->id);
                                        $isChecked     = !is_null($ep);
                                        $isReplaceable = $ep ? (bool) $ep['is_replaceable'] : false;
                                        $existingId    = $ep ? $ep['id'] : '';
                                    @endphp
                                    <div class="d-flex align-items-center p-4 product-item {{ !$loop->last ? 'border-bottom' : '' }}"
                                         data-product-id="{{ $prod->id }}"
                                         data-product-name="{{ $prod->name }}"
                                         data-existing-id="{{ $existingId }}"
                                         data-existing-replaceable="{{ $isReplaceable ? '1' : '0' }}">
                                        <div class="form-check form-check-custom form-check-solid d-flex align-items-center gap-3 me-4">
                                            <input class="form-check-input product-check flex-shrink-0" type="checkbox"
                                                   id="product_{{ $prod->id }}"
                                                   {{ $isChecked ? 'checked' : '' }} />
                                            @php $rawImg = $prod->getRawOriginal('image'); @endphp
                                            @if($rawImg && !filter_var($rawImg, FILTER_VALIDATE_URL))
                                                <img src="{{ asset('uploads/products/' . basename($rawImg)) }}"
                                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
                                                     class="product-thumb" />
                                                <div class="product-thumb-placeholder" style="display:none"><i class="bi bi-box"></i></div>
                                            @elseif($rawImg && filter_var($rawImg, FILTER_VALIDATE_URL))
                                                <img src="{{ $rawImg }}"
                                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
                                                     class="product-thumb" />
                                                <div class="product-thumb-placeholder" style="display:none"><i class="bi bi-box"></i></div>
                                            @else
                                                <div class="product-thumb-placeholder"><i class="bi bi-box"></i></div>
                                            @endif
                                            <label class="form-check-label fw-bold mb-0" for="product_{{ $prod->id }}">
                                                {{ $prod->name }}
                                            </label>
                                        </div>
                                        <div class="ms-auto replaceable-toggle" style="{{ $isChecked ? '' : 'display:none' }}">
                                            <div class="form-check form-check-solid">
                                                <input class="form-check-input product-replaceable" type="checkbox"
                                                       id="replaceable_{{ $prod->id }}"
                                                       {{ $isReplaceable ? 'checked' : '' }} />
                                                <label class="form-check-label text-muted small" for="replaceable_{{ $prod->id }}">
                                                    {{ trans('sw.replaceable') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div id="products_no_match" class="alert alert-info d-none mt-4">
                                {{ trans('sw.no_record_found') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>{{-- /tab_products --}}

            {{-- ══════════════════════════════════════════════════════════════
                 TAB 4 — Customization (client-side state)
            ══════════════════════════════════════════════════════════════ --}}
            <div class="tab-pane fade" id="tab_customization" role="tabpanel">
                <div class="card card-flush py-4">
                    <div class="card-header">
                        <div class="card-title">
                            <h2><i class="bi bi-sliders me-2"></i>{{ trans('sw.subscription_options') }}</h2>
                        </div>
                        <div class="card-toolbar">
                            <button type="button" class="btn btn-sm btn-light-success" id="btn_open_add_group">
                                <i class="bi bi-plus me-1"></i>{{ trans('sw.add_option_group') }}
                            </button>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div id="groups_list_container"></div>
                    </div>
                </div>
            </div>{{-- /tab_customization --}}

            {{-- TAB 5 — History (hidden)
            <div class="tab-pane fade" id="tab_history" role="tabpanel">
                <div class="card card-flush py-4">
                    <div class="card-body text-center py-20">
                        <i class="bi bi-clock-history fs-2x text-muted d-block mb-4"></i>
                        <div class="fw-semibold fs-5 text-gray-700">{{ trans('sw.tab_history') }}</div>
                        <div class="text-muted fs-7 mt-2">{{ trans('sw.history_coming_soon') }}</div>
                    </div>
                </div>
            </div>
            --}}

        </div>{{-- /tab-content --}}

        {{-- ── Form Actions (always visible) ───────────────────────────── --}}
        <div class="d-flex justify-content-end gap-3 mt-6">
            <button type="reset" class="btn btn-secondary">{{ trans('admin.reset')}}</button>
            <button type="submit" class="btn btn-primary" id="btn_main_save">
                <span class="indicator-label">{{ trans('global.save')}}</span>
                <span class="indicator-progress d-none">{{ trans('sw.please_wait')}}...
                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                </span>
            </button>
        </div>

    </form>
    <!--end::Subscription Form-->

    {{-- ══════════════════════════════════════════════════════════════════
         Modal: Add Option Group
    ══════════════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="modalAddGroup" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-sliders me-2"></i>{{ trans('sw.add_option_group') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label required">{{ trans('sw.name_in_arabic') }}</label>
                            <input type="text" id="modal_group_name_ar" class="form-control" dir="rtl" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ trans('sw.name_in_english') }}</label>
                            <input type="text" id="modal_group_name_en" class="form-control" />
                        </div>
                        {{-- Source Type --}}
                        <div class="col-md-12">
                            <label class="form-label fw-bold">{{ trans('sw.source_type') }}</label>
                            <div class="d-flex gap-4 mt-1">
                                <div class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="radio" name="modal_group_source_type" id="src_product" value="product" checked />
                                    <label class="form-check-label" for="src_product">
                                        <i class="bi bi-box-seam text-primary me-1"></i>{{ trans('sw.products') }}
                                    </label>
                                </div>
                                <div class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="radio" name="modal_group_source_type" id="src_activity" value="activity" />
                                    <label class="form-check-label" for="src_activity">
                                        <i class="bi bi-list-check text-success me-1"></i>{{ trans('sw.activities') }}
                                    </label>
                                </div>
                                <div class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="radio" name="modal_group_source_type" id="src_text" value="text" />
                                    <label class="form-check-label" for="src_text">
                                        <i class="bi bi-input-cursor-text text-warning me-1"></i>{{ trans('sw.text_input') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ trans('sw.selection_type') }}</label>
                            <select id="modal_group_selection_type" class="form-select">
                                <option value="single">{{ trans('sw.single') }}</option>
                                <option value="multiple">{{ trans('sw.multiple') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end pb-1">
                            <div class="form-check form-check-solid">
                                <input class="form-check-input" type="checkbox" id="modal_group_required" />
                                <label class="form-check-label fw-bold" for="modal_group_required">{{ trans('sw.mandatory') }}</label>
                            </div>
                        </div>
                        {{-- Category link (only relevant when source_type = product) --}}
                        <div class="col-md-12" id="modal_group_category_row">
                            <label class="form-label">
                                <i class="bi bi-tags me-1 text-warning"></i>
                                {{ trans('sw.linked_product_category') }}
                                <span class="text-muted fs-8 ms-1">({{ trans('sw.optional') }})</span>
                            </label>
                            @if($storeCategories->isEmpty())
                                <div class="alert alert-warning py-2 px-3 fs-8 mb-0">
                                    <i class="bi bi-exclamation-triangle me-1"></i>{{ trans('sw.no_store_categories_hint') }}
                                </div>
                                <input type="hidden" id="modal_group_category_id" value="" />
                            @else
                                <select id="modal_group_category_id" class="form-select">
                                    <option value="">— {{ trans('sw.no_category_linked') }} —</option>
                                    @foreach($storeCategories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted">{{ trans('sw.category_link_hint') }}</div>
                            @endif
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold mb-3">{{ trans('sw.visibility') }}</label>
                            <div class="d-flex gap-5 flex-wrap">
                                <div class="form-check form-check-solid">
                                    <input class="form-check-input" type="checkbox" id="modal_group_is_system" checked />
                                    <label class="form-check-label" for="modal_group_is_system"><i class="bi bi-shield-lock text-danger me-1"></i>{{ trans('sw.available_on_system') }}</label>
                                </div>
                                <div class="form-check form-check-solid">
                                    <input class="form-check-input" type="checkbox" id="modal_group_is_web" checked />
                                    <label class="form-check-label" for="modal_group_is_web"><i class="bi bi-globe text-primary me-1"></i>{{ trans('sw.available_on_web') }}</label>
                                </div>
                                <div class="form-check form-check-solid">
                                    <input class="form-check-input" type="checkbox" id="modal_group_is_mobile" checked />
                                    <label class="form-check-label" for="modal_group_is_mobile"><i class="bi bi-phone text-success me-1"></i>{{ trans('sw.available_on_mobile') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('admin.cancel') }}</button>
                    <button type="button" class="btn btn-success" id="btn_modal_save_group">
                        <span class="indicator-label">{{ trans('global.save') }}</span>
                        <span class="indicator-progress d-none"><span class="spinner-border spinner-border-sm me-1"></span>{{ trans('sw.please_wait') }}...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         Modal: Add Option to Group
    ══════════════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="modalAddOption" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-tag me-2"></i>{{ trans('sw.add_option') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Product picker (shown when group source_type = 'product') --}}
                    <div id="modal_option_product_section">
                        <div id="modal_option_no_products_alert" class="alert alert-warning d-none mb-3 py-2 px-3 fs-8">
                            <i class="bi bi-exclamation-triangle me-1"></i>{{ trans('sw.no_store_products_hint') }}
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">{{ trans('sw.product') }}</label>
                            <select id="modal_option_product_id" class="form-select">
                                <option value="">— {{ trans('sw.select_product') }} —</option>
                            </select>
                            <div id="modal_option_category_hint" class="form-text text-muted d-none">
                                <i class="bi bi-funnel me-1"></i>{{ trans('sw.filtered_by_linked_category') }}
                            </div>
                        </div>
                    </div>
                    {{-- Activity picker (shown when group source_type = 'activity') --}}
                    <div id="modal_option_activity_section" class="d-none">
                        <div class="mb-3">
                            <label class="form-label required">{{ trans('sw.activity') }}</label>
                            <select id="modal_option_activity_id" class="form-select">
                                <option value="">— {{ trans('sw.select_activity') }} —</option>
                            </select>
                        </div>
                    </div>
                    {{-- Text input (shown when group source_type = 'text') --}}
                    <div id="modal_option_text_section" class="d-none">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">{{ trans('sw.name_in_arabic') }}</label>
                                <input type="text" id="modal_option_text_name_ar" class="form-control" dir="rtl" placeholder="{{ trans('sw.enter_name_in_arabic') }}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ trans('sw.name_in_english') }}</label>
                                <input type="text" id="modal_option_text_name_en" class="form-control" placeholder="{{ trans('sw.enter_name_in_english') }}" />
                            </div>
                        </div>
                    </div>
                    <hr class="my-3" />
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-currency-dollar me-1 text-success"></i>
                            {{ trans('sw.price_modifier') }}
                            <span class="text-muted fs-8 ms-1 fw-normal">({{ trans('sw.price_modifier_hint') }})</span>
                        </label>
                        <input type="number" id="modal_option_price" class="form-control form-control-lg" value="0" step="0.01" placeholder="0.00" />
                        <div class="form-text text-muted">{{ trans('sw.price_modifier_description') }}</div>
                    </div>
                    <div>
                        <label class="form-label">{{ trans('sw.order') }}</label>
                        <input type="number" id="modal_option_order" class="form-control" value="0" min="0" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('admin.cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="btn_modal_save_option">
                        <span class="indicator-label">{{ trans('global.save') }}</span>
                        <span class="indicator-progress d-none"><span class="spinner-border spinner-border-sm me-1"></span>{{ trans('sw.please_wait') }}...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')
    <style>
        .form-check-custom .form-check-input { width:1.5rem;height:1.5rem;border:2px solid #d1d5db;border-radius:.375rem;transition:all .3s ease; }
        .form-check-custom .form-check-input:checked { background-color:#3b82f6;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.1); }
        .form-check-custom.form-switch .form-check-input { width:3rem;height:1.5rem;border-radius:2rem; }
        .form-check-custom.form-switch .form-check-input:checked { background-color:#10b981;border-color:#10b981; }
        .form-check-lg .form-check-input { width:1.75rem;height:1.75rem; }
        .form-check-custom .form-check-label { cursor:pointer;user-select:none;margin-left:.5rem; }
        .day-checkbox-item { padding:.75rem 1.25rem;background:#f9fafb;border-radius:.5rem;border:2px solid #e5e7eb;transition:all .3s ease; }
        .day-checkbox-item:has(.form-check-input:checked) { background:#eff6ff;border-color:#3b82f6; }
        .activity-item { border:2px solid #e5e7eb;transition:all .3s ease; }
        .activity-item:has(.activity-check:checked) { background:#f0fdf4!important;border-color:#10b981; }
        .card-title h2 { font-size:1.25rem;font-weight:600;color:#1f2937;display:flex;align-items:center; }
        .card-title h2 i { font-size:1.5rem;color:#3b82f6; }
        #subscriptionTabs .nav-link { font-weight:600; }
        #subscriptionTabs .nav-link.active { color: #3b82f6; }
        .products-scrollable { max-height:440px;overflow-y:auto;border:1px solid #e5e7eb;border-radius:.5rem; }
        .product-item { transition:background .2s; }
        .product-item:has(.product-check:checked) { background:#eff6ff; }
        .replaceable-toggle label { font-size:.8rem; }
        .product-thumb { width:44px;height:44px;object-fit:cover;border-radius:8px;flex-shrink:0; }
        .product-thumb-placeholder { width:44px;height:44px;border-radius:8px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;color:#9ca3af;flex-shrink:0; }
        .sw-group-card { border:1px solid #e5e7eb;border-radius:.75rem;margin-bottom:1rem;overflow:hidden; }
        .sw-group-card .card-header { background:#f9fafb;padding:.75rem 1rem;display:flex;align-items:center;gap:.5rem;flex-wrap:wrap; }
        .sw-opt-thumb { width:36px;height:36px;object-fit:cover;border-radius:6px;display:block; }
        .sw-opt-thumb-ph { width:36px;height:36px;border-radius:6px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-size:.9rem; }
    </style>

    <script>
    $(document).ready(function() {

        // ─────────────────────────────────────────────────────────────
        // General form helpers
        // ─────────────────────────────────────────────────────────────
        const vatPercentage = {{ $vatPercentage ?? 0 }};
        const $priceInput   = $('#subscription_price_input');
        const $priceVat     = $('#subscription_price_with_vat');
        function updatePriceWithVat() {
            if ($priceInput.length && $priceVat.length && vatPercentage > 0) {
                var p = parseFloat($priceInput.val()) || 0;
                $priceVat.text((p + p * vatPercentage / 100).toFixed(2));
            }
        }
        if ($priceInput.length) { $priceInput.on('input', updatePriceWithVat); updatePriceWithVat(); }

        $('#gym_image').change(function() {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) { $('#preview').attr('src', e.target.result).show(); };
                reader.readAsDataURL(this.files[0]);
            }
        });

        $('#check_workouts_per_day').change(function() {
            $('#workouts_per_day').prop('disabled', !this.checked);
            if (!this.checked) $('#workouts_per_day').val('');
        });
        $('#time_day').change(function() {
            $('#start_time_day, #end_time_day').prop('disabled', !this.checked);
            if (!this.checked) $('#start_time_day, #end_time_day').val('');
        });
        $('#check_time_week').change(function() {
            $('.week-day-check').prop('disabled', !this.checked);
            if (!this.checked) $('.week-day-check').prop('checked', false);
        });
        $('.activity-check').change(function() {
            var input = $(this).closest('.activity-item').find('.training-times');
            if (this.checked) { input.prop('disabled', false); if (!input.val()) input.val(1); }
            else { input.prop('disabled', true); input.val(''); }
        });
        $('#toggleFreezeHelp').on('click', function(e) { e.preventDefault(); $('#freeze_help_box').toggleClass('d-none'); });
        $('.freeze-def-toggle').on('click', function(e) { e.preventDefault(); $($(this).data('target')).toggleClass('d-none'); });

        // Serialize draft state before submit
        $('#subscriptionMainForm').on('submit', function() {
            $('#hidden_products').val(JSON.stringify(SW_DRAFT.products));
            $('#hidden_groups').val(JSON.stringify(SW_DRAFT.groups));
            var $btn = $('#btn_main_save');
            $btn.find('.indicator-label').addClass('d-none');
            $btn.find('.indicator-progress').removeClass('d-none');
            $btn.prop('disabled', true);
        });

        // ─────────────────────────────────────────────────────────────
        // Toast
        // ─────────────────────────────────────────────────────────────
        function swToast(msg, type) {
            var bg = (type === 'error') ? 'bg-danger' : 'bg-success';
            var $t = $('<div class="toast align-items-center text-white border-0 mb-2 show" role="alert" style="min-width:260px;display:block!important">')
                .addClass(bg)
                .html('<div class="d-flex"><div class="toast-body fw-semibold">' + msg + '</div>'
                    + '<button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="$(this).closest(\'.toast\').remove()"></button></div>');
            $('#sw-toast-container').append($t);
            try {
                if (window.bootstrap && bootstrap.Toast) {
                    new bootstrap.Toast($t[0], { delay: 4000 }).show();
                    $t[0].addEventListener('hidden.bs.toast', function() { $t.remove(); });
                    return;
                }
            } catch(e) {}
            setTimeout(function() { $t.fadeOut(400, function() { $t.remove(); }); }, 4000);
        }

        function swBtnLoading($btn, on) {
            $btn.find('.indicator-label').toggleClass('d-none', on);
            $btn.find('.indicator-progress').toggleClass('d-none', !on);
            $btn.prop('disabled', on);
        }

        function swModalHide(id) {
            var el = document.getElementById(id);
            if (!el) return;
            try { if (window.bootstrap && bootstrap.Modal) { bootstrap.Modal.getOrCreateInstance(el).hide(); return; } } catch(e) {}
            $(el).modal('hide');
        }

        function swModalShow(id) {
            var el = document.getElementById(id);
            if (!el) return;
            try { if (window.bootstrap && bootstrap.Modal) { bootstrap.Modal.getOrCreateInstance(el).show(); return; } } catch(e) {}
            $(el).modal('show');
        }

        // ─────────────────────────────────────────────────────────────
        // Client-side Draft State
        // ─────────────────────────────────────────────────────────────
        var SW_DRAFT = {
            products: @json($initProducts),
            groups:   @json($initGroups)
        };

        var SW_ALL_PRODUCTS   = @json($productsForJs);
        var SW_ALL_ACTIVITIES = @json($activitiesForJs);
        var SW_CATS = @json($storeCategories->pluck('name', 'id'));

        // ─────────────────────────────────────────────────────────────
        // PRODUCTS — checkbox list (like Activities tab)
        // ─────────────────────────────────────────────────────────────
        function swUpdateProductBadge() {
            $('#badge_products').text(SW_DRAFT.products.length);
        }

        // Product checkbox toggled
        $(document).on('change', '.product-check', function() {
            var $item      = $(this).closest('.product-item');
            var productId  = parseInt($item.data('product-id'));
            var productName = String($item.data('product-name') || '');
            var $rToggle   = $item.find('.replaceable-toggle');

            if (this.checked) {
                $rToggle.show();
                var existingId  = $item.data('existing-id');
                var existingRep = $item.data('existing-replaceable') == '1';
                $item.find('.product-replaceable').prop('checked', existingRep);
                SW_DRAFT.products.push({
                    id:             existingId ? parseInt(existingId) : null,
                    product_id:     productId,
                    product_name:   productName,
                    list_order:     SW_DRAFT.products.length,
                    is_replaceable: existingRep,
                });
            } else {
                $rToggle.hide();
                $item.find('.product-replaceable').prop('checked', false);
                SW_DRAFT.products = SW_DRAFT.products.filter(function(p) { return p.product_id !== productId; });
            }
            swUpdateProductBadge();
        });

        // Replaceable checkbox toggled
        $(document).on('change', '.product-replaceable', function() {
            var productId  = parseInt($(this).closest('.product-item').data('product-id'));
            var checked    = this.checked;
            $.each(SW_DRAFT.products, function(i, p) {
                if (p.product_id === productId) { SW_DRAFT.products[i].is_replaceable = checked; return false; }
            });
        });

        swUpdateProductBadge();

        // ─────────────────────────────────────────────────────────────
        // Subscription category select2 with image thumbnails
        // ─────────────────────────────────────────────────────────────
        if (typeof $.fn.select2 !== 'undefined' && $('#sub_cat_select').length) {
            function formatSubCat(opt) {
                if (!opt.id) return opt.text;
                var img = $(opt.element).data('image');
                if (img) {
                    return $('<span class="d-flex align-items-center gap-2">' +
                        '<img src="' + img + '" style="width:26px;height:26px;border-radius:5px;object-fit:cover;flex-shrink:0;">' +
                        '<span>' + opt.text + '</span></span>');
                }
                return opt.text;
            }
            $('#sub_cat_select').select2({
                templateResult:    formatSubCat,
                templateSelection: formatSubCat,
                minimumResultsForSearch: 6,
                allowClear: true,
                placeholder: '{{ trans('sw.select_category') }}',
            });
        }

        // ─────────────────────────────────────────────────────────────
        // ACTIVITIES — filter by text / trainer
        // ─────────────────────────────────────────────────────────────
        function swFilterActivities() {
            var term      = ($('#activities_search').val() || '').toLowerCase().trim();
            var trainerId = $('#activities_trainer_filter').val() || '';
            var visible   = 0;

            $('#activities_container .activity-item').each(function() {
                var $item = $(this);
                var matchesText    = !term || String($item.data('name') || '').toLowerCase().indexOf(term) !== -1;
                var matchesTrainer = !trainerId || String($item.data('trainer-id') || '') === String(trainerId);
                var show = matchesText && matchesTrainer;
                // Bootstrap's d-flex utility is !important, so a plain inline
                // display:none can't hide it — toggle the utility classes instead.
                $item.toggleClass('d-flex', show).toggleClass('d-none', !show);
                if (show) visible++;
            });

            $('#activities_no_match').toggleClass('d-none', visible > 0);
        }
        $(document).on('keyup input', '#activities_search', swFilterActivities);
        $(document).on('change', '#activities_trainer_filter', swFilterActivities);

        // ─────────────────────────────────────────────────────────────
        // PRODUCTS — filter by text
        // ─────────────────────────────────────────────────────────────
        function swFilterProducts() {
            var term    = ($('#products_search').val() || '').toLowerCase().trim();
            var visible = 0;

            $('.products-scrollable .product-item').each(function() {
                var $item = $(this);
                var show = !term || String($item.data('product-name') || '').toLowerCase().indexOf(term) !== -1;
                // Bootstrap's d-flex utility is !important, so a plain inline
                // display:none can't hide it — toggle the utility classes instead.
                $item.toggleClass('d-flex', show).toggleClass('d-none', !show);
                if (show) visible++;
            });

            $('#products_no_match').toggleClass('d-none', visible > 0);
        }
        $(document).on('keyup input', '#products_search', swFilterProducts);

        // Pressing Enter in these filter inputs should never submit the
        // main subscription form — they're just live filters, not fields.
        $(document).on('keydown', '#activities_search, #products_search', function(e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
            }
        });

        // ─────────────────────────────────────────────────────────────
        // OPTION GROUPS — render from SW_DRAFT.groups
        // ─────────────────────────────────────────────────────────────
        var SW_PROD_MAP = {};
        $.each(SW_ALL_PRODUCTS,   function(_, p) { SW_PROD_MAP[p.id] = p; });
        var SW_ACT_MAP = {};
        $.each(SW_ALL_ACTIVITIES, function(_, a) { SW_ACT_MAP[a.id]  = a; });;

        var swEditGroupIdx   = -1;   // -1 = adding new
        var swEditOptGrpIdx  = -1;
        var swEditOptIdx     = -1;   // -1 = adding new

        function swPopulateProductSelect(categoryId) {
            var $sel     = $('#modal_option_product_id');
            var filtered = categoryId
                ? $.grep(SW_ALL_PRODUCTS, function(p) { return p.category_id == categoryId; })
                : SW_ALL_PRODUCTS;
            $sel.empty().append($('<option>').val('').text('— {{ trans("sw.select_product") }} —'));
            $.each(filtered, function(_, p) {
                var displayName = p.display_name || p.name || '';
                var label = (displayName && displayName !== p.name && p.name)
                    ? displayName + ' (' + p.name + ')'
                    : (displayName || p.name || ('#' + p.id));
                $sel.append($('<option>').val(p.id).text(label));
            });
            $('#modal_option_category_hint').toggleClass('d-none', !categoryId);
            $('#modal_option_no_products_alert').toggleClass('d-none', filtered.length > 0);
        }

        function swOptImageHtml(opt) {
            if (opt.is_text) {
                return '<div class="sw-opt-thumb-ph"><i class="bi bi-input-cursor-text text-warning"></i></div>';
            }
            var img = opt.item_image || null;
            if (!img) {
                var item = opt.product_id ? SW_PROD_MAP[opt.product_id] : (opt.activity_id ? SW_ACT_MAP[opt.activity_id] : null);
                if (item) img = item.image;
            }
            var icon = opt.activity_id ? 'bi-list-check' : 'bi-box';
            if (!img) return '<div class="sw-opt-thumb-ph"><i class="bi ' + icon + '"></i></div>';
            return '<img src="' + img + '" class="sw-opt-thumb" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\'" />'
                 + '<div class="sw-opt-thumb-ph" style="display:none"><i class="bi ' + icon + '"></i></div>';
        }

        function swRenderGroups() {
            var $c = $('#groups_list_container');
            var data = SW_DRAFT.groups;
            $('#badge_groups').text(data.length);

            if (data.length === 0) {
                $c.html('<div class="text-center text-muted py-10"><i class="bi bi-sliders fs-1 d-block mb-2 opacity-25"></i><span>{{ trans("sw.no_option_groups_added") }}</span></div>');
                return;
            }

            $c.empty();
            $.each(data, function(i, group) {
                var srcBadge  = group.source_type === 'activity'
                    ? '<span class="badge badge-light-success ms-2"><i class="bi bi-list-check me-1"></i>{{ trans("sw.activities") }}</span>'
                    : (group.source_type === 'text'
                        ? '<span class="badge badge-light-warning ms-2"><i class="bi bi-input-cursor-text me-1"></i>{{ trans("sw.text_input") }}</span>'
                        : '<span class="badge badge-light-info ms-2"><i class="bi bi-box-seam me-1"></i>{{ trans("sw.products") }}</span>');
                var typeBadge = group.selection_type === 'single'
                    ? '<span class="badge badge-light-primary ms-2">{{ trans("sw.single") }}</span>'
                    : '<span class="badge badge-light-success ms-2">{{ trans("sw.multiple") }}</span>';
                var reqBadge = group.is_required ? '<span class="badge badge-light-warning ms-1">{{ trans("sw.mandatory") }}</span>' : '';
                var catBadge = '';
                if (group.category_id && SW_CATS[group.category_id]) {
                    catBadge = '<span class="badge badge-light-warning ms-1"><i class="bi bi-tags me-1"></i>' + $('<span>').text(SW_CATS[group.category_id]).html() + '</span>';
                }

                var $card   = $('<div class="sw-group-card">').attr('data-group-idx', i);
                var $header = $('<div class="card-header">');
                var $title  = $('<div class="fw-bold fs-6 flex-grow-1">');
                $title.append($('<span>').text(group.name_ar));
                $title.append('<span class="text-muted mx-2">/</span>');
                $title.append($('<span>').text(group.name_en || group.name_ar));
                $title.append(srcBadge + typeBadge + reqBadge + catBadge);
                $header.append($title);

                var $actions = $('<div class="d-flex gap-2 ms-auto flex-shrink-0">');
                $actions.append(
                    $('<button type="button" class="btn btn-sm btn-light-primary btn-open-add-option">')
                        .attr('data-group-idx', i).attr('data-category-id', group.category_id || '')
                        .html('<i class="bi bi-plus me-1"></i>{{ trans("sw.add_option") }}')
                );
                $actions.append(
                    $('<button type="button" class="btn btn-sm btn-icon btn-light-warning btn-edit-group">')
                        .attr('data-group-idx', i).html('<i class="bi bi-pencil"></i>')
                );
                $actions.append(
                    $('<button type="button" class="btn btn-sm btn-icon btn-light-danger btn-remove-group">')
                        .attr('data-group-idx', i).html('<i class="bi bi-trash"></i>')
                );
                $header.append($actions);
                $card.append($header);

                var $body = $('<div class="card-body p-0">');
                var options = group.options || [];
                if (options.length === 0) {
                    $body.html('<div class="text-muted text-center py-4 fs-7"><i class="bi bi-tag me-1"></i>{{ trans("sw.no_options_yet") }}</div>');
                } else {
                    var $tbl = $('<table class="table table-sm table-row-dashed align-middle mb-0">');
                    $tbl.append('<thead><tr class="text-muted fs-8 text-uppercase">'
                        + '<th class="ps-3" style="width:50px"></th>'
                        + '<th>{{ trans("sw.product") }}</th>'
                        + '<th style="width:130px">{{ trans("sw.price_modifier") }}</th>'
                        + '<th style="width:80px"></th></tr></thead>');
                    var $tbody2 = $('<tbody>');
                    $.each(options, function(j, opt) {
                        var price = parseFloat(opt.price_modifier || 0);
                        var $otr  = $('<tr>');
                        $otr.append($('<td class="ps-3">').html(swOptImageHtml(opt)));
                        var itemName = opt.is_text
                            ? ((opt.item_name_ar || opt.item_name || '') + (opt.item_name_en && opt.item_name_en !== opt.item_name_ar ? ' / ' + opt.item_name_en : ''))
                            : (opt.item_name || opt.product_name || (opt.product_id ? '#' + opt.product_id : (opt.activity_id ? '#' + opt.activity_id : '?')));
                        var $nameTd = $('<td class="fw-semibold">').text(itemName);
                        $otr.append($nameTd);
                        var $priceTd = $('<td>');
                        if (price > 0)      $priceTd.html('<span class="badge badge-light-success fw-bold">+' + price.toFixed(2) + '</span>');
                        else if (price < 0) $priceTd.html('<span class="badge badge-light-danger fw-bold">' + price.toFixed(2) + '</span>');
                        else                $priceTd.html('<span class="badge badge-light-secondary">0.00</span>');
                        $otr.append($priceTd);
                        $otr.append($('<td class="text-end pe-3">').html(
                            '<div class="d-flex gap-1 justify-content-end">'
                            + '<button type="button" class="btn btn-sm btn-icon btn-light-warning btn-edit-option"'
                            + ' data-group-idx="' + i + '" data-opt-idx="' + j + '"><i class="bi bi-pencil fs-7"></i></button>'
                            + '<button type="button" class="btn btn-sm btn-icon btn-light-danger btn-remove-option"'
                            + ' data-group-idx="' + i + '" data-opt-idx="' + j + '"><i class="bi bi-trash fs-7"></i></button>'
                            + '</div>'
                        ));
                        $tbody2.append($otr);
                    });
                    $tbl.append($tbody2);
                    $body.append($tbl);
                }
                $card.append($body);
                $c.append($card);
            });
        }

        function swGroupSourceType() {
            return $('input[name="modal_group_source_type"]:checked').val() || 'product';
        }

        $('input[name="modal_group_source_type"]').on('change', function() {
            var val = $(this).val();
            $('#modal_group_category_row').toggleClass('d-none', val !== 'product');
        });

        function swOpenGroupModal(editIdx) {
            swEditGroupIdx = (editIdx >= 0) ? editIdx : -1;
            var group = swEditGroupIdx >= 0 ? SW_DRAFT.groups[swEditGroupIdx] : null;
            $('#modalAddGroup .modal-title').html(
                group ? '<i class="bi bi-pencil me-2"></i>{{ trans("sw.edit_option_group") }}'
                       : '<i class="bi bi-sliders me-2"></i>{{ trans("sw.add_option_group") }}'
            );
            var srcType = group ? (group.source_type || 'product') : 'product';
            $('input[name="modal_group_source_type"][value="' + srcType + '"]').prop('checked', true);
            $('#modal_group_category_row').toggleClass('d-none', srcType !== 'product');
            $('#modal_group_name_ar').val(group ? group.name_ar : '');
            $('#modal_group_name_en').val(group ? (group.name_en || '') : '');
            $('#modal_group_selection_type').val(group ? (group.selection_type || 'single') : 'single');
            $('#modal_group_required').prop('checked', group ? !!group.is_required : false);
            $('#modal_group_is_system').prop('checked', group ? group.is_system !== false : true);
            $('#modal_group_is_web').prop('checked',   group ? group.is_web    !== false : true);
            $('#modal_group_is_mobile').prop('checked',group ? group.is_mobile !== false : true);
            if ($('#modal_group_category_id').length) $('#modal_group_category_id').val(group ? (group.category_id || '') : '');
            swModalShow('modalAddGroup');
        }

        $('#btn_open_add_group').on('click',            function() { swOpenGroupModal(-1); });
        $(document).on('click', '.btn-edit-group',      function() { swOpenGroupModal(parseInt($(this).data('group-idx'))); });

        $(document).on('click', '#btn_modal_save_group', function() {
            var nameAr = $('#modal_group_name_ar').val().trim();
            if (!nameAr) { swToast('{{ trans("sw.name_required") }}', 'error'); return; }
            var srcType = swGroupSourceType();
            var gdata = {
                name_ar:        nameAr,
                name_en:        $('#modal_group_name_en').val().trim() || nameAr,
                source_type:    srcType,
                selection_type: $('#modal_group_selection_type').val(),
                is_required:    $('#modal_group_required').is(':checked'),
                is_system:      $('#modal_group_is_system').is(':checked'),
                is_web:         $('#modal_group_is_web').is(':checked'),
                is_mobile:      $('#modal_group_is_mobile').is(':checked'),
                category_id:    srcType === 'product' ? ($('#modal_group_category_id').val() || null) : null,
            };
            if (swEditGroupIdx >= 0) {
                SW_DRAFT.groups[swEditGroupIdx] = $.extend({}, SW_DRAFT.groups[swEditGroupIdx], gdata);
            } else {
                gdata.id = null; gdata.list_order = SW_DRAFT.groups.length; gdata.options = [];
                SW_DRAFT.groups.push(gdata);
            }
            swRenderGroups();
            swModalHide('modalAddGroup');
            swToast('{{ trans("global.save") }}');
        });

        $(document).on('click', '.btn-remove-group', function() {
            if (!confirm('{{ trans("sw.confirm_delete") }}')) return;
            SW_DRAFT.groups.splice(parseInt($(this).data('group-idx')), 1);
            swRenderGroups();
        });

        function swPopulateActivitySelect() {
            var $sel = $('#modal_option_activity_id');
            $sel.empty().append($('<option>').val('').text('— {{ trans("sw.select_activity") }} —'));
            $.each(SW_ALL_ACTIVITIES, function(_, a) {
                $sel.append($('<option>').val(a.id).text(a.name));
            });
        }

        function swOpenOptionModal(groupIdx, editOptIdx) {
            swEditOptGrpIdx = groupIdx;
            swEditOptIdx    = (editOptIdx >= 0) ? editOptIdx : -1;
            var group      = SW_DRAFT.groups[groupIdx];
            var opt        = swEditOptIdx >= 0 ? (group && group.options ? group.options[swEditOptIdx] : null) : null;
            var srcType    = group ? (group.source_type || 'product') : 'product';
            var isActivity = srcType === 'activity';
            var isText     = srcType === 'text';
            $('#modalAddOption .modal-title').html(
                opt ? '<i class="bi bi-pencil me-2"></i>{{ trans("sw.edit_option") }}'
                     : '<i class="bi bi-tag me-2"></i>{{ trans("sw.add_option") }}'
            );
            $('#modal_option_product_section').toggleClass('d-none', isActivity || isText);
            $('#modal_option_activity_section').toggleClass('d-none', !isActivity);
            $('#modal_option_text_section').toggleClass('d-none', !isText);
            if (isActivity) {
                swPopulateActivitySelect();
                $('#modal_option_activity_id').val(opt ? (opt.activity_id || '') : '');
            } else if (isText) {
                $('#modal_option_text_name_ar').val(opt ? (opt.item_name_ar || '') : '');
                $('#modal_option_text_name_en').val(opt ? (opt.item_name_en || '') : '');
            } else {
                swPopulateProductSelect(group ? (group.category_id || '') : '');
                $('#modal_option_product_id').val(opt ? (opt.product_id || '') : '');
            }
            $('#modal_option_price').val(opt ? (opt.price_modifier || 0) : 0);
            $('#modal_option_order').val(opt ? (opt.list_order || 0) : (group && group.options ? group.options.length : 0));
            swModalShow('modalAddOption');
        }

        $(document).on('click', '.btn-open-add-option', function() { swOpenOptionModal(parseInt($(this).data('group-idx')), -1); });
        $(document).on('click', '.btn-edit-option',     function() { swOpenOptionModal(parseInt($(this).data('group-idx')), parseInt($(this).data('opt-idx'))); });

        $(document).on('click', '#btn_modal_save_option', function() {
            var group      = SW_DRAFT.groups[swEditOptGrpIdx];
            if (!group) return;
            var srcType    = group.source_type || 'product';
            var isActivity = srcType === 'activity';
            var isText     = srcType === 'text';
            var odata;
            if (isActivity) {
                var actId = parseInt($('#modal_option_activity_id').val());
                if (!actId) { swToast('{{ trans("sw.select_activity") }}', 'error'); return; }
                var act   = SW_ACT_MAP[actId] || {};
                odata = {
                    is_text:       false,
                    product_id:    null,
                    activity_id:   actId,
                    item_name:     act.name || ('#' + actId),
                    item_name_ar:  null,
                    item_name_en:  null,
                    item_image:    act.image || null,
                    price_modifier: parseFloat($('#modal_option_price').val()) || 0,
                    list_order:    parseInt($('#modal_option_order').val()) || 0,
                };
            } else if (isText) {
                var nameAr = $('#modal_option_text_name_ar').val().trim();
                if (!nameAr) { swToast('{{ trans("sw.name_required") }}', 'error'); return; }
                var nameEn = $('#modal_option_text_name_en').val().trim() || nameAr;
                odata = {
                    is_text:       true,
                    product_id:    null,
                    activity_id:   null,
                    item_name:     nameAr,
                    item_name_ar:  nameAr,
                    item_name_en:  nameEn,
                    item_image:    null,
                    price_modifier: parseFloat($('#modal_option_price').val()) || 0,
                    list_order:    parseInt($('#modal_option_order').val()) || 0,
                };
            } else {
                var $sel = $('#modal_option_product_id');
                var pId  = parseInt($sel.val());
                if (!pId) { swToast('{{ trans("sw.select_product") }}', 'error'); return; }
                var prod  = SW_PROD_MAP[pId] || {};
                odata = {
                    is_text:       false,
                    product_id:    pId,
                    activity_id:   null,
                    item_name:     $sel.find('option:selected').text().trim(),
                    item_name_ar:  null,
                    item_name_en:  null,
                    item_image:    prod.image || null,
                    price_modifier: parseFloat($('#modal_option_price').val()) || 0,
                    list_order:    parseInt($('#modal_option_order').val()) || 0,
                };
            }
            group.options = group.options || [];
            if (swEditOptIdx >= 0) {
                group.options[swEditOptIdx] = $.extend({}, group.options[swEditOptIdx], odata);
            } else {
                odata.id = null;
                if (!odata.list_order) odata.list_order = group.options.length;
                group.options.push(odata);
            }
            swRenderGroups();
            swModalHide('modalAddOption');
            swToast('{{ trans("global.save") }}');
        });

        $(document).on('click', '.btn-remove-option', function() {
            if (!confirm('{{ trans("sw.confirm_delete") }}')) return;
            var gIdx = parseInt($(this).data('group-idx'));
            var oIdx = parseInt($(this).data('opt-idx'));
            if (SW_DRAFT.groups[gIdx]) { SW_DRAFT.groups[gIdx].options.splice(oIdx, 1); swRenderGroups(); }
        });

        // Initial render
        swRenderGroups();

        // ─────────────────────────────────────────────────────────────
        // Tab fallback: init Bootstrap 3 tabs if BS5 isn't available
        // ─────────────────────────────────────────────────────────────
        if (!(window.bootstrap && bootstrap.Tab)) {
            $('#subscriptionTabs button').on('click', function(e) {
                e.preventDefault();
                var target = $(this).data('bsTarget') || $(this).attr('data-bs-target');
                $('#subscriptionTabs button').removeClass('active');
                $(this).addClass('active');
                $('.tab-pane').removeClass('show active');
                $(target).addClass('show active');
            });
        }

    });
    </script>
@endsection

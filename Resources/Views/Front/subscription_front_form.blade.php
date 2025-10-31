@extends('software::layouts.form')
@section('breadcrumb')
    <!--begin::Breadcrumb-->
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
        <!--begin::Item-->
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.dashboard') }}" class="text-muted text-hover-primary">{{ trans('sw.home') }}</a>
        </li>
        <!--end::Item-->
        <!--begin::Item-->
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-300 w-5px h-2px"></span>
        </li>
        <!--end::Item-->
        <!--begin::Item-->
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.listSubscription') }}" class="text-muted text-hover-primary">{{ trans('sw.memberships')}}</a>
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
@section('form_title') {{ @$title }} @endsection
@section('page_body')
    <!--begin::Subscription Form-->
    <form method="post" action="" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
        {{csrf_field()}}
        
        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            
            <!--begin::Basic Information Section-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2><i class="bi bi-info-circle me-2"></i>{{ trans('sw.basic_information')}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                   
                    <div class="row g-5">
                        <!--begin::Name in Arabic-->
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="required form-label">{{ trans('sw.name_in_arabic')}}</label>
                                <input type="text" name="name_ar" class="form-control" 
                                       placeholder="{{ trans('sw.enter_name_in_arabic')}}" 
                                       value="{{ old('name_ar', $subscription->name_ar) }}" 
                                       id="name_ar" required />
                            </div>
                        </div>
                        <!--end::Name in Arabic-->
                        
                        <!--begin::Name in English-->
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="required form-label">{{ trans('sw.name_in_english')}}</label>
                                <input type="text" name="name_en" class="form-control" 
                                       placeholder="{{ trans('sw.enter_name_in_english')}}" 
                                       value="{{ old('name_en', $subscription->name_en) }}" 
                                       id="name_en" required />
                            </div>
                        </div>
                        <!--end::Name in English-->
                        
                        <!--begin::Content in Arabic-->
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="form-label">{{ trans('sw.content_in_arabic')}}</label>
                                <textarea name="content_ar" class="form-control" rows="3"
                                          placeholder="{{ trans('sw.enter_content_in_arabic')}}">{{ old('content_ar', $subscription->content_ar) }}</textarea>
                            </div>
                        </div>
                        <!--end::Content in Arabic-->
                        
                        <!--begin::Content in English-->
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="form-label">{{ trans('sw.content_in_english')}}</label>
                                <textarea name="content_en" class="form-control" rows="3"
                                          placeholder="{{ trans('sw.enter_content_in_english')}}">{{ old('content_en', $subscription->content_en) }}</textarea>
                            </div>
                        </div>
                        <!--end::Content in English-->
                        
                        <!--begin::Category-->
                        <div class="col-md-12">
                            <div class="fv-row">
                                <label class="form-label">{{ trans('sw.category')}}</label>
                                <select name="category_id" class="form-select" data-control="select2" data-placeholder="{{ trans('sw.select_category')}}">
                                    <option value="">{{ trans('sw.select_category')}}</option>
                                    @if(@$categories)
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $subscription->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <!--end::Category-->
                        
                        @if(@$mainSettings->active_mobile)
                        <!--begin::Image Upload-->
                        <div class="col-md-12">
                            <div class="fv-row">
                                <label class="form-label">{{ trans('sw.upload_image')}}</label>
                                <input type="file" name="image" class="form-control mb-3" id="gym_image" accept="image/*" />
                                <div class="text-center">
                                    <label for="gym_image" style="cursor: pointer;">
                                        <img id="preview" src="{{ @$subscription->image ?? 'https://gymmawy.com/resources/assets/front/img/blank-image.svg' }}"
                                             style="height: 160px;width: 100%;max-width: 400px;object-fit: contain;border: 2px dashed #c2cad8;border-radius: 8px;padding: 10px;"
                                             alt="preview image"/>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <!--end::Image Upload-->
                        @endif
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Basic Information Section-->
            
            <!--begin::Pricing & Duration Section-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2><i class="bi bi-currency-dollar me-2"></i>{{ trans('sw.pricing_and_duration')}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <div class="row g-5">
                        <!--begin::Price-->
                        <div class="col-md-4">
                            <div class="fv-row">
                                <label class="required form-label">{{ trans('sw.price')}}</label>
                                <input type="number" name="price" class="form-control" 
                                       placeholder="{{ trans('sw.enter_price')}}" 
                                       value="{{ old('price', $subscription->price) }}" 
                                       step="0.01" min="0" required />
                            </div>
                        </div>
                        <!--end::Price-->
                        
                        <!--begin::Discount-->
                        <div class="col-md-4">
                            <div class="fv-row">
                                <label class="form-label">{{ trans('sw.discount')}}</label>
                                <input type="number" name="discount" class="form-control" 
                                       placeholder="{{ trans('sw.enter_discount')}}" 
                                       value="{{ old('discount', $subscription->discount) }}" 
                                       step="0.01" min="0" />
                            </div>
                        </div>
                        <!--end::Discount-->
                        
                        <!--begin::Period-->
                        <div class="col-md-4">
                            <div class="fv-row">
                                <label class="required form-label">{{ trans('sw.period')}} ({{ trans('sw.days')}})</label>
                                <input type="number" name="period" class="form-control" 
                                       placeholder="{{ trans('sw.enter_period')}}" 
                                       value="{{ old('period', $subscription->period) }}" 
                                       min="1" required />
                            </div>
                        </div>
                        <!--end::Period-->
                        
                        <!--begin::Default Discount Value-->
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="form-label">{{ trans('sw.default_discount_value')}}</label>
                                <input type="number" name="default_discount_value" class="form-control" 
                                       placeholder="{{ trans('sw.enter_default_discount_value')}}" 
                                       value="{{ old('default_discount_value', $subscription->default_discount_value) }}" 
                                       step="0.01" min="0" />
                            </div>
                        </div>
                        <!--end::Default Discount Value-->
                        
                        <!--begin::Default Discount Type-->
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
                        <!--end::Default Discount Type-->
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Pricing & Duration Section-->
            
            <!--begin::Workout Settings Section-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2><i class="bi bi-activity me-2"></i>{{ trans('sw.workout_settings')}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <div class="row g-5">
                        <!--begin::Workouts-->
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="form-label">{{ trans('sw.workouts')}}</label>
                                <input type="number" name="workouts" class="form-control" 
                                       placeholder="{{ trans('sw.enter_workouts')}}" 
                                       value="{{ old('workouts', $subscription->workouts) }}" 
                                       min="0" />
                            </div>
                        </div>
                        <!--end::Workouts-->
                        
                        <!--begin::Workouts Per Day-->
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="form-label">{{ trans('sw.workouts_per_day')}}</label>
                                <input type="number" name="workouts_per_day" id="workouts_per_day" class="form-control" 
                                       placeholder="{{ trans('sw.enter_workouts_per_day')}}" 
                                       value="{{ old('workouts_per_day', $subscription->workouts_per_day) }}" 
                                       min="0" {{ old('workouts_per_day', $subscription->workouts_per_day) ? '' : 'disabled' }} />
                            </div>
                        </div>
                        <!--end::Workouts Per Day-->
                        
                        <!--begin::Enable Workouts Per Day Checkbox-->
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
                        <!--end::Enable Workouts Per Day Checkbox-->
                        
                        <!--begin::Start Time Day-->
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="form-label">{{ trans('sw.start_time_day')}}</label>
                                <input type="time" name="start_time_day" id="start_time_day" class="form-control" 
                                       value="{{ old('start_time_day', $subscription->start_time_day) }}" 
                                       {{ old('start_time_day', $subscription->start_time_day) ? '' : 'disabled' }} />
                            </div>
                        </div>
                        <!--end::Start Time Day-->
                        
                        <!--begin::End Time Day-->
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="form-label">{{ trans('sw.end_time_day')}}</label>
                                <input type="time" name="end_time_day" id="end_time_day" class="form-control" 
                                       value="{{ old('end_time_day', $subscription->end_time_day) }}" 
                                       {{ old('end_time_day', $subscription->end_time_day) ? '' : 'disabled' }} />
                            </div>
                        </div>
                        <!--end::End Time Day-->
                        
                        <!--begin::Enable Time Day Checkbox-->
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
                        <!--end::Enable Time Day Checkbox-->
                        
                        <!--begin::Week Days Selection-->
                        <div class="col-md-12">
                            <div class="fv-row">
                                <label class="form-label">{{ trans('sw.available_days')}}</label>
                                <div class="d-flex flex-wrap gap-3 mt-3" id="week_days_container">
                                    @php
                                        $weekDays = [
                                            'saturday' => trans('sw.saturday'),
                                            'sunday' => trans('sw.sunday'),
                                            'monday' => trans('sw.monday'),
                                            'tuesday' => trans('sw.tuesday'),
                                            'wednesday' => trans('sw.wednesday'),
                                            'thursday' => trans('sw.thursday'),
                                            'friday' => trans('sw.friday'),
                                        ];
                                        $selectedDays = old('time_week', $subscription->time_week ? json_decode($subscription->time_week, true) : []);
                                    @endphp
                                    @foreach($weekDays as $key => $day)
                                        <div class="form-check form-check-custom form-check-solid day-checkbox-item">
                                            <input class="form-check-input week-day-check" type="checkbox" 
                                                   name="time_week[]" value="{{ $key }}" 
                                                   id="day_{{ $key }}" 
                                                   {{ in_array($key, (array)$selectedDays) ? 'checked' : '' }}
                                                   {{ $selectedDays ? '' : 'disabled' }} />
                                            <label class="form-check-label fw-bold" for="day_{{ $key }}">
                                                {{ $day }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <!--end::Week Days Selection-->
                        
                        <!--begin::Enable Week Days Checkbox-->
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
                        <!--end::Enable Week Days Checkbox-->
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Workout Settings Section-->
            
            <!--begin::Activities Section-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2><i class="bi bi-list-check me-2"></i>{{ trans('sw.activities')}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <div id="activities_container">
                        @if(@$activities && count($activities) > 0)
                            @foreach($activities as $activity)
                                @php
                                    $activitySubscription = $subscription->activities->where('activity_id', $activity->id)->first();
                                    $trainingTimes = $activitySubscription ? $activitySubscription->training_times : 0;
                                @endphp
                                <div class="d-flex align-items-center mb-4 p-4 bg-light rounded activity-item">
                                    <div class="form-check form-check-custom form-check-solid me-4">
                                        <input class="form-check-input activity-check" type="checkbox" 
                                               id="activity_{{ $activity->id }}" 
                                               {{ $trainingTimes > 0 ? 'checked' : '' }} />
                                        <label class="form-check-label fw-bold" for="activity_{{ $activity->id }}">
                                            {{ $activity->name }}
                                        </label>
                                    </div>
                                    <div class="flex-grow-1">
                                        <input type="number" class="form-control training-times" 
                                               name="activities[{{ $activity->id }}]" 
                                               value="{{$trainingTimes > 0  ? $trainingTimes : ''}}" 
                                               placeholder="{{ trans('sw.training_times')}}" 
                                               min="0"
                                               {{ $trainingTimes > 0 ? '' : 'disabled' }} />
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-info">
                                {{ trans('sw.no_activities_available')}}
                            </div>
                        @endif
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Activities Section-->
            
            <!--begin::Freeze Settings Section-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
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
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!-- Hidden instruction box: shown on demand -->
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
                        <!--begin::Freeze Limit-->
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="form-label">{{ trans('sw.freeze_limit')}} ({{ trans('sw.days')}})
                                    <a href="#" class="ms-1 text-muted small freeze-def-toggle" data-target="#def_freeze_limit"><i class="bi bi-info-circle"></i></a>
                                </label>
                                <input type="number" name="freeze_limit" class="form-control" 
                                       placeholder="{{ trans('sw.enter_freeze_limit')}}" 
                                       value="{{ old('freeze_limit', $subscription->freeze_limit ?? 0) }}" 
                                       min="0" />
                                <div id="def_freeze_limit" class="form-text d-none">{{ trans('sw.field_help_freeze_limit') }}</div>
                            </div>
                        </div>
                        <!--end::Freeze Limit-->
                        
                        <!--begin::Number Times Freeze-->
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="form-label">{{ trans('sw.number_times_freeze')}}
                                    <a href="#" class="ms-1 text-muted small freeze-def-toggle" data-target="#def_number_times_freeze"><i class="bi bi-info-circle"></i></a>
                                </label>
                                <input type="number" name="number_times_freeze" class="form-control" 
                                       placeholder="{{ trans('sw.enter_number_times_freeze')}}" 
                                       value="{{ old('number_times_freeze', $subscription->number_times_freeze ?? 0) }}" 
                                       min="0" />
                                <div id="def_number_times_freeze" class="form-text d-none">{{ trans('sw.field_help_number_times_freeze') }}</div>
                            </div>
                        </div>
                        <!--end::Number Times Freeze-->
                        
                        <!--begin::Max Extension Days-->
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="form-label">{{ trans('sw.max_extension_days') }}
                                    <a href="#" class="ms-1 text-muted small freeze-def-toggle" data-target="#def_max_extension_days"><i class="bi bi-info-circle"></i></a>
                                </label>
                                <input type="number" name="max_extension_days" class="form-control" 
                                       placeholder="{{ trans('sw.enter_max_extension_days') }}" 
                                       value="{{ old('max_extension_days', $subscription->max_extension_days ?? 0) }}" 
                                       min="0" />
                                <div id="def_max_extension_days" class="form-text d-none">{{ trans('sw.field_help_max_extension_days') }}</div>
                            </div>
                        </div>
                        <!--end::Max Extension Days-->
                        
                        <!--begin::Max Sum of Freeze and Extension-->
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="form-label">{{ trans('sw.max_freeze_extension_sum') }}
                                    <a href="#" class="ms-1 text-muted small freeze-def-toggle" data-target="#def_max_freeze_extension_sum"><i class="bi bi-info-circle"></i></a>
                                </label>
                                <input type="number" name="max_freeze_extension_sum" class="form-control" 
                                       placeholder="{{ trans('sw.enter_max_freeze_extension_sum') }}" 
                                       value="{{ old('max_freeze_extension_sum', $subscription->max_freeze_extension_sum ?? 0) }}" 
                                       min="0" />
                                <div id="def_max_freeze_extension_sum" class="form-text d-none">{{ trans('sw.field_help_max_freeze_extension_sum') }}</div>
                            </div>
                        </div>
                        <!--end::Max Sum of Freeze and Extension-->
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Freeze Settings Section-->
            
            <!--begin::Additional Settings Section-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2><i class="bi bi-gear me-2"></i>{{ trans('sw.additional_settings')}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <div class="row g-5">
                        <!--begin::Availability Checkboxes-->
                        <div class="col-md-12">
                            <label class="form-label fw-bold">{{ trans('sw.availability')}}</label>
                            <div class="d-flex flex-wrap gap-5 mt-3">
                                <!--begin::System Checkbox (first, default checked) -->
                                <div class="form-check form-check-custom form-check-solid form-check-lg">
                                    <input class="form-check-input" type="checkbox" name="is_system" 
                                           id="is_system" value="1" 
                                           {{ old('is_system', $subscription->is_system ?? 1) ? 'checked' : '' }} />
                                    <label class="form-check-label fw-bold" for="is_system">
                                        <i class="bi bi-shield-lock text-danger me-2"></i>{{ trans('sw.system')}}
                                    </label>
                                </div>
                                <!--end::System Checkbox-->

                                <!--begin::Web Checkbox-->
                                <div class="form-check form-check-custom form-check-solid form-check-lg">
                                    <input class="form-check-input" type="checkbox" name="is_web" 
                                           id="is_web" value="1" 
                                           {{ old('is_web', $subscription->is_web) ? 'checked' : '' }} />
                                    <label class="form-check-label fw-bold" for="is_web">
                                        <i class="bi bi-globe text-primary me-2"></i>{{ trans('sw.available_on_web')}}
                                    </label>
                                </div>
                                <!--end::Web Checkbox-->
                                
                                <!--begin::Mobile Checkbox-->
                                <div class="form-check form-check-custom form-check-solid form-check-lg">
                                    <input class="form-check-input" type="checkbox" name="is_mobile" 
                                           id="is_mobile" value="1" 
                                           {{ old('is_mobile', $subscription->is_mobile) ? 'checked' : '' }} />
                                    <label class="form-check-label fw-bold" for="is_mobile">
                                        <i class="bi bi-phone text-success me-2"></i>{{ trans('sw.available_on_mobile')}}
                                    </label>
                                </div>
                                <!--end::Mobile Checkbox-->
                                
                                <!--begin::Expire Changeable Checkbox-->
                                <div class="form-check form-check-custom form-check-solid form-check-lg">
                                    <input class="form-check-input" type="checkbox" name="is_expire_changeable" 
                                           id="is_expire_changeable" value="1" 
                                           {{ old('is_expire_changeable', $subscription->is_expire_changeable) ? 'checked' : '' }} />
                                    <label class="form-check-label fw-bold" for="is_expire_changeable">
                                        <i class="bi bi-calendar-check text-warning me-2"></i>{{ trans('sw.is_expire_changeable')}}
                                    </label>
                                </div>
                                <!--end::Expire Changeable Checkbox-->

                            </div>
                        </div>
                        <!--end::Availability Checkboxes-->
                        
                        <!--begin::Sound Active-->
                        <!-- <div class="col-md-6">
                            <div class="fv-row">
                                <label class="form-label">{{ trans('sw.sound_active')}}</label>
                                <input type="file" name="sound_active" class="form-control" accept="audio/*" />
                                @if(@$subscription->sound_active)
                                    <div class="mt-2">
                                        <audio controls style="width: 100%;">
                                            <source src="{{ asset(Modules\Software\Models\GymSubscription::$uploads_path . $subscription->sound_active) }}" type="audio/mpeg">
                                        </audio>
                                    </div>
                                @endif
                            </div>
                        </div> -->
                        <!--end::Sound Active-->
                        
                        <!--begin::Sound Expired-->
                        <!-- <div class="col-md-6">
                            <div class="fv-row">
                                <label class="form-label">{{ trans('sw.sound_expired')}}</label>
                                <input type="file" name="sound_expired" class="form-control" accept="audio/*" />
                                @if(@$subscription->sound_expired)
                                    <div class="mt-2">
                                        <audio controls style="width: 100%;">
                                            <source src="{{ asset(Modules\Software\Models\GymSubscription::$uploads_path . $subscription->sound_expired) }}" type="audio/mpeg">
                                        </audio>
                                    </div>
                                @endif
                            </div>
                        </div> -->
                        <!--end::Sound Expired-->
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Additional Settings Section-->
            
            <!--begin::Form Actions-->
            <div class="d-flex justify-content-end gap-3">
               
                <!--begin::Reset Button-->
                <button type="reset" class="btn btn-secondary">
                    {{ trans('admin.reset')}}
                </button>
                <!--end::Reset Button-->
                <!--begin::Submit Button-->
                <button type="submit" class="btn btn-primary">
                    <span class="indicator-label">{{ trans('global.save')}}</span>
                    <span class="indicator-progress">{{ trans('sw.please_wait')}}... 
                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
                <!--end::Submit Button-->
            </div>
            <!--end::Form Actions-->
        </div>
        <!--end::Main column-->
    </form>
    <!--end::Subscription Form-->
@endsection
@section('scripts')
    <style>
        /* Custom Checkbox Styles */
        .form-check-custom .form-check-input {
            width: 1.5rem;
            height: 1.5rem;
            border: 2px solid #d1d5db;
            border-radius: 0.375rem;
            transition: all 0.3s ease;
        }
        
        .form-check-custom .form-check-input:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-check-custom.form-switch .form-check-input {
            width: 3rem;
            height: 1.5rem;
            border-radius: 2rem;
        }
        
        .form-check-custom.form-switch .form-check-input:checked {
            background-color: #10b981;
            border-color: #10b981;
        }
        
        .form-check-lg .form-check-input {
            width: 1.75rem;
            height: 1.75rem;
        }
        
        .form-check-custom .form-check-label {
            cursor: pointer;
            user-select: none;
            margin-left: 0.5rem;
        }
        
        .day-checkbox-item {
            padding: 0.75rem 1.25rem;
            background: #f9fafb;
            border-radius: 0.5rem;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        
        .day-checkbox-item:has(.form-check-input:checked) {
            background: #eff6ff;
            border-color: #3b82f6;
        }
        
        .activity-item {
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        
        .activity-item:has(.activity-check:checked) {
            background: #f0fdf4 !important;
            border-color: #10b981;
        }
        
        /* Card Header Enhancements */
        .card-title h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            display: flex;
            align-items: center;
        }
        
        .card-title h2 i {
            font-size: 1.5rem;
            color: #3b82f6;
        }
    </style>
    
    <script>
        $(document).ready(function() {
            // Image Preview
            $("#gym_image").change(function () {
                let input = this;
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('#preview').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            });
            
            // Enable/Disable Workouts Per Day
            $('#check_workouts_per_day').change(function() {
                $('#workouts_per_day').prop('disabled', !this.checked);
                if (!this.checked) {
                    $('#workouts_per_day').val('');
                }
            });
            
            // Enable/Disable Time Day Fields
            $('#time_day').change(function() {
                $('#start_time_day, #end_time_day').prop('disabled', !this.checked);
                if (!this.checked) {
                    $('#start_time_day, #end_time_day').val('');
                }
            });
            
            // Enable/Disable Week Days
            $('#check_time_week').change(function() {
                $('.week-day-check').prop('disabled', !this.checked);
                if (!this.checked) {
                    $('.week-day-check').prop('checked', false);
                }
            });
            
            // Activity Checkbox Handler
            $('.activity-check').change(function() {
                const input = $(this).closest('.activity-item').find('.training-times');
                if (this.checked) {
                    input.prop('disabled', false);
                    if (!input.val()) {
                        input.val(1);
                    }
                } else {
                    input.prop('disabled', true);
                    input.val('');
                }
            });
            
            // Training Times Input remains numeric only

            // Toggle freeze help box
            $('#toggleFreezeHelp').on('click', function(e){
                e.preventDefault();
                $('#freeze_help_box').toggleClass('d-none');
            });
            // Toggle per-field definitions
            $('.freeze-def-toggle').on('click', function(e){
                e.preventDefault();
                var target = $(this).data('target');
                $(target).toggleClass('d-none');
            });
        });
    </script>
@endsection


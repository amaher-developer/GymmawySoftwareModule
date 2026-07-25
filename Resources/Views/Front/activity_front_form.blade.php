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
            <a href="{{ route('sw.listActivity') }}" class="text-muted text-hover-primary">{{ trans('sw.activities')}}</a>
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
    <style>
        .w-200px { width: 200px; }
        .cursor-pointer { cursor: pointer; }
        .image-input img { transition: transform 0.2s ease-in-out; }
        .image-input img:hover { transform: scale(1.05); }
        
        /* Fix spinner input height */
        .spinner-input {
            height: 38px !important;
        }
        .spinner-buttons {
            display: flex;
            flex-direction: column;
        }
        .spinner-buttons .btn {
            padding: 2px 8px !important;
            height: 19px !important;
            line-height: 1 !important;
            border-radius: 0 !important;
        }
        .spinner-buttons .spinner-up {
            border-radius: 0 0.25rem 0 0 !important;
        }
        .spinner-buttons .spinner-down {
            border-radius: 0 0 0.25rem 0 !important;
        }
        
        /* Time input styling */
        .time-input-wrapper {
            position: relative;
        }
        .time-input-wrapper input[disabled] {
            background-color: #f5f8fa;
            cursor: not-allowed;
            opacity: 0.6;
        }
        .time-input-wrapper input:not([disabled]):hover {
            border-color: #009ef7;
        }
        .time-input-wrapper input:not([disabled]):focus {
            border-color: #009ef7;
            box-shadow: 0 0 0 0.2rem rgba(0, 158, 247, 0.25);
        }
        .time-input-wrapper .input-group-text {
            background-color: #f5f8fa;
            border-left: 0;
        }
        .time-input-wrapper input[disabled] + .input-group-text {
            opacity: 0.6;
        }
        
        /* Checkbox styling in table */
        .day-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        .day-checkbox:hover {
            transform: scale(1.1);
        }
        
        /* Table row styling */
        .table-striped tbody tr {
            transition: all 0.2s ease-in-out;
        }
        .table-striped tbody tr:hover {
            background-color: #f9fafb;
        }
        .table-striped tbody tr.disabled-row {
            opacity: 0.5;
        }
        
        /* Time input transitions */
        .time-input {
            transition: all 0.2s ease-in-out;
        }
    </style>
@endsection
@section('form_title') {{ @$title }} @endsection
@section('page_body')
    <!--begin::Activity Form-->
    <form method="post" action="" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
        {{csrf_field()}}
        
        <!--begin::Main column-->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!--begin::Activity Details-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{$title}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.name_in_arabic')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="name_ar" class="form-control mb-2" 
                               placeholder="{{ trans('sw.enter_name_in_arabic')}}" 
                               value="{{ old('name_ar', $activity->name_ar) }}" 
                               id="name_ar" required />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.name_in_english')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="name_en" class="form-control mb-2" 
                               placeholder="{{ trans('sw.enter_name_in_english')}}" 
                               value="{{ old('name_en', $activity->name_en) }}" 
                               id="name_en" required />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ trans('sw.price')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <div id="spinner_price" class="w-200px">
                            <div class="input-group">
                                <input type="number"
                                       name="price"
                                       id="price"
                                       value="{{ old('price', $activity->price) }}"
                                       step="0.01"
                                       placeholder="{{ trans('sw.enter_price')}}"
                                       class="spinner-input form-control"
                                       required>
                                <div class="spinner-buttons input-group-btn btn-group-vertical">
                                    <button type="button" class="btn spinner-up btn-xs btn-primary">
                                        <i class="fa fa-angle-up"></i>
                                    </button>
                                    <button type="button" class="btn spinner-down btn-xs btn-primary">
                                        <i class="fa fa-angle-down"></i>
                                    </button>
                                </div>
                            </div>
                            @php
                                $vatPercentage = data_get($mainSettings ?? [], 'vat_details.vat_percentage', 0);
                            @endphp
                            @if($vatPercentage > 0)
                                <div class="mt-2">
                                    <small class="text-muted" style="font-size: 0.85rem;">
                                        {{ trans('sw.after_vat') }}: <span id="activity_price_with_vat" class="fw-semibold">0.00</span>
                                    </small>
                                </div>
                            @endif
                        </div>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->

                    @if(@$mainSettings->active_mobile)
                    <!--begin::Input group - Image Upload-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.upload_image')}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input id="gym_image" name="image" type="file" class="form-control mb-3" accept="image/*">
                        <!--end::Input-->
                        <!--begin::Image preview-->
                        <div class="image-input image-input-outline">
                            <label for="gym_image" class="cursor-pointer">
                                <img id="preview" 
                                     src="{{ $activity->image ?: 'https://gymmawy.com/resources/assets/new_front/img/blank-image.svg' }}"
                                     class="rounded border border-gray-300"
                                     style="height: 120px; width: 120px; object-fit: contain;"
                                     alt="Activity preview image"/>
                        </label>
                        </div>
                        <!--end::Image preview-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group - Content Arabic-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">
                            {{ trans('sw.content_in_arabic')}}
                            <span class="text-muted fs-7">({{ trans('sw.max')}} 250 {{ trans('sw.characters')}})</span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Textarea-->
                        <textarea name="content_ar" 
                                  class="form-control" 
                                  placeholder="{{ trans('sw.enter_content_in_arabic')}}" 
                                  id="content_ar" 
                                  rows="3"
                                  maxlength="250">{{ old('content_ar', $activity->content_ar) }}</textarea>
                        <!--end::Textarea-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group - Content English-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">
                            {{ trans('sw.content_in_english')}}
                            <span class="text-muted fs-7">({{ trans('sw.max')}} 250 {{ trans('sw.characters')}})</span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Textarea-->
                        <textarea name="content_en" 
                                  class="form-control" 
                                  placeholder="{{ trans('sw.enter_content_in_english')}}" 
                                  id="content_en" 
                                  rows="3"
                                  maxlength="250">{{ old('content_en', $activity->content_en) }}</textarea>
                        <!--end::Textarea-->
                    </div>
                    <!--end::Input group-->
                    @endif
                    <!--begin::Input group - Visibility-->
                    <div class="mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ trans('sw.visible_invisible')}}</label>
                        <!--end::Label-->
                        <!--begin::Checkbox group-->
                        <div class="d-flex flex-wrap gap-5">
                            <!-- is_system first, default checked -->
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_system" 
                                       name="is_system" 
                                       value="1" 
                                       @if(old('is_system', @$activity->is_system ?? 1)) checked @endif />
                                <label class="form-check-label fw-semibold" for="is_system">
                                    <i class="fa fa-shield fs-3 me-1"></i> {{ trans('sw.system')}}
                                </label>
                            </div>
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_mobile" 
                                       name="is_mobile" 
                                       value="1" 
                                       @if(@$activity->is_mobile) checked @endif />
                                <label class="form-check-label fw-semibold" for="is_mobile">
                                    <i class="fa fa-mobile fs-3 me-1"></i> {{ trans('sw.mobile')}}
                                </label>
                            </div>
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_web" 
                                       name="is_web" 
                                       value="1" 
                                       @if(@$activity->is_web) checked @endif />
                                <label class="form-check-label fw-semibold" for="is_web">
                                    <i class="fa fa-globe fs-3 me-1"></i> {{ trans('sw.web')}}
                                </label>
                            </div>
                        </div>
                        <!--end::Checkbox group-->
                    </div>
                    <!--end::Input group-->
                    

                </div>
                <!--end::Card body-->
            </div>
            <!--end::Activity Details-->
            
            @if(isset($mainSettings->active_activity_reservation) && $mainSettings->active_activity_reservation)
            <!--begin::Reservation Settings-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <div class="card-title">
                        <h2><i class="fa fa-calendar"></i> {{ trans('sw.class_days')}}</h2>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">

                    <!--begin::Input group-->
                    <div class="row mb-10">
                        <!--begin::Reservation Limit-->
                        <div class="col-md-6 fv-row">
                            <label class="form-label">
                                {{ trans('sw.reservation_limit')}}
                                <span class="text-muted fs-7 d-block mt-1">
                                    <i class="fa fa-info-circle"></i> {{ trans('sw.by_persons')}}
                                </span>
                            </label>
                            <div id="spinner_reservation_limit" class="w-200px">
                                <div class="input-group">
                                    <input type="number" 
                                           name="reservation_limit" 
                                           id="reservation_limit" 
                                           placeholder="{{ trans('sw.enter_reservation_limit')}}" 
                                           value="{{ old('reservation_limit', $activity->reservation_limit) }}" 
                                           class="spinner-input form-control">
                                    <div class="spinner-buttons input-group-btn btn-group-vertical">
                                        <button type="button" class="btn spinner-up btn-xs btn-primary">
                                            <i class="fa fa-angle-up"></i>
                                        </button>
                                        <button type="button" class="btn spinner-down btn-xs btn-primary">
                                            <i class="fa fa-angle-down"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Reservation Limit-->
                        
                        <!--begin::Reservation Duration-->
                        <div class="col-md-6 fv-row">
                            <label class="form-label">
                                {{ trans('sw.reservation_duration')}}
                                <span class="text-muted fs-7 d-block mt-1">
                                    <i class="fa fa-info-circle"></i> {{ trans('sw.by_minutes')}}
                                </span>
                            </label>
                            <div id="spinner_reservation_period" class="w-200px">
                                <div class="input-group">
                                    <input type="number" 
                                           name="reservation_duration" 
                                           id="reservation_duration" 
                                           placeholder="{{ trans('sw.enter_reservation_duration')}}" 
                                           value="{{ old('reservation_duration', $activity->reservation_duration) }}" 
                                           class="spinner-input form-control">
                                    <div class="spinner-buttons input-group-btn btn-group-vertical">
                                        <button type="button" class="btn spinner-up btn-xs btn-primary">
                                            <i class="fa fa-angle-up"></i>
                                        </button>
                                        <button type="button" class="btn spinner-down btn-xs btn-primary">
                                            <i class="fa fa-angle-down"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Reservation Duration-->
                    </div>
                    <!--end::Input group-->
                    
                    <input type="hidden" name="reservation_period" id="reservation_period" 
                           placeholder="{{ trans('sw.enter_reservation_period')}}" 
                           value="{{ old('reservation_period', $activity->reservation_period) }}" 
                           class="spinner-input form-control">

                    <!--begin::Schedule Table-->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                    <th style="width: 10%;">{{ trans('sw.status')}}</th>
                                    <th style="width: 20%;">{{ trans('sw.day')}}</th>
                                    <th style="width: 35%;">{{ trans('sw.time_from')}}</th>
                                    <th style="width: 35%;">{{ trans('sw.time_to')}}</th>
                            </tr>
                            </thead>
                                <tbody>
                                @php
                                    $weekDays = [
                                        ['index' => 6, 'trans' => 'sat'],
                                        ['index' => 0, 'trans' => 'sun'],
                                        ['index' => 1, 'trans' => 'mon'],
                                        ['index' => 2, 'trans' => 'tue'],
                                        ['index' => 3, 'trans' => 'wed'],
                                        ['index' => 4, 'trans' => 'thurs'],
                                        ['index' => 5, 'trans' => 'fri'],
                                    ];
                                @endphp
                                
                                @foreach($weekDays as $day)
                                <tr class="day-row @if(!@$activity->reservation_details['work_days'][$day['index']]['status']) disabled-row @endif" data-day-row="{{ $day['index'] }}">
                                    <td class="text-center">
                                        <input type="checkbox" 
                                               name="reservation_details[work_days][{{ $day['index'] }}][status]"
                                               id="day_status_{{ $day['index'] }}"
                                               value="1"
                                               class="form-check-input day-checkbox"
                                               data-day="{{ $day['index'] }}"
                                               @if(@$activity->reservation_details['work_days'][$day['index']]['status']) checked @endif>
                                    </td>
                                    <td>
                                        <strong>{{ trans('sw.' . $day['trans'])}}</strong>
                                    </td>
                                    <td>
                                        <div class="input-group time-input-wrapper">
                                            <input type="time" 
                                                   name="reservation_details[work_days][{{ $day['index'] }}][start]"
                                                   id="day_start_{{ $day['index'] }}"
                                                   value="{{@$activity->reservation_details['work_days'][$day['index']]['start']}}"
                                                   class="form-control time-input"
                                                   data-day="{{ $day['index'] }}"
                                                   @if(!@$activity->reservation_details['work_days'][$day['index']]['status']) disabled @endif>
                                            <span class="input-group-text">
                                                <i class="fa fa-clock-o"></i>
                                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group time-input-wrapper">
                                            <input type="time" 
                                                   name="reservation_details[work_days][{{ $day['index'] }}][end]"
                                                   id="day_end_{{ $day['index'] }}"
                                                   value="{{@$activity->reservation_details['work_days'][$day['index']]['end']}}"
                                                   class="form-control time-input"
                                                   data-day="{{ $day['index'] }}"
                                                   @if(!@$activity->reservation_details['work_days'][$day['index']]['status']) disabled @endif>
                                            <span class="input-group-text">
                                                <i class="fa fa-clock-o"></i>
                                                            </span>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!--end::Schedule Table-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Reservation Settings-->
            @endif
            <!--begin::Form Actions-->
            <div class="d-flex justify-content-end">
                <!--begin::Button-->
                <button type="reset" class="btn btn-light me-5">{{ trans('admin.reset')}}</button>
                <!--end::Button-->
                <!--begin::Button-->
                <button type="submit" class="btn btn-primary">
                    <span class="indicator-label">{{ trans('global.save')}}</span>
                    <span class="indicator-progress">Please wait... 
                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
                <!--end::Button-->
            </div>
            <!--end::Form Actions-->
        </div>
        <!--end::Main column-->
    </form>
    <!--end::Activity Form-->
@endsection
@section('scripts')
    {{-- External Libraries --}}
    <script type="text/javascript" src="{{asset('resources/assets/new_front/global/plugins/fuelux/js/spinner.min.js')}}"></script>

    {{-- Form Initialization --}}
  <script>
        $(document).ready(function() {
            // VAT calculation
            const vatPercentage = {{ $vatPercentage ?? 0 }};
            const $priceInput = $('#price');
            const $priceWithVatDisplay = $('#activity_price_with_vat');

            function updatePriceWithVat() {
                if ($priceInput.length && $priceWithVatDisplay.length && vatPercentage > 0) {
                    const price = parseFloat($priceInput.val()) || 0;
                    const vatAmount = price * (vatPercentage / 100);
                    const priceWithVat = price + vatAmount;
                    $priceWithVatDisplay.text(priceWithVat.toFixed(2));
                }
            }

            // Initialize spinners
            $('#spinner_price').spinner({
                value: {{ old('price', $activity->price) ?: 0 }},
                step: 1,
                min: 0,
                max: 10000
            });

            $('#spinner_reservation_limit').spinner({
                value: {{ old('reservation_limit', $activity->reservation_limit) ?: 0 }},
                step: 1,
                min: 0,
                max: 1000
            });

            $('#spinner_reservation_period').spinner({
                value: {{ old('reservation_duration', $activity->reservation_duration) ?: 0 }},
                step: 1,
                min: 0,
                max: 1000
            });

            // Bind VAT calculation to price input and spinner change
            if ($priceInput.length) {
                $priceInput.on('input change', updatePriceWithVat);
                $('#spinner_price').on('changed.fu.spinner', updatePriceWithVat);
                updatePriceWithVat();
            }

            // Image preview handler
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

            // ==========================================
            // Day Checkbox & Time Input Management
            // ==========================================
            
            // Day checkbox toggle for time inputs
            $('.day-checkbox').on('change', function() {
                const dayIndex = $(this).data('day');
                const isChecked = $(this).is(':checked');
                
                // Enable/disable time inputs based on checkbox state
                $(`#day_start_${dayIndex}`).prop('disabled', !isChecked);
                $(`#day_end_${dayIndex}`).prop('disabled', !isChecked);
                
                // Toggle row disabled class
                $(`tr[data-day-row="${dayIndex}"]`).toggleClass('disabled-row', !isChecked);
                
                // Clear values when disabled
                if (!isChecked) {
                    $(`#day_start_${dayIndex}`).val('');
                    $(`#day_end_${dayIndex}`).val('');
                }
            });

            // Initialize all checkboxes state on page load
            $('.day-checkbox').each(function() {
                const dayIndex = $(this).data('day');
                const isChecked = $(this).is(':checked');
                
                $(`#day_start_${dayIndex}`).prop('disabled', !isChecked);
                $(`#day_end_${dayIndex}`).prop('disabled', !isChecked);
                $(`tr[data-day-row="${dayIndex}"]`).toggleClass('disabled-row', !isChecked);
            });

            // Handle form submission - ensure reservation_details is null if no days selected
            $('form').on('submit', function(e) {
                let hasActiveDay = false;
                
                // Check if any day checkbox is checked
                $('.day-checkbox').each(function() {
                    if ($(this).is(':checked')) {
                        hasActiveDay = true;
                        return false; // break loop
                    }
                });
                
                // If no active days, add hidden input to set reservation_details to null
                if (!hasActiveDay) {
                    // Remove any existing reservation_details inputs
                    $('input[name^="reservation_details"]').remove();
                    
                    // Add hidden input to explicitly set reservation_details to null
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'reservation_details',
                        value: ''
                    }).appendTo($(this));
                }
            });
        });
    </script>
@endsection



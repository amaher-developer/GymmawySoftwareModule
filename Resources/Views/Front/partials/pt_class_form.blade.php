@php
    use Carbon\Carbon;
    use Illuminate\Support\Collection;
    use Modules\Software\Models\GymPTClass;

    $mode = $mode ?? 'create';
    $isEdit = $mode === 'edit';
    $formId = $formId ?? uniqid('pt-class-form-');
    /** @var GymPTClass $classModel */
    $classModel = $class ?? new GymPTClass();

    $schedulePayload = $classModel->schedule ?? $classModel->reservation_details ?? [];
    $defaultSchedule = is_array($schedulePayload) ? $schedulePayload : [];

    $trainerAssignments = $classModel->relationLoaded('classTrainers')
        ? $classModel->classTrainers
        : ($classModel->classTrainers ?? collect());

    $trainerRows = $trainerAssignments instanceof Collection
        ? $trainerAssignments->map(function ($assignment) {
            /** @var \Modules\Software\Models\GymPTClassTrainer $assignment */
            return [
                'id' => $assignment->id,
                'trainer_id' => $assignment->trainer_id,
                'trainer_name' => optional($assignment->trainer)->name,
                'session_type' => $assignment->session_type,
                'session_count' => $assignment->session_count,
                'commission_rate' => $assignment->commission_rate,
                'is_active' => $assignment->is_active,
                'schedule' => $assignment->schedule ?? null,
                'date_from' => $assignment->date_from ?? null,
                'date_to' => $assignment->date_to ?? null,
            ];
        })->values()->toArray()
        : [];

    $trainerOptions = $trainers instanceof Collection ? $trainers : collect($trainers ?? []);
    $trainerList = $trainerOptions->map(function ($trainer) {
        return [
            'id' => $trainer->id,
            'name' => $trainer->name,
            'default_percentage' => $trainer->percentage ?? 0,
        ];
    })->values()->toArray();

    $config = [
        'formId' => $formId,
        'mode' => $mode,
        'schedule' => $defaultSchedule,
        'trainerAssignments' => $trainerRows,
        'trainers' => $trainerList,
        'i18n' => [
            'chooseOption' => trans('admin.choose') . '...',
            'sun' => trans('sw.sun'),
            'mon' => trans('sw.mon'),
            'tue' => trans('sw.tue'),
            'wed' => trans('sw.wed'),
            'thurs' => trans('sw.thurs'),
            'fri' => trans('sw.fri'),
            'sat' => trans('sw.sat'),
            'start' => trans('sw.time_from'),
            'end' => trans('sw.time_to'),
            'active' => trans('sw.active'),
            'inactive' => trans('sw.inactive'),
            'remove' => trans('sw.remove'),
            'restore' => trans('sw.restore'),
            'trainer' => trans('sw.pt_trainer'),
            'sessionsLabel' => trans('sw.sessions_short_label'),
            'commissionLabel' => trans('sw.commission_short_label'),
            'inactive' => trans('sw.inactive'),
            'active' => trans('sw.is_active'),
            'removedLabel' => trans('sw.marked_for_deletion'),
            'sessionCount' => trans('sw.session_count'),
            'commissionRate' => trans('sw.trainer_commission_rate'),
            'sessionType' => trans('sw.session_type'),
            'dateFrom' => trans('sw.date_from'),
            'dateTo' => trans('sw.date_to'),
            'yes' => trans('global.yes'),
            'no' => trans('global.no'),
        ],
        'vatPercentage' => (float) data_get($mainSettings ?? [], 'vat_details.vat_percentage', 0),
    ];
@endphp

<div id="{{ $formId }}" class="pt-class-form-wrapper">
    <form method="post" action="{{ $formAction ?? '' }}" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
        @csrf
        @if(($formMethod ?? 'POST') !== 'POST')
            @method($formMethod)
        @endif

        <input type="hidden" name="schedule" id="schedule_input" value="{{ old('schedule', json_encode($defaultSchedule)) }}">
        <input type="hidden" name="reservation_details" id="reservation_details_input" value="{{ old('reservation_details', json_encode($defaultSchedule)) }}">
        <input type="hidden" name="classes" id="legacy_classes_input" value="{{ old('classes', $classModel->classes ?? $classModel->total_sessions ?? 0) }}">

        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2 class="fw-bold">{{ trans('sw.pt_class_details') }}</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="alert alert-info d-flex align-items-start gap-3 mb-5">
                        <i class="ki-outline ki-information-5 fs-2 text-info"></i>
                        <div>
                            <h4 class="fw-bold mb-1">{{ trans('sw.pt_class') }}</h4>
                            <div class="text-muted">{{ trans('sw.pt_class_overview_hint') }}</div>
                        </div>
                    </div>
                    <div class="row g-5">
                        <div class="col-lg-6">
                            <label class="required form-label d-flex align-items-center gap-2">
                                <span>{{ trans('sw.pt_subscription') }}</span>
                                <i class="ki-outline ki-information fs-6 text-muted" data-bs-toggle="tooltip" title="{{ trans('sw.pt_subscription_hint') }}"></i>
                            </label>
                            <select name="pt_subscription_id"
                                    class="form-select select2"
                                    data-placeholder="{{ trans('admin.choose')}}..."
                                    required>
                                <option value="">{{ trans('admin.choose')}}...</option>
                                @foreach($subscriptions as $subscription)
                                    <option value="{{ $subscription->id }}" @selected($subscription->id == old('pt_subscription_id', $classModel->pt_subscription_id))>
                                        {{ $subscription->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6">
                            <div class="row g-5">
                                <div class="col-sm-6">
                                    <label class="form-label d-flex align-items-center gap-2">
                                        <span>{{ trans('sw.is_active') }}</span>
                                        <i class="ki-outline ki-information fs-6 text-muted" data-bs-toggle="tooltip" title="{{ trans('sw.is_active_hint') }}"></i>
                                    </label>
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox"
                                               class="form-check-input"
                                               value="1"
                                               name="is_active"
                                               id="is_active_switch"
                                               @checked(old('is_active', $classModel->is_active ?? true))>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label d-flex align-items-center gap-2">
                                        <span>{{ trans('sw.class_color') }}</span>
                                        <i class="ki-outline ki-information fs-6 text-muted" data-bs-toggle="tooltip" title="{{ trans('sw.class_color_hint') }}"></i>
                                    </label>
                                    <input type="text"
                                           class="form-control"
                                           name="class_color"
                                           id="class_color_input"
                                           value="{{ old('class_color', $classModel->class_color ?? '#bbbbbb') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-5 mt-5">
                        <div class="col-lg-6">
                            <label class="required form-label d-flex align-items-center gap-2">
                                <span>{{ trans('sw.name_in_arabic') }}</span>
                                <i class="ki-outline ki-information fs-6 text-muted" data-bs-toggle="tooltip" title="{{ trans('sw.name_ar_hint') }}"></i>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   name="name_ar"
                                   id="name_ar_input"
                                   maxlength="80"
                                   value="{{ old('name_ar', $classModel->name_ar) }}"
                                   required>
                        </div>
                        <div class="col-lg-6">
                            <label class="required form-label d-flex align-items-center gap-2">
                                <span>{{ trans('sw.name_in_english') }}</span>
                                <i class="ki-outline ki-information fs-6 text-muted" data-bs-toggle="tooltip" title="{{ trans('sw.name_en_hint') }}"></i>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   name="name_en"
                                   id="name_en_input"
                                   maxlength="80"
                                   value="{{ old('name_en', $classModel->name_en) }}"
                                   required>
                        </div>
                    </div>

                    @if(data_get($mainSettings ?? [], 'active_mobile'))
                        <div class="row g-5 mt-5">
                            <div class="col-lg-6">
                                <label class="form-label d-flex align-items-center gap-2">
                                    <span>{{ trans('sw.content_in_arabic') }}</span>
                                    <i class="ki-outline ki-information fs-6 text-muted" data-bs-toggle="tooltip" title="{{ trans('sw.content_ar_hint') }}"></i>
                                </label>
                                <textarea class="form-control"
                                          name="content_ar"
                                          id="content_ar_input"
                                          maxlength="250"
                                          rows="2"
                                          placeholder="{{ trans('sw.enter_content_in_arabic') }}">{{ old('content_ar', $classModel->content_ar) }}</textarea>
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label d-flex align-items-center gap-2">
                                    <span>{{ trans('sw.content_in_english') }}</span>
                                    <i class="ki-outline ki-information fs-6 text-muted" data-bs-toggle="tooltip" title="{{ trans('sw.content_en_hint') }}"></i>
                                </label>
                                <textarea class="form-control"
                                          name="content_en"
                                          id="content_en_input"
                                          maxlength="250"
                                          rows="2"
                                          placeholder="{{ trans('sw.enter_content_in_english') }}">{{ old('content_en', $classModel->content_en) }}</textarea>
                            </div>
                        </div>
                    @endif
                    <div class="row g-5 mt-5">
                        <div class="col-12">
                            <label class="form-label d-flex align-items-center gap-2">
                                <span>{{ trans('sw.visible_invisible') }}</span>
                                <i class="ki-outline ki-information fs-6 text-muted" data-bs-toggle="tooltip" title="{{ trans('sw.visibility_hint') }}"></i>
                            </label>
                            <div class="d-flex flex-wrap gap-5">
                                <label class="form-check form-check-custom form-check-solid">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           name="is_system"
                                           value="1"
                                           @checked(old('is_system', $classModel->is_system ?? 1))>
                                    <span class="form-check-label fw-semibold text-gray-700">{{ trans('sw.system') }}</span>
                                </label>
                                @if(data_get($mainSettings ?? [], 'active_mobile'))
                                    <label class="form-check form-check-custom form-check-solid">
                                        <input type="checkbox"
                                               class="form-check-input"
                                               name="is_mobile"
                                               value="1"
                                               @checked(old('is_mobile', $classModel->is_mobile ?? 0))>
                                        <span class="form-check-label fw-semibold text-gray-700">{{ trans('sw.mobile') }}</span>
                                    </label>
                                @endif
                                <label class="form-check form-check-custom form-check-solid">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           name="is_web"
                                           value="1"
                                           @checked(old('is_web', $classModel->is_web ?? 0))>
                                    <span class="form-check-label fw-semibold text-gray-700">{{ trans('sw.web') }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2 class="fw-bold">{{ trans('sw.session_configuration') }}</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="row g-5">
                        <div class="col-lg-4">
                            <label class="required form-label d-block">
                                <span>{{ trans('sw.class_type') }}</span>
                                <i class="ki-outline ki-information fs-6 text-muted ms-1" data-bs-toggle="tooltip" title="{{ trans('sw.class_type_hint') }}"></i>
                            </label>
                            @foreach(['private', 'group', 'mixed'] as $type)
                                <label class="form-check form-check-inline form-check-custom form-check-solid me-5">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="class_type"
                                           value="{{ $type }}"
                                           @checked(old('class_type', $classModel->class_type ?? 'private') === $type)
                                           required>
                                    <span class="form-check-label text-capitalize">{{ trans('sw.class_type_' . $type) }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="col-lg-4">
                            <label class="required form-label d-block">
                                <span>{{ trans('sw.pricing_type') }}</span>
                                <i class="ki-outline ki-information fs-6 text-muted ms-1" data-bs-toggle="tooltip" title="{{ trans('sw.pricing_type_hint') }}"></i>
                            </label>
                            @foreach(['per_member', 'per_group'] as $type)
                                <label class="form-check form-check-inline form-check-custom form-check-solid me-5">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="pricing_type"
                                           value="{{ $type }}"
                                           @checked(old('pricing_type', $classModel->pricing_type ?? 'per_member') === $type)
                                           required>
                                    <span class="form-check-label text-capitalize">{{ trans('sw.pricing_type_' . $type) }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="col-lg-4">
                            <label class="required form-label d-flex align-items-center gap-2">
                                <span>{{ trans('sw.total_sessions') }}</span>
                                <i class="ki-outline ki-information fs-6 text-muted" data-bs-toggle="tooltip" title="{{ trans('sw.total_sessions_hint') }}"></i>
                            </label>
                            <input type="number"
                                   class="form-control"
                                   name="total_sessions"
                                   id="total_sessions_input"
                                   min="1"
                                   step="1"
                                   value="{{ old('total_sessions', $classModel->total_sessions ?? $classModel->classes ?? 0) }}"
                                   required>
                        </div>
                    </div>

                    <div class="row g-5 mt-5">
                        <div class="col-lg-4">
                            <label class="form-label d-flex align-items-center gap-2">
                                <span>{{ trans('sw.max_members') }}</span>
                                <i class="ki-outline ki-information fs-6 text-muted" data-bs-toggle="tooltip" title="{{ trans('sw.max_members_hint') }}"></i>
                            </label>
                            <input type="number"
                                   class="form-control"
                                   name="max_members"
                                   id="max_members_input"
                                   min="1"
                                   step="1"
                                   value="{{ old('max_members', $classModel->max_members ?? $classModel->member_limit) }}"
                                   placeholder="{{ trans('sw.optional') }}">
                        </div>
                        <div class="col-lg-4">
                            <label class="required form-label d-flex align-items-center gap-2">
                                <span>{{ trans('sw.price') }}</span>
                                <i class="ki-outline ki-information fs-6 text-muted" data-bs-toggle="tooltip" title="{{ trans('sw.price_hint') }}"></i>
                            </label>
                            <div class="input-group">
                                <input type="number"
                                       class="form-control"
                                       name="price"
                                       id="price_input"
                                       min="0"
                                       step="0.01"
                                       value="{{ old('price', $classModel->price ?? 0) }}"
                                       required>
                                <span class="input-group-text">{{ data_get($mainSettings ?? [], 'currency', trans('sw.currency')) }}</span>
                            </div>
                            @php
                                $vatPercentage = data_get($mainSettings ?? [], 'vat_details.vat_percentage', 0);
                            @endphp
                            @if($vatPercentage > 0)
                                <div class="form-text text-muted">
                                    {{ trans('sw.excluding_vat') }}
                                </div>
                                <div class="mt-1">
                                    <small class="text-muted" style="font-size: 0.85rem;">
                                        {{ trans('sw.after_vat') }}: <span id="price_with_vat_display" class="fw-semibold">0.00</span>
                                    </small>
                                </div>
                            @endif
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label d-flex align-items-center gap-2">
                                <span>{{ trans('sw.notes') }}</span>
                                <i class="ki-outline ki-information fs-6 text-muted" data-bs-toggle="tooltip" title="{{ trans('sw.notes_hint') }}"></i>
                            </label>
                            <textarea class="form-control"
                                      name="notes"
                                      id="notes_input"
                                      rows="2"
                                      maxlength="255"
                                      placeholder="{{ trans('sw.notes_placeholder') }}">{{ old('notes', $classModel->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-flush py-4">
                <div class="card-header align-items-center">
                    <div class="card-title">
                        <h2 class="fw-bold mb-0 d-flex align-items-center gap-2">
                            {{ trans('sw.class_trainers_assignment') }}
                            <i class="ki-outline ki-information fs-6 text-muted" data-bs-toggle="tooltip" title="{{ trans('sw.trainers_table_hint') }}"></i>
                        </h2>
                    </div>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-sm btn-light-primary" id="add_trainer_row_btn">
                            <i class="ki-outline ki-plus fs-2"></i>{{ trans('sw.add_trainer') }}
                        </button>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div id="trainer_assignment_container" class="d-flex flex-column gap-5"></div>
                    <div class="text-muted fs-7 mt-3 d-flex flex-wrap gap-2">
                        <span><i class="ki-outline ki-dot fs-7 text-primary me-1"></i>{{ trans('sw.trainer_session_count_hint') }}</span>
                        <span><i class="ki-outline ki-dot fs-7 text-primary me-1"></i>{{ trans('sw.trainer_commission_hint') }}</span>
                        <span><i class="ki-outline ki-dot fs-7 text-primary me-1"></i>{{ trans('sw.trainer_session_type_hint') }}</span>
                        <span><i class="ki-outline ki-dot fs-7 text-primary me-1"></i>{{ trans('sw.trainer_dates_hint') }}</span>
                    </div>
                </div>
            </div>

            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2 class="fw-bold d-flex align-items-center gap-2">
                            {{ trans('sw.schedule') }}
                            <i class="ki-outline ki-information fs-6 text-muted" data-bs-toggle="tooltip" title="{{ trans('sw.schedule_hint') }}"></i>
                        </h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="class_schedule_table">
                            <thead class="table-light">
                                <tr class="text-gray-500 fw-semibold text-uppercase">
                                    <th class="w-125px">{{ trans('sw.day') }}</th>
                                    <th class="w-125px text-center">{{ trans('sw.status') }}</th>
                                    <th>{{ trans('sw.time_from') }}</th>
                                    <th>{{ trans('sw.time_to') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $weekdayOrder = [
                                        ['index' => 0, 'label' => 'sun'],
                                        ['index' => 1, 'label' => 'mon'],
                                        ['index' => 2, 'label' => 'tue'],
                                        ['index' => 3, 'label' => 'wed'],
                                        ['index' => 4, 'label' => 'thurs'],
                                        ['index' => 5, 'label' => 'fri'],
                                        ['index' => 6, 'label' => 'sat'],
                                    ];
                                    $workDays = data_get($defaultSchedule, 'work_days', []);
                                @endphp
                                @foreach($weekdayOrder as $day)
                                    @php
                                        $daySchedule = $workDays[$day['index']] ?? [];
                                        $status = data_get($daySchedule, 'status', false);
                                        $start = data_get($daySchedule, 'start');
                                        $end = data_get($daySchedule, 'end');
                                    @endphp
                                    <tr data-day="{{ $day['index'] }}">
                                        <td class="fw-semibold">{{ trans('sw.' . $day['label']) }}</td>
                                        <td class="text-center">
                                            <div class="form-check form-switch form-check-custom form-check-solid justify-content-center">
                                                <input type="checkbox"
                                                       class="form-check-input schedule-status"
                                                       @checked(old("schedule.work_days.{$day['index']}.status", $status))>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <input type="text"
                                                       class="form-control timepicker-24 schedule-start"
                                                       value="{{ old("schedule.work_days.{$day['index']}.start", $start) }}"
                                                       placeholder="HH:MM">
                                                <span class="input-group-text"><i class="ki-outline ki-time"></i></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <input type="text"
                                                       class="form-control timepicker-24 schedule-end"
                                                       value="{{ old("schedule.work_days.{$day['index']}.end", $end) }}"
                                                       placeholder="HH:MM">
                                                <span class="input-group-text"><i class="ki-outline ki-time"></i></span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-muted fs-7 mt-3"><i class="ki-outline ki-dot fs-7 text-primary me-1"></i>{{ trans('sw.schedule_status_hint') }}</div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="reset" class="btn btn-light me-3">{{ trans('admin.reset') }}</button>
                <button type="submit" class="btn btn-primary">
                    <i class="ki-outline ki-check fs-2 me-1"></i>{{ trans('global.save') }}
                </button>
            </div>
        </div>
    </form>
</div>

@once('pt-class-form-styles')
    @section('sub_styles')
        @parent
        <link rel="stylesheet" type="text/css" href="{{ asset('resources/assets/new_front/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css') }}"/>
        <link rel="stylesheet" type="text/css" href="{{ asset('resources/assets/new_front/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css') }}"/>
        <link rel="stylesheet" type="text/css" href="{{ asset('resources/assets/new_front/global/plugins/jquery-minicolors/jquery.minicolors.css') }}"/>
        <style>
            .pt-class-form-wrapper .table thead th {
                white-space: nowrap;
            }
            .trainer-row.card {
                transition: all .2s ease;
            }
            .trainer-row.card.inactive-row {
                border-color: rgba(255,193,7,.4);
                background-color: rgba(255,193,7,.05);
            }
            .trainer-row.card.deleted-row {
                opacity: .35;
            }
            .trainer-row.card .card-header {
                padding-top: 0.75rem;
                padding-bottom: 0.75rem;
            }
            .trainer-row.card .card-body .form-text {
                font-size: 0.75rem;
            }
            .schedule-disabled .input-group {
                opacity: 0.5;
            }
        </style>
    @endsection
@endonce

@once('pt-class-form-scripts')
    @section('sub_scripts')
        @parent
        <script src="{{ asset('resources/assets/new_front/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js') }}" type="text/javascript"></script>
        <script src="{{ asset('resources/assets/new_front/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js') }}" type="text/javascript"></script>
        <script src="{{ asset('resources/assets/new_front/global/plugins/jquery-minicolors/jquery.minicolors.min.js') }}" type="text/javascript"></script>
        <script>
            (function ($, bootstrap) {
                'use strict';

                window.ptClassFormRegistry = window.ptClassFormRegistry || {};

                let trainerRowIncrement = 0;

                function formatCurrency(value) {
                    const number = parseFloat(value);
                    if (Number.isNaN(number)) {
                        return '0.00';
                    }
                    return number.toFixed(2);
                }

                function initialisePlugins(instance) {
                    if ($.fn.select2) {
                        instance.$root.find('.select2').select2({
                            width: '100%',
                            allowClear: true,
                            placeholder: instance.i18n.chooseOption
                        });
                    }

                    if ($.fn.timepicker) {
                        instance.$root.find('.timepicker-24').timepicker({
                            autoclose: true,
                            minuteStep: 5,
                            showSeconds: false,
                            showMeridian: true
                        });
                    }

                    if (bootstrap && typeof bootstrap.Tooltip === 'function') {
                        instance.$root.find('[data-bs-toggle="tooltip"]').each(function () {
                            new bootstrap.Tooltip(this);
                        });
                    }

                }

                function updateScheduleRowState($row) {
                    const isActive = $row.find('.schedule-status').is(':checked');
                    $row.toggleClass('schedule-disabled', !isActive);
                    $row.find('.schedule-start, .schedule-end')
                        .prop('disabled', !isActive)
                        .closest('.input-group')
                        .toggleClass('opacity-50', !isActive);
                    if (!isActive) {
                        $row.find('.schedule-start, .schedule-end').val('');
                    }
                }

                function initialiseColourPicker(instance) {
                    if ($.fn.minicolors) {
                        instance.$colorInput.minicolors({
                            theme: 'bootstrap',
                            position: 'bottom right'
                        });
                    }
                }

                function gatherSchedule(instance) {
                    const schedule = { work_days: {} };
                    instance.$scheduleRows.each(function () {
                        const $row = $(this);
                        const dayIndex = $row.data('day');
                        const status = $row.find('.schedule-status').is(':checked');
                        const start = status ? ($row.find('.schedule-start').val() || null) : null;
                        const end = status ? ($row.find('.schedule-end').val() || null) : null;

                        schedule.work_days[dayIndex] = {
                            status: status ? 1 : 0,
                            start: start,
                            end: end
                        };
                    });

                    const scheduleJson = JSON.stringify(schedule);
                    instance.$scheduleInput.val(scheduleJson);
                    instance.$reservationDetailsInput.val(scheduleJson);
                    instance.$trainerContainer.find('.trainer-row').each(function () {
                        const $scheduleField = $(this).find('.trainer-schedule');
                        if (!$scheduleField.val()) {
                            $scheduleField.val(scheduleJson);
                        }
                    });
                    return schedule;
                }

                function syncTotalSessions(instance) {
                    const total = parseInt(instance.$totalSessionsInput.val(), 10) || 0;
                    instance.$legacyClassesInput.val(total);
                }

                function refreshTrainerRow(instance, row) {
                    const trainerSelect = row.find('.trainer-select');
                    const selectedOption = trainerSelect.find('option:selected');
                    const trainerName = (selectedOption.length && selectedOption.val())
                        ? selectedOption.text().trim()
                        : instance.i18n.trainer;
                    row.find('.trainer-row-title').text(trainerName || instance.i18n.trainer);

                    const sessionCount = row.find('.session-count-input').val();
                    const commissionRate = row.find('.commission-rate-input').val();
                    const summaries = [];
                    if (sessionCount) {
                        summaries.push(instance.i18n.sessionsLabel.replace(':count', sessionCount));
                    }
                    if (commissionRate) {
                        const formattedRate = parseFloat(commissionRate || 0).toFixed(2);
                        summaries.push(instance.i18n.commissionLabel.replace(':rate', formattedRate));
                    }
                    row.find('.trainer-row-summary').text(summaries.join(' • '));

                    const isActive = row.find('.trainer-active-toggle').is(':checked');
                    row.toggleClass('inactive-row', !isActive);
                }

                function bindTrainerRowEvents(instance, row, index) {
                    row.data('commissionTouched', !!row.find('.commission-rate-input').val());

                    row.find('.trainer-select').on('change', function () {
                        const selected = $(this).find('option:selected');
                        const defaultCommission = parseFloat(selected.data('percentage') || 0);
                        if (!row.data('commissionTouched') && defaultCommission > 0) {
                            row.find('.commission-rate-input').val(defaultCommission);
                        }
                        refreshTrainerRow(instance, row);
                    });

                    row.find('.session-count-input').on('input', function () {
                        refreshTrainerRow(instance, row);
                    });

                    row.find('.commission-rate-input').on('input', function () {
                        row.data('commissionTouched', $(this).val() !== '');
                        refreshTrainerRow(instance, row);
                    });

                    row.find('.trainer-active-toggle').on('change', function () {
                        refreshTrainerRow(instance, row);
                    });

                    const $dateFrom = row.find('.trainer-date-from');
                    const $dateTo = row.find('.trainer-date-to');
                    $dateFrom.on('change', function () {
                        if (!$dateTo.val() || $dateTo.val() < $dateFrom.val()) {
                            $dateTo.val($dateFrom.val());
                        }
                    });
                    $dateTo.on('change', function () {
                        if ($dateFrom.val() && $dateTo.val() < $dateFrom.val()) {
                            $dateFrom.val($dateTo.val());
                        }
                    });

                    row.find('.remove-trainer-row').on('click', function () {
                        const $deleteFlag = row.find('.trainer-delete-flag');
                        const rowHasId = !!row.find(`input[name="class_trainers[${index}][id]"]`).val();
                        if (rowHasId) {
                            const isDeleted = $deleteFlag.val() === '1';
                            $deleteFlag.val(isDeleted ? '0' : '1');
                            row.toggleClass('deleted-row', !isDeleted);
                            const $button = $(this);
                            const $icon = $button.find('i');
                            if (isDeleted) {
                                $button.removeClass('btn-light-success').addClass('btn-light-danger');
                                $icon.removeClass('ki-arrow-left').addClass('ki-trash');
                                $button.attr('title', instance.i18n.remove);
                                row.find(':input')
                                    .not('.trainer-delete-flag')
                                    .prop('disabled', false);
                                row.find('.trainer-select').prop('disabled', false).trigger('change.select2');
                                refreshTrainerRow(instance, row);
                            } else {
                                $button.removeClass('btn-light-danger').addClass('btn-light-success');
                                $icon.removeClass('ki-trash').addClass('ki-arrow-left');
                                $button.attr('title', instance.i18n.restore);
                                row.find(':input')
                                    .not('.trainer-delete-flag')
                                    .not(`[name="class_trainers[${index}][id]"]`)
                                    .prop('disabled', true);
                                row.find('.trainer-select').prop('disabled', true).trigger('change.select2');
                                row.find('.trainer-row-summary').text(instance.i18n.removedLabel);
                            }
                        } else {
                            row.remove();
                        }
                    });
                }

                function buildTrainerRow(instance, data) {
                    const index = trainerRowIncrement++;
                    const assignmentId = data && data.id ? data.id : '';
                    const trainerId = data && data.trainer_id ? data.trainer_id : '';
                    const sessionCount = data && data.session_count ? data.session_count : '';
                    const commissionRate = data && data.commission_rate ? data.commission_rate : '';
                    const sessionType = data && data.session_type ? data.session_type : '';
                    const isActive = data && Object.prototype.hasOwnProperty.call(data, 'is_active') ? !!data.is_active : true;
                    const dateFrom = data && data.date_from ? data.date_from : '';
                    const dateTo = data && data.date_to ? data.date_to : '';
                    const schedule = data && data.schedule ? JSON.stringify(data.schedule) : '';

                    const row = $(`
                        <div class="trainer-row card card-bordered" data-row-index="${index}">
                            <div class="card-header align-items-center">
                                <div class="d-flex flex-column">
                                    <span class="fw-bold trainer-row-title">${instance.i18n.trainer}</span>
                                    <span class="text-muted fs-8 trainer-row-summary"></span>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <label class="form-check form-switch form-check-custom form-check-solid mb-0 d-flex align-items-center gap-2">
                                        <input type="hidden" name="class_trainers[${index}][is_active]" value="0">
                                        <input type="checkbox"
                                               class="form-check-input trainer-active-toggle"
                                               name="class_trainers[${index}][is_active]"
                                               value="1"
                                               ${isActive ? 'checked' : ''}>
                                        <span class="form-check-label text-muted fs-7">${instance.i18n.active || 'Active'}</span>
                                    </label>
                                    <button type="button" class="btn btn-sm btn-light-danger remove-trainer-row" title="${instance.i18n.remove}">
                                        <i class="ki-outline ki-trash fs-2"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body pt-4">
                                <input type="hidden" name="class_trainers[${index}][id]" value="${assignmentId}">
                                <input type="hidden" class="trainer-schedule" name="class_trainers[${index}][schedule]" value='${schedule}'>
                                <input type="hidden" class="trainer-delete-flag" name="class_trainers[${index}][_delete]" value="0">
                                <div class="row g-5 align-items-end">
                                    <div class="col-md-6">
                                        <label class="form-label">{{ trans('sw.pt_trainer') }}</label>
                                        <select class="form-select select2 trainer-select" name="class_trainers[${index}][trainer_id]" data-index="${index}">
                                            <option value="">${instance.i18n.chooseOption}</option>
                                            ${instance.trainers.map(trainer => `<option value="${trainer.id}" ${parseInt(trainerId, 10) === parseInt(trainer.id, 10) ? 'selected' : ''} data-percentage="${trainer.default_percentage || 0}">${trainer.name}</option>`).join('')}
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">{{ trans('sw.session_count') }}</label>
                                        <input type="number"
                                               class="form-control session-count-input"
                                               name="class_trainers[${index}][session_count]"
                                               min="0"
                                               value="${sessionCount}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">{{ trans('sw.trainer_commission_rate') }}</label>
                                        <div class="input-group">
                                            <input type="number"
                                                   class="form-control commission-rate-input"
                                                   name="class_trainers[${index}][commission_rate]"
                                                   min="0"
                                                   max="100"
                                                   step="0.01"
                                                   value="${commissionRate}">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-5 mt-3">
                                    <div class="col-md-4">
                                        <label class="form-label">{{ trans('sw.session_type') }}</label>
                                        <input type="text"
                                               class="form-control trainer-session-type"
                                               name="class_trainers[${index}][session_type]"
                                               value="${sessionType || ''}"
                                               placeholder="${instance.i18n.sessionType}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">{{ trans('sw.date_from') }}</label>
                                        <input type="date"
                                               class="form-control trainer-date-from"
                                               name="class_trainers[${index}][date_from]"
                                               value="${dateFrom}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">{{ trans('sw.date_to') }}</label>
                                        <input type="date"
                                               class="form-control trainer-date-to"
                                               name="class_trainers[${index}][date_to]"
                                               value="${dateTo}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);

                    instance.$trainerContainer.append(row);
                    if ($.fn.select2) {
                        row.find('.select2').select2({
                            width: '100%',
                            allowClear: true,
                            placeholder: instance.i18n.chooseOption
                        });
                    }

                    bindTrainerRowEvents(instance, row, index);
                    refreshTrainerRow(instance, row);
                }

                function initialiseTrainerAssignments(instance) {
                    if (!instance.trainerAssignments.length) {
                        buildTrainerRow(instance, {});
                    } else {
                        instance.trainerAssignments.forEach(function (assignment) {
                            buildTrainerRow(instance, assignment);
                        });
                    }
                }

                function updatePriceWithVat(instance) {
                    if (!instance.$priceInput || !instance.$priceWithVatDisplay || !instance.$priceWithVatDisplay.length) {
                        return;
                    }

                    const price = parseFloat(instance.$priceInput.val()) || 0;
                    const vatAmount = price * (instance.vatPercentage / 100);
                    const priceWithVat = price + vatAmount;
                    instance.$priceWithVatDisplay.text(formatCurrency(priceWithVat));
                }

                function attachEvents(instance) {
                    instance.$addTrainerBtn.on('click', function () {
                        buildTrainerRow(instance, {});
                    });

                    instance.$totalSessionsInput.on('input', function () {
                        syncTotalSessions(instance);
                    });

                    if (instance.$priceInput && instance.$priceInput.length) {
                        instance.$priceInput.on('input', function () {
                            updatePriceWithVat(instance);
                        });
                        updatePriceWithVat(instance);
                    }

                    instance.$scheduleRows.find('.schedule-status').on('change', function () {
                        const $row = $(this).closest('tr');
                        updateScheduleRowState($row);
                        gatherSchedule(instance);
                    });

                    instance.$scheduleRows.find('.schedule-start, .schedule-end').on('change', function () {
                        gatherSchedule(instance);
                    });

                    instance.$form.on('submit', function () {
                        gatherSchedule(instance);
                        syncTotalSessions(instance);
                    });
                }

                function attachInstance(formId) {
                    const instance = window.ptClassFormRegistry[formId];
                    if (!instance || instance.initialized) {
                        return;
                    }
                    instance.initialized = true;

                    instance.$root = $('#' + instance.formId);
                    instance.$form = instance.$root.find('form').first();
                    instance.$trainerContainer = instance.$root.find('#trainer_assignment_container');
                    instance.$addTrainerBtn = instance.$root.find('#add_trainer_row_btn');
                    instance.$scheduleRows = instance.$root.find('#class_schedule_table tbody tr');
                    instance.$scheduleInput = instance.$root.find('#schedule_input');
                    instance.$reservationDetailsInput = instance.$root.find('#reservation_details_input');
                    instance.$totalSessionsInput = instance.$root.find('#total_sessions_input');
                    instance.$legacyClassesInput = instance.$root.find('#legacy_classes_input');
                    instance.$colorInput = instance.$root.find('#class_color_input');
                    instance.$priceInput = instance.$root.find('#price_input');
                    instance.$priceWithVatDisplay = instance.$root.find('#price_with_vat_display');

                    instance.vatPercentage = parseFloat(instance.vatPercentage || 0);

                    initialisePlugins(instance);
                    initialiseColourPicker(instance);
                    initialiseTrainerAssignments(instance);
                    instance.$scheduleRows.each(function () {
                        updateScheduleRowState($(this));
                    });
                    gatherSchedule(instance);
                    syncTotalSessions(instance);
                    attachEvents(instance);
                }

                document.addEventListener('pt-class-form:register', function (event) {
                    if (!event || !event.detail) {
                        return;
                    }
                    attachInstance(event.detail);
                });

                window.initPtClassForm = function (formId) {
                    attachInstance(formId);
                };

                $(document).ready(function () {
                    Object.keys(window.ptClassFormRegistry).forEach(function (formId) {
                        attachInstance(formId);
                    });
                });

            })(jQuery, window.bootstrap || {});
        </script>
    @endsection
@endonce

@section('sub_scripts')
    @parent
    <script>
        window.ptClassFormRegistry = window.ptClassFormRegistry || {};
        window.ptClassFormRegistry['{{ $formId }}'] = {!! json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
        if (window.initPtClassForm) {
            window.initPtClassForm('{{ $formId }}');
        }
        if (typeof document !== 'undefined' && document.dispatchEvent) {
            document.dispatchEvent(new CustomEvent('pt-class-form:register', { detail: '{{ $formId }}' }));
        }
    </script>
@endsection




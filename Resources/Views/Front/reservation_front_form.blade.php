@extends('software::layouts.form')
@section('form_title') {{ @$title }} @endsection
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
            <a href="{{ route('sw.listReservation') }}" class="text-muted text-hover-primary">{{ trans('sw.reservations') }}</a>
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

@section('page_body')
<!--begin::Reservation Form-->
<form method="post" action="{{ isset($reservation) && $reservation->id ? route('sw.editReservation',$reservation->id) : route('sw.createReservation') }}" class="form d-flex flex-column flex-lg-row" id="reservationForm">
    {{ csrf_field() }}
    @if(isset($reservation) && $reservation->id) @method('POST') @endif
    
    <!--begin::Main column-->
    <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
        <!--begin::Reservation Details-->
        <div class="card card-flush py-4">
            <!--begin::Card header-->
            <div class="card-header">
                <div class="card-title">
                    <i class="ki-outline ki-calendar-add fs-2 me-2 text-primary"></i>
                    <h2 class="mb-0">{{ $title }}</h2>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Client Information-->
                <div class="separator separator-dashed mb-10">
                    <div class="d-flex align-items-center">
                        <i class="ki-outline ki-user fs-3 text-primary me-2"></i>
                        <h3 class="fs-4 fw-bold mb-0">{{ trans('sw.client_information') }}</h3>
                    </div>
                </div>
                
                <div class="row mb-10">
                    <div class="col-lg-6">
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="required form-label">
                                <i class="ki-outline ki-profile-user fs-6 me-1"></i>
                                {{ trans('sw.client_type') }}
                            </label>
                            <!--end::Label-->
                            <!--begin::Select-->
                            <select id="client_type" name="client_type" class="form-select form-select-solid" data-control="select2" required>
                                <option value="">{{ trans('admin.choose') }}...</option>
                                <option value="member" {{ old('client_type', $reservation->client_type ?? '') == 'member' ? 'selected' : '' }}>
                                    <i class="ki-outline ki-user fs-6 me-1"></i>{{ trans('sw.member') }}
                                </option>
                                <option value="non_member" {{ old('client_type', $reservation->client_type ?? '') == 'non_member' ? 'selected' : '' }}>
                                    <i class="ki-outline ki-user fs-6 me-1"></i>{{ trans('sw.non_member') }}
                                </option>
                            </select>
                            <!--end::Select-->
                            <!--begin::Help text-->
                            <div class="form-text">
                                <i class="ki-outline ki-information-2 fs-7 me-1"></i>
                                {{ trans('sw.select_client_type_help') }}
                            </div>
                            <!--end::Help text-->
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group - Member-->
                        <div id="memberBlock" style="display:none" class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="required form-label">
                                <i class="ki-outline ki-user fs-6 me-1"></i>
                                {{ trans('sw.member') }}
                            </label>
                            <!--end::Label-->
                            <!--begin::Select-->
                            <select id="member_id" name="member_id" class="form-select form-select-solid" 
                                    data-control="select2" 
                                    data-placeholder="{{ trans('sw.select_member') }}"
                                    data-allow-clear="true">
                                <option value="">{{ trans('sw.select_member') }}</option>
                                @if(isset($reservation) && $reservation->member_id)
                                    @foreach($members ?? [] as $m)
                                        @if($m->id == $reservation->member_id)
                                            <option value="{{ $m->id }}" selected>{{ $m->name }}@if($m->code ?? null) ({{ $m->code }})@endif</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                            <!--end::Select-->
                            <!--begin::Member Info-->
                            <div id="memberInfo" class="mt-3 d-none">
                                <div class="alert alert-light-info d-flex align-items-center p-3">
                                    <i class="ki-outline ki-information-5 fs-2x text-info me-3"></i>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-gray-800" id="memberInfoText"></span>
                                        <span class="text-muted fs-7" id="memberServiceInfo"></span>
                                    </div>
                                </div>
                            </div>
                            <!--end::Member Info-->
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group - Non-Member-->
                        <div id="nonMemberBlock" style="display:none" class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="required form-label">
                                <i class="ki-outline ki-user fs-6 me-1"></i>
                                {{ trans('sw.non_member') }}
                            </label>
                            <!--end::Label-->
                            <!--begin::Select-->
                            <select id="non_member_id" name="non_member_id" class="form-select form-select-solid" 
                                    data-control="select2" 
                                    data-placeholder="{{ trans('sw.select_non_member') }}"
                                    data-allow-clear="true">
                                <option value="">{{ trans('sw.select_non_member') }}</option>
                                @if(isset($reservation) && $reservation->non_member_id)
                                    @foreach($nonMembers ?? [] as $nm)
                                        @if($nm->id == $reservation->non_member_id)
                                            <option value="{{ $nm->id }}" selected>{{ $nm->name }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                            <!--end::Select-->
                        </div>
                        <!--end::Input group-->
                    </div>

                    <div class="col-lg-6">
                        <!--begin::Activity Information-->
                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="required form-label">
                                <i class="ki-outline ki-gym fs-6 me-1"></i>
                                {{ trans('sw.activity') }}
                            </label>
                            <!--end::Label-->
                            <!--begin::Select-->
                            <select id="activity_id" name="activity_id" class="form-select form-select-solid" 
                                    data-control="select2" 
                                    data-placeholder="{{ trans('sw.select_activity') }}"
                                    data-allow-clear="true"
                                    data-search-enabled="true"
                                    data-minimum-results-for-search="0"
                                    required>
                                <option value="">{{ trans('sw.select_activity') }}</option>
                                @foreach($activities ?? [] as $a)
                                    <option value="{{ $a->id }}" 
                                            data-duration="{{ $a->duration_minutes ?? 60 }}"
                                            data-price="{{ $a->price ?? 0 }}"
                                            @selected(old('activity_id', $reservation->activity_id ?? '') == $a->id)>
                                        {{ $a->{'name_'.($lang ?? 'ar')} ?? $a->name }}
                                    </option>
                                @endforeach
                            </select>
                            <!--end::Select-->
                            <!--begin::Activity Info-->
                            <div id="activityInfo" class="mt-3 d-none">
                                <div class="card bg-light-primary p-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="ki-outline ki-information-5 fs-2x text-primary me-3"></i>
                                        <h5 class="mb-0 fw-bold text-gray-800" id="activityName"></h5>
                                    </div>
                                    <div class="separator separator-dashed my-3"></div>
                                    <div class="d-flex flex-wrap gap-4">
                                        <div>
                                            <span class="text-muted fs-7 d-block">{{ trans('sw.duration') }}</span>
                                            <span class="fw-bold text-gray-800" id="activityDuration"></span>
                                        </div>
                                        <div>
                                            <span class="text-muted fs-7 d-block">{{ trans('sw.price') }}</span>
                                            <span class="fw-bold text-gray-800" id="activityPrice"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end::Activity Info-->
                        </div>
                        <!--end::Activity Information-->
                    </div>
                </div>
                <!--end::Client Information-->

                <!--begin::Reservation Time-->
                <div class="separator separator-dashed my-10">
                    <div class="d-flex align-items-center">
                        <i class="ki-outline ki-calendar-tick fs-3 text-primary me-2"></i>
                        <h3 class="fs-4 fw-bold mb-0">{{ trans('sw.reservation_time') }}</h3>
                    </div>
                </div>

                <div class="row mb-10">
                    <div class="col-lg-4">
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="required form-label">
                                <i class="ki-outline ki-calendar fs-6 me-1"></i>
                                {{ trans('sw.reservation_date') }}
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="date" id="reservation_date" name="reservation_date" 
                                   class="form-control form-control-solid" 
                                   value="{{ old('reservation_date', optional($reservation->reservation_date ?? null)->format('Y-m-d') ?? '') }}" 
                                   min="{{ date('Y-m-d') }}"
                                   required />
                            <!--end::Input-->
                            <!--begin::Help text-->
                            <div class="form-text">
                                <i class="ki-outline ki-information-2 fs-7 me-1"></i>
                                {{ trans('sw.min_date_today') }}
                            </div>
                            <!--end::Help text-->
                        </div>
                        <!--end::Input group-->
                    </div>

                    <div class="col-lg-4">
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="required form-label">
                                <i class="ki-outline ki-time fs-6 me-1"></i>
                                {{ trans('sw.start_time') }}
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="time" id="start_time" name="start_time" 
                                   class="form-control form-control-solid" 
                                   value="{{ old('start_time', $reservation->start_time ?? '') }}" 
                                   required />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                    </div>

                    <div class="col-lg-4">
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="required form-label">
                                <i class="ki-outline ki-time fs-6 me-1"></i>
                                {{ trans('sw.end_time') }}
                                <button type="button" class="btn btn-sm btn-icon btn-light-primary ms-2" id="btnAutoCalc" title="{{ trans('sw.auto_calculate') }}" style="display:none;">
                                    <i class="ki-outline ki-arrows-circle fs-5"></i>
                                </button>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="time" id="end_time" name="end_time" 
                                   class="form-control form-control-solid" 
                                   value="{{ old('end_time', $reservation->end_time ?? '') }}" 
                                   required />
                            <!--end::Input-->
                            <!--begin::Conflict Alert-->
                            <div id="conflictAlert" class="alert alert-danger d-none mt-3">
                                <i class="ki-outline ki-cross-circle fs-2x me-2"></i>
                                <span>{{ trans('sw.time_conflict_detected') }}</span>
                            </div>
                            <!--end::Conflict Alert-->
                            <!--begin::Available Alert-->
                            <div id="availableAlert" class="alert alert-success d-none mt-3">
                                <i class="ki-outline ki-check-circle fs-2x me-2"></i>
                                <span>{{ trans('sw.time_slot_available') }}</span>
                            </div>
                            <!--end::Available Alert-->
                        </div>
                        <!--end::Input group-->
                    </div>
                </div>
                <!--end::Reservation Time-->

                <!--begin::Additional Information-->
                <div class="separator separator-dashed my-10">
                    <div class="d-flex align-items-center">
                        <i class="ki-outline ki-note-edit fs-3 text-primary me-2"></i>
                        <h3 class="fs-4 fw-bold mb-0">{{ trans('sw.additional_information') }}</h3>
                    </div>
                </div>

                <div class="row mb-10">
                    <div class="col-lg-12">
                        <!--begin::Input group-->
                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="form-label">
                                <i class="ki-outline ki-note-text fs-6 me-1"></i>
                                {{ trans('sw.notes') }}
                            </label>
                            <!--end::Label-->
                            <!--begin::Textarea-->
                            <textarea name="notes" class="form-control form-control-solid" rows="4" placeholder="{{ trans('sw.enter_notes_placeholder') }}">{{ old('notes', $reservation->notes ?? '') }}</textarea>
                            <!--end::Textarea-->
                            <!--begin::Help text-->
                            <div class="form-text">
                                <i class="ki-outline ki-information-2 fs-7 me-1"></i>
                                {{ trans('sw.notes_optional_help') }}
                            </div>
                            <!--end::Help text-->
                        </div>
                        <!--end::Input group-->
                    </div>
                </div>
                <!--end::Additional Information-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Reservation Details-->

        <!--begin::Actions-->
        <div class="d-flex flex-stack">
            <a href="{{ route('sw.listReservation') }}" class="btn btn-light">
                <i class="ki-outline ki-arrow-left fs-2"></i>
                {{ trans('admin.back') }}
            </a>
            <div>
                <button type="button" class="btn btn-light me-3" id="btnCheckAvailability">
                    <i class="ki-outline ki-magnifier fs-2"></i>
                    {{ trans('sw.check_availability') }}
                </button>
                <button type="submit" class="btn btn-primary" id="btnSubmit">
                    <i class="ki-outline ki-check fs-2"></i>
                    {{ trans('sw.save') }}
                </button>
            </div>
        </div>
        <!--end::Actions-->
    </div>
    <!--end::Main column-->
</form>
<!--end::Reservation Form-->
@endsection

@section('styles')
<style>
.slot-btn { 
    min-width: 110px; 
    font-size: 0.875rem;
}
.slot-free { 
    border: 1px solid #50cd89; 
    color: #50cd89;
}
.slot-free.active {
    background-color: #50cd89;
    color: #ffffff;
}
.slot-busy { 
    border: 1px solid #e4e6ef; 
    color: #a1a5b7; 
    cursor: not-allowed; 
    opacity: 0.6;
}
</style>
@endsection

@section('scripts')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function(){
    let selectedActivityDuration = null;
    let selectedActivityPrice = null;
    let checkTimeout = null;

    // Toggle client blocks based on client type
    function toggleClientBlocks() {
        const type = $('#client_type').val();
        if (type === 'member') {
            $('#memberBlock').show();
            $('#nonMemberBlock').hide();
            $('#member_id').prop('required', true);
            $('#non_member_id').prop('required', false);
            // Clear non-member selection when switching to member
            $('#non_member_id').val(null).trigger('change');
            loadMemberInfo();
        } else if (type === 'non_member') {
            $('#memberBlock').hide();
            $('#nonMemberBlock').show();
            $('#member_id').prop('required', false);
            $('#non_member_id').prop('required', true);
            // Clear member selection when switching to non-member
            $('#member_id').val(null).trigger('change');
            $('#memberInfo').addClass('d-none');
        } else {
            $('#memberBlock').hide();
            $('#nonMemberBlock').hide();
            $('#member_id').prop('required', false);
            $('#non_member_id').prop('required', false);
            $('#memberInfo').addClass('d-none');
        }
    }
    
    // Initialize on page load
    toggleClientBlocks();
    $('#client_type').on('change', toggleClientBlocks);

    // Initialize Select2 with search for all select2 dropdowns
    $(document).ready(function() {
        // Prepare initial member data if exists
        var initialMemberData = [];
        @if(isset($reservation) && $reservation->member_id && $members->count() > 0)
            @foreach($members as $m)
                initialMemberData.push({
                    id: {{ $m->id }},
                    text: '{{ addslashes($m->name) }}'@if($m->code) + ' ({{ $m->code }})'@endif
                });
            @endforeach
        @endif

        // Initialize Select2 for member dropdown with AJAX
        $('#member_id').select2({
            placeholder: '{{ trans('sw.select_member') }}',
            allowClear: true,
            minimumInputLength: 0,
            data: initialMemberData,
            ajax: {
                url: "{{ route('sw.reservation.loadMembers') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term || '',
                        page: params.page || 1
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.results,
                        pagination: {
                            more: false
                        }
                    };
                },
                cache: true
            },
            language: {
                searching: function() {
                    return '{{ trans('sw.searching') }}...';
                },
                noResults: function() {
                    return '{{ trans('sw.no_results_found') }}';
                }
            }
        });

        // Prepare initial non-member data if exists
        var initialNonMemberData = [];
        @if(isset($reservation) && $reservation->non_member_id && $nonMembers->count() > 0)
            @foreach($nonMembers as $nm)
                initialNonMemberData.push({
                    id: {{ $nm->id }},
                    text: '{{ addslashes($nm->name) }}'
                });
            @endforeach
        @endif

        // Initialize Select2 for non-member dropdown with AJAX
        $('#non_member_id').select2({
            placeholder: '{{ trans('sw.select_non_member') }}',
            allowClear: true,
            minimumInputLength: 0,
            data: initialNonMemberData,
            ajax: {
                url: "{{ route('sw.reservation.loadNonMembers') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term || '',
                        page: params.page || 1
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.results,
                        pagination: {
                            more: false
                        }
                    };
                },
                cache: true
            },
            language: {
                searching: function() {
                    return '{{ trans('sw.searching') }}...';
                },
                noResults: function() {
                    return '{{ trans('sw.no_results_found') }}';
                }
            }
        });

        // Initialize Select2 for activity dropdown
        $('#activity_id').select2({
            placeholder: '{{ trans('sw.select_activity') }}',
            allowClear: true,
            minimumResultsForSearch: 0,
            language: {
                searching: function() {
                    return '{{ trans('sw.searching') }}...';
                },
                noResults: function() {
                    return '{{ trans('sw.no_results_found') }}';
                }
            }
        });

    });

    // Load member info when member is selected
    $('#member_id').on('change', function() {
        loadMemberInfo();
    });

    function loadMemberInfo() {
        const memberId = $('#member_id').val();
        if (memberId) {
            const selectedOption = $('#member_id option:selected');
            const memberName = selectedOption.text();
            $('#memberInfoText').text('{{ trans('sw.member') }}: ' + memberName);
            // TODO: Load member service info if available
            $('#memberServiceInfo').text('{{ trans('sw.checking_service_balance') }}...');
            $('#memberInfo').removeClass('d-none');
        } else {
            $('#memberInfo').addClass('d-none');
        }
    }

    // Activity selection handler
    $('#activity_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const activityId = $(this).val();
        selectedActivityDuration = parseInt(selectedOption.data('duration')) || 60;
        selectedActivityPrice = parseFloat(selectedOption.data('price')) || 0;

        if (activityId) {
            const activityName = selectedOption.text();
            $('#activityName').text(activityName);
            $('#activityDuration').text(selectedActivityDuration + ' {{ trans('sw.minutes') }}');
            $('#activityPrice').text(selectedActivityPrice.toFixed(2) + ' {{ trans('sw.app_currency') ?? 'SAR' }}');
            $('#activityInfo').removeClass('d-none');
            
            // Show auto-calculate button
            if ($('#start_time').val()) {
                $('#btnAutoCalc').show();
            }
        } else {
            $('#activityInfo').addClass('d-none');
            $('#btnAutoCalc').hide();
        }

        // Auto-check availability if all fields filled
        checkAvailabilityDelayed();
    });

    // Auto-calculate end time based on start time and activity duration
    $('#start_time').on('change', function() {
        if (selectedActivityDuration && $(this).val()) {
            $('#btnAutoCalc').show();
        } else {
            $('#btnAutoCalc').hide();
        }
        checkAvailabilityDelayed();
    });

    $('#btnAutoCalc').on('click', function() {
        const startTime = $('#start_time').val();
        if (!startTime || !selectedActivityDuration) return;

        const [hours, minutes] = startTime.split(':');
        const startDate = new Date();
        startDate.setHours(parseInt(hours), parseInt(minutes), 0, 0);
        
        const endDate = new Date(startDate.getTime() + selectedActivityDuration * 60000);
        const endTimeStr = String(endDate.getHours()).padStart(2, '0') + ':' + String(endDate.getMinutes()).padStart(2, '0');
        
        $('#end_time').val(endTimeStr);
        checkAvailability();
    });

    // Date change handler
    $('#reservation_date').on('change', function() {
        checkAvailabilityDelayed();
    });

    // Time change handlers with delayed availability check
    $('#end_time').on('change', function() {
        checkAvailabilityDelayed();
    });

    // Delayed availability check to avoid too many requests
    function checkAvailabilityDelayed() {
        clearTimeout(checkTimeout);
        checkTimeout = setTimeout(function() {
            if ($('#activity_id').val() && $('#reservation_date').val() && $('#start_time').val() && $('#end_time').val()) {
                checkAvailability();
            } else {
                hideAvailabilityAlerts();
            }
        }, 800);
    }

    // Check availability function
    function checkAvailability() {
        const activityId = $('#activity_id').val();
        const date = $('#reservation_date').val();
        const startTime = $('#start_time').val();
        const endTime = $('#end_time').val();

        if (!activityId || !date || !startTime || !endTime) {
            hideAvailabilityAlerts();
            return;
        }

        // Validate time logic
        if (startTime >= endTime) {
            showConflictAlert('{{ trans('sw.end_time_must_be_after_start_time') }}');
            return;
        }

        // Check for conflicts via AJAX
        fetch("{{ route('sw.reservation.check') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                activity_id: activityId,
                reservation_date: date,
                start_time: startTime,
                end_time: endTime,
                @if(isset($reservation) && $reservation->id)
                reservation_id: {{ $reservation->id }},
                @endif
            })
        })
        .then(r => r.json())
        .then(res => {
            if (res.conflict) {
                showConflictAlert(res.message || '{{ trans('sw.time_conflict_detected') }}');
            } else {
                showAvailableAlert(res.message || '{{ trans('sw.time_slot_available') }}');
            }
        })
        .catch(() => {
            hideAvailabilityAlerts();
        });
    }

    function showConflictAlert(message) {
        $('#conflictAlert span').text(message);
        $('#conflictAlert').removeClass('d-none');
        $('#availableAlert').addClass('d-none');
    }

    function showAvailableAlert(message) {
        $('#availableAlert span').text(message);
        $('#availableAlert').removeClass('d-none');
        $('#conflictAlert').addClass('d-none');
    }

    function hideAvailabilityAlerts() {
        $('#conflictAlert').addClass('d-none');
        $('#availableAlert').addClass('d-none');
    }

    // Manual check availability button
    $('#btnCheckAvailability').on('click', function() {
        if (!$('#activity_id').val() || !$('#reservation_date').val() || !$('#start_time').val() || !$('#end_time').val()) {
            Swal.fire({
                icon: 'warning',
                title: '{{ trans('sw.incomplete_form') }}',
                text: '{{ trans('sw.fill_all_fields_first') }}'
            });
            return;
        }
        checkAvailability();
    });

    // Form validation before submit
    $('#reservationForm').on('submit', function(e) {
        // Check if there's a conflict
        if (!$('#conflictAlert').hasClass('d-none')) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: '{{ trans('sw.time_conflict') }}',
                text: '{{ trans('sw.cannot_submit_with_conflict') }}'
            });
            return false;
        }

        // Validate client selection
        const clientType = $('#client_type').val();
        if (clientType === 'member' && !$('#member_id').val()) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: '{{ trans('sw.missing_information') }}',
                text: '{{ trans('sw.please_select_member') }}'
            });
            return false;
        }
        if (clientType === 'non_member' && !$('#non_member_id').val()) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: '{{ trans('sw.missing_information') }}',
                text: '{{ trans('sw.please_select_non_member') }}'
            });
            return false;
        }

        // Show loading state
        $('#btnSubmit').prop('disabled', true).html('<i class="ki-outline ki-loading fs-2"></i> {{ trans('sw.saving') }}...');
    });
});
</script>
@endsection

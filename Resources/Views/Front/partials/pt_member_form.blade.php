@php
    use Carbon\Carbon;
    use Illuminate\Support\Collection;

    $mode = $mode ?? 'create';
    $isEdit = $mode === 'edit';
    $formId = $formId ?? uniqid('pt-member-form-');
    $swUser = $swUser ?? auth('sw')->user();
    $memberModel = $member ?? new \Modules\Software\Models\GymPTMember();
    $paymentCollection = $paymentTypes ?? $payment_types ?? collect();
    if (! $paymentCollection instanceof Collection) {
        $paymentCollection = collect($paymentCollection);
    }

    $memberInfo = optional($memberModel->member)->member_subscription_info;
    $memberSubscription = optional($memberInfo)->subscription;

    $selectedSubscriptionId = old('pt_subscription_id', $memberModel->pt_subscription_id);
    $selectedClassId = old('pt_class_id', $memberModel->class_id ?? $memberModel->pt_class_id);
    $selectedTrainerId = old('pt_trainer_id', $memberModel->pt_trainer_id);
    $selectedClassTrainerId = old('class_trainer_id', $memberModel->class_trainer_id);

    $initialTotalSessions = (int) old('total_sessions', $memberModel->total_sessions ?? $memberModel->classes ?? 0);
    $initialUsedSessions = $isEdit ? (int) ($memberModel->sessions_used ?? 0) : 0;
    $initialRemainingSessions = (int) old(
        'remaining_sessions',
        $memberModel->sessions_remaining ?? max($initialTotalSessions - $initialUsedSessions, 0)
    );

    $joiningDateValue = old(
        'joining_date',
        optional($memberModel->start_date)->format('Y-m-d')
            ?? optional($memberModel->joining_date)->format('Y-m-d')
            ?? Carbon::now()->format('Y-m-d')
    );
    $expireDateValue = old(
        'expire_date',
        optional($memberModel->end_date)->format('Y-m-d')
            ?? optional($memberModel->expire_date)->format('Y-m-d')
            ?? Carbon::now()->format('Y-m-d')
    );

    $ptClassesPayload = $classes->mapWithKeys(function ($class) {
        return [
            $class->id => [
                'id'                 => $class->id,
                'name'               => $class->name,
                'pt_subscription_id' => $class->pt_subscription_id,
                'class_type'         => $class->class_type,
                'pricing_type'       => $class->pricing_type,
                'total_sessions'     => $class->total_sessions ?? $class->classes ?? 0,
                'classes'            => $class->classes,
                'max_members'        => $class->max_members,
                'price'              => (float) ($class->price ?? 0),
                'schedule'           => $class->schedule ?? [],
                'trainers'           => $class->classTrainers->map(function ($assignment) {
                    return [
                        'assignment_id' => $assignment->id,
                        'trainer_id'    => $assignment->trainer_id,
                        'name'          => optional($assignment->trainer)->name,
                        'percentage'    => $assignment->commission_rate,
                        'session_count' => $assignment->session_count,
                        'is_active'     => (bool) $assignment->is_active,
                    ];
                })->values()->toArray(),
            ],
        ];
    })->toArray();

    $trainerList = $trainers->map(function ($trainer) {
        return [
            'id'         => $trainer->id,
            'name'       => $trainer->name,
            'percentage' => $trainer->percentage ?? 0,
        ];
    })->values()->toArray();

    $memberSnapshot = [
        'name'             => optional($memberModel->member)->name,
        'barcode'          => optional($memberModel->member)->code,
        'membership'       => optional($memberSubscription)->name,
        'expire_date'      => optional($memberInfo)->expire_date ? Carbon::parse($memberInfo->expire_date)->format('Y-m-d') : null,
        'amount_remaining' => optional($memberInfo)->amount_remaining,
        'status_name'      => optional($memberInfo)->status_name,
        'status_code'      => optional($memberInfo)->status,
        'joining_date'     => optional($memberInfo)->joining_date ? Carbon::parse($memberInfo->joining_date)->format('Y-m-d') : null,
    ];

    $invoice = $memberModel->zatcaInvoice ?? null;

    $formAction = $formAction ?? ($isEdit ? route('sw.updatePTMember', $memberModel->id) : route('sw.storePTMember'));
    $formMethod = strtoupper($formMethod ?? ($isEdit ? 'PUT' : 'POST'));

    $config = [
        'formId'                 => $formId,
        'mode'                   => $mode,
        'selectedSubscriptionId' => $selectedSubscriptionId,
        'selectedClassId'        => $selectedClassId,
        'selectedTrainerId'      => $selectedTrainerId,
        'selectedClassTrainerId' => $selectedClassTrainerId,
        'initialSessions'        => [
            'total'     => $initialTotalSessions,
            'used'      => $initialUsedSessions,
            'remaining' => $initialRemainingSessions,
        ],
        'classes'                => $ptClassesPayload,
        'trainers'               => $trainerList,
        'memberSnapshot'         => $memberSnapshot,
        'urls'                   => [
            'classActiveMembers' => route('sw.ptClassActiveMemberAjax'),
            'memberLookup'       => route('sw.getPTMemberAjax'),
            'loyaltyRate'        => route('sw.getMemberLoyaltyInfo'),
        ],
        'loyaltyEnabled'         => (bool) data_get($mainSettings ?? [], 'active_loyalty'),
        'vatPercentage'          => (float) data_get($mainSettings ?? [], 'vat_details.vat_percentage', 0),
        'i18n'                   => [
            'chooseOption'      => trans('admin.choose') . '...',
            'noScheduleDefined' => trans('sw.no_schedule_defined'),
            'days'              => [
                0 => trans('sw.sun'),
                1 => trans('sw.mon'),
                2 => trans('sw.tue'),
                3 => trans('sw.wed'),
                4 => trans('sw.thurs'),
                5 => trans('sw.fri'),
                6 => trans('sw.sat'),
            ],
        ],
    ];
@endphp

<div id="{{ $formId }}" class="pt-member-form-wrapper">
    <form method="post" action="{{ $formAction }}" class="form d-flex flex-column flex-lg-row" enctype="multipart/form-data">
        @csrf
        @if($formMethod !== 'POST')
            @method($formMethod)
        @endif

        <input type="hidden" name="total_sessions" id="total_sessions_input" value="{{ $initialTotalSessions }}">
        <input type="hidden" name="remaining_sessions" id="remaining_sessions_input" value="{{ $initialRemainingSessions }}">
        <input type="hidden" name="class_trainer_id" id="class_trainer_id_input" value="{{ $selectedClassTrainerId }}">

        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-375px mb-7 me-lg-10">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2 class="fw-bold">{{ trans('sw.pt_member') }}</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="mb-7">
                        <label class="required form-label">
                            {{ trans('sw.member_id') }}
                            <i class="ki-outline ki-information fs-6 text-muted ms-1"
                               data-bs-toggle="tooltip"
                               title="{{ trans('sw.pt_member_member_id_hint') }}"></i>
                        </label>
                        <input id="member_id"
                               name="member_id"
                               type="text"
                               class="form-control"
                               value="{{ old('member_id', $memberModel->member_id) }}"
                               placeholder="{{ trans('sw.enter_member_id') }}"
                               autocomplete="off"
                               {{ $isEdit ? 'readonly' : '' }}
                               required>
                    </div>

                    <div class="pt-member-snapshot bg-light rounded p-4">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-semibold text-gray-600">{{ trans('sw.name') }}</span>
                            <span id="snapshot_name" class="fw-bold text-gray-800">—</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-semibold text-gray-600">{{ trans('sw.barcode') }}</span>
                            <span id="snapshot_barcode" class="fw-bold text-gray-800">—</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-semibold text-gray-600">{{ trans('sw.membership') }}</span>
                            <span id="snapshot_membership" class="fw-bold text-gray-800">—</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-semibold text-gray-600">{{ trans('sw.expire_date') }}</span>
                            <span id="snapshot_expire_date" class="fw-bold text-gray-800">—</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-semibold text-gray-600">{{ trans('sw.amount_remaining') }}</span>
                            <span id="snapshot_amount_remaining" class="fw-bold text-gray-800">—</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold text-gray-600">{{ trans('sw.status') }}</span>
                            <span id="snapshot_status" class="badge badge-light">—</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-flush py-4 pt-session-summary-card" id="session_summary_card" style="display:none;">
                <div class="card-header">
                    <div class="card-title">
                        <h2 class="fw-bold">{{ trans('sw.sessions_summary') }}</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-semibold text-gray-600">{{ trans('sw.pt_training_name') }}</span>
                        <span id="summary_class_name" class="fw-bold text-gray-800">—</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-semibold text-gray-600">{{ trans('sw.class_type') }}</span>
                        <span id="summary_class_type" class="fw-bold text-gray-800">—</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-semibold text-gray-600">{{ trans('sw.sessions_total') }}</span>
                        <span id="summary_total_sessions" class="fw-bold text-gray-800">0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-semibold text-gray-600">{{ trans('sw.sessions_used') }}</span>
                        <span id="summary_used_sessions" class="fw-bold text-gray-800">0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-semibold text-gray-600">{{ trans('sw.sessions_remaining') }}</span>
                        <span id="summary_remaining_sessions" class="fw-bold text-gray-800">0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-semibold text-gray-600">{{ trans('sw.max_members') }}</span>
                        <span id="summary_max_members" class="fw-bold text-gray-800">—</span>
                    </div>
                    <div class="pt-member-schedule mt-4">
                        <h5 class="fw-semibold text-gray-600 mb-3">{{ trans('sw.schedule') }}</h5>
                        <div id="summary_schedule" class="border border-dashed rounded p-3 bg-light text-gray-700">
                            <span>{{ trans('sw.no_schedule_defined') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2 class="fw-bold">{{ trans('sw.subscription_details') }}</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="row g-5">
                        <div class="col-lg-6">
                            <label class="required form-label">
                                {{ trans('sw.pt_subscription') }}
                                <i class="ki-outline ki-information fs-6 text-muted ms-1"
                                   data-bs-toggle="tooltip"
                                   title="{{ trans('sw.pt_member_subscription_hint') }}"></i>
                            </label>
                            <select id="pt_subscription_id"
                                    name="pt_subscription_id"
                                    class="form-select select2"
                                    data-placeholder="{{ trans('admin.choose')}}..."
                                    required>
                                <option value="">{{ trans('admin.choose')}}...</option>
                                @foreach($subscriptions as $subscription)
                                    <option value="{{ $subscription->id }}" @selected($subscription->id == $selectedSubscriptionId)>
                                        {{ $subscription->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6">
                            <label class="required form-label">
                                {{ trans('sw.pt_class') }}
                                <i class="ki-outline ki-information fs-6 text-muted ms-1"
                                   data-bs-toggle="tooltip"
                                   title="{{ trans('sw.pt_member_class_hint') }}"></i>
                            </label>
                            <select id="pt_class_id"
                                    name="pt_class_id"
                                    class="form-select select2"
                                    data-placeholder="{{ trans('admin.choose')}}..."
                                    required>
                            </select>
                            <div id="class_limit_msg" class="form-text mt-3"></div>
                        </div>
                    </div>

                    <div class="row g-5 mt-5">
                        <div class="col-lg-6">
                            <label class="required form-label">
                                {{ trans('sw.pt_trainer') }}
                                <i class="ki-outline ki-information fs-6 text-muted ms-1"
                                   data-bs-toggle="tooltip"
                                   title="{{ trans('sw.pt_member_trainer_hint') }}"></i>
                            </label>
                            <select id="pt_trainer_id"
                                    name="pt_trainer_id"
                                    class="form-select select2"
                                    data-placeholder="{{ trans('admin.choose')}}..."
                                    required>
                            </select>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">
                                {{ trans('sw.trainer_percentage_for_member') }}
                                <i class="ki-outline ki-information fs-6 text-muted ms-1"
                                   data-bs-toggle="tooltip"
                                   title="{{ trans('sw.pt_member_trainer_percentage_hint') }}"></i>
                            </label>
                            <div class="input-group">
                                <input id="trainer_percentage"
                                       name="trainer_percentage"
                                       type="number"
                                       min="0"
                                       max="100"
                                       step="0.01"
                                       value="{{ old('trainer_percentage', $memberModel->trainer_percentage) }}"
                                       class="form-control"
                                       placeholder="{{ trans('sw.enter_percentage') }}">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-5 mt-5">
                        <div class="col-lg-6">
                            <label class="required form-label">
                                {{ trans('sw.membership_date') }}
                                <i class="ki-outline ki-information fs-6 text-muted ms-1"
                                   data-bs-toggle="tooltip"
                                   title="{{ trans('sw.pt_member_membership_dates_hint') }}"></i>
                            </label>
                            <div class="input-group input-daterange">
                                <input type="text"
                                       class="form-control"
                                       id="joining_date_input"
                                       name="joining_date"
                                       value="{{ $joiningDateValue }}"
                                       placeholder="{{ trans('sw.joining_date') }}"
                                       autocomplete="off"
                                       required>
                                <span class="input-group-text">{{ trans('sw.to') }}</span>
                                <input type="text"
                                       class="form-control"
                                       id="expire_date_input"
                                       name="expire_date"
                                       value="{{ $expireDateValue }}"
                                       placeholder="{{ trans('sw.expire_date') }}"
                                       autocomplete="off"
                                       required>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">
                                {{ trans('sw.notes') }}
                                <i class="ki-outline ki-information fs-6 text-muted ms-1"
                                   data-bs-toggle="tooltip"
                                   title="{{ trans('sw.pt_member_notes_hint') }}"></i>
                            </label>
                            <textarea name="notes"
                                      rows="2"
                                      maxlength="255"
                                      class="form-control"
                                      placeholder="{{ trans('sw.notes_placeholder') }}">{{ old('notes', $memberModel->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2 class="fw-bold">{{ trans('sw.payment_details') }}</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="row g-5">
                        <div class="col-lg-6">
                            <label class="form-label">
                                {{ trans('sw.discount_value') }}
                                <i class="ki-outline ki-information fs-6 text-muted ms-1"
                                   data-bs-toggle="tooltip"
                                   title="{{ trans('sw.pt_member_discount_value_hint') }}"></i>
                            </label>
                            <input id="discount_value"
                                   name="discount_value"
                                   type="number"
                                   min="0"
                                   step="0.01"
                                   value="{{ old('discount_value', $memberModel->discount_value ?? 0) }}"
                                   class="form-control"
                                   placeholder="{{ trans('sw.discount_value') }}">

                            @if(($discounts ?? collect())->isNotEmpty() && (in_array('editPTMemberDiscountGroup', (array) ($swUser->permissions ?? [])) || ($swUser->is_super_user ?? false)))
                                <div class="mt-5">
                                    <label class="form-label">
                                        {{ trans('sw.discount') }}
                                        <i class="ki-outline ki-information fs-6 text-muted ms-1"
                                           data-bs-toggle="tooltip"
                                           title="{{ trans('sw.pt_member_group_discount_hint') }}"></i>
                                    </label>
                                    <select id="group_discount_id"
                                            name="group_discount_id"
                                            class="form-select select2"
                                            data-placeholder="{{ trans('admin.choose')}}...">
                                        <option value="">{{ trans('admin.choose')}}...</option>
                                        @foreach($discounts as $discount)
                                            <option value="{{ $discount->id }}"
                                                    data-type="{{ $discount->type }}"
                                                    data-amount="{{ $discount->amount }}"
                                                    @selected($discount->id == old('group_discount_id'))>
                                                {{ $discount->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="mt-5">
                                <label class="required form-label">
                                    {{ trans('sw.amount_paid') }}
                                    <i class="ki-outline ki-information fs-6 text-muted ms-1"
                                       data-bs-toggle="tooltip"
                                       title="{{ trans('sw.pt_member_amount_paid_hint') }}"></i>
                                </label>
                                <input id="amount_paid_input"
                                       name="amount_paid"
                                       type="number"
                                       min="0"
                                       step="0.01"
                                       value="{{ old('amount_paid', $memberModel->amount_paid ?? 0) }}"
                                       class="form-control"
                                       placeholder="{{ trans('sw.enter_amount_paid') }}"
                                       required>
                            </div>

                            <div class="mt-5">
                                <label class="required form-label">
                                    {{ trans('sw.payment_type') }}
                                    <i class="ki-outline ki-information fs-6 text-muted ms-1"
                                       data-bs-toggle="tooltip"
                                       title="{{ trans('sw.pt_member_payment_type_hint') }}"></i>
                                </label>
                                <select id="payment_type"
                                        name="payment_type"
                                        class="form-select select2"
                                        data-placeholder="{{ trans('admin.choose')}}..."
                                        required>
                                    <option value="">{{ trans('admin.choose')}}...</option>
                                    @foreach($paymentCollection as $paymentType)
                                        @php
                                            $paymentTypeId = $paymentType->payment_id ?? $paymentType->id ?? ($paymentType['payment_id'] ?? $paymentType['id'] ?? null);
                                            $paymentTypeName = $paymentType->name ?? ($paymentType['name'] ?? __('Unknown'));
                                        @endphp
                                        <option value="{{ $paymentTypeId }}" @selected($paymentTypeId == old('payment_type', $memberModel->payment_type))>
                                            {{ $paymentTypeName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="p-5 bg-light rounded">
                                <div class="d-flex justify-content-between fs-6">
                                    <span class="fw-semibold text-gray-600">{{ trans('sw.price') }}</span>
                                    <span id="price_base" class="fw-bold text-gray-800">0.00</span>
                                </div>
                                <div id="price_after_discount_row" class="d-flex justify-content-between fs-6 mt-3" style="display:none;">
                                    <span class="fw-semibold text-gray-600">{{ trans('sw.after_discount') }}</span>
                                    <span id="price_after_discount" class="fw-bold text-gray-800">0.00</span>
                                </div>
                                <div class="d-flex justify-content-between fs-6 mt-3">
                                    <span class="fw-semibold text-gray-600">{{ trans('sw.include_vat', ['vat' => data_get($mainSettings ?? [], 'vat_details.vat_percentage', 0)]) }}</span>
                                    <span id="price_with_vat" class="fw-bold text-gray-800">0.00</span>
                                </div>
                            </div>

                            <div class="mt-5">
                                <label class="form-label">
                                    {{ trans('sw.amount_remaining') }}
                                    <i class="ki-outline ki-information fs-6 text-muted ms-1"
                                       data-bs-toggle="tooltip"
                                       title="{{ trans('sw.pt_member_amount_remaining_hint') }}"></i>
                                </label>
                                <input id="amount_remaining_input"
                                       type="number"
                                       min="0"
                                       step="0.01"
                                       class="form-control"
                                       value="{{ old('amount_remaining', $memberModel->amount_remaining ?? 0) }}"
                                       readonly>
                            </div>

                            @if(data_get($mainSettings ?? [], 'active_loyalty'))
                                <div id="pt_member_loyalty_earning_info" class="alert alert-success d-none mt-5">
                                    <div class="d-flex align-items-center">
                                        <i class="ki-outline ki-gift fs-2x me-3"></i>
                                        <div>
                                            <strong class="d-block">{{ trans('sw.points_earning_info') }}</strong>
                                            <div>{!! trans('sw.you_will_earn_points', ['points' => '<span id="pt_member_estimated_earning_points">0</span>']) !!}</div>
                                            <small id="pt_member_loyalty_earning_rate" class="text-muted d-block mt-1"></small>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if($isEdit && $invoice)
                <div class="card bg-light-primary p-5">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-4">
                        <div class="d-flex flex-column">
                            <h4 class="text-primary mb-2">{{ trans('sw.zatca_invoice_details') }}</h4>
                            <p class="mb-1 fw-semibold text-gray-800">{{ trans('sw.invoice_number') }}: {{ $invoice->invoice_number }}</p>
                            <p class="mb-1 text-gray-700">{{ trans('sw.total_amount') }}: {{ number_format($invoice->total_amount, 2) }}</p>
                            <p class="mb-1 text-gray-700">{{ trans('sw.vat_amount') }}: {{ number_format($invoice->vat_amount, 2) }}</p>
                            <p class="mb-1 text-gray-700">{{ trans('sw.status') }}: {{ $invoice->zatca_status }}</p>
                            @if($invoice->zatca_sent_at)
                                <p class="mb-0 text-gray-700">{{ trans('sw.sent_at') }}: {{ $invoice->zatca_sent_at->format('Y-m-d H:i') }}</p>
                            @endif
                        </div>
                        @if($invoice->zatca_qr_code)
                            <div class="flex-shrink-0">
                                <img src="data:image/png;base64,{{ $invoice->zatca_qr_code }}" alt="ZATCA QR Code" width="120" height="120" class="img-thumbnail">
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="d-flex justify-content-end">
                <button type="reset" class="btn btn-light me-3">{{ trans('admin.reset') }}</button>
                <button type="submit" class="btn btn-primary">
                    <i class="ki-outline ki-check fs-2 me-1"></i>{{ trans('global.save') }}
                </button>
            </div>
        </div>
    </form>
</div>

@once('pt-member-form-styles')
    @section('sub_styles')
        @parent
        <style>
            .pt-member-form-wrapper .pt-member-snapshot .fw-semibold {
                min-width: 120px;
            }
            .pt-member-form-wrapper .pt-member-snapshot span,
            .pt-member-form-wrapper .pt-session-summary-card span {
                word-break: break-word;
            }
            .pt-member-form-wrapper .select2-container {
                z-index: auto !important;
            }
            .pt-member-form-wrapper .select2-container--open {
                z-index: auto !important;
            }
            .pt-member-form-wrapper .select2-dropdown {
                z-index: auto !important;
            }
        </style>
    @endsection
@endonce

@once('pt-member-form-scripts')
    @section('sub_scripts')
        @parent
        <script>
            (function ($, bootstrap) {
                'use strict';

                window.ptMemberFormRegistry = window.ptMemberFormRegistry || {};

                function formatCurrency(value) {
                    const number = parseFloat(value);
                    if (Number.isNaN(number)) {
                        return '0.00';
                    }
                    return number.toFixed(2);
                }

                function updateSnapshot(instance, snapshot) {
                    snapshot = snapshot || {};
                    instance.memberSnapshot = snapshot;
                    const statusCode = (snapshot.status_code || '').toString().toLowerCase();
                    const statusName = snapshot.status_name || '—';

                    instance.$snapshotName.text(snapshot.name || '—');
                    instance.$snapshotBarcode.text(snapshot.barcode || '—');
                    instance.$snapshotMembership.text(snapshot.membership || '—');
                    instance.$snapshotExpire.text(snapshot.expire_date || '—');
                    instance.$snapshotAmountRemaining.text(snapshot.amount_remaining !== undefined && snapshot.amount_remaining !== null
                        ? formatCurrency(snapshot.amount_remaining)
                        : '—');
                    instance.$snapshotStatus
                        .text(statusName)
                        .removeClass('badge-success badge-warning badge-danger badge-info badge-secondary badge-light')
                        .addClass(
                            statusCode === 'active' ? 'badge-success'
                                : statusCode === 'expired' ? 'badge-danger'
                                    : statusCode === 'coming' ? 'badge-info'
                                        : 'badge-light'
                        );

                    if (snapshot.joining_date && !instance.joiningDateManuallyChanged) {
                        instance.$joiningDateInput.val(snapshot.joining_date);
                    }
                    if (snapshot.expire_date && !instance.expireDateManuallyChanged) {
                        instance.$expireDateInput.val(snapshot.expire_date);
                    }
                }

                function renderSchedule(instance, schedule) {
                    const container = instance.$summarySchedule;
                    container.empty();
                    const workDays = schedule && schedule.work_days ? schedule.work_days : {};
                    const dayKeys = Object.keys(workDays);

                    if (!dayKeys.length) {
                        container.html('<span>' + instance.i18n.noScheduleDefined + '</span>');
                        return;
                    }

                    dayKeys.sort(function (a, b) {
                        return parseInt(a, 10) - parseInt(b, 10);
                    }).forEach(function (key) {
                        const slot = workDays[key];
                        if (!slot || slot.status === false) {
                            return;
                        }
                        const label = instance.i18n.days[key] || key;
                        const start = slot.start || '--:--';
                        const end = slot.end || '--:--';
                        container.append(
                            '<div class="d-flex justify-content-between mb-2">' +
                                '<span class="fw-semibold text-gray-600">' + label + '</span>' +
                                '<span class="badge badge-light-primary">' + start + ' → ' + end + '</span>' +
                            '</div>'
                        );
                    });

                    if (!container.children().length) {
                        container.html('<span>' + instance.i18n.noScheduleDefined + '</span>');
                    }
                }

                function updateLoyalty(instance, amountPaid) {
                    if (!instance.loyaltyEnabled || !instance.$loyaltyPanel.length) {
                        return;
                    }

                    if (!instance.loyaltyRate || instance.loyaltyRate <= 0 || amountPaid <= 0) {
                        instance.$loyaltyPanel.addClass('d-none');
                        return;
                    }

                    const points = Math.floor(amountPaid / instance.loyaltyRate);
                    if (points > 0) {
                        instance.$loyaltyPoints.text(points);
                        instance.$loyaltyPanel.removeClass('d-none');
                    } else {
                        instance.$loyaltyPanel.addClass('d-none');
                    }
                }

                function calculatePrice(instance, forceFromClass) {
                    const klass = instance.activeClass;
                    const basePrice = klass ? parseFloat(klass.price || 0) : 0;
                    let discountValue = parseFloat(instance.$discountInput.val()) || 0;

                    if (instance.$groupDiscountSelect && instance.$groupDiscountSelect.length) {
                        const option = instance.$groupDiscountSelect.find('option:selected');
                        if (option.length && option.val()) {
                            const type = parseInt(option.data('type'), 10);
                            const amount = parseFloat(option.data('amount')) || 0;
                            discountValue = type === 1 ? basePrice * (amount / 100) : amount;
                            instance.$discountInput.val(formatCurrency(discountValue));
                        }
                    }

                    if (discountValue > basePrice) {
                        discountValue = basePrice;
                        instance.$discountInput.val(formatCurrency(discountValue));
                    }

                    const priceAfterDiscount = basePrice - discountValue;
                    const vatAmount = priceAfterDiscount * (instance.vatPercentage / 100);
                    const totalWithVat = priceAfterDiscount + vatAmount;

                    instance.$priceBase.text(formatCurrency(basePrice));
                    if (discountValue > 0) {
                        instance.$priceAfterDiscountRow.show();
                        instance.$priceAfterDiscount.text(formatCurrency(priceAfterDiscount));
                    } else {
                        instance.$priceAfterDiscountRow.hide();
                    }
                    instance.$priceWithVat.text(formatCurrency(totalWithVat));

                    let amountPaid = parseFloat(instance.$amountPaidInput.val()) || 0;
                    if (forceFromClass || !instance.amountPaidManuallyEdited) {
                        amountPaid = totalWithVat;
                        instance.$amountPaidInput.val(formatCurrency(amountPaid));
                    }

                    const amountRemaining = Math.max(totalWithVat - amountPaid, 0);
                    instance.$amountRemainingInput.val(formatCurrency(amountRemaining));

                    updateLoyalty(instance, amountPaid);
                }

                function clearSummary(instance) {
                    instance.$summaryCard.hide();
                    instance.$summaryClassName.text('—');
                    instance.$summaryClassType.text('—');
                    instance.$summaryTotalSessions.text('0');
                    instance.$summaryUsedSessions.text(instance.initialSessions.used || 0);
                    instance.$summaryRemainingSessions.text(instance.initialSessions.remaining || 0);
                    instance.$summaryMaxMembers.text('—');
                    instance.$summarySchedule.html('<span>' + instance.i18n.noScheduleDefined + '</span>');
                    instance.$totalSessionsInput.val(0);
                    instance.$remainingSessionsInput.val(instance.mode === 'create' ? 0 : instance.initialSessions.remaining || 0);
                }

                function renderTrainerOptions(instance, classData) {
                    instance.rebuildingTrainerOptions = true;
                    instance.$trainerSelect.empty().append(new Option(instance.i18n.chooseOption, ''));

                    const assignments = classData && Array.isArray(classData.trainers)
                        ? classData.trainers.filter(function (item) {
                            return item && item.is_active;
                        })
                        : [];

                    const source = assignments.length ? assignments : instance.trainers;
                    let fallbackTrainerId = source.length === 1 ? (source[0].trainer_id ?? source[0].id) : null;

                    source.forEach(function (item) {
                        const trainerId = item.trainer_id ?? item.id;
                        if (!trainerId) {
                            return;
                        }
                        const option = new Option(item.name || ('#' + trainerId), trainerId, false, false);
                        $(option).attr('data-percentage', item.percentage ?? 0);
                        if (item.assignment_id) {
                            $(option).attr('data-assignment-id', item.assignment_id);
                        }
                        instance.$trainerSelect.append(option);
                    });

                    if (instance.selectedClassTrainerId) {
                        const option = instance.$trainerSelect.find('[data-assignment-id="' + instance.selectedClassTrainerId + '"]');
                        if (option.length) {
                            option.prop('selected', true);
                        }
                    } else if (instance.selectedTrainerId) {
                        instance.$trainerSelect.val(instance.selectedTrainerId);
                    } else if (fallbackTrainerId) {
                        instance.$trainerSelect.val(fallbackTrainerId);
                    }

                    instance.$trainerSelect.trigger('change.select2');
                    instance.rebuildingTrainerOptions = false;
                    handleTrainerChange(instance);
                }

                function applyClass(instance, classId) {
                    const cls = classId ? instance.classes[classId] : null;
                    instance.activeClass = cls || null;
                    instance.selectedClassId = cls ? cls.id : null;

                    if (!cls) {
                        clearSummary(instance);
                        renderTrainerOptions(instance, null);
                        calculatePrice(instance, true);
                        return;
                    }

                    instance.$summaryCard.show();
                    instance.$summaryClassName.text(cls.name || '—');
                    instance.$summaryClassType.text((cls.class_type || '—').toString().toUpperCase());

                    const totalSessions = parseInt(cls.total_sessions || cls.classes || 0, 10) || 0;
                    const usedSessions = instance.mode === 'edit' ? (instance.initialSessions.used || 0) : 0;
                    const remainingSessions = instance.mode === 'edit'
                        ? Math.max(totalSessions - usedSessions, 0)
                        : totalSessions;

                    instance.$summaryTotalSessions.text(totalSessions);
                    instance.$summaryUsedSessions.text(usedSessions);
                    instance.$summaryRemainingSessions.text(remainingSessions);
                    instance.$summaryMaxMembers.text(cls.max_members ?? '—');

                    instance.$totalSessionsInput.val(totalSessions);
                    instance.$remainingSessionsInput.val(remainingSessions);

                    renderSchedule(instance, cls.schedule || {});
                    renderTrainerOptions(instance, cls);

                    if (cls.id) {
                        $.get(instance.urls.classActiveMembers, { pt_class_id: cls.id })
                            .done(function (response) {
                                instance.$classLimitMsg.html(response);
                            })
                            .fail(function () {
                                instance.$classLimitMsg.empty();
                            });
                    }

                    calculatePrice(instance, true);
                }

                function buildClassOptions(instance, subscriptionId) {
                    instance.rebuildingClassOptions = true;
                    instance.$classSelect.empty().append(new Option(instance.i18n.chooseOption, ''));

                    let firstMatch = null;
                    Object.keys(instance.classes).forEach(function (key) {
                        const cls = instance.classes[key];
                        if (subscriptionId && cls.pt_subscription_id != subscriptionId) {
                            return;
                        }
                        if (!firstMatch) {
                            firstMatch = cls;
                        }
                        const option = new Option(cls.name, cls.id, false, cls.id == instance.selectedClassId);
                        instance.$classSelect.append(option);
                    });

                    instance.$classSelect.trigger('change.select2');
                    instance.rebuildingClassOptions = false;

                    const candidate = instance.selectedClassId && instance.classes[instance.selectedClassId]
                        ? instance.selectedClassId
                        : (firstMatch ? firstMatch.id : '');

                    if (candidate) {
                        instance.$classSelect.val(candidate).trigger('change');
                    } else {
                        instance.$classSelect.val('').trigger('change');
                    }
                }

                function handleSubscriptionChange(instance) {
                    instance.selectedSubscriptionId = instance.$subscriptionSelect.val() || null;
                    instance.selectedClassId = null;
                    instance.selectedClassTrainerId = null;
                    buildClassOptions(instance, instance.selectedSubscriptionId);
                }

                function handleClassChange(instance) {
                    if (instance.rebuildingClassOptions) {
                        return;
                    }
                    instance.selectedClassId = instance.$classSelect.val() || null;
                    instance.selectedClassTrainerId = null;
                    applyClass(instance, instance.selectedClassId);
                }

                function handleTrainerChange(instance) {
                    if (instance.rebuildingTrainerOptions) {
                        return;
                    }
                    const option = instance.$trainerSelect.find('option:selected');
                    const trainerId = option.val() || null;
                    const percentage = parseFloat(option.data('percentage')) || 0;
                    const assignmentId = option.data('assignmentId') || null;

                    instance.selectedTrainerId = trainerId;
                    instance.selectedClassTrainerId = assignmentId;
                    instance.$classTrainerInput.val(assignmentId || '');

                    if (!instance.trainerPercentageManuallyEdited && percentage) {
                        instance.$trainerPercentageInput.val(formatCurrency(percentage));
                    }
                }

                function handleMemberLookup(instance, code) {
                    if (!code || instance.memberLookupInFlight) {
                        if (!code) {
                            updateSnapshot(instance, null);
                        }
                        return;
                    }

                    instance.memberLookupInFlight = true;
                    $.get(instance.urls.memberLookup, { member_id: code })
                        .done(function (result) {
                            if (result && result.member_subscription_info) {
                                const info = result.member_subscription_info;
                                updateSnapshot(instance, {
                                    name: result.name,
                                    barcode: result.code,
                                    membership: info.subscription ? info.subscription.name : null,
                                    expire_date: info.expire_date_str || info.expire_date || null,
                                    amount_remaining: info.amount_remaining,
                                    status_name: info.status_name,
                                    status_code: info.status,
                                    joining_date: info.joining_date ? info.joining_date.split('T')[0] : null,
                                });
                            } else {
                                updateSnapshot(instance, null);
                            }
                        })
                        .fail(function () {
                            updateSnapshot(instance, null);
                        })
                        .always(function () {
                            instance.memberLookupInFlight = false;
                        });
                }

                function fetchLoyaltyRate(instance) {
                    if (!instance.loyaltyEnabled || instance.loyaltyRateFetched || !instance.$loyaltyPanel.length) {
                        return;
                    }

                    instance.loyaltyRateFetched = true;
                    $.get(instance.urls.loyaltyRate, { member_id: instance.$memberIdInput.val() || 0 })
                        .done(function (response) {
                            const rate = parseFloat(response.money_to_point_rate);
                            if (rate && rate > 0) {
                                instance.loyaltyRate = rate;
                                if (instance.$loyaltyRateLabel.length) {
                                    instance.$loyaltyRateLabel.text('1 ' + (response.currency || '') + ' ≈ ' + formatCurrency(rate) + ' pts');
                                }
                                updateLoyalty(instance, parseFloat(instance.$amountPaidInput.val()) || 0);
                            } else {
                                instance.$loyaltyPanel.addClass('d-none');
                            }
                        })
                        .fail(function () {
                            instance.$loyaltyPanel.addClass('d-none');
                        });
                }

                function attachInstance(formId) {
                    const instance = window.ptMemberFormRegistry[formId];
                    if (!instance || instance.initialized) {
                        return;
                    }
                    instance.initialized = true;

                    instance.$root = $('#' + instance.formId);
                    instance.$form = instance.$root.find('form').first();

                    instance.$subscriptionSelect = instance.$form.find('#pt_subscription_id');
                    instance.$classSelect = instance.$form.find('#pt_class_id');
                    instance.$trainerSelect = instance.$form.find('#pt_trainer_id');
                    instance.$trainerPercentageInput = instance.$form.find('#trainer_percentage');
                    instance.$classTrainerInput = instance.$form.find('#class_trainer_id_input');
                    instance.$totalSessionsInput = instance.$form.find('#total_sessions_input');
                    instance.$remainingSessionsInput = instance.$form.find('#remaining_sessions_input');
                    instance.$discountInput = instance.$form.find('#discount_value');
                    instance.$groupDiscountSelect = instance.$form.find('#group_discount_id');
                    instance.$amountPaidInput = instance.$form.find('#amount_paid_input');
                    instance.$amountRemainingInput = instance.$form.find('#amount_remaining_input');
                    instance.$priceBase = instance.$form.find('#price_base');
                    instance.$priceAfterDiscountRow = instance.$form.find('#price_after_discount_row');
                    instance.$priceAfterDiscount = instance.$form.find('#price_after_discount');
                    instance.$priceWithVat = instance.$form.find('#price_with_vat');
                    instance.$summaryCard = instance.$root.find('#session_summary_card');
                    instance.$summaryClassName = instance.$root.find('#summary_class_name');
                    instance.$summaryClassType = instance.$root.find('#summary_class_type');
                    instance.$summaryTotalSessions = instance.$root.find('#summary_total_sessions');
                    instance.$summaryUsedSessions = instance.$root.find('#summary_used_sessions');
                    instance.$summaryRemainingSessions = instance.$root.find('#summary_remaining_sessions');
                    instance.$summaryMaxMembers = instance.$root.find('#summary_max_members');
                    instance.$summarySchedule = instance.$root.find('#summary_schedule');
                    instance.$classLimitMsg = instance.$root.find('#class_limit_msg');
                    instance.$snapshotName = instance.$root.find('#snapshot_name');
                    instance.$snapshotBarcode = instance.$root.find('#snapshot_barcode');
                    instance.$snapshotMembership = instance.$root.find('#snapshot_membership');
                    instance.$snapshotExpire = instance.$root.find('#snapshot_expire_date');
                    instance.$snapshotAmountRemaining = instance.$root.find('#snapshot_amount_remaining');
                    instance.$snapshotStatus = instance.$root.find('#snapshot_status');
                    instance.$joiningDateInput = instance.$form.find('#joining_date_input');
                    instance.$expireDateInput = instance.$form.find('#expire_date_input');
                    instance.$memberIdInput = instance.$form.find('#member_id');
                    instance.$loyaltyPanel = instance.$form.find('#pt_member_loyalty_earning_info');
                    instance.$loyaltyPoints = instance.$form.find('#pt_member_estimated_earning_points');
                    instance.$loyaltyRateLabel = instance.$form.find('#pt_member_loyalty_earning_rate');

                    if ($.fn.select2) {
                        instance.$form.find('.select2').select2({
                            width: '100%',
                            allowClear: true,
                            placeholder: instance.i18n.chooseOption
                        });
                    }

                    if ($.fn.datepicker) {
                        instance.$form.find('.input-daterange').datepicker({
                            format: 'yyyy-mm-dd',
                            autoclose: true,
                            todayHighlight: true
                        });
                    }

                    if (bootstrap && typeof bootstrap.Tooltip === 'function') {
                        instance.$root.find('[data-bs-toggle="tooltip"]').each(function () {
                            new bootstrap.Tooltip(this);
                        });
                    }

                    instance.amountPaidManuallyEdited = false;
                    instance.trainerPercentageManuallyEdited = false;
                    instance.joiningDateManuallyChanged = false;
                    instance.expireDateManuallyChanged = false;

                    instance.$joiningDateInput.on('change', function () {
                        instance.joiningDateManuallyChanged = true;
                    });
                    instance.$expireDateInput.on('change', function () {
                        instance.expireDateManuallyChanged = true;
                    });

                    instance.$subscriptionSelect.on('change', function () {
                        handleSubscriptionChange(instance);
                    });

                    instance.$classSelect.on('change', function () {
                        handleClassChange(instance);
                    });

                    instance.$trainerSelect.on('change', function () {
                        handleTrainerChange(instance);
                    });

                    instance.$trainerPercentageInput.on('input', function () {
                        instance.trainerPercentageManuallyEdited = true;
                    });

                    instance.$discountInput.on('input', function () {
                        instance.amountPaidManuallyEdited = false;
                        calculatePrice(instance, false);
                    });

                    if (instance.$groupDiscountSelect && instance.$groupDiscountSelect.length) {
                        instance.$groupDiscountSelect.on('change', function () {
                            instance.amountPaidManuallyEdited = false;
                            calculatePrice(instance, false);
                        });
                    }

                    instance.$amountPaidInput.on('input', function () {
                        instance.amountPaidManuallyEdited = true;
                        calculatePrice(instance, false);
                    });

                    let memberLookupTimer = null;
                    instance.$memberIdInput.on('input', function () {
                        const value = ($(this).val() || '').trim();
                        clearTimeout(memberLookupTimer);
                        if (!value) {
                            updateSnapshot(instance, null);
                            return;
                        }
                        memberLookupTimer = setTimeout(function () {
                            handleMemberLookup(instance, value);
                        }, 350);
                    });

                    instance.classes = instance.classes || {};
                    instance.trainers = instance.trainers || [];
                    instance.urls = instance.urls || {};
                    instance.i18n = instance.i18n || {};
                    instance.initialSessions = instance.initialSessions || { total: 0, used: 0, remaining: 0 };
                    instance.vatPercentage = parseFloat(instance.vatPercentage || 0);
                    instance.loyaltyEnabled = !!instance.loyaltyEnabled;
                    instance.loyaltyRate = 0;
                    instance.loyaltyRateFetched = false;

                    if (instance.memberSnapshot && Object.keys(instance.memberSnapshot).length) {
                        updateSnapshot(instance, instance.memberSnapshot);
                    } else if (!instance.$memberIdInput.prop('readonly') && instance.$memberIdInput.val()) {
                        handleMemberLookup(instance, instance.$memberIdInput.val());
                    } else {
                        updateSnapshot(instance, null);
                    }

                    if (instance.selectedSubscriptionId) {
                        instance.$subscriptionSelect.val(instance.selectedSubscriptionId).trigger('change.select2');
                    } else if (instance.$subscriptionSelect.find('option').length === 2) {
                        const autoSubscription = instance.$subscriptionSelect.find('option').eq(1).val();
                        instance.$subscriptionSelect.val(autoSubscription).trigger('change.select2');
                        instance.selectedSubscriptionId = autoSubscription;
                    }

                    buildClassOptions(instance, instance.selectedSubscriptionId);

                    if (instance.loyaltyEnabled) {
                        fetchLoyaltyRate(instance);
                    }
                }

                window.initPtMemberForm = function (formId) {
                    attachInstance(formId);
                };

                $(document).ready(function () {
                    Object.keys(window.ptMemberFormRegistry).forEach(function (formId) {
                        attachInstance(formId);
                    });
                });
            })(jQuery, window.bootstrap || null);
        </script>
    @endsection
@endonce

@section('sub_scripts')
    @parent
    <script>
        window.ptMemberFormRegistry = window.ptMemberFormRegistry || {};
        window.ptMemberFormRegistry['{{ $formId }}'] = {!! json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
        if (window.initPtMemberForm) {
            window.initPtMemberForm('{{ $formId }}');
        }
    </script>
@endsection


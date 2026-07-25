@extends('software::layouts.list')
@section('list_title') {{ @$title }} @endsection
@section('breadcrumb')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.dashboard') }}" class="text-muted text-hover-primary">{{ trans('sw.home') }}</a>
        </li>
        <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.aiReports.index') }}" class="text-muted text-hover-primary">{{ trans('sw.ai_reports') }}</a>
        </li>
        <li class="breadcrumb-item"><span class="bullet bg-gray-300 w-5px h-2px"></span></li>
        <li class="breadcrumb-item text-gray-900">{{ $title }}</li>
    </ul>
@endsection

@php
    // ── Language resolution ──────────────────────────────────────────────────
    // $lang = system UI language (from controller, ar or en)
    // The stored report may be single-lang (old) or dual {'ar':…,'en':…} (new)
    $isAr  = $lang === 'ar';
    $dir   = $isAr ? 'rtl' : 'ltr';
    $align = $isAr ? 'right' : 'left';
    $opp   = $isAr ? 'left'  : 'right';

    $r = $record->report ?? [];

    // ── Static UI labels (always in system UI lang, not report lang) ─────────
    $ui = $isAr ? [
        'executive_summary'     => 'الملخص التنفيذي',
        'kpi_analysis'          => 'تحليل مؤشرات الأداء',
        'attendance_analysis'   => 'تحليل الحضور',
        'top_packages'          => 'الباقات الرائدة',
        'weak_packages'         => 'الباقات الضعيفة',
        'sales_insights'        => 'رؤى المبيعات',
        'risk_alerts'           => 'تنبيهات المخاطر',
        'recommendations'       => 'التوصيات الاستراتيجية',
        'action_plan'           => 'خطة العمل للشهر القادم',
        // KPI keys
        'total_revenue'             => 'إجمالي الإيرادات',
        'renewal_rate'              => 'معدل التجديد',
        'new_members'               => 'أعضاء جدد',
        'churn_rate'                => 'معدل الإلغاء',
        'average_member_value'      => 'متوسط قيمة العضو',
        // Attendance keys
        'average_visits_per_member' => 'متوسط الزيارات لكل عضو',
        'inactive_members'          => 'أعضاء غير نشطين',
        'peak_hours'                => 'أوقات الذروة',
        'low_hours'                 => 'أوقات الانخفاض',
    ] : [
        'executive_summary'     => 'Executive Summary',
        'kpi_analysis'          => 'KPI Analysis',
        'attendance_analysis'   => 'Attendance Analysis',
        'top_packages'          => 'Top Packages',
        'weak_packages'         => 'Weak Packages',
        'sales_insights'        => 'Sales Insights',
        'risk_alerts'           => 'Risk Alerts',
        'recommendations'       => 'Strategic Recommendations',
        'action_plan'           => 'Next Month Action Plan',
        // KPI keys
        'total_revenue'             => 'Total Revenue',
        'renewal_rate'              => 'Renewal Rate',
        'new_members'               => 'New Members',
        'churn_rate'                => 'Churn Rate',
        'average_member_value'      => 'Avg. Member Value',
        // Attendance keys
        'average_visits_per_member' => 'Avg. Visits / Member',
        'inactive_members'          => 'Inactive Members',
        'peak_hours'                => 'Peak Hours',
        'low_hours'                 => 'Low Hours',
    ];

    $keyLabel = fn(string $k) => $ui[$k] ?? ucwords(str_replace('_', ' ', $k));

    // Colours
    $C = ['navy' => '#1A3A5C', 'blue' => '#1a5276', 'green' => '#1e8449', 'red' => '#c0392b', 'orange' => '#e67e22'];
@endphp

@section('styles')
<style>
    /* ── Section cards ───────────────────────────────────────────── */
    .ai-card {
        border-radius: 10px;
        margin-bottom: 18px;
        overflow: hidden;
        box-shadow: 0 1px 6px rgba(0,0,0,.07);
        border: 1px solid #eef0f5;
    }
    .ai-card-header {
        padding: 11px 20px;
        font-size: 14px;
        font-weight: 700;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 8px;
        direction: inherit;
    }
    .ai-card-body {
        padding: 18px 20px;
        background: #fff;
        direction: inherit;
        text-align: start;
    }
    .ai-card-body.no-pad { padding: 0; }

    /* ── KPI / Attendance table ──────────────────────────────────── */
    .ai-kpi-table {
        width: 100%;
        border-collapse: collapse;
    }
    .ai-kpi-table td {
        padding: 9px 18px;
        font-size: 13px;
        border-bottom: 1px solid #f0f2f5;
        vertical-align: middle;
    }
    .ai-kpi-table tr:last-child td { border-bottom: none; }
    .ai-kpi-table .col-label {
        width: 52%;
        font-weight: 600;
        color: #555;
        text-align: start;
    }
    .ai-kpi-table .col-value {
        font-weight: 700;
        text-align: start;
    }

    /* ── List items ─────────────────────────────────────────────── */
    .ai-list-item {
        padding: 7px 0;
        font-size: 13px;
        color: #444;
        border-bottom: 1px solid #f5f5f5;
        line-height: 1.6;
        display: flex;
        align-items: flex-start;
        gap: 8px;
        direction: inherit;
    }
    .ai-list-item:last-child { border-bottom: none; }
    .ai-list-item i { flex-shrink: 0; margin-top: 2px; }

    /* Numbered action plan */
    .ai-action-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 8px 0;
        border-bottom: 1px solid #f5f5f5;
        direction: inherit;
        font-size: 13px;
        color: #333;
        line-height: 1.6;
    }
    .ai-action-item:last-child { border-bottom: none; }
    .ai-action-num {
        flex-shrink: 0;
        width: 24px; height: 24px;
        border-radius: 50%;
        background: #e67e22;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 1px;
    }

    /* ── Right panel sticky ─────────────────────────────────────── */
    @media (min-width: 1200px) {
        .send-panel-sticky { position: sticky; top: 20px; }
    }

    /* ── Recipient tag ─────────────────────────────────────────── */
    .recipient-tag {
        display: inline-flex; align-items: center; gap: 5px;
        background: #f1f5fb; border-radius: 20px;
        padding: 4px 12px; margin: 3px 3px;
        font-size: 12px; color: #444;
    }
</style>
@endsection

@section('page_body')
<div class="row g-5" dir="{{ $dir }}">

    {{-- ══════════════ LEFT / MAIN COLUMN ══════════════ --}}
    <div class="col-xl-8">

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center p-4 mb-5">
                <i class="ki-outline ki-shield-tick fs-2 text-success {{ $isAr ? 'ms-3' : 'me-3' }}"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center p-4 mb-5">
                <i class="ki-outline ki-information-5 fs-2 text-danger {{ $isAr ? 'ms-3' : 'me-3' }}"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- ── Report meta header ────────────────────────────────────── --}}
        <div class="card card-flush mb-5">
            <div class="card-body py-5">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" dir="{{ $dir }}">
                    {{-- Identity --}}
                    <div class="d-flex align-items-center gap-4">
                        <div class="symbol symbol-50px symbol-circle bg-primary">
                            <span class="symbol-label text-white fs-4 fw-bold">AI</span>
                        </div>
                        <div style="text-align: {{ $align }}">
                            <h4 class="fw-bold text-dark mb-1">
                                {{ ucfirst($record->type) }} {{ trans('sw.ai_report') }}
                                <span class="badge badge-light-primary {{ $isAr ? 'me-2' : 'ms-2' }}">
                                    {{ $record->lang === 'both' ? 'AR + EN' : strtoupper($record->lang) }}
                                </span>
                            </h4>
                            <div class="text-muted fs-7">
                                <i class="ki-outline ki-calendar fs-6 {{ $isAr ? 'ms-1' : 'me-1' }}"></i>
                                {{ $record->from_date }} → {{ $record->to_date }}
                                &nbsp;&bull;&nbsp;
                                <!-- <i class="ki-outline ki-abstract-26 fs-6 {{ $isAr ? 'ms-1' : 'me-1' }}"></i>
                                {{ $record->model_used ?? '—' }}
                                &nbsp;&bull;&nbsp; -->
                                <i class="ki-outline ki-time fs-6 {{ $isAr ? 'ms-1' : 'me-1' }}"></i>
                                {{ $record->created_at->format('Y-m-d H:i') }}
                            </div>
                        </div>
                    </div>
                    {{-- Delivery badges --}}
                    <div class="d-flex gap-2 flex-wrap">
                        @if($record->email_sent)
                            <span class="badge badge-light-success fs-7 px-3 py-2">
                                <i class="ki-outline ki-sms fs-6 {{ $isAr ? 'ms-1' : 'me-1' }}"></i>
                                {{ trans('sw.email_sent') }}
                            </span>
                        @endif
                        @if($record->sms_sent)
                            <span class="badge badge-light-success fs-7 px-3 py-2">
                                <i class="ki-outline ki-phone fs-6 {{ $isAr ? 'ms-1' : 'me-1' }}"></i>
                                {{ trans('sw.sms_sent') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Executive Summary ─────────────────────────────────────── --}}
        @if(!empty($r['executive_summary']))
            <div class="ai-card">
                <div class="ai-card-header" style="background: {{ $C['navy'] }}">
                    <span>📋</span> {{ $ui['executive_summary'] }}
                </div>
                <div class="ai-card-body">
                    <p class="mb-0 text-gray-800 fs-6 lh-lg">{{ $r['executive_summary'] }}</p>
                </div>
            </div>
        @endif

        {{-- ── KPI Analysis ──────────────────────────────────────────── --}}
        @if(!empty($r['kpi_analysis']) && is_array($r['kpi_analysis']))
            <div class="ai-card">
                <div class="ai-card-header" style="background: {{ $C['navy'] }}">
                    <span>📊</span> {{ $ui['kpi_analysis'] }}
                </div>
                <div class="ai-card-body no-pad">
                    <table class="ai-kpi-table">
                        @foreach($r['kpi_analysis'] as $key => $value)
                            <tr>
                                <td class="col-label">{{ $keyLabel($key) }}</td>
                                <td class="col-value text-primary">{{ $value }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        @endif

        {{-- ── Attendance Analysis ───────────────────────────────────── --}}
        @if(!empty($r['attendance_analysis']) && is_array($r['attendance_analysis']))
            <div class="ai-card">
                <div class="ai-card-header" style="background: {{ $C['blue'] }}">
                    <span>🏃</span> {{ $ui['attendance_analysis'] }}
                </div>
                <div class="ai-card-body no-pad">
                    <table class="ai-kpi-table">
                        @foreach($r['attendance_analysis'] as $key => $value)
                            <tr>
                                <td class="col-label">{{ $keyLabel($key) }}</td>
                                <td class="col-value text-info">{{ $value }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        @endif

        {{-- ── Top & Weak Packages ───────────────────────────────────── --}}
        @if(!empty($r['top_packages']) || !empty($r['weak_packages']))
            <div class="row g-4 mb-1">
                @if(!empty($r['top_packages']))
                    <div class="col-md-6">
                        <div class="ai-card mb-0">
                            <div class="ai-card-header" style="background: {{ $C['green'] }}">
                                <span>🏆</span> {{ $ui['top_packages'] }}
                            </div>
                            <div class="ai-card-body">
                                @foreach((array)$r['top_packages'] as $item)
                                    <div class="ai-list-item">
                                        <i class="ki-outline ki-arrow-{{ $isAr ? 'up-left' : 'up-right' }} fs-6 text-success"></i>
                                        <span>{{ $item }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
                @if(!empty($r['weak_packages']))
                    <div class="col-md-6">
                        <div class="ai-card mb-0">
                            <div class="ai-card-header" style="background: {{ $C['red'] }}">
                                <span>⚠️</span> {{ $ui['weak_packages'] }}
                            </div>
                            <div class="ai-card-body">
                                @foreach((array)$r['weak_packages'] as $item)
                                    <div class="ai-list-item">
                                        <i class="ki-outline ki-arrow-{{ $isAr ? 'down-left' : 'down-right' }} fs-6 text-danger"></i>
                                        <span>{{ $item }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- ── Sales Insights ────────────────────────────────────────── --}}
        @if(!empty($r['sales_insights']))
            <div class="ai-card">
                <div class="ai-card-header" style="background: {{ $C['blue'] }}">
                    <span>💡</span> {{ $ui['sales_insights'] }}
                </div>
                <div class="ai-card-body">
                    @foreach((array)$r['sales_insights'] as $item)
                        <div class="ai-list-item">
                            <i class="ki-outline ki-arrow-{{ $isAr ? 'left' : 'right' }} fs-6 text-info"></i>
                            <span>{{ $item }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── Risk Alerts ───────────────────────────────────────────── --}}
        @if(!empty($r['risk_alerts']))
            <div class="ai-card">
                <div class="ai-card-header" style="background: {{ $C['red'] }}">
                    <span>🚨</span> {{ $ui['risk_alerts'] }}
                </div>
                <div class="ai-card-body">
                    @foreach((array)$r['risk_alerts'] as $item)
                        <div class="ai-list-item text-danger">
                            <i class="ki-outline ki-information-5 fs-6 text-danger"></i>
                            <span>{{ $item }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── Strategic Recommendations ─────────────────────────────── --}}
        @if(!empty($r['strategic_recommendations']))
            <div class="ai-card">
                <div class="ai-card-header" style="background: {{ $C['green'] }}">
                    <span>🎯</span> {{ $ui['recommendations'] }}
                </div>
                <div class="ai-card-body">
                    @foreach((array)$r['strategic_recommendations'] as $item)
                        <div class="ai-list-item">
                            <i class="ki-outline ki-check fs-6 text-success"></i>
                            <span>{{ $item }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── Next Month Action Plan ────────────────────────────────── --}}
        @if(!empty($r['next_month_action_plan']))
            <div class="ai-card">
                <div class="ai-card-header" style="background: {{ $C['orange'] }}">
                    <span>📅</span> {{ $ui['action_plan'] }}
                </div>
                <div class="ai-card-body">
                    @foreach((array)$r['next_month_action_plan'] as $i => $item)
                        <div class="ai-action-item">
                            <div class="ai-action-num">{{ $i + 1 }}</div>
                            <span>{{ $item }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
    {{-- end main column --}}

    {{-- ══════════════ RIGHT / SEND PANEL ══════════════ --}}
    <div class="col-xl-4">
        <div class="card card-flush send-panel-sticky">
            <div class="card-header min-h-50px" style="background:#1A3A5C; border-radius:10px 10px 0 0">
                <h3 class="card-title fw-bold text-white fs-5" dir="{{ $dir }}">
                    <i class="ki-outline ki-send fs-4 text-white {{ $isAr ? 'ms-2' : 'me-2' }}"></i>
                    {{ trans('sw.ai_send_report') }}
                </h3>
            </div>
            <div class="card-body" dir="{{ $dir }}">
                <form action="{{ route('sw.aiReports.send', $record->id) }}" method="POST" id="sendForm">
                    @csrf

                    {{-- Saved emails from settings --}}
                    <div class="mb-6">
                        <label class="form-label fw-semibold d-block" style="text-align:{{ $align }}">
                            <i class="ki-outline ki-sms fs-5 text-primary {{ $isAr ? 'ms-1' : 'me-1' }}"></i>
                            {{ trans('sw.notify_emails') }}
                        </label>
                        @if(!empty($notifyEmails))
                            <div class="mb-3">
                                <div class="text-muted fs-8 mb-2" style="text-align:{{ $align }}">{{ trans('sw.from_settings') }}:</div>
                                @foreach($notifyEmails as $email)
                                    <span class="recipient-tag">
                                        <i class="ki-outline ki-sms fs-7 text-primary"></i>{{ $email }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                        <div id="extraEmailsList"></div>
                        <button type="button" class="btn btn-light-primary btn-sm mt-2" onclick="addEmail()">
                            <i class="ki-outline ki-plus fs-6"></i> {{ trans('sw.add_email') }}
                        </button>
                    </div>

                    @if($mainSettings->active_sms)
                    {{-- Saved phones from settings --}}
                    <div class="mb-6">
                        <label class="form-label fw-semibold d-block" style="text-align:{{ $align }}">
                            <i class="ki-outline ki-phone fs-5 text-success {{ $isAr ? 'ms-1' : 'me-1' }}"></i>
                            {{ trans('sw.notify_phones') }}
                        </label>
                        @if(!empty($notifyPhones))
                            <div class="mb-3">
                                <div class="text-muted fs-8 mb-2" style="text-align:{{ $align }}">{{ trans('sw.from_settings') }}:</div>
                                @foreach($notifyPhones as $phone)
                                    <span class="recipient-tag">
                                        <i class="ki-outline ki-phone fs-7 text-success"></i>{{ $phone }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                        <div id="extraPhonesList"></div>
                        <button type="button" class="btn btn-light-success btn-sm mt-2" onclick="addPhone()">
                            <i class="ki-outline ki-plus fs-6"></i> {{ trans('sw.add_phone') }}
                        </button>
                    </div>  
                    @endif

                    {{-- Warning if no defaults --}}
                    @if(empty($notifyEmails) && empty($notifyPhones))
                        <!-- <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-3 mb-5"
                             style="direction:{{ $dir }};text-align:{{ $align }}">
                            <i class="ki-outline ki-information-5 fs-3 text-warning {{ $isAr ? 'ms-3' : 'me-3' }} mt-1 flex-shrink-0"></i>
                            <div class="fs-7 text-gray-700">
                                {{ trans('sw.ai_no_default_recipients') }}
                                <a href="{{ route('sw.editIntegrations') }}#tab-ai" target="_blank" class="fw-bold">
                                    {{ trans('sw.settings') }}
                                </a>
                            </div>
                        </div> -->
                    @endif

                    <button type="submit" class="btn btn-primary w-100" id="sendBtn">
                        <span class="indicator-label">
                            <i class="ki-outline ki-send fs-5 {{ $isAr ? 'ms-1' : 'me-1' }}"></i>
                            {{ trans('sw.ai_send_report') }}
                        </span>
                        <span class="indicator-progress">
                            {{ trans('sw.sending') }}
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
    {{-- end send panel --}}

</div>
@endsection

@section('scripts')
<script>
    function addEmail() {
        const div = document.createElement('div');
        div.className = 'd-flex align-items-center gap-2 mb-2';
        div.innerHTML = `
            <input type="email" name="extra_emails[]"
                   class="form-control form-control-solid form-control-sm"
                   placeholder="email@example.com">
            <button type="button" class="btn btn-icon btn-light-danger btn-sm flex-shrink-0"
                    onclick="this.parentElement.remove()">
                <i class="ki-outline ki-cross fs-6"></i>
            </button>`;
        document.getElementById('extraEmailsList').appendChild(div);
    }

    function addPhone() {
        const div = document.createElement('div');
        div.className = 'd-flex align-items-center gap-2 mb-2';
        div.innerHTML = `
            <input type="tel" name="extra_phones[]"
                   class="form-control form-control-solid form-control-sm"
                   placeholder="+966XXXXXXXXX">
            <button type="button" class="btn btn-icon btn-light-danger btn-sm flex-shrink-0"
                    onclick="this.parentElement.remove()">
                <i class="ki-outline ki-cross fs-6"></i>
            </button>`;
        document.getElementById('extraPhonesList').appendChild(div);
    }

    document.getElementById('sendForm').addEventListener('submit', function () {
        const btn = document.getElementById('sendBtn');
        btn.setAttribute('data-kt-indicator', 'on');
        btn.disabled = true;
    });
</script>
@endsection

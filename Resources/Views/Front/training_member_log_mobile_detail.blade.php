<!DOCTYPE html>
<html lang="{{ $lang }}" dir="{{ $isArabic ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#18181B">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>{{ $typeLabel }} — {{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* ─── Reset ─── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:      #F2F5FB;
            --surface: #FFFFFF;
            --dark:    #18181B;
            --ink:     #111827;
            --ink2:    #374151;
            --muted:   #6B7280;
            --border:  #E5E7EB;
            --ff:      'Cairo', sans-serif;
            --safe-b:  env(safe-area-inset-bottom, 16px);
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: var(--ff);
            background: var(--bg);
            color: var(--ink);
            min-height: 100dvh;
            -webkit-font-smoothing: antialiased;
            -webkit-tap-highlight-color: transparent;
        }

        /* ─── Sticky Topbar ─── */
        .topbar {
            position: sticky;
            top: 0;
            z-index: 200;
            height: 56px;
            background: var(--dark);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            gap: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.25);
        }

        .back-btn {
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #CBD5E1;
            font-size: 14px;
            font-weight: 700;
            font-family: var(--ff);
            padding: 6px 10px;
            border-radius: 10px;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.12);
            transition: background 0.15s;
            -webkit-tap-highlight-color: transparent;
        }

        .back-btn:active { background: rgba(255,255,255,0.12); }

        .back-arrow {
            font-size: 16px;
            line-height: 1;
        }

        .topbar-stamp {
            font-size: 11px;
            color: #94A3B8;
            font-weight: 500;
            text-align: end;
            line-height: 1.5;
        }

        /* ─── Type Hero ─── */
        .hero {
            padding: 28px 20px 32px;
            position: relative;
            overflow: hidden;
        }

        .hero-bubble-1 {
            position: absolute;
            top: -80px;
            inset-inline-end: -60px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            pointer-events: none;
        }

        .hero-bubble-2 {
            position: absolute;
            bottom: -60px;
            inset-inline-start: -40px;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
            pointer-events: none;
        }

        .hero-inner { position: relative; z-index: 1; }

        .hero-icon-wrap {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 14px;
        }

        .hero-type-label {
            font-size: 12px;
            font-weight: 700;
            color: rgba(255,255,255,0.65);
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 6px;
        }

        .hero-title {
            font-size: clamp(22px, 6vw, 34px);
            font-weight: 900;
            color: #F8FAFC;
            line-height: 1.2;
            letter-spacing: -0.2px;
        }

        .hero-summary {
            margin-top: 10px;
            font-size: 14px;
            color: rgba(248,250,252,0.72);
            line-height: 1.85;
            max-width: 420px;
        }

        .hero-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 14px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 999px;
            padding: 5px 14px;
            color: #fff;
            font-size: 12px;
            font-weight: 700;
        }

        /* ─── Hero Gradient (neutral — safe for any brand) ─── */
        .hero-assessment,
        .hero-plan,
        .hero-medicine,
        .hero-note,
        .hero-file,
        .hero-track,
        .hero-ai,
        .hero-ai_plan,
        .hero-activity,
        .hero-default { background: linear-gradient(145deg, #18181B 0%, #27272A 55%, #18181B 100%); }

        /* ─── Member Strip ─── */
        .member-strip {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 12px 18px;
            display: flex;
            align-items: center;
            gap: 0;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .member-strip::-webkit-scrollbar { display: none; }

        .ms-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
            flex-shrink: 0;
            padding: 0 16px;
        }

        .ms-item:first-child { padding-inline-start: 0; }

        .ms-divider {
            width: 1px;
            height: 32px;
            background: var(--border);
            flex-shrink: 0;
        }

        .ms-label {
            font-size: 10px;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .ms-value {
            font-size: 13px;
            font-weight: 800;
            color: var(--ink);
        }

        /* ─── Content Feed ─── */
        .content {
            padding: 14px 14px calc(20px + var(--safe-b));
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* ─── Section Card ─── */
        .sec-card {
            background: var(--surface);
            border-radius: 18px;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 1px 4px rgba(0,0,0,0.04), 0 4px 14px rgba(0,0,0,0.06);
            overflow: hidden;
        }

        .sec-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 16px 11px;
            border-bottom: 1px solid #F3F4F6;
        }

        .sec-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            background: #F1F5F9;
            flex-shrink: 0;
        }

        .sec-title {
            font-size: 15px;
            font-weight: 800;
            color: var(--ink);
        }

        .sec-body { padding: 14px 16px; }

        /* ─── Summary Text ─── */
        .summary-text {
            font-size: 14px;
            line-height: 1.9;
            color: var(--ink2);
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        /* ─── KV Row List ─── */
        .kv-list { display: flex; flex-direction: column; gap: 0; }

        .kv-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            padding: 10px 0;
            border-bottom: 1px solid #F3F4F6;
        }

        .kv-row:last-child { border-bottom: none; padding-bottom: 0; }
        .kv-row:first-child { padding-top: 0; }

        .kv-key {
            font-size: 13px;
            color: var(--muted);
            font-weight: 500;
            flex-shrink: 0;
            max-width: 45%;
        }

        .kv-val {
            font-size: 13px;
            font-weight: 800;
            color: var(--ink);
            text-align: end;
            word-break: break-word;
        }

        /* ─── Stats Grid ─── */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .stat-box {
            background: #F8FAFC;
            border: 1px solid #EEF2F7;
            border-radius: 14px;
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .stat-box .sb-label {
            font-size: 11px;
            color: var(--muted);
            font-weight: 600;
        }

        .stat-box .sb-value {
            font-size: 18px;
            font-weight: 900;
            color: var(--ink);
            line-height: 1;
        }

        .stat-box .sb-unit {
            font-size: 12px;
            color: var(--muted);
            font-weight: 500;
        }

        /* ─── Plan Tasks ─── */
        .tasks-list { display: flex; flex-direction: column; gap: 8px; }

        .task-item {
            border-radius: 12px;
            border: 1px solid #EEF2F7;
            background: #FAFBFF;
            padding: 12px;
        }

        .task-title {
            font-size: 14px;
            font-weight: 800;
            color: var(--ink);
            margin-bottom: 4px;
            display: flex;
            align-items: flex-start;
            gap: 7px;
        }

        .task-bullet {
            width: 20px;
            height: 20px;
            border-radius: 6px;
            background: linear-gradient(135deg, #1D4ED8, #3B82F6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #fff;
            font-weight: 900;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .task-notes {
            font-size: 13px;
            color: #4B5563;
            line-height: 1.75;
            white-space: pre-wrap;
            padding-inline-start: 27px;
        }

        /* ─── Download / Action Buttons ─── */
        .btn-row {
            padding-top: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .btn-download {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            text-decoration: none;
            border-radius: 12px;
            padding: 10px 18px;
            font-family: var(--ff);
            font-size: 13px;
            font-weight: 800;
            transition: opacity 0.15s;
        }

        .btn-download:active { opacity: 0.75; }

        .btn-primary {
            background: linear-gradient(135deg, #1D4ED8, #3B82F6);
            color: #fff;
        }

        .btn-success {
            background: linear-gradient(135deg, #047857, #10B981);
            color: #fff;
        }

        /* ─── File Card ─── */
        .file-card {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px;
            background: #FAFBFF;
            border: 1px solid #E8EDF7;
            border-radius: 14px;
        }

        .file-icon-wrap {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #B45309, #F59E0B);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .file-info { flex: 1; min-width: 0; }

        .file-title {
            font-size: 11px;
            color: var(--muted);
            font-weight: 600;
            margin-bottom: 3px;
        }

        .file-name {
            font-size: 14px;
            font-weight: 800;
            color: var(--ink);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* ─── AI JSON Block ─── */
        .json-wrap {
            background: #0F2235;
            border-radius: 14px;
            overflow: hidden;
        }

        .json-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 14px;
            background: rgba(255,255,255,0.05);
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }

        .json-label {
            font-size: 11px;
            color: #64748B;
            font-weight: 700;
            font-family: var(--ff);
        }

        .json-dots { display: flex; gap: 5px; }

        .json-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        pre.json-code {
            font-family: Consolas, 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.75;
            color: #A5F3C8;
            padding: 14px;
            overflow-x: auto;
            white-space: pre;
            margin: 0;
        }

        /* ─── Notes ─── */
        .notes-body {
            font-size: 14px;
            line-height: 1.95;
            color: var(--ink2);
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }
    </style>
</head>
<body>

@php
    $logType = $log->training_type ?? 'activity';
    $typeIcons = [
        'assessment' => '📋',
        'plan'       => '🏋️',
        'medicine'   => '💊',
        'note'       => '📝',
        'file'       => '📎',
        'track'      => '📊',
        'ai'         => '🤖',
        'ai_plan'    => '✨',
        'activity'   => '🎯',
    ];
    $heroIcon = $typeIcons[$logType] ?? '🎯';
    $heroClass = 'hero-' . $logType;
    if (!in_array($logType, ['assessment','plan','medicine','note','file','track','ai','ai_plan','activity'])) {
        $heroClass = 'hero-default';
    }
@endphp

{{-- ─── Sticky Topbar ─── --}}
<header class="topbar">
    <a href="{{ $backUrl }}" class="back-btn">
        <span class="back-arrow">{{ $isArabic ? '→' : '←' }}</span>
        <span>{{ $isArabic ? 'رجوع' : 'Back' }}</span>
    </a>
    <div class="topbar-stamp">{{ optional($log->created_at)->translatedFormat('d F Y') }}<br>{{ optional($log->created_at)->format('H:i') }}</div>
</header>

{{-- ─── Type Hero ─── --}}
<div class="hero {{ $heroClass }}">
    <div class="hero-bubble-1"></div>
    <div class="hero-bubble-2"></div>
    <div class="hero-inner">
        <div class="hero-icon-wrap">{{ $heroIcon }}</div>
        <div class="hero-type-label">{{ $isArabic ? 'نوع السجل' : 'Log Type' }}</div>
        <h1 class="hero-title">{{ $typeLabel }}</h1>
        <p class="hero-summary">{{ $summary }}</p>
        <span class="hero-action">
            <span>✔</span>
            <span>{{ $actionLabel }}</span>
        </span>
    </div>
</div>

{{-- ─── Member Strip ─── --}}
<div class="member-strip">
    <div class="ms-item">
        <span class="ms-label">{{ $isArabic ? 'الاسم' : 'Name' }}</span>
        <span class="ms-value">{{ $member->name ?? '-' }}</span>
    </div>
    <div class="ms-divider"></div>
    <div class="ms-item">
        <span class="ms-label">{{ $isArabic ? 'الكود' : 'Code' }}</span>
        <span class="ms-value">#{{ $member->code ?? $memberId }}</span>
    </div>
    <div class="ms-divider"></div>
    <div class="ms-item">
        <span class="ms-label">{{ $isArabic ? 'النوع' : 'Type' }}</span>
        <span class="ms-value">{{ $typeLabel }}</span>
    </div>
</div>

{{-- ─── Content Sections ─── --}}
<div class="content">

    {{-- Summary --}}
    <div class="sec-card">
        <div class="sec-header">
            <div class="sec-icon">📄</div>
            <span class="sec-title">{{ $isArabic ? 'الملخص' : 'Summary' }}</span>
        </div>
        <div class="sec-body">
            <p class="summary-text">{{ $summary }}</p>
        </div>
    </div>

    {{-- Notes --}}
    @if(!empty($details['notes']) || !empty($details['note']))
        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon">📝</div>
                <span class="sec-title">{{ $isArabic ? 'ملاحظات' : 'Notes' }}</span>
            </div>
            <div class="sec-body">
                <p class="notes-body">{{ $details['notes'] ?? $details['note'] }}</p>
            </div>
        </div>
    @endif

    {{-- Assessment Answers --}}
    @if(!empty($details['answers']) && is_array($details['answers']))
        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon">📋</div>
                <span class="sec-title">{{ $isArabic ? 'بيانات التقييم' : 'Assessment Data' }}</span>
            </div>
            <div class="sec-body">
                <div class="kv-list">
                    @foreach($details['answers'] as $key => $value)
                        <div class="kv-row">
                            <span class="kv-key">{{ is_string($key) ? str_replace('_', ' ', ucfirst($key)) : $key }}</span>
                            <span class="kv-val">{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Plan Tasks --}}
    @if(!empty($details['tasks']) && is_array($details['tasks']))
        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon">🏋️</div>
                <span class="sec-title">{{ $isArabic ? 'مهام الخطة' : 'Plan Tasks' }}</span>
            </div>
            <div class="sec-body">
                <div class="tasks-list">
                    @foreach($details['tasks'] as $i => $task)
                        <div class="task-item">
                            <div class="task-title">
                                <span class="task-bullet">{{ $i + 1 }}</span>
                                <span>{{ $task['title'] ?? '-' }}</span>
                            </div>
                            @if(!empty($task['notes']))
                                <div class="task-notes">{{ $task['notes'] }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
                @if(!empty($details['member_plan_id']))
                    @php
                        $token = request('payment_link_token') ?: request('token');
                        $planUrl = route('sw.training-plan-mobile', [
                            'id'    => (int) $details['member_plan_id'],
                            'token' => $token,
                            'lang'  => $lang,
                        ]);
                    @endphp
                    <div class="btn-row">
                        <a class="btn-download btn-primary" href="{{ $planUrl }}">
                            <span>📄</span>
                            <span>{{ $isArabic ? 'فتح الخطة الكاملة' : 'Open Full Plan' }}</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Track Measurements --}}
    @if(!empty($details['measurements']) && is_array($details['measurements']))
        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon">📏</div>
                <span class="sec-title">{{ $isArabic ? 'القياسات' : 'Measurements' }}</span>
            </div>
            <div class="sec-body">
                @php
                    $measurements = $details['measurements'];
                    $pairs = array_chunk(array_keys($measurements), 2);
                @endphp
                <div class="stats-grid">
                    @foreach($measurements as $mKey => $mVal)
                        @php
                            preg_match('/^([\d.,]+)\s*(.*)$/', (string)$mVal, $m);
                            $num  = $m[1] ?? $mVal;
                            $unit = trim($m[2] ?? '');
                        @endphp
                        <div class="stat-box">
                            <span class="sb-label">{{ str_replace('_', ' ', ucfirst($mKey)) }}</span>
                            <span class="sb-value">{{ $num }}</span>
                            @if($unit)
                                <span class="sb-unit">{{ $unit }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Track Calculations --}}
    @if(!empty($details['calculations']) && is_array($details['calculations']))
        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon">🔢</div>
                <span class="sec-title">{{ $isArabic ? 'الحسابات' : 'Calculations' }}</span>
            </div>
            <div class="sec-body">
                <div class="stats-grid">
                    @foreach($details['calculations'] as $cKey => $cVal)
                        @php
                            preg_match('/^([\d.,]+)\s*(.*)$/', (string)$cVal, $m);
                            $num  = $m[1] ?? $cVal;
                            $unit = trim($m[2] ?? '');
                        @endphp
                        <div class="stat-box">
                            <span class="sb-label">{{ str_replace('_', ' ', ucfirst($cKey)) }}</span>
                            <span class="sb-value">{{ $num }}</span>
                            @if($unit)
                                <span class="sb-unit">{{ $unit }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Medicine --}}
    @if($logType === 'medicine' && !empty($details))
        @php
            $medFields = array_filter([
                ($isArabic ? 'الدواء' : 'Medicine')  => $details['medicine_name'] ?? $details['name'] ?? null,
                ($isArabic ? 'الجرعة'   : 'Dose')    => $details['dose'] ?? null,
                ($isArabic ? 'ملاحظات'  : 'Notes')   => $details['notes'] ?? null,
            ]);
        @endphp
        @if(!empty($medFields))
            <div class="sec-card">
                <div class="sec-header">
                    <div class="sec-icon">💊</div>
                    <span class="sec-title">{{ $isArabic ? 'تفاصيل الدواء' : 'Medicine Details' }}</span>
                </div>
                <div class="sec-body">
                    <div class="kv-list">
                        @foreach($medFields as $label => $val)
                            <div class="kv-row">
                                <span class="kv-key">{{ $label }}</span>
                                <span class="kv-val">{{ $val }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- File --}}
    @if(!empty($details['path']))
        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon">📎</div>
                <span class="sec-title">{{ $isArabic ? 'الملف المرفق' : 'Attached File' }}</span>
            </div>
            <div class="sec-body">
                <div class="file-card">
                    <div class="file-icon-wrap">📎</div>
                    <div class="file-info">
                        <div class="file-title">{{ $details['title'] ?? ($isArabic ? 'عنوان الملف' : 'File Title') }}</div>
                        <div class="file-name">{{ $details['file_name'] ?? basename($details['path']) }}</div>
                    </div>
                </div>
                <div class="btn-row">
                    <a class="btn-download btn-success" href="{{ $details['path'] }}" target="_blank">
                        <span>⬇</span>
                        <span>{{ $isArabic ? 'تحميل / فتح الملف' : 'Open / Download File' }}</span>
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- AI Response --}}
    @if(!empty($details['response']) && is_array($details['response']))
        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon">🤖</div>
                <span class="sec-title">{{ $isArabic ? 'تفاصيل الذكاء الاصطناعي' : 'AI Response' }}</span>
            </div>
            <div class="sec-body" style="padding: 0;">
                <div class="json-wrap">
                    <div class="json-toolbar">
                        <span class="json-label">JSON</span>
                        <div class="json-dots">
                            <span class="json-dot" style="background:#EF4444;"></span>
                            <span class="json-dot" style="background:#F59E0B;"></span>
                            <span class="json-dot" style="background:#10B981;"></span>
                        </div>
                    </div>
                    <pre class="json-code">{{ json_encode($details['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        </div>
    @endif

</div>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#18181B">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @php
        $isArabic        = app()->getLocale() === 'ar';
        $planTitle       = $assignment->title ?? $plan->title ?? trans('sw.training_plan');
        $planTypeLabel   = $isDietPlan ? trans('sw.plan_diet') : trans('sw.plan_training');
        $planIcon        = $isDietPlan ? '🥗' : '🏋️';
        $memberName      = $member->name ?? '-';
        $memberCode      = $member->code ?? '-';
        $fromDate        = !empty($assignment->from_date) ? \Carbon\Carbon::parse($assignment->from_date)->translatedFormat('d M Y') : null;
        $toDate          = !empty($assignment->to_date)   ? \Carbon\Carbon::parse($assignment->to_date)->translatedFormat('d M Y')   : null;
        $stampDate       = !empty($assignment->created_at) ? \Carbon\Carbon::parse($assignment->created_at)->translatedFormat('d F Y') : ($fromDate ?? '');
        $stampTime       = !empty($assignment->created_at) ? \Carbon\Carbon::parse($assignment->created_at)->format('H:i') : '';
        $assignmentWeight = $assignment->weight ?? null;
        $assignmentHeight = $assignment->height ?? null;
        $notes           = trim((string)($assignment->notes ?? ''));
        $detailsHtml     = trim((string)($planDetailsHtml ?? ''));
        $detailsText     = trim(strip_tags($detailsHtml));
        $hasHtmlDetails  = $detailsHtml !== '' && $detailsHtml !== $detailsText;

        // Active / expired status
        $now = now();
        $isActive = true;
        if ($toDate && \Carbon\Carbon::parse($assignment->to_date)->lt($now)) {
            $isActive = false;
        }
        $statusLabel = $isActive
            ? ($isArabic ? 'نشطة' : 'Active')
            : ($isArabic ? 'منتهية' : 'Expired');
    @endphp

    <style>
        /* ─── Reset ─── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:      #F2F5FB;
            --surface: #FFFFFF;
            --dark:    #18181B;
            --dark2:   #27272A;
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
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
        }

        .back-btn:active { background: rgba(255,255,255,0.12); }

        .topbar-stamp {
            font-size: 11px;
            color: #94A3B8;
            font-weight: 500;
            text-align: end;
            line-height: 1.5;
        }

        /* ─── Hero ─── */
        .hero {
            background: linear-gradient(145deg, #18181B 0%, #27272A 55%, #18181B 100%);
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
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.14);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 14px;
        }

        .hero-type-label {
            font-size: 12px;
            font-weight: 700;
            color: rgba(255,255,255,0.55);
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
            color: rgba(248,250,252,0.65);
            line-height: 1.85;
        }

        .hero-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 14px;
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 999px;
            padding: 5px 14px;
            color: #fff;
            font-size: 12px;
            font-weight: 700;
        }

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
            position: relative;
            z-index: 0;
            isolation: isolate;
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
            background: #F1F5F9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
        }

        .sec-title {
            font-size: 15px;
            font-weight: 800;
            color: var(--ink);
        }

        .sec-count {
            margin-inline-start: auto;
            font-size: 11px;
            font-weight: 700;
            color: var(--muted);
            background: #F3F4F6;
            border-radius: 999px;
            padding: 2px 9px;
        }

        .sec-body { padding: 14px 16px; }

        /* ─── Description ─── */
        .desc-body {
            font-size: 14px;
            line-height: 1.95;
            color: var(--ink2);
            overflow-wrap: anywhere;
            white-space: pre-wrap;
        }

        .desc-body p:first-child { margin-top: 0; }
        .desc-body p:last-child  { margin-bottom: 0; }

        /* ─── Notes Box ─── */
        .notes-box {
            margin-top: 12px;
            background: #FEFCE8;
            border: 1px solid #FDE68A;
            border-radius: 14px;
            padding: 12px 14px;
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }

        .notes-icon { font-size: 16px; flex-shrink: 0; margin-top: 2px; }

        .notes-text {
            font-size: 13px;
            line-height: 1.85;
            color: #78350F;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        /* ─── Tasks ─── */
        .tasks-list { display: flex; flex-direction: column; gap: 9px; }

        .task-item {
            border-radius: 14px;
            border: 1px solid #EEF2F7;
            background: #FAFBFF;
            padding: 13px;
        }

        .task-header {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 5px;
        }

        .task-num {
            width: 26px;
            height: 26px;
            border-radius: 8px;
            background: var(--dark);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 900;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .task-title {
            font-size: 14px;
            font-weight: 800;
            color: var(--ink);
            line-height: 1.4;
        }

        .task-notes {
            font-size: 13px;
            color: #4B5563;
            line-height: 1.8;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            padding-inline-start: 36px;
        }

        /* ─── Empty State ─── */
        .empty-wrap {
            text-align: center;
            padding: 28px 16px;
            color: var(--muted);
        }

        .empty-wrap .ei { font-size: 36px; display: block; margin-bottom: 10px; }
        .empty-wrap p   { font-size: 13px; line-height: 1.7; }
    </style>
</head>
<body>

    {{-- ─── Sticky Topbar ─── --}}
    <header class="topbar">
        <button class="back-btn" onclick="history.back()" type="button">
            <span>{{ $isArabic ? '→' : '←' }}</span>
            <span>{{ $isArabic ? 'رجوع' : 'Back' }}</span>
        </button>
        @if($stampDate)
            <div class="topbar-stamp">
                {{ $stampDate }}@if($stampTime)<br>{{ $stampTime }}@endif
            </div>
        @endif
    </header>

    {{-- ─── Hero ─── --}}
    <div class="hero">
        <div class="hero-bubble-1"></div>
        <div class="hero-bubble-2"></div>
        <div class="hero-inner">
            <div class="hero-icon-wrap">{{ $planIcon }}</div>
            <div class="hero-type-label">{{ $planTypeLabel }}</div>
            <h1 class="hero-title">{{ $planTitle }}</h1>
            <p class="hero-summary">
                {{ $isArabic ? 'خطة مخصصة للعضو' : 'Assigned plan for' }} {{ $memberName }}
                @if($fromDate && $toDate)
                    &nbsp;·&nbsp; {{ $fromDate }} — {{ $toDate }}
                @endif
            </p>
            <span class="hero-action">
                <span>{{ $isActive ? '✔' : '✕' }}</span>
                <span>{{ $statusLabel }}</span>
            </span>
        </div>
    </div>

    {{-- ─── Member Strip ─── --}}
    <div class="member-strip">
        <div class="ms-item">
            <span class="ms-label">{{ $isArabic ? 'الاسم' : 'Name' }}</span>
            <span class="ms-value">{{ $memberName }}</span>
        </div>
        <div class="ms-divider"></div>
        <div class="ms-item">
            <span class="ms-label">{{ $isArabic ? 'الكود' : 'Code' }}</span>
            <span class="ms-value">#{{ $memberCode }}</span>
        </div>
        <div class="ms-divider"></div>
        <div class="ms-item">
            <span class="ms-label">{{ $isArabic ? 'النوع' : 'Type' }}</span>
            <span class="ms-value">{{ $planTypeLabel }}</span>
        </div>
        @if($fromDate)
            <div class="ms-divider"></div>
            <div class="ms-item">
                <span class="ms-label">{{ $isArabic ? 'من' : 'From' }}</span>
                <span class="ms-value">{{ $fromDate }}</span>
            </div>
        @endif
        @if($toDate)
            <div class="ms-divider"></div>
            <div class="ms-item">
                <span class="ms-label">{{ $isArabic ? 'إلى' : 'To' }}</span>
                <span class="ms-value">{{ $toDate }}</span>
            </div>
        @endif
        @if(!empty($assignmentWeight))
            <div class="ms-divider"></div>
            <div class="ms-item">
                <span class="ms-label">{{ $isArabic ? 'الوزن' : 'Weight' }}</span>
                <span class="ms-value">{{ $assignmentWeight }}</span>
            </div>
        @endif
        @if(!empty($assignmentHeight))
            <div class="ms-divider"></div>
            <div class="ms-item">
                <span class="ms-label">{{ $isArabic ? 'الطول' : 'Height' }}</span>
                <span class="ms-value">{{ $assignmentHeight }}</span>
            </div>
        @endif
    </div>

    {{-- ─── Content Sections ─── --}}
    <div class="content">

        {{-- Description --}}
        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon">📄</div>
                <span class="sec-title">{{ $isArabic ? 'وصف الخطة' : 'Plan Description' }}</span>
            </div>
            <div class="sec-body">
                @if($detailsHtml !== '')
                    <div class="desc-body">
                        @if($hasHtmlDetails)
                            {!! $detailsHtml !!}
                        @else
                            {!! nl2br(e($detailsText)) !!}
                        @endif
                    </div>
                @else
                    <div class="empty-wrap">
                        <span class="ei">📭</span>
                        <p>{{ $isArabic ? 'لا يوجد وصف لهذه الخطة.' : 'No description available for this plan.' }}</p>
                    </div>
                @endif

                @if($notes !== '')
                    <div class="notes-box">
                        <span class="notes-icon">📌</span>
                        <div class="notes-text">{{ $notes }}</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Tasks --}}
        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon">✅</div>
                <span class="sec-title">{{ $isArabic ? 'مهام الخطة' : 'Plan Tasks' }}</span>
                @if($tasks->isNotEmpty())
                    <span class="sec-count">{{ $tasks->count() }}</span>
                @endif
            </div>
            <div class="sec-body">
                @if($tasks->isEmpty())
                    <div class="empty-wrap">
                        <span class="ei">🗒️</span>
                        <p>{{ $isArabic ? 'لا توجد مهام مضافة لهذه الخطة.' : 'No tasks added to this plan yet.' }}</p>
                    </div>
                @else
                    <div class="tasks-list">
                        @foreach($tasks as $index => $task)
                            <div class="task-item">
                                <div class="task-header">
                                    <span class="task-num">{{ $index + 1 }}</span>
                                    <span class="task-title">{{ $task['title'] !== '' ? $task['title'] : ($isArabic ? 'مهمة ' . ($index + 1) : 'Task ' . ($index + 1)) }}</span>
                                </div>
                                @if($task['notes'] !== '')
                                    <div class="task-notes">{!! nl2br(e($task['notes'])) !!}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>

</body>
</html>

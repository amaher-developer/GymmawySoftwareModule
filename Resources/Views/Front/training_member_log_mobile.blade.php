<!DOCTYPE html>
<html lang="{{ $lang }}" dir="{{ $isArabic ? 'rtl' : 'ltr' }}">
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

        /* ─── Type Color Tokens ─── */
        .t-assessment { --tc:#7C3AED; --tc-bg:#F5F3FF; --tc-bd:#DDD6FE; }
        .t-plan       { --tc:#1D4ED8; --tc-bg:#EFF6FF; --tc-bd:#BFDBFE; }
        .t-medicine   { --tc:#BE123C; --tc-bg:#FFF1F2; --tc-bd:#FECDD3; }
        .t-note       { --tc:#047857; --tc-bg:#ECFDF5; --tc-bd:#A7F3D0; }
        .t-file       { --tc:#B45309; --tc-bg:#FFFBEB; --tc-bd:#FDE68A; }
        .t-track      { --tc:#0369A1; --tc-bg:#F0F9FF; --tc-bd:#BAE6FD; }
        .t-ai         { --tc:#6D28D9; --tc-bg:#F5F3FF; --tc-bd:#DDD6FE; }
        .t-ai_plan    { --tc:#5B21B6; --tc-bg:#F5F3FF; --tc-bd:#DDD6FE; }
        .t-activity   { --tc:#047857; --tc-bg:#ECFDF5; --tc-bd:#A7F3D0; }

        /* ─── Sticky App Header ─── */
        .app-header {
            position: sticky;
            top: 0;
            z-index: 200;
            background: var(--dark);
            padding: 0 18px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.25);
        }

        .header-title {
            color: #F1F5F9;
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 0.1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .member-badge {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.14);
            border-radius: 999px;
            padding: 5px 12px 5px 8px;
            color: #CBD5E1;
            font-size: 12px;
            font-weight: 600;
            max-width: 170px;
        }

        .member-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.22);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 900;
            color: #fff;
            flex-shrink: 0;
        }

        .member-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .member-code {
            color: #9CA3AF;
            font-weight: 700;
            font-size: 11px;
        }

        /* ─── Hero ─── */
        .hero {
            background: linear-gradient(150deg, #18181B 0%, #27272A 50%, #18181B 100%);
            padding: 28px 20px 32px;
            position: relative;
            overflow: hidden;
        }

        .hero-bubble-1 {
            position: absolute;
            top: -70px;
            inset-inline-end: -50px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
            pointer-events: none;
        }

        .hero-bubble-2 {
            position: absolute;
            bottom: -60px;
            inset-inline-start: -40px;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: rgba(255,255,255,0.03);
            pointer-events: none;
        }

        .hero-inner {
            position: relative;
            z-index: 1;
        }

        .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 999px;
            padding: 4px 12px;
            color: #D1D5DB;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 12px;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }

        .hero-title {
            font-size: clamp(24px, 6.5vw, 38px);
            font-weight: 900;
            color: #F8FAFC;
            line-height: 1.2;
            letter-spacing: -0.3px;
        }

        .hero-sub {
            margin-top: 8px;
            font-size: 14px;
            color: rgba(248,250,252,0.6);
            line-height: 1.8;
            max-width: 340px;
        }

        .hero-stats {
            margin-top: 18px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .stat-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 10px;
            padding: 7px 13px;
            color: #E2E8F0;
            font-size: 13px;
            font-weight: 700;
        }

        .stat-pill .sp-icon { font-size: 15px; }

        /* ─── Filters Strip ─── */
        .filters-bar {
            position: sticky;
            top: 56px;
            z-index: 150;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            padding: 10px 0;
        }

        .filters-scroll {
            display: flex;
            gap: 7px;
            overflow-x: auto;
            padding: 0 16px;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .filters-scroll::-webkit-scrollbar { display: none; }

        .f-pill {
            flex-shrink: 0;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 13px;
            border-radius: 999px;
            border: 1.5px solid #E5E7EB;
            background: #FAFAFA;
            color: #4B5563;
            font-family: var(--ff);
            font-size: 12px;
            font-weight: 700;
            transition: all 0.18s ease;
            -webkit-tap-highlight-color: transparent;
        }

        .f-pill:active { opacity: 0.75; }

        .f-pill.active {
            background: var(--dark);
            border-color: var(--dark);
            color: #fff;
        }

        .f-pill .fi { font-size: 13px; }

        .f-count {
            font-size: 10px;
            font-weight: 800;
            padding: 1px 7px;
            border-radius: 999px;
            background: rgba(0,0,0,0.08);
        }

        .f-pill.active .f-count { background: rgba(255,255,255,0.18); }

        /* ─── Cards Feed ─── */
        .feed {
            padding: 14px 14px 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding-bottom: calc(16px + var(--safe-b));
            position: relative;
            z-index: 0;
            isolation: isolate;
        }

        /* ─── Log Card ─── */
        .log-card {
            text-decoration: none;
            color: inherit;
            display: block;
            background: var(--surface);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05), 0 4px 16px rgba(0,0,0,0.07);
            border: 1px solid rgba(0,0,0,0.04);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .log-card:active {
            transform: scale(0.985);
            box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        }

        .card-stripe {
            height: 3px;
            background: var(--tc, #059669);
        }

        .card-inner {
            padding: 14px 16px;
        }

        .card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
        }

        .type-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 8px;
            background: var(--tc-bg, #ECFDF5);
            border: 1px solid var(--tc-bd, #A7F3D0);
            color: var(--tc, #047857);
            font-family: var(--ff);
            font-size: 12px;
            font-weight: 800;
        }

        .type-tag .ti { font-size: 13px; }

        .card-date {
            flex-shrink: 0;
        }

        [dir="rtl"] .card-date { text-align: left; }
        [dir="ltr"] .card-date { text-align: right; }

        .date-d {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #374151;
        }

        .date-t {
            display: block;
            font-size: 10px;
            color: #9CA3AF;
            margin-top: 2px;
            font-weight: 500;
        }

        .card-summary {
            margin: 10px 0 12px;
            font-size: 13.5px;
            line-height: 1.8;
            color: #374151;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 10px;
            border-top: 1px solid #F3F4F6;
            gap: 10px;
        }

        .action-tag {
            font-size: 11px;
            font-weight: 800;
            color: var(--tc, #047857);
            background: var(--tc-bg, #ECFDF5);
            border-radius: 999px;
            padding: 3px 10px;
            white-space: nowrap;
        }

        .see-more {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 800;
            color: var(--dark);
            white-space: nowrap;
        }

        .arrow-circle {
            width: 22px;
            height: 22px;
            background: #F1F5F9;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
        }

        /* ─── Empty State ─── */
        .empty-wrap {
            background: var(--surface);
            border-radius: 20px;
            border: 2px dashed #D1D5DB;
            padding: 48px 24px;
            text-align: center;
        }

        .empty-emoji { font-size: 52px; display: block; margin-bottom: 14px; }

        .empty-title {
            font-size: 17px;
            font-weight: 800;
            color: var(--ink);
            margin-bottom: 8px;
        }

        .empty-body {
            font-size: 13px;
            color: var(--muted);
            line-height: 1.8;
        }

        /* ─── Pagination ─── */
        .pager {
            padding: 4px 0;
        }

        .pager .pagination {
            margin: 0;
            justify-content: center;
            gap: 4px;
            flex-wrap: wrap;
        }

        .pager .page-link {
            border-radius: 10px;
            color: var(--dark);
            border: 1.5px solid var(--border);
            background: var(--surface);
            padding: 8px 13px;
            font-size: 13px;
            font-family: var(--ff);
            font-weight: 600;
        }

        .pager .page-item.active .page-link {
            background: var(--dark);
            border-color: var(--dark);
            color: #fff;
        }

        .pager .page-item.disabled .page-link {
            opacity: 0.4;
        }
    </style>
</head>
<body>

    {{-- ─── Sticky App Header ─── --}}
    <!-- <header class="app-header">
        <span class="header-title">{{ trans('sw.training_member_logs') }}</span>
        <div class="member-badge">
            @php $initials = mb_strtoupper(mb_substr($member->name ?? 'M', 0, 1)); @endphp
            <div class="member-avatar">{{ $initials }}</div>
            <span class="member-name">{{ $member->name ?? '-' }}</span>
            <span class="member-code">#{{ $member->code ?? $member->id }}</span>
        </div>
    </header> -->

    {{-- ─── Hero ─── --}}
    <div class="hero">
        <div class="hero-bubble-1"></div>
        <div class="hero-bubble-2"></div>
        <div class="hero-inner">
            <div class="hero-eyebrow">
                <span>🏋️</span>
                <span>{{ $isArabic ? 'سجلات التدريب' : 'Training Logs' }}</span>
            </div>
            <!-- <h1 class="hero-title">
                {{ $isArabic ? 'كل سجلاتك' : 'Your Activity' }}<br>
                {{ $isArabic ? 'في مكان واحد' : 'All in One Place' }}
            </h1> -->
            <p class="hero-sub">{{ $isArabic ? 'تتبع تقدمك وخططك وتقييماتك بكل سهولة.' : 'Track your progress, plans, and assessments with ease.' }}</p>
            @php
                $totalLogs = collect($filters)->firstWhere('value', 'all')['count'] ?? 0;
            @endphp
            <div class="hero-stats">
                <span class="stat-pill">
                    <span class="sp-icon">📊</span>
                    <span>{{ $totalLogs }} {{ $isArabic ? 'سجل' : 'Logs' }}</span>
                </span>
                <span class="stat-pill">
                    <span class="sp-icon">👤</span>
                    <span>{{ $member->name ?? '-' }}</span>
                </span>
            </div>
        </div>
    </div>

    {{-- ─── Filters ─── --}}
    <nav class="filters-bar" aria-label="{{ $isArabic ? 'التصفية' : 'Filters' }}">
        <div class="filters-scroll">
            @php
                $filterIcons = [
                    'all'        => '🔍',
                    'assessment' => '📋',
                    'plan'       => '🏋️',
                    'medicine'   => '💊',
                    'note'       => '📝',
                    'file'       => '📎',
                    'track'      => '📊',
                    'ai'         => '🤖',
                    'ai_plan'    => '✨',
                ];
            @endphp
            @foreach($filters as $filter)
                <a href="{{ $filter['url'] }}" class="f-pill {{ $filter['active'] ? 'active' : '' }}">
                    <span class="fi">{{ $filterIcons[$filter['value']] ?? '•' }}</span>
                    <span>{{ $filter['label'] }}</span>
                    <span class="f-count">{{ $filter['count'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>

    {{-- ─── Feed ─── --}}
    <main class="feed">
        @php
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
        @endphp

        @if($logs->count() > 0)

            @foreach($logs as $item)
                @php
                    $rawType  = $item['type'] ?? 'activity';
                    $tClass   = 't-' . str_replace('_', '_', $rawType);
                    $icon     = $typeIcons[$rawType] ?? '🎯';
                @endphp
                <a class="log-card {{ $tClass }}" href="{{ $item['details_url'] }}" aria-label="{{ $item['type_label'] }}">
                    <div class="card-stripe"></div>
                    <div class="card-inner">
                        <div class="card-top">
                            <div class="type-tag">
                                <span class="ti">{{ $icon }}</span>
                                <span>{{ $item['type_label'] }}</span>
                            </div>
                            <div class="card-date">
                                <span class="date-d">{{ $item['date'] }}</span>
                                <span class="date-t">{{ $item['time'] }}</span>
                            </div>
                        </div>
                        <p class="card-summary">{{ $item['summary'] ?: ($isArabic ? 'لا يوجد وصف.' : 'No description available.') }}</p>
                        <div class="card-bottom">
                            <span class="action-tag">{{ $item['action_label'] }}</span>
                            <span class="see-more">
                                {{ $isArabic ? 'عرض التفاصيل' : 'View Details' }}
                                <span class="arrow-circle">{{ $isArabic ? '←' : '→' }}</span>
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach

            @if($logs->hasPages())
                <div class="pager">{{ $logs->links() }}</div>
            @endif

        @else

            <div class="empty-wrap">
                <span class="empty-emoji">📭</span>
                <p class="empty-title">{{ $isArabic ? 'لا توجد سجلات حالياً' : 'No Logs Yet' }}</p>
                <p class="empty-body">{{ $isArabic ? 'بمجرد إضافة تقييمات أو خطط أو قياسات ستظهر هنا.' : 'Once assessments, plans, notes or tracks are added, they will appear here.' }}</p>
            </div>

        @endif
    </main>

</body>
</html>

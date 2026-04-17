<!DOCTYPE html>
<html lang="{{ $lang }}" dir="{{ $isArabic ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if($isArabic)
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    @else
        <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    @endif
    <style>
        :root {
            --bg-1: #f3f7f2;
            --bg-2: #e7eef9;
            --surface: rgba(255, 255, 255, 0.88);
            --surface-2: #ffffff;
            --text: #10231f;
            --muted: #5b706f;
            --line: #d7e3e3;
            --brand: #0d9f6e;
            --brand-dark: #077855;
            --chip: #eff5f4;
            --chip-active: #0b8f64;
            --chip-text: #305550;
            --warning: #ffeecf;
            --shadow: 0 22px 44px rgba(19, 35, 35, 0.12);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--text);
            direction: {{ $isArabic ? 'rtl' : 'ltr' }};
            font-family: {{ $isArabic ? "'Cairo', sans-serif" : "'Space Grotesk', sans-serif" }};
            background:
                radial-gradient(1200px 520px at 10% -10%, #d7f6e6 0%, transparent 56%),
                radial-gradient(900px 520px at 92% 2%, #d9e9ff 0%, transparent 58%),
                linear-gradient(165deg, var(--bg-1) 0%, var(--bg-2) 100%);
        }

        .page {
            max-width: 1080px;
            margin: 0 auto;
            padding: 18px 14px 26px;
        }

        .hero {
            position: relative;
            overflow: hidden;
            border-radius: 24px;
            padding: 20px 18px;
            color: #f8fffd;
            background: linear-gradient(130deg, #0f3942 0%, #0a7e66 44%, #0f9f74 100%);
            box-shadow: var(--shadow);
        }

        .hero::before,
        .hero::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }

        .hero::before {
            width: 180px;
            height: 180px;
            top: -60px;
            inset-inline-end: -50px;
            background: rgba(255, 255, 255, 0.15);
        }

        .hero::after {
            width: 120px;
            height: 120px;
            bottom: -40px;
            inset-inline-start: -35px;
            background: rgba(255, 255, 255, 0.12);
        }

        .hero-head {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .hero-title {
            margin: 0;
            font-size: clamp(22px, 5vw, 34px);
            letter-spacing: 0.2px;
        }

        .hero-sub {
            margin: 6px 0 0;
            font-size: 14px;
            color: rgba(248, 255, 253, 0.88);
        }

        .member-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(255, 255, 255, 0.24);
            border-radius: 999px;
            padding: 7px 14px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 13px;
        }

        .filters-wrap {
            margin-top: 14px;
            padding: 12px;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: var(--surface);
            backdrop-filter: blur(6px);
        }

        .filters-row {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 2px;
            scrollbar-width: thin;
        }

        .filters-row::-webkit-scrollbar { height: 6px; }

        .chip {
            text-decoration: none;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 14px;
            border-radius: 999px;
            border: 1px solid #dbe7e6;
            background: var(--chip);
            color: var(--chip-text);
            font-size: 13px;
            font-weight: 700;
            transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease;
        }

        .chip:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 16px rgba(8, 46, 39, 0.12);
        }

        .chip.active {
            color: #fff;
            border-color: var(--chip-active);
            background: linear-gradient(120deg, var(--chip-active) 0%, #12af7f 100%);
            box-shadow: 0 10px 18px rgba(10, 113, 80, 0.22);
        }

        .chip-count {
            font-size: 12px;
            padding: 2px 7px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.7);
            color: #19443d;
        }

        .chip.active .chip-count {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .cards {
            margin-top: 14px;
            display: grid;
            gap: 10px;
        }

        .card {
            text-decoration: none;
            color: inherit;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: var(--surface-2);
            box-shadow: 0 10px 26px rgba(22, 46, 47, 0.08);
            padding: 14px;
            display: block;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 30px rgba(14, 39, 38, 0.14);
        }

        .card-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            flex-wrap: wrap;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid #cae7dc;
            background: #f1fbf6;
            color: #16684f;
        }

        .meta {
            text-align: {{ $isArabic ? 'left' : 'right' }};
            font-size: 12px;
            color: var(--muted);
        }

        .summary {
            margin: 10px 0 12px;
            color: #1e3634;
            line-height: 1.85;
            font-size: 14px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .action {
            color: #235d50;
            font-size: 12px;
            font-weight: 700;
        }

        .more {
            font-size: 13px;
            color: var(--brand-dark);
            font-weight: 800;
        }

        .empty {
            margin-top: 14px;
            border-radius: 18px;
            border: 1px dashed #b7ceca;
            background: var(--warning);
            color: #6f5e2c;
            text-align: center;
            padding: 24px 14px;
        }

        .pagination-wrap {
            margin-top: 14px;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: var(--surface);
            padding: 8px;
            overflow-x: auto;
        }

        .pagination-wrap .pagination {
            margin: 0;
            justify-content: center;
            gap: 5px;
            flex-wrap: nowrap;
            min-width: max-content;
        }

        .pagination-wrap .page-link {
            border-radius: 10px;
            color: #24564d;
            border: 1px solid #d6e7e2;
            background: #fff;
            padding: 8px 11px;
            font-size: 13px;
        }

        .pagination-wrap .page-item.active .page-link {
            background: var(--brand);
            border-color: var(--brand);
            color: #fff;
        }

        @media (min-width: 880px) {
            .cards {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .card:nth-child(3n) {
                grid-column: span 2;
            }
        }

        @media (max-width: 540px) {
            .page { padding-inline: 10px; }
            .hero { border-radius: 20px; padding: 16px 14px; }
            .filters-wrap { padding: 10px; }
            .card { padding: 12px; }
        }
    </style>
</head>
<body>
    <main class="page">
        <section class="hero">
            <div class="hero-head">
                <div>
                    <h1 class="hero-title">{{ trans('sw.training_member_logs') }}</h1>
                    <p class="hero-sub">{{ $isArabic ? 'كل سجلات العضو في مكان واحد مع تصفية حسب نوع النشاط.' : 'All member logs in one place, with quick filtering by activity type.' }}</p>
                </div>
                <div class="member-chip">
                    <strong>{{ $member->name ?? '-' }}</strong>
                    <span>#{{ $member->code ?? $member->id }}</span>
                </div>
            </div>
        </section>

        <section class="filters-wrap" aria-label="filters">
            <div class="filters-row">
                @foreach($filters as $filter)
                    <a href="{{ $filter['url'] }}" class="chip {{ $filter['active'] ? 'active' : '' }}">
                        <span>{{ $filter['label'] }}</span>
                        <span class="chip-count">{{ $filter['count'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>

        @if($logs->count() > 0)
            <section class="cards">
                @foreach($logs as $item)
                    <a class="card" href="{{ $item['details_url'] }}">
                        <div class="card-head">
                            <div class="tag">{{ $item['type_label'] }}</div>
                            <div class="meta">
                                <div>{{ $item['date'] }}</div>
                                <div>{{ $item['time'] }}</div>
                            </div>
                        </div>
                        <p class="summary">{{ $item['summary'] }}</p>
                        <div class="card-foot">
                            <span class="action">{{ $item['action_label'] }}</span>
                            <span class="more">{{ $isArabic ? 'عرض التفاصيل' : 'View details' }}</span>
                        </div>
                    </a>
                @endforeach
            </section>

            @if($logs->hasPages())
                <div class="pagination-wrap">
                    {{ $logs->links() }}
                </div>
            @endif
        @else
            <section class="empty">
                <h3 style="margin:0 0 8px; font-size:18px;">{{ $isArabic ? 'لا توجد سجلات حالياً' : 'No logs available yet' }}</h3>
                <p style="margin:0; font-size:14px;">{{ $isArabic ? 'بمجرد إضافة تقييمات أو خطط أو قياسات ستظهر هنا.' : 'Once assessments, plans, notes, or tracks are added, they will appear here.' }}</p>
            </section>
        @endif
    </main>
</body>
</html>

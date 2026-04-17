<!DOCTYPE html>
<html lang="{{ $lang }}" dir="{{ $isArabic ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $typeLabel }} - {{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if($isArabic)
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    @else
        <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    @endif
    <style>
        :root {
            --bg: #f4f7fd;
            --surface: #ffffff;
            --ink: #152739;
            --muted: #5c6f7d;
            --line: #d6e2ee;
            --brand: #1a7ed4;
            --brand-dark: #135e9f;
            --soft: #eef5fd;
            --soft-2: #f7fbff;
            --ok: #e9faf2;
            --shadow: 0 20px 36px rgba(24, 45, 66, 0.12);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--ink);
            background:
                radial-gradient(860px 460px at 0% -10%, #dfeeff 0%, transparent 58%),
                radial-gradient(860px 460px at 100% -10%, #e0f7f1 0%, transparent 58%),
                var(--bg);
            direction: {{ $isArabic ? 'rtl' : 'ltr' }};
            font-family: {{ $isArabic ? "'Cairo', sans-serif" : "'Space Grotesk', sans-serif" }};
        }

        .page {
            max-width: 980px;
            margin: 0 auto;
            padding: 16px 14px 28px;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }

        .back-link {
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            border: 1px solid #c9dae8;
            background: #fff;
            color: #23445e;
            padding: 8px 12px;
            font-weight: 700;
            font-size: 13px;
        }

        .stamp {
            font-size: 12px;
            color: var(--muted);
        }

        .hero {
            border-radius: 22px;
            background: linear-gradient(126deg, #102f49 0%, #1f6cae 45%, #37a4d5 100%);
            color: #f3fbff;
            padding: 18px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .hero::after {
            content: "";
            position: absolute;
            width: 190px;
            height: 190px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            top: -70px;
            inset-inline-end: -50px;
        }

        .hero h1 {
            position: relative;
            z-index: 1;
            margin: 0;
            font-size: clamp(21px, 5vw, 32px);
        }

        .hero p {
            position: relative;
            z-index: 1;
            margin: 8px 0 0;
            color: rgba(243, 251, 255, 0.88);
            line-height: 1.8;
            font-size: 14px;
        }

        .grid {
            margin-top: 14px;
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 12px;
        }

        .card {
            grid-column: span 12;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: var(--surface);
            box-shadow: 0 12px 28px rgba(18, 41, 62, 0.08);
            padding: 15px;
        }

        .card h2 {
            margin: 0 0 12px;
            font-size: 18px;
        }

        .meta-list {
            display: grid;
            gap: 10px;
        }

        .meta-item {
            border-radius: 14px;
            border: 1px solid #d9e6f1;
            background: var(--soft-2);
            padding: 10px 12px;
        }

        .meta-item .label {
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 4px;
        }

        .meta-item .value {
            font-size: 14px;
            line-height: 1.75;
            font-weight: 700;
            word-break: break-word;
        }

        .summary {
            padding: 13px;
            border-radius: 14px;
            border: 1px solid #cde2f6;
            background: var(--soft);
            line-height: 1.9;
            font-size: 14px;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .box {
            border: 1px solid #dbe7f3;
            border-radius: 14px;
            padding: 10px;
            background: #fbfdff;
        }

        .box-title {
            margin: 0 0 7px;
            font-size: 14px;
            color: #254764;
            font-weight: 800;
        }

        .inline-grid {
            display: grid;
            gap: 8px;
        }

        .inline-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            border-bottom: 1px dashed #dce7f1;
            padding-bottom: 6px;
        }

        .inline-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .inline-row .k {
            color: var(--muted);
            font-size: 12px;
        }

        .inline-row .v {
            font-size: 13px;
            font-weight: 700;
            text-align: {{ $isArabic ? 'left' : 'right' }};
            max-width: 70%;
        }

        .tasks {
            display: grid;
            gap: 8px;
        }

        .task {
            border-radius: 12px;
            border: 1px solid #d5e4ef;
            padding: 10px;
            background: #fcfeff;
        }

        .task .t {
            font-weight: 800;
            margin-bottom: 4px;
            font-size: 14px;
        }

        .task .n {
            color: #31506a;
            line-height: 1.8;
            font-size: 13px;
            white-space: pre-wrap;
        }

        .download {
            display: inline-flex;
            margin-top: 10px;
            text-decoration: none;
            border-radius: 10px;
            background: var(--brand);
            color: #fff;
            padding: 8px 12px;
            font-weight: 700;
            font-size: 13px;
        }

        .json {
            font-family: Consolas, Menlo, Monaco, monospace;
            font-size: 12px;
            line-height: 1.7;
            background: #0f2235;
            color: #c9e9ff;
            border-radius: 12px;
            padding: 12px;
            overflow: auto;
            white-space: pre;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            border: 1px solid #c6e9db;
            background: var(--ok);
            color: #1a6f4f;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 700;
            margin-top: 6px;
        }

        @media (min-width: 840px) {
            .span-5 { grid-column: span 5; }
            .span-7 { grid-column: span 7; }
            .span-6 { grid-column: span 6; }
        }

        @media (max-width: 520px) {
            .page { padding-inline: 10px; }
            .hero { padding: 15px; border-radius: 18px; }
            .card { padding: 12px; }
        }
    </style>
</head>
<body>
    <main class="page">
        <div class="topbar">
            <a class="back-link" href="{{ $backUrl }}">
                <span>{{ $isArabic ? 'رجوع' : 'Back' }}</span>
            </a>
            <div class="stamp">{{ optional($log->created_at)->translatedFormat('d F Y H:i') }}</div>
        </div>

        <section class="hero">
            <h1>{{ $typeLabel }}</h1>
            <p>{{ $summary }}</p>
            <span class="status">{{ $actionLabel }}</span>
        </section>

        <section class="grid">
            <article class="card span-5">
                <h2>{{ $isArabic ? 'العضو' : 'Member' }}</h2>
                <div class="meta-list">
                    <div class="meta-item">
                        <div class="label">{{ $isArabic ? 'الاسم' : 'Name' }}</div>
                        <div class="value">{{ $member->name ?? '-' }}</div>
                    </div>
                    <div class="meta-item">
                        <div class="label">{{ $isArabic ? 'الكود' : 'Code' }}</div>
                        <div class="value">#{{ $member->code ?? $memberId }}</div>
                    </div>
                    <div class="meta-item">
                        <div class="label">{{ $isArabic ? 'نوع السجل' : 'Log Type' }}</div>
                        <div class="value">{{ $typeLabel }}</div>
                    </div>
                </div>
            </article>

            <article class="card span-7">
                <h2>{{ $isArabic ? 'الملخص' : 'Summary' }}</h2>
                <div class="summary">{{ $summary }}</div>
            </article>

            @if(!empty($details['notes']) || !empty($details['note']))
                <article class="card span-12">
                    <h2>{{ $isArabic ? 'ملاحظات' : 'Notes' }}</h2>
                    <div class="summary">{{ $details['notes'] ?? $details['note'] }}</div>
                </article>
            @endif

            @if(!empty($details['answers']) && is_array($details['answers']))
                <article class="card span-12">
                    <h2>{{ $isArabic ? 'بيانات التقييم' : 'Assessment Data' }}</h2>
                    <div class="box">
                        <div class="inline-grid">
                            @foreach($details['answers'] as $key => $value)
                                <div class="inline-row">
                                    <span class="k">{{ is_string($key) ? str_replace('_', ' ', ucfirst($key)) : $key }}</span>
                                    <span class="v">{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </article>
            @endif

            @if(!empty($details['tasks']) && is_array($details['tasks']))
                <article class="card span-12">
                    <h2>{{ $isArabic ? 'مهام الخطة' : 'Plan Tasks' }}</h2>
                    <div class="tasks">
                        @foreach($details['tasks'] as $task)
                            <div class="task">
                                <div class="t">{{ $task['title'] ?? '-' }}</div>
                                @if(!empty($task['notes']))
                                    <div class="n">{{ $task['notes'] }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @if(!empty($details['member_plan_id']))
                        @php
                            $token = request('payment_link_token') ?: request('token');
                            $downloadUrl = route('sw.training-plan-mobile', [
                                'id' => (int) $details['member_plan_id'],
                                'token' => $token,
                                'lang' => $lang,
                            ]);
                        @endphp
                        <a class="download" href="{{ $downloadUrl }}">{{ $isArabic ? 'فتح الخطة الكاملة' : 'Open Full Plan' }}</a>
                    @endif
                </article>
            @endif

            @if(!empty($details['measurements']) && is_array($details['measurements']))
                <article class="card span-6">
                    <h2>{{ $isArabic ? 'القياسات' : 'Measurements' }}</h2>
                    <div class="box">
                        <div class="inline-grid">
                            @foreach($details['measurements'] as $key => $value)
                                <div class="inline-row">
                                    <span class="k">{{ str_replace('_', ' ', ucfirst($key)) }}</span>
                                    <span class="v">{{ $value }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </article>
            @endif

            @if(!empty($details['calculations']) && is_array($details['calculations']))
                <article class="card span-6">
                    <h2>{{ $isArabic ? 'الحسابات' : 'Calculations' }}</h2>
                    <div class="box">
                        <div class="inline-grid">
                            @foreach($details['calculations'] as $key => $value)
                                <div class="inline-row">
                                    <span class="k">{{ str_replace('_', ' ', ucfirst($key)) }}</span>
                                    <span class="v">{{ $value }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </article>
            @endif

            @if(!empty($details['path']))
                <article class="card span-12">
                    <h2>{{ $isArabic ? 'الملف' : 'File' }}</h2>
                    <div class="meta-item">
                        <div class="label">{{ $details['title'] ?? ($isArabic ? 'عنوان الملف' : 'File Title') }}</div>
                        <div class="value">{{ $details['file_name'] ?? $details['path'] }}</div>
                    </div>
                    <a class="download" href="{{ $details['path'] }}" target="_blank">{{ $isArabic ? 'تحميل / فتح الملف' : 'Open / Download File' }}</a>
                </article>
            @endif

            @if(!empty($details['response']) && is_array($details['response']))
                <article class="card span-12">
                    <h2>{{ $isArabic ? 'تفاصيل الذكاء الاصطناعي' : 'AI Details' }}</h2>
                    <pre class="json">{{ json_encode($details['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                </article>
            @endif
        </section>
    </main>
</body>
</html>

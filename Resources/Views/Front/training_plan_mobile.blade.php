<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title }}</title>
    @php
        $isRtl = app()->getLocale() === 'ar';
        $textAlign = $isRtl ? 'right' : 'left';
        $planTitle = $assignment->title ?? $plan->title ?? trans('sw.training_plan');
        $planTypeLabel = $isDietPlan ? trans('sw.plan_diet') : trans('sw.plan_training');
        $memberName = $member->name ?? '-';
        $memberCode = $member->code ?? '-';
        $fromDate = !empty($assignment->from_date) ? \Carbon\Carbon::parse($assignment->from_date)->format('Y-m-d') : null;
        $toDate = !empty($assignment->to_date) ? \Carbon\Carbon::parse($assignment->to_date)->format('Y-m-d') : null;
        $assignmentWeight = $assignment->weight ?? null;
        $assignmentHeight = $assignment->height ?? null;
        $notes = trim((string) ($assignment->notes ?? ''));
        $detailsHtml = trim((string) ($planDetailsHtml ?? ''));
        $detailsText = trim(strip_tags($detailsHtml));
        $hasHtmlDetails = $detailsHtml !== '' && $detailsHtml !== $detailsText;
    @endphp
    <style>
        :root {
            --bg: #f4f7fb;
            --card: #ffffff;
            --ink: #10233a;
            --muted: #6b7a90;
            --line: #dbe4ef;
            --accent: #17b26a;
            --accent-soft: #e8fff3;
            --primary: #1f6feb;
            --primary-soft: #ecf4ff;
            --shadow: 0 14px 40px rgba(16, 35, 58, 0.08);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Tahoma, Arial, sans-serif;
            background:
                radial-gradient(circle at top, #ffffff 0%, #eef6ff 34%, var(--bg) 72%),
                var(--bg);
            color: var(--ink);
            direction: {{ $isRtl ? 'rtl' : 'ltr' }};
            text-align: {{ $textAlign }};
            min-height: 100vh;
        }

        .page {
            width: min(960px, 100%);
            margin: 0 auto;
            padding: 20px 14px 32px;
        }

        .hero {
            background: linear-gradient(135deg, #0f2745 0%, #1b5faa 100%);
            color: #fff;
            border-radius: 24px;
            padding: 22px 18px;
            box-shadow: var(--shadow);
            overflow: hidden;
            position: relative;
        }

        .hero::after {
            content: "";
            position: absolute;
            inset: auto -40px -40px auto;
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 12px;
            margin-bottom: 14px;
        }

        .hero h1 {
            margin: 0;
            font-size: 28px;
            line-height: 1.3;
        }

        .hero p {
            margin: 10px 0 0;
            color: rgba(255, 255, 255, 0.82);
            font-size: 14px;
            line-height: 1.8;
            max-width: 640px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 14px;
            margin-top: 16px;
        }

        .card {
            grid-column: span 12;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 22px;
            box-shadow: var(--shadow);
            padding: 18px;
        }

        .card h2 {
            margin: 0 0 14px;
            font-size: 18px;
            color: var(--ink);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .stat {
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 14px;
        }

        .stat .label {
            color: var(--muted);
            font-size: 12px;
            margin-bottom: 6px;
        }

        .stat .value {
            font-weight: 700;
            font-size: 16px;
            color: var(--ink);
            word-break: break-word;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .summary .box {
            background: var(--primary-soft);
            border: 1px solid #cfe1ff;
            border-radius: 18px;
            padding: 14px;
        }

        .summary .box.green {
            background: var(--accent-soft);
            border-color: #c5f0d9;
        }

        .summary .title {
            color: var(--muted);
            font-size: 12px;
            margin-bottom: 6px;
        }

        .summary .text {
            font-size: 15px;
            font-weight: 700;
        }

        .content-wrap {
            background: #fbfcfe;
            border: 1px dashed #cbd8e6;
            border-radius: 18px;
            padding: 16px;
            line-height: 1.9;
            color: #25374d;
            overflow-wrap: anywhere;
        }

        .content-wrap p:first-child { margin-top: 0; }
        .content-wrap p:last-child { margin-bottom: 0; }

        .note {
            margin-top: 14px;
            background: #fff8e8;
            border: 1px solid #f2dfab;
            color: #7a5c13;
            border-radius: 16px;
            padding: 14px;
            line-height: 1.8;
        }

        .tasks-list {
            display: grid;
            gap: 12px;
        }

        .task {
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 14px;
            background: linear-gradient(180deg, #fff 0%, #f9fbfd 100%);
        }

        .task-top {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .task-index {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--primary);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            flex-shrink: 0;
        }

        .task-name {
            font-size: 15px;
            font-weight: 700;
            color: var(--ink);
        }

        .task-notes {
            color: #42556d;
            line-height: 1.85;
            font-size: 14px;
            white-space: pre-wrap;
        }

        .empty {
            border: 1px dashed var(--line);
            border-radius: 18px;
            padding: 22px 16px;
            text-align: center;
            color: var(--muted);
            background: #fafcff;
        }

        @media (min-width: 760px) {
            .card.span-7 { grid-column: span 7; }
            .card.span-5 { grid-column: span 5; }
        }

        @media (max-width: 640px) {
            .page { padding-inline: 12px; }
            .hero h1 { font-size: 22px; }
            .summary,
            .stats { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="page">
        <section class="hero">
            <div class="badge">{{ $planTypeLabel }}</div>
            <h1>{{ $planTitle }}</h1>
            <p>{{ trans('sw.training_plan') }} {{ trans('sw.description') }} {{ $memberName }}</p>
        </section>

        <section class="grid">
            <div class="card span-5">
                <h2>{{ trans('sw.plan_title') }}</h2>
                <div class="summary">
                    <div class="box green">
                        <div class="title">{{ trans('sw.plan_title') }}</div>
                        <div class="text">{{ $planTitle }}</div>
                    </div>
                    <div class="box">
                        <div class="title">{{ trans('sw.type') }}</div>
                        <div class="text">{{ $planTypeLabel }}</div>
                    </div>
                </div>
            </div>

            <div class="card span-7">
                <h2>{{ trans('sw.member') }}</h2>
                <div class="stats">
                    <div class="stat">
                        <div class="label">{{ trans('sw.name') }}</div>
                        <div class="value">{{ $memberName }}</div>
                    </div>
                    <div class="stat">
                        <div class="label">{{ trans('sw.code') }}</div>
                        <div class="value">{{ $memberCode }}</div>
                    </div>
                    @if($fromDate)
                        <div class="stat">
                            <div class="label">{{ trans('sw.from') }}</div>
                            <div class="value">{{ $fromDate }}</div>
                        </div>
                    @endif
                    @if($toDate)
                        <div class="stat">
                            <div class="label">{{ trans('sw.to') }}</div>
                            <div class="value">{{ $toDate }}</div>
                        </div>
                    @endif
                    @if(!empty($assignmentWeight))
                        <div class="stat">
                            <div class="label">{{ trans('sw.weight') }}</div>
                            <div class="value">{{ $assignmentWeight }}</div>
                        </div>
                    @endif
                    @if(!empty($assignmentHeight))
                        <div class="stat">
                            <div class="label">{{ trans('sw.height') }}</div>
                            <div class="value">{{ $assignmentHeight }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card span-12">
                <h2>{{ trans('sw.description') }}</h2>
                <div class="content-wrap">
                    @if($detailsHtml !== '')
                        @if($hasHtmlDetails)
                            {!! $detailsHtml !!}
                        @else
                            {!! nl2br(e($detailsText)) !!}
                        @endif
                    @else
                        <div class="empty">{{ trans('sw.no_data') }}</div>
                    @endif
                </div>

                @if($notes !== '')
                    <div class="note">
                        <strong>{{ trans('sw.notes') }}:</strong>
                        <div>{!! nl2br(e($notes)) !!}</div>
                    </div>
                @endif
            </div>

            <div class="card span-12">
                <h2>{{ trans('sw.tasks') }}</h2>

                @if($tasks->isEmpty())
                    <div class="empty">{{ trans('sw.no_data') }}</div>
                @else
                    <div class="tasks-list">
                        @foreach($tasks as $index => $task)
                            <article class="task">
                                <div class="task-top">
                                    <span class="task-index">{{ $index + 1 }}</span>
                                    <div class="task-name">{{ $task['title'] !== '' ? $task['title'] : trans('sw.task') . ' ' . ($index + 1) }}</div>
                                </div>

                                @if($task['notes'] !== '')
                                    <div class="task-notes">{!! nl2br(e($task['notes'])) !!}</div>
                                @else
                                    <div class="task-notes">{{ trans('sw.no_data') }}</div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </div>
</body>
</html>
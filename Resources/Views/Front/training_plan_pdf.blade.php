<!DOCTYPE html>
<html lang="{{ $lang ?? 'ar' }}" dir="{{ $lang == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ trans('sw.training_plan') }} - {{ $plan->title ?? 'Plan' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @php
            $fontFamily = $lang == 'ar' ? "'DejaVu Sans', 'Arial Unicode MS', 'Tahoma', Arial, sans-serif" : "'DejaVu Sans', Arial, sans-serif";
            $textAlign = $lang == 'ar' ? 'right' : 'left';
            $textDirection = $lang == 'ar' ? 'rtl' : 'ltr';
        @endphp
        
        body {
            font-family: {!! $fontFamily !!};
            font-size: 12px;
            line-height: 1.8;
            color: #333;
            direction: {!! $textDirection !!};
            text-align: {!! $textAlign !!};
        }
        
        @if($lang == 'ar')
        body {
            direction: rtl;
            text-align: right;
        }
        
        body * {
            direction: inherit;
            text-align: inherit;
        }
        
        .header, .header h1, .header .subtitle {
            text-align: center;
        }
        
        .arabic-text, h1, h2, h3, h4, h5, h6, .plan-title, .task-name, .task-description, .plan-content {
            font-family: 'DejaVu Sans', 'Arial Unicode MS', 'Tahoma', Arial, sans-serif;
        }
        @endif
        
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 3px solid #009ef7;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #009ef7;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .header .subtitle {
            color: #666;
            font-size: 14px;
        }
        
        .info-section {
            margin-bottom: 30px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .info-label {
            display: table-cell;
            width: 40%;
            font-weight: bold;
            color: #666;
            text-align: {!! $textAlign !!};
            padding-{!! $lang == 'ar' ? 'left' : 'right' !!}: 10px;
            direction: {!! $textDirection !!};
        }
        
        .info-value {
            display: table-cell;
            width: 60%;
            color: #333;
            text-align: {!! $textAlign !!};
            padding-{!! $lang == 'ar' ? 'right' : 'left' !!}: 10px;
            direction: {!! $textDirection !!};
        }
        
        .plan-details {
            margin-bottom: 30px;
            padding: 20px;
            background: #fff;
            border: 2px solid #50CD89;
            border-radius: 5px;
        }
        
        .plan-title {
            font-size: 20px;
            font-weight: bold;
            color: #50CD89;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .plan-type {
            text-align: center;
            color: #666;
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .plan-dates {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .plan-date {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 10px;
        }
        
        .date-label {
            font-weight: bold;
            color: #666;
            margin-bottom: 5px;
        }
        
        .date-value {
            color: #333;
            font-size: 14px;
        }
        
        .plan-content {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            white-space: pre-wrap;
            text-align: {!! $textAlign !!};
            direction: {!! $textDirection !!};
            word-wrap: break-word;
        }
        
        .tasks-section {
            margin-top: 30px;
        }
        
        .tasks-title {
            font-size: 18px;
            font-weight: bold;
            color: #009ef7;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #009ef7;
        }
        
        .task-item {
            margin-bottom: 20px;
            padding: 15px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            page-break-inside: avoid;
        }
        
        .task-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            flex-direction: {!! $lang == 'ar' ? 'row-reverse' : 'row' !!};
        }
        
        .task-number {
            background: #009ef7;
            color: #fff;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-{!! $lang == 'ar' ? 'right' : 'left' !!}: 10px;
            flex-shrink: 0;
        }
        
        .task-name {
            font-weight: bold;
            font-size: 14px;
            color: #333;
            flex: 1;
            text-align: {!! $textAlign !!};
            direction: {!! $textDirection !!};
        }
        
        .task-description {
            margin: 10px 0;
            color: #666;
            font-size: 11px;
            text-align: {!! $textAlign !!};
            direction: {!! $textDirection !!};
        }
        
        .task-details {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #ddd;
        }
        
        .detail-badge {
            display: inline-block;
            padding: 4px 8px;
            margin: 3px;
            background: #f0f0f0;
            border-radius: 3px;
            font-size: 10px;
            color: #333;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
        
        .member-info {
            background: #e8f4f8;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        @media print {
            .task-item {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 @if($lang == 'ar') dir="rtl" @endif>{{ trans('sw.training_plan') }}</h1>
        <div class="subtitle" @if($lang == 'ar') dir="rtl" @endif>{{ trans('sw.plan_details') }}</div>
    </div>

    <!-- Member Information -->
    <div class="member-info" @if($lang == 'ar') dir="rtl" @endif>
        <div class="info-row">
            <div class="info-label">{{ trans('sw.member_name') }}:</div>
            <div class="info-value" @if($lang == 'ar') dir="rtl" @endif>{{ $member->name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">{{ trans('sw.member_code') }}:</div>
            <div class="info-value">{{ $member->code }}</div>
        </div>
        @if(isset($plan->assignment_weight) && $plan->assignment_weight > 0)
        <div class="info-row">
            <div class="info-label">{{ trans('sw.weight_at_assignment') }}:</div>
            <div class="info-value">{{ $plan->assignment_weight }} kg</div>
        </div>
        @endif
        @if(isset($plan->assignment_height) && $plan->assignment_height > 0)
        <div class="info-row">
            <div class="info-label">{{ trans('sw.height_at_assignment') }}:</div>
            <div class="info-value">{{ $plan->assignment_height }} cm</div>
        </div>
        @endif
        @if(isset($plan->from_date) && $plan->from_date)
        <div class="info-row">
            <div class="info-label">{{ trans('sw.plan_start_date') }}:</div>
            <div class="info-value">{{ \Carbon\Carbon::parse($plan->from_date)->format('Y-m-d') }}</div>
        </div>
        @endif
        @if(isset($plan->to_date) && $plan->to_date)
        <div class="info-row">
            <div class="info-label">{{ trans('sw.plan_end_date') }}:</div>
            <div class="info-value">{{ \Carbon\Carbon::parse($plan->to_date)->format('Y-m-d') }}</div>
        </div>
        @endif
    </div>

    <!-- Plan Details -->
    <div class="plan-details" @if($lang == 'ar') dir="rtl" @endif>
        <div class="plan-title" @if($lang == 'ar') dir="rtl" @endif>{{ $plan->title ?? ($plan->plan_title ?? 'N/A') }}</div>
        <div class="plan-type" @if($lang == 'ar') dir="rtl" @endif>
            {{ ($plan->type ?? 1) == 1 ? trans('sw.training_plan') : trans('sw.diet_plan') }}
        </div>
        
        @if(isset($plan->content) && $plan->content)
        <div class="plan-content" @if($lang == 'ar') dir="rtl" @endif>
            @php
                $contentJson = null;
                $contentText = $plan->content;
                try {
                    $decoded = json_decode($plan->content, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $contentJson = $decoded;
                    }
                } catch (\Exception $e) {
                    // Not JSON, use as text
                }
            @endphp
            
            @if($contentJson)
                @if(isset($contentJson['summary']))
                    <div @if($lang == 'ar') dir="rtl" @endif>{{ $contentJson['summary'] }}</div>
                @elseif(isset($contentJson['description']))
                    <div @if($lang == 'ar') dir="rtl" @endif>{{ $contentJson['description'] }}</div>
                @endif
                
                @if(isset($contentJson['notes']) && $contentJson['notes'])
                    <br><br><strong>{{ trans('sw.notes') }}:</strong><br>
                    <div @if($lang == 'ar') dir="rtl" @endif>{{ $contentJson['notes'] }}</div>
                @endif
            @else
                <div @if($lang == 'ar') dir="rtl" @endif>{{ $contentText }}</div>
            @endif
        </div>
        @endif
    </div>

    <!-- Tasks Section -->
    @php
        $planTasks = collect([]);
        if (isset($plan->tasks)) {
            if (is_countable($plan->tasks)) {
                $planTasks = is_a($plan->tasks, 'Illuminate\Support\Collection') 
                    ? $plan->tasks 
                    : collect($plan->tasks);
            }
        }
    @endphp
    
    @if($planTasks && $planTasks->count() > 0)
    <div class="tasks-section">
        <div class="tasks-title">
            {{ trans('sw.plan_tasks') }} ({{ $planTasks->count() }})
        </div>
        
        @foreach($planTasks as $index => $task)
        <div class="task-item" @if($lang == 'ar') dir="rtl" @endif>
            <div class="task-header">
                <div class="task-number">{{ $index + 1 }}</div>
                <div class="task-name" @if($lang == 'ar') dir="rtl" @endif>
                    {{ $task->{'name_'.($lang ?? 'ar')} ?? $task->name_ar ?? $task->name_en ?? 'Task ' . ($index + 1) }}
                </div>
            </div>
            
            @if(isset($task->details) && $task->details)
            <div class="task-description" @if($lang == 'ar') dir="rtl" @endif>{{ $task->details }}</div>
            @endif
            
            @if(($plan->type ?? 1) == 1)
                {{-- Training Details --}}
                @if(isset($task->t_group) || isset($task->t_repeats) || isset($task->t_rest) || isset($task->youtube_link))
                <div class="task-details">
                    @if(isset($task->t_group) && $task->t_group)
                    <span class="detail-badge">{{ trans('sw.sets') }}: {{ $task->t_group }}</span>
                    @endif
                    @if(isset($task->t_repeats) && $task->t_repeats)
                    <span class="detail-badge">{{ trans('sw.reps') }}: {{ $task->t_repeats }}</span>
                    @endif
                    @if(isset($task->t_rest) && $task->t_rest)
                    <span class="detail-badge">{{ trans('sw.rest_time') }}: {{ $task->t_rest }}</span>
                    @endif
                    @if(isset($task->youtube_link) && $task->youtube_link)
                    <span class="detail-badge">{{ trans('sw.video_link') }}: {{ $task->youtube_link }}</span>
                    @endif
                </div>
                @endif
            @else
                {{-- Diet/Nutrition Details --}}
                @if(isset($task->d_calories) || isset($task->d_protein) || isset($task->d_carb) || isset($task->d_fats))
                <div class="task-details">
                    @if(isset($task->d_calories) && $task->d_calories)
                    <span class="detail-badge">{{ $task->d_calories }} {{ trans('sw.calories') }}</span>
                    @endif
                    @if(isset($task->d_protein) && $task->d_protein)
                    <span class="detail-badge">P: {{ $task->d_protein }}g</span>
                    @endif
                    @if(isset($task->d_carb) && $task->d_carb)
                    <span class="detail-badge">C: {{ $task->d_carb }}g</span>
                    @endif
                    @if(isset($task->d_fats) && $task->d_fats)
                    <span class="detail-badge">F: {{ $task->d_fats }}g</span>
                    @endif
                </div>
                @endif
            @endif
        </div>
        @endforeach
    </div>
    @endif

    @if(isset($plan->assignment_notes) && $plan->assignment_notes)
    <div class="info-section" @if($lang == 'ar') dir="rtl" @endif>
        <div class="info-label">{{ trans('sw.assignment_notes') }}:</div>
        <div class="info-value" @if($lang == 'ar') dir="rtl" @endif>{{ $plan->assignment_notes }}</div>
    </div>
    @endif

    <div class="footer">
        <div>{{ trans('sw.generated_on') }}: {{ date('Y-m-d H:i:s') }}</div>
        <div>{{ trans('sw.member') }}: {{ $member->name }} ({{ $member->code }})</div>
    </div>
</body>
</html>




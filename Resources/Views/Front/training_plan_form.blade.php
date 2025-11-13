@extends('software::layouts.list')
@section('list_title') {{ @$title }} @endsection
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
            <a href="{{ route('sw.listTrainingPlan') }}" class="text-muted text-hover-primary">{{ trans('sw.training_plans') }}</a>
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

<form action="{{ $plan->id ? route('sw.updateTrainingPlan', $plan->id) : route('sw.storeTrainingPlan') }}" method="POST" id="plan_form">
    @csrf

    <div class="row g-7">
        <!--begin::Left Column - Instructions-->
        <div class="col-lg-4 col-xl-3">
            <div class="card card-flush mb-5 sticky-top" style="top: 100px;">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="fw-bold m-0">{{ trans('sw.instructions') }}</h3>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="d-flex align-items-start mb-7">
                        <div class="symbol symbol-40px me-5">
                            <span class="symbol-label bg-light-primary">
                                <i class="ki-outline ki-information-5 fs-1 text-primary"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold text-gray-800 mb-1">{{ trans('sw.plan_creation_guide') }}</div>
                            <div class="text-muted fs-7">{{ trans('sw.plan_creation_guide_desc') }}</div>
                        </div>
                    </div>

                    <div class="separator separator-dashed my-5"></div>

                    <div class="d-flex align-items-start mb-7">
                        <div class="symbol symbol-40px me-5">
                            <span class="symbol-label bg-light-success">
                                <i class="la la-dumbbell fs-1 text-success"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold text-gray-800 mb-1">{{ trans('sw.training_exercises') }}</div>
                            <div class="text-muted fs-7">{{ trans('sw.training_exercises_desc') }}</div>
                        </div>
                    </div>

                    <div class="separator separator-dashed my-5"></div>

                    <div class="d-flex align-items-start">
                        <div class="symbol symbol-40px me-5">
                            <span class="symbol-label bg-light-warning">
                                <i class="ki-outline ki-apple fs-1 text-warning"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold text-gray-800 mb-1">{{ trans('sw.diet_meals') }}</div>
                            <div class="text-muted fs-7">{{ trans('sw.diet_meals_desc') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!--begin::Actions-->
            <div class="card card-flush">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="ki-outline ki-check fs-2"></i>
                        {{ $plan->id ? trans('sw.update_plan') : trans('sw.save_plan') }}
                    </button>
                    <a href="{{ route('sw.listTrainingPlan') }}" class="btn btn-light w-100">
                        <i class="ki-outline ki-cross fs-2"></i>
                        {{ trans('sw.cancel') }}
                    </a>
                </div>
            </div>
            <!--end::Actions-->
        </div>
        <!--end::Left Column-->

        <!--begin::Right Column - Form-->
        <div class="col-lg-8 col-xl-9">
            <!--begin::Plan Basic Info-->
            <div class="card card-flush mb-7">
                <div class="card-header">
                    <div class="card-title">
                        <h2 class="fw-bold">{{ trans('sw.plan_information') }}</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="row g-5">
                        <div class="col-md-8">
                            <label class="form-label required">{{ trans('sw.plan_title') }}</label>
                            <input type="text" name="title" value="{{ old('title', $plan->title) }}" 
                                   class="form-control form-control-lg @error('title') is-invalid @enderror" 
                                   placeholder="{{ trans('sw.enter_plan_title') }}" required />
                            @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label required">{{ trans('sw.plan_type') }}</label>
                            <select name="type" id="plan_type" class="form-select form-select-lg @error('type') is-invalid @enderror" required>
                                <option value="1" {{ old('type', $plan->type) == 1 ? 'selected' : '' }}>
                                    {{ trans('sw.training_plan') }}
                                </option>
                                <option value="2" {{ old('type', $plan->type) == 2 ? 'selected' : '' }}>
                                    {{ trans('sw.diet_plan') }}
                                </option>
                            </select>
                            @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label required">{{ trans('sw.plan_description') }}</label>
                            <textarea name="content" class="form-control @error('content') is-invalid @enderror" 
                                      rows="4" placeholder="{{ trans('sw.enter_plan_description') }}" required>{{ old('content', $plan->content) }}</textarea>
                            @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if(count($categories) > 0)
                        <div class="col-12">
                            <label class="form-label">{{ trans('sw.subscription_category') }}</label>
                            <select name="subscription_category_id" class="form-select">
                                <option value="">-- {{ trans('sw.choose') }} --</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('subscription_category_id', $plan->subscription_category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <!--end::Plan Basic Info-->

            <!--begin::Tasks Section-->
            <div class="card card-flush">
                <div class="card-header">
                    <div class="card-title">
                        <h2 class="fw-bold">
                            <span id="tasks_title_training">{{ trans('sw.plan_exercises') }}</span>
                            <span id="tasks_title_diet" style="display: none;">{{ trans('sw.plan_meals') }}</span>
                        </h2>
                    </div>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-sm btn-light-primary" onclick="addTask()">
                            <i class="ki-outline ki-plus fs-2"></i> {{ trans('sw.add_task') }}
                        </button>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div id="tasks_container">
                        @if(isset($tasks) && count($tasks) > 0)
                            @foreach($tasks as $index => $task)
                                @include('software::Front.training_plan_task_row', ['task' => $task, 'index' => $index])
                            @endforeach
                        @else
                            <div class="text-center py-10 text-muted" id="empty_tasks_message">
                                <i class="ki-outline ki-information fs-3x mb-3"></i>
                                <div>{{ trans('sw.no_tasks_added_yet') }}</div>
                                <div class="fs-7">{{ trans('sw.click_add_task_to_start') }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <!--end::Tasks Section-->
        </div>
        <!--end::Right Column-->
    </div>
</form>

@endsection

@section('scripts')
@parent
<script>
let taskIndex = {{ isset($tasks) ? count($tasks) : 0 }};

// Show/hide task fields based on plan type
function updateTaskFields() {
    const planType = document.getElementById('plan_type').value;
    const isTraining = planType == '1';
    
    // Update titles
    document.getElementById('tasks_title_training').style.display = isTraining ? '' : 'none';
    document.getElementById('tasks_title_diet').style.display = isTraining ? 'none' : '';
    
    // Update all task rows
    document.querySelectorAll('.task-row').forEach(row => {
        row.querySelectorAll('.training-only').forEach(el => el.style.display = isTraining ? '' : 'none');
        row.querySelectorAll('.diet-only').forEach(el => el.style.display = isTraining ? 'none' : '');
    });
}

// Add new task row
function addTask() {
    document.getElementById('empty_tasks_message')?.remove();
    
    const planType = document.getElementById('plan_type').value;
    const isTraining = planType == '1';
    
    const taskHtml = `
        <div class="card card-flush mb-5 task-row" id="task_${taskIndex}">
            <div class="card-header min-h-50px">
                <div class="card-title">
                    <h3 class="fw-bold">${isTraining ? '{{ trans('sw.exercise') }}' : '{{ trans('sw.meal') }}'} #<span class="task-number">${taskIndex + 1}</span></h3>
                </div>
                <div class="card-toolbar">
                    <button type="button" class="btn btn-sm btn-icon btn-light-danger" onclick="removeTask(${taskIndex})">
                        <i class="ki-outline ki-trash fs-2"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-5">
                    <div class="col-md-6">
                        <label class="form-label">{{ trans('sw.day_name') }}</label>
                        <input type="text" name="tasks[${taskIndex}][day_name]" class="form-control" placeholder="{{ trans('sw.day_1') }}" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ trans('sw.order') }}</label>
                        <input type="number" name="tasks[${taskIndex}][order]" class="form-control" value="${taskIndex}" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">{{ trans('sw.name_ar') }}</label>
                        <input type="text" name="tasks[${taskIndex}][name_ar]" class="form-control" placeholder="{{ trans('sw.enter_name_ar') }}" required />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">{{ trans('sw.name_en') }}</label>
                        <input type="text" name="tasks[${taskIndex}][name_en]" class="form-control" placeholder="{{ trans('sw.enter_name_en') }}" required />
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ trans('sw.description') }}</label>
                        <textarea name="tasks[${taskIndex}][description]" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <!-- Training Fields -->
                    <div class="col-md-6 training-only" style="display: ${isTraining ? '' : 'none'}">
                        <label class="form-label">{{ trans('sw.youtube_link') }}</label>
                        <input type="url" name="tasks[${taskIndex}][youtube_link]" class="form-control" placeholder="https://youtube.com/..." />
                    </div>
                    <div class="col-md-2 training-only" style="display: ${isTraining ? '' : 'none'}">
                        <label class="form-label">{{ trans('sw.groups') }}</label>
                        <input type="number" name="tasks[${taskIndex}][t_group]" class="form-control" placeholder="3" />
                    </div>
                    <div class="col-md-2 training-only" style="display: ${isTraining ? '' : 'none'}">
                        <label class="form-label">{{ trans('sw.repeats') }}</label>
                        <input type="number" name="tasks[${taskIndex}][t_repeats]" class="form-control" placeholder="12" />
                    </div>
                    <div class="col-md-2 training-only" style="display: ${isTraining ? '' : 'none'}">
                        <label class="form-label">{{ trans('sw.rest_time') }}</label>
                        <input type="text" name="tasks[${taskIndex}][t_rest]" class="form-control" placeholder="60s" />
                    </div>
                    
                    <!-- Diet Fields -->
                    <div class="col-md-3 diet-only" style="display: ${isTraining ? 'none' : ''}">
                        <label class="form-label">{{ trans('sw.calories') }}</label>
                        <input type="text" name="tasks[${taskIndex}][d_calories]" class="form-control" placeholder="500" />
                    </div>
                    <div class="col-md-3 diet-only" style="display: ${isTraining ? 'none' : ''}">
                        <label class="form-label">{{ trans('sw.protein') }}</label>
                        <input type="text" name="tasks[${taskIndex}][d_protein]" class="form-control" placeholder="30g" />
                    </div>
                    <div class="col-md-3 diet-only" style="display: ${isTraining ? 'none' : ''}">
                        <label class="form-label">{{ trans('sw.carbs') }}</label>
                        <input type="text" name="tasks[${taskIndex}][d_carb]" class="form-control" placeholder="50g" />
                    </div>
                    <div class="col-md-3 diet-only" style="display: ${isTraining ? 'none' : ''}">
                        <label class="form-label">{{ trans('sw.fats') }}</label>
                        <input type="text" name="tasks[${taskIndex}][d_fats]" class="form-control" placeholder="10g" />
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label">{{ trans('sw.additional_details') }}</label>
                        <textarea name="tasks[${taskIndex}][details]" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="tasks[${taskIndex}][status]" value="1" checked />
                            <label class="form-check-label">{{ trans('sw.active') }}</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('tasks_container').insertAdjacentHTML('beforeend', taskHtml);
    taskIndex++;
    updateTaskNumbers();
}

// Remove task
function removeTask(index) {
    if (confirm('{{ trans('sw.delete_task_confirm') }}')) {
        document.getElementById('task_' + index).remove();
        updateTaskNumbers();
        
        // Show empty message if no tasks
        if (document.querySelectorAll('.task-row').length === 0) {
            document.getElementById('tasks_container').innerHTML = `
                <div class="text-center py-10 text-muted" id="empty_tasks_message">
                    <i class="ki-outline ki-information fs-3x mb-3"></i>
                    <div>{{ trans('sw.no_tasks_added_yet') }}</div>
                    <div class="fs-7">{{ trans('sw.click_add_task_to_start') }}</div>
                </div>
            `;
        }
    }
}

// Update task numbers
function updateTaskNumbers() {
    document.querySelectorAll('.task-row').forEach((row, index) => {
        row.querySelector('.task-number').textContent = index + 1;
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateTaskFields();
    document.getElementById('plan_type').addEventListener('change', updateTaskFields);
});
</script>
@endsection


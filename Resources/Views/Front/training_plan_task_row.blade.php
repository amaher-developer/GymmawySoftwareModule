@php
    $taskIndex = $index;
    $planType = old('type', $plan->type ?? 1);
    $isTraining = (int) $planType === 1;

    $getOld = function (string $key, $default = null) use ($taskIndex, $task) {
        return old("tasks.{$taskIndex}.{$key}", $task->{$key} ?? $default);
    };
@endphp

<div class="card card-flush mb-5 task-row" id="task_{{ $taskIndex }}">
    <div class="card-header min-h-50px">
        <div class="card-title">
            <h3 class="fw-bold">
                {{ $isTraining ? trans('sw.exercise') : trans('sw.meal') }}
                #<span class="task-number">{{ $taskIndex + 1 }}</span>
            </h3>
        </div>
        <div class="card-toolbar">
            <button type="button" class="btn btn-sm btn-icon btn-light-danger" onclick="removeTask({{ $taskIndex }})">
                <i class="ki-outline ki-trash fs-2"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-5">
            <div class="col-md-6">
                <label class="form-label">{{ trans('sw.day_name') }}</label>
                <input type="text"
                       name="tasks[{{ $taskIndex }}][day_name]"
                       class="form-control"
                       value="{{ $getOld('day_name') }}"
                       placeholder="{{ trans('sw.day_1') }}" />
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ trans('sw.order') }}</label>
                <input type="number"
                       name="tasks[{{ $taskIndex }}][order]"
                       class="form-control"
                       value="{{ $getOld('order', $taskIndex) }}" />
            </div>

            <div class="col-md-6">
                <label class="form-label required">{{ trans('sw.name_ar') }}</label>
                <input type="text"
                       name="tasks[{{ $taskIndex }}][name_ar]"
                       class="form-control"
                       value="{{ $getOld('name_ar') }}"
                       placeholder="{{ trans('sw.enter_name_ar') }}"
                       required />
            </div>
            <div class="col-md-6">
                <label class="form-label required">{{ trans('sw.name_en') }}</label>
                <input type="text"
                       name="tasks[{{ $taskIndex }}][name_en]"
                       class="form-control"
                       value="{{ $getOld('name_en') }}"
                       placeholder="{{ trans('sw.enter_name_en') }}"
                       required />
            </div>

            <div class="col-12">
                <label class="form-label">{{ trans('sw.description') }}</label>
                <textarea name="tasks[{{ $taskIndex }}][description]"
                          class="form-control"
                          rows="2">{{ $getOld('description') }}</textarea>
            </div>

            <!-- Image URL - Available for both Training and Diet -->
            <div class="col-md-6">
                <label class="form-label">{{ trans('sw.image_url') ?? 'Image URL' }}</label>
                <input type="url"
                       name="tasks[{{ $taskIndex }}][image_url]"
                       class="form-control"
                       value="{{ $getOld('image_url') }}"
                       placeholder="https://example.com/image.jpg" />
            </div>
            
            <div class="col-md-6 training-only" style="{{ $isTraining ? '' : 'display: none;' }}">
                <label class="form-label">{{ trans('sw.youtube_link') }}</label>
                <input type="url"
                       name="tasks[{{ $taskIndex }}][youtube_link]"
                       class="form-control"
                       value="{{ $getOld('youtube_link') }}"
                       placeholder="https://youtube.com/..." />
            </div>
            <div class="col-md-2 training-only" style="{{ $isTraining ? '' : 'display: none;' }}">
                <label class="form-label">{{ trans('sw.groups') }}</label>
                <input type="number"
                       name="tasks[{{ $taskIndex }}][t_group]"
                       class="form-control"
                       value="{{ $getOld('t_group') }}"
                       placeholder="3" />
            </div>
            <div class="col-md-2 training-only" style="{{ $isTraining ? '' : 'display: none;' }}">
                <label class="form-label">{{ trans('sw.repeats') }}</label>
                <input type="number"
                       name="tasks[{{ $taskIndex }}][t_repeats]"
                       class="form-control"
                       value="{{ $getOld('t_repeats') }}"
                       placeholder="12" />
            </div>
            <div class="col-md-2 training-only" style="{{ $isTraining ? '' : 'display: none;' }}">
                <label class="form-label">{{ trans('sw.rest_time') }}</label>
                <input type="text"
                       name="tasks[{{ $taskIndex }}][t_rest]"
                       class="form-control"
                       value="{{ $getOld('t_rest') }}"
                       placeholder="60s" />
            </div>

            <div class="col-md-3 diet-only" style="{{ $isTraining ? 'display: none;' : '' }}">
                <label class="form-label">{{ trans('sw.calories') }}</label>
                <input type="text"
                       name="tasks[{{ $taskIndex }}][d_calories]"
                       class="form-control"
                       value="{{ $getOld('d_calories') }}"
                       placeholder="500" />
            </div>
            <div class="col-md-3 diet-only" style="{{ $isTraining ? 'display: none;' : '' }}">
                <label class="form-label">{{ trans('sw.protein') }}</label>
                <input type="text"
                       name="tasks[{{ $taskIndex }}][d_protein]"
                       class="form-control"
                       value="{{ $getOld('d_protein') }}"
                       placeholder="30g" />
            </div>
            <div class="col-md-3 diet-only" style="{{ $isTraining ? 'display: none;' : '' }}">
                <label class="form-label">{{ trans('sw.carb') }}</label>
                <input type="text"
                       name="tasks[{{ $taskIndex }}][d_carb]"
                       class="form-control"
                       value="{{ $getOld('d_carb') }}"
                       placeholder="50g" />
            </div>
            <div class="col-md-3 diet-only" style="{{ $isTraining ? 'display: none;' : '' }}">
                <label class="form-label">{{ trans('sw.fats') }}</label>
                <input type="text"
                       name="tasks[{{ $taskIndex }}][d_fats]"
                       class="form-control"
                       value="{{ $getOld('d_fats') }}"
                       placeholder="10g" />
            </div>
            
            <!-- YouTube Link for Diet Plans -->
            <div class="col-md-6 diet-only" style="{{ $isTraining ? 'display: none;' : '' }}">
                <label class="form-label">{{ trans('sw.youtube_link') }}</label>
                <input type="url"
                       name="tasks[{{ $taskIndex }}][youtube_link]"
                       class="form-control"
                       value="{{ $getOld('youtube_link') }}"
                       placeholder="https://youtube.com/..." />
            </div>

            <div class="col-12">
                <label class="form-label">{{ trans('sw.details') }}</label>
                <textarea name="tasks[{{ $taskIndex }}][details]"
                          class="form-control"
                          rows="2">{{ $getOld('details') }}</textarea>
            </div>

            <div class="col-12">
                <div class="form-check form-switch form-switch-sm form-check-custom form-check-solid">
                    <input class="form-check-input"
                           type="checkbox"
                           name="tasks[{{ $taskIndex }}][status]"
                           value="1"
                           {{ $getOld('status', $task->status ?? 1) ? 'checked' : '' }} />
                    <label class="form-check-label">
                        {{ trans('sw.active') }}
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>




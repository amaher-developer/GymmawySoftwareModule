@php
    $atId = $at->id ?? '';
    $atTrainerId = $at->trainer_id ?? '';
    $atLimit = $at->reservation_limit ?? '';
    $atActive = $at ? (bool) $at->is_active : true;
    $atWorkDays = (is_array($at->schedule['work_days'] ?? null)) ? $at->schedule['work_days'] : [];
    foreach ($atWorkDays as $k => $v) { if (!is_array($v)) $atWorkDays[$k] = []; }
@endphp
<div class="activity-trainer-row border rounded p-4 mb-4" data-index="{{ $index }}">
    <input type="hidden" name="activity_trainers[{{ $index }}][id]" value="{{ $atId }}" class="at-id-input">
    <input type="hidden" name="activity_trainers[{{ $index }}][_delete]" value="0" class="at-delete-input">

    <div class="row g-4 align-items-end mb-4">
        <div class="col-md-4">
            <label class="form-label">{{ trans('sw.trainer') }}</label>
            <select name="activity_trainers[{{ $index }}][trainer_id]" class="form-select at-trainer-select">
                <option value="">-- {{ trans('sw.select_trainer') }} --</option>
                @foreach($trainers ?? [] as $trainer)
                    <option value="{{ $trainer->id }}" @if($atTrainerId == $trainer->id) selected @endif>{{ $trainer->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">{{ trans('sw.reservation_limit') }}</label>
            <input type="number" min="0" class="form-control"
                   name="activity_trainers[{{ $index }}][reservation_limit]" value="{{ $atLimit }}"
                   placeholder="{{ trans('sw.activity_limit_placeholder') }}">
        </div>
        <div class="col-md-3">
            <div class="form-check form-check-custom form-check-solid">
                <input type="checkbox" class="form-check-input" value="1"
                       name="activity_trainers[{{ $index }}][is_active]" @if($atActive) checked @endif>
                <label class="form-check-label">{{ trans('sw.active') }}</label>
            </div>
        </div>
        <div class="col-md-2 text-end">
            <button type="button" class="btn btn-sm btn-light-danger remove-activity-trainer-btn">
                <i class="fa fa-trash"></i> {{ trans('admin.delete') }}
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm table-striped mb-0">
            <thead>
                <tr>
                    <th style="width: 10%;">{{ trans('sw.status') }}</th>
                    <th style="width: 20%;">{{ trans('sw.day') }}</th>
                    <th style="width: 35%;">{{ trans('sw.time_from') }}</th>
                    <th style="width: 35%;">{{ trans('sw.time_to') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($weekDays as $day)
                    @php $dayData = $atWorkDays[$day['index']] ?? []; @endphp
                    <tr class="at-day-row @if(!($dayData['status'] ?? false)) disabled-row @endif">
                        <td class="text-center">
                            <input type="checkbox" value="1" class="form-check-input at-day-checkbox"
                                   name="activity_trainers[{{ $index }}][schedule][work_days][{{ $day['index'] }}][status]"
                                   @if($dayData['status'] ?? false) checked @endif>
                        </td>
                        <td><strong>{{ trans('sw.' . $day['trans']) }}</strong></td>
                        <td>
                            <input type="time" class="form-control at-time-input"
                                   name="activity_trainers[{{ $index }}][schedule][work_days][{{ $day['index'] }}][start]"
                                   value="{{ $dayData['start'] ?? '' }}"
                                   @if(!($dayData['status'] ?? false)) disabled @endif>
                        </td>
                        <td>
                            <input type="time" class="form-control at-time-input"
                                   name="activity_trainers[{{ $index }}][schedule][work_days][{{ $day['index'] }}][end]"
                                   value="{{ $dayData['end'] ?? '' }}"
                                   @if(!($dayData['status'] ?? false)) disabled @endif>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

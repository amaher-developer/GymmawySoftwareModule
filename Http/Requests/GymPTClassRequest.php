<?php

namespace Modules\Software\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GymPTClassRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'pt_subscription_id' => 'required|integer|exists:sw_gym_pt_subscriptions,id',
            'name_ar' => 'required|string|max:80',
            'name_en' => 'required|string|max:80',
            'content_ar' => 'nullable|string|max:250',
            'content_en' => 'nullable|string|max:250',
            'price' => 'required|numeric|min:0',
            'total_sessions' => 'required|integer|min:1',
            'max_members' => 'nullable|integer|min:1',
            'class_type' => 'required|in:private,group,mixed',
            'pricing_type' => 'required|in:per_member,per_group',
            'is_active' => 'nullable|boolean',
            'is_system' => 'nullable|boolean',
            'is_mobile' => 'nullable|boolean',
            'is_web' => 'nullable|boolean',
            'class_color' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:255',
            'schedule' => 'nullable',
            'class_trainers' => 'nullable|array',
            'class_trainers.*.id' => 'nullable|integer|exists:sw_gym_pt_class_trainers,id',
            'class_trainers.*.trainer_id' => 'nullable|integer|exists:sw_gym_pt_trainers,id',
            'class_trainers.*.session_count' => 'nullable|integer|min:0',
            'class_trainers.*.commission_rate' => 'nullable|numeric|min:0|max:100',
            'class_trainers.*.session_type' => 'nullable|string|max:50',
            'class_trainers.*.is_active' => 'nullable|boolean',
            'class_trainers.*.schedule' => 'nullable',
            'class_trainers.*.date_from' => 'nullable|date',
            'class_trainers.*.date_to' => 'nullable|date',
        ];
    }

    /**
     * Prepare data for validation
     */
    protected function prepareForValidation()
    {
        $schedule = $this->input('schedule');
        if ($schedule) {
            $scheduleData = is_string($schedule) ? json_decode($schedule, true) : $schedule;
            if (is_array($scheduleData) && isset($scheduleData['work_days'])) {
                $this->sanitizeScheduleTimes($scheduleData);
                $this->merge(['schedule' => $scheduleData]);
            }
        }

        $classTrainers = $this->input('class_trainers', []);
        if (is_array($classTrainers)) {
            foreach ($classTrainers as $index => $trainer) {
                if (isset($trainer['schedule'])) {
                    $trainerSchedule = is_string($trainer['schedule'])
                        ? json_decode($trainer['schedule'], true)
                        : $trainer['schedule'];

                    if (is_array($trainerSchedule) && isset($trainerSchedule['work_days'])) {
                        $this->sanitizeScheduleTimes($trainerSchedule);
                        $classTrainers[$index]['schedule'] = $trainerSchedule;
                    }
                }
            }
            $this->merge(['class_trainers' => $classTrainers]);
        }
    }

    /**
     * Sanitize time strings in schedule data
     */
    protected function sanitizeScheduleTimes(array &$scheduleData): void
    {
        if (!isset($scheduleData['work_days']) || !is_array($scheduleData['work_days'])) {
            return;
        }

        foreach ($scheduleData['work_days'] as $day => &$daySchedule) {
            if (!is_array($daySchedule)) {
                continue;
            }

            if (isset($daySchedule['start']) && $daySchedule['start']) {
                $daySchedule['start'] = $this->sanitizeTimeString($daySchedule['start']);
            }

            if (isset($daySchedule['end']) && $daySchedule['end']) {
                $daySchedule['end'] = $this->sanitizeTimeString($daySchedule['end']);
            }
        }
    }

    /**
     * Sanitize a time string to ensure valid format
     */
    protected function sanitizeTimeString(?string $timeString): ?string
    {
        if (!$timeString) {
            return null;
        }

        $timeString = trim($timeString);

        if (preg_match('/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i', $timeString, $matches)) {
            $hour = (int) $matches[1];
            $minute = $matches[2];
            $meridiem = strtoupper($matches[3]);

            if ($hour === 0) {
                $hour = 12;
            } elseif ($hour > 12) {
                return $timeString;
            }

            if ((int) $minute > 59) {
                return $timeString;
            }

            return sprintf('%d:%s %s', $hour, $minute, $meridiem);
        }

        return $timeString;
    }
}


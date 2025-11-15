<?php

namespace Modules\Software\Services\PT;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Software\Models\GymPTClass;
use Modules\Software\Models\GymPTClassTrainer;

/**
 * PTSessionService
 *
 * Responsible for interpreting PT class schedules and producing virtual
 * session timelines (no physical rows are generated).
 */
class PTSessionService
{
    public function resolveNextScheduledSlot(
        GymPTClass $class,
        ?GymPTClassTrainer $classTrainer = null,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): ?Carbon {
        $start = $startDate ? $startDate->copy() : now()->copy();
        if ($classTrainer && $classTrainer->date_from) {
            $start = $start->max(Carbon::parse($classTrainer->date_from)->startOfDay());
        }

        $end = $endDate
            ? $endDate->copy()
            : ($classTrainer && $classTrainer->date_to
                ? Carbon::parse($classTrainer->date_to)->endOfDay()
                : $start->copy()->addMonths(3));

        if ($classTrainer && $classTrainer->date_to) {
            $end = $end->min(Carbon::parse($classTrainer->date_to)->endOfDay());
        }

        if ($end->lt($start)) {
            return null;
        }

        $slots = $this->generateVirtualSlots($class, $classTrainer, $start, $end);

        return $slots->first();
    }

    public function resolveVirtualTimeline(
        Collection $classTrainers,
        Carbon $from,
        Carbon $to
    ): Collection {
        $timeline = collect();

        foreach ($classTrainers as $assignment) {
            $class = $assignment->class ?? null;
            if (!$class) {
                continue;
            }

            $trainerAssignment = $assignment->id ? $assignment : null;

            $slots = $this->generateVirtualSlots($class, $trainerAssignment, $from, $to);

            foreach ($slots as $slot) {
                $timeline->push((object) [
                    'slot' => $slot->copy(),
                    'class' => $class,
                    'class_trainer' => $trainerAssignment,
                    'trainer' => $trainerAssignment?->trainer,
                ]);
            }
        }

        return $timeline->sortBy(fn ($entry) => $entry->slot->timestamp)->values();
    }

    protected function generateVirtualSlots(
        GymPTClass $class,
        ?GymPTClassTrainer $classTrainer,
        Carbon $from,
        Carbon $until
    ): Collection {
        $schedule = $this->normalizeSchedule(
            data_get($classTrainer?->schedule, 'work_days', []) ?: data_get($class->schedule, 'work_days', [])
        );

        if (empty($schedule)) {
            return collect();
        }

        $start = $from->copy();
        if ($classTrainer && $classTrainer->date_from) {
            $start = $start->max(Carbon::parse($classTrainer->date_from)->startOfDay());
        }

        $end = $until->copy();
        if ($classTrainer && $classTrainer->date_to) {
            $end = $end->min(Carbon::parse($classTrainer->date_to)->endOfDay());
        }

        if ($end->lt($start)) {
            return collect();
        }

        $cursor = $start->copy();
        $slots = collect();

        while ($cursor->lte($end)) {
            $weekday = $cursor->dayOfWeek;
            $workDay = $schedule[$weekday] ?? null;

            if ($workDay && data_get($workDay, 'status')) {
                $startTime = data_get($workDay, 'start');
                if ($startTime) {
                    $slotDate = $cursor->copy()->setTimeFromTimeString($startTime);
                    if ($slotDate->gte($start) && $slotDate->lte($end)) {
                        $slots->push($slotDate->copy());
                    }
                }
            }

            $cursor->addDay()->startOfDay();
        }

        return $slots;
    }

    public function encodeVirtualSessionId(GymPTClass $class, ?GymPTClassTrainer $trainer, Carbon $slot): string
    {
        $payload = implode('|', [
            $class->id,
            $trainer?->id ?? 0,
            $slot->timestamp,
        ]);

        return base64_encode($payload);
    }

    public function decodeVirtualSessionId(string $encoded): ?array
    {
        $decoded = base64_decode($encoded, true);
        if ($decoded === false) {
            return null;
        }

        $parts = explode('|', $decoded);
        if (count($parts) !== 3) {
            return null;
        }

        [$classId, $trainerId, $timestamp] = $parts;

        return [
            'class_id' => (int) $classId,
            'class_trainer_id' => ((int) $trainerId) ?: null,
            'timestamp' => (int) $timestamp,
        ];
    }

    protected function normalizeSchedule(array $schedule): array
    {
        $normalized = [];
        foreach ($schedule as $key => $value) {
            $index = $this->resolveWeekdayIndex($key);
            if ($index === null) {
                continue;
            }
            $normalized[$index] = $value;
        }

        return $normalized;
    }

    protected function resolveWeekdayIndex($key): ?int
    {
        if (is_numeric($key)) {
            $index = (int) $key;
            return ($index >= 0 && $index <= 6) ? $index : null;
        }

        $map = [
            'sun' => 0,
            'sunday' => 0,
            'mon' => 1,
            'monday' => 1,
            'tue' => 2,
            'tuesday' => 2,
            'wed' => 3,
            'wednesday' => 3,
            'thu' => 4,
            'thur' => 4,
            'thurs' => 4,
            'thursday' => 4,
            'fri' => 5,
            'friday' => 5,
            'sat' => 6,
            'saturday' => 6,
        ];

        $key = strtolower(trim((string) $key));

        return $map[$key] ?? null;
    }
}




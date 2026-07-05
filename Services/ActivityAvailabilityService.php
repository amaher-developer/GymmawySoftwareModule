<?php

namespace Modules\Software\Services;

use Carbon\Carbon;
use Modules\Software\Models\GymActivity;
use Modules\Software\Models\GymActivityTrainer;
use Modules\Software\Models\GymMemberSubscription;
use Modules\Software\Models\GymReservation;

/**
 * Single place for computing activity availability / overlap checks, used by
 * both the admin reservation panel and the member-facing booking API.
 *
 * Every overlap/limit query is scoped by activity_id AND (activity_trainer_id
 * when a trainer is resolved, or activity_trainer_id IS NULL for the legacy
 * path) so that two different trainers on the same activity are scheduled
 * completely independently, while activities that never adopt the
 * multi-trainer feature keep behaving exactly as before.
 */
class ActivityAvailabilityService
{
    /**
     * True when staff/the client must explicitly pick a trainer for this
     * activity (more than one active trainer assigned) - i.e. there is no
     * safe default to auto-resolve.
     */
    public function requiresTrainerSelection(GymActivity $activity): bool
    {
        return $activity->activeActivityTrainers()->count() > 1;
    }

    /**
     * Resolve an explicitly-given activity_trainer_id for the admin/staff
     * flow. Returns null when the activity has no multi-trainer rows at all
     * (pure legacy path) or when exactly one active row exists (auto-select).
     */
    public function resolveActivityTrainer(GymActivity $activity, ?int $activityTrainerId): ?GymActivityTrainer
    {
        if ($activityTrainerId) {
            return $activity->activeActivityTrainers()->where('id', $activityTrainerId)->first();
        }

        $active = $activity->activeActivityTrainers()->get();

        return $active->count() === 1 ? $active->first() : null;
    }

    /**
     * Resolve the trainer already pinned to this activity inside a member's
     * subscription (the one-time choice made at subscription-creation time).
     * Falls back to null when the subscription's stored entry has no pinned
     * trainer (legacy data / single-trainer activity).
     */
    public function resolveActivityTrainerFromSubscription(GymMemberSubscription $subscription, int $activityId): ?GymActivityTrainer
    {
        $activities = is_array($subscription->activities) ? $subscription->activities : (array) $subscription->activities;

        foreach ($activities as $entry) {
            $entry = (array) $entry;
            $entryActivityId = $entry['activity_id'] ?? $entry['id'] ?? null;

            if ((int) $entryActivityId !== (int) $activityId) {
                continue;
            }

            if (empty($entry['activity_trainer_id'])) {
                return null;
            }

            return GymActivityTrainer::active()->where('id', $entry['activity_trainer_id'])->first();
        }

        return null;
    }

    /**
     * The work_days schedule to use for slot generation: the trainer row's
     * own schedule if one is resolved, else the activity's shared schedule
     * (100% unchanged legacy behaviour).
     */
    public function resolveSchedule(GymActivity $activity, ?GymActivityTrainer $activityTrainer): ?array
    {
        if ($activityTrainer && $activityTrainer->schedule) {
            return $activityTrainer->schedule;
        }

        return $activity->reservation_details ?? null;
    }

    public function resolveReservationLimit(GymActivity $activity, ?GymActivityTrainer $activityTrainer): int
    {
        if ($activityTrainer && $activityTrainer->reservation_limit !== null) {
            return (int) $activityTrainer->reservation_limit;
        }

        return (int) ($activity->reservation_limit ?? 0);
    }

    /**
     * Base query for reservations competing for the same trainer-scoped
     * calendar slot: same activity, and either the same activity_trainer_id
     * (when resolved) or NULL (legacy path) - never mixed.
     */
    private function overlapBaseQuery(GymActivity $activity, ?GymActivityTrainer $activityTrainer)
    {
        return GymReservation::where('activity_id', $activity->id)
            ->when(
                $activityTrainer,
                fn ($q) => $q->where('activity_trainer_id', $activityTrainer->id),
                fn ($q) => $q->whereNull('activity_trainer_id')
            )
            ->whereNotIn('status', ['cancelled', 'missed']);
    }

    private function dayAvailability(?array $schedule, int $dayOfWeek): array
    {
        if (!$schedule || !isset($schedule['work_days']) || !is_array($schedule['work_days']) || count($schedule['work_days']) === 0) {
            return ['available' => true, 'work_hours' => null];
        }

        if (!isset($schedule['work_days'][$dayOfWeek])) {
            return ['available' => false, 'work_hours' => null];
        }

        $dayConfig = $schedule['work_days'][$dayOfWeek];
        $available = isset($dayConfig['status']) && $dayConfig['status'] == 1;

        $workHours = null;
        if ($available && isset($dayConfig['start']) && isset($dayConfig['end'])) {
            $workHours = ['start' => $dayConfig['start'], 'end' => $dayConfig['end']];
        }

        return ['available' => $available, 'work_hours' => $workHours];
    }

    /**
     * @return array{date:string,duration:int,interval:int,reservation_limit:int,has_limit:bool,day_available:bool,work_hours?:array,message?:string,slots:array}
     */
    public function getAvailableSlots(
        GymActivity $activity,
        ?GymActivityTrainer $activityTrainer,
        string $date,
        ?int $duration = null,
        int $interval = 30
    ): array {
        $duration = $duration ?? (int) ($activity->reservation_duration ?? ($activity->duration_minutes ?? 60));
        $reservationLimit = $this->resolveReservationLimit($activity, $activityTrainer);
        $hasLimit = $reservationLimit > 0;

        $dateCarbon = Carbon::parse($date);
        $date = $dateCarbon->format('Y-m-d');
        $dayOfWeek = $dateCarbon->dayOfWeek;

        $schedule = $this->resolveSchedule($activity, $activityTrainer);
        $dayInfo = $this->dayAvailability($schedule, $dayOfWeek);

        if (!$dayInfo['available']) {
            return [
                'date' => $date,
                'duration' => $duration,
                'interval' => $interval,
                'reservation_limit' => $reservationLimit,
                'has_limit' => $hasLimit,
                'day_available' => false,
                'message' => trans('sw.day_not_available_for_reservation'),
                'slots' => [],
            ];
        }

        $startOfDay = $dayInfo['work_hours']['start'] ?? '08:00';
        $endOfDay = $dayInfo['work_hours']['end'] ?? '20:00';

        $cursor = Carbon::parse("$date $startOfDay");
        $endDay = Carbon::parse("$date $endOfDay");
        $slots = [];

        while ($cursor->copy()->addMinutes($duration) <= $endDay) {
            $slots[] = [
                'start_time' => $cursor->format('H:i'),
                'end_time' => $cursor->copy()->addMinutes($duration)->format('H:i'),
            ];
            $cursor->addMinutes($interval);
        }

        $existing = $this->overlapBaseQuery($activity, $activityTrainer)
            ->whereDate('reservation_date', $date)
            ->get()
            ->map(function ($r) {
                return [
                    'start' => $r->start_time ? substr($r->start_time, 0, 5) : null,
                    'end' => $r->end_time ? substr($r->end_time, 0, 5) : null,
                ];
            })
            ->filter(fn ($r) => $r['start'] && $r['end'])
            ->values()
            ->toArray();

        $countOverlaps = function (string $s, string $e) use ($existing) {
            $count = 0;
            foreach ($existing as $ex) {
                try {
                    $aStart = Carbon::createFromFormat('H:i', $ex['start']);
                    $aEnd = Carbon::createFromFormat('H:i', $ex['end']);
                    $sTime = Carbon::createFromFormat('H:i', $s);
                    $eTime = Carbon::createFromFormat('H:i', $e);

                    if ($sTime->lt($aEnd) && $eTime->gt($aStart)) {
                        $count++;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            return $count;
        };

        $result = [];
        foreach ($slots as $slot) {
            $overlapCount = $countOverlaps($slot['start_time'], $slot['end_time']);

            if ($hasLimit) {
                $isAvailable = $overlapCount < $reservationLimit;
                $remainingSlots = max(0, $reservationLimit - $overlapCount);
            } else {
                $isAvailable = true;
                $remainingSlots = null;
            }

            $result[] = [
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
                'available' => $isAvailable,
                'current_bookings' => $overlapCount,
                'reservation_limit' => $reservationLimit,
                'remaining_slots' => $remainingSlots,
            ];
        }

        return [
            'date' => $date,
            'duration' => $duration,
            'interval' => $interval,
            'reservation_limit' => $reservationLimit,
            'has_limit' => $hasLimit,
            'day_available' => true,
            'work_hours' => ['start' => $startOfDay, 'end' => $endOfDay],
            'slots' => $result,
        ];
    }

    /**
     * @return array{conflict: bool, message: ?string}
     */
    public function checkConflict(
        GymActivity $activity,
        ?GymActivityTrainer $activityTrainer,
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeReservationId = null
    ): array {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $schedule = $this->resolveSchedule($activity, $activityTrainer);
        $dayInfo = $this->dayAvailability($schedule, $dayOfWeek);

        if (!$dayInfo['available']) {
            return ['conflict' => true, 'message' => trans('sw.day_not_available_for_reservation')];
        }

        if ($dayInfo['work_hours']) {
            $s = substr($startTime, 0, 5);
            $e = substr($endTime, 0, 5);
            $dayStart = substr($dayInfo['work_hours']['start'], 0, 5);
            $dayEnd = substr($dayInfo['work_hours']['end'], 0, 5);

            if ($s < $dayStart || $e > $dayEnd || $s >= $dayEnd) {
                return [
                    'conflict' => true,
                    'message' => trans('sw.time_outside_working_hours', ['start' => $dayStart, 'end' => $dayEnd]),
                ];
            }
        }

        $reservationLimit = $this->resolveReservationLimit($activity, $activityTrainer);

        $query = $this->overlapBaseQuery($activity, $activityTrainer)
            ->whereDate('reservation_date', $date);

        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        $overlapCount = $query->get()->filter(function ($r) use ($startTime, $endTime) {
            $s = substr($startTime, 0, 5);
            $e = substr($endTime, 0, 5);
            $rStart = $r->start_time ? substr($r->start_time, 0, 5) : null;
            $rEnd = $r->end_time ? substr($r->end_time, 0, 5) : null;

            if (!$rStart || !$rEnd) {
                return false;
            }

            try {
                $aStart = Carbon::createFromFormat('H:i', $rStart);
                $aEnd = Carbon::createFromFormat('H:i', $rEnd);
                $sTime = Carbon::createFromFormat('H:i', $s);
                $eTime = Carbon::createFromFormat('H:i', $e);

                return $sTime->lt($aEnd) && $eTime->gt($aStart);
            } catch (\Exception $ex) {
                return false;
            }
        })->count();

        if ($reservationLimit > 0) {
            if ($overlapCount >= $reservationLimit) {
                return [
                    'conflict' => true,
                    'message' => trans('sw.reservation_limit_reached', ['limit' => $reservationLimit]),
                ];
            }

            return ['conflict' => false, 'message' => null];
        }

        if ($overlapCount > 0) {
            return ['conflict' => true, 'message' => trans('sw.time_conflict_detected')];
        }

        return ['conflict' => false, 'message' => trans('sw.time_slot_available')];
    }
}

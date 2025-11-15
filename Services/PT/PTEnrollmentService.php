<?php

namespace Modules\Software\Services\PT;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Software\Models\GymPTClass;
use Modules\Software\Models\GymPTClassTrainer;
use Modules\Software\Models\GymPTMember;

/**
 * PTEnrollmentService
 *
 * Centralises enrolment flows for PT members including trainer assignment,
 * session allocation, and remaining sessions projection.
 *
 * @example
 * $service = app(PTEnrollmentService::class);
 * $member = $service->enrollMember($class, $payload);
 */
class PTEnrollmentService
{
    public function __construct(
        protected PTSessionService $sessionService
    ) {
    }

    /**
     * Enrol a member into a PT class.
     *
     * @param  GymPTClass  $class
     * @param  array<string, mixed>  $attributes
     * @return GymPTMember
     *
     * @throws ValidationException
     */
    public function enrollMember(GymPTClass $class, array $attributes): GymPTMember
    {
        $this->validateEnrollmentPayload($class, $attributes);

        return DB::transaction(function () use ($class, $attributes) {
            $member = new GymPTMember($this->prepareMemberAttributes($class, $attributes));
            $member->branch_setting_id = $class->branch_setting_id;
            $member->save();

            return $member;
        });
    }

    /**
     * Update an existing PT member keeping legacy counters aligned.
     *
     * @param  GymPTMember  $member
     * @param  array<string, mixed>  $attributes
     * @return GymPTMember
     *
     * @throws ValidationException
     */
    public function updateMember(GymPTMember $member, array $attributes): GymPTMember
    {
        $class = $member->pt_class ?? $member->class;
        
        if (!$class) {
            throw ValidationException::withMessages([
                'pt_class_id' => trans('sw.no_record_found'),
            ]);
        }

        $this->validateEnrollmentPayload($class, $attributes, $member);
        
        return DB::transaction(function () use ($member, $class, $attributes) {
            $originalTotal = $member->total_sessions ?? $member->classes ?? 0;
            $originalRemaining = $member->remaining_sessions ?? max($originalTotal - ($member->visits ?? 0), 0);
            $consumed = max($originalTotal - $originalRemaining, 0);

            $member->fill($this->prepareMemberAttributes($class, $attributes, $member));

            $targetTotal = $member->total_sessions ?? $member->classes ?? 0;
            $member->remaining_sessions = max($targetTotal - $consumed, 0);

            $member->save();

            return $member;
        });
    }

    /**
     * Adjust remaining sessions after attendance is recorded or reversed.
     *
     * @param  GymPTMember  $member
     * @param  int  $delta negative values reduce remaining sessions
     * @return GymPTMember
     */
    public function adjustRemainingSessions(GymPTMember $member, int $delta): GymPTMember
    {
        $member->refresh();

        $member->remaining_sessions = max(($member->remaining_sessions ?? 0) + $delta, 0);

        // Keep legacy visits in sync for backwards compatibility.
        $expectedTotal = $member->total_sessions ?? $member->classes ?? 0;
        if ($expectedTotal > 0) {
            $member->visits = max($expectedTotal - $member->remaining_sessions, 0);
        }

        $member->save();

        return $member;
    }

    /**
     * Validate enrolment payload according to class type.
     *
     * @param  GymPTClass  $class
     * @param  array<string, mixed>  $attributes
     * @param  GymPTMember|null  $existingMember
     *
     * @throws ValidationException
     */
    protected function validateEnrollmentPayload(
        GymPTClass $class,
        array $attributes,
        ?GymPTMember $existingMember = null
    ): void {
        $classType = $attributes['class_type'] ?? $class->class_type ?? 'private';
        $trainerId = $attributes['class_trainer_id'] ?? $attributes['pt_trainer_id'] ?? null;

        if ($classType === 'private' && !$trainerId && !$existingMember?->class_trainer_id) {
            throw ValidationException::withMessages([
                'class_trainer_id' => trans('sw.required_field'),
            ]);
        }

        if (in_array($classType, ['group', 'mixed'], true) && !$trainerId && !$existingMember?->class_trainer_id) {
            throw ValidationException::withMessages([
                'class_trainer_id' => trans('sw.pt_group_trainer_assignment_message'),
            ]);
        }
    }

    /**
     * Prepare attributes ready for persistence.
     *
     * @param  GymPTClass  $class
     * @param  array<string, mixed>  $attributes
     * @param  GymPTMember|null  $existingMember
     * @return array<string, mixed>
     */
    protected function prepareMemberAttributes(
        GymPTClass $class,
        array $attributes,
        ?GymPTMember $existingMember = null
    ): array {
        $target = [];
        $classType = $attributes['class_type'] ?? $class->class_type ?? 'private';

        $target['member_id'] = $attributes['member_id'] ?? $existingMember?->member_id;
        $target['pt_class_id'] = $class->id;
        $target['class_id'] = $class->id;
        $target['pt_subscription_id'] = $attributes['pt_subscription_id'] ?? $existingMember?->pt_subscription_id ?? $class->pt_subscription_id;

        $selectedTrainerId = $attributes['class_trainer_id'] ?? $existingMember?->class_trainer_id;
        $target['class_trainer_id'] = $selectedTrainerId;
        $target['pt_trainer_id'] = $this->resolveTrainerId($selectedTrainerId);

        $target['start_date'] = $this->resolveDate($attributes, 'start_date', $existingMember?->start_date);
        $target['end_date'] = $this->resolveDate($attributes, 'end_date', $existingMember?->end_date);
        $target['joining_date'] = $target['start_date'] ?? $existingMember?->joining_date ?? now();
        $target['expire_date'] = $target['end_date'] ?? $existingMember?->expire_date;

        $target['classes'] = $attributes['classes'] ?? $class->total_sessions ?? $existingMember?->classes ?? 0;
        $target['total_sessions'] = $attributes['total_sessions'] ?? $target['classes'];
        $target['remaining_sessions'] = $attributes['remaining_sessions'] ?? $existingMember?->remaining_sessions ?? $target['total_sessions'];

        $target['amount_paid'] = $attributes['amount_paid'] ?? $existingMember?->amount_paid ?? 0;
        $target['amount_before_discount'] = $attributes['amount_before_discount'] ?? $existingMember?->amount_before_discount ?? $target['amount_paid'];
        $target['discount_value'] = $attributes['discount'] ?? $attributes['discount_value'] ?? $existingMember?->discount_value ?? 0;
        $target['discount'] = $target['discount_value'];
        $target['payment_method'] = $attributes['payment_method'] ?? $existingMember?->payment_method;

        $target['trainer_percentage'] = $attributes['trainer_percentage'] ?? $existingMember?->trainer_percentage ?? 0;

        return $target;
    }

    /**
     * Resolve trainer id from class trainer pivot.
     *
     * @param  int|null  $classTrainerId
     * @return int|null
     */
    protected function resolveTrainerId(?int $classTrainerId): ?int
    {
        if (!$classTrainerId) {
            return null;
        }

        return GymPTClassTrainer::query()
            ->where('id', $classTrainerId)
            ->value('trainer_id');
    }

    /**
     * Helper to normalise date values from payload.
     *
     * @param  array<string, mixed>  $attributes
     * @param  string  $key
     * @param  Carbon|string|null  $fallback
     * @return Carbon|null
     */
    protected function resolveDate(array $attributes, string $key, $fallback = null): ?Carbon
    {
        if (empty($attributes[$key])) {
            return $fallback ? Carbon::parse($fallback) : null;
        }

        return Carbon::parse($attributes[$key]);
    }

    /**
     * Fetch existing sessions for a member summarised by status.
     *
     * @param  GymPTMember  $member
     * @return array<string, int>
     */
    public function summarizeMemberSessions(GymPTMember $member): array
    {
        $member->loadMissing('sessions');

        return $member->sessions
            ->groupBy('status')
            ->map->count()
            ->sortKeys()
            ->toArray();
    }
}




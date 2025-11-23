<?php

namespace Modules\Software\Http\Controllers\Front;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Software\Models\GymPTClass;
use Modules\Software\Models\GymPTClassTrainer;
use Modules\Software\Models\GymPTMemberAttendee;
use Modules\Software\Models\GymPTTrainer;
use Modules\Software\Services\PT\PTSessionService;

class GymPTSessionFrontController extends GymGenericFrontController
{
    protected PTSessionService $sessionService;

    public function __construct()
    {
        parent::__construct();
        $this->sessionService = app(PTSessionService::class);
    }

    public function index(Request $request): View
    {
        $title = trans('sw.pt_sessions');

        // Optimize: Add select to limit columns and ensure pt_subscription is eager loaded
        $classes = GymPTClass::branch()
            ->select('id', 'name_ar', 'name_en', 'pt_subscription_id')
            ->with(['pt_subscription' => function($q) {
                $q->select('id', 'name_ar', 'name_en');
            }])
            ->orderBy('name_' . $this->lang, 'asc')
            ->get();

        $trainers = GymPTTrainer::branch()
            ->orderBy('name', 'asc')
            ->get();

        $fromInput = $request->filled('from')
            ? $request->input('from')
            : $request->input('date_from');
        $toInput = $request->filled('to')
            ? $request->input('to')
            : $request->input('date_to');

        $rangeStart = $fromInput
            ? Carbon::parse($fromInput)->startOfDay()
            : Carbon::now()->startOfWeek();

        $rangeEnd = $toInput
            ? Carbon::parse($toInput)->endOfDay()
            : $rangeStart->copy()->addWeek();

        if ($rangeEnd->lt($rangeStart)) {
            [$rangeStart, $rangeEnd] = [$rangeEnd->copy()->startOfDay(), $rangeStart->copy()->endOfDay()];
        }

        $branchId = @$this->user_sw->branch_setting_id;
        $classFilter = $request->integer('class_id');
        $trainerFilter = $request->integer('trainer_id');
        $statusFilter = $request->input('status');

        // Optimize: Add eager loading for class.pt_subscription to prevent lazy loading
        $trainerAssignments = GymPTClassTrainer::with([
            'class' => function($q) {
                $q->select('id', 'name_ar', 'name_en', 'pt_subscription_id', 'schedule');
            },
            'class.pt_subscription' => function($q) {
                $q->select('id', 'name_ar', 'name_en');
            },
            'trainer' => function($q) {
                $q->select('id', 'name');
            }
        ])
            ->where('is_active', true)
            ->when($branchId, function ($query, $branchId) {
                $query->where('branch_setting_id', $branchId);
            })
            ->when($classFilter, function ($query) use ($classFilter) {
                $query->where('class_id', $classFilter);
            })
            ->when($trainerFilter, function ($query) use ($trainerFilter) {
                $query->where('trainer_id', $trainerFilter);
            })
            ->get();

        // Optimize: Add select to limit columns and ensure pt_subscription is eager loaded
        $classesNeedingFallback = GymPTClass::branch()
            ->select('id', 'name_ar', 'name_en', 'pt_subscription_id', 'schedule')
            ->with(['pt_subscription' => function($q) {
                $q->select('id', 'name_ar', 'name_en');
            }])
            ->when($classFilter, function ($query) use ($classFilter) {
                $query->where('id', $classFilter);
            })
            ->whereNotNull('schedule')
            ->get();

        foreach ($classesNeedingFallback as $classModel) {
            $alreadyAssigned = $trainerAssignments->firstWhere('class_id', $classModel->id);
            if ($alreadyAssigned) {
                continue;
            }

            $trainerAssignments->push($this->makeVirtualAssignment($classModel));
        }

        $attendanceLookup = GymPTMemberAttendee::with('pt_member')
            ->whereBetween('session_date', [$rangeStart, $rangeEnd])
            ->get()
            ->groupBy(function (GymPTMemberAttendee $attendee) {
                $member = $attendee->pt_member;
                if (!$member || !$attendee->session_date) {
                    return null;
                }

                $classId = $member->class_id ?? $member->pt_class_id ?? 0;
                $trainerId = $member->class_trainer_id ?? 0;

                return $this->buildTimelineKey($classId, $trainerId, $attendee->session_date);
            })
            ->filter(fn ($value, $key) => !is_null($key));

        $timeline = $this->sessionService->resolveVirtualTimeline($trainerAssignments, $rangeStart, $rangeEnd);

        $records = $timeline
            ->map(function (object $entry) use ($attendanceLookup, $statusFilter) {
                $classModel = $entry->class;
                $assignment = $entry->class_trainer;
                $slot = $entry->slot;
                $trainer = $entry->trainer;

                $key = $this->buildTimelineKey($classModel->id, $assignment?->id ?? 0, $slot);
                $attendeeCount = isset($attendanceLookup[$key]) ? $attendanceLookup[$key]->count() : 0;
                $status = $attendeeCount > 0 ? 'completed' : 'pending';

                if ($statusFilter && $status !== $statusFilter) {
                    return null;
                }

                return (object) [
                    'id' => $this->sessionService->encodeVirtualSessionId($classModel, $assignment, $slot),
                    'class' => $classModel,
                    'trainer' => $trainer,
                    'session_date' => $slot->copy(),
                    'status' => $status,
                    'attendee_count' => $attendeeCount,
                    'max_members' => $classModel->max_members,
                ];
            })
            ->filter()
            ->sortBy('session_date')
            ->values();
        $perPage = $this->limit ?? 25;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paginated = new LengthAwarePaginator(
            $records->forPage($currentPage, $perPage),
            $records->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $statusOptions = [
            'pending' => trans('sw.session_status_pending'),
            'completed' => trans('sw.session_status_completed'),
        ];

        $filtersApplied = $request->filled('class_id')
            || $request->filled('trainer_id')
            || $request->filled('status')
            || $request->filled('from')
            || $request->filled('to')
            || $request->filled('date_from')
            || $request->filled('date_to');

        return view('software::Front.pt_session_front_list', [
            'title' => $title,
            'sessions' => $paginated,
            'classes' => $classes,
            'trainers' => $trainers,
            'statusOptions' => $statusOptions,
            'filtersApplied' => $filtersApplied,
            'filters' => [
                'from' => $fromInput,
                'to' => $toInput,
                'class_id' => $classFilter,
                'trainer_id' => $trainerFilter,
                'status' => $statusFilter,
            ],
        ]);
    }

    public function show(string $virtualSession): View
    {
        $decoded = $this->sessionService->decodeVirtualSessionId($virtualSession);
        if (!$decoded) {
            abort(404);
        }

        // Optimize: Add select to limit columns and ensure pt_subscription is eager loaded
        $class = GymPTClass::branch()
            ->select('id', 'name_ar', 'name_en', 'pt_subscription_id', 'max_members')
            ->with(['pt_subscription' => function($q) {
                $q->select('id', 'name_ar', 'name_en');
            }])
            ->find($decoded['class_id']);

        if (!$class) {
            abort(404);
        }

        $classTrainer = null;
        if ($decoded['class_trainer_id']) {
            $classTrainer = GymPTClassTrainer::with('trainer')
                ->find($decoded['class_trainer_id']);

            if (!$classTrainer || $classTrainer->class_id !== $class->id) {
                abort(404);
            }
        }

        $slot = Carbon::createFromTimestamp($decoded['timestamp'])->timezone(config('app.timezone'));

        $attendeeQuery = GymPTMemberAttendee::with([
            'pt_member.member',
            'pt_member.pt_subscription',
            'user',
        ])
            ->where('session_date', $slot->copy())
            ->whereHas('pt_member', function ($query) use ($class, $classTrainer) {
                $query->where(function ($inner) use ($class) {
                    $inner->where('class_id', $class->id)
                        ->orWhere('pt_class_id', $class->id);
                });

                if ($classTrainer) {
                    $query->where('class_trainer_id', $classTrainer->id);
                }
            });

        $attendees = $attendeeQuery
            ->orderByDesc('created_at')
            ->get()
            ->map(function (GymPTMemberAttendee $attendee) {
                $ptMember = $attendee->pt_member;
                $sessionsTotal = $ptMember?->sessions_total ?? $ptMember?->total_sessions ?? 0;
                $sessionsRemaining = $ptMember?->sessions_remaining;
                $sessionsUsed = $ptMember?->sessions_used;
                if ($sessionsUsed === null && $sessionsRemaining !== null && $sessionsTotal !== null) {
                    $sessionsUsed = max($sessionsTotal - $sessionsRemaining, 0);
                }

                return [
                    'id' => $attendee->id,
                    'member_name' => optional($ptMember?->member)->name,
                    'member_code' => optional($ptMember?->member)->code,
                    'subscription' => optional($ptMember?->pt_subscription)->name,
                    'sessions_total' => $sessionsTotal,
                    'sessions_used' => $sessionsUsed,
                    'sessions_remaining' => $sessionsRemaining,
                    'recorded_at' => optional($attendee->created_at)->format('Y-m-d H:i'),
                    'recorded_by' => optional($attendee->user)->name,
                ];
            })
            ->values();

        $status = $attendees->count() > 0 ? 'completed' : 'pending';

        $sessionViewModel = (object) [
            'id' => $virtualSession,
            'session_date' => $slot,
            'class' => $class,
            'trainer' => $classTrainer?->trainer,
            'status' => $status,
            'attendee_count' => $attendees->count(),
            'max_members' => $class->max_members,
        ];

        $summary = [
            'total_attendees' => $attendees->count(),
            'max_members' => $class->max_members,
            'remaining_capacity' => $class->max_members
                ? max($class->max_members - $attendees->count(), 0)
                : null,
        ];

        if ($classTrainer && !$classTrainer->relationLoaded('class')) {
            $classTrainer->setRelation('class', $class);
        }
        $assignmentForTimeline = $classTrainer ?? $this->makeVirtualAssignment($class);

        $upcomingTimeline = $this->sessionService->resolveVirtualTimeline(
            collect([$assignmentForTimeline]),
            $slot->copy()->addDay()->startOfDay(),
            $slot->copy()->addWeeks(2)->endOfDay()
        );

        $attendanceLookup = GymPTMemberAttendee::whereBetween('session_date', [
            $slot->copy()->addDay()->startOfDay(),
            $slot->copy()->addWeeks(2)->endOfDay(),
        ])
            ->whereHas('pt_member', function ($query) use ($class, $classTrainer) {
                $query->where(function ($inner) use ($class) {
                    $inner->where('class_id', $class->id)
                        ->orWhere('pt_class_id', $class->id);
                });

                if ($classTrainer) {
                    $query->where('class_trainer_id', $classTrainer->id);
                }
            })
            ->with('pt_member')
            ->get()
            ->groupBy(function (GymPTMemberAttendee $attendee) use ($classTrainer, $class) {
                $member = $attendee->pt_member;
                if (!$member || !$attendee->session_date) {
                    return null;
                }

                $classId = $member->class_id ?? $member->pt_class_id ?? $class->id;
                $trainerId = $member->class_trainer_id ?? ($classTrainer?->id ?? 0);

                return $this->buildTimelineKey($classId, $trainerId, $attendee->session_date);
            })
            ->filter(fn ($value, $key) => !is_null($key));

        $upcomingSessions = $upcomingTimeline
            ->take(5)
            ->map(function (object $entry) use ($attendanceLookup, $classTrainer) {
                $classModel = $entry->class;
                $assignment = $entry->class_trainer ?? $classTrainer;
                $slot = $entry->slot;

                $key = $this->buildTimelineKey($classModel->id, $assignment?->id ?? 0, $slot);

                return [
                    'session_date' => $slot->copy(),
                    'trainer_name' => optional($assignment?->trainer)->name,
                    'status' => isset($attendanceLookup[$key]) && $attendanceLookup[$key]->count() > 0
                        ? 'completed'
                        : 'pending',
                ];
            });

        return view('software::Front.pt_session_front_show', [
            'title' => trans('sw.pt_session_details'),
            'session' => $sessionViewModel,
            'attendees' => $attendees,
            'summary' => $summary,
            'upcomingSessions' => $upcomingSessions,
        ]);
    }

    protected function makeVirtualAssignment(GymPTClass $class): GymPTClassTrainer
    {
        $assignment = new GymPTClassTrainer([
            'id' => null,
            'class_id' => $class->id,
            'trainer_id' => null,
            'is_active' => true,
        ]);

        $assignment->setRelation('class', $class);

        return $assignment;
    }

    protected function buildTimelineKey(int $classId, int $trainerId, Carbon $slot): string
    {
        return "{$classId}|{$trainerId}|" . $slot->format('Y-m-d H:i:s');
    }
}






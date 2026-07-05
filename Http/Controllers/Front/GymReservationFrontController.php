<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Software\Http\Controllers\Front\GymGenericFrontController;
use Modules\Software\Repositories\GymReservationRepository;
use Modules\Software\Http\Requests\GymReservationRequest;
use Modules\Software\Models\GymReservation;
use Modules\Software\Models\GymActivity;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymNonMember;
use Modules\Software\Models\GymPTTrainer;
use Modules\Software\Models\GymReservationUsage;
use Modules\Software\Models\GymMemberSubscription;
use Modules\Software\Models\GymNonMemberTime;
use Modules\Software\Models\GymActivityTrainer;
use Modules\Software\Services\ActivityAvailabilityService;
use Modules\Software\Classes\TypeConstants;
use Illuminate\Container\Container as Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GymReservationFrontController extends GymGenericFrontController
{
    public $ReservationRepository;
    private ActivityAvailabilityService $availabilityService;

    public function __construct()
    {
        parent::__construct();

        $this->ReservationRepository =
            new GymReservationRepository(new Application);
        $this->availabilityService = new ActivityAvailabilityService();
    }

    /**
     * Resolves which trainer a booking should be scoped to:
     * 1. An explicitly given activity_trainer_id (staff override) - validated.
     * 2. For a member booking, the trainer already pinned on their own
     *    subscription for this activity (chosen once at subscription time).
     * 3. The activity's sole active trainer, if there's exactly one.
     * Returns an error when the activity has multiple trainers and none of
     * the above resolved one - staff must pick explicitly in that case
     * (e.g. non-member walk-ins, or a member without a prior pin).
     *
     * @return array{trainer: ?GymActivityTrainer, error: ?string}
     */
    private function resolveActivityTrainerForBooking(GymActivity $activity, ?int $activityTrainerId, string $clientType, ?int $memberId): array
    {
        if ($activityTrainerId) {
            $trainer = $activity->activeActivityTrainers()->where('id', $activityTrainerId)->first();
            if (!$trainer) {
                return ['trainer' => null, 'error' => trans('sw.trainer_not_valid_for_activity')];
            }
            return ['trainer' => $trainer, 'error' => null];
        }

        if ($clientType === 'member' && $memberId) {
            $subscription = GymMemberSubscription::where('member_id', $memberId)
                ->where('expire_date', '>=', Carbon::today())
                ->get()
                ->first(function ($ms) use ($activity) {
                    $entries = is_array($ms->activities) ? $ms->activities : (array) $ms->activities;
                    foreach ($entries as $entry) {
                        if ((int) (((object) $entry)->activity_id ?? 0) === $activity->id) {
                            return true;
                        }
                    }
                    return false;
                });

            if ($subscription) {
                $pinned = $this->availabilityService->resolveActivityTrainerFromSubscription($subscription, $activity->id);
                if ($pinned) {
                    return ['trainer' => $pinned, 'error' => null];
                }
            }
        }

        if ($this->availabilityService->requiresTrainerSelection($activity)) {
            return ['trainer' => null, 'error' => trans('sw.trainer_selection_required')];
        }

        return ['trainer' => $this->availabilityService->resolveActivityTrainer($activity, null), 'error' => null];
    }

    public function index()
    {
        $title = trans('sw.reservations');

        $this->request_array = ['search', 'activity_id', 'member_id', 'non_member_id', 'trainer_id', 'status', 'date'];
        foreach ($this->request_array as $item)
            $$item = request()->has($item) ? request()->$item : false;

        if (request('trashed')) {
            $reservations = $this->ReservationRepository->branch()->onlyTrashed()->orderBy('id', 'DESC');
        } else {
            $reservations = $this->ReservationRepository->branch()->orderBy('id', 'DESC');
        }

        $reservations->when($search, function ($query) use ($search) {
            $query->where(function($q) use ($search) {
                // Search by reservation ID
                $q->where('id', '=', $search)
                  // Search by member name
                  ->orWhereHas('member', function($memberQuery) use ($search) {
                      $memberQuery->where('name', 'like', "%{$search}%");
                  })
                  // Search by member code (ID)
                  ->orWhereHas('member', function($memberQuery) use ($search) {
                      $memberQuery->where('id', 'like', "%{$search}%");
                  })
                  // Search by non-member name
                  ->orWhereHas('nonMember', function($nonMemberQuery) use ($search) {
                      $nonMemberQuery->where('name', 'like', "%{$search}%");
                  });
            });
        });

        $reservations->when($activity_id, fn($q) => $q->where('activity_id', $activity_id));
        $reservations->when($member_id, fn($q) => $q->where('member_id', $member_id));
        $reservations->when($non_member_id, fn($q) => $q->where('non_member_id', $non_member_id));
        // Match the reservation's own resolved trainer (set on every booking going forward,
        // whether pinned via a member's subscription or picked by staff) OR, for legacy rows
        // that predate this column, fall back to the activity's single default trainer.
        $reservations->when($trainer_id, fn($q) => $q->where(function ($q2) use ($trainer_id) {
            $q2->where('trainer_id', $trainer_id)
               ->orWhere(function ($q3) use ($trainer_id) {
                   $q3->whereNull('trainer_id')
                      ->whereHas('activity', fn($aq) => $aq->where('trainer_id', $trainer_id));
               });
        }));
        $reservations->when($status, fn($q) => $q->where('status', $status));
        $reservations->when($date, fn($q) => $q->whereDate('reservation_date', $date));

        $search_query = request()->query();

        // Eager load relationships (include soft deleted activities, members, non-members)
        $reservations = $reservations->with([
            'activity' => function($query) {
                $query->withTrashed();
            },
            'member' => function($query) {
                $query->withTrashed();
            },
            'nonMember' => function($query) {
                $query->withTrashed();
            }
        ]);

        if ($this->limit) {
            $reservations = $reservations->paginate($this->limit);
            $total = $reservations->total();
        } else {
            $reservations = $reservations->get();
        $total = $reservations->count();
        }
        
        // Ensure relationships are loaded for all records (including soft deleted)
        if (method_exists($reservations, 'getCollection')) {
            $reservations->getCollection()->loadMissing([
                'activity' => function($query) {
                    $query->withTrashed();
                },
                'member' => function($query) {
                    $query->withTrashed();
                },
                'nonMember' => function($query) {
                    $query->withTrashed();
                }
            ]);
        } else {
            $reservations->loadMissing([
                'activity' => function($query) {
                    $query->withTrashed();
                },
                'member' => function($query) {
                    $query->withTrashed();
                },
                'nonMember' => function($query) {
                    $query->withTrashed();
                }
            ]);
        }

        $activities = GymActivity::branch()->get();
        $trainers = GymPTTrainer::branch()->get();

        // Set records - if paginated, use the paginator itself (it has items() method for iteration)
        // If not paginated, use the collection
        $records = $reservations;

        return view('software::Front.reservation_front_list', compact('reservations', 'records', 'title', 'total', 'search_query', 'activities', 'trainers'));
    }

    public function create()
    {
        $title = trans('sw.reservation_add');
        $activities = GymActivity::branch()->get();
        $members = GymMember::branch()->limit(100)->get(['id', 'name']);
        $nonMembers = GymNonMember::branch()->limit(100)->get(['id', 'name']);

        return view('software::Front.reservation_front_form', [
            'reservation' => new GymReservation(),
            'title'       => $title,
            'activities'  => $activities,
            'members'     => $members,
            'nonMembers'  => $nonMembers,
        ]);
    }

    public function store(GymReservationRequest $request)
    {
        // Check permission
        if (!in_array('createReservation', (array)$this->user_sw->permissions ?? []) && !@$this->user_sw->is_super_user) {
            session()->flash('sweet_flash_message', [
                'title'   => trans('admin.error'),
                'message' => trans('admin.permission_denied'),
                'type'    => 'error'
            ]);
            return redirect()->route('sw.listReservation');
        }

        $inputs = $request->except(['_token']);

        if (@$this->user_sw->branch_setting_id)
            $inputs['branch_setting_id'] = $this->user_sw->branch_setting_id;

        $this->ReservationRepository->create($inputs);

        session()->flash('sweet_flash_message', [
            'title'   => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type'    => 'success'
        ]);

        return redirect()->route('sw.listReservation');
    }

    public function edit($id)
    {
        // Check permission
        if (!in_array('editReservation', (array)$this->user_sw->permissions ?? []) && !@$this->user_sw->is_super_user) {
            session()->flash('sweet_flash_message', [
                'title'   => trans('admin.error'),
                'message' => trans('admin.permission_denied'),
                'type'    => 'error'
            ]);
            return redirect()->route('sw.listReservation');
        }

        $reservation = $this->ReservationRepository->withTrashed()->find($id);
        if (!$reservation) {
            abort(404, 'Reservation not found');
        }
        
        $title = trans('sw.reservation_edit');
        $activities = GymActivity::branch()->get();
        
        // Only load members/non-members if they are needed for the current reservation
        // For editing, we only need to load the current client to pre-select it
        $members = collect();
        $nonMembers = collect();
        
        // If reservation has a member, load only that member
        if ($reservation->member_id) {
            $member = GymMember::branch()->find($reservation->member_id);
            if ($member) {
                $members = collect([$member]);
            }
        }
        
        // If reservation has a non-member, load only that non-member
        if ($reservation->non_member_id) {
            $nonMember = GymNonMember::branch()->find($reservation->non_member_id);
            if ($nonMember) {
                $nonMembers = collect([$nonMember]);
            }
        }

        return view('software::Front.reservation_front_form', compact('reservation', 'title', 'activities', 'members', 'nonMembers'));
    }
    
    /**
     * AJAX endpoint to load members for select2
     */
    public function loadMembers(Request $request)
    {
        $search = $request->get('search', '');
        $query = GymMember::branch();
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }
        
        $members = $query->limit(50)->get(['id', 'name', 'code']);
        
        return response()->json([
            'results' => $members->map(function($member) {
                $text = $member->name;
                if ($member->code) {
                    $text .= ' (' . $member->code . ')';
                }
                return [
                    'id' => $member->id,
                    'text' => $text
                ];
            })
        ]);
    }
    
    /**
     * AJAX endpoint to load non-members for select2
     */
    public function loadNonMembers(Request $request)
    {
        $search = $request->get('search', '');
        $query = GymNonMember::branch();
        
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        
        $nonMembers = $query->limit(50)->get(['id', 'name']);
        
        return response()->json([
            'results' => $nonMembers->map(function($nonMember) {
                return [
                    'id' => $nonMember->id,
                    'text' => $nonMember->name
                ];
            })
        ]);
    }

    public function update(GymReservationRequest $request, $id)
    {
        // Check permission
        if (!in_array('editReservation', (array)$this->user_sw->permissions ?? []) && !@$this->user_sw->is_super_user) {
            session()->flash('sweet_flash_message', [
                'title'   => trans('admin.error'),
                'message' => trans('admin.permission_denied'),
                'type'    => 'error'
            ]);
            return redirect()->route('sw.listReservation');
        }

        $reservation = $this->ReservationRepository->withTrashed()->find($id);
        $inputs = $request->except(['_token']);

        $reservation->update($inputs);

        session()->flash('sweet_flash_message', [
            'title'   => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type'    => 'success'
        ]);

        return redirect()->route('sw.listReservation');
    }

    public function destroy($id)
    {
        // Check permission
        if (!in_array('deleteReservation', (array)$this->user_sw->permissions ?? []) && !@$this->user_sw->is_super_user) {
            session()->flash('sweet_flash_message', [
                'title'   => trans('admin.error'),
                'message' => trans('admin.permission_denied'),
                'type'    => 'error'
            ]);
            return redirect()->route('sw.listReservation');
        }

        $reservation = $this->ReservationRepository->withTrashed()->find($id);

        if ($reservation->trashed()) {
            $reservation->restore();
        } else {
            $reservation->delete();
        }

        session()->flash('sweet_flash_message', [
            'title'   => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type'    => 'success'
        ]);

        return redirect()->route('sw.listReservation');
    }

    /**
     * Events JSON feed for FullCalendar
     */
    public function events(Request $request)
    {
        $branch = auth()->check() && auth()->user()->branch_setting_id ? auth()->user()->branch_setting_id : null;
        $query = GymReservation::query()
            ->with([
                'member' => function($q) { $q->withTrashed(); },
                'nonMember' => function($q) { $q->withTrashed(); },
                'activity' => function($q) { $q->withTrashed(); }
            ])
            ->whereIn('status', ['pending', 'confirmed', 'attended']) // Only show active reservations
            ->orderBy('reservation_date', 'ASC')
            ->orderBy('start_time', 'ASC');

        if ($branch) {
            $query->where('branch_setting_id', $branch);
        }

        $all = $query->get();
        
        // Return empty array if no reservations found
        if ($all->isEmpty()) {
            return response()->json([]);
        }
        
        $events = $all->map(function($r) use ($request){
            $clientName = '';
            if ($r->client_type == 'member' && $r->member) {
                $clientName = $r->member->name ?? 'N/A';
            } elseif ($r->client_type == 'non_member' && $r->nonMember) {
                $clientName = $r->nonMember->name ?? 'N/A';
            } else {
                $clientName = $r->client_type == 'member' ? (trans('sw.member') . ' #' . $r->member_id) : (trans('sw.non_member') . ' #' . $r->non_member_id);
            }
            
            $activityName = 'N/A';
            if ($r->activity) {
                $activityLang = $request->get('lang') ?? ($lang ?? 'ar');
                $activityName = $r->activity->{'name_'.$activityLang} ?? $r->activity->name_ar ?? $r->activity->name_en ?? $r->activity->name ?? 'N/A';
            }

            // Format dates properly for FullCalendar
            $startDate = $r->reservation_date ? Carbon::parse($r->reservation_date)->format('Y-m-d') : date('Y-m-d');
            $startTime = $r->start_time ? substr($r->start_time, 0, 5) : '00:00'; // Get HH:mm format
            $endTime = $r->end_time ? substr($r->end_time, 0, 5) : '00:00'; // Get HH:mm format
            
            $start = $startDate . 'T' . $startTime . ':00';
            $end = $startDate . 'T' . $endTime . ':00';

            // Set color based on status
            $color = '#007bff'; // Default blue
            if ($r->status == 'attended') $color = '#28a745'; // Green
            elseif ($r->status == 'cancelled') $color = '#dc3545'; // Red
            elseif ($r->status == 'pending') $color = '#ffc107'; // Yellow
            elseif ($r->status == 'missed') $color = '#6c757d'; // Gray

            return [
                'id' => $r->id,
                'title' => $clientName . ' — ' . $activityName,
                'start' => $start,
                'end' => $end,
                'color' => $color,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'status' => $r->status,
                    'client_type' => $r->client_type,
                    'activity_id' => $r->activity_id,
                    'member_id' => $r->member_id,
                    'non_member_id' => $r->non_member_id
                ]
            ];
        })->filter(); // Filter out any null values

        return response()->json($events->values()->all());
    }

    /**
     * Return available slots for an activity on a date
     * POST: activity_id, reservation_date, duration (mins), slot_interval (mins)
     */
    public function availableSlots(Request $request)
    {
        $data = $request->validate([
            'activity_id' => 'required|integer',
            'reservation_date' => 'required|date',
            'duration' => 'nullable|integer',
            'slot_interval' => 'nullable|integer',
            'branch_setting_id' => 'nullable|integer',
            'activity_trainer_id' => 'nullable|integer',
            'client_type' => 'nullable|in:member,non_member',
            'member_id' => 'nullable|integer',
        ]);

        $activity = GymActivity::find($data['activity_id']);
        if (!$activity) {
            return response()->json(['day_available' => false, 'message' => trans('sw.activity_not_found'), 'slots' => []], 422);
        }

        $resolved = $this->resolveActivityTrainerForBooking(
            $activity,
            $data['activity_trainer_id'] ?? null,
            $data['client_type'] ?? 'member',
            $data['member_id'] ?? null
        );

        if ($resolved['error']) {
            return response()->json(['day_available' => false, 'message' => $resolved['error'], 'slots' => []], 422);
        }

        $result = $this->availabilityService->getAvailableSlots(
            $activity,
            $resolved['trainer'],
            $data['reservation_date'],
            $data['duration'] ?? null,
            (int) ($data['slot_interval'] ?? 30)
        );

        return response()->json($result);
    }

    /**
     * AJAX overlap checker (basic)
     */
    public function checkOverlap(Request $request)
    {
        $data = $request->validate([
            'activity_id' => 'required|integer',
            'reservation_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'reservation_id' => 'nullable|integer', // For edit mode - exclude current reservation
            'activity_trainer_id' => 'nullable|integer',
            'client_type' => 'nullable|in:member,non_member',
            'member_id' => 'nullable|integer',
        ]);

        $activity = GymActivity::find($data['activity_id']);
        if (!$activity) {
            return response()->json(['conflict' => true, 'message' => trans('sw.activity_not_found')]);
        }

        $resolved = $this->resolveActivityTrainerForBooking(
            $activity,
            $data['activity_trainer_id'] ?? null,
            $data['client_type'] ?? 'member',
            $data['member_id'] ?? null
        );

        if ($resolved['error']) {
            return response()->json(['conflict' => true, 'message' => $resolved['error']]);
        }

        return response()->json($this->availabilityService->checkConflict(
            $activity,
            $resolved['trainer'],
            $data['reservation_date'],
            $data['start_time'],
            $data['end_time'],
            $data['reservation_id'] ?? null
        ));
    }

    /**
     * Quick AJAX create (used by booking panel)
     * Accepts same inputs as store, returns created reservation JSON
     */
    public function ajaxCreate(Request $request)
    {
        // Check permission
        if (!in_array('createReservation', (array)$this->user_sw->permissions ?? []) && !@$this->user_sw->is_super_user) {
            return response()->json(['success' => false, 'message' => trans('admin.permission_denied')], 403);
        }
        $inputs = $request->validate([
            'client_type' => 'required|in:member,non_member',
            'member_id' => 'nullable|integer',
            'non_member_id' => 'nullable|integer',
            'activity_id' => 'required|integer',
            'activity_trainer_id' => 'nullable|integer',
            'reservation_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'notes' => 'nullable|string',
        ]);

        $activity = GymActivity::find($inputs['activity_id']);
        if (!$activity) {
            return response()->json(['success' => false, 'message' => trans('sw.activity_not_found')], 422);
        }

        $resolved = $this->resolveActivityTrainerForBooking(
            $activity,
            $inputs['activity_trainer_id'] ?? null,
            $inputs['client_type'],
            $inputs['member_id'] ?? null
        );

        if ($resolved['error']) {
            return response()->json(['success' => false, 'message' => $resolved['error']], 422);
        }

        $conflict = $this->availabilityService->checkConflict(
            $activity,
            $resolved['trainer'],
            $inputs['reservation_date'],
            $inputs['start_time'],
            $inputs['end_time']
        );

        if ($conflict['conflict']) {
            return response()->json(['success' => false, 'message' => $conflict['message']], 422);
        }

        if (@$this->user_sw->branch_setting_id) {
            $inputs['branch_setting_id'] = $this->user_sw->branch_setting_id;
        }
        $inputs['activity_trainer_id'] = $resolved['trainer'] ? $resolved['trainer']->id : null;
        $inputs['trainer_id'] = $resolved['trainer'] ? $resolved['trainer']->trainer_id : null;

        $reservation = $this->ReservationRepository->create($inputs);

        return response()->json(['success'=>true,'data'=>$reservation]);
    }

    /**
     * Quick AJAX update (used by booking panel)
     * Updates existing reservation via AJAX
     */
    public function ajaxUpdate(Request $request, $id)
    {
        // Check permission
        if (!in_array('editReservation', (array)$this->user_sw->permissions ?? []) && !@$this->user_sw->is_super_user) {
            return response()->json(['success' => false, 'message' => trans('admin.permission_denied')], 403);
        }
        $inputs = $request->validate([
            'client_type' => 'required|in:member,non_member',
            'member_id' => 'nullable|integer',
            'non_member_id' => 'nullable|integer',
            'activity_id' => 'required|integer',
            'activity_trainer_id' => 'nullable|integer',
            'reservation_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'notes' => 'nullable|string',
        ]);

        $reservation = $this->ReservationRepository->find($id);
        if (!$reservation) {
            return response()->json(['success' => false, 'message' => trans('sw.reservation_not_found')], 404);
        }

        $activity = GymActivity::find($inputs['activity_id']);
        if (!$activity) {
            return response()->json(['success' => false, 'message' => trans('sw.activity_not_found')], 422);
        }

        $resolved = $this->resolveActivityTrainerForBooking(
            $activity,
            $inputs['activity_trainer_id'] ?? null,
            $inputs['client_type'],
            $inputs['member_id'] ?? null
        );

        if ($resolved['error']) {
            return response()->json(['success' => false, 'message' => $resolved['error']], 422);
        }

        $conflict = $this->availabilityService->checkConflict(
            $activity,
            $resolved['trainer'],
            $inputs['reservation_date'],
            $inputs['start_time'],
            $inputs['end_time'],
            (int) $id
        );

        if ($conflict['conflict']) {
            return response()->json(['success' => false, 'message' => $conflict['message']], 422);
        }

        $inputs['activity_trainer_id'] = $resolved['trainer'] ? $resolved['trainer']->id : null;
        $inputs['trainer_id'] = $resolved['trainer'] ? $resolved['trainer']->trainer_id : null;

        $reservation->update($inputs);

        return response()->json(['success' => true, 'data' => $reservation]);
    }

    /**
     * Get reservation data for editing
     */
    public function ajaxGet($id)
    {
        // Check permission (viewing reservation data requires edit or list permission)
        if (!in_array('editReservation', (array)$this->user_sw->permissions ?? []) && 
            !in_array('listReservation', (array)$this->user_sw->permissions ?? []) && 
            !@$this->user_sw->is_super_user) {
            return response()->json(['success' => false, 'message' => trans('admin.permission_denied')], 403);
        }
        $reservation = $this->ReservationRepository->find($id);
        if (!$reservation) {
            return response()->json(['success' => false, 'message' => trans('sw.reservation_not_found')], 404);
        }

        // Format reservation_date safely
        $reservationDate = null;
        if ($reservation->reservation_date) {
            if ($reservation->reservation_date instanceof \Carbon\Carbon) {
                $reservationDate = $reservation->reservation_date->format('Y-m-d');
            } elseif (is_string($reservation->reservation_date)) {
                try {
                    $reservationDate = Carbon::parse($reservation->reservation_date)->format('Y-m-d');
                } catch (\Exception $e) {
                    $reservationDate = $reservation->reservation_date;
                }
            } else {
                $reservationDate = $reservation->reservation_date;
            }
        }

        // Format times safely
        $startTime = $reservation->start_time ? substr($reservation->start_time, 0, 5) : '00:00';
        $endTime = $reservation->end_time ? substr($reservation->end_time, 0, 5) : '00:00';

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $reservation->id,
                'client_type' => $reservation->client_type,
                'member_id' => $reservation->member_id,
                'non_member_id' => $reservation->non_member_id,
                'activity_id' => $reservation->activity_id,
                'reservation_date' => $reservationDate,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'notes' => $reservation->notes,
                'status' => $reservation->status,
            ]
        ]);
    }

    public function confirm($id)
    {
        // Check permission
        if (!in_array('confirmReservation', (array)$this->user_sw->permissions ?? []) && !@$this->user_sw->is_super_user) {
            return response()->json(['success' => false, 'message' => trans('admin.permission_denied')], 403);
        }

        $record = $this->ReservationRepository->find($id);
        if (!$record) {
            return response()->json(['success' => false, 'message' => trans('sw.reservation_not_found')], 404);
        }
        $record->update(['status' => 'confirmed']);

        return response()->json(['success' => true, 'status' => 'confirmed']);
    }

    public function cancel($id)
    {
        // Check permission
        if (!in_array('cancelReservation', (array)$this->user_sw->permissions ?? []) && !@$this->user_sw->is_super_user) {
            return response()->json(['success' => false, 'message' => trans('admin.permission_denied')], 403);
        }

        $record = $this->ReservationRepository->find($id);
        if (!$record) {
            return response()->json(['success' => false, 'message' => trans('sw.reservation_not_found')], 404);
        }

        if ($record->status === 'attended') {
            $this->reverseAttendanceFromReservation($record);
        }

        $record->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        return response()->json(['success' => true, 'status' => 'cancelled']);
    }

    public function attend($id)
    {
        // Check permission
        if (!in_array('attendReservation', (array)$this->user_sw->permissions ?? []) && !@$this->user_sw->is_super_user) {
            return response()->json(['success' => false, 'message' => trans('admin.permission_denied')], 403);
        }

        $record = $this->ReservationRepository->find($id);
        if (!$record) {
            return response()->json(['success' => false, 'message' => trans('sw.reservation_not_found')], 404);
        }

        // Guard against double-counting if already attended
        $alreadyAttended = $record->status === 'attended';

        $record->update(['status' => 'attended']);

        if (!$alreadyAttended) {
            $this->recordAttendanceFromReservation($record);
        }

        return response()->json(['success' => true, 'status' => 'attended']);
    }

    /**
     * Deduct activity visit from subscription and create attendance log (GymNonMemberTime).
     * Mirrors GymMemberFrontController::memberActivityMembershipAttendees() (home page flow).
     *
     * Guards:
     *  - If a log row for this member+activity already exists today with attended_at set
     *    (home page already recorded it), we skip creating another log and skip incrementing
     *    visits, but we still mark the reservation as attended so the page reflects reality.
     *  - Stores source_reservation_id on the created log row so reversal can find it exactly.
     */
    private function recordAttendanceFromReservation(GymReservation $record): void
    {
        $activityId = $record->activity_id;
        if (!$activityId) return;

        $attendedAt = Carbon::now()->toDateTimeString();
        $branchId   = $this->user_sw->branch_setting_id ?? null;
        $staffId    = $this->user_sw->id ?? null;

        // ── Member: deduct from subscription + log ────────────────────────
        if ($record->member_id) {
            // If home page already recorded attendance for same member+activity today,
            // don't double-count visits or create a duplicate log row.
            $alreadyLoggedToday = GymNonMemberTime::where('member_id', $record->member_id)
                ->where('activity_id', $activityId)
                ->whereDate('attended_at', Carbon::today())
                ->whereNotNull('attended_at')
                ->whereNull('source_reservation_id') // only rows not already owned by a reservation
                ->exists();

            if ($alreadyLoggedToday) {
                return; // visits already decremented by home page; nothing to do
            }

            $membership = GymMemberSubscription::where('member_id', $record->member_id)
                ->where('status', TypeConstants::Active)
                ->first();

            if ($membership && is_array($membership->activities) && count($membership->activities) > 0) {
                $activityResult = [];
                $deducted = false;

                foreach ($membership->activities as $i => $activity) {
                    $activityResult[$i] = $activity;

                    if (!$deducted
                        && (int) ($activity['id'] ?? 0) === (int) $activityId
                        && (int) ($activity['training_times'] ?? 0) > (int) ($activity['visits'] ?? 0)
                    ) {
                        $activityResult[$i]['visits'] = (int) ($activity['visits'] ?? 0) + 1;
                        $deducted = true;

                        GymNonMemberTime::create([
                            'user_id'                => $staffId,
                            'member_id'              => $record->member_id,
                            'member_subscription_id' => $membership->id,
                            'activity_id'            => $activityId,
                            'date'                   => $attendedAt,
                            'attended_at'            => $attendedAt,
                            'branch_setting_id'      => $branchId,
                            'source_reservation_id'  => $record->id,
                        ]);
                    }
                }

                DB::table('sw_gym_member_subscription')
                    ->where('id', $membership->id)
                    ->update(['activities' => json_encode($activityResult), 'updated_at' => now()]);
            }
        }

        // ── Non-member: log only ──────────────────────────────────────────
        if ($record->non_member_id) {
            $alreadyLoggedToday = GymNonMemberTime::where('non_member_id', $record->non_member_id)
                ->where('activity_id', $activityId)
                ->whereDate('attended_at', Carbon::today())
                ->whereNotNull('attended_at')
                ->whereNull('source_reservation_id')
                ->exists();

            if (!$alreadyLoggedToday) {
                GymNonMemberTime::create([
                    'user_id'               => $staffId,
                    'non_member_id'         => $record->non_member_id,
                    'activity_id'           => $activityId,
                    'date'                  => $attendedAt,
                    'attended_at'           => $attendedAt,
                    'branch_setting_id'     => $branchId,
                    'source_reservation_id' => $record->id,
                ]);
            }
        }
    }

    /**
     * Reverse the attendance recorded when this reservation was marked attended:
     * delete the linked log row and restore one visit on the member's subscription.
     * Called before changing status away from 'attended' (cancel / missed).
     */
    private function reverseAttendanceFromReservation(GymReservation $record): void
    {
        $activityId = $record->activity_id;
        if (!$activityId) return;

        // Find the log row that was created by this reservation (linked via source_reservation_id)
        $logRow = GymNonMemberTime::where('source_reservation_id', $record->id)->first();

        if (!$logRow) return; // attendance was recorded from home page — don't touch it

        $logRow->delete();

        // Restore one visit on the member's subscription
        if ($record->member_id) {
            $membership = GymMemberSubscription::where('member_id', $record->member_id)
                ->where('status', TypeConstants::Active)
                ->first();

            if ($membership && is_array($membership->activities) && count($membership->activities) > 0) {
                $activityResult = [];
                $restored = false;

                foreach ($membership->activities as $i => $activity) {
                    $activityResult[$i] = $activity;

                    if (!$restored && (int) ($activity['id'] ?? 0) === (int) $activityId) {
                        $currentVisits = (int) ($activity['visits'] ?? 0);
                        $activityResult[$i]['visits'] = max(0, $currentVisits - 1);
                        $restored = true;
                    }
                }

                DB::table('sw_gym_member_subscription')
                    ->where('id', $membership->id)
                    ->update(['activities' => json_encode($activityResult), 'updated_at' => now()]);
            }
        }
    }

    public function missed($id)
    {
        // Check permission
        if (!in_array('markMissedReservation', (array)$this->user_sw->permissions ?? []) && !@$this->user_sw->is_super_user) {
            return response()->json(['success' => false, 'message' => trans('admin.permission_denied')], 403);
        }

        $record = $this->ReservationRepository->find($id);
        if (!$record) {
            return response()->json(['success' => false, 'message' => trans('sw.reservation_not_found')], 404);
        }

        if ($record->status === 'attended') {
            $this->reverseAttendanceFromReservation($record);
        }

        $record->update(['status' => 'missed']);

        return response()->json(['success' => true, 'status' => 'missed']);
    }
}


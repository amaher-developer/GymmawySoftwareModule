<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Software\Http\Controllers\Front\GymGenericFrontController;
use Modules\Software\Repositories\GymReservationRepository;
use Modules\Software\Http\Requests\GymReservationRequest;
use Modules\Software\Models\GymReservation;
use Modules\Software\Models\GymActivity;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymNonMember;
use Modules\Software\Models\GymReservationUsage;
use Illuminate\Container\Container as Application;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GymReservationFrontController extends GymGenericFrontController
{
    public $ReservationRepository;

    public function __construct()
    {
        parent::__construct();

        $this->ReservationRepository =
            new GymReservationRepository(new Application);

        $this->ReservationRepository =
            $this->ReservationRepository->branch();
    }

    public function index()
    {
        $title = trans('sw.reservations');

        $this->request_array = ['search', 'activity_id', 'member_id', 'non_member_id', 'status', 'date'];
        foreach ($this->request_array as $item)
            $$item = request()->has($item) ? request()->$item : false;

        if (request('trashed')) {
            $reservations = $this->ReservationRepository->onlyTrashed()->orderBy('id', 'DESC');
        } else {
            $reservations = $this->ReservationRepository->orderBy('id', 'DESC');
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
        
        // Set records - if paginated, use the paginator itself (it has items() method for iteration)
        // If not paginated, use the collection
        $records = $reservations;

        return view('software::Front.reservation_front_list', compact('reservations', 'records', 'title', 'total', 'search_query', 'activities'));
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
        ]);

        $activity = GymActivity::find($data['activity_id']);
        
        // Get duration - priority: request > activity reservation_duration > activity duration_minutes > default
        $duration = (int)($data['duration'] ?? ($activity->reservation_duration ?? ($activity->duration_minutes ?? 60)));
        $interval = (int)($data['slot_interval'] ?? 30);
        
        // Get reservation limit (0 or null means unlimited, >0 means max reservations at same time)
        $reservationLimit = (int)($activity->reservation_limit ?? 0);
        $hasLimit = $reservationLimit > 0;

        $date = Carbon::parse($data['reservation_date'])->format('Y-m-d');
        $dateCarbon = Carbon::parse($data['reservation_date']);
        $dayOfWeek = $dateCarbon->dayOfWeek; // 0 (Sunday) to 6 (Saturday)

        // Check if this day is available for reservations
        // If reservation_details is null, all days and times are available
        $reservationDetails = $activity->reservation_details ?? null;
        $isDayAvailable = true;
        $dayWorkHours = null;

        if ($reservationDetails !== null && isset($reservationDetails['work_days']) && is_array($reservationDetails['work_days']) && count($reservationDetails['work_days']) > 0) {
            // If reservation_details exists and has work_days configured, check if this day is available
            if (isset($reservationDetails['work_days'][$dayOfWeek])) {
                $dayConfig = $reservationDetails['work_days'][$dayOfWeek];
                $isDayAvailable = isset($dayConfig['status']) && $dayConfig['status'] == 1;
                
                if ($isDayAvailable && isset($dayConfig['start']) && isset($dayConfig['end'])) {
                    $dayWorkHours = [
                        'start' => $dayConfig['start'],
                        'end' => $dayConfig['end']
                    ];
                }
            } else {
                // Day not configured in work_days = not available (only when reservation_details is configured)
                $isDayAvailable = false;
            }
        }
        // If reservation_details is null or empty, all days are available (default behavior - reservation anytime)

        // If day is not available, return empty slots
        if (!$isDayAvailable) {
            return response()->json([
                'date' => $date,
                'duration' => $duration,
                'interval' => $interval,
                'reservation_limit' => $reservationLimit,
                'has_limit' => $hasLimit,
                'day_available' => false,
                'message' => trans('sw.day_not_available_for_reservation'),
                'slots' => []
            ]);
        }

        // Determine working hours for this day
        if ($dayWorkHours) {
            // Use day-specific working hours from reservation_details
            $startOfDay = $dayWorkHours['start'];
            $endOfDay = $dayWorkHours['end'];
        } else {
            // Default working hours (adjust to your branch settings if available)
            $startOfDay = '08:00';
            $endOfDay = '20:00';
        }

        $cursor = Carbon::parse("$date $startOfDay");
        $endDay = Carbon::parse("$date $endOfDay");
        $slots = [];

        while ($cursor->copy()->addMinutes($duration) <= $endDay) {
            $s = $cursor->format('H:i');
            $e = $cursor->copy()->addMinutes($duration)->format('H:i');
            $slots[] = ['start_time' => $s, 'end_time' => $e];
            $cursor->addMinutes($interval);
        }

        // Load existing reservations for that activity & date
        // Exclude cancelled and missed reservations
        $existing = GymReservation::where('activity_id', $data['activity_id'])
            ->whereDate('reservation_date', $date)
            ->whereNotIn('status', ['cancelled', 'missed'])
            ->get()
            ->map(function($r){
                // Ensure time format is correct (H:i)
                $start = $r->start_time;
                $end = $r->end_time;
                
                // If time contains seconds or other data, extract only H:i
                if ($start && strlen($start) > 5) {
                    $start = substr($start, 0, 5);
                }
                if ($end && strlen($end) > 5) {
                    $end = substr($end, 0, 5);
                }
                
                return [
                    'start' => $start,
                    'end' => $end,
                    'id' => $r->id
                ];
            })
            ->filter(function($r) {
                return $r['start'] && $r['end'];
            })
            ->toArray();

        // Count overlapping reservations for a given time slot
        $countOverlaps = function($s, $e) use ($existing) {
            $count = 0;
            foreach ($existing as $ex) {
                // Ensure time format is correct (H:i)
                $exStart = $ex['start'];
                $exEnd = $ex['end'];
                $slotStart = $s;
                $slotEnd = $e;
                
                // Extract only H:i format if there's trailing data
                if (strlen($exStart) > 5) {
                    $exStart = substr($exStart, 0, 5);
                }
                if (strlen($exEnd) > 5) {
                    $exEnd = substr($exEnd, 0, 5);
                }
                if (strlen($slotStart) > 5) {
                    $slotStart = substr($slotStart, 0, 5);
                }
                if (strlen($slotEnd) > 5) {
                    $slotEnd = substr($slotEnd, 0, 5);
                }
                
                try {
                    $aStart = Carbon::createFromFormat('H:i', $exStart);
                    $aEnd = Carbon::createFromFormat('H:i', $exEnd);
                    $sTime = Carbon::createFromFormat('H:i', $slotStart);
                    $eTime = Carbon::createFromFormat('H:i', $slotEnd);
                    
                    // Check if times overlap
                    // Two time slots overlap if: start1 < end2 && start2 < end1
                    if ($sTime->lt($aEnd) && $eTime->gt($aStart)) {
                        $count++;
                    }
                } catch (\Exception $e) {
                    // Skip invalid time formats
                    continue;
                }
            }
            return $count;
        };

        $result = [];
        foreach ($slots as $slot) {
            $overlapCount = $countOverlaps($slot['start_time'], $slot['end_time']);
            
            // Determine availability based on reservation limit
            if ($hasLimit) {
                // If limit is set, check if we've reached it
                $isAvailable = $overlapCount < $reservationLimit;
                $remainingSlots = max(0, $reservationLimit - $overlapCount);
            } else {
                // No limit - always available (unlimited reservations allowed)
                $isAvailable = true;
                $remainingSlots = null; // null means unlimited
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

        return response()->json([
            'date' => $date,
            'duration' => $duration,
            'interval' => $interval,
            'reservation_limit' => $reservationLimit,
            'has_limit' => $hasLimit,
            'day_available' => true,
            'work_hours' => [
                'start' => $startOfDay,
                'end' => $endOfDay
            ],
            'slots' => $result
        ]);
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
        ]);

        $activity = GymActivity::find($data['activity_id']);
        if (!$activity) {
            return response()->json([
                'conflict' => true,
                'message' => trans('sw.activity_not_found')
            ]);
        }

        // Check if day is available for reservations
        // If reservation_details is null, all days and times are available (reservation anytime)
        $dateCarbon = Carbon::parse($data['reservation_date']);
        $dayOfWeek = $dateCarbon->dayOfWeek;
        $reservationDetails = $activity->reservation_details ?? null;
        
        if ($reservationDetails !== null && isset($reservationDetails['work_days']) && is_array($reservationDetails['work_days']) && count($reservationDetails['work_days']) > 0) {
            // Only check restrictions if reservation_details is configured
            if (isset($reservationDetails['work_days'][$dayOfWeek])) {
                $dayConfig = $reservationDetails['work_days'][$dayOfWeek];
                $isDayAvailable = isset($dayConfig['status']) && $dayConfig['status'] == 1;
                
                if (!$isDayAvailable) {
                    return response()->json([
                        'conflict' => true,
                        'message' => trans('sw.day_not_available_for_reservation')
                    ]);
                }
                
                // Check if time is within working hours
                if (isset($dayConfig['start']) && isset($dayConfig['end'])) {
                    $startTime = substr($data['start_time'], 0, 5);
                    $endTime = substr($data['end_time'], 0, 5);
                    $dayStart = substr($dayConfig['start'], 0, 5);
                    $dayEnd = substr($dayConfig['end'], 0, 5);
                    
                    if ($startTime < $dayStart || $endTime > $dayEnd || $startTime >= $dayEnd) {
                        return response()->json([
                            'conflict' => true,
                            'message' => trans('sw.time_outside_working_hours', ['start' => $dayStart, 'end' => $dayEnd])
                        ]);
                    }
                }
            } else {
                // Day not configured = not available (only when reservation_details is configured)
                return response()->json([
                    'conflict' => true,
                    'message' => trans('sw.day_not_available_for_reservation')
                ]);
            }
        }
        // If reservation_details is null or empty, skip all restrictions (allow reservation anytime)

        $query = GymReservation::where('activity_id', $data['activity_id'])
            ->whereDate('reservation_date', $data['reservation_date']);

        // Exclude current reservation if editing
        if (!empty($data['reservation_id'])) {
            $query->where('id', '!=', $data['reservation_id']);
        }

        $exists = $query->get()
            ->filter(function($r) use ($data) {
                $s = substr($data['start_time'], 0, 5);
                $e = substr($data['end_time'], 0, 5);
                $rStart = substr($r->start_time ?? '', 0, 5);
                $rEnd = substr($r->end_time ?? '', 0, 5);
                
                if (!$rStart || !$rEnd) return false;
                
                return (
                    ($rStart >= $s && $rStart < $e) ||
                    ($rEnd > $s && $rEnd <= $e) ||
                    ($rStart <= $s && $rEnd >= $e)
                );
            })->count() > 0;

        return response()->json([
            'conflict' => $exists,
            'message' => $exists ? trans('sw.time_conflict_detected') : trans('sw.time_slot_available')
        ]);
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
            'reservation_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'notes' => 'nullable|string',
        ]);

        $activity = GymActivity::find($inputs['activity_id']);
        if (!$activity) {
            return response()->json(['success' => false, 'message' => trans('sw.activity_not_found')], 422);
        }

        // Check if day is available for reservations
        // If reservation_details is null, all days and times are available (reservation anytime)
        $dateCarbon = Carbon::parse($inputs['reservation_date']);
        $dayOfWeek = $dateCarbon->dayOfWeek;
        $reservationDetails = $activity->reservation_details ?? null;
        
        if ($reservationDetails !== null && isset($reservationDetails['work_days']) && is_array($reservationDetails['work_days']) && count($reservationDetails['work_days']) > 0) {
            // Only check restrictions if reservation_details is configured
            if (isset($reservationDetails['work_days'][$dayOfWeek])) {
                $dayConfig = $reservationDetails['work_days'][$dayOfWeek];
                $isDayAvailable = isset($dayConfig['status']) && $dayConfig['status'] == 1;
                
                if (!$isDayAvailable) {
                    return response()->json(['success' => false, 'message' => trans('sw.day_not_available_for_reservation')], 422);
                }
                
                // Check if time is within working hours
                if (isset($dayConfig['start']) && isset($dayConfig['end'])) {
                    $startTime = substr($inputs['start_time'], 0, 5);
                    $endTime = substr($inputs['end_time'], 0, 5);
                    $dayStart = substr($dayConfig['start'], 0, 5);
                    $dayEnd = substr($dayConfig['end'], 0, 5);
                    
                    if ($startTime < $dayStart || $endTime > $dayEnd || $startTime >= $dayEnd) {
                        return response()->json([
                            'success' => false,
                            'message' => trans('sw.time_outside_working_hours', ['start' => $dayStart, 'end' => $dayEnd])
                        ], 422);
                    }
                }
            } else {
                // Day not configured = not available (only when reservation_details is configured)
                return response()->json(['success' => false, 'message' => trans('sw.day_not_available_for_reservation')], 422);
            }
        }
        // If reservation_details is null or empty, skip all restrictions (allow reservation anytime)

        if (@$this->user_sw->branch_setting_id) {
            $inputs['branch_setting_id'] = $this->user_sw->branch_setting_id;
        }

        // Check reservation limit
        $reservationLimit = (int)($activity->reservation_limit ?? 0);
        if ($reservationLimit > 0) {
            // Count overlapping reservations
            $overlapCount = GymReservation::where('activity_id', $inputs['activity_id'])
                ->whereDate('reservation_date', $inputs['reservation_date'])
                ->whereNotIn('status', ['cancelled', 'missed'])
                ->get()
                ->filter(function ($r) use ($inputs) {
                    $s = substr($inputs['start_time'], 0, 5);
                    $e = substr($inputs['end_time'], 0, 5);
                    $rStart = substr($r->start_time ?? '', 0, 5);
                    $rEnd = substr($r->end_time ?? '', 0, 5);
                    
                    if (!$rStart || !$rEnd) return false;
                    
                    try {
                        $aStart = Carbon::createFromFormat('H:i', $rStart);
                        $aEnd = Carbon::createFromFormat('H:i', $rEnd);
                        $sTime = Carbon::createFromFormat('H:i', $s);
                        $eTime = Carbon::createFromFormat('H:i', $e);
                        
                        return ($sTime->lt($aEnd) && $eTime->gt($aStart));
                    } catch (\Exception $e) {
                        return false;
                    }
                })->count();
            
            if ($overlapCount >= $reservationLimit) {
                return response()->json([
                    'success' => false,
                    'message' => trans('sw.reservation_limit_reached', ['limit' => $reservationLimit])
                ], 422);
            }
        }

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

        // Check if day is available for reservations
        $dateCarbon = Carbon::parse($inputs['reservation_date']);
        $dayOfWeek = $dateCarbon->dayOfWeek;
        $reservationDetails = $activity->reservation_details ?? null;
        
        if ($reservationDetails !== null && isset($reservationDetails['work_days']) && is_array($reservationDetails['work_days']) && count($reservationDetails['work_days']) > 0) {
            if (isset($reservationDetails['work_days'][$dayOfWeek])) {
                $dayConfig = $reservationDetails['work_days'][$dayOfWeek];
                $isDayAvailable = isset($dayConfig['status']) && $dayConfig['status'] == 1;
                
                if (!$isDayAvailable) {
                    return response()->json(['success' => false, 'message' => trans('sw.day_not_available_for_reservation')], 422);
                }
                
                // Check if time is within working hours
                if (isset($dayConfig['start']) && isset($dayConfig['end'])) {
                    $startTime = substr($inputs['start_time'], 0, 5);
                    $endTime = substr($inputs['end_time'], 0, 5);
                    $dayStart = substr($dayConfig['start'], 0, 5);
                    $dayEnd = substr($dayConfig['end'], 0, 5);
                    
                    if ($startTime < $dayStart || $endTime > $dayEnd || $startTime >= $dayEnd) {
                        return response()->json([
                            'success' => false,
                            'message' => trans('sw.time_outside_working_hours', ['start' => $dayStart, 'end' => $dayEnd])
                        ], 422);
                    }
                }
            } else {
                return response()->json(['success' => false, 'message' => trans('sw.day_not_available_for_reservation')], 422);
            }
        }

        // Check for conflicts (exclude current reservation)
        $reservationLimit = (int)($activity->reservation_limit ?? 0);
        if ($reservationLimit > 0) {
            $overlapCount = GymReservation::where('activity_id', $inputs['activity_id'])
                ->whereDate('reservation_date', $inputs['reservation_date'])
                ->where('id', '!=', $id)
                ->whereNotIn('status', ['cancelled', 'missed'])
                ->get()
                ->filter(function ($r) use ($inputs) {
                    $s = substr($inputs['start_time'], 0, 5);
                    $e = substr($inputs['end_time'], 0, 5);
                    $rStart = substr($r->start_time ?? '', 0, 5);
                    $rEnd = substr($r->end_time ?? '', 0, 5);
                    
                    if (!$rStart || !$rEnd) return false;
                    
                    try {
                        $aStart = Carbon::createFromFormat('H:i', $rStart);
                        $aEnd = Carbon::createFromFormat('H:i', $rEnd);
                        $sTime = Carbon::createFromFormat('H:i', $s);
                        $eTime = Carbon::createFromFormat('H:i', $e);
                        
                        return ($sTime->lt($aEnd) && $eTime->gt($aStart));
                    } catch (\Exception $e) {
                        return false;
                    }
                })->count();
            
            if ($overlapCount >= $reservationLimit) {
                return response()->json([
                    'success' => false,
                    'message' => trans('sw.reservation_limit_reached', ['limit' => $reservationLimit])
                ], 422);
            }
        } else {
            // Check for simple conflicts
            $exists = GymReservation::where('activity_id', $inputs['activity_id'])
                ->whereDate('reservation_date', $inputs['reservation_date'])
                ->where('id', '!=', $id)
                ->whereNotIn('status', ['cancelled', 'missed'])
                ->get()
                ->filter(function($r) use ($inputs) {
                    $s = substr($inputs['start_time'], 0, 5);
                    $e = substr($inputs['end_time'], 0, 5);
                    $rStart = substr($r->start_time ?? '', 0, 5);
                    $rEnd = substr($r->end_time ?? '', 0, 5);
                    
                    if (!$rStart || !$rEnd) return false;
                    
                    try {
                        $aStart = Carbon::createFromFormat('H:i', $rStart);
                        $aEnd = Carbon::createFromFormat('H:i', $rEnd);
                        $sTime = Carbon::createFromFormat('H:i', $s);
                        $eTime = Carbon::createFromFormat('H:i', $e);
                        
                        return ($sTime->lt($aEnd) && $eTime->gt($aStart));
                    } catch (\Exception $e) {
                        return false;
                    }
                })->count() > 0;

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => trans('sw.slot_conflict')
                ], 422);
            }
        }

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
        $record->update(['status' => 'cancelled','cancelled_at'=>now()]);

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
        $record->update(['status' => 'attended']);

        return response()->json(['success' => true, 'status' => 'attended']);
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
        $record->update(['status' => 'missed']);

        return response()->json(['success' => true, 'status' => 'missed']);
    }
}


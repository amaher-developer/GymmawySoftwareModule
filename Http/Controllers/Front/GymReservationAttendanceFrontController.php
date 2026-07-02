<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Software\Http\Controllers\Front\GymGenericFrontController;
use Modules\Software\Repositories\GymReservationRepository;
use Modules\Software\Models\GymReservation;
use Modules\Software\Models\GymReservationUsage;
use Modules\Software\Models\GymMemberSubscription;
use Modules\Software\Models\GymNonMemberTime;
use Modules\Software\Classes\TypeConstants;
use Illuminate\Container\Container as Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GymReservationAttendanceFrontController extends GymGenericFrontController
{
    public $ReservationRepository;

    public function __construct()
    {
        parent::__construct();

        $this->ReservationRepository =
            new GymReservationRepository(new Application);

    }

    public function attendForm($id)
    {
        $reservation = $this->ReservationRepository->find($id);
        $title = trans('sw.attendance');

        return view('software::Front.reservation_attendance_front', compact('reservation', 'title'));
    }

    public function attend(Request $request, $id)
    {
        $reservation = $this->ReservationRepository->find($id);

        $alreadyAttended = $reservation->status === 'attended';

        GymReservationUsage::create([
            'reservation_id' => $reservation->id,
            'client_type'    => $reservation->client_type,
            'member_id'      => $reservation->member_id,
            'non_member_id'  => $reservation->non_member_id,
            'activity_id'    => $reservation->activity_id,
            'staff_id'       => $this->user_sw->id ?? null,
            'used_at'        => Carbon::now(),
        ]);

        $reservation->update(['status' => 'attended']);

        if (!$alreadyAttended) {
            $this->recordAttendanceFromReservation($reservation);
        }

        session()->flash('sweet_flash_message', [
            'title'   => trans('admin.done'),
            'message' => trans('sw.attendance_recorded'),
            'type'    => 'success'
        ]);

        return redirect()->route('sw.listReservation');
    }

    /**
     * Deduct activity visit from subscription and create attendance log (GymNonMemberTime).
     *
     * Guards:
     *  - If home page already recorded attendance for same member+activity today,
     *    skip creating another log and skip decrementing visits (prevent double-count).
     *  - Stores source_reservation_id on the created log row so reversal can find it.
     */
    private function recordAttendanceFromReservation(GymReservation $record): void
    {
        $activityId = $record->activity_id;
        if (!$activityId) return;

        $attendedAt = Carbon::now()->toDateTimeString();
        $branchId   = $this->user_sw->branch_setting_id ?? null;
        $staffId    = $this->user_sw->id ?? null;

        if ($record->member_id) {
            $alreadyLoggedToday = GymNonMemberTime::where('member_id', $record->member_id)
                ->where('activity_id', $activityId)
                ->whereDate('attended_at', Carbon::today())
                ->whereNotNull('attended_at')
                ->whereNull('source_reservation_id')
                ->exists();

            if ($alreadyLoggedToday) {
                return;
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
}

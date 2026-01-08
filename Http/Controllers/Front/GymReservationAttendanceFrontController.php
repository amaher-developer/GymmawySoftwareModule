<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Software\Http\Controllers\Front\GymGenericFrontController;
use Modules\Software\Repositories\GymReservationRepository;
use Modules\Software\Models\GymReservation;
use Modules\Software\Models\GymReservationUsage;
use Illuminate\Container\Container as Application;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GymReservationAttendanceFrontController extends GymGenericFrontController
{
    public $ReservationRepository;

    public function __construct()
    {
        parent::__construct();

        $this->ReservationRepository =
            new GymReservationRepository(new Application);

        // Repository branch filtering removed from constructor - now applied per query
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

        session()->flash('sweet_flash_message', [
            'title'   => trans('admin.done'),
            'message' => trans('sw.attendance_recorded'),
            'type'    => 'success'
        ]);

        return redirect()->route('sw.listReservation');
    }
}



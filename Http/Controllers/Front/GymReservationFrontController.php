<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Software\Classes\TypeConstants;
use Modules\Software\Http\Requests\GymActivityRequest;
use Modules\Software\Models\GymActivity;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymReservation;
use Modules\Software\Repositories\GymReservationRepository;
use Carbon\Carbon;
use Illuminate\Container\Container as Application;


class GymReservationFrontController extends GymGenericFrontController
{
    public $ReservationRepository;
    public $fileName;

    public function __construct()
    {
        parent::__construct();
        $this->ReservationRepository = new GymReservationRepository(new Application);
        $this->ReservationRepository = $this->ReservationRepository->branch();
    }


    public function index()
    {
//        $this->mainSettings->reservation_details['work_days']
        $title = trans('sw.reservations');

        $reservations = GymReservation::branch()->select('time_slot', 'date', \DB::raw('count(*) as total'))
                        ->where('date', '>=', Carbon::now()->toDateString())
                        ->groupBy('time_slot','date')
                        ->orderBy('time_slot', 'ASC')
                        ->get();

        $total = $reservations->count();

        return view('software::Front.reservation_front_list', compact('reservations','title', 'total'));
    }




    public function create()
    {
        $title = trans('sw.activity_add');
        return view('software::Front.activity_front_form', ['activity' => new GymActivity(),'title'=>$title]);
    }

    public function store(GymActivityRequest $request)
    {
        $activity_inputs = ($request->except(['_token']));
        $this->ReservationRepository->create($activity_inputs);
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);

        $notes = str_replace(':name', $activity_inputs['name_'.$this->lang], trans('sw.add_activity'));
        $this->userLog($notes, TypeConstants::CreateActivity);
        return redirect(route('sw.listActivity'));
    }


    public function destroy($id)
    {
        $activity =$this->ReservationRepository->withTrashed()->find($id);
        if($activity->trashed())
        {
            $activity->restore();
        }
        else
        {
            $activity->delete();

            $notes = str_replace(':name', $activity['name'], trans('sw.delete_activity'));
            $this->userLog($notes, TypeConstants::DeleteActivity);
        }
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listActivity'));
    }

    public function getReservationMemberAjax(){
        $member_id = request('member_id');
        if($member_id){
            $member = GymMember::branch()->with(['member_subscription_info.subscription'
                        , 'gym_reservations' => function($q){
                            return $q->where('date', '>=', Carbon::now()->toDateString())
                                    ->orderBy('date', 'asc')
                                    ->orderBy('time_slot', 'asc');}])
                        ->where('code', $member_id)->first();
            return $member;
        }
        return [];
    }

    public function deleteReservationMemberAjax(){
        $id = request('id');
        $time = request('time');

        if($id){
            GymReservation::branch()->where('id', $id)->forceDelete();
            $countCheck = GymReservation::branch()->where('date', Carbon::now()->toDateString())->where('time_slot', @$time)->count();
            if($countCheck){
                return 'reload';
            }
            return '1';
        }
        return '0';
    }
    public function createReservationMemberAjax(){
        $member_id = request('member_id');
        $selected_date = request('selected_date');
        $selected_time = request('selected_time');

        if($member_id && $selected_date && $selected_time){
            $reservation = GymReservation::branch()->where('member_id', $member_id)->where( 'date' , Carbon::parse($selected_date)->toDateString())->where('time_slot', $selected_time)->first();
            if($reservation){
                return 'exist';
            }else{
                $reservation = GymReservation::create(['user_id' => $this->user_sw->id, 'member_id' => $member_id, 'date' => Carbon::parse($selected_date)->toDateString(), 'time_slot' => $selected_time, 'branch_setting_id' => @$this->user_sw->branch_setting_id]);
                $countCheck = GymReservation::branch()->where('date',  Carbon::parse($selected_date)->toDateString())->where('time_slot', @$selected_time)->count();
                if($countCheck >= (int)$this->mainSettings->reservation_details['max_member_per_slot']){
                    return 'reload';
                }
                return $reservation->id;
            }
        }
        return '0';
    }

}

<?php

namespace Modules\Software\Http\Controllers\Api;



use Modules\Software\Http\Resources\PTClassResource;
use Modules\Software\Http\Resources\PTContentResource;
use Modules\Software\Http\Resources\PTResource;
use Modules\Software\Models\GymPotentialMember;
use Modules\Software\Models\GymPTClass;
use Modules\Software\Models\GymPTMember;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class GymPTApiController extends GymGenericApiController
{
    public function trainings(){
        $trainings = GymPTClass::with(['pt_subscription.pt_trainers']);
//        if(@request('device_type'))
            $trainings = $trainings->where('is_mobile', 1);
//        else
//            $trainings = $trainings->where('is_web', 1);
        $trainings = $trainings->orderBy("id", "desc")->paginate($this->limit);
        $this->getPaginateAttribute($trainings);
        $this->return['result']['trainings'] =  $trainings ?  PTResource::collection($trainings) : [];
        return $this->successResponse();
    }
    public function training($id){
        $training = GymPTClass::with([
            'pt_subscription',
            'pt_subscription.classes.activeClassTrainers.trainer',
            'pt_subscription_trainer.pt_trainer',
            'activeClassTrainers.trainer',
        ])->where("id", $id)->first();
        $this->return['result']['training'] = $training ? new PTContentResource($training) : '';

        return $this->successResponse();
    }
    public function trainingReservation($id){
        $member_id = @$this->api_member->id;
        if(!$member_id){
            if (!$this->validateApiRequest(['name', 'phone'])) return $this->response;
        }
        if (!$this->validateApiRequest(['id'])) return $this->response;

        $class = GymPTClass::with(['pt_subscription'])->where("id", $id)->first();

        GymPotentialMember::updateOrCreate(
            ['pt_class_id' => $id, 'pt_subscription_id' => @$class->pt_subscription->id, 'name' => @request('name'), 'phone' => @request('phone')]
        ,['pt_class_id' => $id, 'pt_subscription_id' => @$class->pt_subscription->id, 'name' => @request('name'), 'phone' => @request('phone')]
        );

        $this->message = trans('sw.reserved_success_msg');
        return $this->successResponse();
    }
    public function trainingClasses(){
        $date = request('date') ?: Carbon::today()->toDateString();
        $parsedDate = Carbon::parse($date);
        $dayOfWeek  = $parsedDate->dayOfWeek; // 0=Sunday … 6=Saturday

        $authUser = Auth::guard('api')->user();
        if (!$authUser) {
            $this->return['result']['classes'] = [];
            return $this->successResponse();
        }

        // Load all active PT memberships for this member that cover the requested date
        $ptMembers = GymPTMember::with([
                'class.activeClassTrainers.trainer',   // new schema
                'class.pt_subscription',
                'legacyClass.pt_subscription_trainer.pt_trainer', // old schema fallback
                'legacyClass.pt_subscription',
                'trainer',                             // old direct trainer link
            ])
            ->where('member_id', $authUser->id)
            ->where(function ($q) use ($parsedDate) {
                $dateStr = $parsedDate->toDateString();
                $q->whereDate('joining_date', '<=', $dateStr)
                  ->whereDate('expire_date',  '>=', $dateStr);
            })
            ->get();

        $records = [];

        foreach ($ptMembers as $ptMember) {
            // ── Try new schema first (class_id → GymPTClass) ─────────────────
            $ptClass = $ptMember->class ?? null;

            // ── Fall back to old schema (pt_class_id) ────────────────────────
            if (!$ptClass) {
                $ptClass = $ptMember->legacyClass ?? null;
            }

            if (!$ptClass) {
                continue;
            }

            // Check if this class runs on the requested day-of-week
            $workDays = $ptClass->schedule['work_days'] ?? [];
            $daySlot  = $workDays[$dayOfWeek] ?? null;

            if (!$daySlot || empty($daySlot['status'])) {
                continue;
            }

            $subscriptionName = $ptClass->pt_subscription->name ?? '';
            $startTime        = Carbon::parse($daySlot['start'] ?? '00:00')->format('g:i A');

            // ── Build trainer rows for this class ────────────────────────────
            $trainers = $ptClass->activeClassTrainers ?? collect([]);

            if ($trainers->isEmpty()) {
                // Old schema: single trainer stored directly on ptMember
                $trainerName  = $ptMember->trainer->name ?? '';
                $trainerImage = $ptMember->trainer->image ?? '';

                $records[] = [
                    'title'         => $subscriptionName,
                    'trainer_name'  => $trainerName,
                    'trainer_image' => $trainerImage,
                    'period'        => $startTime,
                    'date'          => $date,
                ];
            } else {
                foreach ($trainers as $classTrainer) {
                    $trainerName  = $classTrainer->trainer->name  ?? '';
                    $trainerImage = $classTrainer->trainer->image ?? '';

                    // Trainer may have its own schedule; fall back to class schedule
                    $trainerWorkDays = [];
                    if (is_array($classTrainer->schedule)) {
                        $trainerWorkDays = $classTrainer->schedule['work_days'] ?? [];
                    }
                    $trainerDaySlot = $trainerWorkDays[$dayOfWeek] ?? $daySlot;

                    if (!empty($trainerWorkDays) && empty($trainerDaySlot['status'])) {
                        continue; // This trainer doesn't work on this day
                    }

                    $trainerStart = Carbon::parse($trainerDaySlot['start'] ?? $daySlot['start'] ?? '00:00')->format('g:i A');

                    $records[] = [
                        'title'         => $subscriptionName,
                        'trainer_name'  => $trainerName,
                        'trainer_image' => $trainerImage,
                        'period'        => $trainerStart,
                        'date'          => $date,
                    ];
                }
            }
        }

        $this->return['result']['classes'] = $records;

        return $this->successResponse();
    }
}


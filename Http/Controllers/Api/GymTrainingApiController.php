<?php

namespace Modules\Software\Http\Controllers\Api;



use Carbon\Carbon;
use Modules\Software\Http\Resources\PTContentResource;
use Modules\Software\Http\Resources\PTResource;
use Modules\Software\Http\Resources\TrainerPlanContentResource;
use Modules\Software\Http\Resources\TrainerTrackContentResource;
use Modules\Software\Http\Resources\TrainingPlanResource;
use Modules\Software\Http\Resources\TrainingTrackResource;
use Modules\Software\Models\GymPotentialMember;
use Modules\Software\Models\GymPTClass;
use Modules\Software\Models\GymTrainingMember;
use Modules\Software\Models\GymTrainingMemberLog;
use Modules\Software\Models\GymTrainingTrack;
use Illuminate\Support\Facades\Auth;

class GymTrainingApiController extends GymGenericApiController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function plans(){
        $type = @request('type');
        $member = @\request()->user();
        $plans = GymTrainingMember::with(['member'])->where('member_id', @$member->id)->when($type, function ($q) use ($type){ $q->where('type', $type);})->orderBy("id", "desc")->paginate($this->limit);
        $this->getPaginateAttribute($plans);
        $this->return['result']['plans'] =  $plans ?  TrainingPlanResource::collection($plans) : [];
        return $this->successResponse();
    }
    public function plan($id){
        $member = @\request()->user();
        if (!$this->validateApiRequest(['id'])) return $this->response;
        $plan = GymTrainingMember::with(['member'])->where("id", $id)->where('member_id', @$member->id)->first();
        $this->return['result']['plan'] =  $plan ? new TrainerPlanContentResource($plan) : '';

        return $this->successResponse();
    }


    public function tracks(){
        $member = @\request()->user();
        $lang = request('lang') ?: env('DEFAULT_LANG', 'en');
        Carbon::setLocale($lang);

        $tracks = GymTrainingTrack::with(['member'])
            ->where('member_id', @$member->id)
            ->orderBy("id", "desc")
            ->get();

        $trackItems = $tracks->map(function ($track) {
            $memberName = @$track->member->name ?: '-';
            $summary = trim(strip_tags((string) @$track->notes));
            if (mb_strlen($summary) > 120) {
                $summary = mb_substr($summary, 0, 120) . '...';
            }
            if ($summary === '') {
                $summary = trans('sw.report_msg', ['name' => $memberName]);
            }

            return [
                'id' => (int) $track->id,
                'track_id' => (int) $track->id,
                'is_timeline' => 0,
                'type' => 'track',
                'action' => 'view',
                'title' => Carbon::parse(@$track->created_at)->translatedFormat('d F Y'),
                'image' => asset('resources/assets/new_front/images/report_track.png'),
                'short_content' => $summary,
                'date' => Carbon::parse(@$track->created_at)->translatedFormat('d F Y'),
                'time' => Carbon::parse(@$track->created_at)->format('H:i'),
                'is_new' => 0,
                'new_title' => '',
                'sort_at' => Carbon::parse(@$track->created_at)->timestamp,
            ];
        });

        $logItems = GymTrainingMemberLog::query()
            ->where('member_id', @$member->id)
            ->orderByDesc('created_at')
            ->limit(200)
            ->get()
            ->map(function ($log) {
                $type = (string) ($log->training_type ?? 'activity');
                $action = (string) ($log->action ?? 'updated');
                $notes = trim((string) ($log->notes ?? ''));
                if ($notes === '') {
                    $notes = trim(ucfirst(str_replace('_', ' ', $type . ' ' . $action)));
                }

                return [
                    'id' => (int) $log->id,
                    'track_id' => (int) ($log->training_id ?? 0),
                    'is_timeline' => 1,
                    'type' => $type,
                    'action' => $action,
                    'title' => Carbon::parse(@$log->created_at)->translatedFormat('d F Y'),
                    'image' => asset('resources/assets/new_front/images/report_track.png'),
                    'short_content' => $notes,
                    'date' => Carbon::parse(@$log->created_at)->translatedFormat('d F Y'),
                    'time' => Carbon::parse(@$log->created_at)->format('H:i'),
                    'is_new' => 0,
                    'new_title' => '',
                    'sort_at' => Carbon::parse(@$log->created_at)->timestamp,
                ];
            });

        $items = $trackItems
            ->concat($logItems)
            ->sortByDesc('sort_at')
            ->values()
            ->map(function ($item) {
                unset($item['sort_at']);
                return $item;
            });

        $this->return['result']['tracks'] = $items;
        return $this->successResponse();
    }
    public function track($id){
        $member = @\request()->user();
        if (!$this->validateApiRequest(['id'])) return $this->response;
        $track = GymTrainingTrack::with(['member'])->where('member_id', @$member->id)->where("id", $id)->first();
        $this->return['result']['track'] =  $track ? new TrainerTrackContentResource($track) : '';

        return $this->successResponse();
    }

}


<?php

namespace Modules\Software\Http\Resources;

use Modules\Software\Classes\TypeConstants;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Software\Models\GymTrainingMemberLog;

class TrainerTrackContentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        Carbon::setLocale($request->lang);
        $memberName = @$this->member->name ?: '-';
        $timeline = GymTrainingMemberLog::query()
            ->where('member_id', $this->member_id)
            ->where('training_id', $this->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function ($log) use ($request) {
                $type = (string) ($log->training_type ?? 'activity');
                $action = (string) ($log->action ?? 'updated');
                $notes = (string) ($log->notes ?? '');
                return [
                    'id' => $log->id,
                    'type' => $type,
                    'action' => $action,
                    'title' => trim(ucfirst(str_replace('_', ' ', $type))),
                    'content' => $notes,
                    'date' => Carbon::parse($log->created_at)->translatedFormat('d F Y'),
                    'time' => Carbon::parse($log->created_at)->format('H:i'),
                ];
            })
            ->values();

        return
            [

                "id" => $this->id,
                "title" => Carbon::parse(@$this->created_at)->translatedFormat('d F Y'),
                "image" => asset('resources/assets/new_front/images/report_track.png'),
                "height" => @$this->height .' cm ',
                "weight" => @$this->weight . ' kg ',
                "report" => @$this->notes,
                "notes" => @$this->notes,
                "assessment" => @$this->assessment ?: (@$this->report_assessment ?: ''),
                "medicines" => @$this->medicines ?: (@$this->medicine ?: ''),
                "plans" => @$this->plans ?: (@$this->plan_details ?: ''),
                "activity_timeline" => $timeline,
                "date" => Carbon::parse(@$this->created_at)->translatedFormat('d F Y'),
                "short_content" => trans('sw.report_msg', ['name' => $memberName])
            ];
    }
}



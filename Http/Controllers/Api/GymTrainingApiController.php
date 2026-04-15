<?php

namespace Modules\Software\Http\Controllers\Api;



use Carbon\Carbon;
use Modules\Software\Http\Resources\PTContentResource;
use Modules\Software\Http\Resources\PTResource;
use Modules\Software\Http\Resources\TrainerPlanContentResource;
use Modules\Software\Http\Resources\TrainingPlanResource;
use Modules\Software\Http\Resources\TrainingTrackResource;
use Modules\Software\Models\GymAiRecommendation;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymPotentialMember;
use Modules\Software\Models\GymPTClass;
use Modules\Software\Models\GymTrainingAssessment;
use Modules\Software\Models\GymTrainingFile;
use Modules\Software\Models\GymTrainingMedicine;
use Modules\Software\Models\GymTrainingMember;
use Modules\Software\Models\GymTrainingMemberLog;
use Modules\Software\Models\GymTrainingPlan;
use Modules\Software\Models\GymTrainingTrack;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $latestTrackId = GymTrainingTrack::where('member_id', @$member->id)
            ->orderByDesc('id')
            ->value('id');

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
            ->map(function ($log) use ($latestTrackId) {
                $type = (string) ($log->training_type ?? 'activity');
                $action = (string) ($log->action ?? 'updated');
                $notes = trim((string) ($log->notes ?? ''));
                $meta = is_string($log->meta) ? json_decode($log->meta, true) : (array) $log->meta;
                $title = trim(ucfirst(str_replace('_', ' ', $type)));
                if ($action !== '' && $type !== 'note') {
                    $title .= ' - ' . ucfirst(str_replace('_', ' ', $action));
                }
                if ($notes === '') {
                    $notes = trim(ucfirst(str_replace('_', ' ', $type . ' ' . $action)));
                }

                $trackId = 0;
                if ($type === 'track' && !empty($log->reference_id)) {
                    $trackId = (int) $log->reference_id;
                } elseif (!empty($meta['track_id'])) {
                    $trackId = (int) $meta['track_id'];
                } elseif (!empty($latestTrackId)) {
                    $trackId = (int) $latestTrackId;
                }

                return [
                    'id' => (int) $log->id,
                    'track_id' => $trackId,
                    'is_timeline' => 1,
                    'type' => $type,
                    'action' => $action,
                    'title' => $title,
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

        $lang = request('lang') ?: env('DEFAULT_LANG', 'en');
        Carbon::setLocale($lang);

        $track = GymTrainingTrack::with(['member'])->where('member_id', @$member->id)->where("id", $id)->first();
        $this->return['result']['track'] = $track ? $this->buildTrackDetailsPayload($track, $lang) : '';

        return $this->successResponse();
    }

    private function buildTrackDetailsPayload(GymTrainingTrack $track, string $lang): array
    {
        $memberName = @$track->member->name ?: '-';
        $timelineData = $this->resolveTrackTimeline($track, $lang);
        $measurements = $this->buildTrackMeasurementsPayload($track);
        $calculations = $this->buildTrackCalculationsPayload($track, $track->member);

        $latestAssessment = $timelineData['latest']['assessment'] ?? null;
        $latestPlan = $timelineData['latest']['plan'] ?? null;
        $latestMedicine = $timelineData['latest']['medicine'] ?? null;
        $latestNote = $timelineData['latest']['note'] ?? null;
        $latestFile = $timelineData['latest']['file'] ?? null;
        $latestAi = $timelineData['latest']['ai'] ?? null;

        $summary = trim(strip_tags((string) @$track->notes));
        if ($summary === '') {
            $summary = trans('sw.report_msg', ['name' => $memberName]);
        }

        return [
            'id' => (int) $track->id,
            'title' => Carbon::parse(@$track->created_at)->translatedFormat('d F Y'),
            'image' => asset('resources/assets/new_front/images/report_track.png'),
            'height' => $this->valueWithUnit($track->height, 'cm'),
            'weight' => $this->valueWithUnit($track->weight, 'kg'),
            'report' => (string) ($track->notes ?? ''),
            'notes' => (string) ($track->notes ?? ''),
            'assessment' => (string) ($latestAssessment['summary'] ?? ''),
            'medicines' => (string) ($latestMedicine['summary'] ?? ''),
            'plans' => (string) ($latestPlan['summary'] ?? ''),
            'date' => Carbon::parse(@$track->created_at)->translatedFormat('d F Y'),
            'short_content' => $summary,
            'measurements' => $measurements,
            'calculations' => $calculations,
            'latest_assessment' => $latestAssessment,
            'latest_plan' => $latestPlan,
            'latest_medicine' => $latestMedicine,
            'latest_note' => $latestNote,
            'latest_file' => $latestFile,
            'latest_ai' => $latestAi,
            'activity_timeline' => $timelineData['items'],
            'related_logs_count' => count($timelineData['items']),
        ];
    }

    private function resolveTrackTimeline(GymTrainingTrack $track, string $lang): array
    {
        $trackId = (int) $track->id;
        $logs = GymTrainingMemberLog::query()
            ->where('member_id', (int) $track->member_id)
            ->orderByDesc('created_at')
            ->limit(300)
            ->get();

        $relatedLogs = $logs->filter(function ($log) use ($trackId) {
            return $this->isLogRelatedToTrack($log, $trackId);
        })->values();

        if ($relatedLogs->isEmpty()) {
            $relatedLogs = $logs->take(50)->values();
        }

        $latest = [
            'assessment' => null,
            'plan' => null,
            'medicine' => null,
            'note' => null,
            'file' => null,
            'ai' => null,
        ];

        $items = $relatedLogs->map(function ($log) use (&$latest, $lang) {
            $type = (string) ($log->training_type ?? 'activity');
            $action = (string) ($log->action ?? 'updated');
            $details = $this->resolveTrackLogDetails($log, $lang);
            $summary = $details['summary'] ?? trim((string) ($log->notes ?? ''));

            if ($summary === '') {
                $summary = trim(ucfirst(str_replace('_', ' ', $type . ' ' . $action)));
            }

            $timelineItem = [
                'id' => (int) $log->id,
                'type' => $type,
                'action' => $action,
                'title' => trim(ucfirst(str_replace('_', ' ', $type))),
                'content' => $summary,
                'date' => Carbon::parse(@$log->created_at)->translatedFormat('d F Y'),
                'time' => Carbon::parse(@$log->created_at)->format('H:i'),
                'details' => $details,
            ];

            if ($type === 'assessment' && !$latest['assessment']) {
                $latest['assessment'] = $details;
            } elseif ($type === 'plan' && !$latest['plan']) {
                $latest['plan'] = $details;
            } elseif ($type === 'medicine' && !$latest['medicine']) {
                $latest['medicine'] = $details;
            } elseif ($type === 'note' && !$latest['note']) {
                $latest['note'] = $details;
            } elseif ($type === 'file' && !$latest['file']) {
                $latest['file'] = $details;
            } elseif (in_array($type, ['ai', 'ai_plan'], true) && !$latest['ai']) {
                $latest['ai'] = $details;
            }

            return $timelineItem;
        })->values()->all();

        return ['items' => $items, 'latest' => $latest];
    }

    private function isLogRelatedToTrack(GymTrainingMemberLog $log, int $trackId): bool
    {
        $meta = $this->parseMeta($log->meta);

        if ((int) ($log->reference_id ?? 0) === $trackId) {
            return true;
        }

        if ((int) ($log->training_id ?? 0) === $trackId && ($log->training_type ?? '') === 'track') {
            return true;
        }

        foreach (['track_id', 'reference_track_id', 'origin_track_id'] as $key) {
            if ((int) ($meta[$key] ?? 0) === $trackId) {
                return true;
            }
        }

        return false;
    }

    private function resolveTrackLogDetails(GymTrainingMemberLog $log, string $lang): array
    {
        $meta = $this->parseMeta($log->meta);
        $type = (string) ($log->training_type ?? '');

        if ($type === 'assessment') {
            $assessment = GymTrainingAssessment::find((int) ($log->reference_id ?? 0));
            if (!$assessment) {
                return ['summary' => (string) ($log->notes ?? ''), 'data' => null];
            }
            $answers = is_array($assessment->answers)
                ? $assessment->answers
                : (json_decode((string) $assessment->answers, true) ?: []);

            return [
                'summary' => $assessment->notes ?: $this->stringifyAnswers($answers) ?: (string) ($log->notes ?? ''),
                'notes' => (string) ($assessment->notes ?? ''),
                'answers' => $answers,
                'date' => optional($assessment->created_at)->format('Y-m-d'),
            ];
        }

        if ($type === 'plan') {
            $memberPlanId = (int) ($log->reference_id ?: ($meta['member_plan_id'] ?? 0));
            $planAssignment = $memberPlanId ? DB::table('sw_gym_training_members')->where('id', $memberPlanId)->first() : null;
            $planId = (int) ($meta['plan_id'] ?? 0);
            if ($planAssignment && !$planId) {
                $planId = (int) ($planAssignment->training_plan_id ?? $planAssignment->diet_plan_id ?? $planAssignment->plan_id ?? 0);
            }

            $plan = $planId ? GymTrainingPlan::with(['tasks' => function ($q) { $q->orderBy('order', 'asc')->orderBy('id', 'asc'); }])->find($planId) : null;
            $tasks = $plan
                ? $plan->tasks->map(function ($task) {
                    return ['id' => (int) $task->id, 'title' => (string) ($task->title ?? ''), 'notes' => (string) ($task->content ?? '')];
                })->values()->all()
                : [];

            $summary = $planAssignment->title ?? $plan->title ?? (string) ($log->notes ?? '');
            if (!empty($meta['notes'])) {
                $summary = trim($summary . ' - ' . $meta['notes']);
            }

            return [
                'summary' => $summary,
                'title' => (string) ($planAssignment->title ?? $plan->title ?? ''),
                'type' => (int) ($planAssignment->type ?? $plan->type ?? 0),
                'from_date' => $planAssignment->from_date ?? ($meta['from_date'] ?? null),
                'to_date' => $planAssignment->to_date ?? ($meta['to_date'] ?? null),
                'notes' => (string) ($planAssignment->notes ?? ($meta['notes'] ?? '')),
                'tasks' => $tasks,
            ];
        }

        if ($type === 'medicine') {
            $medicineId = (int) ($log->reference_id ?: ($meta['medicine_id'] ?? 0));
            $medicine = $medicineId ? GymTrainingMedicine::find($medicineId) : null;

            $name = $medicine
                ? ($medicine->{'name_' . $lang} ?? $medicine->name_en ?? $medicine->name_ar ?? $medicine->name ?? '')
                : (string) ($meta['medicine_name'] ?? '');
            $dose = (string) ($meta['dose'] ?? ($medicine->dose ?? ''));
            $notes = (string) ($meta['notes'] ?? $log->notes ?? '');
            $summary = trim(implode(' - ', array_filter([$name, $dose])));
            if ($summary === '') {
                $summary = $notes;
            }

            return [
                'summary' => $summary,
                'name' => $name,
                'dose' => $dose,
                'notes' => $notes,
            ];
        }

        if ($type === 'file') {
            $file = $log->reference_id ? GymTrainingFile::find((int) $log->reference_id) : null;
            if (!$file) {
                return ['summary' => (string) ($log->notes ?? ''), 'title' => '', 'path' => ''];
            }
            $fileName = (string) ($file->file_name ?? $file->file_path ?? '');
            return [
                'summary' => (string) ($file->title ?? $fileName),
                'title' => (string) ($file->title ?? ''),
                'path' => $fileName ? asset('training_files/' . ltrim($fileName, '/')) : '',
                'file_name' => $fileName,
            ];
        }

        if (in_array($type, ['ai', 'ai_plan'], true)) {
            $ai = $log->reference_id ? GymAiRecommendation::find((int) $log->reference_id) : null;
            $response = $ai && is_array($ai->ai_response) ? $ai->ai_response : [];
            $summary = (string) ($response['summary'] ?? $response['title'] ?? $log->notes ?? trans('sw.ai_recommendation_generated'));

            return [
                'summary' => $summary,
                'title' => (string) ($response['title'] ?? ''),
                'response' => $response,
            ];
        }

        if ($type === 'track') {
            $trackRef = $log->reference_id ? GymTrainingTrack::find((int) $log->reference_id) : null;
            $measurements = $trackRef ? $this->buildTrackMeasurementsPayload($trackRef) : [];
            return [
                'summary' => (string) ($log->notes ?: trans('sw.progress_measurement_added')),
                'measurements' => $measurements,
            ];
        }

        if ($type === 'note') {
            return [
                'summary' => (string) ($log->notes ?? ''),
                'note' => (string) ($log->notes ?? ''),
            ];
        }

        return [
            'summary' => (string) ($log->notes ?? ''),
            'meta' => $meta,
        ];
    }

    private function buildTrackMeasurementsPayload(?GymTrainingTrack $track): array
    {
        if (!$track) {
            return [];
        }

        return array_filter([
            'weight' => $this->valueWithUnit($track->weight, 'kg'),
            'height' => $this->valueWithUnit($track->height, 'cm'),
            'bmi' => $track->bmi ? (string) round((float) $track->bmi, 2) : null,
            'fat_percentage' => $track->fat_percentage ? (string) round((float) $track->fat_percentage, 2) . '%' : null,
            'muscle_mass' => $this->valueWithUnit($track->muscle_mass, 'kg'),
            'neck_circumference' => $this->valueWithUnit($track->neck_circumference, 'cm'),
            'chest_circumference' => $this->valueWithUnit($track->chest_circumference, 'cm'),
            'arm_circumference' => $this->valueWithUnit($track->arm_circumference, 'cm'),
            'abdominal_circumference' => $this->valueWithUnit($track->abdominal_circumference, 'cm'),
            'pelvic_circumference' => $this->valueWithUnit($track->pelvic_circumference, 'cm'),
            'thigh_circumference' => $this->valueWithUnit($track->thigh_circumference, 'cm'),
        ], function ($value) {
            return !is_null($value) && $value !== '';
        });
    }

    private function buildTrackCalculationsPayload(?GymTrainingTrack $track, ?GymMember $member): array
    {
        if (!$track) {
            return [];
        }

        $weight = (float) ($track->weight ?? 0);
        $height = (float) ($track->height ?? 0);
        $fatPercentage = (float) ($track->fat_percentage ?? 0);
        if ($weight <= 0 || $height <= 0) {
            return [];
        }

        $heightMeters = $height / 100;
        if ($heightMeters <= 0) {
            return [];
        }

        $gender = (int) ($member->gender ?? 1);
        $age = 30;
        if (!empty($member->dob)) {
            $age = Carbon::parse($member->dob)->age;
        }

        $bmr = $gender === 2
            ? (10 * $weight + 6.25 * $height - 5 * $age - 161)
            : (10 * $weight + 6.25 * $height - 5 * $age + 5);
        $tdee = $bmr * 1.55;
        $bmi = $weight / ($heightMeters * $heightMeters);

        $result = [
            'bmr' => round($bmr, 2) . ' kcal/day',
            'tdee' => round($tdee, 2) . ' kcal/day',
            'bmi' => round($bmi, 2),
        ];

        if ($fatPercentage > 0) {
            $fatMass = ($weight * $fatPercentage) / 100;
            $result['body_fat_mass'] = round($fatMass, 2) . ' kg';
            $result['lean_body_mass'] = round($weight - $fatMass, 2) . ' kg';
        }

        return $result;
    }

    private function valueWithUnit($value, string $unit): ?string
    {
        if (is_null($value) || $value === '') {
            return null;
        }
        return rtrim(rtrim((string) $value, '0'), '.') . ' ' . $unit;
    }

    private function parseMeta($meta): array
    {
        if (is_array($meta)) {
            return $meta;
        }
        if (is_string($meta) && $meta !== '') {
            $decoded = json_decode($meta, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    private function stringifyAnswers($answers): ?string
    {
        if (!is_array($answers) || empty($answers)) {
            return null;
        }
        $parts = [];
        foreach ($answers as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', array_filter(array_map('strval', $value)));
            }
            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }
            $parts[] = str_replace('_', ' ', (string) $key) . ': ' . $value;
        }
        return empty($parts) ? null : implode("\n", $parts);
    }

}


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
        app()->setLocale($lang);
        Carbon::setLocale($lang);
        $latestTrackId = GymTrainingTrack::where('member_id', @$member->id)
            ->orderByDesc('id')
            ->value('id');

        $tracks = GymTrainingTrack::with(['member'])
            ->where('member_id', @$member->id)
            ->orderBy("id", "desc")
            ->get();

        $trackItems = $tracks->map(function ($track) {
            $trackDate = $this->resolveTrackDate($track);
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
                'title' => $trackDate?->translatedFormat('d F Y') ?: '',
                'image' => asset('resources/assets/new_front/images/report_track.png'),
                'short_content' => $summary,
                'date' => $trackDate?->translatedFormat('d F Y') ?: '',
                'time' => optional($track->created_at)->format('H:i') ?: '',
                'is_new' => 0,
                'new_title' => '',
                'sort_at' => $trackDate?->timestamp ?: optional($track->created_at)->timestamp ?: 0,
            ];
        });

        $logItems = GymTrainingMemberLog::query()
            ->where('member_id', @$member->id)
            ->orderByDesc('created_at')
            ->limit(200)
            ->get()
            ->map(function ($log) use ($latestTrackId, $lang, $member) {
                $type = (string) ($log->training_type ?? 'activity');
                $action = (string) ($log->action ?? 'updated');
                $notes = trim((string) ($log->notes ?? ''));
                $meta = is_string($log->meta) ? json_decode($log->meta, true) : (array) $log->meta;
                $title = $this->localizedTrainingType($type, $lang);
                if ($notes === '') {
                    $notes = trim(ucfirst(str_replace('_', ' ', $type . ' ' . $action)));
                }

                $trackId = 0;
                if ($type === 'track' && !empty($log->reference_id)) {
                    $trackId = (int) $log->reference_id;
                } elseif (!empty($meta['track_id'])) {
                    $trackId = (int) $meta['track_id'];
                } else {
                    $trackId = $this->resolveRelatedTrackIdForLog($log, (int) @$member->id, (int) $latestTrackId);
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
        app()->setLocale($lang);
        Carbon::setLocale($lang);

        $isTimeline = (int) request('is_timeline') === 1;
        $selectedType = (string) request('type', '');

        if ($isTimeline) {
            $log = GymTrainingMemberLog::query()
                ->where('member_id', @$member->id)
                ->where('id', $id)
                ->first();

            $this->return['result']['track'] = $log
                ? $this->buildSelectedLogPayload($log, $lang)
                : '';

            return $this->successResponse();
        }

        $track = GymTrainingTrack::with(['member'])->where('member_id', @$member->id)->where("id", $id)->first();
        $this->return['result']['track'] = $track
            ? $this->buildTrackDetailsPayload($track, $lang, $selectedType)
            : '';

        return $this->successResponse();
    }

    private function buildTrackDetailsPayload(GymTrainingTrack $track, string $lang, string $selectedType = ''): array
    {
        $memberName = @$track->member->name ?: '-';
        $trackDate = $this->resolveTrackDate($track);
        $timeline = $this->resolveTrackTimeline($track, $lang);
        $measurements = $this->buildTrackMeasurementsPayload($track);
        $calculations = $this->buildTrackCalculationsPayload($track, $track->member);

        $summary = trim(strip_tags((string) @$track->notes));
        if ($summary === '') {
            $summary = trans('sw.report_msg', ['name' => $memberName]);
        }

        return [
            'id' => (int) $track->id,
            'title' => $trackDate?->translatedFormat('d F Y') ?: '',
            'image' => asset('resources/assets/new_front/images/report_track.png'),
            'height' => $this->valueWithUnit($track->height, 'cm'),
            'weight' => $this->valueWithUnit($track->weight, 'kg'),
            'report' => (string) ($track->notes ?? ''),
            'notes' => (string) ($track->notes ?? ''),
            'assessment' => '',
            'medicines' => '',
            'plans' => '',
            'date' => $trackDate?->translatedFormat('d F Y') ?: '',
            'short_content' => $summary,
            'measurements' => $measurements,
            'calculations' => $calculations,
            'latest_assessment' => $timeline['latest']['assessment'] ?? null,
            'latest_plan' => $timeline['latest']['plan'] ?? null,
            'latest_medicine' => $timeline['latest']['medicine'] ?? null,
            'latest_note' => $timeline['latest']['note'] ?? null,
            'latest_file' => $timeline['latest']['file'] ?? null,
            'latest_ai' => $timeline['latest']['ai'] ?? null,
            'activity_timeline' => !empty($timeline['items']) ? $timeline['items'] : [
                [
                    'id' => (int) $track->id,
                    'type' => $selectedType !== '' ? $selectedType : 'track',
                    'action' => 'view',
                    'title' => $this->localizedTrainingType('track', $lang),
                    'content' => (string) ($track->notes ?? ''),
                    'date' => $trackDate?->translatedFormat('d F Y') ?: '',
                    'time' => optional($track->created_at)->format('H:i') ?: '',
                    'details' => [
                        'summary' => $summary,
                        'measurements' => $measurements,
                        'calculations' => $calculations,
                    ],
                ],
            ],
            'related_logs_count' => count($timeline['items'] ?? []),
        ];
    }

    private function buildSelectedLogPayload(GymTrainingMemberLog $log, string $lang): array
    {
        $type = (string) ($log->training_type ?? 'activity');
        $details = $this->resolveTrackLogDetails($log, $lang);
        $summary = trim((string) ($details['summary'] ?? $log->notes ?? ''));
        $track = null;

        if ($type === 'track' && !empty($log->reference_id)) {
            $track = GymTrainingTrack::with('member')->find((int) $log->reference_id);
        } elseif (!empty(request('track_id'))) {
            $track = GymTrainingTrack::with('member')->find((int) request('track_id'));
        } else {
            $track = $this->resolveRelatedTrackForLog($log);
        }

        $measurements = $this->buildTrackMeasurementsPayload($track);
        $calculations = $this->buildTrackCalculationsPayload($track, $track?->member);

        return [
            'id' => (int) $log->id,
            'title' => $this->localizedTrainingType($type, $lang),
            'image' => asset('resources/assets/new_front/images/report_track.png'),
            'height' => $measurements['height'] ?? null,
            'weight' => $measurements['weight'] ?? null,
            'report' => $summary,
            'notes' => (string) ($log->notes ?? ''),
            'assessment' => $type === 'assessment' ? $summary : '',
            'medicines' => $type === 'medicine' ? $summary : '',
            'plans' => $type === 'plan' ? $summary : '',
            'date' => Carbon::parse(@$log->created_at)->translatedFormat('d F Y'),
            'short_content' => $summary,
            'measurements' => $measurements,
            'calculations' => $calculations,
            'latest_assessment' => $type === 'assessment' ? $details : null,
            'latest_plan' => $type === 'plan' ? $details : null,
            'latest_medicine' => $type === 'medicine' ? $details : null,
            'latest_note' => $type === 'note' ? $details : null,
            'latest_file' => $type === 'file' ? $details : null,
            'latest_ai' => in_array($type, ['ai', 'ai_plan'], true) ? $details : null,
            'activity_timeline' => [
                [
                    'id' => (int) $log->id,
                    'type' => $type,
                    'action' => (string) ($log->action ?? 'view'),
                    'title' => $this->localizedTrainingType($type, $lang),
                    'content' => $summary,
                    'date' => Carbon::parse(@$log->created_at)->translatedFormat('d F Y'),
                    'time' => Carbon::parse(@$log->created_at)->format('H:i'),
                    'details' => $details,
                ],
            ],
            'related_logs_count' => 1,
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
            $relatedLogs = $this->inferTrackLogsByDate($logs, $track)->values();
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
                'title' => $this->localizedTrainingType($type, $lang),
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
                return [
                    'summary' => $this->formatAssessmentSummaryText((string) ($log->notes ?? ''), $lang),
                    'data' => null,
                ];
            }
            $answers = is_array($assessment->answers)
                ? $assessment->answers
                : (json_decode((string) $assessment->answers, true) ?: []);

            $summarySource = $assessment->notes
                ?: $this->stringifyAnswers($answers, $lang)
                ?: (string) ($log->notes ?? '');

            return [
                'summary' => $this->formatAssessmentSummaryText($summarySource, $lang),
                'notes' => $this->formatAssessmentSummaryText((string) ($assessment->notes ?? ''), $lang),
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

            $planDetails = (string) (
                $planAssignment->training_plan_details
                ?? $planAssignment->diet_plan_details
                ?? $planAssignment->plan_details
                ?? $plan->content
                ?? ''
            );

            return [
                'summary' => $summary,
                'title' => (string) ($planAssignment->title ?? $plan->title ?? ''),
                'type' => (int) ($planAssignment->type ?? $plan->type ?? 0),
                'from_date' => $planAssignment->from_date ?? ($meta['from_date'] ?? null),
                'to_date' => $planAssignment->to_date ?? ($meta['to_date'] ?? null),
                'notes' => (string) ($planAssignment->notes ?? ($meta['notes'] ?? '')),
                'plan_details' => $planDetails,
                'download_url' => $this->resolvePlanDownloadUrl($log),
                'file_name' => trim((string) ($planAssignment->title ?? $plan->title ?? trans('sw.plan'))) . '.pdf',
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
                'path' => $this->resolveTrainingFileUrl($file, $meta),
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

    private function stringifyAnswers($answers, string $lang): ?string
    {
        if (!is_array($answers) || empty($answers)) {
            return null;
        }

        $parts = $this->flattenAssessmentAnswers($answers, $lang);

        return empty($parts) ? null : implode('<br>', $parts);
    }

    private function flattenAssessmentAnswers(array $answers, string $lang): array
    {
        $parts = [];

        foreach ($answers as $key => $value) {
            $normalizedKey = strtolower(trim(str_replace(' ', '_', (string) $key)));

            if (is_array($value)) {
                if ($this->isAssocArray($value)) {
                    $parts = array_merge($parts, $this->flattenAssessmentAnswers($value, $lang));
                    continue;
                }

                $values = array_values(array_filter(array_map(function ($item) use ($normalizedKey, $lang) {
                    if (is_array($item)) {
                        return $this->stringifyAnswers($item, $lang) ?? '';
                    }

                    return $this->localizeAssessmentValue($normalizedKey, (string) $item, $lang);
                }, $value)));

                if (!empty($values)) {
                    $parts[] = $this->localizeAssessmentFieldLabel($normalizedKey, $lang) . ': ' . implode(', ', $values);
                }

                continue;
            }

            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }

            $parts[] = $this->localizeAssessmentFieldLabel($normalizedKey, $lang) . ': ' . $this->localizeAssessmentValue($normalizedKey, $value, $lang);
        }

        return $parts;
    }

    private function isAssocArray(array $value): bool
    {
        if ($value === []) {
            return false;
        }

        return array_keys($value) !== range(0, count($value) - 1);
    }

    private function formatAssessmentSummaryText(string $text, string $lang): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        $normalized = preg_replace('/\s+/', ' ', $text) ?? $text;
        preg_match_all('/([a-zA-Z][a-zA-Z0-9_ ]*?):\s*([^:]+?)(?=(?:\s+[a-zA-Z][a-zA-Z0-9_ ]*?:)|$)/', $normalized, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            return $text;
        }

        $parts = [];
        foreach ($matches as $match) {
            $rawKey = trim((string) ($match[1] ?? ''));
            $rawValue = trim((string) ($match[2] ?? ''));
            if ($rawKey === '' || $rawValue === '') {
                continue;
            }

            $key = strtolower(str_replace(' ', '_', $rawKey));
            $label = $this->localizeAssessmentFieldLabel($key, $lang);
            $value = $this->localizeAssessmentValue($key, $rawValue, $lang);
            $parts[] = $label . ': ' . $value;
        }

        if (empty($parts)) {
            return $text;
        }

        return implode('<br>', $parts);
    }

    private function localizeAssessmentFieldLabel(string $key, string $lang): string
    {
        $isArabic = strtolower($lang) === 'ar';

        $mapAr = [
            'age' => 'العمر',
            'gender' => 'النوع',
            'weight' => 'الوزن',
            'height' => 'الطول',
            'bmi' => 'مؤشر كتلة الجسم',
            'fat_percentage' => 'نسبة الدهون',
            'muscle_mass' => 'الكتلة العضلية',
            'waist_circumference' => 'محيط الخصر',
            'chest_circumference' => 'محيط الصدر',
            'arm_circumference' => 'محيط الذراع',
            'thigh_circumference' => 'محيط الفخذ',
            'hip_circumference' => 'محيط الحوض',
            'primary_goal' => 'الهدف الأساسي',
            'target_weight' => 'الوزن المستهدف',
            'target_date' => 'التاريخ المستهدف',
            'goals' => 'الأهداف',
            'training_experience' => 'الخبرة التدريبية',
            'injuries' => 'الإصابات',
            'diseases' => 'الأمراض',
            'medications' => 'الأدوية',
            'allergies' => 'الحساسية',
            'activity_level' => 'مستوى النشاط',
            'sleep_hours' => 'ساعات النوم',
            'stress_level' => 'مستوى التوتر',
            'diet_type' => 'نوع النظام الغذائي',
            'water_intake' => 'شرب المياه',
            'water_percentage' => 'نسبة المياه',
            'bone_mass' => 'كتلة العظام',
        ];

        $mapEn = [
            'age' => 'Age',
            'gender' => 'Gender',
            'weight' => 'Weight',
            'height' => 'Height',
            'bmi' => 'BMI',
            'fat_percentage' => 'Fat Percentage',
            'muscle_mass' => 'Muscle Mass',
            'waist_circumference' => 'Waist Circumference',
            'chest_circumference' => 'Chest Circumference',
            'arm_circumference' => 'Arm Circumference',
            'thigh_circumference' => 'Thigh Circumference',
            'hip_circumference' => 'Hip Circumference',
            'primary_goal' => 'Primary Goal',
            'target_weight' => 'Target Weight',
            'target_date' => 'Target Date',
            'goals' => 'Goals',
            'training_experience' => 'Training Experience',
            'injuries' => 'Injuries',
            'diseases' => 'Diseases',
            'medications' => 'Medications',
            'allergies' => 'Allergies',
            'activity_level' => 'Activity Level',
            'sleep_hours' => 'Sleep Hours',
            'stress_level' => 'Stress Level',
            'diet_type' => 'Diet Type',
            'water_intake' => 'Water Intake',
            'water_percentage' => 'Water Percentage',
            'bone_mass' => 'Bone Mass',
        ];

        if ($isArabic) {
            return $mapAr[$key] ?? str_replace('_', ' ', $key);
        }

        return $mapEn[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }

    private function localizeAssessmentValue(string $key, string $value, string $lang): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $isArabic = strtolower($lang) === 'ar';
        $normalized = strtolower(str_replace(' ', '_', $value));

        if (!$isArabic) {
            return str_replace('_', ' ', $value);
        }

        $map = [
            'male' => 'ذكر',
            'female' => 'أنثى',
            'beginner' => 'مبتدئ',
            'intermediate' => 'متوسط',
            'advanced' => 'متقدم',
            'light' => 'خفيف',
            'moderate' => 'متوسط',
            'medium' => 'متوسط',
            'high' => 'مرتفع',
            'low' => 'منخفض',
            'sedentary' => 'خامل',
            'active' => 'نشط',
            'very_active' => 'نشط جدًا',
            'regular' => 'عادي',
            'body_toning' => 'شد الجسم',
            'weight_loss' => 'إنقاص الوزن',
            'weight_gain' => 'زيادة الوزن',
            'muscle_gain' => 'زيادة الكتلة العضلية',
            'strength_increase' => 'زيادة القوة',
            'endurance_improvement' => 'تحسين التحمل',
            'general_fitness' => 'لياقة عامة',
            'vegetarian' => 'نباتي',
            'vegan' => 'نباتي صرف',
            'keto' => 'كيتو',
            'low_carb' => 'منخفض الكربوهيدرات',
        ];

        return $map[$normalized] ?? str_replace('_', ' ', $value);
    }

    private function resolveTrackDate(?GymTrainingTrack $track): ?Carbon
    {
        if (!$track) {
            return null;
        }

        if (!empty($track->date)) {
            return Carbon::parse($track->date);
        }

        return !empty($track->created_at) ? Carbon::parse($track->created_at) : null;
    }

    private function resolveRelatedTrackIdForLog(GymTrainingMemberLog $log, int $memberId, int $latestTrackId = 0): int
    {
        $track = $this->resolveRelatedTrackForLog($log, $memberId);

        if ($track) {
            return (int) $track->id;
        }

        return $latestTrackId > 0 ? $latestTrackId : 0;
    }

    private function resolveRelatedTrackForLog(GymTrainingMemberLog $log, ?int $memberId = null): ?GymTrainingTrack
    {
        $memberId = $memberId ?: (int) ($log->member_id ?? 0);
        if ($memberId <= 0) {
            return null;
        }

        $meta = $this->parseMeta($log->meta);
        foreach (['track_id', 'reference_track_id', 'origin_track_id'] as $key) {
            if (!empty($meta[$key])) {
                $track = GymTrainingTrack::with('member')->find((int) $meta[$key]);
                if ($track) {
                    return $track;
                }
            }
        }

        if (($log->training_type ?? '') === 'track' && !empty($log->reference_id)) {
            return GymTrainingTrack::with('member')->find((int) $log->reference_id);
        }

        $logDate = !empty($log->created_at) ? Carbon::parse($log->created_at)->toDateString() : null;
        if (!$logDate) {
            return null;
        }

        $sameDateTrack = GymTrainingTrack::with('member')
            ->where('member_id', $memberId)
            ->where(function ($query) use ($logDate) {
                $query->whereDate('date', $logDate)
                    ->orWhere(function ($subQuery) use ($logDate) {
                        $subQuery->whereNull('date')->whereDate('created_at', $logDate);
                    });
            })
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->first();

        if ($sameDateTrack) {
            return $sameDateTrack;
        }

        return GymTrainingTrack::with('member')
            ->where('member_id', $memberId)
            ->where(function ($query) use ($logDate) {
                $query->whereDate('date', '<=', $logDate)
                    ->orWhere(function ($subQuery) use ($logDate) {
                        $subQuery->whereNull('date')->whereDate('created_at', '<=', $logDate);
                    });
            })
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->first();
    }

    private function inferTrackLogsByDate($logs, GymTrainingTrack $track)
    {
        $trackDate = $this->resolveTrackDate($track);
        if (!$trackDate) {
            return collect();
        }

        $trackDateString = $trackDate->toDateString();
        $sameDayLogs = $logs->filter(function ($log) use ($trackDateString) {
            return optional($log->created_at)->toDateString() === $trackDateString;
        });

        if ($sameDayLogs->isNotEmpty()) {
            return $sameDayLogs;
        }

        return $logs->filter(function ($log) use ($trackDate) {
            if (empty($log->created_at)) {
                return false;
            }

            return abs(Carbon::parse($log->created_at)->diffInDays($trackDate, false)) <= 7;
        })->take(20);
    }

    private function resolveTrainingFileUrl(?GymTrainingFile $file, array $meta): string
    {
        $path = trim((string) ($meta['path'] ?? ''));
        if ($path !== '') {
            if (preg_match('/^https?:\/\//i', $path)) {
                return $path;
            }

            return asset(ltrim($path, '/'));
        }

        $fileName = trim((string) ($file->file_name ?? ''));
        if ($fileName === '') {
            return '';
        }

        return asset('uploads/training_files/' . ltrim($fileName, '/'));
    }

    private function resolvePlanDownloadUrl(GymTrainingMemberLog $log): string
    {
        $memberId = (int) ($log->member_id ?? 0);
        $logId = (int) ($log->id ?? 0);

        if ($memberId <= 0 || $logId <= 0) {
            return '';
        }

        return url('training/member-log/' . $memberId . '/plan/' . $logId . '/pdf');
    }

    private function localizedTrainingType(string $type, string $lang): string
    {
        $isArabic = strtolower($lang) === 'ar';

        return match ($type) {
            'assessment' => $isArabic ? 'تقييم' : 'Assessment',
            'plan' => trans('sw.plan'),
            'medicine' => $isArabic ? 'دواء' : 'Medicine',
            'note' => trans('sw.notes'),
            'file' => trans('sw.file'),
            'track' => $isArabic ? 'قياس' : 'Track',
            'ai', 'ai_plan' => $isArabic ? 'خطة ذكية' : 'AI Plan',
            default => trans('sw.activity'),
        };
    }

}


<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Generic\Http\Controllers\Front\GenericFrontController;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Exports\ActivitiesExport;
use Modules\Software\Exports\RecordsExport;
use Modules\Software\Http\Requests\GymActivityRequest;
use Modules\Software\Models\GymActivity;
use Modules\Software\Models\GymUserLog;
use Modules\Software\Repositories\GymActivityRepository;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Illuminate\Container\Container as Application;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Maatwebsite\Excel\Facades\Excel;

class GymActivityFrontController extends GymGenericFrontController
{
    public $ActivityRepository;
    public $fileName;
    private $imageManager;

    public function __construct()
    {
        parent::__construct();
        $this->ActivityRepository =new GymActivityRepository(new Application);
        $this->imageManager = new ImageManager(new Driver());
    }


    public function index()
    {
        $title = trans('sw.activities');
        $this->request_array = ['search'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $activities = $this->ActivityRepository->branch()->onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $activities = $this->ActivityRepository->branch()->orderBy('id', 'DESC');
        }

        //apply filters
        $activities->when($search, function ($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('id', '=', $search);
                $query->orWhere('name_' . $this->lang, 'like', "%" . $search . "%");
            });
        });
        $trainer_id = request('trainer_id');
        $activities->when($trainer_id, function ($q) use ($trainer_id) {
            $q->where('trainer_id', $trainer_id);
        });
        $search_query = request()->query();

        $trainers = \Modules\Software\Models\GymPTTrainer::branch()->orderBy('name')->get();

        if ($this->limit) {
            $activities = $activities->paginate($this->limit);
            $total = $activities->total();
        } else {
            $activities = $activities->get();
            $total = $activities->count();
        }

        return view('software::Front.activity_front_list', compact('activities','title', 'total', 'search_query', 'trainers'));
    }


    function exportExcel(){
        $records = $this->ActivityRepository->get();
        $this->fileName = 'activities-' . Carbon::now()->toDateTimeString();

//        $title = trans('sw.activities');
//        $records = $this->prepareForExport($records);


        $notes = trans('sw.export_excel_activities');
        $this->userLog($notes, TypeConstants::ExportActivityExcel);

        return Excel::download(new RecordsExport(['records' => $records, 'keys' => ['name', 'price'],'lang' => $this->lang, 'settings' => $this->mainSettings]), $this->fileName.'.xlsx');

//        Excel::create($this->fileName, function($excel) use ($records, $title) {
//            $excel->setTitle($title);
////            $excel->setCreator($title)->setCompany($title);
//            $excel->setDescription(trans('sw.activities_data'));
//            $excel->sheet(trans('sw.activities_data'), function($sheet) use ($records) {
//                $sheet->setRightToLeft(true);
//                $sheet->fromArray($records, null, 'A1', false, false);
//                $sheet->mergeCells('A1:B1');
//                $sheet->cells('A1:B1', function ($cells) {
//                    $cells->setBackground('#d8d8d8');
//                    $cells->setFontWeight('bold');
//                    $cells->setAlignment('center');
//                });
//            });
//
//        })->download('xlsx');

    }

    private function prepareForExport($data)
    {
        $name = [trans('sw.name'), trans('sw.price')];
        $result = array_map(function ($row) {
            return [
                trans('sw.name') => $row['name'],
                trans('sw.price') => $row['price']
            ];
        }, $data->toArray());
        array_unshift($result, $name);
        array_unshift($result, [trans('sw.activities')]);
        return $result;
    }
    function exportPDF(){
        $records = $this->ActivityRepository->get();
        $this->fileName = 'activities-' . Carbon::now()->toDateTimeString();

        $keys = ['name', 'price'];
        if($this->lang == 'ar') $keys = array_reverse($keys);

        $title = trans('sw.activities');
        $customPaper = array(0,0,550,750);
        
        // Try mPDF for better Arabic support
        if ($this->lang == 'ar') {
            try {
                $mpdf = new Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4-L', // Landscape
                    'orientation' => 'L',
                    'margin_left' => 15,
                    'margin_right' => 15,
                    'margin_top' => 16,
                    'margin_bottom' => 16,
                    'margin_header' => 9,
                    'margin_footer' => 9,
                    'default_font' => 'dejavusans',
                    'default_font_size' => 10
                ]);
                
                $html = view('software::Front.export_pdf', [
                    'records' => $records, 
                    'title' => $title, 
                    'keys' => $keys,
                    'lang' => $this->lang
                ])->render();
                
                $mpdf->WriteHTML($html);
                
                $notes = trans('sw.export_pdf_activities');
                $this->userLog($notes, TypeConstants::ExportActivityPDF);
                
                return response($mpdf->Output($this->fileName.'.pdf', 'D'), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $this->fileName . '.pdf"'
                ]);
                
            } catch (\Exception $e) {
                // Fallback to DomPDF if mPDF fails
                \Log::error('mPDF failed, falling back to DomPDF: ' . $e->getMessage());
            }
        }
        
        // Configure PDF for Arabic text using DomPDF
        $pdf = PDF::loadView('software::Front.export_pdf', [
            'records' => $records, 
            'title' => $title, 
            'keys' => $keys,
            'lang' => $this->lang
        ])
        ->setPaper($customPaper, 'landscape')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'DejaVu Sans',
            'isPhpEnabled' => true,
            'isJavascriptEnabled' => false
        ]);

        $notes = trans('sw.export_pdf_activities');
        $this->userLog($notes, TypeConstants::ExportActivityPDF);

        return $pdf->download($this->fileName.'.pdf');
    }


    public function create()
    {
        $title = trans('sw.activity_add');
        $trainers = \Modules\Software\Models\GymPTTrainer::branch()->orderBy('name')->get();
        return view('software::Front.activity_front_form', ['activity' => new GymActivity(),'title'=>$title, 'trainers' => $trainers]);
    }

    public function store(GymActivityRequest $request)
    {
        $activity_inputs = $this->prepare_inputs($request->except(['_token', 'activity_trainers']));
        $activity_inputs['is_system'] = request()->has('is_system') ? 1 : 0;
        $activity = $this->ActivityRepository->create($activity_inputs);
        $this->syncActivityTrainers($activity, (array) $request->input('activity_trainers', []));
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);

        $notes = str_replace(':name', $activity_inputs['name_'.$this->lang], trans('sw.add_activity'));
        $this->userLog($notes, TypeConstants::CreateActivity);
        return redirect(route('sw.listActivity'));
    }

    public function edit($id)
    {
        $activity =$this->ActivityRepository->withTrashed()->find($id);
        $activity->loadMissing('activityTrainers.trainer');
        $title = trans('sw.activity_edit');
        $trainers = \Modules\Software\Models\GymPTTrainer::branch()->orderBy('name')->get();
        return view('software::Front.activity_front_form', ['activity' => $activity,'title'=>$title, 'trainers' => $trainers]);
    }

    public function update(GymActivityRequest $request, $id)
    {
        $activity =$this->ActivityRepository->withTrashed()->find($id);
        $activity_inputs = $this->prepare_inputs($request->except(['_token', 'activity_trainers']));
        $activity_inputs['is_system'] = request()->has('is_system') ? 1 : 0;
        $activity_inputs['is_web'] = @(int)$activity_inputs['is_web'];
        $activity_inputs['is_mobile'] = @(int)$activity_inputs['is_mobile'];
        $activity->update($activity_inputs);
        $this->syncActivityTrainers($activity, (array) $request->input('activity_trainers', []));

        $notes = str_replace(':name', $activity['name'], trans('sw.edit_activity'));
        $this->userLog($notes, TypeConstants::EditActivity);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listActivity'));
    }

    /**
     * Syncs an activity's optional multiple-trainers-each-with-own-schedule
     * rows (Modules\Software\Models\GymActivityTrainer), mirroring
     * GymPTClassFrontController::syncClassTrainers(). An activity with zero
     * rows here keeps using its legacy single trainer_id + reservation_details
     * (nothing about this method's absence of input changes that behaviour).
     */
    private function syncActivityTrainers(GymActivity $activity, array $trainerPayloads): void
    {
        $existing = $activity->activityTrainers()->withTrashed()->get()->keyBy('id');
        $processedIds = [];
        $hasMeaningfulRow = false;

        foreach ($trainerPayloads as $payload) {
            $assignmentId = $payload['id'] ?? null;
            $trainerId = $payload['trainer_id'] ?? null;
            $shouldDelete = !empty($payload['_delete']);

            if (!$trainerId && !$assignmentId) {
                continue;
            }

            $hasMeaningfulRow = true;

            if ($assignmentId && isset($existing[$assignmentId])) {
                $assignment = $existing[$assignmentId];
                if ($shouldDelete) {
                    $assignment->delete();
                    continue;
                }
                if ($assignment->trashed()) {
                    $assignment->restore();
                }
            } else {
                if ($shouldDelete || !$trainerId) {
                    continue;
                }
                $assignment = new \Modules\Software\Models\GymActivityTrainer();
                $assignment->activity_id = $activity->id;
                $assignment->branch_setting_id = $activity->branch_setting_id ?? ($this->user_sw->branch_setting_id ?? null);
            }

            $assignment->trainer_id = $trainerId;
            $assignment->is_active = array_key_exists('is_active', $payload) ? (bool) $payload['is_active'] : true;

            if (array_key_exists('reservation_limit', $payload)) {
                $assignment->reservation_limit = $payload['reservation_limit'] !== '' ? (int) $payload['reservation_limit'] : null;
            }

            if (array_key_exists('schedule', $payload)) {
                $schedule = $payload['schedule'];
                if (is_string($schedule) && $schedule !== '') {
                    $schedule = json_decode($schedule, true) ?: [];
                }
                $assignment->schedule = $schedule ?: null;
            }

            $assignment->save();
            $processedIds[] = $assignment->id;
        }

        if ($hasMeaningfulRow) {
            if (!empty($processedIds)) {
                $activity->activityTrainers()->whereNotIn('id', $processedIds)->delete();
            } else {
                $activity->activityTrainers()->delete();
            }
        }
    }

    public function destroy($id)
    {
        $activity =$this->ActivityRepository->withTrashed()->find($id);
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

    private function prepare_inputs($inputs)
    {
        $input_file = 'image';
        if (request()->hasFile($input_file)) {
            $file = request()->file($input_file);
            if ($file->isValid()) {
                $extension = $file->getClientOriginalExtension();
                $filename = uniqid() . time() . ($extension ? '.' . $extension : '.jpg');
                $destinationPath = base_path(GymActivity::$uploads_path);

                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0777, true, true);
                }

                try {
                    $img = $this->imageManager->read($file->getRealPath());
                    $img->scaleDown(360)->toJpeg(90)->save($destinationPath . DIRECTORY_SEPARATOR . $filename);
                    $inputs[$input_file] = $filename;
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        } else {
            unset($inputs[$input_file]);
        }

        // Handle text fields - convert empty strings to avoid null constraint violations
        $inputs['content_ar'] = isset($inputs['content_ar']) && $inputs['content_ar'] !== null ? $inputs['content_ar'] : '';
        $inputs['content_en'] = isset($inputs['content_en']) && $inputs['content_en'] !== null ? $inputs['content_en'] : '';

        // Handle reservation_details - set to null if no work days are selected
        if (isset($inputs['reservation_details']) && isset($inputs['reservation_details']['work_days'])) {
            $workDays = $inputs['reservation_details']['work_days'];
            $hasActiveDay = false;
            
            // Check if any day has status = 1
            foreach ($workDays as $dayIndex => $dayData) {
                if (isset($dayData['status']) && $dayData['status'] == 1) {
                    $hasActiveDay = true;
                    break;
                }
            }
            
            if ($hasActiveDay) {
                // Build reservation_details structure with only active days
                $reservationDetails = ['work_days' => []];
                foreach ($workDays as $dayIndex => $dayData) {
                    if (isset($dayData['status']) && $dayData['status'] == 1) {
                        $reservationDetails['work_days'][$dayIndex] = [
                            'status' => 1,
                            'start' => $dayData['start'] ?? null,
                            'end' => $dayData['end'] ?? null
                        ];
                    }
                }
                $inputs['reservation_details'] = $reservationDetails;
            } else {
                // No active days - set to null
                $inputs['reservation_details'] = null;
            }
        } else {
            // No reservation_details in input - set to null
            $inputs['reservation_details'] = null;
        }

        if(@$this->user_sw->branch_setting_id){
            $inputs['branch_setting_id'] = @$this->user_sw->branch_setting_id;
        }

        return $inputs;
    }

}


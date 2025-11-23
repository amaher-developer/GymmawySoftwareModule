<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Software\Classes\TypeConstants;
use Modules\Software\Exports\RecordsExport;
use Modules\Software\Http\Requests\GymActivityRequest;
use Modules\Software\Http\Requests\GymPTClassRequest;
use Modules\Software\Models\GymActivity;
use Modules\Software\Models\GymPTClass;
use Modules\Software\Models\GymPTClassTrainer;
use Modules\Software\Models\GymPTSubscription;
use Modules\Software\Models\GymPTTrainer;
use Modules\Software\Repositories\GymPTClassRepository;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Illuminate\Container\Container as Application;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Schema;

class GymPTClassFrontController extends GymGenericFrontController
{
    public $ClassRepository;
    public $fileName;

    protected bool $classTrainerHasScheduleColumn;
    protected bool $classTrainerHasDateFromColumn;
    protected bool $classTrainerHasDateToColumn;

    public function __construct()
    {
        parent::__construct();
        $this->ClassRepository=new GymPTClassRepository(new Application);
        $this->ClassRepository=$this->ClassRepository->branch();

        $this->classTrainerHasScheduleColumn = Schema::hasColumn('sw_gym_pt_class_trainers', 'schedule');
        $this->classTrainerHasDateFromColumn = Schema::hasColumn('sw_gym_pt_class_trainers', 'date_from');
        $this->classTrainerHasDateToColumn = Schema::hasColumn('sw_gym_pt_class_trainers', 'date_to');
    }


    public function index()
    {
        $title = trans('sw.pt_classes');
        $this->request_array = ['search'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $classes = $this->ClassRepository->with(['pt_subscription' => function($q){
                $q->withTrashed();
            }])->onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $classes = $this->ClassRepository->with(['pt_subscription' => function($q){
                $q->withTrashed();
            }])->orderBy('id', 'DESC');
        }

        //apply filters
        $classes->when($search, function ($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('id', '=', $search);
                $query->orWhere('name_' . $this->lang, 'like', "%" . $search . "%");
            });
        });
        $search_query = request()->query();

        if ($this->limit) {
            $classes = $classes->paginate($this->limit);
            $total = $classes->total();
        } else {
            $classes = $classes->get();
            $total = $classes->count();
        }

        return view('software::Front.pt_class_front_list', compact('classes','title', 'total', 'search_query'));
    }


    function exportExcel(){
        $records = $this->ClassRepository->get();
        $this->fileName = 'pt_classes-' . Carbon::now()->toDateTimeString();

//        $title = trans('sw.pt_classes');
//        $records = $this->prepareForExport($records);


        $notes = trans('sw.export_excel_pt_classes');
        $this->userLog($notes, TypeConstants::ExportPTClassExcel);

        return Excel::download(new RecordsExport(['records' => $records, 'keys' => ['name', 'price'],'lang' => $this->lang]), $this->fileName.'.xlsx');

//        Excel::create($this->fileName, function($excel) use ($records, $title) {
//            $excel->setTitle($title);
////            $excel->setCreator($title)->setCompany($title);
//            $excel->setDescription(trans('sw.pt_classes_data'));
//            $excel->sheet(trans('sw.pt_classes_data'), function($sheet) use ($records) {
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
                trans('sw.name') => $row['name']
            ];
        }, $data->toArray());
        array_unshift($result, $name);
        array_unshift($result, [trans('sw.pt_classes')]);
        return $result;
    }
    function exportPDF(){
        $records = $this->ClassRepository->get();
        $this->fileName = 'pt_classes-' . Carbon::now()->toDateTimeString();

        $keys = ['name'];
        if($this->lang == 'ar') $keys = array_reverse($keys);

        $title = trans('sw.pt_classes');
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
                
                $notes = trans('sw.export_pdf_pt_classes');
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

        $notes = trans('sw.export_pdf_pt_classes');
        $this->userLog($notes, TypeConstants::ExportActivityPDF);

        return $pdf->download($this->fileName.'.pdf');
    }


    public function create()
    {
        $title = trans('sw.pt_class_add');
        $subscriptions = GymPTSubscription::branch()->get();
        $trainers = GymPTTrainer::branch()->withTrashed()->orderBy('name')->get();

        $class = new GymPTClass();
        $class->setRelation('classTrainers', collect());

        return view('software::Front.pt_class_front_form', [
            'class' => $class,
            'subscriptions' => $subscriptions,
            'trainers' => $trainers,
            'title' => $title,
            'mainSettings' => $this->mainSettings,
        ]);
    }

    public function store(GymPTClassRequest $request)
    {
        $class_inputs = $this->prepare_inputs($request->except(['_token']));
        $class = $this->ClassRepository->create($class_inputs);
        $this->syncClassTrainers($class, $request->input('class_trainers', []));
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);

        $notes = trans('sw.add_pt_class', ['name' => $class->pt_subscription->name]);
        $this->userLog($notes, TypeConstants::CreatePTClass);
        return redirect(route('sw.listPTClass'));
    }

    public function edit($id)
    {
        $class = $this->ClassRepository
            ->with(['classTrainers.trainer' => function ($query) {
                $query->withTrashed();
            }])
            ->withTrashed()
            ->find($id);
        $title = trans('sw.pt_class_edit');

        $subscriptions = GymPTSubscription::branch()->get();
        $trainers = GymPTTrainer::branch()->withTrashed()->orderBy('name')->get();

        if ($class) {
            $class->loadMissing('classTrainers.trainer');
        }

        return view('software::Front.pt_class_front_form', [
            'class' => $class,
            'title' => $title,
            'subscriptions' => $subscriptions,
            'trainers' => $trainers,
            'mainSettings' => $this->mainSettings,
        ]);
    }

    public function update(GymPTClassRequest $request, $id)
    {
        $class =$this->ClassRepository->withTrashed()->find($id);
        $class_inputs = $this->prepare_inputs($request->except(['_token']));
        $class->update($class_inputs);
        $this->syncClassTrainers($class, $request->input('class_trainers', []));

        $notes = trans('sw.edit_pt_class', ['name' => $class->pt_subscription->name]);

        $this->userLog($notes, TypeConstants::EditPTClass);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listPTClass'));
    }

    public function destroy($id)
    {
        $class =$this->ClassRepository->withTrashed()->find($id);
        if($class->trashed())
        {
            $class->restore();
        }
        else
        {
            $class->delete();

            $notes = str_replace(':name', $class['name'], trans('sw.delete_activity'));
            $this->userLog($notes, TypeConstants::DeletePTClass);
        }
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listPTClass'));
    }

    private function prepare_inputs($inputs)
    {
        // Handle text fields - convert null/empty values to empty strings to avoid null constraint violations
        $inputs['content_ar'] = isset($inputs['content_ar']) && $inputs['content_ar'] !== null ? $inputs['content_ar'] : '';
        $inputs['content_en'] = isset($inputs['content_en']) && $inputs['content_en'] !== null ? $inputs['content_en'] : '';

        $inputs['is_system'] = array_key_exists('is_system', $inputs) ? 1 : 0;
        $inputs['is_mobile'] = array_key_exists('is_mobile', $inputs) ? 1 : 0;
        $inputs['is_web'] = array_key_exists('is_web', $inputs) ? 1 : 0;

        $totalSessions = (int) ($inputs['total_sessions'] ?? 0);
        if ($totalSessions <= 0 && isset($inputs['classes'])) {
            $totalSessions = (int) $inputs['classes'];
        }
        $inputs['total_sessions'] = $totalSessions;
        $inputs['classes'] = $totalSessions;

        $inputs['max_members'] = isset($inputs['max_members']) && $inputs['max_members'] !== ''
            ? (int) $inputs['max_members']
            : null;
        $inputs['member_limit'] = $inputs['max_members'];

        $classType = $inputs['class_type'] ?? 'private';
        $inputs['is_mixed'] = $classType === 'mixed';
        $inputs['pricing_type'] = $inputs['pricing_type'] ?? 'per_member';
        $inputs['is_active'] = array_key_exists('is_active', $inputs) ? (bool)$inputs['is_active'] : true;

        if (isset($inputs['schedule'])) {
            if (is_string($inputs['schedule'])) {
                $decoded = json_decode($inputs['schedule'], true) ?: [];
            } elseif (is_array($inputs['schedule'])) {
                $decoded = $inputs['schedule'];
            } else {
                $decoded = [];
            }
            $inputs['schedule'] = $decoded;
            $inputs['reservation_details'] = $decoded;
        }
        
        if(@$this->user_sw->branch_setting_id){
            $inputs['branch_setting_id'] = @$this->user_sw->branch_setting_id;
        }
        return $inputs;
    }

    private function syncClassTrainers(GymPTClass $class, array $trainerPayloads): void
    {
        $existing = $class->classTrainers()->withTrashed()->get()->keyBy('id');
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
                $assignment = new GymPTClassTrainer();
                $assignment->class_id = $class->id;
                $assignment->branch_setting_id = $class->branch_setting_id ?? ($this->user_sw->branch_setting_id ?? null);
            }

            $assignment->trainer_id = $trainerId;
            $assignment->session_type = $payload['session_type'] ?? null;
            $assignment->session_count = (int) ($payload['session_count'] ?? 0);
            $assignment->commission_rate = (float) ($payload['commission_rate'] ?? 0);
            $assignment->is_active = array_key_exists('is_active', $payload) ? (bool) $payload['is_active'] : true;

            if ($this->classTrainerHasScheduleColumn && array_key_exists('schedule', $payload)) {
                $schedule = $payload['schedule'];
                if (is_string($schedule) && $schedule !== '') {
                    $schedule = json_decode($schedule, true) ?: [];
                }
                $assignment->schedule = $schedule ?: null;
            }

            if ($this->classTrainerHasDateFromColumn && array_key_exists('date_from', $payload)) {
                $assignment->date_from = $payload['date_from'] ?: null;
            }

            if ($this->classTrainerHasDateToColumn && array_key_exists('date_to', $payload)) {
                $assignment->date_to = $payload['date_to'] ?: null;
            }

            $assignment->save();
            $processedIds[] = $assignment->id;
        }

        if ($hasMeaningfulRow) {
            if (!empty($processedIds)) {
                $class->classTrainers()
                    ->whereNotIn('id', $processedIds)
                    ->delete();
            } else {
                $class->classTrainers()->delete();
            }
        }
    }
}


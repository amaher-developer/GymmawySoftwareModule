<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Generic\Http\Controllers\Front\GenericFrontController;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Exports\RecordsExport;
use Modules\Software\Http\Requests\GymPTTrainerRequest;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Models\GymPTClass;
use Modules\Software\Models\GymPTSubscription;
use Modules\Software\Models\GymPTTrainer;
use Modules\Software\Models\GymPTClassTrainer;
use Modules\Software\Models\GymPTMemberAttendee;
use Modules\Software\Models\GymPTCommission;
use Modules\Software\Models\GymUserLog;
use Modules\Software\Repositories\GymPTTrainerRepository;
use Modules\Software\Services\PT\PTCommissionService;
use Modules\Software\Services\PT\PTSessionService;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Illuminate\Container\Container as Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Maatwebsite\Excel\Facades\Excel;

class GymPTTrainerFrontController extends GymGenericFrontController
{
    public $TrainerRepository;
    private $imageManager;
    public $fileName;
    protected PTCommissionService $commissionService;
    protected PTSessionService $sessionService;

    public function __construct()
    {
        parent::__construct();
        $this->imageManager = new ImageManager(new Driver());
        $this->TrainerRepository=new GymPTTrainerRepository(new Application);
        $this->TrainerRepository=$this->TrainerRepository->branch();
        $this->commissionService = app(PTCommissionService::class);
        $this->sessionService = app(PTSessionService::class);
    }


    public function index()
    {
        $title = trans('sw.pt_trainers');
        $this->request_array = ['search', 'from', 'to', 'class_id'];
        $search = request('search');
        $trashed = request()->boolean('trashed');

        $commissionFilters = [
            'class_id' => request('class_id') ?: null,
            'from' => request('from') ?: null,
            'to' => request('to') ?: null,
        ];
        if (@$this->user_sw->branch_setting_id) {
            $commissionFilters['branch_setting_id'] = $this->user_sw->branch_setting_id;
        }
        $commissionFilters = array_filter($commissionFilters, static function ($value) {
            return !is_null($value) && $value !== '';
        });

        $pendingSummary = $this->commissionService->summarizePending($commissionFilters);
        $groupedPending = $pendingSummary['grouped'] instanceof \Illuminate\Support\Collection
            ? $pendingSummary['grouped']
            : collect($pendingSummary['grouped']);
        $pendingTotals = [
            'amount' => round($pendingSummary['total_amount'], 2),
            'count' => $pendingSummary['total_count'],
        ];

        $trainersQuery = $this->TrainerRepository
            ->orderBy('id', 'DESC');

        if ($trashed) {
            $trainersQuery->onlyTrashed();
        }

        $trainersQuery->when($search, function ($query) use ($search) {
            $query->where(function($innerQuery) use ($search) {
                $innerQuery->where('id', '=', (int) $search)
                    ->orWhere('name', 'like', "%" . $search . "%");
            });
        });

        $search_query = request()->query();

        if ($this->limit) {
            $trainers = $trainersQuery->paginate($this->limit);
            $collection = collect($trainers->items());
        } else {
            $collection = $trainersQuery->get();
            $trainers = $collection;
        }

        $collection = $collection->map(function (GymPTTrainer $trainer) use ($groupedPending) {
            $summary = $groupedPending->get($trainer->id);
            $trainer->pending_commission_total = round(data_get($summary, 'total_amount', 0), 2);
            $trainer->pending_commission_count = data_get($summary, 'total_count', 0);
            return $trainer;
        });

        if ($trainers instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
            $trainers->setCollection($collection);
            $total = $trainers->total();
        } else {
            $trainers = $collection;
            $total = $collection->count();
        }

        $classes = GymPTClass::branch()
            ->orderBy('name_' . $this->lang, 'asc')
            ->get();

        return view('software::Front.pt_trainer_front_list', [
            'trainers' => $trainers,
            'title' => $title,
            'total' => $total,
            'search_query' => $search_query,
            'pendingTotals' => $pendingTotals,
            'classes' => $classes,
        ]);
    }


    function exportExcel(){
        $records = $this->TrainerRepository->get();
        $this->fileName = 'pt_trainers-' . Carbon::now()->toDateTimeString();

//        $title = trans('sw.pt_trainers');
//        $records = $this->prepareForExport($records);


        $notes = trans('sw.export_excel_pt_trainers');
        $this->userLog($notes, TypeConstants::ExportActivityExcel);

        return Excel::download(new RecordsExport(['records' => $records, 'keys' => ['name', 'price'],'lang' => $this->lang]), $this->fileName.'.xlsx');

//        Excel::create($this->fileName, function($excel) use ($records, $title) {
//            $excel->setTitle($title);
////            $excel->setCreator($title)->setCompany($title);
//            $excel->setDescription(trans('sw.pt_trainers_data'));
//            $excel->sheet(trans('sw.pt_trainers_data'), function($sheet) use ($records) {
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
        array_unshift($result, [trans('sw.pt_trainers')]);
        return $result;
    }
    function exportPDF(){
        $records = $this->TrainerRepository->get();
        $this->fileName = 'pt_trainers-' . Carbon::now()->toDateTimeString();

        $keys = ['name', 'price'];
        if($this->lang == 'ar') $keys = array_reverse($keys);

        $title = trans('sw.pt_trainers');
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
                
                $notes = trans('sw.export_pdf_pt_trainers');
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

        $notes = trans('sw.export_pdf_pt_trainers');
        $this->userLog($notes, TypeConstants::ExportActivityPDF);

        return $pdf->download($this->fileName.'.pdf');
    }


    public function create()
    {
        $title = trans('sw.pt_trainer_add');
        return view('software::Front.pt_trainer_front_form', [
            'trainer' => new GymPTTrainer(),
            'title' => $title,
        ]);
    }

    public function store(GymPTTrainerRequest $request)
    {
        $trainer_inputs = $this->prepare_inputs($request->except(['_token']));
        $trainer = $this->TrainerRepository->create($trainer_inputs);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);

        $notes = str_replace(':name', $trainer_inputs['name'], trans('sw.add_pt_trainer'));
        $this->userLog($notes, TypeConstants::CreatePTTrainer);
        return redirect(route('sw.listPTTrainer'));
    }

    public function edit($id)
    {
        $trainer = $this->TrainerRepository->withTrashed()->find($id);
        $title = trans('sw.pt_trainer_edit');
        return view('software::Front.pt_trainer_front_form', [
            'trainer' => $trainer,
            'title' => $title,
        ]);
    }

    public function update(GymPTTrainerRequest $request, $id)
    {
        $trainer =$this->TrainerRepository->withTrashed()->find($id);
        $trainer_inputs = $this->prepare_inputs($request->except(['_token']));
        $trainer->update($trainer_inputs);

        $notes = str_replace(':name', $trainer['name'], trans('sw.edit_activity'));
        $this->userLog($notes, TypeConstants::EditPTTrainer);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listPTTrainer'));
    }

    public function destroy($id)
    {
        $trainer =$this->TrainerRepository->withTrashed()->find($id);
        if($trainer->trashed())
        {
            $trainer->restore();
        }
        else
        {
            $trainer->delete();

            $notes = str_replace(':name', $trainer['name'], trans('sw.delete_activity'));
            $this->userLog($notes, TypeConstants::DeletePTTrainer);
        }
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listPTTrainer'));
    }

    private function prepare_inputs($inputs)
    {
        $input_file = 'image';
        if (request()->hasFile($input_file)) {
            $file = request()->file($input_file);
            $filename = rand(0, 20000) . time() . '.' . $file->getClientOriginalExtension();
            $destinationPath = base_path(GymPTTrainer::$uploads_path);

            $upload_success = $this->imageManager->read($file)->scale(width: 120)->toJpeg()->save($destinationPath.$filename);

//            $upload_success = $file->move($destinationPath, $filename);
            if ($upload_success) {
                $inputs[$input_file] = $filename;
            }
        } else {
            unset($inputs[$input_file]);
        }
        // Handle text fields - convert null/empty values to empty strings to avoid null constraint violations
        if (isset($inputs['content_ar'])) {
            $inputs['content_ar'] = $inputs['content_ar'] !== null ? $inputs['content_ar'] : '';
        } else {
            $inputs['content_ar'] = '';
        }
        if (isset($inputs['content_en'])) {
            $inputs['content_en'] = $inputs['content_en'] !== null ? $inputs['content_en'] : '';
        } else {
            $inputs['content_en'] = '';
        }


        if(@$this->user_sw->branch_setting_id){
            $inputs['branch_setting_id'] = @$this->user_sw->branch_setting_id;
        }
        return $inputs;
    }


    public function reports()
    {
        $title = trans('sw.pt_training_calender');
        $this->request_array = ['from', 'to',  'pt_class_id', 'pt_trainer'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;

        $branchId = @$this->user_sw->branch_setting_id;

        $rangeStart = $from ? Carbon::parse($from)->startOfDay() : Carbon::now()->startOfWeek();
        $rangeEnd = $to
            ? Carbon::parse($to)->endOfDay()
            : $rangeStart->copy()->addWeek();

        $trainerAssignments = GymPTClassTrainer::with(['class', 'trainer'])
            ->where('is_active', true)
            ->when($branchId, function ($query, $branchId) {
                $query->where('branch_setting_id', $branchId);
            })
            ->when($pt_class_id, function ($query) use ($pt_class_id) {
                $query->where('class_id', $pt_class_id);
            })
            ->when($pt_trainer, function ($query) use ($pt_trainer) {
                $query->where('trainer_id', $pt_trainer);
            })
            ->get();

        $classesWithSchedule = GymPTClass::branch()
            ->with('pt_subscription')
            ->when($pt_class_id, function ($query) use ($pt_class_id) {
                $query->where('id', $pt_class_id);
            })
            ->whereNotNull('schedule')
            ->get();

        foreach ($classesWithSchedule as $classModel) {
            $already = $trainerAssignments->firstWhere('class_id', $classModel->id);
            if ($already) {
                continue;
            }

            $trainerAssignments->push($this->makeVirtualAssignment($classModel));
        }

        $attendanceLookup = GymPTMemberAttendee::with('pt_member')
            ->whereBetween('session_date', [$rangeStart, $rangeEnd])
            ->get()
            ->groupBy(function (GymPTMemberAttendee $attendee) {
                $member = $attendee->pt_member;
                if (!$member || !$attendee->session_date) {
                    return null;
                }

                $classId = $member->class_id ?? $member->pt_class_id ?? 0;
                $trainerId = $member->class_trainer_id ?? 0;

                return $this->buildTimelineKey($classId, $trainerId, $attendee->session_date);
            })
            ->filter(fn ($value, $key) => !is_null($key));

        $timeline = $this->sessionService->resolveVirtualTimeline($trainerAssignments, $rangeStart, $rangeEnd);

        $reservations = $timeline->map(function (object $entry) use ($attendanceLookup) {
            $classModel = $entry->class;
            $assignment = $entry->class_trainer;
            $trainer = $entry->trainer;
            $slot = $entry->slot;

            $key = $this->buildTimelineKey($classModel->id, $assignment?->id ?? 0, $slot);
            $attendeeCount = isset($attendanceLookup[$key]) ? $attendanceLookup[$key]->count() : 0;
            $duration = $classModel->session_duration ?? 60;

            $trainerName = optional($trainer)->name ?? trans('sw.unassigned_trainer');

            return [
                'title' => trim(($classModel->name ?? trans('sw.pt_class')) . ' - ' . $trainerName),
                'start' => $slot->format('Y-m-d H:i:s'),
                'end' => $slot->copy()->addMinutes($duration)->format('Y-m-d H:i:s'),
                'background_color' => $classModel->class_color ?? '',
                'pt_class_id' => $classModel->id,
                'pt_trainer_id' => $assignment?->trainer_id,
                'session_token' => $this->sessionService->encodeVirtualSessionId($classModel, $assignment, $slot),
                'status' => $attendeeCount > 0 ? 'completed' : 'pending',
                'attendee_count' => $attendeeCount,
            ];
        })->values()->toArray();

        $pt_trainers = GymPTTrainer::branch()->get();
        $subscriptions = GymPTSubscription::branch()->with('pt_classes')->get();
        $classes = GymPTClass::branch()->get();
        return view('software::Front.pt_trainer_front_reports', [
            'pt_trainers' => $pt_trainers,
            'classes' => $classes,
            'subscriptions' => $subscriptions,
            'trainer' => new GymPTTrainer(),
            'reservations' => $reservations,
            'title' => $title,
        ]);
    }


    public function pendingCommissions(Request $request, GymPTTrainer $trainer)
    {
        $filters = [
            'trainer_id' => $trainer->id,
            'class_id' => $request->input('class_id'),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
        ];
        if (@$this->user_sw->branch_setting_id) {
            $filters['branch_setting_id'] = $this->user_sw->branch_setting_id;
        }

        $summary = $this->commissionService->summarizePending(array_filter($filters));
        $group = $summary['grouped'] instanceof \Illuminate\Support\Collection
            ? $summary['grouped']->get($trainer->id)
            : null;

        $commissions = collect(data_get($group, 'commissions', []))->map(function (GymPTCommission $commission) {
            $member = $commission->member;
            $memberRecord = optional($member)->member;
            $attendee = $commission->attendee;
            $class = optional($attendee?->pt_member?->pt_class);

            // Handle session_date formatting
            $sessionDate = null;
            if ($attendee && $attendee->session_date) {
                try {
                    $sessionDate = Carbon::parse($attendee->session_date)->format('Y-m-d H:i');
                } catch (\Exception $e) {
                    $sessionDate = $attendee->session_date;
                }
            } elseif ($commission->session_date) {
                try {
                    $sessionDate = Carbon::parse($commission->session_date)->format('Y-m-d H:i');
                } catch (\Exception $e) {
                    $sessionDate = $commission->session_date;
                }
            }

            return [
                'id' => $commission->id,
                'session_date' => $sessionDate ?? '-',
                'member_name' => optional($memberRecord)->name ?? '-',
                'member_code' => optional($memberRecord)->code ?? '',
                'class_name' => optional($class)->name ?? '-',
                'commission_amount' => number_format($commission->commission_amount ?? 0, 2),
                'commission_rate' => number_format($commission->commission_rate ?? 0, 2),
            ];
        })->values();

        return response()->json([
            'commissions' => $commissions,
            'totals' => [
                'amount' => round(data_get($group, 'total_amount', 0), 2),
                'count' => data_get($group, 'total_count', 0),
            ],
        ]);
    }


    public function createTrainerPayPercentageAmountForm(Request $request)
    {
        $trainerId = (int) ($request->input('trainer_id') ?? $request->input('id'));

        $trainer = $this->TrainerRepository->withTrashed()->find($trainerId);
        if (!$trainer) {
            return response()->json([
                'status' => false,
                'message' => trans('sw.no_record_found'),
            ], 404);
        }

        $commissionIds = $request->input('commission_ids', []);
        if (is_string($commissionIds)) {
            $commissionIds = array_filter(array_map('intval', explode(',', $commissionIds)));
        }
        if (!is_array($commissionIds)) {
            $commissionIds = [];
        }

        $filters = [
            'class_id' => $request->input('class_id'),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
        ];
        if (@$this->user_sw->branch_setting_id) {
            $filters['branch_setting_id'] = $this->user_sw->branch_setting_id;
        }

        $pendingQuery = $trainer->commissions()
            ->where('status', 'pending')
            ->when(@$this->user_sw->branch_setting_id, function ($query, $branchId) {
                $query->where('branch_setting_id', $branchId);
            });

        if (!empty($filters['class_id'])) {
            $pendingQuery->whereHas('attendee.pt_member', function ($query) use ($filters) {
                $query->where('class_id', $filters['class_id'])
                    ->orWhere('pt_class_id', $filters['class_id']);
            });
        }

        if (!empty($filters['from'])) {
            $pendingQuery->where(function ($query) use ($filters) {
                $query->whereDate('session_date', '>=', Carbon::parse($filters['from'])->format('Y-m-d'))
                    ->orWhere(function ($inner) use ($filters) {
                        $inner->whereNull('session_date')
                            ->whereDate('created_at', '>=', Carbon::parse($filters['from'])->format('Y-m-d'));
                    });
            });
        }

        if (!empty($filters['to'])) {
            $pendingQuery->where(function ($query) use ($filters) {
                $query->whereDate('session_date', '<=', Carbon::parse($filters['to'])->format('Y-m-d'))
                    ->orWhere(function ($inner) use ($filters) {
                        $inner->whereNull('session_date')
                            ->whereDate('created_at', '<=', Carbon::parse($filters['to'])->format('Y-m-d'));
                    });
            });
        }

        if (!empty($commissionIds)) {
            $pendingQuery->whereIn('id', $commissionIds);
        }

        $commissions = $pendingQuery
            ->with(['member.member', 'attendee.pt_member.pt_class'])
            ->get();

        if ($commissions->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => trans('sw.no_commissions_available'),
            ], 422);
        }

        $totalAmount = round($commissions->sum('commission_amount'), 2);
        $totalCount = $commissions->count();

        DB::transaction(function () use ($trainer, $commissions, $totalAmount) {
            $commissionIds = $commissions->pluck('id')->all();
            $this->commissionService->settleCommissionsForTrainer(
                $trainer,
                $commissionIds,
                optional(Auth::guard('sw')->user())->id
            );

            // Mirror the legacy money box behaviour while sourcing amounts from the new ledger.
            $vatPercentage = (float) data_get($this->mainSettings, 'vat_details.vat_percentage', 0);
            $vatAmount = $vatPercentage > 0
                ? round(($totalAmount * ($vatPercentage / 100)) / (1 + ($vatPercentage / 100)), 2)
                : 0;

            $amount_box = GymMoneyBox::branch()->latest()->first();
            $amount_after = GymMoneyBoxFrontController::amountAfter(
                (float) optional($amount_box)->amount,
                (float) optional($amount_box)->amount_before,
                (int) optional($amount_box)->operation
            );

            $notes = trans('sw.trainer_commission_payout_note', [
                'trainer' => $trainer->name,
                'sessions' => $commissions->count(),
                'amount' => number_format($totalAmount, 2),
            ]);

            GymMoneyBox::create([
                'user_id' => optional(Auth::guard('sw')->user())->id,
                'amount' => (float) $totalAmount,
                'vat' => $vatAmount,
                'operation' => TypeConstants::Sub,
                'amount_before' => $amount_after,
                'notes' => $notes,
                'type' => TypeConstants::PayPTTrainerCommission,
                'payment_type' => TypeConstants::CASH_PAYMENT,
                'member_id' => null,
                'member_pt_subscription_id' => null,
                'branch_setting_id' => @$this->user_sw->branch_setting_id,
            ]);

            $this->userLog($notes, TypeConstants::CreateMoneyBoxAdd);
        });

        $remainingQuery = $trainer->commissions()
            ->where('status', 'pending')
            ->when(@$this->user_sw->branch_setting_id, function ($query, $branchId) {
                $query->where('branch_setting_id', $branchId);
            });

        if (!empty($filters['class_id'])) {
            $remainingQuery->whereHas('attendee.pt_member', function ($query) use ($filters) {
                $query->where('class_id', $filters['class_id'])
                    ->orWhere('pt_class_id', $filters['class_id']);
            });
        }

        if (!empty($filters['from'])) {
            $remainingQuery->whereDate('created_at', '>=', Carbon::parse($filters['from'])->format('Y-m-d'));
        }

        if (!empty($filters['to'])) {
            $remainingQuery->whereDate('created_at', '<=', Carbon::parse($filters['to'])->format('Y-m-d'));
        }

        $remainingAmount = $remainingQuery->sum('commission_amount');
        $remainingCount = $remainingQuery->count();

        $pendingTotals = $this->commissionService->summarizePending(array_filter($filters));

        return response()->json([
            'status' => true,
            'message' => trans('admin.successfully_paid'),
            'paid_total' => $totalAmount,
            'paid_count' => $totalCount,
            'remaining' => [
                'amount' => round($remainingAmount, 2),
                'count' => $remainingCount,
            ],
            'pending_totals' => [
                'amount' => round($pendingTotals['total_amount'], 2),
                'count' => $pendingTotals['total_count'],
            ],
        ]);
    }

    protected function makeVirtualAssignment(GymPTClass $class): GymPTClassTrainer
    {
        $assignment = new GymPTClassTrainer([
            'id' => null,
            'class_id' => $class->id,
            'trainer_id' => null,
            'is_active' => true,
        ]);

        $assignment->setRelation('class', $class);

        return $assignment;
    }

    protected function buildTimelineKey(int $classId, int $trainerId, Carbon $slot): string
    {
        return "{$classId}|{$trainerId}|" . $slot->format('Y-m-d H:i:s');
    }
}

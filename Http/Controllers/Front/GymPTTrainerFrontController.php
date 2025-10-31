<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Generic\Http\Controllers\Front\GenericFrontController;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Exports\RecordsExport;
use Modules\Software\Http\Requests\GymActivityRequest;
use Modules\Software\Http\Requests\GymPTTrainerRequest;
use Modules\Software\Models\GymActivity;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Models\GymPTClass;
use Modules\Software\Models\GymPTMember;
use Modules\Software\Models\GymPTSubscription;
use Modules\Software\Models\GymPTSubscriptionTrainer;
use Modules\Software\Models\GymPTTrainer;
use Modules\Software\Models\GymUserLog;
use Modules\Software\Repositories\GymPTTrainerRepository;
use Modules\Software\Repositories\GymTrainerRepository;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Container\Container as Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Maatwebsite\Excel\Facades\Excel;

class GymPTTrainerFrontController extends GymGenericFrontController
{
    public $TrainerRepository;
    private $imageManager;
    public $fileName;

    public function __construct()
    {
        parent::__construct();
        $this->imageManager = new ImageManager(new Driver());
        $this->TrainerRepository=new GymPTTrainerRepository(new Application);
        $this->TrainerRepository=$this->TrainerRepository->branch();
    }


    public function index()
    {
        $title = trans('sw.pt_trainers');
        $this->request_array = ['search'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $trainers = $this->TrainerRepository->with(['pt_subscriptions', 'pt_members.pt_class.pt_subscription' => function($q){ $q->withTrashed();}, 'pt_members.member' => function($q){ $q->withTrashed();}, 'pt_members_trainer_amount_status_false.member' => function ($q){$q->withTrashed();},  'pt_members_trainer_amount_status_false' => function ($q){ $q->withTrashed();}])->onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $trainers = $this->TrainerRepository->with(['pt_subscriptions', 'pt_members.pt_class.pt_subscription' => function($q){ $q->withTrashed();}, 'pt_members.member' => function($q){ $q->withTrashed();}, 'pt_members_trainer_amount_status_false.member' => function ($q){$q->withTrashed();}, 'pt_members_trainer_amount_status_false.pt_class' => function ($q){ $q->withTrashed();}])->orderBy('id', 'DESC');
        }

        //apply filters
        $trainers->when($search, function ($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('id', '=', (int)$search);
                $query->orWhere('name', 'like', "%" . $search . "%");
            });
        });
        $search_query = request()->query();

        if ($this->limit) {
            $trainers = $trainers->paginate($this->limit);
            $total = $trainers->total();
        } else {
            $trainers = $trainers->get();
            $total = $trainers->count();
        }

        return view('software::Front.pt_trainer_front_list', compact('trainers','title', 'total', 'search_query'));
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
        $subscriptions = GymPTSubscription::branch()->with('pt_classes')->get();
        return view('software::Front.pt_trainer_front_form', ['trainer' => new GymPTTrainer(), 'subscriptions' => $subscriptions, 'title'=>$title]);
    }

    public function store(GymPTTrainerRequest $request)
    {
        $reservation_details = @$request->reservation_details;
        $class_ids = @$request->class_ids;
        $trainer_inputs = $this->prepare_inputs($request->except(['_token', 'reservation_details', 'class_ids']));
        $trainer = $this->TrainerRepository->create($trainer_inputs);

        if((is_array($reservation_details) && count($reservation_details) > 0) && (is_array($class_ids) && count($class_ids) > 0) && @$trainer->id){
            foreach ($reservation_details as $key => $reservation_detail){

                $reservation_detail_split = explode('@@', $reservation_detail);
                $reservation_detail_split = array_filter($reservation_detail_split);
                $reservation_detail_data = [];

                foreach($reservation_detail_split as $i => $get_reservation_detail) {
                    $get_reservation_detail_split = explode(',,', $get_reservation_detail);
                    $get_reservation_day = $get_reservation_detail_split[0];
                    $get_reservation_start = $get_reservation_detail_split[1];
                    $get_reservation_end = $get_reservation_detail_split[2];

                    $reservation_detail_data[$get_reservation_day]['start'] = $get_reservation_start;
                    $reservation_detail_data[$get_reservation_day]['end'] = $get_reservation_end;
                    $reservation_detail_data[$get_reservation_day]['status'] = true;

                }

                if (is_array($$reservation_detail_data) && count($$reservation_detail_data) > 0) {
                    $reservation_detail_result = ['work_days' => $reservation_detail_data];
                    GymPTSubscriptionTrainer::branch()->where('pt_class_id', $class_ids[$key])->where('pt_trainer_id', @$trainer->id)->forceDelete();
                    GymPTSubscriptionTrainer::create(
                        ['pt_class_id' => $class_ids[$key], 'pt_trainer_id' => @$trainer->id, 'reservation_details' => $reservation_detail_result, 'branch_setting_id' => $this->user_sw->branch_setting_id]
                    );
                }
            }
        }
//        $trainer->pt_subscriptions()->sync($request->pt_subscriptions);
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
        $trainer = $this->TrainerRepository->with(['pt_subscription_trainer.pt_class.pt_subscription'])->withTrashed()->find($id);
        $subscriptions = GymPTSubscription::branch()->get();
        $selectedPTSubscriptions = @array_filter(array_map(function ($subscription){
            return  $subscription['id'];
        }, $trainer->pt_subscriptions->toArray()));
        $title = trans('sw.pt_trainer_edit');
        return view('software::Front.pt_trainer_front_form', ['trainer' => $trainer, 'selectedPTSubscriptions' => $selectedPTSubscriptions,'subscriptions' => $subscriptions,'title'=>$title]);
    }

    public function update(GymPTTrainerRequest $request, $id)
    {
        $reservation_details = @$request->reservation_details;
        $class_ids = @$request->class_ids;
        $trainer =$this->TrainerRepository->withTrashed()->find($id);
        $trainer_inputs = $this->prepare_inputs($request->except(['_token', 'reservation_details', 'class_ids']));
        $trainer->update($trainer_inputs);
        if((is_array($reservation_details) && count($reservation_details) > 0) && (is_array($class_ids) && count($class_ids) > 0) && @$trainer->id){
            $class_ids = array_filter($class_ids);
            $x = 0;
            foreach ($reservation_details as $key => $reservation_detail){
                if(@$class_ids[$x]) {
                    $reservation_detail_split = explode('@@', $reservation_detail);
                    $reservation_detail_split = array_filter($reservation_detail_split);
                    $reservation_detail_data = [];

                    foreach ($reservation_detail_split as $i => $get_reservation_detail) {
                        $get_reservation_detail_split = explode(',,', $get_reservation_detail);
                        $get_reservation_day = $get_reservation_detail_split[0];
                        $get_reservation_start = $get_reservation_detail_split[1];
                        $get_reservation_end = $get_reservation_detail_split[2];

                        $reservation_detail_data[$get_reservation_day]['start'] = $get_reservation_start;
                        $reservation_detail_data[$get_reservation_day]['end'] = $get_reservation_end;
                        $reservation_detail_data[$get_reservation_day]['status'] = true;

                    }

                    if (is_array($$reservation_detail_data) && count($$reservation_detail_data) > 0) {
                        $reservation_detail_result = ['work_days' => $reservation_detail_data];
                        GymPTSubscriptionTrainer::branch()->where('pt_class_id', $class_ids[$key])->where('pt_trainer_id', @$trainer->id)->forceDelete();
                        GymPTSubscriptionTrainer::create(
                            ['pt_class_id' => $class_ids[$key], 'pt_trainer_id' => @$trainer->id, 'reservation_details' => $reservation_detail_result, 'branch_setting_id' => $this->user_sw->branch_setting_id]
                        );
                    }
                }
            }
        }
//        $trainer->pt_subscriptions()->sync($request->pt_subscriptions);

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

        $pt_subscription_trainers = GymPTSubscriptionTrainer::branch()->with(['pt_class.pt_subscription','pt_trainer', 'pt_class.pt_members']);
        $pt_subscription_trainers = $pt_subscription_trainers->when(intval(@$pt_class_id), function ($query) use ($pt_class_id) {
                $query->where('pt_class_id', $pt_class_id);
            })->when((@$pt_trainer), function ($query) use ($pt_trainer) {
                $query->where('pt_trainer_id', $pt_trainer);
            });
            if(@$from && @$to) {
                $pt_subscription_trainers = $pt_subscription_trainers->when(($from), function ($query) use ($from) {
                    $query->whereDate('date_from', '>=', Carbon::parse($from)->format('Y-m-d'));
                })->when(($to), function ($query) use ($to) {
                    $query->whereDate('date_to', '<=', Carbon::parse($to)->format('Y-m-d'));
                });
            }
            $pt_subscription_trainers = $pt_subscription_trainers->get();
        $result = [];
        $i = 0;
        foreach ($pt_subscription_trainers as $pt_subscription_trainer){
            if(@$pt_subscription_trainer->pt_class && $pt_subscription_trainer->reservation_details) {
                $from = \Carbon\Carbon::parse($pt_subscription_trainer->date_from)->toDateString();
                $to = \Carbon\Carbon::parse($pt_subscription_trainer->date_from)->addMonth()->toDateString();
                if(@$pt_subscription_trainer->date_to)
                    $to = \Carbon\Carbon::parse($pt_subscription_trainer->date_to)->toDateString();
                $dateRange = CarbonPeriod::create($from, $to);
                foreach ($pt_subscription_trainer->reservation_details['work_days'] as $index => $pt_subscription) {
                    foreach($dateRange as $date){
                        if($date->dayOfWeek == $index){
                            $result[$i]['title'] = @$pt_subscription_trainer->pt_class->name; //@$pt_subscription_trainer->pt_class->pt_subscription->name .' - '.trim($pt_subscription_trainer->pt_trainer->name).' ( ' . @$pt_subscription_trainer->pt_class->classes . ' ' . trans('sw.pt_class_2').' ) ';
                            $result[$i]['start'] = $date->toDateString().' '.$pt_subscription['start'];
                            $result[$i]['end'] = $date->toDateString().' '.$pt_subscription['end'];
                            $result[$i]['background_color'] = @$pt_subscription_trainer->pt_class->class_color ?? '';
                            $result[$i]['pt_class_id'] = @$pt_subscription_trainer->pt_class_id;
                            $result[$i]['pt_trainer_id'] = @$pt_subscription_trainer->pt_trainer_id;
                            $result[$i]['id'] = @$pt_subscription_trainer->id;
                            $i++;
                        }
                    }
                }
            }
        }
        $reservations = $result;

        $pt_trainers = GymPTTrainer::branch()->get();
        $subscriptions = GymPTSubscription::branch()->with('pt_classes')->get();
        $classes = GymPTClass::branch()->get();
        return view('software::Front.pt_trainer_front_reports', ['pt_trainers' => $pt_trainers, 'classes' => $classes, 'subscriptions' => $subscriptions,'trainer' => new GymPTTrainer(), 'reservations' => $reservations, 'title'=>$title]);
    }


    public function createTrainerPayPercentageAmountForm(){
        $member_id = request('id');
        $member = GymPTMember::where('id', $member_id)->first();
        if(!$member){
            return Response::json(['status' => false, 'message' => trans('sw.no_record_found')], 404);
        }
        // Prevent duplicate payment
        if((int)@$member->trainer_amount_status === 1){
            return Response::json(['status' => false, 'message' => trans('sw.already_paid')], 409);
        }

        if($member){
            $trainer_amount_paid = $member->trainer_percentage / 100 * ($member->amount_paid - $member->vat);
            $member->trainer_amount_status = 1;
            $member->trainer_amount_paid = $trainer_amount_paid;
            $member->save();


            $amount_box = GymMoneyBox::branch()->latest()->first();
            $amount_after = GymMoneyBoxFrontController::amountAfter( (float)@$amount_box->amount, (float)@$amount_box->amount_before, (int)@$amount_box->operation);
            $notes = trans('sw.pt_trainer_moneybox_add_msg', ['subscription' => $member->pt_subscription->name, 'member' => $member->member->name, 'amount_paid' => (float)($trainer_amount_paid), 'trainer_name' => $member->pt_trainer->name, 'trainer_percentage' => $member->trainer_percentage]);

            $moneyBox = GymMoneyBox::create([
                'user_id' => Auth::guard('sw')->user()->id
                , 'amount' => @(float)$trainer_amount_paid
                , 'vat' => ((@(float)$trainer_amount_paid * (@$this->mainSettings->vat_details['vat_percentage'] / 100)) / (1 + (@$this->mainSettings->vat_details['vat_percentage'] / 100)))
                , 'operation' => TypeConstants::Sub
                , 'amount_before' => $amount_after
                , 'notes' => $notes
                , 'type' => TypeConstants::PayPTTrainerCommission
                , 'payment_type' => TypeConstants::CASH_PAYMENT
                , 'member_id' => @$member->member->member_id
                , 'member_pt_subscription_id' => @$member->id
                , 'branch_setting_id' => @$this->user_sw->branch_setting_id
            ]);

            $this->userLog($notes, TypeConstants::CreateMoneyBoxAdd);

        }
        return '1';
    }

    public function createSubscription()
    {
        $subscriptions = GymPTSubscription::branch()->get();
        $selectedPTSubscriptions = [];
        $title = trans('sw.add_pt_trainer_schedule');
        return view('software::Front.pt_trainer_subscription_front_form', [
            'selectedPTSubscriptions' => $selectedPTSubscriptions,
            'subscriptions' => $subscriptions,
            'title' => $title
        ]);
    }

    public function storeSubscription(Request $request)
    {
        $reservation_details = @$request->reservation_details;
        $class_ids = @$request->class_ids;
        $class_ids = @array_filter($class_ids);
        $reservation_check = false;
        $reservation_detail_result = [];

        if (is_array($reservation_details) && count($reservation_details) > 0) {
            foreach ($reservation_details as $key => $reservation_detail) {
                if (@$reservation_detail['start'] && @$reservation_detail['end']) {
                    $reservation_check = true;
                    $reservation_detail_result[$key] = $reservation_detail;
                }
            }
        }

        if ($reservation_check) {
            $trainer = new GymPTTrainer();
            $trainer->reservation_details = $reservation_detail_result;
            $trainer->save();

            if (is_array($class_ids) && count($class_ids) > 0) {
                $trainer->pt_subscriptions()->sync($class_ids);
            }

            session()->flash('sweet_flash_message', [
                'type' => 'success',
                'title' => trans('sw.success'),
                'text' => trans('sw.pt_trainer_schedule_added_successfully')
            ]);
        } else {
            session()->flash('sweet_flash_message', [
                'type' => 'error',
                'title' => trans('sw.error'),
                'text' => trans('sw.please_select_at_least_one_time_slot')
            ]);
        }

        return redirect()->route('sw.listPTTrainer');
    }

    public function showSubscription($id)
    {
        $subscription = GymPTSubscription::branch()->with(['pt_trainers'])->find($id);
        
        if (!$subscription) {
            session()->flash('sweet_flash_message', [
                'type' => 'error',
                'title' => trans('sw.error'),
                'text' => trans('sw.subscription_not_found')
            ]);
            return redirect()->route('sw.listPTTrainer');
        }
        
        $title = trans('sw.view_pt_trainer_subscription', ['name' => $subscription->name_en]);
        
        return view('software::Front.pt_trainer_subscription_front_show', [
            'subscription' => $subscription,
            'title' => $title
        ]);
    }

    public function destroySubscription($id)
    {
        $subscription = GymPTSubscription::branch()->find($id);
        
        if (!$subscription) {
            session()->flash('sweet_flash_message', [
                'type' => 'error',
                'title' => trans('sw.error'),
                'text' => trans('sw.subscription_not_found')
            ]);
            return redirect()->route('sw.listPTTrainer');
        }
        
        try {
            // Soft delete the subscription
            $subscription->delete();
            
            session()->flash('sweet_flash_message', [
                'type' => 'success',
                'title' => trans('sw.success'),
                'text' => trans('sw.pt_trainer_subscription_deleted_successfully')
            ]);
        } catch (\Exception $e) {
            session()->flash('sweet_flash_message', [
                'type' => 'error',
                'title' => trans('sw.error'),
                'text' => trans('sw.error_deleting_subscription')
            ]);
        }
        
        return redirect()->route('sw.listPTTrainer');
    }

    public function editSubscription($id, Request $request)
    {
        $trainer = $this->TrainerRepository->with(['pt_subscription_trainer.pt_class.pt_subscription'])->withTrashed()->find($id);
        
        // Handle search functionality
        $search = $request->get('search');
        $query = GymPTSubscription::branch();
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name_en', 'like', '%' . $search . '%')
                  ->orWhere('name_ar', 'like', '%' . $search . '%')
                  ->orWhere('price', 'like', '%' . $search . '%');
            });
        }
        
        $subscriptions = $query->paginate(10);
        $selectedPTSubscriptions = @array_filter(array_map(function ($subscription){
            return  $subscription['id'];
        }, $trainer->pt_subscriptions->toArray()));
        $total = $subscriptions->total();
        $title = trans('sw.edit_pt_trainer_schedule', ['name' => $trainer->name]);
        
        // Prepare search query for pagination
        $search_query = $request->only(['search']);
        
        return view('software::Front.pt_trainer_subscription_front_list', [
            'trainer' => $trainer, 
            'selectedPTSubscriptions' => $selectedPTSubscriptions,
            'subscriptions' => $subscriptions,
            'total' => $total,
            'title' => $title,
            'search_query' => $search_query
        ]);
    }

    public function updateSubscription(Request $request, $id)
    {
        $reservation_details = @$request->reservation_details;
        $class_ids = @$request->class_ids;
        $class_ids = @array_filter($class_ids);
        $trainer =$this->TrainerRepository->withTrashed()->find($id);
        $reservation_check = false;
        $reservation_detail_result = [];
        $subscription_trainers = [];

        if((is_array($reservation_details) && count($reservation_details) > 0) && (count($class_ids) > 0) && @$trainer->id){
            $x = 0;
            foreach ($reservation_details as $key => $reservation_detail){
                if(@$class_ids[$x]) {
                    $reservation_detail_split = explode('@@', $reservation_detail);
                    $reservation_detail_split = array_filter($reservation_detail_split);
                    $reservation_detail_data = [];

                    foreach ($reservation_detail_split as $i => $get_reservation_detail) {
                        $get_reservation_detail_split = explode(',,', $get_reservation_detail);
                        $get_reservation_day = $get_reservation_detail_split[0];
                        $get_reservation_start = $get_reservation_detail_split[1];
                        $get_reservation_end = $get_reservation_detail_split[2];

                        $reservation_detail_data[$get_reservation_day]['start'] = $get_reservation_start;
                        $reservation_detail_data[$get_reservation_day]['end'] = $get_reservation_end;
                        $reservation_detail_data[$get_reservation_day]['status'] = true;

                    }
                    if (is_array($$reservation_detail_data) && count($$reservation_detail_data) > 0) {
                        $reservation_check = true;
                        $reservation_detail_result[$key] = ['work_days' => $reservation_detail_data];
                        $subscription_trainers[$key] = ['pt_class_id' => $class_ids[$key], 'pt_trainer_id' => @$trainer->id, 'reservation_details' => $reservation_detail_result[$key], 'branch_setting_id' => $this->user_sw->branch_setting_id];
                    }

                }
            }
        }
        GymPTSubscriptionTrainer::branch()
//                    ->where('pt_class_id', $class_ids[$key])
            ->where('pt_trainer_id', @$trainer->id)->forceDelete();
        if($reservation_check == true){
            foreach ($subscription_trainers as  $subscription_trainer) {
                GymPTSubscriptionTrainer::create(
                    $subscription_trainer
                );
            }
        }
        $notes = str_replace(':name', $trainer['name'], trans('sw.edit_pt_trainer_schedule'));
        $this->userLog($notes, TypeConstants::EditPTTrainerSchedule);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        return redirect(route('sw.editPTTrainerSubscription', $id));
    }

}

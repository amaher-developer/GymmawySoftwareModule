<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Generic\Http\Controllers\Front\GenericFrontController;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Exports\NonMembersExport;
use Modules\Software\Exports\RecordsExport;
use Modules\Software\Http\Requests\GymActivityRequest;
use Modules\Software\Http\Requests\GymNonMemberRequest;
use Modules\Software\Models\GymActivity;
use Modules\Software\Models\GymGroupDiscount;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymMemberSubscription;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Models\GymNonMember;
use Modules\Software\Models\GymNonMemberTime;
use Modules\Software\Models\GymSaleChannel;
use Modules\Software\Models\GymUserLog;
use Modules\Software\Repositories\GymActivityRepository;
use Modules\Software\Repositories\GymNonMemberRepository;
use Modules\Billing\Services\SwBillingService;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Container\Container as Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Maatwebsite\Excel\Facades\Excel;

class GymNonMemberFrontController extends GymGenericFrontController
{

    public $NonMemberRepository;
    private $imageManager;
    public $fileName;
    public $reservation_times;

    public function __construct()
    {
        parent::__construct();
        $this->imageManager = new ImageManager(new Driver());

        $this->NonMemberRepository=new GymNonMemberRepository(new Application);
        $this->NonMemberRepository=$this->NonMemberRepository->branch();
    }


    public function index()
    {

        $title = trans('sw.daily_clients');
        $this->request_array = ['search', 'from', 'to', 'activity'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $members = GymNonMember::branch()->onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $members = GymNonMember::branch()->orderBy('id', 'DESC');
        }
        $members = $members->with(['non_member_times']);
            //apply filters
        $members->when(($from), function ($query) use ($from) {
            $query->whereDate('created_at', '>=', Carbon::parse($from)->format('Y-m-d'));
        })->when(($to), function ($query) use ($to) {
            $query->whereDate('created_at', '<=', Carbon::parse($to)->format('Y-m-d'));
        })->when(($activity), function ($query) use ($activity) {
            $query->whereJsonContains('activities', ['name_'.$this->lang => $activity]);
        })->when($search, function ($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('id', '=', (int)$search);
                $query->orWhere('name', 'like', "%" . $search . "%");
                $query->orWhere('phone', 'like', "%" . $search . "%");
            });
//            $query->whereRaw(' json_extract(activities->"$[*].name_ar", "'.$search.'")');
        });
        $search_query = request()->query();

//        if (request()->exists('export')) {
//            $members = $members->get();
//            $array = $this->prepareForExport($members);
//
//            $fileName = 'non-members-' . Carbon::now()->toDateTimeString();
//            $file = Excel::create($fileName, function ($excel) use ($array) {
//                $excel->setTitle('title');
//                $excel->sheet(trans('sw.non_members_data'), function ($sheet) use ($array) {
//                    if($this->lang == 'ar') $sheet->setRightToLeft(true);
//                    $sheet->fromArray($array);
//                });
//            });
//
//            $file = $file->string('xlsx');
//            return [
//                'name' => $fileName,
//                'file' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64," . base64_encode($file)
//            ];
//        }
        if ($this->limit) {
            $members = $members->paginate($this->limit)->onEachSide(1);
            $total = $members->total();
            $memberIds = $members->pluck('id')->toArray();
        } else {
            $members = $members->get();
            $total = $members->count();
            $memberIds = $members->pluck('id')->toArray();
        }
        
        // Optimize: Use select to limit columns (duration_minutes doesn't exist in activities table)
        $activities = GymActivity::branch()->isSystem()->select('id', 'name_ar', 'name_en')->get();
        
        // Load upcoming reservations for non-members
        $upcomingReservations = [];
        if (!empty($memberIds)) {
            $upcomingReservations = \Modules\Software\Models\GymReservation::branch()
                ->select('id', 'non_member_id', 'activity_id', 'reservation_date', 'start_time', 'end_time', 'status')
                ->where('client_type', 'non_member')
                ->whereIn('non_member_id', $memberIds)
                ->whereDate('reservation_date', '>=', \Carbon\Carbon::today()->format('Y-m-d'))
                ->whereNotIn('status', ['cancelled', 'missed'])
                ->with(['activity' => function($q) {
                    $q->select('id', 'name_ar', 'name_en')->withTrashed();
                }])
                ->orderBy('reservation_date', 'asc')
                ->orderBy('start_time', 'asc')
                ->get()
                ->groupBy('non_member_id');
        }
        
        // Pre-compute active_activity_reservation feature check (move from Blade to Controller)
        $mainSettings = \Illuminate\Support\Facades\View::shared('mainSettings') ?? 
            \Modules\Generic\Models\Setting::where('id', \Modules\Generic\Models\GenericModel::getCurrentBranchId())->first();
        $features = is_array($mainSettings->features ?? null) 
            ? $mainSettings->features 
            : (is_string($mainSettings->features ?? null) 
                ? json_decode($mainSettings->features, true) 
                : []);
        $active_activity_reservation = isset($features['active_activity_reservation']) && $features['active_activity_reservation'];
        
        // Pre-format date inputs (move Carbon parsing from Blade to Controller)
        $formatted_from_date = request('from') ? \Carbon\Carbon::parse(request('from'))->format('Y-m-d') : '';
        $formatted_to_date = request('to') ? \Carbon\Carbon::parse(request('to'))->format('Y-m-d') : '';
        $formatted_search = request('search') ? strip_tags(request('search')) : '';
        
        // Process members to add computed properties (move logic from Blade to Controller)
        if ($members instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
            $members->getCollection()->transform(function($member) use ($upcomingReservations) {
                return $this->processNonMemberForBlade($member, $upcomingReservations);
            });
        } else {
            $members = $members->map(function($member) use ($upcomingReservations) {
                return $this->processNonMemberForBlade($member, $upcomingReservations);
            });
        }
        
        return view('software::Front.nonmember_front_list', compact('members', 'activities','title', 'total', 'search_query', 'upcomingReservations', 'active_activity_reservation', 'formatted_from_date', 'formatted_to_date', 'formatted_search'));
    }

    /**
     * Process non-member data for Blade view (moved from Blade to Controller)
     * Pre-computes values to avoid logic and Carbon parsing in Blade templates
     */
    private function processNonMemberForBlade($member, $upcomingReservations)
    {
        // Pre-compute member reservations count (move from Blade to Controller)
        $member->reservations_count = isset($upcomingReservations[$member->id]) 
            ? $upcomingReservations[$member->id]->count() 
            : 0;
        $member->member_reservations = $upcomingReservations[$member->id] ?? collect();
        
        // Pre-process activities array to extract needed data (move complex logic from Blade to Controller)
        if (!empty($member->activities)) {
            $processedActivities = [];
            $lang = $this->lang ?? 'ar';
            foreach ($member->activities as $activity) {
                $activityId = is_array($activity) ? ($activity['id'] ?? null) : $activity->id ?? null;
                $activityName = is_array($activity) 
                    ? ($activity['name_'.$lang] ?? $activity['name_ar'] ?? $activity['name'] ?? '') 
                    : ($activity->{'name_'.$lang} ?? $activity->name ?? '');
                // Note: duration_minutes doesn't exist in activities table, using default 60
                $duration = 60;
                
                if ($activityId) {
                    $processedActivities[] = [
                        'id' => $activityId,
                        'name' => $activityName,
                        'name_ar' => is_array($activity) ? ($activity['name_ar'] ?? '') : ($activity->name_ar ?? ''),
                        'duration_minutes' => $duration
                    ];
                }
            }
            $member->processed_activities = $processedActivities;
        } else {
            $member->processed_activities = [];
        }
        
        return $member;
    }

    function exportExcel(){

        $this->limit = null;
        $records = $this->index()->with(\request()->all());
        $records = $records->members;

        //$records = $this->NonMemberRepository->get();
        $this->fileName =  'non-members-' . Carbon::now()->toDateTimeString();

//        $title = trans('sw.daily_clients');
//        $records = $this->prepareForExport($records);

        $notes = trans('sw.export_excel_non_members');
        $this->userLog($notes, TypeConstants::ExportNonMemberExcel);

        return Excel::download(new NonMembersExport(['records' => $records, 'keys' => ['name', 'phone', 'activities', 'price', 'date'],'lang' => $this->lang]), $this->fileName.'.xlsx');

//        Excel::create($this->fileName, function($excel) use ($records, $title) {
//            $excel->setTitle($title);
////            $excel->setCreator($title)->setCompany($title);
//            $excel->setDescription(trans('sw.non_members_data'));
//            $excel->sheet(trans('sw.non_members_data'), function($sheet) use ($records) {
//                $sheet->setRightToLeft(true);
//                $sheet->fromArray($records, null, 'A1', false, false);
//                $sheet->mergeCells('A1:E1');
//                $sheet->cells('A1:E1', function ($cells) {
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
        $name = [trans('sw.name'),trans('sw.phone'),trans('sw.activities'), trans('sw.price'), trans('sw.date')];
        $result =   array_map(function ($row) {
//            dd(implode(', ', collect($row['activities'])->pluck('name')->toArray()));
            return  [
                trans('sw.name') => $row['name'],
                trans('sw.phone') => $row['phone'],
                trans('sw.activities') => implode(', ', collect($row['activities'])->pluck('name')->toArray()),
                trans('sw.price') => number_format($row['price'], 2),
                trans('sw.date') => Carbon::parse($row['created_at'])->toDateString()
            ];
        }, $data->toArray());
        array_unshift($result, $name);
        array_unshift($result, [trans('sw.daily_clients')]);
        return $result;
    }

    function exportPDF(){

        $this->limit = null;
        $records = $this->index()->with(\request()->all());
        $records = $records->members;

        $keys = ['name', 'phone', 'activities', 'price', 'created_at'];
        if($this->lang == 'ar') $keys = array_reverse($keys);

        //$records = $this->NonMemberRepository->select($keys)->get();
        $this->fileName =  'non-members-' . Carbon::now()->toDateTimeString();
        foreach ($records as $record){
            $record['activities'] = implode(', ', collect($record['activities'])->pluck('name')->toArray());
            $record['created_at'] = Carbon::parse($record['created_at'])->toDateString();
            $record['price'] = number_format($record['price'], 2);
        }
        $title = trans('sw.daily_clients');
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
                
                $notes = trans('sw.export_pdf_members');
                $this->userLog($notes, \Modules\Software\Classes\TypeConstants::ExportMemberPDF);
                
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
        $pdf = PDF::loadView('software::Front.export_pdf', ['records' => $records, 'title' => $title, 'keys' => $keys])
        ->setPaper($customPaper, 'landscape')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'DejaVu Sans',
            'isPhpEnabled' => true,
            'isJavascriptEnabled' => false
        ]);

        $notes = trans('sw.export_pdf_non_members');
        $this->userLog($notes, TypeConstants::ExportNonMemberPDF);

        return $pdf->download($this->fileName.'.pdf');
    }



    public function create()
    {
        $title = trans('sw.non_member_add');

        $channels = GymSaleChannel::branch()->get();
        $discounts = GymGroupDiscount::branch()->where('is_non_member', true)->get();
        $activities = GymActivity::branch()->isSystem()->get();
        $selectedActivities = []; // Empty array for new non-member
        $billingSettings = SwBillingService::getSettings();

        return view('software::Front.nonmember_front_form', [
            'activities' => $activities,
            'channels' => $channels,
            'discounts' => $discounts,
            'selectedActivities' => $selectedActivities,
            'member' => new GymNonMember(),
            'title' => $title,
            'billingSettings' => $billingSettings,
        ]);
    }

    public function store(GymNonMemberRequest $request)
    {
        $billingSettings = SwBillingService::getSettings();
        $vat = 0;
        $non_member_inputs = $this->prepare_inputs($request->except(['_token']));
        $activities = GymActivity::branch()->whereIn('id', $request->activities);
        $non_member_inputs['amount_before_discount'] = $activities->pluck('price')->sum();
        if(@$this->mainSettings->vat_details['vat_percentage']) {
            $vat = ($non_member_inputs['amount_before_discount'] -  $non_member_inputs['discount_value']) * (@$this->mainSettings->vat_details['vat_percentage'] / 100);
            $non_member_inputs['vat'] = $vat;
        }

        $non_member_inputs['amount_remaining'] = ($activities->pluck('price')->sum() + $vat - $non_member_inputs['price'] - $non_member_inputs['discount_value']) > 0 ? ($activities->pluck('price')->sum() + $vat - $non_member_inputs['price'] - $non_member_inputs['discount_value']) : 0;

        $non_member_inputs['activities'] = json_decode($activities->select(['id', 'name_ar', 'name_en', 'price', 'reservation_limit', 'reservation_duration', 'reservation_period'])->get()->toJson());
        $non_member_inputs['branch_setting_id'] = $this->user_sw->branch_setting_id;
        $non_member_inputs['created_at'] = Carbon::now();
        $non_member_inputs['updated_at'] = Carbon::now();

        $subscription_price = round(($non_member_inputs['amount_before_discount'] - @$non_member_inputs['discount_value'] + $vat), 2);

        if (($non_member_inputs['price'] < 0) || @($non_member_inputs['price'] > $subscription_price)) {
            return redirect(route('sw.createNonMember'))->withErrors(['price' => trans('sw.amount_paid_validate_must_less')]);
        }

        $non_member = $this->NonMemberRepository->create($non_member_inputs);


        $amount_box = GymMoneyBox::branch()->latest()->first();

        $amount_after = $amount_box ? GymMoneyBoxFrontController::amountAfter($amount_box->amount, $amount_box->amount_before, $amount_box->operation) : 0;
        $amount_after = round($amount_after, 2);

        $notes = str_replace(':activities', implode(', ', $activities->pluck('name_'.$this->lang)->toArray()), trans('sw.non_member_moneybox_add_msg'));
        $notes = str_replace(':member', $non_member_inputs['name'] , $notes );
        $notes = str_replace(':amount_paid', @round($non_member_inputs['price'], 2), $notes);
        $notes = str_replace(':amount_remaining', @round($non_member_inputs['amount_remaining'], 2), $notes);

        if ($non_member_inputs['discount_value'])
            $notes = $notes . trans('sw.discount_msg', ['value' => $non_member_inputs['discount_value']]);

        if ($this->mainSettings->vat_details['vat_percentage']) {
            $notes = $notes . ' - ' . trans('sw.vat_added');
        }
        $moneyBoxEntry = GymMoneyBox::create([
            'user_id' => Auth::guard('sw')->user()->id
            ,'amount' => $non_member_inputs['price']
            ,'vat' => @$non_member_inputs['vat']
            , 'operation' => TypeConstants::Add
            , 'amount_before' => $amount_after
            , 'notes' => $notes
            , 'type' => TypeConstants::CreateNonMember
            , 'payment_type' => @$non_member_inputs['payment_type']
            , 'branch_setting_id' => @$this->user_sw->branch_setting_id
            , 'non_member_subscription_id' => @$non_member->id
        ]);

        $this->userLog($notes, TypeConstants::CreateMoneyBoxAdd);

        if (config('sw_billing.zatca_enabled') && config('sw_billing.auto_invoice') && !empty($billingSettings['sections']['non_members'])) {
            try {
                \Log::info('Attempting to create non-member ZATCA invoice', [
                    'non_member_id' => $non_member->id,
                ]);
                $invoice = SwBillingService::createInvoiceFromNonMember($non_member, $moneyBoxEntry);
                if ($invoice) {
                    \Log::info('Non-member ZATCA invoice processed', [
                        'invoice_id' => $invoice->id,
                        'non_member_id' => $non_member->id,
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to create ZATCA invoice for non-member', [
                    'non_member_id' => $non_member->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $notes = str_replace(':name', $non_member_inputs['name'], trans('sw.add_non_member'));
        $this->userLog($notes, TypeConstants::CreateNonMember);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);
        return redirect(route('sw.showOrderSubscriptionNonMember', $non_member->id));
    }

    public function edit($id)
    {
        $member = $this->NonMemberRepository->withTrashed()->find($id);
        $member->load('zatcaInvoice');

        $selectedActivities = @array_filter(array_map(function ($activity){
            return  $activity['name_'.$this->lang];
        }, $member->activities));
        $activities = GymActivity::branch()->isSystem()->get();

        $channels = GymSaleChannel::branch()->get();
        $discounts = GymGroupDiscount::branch()->where('is_non_member', true)->get();
        $title = trans('sw.non_member_edit');
        $billingSettings = SwBillingService::getSettings();
        return view('software::Front.nonmember_front_form', ['member' => $member,
            'channels' => $channels,
            'discounts' => $discounts,
            'activities' => $activities, 'title'=>$title, 'selectedActivities' => $selectedActivities,
            'billingSettings' => $billingSettings,
        ]);
    }

    public function update(GymNonMemberRequest $request, $id)
    {
        $member = $this->NonMemberRepository->withTrashed()->find($id);
        $billingSettings = SwBillingService::getSettings();
        $non_member_inputs = $this->prepare_inputs($request->except(['_token', 'diff_amount_paid_input']));
        $activities = GymActivity::branch()->whereIn('id', $request->activities);
        $vat = 0;

        $non_member_inputs['amount_before_discount'] = $activities->pluck('price')->sum();
        if(@$this->mainSettings->vat_details['vat_percentage']) {
            $vat = ($non_member_inputs['amount_before_discount'] -  $non_member_inputs['discount_value']) * (@$this->mainSettings->vat_details['vat_percentage'] / 100);
            $non_member_inputs['vat'] = $vat;
        }

        $non_member_inputs['amount_remaining'] = ($activities->pluck('price')->sum() + $vat - $non_member_inputs['price'] - $non_member_inputs['discount_value']) > 0 ? ($activities->pluck('price')->sum() + $vat - $non_member_inputs['price'] - $non_member_inputs['discount_value']) : 0;

        $non_member_inputs['activities'] = json_decode($activities->select(['id', 'name_ar', 'name_en', 'price', 'reservation_limit', 'reservation_duration', 'reservation_period'])->get()->toJson());
        $non_member_inputs['updated_at'] = Carbon::now();


        if(@abs($request->diff_amount_paid_input) > 0) {
            $amount_box = GymMoneyBox::branch()->latest()->first();
            $amount_after = GymMoneyBoxFrontController::amountAfter($amount_box->amount, $amount_box->amount_before, $amount_box->operation);
            $amount_after = round($amount_after, 2);

            if($non_member_inputs['price'] > $member->price){
                $amount = abs($request->diff_amount_paid_input);
                $operation = 0;
            }else if($non_member_inputs['price'] < $member->price){
                $amount = abs($request->diff_amount_paid_input);
                $operation = 1;
            }

            $notes = str_replace(':activities', implode(', ', $activities->pluck('name_' . $this->lang)->toArray()), trans('sw.non_member_moneybox_edit_msg'));
            $notes = str_replace(':member', $non_member_inputs['name'], $notes);
            $notes = str_replace(':amount_paid', @$non_member_inputs['price'], $notes);
            $notes = str_replace(':amount_remaining', $non_member_inputs['amount_remaining'], $notes);
            if ($non_member_inputs['discount_value'])
                $notes = $notes . trans('sw.discount_msg', ['value' => $non_member_inputs['discount_value']]);

            if ($this->mainSettings->vat_details['vat_percentage']) {
                $notes = $notes . ' - ' . trans('sw.vat_added');
            }
//            dd($amount_after, $member->price ,$non_member_inputs['price'], $non_member_inputs['price'] - $member->price);
            GymMoneyBox::create([
                'user_id' => Auth::guard('sw')->user()->id
                , 'amount' => $amount
//                , 'vat' => @$non_member_inputs['vat']
                , 'vat' => ((@$amount * (@$this->mainSettings->vat_details['vat_percentage'] / 100)) / (1 + (@$this->mainSettings->vat_details['vat_percentage'] / 100)))
                , 'operation' => $operation
                , 'amount_before' => $amount_after
                , 'notes' => $notes
                , 'type' => TypeConstants::EditNonMember
                , 'branch_setting_id' => @$this->user_sw->branch_setting_id
                , 'non_member_subscription_id' => @$member->id
            ]);

            $this->userLog($notes, TypeConstants::CreateMoneyBoxAdd);

        }


        $member->update($non_member_inputs);
        $member->refresh();

        if (config('sw_billing.zatca_enabled') && config('sw_billing.auto_invoice') && !empty($billingSettings['sections']['non_members'])) {
            try {
                \Log::info('Attempting to update non-member ZATCA invoice', [
                    'non_member_id' => $member->id,
                ]);
                $invoice = SwBillingService::createInvoiceFromNonMember($member);
                if ($invoice) {
                    \Log::info('Non-member ZATCA invoice updated', [
                        'invoice_id' => $invoice->id,
                        'non_member_id' => $member->id,
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to update ZATCA invoice for non-member', [
                    'non_member_id' => $member->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $notes = str_replace(':name', $member['name'], trans('sw.edit_non_member'));
        $this->userLog($notes, TypeConstants::EditNonMember);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listNonMember'));
    }

    public function destroy($id)
    {
        $member =$this->NonMemberRepository->withTrashed()->find($id);
//        if($member->trashed())
//        {
//            $member->restore();
//        }
//        else
//        {
//            $member->delete();
//        }

        $member->delete();

        $amount_box = GymMoneyBox::branch()->latest()->first();
        $amount_after = (int)GymMoneyBoxFrontController::amountAfter($amount_box->amount, $amount_box->amount_before, $amount_box->operation);

        $vat = ($member->vat);
        $amount = ($member->price);
        if(\request('total_amount') && \request('amount') && (\request('total_amount') >= \request('amount') )){
            $amount = \request('amount');
        }

        $activities = @array_filter(array_map(function ($activity){
            return $activity['name_'.$this->lang];
        }, $member['activities']));

        $notes = str_replace(':activities', implode(', ', $activities), trans('sw.non_member_moneybox_delete_msg'));
        $notes = str_replace(':member', $member->name, $notes);
        $notes = str_replace(':amount_paid', $amount, $notes);

        GymMoneyBox::create([
            'user_id' => Auth::guard('sw')->user()->id
            , 'amount' => $amount
            , 'vat' => $vat
            , 'operation' => TypeConstants::Sub
            , 'amount_before' => $amount_after
            , 'notes' => $notes
            , 'type' => TypeConstants::DeleteNonMember
            , 'branch_setting_id' => @$this->user_sw->branch_setting_id
            , 'non_member_subscription_id' => @$member->id
        ]);
        $this->userLog($notes, TypeConstants::CreateMoneyBoxWithdraw);


        $notes = str_replace(':name', $member['name'], trans('sw.delete_non_member'));
        $this->userLog($notes, TypeConstants::DeleteNonMember);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listNonMember'));
    }

    private function prepare_inputs($inputs)
    {
        $input_file = 'image';
        $uploaded='';

        $destinationPath = base_path(GymNonMember::$uploads_path);
        $ThumbnailsDestinationPath = base_path(GymNonMember::$thumbnails_uploads_path);

        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, $mode = 0777, true, true);
        }
        if (!File::exists($ThumbnailsDestinationPath)) {
            File::makeDirectory($ThumbnailsDestinationPath, $mode = 0777, true, true);
        }
        if (request()->hasFile($input_file)) {
            $file = request()->file($input_file);

            if (file_exists($file->getRealPath()) && getimagesize($file->getRealPath()) !== false) {
                $filename = rand(0, 20000) . time() . '.' . $file->getClientOriginalExtension();


                $uploaded = $filename;

                $img = $this->imageManager->read($file);
                $original_width = $img->width();
                $original_height = $img->height();

                if ($original_width > 1200 || $original_height > 900) {
                    if ($original_width < $original_height) {
                        $new_width = 1200;
                        $new_height = ceil($original_height * 900 / $original_width);
                    } else {
                        $new_height = 900;
                        $new_width = ceil($original_width * 1200 / $original_height);
                    }

                    //save used image
                    $img->toJpeg(90)->save($destinationPath . $filename);
                    $img->scale(width: $new_width, height: $new_height)->toJpeg(90)->save($destinationPath . '' . $filename);

                    //create thumbnail
                    if ($original_width < $original_height) {
                        $thumbnails_width = 400;
                        $thumbnails_height = ceil($new_height * 300 / $new_width);
                    } else {
                        $thumbnails_height = 300;
                        $thumbnails_width = ceil($new_width * 400 / $new_height);
                    }
                    $img->scale(width: $thumbnails_width, height: $thumbnails_height)->toJpeg(90)->save($ThumbnailsDestinationPath . '' . $filename);
                } else {
                    //save used image
                    $img->toJpeg(90)->save($destinationPath . $filename);
                    //create thumbnail
                    if ($original_width < $original_height) {
                        $thumbnails_width = 400;
                        $thumbnails_height = ceil($original_height * 300 / $original_width);
                    } else {
                        $thumbnails_height = 300;
                        $thumbnails_width = ceil($original_width * 400 / $original_height);
                    }
                    $img->scale(width: $thumbnails_width, height: $thumbnails_height)->toJpeg(90)->save($ThumbnailsDestinationPath . '' . $filename);
                }
                $inputs[$input_file]=$uploaded;
            }

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
//        !$inputs['deleted_at']?$inputs['deleted_at']=null:'';

        return $inputs;
    }

    public function getNonMemberReservation(){
        $non_member_id = request('non_member_id');
        $activity_id = request('activity_id');
        $start_date = request('start_date');
        $step = request('step');
        $member_type = @request('member_type');
        $reservation_check = 0;

        if($step == 1){
            // prev
            $start_date = Carbon::parse($start_date)->subDays(7)->toDateString();
        }elseif($step == 2){
            // next
            $start_date = Carbon::parse($start_date)->addDays(7)->toDateString();
        }else{
            // now
            $start_date = Carbon::now()->subDay(Carbon::now()->dayOfWeek)->format('Y-m-d');
        }

        if($member_type == 2) {
            $non_member = GymMember::select('id', 'name', 'phone')->with('member_subscription_info')->where('id', $non_member_id)->first();
            $activities = GymMemberSubscription::select('id', 'member_id', 'activities')->where('member_id', $non_member->id)->where('status', TypeConstants::Active)->first();
            if($activities){
                $reservation_arr_check =  array_map(function ($name) use ($activity_id){ static $i = 0;  if(@$name['activity']['id'] == $activity_id){ return (@$name['training_times'] > @$name['visits'] ? 0 : 1); } $i++; }, @$activities->activities ?? []);
                if(in_array(1, $reservation_arr_check)){ $reservation_check = 1;} // exceed all visits
            }else
                $reservation_check = 2; // no activities for this member
        }else
            $non_member = GymNonMember::select('id', 'name', 'phone')->where('id', $non_member_id)->first();

        $activity = GymActivity::where('id', $activity_id)->first();
        $reservation_times = GymNonMemberTime::branch()->with(['member' => function($q){$q->select('id', 'name', 'phone');}, 'non_member' => function($q){$q->select('id', 'name', 'phone');}])
            ;//->where('date', '>=', Carbon::parse(@$start_date)->toDateString());


        $reservation_times = $reservation_times->where('date', '<', Carbon::parse(@$start_date)->addDays(7)->toDateString());
        if(@$non_member->member_subscription_info){
            $reservation_times = $reservation_times->where('member_subscription_id', @$non_member->member_subscription_info->id);
        }
        $reservation_times = $reservation_times->where('activity_id', $activity_id)
            ->orderBy('date', 'ASC')
            ->get();

        $this->reservation_times = [];
        if($reservation_check == 0) {
            if (@$activity->reservation_details['work_days']) {
                foreach (@$activity->reservation_details['work_days'] as $index => $reservation_detail) {

                    $intervals = [];
                    $dayIndex = \Carbon\Carbon::now()->addDays((int)@$index)->dayOfWeek;
                    if (@$reservation_detail['status'])
                        $intervals = \Carbon\CarbonInterval::minutes(@$activity->reservation_duration)->toPeriod(@$reservation_detail['start'], @$reservation_detail['end']);

                    if (count($intervals) > 1) {
                        foreach ($intervals as $i => $date) {
                            if(($i+1) == count($intervals)){break;}
                            $this->reservation_times[$index][][] = '' . $date->format('H:i') . '';
                        }
                    }
                }
            }else{
                $reservation_check = 2;
            }
            $grouped_reservation_times = $reservation_times->groupBy('date');

            $grouped_reservation_times->filter(function ($items) use ($activity) {
                $reservation_time_count = count($items);
                $reservation_time_max = @(int)$activity->reservation_limit;
                foreach ($items as $item) {
                    $dayIndex = \Carbon\Carbon::parse($item->date)->dayOfWeek;
                    $dayTime = (string)$item->time_slot;
                    if ((is_array($this->reservation_times[$dayIndex]) && count($this->reservation_times[$dayIndex])) && ($reservation_time_max <= $reservation_time_count)) {
                        foreach ($this->reservation_times[$dayIndex] as $x => $reservation) {
                            if (@$reservation[0] == $dayTime) {
                                unset($this->reservation_times[$dayIndex][$x]);
                            }
                        }
                    }
                }
            })->values();

        }

        return Response::json(['reservation_check' => @$reservation_check,'start_date' => $start_date, 'non_member' => $non_member, 'activity'=>$activity, 'reservations' => $this->reservation_times, 'member_reservations' => $reservation_times], 200);
    }

    public function createReservationNonMemberAjax(){
        $non_member_id = request('selected_non_member_id');
        $activity_id = request('selected_activity_id');
        $selected_date = request('selected_date');
        $selected_time = request('selected_time');
        $type = @request('type');
        $activity = GymActivity::where('id', $activity_id)->withTrashed()->first();
        if($activity && $non_member_id && $selected_date && $selected_time){
            $reservation = GymNonMemberTime::branch();
            if($type == 2)
                $reservation = $reservation->where('member_id', $non_member_id)->whereDate('date',  Carbon::parse($selected_date)->toDateString())->where('time_slot', @$selected_time);
            else
                $reservation = $reservation->where('non_member_id', $non_member_id);
//                ->where( 'date' , Carbon::parse($selected_date)->toDateString())->where('time_slot', $selected_time)->first();

            $reservation = $reservation->where( 'activity_id' , $activity_id)->first();
            if($reservation){
                return 'exist';
            }else{
                // check on reservation times for member

                if($type == 2) {
                    $member = GymMember::with('member_subscription_info')->where('id', $non_member_id)->first();
                    $member_times_count = GymNonMemberTime::where('member_id', $member->id)->where('member_subscription_id', @$member->member_subscription_info->id)->count();
                    $selected_activity = $this->getActivityByActivityId($member->member_subscription_info->activities, $activity_id);
                    if(@$selected_activity['training_times'] <= @$member_times_count)
                        return 'exceed_limit';

                    $reservation = GymNonMemberTime::create(['user_id' => $this->user_sw->id, 'member_id' => $non_member_id, 'member_subscription_id' => @$member->member_subscription_info->id, 'activity_id' => $activity_id, 'date' => Carbon::parse($selected_date)->toDateString() . ' ' . $selected_time, 'time_slot' => $selected_time, 'expire_date' => Carbon::now()->addDays(@$activity->reservation_period)->toDateString(), 'branch_setting_id' => @$this->user_sw->branch_setting_id]);
                }else
                    $reservation = GymNonMemberTime::create(['user_id' => $this->user_sw->id, 'non_member_id' => $non_member_id, 'activity_id' => $activity_id, 'date' => Carbon::parse($selected_date)->toDateString().' '.$selected_time, 'time_slot' => $selected_time, 'expire_date' => Carbon::now()->addDays(@$activity->reservation_period)->toDateString(), 'branch_setting_id' => @$this->user_sw->branch_setting_id]);

                $countCheck = GymNonMemberTime::branch()->where('date',  Carbon::parse($selected_date)->toDateString())->where('time_slot', @$selected_time)->count();
                if($countCheck >= (int)$activity->reservation_duration){
                    return 'reload';
                }
                return $reservation->id;
            }
        }
        return '0';
    }

    private function getActivityByActivityId($activities, $activity_id){
        foreach ($activities as $activity){
            if($activity['activity_id'] == (int)$activity_id){
                return $activity;
            }
        }
        return [];
    }

    public function deleteReservationNonMemberAjax(){
        $id = request('id');
        $time = request('time');

        if($id){
            $non_member_time = GymNonMemberTime::branch()->where('id', $id)->first();
//            if($non_member_time->member_id){
//                $membership = GymMemberSubscription::where('member_id', $non_member_time->member_id)->where('status', TypeConstants::Active)->first();
//                $visits = 0;
//                $activity_result = [];
//                foreach ($membership->activities as $i => $activity) {
//                    $activity_result[$i] = $activity;
//
//                    if (($activity['activity']['id'] == $non_member_time->activity_id) && ($activity['training_times'] > @(int)$activity['visits'])) {
//                        $visits = @(int)$activity['visits'] - 1;
//                        $activity_result[$i]['visits'] = $visits;
//                    }
//                }
//                $membership->activities = $activity_result;
//                $membership->save();
//            }

            $non_member_time->delete();
            $countCheck = GymNonMemberTime::branch()->where('date', Carbon::now()->toDateString())->where('time_slot', @$time)->count();
            if($countCheck){
                return 'reload';
            }
            return '1';
        }
        return '0';
    }

    public function reports()
    {
        $title = trans('sw.activities_calender');
        $this->request_array = ['from', 'to'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;

        $activities = GymActivity::branch();
//        if(@$from && @$to) {
//            $activities = $activities->when(($from), function ($query) use ($from) {
//                $query->whereDate('date', '>=', Carbon::parse($from)->format('Y-m-d'));
//            })->when(($to), function ($query) use ($to) {
//                $query->whereDate('date', '<=', Carbon::parse($to)->format('Y-m-d'));
//            });
//        }
        $activities = $activities->get();
        $result = [];
        $i = 0;
        foreach ($activities as $activity){
            if(@$activity->reservation_details) {
                $from = \Carbon\Carbon::parse($activity->date_from)->subDay(Carbon::now()->dayOfWeek)->toDateString();
                $to = \Carbon\Carbon::parse($from)->addMonth()->toDateString();
                if(@$activity->date_to)
                    $to = \Carbon\Carbon::parse($activity->date_to)->toDateString();
                $dateRange = CarbonPeriod::create($from, $to);
                foreach ($activity->reservation_details['work_days'] as $index => $reservation_detail) {
                    foreach($dateRange as $date){
                        if(($date->dayOfWeek == $index) && (@$activity->reservation_details['work_days'][$index]['status'] == 1)){
                            $result[$i]['title'] = @$activity->name; //@$pt_subscription_trainer->pt_class->pt_subscription->name .' - '.trim($pt_subscription_trainer->pt_trainer->name).' ( ' . @$pt_subscription_trainer->pt_class->classes . ' ' . trans('sw.pt_class_2').' ) ';
                            $result[$i]['start'] = $date->toDateString().' '.$reservation_detail['start'];
                            $result[$i]['end'] = $date->toDateString().' '.$reservation_detail['end'];
                            $result[$i]['background_color'] = @$activity->time_color ?? '';
                            $result[$i]['id'] = @$activity->id;
                            $i++;
                        }
                    }
                }
            }
        }
        $reservations = $result;

        return view('software::Front.nonmember_front_reports', ['activity' => new GymActivity(), 'reservations' => $reservations, 'title'=>$title]);
    }


    public function listNonMemberInTimeCalendar($id, $date){
        $non_members = GymNonMemberTime::select(['id', 'non_member_id', 'member_id', 'activity_id', 'attended_at'])
            ->with(['non_member' => function ($q){$q->select(['id', 'name'])->withTrashed();}, 'member' => function ($q){$q->select(['id', 'name'])->withTrashed();;} ])
            ->where('activity_id', $id)
            ->whereDate('date', Carbon::parse($date)->toDateString())
//            ->whereDate('date', '>=', Carbon::parse($end)->toDateTimeString())
            ->limit(50)->get();

        return Response::json(['result' => $non_members], 200);
    }

    public function createNonMemberAttendInTimeCalendar(){
        $id = @request('id');
        $non_members = GymNonMemberTime::where('id', $id)->update(['attended_at' => Carbon::now()->toDateTimeString()]);
        return Response::json(['result' => Carbon::now()->toDateTimeString()], 200);
    }


}

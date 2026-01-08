<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Software\Classes\TypeConstants;
use Modules\Software\Classes\LoyaltyService;
use Modules\Software\Exports\RecordsExport;
use Modules\Software\Http\Requests\GymPTMemberRequest;
use Modules\Software\Models\GymActivity;
use Modules\Software\Models\GymGroupDiscount;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymMemberAttendee;
use Modules\Software\Models\GymMemberSubscription;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Models\GymPaymentType;
use Modules\Software\Models\GymPTClass;
use Modules\Software\Models\GymPTMember;
use Modules\Software\Models\GymPTMemberAttendee;
use Modules\Software\Models\GymPTSubscription;
use Modules\Software\Models\GymPTSubscriptionTrainer;
use Modules\Software\Models\GymPTTrainer;
use Modules\Billing\Services\SwBillingService;
use Modules\Software\Repositories\GymPTMemberRepository;
use Modules\Software\Services\PT\PTCommissionService;
use Modules\Software\Services\PT\PTEnrollmentService;
use Modules\Software\Services\PT\PTSessionService;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Container\Container as Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;

class GymPTMemberFrontController extends GymGenericFrontController
{
    public $MemberRepository;
    public $fileName;
    protected PTEnrollmentService $enrollmentService;
    protected PTSessionService $sessionService;
    protected PTCommissionService $commissionService;

    public function __construct(
        PTEnrollmentService $enrollmentService,
        PTSessionService $sessionService,
        PTCommissionService $commissionService
    )
    {
        parent::__construct();
        $this->enrollmentService = $enrollmentService;
        $this->sessionService = $sessionService;
        $this->commissionService = $commissionService;
        $this->MemberRepository=new GymPTMemberRepository(new Application);
        // Repository branch filtering removed from constructor - now applied per query
    }


    public function index()
    {
        $title = trans('sw.pt_members');
        $this->request_array = ['search', 'from', 'to', 'pt_subscription', 'pt_trainer'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $members = GymPTMember::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->with(['pt_subscription', 'pt_class', 'pt_trainer', 'member.member_subscription_info'])->onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $members = GymPTMember::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->with(['pt_subscription'=> function($q){
                $q->withTrashed();
            }, 'pt_class'=> function($q){
                $q->withTrashed();
            }, 'pt_trainer'=> function($q){
                $q->withTrashed();
            },  'member' => function($q){
                $q->withTrashed();
            },'member.member_subscription_info'])->orderBy('id', 'DESC');
        }
        $members->whereHas('member', function ($q){
            $q->whereNull('deleted_at');
        });
        //apply filters
        $members->when(($from), function ($query) use ($from) {
            $query->whereDate('created_at', '>=', Carbon::parse($from)->format('Y-m-d'));
        })->when(($to), function ($query) use ($to) {
            $query->whereDate('created_at', '<=', Carbon::parse($to)->format('Y-m-d'));
        })->when(($pt_subscription), function ($query) use ($pt_subscription) {
            $query->whereHas('pt_subscription', function ($q) use ($pt_subscription){
                $q->where('pt_subscription_id', $pt_subscription);
            });
        })->when(($pt_trainer), function ($query) use ($pt_trainer) {
            $query->whereHas('pt_trainer', function ($q) use ($pt_trainer){
                $q->where('pt_trainer_id', $pt_trainer);
            });
        })->when($search, function ($query) use ($search) {
            $query->whereHas('member', function ($q) use ($search){
                $q->where('code', 'like', "%" . $search . "%");
                $q->orWhere('name', 'like', "%" . $search . "%");
                $q->orWhere('phone', 'like', "%" . $search . "%");
            });
//            $query->orWhere('member.name','like', "%".$search."%");
        });
        $search_query = request()->query();

        if ($this->limit) {
            $members = $members->paginate($this->limit)->onEachSide(1);
            $total = $members->total();
        } else {
            $members = $members->get();
            $total = $members->count();
        }
        $pt_subscriptions = GymPTSubscription::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->get();
        $pt_trainers = GymPTTrainer::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->get();
        return view('software::Front.pt_member_front_list', compact('members', 'pt_trainers','pt_subscriptions','title', 'total', 'search_query'));
    }


    function exportExcel(){
        $records = $this->MemberRepository->get();
        $this->fileName = 'pt_members-' . Carbon::now()->toDateTimeString();

//        $title = trans('sw.pt_members');
//        $records = $this->prepareForExport($records);


        $notes = trans('sw.export_excel_pt_members');
        $this->userLog($notes, TypeConstants::ExportActivityExcel);

        return Excel::download(new RecordsExport(['records' => $records, 'keys' => ['name', 'price'],'lang' => $this->lang]), $this->fileName.'.xlsx');

//        Excel::create($this->fileName, function($excel) use ($records, $title) {
//            $excel->setTitle($title);
////            $excel->setCreator($title)->setCompany($title);
//            $excel->setDescription(trans('sw.pt_members_data'));
//            $excel->sheet(trans('sw.pt_members_data'), function($sheet) use ($records) {
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
        array_unshift($result, [trans('sw.pt_members')]);
        return $result;
    }
    function exportPDF(){
        $records = $this->PTMemberRepository->get();
        $this->fileName = 'pt_members-' . Carbon::now()->toDateTimeString();

        $keys = ['name', 'phone'];
        if($this->lang == 'ar') $keys = array_reverse($keys);

        $title = trans('sw.pt_members');
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
                
                $notes = trans('sw.export_pdf_pt_members');
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

        $notes = trans('sw.export_pdf_pt_members');
        $this->userLog($notes, TypeConstants::ExportActivityPDF);

        return $pdf->download($this->fileName.'.pdf');
    }


    public function create()
    {
        $title = trans('sw.pt_member_add');
        $subscriptions = GymPTSubscription::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->whereHas('pt_classes', function ($q){
            $q->having('id', '>', 0);
        })->get();
        $trainers = GymPTTrainer::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->get();
        $discounts = GymGroupDiscount::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('is_pt_member', true)->get();
        // Optimize: Add eager loading for classTrainers and trainer relation to prevent lazy loading
        $classes = GymPTClass::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->isSystem()->with([
            'pt_subscription_trainer',
            'classTrainers' => function($q) {
                $q->select('id', 'class_id', 'trainer_id', 'commission_rate', 'session_count', 'is_active');
            },
            'classTrainers.trainer' => function($q) {
                $q->select('id', 'name');
            }
        ])->get();
        $billingSettings = SwBillingService::getSettings();
        $paymentTypes = GymPaymentType::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->orderBy('payment_id')->get();
        return view('software::Front.pt_member_front_create', [
            'member' => new GymPTMember(),
            'discounts' => $discounts,
            'subscriptions'=> $subscriptions,
            'trainers'=> $trainers,
            'classes'=> $classes,
            'title'=>$title,
            'billingSettings' => $billingSettings,
            'paymentTypes' => $paymentTypes,
        ]);
    }

    protected function processZatcaInvoiceForPtMember(GymPTMember $ptMember, float $amountPaid, float $vatAmount, ?GymMoneyBox $moneyBox = null): void
    {
        if (!config('sw_billing.zatca_enabled') || !config('sw_billing.auto_invoice')) {
            return;
        }

        $settings = SwBillingService::getSettings();
        if (empty($settings['sections']['pt_members'])) {
            return;
        }

        try {
            SwBillingService::createInvoiceFromPtMember($ptMember, $amountPaid, $vatAmount, $moneyBox);
        } catch (\Exception $e) {
            \Log::error('Failed to process PT member ZATCA invoice', [
                'pt_member_id' => $ptMember->id,
                'member_id' => $ptMember->member_id,
                'money_box_id' => $moneyBox?->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function store(GymPTMemberRequest $request)
    {
        $vat = 0;
        $member_inputs = $this->prepare_inputs($request->except(['_token', 'amount_paid']));
        $memberSubscription = GymMember::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->with(['member_subscription_info'])->where('code', $member_inputs['member_id'])->orderBy('id', 'desc')->first();
        if($memberSubscription->member_subscription_info->expire_date < Carbon::now()->toDateTimeString()){
            return redirect(route('sw.createPTMember'))->withErrors(['member_id'=>trans('sw.membership_expired')]);
        }

        $class = GymPTClass::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->with(['pt_subscription'])->where('id', $member_inputs['pt_class_id'])->orderBy('id', 'desc')->first();

        $amount_paid = round(@$request->amount_paid, 2);
        $discount_value = round(@$request->discount_value, 2);
        $vat = (($class->price - @$discount_value) * ((float)@$this->mainSettings->vat_details['vat_percentage'] / 100));
        $class_price = $class->price - @$discount_value + $vat;
        $class_price = round($class_price, 2);
        if(@$amount_paid > $class_price){
            return redirect(route('sw.createPTMember'))->withErrors(['amount_paid' => trans('sw.amount_paid_validate_must_less')]);
        }

        $active_member_count = $this->classActiveMemberCount($member_inputs['pt_class_id']);
        if(($class->member_limit != 0) && (@$active_member_count >= $class->member_limit)){
            return redirect(route('sw.createPTMember'))->withErrors(['pt_class_id' => trans('sw.active_members_exceeds_available_members')]);
        }

        $calculatedSessions = (int) ($request->input('total_sessions') ?? $class->total_sessions ?? $class->classes ?? 0);
        $remainingSessions = (int) ($request->input('remaining_sessions') ?? $calculatedSessions);
        $amountRemaining = (($class->price - (float)$amount_paid - (float)$discount_value) + (($class->price - (float)$discount_value) * ((float)@$this->mainSettings->vat_details['vat_percentage'] / 100)));

        $enrollmentPayload = [
            'member_id' => $memberSubscription->id,
            'pt_subscription_id' => $member_inputs['pt_subscription_id'],
            'class_trainer_id' => $request->input('class_trainer_id'),
            'pt_trainer_id' => $request->input('pt_trainer_id'), // Fallback if class_trainer_id is empty
            'class_type' => $class->class_type,
            'classes' => $calculatedSessions,
            'total_sessions' => $calculatedSessions,
            'remaining_sessions' => $remainingSessions,
            'start_date' => $request->input('joining_date'),
            'end_date' => $request->input('expire_date'),
            'amount_paid' => (float) $amount_paid,
            'amount_before_discount' => $class->price,
            'discount_value' => (float) $discount_value,
            'payment_method' => (string) $request->input('payment_type'),
            'trainer_percentage' => $request->input('trainer_percentage'),
            'notes' => $member_inputs['notes'] ?? null,
        ];

        $member = $this->enrollmentService->enrollMember($class, $enrollmentPayload);
        $member->start_time_day = @$class->start_time_day;
        $member->end_time_day = @$class->end_time_day;
        $member->workouts_per_day = @$class->workouts_per_day;
        $member->discount_value = (float) $discount_value;
        $member->discount = (float) $discount_value;
        $member->vat = $vat;
        $member->amount_before_discount = $class->price;
        $member->amount_remaining = $amountRemaining;
        $member->payment_type = (int) $request->payment_type;
        $member->notes = $member_inputs['notes'] ?? null;
        $member->save();
        
        // Award loyalty points if member made a payment
        $loyaltyPointsEarned = 0;
        if ($memberSubscription && $amount_paid > 0) {
            try {
                $loyaltyService = new LoyaltyService();
                $transaction = $loyaltyService->earn(
                    $memberSubscription,
                    $amount_paid,
                    'pt_member',
                    $member->id
                );
                
                if ($transaction) {
                    $loyaltyPointsEarned = $transaction->points;
                }
            } catch (\Exception $e) {
                \Log::error('Failed to award loyalty points for PT member', [
                    'pt_member_id' => $member->id,
                    'member_id' => $memberSubscription->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        // Success message with loyalty points info
        $successMessage = trans('admin.successfully_added');
        if ($loyaltyPointsEarned > 0) {
            $successMessage .= ' - ' . trans('sw.earned_loyalty_points', ['points' => $loyaltyPointsEarned]);
        }
        
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => $successMessage,
            'type' => 'success'
        ]);


        $amount_box = GymMoneyBox::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->latest()->first();
        $amount_after = GymMoneyBoxFrontController::amountAfter( (float)@$amount_box->amount, (float)@$amount_box->amount_before, (int)@$amount_box->operation);
        $notes = trans('sw.pt_member_moneybox_add_msg', ['subscription' => $class->pt_subscription->name." (".$class->classes.")", 'member' => $memberSubscription->name, 'amount_paid' => (float)($amount_paid), 'amount_remaining' => round($amountRemaining, 2)]);
        if($discount_value) {
            $notes = $notes . trans('sw.discount_msg', ['value' => (float)$discount_value]);
        }
        if($this->mainSettings->vat_details['vat_percentage']){
            $notes = $notes.' - '.trans('sw.vat_added');
        }
        $moneyBox = GymMoneyBox::create([
            'user_id' => Auth::guard('sw')->user()->id
            , 'amount' => @(float)$amount_paid
            , 'vat' => @$vat
            , 'operation' => TypeConstants::Add
            , 'amount_before' => $amount_after
            , 'notes' => $notes
            , 'type' => TypeConstants::CreatePTMember
            , 'payment_type' => intval($request->payment_type)
            , 'member_id' => @$member->member_id
            , 'member_pt_subscription_id' => @$member->id
            , 'branch_setting_id' => @$this->user_sw->branch_setting_id
            , 'tenant_id' => @$this->user_sw->tenant_id
        ]);

        $this->userLog($notes, TypeConstants::CreateMoneyBoxAdd);

        $this->processZatcaInvoiceForPtMember($member, (float) $amount_paid, (float) $vat, $moneyBox);

//        return redirect(route('sw.listPTMember'));
        return redirect(route('sw.showOrder', $moneyBox->id));
    }
    private function classActiveMember($class_id){
        $member_count = GymPTMember::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('pt_class_id', $class_id)
                                ->where(function($q) use ($class_id) {
                                    $q->where('expire_date', '>=', Carbon::now()->toDateString());
                                    $q->orWhere('visits', '<=', 'classes');
                                })->count();
        return "( ".$member_count." / ".""." )";
    }
    private function classActiveMemberCount($class_id){
        $member_count = GymPTMember::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('pt_class_id', $class_id)
                                ->where(function($q) use ($class_id) {
                                    $q->where('expire_date', '>=', Carbon::now()->toDateString());
                                    $q->orWhere('visits', '<=', 'classes');
                                })->count();
        return $member_count;
    }
    public function classActiveMemberAjax(){
        $class_id = \request('pt_class_id');
        $class = GymPTClass::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('id', $class_id)->first();
        $class_limit_count = $class->member_limit;
        $member_count = GymPTMember::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('pt_class_id', $class_id)
                                ->where(function($q) use ($class_id) {
                                    $q->where('expire_date', '>=', Carbon::now()->toDateString());
                                    $q->orWhere('visits', '<=', 'classes');
                                })->count();
        return "( <b style='font-size: 18px;'>".$member_count."</b> / ". $class_limit_count ." )";
    }
    public function edit($id)
    {
        $member = $this->MemberRepository->with(['member.member_subscription_info.subscription' => function ($q){$q->withTrashed();}, 'pt_subscription', 'pt_class', 'pt_trainer'])->withTrashed()->find($id);
        $subscriptions = GymPTSubscription::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->whereHas('pt_classes', function ($q){
            $q->having('id', '>', 0);
        })->get();
        $trainers = GymPTTrainer::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->get();
        $discounts = GymGroupDiscount::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('is_pt_member', true)->get();
        // Optimize: Add eager loading for classTrainers and trainer relation to prevent lazy loading
        $classes = GymPTClass::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->isSystem()->with([
            'classTrainers' => function($q) {
                $q->select('id', 'class_id', 'trainer_id', 'commission_rate', 'session_count', 'is_active');
            },
            'classTrainers.trainer' => function($q) {
                $q->select('id', 'name');
            }
        ])->get();
        $title = trans('sw.pt_member_edit');
        $member->loadMissing('zatcaInvoice');
        $billingSettings = SwBillingService::getSettings();
        $paymentTypes = GymPaymentType::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->orderBy('payment_id')->get();
        return view('software::Front.pt_member_front_edit', [
            'member' => $member,
            'discounts' => $discounts,
            'subscriptions'=> $subscriptions,
            'trainers'=> $trainers,
            'classes'=> $classes,
            'title'=>$title,
            'billingSettings' => $billingSettings,
            'paymentTypes' => $paymentTypes,
        ]);
    }

    public function update(GymPTMemberRequest $request, $id)
    {
        $member = $this->MemberRepository->with(['member.member_subscription_info.subscription', 'pt_subscription', 'pt_class', 'pt_trainer'])->withTrashed()->find($id);
        $originalAmountPaid = $member->amount_paid;
        $member_inputs = $this->prepare_inputs($request->except(['_token']));
        $class = GymPTClass::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->with(['pt_subscription'])->where('id', $member_inputs['pt_class_id'])->orderBy('id', 'desc')->first();
        $vat = (($class->price - @$request->discount_value) * ((float)@$this->mainSettings->vat_details['vat_percentage'] / 100));
        $class_price = $class->price - @$request->discount_value + $vat;
        if(@$request->amount_paid > $class_price){
            return redirect(route('sw.editPTMember', $member->id))->withErrors(['amount_paid' => trans('sw.amount_paid_validate_must_less')]);
        }
//        if($member->member->member_subscription_info->expire_date < Carbon::now()->toDateTimeString()){
//            return redirect(route('sw.editPTMember', $member->id))->withErrors(['member_id'=>trans('sw.member')]);
//        }

        $active_member_count = $this->classActiveMemberCount($member->pt_class_id);
        if(($class->member_limit != 0) && (@($active_member_count-1) >= $class->member_limit)){
            return redirect(route('sw.editPTMember', $member->id))->withErrors(['pt_class_id' => trans('sw.active_members_exceeds_available_members')]);
        }
        $calculatedSessions = (int) ($request->input('total_sessions') ?? $class->total_sessions ?? $class->classes ?? 0);
        $remainingSessions = (int) ($request->input('remaining_sessions') ?? $member->remaining_sessions ?? $calculatedSessions);
        $amountRemaining = (($class->price - (float)$request->amount_paid - @(float)$request->discount_value) + (($class->price - @$request->discount_value) * ((float)@$this->mainSettings->vat_details['vat_percentage'] / 100)));

        $enrollmentPayload = [
            'member_id' => $member->member_id,
            'pt_subscription_id' => $member_inputs['pt_subscription_id'],
            'class_trainer_id' => $request->input('class_trainer_id') ?? $member->class_trainer_id,
            'class_type' => $class->class_type,
            'classes' => $calculatedSessions,
            'total_sessions' => $calculatedSessions,
            'remaining_sessions' => $remainingSessions,
            'start_date' => $request->input('joining_date'),
            'end_date' => $request->input('expire_date'),
            'amount_paid' => (float) $request->amount_paid,
            'amount_before_discount' => $class->price,
            'discount_value' => (float) $request->discount_value,
            'payment_method' => (string) $request->input('payment_type'),
            'trainer_percentage' => $request->input('trainer_percentage'),
            'notes' => $member_inputs['notes'] ?? null,
        ];

        $member = $this->enrollmentService->updateMember($member, $enrollmentPayload);
        $member->start_time_day = @$class->start_time_day;
        $member->end_time_day = @$class->end_time_day;
        $member->workouts_per_day = @$class->workouts_per_day;
        $member->discount_value = (float) $request->discount_value;
        $member->discount = (float) $request->discount_value;
        $member->vat = $vat;
        $member->amount_before_discount = $class->price;
        $member->amount_remaining = $amountRemaining;
        $member->payment_type = (int) $request->payment_type;
        $member->notes = $member_inputs['notes'] ?? null;
        $member->save();

        $member_inputs['amount_paid'] = (float)$request->amount_paid;
        $member_inputs['amount_remaining'] = $amountRemaining;
        $member_inputs['vat'] = $vat;

        $differentPrice = (float)$request->amount_paid - $originalAmountPaid;

        $notes = str_replace(':name', $member['name'], trans('sw.edit_activity'));
        $this->userLog($notes, TypeConstants::EditPTMember);
        
        // Handle loyalty points when amount changes
        $loyaltyPointsChange = 0;
        if ($differentPrice != 0 && $member->member_id && @$this->mainSettings->active_loyalty) {
            try {
                $gymMember = GymMember::find($member->member_id);
                if ($gymMember) {
                    $loyaltyService = new LoyaltyService();
                    
                    if ($differentPrice > 0) {
                        // Amount increased - award additional points
                        $transaction = $loyaltyService->earn(
                            $gymMember,
                            abs($differentPrice),
                            'pt_member_edit',
                            $member->id
                        );
                        
                        if ($transaction) {
                            $loyaltyPointsChange = $transaction->points;
                        }
                    } else {
                        // Amount decreased - deduct points proportionally
                        // Find original earn transactions for this PT member
                        $loyaltyTransactions = \Modules\Software\Models\LoyaltyTransaction::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('member_id', $gymMember->id)
                            ->whereIn('source_type', ['pt_member', 'pt_member_edit'])
                            ->where('source_id', $member->id)
                            ->where('type', 'earn')
                            ->where('is_expired', false)
                            ->get();
                        
                        $totalPointsEarned = $loyaltyTransactions->sum('points');
                        $oldAmountPaid = $member->amount_paid; // Before update
                        
                        if ($totalPointsEarned > 0 && $oldAmountPaid > 0) {
                            // Calculate points to deduct based on the amount reduction
                            $amountReduction = abs($differentPrice);
                            $reductionRatio = $amountReduction / $oldAmountPaid;
                            $pointsToDeduct = (int) round($totalPointsEarned * $reductionRatio);
                            
                            if ($pointsToDeduct > 0 && $gymMember->loyalty_points_balance >= $pointsToDeduct) {
                                $deductionTransaction = $loyaltyService->addManual(
                                    $gymMember,
                                    -$pointsToDeduct,
                                    trans('sw.points_deducted_for_pt_amount_reduction', [
                                        'pt_member_id' => $member->id,
                                        'old_amount' => $oldAmountPaid,
                                        'new_amount' => $member_inputs['amount_paid']
                                    ]),
                                    $this->user_sw->id ?? null
                                );
                                
                                if ($deductionTransaction) {
                                    $deductionTransaction->source_type = 'pt_member_edit_reduction';
                                    $deductionTransaction->source_id = $member->id;
                                    $deductionTransaction->save();
                                    $loyaltyPointsChange = -$pointsToDeduct;
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Failed to adjust loyalty points for PT member edit', [
                    'pt_member_id' => $member->id,
                    'member_id' => $member->member_id ?? null,
                    'amount_difference' => $differentPrice,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        // Success message with loyalty points info
        $successMessage = trans('admin.successfully_edited');
        if ($loyaltyPointsChange > 0) {
            $successMessage .= ' - ' . trans('sw.earned_loyalty_points', ['points' => $loyaltyPointsChange]);
        } elseif ($loyaltyPointsChange < 0) {
            $successMessage .= ' - ' . trans('sw.deducted_loyalty_points', ['points' => abs($loyaltyPointsChange)]);
        }

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => $successMessage,
            'type' => 'success'
        ]);

        if($differentPrice != 0) {
            $amount_box = GymMoneyBox::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->latest()->first();
            $amount_after = GymMoneyBoxFrontController::amountAfter((float)@$amount_box->amount, (float)@$amount_box->amount_before, (int)@$amount_box->operation);
            $notes = trans('sw.pt_member_moneybox_edit_msg', ['subscription' => $class->pt_subscription->name . " (" . $class->classes . ")", 'member' => $member->member->name, 'amount_paid' => ($differentPrice), 'amount_remaining' => round($member_inputs['amount_remaining'], 2)]);
            if ($request->discount_value)
                $notes = $notes . trans('sw.discount_msg', ['value' => (float)$request->discount_value]);

            $moneyBox = GymMoneyBox::create([
                'user_id' => Auth::guard('sw')->user()->id
                , 'amount' => abs((int)$differentPrice)
//                , 'vat' => $vat
                , 'vat' => ((@$differentPrice * (@$this->mainSettings->vat_details['vat_percentage'] / 100)) / (1 + (@$this->mainSettings->vat_details['vat_percentage'] / 100)))
                , 'operation' => $differentPrice > 0 ? TypeConstants::Add : TypeConstants::Sub
                , 'amount_before' => $amount_after
                , 'notes' => $notes
                , 'type' => TypeConstants::EditPTMember
                , 'member_id' => @$member->member_id
                , 'member_pt_subscription_id' => @$member->id
                , 'branch_setting_id' => @$this->user_sw->branch_setting_id
                , 'tenant_id' => @$this->user_sw->tenant_id
            ]);
            $this->userLog($notes, TypeConstants::CreateMoneyBoxAdd);

            $this->processZatcaInvoiceForPtMember($member->fresh(), (float) $member_inputs['amount_paid'], (float) $member_inputs['vat'], $moneyBox);

            return redirect(route('sw.showOrder', $moneyBox->id));
        }

        $this->processZatcaInvoiceForPtMember($member->fresh(), (float) $member_inputs['amount_paid'], (float) $member_inputs['vat']);
        return redirect(route('sw.listPTMember'));
    }

    public function destroy($id)
    {
        $member = GymPTMember::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->with(['member', 'pt_subscription'])->withTrashed()->find($id);
        if($member->trashed())
        {
            $member->restore();
        }
        else
        {
            $member->delete();

            if(\request('refund')){
                $amount_box = GymMoneyBox::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->latest()->first();
                $amount_after = (int)GymMoneyBoxFrontController::amountAfter($amount_box->amount, $amount_box->amount_before, $amount_box->operation);

                // Calculate refund amount (full or partial)
                $vat = @$member->vat;
                $refundAmount = $member->amount_paid; // Default to full refund
                $isPartialRefund = false;
                
                if(\request('total_amount') && \request('amount') && (\request('total_amount') >= \request('amount') )){
                    $refundAmount = \request('amount');
                    $isPartialRefund = ($refundAmount < $member->amount_paid);
                }
                
                // Deduct loyalty points if they were awarded for this PT member
                if ($member->member_id && @$this->mainSettings->active_loyalty) {
                    try {
                        $gymMember = GymMember::find($member->member_id);
                        if ($gymMember) {
                            // Find loyalty transactions for this PT member
                            $loyaltyTransactions = \Modules\Software\Models\LoyaltyTransaction::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('source_type', 'pt_member')
                                ->where('source_id', $member->id)
                                ->where('type', 'earn')
                                ->where('is_expired', false)
                                ->get();
                            
                            $totalPointsEarned = $loyaltyTransactions->sum('points');
                            
                            if ($totalPointsEarned > 0 && $member->amount_paid > 0) {
                                // Check how many points have already been deducted for this PT member (from previous refunds)
                                $alreadyDeductedPoints = abs(\Modules\Software\Models\LoyaltyTransaction::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('member_id', $gymMember->id)
                                    ->where('type', 'manual')
                                    ->where('source_type', 'pt_member_refund')
                                    ->where('source_id', $member->id)
                                    ->where('points', '<', 0)
                                    ->sum('points')) ?? 0; // sum of negative values, so we get positive amount deducted
                                
                                $remainingDeductiblePoints = $totalPointsEarned - $alreadyDeductedPoints;
                                
                                // Calculate proportional points to deduct based on refund ratio
                                $refundRatio = $refundAmount / $member->amount_paid;
                                $pointsToDeduct = (int) round($totalPointsEarned * $refundRatio);
                                
                                // Don't deduct more than what's remaining
                                if ($pointsToDeduct > $remainingDeductiblePoints) {
                                    $pointsToDeduct = max(0, $remainingDeductiblePoints);
                                }
                                
                                if ($pointsToDeduct > 0) {
                                    // Check if member has enough points
                                    if ($gymMember->loyalty_points_balance >= $pointsToDeduct) {
                                        // Deduct points using manual adjustment
                                        $loyaltyService = new LoyaltyService();
                                        
                                        $reason = $isPartialRefund 
                                            ? trans('sw.points_deducted_for_partial_refund_pt', [
                                                'pt_member_id' => $member->id, 
                                                'refund_amount' => $refundAmount,
                                                'original_amount' => $member->amount_paid
                                            ])
                                            : trans('sw.points_deducted_for_refund_pt', ['pt_member_id' => $member->id]);
                                        
                                        // Create the deduction transaction with source tracking
                                        $deductionTransaction = $loyaltyService->addManual(
                                            $gymMember,
                                            -$pointsToDeduct,
                                            $reason,
                                            $this->user_sw->id ?? null
                                        );
                                        
                                        // Update source_type and source_id to track refunds properly
                                        if ($deductionTransaction) {
                                            $deductionTransaction->source_type = 'pt_member_refund';
                                            $deductionTransaction->source_id = $member->id;
                                            $deductionTransaction->save();
                                        }
                                        
                                        // Mark original transactions as expired only for full refunds
                                        if (!$isPartialRefund) {
                                            foreach ($loyaltyTransactions as $earnTransaction) {
                                                $earnTransaction->is_expired = true;
                                                $earnTransaction->save();
                                            }
                                        }
                                        
                                        \Log::info('Loyalty points deducted for PT member refund', [
                                            'pt_member_id' => $member->id,
                                            'member_id' => $gymMember->id,
                                            'points_deducted' => $pointsToDeduct,
                                            'total_points_earned' => $totalPointsEarned,
                                            'already_deducted_points' => $alreadyDeductedPoints,
                                            'remaining_deductible_points' => $remainingDeductiblePoints,
                                            'refund_amount' => $refundAmount,
                                            'original_amount' => $member->amount_paid,
                                            'refund_ratio' => $refundRatio,
                                            'is_partial' => $isPartialRefund,
                                        ]);
                                    } else {
                                        // Member doesn't have enough points
                                        \Log::warning('Cannot deduct loyalty points - insufficient balance', [
                                            'pt_member_id' => $member->id,
                                            'member_id' => $gymMember->id,
                                            'points_needed' => $pointsToDeduct,
                                            'current_balance' => $gymMember->loyalty_points_balance,
                                            'refund_amount' => $refundAmount,
                                        ]);
                                        
                                        // Mark transactions as expired only for full refunds
                                        if (!$isPartialRefund) {
                                            foreach ($loyaltyTransactions as $earnTransaction) {
                                                $earnTransaction->is_expired = true;
                                                $earnTransaction->save();
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to deduct loyalty points on PT member refund', [
                            'pt_member_id' => $member->id,
                            'refund_amount' => $refundAmount,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                
                $amount = $refundAmount;
                
                $notes = trans('sw.member_moneybox_delete_msg', ['member' => $member->member->name, 'subscription' => $member->pt_subscription->name, 'amount_paid' => $amount]);
                GymMoneyBox::create([
                    'user_id' => Auth::guard('sw')->user()->id
                    , 'amount' => $amount
                    , 'vat' => @$vat
                    , 'operation' => TypeConstants::Sub
                    , 'amount_before' => $amount_after
                    , 'notes' => $notes
                    , 'type' => TypeConstants::DeletePTMember
                    , 'member_id' => @$member->member_id
                    , 'member_pt_subscription_id' => @$member->id
                    , 'member_subscription_id' => @$member->member->member_subscription_info->id
                    , 'branch_setting_id' => @$this->user_sw->branch_setting_id
                    , 'tenant_id' => @$this->user_sw->tenant_id
                ]);
                $this->userLog($notes, TypeConstants::CreateMoneyBoxWithdraw);
            }
            $notes = str_replace(':name', $member['name'], trans('sw.delete_activity'));
            $this->userLog($notes, TypeConstants::DeletePTMember);
        }
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listPTMember'));
    }

    public function getPTTrainerAjax(){
        $trainers = request('trainers');
        if($trainers){
            $trainerIds = explode(',', $trainers);
            $trainers = GymPTTrainer::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->whereIn('id', $trainerIds)->get()->toArray();
            $option = '<option value="">'.trans('admin.choose').'...</option>';
            foreach ($trainers as $trainer)
                $option.="<option data-percentage='".$trainer['percentage']."' value='".$trainer['id']."'>".$trainer['name']."</option>";
            return $option;
        }
        return '';
    }

    public function getPTMemberAjax(){
        $member_id = request('member_id');
        if($member_id){
            $member = GymMember::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->with(['member_subscription_info.subscription'])->where('code', $member_id)->first();
            $member->member_subscription_info->expire_date_str = @Carbon::parse($member->member_subscription_info->expire_date)->toDateString();
            return $member;
        }
        return [];
    }

    private function prepare_inputs($inputs)
    {
        if(@$this->user_sw->branch_setting_id){
            $inputs['branch_setting_id'] = @$this->user_sw->branch_setting_id;
        }
        return $inputs;
    }

    private function recordMemberAttendance(GymPTMember $member, ?Carbon $sessionDate = null, array $sessionContext = []): ?GymPTMemberAttendee
    {
        $member->loadMissing(['member.member_subscription_info', 'pt_class', 'classTrainer']);
        $class = $member->pt_class ?? GymPTClass::find($member->pt_class_id);

        if (!$class) {
            return null;
        }

        $today = Carbon::now();
        $endDate = $member->end_date ?? ($member->expire_date ? Carbon::parse($member->expire_date) : null);
        if ($endDate && $endDate->lt($today)) {
            return null;
        }

        $remaining = $member->remaining_sessions;
        if ($remaining === null) {
            $totalSessions = $member->total_sessions ?? $member->classes ?? 0;
            $remaining = max($totalSessions - ($member->visits ?? 0), 0);
        }
        if ($remaining <= 0 && ($member->classes ?? $member->total_sessions ?? 0) > 0) {
            return null;
        }

        $useExactSlot = !empty($sessionContext);
        $sessionDateLocal = $sessionDate ? $sessionDate->copy() : Carbon::now();
        $sessionDateUtc = $sessionDateLocal->copy()->setTimezone('UTC');

        [$windowStart, $windowEnd] = $this->buildDuplicateWindow($sessionDateUtc, $useExactSlot);

        $duplicateQuery = GymPTMemberAttendee::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('pt_member_id', $member->id)
            ->whereBetween('session_date', [$windowStart, $windowEnd]);

        $existingAttendee = $duplicateQuery->first();
        if ($existingAttendee) {
            $member->refresh();
            return $existingAttendee;
        }

        $attendee = GymPTMemberAttendee::create([
            'pt_member_id' => $member->id,
            'session_id' => null,
            'session_date' => $sessionDateUtc,
            'user_id' => optional(Auth::guard('sw')->user())->id,
            'branch_setting_id' => $member->branch_setting_id ?? $class->branch_setting_id ?? @$this->user_sw->branch_setting_id,
            'attended' => true,
        ]);

        $this->commissionService->recordForAttendance($attendee, $sessionDateLocal, $member->classTrainer);
        $this->enrollmentService->adjustRemainingSessions($member, -1);

        GymMemberAttendee::create([
            'user_id' => optional(Auth::guard('sw')->user())->id,
            'member_id' => $member->member_id,
            'pt_subscription_id' => $member->id,
            'subscription_id' => optional(optional($member->member)->member_subscription_info)->id,
            'type' => TypeConstants::ATTENDANCE_TYPE_PT,
            'branch_setting_id' => @$this->user_sw->branch_setting_id,
            'tenant_id' => @$this->user_sw->tenant_id,
        ]);

        $note = trans('sw.pt_member_used', [
            'subscription' => ' ( ' . ($class->name ?? trans('sw.pt_class')) . ' - ' . ($class->classes ?? $class->total_sessions ?? 0) . ' ) ',
            'name' => optional($member->member)->name,
        ]);
        $this->userLog($note, TypeConstants::ScanPTMember);

        return $attendee;
    }

    private function memberPTAttendeesById($id, array $sessionContext = []){
        $id = $id;
        $member = GymPTMember::with(['member', 'pt_subscription', 'pt_class', 'classTrainer'])->where('id', $id);
        // Apply branch restriction:
        // 1. If allow_member_in_branches is false, always apply for all users
        // 2. If allow_member_in_branches is true but user is not super user, apply branch restriction
        // 3. Only super users with allow_member_in_branches=true can access all branches
        $userSw = $this->user_sw ?? Auth::guard('sw')->user();
        $isSuperUser = $userSw ? ($userSw->is_super_user ?? false) : false;
        $allowCrossBranch = @$this->mainSettings->allow_member_in_branches ?? false;
        
        if(!$allowCrossBranch || !$isSuperUser){
            // Use direct branch_setting_id from user instead of scope to avoid Auth issues
            $branchId = $userSw ? ($userSw->branch_setting_id ?? 1) : 1;
            $member = $member->where('branch_setting_id', $branchId);
        }
        $member = $member->first();
        if(!$member) {
            return '';
        }

        if (!empty($sessionContext) && !$this->memberMatchesSessionContext($member, $sessionContext)) {
            return '';
        }

        // Store initial attendee count to detect if new one was created
        $sessionDateUtc = ($sessionContext['session_date'] ?? Carbon::now())->copy()->setTimezone('UTC');
        [$windowStart, $windowEnd] = $this->buildDuplicateWindow($sessionDateUtc, !empty($sessionContext));
        $existingTodayCount = GymPTMemberAttendee::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('pt_member_id', $member->id)
            ->whereBetween('session_date', [$windowStart, $windowEnd])
            ->count();

        $attendee = $this->recordMemberAttendance($member, $sessionContext['session_date'] ?? null, $sessionContext);
        
        // Always refresh member to get latest data
        $member->refresh();
        
        if ($attendee) {
            // Check if a new attendee was created (count increased)
            $newTodayCount = GymPTMemberAttendee::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('pt_member_id', $member->id)
                ->whereBetween('session_date', [$windowStart, $windowEnd])
                ->count();
            
            // Only update remaining_sessions if a new attendee was actually created
            if ($newTodayCount > $existingTodayCount) {
                // The adjustRemainingSessions was already called in recordMemberAttendance
                // Just refresh to ensure we have latest data
                $member->refresh();
            }
            
            $totalSessions = $member->sessions_total ?? $member->total_sessions ?? $member->classes ?? 0;
            $usedSessions = $member->sessions_used ?? ($totalSessions - ($member->remaining_sessions ?? 0));
            return $member->pt_subscription->name.' ('.$usedSessions.' / '.$totalSessions.') ';
        }
        return '';
    }
    public function memberPTAttendees(Request $request){
        $sessionContext = $this->resolveSessionContext($request);
        // Use id if provided
        $idToUse = @$request->id;
        if($idToUse){
            // Try id first
            $result = $this->memberPTAttendeesById($idToUse, $sessionContext);
            if(!empty($result)){
                return $result;
            }
            // If id failed and no code provided, return error
            if(!@$request->code){
                return Response::json(['msg' => trans('sw.no_code_found'), 'member' => null, 'status' => false], 200);
            }
            // If id failed but code is provided, fall through to code path
        }

        // Use code path (handles code parameter and phone search)
        $code = preg_replace("/[^0-9]/", "",$request->code);
        // If id was provided but failed, and code is empty, use id as code
        if(@$request->id && empty($code)){
            $code = preg_replace("/[^0-9]/", "", $request->id);
        }
        $enquiry =intval($request->enquiry);
        $msg = '';
        $member = GymPTMember::with(['member' => function ($query){
            $query->orderBy('id', 'desc');
        }, 'pt_subscription'])->withCount(['pt_member_attendees' => function($q){
            $q->whereDate('created_at', Carbon::now()->toDateString());
        }])->where('id', $code)
            ->when(@$enquiry && (strlen(intval($code)) >= 5), function ($q) use ($code){
                $q->orWhereHas('member', function ($q) use ($code){ $q->where('phone', 'like', '%' . $code . '%');});
            });
        // Apply branch restriction:
        // 1. If allow_member_in_branches is false, always apply for all users
        // 2. If allow_member_in_branches is true but user is not super user, apply branch restriction
        // 3. Only super users with allow_member_in_branches=true can access all branches
        $userSw = $this->user_sw ?? Auth::guard('sw')->user();
        $isSuperUser = $userSw ? ($userSw->is_super_user ?? false) : false;
        $allowCrossBranch = @$this->mainSettings->allow_member_in_branches ?? false;
        
        if(!$allowCrossBranch || !$isSuperUser){
            // Use direct branch_setting_id from user instead of scope to avoid Auth issues
            $branchId = $userSw ? ($userSw->branch_setting_id ?? 1) : 1;
            $member = $member->where('branch_setting_id', $branchId);
        }
        $member = $member->first();
        $status = false;
        if($member) {
            if (!empty($sessionContext) && !$this->memberMatchesSessionContext($member, $sessionContext)) {
                return Response::json([
                    'msg' => trans('sw.member_not_in_session'),
                    'member' => $member,
                    'status' => false,
                ], 200);
            }
            if(($member->workouts_per_day > 0) && ($member->pt_member_attendees_count >= $member->workouts_per_day)){
                $msg = trans('sw.workouts_per_day_msg', ['visits' => $member->pt_member_attendees_count, 'classes' => $member->workouts_per_day]);
                return Response::json(['msg' => $msg, 'member' => $member, 'status' => $status], 200);
            }


//            if(($member->start_time_day) && ($member->pt_member_attendees_count <= $member->workouts_per_day)){
//                $msg = trans('sw.workouts_per_day_msg', ['visits' => $member->pt_member_attendees_count, 'classes' => $member->workouts_per_day]);
//                return Response::json(['msg' => $msg, 'member' => $member, 'status' => $status], 200);
//            }

//            if ($member) {
                $checkForMemberVisits = true;
                if(($member->joining_date > Carbon::now()) && ($checkForMemberVisits)){
                    $msg = trans('sw.membership_not_coming');
                }elseif(($member->classes > 0) && ($member->classes >= $member->visits)
                    && ($member->expire_date >= Carbon::now()) && ($checkForMemberVisits)){

                    // time of membership
                    if(($member->start_time_day) && ($member->end_time_day) &&
                        ((Carbon::parse($member->start_time_day)->format('H:i:s') > Carbon::now()->format('H:i:s')) ||
                            (Carbon::parse($member->end_time_day)->subMinute()->format('H:i:s') < Carbon::now()->format('H:i:s')))) {
                        return Response::json(['msg' => trans('sw.failed_time',
                            [
                                'date_from' => '<span style="font-size: 14px;"> ' . '<i class="fa fa-clock-o text-muted"></i> '.strtolower($member->start_time_day).' '
                            , 'date_to' => ' ' . '<i class="fa fa-clock-o text-muted"></i> '.strtolower($member->end_time_day).'</span>'
                            ]), 'member' => $member, 'status' => $status], 200);
                    }

                    if(!$enquiry) {
                        $attendee = $this->recordMemberAttendance($member, $sessionContext['session_date'] ?? null, $sessionContext);
                        if (!$attendee) {
                            return Response::json([
                                'msg' => trans('sw.no_available_sessions'),
                                'member' => $member,
                                'status' => false,
                            ], 200);
                        }
                        $member->refresh();
                    }
                    $status = true;
                }else{
                    $msg = trans('sw.membership_expired');
                }
                $member['expire_date'] = Carbon::parse($member->expire_date)->format('d-m-Y');
                $sessionsRemaining = $member->remaining_sessions ?? ($member->classes - $member->visits);
                $member['remain_workouts'] = $sessionsRemaining;
                $member['amount_remaining'] = number_format($member->amount_remaining, 2);
                return Response::json(['msg' => $msg, 'member' => $member, 'status' => $status], 200);
//            }else{
//                return Response::json(['member' => $member, 'status' => $status], 200);
//            }
        }

        $msg = trans('sw.no_code_found');
        return Response::json(['msg' => $msg, 'status' => $status], 200);
    }

    public function payAmountRemaining(){
        $id = request('id');
        $amountPaid = (float)request('amount_paid');
        $payment_type = (int)request('payment_type');

        $memberPTInfo = GymPTMember::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->with(['pt_subscription', 'member'])->where('id', $id)->orderBy('id', 'desc')->first();
        if(@$memberPTInfo ){
            $amount_remaining = round($memberPTInfo->amount_remaining, 2);

            if($amountPaid == 0) return trans('sw.amount_paid_must_not_zero');
            if($amount_remaining < $amountPaid) return str_replace(':amount_paid', $amount_remaining, trans('sw.amount_paid_must_less'));

            $memberPTInfo->amount_remaining = ($amount_remaining - $amountPaid);
            $memberPTInfo->amount_paid = ($memberPTInfo->amount_paid + $amountPaid);
            $memberPTInfo->save();
            
            // Award loyalty points for the payment
            if ($memberPTInfo->member && $amountPaid > 0 && @$this->mainSettings->active_loyalty) {
                try {
                    $loyaltyService = new LoyaltyService();
                    $transaction = $loyaltyService->earn(
                        $memberPTInfo->member,
                        $amountPaid,
                        'pt_member_remaining_payment',
                        $memberPTInfo->id
                    );
                    
                    if ($transaction) {
                        \Log::info('Loyalty points awarded for PT member remaining payment', [
                            'pt_member_id' => $memberPTInfo->id,
                            'member_id' => $memberPTInfo->member->id,
                            'amount_paid' => $amountPaid,
                            'points_earned' => $transaction->points,
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to award loyalty points for PT member remaining payment', [
                        'pt_member_id' => $memberPTInfo->id,
                        'member_id' => $memberPTInfo->member->id ?? null,
                        'amount_paid' => $amountPaid,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $amount_box = GymMoneyBox::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->latest()->first();
            $amount_after = GymMoneyBoxFrontController::amountAfter($amount_box->amount, $amount_box->amount_before, $amount_box->operation);

            $notes = trans('sw.pt_member_moneybox_remain_msg',['subscription'=> $memberPTInfo->pt_subscription->name, 'member' => $memberPTInfo->member->name, 'amount_paid' => $amountPaid, 'amount_remaining' => round($memberPTInfo->amount_remaining, 2)]);

            GymMoneyBox::create([
                'user_id' => Auth::guard('sw')->user()->id
                , 'amount' => @abs((float)$amountPaid)
                , 'operation' => $amountPaid > 0 ? TypeConstants::Add : TypeConstants::Sub
                , 'amount_before' => $amount_after
                , 'notes' => $notes
                , 'type' => TypeConstants::CreatePTMemberPayAmountRemainingForm
                , 'payment_type' => $payment_type
                , 'member_id' => @$memberPTInfo->member->id
                , 'member_pt_subscription_id' => @$memberPTInfo->id
                , 'branch_setting_id' => @$this->user_sw->branch_setting_id
                , 'tenant_id' => @$this->user_sw->tenant_id
            ]);
            $this->userLog($notes, TypeConstants::CreateMoneyBoxAdd);

            return 1;
        }
        return trans('admin.operation_failed');
    }

    private function resolveSessionContext(Request $request): array
    {
        $context = [];

        $virtualId = $request->input('session_virtual_id') ?? $request->input('session_id');
        if ($virtualId) {
            $decoded = $this->sessionService->decodeVirtualSessionId($virtualId);
            if ($decoded) {
                $context['virtual_id'] = $virtualId;
                $context['class_id'] = $decoded['class_id'] ?? null;
                $context['class_trainer_id'] = $decoded['class_trainer_id'] ?? null;
                if (isset($decoded['timestamp'])) {
                    $context['session_date'] = Carbon::createFromTimestamp($decoded['timestamp'])
                        ->timezone(config('app.timezone'));
                }
            }
        }

        if (!isset($context['session_date']) && $request->filled('session_date')) {
            try {
                $context['session_date'] = Carbon::parse($request->input('session_date'));
            } catch (\Throwable $e) {
                // ignore invalid date input
            }
        }

        if ($request->filled('class_id')) {
            $context['class_id'] = (int) $request->input('class_id');
        }

        if ($request->filled('class_trainer_id')) {
            $context['class_trainer_id'] = (int) $request->input('class_trainer_id');
        }

        return array_filter($context, fn ($value) => !is_null($value) && $value !== '');
    }

    private function memberMatchesSessionContext(GymPTMember $member, array $context): bool
    {
        if (empty($context)) {
            return true;
        }

        $memberClassId = $member->class_id ?? $member->pt_class_id ?? null;
        if (isset($context['class_id']) && $context['class_id'] && $memberClassId && (int) $memberClassId !== (int) $context['class_id']) {
            return false;
        }

        if (isset($context['class_trainer_id']) && $context['class_trainer_id'] && $member->class_trainer_id && (int) $member->class_trainer_id !== (int) $context['class_trainer_id']) {
            return false;
        }

        return true;
    }

    private function buildDuplicateWindow(Carbon $sessionDateUtc, bool $exactSlot = false): array
    {
        if ($exactSlot) {
            return [$sessionDateUtc->copy(), $sessionDateUtc->copy()];
        }

        $localStart = $sessionDateUtc->copy()->timezone(config('app.timezone'))->startOfDay();
        $localEnd = $sessionDateUtc->copy()->timezone(config('app.timezone'))->endOfDay();

        return [
            $localStart->copy()->timezone('UTC'),
            $localEnd->copy()->timezone('UTC'),
        ];
    }

    public function listPTMemberCalendar($member){
        $result = [];
            $pt_member = GymPTMember::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('id', $member)->first();
            if($pt_member) {
                $from = Carbon::parse($pt_member->joining_date);
                $to = Carbon::parse($pt_member->expire_date);
                $period = CarbonPeriod::create($from, $to)->toArray();
                $reservation = @GymPTSubscriptionTrainer::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('pt_class_id', $pt_member->pt_class_id)->where('pt_trainer_id', $pt_member->pt_trainer_id)->orderBy('id', 'desc')->first();
                $reservation_details = @$reservation->reservation_details;

                if ($reservation_details && $reservation_details['work_days']) {
                    $i = 0;
                    $date_now = Carbon::now()->toDateString();
                    foreach ($period as $index => $day) {
                        if (@$reservation_details['work_days'][$day->dayOfWeek]) {
                            $result[$i]['member_date'] = $day->toDateString();
                            $result[$i]['member_time'] = @$reservation_details['work_days'][$day->dayOfWeek];
                            $result[$i]['member_time_from'] = @Carbon::parse(@$reservation_details['work_days'][$day->dayOfWeek]['start'])->format('g:i A');
                            $result[$i]['member_time_to'] = @Carbon::parse(@$reservation_details['work_days'][$day->dayOfWeek]['end'])->format('g:i A');
                            $result[$i]['member_subscription'] = @$pt_member->pt_class->name;
                            if ($day->toDateString() >= $date_now) {
                                $result[$i]['date_status'] = 1;
                            } else {
                                $result[$i]['date_status'] = 0;
                            }
                            $i++;
                        }
                    }

            }
        }
        return Response::json(['result' => $result], 200);


    }
    public function listPTMemberInClassCalendar($pt_class_id, $pt_trainer_id){
        $pt_members = GymPTMember::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->select(['id', 'member_id'])
                      ->with(['member' => function ($q){$q->select(['id', 'name', 'code']);}])
                      ->where('pt_class_id', $pt_class_id)
                      ->where('pt_trainer_id', $pt_trainer_id)
                      ->whereDate('joining_date', '<=', Carbon::now()->toDateString())
                      ->whereDate('expire_date', '>=', Carbon::now()->toDateString())
                      ->limit(50)->get();
        return Response::json(['result' => $pt_members], 200);
    }


    public function membersPTRefresh(){
        //$this->updateSubscriptionsStatus([], true);
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_processed'),
            'type' => 'success'
        ]);
        return  Response::json(['status' => true], 200);
    }
}


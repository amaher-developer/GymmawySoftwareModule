<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Generic\Http\Controllers\Front\GenericFrontController;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Exports\MoneyBoxExport;
use Modules\Software\Exports\NonMembersExport;
use Modules\Software\Http\Requests\GymMoneyBoxRequest;
use Modules\Software\Http\Requests\GymOrderRequest;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Models\GymMoneyBoxType;
use Modules\Software\Models\GymOrder;
use Modules\Software\Models\GymPaymentType;
use Modules\Software\Models\GymSubscription;
use Modules\Software\Models\GymUser;
use Modules\Software\Repositories\GymMoneyBoxRepository;
use Modules\Software\Repositories\GymOrderRepository;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Illuminate\Container\Container as Application;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Excel;


class GymMoneyBoxFrontController extends GymGenericFrontController
{

    public $GymMoneyBoxRepository;
    public $fileName;
    public $payment_revenues;
    public $payment_expenses;

    public function __construct()
    {
        parent::__construct();
        $this->limit = 5;
        $this->GymMoneyBoxRepository = new GymMoneyBoxRepository(new Application);
        $this->GymMoneyBoxRepository = $this->GymMoneyBoxRepository->branch();
    }


    public function index()
    {

        $title = trans('sw.moneybox');
        $this->request_array = ['search', 'from', 'to', 'payment_type', 'moneybox_type', 'user', 'subscription', 'is_store_balance'];
        $users = GymUser::branch()->where('is_test', 0)->get();

        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;

        $orders = GymMoneyBox::branch()->with(['user' => function($q){$q->withTrashed();}, 'member_subscription' => function($q){$q->withTrashed();}])->orderBy('created_at', 'DESC');


        //apply filters
        $orders->when(($from), function ($query) use ($from) {
            $query->whereDate('created_at', '>=', Carbon::parse($from)->format('Y-m-d'));
        })->when(($to), function ($query) use ($to) {
            $query->whereDate('created_at', '<=', Carbon::parse($to)->format('Y-m-d'));
        })->when(((isset($_GET['payment_type'])) &&(!is_null($payment_type))), function ($query) use ($payment_type) {
            $query->where('payment_type', '=', (int)@$payment_type);
        })->when(((isset($_GET['subscription'])) &&(!is_null($subscription))), function ($query) use ($subscription) {
            $query->whereHas('member_subscription', function ($q) use ($subscription){$q->where('subscription_id','=', (int)@$subscription);} );
        })->when(((isset($_GET['moneybox_type'])) &&(!is_null($moneybox_type))), function ($query) use ($moneybox_type) {
            $query->where('type', '=', (int)@$moneybox_type);
        })->when(((isset($_GET['is_store_balance'])) &&(!is_null($is_store_balance))), function ($query) use ($is_store_balance) {
            if($is_store_balance == 1)
                $query->where('is_store_balance','!=', 2);
        })->when(((isset($_GET['user'])) &&(!is_null($user))), function ($query) use ($user) {
            $query->where('user_id', '=', (int)@$user);
        })->when(($search), function ($query) use ($search) {
            if((string)$search[0] == "#"){
                $query->where('id', @(int)trim($search, '#'));
            } else {
//            $query->orWhere('amount', '=', (int)$search)
//                ->orWhere('notes', 'like', "%" . $search . "%");

                $query->whereHas('member', function ($q) use ($search) {
                    $q->where('code', $search);
                    $q->orWhere('name', 'like', "%" . $search . "%");
                });
            }
        });
        $search_query = request()->query();
        if (request()->exists('export')) {
            $orders = $orders->get();
            $array = $this->prepareForExport($orders);

            $fileName = 'reports-' . Carbon::now()->toDateTimeString();
            $file = \Maatwebsite\Excel\Facades\Excel::create($fileName, function ($excel) use ($array) {
                $excel->setTitle('title');
                $excel->sheet(trans('sw.reports_data'), function ($sheet) use ($array) {
                    if ($this->lang == 'ar') $sheet->setRightToLeft(true);
                    $sheet->fromArray($array);
                });
            });

            $file = $file->string('xlsx');
            return [
                'name' => $fileName,
                'file' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64," . base64_encode($file)
            ];
        }

        if ($this->limit) {
            $sorders = $orders->get();
            $orders = $orders->paginate($this->limit);
            $total = $orders->total();
        } else {
            $orders = $orders->get();
            $total = $orders->count();
        }
        $subscriptions = GymSubscription::get();
        $revenues = ($sorders->where('operation', 0)->sum('amount'));
        $expenses = ($sorders->where('operation', 1)->sum('amount'));

        $earnings = ($revenues - $expenses);

        $payment_types = GymPaymentType::branch()->orderBy('id')->get();
        ($sorders->filter(function ($item) use ($payment_types) {
            foreach ($payment_types as $i => $payment_type){
//                if(($item->member_subscription_id != null || $item->member_pt_subscription_id != null) && (@$item->payment_type == $payment_type->payment_id) && ($item->operation == TypeConstants::Add) ){
                if((@$item->payment_type == $payment_type->payment_id) && ($item->operation == TypeConstants::Add) ){
                    return $this->payment_revenues[$payment_type->payment_id] = (@$this->payment_revenues[$payment_type->payment_id]) + $item->amount;
                    //return $item;
                }
            }
//            return $item->where('payment_type', TypeConstants::CASH_PAYMENT);
        })); //->where('operation', 0)->sum('amount'));

        $payment_revenues = $this->payment_revenues;

        //        $cache_expenses = ($sorders->where('payment_type', TypeConstants::CASH_PAYMENT)->where('operation', 1)->sum('amount'));
        ($sorders->filter(function ($item) use ($payment_types) {
            foreach ($payment_types as $i => $payment_type) {
//                if (($item->member_subscription_id != null || $item->member_pt_subscription_id != null) && (@$item->payment_type == $payment_type->payment_id)  && ($item->operation == TypeConstants::Sub) ) {
                if ((@$item->payment_type == $payment_type->payment_id)  && ($item->operation == TypeConstants::Sub) ) {
                    $this->payment_expenses[$payment_type->payment_id] = (@$this->payment_expenses[$payment_type->payment_id]) + $item->amount;
                    return $item;
                }
            }
//            return $item->where('payment_type', TypeConstants::CASH_PAYMENT);
        }));//->where('operation', 1)->sum('amount'));

        $payment_expenses = $this->payment_expenses;

        $cache_earnings = 0;//($cache_revenues - $cache_expenses);

//        $online_revenues = ($sorders->filter(function ($item) {
//            if(($item->member_subscription_id != null || $item->member_pt_subscription_id != null) && (@$item->payment_type == TypeConstants::ONLINE_PAYMENT)){
//                return $item;
//            }
//        })->where('operation', 0)->sum('amount'));
//        $online_expenses = ($sorders->filter(function ($item) {
//            if(($item->member_subscription_id != null || $item->member_pt_subscription_id != null) &&  (@$item->payment_type == TypeConstants::ONLINE_PAYMENT)){
//                return $item;
//            }
//        })->where('operation', 1)->sum('amount'));
//        $online_earnings = ($online_revenues - $online_expenses);
//
//        $bank_revenues = ($sorders->filter(function ($item) {
//            if(($item->member_subscription_id != null || $item->member_pt_subscription_id != null) &&  (@$item->payment_type == TypeConstants::BANK_TRANSFER_PAYMENT)){
//                return $item;
//            }
//        })->where('operation', 0)->sum('amount'));
//        $bank_expenses = ($sorders->filter(function ($item) {
//            if(($item->member_subscription_id != null || $item->member_pt_subscription_id != null) &&  (@$item->payment_type == TypeConstants::BANK_TRANSFER_PAYMENT)){
//                return $item;
//            }
//        })->where('operation', 1)->sum('amount'));
//        $bank_earnings = ($bank_revenues - $bank_expenses);


        $total_add_to_money_box = ($sorders->where('type', TypeConstants::CreateMoneyBoxAdd)->where('operation', 0)->sum('amount'));
        $total_withdraw_from_money_box = ($sorders->where('type', TypeConstants::CreateMoneyBoxWithdraw)->where('operation', 1)->sum('amount'));

        $total_activities = ($sorders->whereIn('type', [TypeConstants::CreateNonMember, TypeConstants::EditNonMember, TypeConstants::DeleteNonMember, TypeConstants::EditActivity, TypeConstants::CreateActivity, TypeConstants::DeleteActivity ])->where('operation', 0)->sum('amount')
            - $sorders->whereIn('type', [TypeConstants::CreateNonMember, TypeConstants::EditNonMember, TypeConstants::DeleteNonMember, TypeConstants::EditActivity, TypeConstants::CreateActivity, TypeConstants::DeleteActivity ])->where('operation', 1)->sum('amount'));

        $total_subscriptions = ($sorders->whereIn('type', [TypeConstants::CreateMember,TypeConstants::RenewMember,TypeConstants::EditMember,TypeConstants::DeleteMember, TypeConstants::CreateMemberPayAmountRemainingForm, TypeConstants::EditSubscription,TypeConstants::CreateSubscription,TypeConstants::DeleteSubscription ])->where('operation', 0)->sum('amount')
            - $sorders->whereIn('type', [TypeConstants::CreateMember,TypeConstants::RenewMember,TypeConstants::EditMember,TypeConstants::DeleteMember, TypeConstants::CreateMemberPayAmountRemainingForm, TypeConstants::EditSubscription,TypeConstants::CreateSubscription,TypeConstants::DeleteSubscription ])->where('operation', 1)->sum('amount'));

//        $total_non_members = ($sorders->whereIn('type', [TypeConstants::CreateNonMember, TypeConstants::EditNonMember, TypeConstants::DeleteNonMember, TypeConstants::EditActivity, TypeConstants::CreateActivity, TypeConstants::DeleteActivity ])->where('operation', 0)->sum('amount')
//            - $sorders->whereIn('type', [TypeConstants::CreateNonMember, TypeConstants::EditNonMember, TypeConstants::DeleteNonMember, TypeConstants::EditActivity, TypeConstants::CreateActivity, TypeConstants::DeleteActivity ])->where('operation', 1)->sum('amount'));

        $total_pt_subscriptions = ($sorders->whereIn('type', [TypeConstants::CreatePTMember,TypeConstants::RenewPTMember,TypeConstants::EditPTMember,TypeConstants::DeletePTMember, TypeConstants::CreatePTMemberPayAmountRemainingForm, TypeConstants::EditPTSubscription,TypeConstants::CreatePTSubscription,TypeConstants::DeletePTSubscription ])->where('operation', 0)->sum('amount')
            - $sorders->whereIn('type', [TypeConstants::CreatePTMember,TypeConstants::RenewPTMember,TypeConstants::EditPTMember,TypeConstants::DeletePTMember, TypeConstants::CreatePTMemberPayAmountRemainingForm, TypeConstants::EditPTSubscription,TypeConstants::CreatePTSubscription,TypeConstants::DeletePTSubscription ])->where('operation', 1)->sum('amount'));

        $total_stores = ($sorders->whereIn('type', [TypeConstants::CreateStoreProduct,TypeConstants::EditStoreProduct,TypeConstants::DeleteStoreProduct, TypeConstants::CreateStoreOrder,TypeConstants::EditStoreOrder,TypeConstants::DeleteStoreOrder  ])->where('operation', 0)->sum('amount')
            - $sorders->whereIn('type', [TypeConstants::CreateStoreProduct,TypeConstants::EditStoreProduct,TypeConstants::DeleteStoreProduct, TypeConstants::CreateStoreOrder,TypeConstants::EditStoreOrder,TypeConstants::DeleteStoreOrder  ])->where('operation', 1)->sum('amount'));

        return view('software::Front.moneybox_front_list', compact(
            'revenues', 'expenses', 'earnings'
//                    ,'cache_revenues', 'cache_expenses', 'cache_earnings'
//                    ,'online_revenues', 'online_expenses', 'online_earnings'
//                    ,'bank_revenues', 'bank_expenses', 'bank_earnings'
                    ,'total_add_to_money_box', 'total_withdraw_from_money_box'
                    ,'total_activities', 'total_subscriptions', 'total_pt_subscriptions', 'total_stores'//, 'total_non_members'
                    , 'orders', 'title', 'total', 'search_query', 'users', 'subscriptions'
                    , 'payment_expenses', 'payment_revenues', 'payment_types'));
    }

    function exportExcel(){
        $from = request('from');
        $to = request('to');
        $payment_type = request('payment_type');
        $moneybox_type = request('moneybox_type');
        $user = request('user');
        $search = request('search');
        $subscription = request('subscription');
        $records = $this->GymMoneyBoxRepository->with(['user', 'member_subscription'])
            ->whereDate('created_at', '>=', Carbon::parse($from)->format('Y-m-d'))
            ->whereDate('created_at', '<=', Carbon::parse($to)->format('Y-m-d'))
            ->when(((isset($subscription)) &&(!is_null($subscription))), function ($query) use ($subscription) {
                $query->whereHas('member_subscription', function ($q) use ($subscription){$q->where('subscription_id','=', (int)@$subscription);} );
            })->when(((isset($_GET['payment_type'])) &&(!is_null($payment_type))), function ($query) use ($payment_type) {
                $query->where('payment_type', '=', (int)@$payment_type);
            })->when(((isset($_GET['moneybox_type'])) &&(!is_null($moneybox_type))), function ($query) use ($moneybox_type) {
                $query->where('type', '=', (int)@$moneybox_type);
            })->when(((isset($_GET['user'])) &&(!is_null($user))), function ($query) use ($user) {
                $query->where('user_id', '=', (int)@$user);
            })->when(($search), function ($query) use ($search) {
                if((string)$search[0] == "#"){
                    $query->where('id', @(int)trim($search, '#'));
                } else {
//            $query->orWhere('amount', '=', (int)$search)
//                ->orWhere('notes', 'like', "%" . $search . "%");
                    $query->whereHas('member', function ($q) use ($search) {
                        $q->where('code', $search);
                        $q->orWhere('name', 'like', "%" . $search . "%");
                    });
                }
            })
            ->orderBy('id', 'desc')
            ->get();
        $this->fileName = 'reports-' . Carbon::now()->toDateTimeString();

//        $title = trans('sw.moneybox');
//        $records = $this->prepareForExport($records);
        $notes = trans('sw.export_excel_moneybox');
        $this->userLog($notes, TypeConstants::ExportMoneyboxExcel);

        return \Maatwebsite\Excel\Facades\Excel::download(new MoneyBoxExport(['records' => $records, 'keys' => ['id', 'amount', 'total_amount_before', 'total_amount_after', 'operation', 'payment_type_name', 'notes', 'date', 'by'],'lang' => $this->lang]), $this->fileName.'.xlsx');


//        \Maatwebsite\Excel\Facades\Excel::create($this->fileName, function($excel) use ($records, $title) {
//            $excel->setTitle($title);
////            $excel->setCreator($title)->setCompany($title);
//            $excel->setDescription(trans('sw.activities_data'));
//            $excel->sheet(trans('sw.activities_data'), function($sheet) use ($records) {
//                $sheet->setRightToLeft(true);
//                $sheet->fromArray($records, null, 'A1', false, false);
//                $sheet->mergeCells('A1:G1');
//                $sheet->cells('A1:G1', function ($cells) {
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
        $name = [trans('sw.amount'), trans('sw.total_amount_before'), trans('sw.total_amount_after')
            , trans('sw.operation'), trans('sw.payment_type'), trans('sw.notes'), trans('sw.date') , trans('sw.by')];
        foreach($data as $record){
            $record->invoice_total = number_format(($record['amount']-$record['vat']), 2);
            $record->vat_total = number_format($record['vat'], 2);
            $record->invoice_total_required = number_format($record['amount'], 2);
            $record->notes = $record['notes'];
            $record->date = Carbon::parse($record['created_at'])->format('Y-m-d') . ' ' . Carbon::parse($record['created_at'])->format('h:i a');
            $record->by = @$record['user']['name'];
        }
//        array_unshift($result, $name);
//        array_unshift($result, [trans('sw.moneybox')]);
        return $data;
    }

    function exportPDF(){

        $from = request('from');
        $to = request('to');
        $subscription = request('subscription');
        $records = $this->GymMoneyBoxRepository->with(['user'])
                    ->whereDate('created_at', '>=', Carbon::parse($from)->format('Y-m-d'))
                    ->whereDate('created_at', '<=', Carbon::parse($to)->format('Y-m-d'))
                    ->when(((isset($subscription)) &&(!is_null($subscription))), function ($query) use ($subscription) {
                        $query->whereHas('member_subscription', function ($q) use ($subscription){$q->where('subscription_id','=', (int)@$subscription);} );
                    })
                    ->orderBy('id', 'desc')
                    ->get()->toArray();
        $this->fileName = 'reports-' . Carbon::now()->toDateTimeString();

        $keys = ['amount', 'total_amount_before', 'total_amount_after', 'operation', 'notes', 'created_at', 'by'];
        if($this->lang == 'ar') $keys = array_reverse($keys);
        for($i = 0; count($records) > $i;$i++ ){
            $records[$i]['operation'] = strip_tags($records[$i]['operation_name']);
            $records[$i]['total_amount_before'] = ($records[$i]['amount_before']);
            $records[$i]['total_amount_after'] = $this->amountAfter($records[$i]['amount'], $records[$i]['amount_before'], $records[$i]['operation']);
            $records[$i]['date'] = Carbon::parse($records[$i]['created_at'])->format('Y-m-d') . ' ' . Carbon::parse($records[$i]['created_at'])->format('h:i a');
            $records[$i]['by'] = @$records[$i]['user']['name'];
        }
        $title = trans('sw.moneybox');
        $customPaper = array(0,0,720,1440);

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
            
            $notes = trans('sw.export_pdf_moneybox');
            $this->userLog($notes, TypeConstants::ExportMoneyboxPDF);
            
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
        
        $notes = trans('sw.export_pdf_moneybox');
        $this->userLog($notes, TypeConstants::ExportMoneyboxPDF);

        return $pdf->download($this->fileName.'.pdf');
    }




    public static function amountAfter($amount, $amountBefore, $operation)
    {
        if ($operation == 0) {
            return ($amountBefore + $amount);
        } elseif ($operation == 1) {
            return ($amountBefore - $amount);
        } elseif ($operation == 2) {
            return ($amountBefore - $amount);
        }
        return $amount;
    }

    public function create()
    {
        $title = trans('sw.add_to_money_box');
        $payment_types = GymPaymentType::branch()->orderBy('id')->get();
        $money_box_types = GymMoneyBoxType::orderBy('operation_type', 'DESC')->get();

        return view('software::Front.moneybox_add_front_form', [
            'order' => new GymMoneyBox(),
            'title' => $title,
            'billingSettings' => config('sw_billing') ? \Modules\Billing\Services\SwBillingService::getSettings() : [],
            'payment_types' => $payment_types,
            'money_box_types' => $money_box_types,
        ]);
    }
    private function calculateVat($amount){
        return (($amount * (@(float)$this->mainSettings->vat_details['vat_percentage'] / 100)) / (1 + (@(float)$this->mainSettings->vat_details['vat_percentage'] / 100)));
    }
    public function store(GymMoneyBoxRequest $request)
    {
        $gymMoneyBox = GymMoneyBox::branch()->latest()->first();
        $money_box_inputs = $request->except(['_token', 'is_vat', 'send_to_zatca']);
        $money_box_inputs['user_id'] = Auth::guard('sw')->user()->id;
        $money_box_inputs['operation'] = 0;
        $money_box_inputs['type'] = TypeConstants::CreateMoneyBoxAdd;
        $money_box_inputs['branch_setting_id'] = @$this->user_sw->branch_setting_id;
        $money_box_inputs['vat'] = 0;
        if($request->is_vat)  $money_box_inputs['vat'] = $this->calculateVat(@(float)$request->amount);

        $money_box_inputs['amount_before'] = self::amountAfter((float)@$gymMoneyBox->amount, (float)@$gymMoneyBox->amount_before, @$gymMoneyBox->operation);

        $moneyBox = $this->GymMoneyBoxRepository->create($money_box_inputs);

        $notes = trans('sw.add_money_to_money_box', ['price' => (float)$request->amount, 'notes' => $request->notes]);
        $this->userLog($notes, TypeConstants::CreateMoneyBoxAdd);

        $sendToZatca = $request->boolean('send_to_zatca', true);

        if ($sendToZatca && config('sw_billing.zatca_enabled') && config('sw_billing.auto_invoice')) {
            $billingSettings = \Modules\Billing\Services\SwBillingService::getSettings();
            if (!empty($billingSettings['sections']['money_boxes'])) {
                try {
                    \Modules\Billing\Services\SwBillingService::createInvoiceFromMoneyBox($moneyBox);
                } catch (\Exception $e) {
                    \Log::error('Failed to create ZATCA invoice for money box', [
                        'money_box_id' => $moneyBox->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_processed'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listMoneyBox').'?from='.date('m/d/Y').'&'.'to='.date('m/d/Y'));
    }

    public function createWithdraw()
    {
        $title = trans('sw.withdraw_from_money_box');
        $payment_types = GymPaymentType::branch()->orderBy('id')->get();
        $money_box_types = GymMoneyBoxType::orderBy('operation_type', 'DESC')->get();

        return view('software::Front.moneybox_withdraw_front_form', [
            'order' => new GymMoneyBox(),
            'title' => $title,
            'payment_types' => $payment_types,
            'money_box_types' => $money_box_types,
        ]);
    }

    public function storeWithdraw(GymMoneyBoxRequest $request)
    {
        $gymMoneyBox = GymMoneyBox::branch()->latest()->first();
        $money_box_inputs = $request->except(['_token', 'is_vat']);
        $money_box_inputs['user_id'] = Auth::guard('sw')->user()->id;
        $money_box_inputs['operation'] = 1;
        $money_box_inputs['type'] = TypeConstants::CreateMoneyBoxWithdraw;
        $money_box_inputs['branch_setting_id'] = @$this->user_sw->branch_setting_id;
        $money_box_inputs['vat'] = 0;
        if($request->is_vat)  $money_box_inputs['vat'] = $this->calculateVat(@(float)$request->amount);


        $money_box_inputs['amount_before'] = self::amountAfter((float)$gymMoneyBox->amount, (float)$gymMoneyBox->amount_before, $gymMoneyBox->operation);

        $this->GymMoneyBoxRepository->create($money_box_inputs);

        $notes = trans('sw.withdraw_money_from_money_box', ['price' => (float)$request->amount, 'notes' => $request->notes]);
        $this->userLog($notes, TypeConstants::CreateMoneyBoxWithdraw);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_processed'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listMoneyBox').'?from='.date('m/d/Y').'&'.'to='.date('m/d/Y'));
    }

    public function createWithdrawEarnings()
    {
        $title = trans('sw.withdraw_earning');
        $payment_types = GymPaymentType::branch()->orderBy('id')->get();
        $money_box_types = GymMoneyBoxType::orderBy('operation_type', 'DESC')->get();

        return view('software::Front.moneybox_withdraw_earnings_front_form', [
            'order' => new GymMoneyBox(),
            'title' => $title,
            'payment_types' => $payment_types,
            'money_box_types' => $money_box_types,
        ]);
    }

    public function storeWithdrawEarnings(GymMoneyBoxRequest $request)
    {

        $gymMoneyBox = GymMoneyBox::branch()->orderBy('created_at','desc')->first();
        $money_box_inputs = $request->except(['_token', 'is_vat']);
        $money_box_inputs['user_id'] = Auth::guard('sw')->user()->id;
        $money_box_inputs['operation'] = 2;
        $money_box_inputs['type'] = TypeConstants::CreateMoneyBoxWithdrawEarnings;
        $money_box_inputs['branch_setting_id'] = @$this->user_sw->branch_setting_id;
        $money_box_inputs['vat'] = 0;
        if($request->is_vat)  $money_box_inputs['vat'] = $this->calculateVat(@(float)$request->amount);


        $money_box_inputs['amount_before'] = self::amountAfter((float)@$gymMoneyBox->amount, (float)@$gymMoneyBox->amount_before, @$gymMoneyBox->operation);

        if ($request->amount > $money_box_inputs['amount_before']) {
            return redirect()->route('sw.createMoneyBoxWithdrawEarnings')->withErrors(['amount' => str_replace(':amount', number_format($money_box_inputs['amount_before'], 2), trans('sw.withdraw_error_msg'))]);
        }

        $this->GymMoneyBoxRepository->create($money_box_inputs);

        $notes = trans('sw.withdraw_money_earning', ['price' => (float)$request->amount, 'notes' => $request->notes]);
        $this->userLog($notes, TypeConstants::CreateMoneyBoxWithdrawEarnings);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_processed'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listMoneyBox').'?from='.date('m/d/Y').'&'.'to='.date('m/d/Y'));
    }


    public function scriptForRebuildMoneybox($id = null, $amount = null){
        $id = $id ?? request('id');
        $amount = $amount ?? request('amount');
        if($id){
            $gymMoneyBox = GymMoneyBox::branch()->where('id', '>=', $id)->orderBy('created_at', 'asc')->get();
            foreach($gymMoneyBox as $i => $moneyBox){
                if($i == 0){
                    $moneyBox->amount = $amount;
                }else{
                    $moneyBox->amount_before = self::amountAfter(round($gymMoneyBox[$i-1]->amount, 2), round($gymMoneyBox[$i-1]->amount_before, 2), round($gymMoneyBox[$i-1]->operation, 2));
                    var_dump('i: '.$i, 'id: '.$moneyBox->id, ' '.'amount_before: '.$moneyBox->amount_before);
                }
                $moneyBox->save();
            }
        }
    }

    public function editPaymentTypeOrder(){
        $id = request('id');
        $payment_type = request('payment_type');
        
        // Validate required parameters
        if (empty($id) || (empty($payment_type) && ($payment_type != 0))) {
            return trans('admin.operation_failed');
        }
        
        // Convert to integers
        $id = (int)$id;
        $payment_type = (int)$payment_type;
        // Validate that payment_type is a valid positive integer
        if ($payment_type < 0) {
            return trans('admin.operation_failed');
        }
        
        // Get authenticated user and branch_setting_id
        $user = Auth::guard('sw')->user();
        if (!$user) {
            return trans('admin.operation_failed');
        }
        $branch_setting_id = $user->branch_setting_id ?? 1;
        
        // Find the order - use branch_setting_id directly to ensure it works
        $order = GymMoneyBox::where('branch_setting_id', $branch_setting_id)
            ->where('id', $id)
            ->first();
        
        if($order){
            $order->payment_type = $payment_type;
            $order->save();

            session()->flash('sweet_flash_message', [
                'title' => trans('admin.done'),
                'message' => trans('admin.successfully_processed'),
                'type' => 'success'
            ]);
            return '1';
        }
        
        return trans('admin.operation_failed');
    }


    public function indexDaily()
    {

        $title = trans('sw.moneybox_daily');
        $this->request_array = ['search', 'payment_type', 'moneybox_type', 'user', 'is_store_balance'];
        $users = GymUser::branch()->where('is_test', 0)->get();

        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;

        $orders = GymMoneyBox::branch()->with(['user' => function($q){$q->withTrashed();}, 'member_subscription' => function($q){$q->withTrashed();}])->orderBy('created_at', 'DESC');

        //apply filters
        $orders = $orders->whereDate('created_at', Carbon::now()->toDateString());
        $orders = $orders->when(((isset($_GET['payment_type'])) &&(!is_null($payment_type))), function ($query) use ($payment_type) {
            $query->where('payment_type', '=', (int)@$payment_type);
        })->when(((isset($_GET['moneybox_type'])) &&(!is_null($moneybox_type))), function ($query) use ($moneybox_type) {
            $query->where('type', '=', (int)@$moneybox_type);
        })->when(((isset($_GET['is_store_balance'])) &&(!is_null($is_store_balance))), function ($query) use ($is_store_balance) {
            if($is_store_balance == 1)
                $query->where('is_store_balance','!=', 2);
        })->when(((isset($_GET['user'])) &&(!is_null($user))), function ($query) use ($user) {
            $query->where('user_id', '=', (int)@$user);
        })->when(($search), function ($query) use ($search) {
            $query->whereHas('member', function($q) use ($search){
                $q->where('code', $search);
                $q->orWhere('name', 'like', "%" . $search . "%");
            });
        });
        $search_query = request()->query();
        if (request()->exists('export')) {
            $orders = $orders->select('amount', 'user.name')->get();
            $array = $this->prepareForExport($orders);

            $fileName = 'reports-' . Carbon::now()->toDateTimeString();
            $file = \Maatwebsite\Excel\Facades\Excel::create($fileName, function ($excel) use ($array) {
                $excel->setTitle('title');
                $excel->sheet(trans('sw.reports_data'), function ($sheet) use ($array) {
                    if ($this->lang == 'ar') $sheet->setRightToLeft(true);
                    $sheet->fromArray($array);
                });
            });

            $file = $file->string('xlsx');
            return [
                'name' => $fileName,
                'file' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64," . base64_encode($file)
            ];
        }
        if ($this->limit) {
            $sorders = $orders->get();
            $orders = $orders->paginate($this->limit);
            $total = $orders->total();
        } else {
            $orders = $orders->get();
            $total = $orders->count();
        }

        $subscriptions = GymSubscription::get();
        $revenues = ($sorders->where('operation', 0)->sum('amount'));
        $expenses = ($sorders->where('operation', 1)->sum('amount'));

        $earnings = ($revenues - $expenses);

        $payment_types = GymPaymentType::branch()->orderBy('id')->get();
        ($sorders->filter(function ($item) use ($payment_types) {
            foreach ($payment_types as $i => $payment_type){
//                if(($item->member_subscription_id != null || $item->member_pt_subscription_id != null) && (@$item->payment_type == $payment_type->payment_id) && ($item->operation == TypeConstants::Add) ){
                if((@$item->payment_type == $payment_type->payment_id) && ($item->operation == TypeConstants::Add) ){
                    return $this->payment_revenues[$payment_type->payment_id] = (@$this->payment_revenues[$payment_type->payment_id]) + $item->amount;
                    //return $item;
                }
            }
//            return $item->where('payment_type', TypeConstants::CASH_PAYMENT);
        })); //->where('operation', 0)->sum('amount'));

        $payment_revenues = $this->payment_revenues;

        //        $cache_expenses = ($sorders->where('payment_type', TypeConstants::CASH_PAYMENT)->where('operation', 1)->sum('amount'));
        ($sorders->filter(function ($item) use ($payment_types) {
            foreach ($payment_types as $i => $payment_type) {
//                if (($item->member_subscription_id != null || $item->member_pt_subscription_id != null) && (@$item->payment_type == $payment_type->payment_id)  && ($item->operation == TypeConstants::Sub) ) {
                if ((@$item->payment_type == $payment_type->payment_id)  && ($item->operation == TypeConstants::Sub) ) {
                    $this->payment_expenses[$payment_type->payment_id] = (@$this->payment_expenses[$payment_type->payment_id]) + $item->amount;
                    return $item;
                }
            }
//            return $item->where('payment_type', TypeConstants::CASH_PAYMENT);
        }));//->where('operation', 1)->sum('amount'));

        $payment_expenses = $this->payment_expenses;

        $cache_earnings = 0;//($cache_revenues - $cache_expenses);

//        $online_revenues = ($sorders->filter(function ($item) {
//            if(($item->member_subscription_id != null || $item->member_pt_subscription_id != null) && (@$item->payment_type == TypeConstants::ONLINE_PAYMENT)){
//                return $item;
//            }
//        })->where('operation', 0)->sum('amount'));
//        $online_expenses = ($sorders->filter(function ($item) {
//            if(($item->member_subscription_id != null || $item->member_pt_subscription_id != null) &&  (@$item->payment_type == TypeConstants::ONLINE_PAYMENT)){
//                return $item;
//            }
//        })->where('operation', 1)->sum('amount'));
//        $online_earnings = ($online_revenues - $online_expenses);
//
//        $bank_revenues = ($sorders->filter(function ($item) {
//            if(($item->member_subscription_id != null || $item->member_pt_subscription_id != null) &&  (@$item->payment_type == TypeConstants::BANK_TRANSFER_PAYMENT)){
//                return $item;
//            }
//        })->where('operation', 0)->sum('amount'));
//        $bank_expenses = ($sorders->filter(function ($item) {
//            if(($item->member_subscription_id != null || $item->member_pt_subscription_id != null) &&  (@$item->payment_type == TypeConstants::BANK_TRANSFER_PAYMENT)){
//                return $item;
//            }
//        })->where('operation', 1)->sum('amount'));
//        $bank_earnings = ($bank_revenues - $bank_expenses);


        $total_add_to_money_box = ($sorders->where('type', TypeConstants::CreateMoneyBoxAdd)->where('operation', 0)->sum('amount'));
        $total_withdraw_from_money_box = ($sorders->where('type', TypeConstants::CreateMoneyBoxWithdraw)->where('operation', 1)->sum('amount'));

        $total_activities = ($sorders->whereIn('type', [TypeConstants::CreateActivity,TypeConstants::EditActivity,TypeConstants::DeleteActivity ])->where('operation', 0)->sum('amount')
            - $sorders->whereIn('type', [TypeConstants::CreateActivity,TypeConstants::EditActivity,TypeConstants::DeleteActivity ])->where('operation', 1)->sum('amount'));

        $total_subscriptions = ($sorders->whereIn('type', [TypeConstants::CreateMember,TypeConstants::RenewMember,TypeConstants::EditMember,TypeConstants::DeleteMember, TypeConstants::CreateMemberPayAmountRemainingForm, TypeConstants::EditSubscription,TypeConstants::CreateSubscription,TypeConstants::DeleteSubscription ])->where('operation', 0)->sum('amount')
            - $sorders->whereIn('type', [TypeConstants::CreateMember,TypeConstants::RenewMember,TypeConstants::EditMember,TypeConstants::DeleteMember, TypeConstants::CreateMemberPayAmountRemainingForm, TypeConstants::EditSubscription,TypeConstants::CreateSubscription,TypeConstants::DeleteSubscription ])->where('operation', 1)->sum('amount'));

        $total_non_members = ($sorders->whereIn('type', [TypeConstants::CreateNonMember, TypeConstants::EditNonMember, TypeConstants::DeleteNonMember, TypeConstants::EditActivity, TypeConstants::CreateActivity, TypeConstants::DeleteActivity ])->where('operation', 0)->sum('amount')
            - $sorders->whereIn('type', [TypeConstants::CreateNonMember, TypeConstants::EditNonMember, TypeConstants::DeleteNonMember, TypeConstants::EditActivity, TypeConstants::CreateActivity, TypeConstants::DeleteActivity ])->where('operation', 1)->sum('amount'));

        $total_pt_subscriptions = ($sorders->whereIn('type', [TypeConstants::CreatePTMember,TypeConstants::RenewPTMember,TypeConstants::EditPTMember,TypeConstants::DeletePTMember, TypeConstants::CreatePTMemberPayAmountRemainingForm, TypeConstants::EditPTSubscription,TypeConstants::CreatePTSubscription,TypeConstants::DeletePTSubscription ])->where('operation', 0)->sum('amount')
            - $sorders->whereIn('type', [TypeConstants::CreatePTMember,TypeConstants::RenewPTMember,TypeConstants::EditPTMember,TypeConstants::DeletePTMember, TypeConstants::CreatePTMemberPayAmountRemainingForm, TypeConstants::EditPTSubscription,TypeConstants::CreatePTSubscription,TypeConstants::DeletePTSubscription ])->where('operation', 1)->sum('amount'));

        $total_stores = ($sorders->whereIn('type', [TypeConstants::CreateStoreProduct,TypeConstants::EditStoreProduct,TypeConstants::DeleteStoreProduct, TypeConstants::CreateStoreOrder,TypeConstants::EditStoreOrder,TypeConstants::DeleteStoreOrder ])->where('operation', 0)->sum('amount')
            - $sorders->whereIn('type', [TypeConstants::CreateStoreProduct,TypeConstants::EditStoreProduct,TypeConstants::DeleteStoreProduct, TypeConstants::CreateStoreOrder,TypeConstants::EditStoreOrder,TypeConstants::DeleteStoreOrder  ])->where('operation', 1)->sum('amount'));

        return view('software::Front.moneybox_daily_front_list', compact(
            'revenues', 'expenses', 'earnings'
//                    ,'cache_revenues', 'cache_expenses', 'cache_earnings'
//                    ,'online_revenues', 'online_expenses', 'online_earnings'
//                    ,'bank_revenues', 'bank_expenses', 'bank_earnings'
            ,'total_add_to_money_box', 'total_withdraw_from_money_box'
            ,'total_activities', 'total_subscriptions', 'total_pt_subscriptions', 'total_stores', 'total_non_members'
            , 'orders', 'title', 'total', 'search_query', 'users', 'subscriptions'
            , 'payment_expenses', 'payment_revenues', 'payment_types'));
    }


}


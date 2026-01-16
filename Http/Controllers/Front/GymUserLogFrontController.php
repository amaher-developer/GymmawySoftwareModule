<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Generic\Http\Controllers\Front\GenericFrontController;
use Modules\Software\Classes\TypeConstants;
use Modules\Billing\Models\SwBillingInvoice;
use Modules\Software\Exports\MembersAttendanceExport;
use Modules\Software\Exports\MembersExport;
use Modules\Software\Exports\MoneyBoxExport;
use Modules\Software\Exports\RecordsExport;
use Modules\Software\Http\Requests\GymActivityRequest;
use Modules\Software\Models\GymGroupDiscount;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymMemberAttendee;
use Modules\Software\Models\GymMemberSubscription;
use Modules\Software\Models\GymMemberSubscriptionFreeze;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Models\GymNonMemberTime;
use Modules\Software\Models\GymOnlinePaymentInvoice;
use Modules\Software\Models\GymPaymentType;
use Modules\Software\Models\GymPTClass;
use Modules\Software\Models\GymPTMember;
use Modules\Software\Models\GymPTSubscription;
use Modules\Software\Models\GymPTTrainer;
use Modules\Software\Models\GymStoreOrder;
use Modules\Software\Models\GymStoreOrderProduct;
use Modules\Software\Models\GymSubscription;
use Modules\Software\Models\GymUser;
use Modules\Software\Models\GymUserLog;
use Modules\Software\Models\GymUserNotification;
use Modules\Software\Repositories\GymActivityRepository;
use Modules\Software\Repositories\GymMemberAttendeeRepository;
use Modules\Software\Repositories\GymMoneyBoxRepository;
use Modules\Software\Repositories\GymUserAttendeeRepository;
use Modules\Software\Repositories\GymUserLogRepository;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Illuminate\Container\Container as Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Maatwebsite\Excel\Facades\Excel;

class GymUserLogFrontController extends GymGenericFrontController
{
    public $UserLogRepository;
    private $imageManager;
    public $UserAttendeeRepository;
    public $MemberAttendeeRepository;
    public $GymMoneyBoxRepository;
    public $payment_revenues;
    public $payment_expenses;
    public $fileName;

    public function __construct()
    {
        parent::__construct();
        $this->imageManager = new ImageManager(new Driver());

        $this->UserLogRepository = (new GymUserLogRepository(new Application))->branch();
        $this->MemberAttendeeRepository = (new GymMemberAttendeeRepository(new Application))->branch();
        $this->UserAttendeeRepository = (new GymUserAttendeeRepository(new Application))->branch();
        $this->GymMoneyBoxRepository = (new GymMoneyBoxRepository(new Application))->branch();
    }


    public function index()
    {
        $title = trans('sw.logs');
        $search_query = request()->query();

        $this->request_array = ['date', 'search'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;

        $logs = $this->UserLogRepository->with(['user']);
        if(!$this->user_sw->is_super_user)
            $logs->where('user_id', $this->user_sw->id);

        $logs->when(@$date, function ($query) use ($date) {
            $query->whereDate('created_at', '=', Carbon::parse($date)->toDateString());
        });

        $logs->when(@$search, function ($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('notes', 'like', "%" . $search . "%");
            });
        });
        $logs->orderBy('id', 'DESC');
        $logs = $logs->paginate($this->limit)->onEachSide(1);
        $total = $logs->total();



        return view('software::Front.log_front_list', compact('logs', 'search_query','title', 'total'));
    }

    public function reports(){

        $title = trans('sw.reports');
        return view('software::Front.reports_front_list', compact('title'));
    }
    public function reportRenewMemberList(){
        $title = trans('sw.logs_renew');
        $search_query = request()->query();
        $logs = $this->UserLogRepository->with(['user'])->where('type', 1)->orderBy('id', 'DESC');
        $logs = $logs->paginate($this->limit)->onEachSide(1);
        $total = $logs->total();

        return view('software::Front.report_renew_member_front_list', compact('logs', 'search_query','title', 'total'));
    }

    function exportRenewMemberExcel()
    {
        $this->limit = null;
        $records = $this->reportRenewMemberList()->with(\request()->all());
        $records = $records->logs;

        //        $records = $this->MemberRepository->with(['member_subscription_info.subscription'])->get();
        $this->fileName = 'renew-members-' . Carbon::now()->toDateTimeString();

//        $title = trans('sw.subscribed_clients');
//        $records = $this->prepareForExport($records);

        $notes = trans('sw.export_excel_members');
        $this->userLog($notes, TypeConstants::ExportRenewMemberExcel);

        return Excel::download(new MembersAttendanceExport(['records' => $records, 'keys' => [ 'notes', 'created_at'], 'lang' => $this->lang]), $this->fileName . '.xlsx');

    }

    function exportRenewMemberPDF()
    {
        $this->limit = null;
        $records = $this->reportRenewMemberList()->with(\request()->all());
        $records = $records->logs;

        $keys = ['notes', 'created_at'];
        if ($this->lang == 'ar') $keys = array_reverse($keys);

        $this->fileName = 'renew-members-' . Carbon::now()->toDateTimeString();
        foreach ($records as $key => $record) {
            $records[$key]['notes'] = $record['notes'];
            $records[$key]['created_at'] = $record['created_at'];
        }

        $title = trans('sw.logs_renew');
        $customPaper = array(0, 0, 720, 1440);
        
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


        $notes = trans('sw.export_pdf_members');
        $this->userLog($notes, TypeConstants::ExportRenewMemberPDF);

        return $pdf->download($this->fileName . '.pdf');
    }

    /* --------------------------------------------------------------------------- */

    public function reportExpireMemberList(){
        $title = trans('sw.logs_expire');
        $this->request_array = ['search', 'date', 'subscription'
            , 'remaining_status', 'status_now', 'to', 'from'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;

        $subscriptions = GymSubscription::branch()->get();
        $logs = GymMemberSubscription::branch()->with(['member.member_subscription_info', 'subscription' => function($q){$q->withTrashed();}]);
        $logs->whereHas('member', function($q){$q->whereNull('deleted_at');})
            ->orderBy('expire_date', 'ASC');
        if(!$from) {
            $logs->whereDate('expire_date', '>=', Carbon::now()->toDateString());
            //$logs->where('visits', '<=', 'workouts');
        }
        $logs->when(($status_now || ($status_now == 0) && ($status_now != '')), function ($query) use ($status_now) {
            $query->whereHas('member.member_subscription_info', function($q) use ($status_now) {$q->where('status' , @$status_now);});
        });
//        $logs->when(($date), function ($query) use ($date) {
//            $query->whereDate('expire_date', '=', Carbon::parse($date)->format('Y-m-d'));
//        });
        if(@$from && @$to) {
            $logs = $logs->when(($from), function ($query) use ($from) {
                $query->whereDate('expire_date', '>=', Carbon::parse($from)->format('Y-m-d'));
            })->when(($to), function ($query) use ($to) {
                $query->whereDate('expire_date', '<=', Carbon::parse($to)->format('Y-m-d'));
            });
        }
        $logs->when(($subscription), function ($query) use ($subscription) {
            $query->where('subscription_id', $subscription);
        })->when(($remaining_status), function ($query) use ($remaining_status) {
            if($remaining_status == TypeConstants::AMOUNT_REMAINING_STATUS_TURE)
                $query->whereRaw('ROUND(amount_remaining, 2) > 0');
            else
                $query->whereRaw('ROUND(amount_remaining, 2) = 0');
        })->when($search, function ($query) use ($search) {
            $query->whereHas('member', function ($q) use ($search){
                $q->where('id', '=', (int)$search);
                $q->orWhere('code', 'like', "%" . $search . "%");
                $q->orWhere('name', 'like', "%" . $search . "%");
                $q->orWhere('phone', 'like', "%" . $search . "%");
                $q->orWhere('address', 'like', "%" . $search . "%");
//            $q->whereRaw(' json_extract(activities->"$[*].name_ar", "'.$search.'")');
            });
        });
        if($this->limit){
            $logs = $logs->paginate($this->limit)->onEachSide(1);
            $total = $logs->total();
        } else {
            $logs = $logs->get();
            $total = $logs->count();
        }

        $search_query = request()->query();

        return view('software::Front.report_expire_member_front_list', compact('subscriptions', 'search_query','logs','title', 'total'));
    }

    function exportExpireMemberExcel()
    {
        $this->limit = null;
        $records = $this->reportExpireMemberList()->with(\request()->all());
        $records = $records->logs;

        //        $records = $this->MemberRepository->with(['member_subscription_info.subscription'])->get();
        $this->fileName = 'expire-members-' . Carbon::now()->toDateTimeString();

//        $title = trans('sw.subscribed_clients');
//        $records = $this->prepareForExport($records);

        $notes = trans('sw.export_excel_members');
        $this->userLog($notes, TypeConstants::ExportExpireMemberExcel);

        return Excel::download(new MembersAttendanceExport(['records' => $records, 'keys' => [ 'barcode', 'name', 'phone', 'subscription', 'workouts', 'number_of_visits', 'amount_remaining'
            , 'joining_date', 'expire_date', 'status'], 'lang' => $this->lang]), $this->fileName . '.xlsx');

    }

    function exportExpireMemberPDF()
    {
        $this->limit = null;
        $records = $this->reportExpireMemberList()->with(\request()->all());
        $records = $records->logs;

        $keys = ['barcode', 'name', 'phone', 'membership', 'workouts', 'number_of_visits', 'amount_remaining'
            , 'joining_date', 'expire_date', 'status'];
        if ($this->lang == 'ar') $keys = array_reverse($keys);

        $this->fileName = 'expire-members-' . Carbon::now()->toDateTimeString();
        foreach ($records as $key => $record) {
            $records[$key]['name'] = $record['member']['name'];
            $records[$key]['barcode'] = $record['member']['code'];
            $records[$key]['phone'] = (str_replace('+', '00', $record['member']['phone']));
            $records[$key]['membership'] = $record['subscription']['name'];
            $records[$key]['workouts'] = $record['member']['member_subscription_info']['workouts'];
            $records[$key]['number_of_visits'] = $record['member']['member_subscription_info']['visits'];
            $records[$key]['amount_remaining'] = $record['member']['member_subscription_info']['amount_remaining'];
            $records[$key]['joining_date'] = Carbon::parse($record['member']['member_subscription_info']['joining_date'])->toDateString();
            $records[$key]['expire_date'] = Carbon::parse($record['member']['member_subscription_info']['expire_date'])->toDateString();
            $records[$key]['status'] = $record['member']['member_subscription_info']['status_name'];

        }

        $title = trans('sw.subscribed_clients');
        $customPaper = array(0, 0, 720, 1440);
        
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


        $notes = trans('sw.export_pdf_members');
        $this->userLog($notes, TypeConstants::ExportExpireMemberPDF);

        return $pdf->download($this->fileName . '.pdf');
    }

    /* --------------------------------------------------------------------------- */


    public function reportSubscriptionMemberList(){
        $title = trans('sw.report_subscriptions');
        $this->request_array = ['search', 'from', 'to', 'subscription'
            , 'status', 'remaining_status', 'discount_status', 'joining_date', 'expire_date', 'group_discount_id'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;

        $subscriptions = GymSubscription::branch()->get();
        $group_discounts = GymGroupDiscount::branch()->where('is_member', 1)->get();
        $logs = GymMemberSubscription::branch()->with(['member', 'subscription' => function($q){$q->withTrashed();}
            ,'member.member_remain_amount_subscriptions.subscription' => function ($q) {
                $q->withTrashed();
            }
        ])->whereHas('member', function($q){$q->whereNull('deleted_at');})->orderBy('id', 'DESC');

        $logs->when(($from), function ($query) use ($from) {
            $query->whereDate('created_at', '>=', Carbon::parse($from)->format('Y-m-d'));
        })->when(($to), function ($query) use ($to) {
            $query->whereDate('created_at', '<=', Carbon::parse($to)->format('Y-m-d'));
        })->when(($subscription), function ($query) use ($subscription) {
            $query->where('subscription_id', $subscription);
        })->when(((isset($_GET['status'])) &&(!is_null($status))), function ($query) use ($status) {
            $query->where('status', $status);
        })->when(($remaining_status), function ($query) use ($remaining_status) {
            if($remaining_status == TypeConstants::AMOUNT_REMAINING_STATUS_TURE)
                $query->whereRaw('ROUND(amount_remaining, 2) > 0');
            else
                $query->whereRaw('ROUND(amount_remaining, 2) = 0');
        })->when(($discount_status), function ($query) use ($discount_status) {
            if($discount_status == TypeConstants::YES)
                $query->whereRaw('(ROUND(discount_value, 2) > 0)');
            else
                $query->whereRaw('ROUND(discount_value, 2) = 0');
        })->when(($group_discount_id), function ($query) use ($group_discount_id) {
            $query->where('group_discount_id', $group_discount_id);
        })->when(($joining_date), function ($query) use ($joining_date) {
            $query->whereDate('joining_date', '=', Carbon::parse($joining_date)->toDateString());
        })->when(($expire_date), function ($query) use ($expire_date) {
            $query->whereDate('expire_date', '=', Carbon::parse($expire_date)->toDateString());
        })->when($search, function ($query) use ($search) {
            $query->whereHas('member', function ($q) use ($search){
                $q->where('id', '=', (int)$search);
                $q->orWhere('code', 'like', "%" . $search . "%");
                $q->orWhere('name', 'like', "%" . $search . "%");
                $q->orWhere('phone', 'like', "%" . $search . "%");
                $q->orWhere('address', 'like', "%" . $search . "%");
//            $q->whereRaw(' json_extract(activities->"$[*].name_ar", "'.$search.'")');
            });
        });
        if($this->limit){
            $logs = $logs->paginate($this->limit)->onEachSide(1);
            $total = $logs->total();
        } else {
            $logs = $logs->get();
            $total = $logs->count();
        }
        $search_query = request()->query();

        return view('software::Front.report_subscription_member_front_list', compact('subscriptions', 'group_discounts','search_query','logs','title', 'total'));
    }
    function exportSubscriptionMemberExcel()
    {
        $this->limit = null;
        $records = $this->reportSubscriptionMemberList()->with(\request()->all());
        $records = $records->logs;

        //        $records = $this->MemberRepository->with(['member_subscription_info.subscription'])->get();
        $this->fileName = 'subscription-members-' . Carbon::now()->toDateTimeString();

//        $title = trans('sw.subscribed_clients');
//        $records = $this->prepareForExport($records);

        $notes = trans('sw.export_excel_members');
        $this->userLog($notes, TypeConstants::ExportSubscriptionMemberExcel);

        return Excel::download(new MembersAttendanceExport(['records' => $records, 'keys' => [  'barcode', 'name', 'phone', 'subscription', 'workouts', 'number_of_visits', 'amount_remaining'
            , 'joining_date', 'expire_date', 'status'], 'lang' => $this->lang]), $this->fileName . '.xlsx');

    }

    function exportSubscriptionMemberPDF()
    {
        $this->limit = null;
        $records = $this->reportSubscriptionMemberList()->with(\request()->all());
        $records = $records->logs;

        $keys = ['barcode', 'name', 'phone', 'membership', 'workouts', 'number_of_visits', 'amount_remaining'
            , 'joining_date', 'expire_date', 'status'];
        if ($this->lang == 'ar') $keys = array_reverse($keys);

        $this->fileName = 'subscription-members-' . Carbon::now()->toDateTimeString();
        foreach ($records as $key => $record) {
            $records[$key]['name'] = $record['member']['name'];
            $records[$key]['barcode'] = $record['member']['code'];
            $records[$key]['phone'] = (str_replace('+', '00', $record['member']['phone']));
            $records[$key]['membership'] = $record['subscription']['name'];
            $records[$key]['workouts'] = $record['member']['member_subscription_info']['workouts'];
            $records[$key]['number_of_visits'] = $record['member']['member_subscription_info']['visits'];
            $records[$key]['amount_remaining'] = $record['member']['member_subscription_info']['amount_remaining'];
            $records[$key]['joining_date'] = Carbon::parse($record['member']['member_subscription_info']['joining_date'])->toDateString();
            $records[$key]['expire_date'] = Carbon::parse($record['member']['member_subscription_info']['expire_date'])->toDateString();
            $records[$key]['status'] = $record['member']['member_subscription_info']['status_name'];

        }

        $title = trans('sw.subscribed_clients');
        $customPaper = array(0, 0, 720, 1440);
        
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


        $notes = trans('sw.export_pdf_members');
        $this->userLog($notes, TypeConstants::ExportSubscriptionMemberPDF);

        return $pdf->download($this->fileName . '.pdf');
    }

    /* --------------------------------------------------------------------------- */


    public function reportPTSubscriptionMemberList(){
        $title = trans('sw.report_pt_subscriptions');
        $this->request_array = ['search', 'pt_subscription', 'pt_class_id', 'pt_trainer'
            , 'status', 'remaining_status', 'discount_status', 'joining_date'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;

        $subscriptions = GymPTSubscription::branch()->with('pt_classes')->get();
        $classes = GymPTClass::branch()->get();
        $logs = GymPTMember::branch()->with(['member.member_subscription_info', 'pt_subscription' => function($q){$q->withTrashed();}])->whereHas('member', function($q){$q->whereNull('deleted_at');})->orderBy('id', 'DESC');

        $logs->when(($pt_subscription), function ($query) use ($pt_subscription) {
            $query->where('pt_subscription_id', $pt_subscription);
        })->when(intval(@$pt_class_id), function ($query) use ($pt_class_id) {
            $query->where('pt_class_id', $pt_class_id);
        })->when(((isset($_GET['status'])) &&(!is_null($status))), function ($query) use ($status) {
            $query->whereHas('member.member_subscription_info', function($q) use($status){$q->where('status', $status);});
        })->when(($remaining_status), function ($query) use ($remaining_status) {
            if($remaining_status == TypeConstants::AMOUNT_REMAINING_STATUS_TURE)
                $query->whereRaw('ROUND(amount_remaining, 2) > 0');
            else
                $query->whereRaw('ROUND(amount_remaining, 2) = 0');
        })->when(($discount_status), function ($query) use ($discount_status) {
            if($discount_status == TypeConstants::YES)
                $query->whereRaw('(ROUND(discount_value, 2) > 0)');
            else
                $query->whereRaw('ROUND(discount_value, 2) = 0');
        })->when(($joining_date), function ($query) use ($joining_date) {
            $query->whereDate('joining_date', '=', Carbon::parse($joining_date)->toDateString());
        })->when(($pt_trainer), function ($query) use ($pt_trainer) {
            $query->whereHas('pt_trainer', function ($q) use ($pt_trainer){
                $q->where('pt_trainer_id', $pt_trainer);
            });
        })->when($search, function ($query) use ($search) {
            $query->whereHas('member', function ($q) use ($search){
                $q->where('id', '=', (int)$search);
                $q->orWhere('code', 'like', "%" . $search . "%");
                $q->orWhere('name', 'like', "%" . $search . "%");
                $q->orWhere('phone', 'like', "%" . $search . "%");
                $q->orWhere('address', 'like', "%" . $search . "%");
//            $q->whereRaw(' json_extract(activities->"$[*].name_ar", "'.$search.'")');
            });
        });

        if($this->limit){
            $logs = $logs->paginate($this->limit)->onEachSide(1);
            $total = $logs->total();
        } else {
            $logs = $logs->get();
            $total = $logs->count();
        }
        $search_query = request()->query();

        $pt_trainers = GymPTTrainer::branch()->get();
        return view('software::Front.report_pt_subscription_member_front_list', compact('pt_trainers','subscriptions', 'classes', 'search_query','logs','title', 'total'));
    }

    function exportPTSubscriptionMemberExcel()
    {
        $this->limit = null;
        $records = $this->reportPTSubscriptionMemberList()->with(\request()->all());
        $records = $records->logs;

        //        $records = $this->MemberRepository->with(['member_subscription_info.subscription'])->get();
        $this->fileName = 'subscription-members-' . Carbon::now()->toDateTimeString();

//        $title = trans('sw.subscribed_clients');
//        $records = $this->prepareForExport($records);

        $notes = trans('sw.export_excel_members');
        $this->userLog($notes, TypeConstants::ExportPTSubscriptionMemberExcel);


        return Excel::download(new MembersAttendanceExport(['records' => $records, 'keys' => [  'barcode', 'name', 'phone', 'pt_subscription'
            ], 'lang' => $this->lang]), $this->fileName . '.xlsx');

    }

    function exportPTSubscriptionMemberPDF()
    {
        $this->limit = null;
        $records = $this->reportPTSubscriptionMemberList()->with(\request()->all());
        $records = $records->logs;

        $keys = [ 'barcode', 'name', 'phone', 'pt_membership'
//            , 'pt_classes', 'pt_visits', 'pt_amount_remaining'
//            , 'pt_joining_date', 'pt_expire_date'
        ];
        if ($this->lang == 'ar') $keys = array_reverse($keys);

        $this->fileName = 'pt-members-' . Carbon::now()->toDateTimeString();
        foreach ($records as $key => $record) {
            $records[$key]['barcode'] = $record['member']['code'];
            $records[$key]['name'] = $record['member']['name'];
            $records[$key]['phone'] = (str_replace('+', '00', $record['member']['phone']));
            $records[$key]['pt_membership'] = $record['pt_subscription']['name'];
//            $records[$key]['pt_classes'] = $record['pt_member_subscription']['classes'];
//            $records[$key]['pt_visits'] = $record['pt_member_subscription']['visits'];
//            $records[$key]['amount_remaining'] = $record['pt_member_subscription']['amount_remaining'];
//            $records[$key]['pt_joining_date'] = Carbon::parse($record['pt_member_subscription']['joining_date'])->toDateString();
//            $records[$key]['pt_expire_date'] = Carbon::parse($record['pt_member_subscription']['expire_date'])->toDateString();

        }

        $title = trans('sw.subscribed_clients');
        $customPaper = array(0, 0, 720, 1440);
        
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


        $notes = trans('sw.export_pdf_members');
        $this->userLog($notes, TypeConstants::ExportPTSubscriptionMemberPDF);

        return $pdf->download($this->fileName . '.pdf');
    }
    /* --------------------------------------------------------------------------- */

    public function reportDetailMemberList(){
        $title = trans('sw.logs_detail');
        $this->limit = 5;

        $this->request_array = ['search', 'subscription'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        $members = GymMember::branch()->with(['member_subscriptions'=> function($q){
            $q->orderBy('id', 'desc');
        },'member_subscriptions.subscription' => function($q){$q->withTrashed();}])->when($search, function ($query) use ($search) {
            $query->where('id', '=', (int)$search);
            $query->orWhere('code', 'like', "%" . $search . "%");
            $query->orWhere('name', 'like', "%" . $search . "%");
            $query->orWhere('phone', 'like', "%" . $search . "%");
            $query->orWhere('address', 'like', "%" . $search . "%");
//            $query->whereRaw(' json_extract(activities->"$[*].name_ar", "'.$search.'")');
        })->withCount('member_subscriptions');

        if($subscription == 1)
            $members->orderBy('member_subscriptions_count', 'asc');
        else
            $members->orderBy('member_subscriptions_count', 'desc');

        $members->orderBy('id', 'DESC');
        $members = $members->paginate($this->limit)->onEachSide(1);
        $total = $members->total();
        $search_query = request()->query();

        return view('software::Front.report_detail_member_front_list', compact('search_query', 'members','title', 'total'));
    }


    /* --------------------------------------------------------------------------- */

    public function reportTodayMemberList(){
        $search_query = request()->query();
        $title = trans('sw.client_attendees_today');
        $this->request_array = ['search', 'date', 'to', 'from'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;

        $logs = GymMemberAttendee::branch()->with(['member' => function($q){
                $q->withTrashed();
            }, 'member.member_subscription_info' => function($q){
            $q->withTrashed();
        },'member.member_remain_amount_subscriptions.subscription' => function ($q) {
            $q->withTrashed();
        }, 'user', 'member.member_subscription_info.subscription' => function ($q) {$q->withTrashed();}]);
        $logs->where('type', TypeConstants::ATTENDANCE_TYPE_GYM);
        $logs = $logs->when($search, function ($query) use ($search) {
            $query->whereHas('member', function ($q) use ($search) {
                    $q->where('id', '=', (int)$search);
                    $q->orWhere('code', 'like', "%" . $search . "%");
                    $q->orWhere('name', 'like', "%" . $search . "%");
                    $q->orWhere('phone', 'like', "%" . $search . "%");
                    $q->orWhere('address', 'like', "%" . $search . "%");
            });
        });
        if(@$from && @$to) {
            $logs = $logs->when(($from), function ($query) use ($from) {
                $query->whereDate('created_at', '>=', Carbon::parse($from)->format('Y-m-d'));
            })->when(($to), function ($query) use ($to) {
                $query->whereDate('created_at', '<=', Carbon::parse($to)->format('Y-m-d'));
            });
        }//else
            //$logs = $logs->whereDate('created_at', Carbon::now()->toDateString());

        $logs->orderBy('created_at', 'DESC');
        if($this->limit){
            $logs = $logs->paginate($this->limit)->onEachSide(1);
            $total = $logs->total();
        } else {
            $logs = $logs->get();
            $total = $logs->count();
        }
        return view('software::Front.report_today_member_front_list', compact('logs','search_query', 'title', 'total'));
    }

    function exportTodayMemberExcel()
    {
        $this->limit = null;
        $records = $this->reportTodayMemberList()->with(\request()->all());
        $records = $records->logs;

        //        $records = $this->MemberRepository->with(['member_subscription_info.subscription'])->get();
        $this->fileName = 'today-members-' . Carbon::now()->toDateTimeString();

//        $title = trans('sw.subscribed_clients');
//        $records = $this->prepareForExport($records);

        $notes = trans('sw.export_excel_members');
        $this->userLog($notes, TypeConstants::ExportTodayMemberExcel);

        return Excel::download(new MembersAttendanceExport(['records' => $records, 'keys' => [ 'created_at', 'barcode', 'name', 'phone', 'membership', 'workouts', 'number_of_visits', 'amount_remaining'
            , 'joining_date', 'expire_date', 'status'], 'lang' => $this->lang]), $this->fileName . '.xlsx');

    }

    function exportTodayMemberPDF()
    {
        $this->limit = null;
        $records = $this->reportTodayMemberList()->with(\request()->all());
        $records = $records->logs;

        $keys = ['barcode', 'name', 'phone', 'membership', 'workouts', 'number_of_visits', 'amount_remaining'
            , 'joining_date', 'expire_date', 'status'];
        if ($this->lang == 'ar') $keys = array_reverse($keys);

        $this->fileName = 'members-' . Carbon::now()->toDateTimeString();
        foreach ($records as $key => $record) {
            $records[$key]['created_at'] = Carbon::parse($record['created_at'])->toDateTimeString();
            $records[$key]['name'] = $record['member']['name'];
            $records[$key]['barcode'] = $record['member']['code'];
            $records[$key]['phone'] = (str_replace('+', '00', $record['member']['phone']));
            $records[$key]['membership'] = $record['member']['member_subscription_info']['subscription']['name'];
            $records[$key]['workouts'] = $record['member']['member_subscription_info']['workouts'];
            $records[$key]['number_of_visits'] = $record['member']['member_subscription_info']['visits'];
            $records[$key]['amount_remaining'] = $record['member']['member_subscription_info']['amount_remaining'];
            $records[$key]['joining_date'] = Carbon::parse($record['member']['member_subscription_info']['joining_date'])->toDateString();
            $records[$key]['expire_date'] = Carbon::parse($record['member']['member_subscription_info']['expire_date'])->toDateString();
            $records[$key]['status'] = $record['member']['member_subscription_info']['status_name'];

        }

        $title = trans('sw.subscribed_clients');
        $customPaper = array(0, 0, 720, 1440);
        
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


        $notes = trans('sw.export_pdf_members');
        $this->userLog($notes, TypeConstants::ExportTodayMemberPDF);

        return $pdf->download($this->fileName . '.pdf');
    }

    /**
     * Create a new attendance record for a member
     * @return \Illuminate\Http\JsonResponse
     */
    public function createAttendance()
    {
        try {
            $validator = \Validator::make(request()->all(), [
                'member_id' => 'required|integer|exists:sw_gym_members,id',
                'attendance_date' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => trans('sw.validation_error'),
                    'errors' => $validator->errors()
                ], 422);
            }

            $memberId = request()->get('member_id');
            $attendanceDate = request()->get('attendance_date');

            // Get member's last active subscription
            $member = \Modules\Software\Models\GymMember::branch()->find($memberId);

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => trans('sw.member_not_found')
                ], 404);
            }

            // Get the member's last subscription
            $subscription = \Modules\Software\Models\GymMemberSubscription::branch()
                ->where('status', TypeConstants::Active)
                ->where('member_id', $memberId)
                ->orderBy('id', 'desc')
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => trans('sw.no_subscription_found')
                ], 404);
            }

            // Create attendance record
            $attendance = new \Modules\Software\Models\GymMemberAttendee();
            $attendance->member_id = $memberId;
            $attendance->subscription_id = $subscription->id;
            $attendance->user_id = auth('sw')->id();
            $attendance->branch_setting_id = @$this->mainSettings->id;
            $attendance->created_at = Carbon::parse($attendanceDate);
            $attendance->updated_at = Carbon::parse($attendanceDate);
            $attendance->save();

            GymMemberSubscription::where('id', $attendance->subscription_id)
            ->increment('visits', 1);

            // Log the action
            $logNotes = trans('sw.attendance_created_for_member') . ': ' . $member->name;
            $this->userLog($logNotes, TypeConstants::CreateAttendance);

            return response()->json([
                'success' => true,
                'message' => trans('sw.attendance_created_successfully'),
                'data' => $attendance
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creating attendance: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => trans('sw.operation_failed') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an attendance record
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAttendance($id)
    {
        try {
            $attendance = \Modules\Software\Models\GymMemberAttendee::branch()->find($id);
            
            if (!$attendance) {
                return response()->json([
                    'success' => false,
                    'message' => trans('sw.attendance_not_found')
                ], 404);
            }

            $memberName = $attendance->member ? $attendance->member->name : trans('sw.unknown');

            GymMemberSubscription::where('id', $attendance->subscription_id)
    ->decrement('visits', 1);
            // Delete the attendance record
            $attendance->delete();

            // Log the action
            $logNotes = trans('sw.attendance_deleted_for_member') . ': ' . $memberName;
            $this->userLog($logNotes, TypeConstants::DeleteAttendance);

            return response()->json([
                'success' => true,
                'message' => trans('sw.attendance_deleted_successfully')
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting attendance: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => trans('sw.operation_failed') . ': ' . $e->getMessage()
            ], 500);
        }
    }


    /* --------------------------------------------------------------------------- */


    public function reportTodayPTMemberList(){
        $search_query = request()->query();
        $title = trans('sw.client_attendees_today');
        $this->request_array = ['search', 'date', 'to', 'from'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;

        $logs = GymMemberAttendee::branch()->with(['member' => function($q){
            $q->withTrashed();
        }, 'user', 'pt_member_subscription' => function($q){
            $q->withTrashed();
        }, 'pt_member_subscription.pt_subscription' => function($q){
            $q->withTrashed();
        }]);
        $logs->where('type', TypeConstants::ATTENDANCE_TYPE_PT);
        $logs = $logs->when($search, function ($query) use ($search) {
            $query->whereHas('member', function ($q) use ($search) {
                $q->where('id', '=', (int)$search);
                $q->orWhere('code', 'like', "%" . $search . "%");
                $q->orWhere('name', 'like', "%" . $search . "%");
                $q->orWhere('phone', 'like', "%" . $search . "%");
                $q->orWhere('address', 'like', "%" . $search . "%");
            });
        });
        if(@$from && @$to) {
            $logs->when(($from), function ($query) use ($from) {
                $query->whereDate('created_at', '>=', Carbon::parse($from)->format('Y-m-d'));
            })->when(($to), function ($query) use ($to) {
                $query->whereDate('created_at', '<=', Carbon::parse($to)->format('Y-m-d'));
            });
        }//else
            //$logs->whereDate('created_at', Carbon::now()->toDateString());
        $logs->orderBy('id', 'DESC');

        $statsQuery = clone $logs;
        $totalAttendances = (clone $statsQuery)->count();
        $attendedMemberIds = (clone $statsQuery)->pluck('pt_subscription_id')->filter()->unique();
        $uniqueMembers = $attendedMemberIds->count();
        $uniqueTrainers = 0;
        if ($attendedMemberIds->isNotEmpty()) {
            $uniqueTrainers = GymPTMember::branch()
                ->whereIn('id', $attendedMemberIds)
                ->whereNotNull('pt_trainer_id')
                ->distinct('pt_trainer_id')
                ->count('pt_trainer_id');
        }

        $stats = [
            'total_attendances' => $totalAttendances,
            'unique_members' => $uniqueMembers,
            'unique_trainers' => $uniqueTrainers,
        ];

        if($this->limit){
            $logs = $logs->paginate($this->limit)->onEachSide(1);
            $total = $logs->total();
        } else {
            $logs = $logs->get();
            $total = $logs->count();
        }
        return view('software::Front.report_today_pt_member_front_list', compact('logs','search_query','title', 'total', 'stats'));
    }
    function exportTodayPTMemberExcel()
    {
        $this->limit = null;
        $records = $this->reportTodayPTMemberList()->with(\request()->all());
        $records = $records->logs;

        //        $records = $this->MemberRepository->with(['member_subscription_info.subscription'])->get();
        $this->fileName = 'today-pt-members-' . Carbon::now()->toDateTimeString();

//        $title = trans('sw.subscribed_clients');
//        $records = $this->prepareForExport($records);

        $notes = trans('sw.export_excel_members');
        $this->userLog($notes, TypeConstants::ExportTodayPTMemberExcel);

        return Excel::download(new MembersAttendanceExport(['records' => $records, 'keys' => [ 'created_at', 'barcode', 'name', 'phone', 'pt_membership', 'pt_classes', 'pt_sessions_used', 'pt_amount_remaining'
            , 'pt_joining_date', 'pt_expire_date'], 'lang' => $this->lang]), $this->fileName . '.xlsx');

    }

    function exportTodayPTMemberPDF()
    {
        $this->limit = null;
        $records = $this->reportTodayPTMemberList()->with(\request()->all());
        $records = $records->logs;

        $keys = [ 'created_at', 'barcode', 'name', 'phone', 'pt_membership', 'pt_classes', 'pt_sessions_used', 'pt_amount_remaining'
            , 'pt_joining_date', 'pt_expire_date'];
        if ($this->lang == 'ar') $keys = array_reverse($keys);

        $this->fileName = 'pt-members-' . Carbon::now()->toDateTimeString();
        foreach ($records as $key => $record) {
            $records[$key]['created_at'] = Carbon::parse($record['created_at'])->toDateTimeString();
            $records[$key]['barcode'] = $record['member']['code'];
            $records[$key]['name'] = $record['member']['name'];
            $records[$key]['phone'] = (str_replace('+', '00', $record['member']['phone']));
            $records[$key]['pt_membership'] = $record['pt_member_subscription']['pt_subscription']['name'];
            $records[$key]['pt_classes'] = $record['pt_member_subscription']['sessions_total'] ?? $record['pt_member_subscription']['classes'];
            $records[$key]['pt_sessions_used'] = $record['pt_member_subscription']['sessions_used'] ?? $record['pt_member_subscription']['visits'];
            $records[$key]['amount_remaining'] = $record['pt_member_subscription']['amount_remaining'];
            $records[$key]['pt_joining_date'] = Carbon::parse($record['pt_member_subscription']['joining_date'])->toDateString();
            $records[$key]['pt_expire_date'] = Carbon::parse($record['pt_member_subscription']['expire_date'])->toDateString();

        }

        $title = trans('sw.subscribed_clients');
        $customPaper = array(0, 0, 720, 1440);
        
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


        $notes = trans('sw.export_pdf_members');
        $this->userLog($notes, TypeConstants::ExportTodayPTMemberPDF);

        return $pdf->download($this->fileName . '.pdf');
    }


    /* --------------------------------------------------------------------------- */


    public function reportTodayNonMemberList(){
        $search_query = request()->query();
        $title = trans('sw.non_client_attendees_today');
        $this->request_array = ['search', 'date', 'to', 'from'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;

        $logs = GymNonMemberTime::branch()->with(['member' => function($q){
            $q->withTrashed();
        }, 'non_member' => function($q){
            $q->withTrashed();
        }, 'user', 'activity']);
        $logs = $logs->when($search, function ($query) use ($search) {
            $query->whereHas('member', function ($q) use ($search) {
                $q->where('id', '=', (int)$search);
                $q->orWhere('code', 'like', "%" . $search . "%");
                $q->orWhere('name', 'like', "%" . $search . "%");
                $q->orWhere('phone', 'like', "%" . $search . "%");
                $q->orWhere('address', 'like', "%" . $search . "%");
            })->orWhereHas('non_member', function ($q) use ($search) {
                $q->where('id', '=', (int)$search);
                $q->orWhere('name', 'like', "%" . $search . "%");
                $q->orWhere('phone', 'like', "%" . $search . "%");
            });
        });
        if(@$from && @$to) {
            $logs->when(($from), function ($query) use ($from) {
                $query->whereDate('created_at', '>=', Carbon::parse($from)->format('Y-m-d'));
            })->when(($to), function ($query) use ($to) {
                $query->whereDate('created_at', '<=', Carbon::parse($to)->format('Y-m-d'));
            });
        }//else
            //$logs->whereDate('created_at', Carbon::now()->toDateString());
        $logs->orderBy('id', 'DESC');
        if($this->limit){
            $logs = $logs->paginate($this->limit)->onEachSide(1);
            $total = $logs->total();
        } else {
            $logs = $logs->get();
            $total = $logs->count();
        }
        return view('software::Front.report_today_non_member_front_list', compact('logs','search_query','title', 'total'));
    }
    function exportTodayNonMemberExcel()
    {
        $this->limit = null;
        $records = $this->reportTodayNonMemberList()->with(\request()->all());
        $records = $records->logs;

        //        $records = $this->MemberRepository->with(['member_subscription_info.subscription'])->get();
        $this->fileName = 'today-non-members-' . Carbon::now()->toDateTimeString();

//        $title = trans('sw.subscribed_clients');
//        $records = $this->prepareForExport($records);

        $notes = trans('sw.export_excel_members');
        $this->userLog($notes, TypeConstants::ExportTodayNonMemberExcel);

        return Excel::download(new MembersAttendanceExport(['records' => $records, 'keys' => [ 'non_created_at',  'non_name', 'non_phone', 'non_membership'], 'lang' => $this->lang]), $this->fileName . '.xlsx');

    }

    function exportTodayNonMemberPDF()
    {
        $this->limit = null;
        $records = $this->reportTodayNonMemberList()->with(\request()->all());
        $records = $records->logs;

        $keys = [  'non_created_at',  'non_name', 'non_phone', 'non_membership'];
        if ($this->lang == 'ar') $keys = array_reverse($keys);

        $this->fileName = 'non-members-' . Carbon::now()->toDateTimeString();
        foreach ($records as $key => $record) {
            $records[$key]['non_created_at'] = Carbon::parse($record['date'])->toDateTimeString();
            $records[$key]['non_name'] = @$record['member']['name'] ?? @$record['non_member']['name'];
            $records[$key]['non_phone'] = @$record['member']['phone'] ?? @$record['non_member']['phone'];
            $records[$key]['non_membership'] = $record['activity']['name'];

        }

        $title = trans('sw.subscribed_clients');
        $customPaper = array(0, 0, 720, 1440);
        
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


        $notes = trans('sw.export_pdf_members');
        $this->userLog($notes, TypeConstants::ExportTodayNonMemberPDF);

        return $pdf->download($this->fileName . '.pdf');
    }



    /* --------------------------------------------------------------------------- */



    public function reportUserAttendeesList(){
        $title = trans('sw.user_attendees_report');
        $search_query = request()->query();
        $this->request_array = ['search', 'date'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        $logs = GymUser::branch()->select('id', 'name', 'title', 'phone', 'image', "salary", "start_time_work", "end_time_work")
            ->with(['user_attendees' => function ($q) use ($date){
            if(@$date)
                $q->whereDate('created_at', Carbon::parse($date)->toDateString());
            else
                $q->whereDate('created_at', Carbon::now()->toDateString());
            $q->orderBy('id', 'ASC');
        }]);
//        if(@$date)
//            $logs->whereDate('created_at', '=', Carbon::parse($date)->toDateString());
//        else
//           $logs->whereDate('created_at', Carbon::now()->toDateString());
        $logs->orderBy('id', 'DESC');
        $logs = $logs->paginate($this->limit);
        $total = $logs->total();
        return view('software::Front.report_user_attendees_front_list', compact('logs', 'search_query','title', 'total'));
    }

    function exportUserAttendeesExcel()
    {
        $this->limit = null;
        $search_query = request()->query();
        $this->request_array = ['search', 'date'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        $logs = GymUser::branch()->select('id', 'name', 'title', 'phone', 'image', "salary", "start_time_work", "end_time_work")
            ->with(['user_attendees' => function ($q) use ($date){
            if(@$date)
                $q->whereDate('created_at', Carbon::parse($date)->toDateString());
            else
                $q->whereDate('created_at', Carbon::now()->toDateString());
            $q->orderBy('id', 'ASC');
        }]);
        $logs->orderBy('id', 'DESC');
        $records = $logs->limit(300)->get();

        $this->fileName = 'user-attendees-' . Carbon::now()->toDateTimeString();

        $notes = trans('sw.export_excel_members');
        $this->userLog($notes, TypeConstants::ExportUserAttendeesExcel);

        return Excel::download(new MembersAttendanceExport(['records' => $records, 'keys' => ['name', 'phone', 'title', 'created_at'], 'lang' => $this->lang]), $this->fileName . '.xlsx');
    }

    function exportUserAttendeesPDF()
    {
        $this->limit = null;
        $search_query = request()->query();
        $this->request_array = ['search', 'date'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        $logs = GymUser::branch()->select('id', 'name', 'title', 'phone', 'image', "salary", "start_time_work", "end_time_work")
            ->with(['user_attendees' => function ($q) use ($date){
            if(@$date)
                $q->whereDate('created_at', Carbon::parse($date)->toDateString());
            else
                $q->whereDate('created_at', Carbon::now()->toDateString());
            $q->orderBy('id', 'ASC');
        }]);
        $logs->orderBy('id', 'DESC');
        $records = $logs->limit(300)->get();

        $keys = ['name', 'phone', 'title', 'created_at'];
        if ($this->lang == 'ar') $keys = array_reverse($keys);

        $this->fileName = 'user-attendees-' . Carbon::now()->toDateTimeString();
        foreach ($records as $key => $record) {
            $records[$key]['name'] = $record['name'];
            $records[$key]['phone'] = $record['phone'];
            $records[$key]['title'] = $record['title'];
            $records[$key]['created_at'] = $record['created_at'];
        }

        $title = trans('sw.user_attendees_report');
        $customPaper = array(0, 0, 720, 1440);

        // Try mPDF for better Arabic support
        if ($this->lang == 'ar') {
            try {
                $mpdf = new Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4-L',
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
                $this->userLog($notes, TypeConstants::ExportUserAttendeesPDF);

                return response($mpdf->Output($this->fileName.'.pdf', 'D'), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $this->fileName . '.pdf"'
                ]);

            } catch (\Exception $e) {
                \Log::error('mPDF failed, falling back to DomPDF: ' . $e->getMessage());
            }
        }

        $pdf = PDF::loadView('software::Front.export_pdf', ['records' => $records, 'title' => $title, 'keys' => $keys])
        ->setPaper($customPaper, 'landscape')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'DejaVu Sans',
            'isPhpEnabled' => true,
            'isJavascriptEnabled' => false
        ]);

        $notes = trans('sw.export_pdf_members');
        $this->userLog($notes, TypeConstants::ExportUserAttendeesPDF);

        return $pdf->download($this->fileName . '.pdf');
    }

    public function reportZatcaInvoices()
    {
        if (!config('sw_billing.zatca_enabled')) {
            abort(404);
        }

        $title = trans('sw.zatca_invoices_report');
        $search_query = request()->query();

        $this->request_array = ['number', 'status', 'type', 'from', 'to', 'buyer', 'source', 'search'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) {
            $$item = request()->has($item) ? request()->$item : null;
        }

        $invoicesQuery = SwBillingInvoice::query()
            ->with([
                'moneyBox.member',
                'storeOrder.member',
                'nonMember',
                'member',
                'memberSubscription',
                'ptMember',
            ])
            ->orderByDesc('created_at');

        if ($from) {
            $invoicesQuery->whereDate('created_at', '>=', Carbon::parse($from)->toDateString());
        }

        if ($to) {
            $invoicesQuery->whereDate('created_at', '<=', Carbon::parse($to)->toDateString());
        }

        if ($status) {
            $invoicesQuery->where('zatca_status', $status);
        }

        if ($type) {
            $invoicesQuery->where('invoice_type', $type);
        }

        if ($number) {
            $invoicesQuery->where('invoice_number', 'like', '%' . $number . '%');
        }

        if ($buyer) {
            $invoicesQuery->where(function ($query) use ($buyer) {
                $query->where('buyer_name', 'like', '%' . $buyer . '%')
                    ->orWhere('buyer_tax_number', 'like', '%' . $buyer . '%');
            });
        }

        if ($source) {
            $invoicesQuery->where(function ($query) use ($source) {
                switch ($source) {
                    case 'money_box':
                        $query->whereNotNull('money_box_id');
                        break;
                    case 'store_order':
                        $query->whereNotNull('store_order_id');
                        break;
                    case 'non_member':
                        $query->whereNotNull('non_member_id');
                        break;
                    case 'member':
                        $query->where(function ($subQuery) {
                            $subQuery->whereNotNull('member_subscription_id')
                                ->orWhereNotNull('member_id');
                        });
                        break;
                    case 'pt_member':
                        $query->whereNotNull('member_pt_subscription_id');
                        break;
                }
            });
        }

        if ($search) {
            $invoicesQuery->where(function ($query) use ($search) {
                $query->where('invoice_number', 'like', '%' . $search . '%')
                    ->orWhere('buyer_name', 'like', '%' . $search . '%')
                    ->orWhere('buyer_tax_number', 'like', '%' . $search . '%')
                    ->orWhere('zatca_uuid', 'like', '%' . $search . '%');
            });
        }

        $invoices = $invoicesQuery->paginate($this->limit)->onEachSide(1);
        $total = $invoices->total();

        $statuses = SwBillingInvoice::select('zatca_status')->distinct()->pluck('zatca_status')->filter()->values();
        $types = SwBillingInvoice::select('invoice_type')->distinct()->pluck('invoice_type')->filter()->values();
        $sources = [
            'money_box' => trans('sw.source_money_box'),
            'store_order' => trans('sw.source_store_order'),
            'non_member' => trans('sw.source_non_member'),
            'member' => trans('sw.source_member'),
            'pt_member' => trans('sw.source_pt_member'),
        ];

        return view('software::Front.report_zatca_invoices_front_list', compact(
            'title',
            'search_query',
            'invoices',
            'statuses',
            'types',
            'sources',
            'total'
        ));
    }


    public function reportStoreList(){
        $title = trans('sw.store_report');
        $search_query = request()->query();
        $from = request('from');
        $to = request('to');
        $search = request('search');

        $productsQuery = GymStoreOrderProduct::branch()
            ->selectRaw('product_id, SUM(price) AS price, SUM(quantity) AS products')
            ->with(['product' => function ($q) {
                $q->withTrashed();
            }])
            ->groupBy('product_id')
            ->orderByDesc('products');

        $ordersQuery = GymStoreOrder::branch()
            ->with([
                'member' => function ($q) {
                    $q->withTrashed();
                },
                'order_product.product' => function ($q) {
                    $q->withTrashed();
                },
                'pay_type',
                'loyaltyRedemption',
                'zatcaInvoice',
            ])
            ->orderByDesc('created_at');

        if ($from) {
            $fromDate = Carbon::parse($from)->format('Y-m-d');
            $productsQuery->whereDate('created_at', '>=', $fromDate);
            $ordersQuery->whereDate('created_at', '>=', $fromDate);
        }

        if ($to) {
            $toDate = Carbon::parse($to)->format('Y-m-d');
            $productsQuery->whereDate('created_at', '<=', $toDate);
            $ordersQuery->whereDate('created_at', '<=', $toDate);
        }

        if ($search) {
            $ordersQuery->where(function ($query) use ($search) {
                $searchValue = trim($search);

                if (str_starts_with($searchValue, '#')) {
                    $query->where('id', (int) ltrim($searchValue, '#'));
                    return;
                }

                if (is_numeric($searchValue)) {
                    $numericValue = (int) $searchValue;
                    $query->where('id', $numericValue);
                } else {
                    $query->whereHas('member', function ($memberQuery) use ($searchValue) {
                        $memberQuery->where('name', 'like', '%' . $searchValue . '%')
                            ->orWhere('phone', 'like', '%' . $searchValue . '%');
                    });
                }
            });
        }

        $products = $productsQuery->get();

        if ($this->limit) {
            $orders = $ordersQuery->paginate($this->limit)->onEachSide(1);
            $total = $orders->total();
        } else {
            $orders = $ordersQuery->get();
            $total = $orders->count();
        }

        return view('software::Front.report_store_front_list', compact('search_query', 'orders', 'products', 'title', 'total'));

    }

    function exportStoreExcel()
    {
        $this->limit = null;
        $from = request('from');
        $to = request('to');
        $search = request('search');

        $ordersQuery = GymStoreOrder::branch()
            ->with([
                'member' => function ($q) {
                    $q->withTrashed();
                },
                'order_product.product' => function ($q) {
                    $q->withTrashed();
                },
                'pay_type',
                'loyaltyRedemption',
                'zatcaInvoice',
            ])
            ->orderByDesc('created_at');

        if ($from) {
            $fromDate = Carbon::parse($from)->format('Y-m-d');
            $ordersQuery->whereDate('created_at', '>=', $fromDate);
        }

        if ($to) {
            $toDate = Carbon::parse($to)->format('Y-m-d');
            $ordersQuery->whereDate('created_at', '<=', $toDate);
        }

        if ($search) {
            $ordersQuery->where(function ($query) use ($search) {
                $searchValue = trim($search);
                if (str_starts_with($searchValue, '#')) {
                    $query->where('id', (int) ltrim($searchValue, '#'));
                    return;
                }
                if (is_numeric($searchValue)) {
                    $numericValue = (int) $searchValue;
                    $query->where('id', $numericValue);
                } else {
                    $query->whereHas('member', function ($memberQuery) use ($searchValue) {
                        $memberQuery->where('name', 'like', '%' . $searchValue . '%')
                            ->orWhere('phone', 'like', '%' . $searchValue . '%');
                    });
                }
            });
        }

        $records = $ordersQuery->limit(300)->get();

        $this->fileName = 'store-report-' . Carbon::now()->toDateTimeString();

        $notes = trans('sw.export_excel_members');
        $this->userLog($notes, TypeConstants::ExportStoreExcel);

        return Excel::download(new MembersAttendanceExport(['records' => $records, 'keys' => ['id', 'member.name', 'member.phone', 'amount_paid', 'created_at'], 'lang' => $this->lang]), $this->fileName . '.xlsx');
    }

    function exportStorePDF()
    {
        $this->limit = null;
        $from = request('from');
        $to = request('to');
        $search = request('search');

        $ordersQuery = GymStoreOrder::branch()
            ->with([
                'member' => function ($q) {
                    $q->withTrashed();
                },
                'order_product.product' => function ($q) {
                    $q->withTrashed();
                },
                'pay_type',
                'loyaltyRedemption',
                'zatcaInvoice',
            ])
            ->orderByDesc('created_at');

        if ($from) {
            $fromDate = Carbon::parse($from)->format('Y-m-d');
            $ordersQuery->whereDate('created_at', '>=', $fromDate);
        }

        if ($to) {
            $toDate = Carbon::parse($to)->format('Y-m-d');
            $ordersQuery->whereDate('created_at', '<=', $toDate);
        }

        if ($search) {
            $ordersQuery->where(function ($query) use ($search) {
                $searchValue = trim($search);
                if (str_starts_with($searchValue, '#')) {
                    $query->where('id', (int) ltrim($searchValue, '#'));
                    return;
                }
                if (is_numeric($searchValue)) {
                    $numericValue = (int) $searchValue;
                    $query->where('id', $numericValue);
                } else {
                    $query->whereHas('member', function ($memberQuery) use ($searchValue) {
                        $memberQuery->where('name', 'like', '%' . $searchValue . '%')
                            ->orWhere('phone', 'like', '%' . $searchValue . '%');
                    });
                }
            });
        }

        $records = $ordersQuery->limit(300)->get();

        $keys = ['id', 'member.name', 'member.phone', 'amount_paid', 'created_at'];
        if ($this->lang == 'ar') $keys = array_reverse($keys);

        $this->fileName = 'store-report-' . Carbon::now()->toDateTimeString();
        foreach ($records as $key => $record) {
            $records[$key]['id'] = $record['id'];
            $records[$key]['member.name'] = $record->member->name ?? trans('sw.not_specified');
            $records[$key]['member.phone'] = $record->member->phone ?? trans('sw.not_specified');
            $records[$key]['amount_paid'] = $record['amount_paid'];
            $records[$key]['created_at'] = $record['created_at'];
        }

        $title = trans('sw.store_report');
        $customPaper = array(0, 0, 720, 1440);

        // Try mPDF for better Arabic support
        if ($this->lang == 'ar') {
            try {
                $mpdf = new Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4-L',
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
                $this->userLog($notes, TypeConstants::ExportStorePDF);

                return response($mpdf->Output($this->fileName.'.pdf', 'D'), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $this->fileName . '.pdf"'
                ]);

            } catch (\Exception $e) {
                \Log::error('mPDF failed, falling back to DomPDF: ' . $e->getMessage());
            }
        }

        $pdf = PDF::loadView('software::Front.export_pdf', ['records' => $records, 'title' => $title, 'keys' => $keys])
        ->setPaper($customPaper, 'landscape')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'DejaVu Sans',
            'isPhpEnabled' => true,
            'isJavascriptEnabled' => false
        ]);

        $notes = trans('sw.export_pdf_members');
        $this->userLog($notes, TypeConstants::ExportStorePDF);

        return $pdf->download($this->fileName . '.pdf');
    }

    public function reportMoneyboxTax(){
            $title = trans('sw.moneybox_tax');
            $this->request_array = ['search', 'from', 'to', 'transaction'];
            $request_array = $this->request_array;
            foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;

            $orders = GymMoneyBox::branch()->with(['user', 'member_subscription.member' => function($q){
                $q->withTrashed();
            }, 'member_pt_subscription' => function($q){
                $q->withTrashed();
            }, 'non_member_subscription' => function($q){
                $q->withTrashed();
            }, 'store_order' => function($q){
                $q->withTrashed();
            }])->orderBy('id', 'DESC');
            if($transaction == TypeConstants::TAX_TRANSACTION_SALES || !$transaction){
//                $orders->whereIn('type', [TypeConstants::EditMember, TypeConstants::EditSubscription, TypeConstants::EditNonMember, TypeConstants::EditPTMember]);
            }
            if(isset($_GET['type']) && request('type')){
                $types = [];
                if(request('type') == 1){
                    //members
                    $types = [TypeConstants::CreateMember, TypeConstants::RenewMember, TypeConstants::DeleteMember, TypeConstants::CreateSubscription, TypeConstants::DeleteSubscription];
                }elseif(request('type') == 2){
                    //daily member
                    $types = [TypeConstants::CreateNonMember, TypeConstants::DeleteNonMember, TypeConstants::EditActivity, TypeConstants::CreateActivity, TypeConstants::DeleteActivity];
                }elseif(request('type') == 3){
                    //pt
                    $types = [TypeConstants::CreatePTMember, TypeConstants::RenewPTMember, TypeConstants::DeletePTMember, TypeConstants::CreatePTSubscription, TypeConstants::DeletePTSubscription];
                }elseif(request('type') == 4){
                    //store
                    $types = [TypeConstants::CreateStoreProduct, TypeConstants::DeleteStoreProduct, TypeConstants::CreateStoreOrder, TypeConstants::DeleteStoreOrder
                        , TypeConstants::CreateStorePurchaseOrder, TypeConstants::DeleteStorePurchaseOrder];
                }elseif(request('type') == 5){
                    //moneybox
                    $types = [TypeConstants::CreateMoneyBoxAdd, TypeConstants::CreateMoneyBoxWithdraw, TypeConstants::CreateMoneyBoxWithdrawEarnings];
                }
                $orders->whereIn('type', $types);
            }else {
                $orders->whereIn('type', [
                    TypeConstants::CreateMember, TypeConstants::RenewMember, TypeConstants::DeleteMember, TypeConstants::CreateSubscription, TypeConstants::DeleteSubscription
                    , TypeConstants::CreateNonMember, TypeConstants::DeleteNonMember, TypeConstants::EditActivity, TypeConstants::CreateActivity, TypeConstants::DeleteActivity
//                , TypeConstants::CreateMemberPayAmountRemainingForm
                    , TypeConstants::CreatePTMember, TypeConstants::RenewPTMember, TypeConstants::DeletePTMember, TypeConstants::CreatePTSubscription, TypeConstants::DeletePTSubscription
                    , TypeConstants::CreateStoreProduct, TypeConstants::DeleteStoreProduct, TypeConstants::CreateStoreOrder, TypeConstants::DeleteStoreOrder
                    , TypeConstants::CreateStorePurchaseOrder, TypeConstants::DeleteStorePurchaseOrder
                    , TypeConstants::CreateMoneyBoxAdd, TypeConstants::CreateMoneyBoxWithdraw, TypeConstants::CreateMoneyBoxWithdrawEarnings
                ]);
            }
            //apply filters
            $orders->when(($from), function ($query) use ($from) {
                $query->whereDate('created_at', '>=', Carbon::parse($from)->format('Y-m-d'));
            })->when((@$this->mainSettings->vat_details['vat_percentage']), function ($query) {
                $query->where('vat', '>', 0);
            })->when(($to), function ($query) use ($to) {
                $query->whereDate('created_at', '<=', Carbon::parse($to)->format('Y-m-d'));
            })->when(($search), function ($query) use ($search) {
                if((string)$search[0] == "#"){
                    $query->where('id', @(int)trim($search, '#'));
                } else {
                    $query->where('id', '=', (int)$search);
                    $query->orWhere('amount', '=', (int)$search)
                        ->orWhere('notes', 'like', "%" . $search . "%");
                }
            })->when(($transaction), function ($query) use ($transaction) {
                $operation = intVal($transaction-1);
                $query->where('operation', '=', $operation);
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
                $sorders = $orders->get()->filter(function ($item){
//                    if(@$item->member_pt_subscription){
//                        $item->amount = round((@$item->member_pt_subscription->amount_paid + @$item->member_pt_subscription->amount_remaining), 2);
//                    }elseif(@$item->non_member_subscription){
//                        $item->amount = round((@$item->non_member_subscription->price + @$item->non_member_subscription->price_remaining), 2);
//                    }elseif(@$item->member_subscription){
//                        $item->amount = round((@$item->member_subscription->amount_paid + @$item->member_subscription->amount_remaining), 2);
//                    }else{
                        $item->amount =round($item->amount,2);
//                    }
                    return $item;
                });
                $orders = $orders->paginate($this->limit)->onEachSide(1);
                $total = $orders->total();
            } else {
                $orders = $orders->get();
                $total = $orders->count();
            }

            $revenues = ($sorders->where('operation', 0)->sum('amount'));
            $expenses = ($sorders->where('operation', 1)->sum('amount'));
            $earnings = ($revenues - $expenses);

        $payment_types = GymPaymentType::branch()->orderBy('id')->get();
        if ($payment_types->isEmpty()) {
            $payment_types = GymPaymentType::orderBy('id')->get();
        }

        ($sorders->filter(function ($item) use ($payment_types) {
            foreach ($payment_types as $i => $payment_type){
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
                if (($item->member_subscription_id != null || $item->member_pt_subscription_id != null) && (@$item->payment_type == $payment_type->payment_id)  && ($item->operation == TypeConstants::Sub) ) {
                    $this->payment_expenses[$payment_type->payment_id] = (@$this->payment_expenses[$payment_type->payment_id]) + $item->amount;
                    return $item;
                }
            }
//            return $item->where('payment_type', TypeConstants::CASH_PAYMENT);
        }));//->where('operation', 1)->sum('amount'));

        $payment_expenses = $this->payment_expenses;

        $cache_earnings = 0;//($cache_revenues - $cache_expenses);

//            $online_revenues = ($sorders->filter(function ($item) {
//                if(($item->member_subscription_id != null || $item->member_pt_subscription_id != null) && (@$item->payment_type == TypeConstants::ONLINE_PAYMENT)){
//                    return $item;
//                }
//            })->where('operation', 0)->sum('amount'));
//            $online_expenses = ($sorders->filter(function ($item) {
//                if(($item->member_subscription_id != null || $item->member_pt_subscription_id != null) && (@$item->payment_type == TypeConstants::ONLINE_PAYMENT)){
//                    return $item;
//                }
//            })->where('operation', 1)->sum('amount'));
//            $online_earnings = ($online_revenues - $online_expenses);
//
//            $bank_revenues = ($sorders->filter(function ($item) {
//                if(($item->member_subscription_id != null || $item->member_pt_subscription_id != null) && (@$item->payment_type == TypeConstants::BANK_TRANSFER_PAYMENT)){
//                    return $item;
//                }
//            })->where('operation', 0)->sum('amount'));
//            $bank_expenses = ($sorders->filter(function ($item) {
//                if(($item->member_subscription_id != null || $item->member_pt_subscription_id != null) && (@$item->payment_type == TypeConstants::BANK_TRANSFER_PAYMENT)){
//                    return $item;
//                }
//            })->where('operation', 1)->sum('amount'));
//            $bank_earnings = ($bank_revenues - $bank_expenses);

//            $total_add_to_money_box = ($sorders->where('type', TypeConstants::CreateMoneyBoxAdd)->where('operation', 0)->sum('amount'));
//            $total_withdraw_from_money_box = ($sorders->where('type', TypeConstants::CreateMoneyBoxWithdraw)->where('operation', 1)->sum('amount'));

            $total_activities = ($sorders->whereIn('type', [TypeConstants::CreateNonMember,  TypeConstants::DeleteNonMember,  TypeConstants::CreateActivity, TypeConstants::DeleteActivity ])->where('operation', 0)->sum('amount')
                - $sorders->whereIn('type', [TypeConstants::CreateNonMember, TypeConstants::DeleteNonMember, TypeConstants::EditActivity, TypeConstants::CreateActivity, TypeConstants::DeleteActivity ])->where('operation', 1)->sum('amount'));

            $total_subscriptions = ($sorders->whereIn('type', [TypeConstants::CreateMember,TypeConstants::RenewMember,TypeConstants::DeleteMember,  TypeConstants::CreateSubscription,TypeConstants::DeleteSubscription])->where('operation', 0)->sum('amount')
                - $sorders->whereIn('type', [TypeConstants::CreateMember,TypeConstants::RenewMember,TypeConstants::DeleteMember,TypeConstants::CreateSubscription,TypeConstants::DeleteSubscription ])->where('operation', 1)->sum('amount'));

            $total_pt_subscriptions = ($sorders->whereIn('type', [TypeConstants::CreatePTMember,TypeConstants::RenewPTMember,TypeConstants::DeletePTMember,TypeConstants::CreatePTSubscription,TypeConstants::DeletePTSubscription])->where('operation', 0)->sum('amount')
                - $sorders->whereIn('type', [TypeConstants::CreatePTMember,TypeConstants::RenewPTMember,TypeConstants::DeletePTMember,TypeConstants::CreatePTSubscription,TypeConstants::DeletePTSubscription ])->where('operation', 1)->sum('amount'));

//            $total_stores = ($sorders->whereIn('type', [TypeConstants::CreateStoreProduct,TypeConstants::EditStoreProduct,TypeConstants::DeleteStoreProduct ])->where('operation', 0)->sum('amount')
//                                - $sorders->whereIn('type', [TypeConstants::CreateStoreProduct,TypeConstants::EditStoreProduct,TypeConstants::DeleteStoreProduct ])->where('operation', 1)->sum('amount'));

            $total_stores = ($sorders->whereIn('type', [TypeConstants::CreateStoreProduct,TypeConstants::DeleteStoreProduct, TypeConstants::CreateStoreOrder,TypeConstants::DeleteStoreOrder, TypeConstants::CreateStorePurchaseOrder, TypeConstants::DeleteStorePurchaseOrder  ])->where('operation', 0)->sum('amount')
                - $sorders->whereIn('type', [TypeConstants::CreateStoreProduct,TypeConstants::DeleteStoreProduct, TypeConstants::CreateStoreOrder,TypeConstants::DeleteStoreOrder, TypeConstants::CreateStorePurchaseOrder, TypeConstants::DeleteStorePurchaseOrder  ])->where('operation', 1)->sum('amount'));

            $total_moneybox = ($sorders->whereIn('type', [TypeConstants::CreateMoneyBoxAdd, TypeConstants::CreateMoneyBoxWithdraw, TypeConstants::CreateMoneyBoxWithdrawEarnings  ])->where('operation', 0)->sum('amount')
                - $sorders->whereIn('type', [TypeConstants::CreateMoneyBoxAdd, TypeConstants::CreateMoneyBoxWithdraw, TypeConstants::CreateMoneyBoxWithdrawEarnings  ])->where('operation', 1)->sum('amount'));


        return view('software::Front.report_moneybox_tax_front_list', compact(
                'revenues', 'expenses', 'earnings'
//                ,'cache_revenues', 'cache_expenses', 'cache_earnings'
//                ,'online_revenues', 'online_expenses', 'online_earnings'
//                ,'bank_revenues', 'bank_expenses', 'bank_earnings'
//                ,'total_add_to_money_box', 'total_withdraw_from_money_box'
                ,'total_activities', 'total_subscriptions', 'total_pt_subscriptions', 'total_stores', 'total_moneybox'
                , 'orders', 'title', 'total', 'search_query'
                , 'payment_expenses', 'payment_revenues', 'payment_types'));

    }

    function exportExcelMoneyboxTax(){
        $from = request('from');
        $to = request('to');;
        $transaction = request('transaction');
        $operation = intVal($transaction-1);

        $records = GymMoneyBox::branch()->with(['user', 'member_subscription.member' => function($q){
            $q->withTrashed();
        }, 'member_pt_subscription' => function($q){
            $q->withTrashed();
        }, 'non_member_subscription' => function($q){
            $q->withTrashed();
        }, 'store_order' => function($q){
            $q->withTrashed();
        }])
           ->whereIn('type', [
               TypeConstants::CreateMember,TypeConstants::RenewMember,TypeConstants::DeleteMember,  TypeConstants::CreateSubscription,TypeConstants::DeleteSubscription
               ,TypeConstants::CreateNonMember, TypeConstants::DeleteNonMember, TypeConstants::EditActivity, TypeConstants::CreateActivity, TypeConstants::DeleteActivity
//                , TypeConstants::CreateMemberPayAmountRemainingForm
               , TypeConstants::CreatePTMember,TypeConstants::RenewPTMember,TypeConstants::DeletePTMember,TypeConstants::CreatePTSubscription,TypeConstants::DeletePTSubscription
               , TypeConstants::CreateStoreProduct,TypeConstants::DeleteStoreProduct, TypeConstants::CreateStoreOrder,TypeConstants::DeleteStoreOrder
               , TypeConstants::CreateStorePurchaseOrder, TypeConstants::DeleteStorePurchaseOrder
           ])
            ->whereDate('created_at', '>=', Carbon::parse($from)->format('Y-m-d'))->whereDate('created_at', '<=', Carbon::parse($to)->format('Y-m-d'));
        if($transaction){
            $records->where('operation', $operation);
        }
        $records = $records->get();

        $this->fileName = 'reports-' . Carbon::now()->toDateTimeString();
//        $title = trans('sw.moneybox');
        $records = $this->prepareForExport($records);
        $notes = trans('sw.export_excel_moneybox');
        $this->userLog($notes, TypeConstants::ExportMoneyboxExcel);

        return \Maatwebsite\Excel\Facades\Excel::download(new MoneyBoxExport(['records' => $records, 'keys' => ['id', 'invoice_total', 'vat_total', 'invoice_total_required', 'notes', 'date', 'by'],'lang' => $this->lang]), $this->fileName.'.xlsx');
    }
    private function prepareForExport($data)
    {
        $name = [trans('sw.amount'), trans('sw.total_amount_before'), trans('sw.total_amount_after')
            , trans('sw.operation'), trans('sw.payment_type'), trans('sw.notes'), trans('sw.date') , trans('sw.by')];
            $records = [];
        foreach($data as $key => $record){
            $total_before_vat = 0;
            $total_after_vat = 0;
            if(@$record['member_pt_subscription']){
                $total_before_vat =  round((@$record['member_pt_subscription']['amount_paid'] + @$record['member_pt_subscription']['amount_remaining']) / ((100+@$this->mainSettings->vat_details['vat_percentage'])/100), 2);
            }else if(@$record['non_member_subscription'] || ((int)$record['type'] === \Modules\Software\Classes\TypeConstants::CreateNonMember)){
                $total_before_vat =  round((@$record['non_member_subscription']['price'] + @$record['non_member_subscription']['price_remaining']) / ((100+@$this->mainSettings->vat_details['vat_percentage'])/100), 2);
                if(!@$record['non_member_subscription']['price']){
                    $total_before_vat= round(((@$record['amount']) / ((100+@$this->mainSettings->vat_details['vat_percentage'])/100)),2);
                }
            }else if(@$record['store_order'] || ((int)$record['type'] === \Modules\Software\Classes\TypeConstants::CreateStoreOrder)){
                $total_before_vat =  round((@$record['store_order']['amount_paid'] + @$record['store_order']['amount_remaining']) / ((100+@$this->mainSettings->vat_details['vat_percentage'])/100), 2);
                if(!@$record['store_order']['amount_paid']){
                    $total_before_vat= round(((@$record['amount']) / ((100+@$this->mainSettings->vat_details['vat_percentage'])/100)), 2);
                }
            }else if(@$record['member_subscription']){
                $total_before_vat =  round((@$record['member_subscription']['amount_paid'] + @$record['member_subscription']['amount_remaining']) / ((100+@$this->mainSettings->vat_details['vat_percentage'])/100), 2);
            }else{
                $total_before_vat =  round($record['amount'] /((100+@$this->mainSettings->vat_details['vat_percentage'])/100),2);
            }
//            if(@$order->member_pt_subscription){
//                $total_after_vat = round((@$order->member_pt_subscription->amount_paid + @$order->member_pt_subscription->amount_remaining), 2);
//                $client_name = @$order->member_pt_subscription->member->name;
//                $product_name = @$order->member_pt_subscription->pt_subscription->name;
//            }elseif(@$order->non_member_subscription){
//                $total_after_vat = round((@$order->non_member_subscription->price + @$order->non_member_subscription->price_remaining), 2);
//                $client_name = @$order->non_member_subscription->name;
//                $product_name = @$order->non_member_subscription->name;
//            }elseif(@$order->store_order){
//                $total_after_vat = round((@$order->store_order->amount_paid + @$order->store_order->amount_remaining), 2);
//                $client_name = @$order->store_order->name;
//                $product_name = @$order->store_order->name;
//            }elseif(@$order->member_subscription){
//                $total_after_vat = round((@$order->member_subscription->amount_paid + @$order->non_member_subscription->amount_remaining), 2);
//                $client_name = @$order->member_subscription->member->name;
//                $product_name = @$order->member_subscription->subscription->name;
//            }else{
//                $total_after_vat =round($order->amount,2);
//                $client_name = trans('sw.guest');
//                $product_name = trans('sw.product');
//
//            }
//            $vat = @$total_after_vat - @$total_before_vat;



            if($record['member_pt_subscription']){
                $total_after_vat = round((@$record['member_pt_subscription']['amount_paid'] + @$record['member_pt_subscription']['amount_remaining']), 2);

                //$amount = $record['member_pt_subscription']['amount_paid'] + $record['member_pt_subscription']['amount_remaining'];
                $client_name = @$record['member_pt_subscription']['member']['name'];
                $product_name = @$record['member_pt_subscription']['pt_subscription']['name'];
            }elseif($record['member_subscription']){
                $total_after_vat = round((@$record['member_subscription']['amount_paid'] + @$record['member_subscription']['amount_remaining']), 2);

                //$amount = $record['member_subscription']['amount_paid'] + $record['member_subscription']['amount_remaining'];
                $client_name = @$record['member_subscription']['member']['name'];
                $product_name = @$record['member_subscription']['subscription']['name'];
            }elseif(@$record['non_member_subscription']){
                $total_after_vat = round((@$record['non_member_subscription']['price'] + @$record['non_member_subscription']['price_remaining']), 2);
                $client_name = @$record['non_member_subscription']['name'];
                $product_name = @$record['non_member_subscription']['name'];
            }elseif(@$record['store_order']){
                $total_after_vat = round((@$record['store_order']['amount_paid'] + @$record['store_order']['amount_remaining']), 2);
                $client_name = @$record['store_order']['name'] ?? trans('sw.guest');
                $product_name = @$record['store_order']['name'] ?? trans('sw.products');

            }else{
                $total_after_vat = round((@$record['amount']), 2);

                //$amount = $record['amount'];
                $client_name = trans('sw.guest');
                $product_name = trans('sw.product');
            }
            $vat = @$total_after_vat - @$total_before_vat;

            // add this edit 8/6/2025
            // $vat = @$record['vat'];
            // $total_before_vat = @$record['amount'];
            // $total_after_vat = @$record['amount'];
            //


//            $record->invoice_total = number_format(($amount-$record['vat']), 2);
            $invoice_total = $total_before_vat;//round($amount / ((100+@$this->mainSettings->vat_details['vat_percentage'])/100), 2);
            $invoice_total_required = $total_after_vat;//round($amount, 2);
            //$vat = ($record['operation'] == 1 ? -1 : 1) * round(($invoice_total_required - $invoice_total ), 2);
            if(($invoice_total > 0) && ($invoice_total_required > 0) && ($vat > 0)){
                $records[$key]['id'] =  $record['id'];
                $records[$key]['invoice_total'] = ($record['operation'] == 1 ? -1 : 1) * $invoice_total;
                $records[$key]['invoice_total_required'] =  ($record['operation'] == 1 ? -1 : 1) * $invoice_total_required;
                $records[$key]['vat_total'] =  ($record['operation'] == 1 ? -1 : 1) * $vat;
                $records[$key]['notes'] = trans(($record['operation'] == TypeConstants::Add ? 'sw.tax_msg_sales' : 'sw.tax_msg_funds'), ['name' => $client_name,'amount' => $invoice_total_required, 'product' => $product_name ]);
                $records[$key]['date'] = Carbon::parse($record['created_at'])->format('Y-m-d') . ' ' . Carbon::parse($record['created_at'])->format('h:i a');
                $records[$key]['by'] = @$record['user']['name'];
                $records[$key]['user']['name'] = @$record['user']['name'];            
                $records[$key]['created_at'] = @Carbon::parse($record['created_at'])->format('Y-m-d') . ' ' . Carbon::parse($record['created_at'])->format('h:i a');
            }
        }
//        array_unshift($result, $name);
//        array_unshift($result, [trans('sw.moneybox')]);
        return collect($records);
    }

    function exportPDFMoneyboxTax(){

        $from = request('from');
        $to = request('to');
        $transaction = request('transaction');
        $operation = intVal($transaction-1);

        $records = $this->GymMoneyBoxRepository->with(['user', 'member_subscription.member' => function($q){
            $q->withTrashed();
        }, 'member_pt_subscription' => function($q){
            $q->withTrashed();
        }])
            ->whereIn('type', [
                TypeConstants::CreateMember,TypeConstants::RenewMember,TypeConstants::DeleteMember,  TypeConstants::CreateSubscription,TypeConstants::DeleteSubscription
                ,TypeConstants::CreateNonMember, TypeConstants::DeleteNonMember, TypeConstants::EditActivity, TypeConstants::CreateActivity, TypeConstants::DeleteActivity
//                , TypeConstants::CreateMemberPayAmountRemainingForm
                , TypeConstants::CreatePTMember,TypeConstants::RenewPTMember,TypeConstants::DeletePTMember,TypeConstants::CreatePTSubscription,TypeConstants::DeletePTSubscription
                , TypeConstants::CreateStoreProduct,TypeConstants::DeleteStoreProduct, TypeConstants::CreateStoreOrder,TypeConstants::DeleteStoreOrder
                , TypeConstants::CreateStorePurchaseOrder, TypeConstants::DeleteStorePurchaseOrder
            ])
            ->whereDate('created_at', '>=', Carbon::parse($from)->format('Y-m-d'))->whereDate('created_at', '<=', Carbon::parse($to)->format('Y-m-d'));
        if($transaction){
            $records->where('operation', $operation);
        }
        $records = $records->get();
        $this->fileName = 'reports-' . Carbon::now()->toDateTimeString();
        $keys = ['id', 'invoice_total', 'vat_total', 'invoice_total_required',  'notes', 'created_at', 'by'];
        if($this->lang == 'ar') $keys = array_reverse($keys);
        for($i = 0; count($records) > $i;$i++ ){
//            $records[$i]['invoice_total'] = ($records[$i]['amount'] - $records[$i]['vat']);
            if($records[$i]['member_pt_subscription']){
                $amount = $records[$i]['member_pt_subscription']['amount_paid'] + $records[$i]['member_pt_subscription']['amount_remaining'];
                $client_name = $records[$i]['member_pt_subscription']['member']['name'];
                $product_name = $records[$i]['member_pt_subscription']['pt_subscription']['name'];
            }elseif($records[$i]['member_subscription']){
                $amount = $records[$i]['member_subscription']['amount_paid'] + $records[$i]['member_subscription']['amount_remaining'];
                $client_name = @$records[$i]['member_subscription']['member']['name'];
                $product_name = @$records[$i]['member_subscription']['subscription']['name'];
            }else{
                $amount = $records[$i]['amount'];
                $client_name = trans('sw.guest');
                $product_name = trans('sw.product');
            }
            $invoice_total = round(($amount / ((100+@$this->mainSettings->vat_details['vat_percentage'])/100)), 2);
            $invoice_total_required = round($amount, 2);
            $vat = round(($invoice_total_required - $invoice_total ), 2);

            // add this edit 8/6/2025
            $vat = @$records[$i]['vat'];
            $invoice_total = @$records[$i]['amount'];
            $invoice_total_required = @$records[$i]['amount'];
            //

            $records[$i]['invoice_total'] = $invoice_total;
            $records[$i]['vat_total'] = $vat;
            $records[$i]['invoice_total_required'] = $invoice_total_required;
            $records[$i]['notes']  = trans(($records[$i]['operation'] == TypeConstants::Add ? 'sw.tax_msg_sales' : 'sw.tax_msg_funds'), ['name' => $client_name,'amount' => $invoice_total_required, 'product' => $product_name ]);
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






    public function reportOnlinePaymentTransactionList(){
        $title = trans('sw.online_transaction_report');
        $this->request_array = ['search', 'from', 'to', 'transaction'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;

        $orders = GymOnlinePaymentInvoice::branch()->with(['member', 'subscription' => function($q){
            $q->withTrashed();
        }])->orderBy('id', 'DESC');

        //apply filters
        $orders->when(($from), function ($query) use ($from) {
            $query->whereDate('created_at', '>=', Carbon::parse($from)->format('Y-m-d'));
        })->when(($to), function ($query) use ($to) {
            $query->whereDate('created_at', '<=', Carbon::parse($to)->format('Y-m-d'));
        })->when(($search), function ($query) use ($search) {
            $query->where('id', '=', (int)$search);
            $query->orWhere('name', '=', (int)$search);
            $query->orWhere('phone', '=', (int)$search);
            $query->orWhere('address', '=', (int)$search);
        });
        $search_query = request()->query();

        if ($this->limit) {
            $orders = $orders->paginate($this->limit)->onEachSide(1);
            $total = $orders->total();
        } else {
            $orders = $orders->get();
            $total = $orders->count();
        }

      return view('software::Front.report_online_payment_transactions_front_list', compact( 'orders', 'title', 'total', 'search_query'));

    }

    function exportOnlinePaymentExcel()
    {
        $this->limit = null;
        $this->request_array = ['search', 'from', 'to', 'transaction'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;

        $orders = GymOnlinePaymentInvoice::branch()->with(['member', 'subscription' => function($q){
            $q->withTrashed();
        }])->orderBy('id', 'DESC');

        //apply filters
        $orders->when(($from), function ($query) use ($from) {
            $query->whereDate('created_at', '>=', Carbon::parse($from)->format('Y-m-d'));
        })->when(($to), function ($query) use ($to) {
            $query->whereDate('created_at', '<=', Carbon::parse($to)->format('Y-m-d'));
        })->when(($search), function ($query) use ($search) {
            $query->where('id', '=', (int)$search);
            $query->orWhere('name', '=', (int)$search);
            $query->orWhere('phone', '=', (int)$search);
            $query->orWhere('address', '=', (int)$search);
        });

        $records = $orders->limit(300)->get();

        $this->fileName = 'online-payment-transactions-' . Carbon::now()->toDateTimeString();

        $notes = trans('sw.export_excel_members');
        $this->userLog($notes, TypeConstants::ExportOnlinePaymentExcel);

        return Excel::download(new MembersAttendanceExport(['records' => $records, 'keys' => ['name', 'phone', 'subscription.name', 'amount', 'status', 'created_at'], 'lang' => $this->lang]), $this->fileName . '.xlsx');
    }

    function exportOnlinePaymentPDF()
    {
        $this->limit = null;
        $this->request_array = ['search', 'from', 'to', 'transaction'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;

        $orders = GymOnlinePaymentInvoice::branch()->with(['member', 'subscription' => function($q){
            $q->withTrashed();
        }])->orderBy('id', 'DESC');

        //apply filters
        $orders->when(($from), function ($query) use ($from) {
            $query->whereDate('created_at', '>=', Carbon::parse($from)->format('Y-m-d'));
        })->when(($to), function ($query) use ($to) {
            $query->whereDate('created_at', '<=', Carbon::parse($to)->format('Y-m-d'));
        })->when(($search), function ($query) use ($search) {
            $query->where('id', '=', (int)$search);
            $query->orWhere('name', '=', (int)$search);
            $query->orWhere('phone', '=', (int)$search);
            $query->orWhere('address', '=', (int)$search);
        });

        $records = $orders->limit(300)->get();

        $keys = ['name', 'phone', 'subscription.name', 'amount', 'status', 'created_at'];
        if ($this->lang == 'ar') $keys = array_reverse($keys);

        $this->fileName = 'online-payment-transactions-' . Carbon::now()->toDateTimeString();
        foreach ($records as $key => $record) {
            $records[$key]['name'] = $record['name'];
            $records[$key]['phone'] = $record['phone'];
            $records[$key]['subscription.name'] = $record->subscription->name ?? trans('sw.not_specified');
            $records[$key]['amount'] = $record['amount'];
            $records[$key]['status'] = $record['status'] == 1 ? trans('sw.successful') : trans('sw.declined');
            $records[$key]['created_at'] = $record['created_at'];
        }

        $title = trans('sw.online_transaction_report');
        $customPaper = array(0, 0, 720, 1440);

        // Try mPDF for better Arabic support
        if ($this->lang == 'ar') {
            try {
                $mpdf = new Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4-L',
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
                $this->userLog($notes, TypeConstants::ExportOnlinePaymentPDF);

                return response($mpdf->Output($this->fileName.'.pdf', 'D'), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $this->fileName . '.pdf"'
                ]);

            } catch (\Exception $e) {
                \Log::error('mPDF failed, falling back to DomPDF: ' . $e->getMessage());
            }
        }

        $pdf = PDF::loadView('software::Front.export_pdf', ['records' => $records, 'title' => $title, 'keys' => $keys])
        ->setPaper($customPaper, 'landscape')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'DejaVu Sans',
            'isPhpEnabled' => true,
            'isJavascriptEnabled' => false
        ]);

        $notes = trans('sw.export_pdf_members');
        $this->userLog($notes, TypeConstants::ExportOnlinePaymentPDF);

        return $pdf->download($this->fileName . '.pdf');
    }

    public function reportUserNotificationsList()
    {
        $title = trans('sw.notifications');
        $search_query = request()->query();

        $this->request_array = ['date', 'search'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;

        $logs = GymUserNotification::with(['user']);
        $logs->where('user_id', $this->user_sw->id);

        $logs->when(@$date, function ($query) use ($date) {
            $query->whereDate('created_at', '=', Carbon::parse($date)->toDateString());
        });

        $logs->when(@$search, function ($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('title', 'like', "%" . $search . "%");
                $query->orWhere('body', 'like', "%" . $search . "%");
            });
        });
        $logs->orderBy('id', 'DESC');
        $logs = $logs->paginate($this->limit)->onEachSide(1);
        $total = $logs->total();



        return view('software::Front.report_user_notification_list', compact('logs', 'search_query','title', 'total'));
    }

    public function reportFreezeMemberList(){
        $title = trans('sw.freeze_members_report') ?? 'Freeze Members Report';
        $this->request_array = ['search', 'subscription', 'status', 'from', 'to'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;

        $subscriptions = GymSubscription::branch()->get();
        
        // Get members with any freeze records (all statuses)
        // Default: show all freezes, but can be filtered by status and date range
        $members = GymMember::branch()
            ->with([
                'member_subscription_info' => function($q){
                    $q->with([
                        'subscription',
                        'freezes' => function($q){
                            // Load all freezes for filtering in view, but order by most recent
                            $q->orderBy('id', 'desc');
                        }
                    ]);
                }
            ])
            ->whereHas('member_subscription_info', function($q){
                // Has any freeze records (all statuses)
                $q->whereHas('freezes');
            })
            ->whereNull('deleted_at');

        $members->when($search, function ($query) use ($search){
            $query->where(function($q) use ($search){
                $q->where('id', '=', (int)$search);
                $q->orWhere('code', 'like', "%" . $search . "%");
                $q->orWhere('name', 'like', "%" . $search . "%");
                $q->orWhere('phone', 'like', "%" . $search . "%");
                $q->orWhere('address', 'like', "%" . $search . "%");
            });
        });

        $members->when($subscription, function ($query) use ($subscription) {
            $query->whereHas('member_subscription_info', function($q) use ($subscription){
                $q->where('subscription_id', $subscription);
            });
        });

        $members->when($status, function ($query) use ($status) {
            $query->whereHas('member_subscription_info.freezes', function($q) use ($status){
                // Filter by freeze status - show all freezes with this status
                $q->where('status', $status);
            });
        });

        if($from && $to) {
            $members->whereHas('member_subscription_info.freezes', function($q) use ($from, $to){
                $q->where(function($query) use ($from, $to){
                    $fromDate = Carbon::parse($from)->toDateString();
                    $toDate = Carbon::parse($to)->toDateString();
                    // Freeze overlaps with date range
                    $query->where(function($q) use ($fromDate, $toDate){
                        // Freeze starts within range
                        $q->whereBetween('start_date', [$fromDate, $toDate])
                          // OR freeze ends within range
                          ->orWhereBetween('end_date', [$fromDate, $toDate])
                          // OR freeze completely covers the range
                          ->orWhere(function($subQuery) use ($fromDate, $toDate){
                              $subQuery->where('start_date', '<=', $fromDate)
                                       ->where('end_date', '>=', $toDate);
                          });
                    });
                });
            });
        }

        $members->orderBy('id', 'DESC');
        
        if($this->limit){
            $members = $members->paginate($this->limit)->onEachSide(1);
            $total = $members->total();
        } else {
            $members = $members->get();
            $total = $members->count();
        }

        $search_query = request()->query();

        return view('software::Front.report_freeze_member_front_list', compact('subscriptions', 'search_query','members','title', 'total'));
    }

    function exportFreezeMemberExcel()
    {
        $this->limit = null;
        $this->request_array = ['search', 'subscription', 'status', 'from', 'to'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        
        $members = GymMember::branch()
            ->with([
                'member_subscription_info' => function($q){
                    $q->with([
                        'subscription',
                        'freezes' => function($q){
                            $q->orderBy('id', 'desc');
                        }
                    ]);
                }
            ])
            ->whereHas('member_subscription_info', function($q){
                $q->where(function($query){
                    // Has active/approved freeze records that are currently active (within date range)
                    $query->whereHas('freezes', function($q){
                        $q->where(function($subQuery){
                            // Active/approved freezes within current date range
                            $subQuery->whereIn('status', ['active', 'approved'])
                              ->where('start_date', '<=', Carbon::now()->toDateString())
                              ->where('end_date', '>=', Carbon::now()->toDateString());
                        })
                        // OR pending freezes that haven't started yet
                        ->orWhere(function($subQuery){
                            $subQuery->where('status', 'pending')
                              ->where('start_date', '>', Carbon::now()->toDateString());
                        });
                    });
                });
            })
            ->whereNull('deleted_at');

        if($search) {
            $members->where(function($q) use ($search){
                $q->where('id', '=', (int)$search);
                $q->orWhere('code', 'like', "%" . $search . "%");
                $q->orWhere('name', 'like', "%" . $search . "%");
                $q->orWhere('phone', 'like', "%" . $search . "%");
            });
        }

        if($subscription) {
            $members->whereHas('member_subscription_info', function($q) use ($subscription){
                $q->where('subscription_id', $subscription);
            });
        }

        if($status) {
            $members->whereHas('member_subscription_info.freezes', function($q) use ($status){
                if(in_array($status, ['active', 'approved'])) {
                    // For active/approved, must be within current date range
                    $q->where('status', $status)
                      ->where('start_date', '<=', Carbon::now()->toDateString())
                      ->where('end_date', '>=', Carbon::now()->toDateString());
                } elseif($status == 'pending') {
                    // For pending, must not have started yet
                    $q->where('status', $status)
                      ->where('start_date', '>', Carbon::now()->toDateString());
                } else {
                    // For other statuses (completed, rejected), show all
                    $q->where('status', $status);
                }
            });
        }

        if($from && $to) {
            $members->whereHas('member_subscription_info.freezes', function($q) use ($from, $to){
                $q->where(function($query) use ($from, $to){
                    $fromDate = Carbon::parse($from)->toDateString();
                    $toDate = Carbon::parse($to)->toDateString();
                    // Freeze overlaps with date range
                    $query->where(function($q) use ($fromDate, $toDate){
                        // Freeze starts within range
                        $q->whereBetween('start_date', [$fromDate, $toDate])
                          // OR freeze ends within range
                          ->orWhereBetween('end_date', [$fromDate, $toDate])
                          // OR freeze completely covers the range
                          ->orWhere(function($subQuery) use ($fromDate, $toDate){
                              $subQuery->where('start_date', '<=', $fromDate)
                                       ->where('end_date', '>=', $toDate);
                          });
                    });
                });
            });
        }

        $members = $members->get();

        $records = [];
        foreach($members as $member) {
            $freeze = @$member->member_subscription_info->freezes->first();
            if(!$freeze) continue;
            
            // Calculate days remaining correctly
            $end_date = \Carbon\Carbon::parse($freeze->end_date)->startOfDay();
            $now = \Carbon\Carbon::now()->startOfDay();
            $days_remaining = $end_date->isPast() ? 0 : max(0, (int) $now->diffInDays($end_date, false));
            
            $records[] = [
                'barcode' => $member->code,
                'name' => $member->name,
                'phone' => str_replace('+', '00', $member->phone),
                'membership' => @$member->member_subscription_info->subscription->name ?? '',
                'start_freeze_date' => \Carbon\Carbon::parse($freeze->start_date)->toDateString(),
                'end_freeze_date' => \Carbon\Carbon::parse($freeze->end_date)->toDateString(),
                'freeze_status' => $freeze->status,
                'days_remaining' => $days_remaining,
                'duration_days' => (int) \Carbon\Carbon::parse($freeze->start_date)->diffInDays(\Carbon\Carbon::parse($freeze->end_date)),
                'reason' => $freeze->reason ?? '',
                'admin_note' => $freeze->admin_note ?? '',
                'joining_date' => @$member->member_subscription_info->joining_date ? \Carbon\Carbon::parse($member->member_subscription_info->joining_date)->toDateString() : '',
                'expire_date' => @$member->member_subscription_info->expire_date ? \Carbon\Carbon::parse($member->member_subscription_info->expire_date)->toDateString() : '',
            ];
        }

        $this->fileName = 'freeze-members-' . Carbon::now()->toDateTimeString();
        $notes = trans('sw.export_excel_members');
        $this->userLog($notes, TypeConstants::ExportMemberExcel);

        $keys = ['barcode', 'name', 'phone', 'membership', 'start_freeze_date', 'end_freeze_date', 'freeze_status', 'days_remaining', 'duration_days', 'reason', 'admin_note', 'joining_date', 'expire_date'];
        return Excel::download(new MembersAttendanceExport(['records' => $records, 'keys' => $keys, 'lang' => $this->lang]), $this->fileName . '.xlsx');
    }

    function exportFreezeMemberPDF()
    {
        $this->limit = null;
        $this->request_array = ['search', 'subscription', 'status', 'from', 'to'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        
        $members = GymMember::branch()
            ->with([
                'member_subscription_info' => function($q){
                    $q->with([
                        'subscription',
                        'freezes' => function($q){
                            $q->orderBy('id', 'desc');
                        }
                    ]);
                }
            ])
            ->whereHas('member_subscription_info', function($q){
                $q->where(function($query){
                    // Has active/approved freeze records that are currently active (within date range)
                    $query->whereHas('freezes', function($q){
                        $q->where(function($subQuery){
                            // Active/approved freezes within current date range
                            $subQuery->whereIn('status', ['active', 'approved'])
                              ->where('start_date', '<=', Carbon::now()->toDateString())
                              ->where('end_date', '>=', Carbon::now()->toDateString());
                        })
                        // OR pending freezes that haven't started yet
                        ->orWhere(function($subQuery){
                            $subQuery->where('status', 'pending')
                              ->where('start_date', '>', Carbon::now()->toDateString());
                        });
                    });
                });
            })
            ->whereNull('deleted_at');

        if($search) {
            $members->where(function($q) use ($search){
                $q->where('id', '=', (int)$search);
                $q->orWhere('code', 'like', "%" . $search . "%");
                $q->orWhere('name', 'like', "%" . $search . "%");
                $q->orWhere('phone', 'like', "%" . $search . "%");
            });
        }

        if($subscription) {
            $members->whereHas('member_subscription_info', function($q) use ($subscription){
                $q->where('subscription_id', $subscription);
            });
        }

        if($status) {
            $members->whereHas('member_subscription_info.freezes', function($q) use ($status){
                if(in_array($status, ['active', 'approved'])) {
                    // For active/approved, must be within current date range
                    $q->where('status', $status)
                      ->where('start_date', '<=', Carbon::now()->toDateString())
                      ->where('end_date', '>=', Carbon::now()->toDateString());
                } elseif($status == 'pending') {
                    // For pending, must not have started yet
                    $q->where('status', $status)
                      ->where('start_date', '>', Carbon::now()->toDateString());
                } else {
                    // For other statuses (completed, rejected), show all
                    $q->where('status', $status);
                }
            });
        }

        if($from && $to) {
            $members->whereHas('member_subscription_info.freezes', function($q) use ($from, $to){
                $q->where(function($query) use ($from, $to){
                    $fromDate = Carbon::parse($from)->toDateString();
                    $toDate = Carbon::parse($to)->toDateString();
                    // Freeze overlaps with date range
                    $query->where(function($q) use ($fromDate, $toDate){
                        // Freeze starts within range
                        $q->whereBetween('start_date', [$fromDate, $toDate])
                          // OR freeze ends within range
                          ->orWhereBetween('end_date', [$fromDate, $toDate])
                          // OR freeze completely covers the range
                          ->orWhere(function($subQuery) use ($fromDate, $toDate){
                              $subQuery->where('start_date', '<=', $fromDate)
                                       ->where('end_date', '>=', $toDate);
                          });
                    });
                });
            });
        }

        $members = $members->get();

        $records = [];
        foreach($members as $member) {
            $freeze = @$member->member_subscription_info->freezes->first();
            if(!$freeze) continue;
            
            // Calculate days remaining correctly
            $end_date = \Carbon\Carbon::parse($freeze->end_date)->startOfDay();
            $now = \Carbon\Carbon::now()->startOfDay();
            $days_remaining = $end_date->isPast() ? 0 : max(0, (int) $now->diffInDays($end_date, false));
            
            $records[] = [
                'barcode' => $member->code,
                'name' => $member->name,
                'phone' => str_replace('+', '00', $member->phone),
                'membership' => @$member->member_subscription_info->subscription->name ?? '',
                'start_freeze_date' => \Carbon\Carbon::parse($freeze->start_date)->toDateString(),
                'end_freeze_date' => \Carbon\Carbon::parse($freeze->end_date)->toDateString(),
                'freeze_status' => trans('sw.' . $freeze->status) ?? $freeze->status,
                'days_remaining' => $days_remaining,
                'duration_days' => (int) \Carbon\Carbon::parse($freeze->start_date)->diffInDays(\Carbon\Carbon::parse($freeze->end_date)),
                'reason' => $freeze->reason ?? '',
                'admin_note' => $freeze->admin_note ?? '',
            ];
        }

        $keys = ['barcode', 'name', 'phone', 'membership', 'start_freeze_date', 'end_freeze_date', 'freeze_status', 'days_remaining', 'duration_days', 'reason', 'admin_note'];
        if ($this->lang == 'ar') $keys = array_reverse($keys);

        $this->fileName = 'freeze-members-' . Carbon::now()->toDateTimeString();
        $title = trans('sw.freeze_members_report') ?? 'Freeze Members Report';
        $customPaper = array(0, 0, 720, 1440);
        
        if ($this->lang == 'ar') {
            try {
                $mpdf = new Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4-L',
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
                $this->userLog($notes, TypeConstants::ExportMemberPDF);
                
                return response($mpdf->Output($this->fileName.'.pdf', 'D'), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $this->fileName . '.pdf"'
                ]);
            } catch (\Exception $e) {
                \Log::error('mPDF failed: ' . $e->getMessage());
            }
        }
        
        $pdf = PDF::loadView('software::Front.export_pdf', ['records' => $records, 'title' => $title, 'keys' => $keys])
        ->setPaper($customPaper, 'landscape')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'DejaVu Sans',
            'isPhpEnabled' => true,
            'isJavascriptEnabled' => false
        ]);

        $notes = trans('sw.export_pdf_members');
        $this->userLog($notes, TypeConstants::ExportMemberPDF);

        return $pdf->download($this->fileName . '.pdf');
    }

}


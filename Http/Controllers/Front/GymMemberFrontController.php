<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Generic\Classes\Constants;
use Modules\Generic\Models\Setting;
use App\Modules\Notification\Http\Controllers\Api\FirebaseApiController;
use Modules\Software\Imports\MembersSubscriptionsImport;
use Modules\Software\Classes\LoyaltyService;
use Modules\Software\Classes\SMSFactory;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Classes\WA;
use Modules\Software\Classes\WAUltramsg;
use Modules\Software\Exports\MembersExport;
use Modules\Software\Http\Controllers\Api\GymMemberApiController;
use Modules\Software\Models\GymBlockMember;
use Modules\Software\Models\GymEventNotification;
use Modules\Software\Models\GymGroupDiscount;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymMemberAttendee;
use Modules\Software\Models\GymMemberCredit;
use Modules\Software\Models\GymMemberSubscription;
use Modules\Software\Models\GymMemberSubscriptionFreeze;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Models\GymMoneyBoxType;
use Modules\Software\Models\GymNonMemberTime;
use Modules\Software\Models\GymPaymentType;
use Modules\Software\Models\GymReservation;
use Modules\Software\Models\GymPotentialMember;
use Modules\Software\Models\GymSaleChannel;
use Modules\Software\Models\GymStoreOrder;
use Modules\Software\Models\GymSubscription;
use Modules\Software\Models\GymUser;
use Modules\Software\Models\GymWALog;
use Modules\Billing\Services\SwBillingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
use Chumper\Zipper\Facades\Zipper;
use Illuminate\Container\Container as Application;
use Modules\Software\Http\Requests\GymMemberRequest;
use Modules\Software\Repositories\GymMemberRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use peal\barcodegenerator\BarCode;


class GymMemberFrontController extends GymGenericFrontController
{
    public $MemberRepository;
    private $imageManager;
    public $fileName;
    public $keys;
    public $member_balance;

    public function __construct()
    {
        parent::__construct();
        $this->imageManager = new ImageManager(new Driver());
        $this->limit = 5;
        $this->keys = ['code', 'image', 'name', 'phone', 'address', 'member_subscription'];
        $this->MemberRepository = new GymMemberRepository(new Application);
        $this->MemberRepository = $this->MemberRepository->branch();
    }

    public function showProfile($id)
    {
        $title = trans('sw.member_profile');
        $member =  GymMember::with(['member_subscription_info', 'member_subscriptions' => function ($q) {
           $q->orderBy('id', 'desc');
        },'member_subscriptions.subscription' => function ($q) {
            $q->withTrashed();
        }, 'member_attendees'])
            ->withCount([ 'member_remain_amount_subscriptions AS total_amount_remaining' => function ($query) {
                $query->select(DB::raw("SUM(amount_remaining) as total_amount_remaining"));
            }
            ])
            ->where('id', $id)->first();

        $member_credit_transactions = GymMemberCredit::branch()->where('member_id', $member->id)->orderBy('id', 'desc')->limit(20)->get();

        return view('software::Front.member_front_profile', [
            'member' => $member,
            'member_credit_transactions' => $member_credit_transactions,
            'title' => $title]);
    }

    public function index()
    {

        $title = trans('sw.subscribed_clients');
        $this->request_array = ['search', 'from', 'to', 'subscription' ,'sale_user_id'
            , 'status', 'remaining_status', 'joining_date', 'expire_date', 'fp_status', 'remaining_store_status', 'fp_id'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;

        if (request('trashed')) {
            $members = GymMember::branch()->onlyTrashed()->orderBy('id', 'DESC');
        } else {
            $members = GymMember::branch()->orderBy('id', 'DESC');
        }

//        $members = $members->leftJoin('sw_gym_member_subscription', function($query)
//                    {
//                        $query->on('sw_gym_members.id','=','sw_gym_member_subscription.member_id')
//                            ->leftJoin('sw_gym_subscriptions', function($query2)
//                    {
//                        $query2->on('sw_gym_subscriptions.id','=','sw_gym_member_subscription.subscription_id');
//                        $query2->select('name_ar AS subscription_name');
//                    });
//                        $query->whereRaw('sw_gym_member_subscription.id IN (select MAX(a2.id) from sw_gym_member_subscription as a2 join sw_gym_members as u2 on u2.id = a2.member_id group by u2.id)');
//                    });
        $members->with(['member_subscription_info', 'member_subscription_info.subscription' => function ($q) {
            $q->withTrashed();
        }, 'member_subscription_info_has_active.subscription' => function ($q) {
            $q->withTrashed();
        },'member_remain_amount_subscriptions.subscription' => function ($q) {
            $q->withTrashed();
        }]);
        $members->withCount(['member_subscriptions' => function ($q) {
            $q->where('expire_date', '>=', Carbon::now()->toDateString());
        }]);

        //apply filters
        $members->when(($from), function ($query) use ($from) {
            $query->whereDate('created_at', '>=', Carbon::parse($from)->format('Y-m-d'));
        })->when(($to), function ($query) use ($to) {
            $query->whereDate('created_at', '<=', Carbon::parse($to)->format('Y-m-d'));
        })->when(($sale_user_id), function ($query) use ($sale_user_id) {
            $query->where('sale_user_id', $sale_user_id);
        })->when(((isset($_GET['status'])) && (!is_null($status))), function ($query) use ($status) {
//            $query->where('status', $status);
            $query->whereHas('member_subscription_info', function ($q) use ($status) {
                $q->whereRaw('sw_gym_member_subscription.id IN (select MAX(a2.id) from sw_gym_member_subscription as a2 join sw_gym_members as u2 on u2.id = a2.member_id group by u2.id) and sw_gym_member_subscription.status = '.(int)$status);
                //$q->where('status', (int)$status);
            });
        })->when(($subscription), function ($query) use ($subscription) {
            $query->whereHas('member_subscription_info', function ($q) use ($subscription) {
                    $q->whereRaw('sw_gym_member_subscription.id IN (select MAX(a2.id) from sw_gym_member_subscription as a2 join sw_gym_members as u2 on u2.id = a2.member_id group by u2.id) and  sw_gym_member_subscription.subscription_id = '.$subscription.'');
                    //$q->where('subscription_id', $subscription);
            });
        })->when(($remaining_status), function ($query) use ($remaining_status) {
            $query->whereHas('member_subscription_info', function ($q) use ($remaining_status) {
                //$q->whereRaw('sw_gym_member_subscription.id IN (select MAX(a2.id) from sw_gym_member_subscription as a2 join sw_gym_members as u2 on u2.id = a2.member_id group by u2.id)');
                if ($remaining_status == TypeConstants::AMOUNT_REMAINING_STATUS_TURE)
                    $q->whereRaw('ROUND(amount_remaining, 0) > 0');
                else
                    $q->whereRaw('ROUND(amount_remaining, 0) = 0');
            });
        })->when(((isset($_GET['remaining_store_status'])) && (!is_null($remaining_store_status))), function ($query) use ($remaining_store_status) {
            if($remaining_store_status == 1) {
                $query->where('store_balance', '>', 0);
            }else if($remaining_store_status == 2) {
                $query->where('store_balance', '<', 0);
            }elseif($remaining_store_status == 0) {
                $query->where('store_balance', 0);
            }
        })->when(($fp_status), function ($q) use ($fp_status) {
            if ($fp_status == TypeConstants::AMOUNT_REMAINING_STATUS_TURE)
                $q->whereNotNull('fp_id');
            else
                $q->whereNull('fp_id');
        })->when(($fp_id !== false && $fp_id !== null && $fp_id !== ''), function ($query) use ($fp_id) {
            $query->where('fp_id', 'like', '%' . $fp_id . '%');
        })->when(($joining_date), function ($query) use ($joining_date) {
            $query->whereHas('member_subscription_info', function ($q) use ($joining_date) {
                //$q->whereRaw('sw_gym_member_subscription.id IN (select MAX(a2.id) from sw_gym_member_subscription as a2 join sw_gym_members as u2 on u2.id = a2.member_id group by u2.id)');
                $q->whereDate('joining_date', '=', Carbon::parse($joining_date)->toDateString());
            });
        })->when(($expire_date), function ($query) use ($expire_date) {
            $query->whereHas('member_subscription_info', function ($q) use ($expire_date) {
                //$q->whereRaw('sw_gym_member_subscription.id IN (select MAX(a2.id) from sw_gym_member_subscription as a2 join sw_gym_members as u2 on u2.id = a2.member_id group by u2.id)');
                $q->whereDate('expire_date', '=', Carbon::parse($expire_date)->toDateString());
            });
        })->when($search, function ($query) use ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('id', '=', (int)$search);
                $query->orWhere('code', 'like', "%" . $search . "%");
                $query->orWhere('name', 'like', "%" . $search . "%");
                $query->orWhere('phone', 'like', "%" . $search . "%");
                $query->orWhere('address', 'like', "%" . $search . "%");
                $query->orWhere('national_id', 'like', "%" . $search . "%");
                $query->orWhere('fp_id', 'like', "%" . $search . "%");
            });
//            $query->whereRaw(' json_extract(activities->"$[*].name_ar", "'.$search.'")');
        });
        $search_query = request()->query();

        if ($this->limit) {
            $members = $members->paginate($this->limit)->onEachSide(1);
            $total = $members->total();
//            $members = $members->get();
//            $total = count($members);
//            $members = $this->paginate($members);

            $ids = @$members->pluck('id')->toArray();
            if ($ids)
                $this->updateSubscriptionsStatus($ids);
        } else {
            $members = $members->get();
            $total = $members->count();
        }

        $subscriptions = GymSubscription::branch()->isSystem()->get();
        $users = GymUser::branch()-> get();
        $payment_types = GymPaymentType::get();
        
        // Load upcoming reservations for members
        $memberIds = $members instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator 
            ? $members->pluck('id')->toArray() 
            : $members->pluck('id')->toArray();
        $upcomingReservations = [];
        if (!empty($memberIds)) {
            $upcomingReservations = GymReservation::branch()
                ->where('client_type', 'member')
                ->whereIn('member_id', $memberIds)
                ->whereDate('reservation_date', '>=', \Carbon\Carbon::today()->format('Y-m-d'))
                ->whereNotIn('status', ['cancelled', 'missed'])
                ->with(['activity' => function($q) {
                    $q->withTrashed();
                }])
                ->orderBy('reservation_date', 'asc')
                ->orderBy('start_time', 'asc')
                ->get()
                ->groupBy('member_id');
        }
        
        return view('software::Front.member_front_list', compact('members', 'users', 'title', 'subscriptions', 'total', 'search_query', 'upcomingReservations','payment_types'));
    }

    public function updateSubscriptionsStatus($id = [], $all = false)
    {
        if ($id || ($all == true)) {

            $statues = [TypeConstants::Active, TypeConstants::Coming, TypeConstants::Freeze];
            if((count($id) <= $this->limit))
                array_push($statues, TypeConstants::Expired);

            $subscriptions = GymMemberSubscription::branch()->with(['subscription', 'member'])->when($id, function ($q) use ($id) {
                $q->whereIn('member_id', $id);
            })->whereIn('status', $statues)->get();
            $del_members = [];
            foreach ($subscriptions as $subscription) {
                $checkForMemberVisits = true;
//            if($subscription->workouts > 0 && ($subscription->workouts < $subscription->visits)){
////                $subscription->expire_date = Carbon::now();
//                $checkForMemberVisits = false;
//            }

                $expireDate = Carbon::parse($subscription->expire_date)->toDateString();
                $joiningDate = Carbon::parse($subscription->joining_date)->toDateString();
//            $joiningDate = Carbon::parse($expireDate)->subDays(@$subscription->subscription->period)->toDateString();

                if (($subscription->workouts > 0) && ($subscription->workouts < $subscription->visits)) {
                    $checkForMemberVisits = false;
                }

                if (
                    ($subscription->start_freeze_date) && ($subscription->end_freeze_date) &&
                    (Carbon::parse($subscription->start_freeze_date)->toDateString() <= Carbon::now()->toDateString())
                    &&
                    (Carbon::parse($subscription->end_freeze_date)->toDateString() > Carbon::now()->toDateString())
                ) {

                    $subscription->status = TypeConstants::Freeze;
                } else if ($expireDate < Carbon::now()->toDateString() || (!$checkForMemberVisits)) {
                    // zk delete user from machine
                    if (@$subscription->member->fp_id && (!in_array($subscription->status, [TypeConstants::Expired]))) {
                        $subscription->member->fp_check = TypeConstants::ZK_EXPIRE_MEMBER;
                        $subscription->member->fp_check_count = 0;
                        $subscription->member->save();
                    }

                    $subscription->status = TypeConstants::Expired;
                } else if (($expireDate >= Carbon::now()->toDateString()) && ($joiningDate > Carbon::now()->toDateString())) {
                    $subscription->status = TypeConstants::Coming;
                } else if ($expireDate >= Carbon::now()->toDateString()) {
                    $subscription->status = TypeConstants::Active;
                }
//            $subscription->joining_date = $joiningDate;
                $subscription->save();
            }

            if($id && @$this->mainSettings->active_zk){
                $this->updateFpStatus($id);
            }
        }
    }

    public function updateFpStatus($id = [], $all = false)
    {
        $settings = Setting::get();
        if($settings){
            foreach ($settings as $setting) {
                $members = GymMember::with('member_subscription_info')
                    ->where('branch_setting_id', $setting->id);
                if($id){
                    $members = $members->whereIn('id', $id);
                }else{
                    $members = $members->whereHas('member_subscription_info', function ($q) use ($setting) {
                        $q->where('updated_at', '>=', Carbon::parse(@$setting->fp_last_updated_at));
                    });
                }
                $members = $members->withTrashed()->get();
                foreach ($members as $member) {
                    if (@$member->fp_id && @$member->member_subscription_info && (in_array($member->member_subscription_info->status, [TypeConstants::Expired, TypeConstants::Freeze, TypeConstants::Coming]))) {
                        if((@env('APP_ZK_FINGERPRINT') == false) && (@env('APP_ZK_GATE') == true) && (in_array($member->member_subscription_info->status, [TypeConstants::Freeze, TypeConstants::Coming]))){
                        // make nothing
                        }else {
                            $member->fp_check = TypeConstants::ZK_EXPIRE_MEMBER;
                            $member->fp_check_count = 0;
                            $member->save();
                        }
                    }else{
                        $member->fp_check = TypeConstants::ZK_ACTIVE_MEMBER;
                        $member->fp_check_count = 0;
                        $member->save();
                    }
                }

            }
        }
    }

    public function membersRefresh(){
        $this->updateMoneyBox();
        $this->updateSubscriptionsStatus([], true);
        if(@$this->mainSettings->active_zk)
            $this->updateFpStatus();
        Cache::flush();
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_processed'),
            'type' => 'success'
        ]);
        return  Response::json(['status' => true], 200);
    }

    /**
     * Search members by code or name for Select2 dropdown
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMembersBySearch()
    {
        $search = request()->get('search', '');
        $page = request()->get('page', 1);
        $perPage = 10;

        $query = $this->MemberRepository->branch();

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $members = $query->orderBy('name', 'asc')
                         ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $members->items(),
            'current_page' => $members->currentPage(),
            'last_page' => $members->lastPage(),
            'total' => $members->total(),
        ]);
    }

    private function updateMoneyBox()
    {
        $oneMonthAgo = GymMoneyBox::whereDate('created_at', '<=',Carbon::now()->subMonth()->toDateString())->orderBy('created_at','desc')->first();
        if(@$oneMonthAgo){
            $moneyBox= new GymMoneyBoxFrontController();
            $moneyBox->scriptForRebuildMoneybox($oneMonthAgo->id, $oneMonthAgo->amount);
        }
    }

//    public function paginate($items, $perPage = 2, $page = null, $options = [])
//    {
//        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
//        $items = $items instanceof Collection ? $items : Collection::make($items);
//        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
//    }

    function exportExcel()
    {
        $this->limit = null;
        $records = $this->index()->with(\request()->all());
        $records = $records->members;

        //        $records = $this->MemberRepository->with(['member_subscription_info.subscription'])->get();
        $this->fileName = 'members-' . Carbon::now()->toDateTimeString();

//        $title = trans('sw.subscribed_clients');
//        $records = $this->prepareForExport($records);

        $notes = trans('sw.export_excel_members');
        $this->userLog($notes, TypeConstants::ExportMemberExcel);

        return Excel::download(new MembersExport(['records' => $records, 'keys' => ['barcode', 'name', 'phone', 'address', 'membership', 'workouts', 'number_of_visits', 'amount_remaining', 'store_balance', 'national_id', 'dob'
            , 'joining_date', 'expire_date', 'status', 'created_at'], 'lang' => $this->lang]), $this->fileName . '.xlsx');

//        Excel::create($this->fileName, function($excel) use ($records, $title) {
//            $excel->setTitle($title);
////            $excel->setCreator($title)->setCompany($title);
//            $excel->setDescription(trans('sw.members_data'));
//            $excel->sheet(trans('sw.members_data'), function($sheet) use ($records) {
//                $sheet->setRightToLeft(true);
//                $sheet->fromArray($records, null, 'A1', false, false);
//                $sheet->mergeCells('A1:L1');
//                $sheet->cells('A1:L1', function ($cells) {
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

        $name = [trans('sw.barcode'), trans('sw.name'), trans('sw.phone'), trans('sw.address'),
            trans('sw.membership'), trans('sw.workouts'), trans('sw.number_of_visits'),
            trans('sw.amount_remaining'),trans('sw.store_balance'), trans('sw.joining_date'), trans('sw.expire_date'),
            trans('sw.status'), trans('sw.created_at')];
        $result = array_map(function ($row) {
            return [
                trans('sw.barcode') => $row['code'],
                trans('sw.name') => $row['name'],
                trans('sw.phone') => $row['phone'],
                trans('sw.address') => $row['address'],
                trans('sw.membership') => $row['member_subscription_info']['subscription']['name'],
                trans('sw.workouts') => $row['member_subscription_info']['workouts'],
                trans('sw.number_of_visits') => $row['member_subscription_info']['visits'],
                trans('sw.amount_remaining') => $row['member_subscription_info']['amount_remaining'],
                trans('sw.store_balance') => @$row['store_balance'],
                trans('sw.joining_date') => Carbon::parse($row['member_subscription_info']['joining_date'])->toDateString(),
                trans('sw.expire_date') => Carbon::parse($row['member_subscription_info']['expire_date'])->toDateString(),
                trans('sw.status') => $row['member_subscription_info']['status_name'],
                trans('sw.created_at') => Carbon::parse($row['created_at'])->toDateString(),
            ];
        }, $data->toArray());
        array_unshift($result, $name);
        array_unshift($result, [trans('sw.subscribed_clients')]);
        return $result;
    }

    function exportPDF()
    {
        $this->limit = null;
        $records = $this->index()->with(\request()->all());
        $records = $records->members;

        $keys = ['barcode', 'name', 'phone', 'membership', 'workouts', 'number_of_visits', 'amount_remaining'
            , 'joining_date', 'expire_date', 'status', 'store_balance'];
        if ($this->lang == 'ar') $keys = array_reverse($keys);

        //$records = $this->MemberRepository->with(['member_subscription_info.subscription'])->get()->toArray();
        $this->fileName = 'members-' . Carbon::now()->toDateTimeString();
        foreach ($records as $key => $record) {
            $records[$key]['barcode'] = $record['code'];
            $records[$key]['phone'] = (str_replace('+', '00', $record['phone']));
            $records[$key]['membership'] = $record['member_subscription_info']['subscription']['name'];
            $records[$key]['workouts'] = $record['member_subscription_info']['workouts'];
            $records[$key]['number_of_visits'] = $record['member_subscription_info']['visits'];
            $records[$key]['amount_remaining'] = $record['member_subscription_info']['amount_remaining'];
            $records[$key]['store_balance'] = $record['store_balance'];
            $records[$key]['joining_date'] = Carbon::parse($record['member_subscription_info']['joining_date'])->toDateString();
            $records[$key]['expire_date'] = Carbon::parse($record['member_subscription_info']['expire_date'])->toDateString();
            $records[$key]['status'] = $record['member_subscription_info']['status_name'];

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
                $this->userLog($notes, TypeConstants::ExportMemberPDF);
                
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

        $notes = trans('sw.export_pdf_members');
        $this->userLog($notes, TypeConstants::ExportMemberPDF);

        return $pdf->download($this->fileName . '.pdf');
    }


    public function create()
    {
        $title = trans('sw.member_add');
        $member =  new GymMember();
        $invoice = null;
        if(\request('reservation_id')){
            $member = GymPotentialMember::where('id', \request('reservation_id'))->first();
            if(!@$member){
                $member = new GymMember();
            }
        }
        if ($member instanceof GymMember && $member->id) {
            $invoice = optional($member->billingInvoices()->latest()->first());
        }
        $subscriptions = GymSubscription::branch()->isSystem()->get();
        $channels = GymSaleChannel::branch()->get();
        $users = GymUser::branch()->get();
        $discounts = GymGroupDiscount::branch()->where('is_member', true)->get();
        $maxId = str_pad((GymMember::withTrashed()->max('code') + 1), 14, 0, STR_PAD_LEFT);
        $billingSettings = SwBillingService::getSettings();
        $payment_types = GymPaymentType::branch()->get();
        //        $this->mainSettings->last_barcode_number = $this->mainSettings->last_barcode_number + 1;
//        $maxId = str_pad(($this->mainSettings->last_barcode_number), 14, 0, STR_PAD_LEFT);

        return view('software::Front.member_front_create', [
            'member' => $member,
            'maxId' => $maxId,
            'subscriptions' => $subscriptions,
            'discounts' => $discounts,
            'channels' => $channels,
            'users' => $users,
            'title' => $title,
            'billingSettings' => $billingSettings,
            'invoice' => $invoice,
            'payment_types' => $payment_types,
        ]);
    }


    private function incrementLastBarcodeNumber($qty = 1)
    {
        $this->mainSettings->last_barcode_number = $this->mainSettings->last_barcode_number + $qty;
        $this->mainSettings->save();
        Cache::store('file')->clear();
    }

    protected function createZatcaInvoiceForMoneyBox(?GymMoneyBox $moneyBox): void
    {
        if (!$moneyBox || !config('sw_billing.zatca_enabled') || !config('sw_billing.auto_invoice')) {
            return;
        }

        $settings = SwBillingService::getSettings();
        if (empty($settings['sections']['money_boxes'])) {
            return;
        }

        try {
            SwBillingService::createInvoiceFromMoneyBox($moneyBox);
        } catch (\Exception $e) {
            Log::error('Failed to process ZATCA invoice for member money box', [
                'money_box_id' => $moneyBox->id,
                'member_id' => $moneyBox->member_id,
                'error' => $e->getMessage(),
            ]);
        }
    }


    public function store(GymMemberRequest $request)
    {
        $checkBlockUser = GymBlockMember::branch()->where('phone', $request->phone)->count();
        if ($checkBlockUser)
            return redirect()->back()->withErrors(['phone' => trans('sw.block_member_validate')]);

        $maxId = str_pad((GymMember::withTrashed()->max('code') + 1), 14, 0, STR_PAD_LEFT);
        if(@(int)$request->code)
            $maxId = str_pad(intval(@$request->code), 14, 0, STR_PAD_LEFT);

        $member_inputs = $this->prepare_inputs($request->except(['_token', 'subscription_id', 'amount_paid', 'discount_value', 'payment_type', 'notes']));
        if(@$request->dob){$member_inputs['dob'] = Carbon::parse($request->dob);}
        $member_inputs['user_id'] = Auth::guard('sw')->user()->id;
        $member_inputs['code'] = $maxId;
        if (@env('APP_ZK_GATE') == false) {
            $member_inputs['fp_id'] = (int)$member_inputs['code'];
        }

        $subscription = GymSubscription::branch()->with(['activities' => function ($q) {
            $q->select('id', 'activity_id', 'subscription_id', 'training_times')->with(['activity' => function ($q) {
                $q->select('id', 'name_ar', 'name_en');
            }]);
        }])->find($request->subscription_id);

        if ($subscription) {
            $vat = ($subscription->price - @$request->discount_value) * (@$this->mainSettings->vat_details['vat_percentage'] / 100);
            $vat = round($vat, 2);
            $amount_paid = round(@$request->amount_paid, 2);
            $discount_value = round(@$request->discount_value, 2);
            $subscription_price = round(($subscription->price - @$request->discount_value + $vat), 2);
            $notes = (string)$request->notes;
            if (@$amount_paid > $subscription_price) {
                return redirect(route('sw.createMember'))->withErrors(['amount_paid' => trans('sw.amount_paid_validate_must_less')]);
            }

            $member = null;
            $member_subscription = null;
            $moneyBox = null;
            $sub = [];

            try {
                DB::transaction(function () use (&$member, &$member_subscription, &$moneyBox, &$sub, $member_inputs, $subscription, $amount_paid, $discount_value, $request, $vat, $notes) {
            $member = $this->MemberRepository->create($member_inputs);

            $this->incrementLastBarcodeNumber();

                    $sub = [
                        'subscription_id' => $subscription->id,
                        'member_id' => $member->id,
                        'workouts' => $subscription->workouts,
                        'start_time_day' => @$subscription->start_time_day,
                        'end_time_day' => @$subscription->end_time_day,
                        'workouts_per_day' => @$subscription->workouts_per_day,
                        'number_times_freeze' => $subscription->number_times_freeze,
                        'freeze_limit' => $subscription->freeze_limit,
                        'max_extension_days' => $subscription->max_extension_days,
                        'max_freeze_extension_sum' => $subscription->max_freeze_extension_sum,
                        'joining_date' => $member_inputs['joining_date'] ? Carbon::parse($member_inputs['joining_date']) : Carbon::now(),
                        'expire_date' => $member_inputs['expire_date'] ? Carbon::parse($member_inputs['expire_date']) : Carbon::now()->addDays($subscription->period),
                        'amount_remaining' => (($subscription->price - $amount_paid - @$discount_value) + (($subscription->price - @$discount_value) * ((float)@$this->mainSettings->vat_details['vat_percentage'] / 100))),
                        'amount_paid' => (float)($amount_paid),
                        'discount_value' => (float)$discount_value,
                        'payment_type' => (int)($request->payment_type),
                        'amount_before_discount' => $subscription->price,
                        'vat' => $vat,
                        'vat_percentage' => @$this->mainSettings->vat_details['vat_percentage'],
                        'activities' => @$subscription->activities->toJson(),
                        'time_week' => @json_encode($subscription->time_week),
                        'branch_setting_id' => @$this->user_sw->branch_setting_id,
                        'notes' => @$notes,
                    ];

            $member_subscription = GymMemberSubscription::branch()->insertGetId($sub);

            $amount_box = GymMoneyBox::branch()->latest()->first();
            $amount_after = GymMoneyBoxFrontController::amountAfter(@$amount_box->amount, @$amount_box->amount_before, (int)@$amount_box->operation);

                    $moneyBoxNotes = trans('sw.member_moneybox_add_msg',
                [
                    'subscription' => $subscription->name,
                    'member' => $member->name,
                    'amount_paid' => @(float)$amount_paid,
                    'amount_remaining' => number_format($sub['amount_remaining'], 2),
                ]);
            if ($discount_value)
                        $moneyBoxNotes = $moneyBoxNotes . trans('sw.discount_msg', ['value' => (float)$discount_value]);

            if ($this->mainSettings->vat_details['vat_percentage']) {
                        $moneyBoxNotes = $moneyBoxNotes . ' - ' . trans('sw.vat_added');
            }

            $moneyBox = GymMoneyBox::create([
                'user_id' => Auth::guard('sw')->user()->id
                , 'amount' => @(float)$amount_paid
                , 'vat' => @$vat
                , 'operation' => TypeConstants::Add
                , 'amount_before' => $amount_after
                        , 'notes' => $moneyBoxNotes
                , 'type' => TypeConstants::CreateMember
                , 'member_id' => $member->id
                , 'payment_type' => intval($request->payment_type)
                , 'member_subscription_id' => @$member_subscription
                , 'branch_setting_id' => @$this->user_sw->branch_setting_id
                    ]);
                });
            } catch (\Throwable $e) {
                Log::error('Failed to create member with subscription', [
                    'subscription_id' => $request->subscription_id,
                    'phone' => $request->phone,
                    'error' => $e->getMessage()
                ]);

                return redirect(route('sw.createMember'))->withErrors(['subscription_id']);
            }

            if (!$member || !$member_subscription) {
                Log::warning('Member subscription transaction did not persist', [
                    'subscription_id' => $request->subscription_id,
                    'phone' => $request->phone,
                ]);
                return redirect(route('sw.createMember'))->withErrors(['subscription_id']);
            }

            $notes = trans('sw.member_moneybox_add_msg',
                [
                    'subscription' => $subscription->name,
                    'member' => $member->name,
                    'amount_paid' => @(float)$amount_paid,
                    'amount_remaining' => number_format($sub['amount_remaining'], 2),
                ]);
            if ($discount_value)
                $notes = $notes . trans('sw.discount_msg', ['value' => (float)$discount_value]);

            if ($this->mainSettings->vat_details['vat_percentage']) {
                $notes = $notes . ' - ' . trans('sw.vat_added');
            }

            $this->userLog($notes, TypeConstants::CreateMoneyBoxAdd);

            $this->createZatcaInvoiceForMoneyBox($moneyBox);

            $notes = str_replace(':name', $member_inputs['name'], trans('sw.add_member'));
            $this->userLog($notes, TypeConstants::CreateMember);
            
            // Award loyalty points if member made a payment
            $loyaltyPointsEarned = 0;
            if ($member && $amount_paid > 0 && @$this->mainSettings->active_loyalty) {
                try {
                    $loyaltyService = new LoyaltyService();
                    $transaction = $loyaltyService->earn(
                        $member,
                        $amount_paid,
                        'member_subscription',
                        $member_subscription
                    );
                    
                    if ($transaction) {
                        $loyaltyPointsEarned = $transaction->points;
                        // Log::info('Loyalty points awarded for new member subscription', [
                        //     'member_id' => $member->id,
                        //     'subscription_id' => $member_subscription,
                        //     'amount_paid' => $amount_paid,
                        //     'points_earned' => $transaction->points,
                        // ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to award loyalty points for new member subscription', [
                        'member_id' => $member->id,
                        'subscription_id' => $member_subscription,
                        'amount_paid' => $amount_paid,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if (@$member->email) {
                $qrcodes_folder = base_path('uploads/barcodes/');
                $d = new DNS1D();
                $d->setStorPath($qrcodes_folder);
                $img = $d->getBarcodePNGPath($member->code, TypeConstants::BarcodeType);
                $data = ['name' => $member->name, 'subscription' => $subscription->name, 'joining_date' => Carbon::parse($sub['joining_date'])->toDateString(), 'expire_date' => Carbon::parse($sub['expire_date'])->toDateString(), 'image' => $member->image, 'code' => asset($img), 'barcode' => $member->code];
                // Send member registration email
                try {
                    // Log the email data for debugging
                    Log::info('Member registration email data', [
                        'member_email' => $member->email,
                        'member_name' => $member->name,
                        'subscription' => $subscription->name,
                        'joining_date' => Carbon::parse($sub['joining_date'])->toDateString(),
                        'expire_date' => Carbon::parse($sub['expire_date'])->toDateString(),
                        'barcode' => $member->code
                    ]);
                    
                    // TODO: Implement proper mail template and sending
                    // For now, we'll just log the email instead of sending it
                    // to prevent the application from breaking
                    
                } catch (\Exception $e) {
                    Log::error('Failed to send member registration email', [
                        'member_id' => $member->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $message_notification = GymEventNotification::where('event_code', 'new_member')->first();
            $msg = @$message_notification->message;
            $member_subscription = GymMemberSubscription::with('member')->where('id',@$member_subscription)->first();
            $msg = $this->dynamicMsg($msg, @$member_subscription, @$this->mainSettings);

            if(@$message_notification && @$member->phone && $this->mainSettings->active_sms && @env('SMS_GATEWAY')){
                try {
                    $sms = new SMSFactory(@env('SMS_GATEWAY'));
                    $sms->send(trim($request->phone), $msg);
                    Log::info('SMS sent successfully', [
                        'member_id' => $member->id,
                        'phone' => $request->phone,
                        'message' => $msg
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send SMS', [
                        'member_id' => $member->id,
                        'phone' => $request->phone,
                        'error' => $e->getMessage()
                    ]);
                    // Continue execution without breaking the application
                }
            }
            if (@$message_notification && @$member->phone && $this->mainSettings->active_wa && (@env('WA_GATEWAY') == 'ULTRA')) {
                try {
                    $wa = new WAUltramsg();
                    $wa->sendText(trim($request->phone), $msg);
                    Log::info('WhatsApp message sent successfully', [
                        'member_id' => $member->id,
                        'phone' => $request->phone,
                        'message' => $msg
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send WhatsApp message', [
                        'member_id' => $member->id,
                        'phone' => $request->phone,
                        'error' => $e->getMessage()
                    ]);
                    // Continue execution without breaking the application
                }
            }
            if (@$message_notification && @$member->phone && $this->mainSettings->active_wa && @env('WA_USER_TOKEN')) {
//                $qrcodes_folder = base_path('uploads/barcodes/');
//                $d = new DNS1D();
//                $d->setStorPath($qrcodes_folder);
//                $img = $d->getBarcodePNGPath($member->code, TypeConstants::BarcodeType);
//                $msg = trans('sw.wa_msg_new_member', ['name' => $member->name,'id' => $member->code,'start_date' => Carbon::parse($sub['joining_date'])->toDateString(),
//                    'end_date' => Carbon::parse($sub['expire_date'])->toDateString(),'paid' => @$request->amount_paid,'reminder' => $sub['amount_remaining']]);

//                $wa = new WAUltramsg();
//                $wa->sendImage(trim($member->phone), ($msg), asset($img));
                $member_card_url = @$this->memberCard($member->code);
                // send wa
                $wa = new WA();
                $wa->sendTextImageWithTemplate(trim($request->phone), 'gymmawy_new_subscription',
                    [
                        [
                            "type" => "text",
                            "text" => "*" . $member->name . "*"
                        ],
                        [
                            "type" => "text",
                            "text" => "*" . @$this->mainSettings->name . "*"
                        ],
                        [
                            "type" => "text",
                            "text" => "*" . @$this->mainSettings->phone . "*"
                        ],
                        [
                            "type" => "text",
                            "text" => "*" . @$this->mainSettings->facebook . "*"
                        ]
                    ], $member_card_url);
                // end send wa

            }
            session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);

//            return redirect(route('sw.listMember'));
            return redirect(route('sw.showOrderSubscription', @$member_subscription));
        }


        return redirect(route('sw.createMember'))->withErrors(['subscription_id']);

    }
    public function dynamicMsg($msg = '', $membership = null, $setting = null)
    {
        $dynamic_variables = [
            '#member_name' => @$membership->member->name
            , '#member_code' => @(int)$membership->member->code
            , '#member_phone' => @$membership->member->phone
            , '#membership_start_date' => Carbon::parse($membership->joining_date)->addHours(12)->toDateString()
            , '#membership_expire_date' => Carbon::parse($membership->expire_date)->toDateString()
            , '#membership_amount_paid' => @$membership->amount_paid
            , '#membership_name' => @$membership->subscription->name
            , '#setting_phone' => @$setting->phone
        ];
        foreach ($dynamic_variables as $dynamic_variable_key => $dynamic_variable_val){
            $msg = str_replace($dynamic_variable_key, $dynamic_variable_val, $msg);
        }

        return $msg;
    }
    public function br2nl($input)
    {
        return preg_replace('/<br\s?\/?>/ius', "\n", str_replace("\n", "", str_replace("\r", "", htmlspecialchars_decode($input))));
    }

    public function edit($id)
    {
        $this->updateSubscriptionsStatus([$id]);
        $member = $this->MemberRepository->with(['member_subscription_info.subscription' => function ($q) {
            $q->withTrashed();
        }])->withTrashed()->find($id);

        if (!$member) {
            abort(404);
        }

        $member_subscriptions = GymMemberSubscription::branch()->with(['subscription' => function ($q) {
            $q->withTrashed();
        }])
            ->where('member_id', $member->id)
//            ->whereDate('expire_date', '>=', Carbon::now()->toDateString())
            ->limit(TypeConstants::RENEW_MEMBERSHIPS_MAX_NUM_2)
            ->orderBy('id', 'desc')
            ->get();
        $expired_member_subscriptions_count = $member_subscriptions->where('status', TypeConstants::Expired)->count();

        if ((count($member_subscriptions) >= TypeConstants::RENEW_MEMBERSHIPS_MAX_NUM_2) && ($expired_member_subscriptions_count == TypeConstants::RENEW_MEMBERSHIPS_MAX_NUM_2)) {
            $member_subscription_last[] = $member_subscriptions[0];
            $member_subscriptions = $member_subscription_last;
        }

        $subscriptions = GymSubscription::branch()->get();
        $channels = GymSaleChannel::branch()->get();
        $users = GymUser::branch()->get();
        $discounts = GymGroupDiscount::branch()->where('is_member', true)->get();
        $maxId = GymMember::withTrashed()->max('id');

        $subscriptionPrice = (float) data_get($member, 'member_subscription_info.subscription.price', 0);
        $vatPercentage = (float) data_get($this->mainSettings, 'vat_details.vat_percentage', 0);
        $vat = round($subscriptionPrice * ($vatPercentage / 100), 2);

        $title = trans('sw.member_edit');   
        $payment_types = GymPaymentType::branch()->get();
        return view('software::Front.member_front_edit', ['member' => $member, 'member_subscriptions' => $member_subscriptions, 'subscriptions' => $subscriptions, 'discounts' => $discounts, 'channels' => $channels, 'users' => $users, 'maxId' => $maxId, 'title' => $title, 'vat' => @(float)$vat, 'payment_types' => $payment_types]);
    }

    public function update(GymMemberRequest $request, $id)
    {
        $member = $this->MemberRepository->with('member_subscription_info.subscription')->withTrashed()->find($id);
        $member_inputs = $this->prepare_inputs($request->only(['image', 'code', 'name', 'gender', 'phone', 'address', 'dob', 'national_id', 'fp_id', 'invitations', 'sale_channel_id', 'sale_user_id', 'additional_info']));

        // Check if user intentionally removed the image
        // If no file uploaded, no camera photo, and member currently has an image, then user removed it
        if (!$request->hasFile('image') && empty($request->input('image')) && !empty($member->image)) {
            // Check if this is an intentional removal (Dropify sends empty string when removed)
            // If the request was submitted without any image data, set to null
            $member_inputs['image'] = null;
        } elseif (empty($member_inputs['image'])) {
            // No new image provided, keep existing image
            unset($member_inputs['image']);
        }
//        if(@$request->expire_date &&
//            (@Carbon::parse($request->expire_date)->toDateString() != @Carbon::parse($member->member_subscription_info->expire_date)->toDateString())
//            && @$member->member_subscription_info->subscription->is_expire_changeable){
//            $member->member_subscription_info->expire_date = Carbon::parse($request->expire_date)->toDateString();
//            $member->member_subscription_info->save();
//        }

//        if($request->typeRequest == 'renew_request'){
//            $subscription_id = $request->subscription_id;
//            $subscription = GymSubscription::find($subscription_id);
//            $amount_paid = round(@$request->amount_paid, 2);
//            $discount_value = round(@$request->discount_value,2);
//            $payment_type = @$request->payment_type;
//            $amount_remaining = (($subscription->price - $amount_paid - @$discount_value) + (($subscription->price - @$discount_value) * (@$this->mainSettings->vat_details['vat_percentage'] / 100)));
//            @$request->custom_expire_date ? $custom_expire_date = @$request->custom_expire_date : $custom_expire_date = $member->member_subscription_info->expire_date;
//
//            $vat = (($subscription->price - @$discount_value) * (@$this->mainSettings->vat_details['vat_percentage'] / 100));
//            $vat = round($vat, 2);
//            $subscription_price = $subscription->price - @$discount_value + $vat;
//            $subscription_price = round($subscription_price,2);
//            if($amount_paid > $subscription_price){
//                return redirect()->back()->withErrors(['amount_paid' => trans('sw.amount_paid_validate_must_less')]);
//            }
//            if($member->member_subscription_info->status == TypeConstants::Active){
//                return redirect()->back()->withErrors(['amount_paid' => trans('sw.subscription_already_active')]);
//            }
//            if($member->member_subscription_info->amount_paid > $amount_paid && ($amount_paid < 0)){
//                $price_diff = abs($member->member_subscription_info->amount_paid - $amount_paid);
//                $operation = TypeConstants::Sub;
//            }else{
//                $price_diff = abs($amount_paid - $member->member_subscription_info->amount_paid);
//                $operation = TypeConstants::Add;
//            }
////            if($custom_expire_date) {
//
////                $diffDaysOfNowAndExpire = \Carbon\Carbon::now()->diffInDays($member->member_subscription_info->expire_date );
////                $expire_date = Carbon::parse($custom_expire_date)->addDays($diffDaysOfNowAndExpire  + (int)$member->member_subscription_info->subscription->period)->toDateString();
//
////                $member->member_subscription_info->subscription_id = $subscription->id;
//////                $member->member_subscription_info->workouts = $member->member_subscription_info->workouts + $subscription->workouts;
////                $member->member_subscription_info->workouts = $subscription->workouts;
////                $member->member_subscription_info->visits = 0;
////                $member->member_subscription_info->number_times_freeze = $subscription->number_times_freeze;
////                $member->member_subscription_info->freeze_limit = $subscription->freeze_limit;
////                $member->member_subscription_info->joining_date = Carbon::now()->toDateString() > $custom_expire_date ? Carbon::now()->toDateString() : $custom_expire_date ;
////                $member->member_subscription_info->expire_date = Carbon::parse($expire_date);
////                $member->member_subscription_info->amount_remaining = (($subscription->price - $amount_paid - @$request->discount_value) + $member->member_subscription_info->amount_remaining + ceil($subscription->price * ((int)@$this->mainSettings->vat_details['vat_percentage'] / 100)));
////                $member->member_subscription_info->amount_paid = $amount_paid;
////                $member->member_subscription_info->discount_value = $discount_value;
////                $member->member_subscription_info->status = TypeConstants::Active;
////                $member->member_subscription_info->amount_before_discount = $subscription->price + ceil($subscription->price * ((int)@$this->mainSettings->vat_details['vat_percentage'] / 100));
////                $member->member_subscription_info->save();
//
//            $expire_date = @$request->custom_expire_date ? Carbon::parse(@$request->custom_expire_date)->toDateString() : Carbon::now()->addDays((int)$member->member_subscription_info->subscription->period)->toDateString();
//
//            $renew_subscription = [
//                'member_id' => $member->id,
//                'subscription_id' => $subscription->id,
//                'workouts' => $subscription->workouts,
//                'visits' => 0,
//                'number_times_freeze' => $subscription->number_times_freeze,
//                'freeze_limit' => $subscription->freeze_limit,
////                    'joining_date' => Carbon::now()->toDateString() > $custom_expire_date ? Carbon::now()->toDateString() : $custom_expire_date,
//                'joining_date' => Carbon::now()->toDateString(),
//                'expire_date' => $expire_date,
////                    'amount_remaining' => (($subscription->price - $amount_paid - @$request->discount_value) + $member->member_subscription_info->amount_remaining + ceil($subscription->price * ((int)@$this->mainSettings->vat_details['vat_percentage'] / 100))),
//                'amount_remaining' => $amount_remaining,
//                'amount_paid' => $amount_paid,
//                'discount_value' => $discount_value,
//                'payment_type' => $payment_type,
//                'status' => TypeConstants::Active,
//                'amount_before_discount' => $subscription->price,
//                'updated_at' => Carbon::now()
//            ];
//
//            $member_subscription = GymMemberSubscription::insertGetId($renew_subscription);
//
////            }
//
//            $amount_box = GymMoneyBox::latest()->first();
//            $amount_after = GymMoneyBoxFrontController::amountAfter($amount_box->amount, $amount_box->amount_before, $amount_box->operation);
//
//            $notes = trans('sw.member_moneybox_renew_msg', [
//                'subscription' => $subscription->name,
//                'member' => $member->name,
//                'amount_paid' => @$amount_paid,
//                'amount_remaining' => number_format($amount_remaining, 2),
//            ]);
//            if($discount_value)
//                $notes = $notes . trans('sw.discount_msg', ['value' => $discount_value]);
//
//            if($this->mainSettings->vat_details['vat_percentage']){
//                $notes = $notes.' - '.trans('sw.vat_added');
//            }
//            $moneyBox = GymMoneyBox::create([
//                'user_id' => Auth::guard('sw')->user()->id
//                , 'amount' => @$amount_paid
//                , 'vat' => @$vat
//                , 'operation' => $operation
//                , 'amount_before' => $amount_after
//                , 'notes' => $notes
//                , 'type' => TypeConstants::RenewMember
//                , 'member_id' => $member->id
//                , 'payment_type' => $payment_type
//                , 'member_subscription_id' => @$member_subscription
//
//            ]);
//
//            $this->userLog($notes, TypeConstants::RenewMember);
//
//            if(@$member->email){
//                $qrcodes_folder = base_path('uploads/barcodes/');
//                $d = new DNS1D();
//                $d->setStorPath($qrcodes_folder);
//                $img = $d->getBarcodePNGPath($member->code, TypeConstants::BarcodeType);
//                $data = ['name' => $member->name, 'subscription' =>  $subscription->name,'joining_date' => $custom_expire_date , 'expire_date' => $expire_date, 'image' => $member->image,'code' => asset($img)];
//                @sendMail('software_member_register',$member->email, trans('global.new_register'), $data);
//            }
//
//            if(@$member->phone && $this->mainSettings->active_wa && @env('WA_USER_TOKEN')){
//                $member_card_url = $this->memberCard($member->code);
//                // send wa
//                $wa = new WA();
//                $wa->sendTextImageWithTemplate(trim(@$member->phone), 'gymmawy_renew_membership',
//                    [
//                        [
//                            "type" => "text",
//                            "text" => "*" . $member->name . "*"
//                        ],
//                        [
//                            "type" => "text",
//                            "text" => "*" . @$subscription->name . "*"
//                        ],
//                        [
//                            "type" => "text",
//                            "text" => "*" . @$expire_date . "*"
//                        ],
//                        [
//                            "type" => "text",
//                            "text" => "*" . @$this->mainSettings->name . "*"
//                        ]
//                    ], $member_card_url);
//                // end send wa
//            }
//            // send notify for renew to gymmawy
//            $sendNotify = new GymMemberApiController();
//            $sendNotify->sendOneMemberToGymmawy($member->id, TypeConstants::RenewMember);
//
//
//        }
//
//
//
//        if($request->typeRequest == 'edit_current_request'){
//            $subscription_id = $request->subscription_id;
//            $subscription = GymSubscription::find($subscription_id);
//            $amount_paid = round(@$request->amount_paid, 2);
//            $discount_value = round(@$request->discount_value, 2);
//            $payment_type = @$request->payment_type;
//            $workouts = @$request->workouts;
//
//            $vat = ($subscription->price - $discount_value) * (@$this->mainSettings->vat_details['vat_percentage'] / 100);
//            $vat = round($vat, 2);
//            $subscription_price = $subscription->price - $discount_value + $vat;
//            $subscription_price = round($subscription_price, 2);
//            if($amount_paid > $subscription_price){
//                return redirect()->back()->withErrors(['amount_paid' => trans('sw.amount_paid_validate_must_less')]);
//            }
//
//            if($member->member_subscription_info->amount_paid > $amount_paid){
//                $price_diff = $member->member_subscription_info->amount_paid - $amount_paid;
//                $operation = TypeConstants::Sub;
//            }else{
//                $price_diff = $amount_paid - $member->member_subscription_info->amount_paid;
//                $operation = TypeConstants::Add;
//            }
//            $price_diff = round(@$price_diff, 2);
//            $expire_date = Carbon::parse($request->expire_date);
//            $amount_remaining = ($subscription->price - $discount_value - $amount_paid) + (($subscription->price-$discount_value) * (@$this->mainSettings->vat_details['vat_percentage'] / 100));
//            $amount_remaining = round(@$amount_remaining, 2);
//
//            $member->member_subscription_info->subscription_id = $subscription->id;
//            $member->member_subscription_info->expire_date = $expire_date;
//            $member->member_subscription_info->amount_remaining = $amount_remaining;
//            $member->member_subscription_info->amount_paid = $amount_paid;
//            $member->member_subscription_info->discount_value = $discount_value;
//            $member->member_subscription_info->vat = $vat;
//            $member->member_subscription_info->payment_type = $payment_type;
//            $member->member_subscription_info->workouts = $workouts;
//            $member->member_subscription_info->amount_before_discount = $subscription->price;
//            $member->member_subscription_info->save();
//
//
//            if($price_diff != 0) {
//                $amount_box = GymMoneyBox::latest()->first();
//                $amount_after = GymMoneyBoxFrontController::amountAfter($amount_box->amount, $amount_box->amount_before, $amount_box->operation);
//
//                $notes = trans('sw.member_moneybox_edit_msg', [
//                    'subscription' => $subscription->name,
//                    'member' => $member->name,
//                    'amount_paid' => @$price_diff,
//                    'amount_remaining' => number_format($member->member_subscription_info->amount_remaining, 2) ,
//                ]);
//                if ($request->discount_value)
//                    $notes = $notes . trans('sw.discount_msg', ['value' => $discount_value]);
//
//                if($this->mainSettings->vat_details['vat_percentage']){
//                    $notes = $notes.' - '.trans('sw.vat_added');
//                }
//                $moneyBox = GymMoneyBox::create([
//                    'user_id' => Auth::guard('sw')->user()->id
//                    , 'amount' => @$price_diff
//                    , 'vat' => @$vat
//                    , 'operation' => $operation
//                    , 'amount_before' => $amount_after
//                    , 'notes' => $notes
//                    , 'type' => TypeConstants::EditSubscription
//                    , 'member_id' => $member->id
//                    , 'payment_type' => $payment_type
//                    , 'member_subscription_id' => @$member->member_subscription_info->id
//                ]);
//            }
//        }

        $member->update($member_inputs);

        $notes = str_replace(':name', $member['name'], trans('sw.edit_member'));
        $this->userLog($notes, TypeConstants::EditMember);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);

        return redirect(route('sw.listMember'));
    }

    public function memberSubscriptionEdit(Request $request)
    {

        $subscription_id = $request->subscription_id;
        $member_subscription_id = $request->member_subscription_id;
        $member_subscription = GymMemberSubscription::branch()->with('member')->find($member_subscription_id);
        $subscription = GymSubscription::branch()->withTrashed()->find($subscription_id);
        $amount_paid = round(@$request->amount_paid, 2);
        $discount_value = round(@$request->discount_value, 2);
        $group_discount_id = @(int)$request->group_discount_id;
//            $payment_type = @$request->payment_type;
        $workouts = @$request->workouts;
        $number_times_freeze = @$request->number_times_freeze;
        $freeze_limit = @$request->freeze_limit;
        $max_extension_days = (int)@$request->max_extension_days;
        $max_freeze_extension_sum = (int)@$request->max_freeze_extension_sum;
        $notes = @(string)$request->notes;
        $joining_date = Carbon::parse(@$request->joining_date)->toDateString();
        $expire_date = @$request->expire_date ? Carbon::parse(@$request->expire_date)->toDateString() : Carbon::now()->addDays((int)$subscription->period)->toDateString();

        if ($member_subscription->subscription_id == $subscription_id) {
            $get_subscription_price = $member_subscription->amount_before_discount;
        } else {
            $get_subscription_price = $subscription->price;
        }

        $vat = ($get_subscription_price - $discount_value) * (@$this->mainSettings->vat_details['vat_percentage'] / 100);
        $vat = round($vat, 2);
        $subscription_price = $get_subscription_price - $discount_value + $vat;
        $subscription_price = round($subscription_price, 2);
        if ($amount_paid > $subscription_price) {
            return Response::json(['msg' => trans('sw.amount_paid_validate_must_less'), 'code' => 'amount_paid'], 200);
        }


        $other_subscriptions = GymMemberSubscription::branch()->whereBetween('joining_date', [$joining_date, $expire_date])
            ->orWhereBetween('expire_date', [$joining_date, $expire_date])
            ->get();
        $other_subscriptions = $other_subscriptions->where('member_id', $member_subscription->member_id)
            ->where('id', '!=', $member_subscription_id);
        if ($other_subscriptions && $other_subscriptions->count() > 0) {
            return Response::json(['msg' => trans('sw.error_date_between'), 'code' => 'expire_date'], 200);
        }

        if ($member_subscription->amount_paid > $amount_paid) {
            $price_diff = $member_subscription->amount_paid - $amount_paid;
            $operation = TypeConstants::Sub;
        } else {
            $price_diff = $amount_paid - $member_subscription->amount_paid;
            $operation = TypeConstants::Add;
        }
        $price_diff = round(@$price_diff, 2);
        $amount_remaining = ($get_subscription_price - $discount_value - $amount_paid) + (($get_subscription_price - $discount_value) * (@$this->mainSettings->vat_details['vat_percentage'] / 100));
        $amount_remaining = round(@$amount_remaining, 2);
        
        // Store old amount before updating
        $oldAmountPaid = $member_subscription->amount_paid;

        $member_subscription->subscription_id = $subscription->id;
        $member_subscription->expire_date = $expire_date;
        $member_subscription->joining_date = $joining_date;
        $member_subscription->amount_remaining = $amount_remaining;
        $member_subscription->amount_paid = $amount_paid;
        $member_subscription->discount_value = $discount_value;
        $member_subscription->group_discount_id = $group_discount_id;
        $member_subscription->vat = $vat;
//            $member_subscription->payment_type = $payment_type;
        $member_subscription->workouts = $workouts;
        $member_subscription->freeze_limit = $freeze_limit;
        $member_subscription->number_times_freeze = $number_times_freeze;
        $member_subscription->max_extension_days = $max_extension_days;
        $member_subscription->max_freeze_extension_sum = $max_freeze_extension_sum;
        $member_subscription->amount_before_discount = $get_subscription_price;
        $member_subscription->time_week = $subscription->time_week;
        $member_subscription->notes = $notes;
        $member_subscription->save();

        // Handle loyalty points when amount changes
        if ($price_diff != 0 && $member_subscription->member_id && @$this->mainSettings->active_loyalty) {
            try {
                $member = GymMember::find($member_subscription->member_id);
                if ($member) {
                    $loyaltyService = new LoyaltyService();
                    
                    if ($operation == TypeConstants::Add) {
                        // Amount increased - award additional points
                        $transaction = $loyaltyService->earn(
                            $member,
                            $price_diff,
                            'member_subscription_edit',
                            $member_subscription->id
                        );
                        
                        if ($transaction) {
                            Log::info('Loyalty points awarded for subscription edit (increase)', [
                                'subscription_id' => $member_subscription->id,
                                'member_id' => $member->id,
                                'amount_increase' => $price_diff,
                                'points_earned' => $transaction->points,
                            ]);
                        }
                    } else {
                        // Amount decreased - deduct points proportionally
                        $loyaltyTransactions = \Modules\Software\Models\LoyaltyTransaction::where('member_id', $member->id)
                            ->whereIn('source_type', ['member_subscription', 'member_subscription_renew', 'member_subscription_edit', 'member_subscription_remaining_payment'])
                            ->where('source_id', $member_subscription->id)
                            ->where('type', 'earn')
                            ->where('is_expired', false)
                            ->get();
                        
                        $totalPointsEarned = $loyaltyTransactions->sum('points');
                        
                        if ($totalPointsEarned > 0 && $oldAmountPaid > 0) {
                            // Calculate points to deduct based on the amount reduction
                            $reductionRatio = $price_diff / $oldAmountPaid;
                            $pointsToDeduct = (int) round($totalPointsEarned * $reductionRatio);
                            
                            if ($pointsToDeduct > 0 && $member->loyalty_points_balance >= $pointsToDeduct) {
                                $deductionTransaction = $loyaltyService->addManual(
                                    $member,
                                    -$pointsToDeduct,
                                    trans('sw.points_deducted_for_subscription_amount_reduction', [
                                        'subscription_id' => $member_subscription->id,
                                        'old_amount' => $oldAmountPaid,
                                        'new_amount' => $amount_paid
                                    ]),
                                    $this->user_sw->id ?? null
                                );
                                
                                if ($deductionTransaction) {
                                    $deductionTransaction->source_type = 'member_subscription_edit_reduction';
                                    $deductionTransaction->source_id = $member_subscription->id;
                                    $deductionTransaction->save();
                                }
                                
                                Log::info('Loyalty points deducted for subscription edit (decrease)', [
                                    'subscription_id' => $member_subscription->id,
                                    'member_id' => $member->id,
                                    'amount_decrease' => $price_diff,
                                    'points_deducted' => $pointsToDeduct,
                                ]);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to adjust loyalty points for subscription edit', [
                    'subscription_id' => $member_subscription->id,
                    'member_id' => $member_subscription->member_id ?? null,
                    'amount_difference' => $price_diff,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($price_diff != 0) {
            $amount_box = GymMoneyBox::branch()->latest()->first();
            $amount_after = GymMoneyBoxFrontController::amountAfter($amount_box->amount, $amount_box->amount_before, $amount_box->operation);

            $notes = trans('sw.member_moneybox_edit_msg', [
                'subscription' => $subscription->name,
                'member' => @$member_subscription->member->name,
                'amount_paid' => @$price_diff,
                'amount_remaining' => number_format($amount_remaining, 2),
            ]);
            if ($discount_value)
                $notes = $notes . trans('sw.discount_msg', ['value' => $discount_value]);

            if ($this->mainSettings->vat_details['vat_percentage']) {
                $notes = $notes . ' - ' . trans('sw.vat_added');
            }
            $moneyBoxAdjustment = GymMoneyBox::create([
                'user_id' => Auth::guard('sw')->user()->id
                , 'amount' => @$price_diff
//                , 'vat' => @$vat
                , 'vat' => (@$price_diff * (@$this->mainSettings->vat_details['vat_percentage'] / 100))
                , 'operation' => $operation
                , 'amount_before' => $amount_after
                , 'notes' => $notes
                , 'type' => TypeConstants::EditMember
                , 'member_id' => @$member_subscription->member->id
                //, 'payment_type' => $payment_type
                , 'member_subscription_id' => @$member_subscription->id
                , 'branch_setting_id' => @$this->user_sw->branch_setting_id
            ]);
            $this->createZatcaInvoiceForMoneyBox($moneyBoxAdjustment);
        }

        if ($expire_date >= Carbon::now()->toDateString() && @$member_subscription->member->fp_id && (@$member_subscription->member->fp_check != TypeConstants::ZK_NEW_MEMBER)) {
            $member_subscription->member->fp_check = TypeConstants::ZK_SET_MEMBER;
            $member_subscription->member->fp_check_count = 0;
            $member_subscription->member->save();
        }


        // update status of member
        $this->updateSubscriptionsStatus([$member_subscription->member_id]);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        return Response::json(['status' => true], 200);

    }

    public function destroy($id)
    {
        $member = GymMember::branch()->with(['member_subscription_info'])->withTrashed()->find($id);
        // zk delete member from machine
        if ($member->fp_id) {
            $member->fp_check = TypeConstants::ZK_EXPIRE_MEMBER;
            $member->fp_check_count = 0;
            $member->save();
        }

        if ($member->trashed()) {
            $member->restore();
        } else {
            $member->delete();
            if (\request('refund') && @$member->member_subscription_inf) {
                $amount_box = GymMoneyBox::branch()->latest()->first();
                $amount_after = GymMoneyBoxFrontController::amountAfter($amount_box->amount, $amount_box->amount_before, $amount_box->operation);

                $vat = @$member->member_subscription_info->vat;
                $refundAmount = ($member->member_subscription_info->amount_paid);
                $isPartialRefund = false;
                
                if(\request('total_amount') && \request('amount') && (\request('total_amount') >= \request('amount') )){
                    $refundAmount = \request('amount');
                    $isPartialRefund = ($refundAmount < $member->member_subscription_info->amount_paid);
                }
                
                // Deduct loyalty points if they were awarded for this member's subscription
                if ($member->id && $member->member_subscription_info && @$this->mainSettings->active_loyalty) {
                    try {
                        $subscriptionId = $member->member_subscription_info->id;
                        $originalAmount = $member->member_subscription_info->amount_paid;
                        
                        // Find loyalty transactions for this member's active subscription
                        $loyaltyTransactions = \Modules\Software\Models\LoyaltyTransaction::whereIn('source_type', ['member_subscription', 'member_subscription_renew', 'member_subscription_remaining_payment', 'member_subscription_edit'])
                            ->where('source_id', $subscriptionId)
                            ->where('type', 'earn')
                            ->where('is_expired', false)
                            ->get();
                        
                        $totalPointsEarned = $loyaltyTransactions->sum('points');
                        
                        if ($totalPointsEarned > 0 && $originalAmount > 0) {
                            // Check how many points have already been deducted for this subscription (from previous refunds)
                            $alreadyDeductedPoints = abs(\Modules\Software\Models\LoyaltyTransaction::where('member_id', $member->id)
                                ->where('type', 'manual')
                                ->where('source_type', 'member_subscription_refund')
                                ->where('source_id', $subscriptionId)
                                ->where('points', '<', 0)
                                ->sum('points')) ?? 0;
                            
                            $remainingDeductiblePoints = $totalPointsEarned - $alreadyDeductedPoints;
                            
                            // Calculate proportional points to deduct based on refund ratio
                            $refundRatio = $refundAmount / $originalAmount;
                            $pointsToDeduct = (int) round($totalPointsEarned * $refundRatio);
                            
                            // Don't deduct more than what's remaining
                            if ($pointsToDeduct > $remainingDeductiblePoints) {
                                $pointsToDeduct = max(0, $remainingDeductiblePoints);
                            }
                            
                            if ($pointsToDeduct > 0) {
                                if ($member->loyalty_points_balance >= $pointsToDeduct) {
                                    $loyaltyService = new LoyaltyService();
                                    
                                    $reason = $isPartialRefund 
                                        ? trans('sw.points_deducted_for_partial_refund_subscription', [
                                            'subscription_id' => $subscriptionId, 
                                            'refund_amount' => $refundAmount,
                                            'original_amount' => $originalAmount
                                        ])
                                        : trans('sw.points_deducted_for_subscription_refund', ['subscription_id' => $subscriptionId]);
                                    
                                    $deductionTransaction = $loyaltyService->addManual(
                                        $member,
                                        -$pointsToDeduct,
                                        $reason,
                                        $this->user_sw->id ?? null
                                    );
                                    
                                    if ($deductionTransaction) {
                                        $deductionTransaction->source_type = 'member_subscription_refund';
                                        $deductionTransaction->source_id = $subscriptionId;
                                        $deductionTransaction->save();
                                    }
                                    
                                    // Mark original transactions as expired only for full refunds
                                    if (!$isPartialRefund) {
                                        foreach ($loyaltyTransactions as $earnTransaction) {
                                            $earnTransaction->is_expired = true;
                                            $earnTransaction->save();
                                        }
                                    }
                                    
                                    Log::info('Loyalty points deducted for member deletion refund', [
                                        'member_id' => $member->id,
                                        'subscription_id' => $subscriptionId,
                                        'points_deducted' => $pointsToDeduct,
                                        'total_points_earned' => $totalPointsEarned,
                                        'already_deducted_points' => $alreadyDeductedPoints,
                                        'refund_amount' => $refundAmount,
                                        'original_amount' => $originalAmount,
                                        'is_partial' => $isPartialRefund,
                                    ]);
                                } else {
                                    Log::warning('Cannot deduct loyalty points - insufficient balance', [
                                        'member_id' => $member->id,
                                        'subscription_id' => $subscriptionId,
                                        'points_needed' => $pointsToDeduct,
                                        'current_balance' => $member->loyalty_points_balance,
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
                    } catch (\Exception $e) {
                        Log::error('Failed to deduct loyalty points on member deletion refund', [
                            'member_id' => $member->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                
                $amount = $refundAmount;

                $notes = trans('sw.member_moneybox_delete_msg', ['member' => $member->name, 'subscription' => $member->member_subscription_info->subscription->name, 'amount_paid' => $amount]);
                $moneyBoxAdjustment = GymMoneyBox::create([
                    'user_id' => Auth::guard('sw')->user()->id
                    , 'amount' => $amount
                    , 'vat' => @$vat
                    , 'operation' => TypeConstants::Sub
                    , 'amount_before' => $amount_after
                    , 'notes' => $notes
                    , 'type' => TypeConstants::DeleteMember
                    , 'member_id' => $member->id
                    , 'member_subscription_id' => $member->member_subscription_info->id
                    , 'branch_setting_id' => @$this->user_sw->branch_setting_id
                ]);
                $this->createZatcaInvoiceForMoneyBox($moneyBoxAdjustment);
                $this->userLog($notes, TypeConstants::CreateMoneyBoxWithdraw);
            }


            $notes = str_replace(':name', $member['name'], trans('sw.delete_member'));
            $this->userLog($notes, TypeConstants::DeleteMember);
        }


        // update status of member
        $this->updateSubscriptionsStatus([@$id]);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listMember'));
    }

    public function destroySubscription($id)
    {
        $subscription = GymMemberSubscription::branch()->with(['member', 'subscription'])->withTrashed()->find($id);
        if ($subscription->trashed()) {
            $subscription->restore();
        } else {
            $subscription->delete();
            if (\request('refund')) {
                $amount_box = GymMoneyBox::branch()->latest()->first();
                $amount_after = GymMoneyBoxFrontController::amountAfter($amount_box->amount, $amount_box->amount_before, $amount_box->operation);

                $vat = @$subscription->vat;
                $amount = ($subscription->amount_paid);
                
                // Deduct loyalty points if they were awarded for this subscription
                if ($subscription->member_id && @$this->mainSettings->active_loyalty) {
                    try {
                        $member = GymMember::find($subscription->member_id);
                        if ($member) {
                            // Find loyalty transactions for this subscription
                            $loyaltyTransactions = \Modules\Software\Models\LoyaltyTransaction::whereIn('source_type', ['member_subscription', 'member_subscription_renew', 'member_subscription_remaining_payment'])
                                ->where('source_id', $subscription->id)
                                ->where('type', 'earn')
                                ->where('is_expired', false)
                                ->get();
                            
                            $totalPointsEarned = $loyaltyTransactions->sum('points');
                            
                            if ($totalPointsEarned > 0) {
                                // Full refund - deduct all points
                                if ($member->loyalty_points_balance >= $totalPointsEarned) {
                                    $loyaltyService = new LoyaltyService();
                                    $deductionTransaction = $loyaltyService->addManual(
                                        $member,
                                        -$totalPointsEarned,
                                        trans('sw.points_deducted_for_subscription_refund', ['subscription_id' => $subscription->id]),
                                        $this->user_sw->id ?? null
                                    );
                                    
                                    if ($deductionTransaction) {
                                        $deductionTransaction->source_type = 'member_subscription_refund';
                                        $deductionTransaction->source_id = $subscription->id;
                                        $deductionTransaction->save();
                                    }
                                    
                                    // Mark original transactions as expired
                                    foreach ($loyaltyTransactions as $earnTransaction) {
                                        $earnTransaction->is_expired = true;
                                        $earnTransaction->save();
                                    }
                                    
                                    Log::info('Loyalty points deducted for subscription refund', [
                                        'subscription_id' => $subscription->id,
                                        'member_id' => $member->id,
                                        'points_deducted' => $totalPointsEarned,
                                    ]);
                                } else {
                                    Log::warning('Cannot deduct loyalty points - insufficient balance', [
                                        'subscription_id' => $subscription->id,
                                        'member_id' => $member->id,
                                        'points_needed' => $totalPointsEarned,
                                        'current_balance' => $member->loyalty_points_balance,
                                    ]);
                                    
                                    // Mark transactions as expired anyway
                                    foreach ($loyaltyTransactions as $earnTransaction) {
                                        $earnTransaction->is_expired = true;
                                        $earnTransaction->save();
                                    }
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to deduct loyalty points on subscription refund', [
                            'subscription_id' => $subscription->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                $notes = trans('sw.member_moneybox_delete_msg', ['member' => @$subscription->member->name, 'subscription' => @$subscription->subscription->name, 'amount_paid' => $amount]);
                $moneyBoxAdjustment = GymMoneyBox::create([
                    'user_id' => Auth::guard('sw')->user()->id
                    , 'amount' => $amount
                    , 'vat' => @$vat
                    , 'operation' => TypeConstants::Sub
                    , 'amount_before' => $amount_after
                    , 'notes' => $notes
                    , 'type' => TypeConstants::DeleteSubscription
                    , 'member_id' => @$subscription->member->id
                    , 'member_subscription_id' => $subscription->id
                    , 'branch_setting_id' => @$this->user_sw->branch_setting_id
                ]);
                $this->createZatcaInvoiceForMoneyBox($moneyBoxAdjustment);
                $this->userLog($notes, TypeConstants::CreateMoneyBoxWithdraw);
            }


            $notes = str_replace(':name', @$subscription->member->name, trans('sw.delete_member'));
            $this->userLog($notes, TypeConstants::DeleteSubscription);
        }
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('sw.editMember', $subscription->member->id));
    }

    public function payAmountRemaining()
    {
        $id = request('id');
        $amountPaid = (float)request('amount_paid');
        $payment_type = (int)request('payment_type');
        $memberInfo = GymMemberSubscription::branch()->with(['subscription'=> function ($q) {
            $q->withTrashed();
        }, 'member'])->where('id', $id)->orderBy('id', 'desc')->first();
        if ($memberInfo) {
            $amount_remaining = round($memberInfo->amount_remaining, 2);

            if ($amountPaid == 0) return trans('sw.amount_paid_must_not_zero');
            if ($amount_remaining < $amountPaid) return str_replace(':amount_paid', $amount_remaining, trans('sw.amount_paid_must_less'));

            $memberInfo->amount_remaining = ($amount_remaining - $amountPaid);
            $memberInfo->amount_paid = ($memberInfo->amount_paid + $amountPaid);
            $memberInfo->save();
            
            // Award loyalty points for the payment
            if ($memberInfo->member && $amountPaid > 0 && @$this->mainSettings->active_loyalty) {
                try {
                    $loyaltyService = new LoyaltyService();
                    $transaction = $loyaltyService->earn(
                        $memberInfo->member,
                        $amountPaid,
                        'member_subscription_remaining_payment',
                        $memberInfo->id
                    );
                    
                    if ($transaction) {
                        Log::info('Loyalty points awarded for member subscription remaining payment', [
                            'member_id' => $memberInfo->member->id,
                            'subscription_id' => $memberInfo->id,
                            'amount_paid' => $amountPaid,
                            'points_earned' => $transaction->points,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to award loyalty points for member subscription remaining payment', [
                        'member_id' => $memberInfo->member->id ?? null,
                        'subscription_id' => $memberInfo->id,
                        'amount_paid' => $amountPaid,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $amount_box = GymMoneyBox::branch()->latest()->first();
            $amount_after = GymMoneyBoxFrontController::amountAfter($amount_box->amount, $amount_box->amount_before, $amount_box->operation);

            $notes = trans('sw.member_moneybox_remain_msg', ['subscription' => @$memberInfo->subscription->name, 'member' => $memberInfo->member->name, 'amount_paid' => $amountPaid, 'amount_remaining' => number_format($memberInfo->amount_remaining, 2)]);

            GymMoneyBox::create([
                'user_id' => Auth::guard('sw')->user()->id
                , 'amount' => @abs((float)$amountPaid)
                , 'operation' => $amountPaid > 0 ? TypeConstants::Add : TypeConstants::Sub
                , 'amount_before' => $amount_after
                , 'notes' => $notes
                , 'type' => TypeConstants::CreateMemberPayAmountRemainingForm
                , 'payment_type' => $payment_type
                , 'member_id' => @$memberInfo->member->id
                , 'member_subscription_id' => @$memberInfo->id
                , 'branch_setting_id' => @$this->user_sw->branch_setting_id
            ]);
            $this->userLog($notes, TypeConstants::CreateMoneyBoxAdd);

            return 1;
        }
        return trans('admin.operation_failed');
    }

    public function freezeMember()
    {
        $id = request('id');
        $start_date = request('start_date');
        $end_date = request('end_date');
        $reason = request('reason');
        $admin_note = request('admin_note');

        $this->updateSubscriptionsStatus([$id]);
        $memberInfo = GymMemberSubscription::branch()->with(['member', 'subscription'])->where('status', TypeConstants::Active)->where('member_id', $id)->orderBy('id', 'desc')->first();
        if ($memberInfo && ($memberInfo->number_times_freeze > 0) && ($memberInfo->status == TypeConstants::Active)) {

            // Calculate requested freeze days
            $freeze_start_date = $start_date ? Carbon::parse($start_date) : Carbon::now();
            $freeze_end_date = $end_date ? Carbon::parse($end_date) : Carbon::now()->addDays($memberInfo->freeze_limit);
            $freeze_days = $freeze_start_date->diffInDays($freeze_end_date);

            // Validation 1: Check if requested days exceed freeze_limit (max per freeze)
            if ($freeze_days > $memberInfo->freeze_limit) {
                session()->flash('sweet_flash_message', [
                    'title' => trans('admin.operation_failed'),
                    'message' => trans('sw.freeze_days_exceeded', [
                        'requested_days' => $freeze_days,
                        'freeze_limit' => $memberInfo->freeze_limit
                    ]),
                    'type' => 'error'
                ]);
                return redirect()->back();
            }

            // Validation 2: Check total freeze balance (if max_freeze_extension_sum is set)
            if ($memberInfo->max_freeze_extension_sum > 0) {
                // Calculate total used freeze days from history
                $usedFreezeDays = GymMemberSubscriptionFreeze::where('member_subscription_id', $memberInfo->id)
                    ->whereIn('status', ['completed', 'active', 'approved'])
                    ->get()
                    ->sum(function($freeze) {
                        $start = Carbon::parse($freeze->start_date);
                        $end = Carbon::parse($freeze->end_date);
                        return $start->diffInDays($end);
                    });

                $remainingDays = $memberInfo->max_freeze_extension_sum - $usedFreezeDays;

                if ($freeze_days > $remainingDays) {
                    session()->flash('sweet_flash_message', [
                        'title' => trans('admin.operation_failed'),
                        'message' => trans('sw.freeze_total_exceeded', [
                            'used_days' => $usedFreezeDays,
                            'remaining_days' => $remainingDays,
                            'requested_days' => $freeze_days,
                            'max_days' => $memberInfo->max_freeze_extension_sum
                        ]),
                        'type' => 'error'
                    ]);
                    return redirect()->back();
                }
            }

            // zk delete user from machine
            if ((@env('APP_ZK_FINGERPRINT') == false) && (@env('APP_ZK_GATE') == true) && @$memberInfo->member->fp_id && (!in_array($memberInfo->status, [TypeConstants::Freeze]))) {
                $memberInfo->member->fp_check = TypeConstants::ZK_EXPIRE_MEMBER;
                $memberInfo->member->fp_check_count = 0;
                $memberInfo->member->save();
            }

            $memberInfo->status = TypeConstants::Freeze;
            $memberInfo->number_times_freeze = ($memberInfo->number_times_freeze - 1);
            $memberInfo->start_freeze_date = $freeze_start_date;
            $memberInfo->end_freeze_date = $freeze_end_date;
            $memberInfo->expire_date = Carbon::parse($memberInfo->expire_date)->addDays($freeze_days);
            $memberInfo->save();

            // create freeze record
            GymMemberSubscriptionFreeze::create([
                'member_id' => $memberInfo->member_id,
                'member_subscription_id' => $memberInfo->id,
                'start_date' => $freeze_start_date->toDateString(),
                'end_date' => $freeze_end_date->toDateString(),
                'status' => 'active',
                'freeze_limit' => (int)$memberInfo->freeze_limit,
                'reason' => $reason ? trim($reason) : null,
                'admin_note' => $admin_note ? trim($admin_note) : null,
            ]);

            $notes = str_replace(':name', @$memberInfo->member->name, trans('sw.freeze_member'));
            $notes = str_replace(':membership', @$memberInfo->subscription->name, $notes);
            $this->userLog($notes, TypeConstants::FreezeMember);


            // update status of member
            $this->updateSubscriptionsStatus([@$id]);

            session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_processed'),
            'type' => 'success'
        ]);
            return redirect()->back();
        }

        // If we reach here, member doesn't meet freeze requirements
        $errorMessage = trans('admin.operation_failed');
        if ($memberInfo && $memberInfo->number_times_freeze <= 0) {
            $errorMessage = trans('sw.no_freeze_attempts_remaining');
        }

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.operation_failed'),
            'message' => $errorMessage,
            'type' => 'error'
        ]);
        return redirect()->back();
    }

    public function unfreezeMember(Request $request){
        $membership_id  = $request->id;
        $membership = GymMemberSubscription::with('member')->where('id', $membership_id)->first();

        if($membership){
            // zk active user from machine
            if ((@env('APP_ZK_FINGERPRINT') == false) && (@env('APP_ZK_GATE') == true) && @$membership->member->fp_id && (in_array($membership->status, [TypeConstants::Freeze]))) {
                $membership->member->fp_check = TypeConstants::ZK_ACTIVE_MEMBER;
                $membership->member->fp_check_count = 0;
                $membership->member->save();
            }

            // cal. the days reminder and sub from expire_date
            $end_freeze_date = Carbon::parse($membership->end_freeze_date);
            if($end_freeze_date > Carbon::now()){
                // Calculate unused freeze days (days remaining from now until end_freeze_date)
                $daysDifference = Carbon::now()->diffInDays($end_freeze_date);
                $membership->expire_date = Carbon::parse($membership->expire_date)->subDays($daysDifference);
            }
            $membership->end_freeze_date = Carbon::now();
            $membership->save();
            // complete active freeze record
            $activeFreeze = GymMemberSubscriptionFreeze::where('member_subscription_id', $membership->id)
                ->whereIn('status', ['active','approved'])
                ->orderBy('id','desc')
                ->first();
            if($activeFreeze){
                $activeFreeze->end_date = Carbon::now()->toDateString();
                $activeFreeze->status = 'completed';
                $activeFreeze->save();
            }
            GymMemberAttendee::insert(['member_id' => $membership->member_id, 'user_id' => Auth::guard('sw')->user()->id, 'subscription_id' => @$membership->id, 'branch_setting_id' => @$this->user_sw->branch_setting_id]);


            // update status of member
            $this->updateSubscriptionsStatus([@$membership->member_id]);

            session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_processed'),
            'type' => 'success'
        ]);
            return Response::json(['status' => true], 200);
        }
        return Response::json(['status' => false], 200);
    }
    public function memberAttendees(Request $request)
    {
        $code = preg_replace("/[^0-9]/", "", $request->code);
        $enquiry = intval($request->enquiry);
        $msg = '';

        $member_subscriptions = GymMemberSubscription::branch()->with(['subscription' => function ($q) {
            $q->withTrashed();
        }])
            ->whereHas('member', function ($q) use ($code){
                $q->where('code', $code);
            })
            ->limit(TypeConstants::RENEW_MEMBERSHIPS_MAX_NUM_2)
            ->orderBy('id', 'desc')
            ->get();
            
        $preferredSubscriptionId = null;
        if($member_subscriptions instanceof \Illuminate\Support\Collection){
            // First, try to find an Active subscription
            $preferredSubscription = $member_subscriptions->first(function ($subscription) {
                return $subscription->status == TypeConstants::Active;
            });

            // If no Active subscription, find any non-Coming subscription (Freeze, Expired, etc.)
            if(!$preferredSubscription){
                $preferredSubscription = $member_subscriptions->first(function ($subscription) {
                    return $subscription->status != TypeConstants::Coming;
                });
            }

            // If still no subscription found, fall back to the first one
            if(!$preferredSubscription){
                $preferredSubscription = $member_subscriptions->first();
            }
            $preferredSubscriptionId = optional($preferredSubscription)->id;
        }

        $member = GymMember::with(['member_subscription_info' => function ($query) use ($preferredSubscriptionId) {
            if($preferredSubscriptionId){
                $query->where('id',  $preferredSubscriptionId);
            }
            $query->orderBy('id', 'desc');
        }, 'member_subscription_info.subscription', 'pt_members.pt_subscription', 'gym_reservations' => function ($q) {
            $q->where('date', Carbon::now()->toDateString())->orderBy('time_slot', 'asc');
        }
        ])->withCount(['member_attendees' => function ($q) {
            $q->whereDate('created_at', Carbon::now()->toDateString());
        }, 'member_remain_amount_subscriptions AS total_amount_remaining' => function ($query) {
                $query->select(DB::raw("SUM(amount_remaining) as total_amount_remaining"));
            }
        ])->where('code', $code)
            ->when(@$enquiry && (strlen(intval($code)) >= 5), function ($q) use ($code) {
                $q->orWhere('phone', 'like', '%' . $code . '%');
            });
        if (@!$this->mainSettings->allow_member_in_branches) {
            $member = $member->branch();
        }
        $member = $member->first();
        $status = false;
        $renew_status = true;
        if ($member) {
            if ($member->member_subscription_info) {
                $currentDate = Carbon::now()->toDateString();
                $expireDate = Carbon::parse($member->member_subscription_info->expire_date)->toDateString();

                if ($expireDate < $currentDate) {
                    if(!$enquiry && @$this->mainSettings->member_attendees_expire){
                        $member->member_subscription_info->increment('visits');
                        GymMemberAttendee::insert(['member_id' => $member->id, 'user_id' => Auth::guard('sw')->user()->id, 'subscription_id' => @$member->member_subscription_info->id, 'branch_setting_id' => @$this->user_sw->branch_setting_id]);
                    }

                    $msg = trans('sw.membership_expired_with_date', ['date' => $expireDate]);
                    return Response::json([
                        'msg' => $msg,
                        'member' => $member,
                        'status' => false,
                        'renew_status' => true
                    ], 200);
                }                

                if (($member->member_subscription_info->workouts_per_day > 0) && ($member->member_attendees_count >= $member->member_subscription_info->workouts_per_day)) {
                    $msg = trans('sw.workouts_per_day_msg', ['visits' => $member->member_attendees_count, 'classes' => $member->member_subscription_info->workouts_per_day]);
                    return Response::json(['msg' => $msg, 'member' => $member, 'status' => $status, 'renew_status' => $status], 200);
                }

                if ($member->member_subscription_info->start_time_day && $member->member_subscription_info->end_time_day) {
                    $startTime = Carbon::parse($member->member_subscription_info->start_time_day);
                    $endTime = Carbon::parse($member->member_subscription_info->end_time_day);
                    $nowTime = Carbon::now();

                    if (!$nowTime->between($startTime, $endTime, true)) {
                        return Response::json(['msg' => trans('sw.failed_time'
                            ,[
                                'date_from' => '<span style="font-size: 14px;"> ' . '<i class="fa fa-clock-o text-muted"></i> '.strtolower($startTime->format('h:i A')).' '
                                , 'date_to' => ' ' . '<i class="fa fa-clock-o text-muted"></i> '.strtolower($endTime->format('h:i A')).'</span>'
                            ]), 'member' => $member, 'status' => $status, 'renew_status' => false], 200);
                    }
                }

                if (($member->member_subscription_info->time_week) &&
                    isset($member->member_subscription_info->time_week['work_days']) &&
                    (@!$member->member_subscription_info->time_week['work_days'][Carbon::now()->dayOfWeek]['status'])) {
                    $days = (array_keys($member->member_subscription_info->time_week['work_days']));
                    $days_str = '';
                    foreach ($days as $day){
                        $days_str .= week_name($day, $this->lang) . ', ';
                    }
                    return Response::json(['msg' => trans('sw.failed_time_days', ['days' => trim($days_str, ', ')]), 'member' => $member, 'status' => $status, 'renew_status' => false], 200);
                }

                $checkForMemberVisits = true;
                if (($member->member_subscription_info->workouts > 0) && ($member->member_subscription_info->workouts < $member->member_subscription_info->visits)) {
                    if (!$enquiry && Carbon::parse($member->member_subscription_info->expire_date)->toDateString() > Carbon::now()->toDateString()) {
                        $member->member_subscription_info->expire_date = Carbon::now()->toDateString();
                        $member->member_subscription_info->save();
                    }
                    $checkForMemberVisits = false;
                }

                if ($member->member_subscription_info->status == TypeConstants::Freeze) {
                    $msg = trans('sw.membership_frozen_msg', ['date_from' => Carbon::parse($member->member_subscription_info->start_freeze_date)->toDateString(), 'date_to' => Carbon::parse($member->member_subscription_info->end_freeze_date)->toDateString()]);
                    return Response::json(['msg' => $msg, 'member' => $member, 'status' => $status, 'renew_status' => $renew_status, 'freeze_status' => true], 200);
                }
                /*
                                if((@$member->member_subscription_info->start_time_day && @$member->member_subscription_info->end_time_day) && (Carbon::parse($member->member_subscription_info->start_time_day) != Carbon::parse($member->member_subscription_info->end_time_day)) &&
                                    ((Carbon::parse($member->member_subscription_info->start_time_day)->toTimeString() > Carbon::now()->toTimeString()) || (Carbon::parse($member->member_subscription_info->end_time_day)->toTimeString() < Carbon::now()->toTimeString()))){
                                    $msg = trans('auth.failed_time');
                                }else */
                if((Carbon::parse($member->member_subscription_info->joining_date)->toDateString() > Carbon::now()->toDateString()) && ($checkForMemberVisits)){
                    $msg = trans('sw.membership_not_coming');
                    $status = true;
                }elseif (($expireDate >= $currentDate) && ($checkForMemberVisits)) {
                    if (!$enquiry) {
                        $member->member_subscription_info->increment('visits');
                        $member->member_subscription_info->status = TypeConstants::Active;
                        $member->member_subscription_info->save();
                        GymMemberAttendee::insert(['member_id' => $member->id, 'user_id' => Auth::guard('sw')->user()->id, 'subscription_id' => @$member->member_subscription_info->id, 'branch_setting_id' => @$this->user_sw->branch_setting_id]);

                        $note = str_replace(':name', $member->name, trans('sw.barcode_scan_note'));
                        $this->userLog($note, TypeConstants::ScanMember);
                    }
                    $status = true;
                    $renew_status = false;
                } else {
                    $msg = trans('sw.membership_expired_with_date', ['date' => $expireDate]);
                }
                $attend = GymMemberAttendee::where('member_id' , $member->id)->orderBy('id', 'desc')->first();

                $member->store_balance = @number_format( $member->store_balance,2);
                $member['total_amount_remaining'] = number_format(@$member->total_amount_remaining, 2);
//                $member['member_subscription_info']['expire_date'] = Carbon::parse($member->member_subscription_info->expire_date)->format('d-m-Y');
                $member['member_subscription_info']['last_attend_date'] =  $attend ? Carbon::parse($attend->created_at)->format('Y-m-d h:i A') : ' - ';
                $member['member_subscription_info']['joining_date'] = Carbon::parse($member->member_subscription_info->joining_date)->format('Y-m-d');
                $member['member_subscription_info']['expire_date'] = Carbon::parse($member->member_subscription_info->expire_date)->format('Y-m-d');
                $member['member_subscription_info']['remain_workouts'] = $member->member_subscription_info->workouts - $member->member_subscription_info->visits;
                $member['member_subscription_info']['amount_remaining'] = number_format($member->member_subscription_info->amount_remaining, 2);
                $member['member_subscription_info']['activities'] = @$member->member_subscription_info->activities;
                
                // loyalty_points_formatted is automatically appended by GymMember model
                
                return Response::json(['msg' => $msg, 'member' => $member, 'status' => $status, 'renew_status' => $renew_status], 200);
            } else {
                return Response::json(['member' => $member, 'status' => $status, 'renew_status' => $renew_status], 200);
            }
        }

        $msg = trans('sw.no_code_found');
        return Response::json(['msg' => $msg, 'status' => $status, 'renew_status' => $renew_status], 200);
    }

    public function memberInvitationAttendees(Request $request)
    {
        $code = str_replace('root', '', $request->code);
        $msg = '';
        $member = $this->MemberRepository->where('code', $code)->first();
        $status = false;
        if ($member && ($member->invitations > 0)) {
            $member->decrement('invitations');
            $note = str_replace(':name', $member->name, trans('sw.member_invitation_used'));
            $this->userLog($note, TypeConstants::ScanMember);
            return Response::json(['msg' => $msg, 'member' => $member, 'status' => $status], 200);

        }

        $msg = trans('sw.no_code_found');
        return Response::json(['msg' => $msg, 'status' => $status], 200);
    }

    public function memberSubscriptionRenew(Request $request)
    {
        $membership = GymMemberSubscription::branch()->with('member')->where('id', $request->id)->orderBy('id', 'desc')->withTrashed()->first();
        $subscriptions = GymSubscription::branch()->isSystem()->get();
        $member = GymMember::branch()->where('id', $request->member_id)->first();

        if($membership){
            $membership_id = $membership->subscription_id;
            $subscription = GymSubscription::withTrashed()->where('id', $membership_id)->first();
            if(($subscription && $subscription->deleted_at != null) || ($subscription && $subscription->is_system != 1))
                $subscriptions->push($subscription);
            $member = $membership->member;
            $membership->joining_date = Carbon::parse($membership->joining_date)->toDateString();
            $membership->expire_date = Carbon::parse($membership->expire_date)->toDateString();
            $membership->from_expire_days = Carbon::parse($membership->expire_date)->diffInDays(Carbon::now()->subDay()->toDateString());
        }
        return Response::json(['membership' => $subscriptions, 'member' => @$member, 'member_membership' => @$membership], 200);
    }

    public function memberSubscriptionRenewStore(Request $request)
    {
        $membership = GymMemberSubscription::where('id', $request->id)->first();
        $subscription_id = $request->membership_id;
        $subscription = GymSubscription::branch()->with(['activities' => function ($q) {
            $q->select('id', 'activity_id', 'subscription_id', 'training_times')->with(['activity' => function ($q) {
                $q->select('id', 'name_ar', 'name_en');
            }]);
        }])->find($subscription_id);
        $custom_expire_date = $request->custom_expire_date;
        $custom_start_date = $request->custom_start_date;
        $amount_paid = (float)@$request->amount_paid;
        $discount_value = (float)@$request->discount_value;
        $group_discount_id = (int)@$request->group_discount_id;
        $payment_type = (float)@$request->payment_type;
        $vat = ($subscription->price - $discount_value) * ((float)@$this->mainSettings->vat_details['vat_percentage'] / 100);
        $vat = round(@$vat, 2);
        $vat_percentage = @$this->mainSettings->vat_details['vat_percentage'];
        $amount_remaining = ($subscription->price - $discount_value + $vat - $amount_paid);
        $amount_remaining = round(@$amount_remaining, 2);
        $notes =  (string)@$request->notes;

        if (!$custom_expire_date && ($subscription->is_expire_changeable)) {
            return Response::json(['msg' => trans('sw.error_expire_date'), 'code' => 'custom_expire_date'], 200);
        } else if (($amount_paid < 0) && ($amount_remaining < 0)) {
            return Response::json(['msg' => trans('sw.error_amount_paid'), 'code' => 'amount_paid'], 200);
        }
        $member_id = @$membership ? @$membership->member_id : @$request->member_id;
        $member = $this->MemberRepository->with(['member_subscription_info'])->withTrashed()->find($member_id);
        $expire_date = @$request->custom_expire_date ? Carbon::parse(@$request->custom_expire_date)->toDateString() : Carbon::now()->addDays((int)$subscription->period)->toDateString();

        $other_subscriptions = GymMemberSubscription::branch()->
        where(function ($query) use ($custom_start_date, $expire_date) {
            $query->where('joining_date', '<=', Carbon::parse($custom_start_date))
                ->where('expire_date', '>=', Carbon::parse($expire_date));

        })
            ->orWhereBetween('joining_date', [$custom_start_date, $expire_date])
            ->orWhereBetween('expire_date', [$custom_start_date, $expire_date])
            ->get();
        $other_subscriptions = $other_subscriptions->where('member_id', $member->id);
        if ($other_subscriptions->count() > 0) {
            return Response::json(['msg' => trans('sw.error_date_between'), 'code' => 'custom_expire_date'], 200);
        }

        $renew_subscription = [
            'member_id' => $member->id,
            'subscription_id' => $subscription->id,
            'workouts' => $subscription->workouts,
            'start_time_day' => @$subscription->start_time_day,
            'end_time_day' => @$subscription->end_time_day,
            'workouts_per_day' => @$subscription->workouts_per_day,
            'visits' => 0,
            'vat' => $vat,
            'vat_percentage' => $vat_percentage,
            'number_times_freeze' => $subscription->number_times_freeze,
            'freeze_limit' => $subscription->freeze_limit,
            'joining_date' => $custom_start_date ?? Carbon::now()->toDateString(),
            'expire_date' => $expire_date,
            'amount_remaining' => $amount_remaining,
            'amount_paid' => $amount_paid,
            'discount_value' => $discount_value,
            'group_discount_id' => $group_discount_id,
            'payment_type' => $payment_type,
            'status' => TypeConstants::Active,
            'amount_before_discount' => $subscription->price,
            'activities' => @$subscription->activities->toJson(),
            'time_week' =>  @json_encode($subscription->time_week),
            'updated_at' => Carbon::now(),
            'branch_setting_id' => @$this->user_sw->branch_setting_id,
            'notes' => @$notes
        ];
        $member_subscription = GymMemberSubscription::insertGetId($renew_subscription);


        $amount_box = GymMoneyBox::branch()->latest()->first();
        $amount_after = GymMoneyBoxFrontController::amountAfter($amount_box->amount, $amount_box->amount_before, $amount_box->operation);

        $notes = str_replace(':subscription', $subscription->name, trans('sw.member_moneybox_renew_msg'));
        $notes = str_replace(':member', $member->name, $notes);
        $notes = str_replace(':amount_paid', @$amount_paid, $notes);
        $notes = str_replace(':amount_remaining', $amount_remaining, $notes);

        if ($request->discount_value)
            $notes = $notes . trans('sw.discount_msg', ['value' => $request->discount_value]);

        if ($this->mainSettings->vat_details['vat_percentage']) {
            $notes = $notes . ' - ' . trans('sw.vat_added');
        }

        GymMoneyBox::create([
            'user_id' => Auth::guard('sw')->user()->id
            , 'amount' => @$request->amount_paid
            , 'vat' => @$vat
            , 'operation' => TypeConstants::Add
            , 'amount_before' => $amount_after
            , 'notes' => $notes
            , 'type' => TypeConstants::RenewMember
            , 'member_id' => $member->id
            , 'payment_type' => $payment_type
            , 'member_subscription_id' => @$member_subscription
            , 'branch_setting_id' => @$this->user_sw->branch_setting_id
        ]);

        $this->userLog($notes, TypeConstants::RenewMember);
        
        // Award loyalty points if member made a payment
        $loyaltyPointsEarned = 0;
        if ($member && $amount_paid > 0 && @$this->mainSettings->active_loyalty) {
            try {
                $loyaltyService = new LoyaltyService();
                $transaction = $loyaltyService->earn(
                    $member,
                    $amount_paid,
                    'member_subscription_renew',
                    $member_subscription
                );
                
                if ($transaction) {
                    $loyaltyPointsEarned = $transaction->points;
                    Log::info('Loyalty points awarded for subscription renewal', [
                        'member_id' => $member->id,
                        'subscription_id' => $member_subscription,
                        'amount_paid' => $amount_paid,
                        'points_earned' => $transaction->points,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to award loyalty points for subscription renewal', [
                    'member_id' => $member->id,
                    'subscription_id' => $member_subscription,
                    'amount_paid' => $amount_paid,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // set this member to machine
        if ($member->fp_id) {
            $member->fp_check = TypeConstants::ZK_SET_MEMBER;
            $member->fp_check_count = 0;
            $member->save();
        }

        $message_notification = GymEventNotification::where('event_code', 'renew_member')->first();
        $msg = @$message_notification->message;
        $member_subscription = GymMemberSubscription::with('member')->where('id',@$member_subscription)->first();
        $msg = $this->dynamicMsg($msg, @$member_subscription, @$this->mainSettings);

        if(@$message_notification && @$member->phone && $this->mainSettings->active_sms && @env('SMS_GATEWAY')){
            try {
                $sms = new SMSFactory(@env('SMS_GATEWAY'));
                $sms->send(trim(@$member->phone), @$msg);
                Log::info('SMS sent successfully (second instance)', [
                    'member_id' => $member->id,
                    'phone' => $member->phone,
                    'message' => $msg
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send SMS (second instance)', [
                    'member_id' => $member->id,
                    'phone' => $member->phone,
                    'error' => $e->getMessage()
                ]);
                // Continue execution without breaking the application
            }
        }
        if (@$message_notification && @$member->phone && $this->mainSettings->active_wa && (@env('WA_GATEWAY') == 'ULTRA')) {
            try {
                $wa = new WAUltramsg();
                $wa->sendText(trim($member->phone), @$msg);
                Log::info('WhatsApp message sent successfully (second instance)', [
                    'member_id' => $member->id,
                    'phone' => $member->phone,
                    'message' => $msg
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send WhatsApp message (second instance)', [
                    'member_id' => $member->id,
                    'phone' => $member->phone,
                    'error' => $e->getMessage()
                ]);
                // Continue execution without breaking the application
            }
        }
        if (@$member->phone && $this->mainSettings->active_wa && @env('WA_USER_TOKEN')) {
            $member_card_url = $this->memberCard($member->code);
            // send wa
            $wa = new WA();
            $wa->sendTextImageWithTemplate(trim(@$member->phone), 'gymmawy_renew_membership',
                [
                    [
                        "type" => "text",
                        "text" => "*" . $member->name . "*"
                    ],
                    [
                        "type" => "text",
                        "text" => "*" . @$subscription->name . "*"
                    ],
                    [
                        "type" => "text",
                        "text" => "*" . @$expire_date . "*"
                    ],
                    [
                        "type" => "text",
                        "text" => "*" . @$this->mainSettings->name . "*"
                    ]
                ], $member_card_url);
            // end send wa
        }
        if(@$message_notification &&  $this->mainSettings->active_mobile){
            $notify_data['image'] = @env('APP_WEBSITE') ? @env('APP_WEBSITE') . 'placeholder_black.png' : @$this->mainSettings->logo;
            $notify_data['sound'] = 'default';
            $notify_data['badge'] = '1';
            $notify_data['e'] = 1;
            $notify_data['title'] = @$msg;
            $notify_data['body'] = @$msg;
            if (class_exists(FirebaseApiController::class)) {
                (new FirebaseApiController())->push([$member->id], $notify_data);
            } else {
                Log::warning('FirebaseApiController class missing; skipping push notification.');
            }
        }

        // update status of member
        $this->updateSubscriptionsStatus([$member->id]);

        // send notify for renew to gymmawy
        $sendNotify = new GymMemberApiController();
        $sendNotify->sendOneMemberToGymmawy($member->id, TypeConstants::RenewMember);



        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_processed'),
            'type' => 'success'
        ]);

        return Response::json(['status' => true], 200);
    }

    private function prepare_inputs($inputs)
    {
        $input_file = 'image';
        $uploaded = '';

        $destinationPath = base_path(GymMember::$uploads_path);
        $ThumbnailsDestinationPath = base_path(GymMember::$thumbnails_uploads_path);

        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, $mode = 0777, true, true);
        }
        if (!File::exists($ThumbnailsDestinationPath)) {
            File::makeDirectory($ThumbnailsDestinationPath, $mode = 0777, true, true);
        }

        if (@$this->user_sw->branch_setting_id) {
            $inputs['branch_setting_id'] = @$this->user_sw->branch_setting_id;
        }

        if (is_string(request($input_file)) && (strpos(request($input_file), 'data:image/png;base64') !== false)) {
            $image = request($input_file);  // your base64 encoded
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = rand(0, 20000) . time() . '.' . 'png';
            \File::put($destinationPath . '' . $imageName, base64_decode($image));
            \File::put($ThumbnailsDestinationPath . '' . $imageName, base64_decode($image));

            $inputs[$input_file] = $imageName;
            return $inputs;
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
                $inputs[$input_file] = $uploaded;
            }

        }


//        !$inputs['deleted_at']?$inputs['deleted_at']=null:'';

        return $inputs;
    }

    public function downloadCode()
    {
        $code = \request('code');
        $qrcodes_folder = base_path('uploads/barcodes/');
        if ($code) {
            $d = new DNS1D();
            $d->setStorPath($qrcodes_folder);
            $img = $d->getBarcodePNGPath($code, TypeConstants::BarcodeType, 2, 60, array(0, 0, 0), true);

            return redirect()->to($img);
        }
        return redirect()->back();
    }

    public function downloadMemberBarcode(GymMember $member)
    {
        $value = $member->code;
        if (!$value) {
            return redirect()->back();
        }

        $barcodesFolder = base_path('uploads/member-barcodes/');
        if (!File::exists($barcodesFolder)) {
            File::makeDirectory($barcodesFolder, 0755, true, true);
        }

        $generator = new DNS1D();
        $generator->setStorPath($barcodesFolder);
        $imgPath = $generator->getBarcodePNGPath((string)$value, TypeConstants::BarcodeType, 2, 80, [0, 0, 0], true);

        $fullPath = realpath($imgPath);

        if (!$fullPath || !file_exists($fullPath)) {
            return redirect()->back();
        }

        return Response::download($fullPath, sprintf('member-%s.png', $value));
    }

    public function downloadQRCode()
    {
        $code = \request('code');
        $qrcodes_folder = base_path('uploads/qrcodes/');
        if ($code) {
            $d = new DNS2D();
            $d->setStorPath($qrcodes_folder);
            $img = $d->getBarcodePNGPath($code, TypeConstants::QRCodeType, 10, 10, array(0, 0, 0), true);

            return redirect()->to($img);
        }
        return redirect()->back();
    }

    public function generateBarcode()
    {
        $qty = request('qty');
        $lastBarcodeNumber = Setting::pluck('last_barcode_number')->first();
        if ($qty) {
            $barcodes_folder = base_path('uploads/barcodes/');
            $exports_folder = base_path('exports/');

            if (!File::exists(base_path('exports/barcodes.zip'))) {
                File::makeDirectory($exports_folder, $mode = 0755, true, true);
            }
            File::deleteDirectory($barcodes_folder);
            File::deleteDirectory($exports_folder);
            if (!File::exists($barcodes_folder)) {
                File::makeDirectory($barcodes_folder, $mode = 0755, true, true);
            }
            if ($qty > 50) $qty = 50;
            $maxId = ($this->mainSettings->last_barcode_number + 1);
            for ($i = 0; $i < (int)$qty; $i++) {
                $d = new DNS1D();
                $d->setStorPath($barcodes_folder);
                $d->getBarcodePNGPath(str_pad(($maxId + $i), 14, 0, STR_PAD_LEFT), $this->barcode_type, 2, 60, array(0, 0, 0), true);
            }

//            Zipper::make('exports/barcodes.zip')->add($barcodes_folder)->close();
//            chmod(base_path('exports/barcodes.zip'), 0777);

            $this->incrementLastBarcodeNumber($qty);

//            session()->flash('sweet_flash_message', [
        //     'title' => trans('admin.done'),
        //     'message' => trans('admin.successfully_processed'),
        //     'type' => 'success'
        // ]);
            return redirect()->to(asset('exports/barcodes.zip'));
        }
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.operation_failed'),
            'type' => 'error'
        ]);
        return redirect()->back();

//        sweet_alert()->success('Done', 'Extract module successfully in (exports/) folder');

    }


    public function downloadCard()
    {
        $code = \request('code');
        $member = GymMember::branch()->where('code', $code)->first();
        $qrcodes_folder = base_path('uploads/barcodes/');
        $cards_folder = base_path('uploads/cards/');
        // ensure required folders exist
        if (!File::exists($qrcodes_folder)) {
            File::makeDirectory($qrcodes_folder, 0755, true, true);
        }
        if (!File::exists($cards_folder)) {
            File::makeDirectory($cards_folder, 0755, true, true);
        }
        if ($code) {
            $setting = Setting::branch()->first();
            $d = new DNS1D();
            $d->setStorPath($qrcodes_folder);
            $d->getBarcodePNGPath($code, TypeConstants::BarcodeType, 2, 60, array(0, 0, 0), false);


            // open background card template or create a blank canvas if missing
            $cardTemplatePathPublic = base_path('uploads/card.png');
            $cardTemplatePathRoot = base_path('uploads/card.png');
            if (File::exists($cardTemplatePathPublic)) {
                $img = $this->imageManager->read($cardTemplatePathPublic);
            } elseif (File::exists($cardTemplatePathRoot)) {
                $img = $this->imageManager->read($cardTemplatePathRoot);
            } else {
                $img = $this->imageManager->create(1000, 600)->fill('#ffffff');
            }
            // add logo on image (top-right area)
            $logoPath = base_path(ltrim($setting->logo_thumb, '/'));
            if (File::exists($logoPath)) {
                // Read and resize logo before placing it
                $logo = $this->imageManager->read($logoPath);
                $logo->scale(width: 150); // Resize logo to 120px width, height auto
                $img->place($logo, 'top-right', 120, 40);
            }
            // canvas dimensions
            $canvasWidth = method_exists($img, 'width') ? $img->width() : 1000;
            $canvasHeight = method_exists($img, 'height') ? $img->height() : 600;

            // add barcode on image positioned on the left side
            $barcodePath = $qrcodes_folder . $code . '.png';
            if (File::exists($barcodePath)) {
                $barcodeX = 100; // 100px from left edge
                $barcodeY = 220; // 200px from top edge
                $img->place($barcodePath, 'top-left', $barcodeX, $barcodeY);
            }
            // member code under barcode (left side)
            $img->text(($code), 200, 300, function ($font) {
                $font->file(base_path('resources/assets/new_front/fonts/Janna LT Bold.ttf'));
                $font->size(16);
                $font->color('#000');
                $font->align('center');
                $font->valign('top');
                $font->angle(0);
            });

            $Arabic = new Arabic();
            $name = $Arabic->utf8Glyphs($member->name);

            // add member name on image (right band, below logo)
            $img->text($name, 200, 120, function ($font) {
                $font->file(base_path('resources/assets/new_front/fonts/Janna LT Bold.ttf'));
                $font->size(20);
                $font->color('#000');
                $font->align('center');
                $font->valign('top');
                $font->angle(0);
            });
            // add gym phone on image
            $img->text($setting->phone, ($canvasWidth - 350), 220, function ($font) {
                $font->file(base_path('resources/assets/new_front/fonts/Janna LT Bold.ttf'));
                $font->size(20);
                $font->color('#fff');
            });
            // add gym email on image
            $img->text($setting->support_email, ($canvasWidth - 350), 300, function ($font) {
                $font->file(base_path('resources/assets/new_front/fonts/Janna LT Bold.ttf'));
                $font->size(20);
                $font->color('#fff');
            });
            $img->save($cards_folder . 'card-' . $code . '.jpg');
        }
        return \response()->download($cards_folder . 'card-' . $code . '.jpg');
    }

    public function memberCard($code)
    {
        $member = GymMember::branch()->where('code', $code)->first();
        $qrcodes_folder = base_path('uploads/barcodes/');
        $cards_folder = base_path('uploads/cards/');
        // ensure required folders exist
        if (!File::exists($qrcodes_folder)) {
            File::makeDirectory($qrcodes_folder, 0755, true, true);
        }
        if (!File::exists($cards_folder)) {
            File::makeDirectory($cards_folder, 0755, true, true);
        }
        if ($code) {
            $setting = Setting::branch()->first();
            $d = new DNS1D();
            $d->setStorPath($qrcodes_folder);
            $d->getBarcodePNGPath($code, TypeConstants::BarcodeType);


            // open background card template or create a blank canvas if missing
            $cardTemplatePathPublic = base_path('uploads/card.png');
            $cardTemplatePathRoot = base_path('uploads/card.png');
            if (File::exists($cardTemplatePathPublic)) {
                $img = $this->imageManager->read($cardTemplatePathPublic);
            } elseif (File::exists($cardTemplatePathRoot)) {
                $img = $this->imageManager->read($cardTemplatePathRoot);
            } else {
                $img = $this->imageManager->create(1000, 600)->fill('#ffffff');
            }
//            $logo = Image::make('uploads/settings/1031636645638.jpg');

            // add logo on image
//            $img->insert($logo, 'top-left', 30, 40);
            $img->place(base_path($setting->logo_thumb), 'top-left', 30, 40);
            // add barcode on image
            $img->place(base_path('uploads/barcodes/' . $code . '.png'), 'bottom-left', 100, 200);
            $img->text(($code), 200, 320, function ($font) {
                $font->file(base_path('./resources/assets/new_front/fonts/Janna LT Bold.ttf'));
                $font->size(16);
                $font->color('#000');
                $font->align('center');
                $font->valign('top');
                $font->angle(0);
            });

            $Arabic = new Arabic();
            $name = $Arabic->utf8Glyphs($member->name);

            // add member name on image
            $img->text($name, 650, 105, function ($font) {
                $font->file(base_path('./resources/assets/new_front/fonts/Janna LT Bold.ttf'));
                $font->size(20);
                $font->color('#fff');
                $font->align('center');
                $font->valign('top');
                $font->angle(0);
            });
            // add gym phone on image
            $img->text($setting->phone, 500, 220, function ($font) {
                $font->file(base_path('./resources/assets/new_front/fonts/Janna LT Bold.ttf'));
                $font->size(20);
                $font->color('#fff');
            });
            // add gym email on image
            $img->text($setting->support_email, 500, 300, function ($font) {
                $font->file(base_path('./resources/assets/new_front/fonts/Janna LT Bold.ttf'));
                $font->size(20);
                $font->color('#fff');
            });
            $img->save($cards_folder . 'card-' . $code . '.jpg');
        }
        return asset('uploads/cards/card-' . $code . '.jpg');
    }

    /**
     * Show the Excel upload form
     *
     * @return \Illuminate\View\View
     */
    public function uploadExcel()
    {
        $title = trans('sw.members_excel_add');

        return view('software::Front.upload_excel', [
            'title' => $title
        ]);
    }

    /**
     * Process the uploaded Excel file and import members with subscriptions
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function uploadExcelStore(Request $request)
    {
        // Validate the uploaded file
        $validator = Validator::make($request->all(), [
            'excel_data' => 'required|file|mimes:xlsx,xls|max:5120', // Max 5MB
        ], [
            'excel_data.required' => trans('sw.excel_file_required'),
            'excel_data.mimes' => trans('sw.excel_file_must_be_xlsx_or_xls'),
            'excel_data.max' => trans('sw.excel_file_max_size_5mb'),
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()->all()
                ], 422);
            }

            return redirect(route('sw.uploadExcel'))
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Create import instance
            $import = new MembersSubscriptionsImport();

            // Import the Excel file
            Excel::import($import, $request->file('excel_data'));

            // Get import statistics
            $stats = $import->getStats();

            // Prepare response message
            $message = trans('sw.import_completed') . ': ' .
                       $stats['successful_rows'] . ' ' . trans('sw.successful') . ', ' .
                       $stats['failed_rows'] . ' ' . trans('sw.failed');

            // Return JSON response for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => $stats
                ]);
            }

            // Return redirect response for traditional form submissions
            if ($stats['failed_rows'] > 0) {
                return redirect(route('sw.uploadExcel'))
                    ->with([
                        'warning' => $message,
                        'import_stats' => $stats
                    ]);
            }

            return redirect(route('sw.uploadExcel'))
                ->with([
                    'success' => $message,
                    'import_stats' => $stats
                ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];

            foreach ($failures as $failure) {
                $errors[] = [
                    'row_number' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'error_message' => implode(', ', $failure->errors())
                ];
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => trans('sw.validation_errors_in_excel'),
                    'data' => [
                        'total_rows' => 0,
                        'successful_rows' => 0,
                        'failed_rows' => count($errors),
                        'errors' => $errors
                    ]
                ], 422);
            }

            return redirect(route('sw.uploadExcel'))
                ->with([
                    'error' => trans('sw.validation_errors_in_excel'),
                    'import_stats' => [
                        'total_rows' => 0,
                        'successful_rows' => 0,
                        'failed_rows' => count($errors),
                        'errors' => $errors
                    ]
                ]);

        } catch (\Exception $e) {
            Log::error('Excel Import Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            $errorMessage = config('app.debug')
                ? $e->getMessage()
                : trans('sw.error_in_excel');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'data' => [
                        'total_rows' => 0,
                        'successful_rows' => 0,
                        'failed_rows' => 0,
                        'errors' => []
                    ]
                ], 500);
            }

            return redirect(route('sw.uploadExcel'))
                ->with('error', $errorMessage);
        }
    }

    public function fingerprintRefresh()
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get( env('APP_ZK_LOCAL_HOST'),  ['verify' => false]);
            $statusCode = $response->getStatusCode();
            $content = $response->getBody();
            $result = json_decode($content, true);
            if($result['status'] == true){
                $this->mainSettings->zk_online_at =  Carbon::now()->toDateTimeString();
            }else{
                $this->mainSettings->zk_online_at =  null;
            }
            $this->mainSettings->save();
            Cache::store('file')->clear();
            return '1';
        } catch (\Exception $e) {
            $this->mainSettings->zk_online_at =  null;
            $this->mainSettings->save();
            Cache::store('file')->clear();
            return '0';
        }
    }

    public function memberActivityMembershipAttendees(Request $request)
    {
        $id = $request->id;
        $subscription_id = $request->subscription_id;
        $membership = GymMemberSubscription::where('id', $subscription_id);
        if (@!$this->mainSettings->allow_member_in_branches) {
            $membership = $membership->branch();
        }
        $membership = $membership->first();
        if ($membership && (count($membership->activities) > 0)) {
            $visits = 0;
            $training_times = 0;
            $activity_name = '';
            $activity_result = [];
            foreach ($membership->activities as $i => $activity) {
                $activity_result[$i] = $activity;

                if (($activity['id'] == $id) && ($activity['training_times'] > @(int)$activity['visits'])) {
                    $activity_name = $activity['activity']['name_' . $this->lang];
                    $training_times = $activity['training_times'];
                    $visits = @(int)$activity['visits'] + 1;

                    $activity_result[$i]['visits'] = $visits;

                    GymNonMemberTime::create(['user_id' => $this->user_sw->id, 'member_id' => $membership->member_id, 'member_subscription_id' => @$membership->id, 'activity_id' => $activity['id'], 'date' => Carbon::now()->toDateTimeString(),  'attended_at' => Carbon::now()->toDateTimeString(), 'branch_setting_id' => @$this->user_sw->branch_setting_id]);

                }
            }
            if (is_array($activity_result) && count($activity_result) > 0) {
                // Use direct database update to avoid member_id constraint issues
                DB::table('sw_gym_member_subscription')
                    ->where('id', $membership->id)
                    ->update([
                        'activities' => json_encode($activity_result),
                        'branch_setting_id' => @$this->user_sw->branch_setting_id,
                        'updated_at' => now()
                    ]);
                
                return $activity_name . ' (' . $visits . ' / ' . $training_times . ') ';
            }
        }
        return '';
    }
    public function creditMemberBalance()
    {
        $member_id = \request('member_id');
        $member_credits = GymMemberCredit::where('member_id', $member_id)->get();
        $this->member_balance = 0;
        $member_credits->filter(function ($item) {
            if($item->operation != 0)
                return $this->member_balance -= $item->amount;
            else
                return $this->member_balance += $item->amount;
        });
        return number_format($this->member_balance, 2);

    }
    public function creditMemberBalanceAdd(Request $request, $member_id)
    {
        $member_id = @$member_id;
        $amount = @$request->amount_add;
        $payment_type = @($request->payment_type);
        $type = @intval($request->type);
        $note_message = trans('sw.add_amount_to_balance', ['amount' => ':amount', 'member_name' => ':member_name']);
        $operation_type = TypeConstants::Add;
        $credit_amount = TypeConstants::AddCreditAmount;
        $moneybox_type = TypeConstants::CreateMoneyBoxAdd;
        $notes2 = trans('sw.add_credit_member', ['name' => ':name']);
        if($type == 1) {
            $amount = @$request->amount_refund;
            $note_message = trans('sw.refund_amount_from_balance', ['amount' => ':amount', 'member_name' => ':member_name']);
            $operation_type = TypeConstants::Sub;
            $credit_amount = TypeConstants::RefundCreditAmount;
            $moneybox_type = TypeConstants::CreateMoneyBoxWithdraw;
            $notes2 = trans('sw.refund_credit_member', ['name' => ':name']);
        }
        if($member_id && $amount){
            $this->member_balance = 0;
            $member = GymMember::select('id', 'name')->where('id', $member_id)->first();
            $member_name = $member->name;
            $member_credits = GymMemberCredit::where('member_id', $member_id)->get();
            $member_credits->filter(function ($item) {
                if($item->operation != 0)
                    return $this->member_balance -= $item->amount;
                else
                    return $this->member_balance += $item->amount;
            });

            if(($type != 0) && ($this->member_balance < $amount))
                return 'no_balance';

            $member_credit = GymMemberCredit::create(['member_id' => $member_id, 'user_id' => Auth::guard('sw')->user()->id, 'amount' => $amount,'operation' => $type]);

            // ACCOUNTING RULE: Create Money Box entry for cash received (wallet top-up or debt payment)
            // These are cash flow entries, NOT sales/revenue
            // The Money Box type distinguishes:
            // - WalletTopUp: Advance payment when balance >= 0 (liability to customer)
            // - DebtPayment: Debt settlement when balance < 0 (clearing accounts receivable)
            if ($type == 0) { // Only for additions (type 0), not refunds (type 1)
                $amount_box = GymMoneyBox::branch()->latest()->first();
                $amount_after = GymMoneyBoxFrontController::amountAfter(@$amount_box->amount, @$amount_box->amount_before, (int)@$amount_box->operation);

                $notes = str_replace(':amount', $amount, $note_message);
                $notes = str_replace(':member_name', $member_name, $notes);

                // Determine if this is a wallet top-up or debt payment
                // If member had negative balance before, this is paying off debt
                // If member had zero or positive balance, this is topping up wallet
                $moneybox_entry_type = ($this->member_balance < 0) ? TypeConstants::DebtPayment : TypeConstants::WalletTopUp;

                $moneyBox = GymMoneyBox::create([
                    'user_id' => Auth::guard('sw')->user()->id
                    , 'amount' => @(float)$amount
                    , 'operation' => $operation_type
                    , 'amount_before' => $amount_after
                    , 'notes' => $notes
                    , 'type' => $moneybox_entry_type  // WalletTopUp or DebtPayment (NOT sales revenue)
                    , 'member_id' => $member_id
                    , 'payment_type' => $payment_type
                    , 'branch_setting_id' => @$this->user_sw->branch_setting_id
                ]);

                // DEBT PAYMENT LOGIC: Link payment to unpaid store orders
                // If member had debt (negative balance), apply this payment to their unpaid invoices
                if ($this->member_balance < 0) {
                    $remainingPayment = (float)$amount;

                    // Find unpaid/partial store orders for this member (oldest first - FIFO)
                    $unpaidOrders = GymStoreOrder::where('member_id', $member_id)
                        ->whereIn('payment_status', ['unpaid', 'partial'])
                        ->where('amount_remaining', '>', 0)
                        ->orderBy('created_at', 'asc')
                        ->get();

                    foreach ($unpaidOrders as $order) {
                        if ($remainingPayment <= 0) break;

                        $amountToApply = min($remainingPayment, $order->amount_remaining);

                        // Update order payment fields
                        $newAmountPaid = $order->amount_paid + $amountToApply;
                        $newAmountRemaining = $order->amount_remaining - $amountToApply;

                        // Determine new payment status
                        $newPaymentStatus = 'paid';
                        if ($newAmountRemaining > 0) {
                            $newPaymentStatus = $newAmountPaid > 0 ? 'partial' : 'unpaid';
                        }

                        $order->update([
                            'amount_paid' => $newAmountPaid,
                            'amount_remaining' => $newAmountRemaining,
                            'payment_status' => $newPaymentStatus
                        ]);

                        $remainingPayment -= $amountToApply;
                    }
                }
            }

            $notes2 = str_replace(':name', $member_name, $notes2);
            $this->userLog($notes2, $credit_amount);

            $member_balance_value = $type == 0 ? ($this->member_balance + $amount) : ($this->member_balance - $amount);
            $member->store_balance = $member_balance_value;
            $member->save();

            if(($member_balance_value >= 0) && ($type != 1)){
                GymMoneyBox::where('is_store_balance', 2)->where('member_id', $member->id)->update(['is_store_balance' => 1, 'payment_type' => $payment_type]);
            }
        }

        $member_balance_value = $type == 0 ? ($this->member_balance + $amount) : ($this->member_balance - $amount);
        return number_format($member_balance_value, 2);

    }
}


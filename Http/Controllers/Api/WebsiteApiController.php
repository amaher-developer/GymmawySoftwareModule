<?php

namespace Modules\Software\Http\Controllers\Api;

use Modules\Generic\Classes\Constants;
use Modules\Generic\Http\Controllers\Api\GenericApiController;
use Modules\Generic\Models\Setting;
use Modules\Generic\Repositories\SettingRepository;
use App\Modules\Notification\Http\Controllers\Api\FirebaseApiController;
use App\Modules\Notification\Models\Push_tokens;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Http\Controllers\Front\GymMoneyBoxFrontController;
use Modules\Software\Http\Resources\ActivityResource;
use Modules\Software\Http\Resources\MemberResource;
use Modules\Software\Http\Resources\SubscriptionResource;
use Modules\Software\Models\GymActivity;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymMemberSubscription;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Models\GymSubscription;
use Modules\Software\Models\GymUserLog;
use Modules\Software\Models\GymUserNotification;
use Carbon\Carbon;
use Illuminate\Container\Container as Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use Salla\ZATCA\GenerateQrCode;
use Salla\ZATCA\Tags\InvoiceDate;
use Salla\ZATCA\Tags\InvoiceTaxAmount;
use Salla\ZATCA\Tags\InvoiceTotalAmount;
use Salla\ZATCA\Tags\Seller;
use Salla\ZATCA\Tags\TaxNumber;
use function foo\func;

class WebsiteApiController extends GenericApiController
{

    private $SettingRepository;
    private $member_id;
    public function __construct()
    {
        parent::__construct();

        $this->SettingRepository=new SettingRepository(new Application);
    }
    public function settings(){

        app()->setLocale(request('lang'));

        $settings = $this->SettingRepository
            ->select(
                'name_en', 'name_ar', 'phone', 'meta_description_en', 'meta_keywords_en', 'meta_description_ar', 'meta_keywords_ar', 'logo_en', 'logo_ar', 'logo_white_en', 'logo_white_ar'
                ,'latitude', 'longitude', 'support_email', 'about_en', 'about_ar', 'terms_en', 'terms_ar'
                ,'address_en', 'address_ar', 'under_maintenance', 'images', 'cover_images', 'social_media'
            )
            ->first();
        
        if($settings && $settings->images && is_array($settings->images) && count($settings->images) > 0){
            $settings->images = array_map(function ($image) {
                return asset(Setting::$uploads_path.'gyms/'.$image);
            }, $settings->images);
        }
        if($settings && $settings->cover_images && is_array($settings->cover_images) && count($settings->cover_images) > 0){
            $settings->cover_images = array_map(function ($image) {
                return asset(Setting::$uploads_path.'gyms/'.$image);
            }, $settings->cover_images);
        }

        if(!$settings){
            $this->return['settings'] = [];
            $this->return['settings']['activities'] = [];
            $this->return['settings']['subscription'] = [];
            return $this->return;
        }

        $activities = GymActivity::where('is_web', 1)->select('id', 'name_ar', 'name_en')->get();
        $subscription = GymSubscription::where('is_web', 1)->select('id', 'name_ar', 'name_en', 'price', 'period', 'workouts')->get();

        $this->return['settings'] = $settings;
        if(!@$settings->meta_keywords){
            $keywords = implode(', ', @$activities->pluck('name')->toArray()). ', '.implode(', ', @$subscription->pluck('name')->toArray());
            $this->return['settings']['meta_keyword'] = $keywords;
        }else{
            $this->return['settings']['meta_keyword'] = $settings->meta_keywords;
        }
        if(!@$settings->meta_description){
            $description = $settings->name.', '.strip_tags($settings->about);
            $this->return['settings']['meta_description_ar'] = $description;
            $this->return['settings']['meta_description_en'] = $description;
            $this->return['settings']['meta_description'] = $description;
        }else{
            $this->return['settings']['meta_description'] = $settings->meta_description;
        }

        $this->return['settings']['activities'] = ActivityResource::collection($activities);
        $this->return['settings']['subscription'] = SubscriptionResource::collection($subscription);
        return $this->return;
    }
    public function memberSubscriptionInfo(){
        $phone = request('phone');
        $code = request('code');
        $lang = request('lang') ? request('lang') : 'ar';
        $device_type = request('device_type') ? request('device_type') : 'android';

        app()->setLocale($lang);
        if(!$this->validateApiRequest(['code', 'phone']))
            return $this->response;

        $member =  GymMember::with(['member_subscription_info.subscription', 'member_attendees' => function($q){
            $q->orderBy('id', 'desc')->limit(20);
        }])->withCount('member_attendees')->where(['code' => $code, 'phone' => $phone])->first();
        if($member){
            $this->member_id = $member->id;
            $qrcodes_folder = base_path('uploads/barcodes/');
            $d = new DNS1D();
            $d->setStorPath($qrcodes_folder);
            $img = $d->getBarcodePNGPath($member->code, TypeConstants::BarcodeType);
            $member->code_url = asset($img);
            $this->return['member'] = new MemberResource($member);

            if(@request('device_token')) $this->updatePushToken();

            return $this->successResponse();
        }
        $this->return['member'] = trans('sw.subscription_not_found');
        return $this->falseResponse();
    }
    public function memberSubscriptionInvoiceInfo(){
        $member_id = request('member_id');
        $invoice_id = request('invoice_id');
        $lang = request('lang') ? request('lang') : 'ar';
        $device_type = request('device_type') ? request('device_type') : 'android';

        app()->setLocale($lang);
        if(!$this->validateApiRequest(['member_id', 'invoice_id']))
            return $this->response;

        $invoice =  GymMemberSubscription::with(['subscription', 'member'])->where(['id' => $invoice_id, 'member_id' => $member_id])->first();
        if($invoice){
            $setting = $this->SettingRepository->first();
            if(@$setting->vat_details['saudi']){

                $qrcodes_folder = base_path('uploads/invoices/');
                File::cleanDirectory($qrcodes_folder.'*.png');
                $generatedQRString = GenerateQrCode::fromArray([
                    new Seller(@$setting->name), // seller name
                    new TaxNumber(@$setting->vat_details['vat_number']), // seller tax number
                    new InvoiceDate(Carbon::parse($invoice['created_at'])->format('c')), // invoice date as Zulu ISO8601 @see https://en.wikipedia.org/wiki/ISO_8601
                    new InvoiceTotalAmount(number_format($invoice['amount'],2)), // invoice total amount
                    new InvoiceTaxAmount(@number_format($invoice['vat'],2)) // invoice tax amount
                    // TODO :: Support others tags
                ])->toBase64();
                $d = new DNS2D();
                $d->setStorPath($qrcodes_folder);
                $qr_img_invoice = $d->getBarcodePNGPath($generatedQRString, TypeConstants::QRCodeType);
            }
            $this->return['invoice'] = $invoice;
            $this->return['invoice']['qr_code'] = @asset($qr_img_invoice);
            return $this->successResponse();
        }
        $this->return['invoice'] = trans('sw.subscription_not_found');
        return $this->falseResponse();
    }

    public function memberSubscriptionInfoByPhone(){
        $phone = request('phone');
        if(!$this->validateApiRequest(['phone']))
            return $this->response;

        $member =  GymMember::with(['member_subscription_info'])->where(['phone' => $phone])->first();
        $this->return['phone'] = ($phone);
        $this->return['member'] = ($member);
        if($member){
            $this->return['member'] = $member;
            return $this->successResponse();
        }
        $this->return['member'] = trans('sw.subscription_not_found');
        return $this->falseResponse();
    }

    public function createMemberSubscription(){

        if(!$this->validateApiRequest(['member', 'subscription']))
            return $this->response;

        $member = request('member');
        $subscription = request('subscription');

        GymMember::updateOrCreate(['phone' => $member->phone], $member);
        $member_subscription = GymMemberSubscription::create($subscription);

        $amount_box = GymMoneyBox::latest()->first();
        $amount_after = GymMoneyBoxFrontController::amountAfter($amount_box->amount, $amount_box->amount_before, $amount_box->operation);

        $notes = str_replace(':subscription', @$subscription->subscription->name, trans('sw.member_moneybox_renew_msg'));
        $notes = str_replace(':member', $member->name, $notes);
        $notes = str_replace(':amount_paid', @$subscription->amount_paid, $notes);


        if($this->mainSettings->vat_details['vat_percentage']){
            $notes = $notes.' - '.trans('sw.vat_added');
        }

        GymMoneyBox::create([
            'user_id' => @Auth::guard('sw')->user()->id
            , 'amount' => @$subscription->amount_paid
            , 'vat' => @$subscription->vat
            , 'operation' => TypeConstants::Add
            , 'amount_before' => $amount_after
            , 'notes' => $notes
            , 'type' => TypeConstants::RenewMember
            , 'member_id' => $member->id
            , 'payment_type' => TypeConstants::ONLINE_PAYMENT
            , 'member_subscription_id' => @$member_subscription->id
        ]);

        $this->userLog($notes, TypeConstants::RenewMember);



        return $this->successResponse();
    }

    public function memberAttendanceInfo(){
        $phone = request('phone');
        $code = request('code');
        $lang = request('lang') ? request('lang') : 'ar';
        $device_type = request('device_type') ? request('device_type') : 'android';

        app()->setLocale($lang);
        if(!$this->validateApiRequest(['code', 'phone']))
            return $this->response;

        $member =  GymMember::with(['member_attendees', 'member_subscription_info_has_active'])->withCount('member_attendees')->where(['code' => $code, 'phone' => $phone])->first();
        if($member){
            $data = [
                'id' => $member->id,
                'name' => $member->name,
                'phone' => $member->phone,
                'image' => $member->image,
                'invitations' => $member->member_subscription_info_has_active?->invitations ?? 0,
                'code_url' => $member->code_url,
                'code' => $member->code,
                'attendees_count' => @$member->member_attendees_count,
                'attendees' => @$member->member_attendees,
            ];
            $this->return['member'] = $data;

            if(@request('device_token')) $this->updatePushToken();

            return $this->successResponse();
        }
        $this->return['member'] = trans('sw.subscription_not_found');
        return $this->falseResponse();
    }
    public function aboutUs(){

        app()->setLocale(request('lang') ?? 'ar');
        $about = $this->SettingRepository
            ->select(
                'about_en', 'about_ar', 'terms_en', 'terms_ar'
            )
            ->first();
        return view('software::Web.about', ['about' => $about]);

    }

    public function gallery(){

        app()->setLocale(request('lang') ?? 'ar');
        $gallery = $this->SettingRepository
            ->select(
                'images'
            )
            ->first();
        $total = 0;
        if(@count($gallery->images)){
            $gallery = array_map(function ($image) {
                return asset(Setting::$uploads_path.'gyms/'.$image);
            }, $gallery->images);
            $total = count($gallery);
        }
        return view('software::Web.gallery', ['gallery' => $gallery, 'total'=> $total]);
    }
    public function subscriptions(){

        app()->setLocale(request('lang') ?? 'ar');

        $subscriptions = GymSubscription::select('id', 'name_ar', 'name_en', 'price')->get();
        $total = $subscriptions->count();

        return view('software::Web.subscriptions', ['subscriptions' => $subscriptions, 'total'=> $total]);
    }
    public function activities(){

        app()->setLocale(request('lang') ?? 'ar');

        $activities = GymActivity::select('id', 'name_ar', 'name_en', 'price')->get();
        $total = $activities->count();

        return view('software::Web.activities', ['activities' => $activities, 'total'=> $total]);
    }
    public function notifications(){

        app()->setLocale(request('lang') ?? 'ar');

        $notifications = GymUserNotification::select('id', 'title', 'body')->where('user_id', '!=', '')->get();
        $total = $notifications->count();

        return view('software::Web.notifications', ['notifications' => $notifications, 'total'=> $total]);
    }
    public function home(){

        app()->setLocale(request('lang') ? request('lang') : 'ar');
        $home = $this->SettingRepository
            ->select(
                'name_en', 'name_ar', 'phone', 'meta_description_en', 'meta_keywords_en', 'meta_description_ar', 'meta_keywords_ar', 'logo_en', 'logo_ar', 'logo_white_en', 'logo_white_ar'
                ,'facebook', 'twitter', 'google_plus', 'instagram', 'youtube', 'latitude', 'longitude', 'support_email', 'about_en', 'about_ar', 'terms_en', 'terms_ar'
                ,'address_en', 'address_ar', 'under_maintenance', 'images', 'cover_images'
            )
            ->first();

        if(@count($home->cover_images)){
            $home->cover_images = array_map(function ($image) {
                return asset(Setting::$uploads_path.'gyms/'.$image);
            }, $home->cover_images);
        }

        if(@request('device_token')) $this->updatePushToken();

        return view('software::Web.home', ['home' => $home, 'total'=> 0]);
    }

    public function updatePushToken()
    {
        $this->validateApiRequest(['device_token']);

        if(in_array(request('device_type'), [Constants::ANDROID, Constants::IOS]))
            $device_type = request('device_type');
        else
            $device_type = Constants::ANDROID;

        $device_token = request('device_token');
        $member_id = request('member_id') ?? $this->member_id;

        $record = Push_tokens::where('token', $device_token)->first();
        if (!$record) {
            Push_tokens::create([
                'device_type' => $device_type,
                'token' => $device_token,
                'user_id' => $member_id,
            ]);
            (new FirebaseApiController())->addTokenToTopic($device_token, $device_type);
        } else {
            $record->update(['user_id' => $member_id]);
        }
        $this->successResponse();
        return $this->response;
    }
    public function userLog($notes, $type = null){
        GymUserLog::insert([
            'user_id' => @Auth::guard('sw')->user()->id,
            'notes' => $notes,
            'type' => $type
        ]);
    }

}


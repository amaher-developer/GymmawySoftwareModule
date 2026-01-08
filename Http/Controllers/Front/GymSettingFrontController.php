<?php

namespace Modules\Software\Http\Controllers\Front;


use Modules\Billing\Services\SwBillingService;
use Modules\Generic\Classes\SMSGymmawy;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Http\Requests\GymGroupDiscountRequest;
use Modules\Software\Http\Requests\GymMoneyBoxTypeRequest;
use Modules\Software\Http\Requests\GymPaymentTypeRequest;
use Modules\Software\Http\Requests\GymSaleChannelRequest;
use Modules\Software\Http\Requests\GymSettingRequest;
use Modules\Generic\Models\Setting;
use Modules\Software\Http\Requests\GymStoreGroupRequest;
use Modules\Software\Models\GymGroupDiscount;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Models\GymMoneyBoxType;
use Modules\Software\Models\GymPaymentType;
use Modules\Software\Models\GymSaleChannel;
use Modules\Software\Models\GymStoreGroup;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class GymSettingFrontController extends GymGenericFrontController
{
    private $imageManager;

    public function __construct()
    {
        parent::__construct();
        $this->imageManager = new ImageManager(new Driver());
    }

    public function edit()
    {

        $mainSetting = Setting::branch()->first();

        $title = trans('sw.settings');
        $smsPoints = $this->formatSmsPoints(0);
        try {
            if($mainSetting['sms_internal_gateway']){
                $sms = new SMSGymmawy();
                $sms = $sms->getBalance();
            }else{
                $sms = new \Modules\Software\Classes\SMSFactory(@env('SMS_GATEWAY'));
                $sms = $sms->getBalance();
            }
            $smsPoints = $this->formatSmsPoints($sms);
        } catch (\Exception $e) {
            \Log::error('Error fetching SMS balance: ' . $e->getMessage());
            $smsPoints = $this->formatSmsPoints(0);
        }
        $max_messages = TypeConstants::WA_ULTRA_MAX_MESSAGE;

        return view('software::Front.setting', [
            'title'=>$title,
            'smsPoints' => $smsPoints,
            'max_messages' => $max_messages,
            'mainSetting' => $mainSetting,
            'billingSettings' => $mainSetting->billing ?? [],
            'imagePath' => asset(Setting::$uploads_path.'gyms/'),
        ]);
    }

    public function update(GymSettingRequest $request)
    {
        $setting = Setting::branch()->first();
        
        $setting_inputs = $this->prepare_inputs($request->only(['name_ar', 'name_en', 'facebook', 'twitter', 'instagram',  'tiktok',  'snapchat', 'youtube', 'address_ar', 'address_en',
            'latitude', 'longitude', 'phone', 'support_email', 'meta_keywords_ar', 'meta_keywords_en', 'meta_description_ar', 'meta_description_en',
            'about_ar', 'about_en', 'terms_ar', 'terms_en', 'sms_username', 'sms_email', 'sms_sms_sender_id'
            , 'images', 'vat_details', 'reservation_details']));

        $billingInput = $request->input('billing');
        if ($billingInput !== null) {
            $currentBilling = $setting->billing ?? [];

            $sectionsInput = data_get($billingInput, 'sections', []);

            $sections = array_merge($currentBilling['sections'] ?? [], [
                'store_orders' => array_key_exists('store_orders', $sectionsInput),
                'non_members' => array_key_exists('non_members', $sectionsInput),
                'money_boxes' => array_key_exists('money_boxes', $sectionsInput),
                'members' => array_key_exists('members', $sectionsInput),
                'pt_members' => array_key_exists('pt_members', $sectionsInput),
            ]);

            $setting_inputs['billing'] = [
                'sections' => $sections,
                'bindings' => $currentBilling['bindings'] ?? [],
            ];
        }

        // Manually build social_media array from individual fields
        $socialMediaData = [];
        $socialKeys = ['facebook', 'twitter', 'instagram', 'youtube', 'snapchat', 'tiktok'];
        foreach ($socialKeys as $key) {
            if (isset($setting_inputs[$key])) {
                $socialMediaData[$key] = $setting_inputs[$key];
            }
        }
        
        // Add social_media to inputs if we have data
        if (!empty($socialMediaData)) {
            $setting_inputs['social_media'] = $socialMediaData;
        }
        
        // Update settings
        $setting->update($setting_inputs);

        SwBillingService::flushSettingsCache();
        
        //Cache::store('file')->clear();
        Cache::flush();
        
        // Flash success message
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        
        return redirect(route('sw.editSetting',1));
    }

    private function prepare_inputs_addons($inputs){
        return $inputs;
    }
    private function prepare_inputs($inputs)
    {
        $input_file = 'logo_ar';
        if (request()->hasFile($input_file)) {
            $file = request()->file($input_file);
            $filename = rand(0, 20000) . time() . '.' . $file->getClientOriginalExtension();
            $destinationPath = base_path(Setting::$uploads_path);

            // Create directory if it doesn't exist
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $img = $this->imageManager->read($file->getRealPath());
            $img->scale(width: 200, height: 200)->toJpeg()->save($destinationPath.'/'.'thumb_'.$filename);

            $upload_success = $file->move($destinationPath, $filename);

            if ($upload_success) {
                $inputs[$input_file] = $filename;
            }
        }else{
            unset($inputs[$input_file]);
        }

        $input_file = 'logo_en';
        if (request()->hasFile($input_file)) {
            $file = request()->file($input_file);
            $filename = rand(0, 20000) . time() . '.' . $file->getClientOriginalExtension();
            $destinationPath = base_path(Setting::$uploads_path);

            // Create directory if it doesn't exist
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $img = $this->imageManager->read($file->getRealPath());
            $img->scale(width: 100, height: 100)->toJpeg()->save($destinationPath.'/'.'thumb_'.$filename);

            $upload_success = $file->move($destinationPath, $filename);

            if ($upload_success) {
                $inputs[$input_file] = $filename;
            }
        }else{
            unset($inputs[$input_file]);
        }
//        $input_file = 'logo_white_ar';
//        if (request()->hasFile($input_file)) {
//            $file = request()->file($input_file);
//            $filename = rand(0, 20000) . time() . '.' . $file->getClientOriginalExtension();
//            $destinationPath = base_path(Setting::$uploads_path);
//            $upload_success = $file->move($destinationPath, $filename);
//            if ($upload_success) {
//                $inputs[$input_file] = $filename;
//            }
//        }else{
//            unset($inputs[$input_file]);
//        }
//        $input_file = 'logo_white_en';
//        if (request()->hasFile($input_file)) {
//            $file = request()->file($input_file);
//            $filename = rand(0, 20000) . time() . '.' . $file->getClientOriginalExtension();
//            $destinationPath = base_path(Setting::$uploads_path);
//            $upload_success = $file->move($destinationPath, $filename);
//            if ($upload_success) {
//                $inputs[$input_file] = $filename;
//            }
//        }else{
//            unset($inputs[$input_file]);
//        }

        if(@$inputs['meta_keywords_ar']) $inputs['meta_keywords_ar'] = implode('&', $inputs['meta_keywords_ar']);
        if(@$inputs['meta_keywords_en']) $inputs['meta_keywords_en'] = implode('&', $inputs['meta_keywords_en']);
        
        // Handle social media - ensure empty strings don't become null
        $socialKeys = ['facebook', 'instagram', 'twitter', 'youtube', 'snapchat', 'tiktok'];
        foreach ($socialKeys as $key) {
            if (!isset($inputs[$key]) || $inputs[$key] === null) {
                $inputs[$key] = '';
            }
        }
        
        // Handle text fields - convert empty strings to avoid null constraint violations
        $inputs['about_ar'] = !empty($inputs['about_ar']) ? nl2br($inputs['about_ar'], false) : '';
        $inputs['about_en'] = !empty($inputs['about_en']) ? nl2br($inputs['about_en'], false) : '';
        $inputs['terms_ar'] = !empty($inputs['terms_ar']) ? nl2br($inputs['terms_ar'], false) : '';
        $inputs['terms_en'] = !empty($inputs['terms_en']) ? nl2br($inputs['terms_en'], false) : '';
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


    function updateImage()
    {
        $settings = Setting::branch()->first();
//        if(@request('type') == 2){
//            $setting_images = (array)$settings->cover_images;
//            $input_file = 'cover_file';
//        }else{
            $setting_images = (array)$settings->images;
            $input_file = 'file';
//        }

        $destinationPath = base_path(Setting::$uploads_path.'gyms/');
        
        // Create directory if it doesn't exist
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }
        
        $max_images = (int)env('MAX_WEBSITE_IMAGES') ? (int)env('MAX_WEBSITE_IMAGES') : 10;
        if(count($setting_images) > $max_images){
            return 1;
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
                    $img->toJpeg(70)->save($destinationPath . $filename);
                    $img->scale(width: $new_width, height: $new_height)->toJpeg(70)->save($destinationPath . '' . $filename);

                } else {
                    //save used image
                    $img->toJpeg(70)->save($destinationPath . $filename);

                }
                $inputs[$input_file] = $uploaded;


                array_push($setting_images, $filename);
                if(@request('type') == 2){ $settings->cover_images = $setting_images; }else{$settings->images = $setting_images;}
                $settings->save();

                return asset(('./uploads/settings/gyms/' . $filename));
            }
        }
        return 0;
    }

    function updateImageDelete(){

        $settings = Setting::branch()->first();
        if(request('type') == 2){ $setting_images = (array)$settings->cover_images;}else{$setting_images = (array)$settings->images;}
        $index = array_search(request('image'),$setting_images);
        if($index !== FALSE){
            @unlink(Setting::$uploads_path.'gyms/'.$setting_images[$index]);
            unset($setting_images[$index]);
            request('type') == 2 ? $settings->cover_images = $setting_images : $settings->images = $setting_images;
            $settings->save();

            return $index;
        }
        return 'false';
    }







    // payment types
    public function indexPaymentType()
    {
        $title = trans('sw.payment_types');
        $this->request_array = ['search'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $payment_types = GymPaymentType::onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $payment_types = GymPaymentType::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->orderBy('id', 'DESC');
        }

        //apply filters
        $payment_types->when($search, function ($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('id', '=', $search);
                $query->orWhere('name_' . $this->lang, 'like', "%" . $search . "%");
            });
        });
        $search_query = request()->query();

        if ($this->limit) {
            $payment_types = $payment_types->paginate($this->limit);
            $total = $payment_types->total();
        } else {
            $payment_types = $payment_types->get();
            $total = $payment_types->count();
        }

        return view('software::Front.payment_type_front_list', compact('payment_types','title', 'total', 'search_query'));
    }

    public function createPaymentType()
    {
        $title = trans('sw.payment_type_add');
        return view('software::Front.payment_type_front_form', ['payment_type' => new GymPaymentType(),'title'=>$title]);
    }

    public function storePaymentType(GymPaymentTypeRequest $request)
    {
        $prepare_inputs = $this->prepare_inputs($request->except(['_token']));
        $payment_type = GymPaymentType::create($prepare_inputs);
        $payment_type->payment_id = $payment_type->id - 1;
        $payment_type->save();
        
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);

        $notes = str_replace(':name', $prepare_inputs['name_'.$this->lang], trans('sw.add_payment_type'));
        $this->userLog($notes, TypeConstants::CreatePaymentType);
        return redirect(route('sw.listPaymentType'));
    }

    public function editPaymentType($id)
    {
        $payment_type = GymPaymentType::withTrashed()->find($id);
        $title = trans('sw.payment_type_edit');
        return view('software::Front.payment_type_front_form', ['payment_type' => $payment_type,'title'=>$title]);
    }

    public function updatePaymentType(GymPaymentTypeRequest $request, $id)
    {
        $payment_type = GymPaymentType::withTrashed()->find($id);
        $prepare_inputs = $this->prepare_inputs($request->except(['_token']));
//        $prepare_inputs['payment_id'] = $payment_type->id - 1;
        $payment_type->update($prepare_inputs);

        $notes = str_replace(':name', $payment_type['name'], trans('sw.edit_payment_type'));
        $this->userLog($notes, TypeConstants::EditPaymentType);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listPaymentType'));
    }

    public function destroyPaymentType($id)
    {
        $payment_type = GymPaymentType::withTrashed()->find($id);
        if($payment_type->trashed())
        {
            $payment_type->restore();
        }
        else
        {
            $payment_type->delete();

            $notes = str_replace(':name', $payment_type['name'], trans('sw.delete_payment_type'));
            $this->userLog($notes, TypeConstants::DeletePaymentType);
        }
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listPaymentType'));
    }





    // group discounts
    public function indexGroupDiscount()
    {
        $title = trans('sw.group_discounts');
        $this->request_array = ['search'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $group_discounts = GymGroupDiscount::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $group_discounts = GymGroupDiscount::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->orderBy('id', 'DESC');
        }

        //apply filters
        $group_discounts->when($search, function ($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('id', '=', $search);
                $query->orWhere('name_' . $this->lang, 'like', "%" . $search . "%");
            });
        });
        $search_query = request()->query();

        if ($this->limit) {
            $group_discounts = $group_discounts->paginate($this->limit);
            $total = $group_discounts->total();
        } else {
            $group_discounts = $group_discounts->get();
            $total = $group_discounts->count();
        }

        return view('software::Front.group_discount_front_list', compact('group_discounts','title', 'total', 'search_query'));
    }

    public function createGroupDiscount()
    {
        $title = trans('sw.group_discount_add');
        return view('software::Front.group_discount_front_form', ['group_discount' => new GymGroupDiscount(),'title'=>$title]);
    }

    public function storeGroupDiscount(GymGroupDiscountRequest $request)
    {
        $prepare_inputs = $this->prepare_inputs($request->except(['_token']));
        GymGroupDiscount::create($prepare_inputs);
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);

        $notes = str_replace(':name', $prepare_inputs['name_'.$this->lang], trans('sw.add_group_discount'));
        $this->userLog($notes, TypeConstants::CreateGroupDiscount);
        return redirect(route('sw.listGroupDiscount'));
    }

    public function editGroupDiscount($id)
    {
        $group_discount = GymGroupDiscount::withTrashed()->find($id);
        $title = trans('sw.group_discount_edit');
        return view('software::Front.group_discount_front_form', ['group_discount' => $group_discount,'title'=>$title]);
    }

    public function updateGroupDiscount(GymGroupDiscountRequest $request, $id)
    {
        $group_discount = GymGroupDiscount::withTrashed()->find($id);
        $prepare_inputs = $this->prepare_inputs($request->except(['_token']));
        $group_discount->update($prepare_inputs);

        $notes = str_replace(':name', $prepare_inputs['name_'.$this->lang], trans('sw.edit_group_discount'));
        $this->userLog($notes, TypeConstants::EditGroupDiscount);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listGroupDiscount'));
    }

    public function destroyGroupDiscount($id)
    {
        $group_discount = GymGroupDiscount::withTrashed()->find($id);
        if($group_discount->trashed())
        {
            $group_discount->restore();
        }
        else
        {
            $group_discount->delete();

            $notes = str_replace(':name', $group_discount['name'], trans('sw.delete_group_discount'));
            $this->userLog($notes, TypeConstants::DeleteGroupDiscount);
        }
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listGroupDiscount'));
    }




    // sale channels
    public function indexSaleChannel()
    {
        $title = trans('sw.sale_channels');
        $this->request_array = ['search'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $sale_channels = GymSaleChannel::onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $sale_channels = GymSaleChannel::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->orderBy('id', 'DESC');
        }

        //apply filters
        $sale_channels->when($search, function ($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('id', '=', $search);
                $query->orWhere('name_' . $this->lang, 'like', "%" . $search . "%");
            });
        });
        $search_query = request()->query();

        if ($this->limit) {
            $sale_channels = $sale_channels->paginate($this->limit);
            $total = $sale_channels->total();
        } else {
            $sale_channels = $sale_channels->get();
            $total = $sale_channels->count();
        }

        return view('software::Front.sale_channel_front_list', compact('sale_channels','title', 'total', 'search_query'));
    }

    public function createSaleChannel()
    {
        $title = trans('sw.sale_channel_add');
        return view('software::Front.sale_channel_front_form', ['sale_channel' => new GymSaleChannel(),'title'=>$title]);
    }

    public function storeSaleChannel(GymSaleChannelRequest $request)
    {
        $prepare_inputs = $this->prepare_inputs($request->except(['_token']));
        GymSaleChannel::create($prepare_inputs);
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);

        $notes = str_replace(':name', $prepare_inputs['name_'.$this->lang], trans('sw.add_group_discount'));
        $this->userLog($notes, TypeConstants::CreateSaleChannel);
        return redirect(route('sw.listSaleChannel'));
    }

    public function editSaleChannel($id)
    {
        $sale_channel = GymSaleChannel::withTrashed()->find($id);
        $title = trans('sw.sale_channel_edit');
        return view('software::Front.sale_channel_front_form', ['sale_channel' => $sale_channel,'title'=>$title]);
    }

    public function updateSaleChannel(GymSaleChannelRequest $request, $id)
    {
        $sale_channel = GymSaleChannel::withTrashed()->find($id);
        $prepare_inputs = $this->prepare_inputs($request->except(['_token']));
        $sale_channel->update($prepare_inputs);

        $notes = str_replace(':name', $prepare_inputs['name_'.$this->lang], trans('sw.edit_sale_channel'));
        $this->userLog($notes, TypeConstants::EditSaleChannel);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listSaleChannel'));
    }

    public function destroySaleChannel($id)
    {
        $sale_channel = GymSaleChannel::withTrashed()->find($id);
        if($sale_channel->trashed())
        {
            $sale_channel->restore();
        }
        else
        {
            $sale_channel->delete();

            $notes = str_replace(':name', $sale_channel['name'], trans('sw.delete_sale_channel'));
            $this->userLog($notes, TypeConstants::DeleteSaleChannel);
        }
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listSaleChannel'));
    }




    // money box types
    public function indexMoneyBoxType()
    {
        $title = trans('sw.money_box_types');
        $this->request_array = ['search'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $money_box_types = GymMoneyBoxType::onlyTrashed()->orderBy('operation_type', 'DESC');
        }
        else
        {
            $money_box_types = GymMoneyBoxType::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->orderBy('operation_type', 'DESC');
        }

        //apply filters
        $money_box_types->when($search, function ($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('id', '=', $search);
                $query->orWhere('name_' . $this->lang, 'like', "%" . $search . "%");
            });
        });
        $search_query = request()->query();

        if ($this->limit) {
            $money_box_types = $money_box_types->paginate($this->limit);
            $total = $money_box_types->total();
        } else {
            $money_box_types = $money_box_types->get();
            $total = $money_box_types->count();
        }

        return view('software::Front.money_box_type_front_list', compact('money_box_types','title', 'total', 'search_query'));
    }

    public function createMoneyBoxType()
    {
        $title = trans('sw.money_box_type_add');
        return view('software::Front.money_box_type_front_form', ['money_box_type' => new GymMoneyBoxType(),'title'=>$title]);
    }

    private function getPaymentTypeOfMoneyBoxType($operation_type){
        if($operation_type == TypeConstants::Add){
            return 0;
        }else{
            return 1;
        }

    }

    public function storeMoneyBoxType(GymMoneyBoxTypeRequest $request)
    {
        $prepare_inputs = $this->prepare_inputs($request->except(['_token']));
        $prepare_inputs['payment_type'] = $this->getPaymentTypeOfMoneyBoxType($prepare_inputs['operation_type']);
        GymMoneyBoxType::create($prepare_inputs);
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);

        $notes = str_replace(':name', $prepare_inputs['name_'.$this->lang], trans('sw.add_money_box_type'));
        $this->userLog($notes, TypeConstants::CreateMoneyBoxType);
        return redirect(route('sw.listMoneyBoxType'));
    }

    public function editMoneyBoxType($id)
    {
        $money_box_type = GymMoneyBoxType::withTrashed()->find($id);
        $title = trans('sw.money_box_type_edit');
        return view('software::Front.money_box_type_front_form', ['money_box_type' => $money_box_type,'title'=>$title]);
    }

    public function updateMoneyBoxType(GymMoneyBoxTypeRequest $request, $id)
    {
        $money_box_type = GymMoneyBoxType::withTrashed()->find($id);
        $prepare_inputs = $this->prepare_inputs($request->except(['_token']));
        $prepare_inputs['payment_type'] = $this->getPaymentTypeOfMoneyBoxType($prepare_inputs['operation_type']);
        $money_box_type->update($prepare_inputs);

        $notes = str_replace(':name', $prepare_inputs['name_'.$this->lang], trans('sw.edit_money_box_type'));
        $this->userLog($notes, TypeConstants::EditMoneyBoxType);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listMoneyBoxType'));
    }

    public function destroyMoneyBoxType($id)
    {
        $money_box_type = GymMoneyBoxType::withTrashed()->find($id);
        if($money_box_type->trashed())
        {
            $money_box_type->restore();
        }
        else
        {
            $money_box_type->delete();

            $notes = str_replace(':name', $money_box_type['name'], trans('sw.delete_money_box_type'));
            $this->userLog($notes, TypeConstants::DeleteMoneyBoxType);
        }
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listMoneyBoxType'));
    }





    // store groups
    public function indexStoreGroup()
    {
        $title = trans('sw.store_groups');
        $this->request_array = ['search'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $store_groups = GymStoreGroup::onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $store_groups = GymStoreGroup::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->orderBy('id', 'DESC');
        }

        //apply filters
        $store_groups->when($search, function ($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('id', '=', $search);
                $query->orWhere('name_' . $this->lang, 'like', "%" . $search . "%");
            });
        });
        $search_query = request()->query();

        if ($this->limit) {
            $store_groups = $store_groups->paginate($this->limit);
            $total = $store_groups->total();
        } else {
            $store_groups = $store_groups->get();
            $total = $store_groups->count();
        }

        return view('software::Front.store_group_front_list', compact('store_groups','title', 'total', 'search_query'));
    }

    public function createStoreGroup()
    {
        $title = trans('sw.store_group_add');
        return view('software::Front.store_group_front_form', ['store_group' => new GymStoreGroup(),'title'=>$title]);
    }

    public function storeStoreGroup(GymStoreGroupRequest $request)
    {
        $prepare_inputs = $this->prepare_inputs($request->except(['_token']));
        GymStoreGroup::create($prepare_inputs);
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);

        $notes = str_replace(':name', $prepare_inputs['name_'.$this->lang], trans('sw.add_store_group'));
        $this->userLog($notes, TypeConstants::CreateStoreGroup);
        return redirect(route('sw.listStoreGroup'));
    }

    public function editStoreGroup($id)
    {
        $store_group = GymStoreGroup::withTrashed()->find($id);
        $title = trans('sw.store_group_edit');
        return view('software::Front.store_group_front_form', ['store_group' => $store_group,'title'=>$title]);
    }

    public function updateStoreGroup(GymStoreGroupRequest $request, $id)
    {
        $store_group = GymStoreGroup::withTrashed()->find($id);
        $prepare_inputs = $this->prepare_inputs($request->except(['_token']));
        $store_group->update($prepare_inputs);

        $notes = str_replace(':name', $prepare_inputs['name_'.$this->lang], trans('sw.edit_money_box_type'));
        $this->userLog($notes, TypeConstants::EditStoreGroup);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listStoreGroup'));
    }

    public function destroyStoreGroup($id)
    {
        $store_group = GymStoreGroup::withTrashed()->find($id);
        if($store_group->trashed())
        {
            $store_group->restore();
        }
        else
        {
            $store_group->delete();

            $notes = str_replace(':name', $store_group['name'], trans('sw.delete_store_group'));
            $this->userLog($notes, TypeConstants::DeleteStoreGroup);
        }
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listStoreGroup'));
    }
}


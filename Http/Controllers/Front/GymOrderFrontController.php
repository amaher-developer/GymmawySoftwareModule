<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Generic\Http\Controllers\Front\GenericFrontController;

use Modules\Software\Classes\TypeConstants;
use Modules\Software\Models\GymMemberSubscription;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Models\GymNonMember;
use Modules\Software\Models\GymPaymentType;
use Modules\Software\Models\GymPTMember;
use Modules\Software\Models\GymSubscription;
use Modules\Software\Models\GymReservation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Container\Container as Application;
use Modules\Software\Http\Requests\GymOrderRequest;
use Modules\Software\Repositories\GymOrderRepository;
use Modules\Software\Models\GymOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Milon\Barcode\DNS2D;
use Salla\ZATCA\GenerateQrCode;
use Salla\ZATCA\Tags\InvoiceDate;
use Salla\ZATCA\Tags\InvoiceTaxAmount;
use Salla\ZATCA\Tags\InvoiceTotalAmount;
use Salla\ZATCA\Tags\Seller;
use Salla\ZATCA\Tags\TaxNumber;

class GymOrderFrontController extends GymGenericFrontController
{
    public $GymOrderRepository;
    private $imageManager;

    public $payments = [];
    public $cash_payment = [];
    public $online_payment = [];
    public $bank_transfer_payment = [];

    public function __construct()
    {
        parent::__construct();
        $this->imageManager = new ImageManager(new Driver());

        $this->GymOrderRepository = new GymOrderRepository(new Application);
        // Repository branch filtering removed from constructor - now applied per query
    }


    public function index()
    {
        $title = trans('global.gym_orders');

        $gymorders = $this->GymOrderRepository->whereHas('member',function ($q){
            $q->where('user_id', $this->user->id);
        })->orderBy('id', 'DESC');

//        $gymorders = $gymorders->get();
        $total = $gymorders->count();


        return view('software::Front.user.gymorder_front_list', compact('title', 'total'));
    }

    private function prepareForExport($data)
    {
        return array_map(function ($row) {
            return [
                'ID' => $row['id']
            ];
        }, $data->toArray());
    }

    public function show($id)
    {
        $order = GymMoneyBox::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->with(['member', 'member_subscription', 'store_order.loyaltyRedemption.rule'])->where('id', $id)->first();
        $transaction_value = 1;
        if(in_array($order->type, [TypeConstants::DeleteMember, TypeConstants::DeleteStoreOrder, TypeConstants::DeleteNonMember, TypeConstants::DeletePTMember, TypeConstants::DeleteSubscription, TypeConstants::DeleteStorePurchaseOrder]))
        {
            $title = trans('sw.refund_invoice');
            $title_details = trans('sw.refund_invoice_details');
            $transaction_value = -1;
        }elseif(in_array($order->type, [TypeConstants::CreateMemberPayAmountRemainingForm, TypeConstants::CreateMoneyBoxWithdrawEarnings, TypeConstants::CreateMoneyBoxWithdraw, TypeConstants::CreateMoneyBoxAdd  ,TypeConstants::EditMember, TypeConstants::EditPTMember, TypeConstants::EditNonMember]))
        {
            $title = trans('sw.cash_receipt');
            $title_details = trans('sw.cash_receipt_details');
        }
        else{
            $title = trans('sw.invoice');
            $title_details = trans('sw.invoice_details');
        }
//        foreach ($order['products'] as $i => $product_id){
//            $order['products'][$i]['details'] = GymStoreProduct::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('id', $product_id)->withTrashed()->first()->toArray();
//        }
        $qrcodes_folder = base_path('uploads/invoices/');
        if (!File::exists($qrcodes_folder)) {
            File::makeDirectory($qrcodes_folder, 0755, true, true);
        }
        File::cleanDirectory($qrcodes_folder.'*.png');


        if(@$this->mainSettings->vat_details['saudi']){
            $generatedQRString = GenerateQrCode::fromArray([
                new Seller(@$this->mainSettings->name), // seller name
                new TaxNumber(@$this->mainSettings->vat_details['vat_number']), // seller tax number
                new InvoiceDate(Carbon::parse($order['created_at'])->format('c')), // invoice date as Zulu ISO8601 @see https://en.wikipedia.org/wiki/ISO_8601
                new InvoiceTotalAmount(@round(($transaction_value) * ( @$order['amount']),2)), // invoice total amount
                new InvoiceTaxAmount(@round($order['vat'],2)) // invoice tax amount
                // TODO :: Support others tags
            ])->toBase64();

            $d = new DNS2D();
            $d->setStorPath($qrcodes_folder);
            $qr_img_invoice = $d->getBarcodePNGPath($generatedQRString, TypeConstants::QRCodeType);
            $qr_img_invoice = str_replace(base_path(), '', $qr_img_invoice);
            // Convert absolute path to relative path for asset() function
            $qr_img_invoice = str_replace(base_path(), '', $qr_img_invoice);
        }
        $payment_types = GymPaymentType::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->get();
        return view('software::Front.order_front_show', ['title_details' => $title_details,'order' => $order, 'qr_img_invoice' => @$qr_img_invoice, 'title'=>$title, 'payment_types' => $payment_types]);
    }


    public function showPOS($id)
    {
        $title = trans('sw.invoice');
        $order = GymMoneyBox::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->with(['pay_type', 'member', 'member_subscription', 'store_order.loyaltyRedemption.rule'])->where('id', $id)->first();
        if(@$this->mainSettings->vat_details['saudi']){
            $qrcodes_folder = base_path('uploads/invoices/');
            if (!File::exists($qrcodes_folder)) {
                File::makeDirectory($qrcodes_folder, 0755, true, true);
            }
            File::cleanDirectory($qrcodes_folder.'*.png');
            $generatedQRString = GenerateQrCode::fromArray([
                new Seller(@$this->mainSettings->name), // seller name
                new TaxNumber(@$this->mainSettings->vat_details['vat_number']), // seller tax number
                new InvoiceDate(Carbon::parse($order['created_at'])->format('c')), // invoice date as Zulu ISO8601 @see https://en.wikipedia.org/wiki/ISO_8601
                new InvoiceTotalAmount(number_format($order['amount'],2)), // invoice total amount
                new InvoiceTaxAmount(@number_format($order['vat'],2)) // invoice tax amount
                // TODO :: Support others tags
            ])->toBase64();

            $d = new DNS2D();
            $d->setStorPath($qrcodes_folder);
            $qr_img_invoice = $d->getBarcodePNGPath($generatedQRString, TypeConstants::QRCodeType);
            $qr_img_invoice = str_replace(base_path(), '', $qr_img_invoice);
        }
        return view('software::Front.order_front_pos_show', ['order' => $order, 'title'=>$title, 'qr_img_invoice' => @$qr_img_invoice]);
    }

    public function showSubscription($id)
    {
        $title = trans('sw.subscription_contract');
        $order = GymMemberSubscription::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->with(['pay_type', 'member' => function($q){$q->withTrashed();}, 'subscription' => function($q){$q->withTrashed();}])->where('id', $id)->first();
        $money_box = GymMoneyBox::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('member_id', $order->member_id)->where('member_subscription_id', $order->id)->get();
        $payment_types = GymPaymentType::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->get();
        $money_box->filter(function ($item) use ($payment_types){
            foreach ($payment_types as $payment_type){
                if($item->payment_type == $payment_type->payment_id){ $this->payments[$payment_type->payment_id][] = ($item->operation == 1 ? -1 : 1) * $item->amount; }
            }
//            if($item->payment_type == TypeConstants::CASH_PAYMENT){ $this->cash_payment[] = ($item->operation == 1 ? -1 : 1) * $item->amount; }
//            if($item->payment_type == TypeConstants::ONLINE_PAYMENT){ $this->online_payment[] = ($item->operation == 1 ? -1 : 1) * $item->amount; }
//            if($item->payment_type == TypeConstants::BANK_TRANSFER_PAYMENT){ $this->bank_transfer_payment[] = ($item->operation == 1 ? -1 : 1) * $item->amount; }
        });
        $payments = [];
        foreach ($payment_types as $i => $payment_type){
            $payments[$i]['payment'] = is_array($this->payments[$payment_type->payment_id] ?? null) ? array_sum($this->payments[$payment_type->payment_id]) : 0;
            $payments[$i]['name'] = $payment_type->name;
        }
//        $cash_payment = array_sum($this->cash_payment);
//        $online_payment = array_sum($this->online_payment);
//        $bank_transfer_payment = array_sum($this->bank_transfer_payment);

        $qrcodes_folder = base_path('uploads/invoices/');
        if (!File::exists($qrcodes_folder)) {
            File::makeDirectory($qrcodes_folder, 0755, true, true);
        }
        File::cleanDirectory($qrcodes_folder.'*.png');

        $vat = 0;
        if(@$this->mainSettings->vat_details['saudi']){
            $vat = ($order->vat);
            $generatedQRString = GenerateQrCode::fromArray([
                new Seller(@$this->mainSettings->name), // seller name
                new TaxNumber(@$this->mainSettings->vat_details['vat_number']), // seller tax number
                new InvoiceDate(Carbon::parse($order['created_at'])->format('c')), // invoice date as Zulu ISO8601 @see https://en.wikipedia.org/wiki/ISO_8601
                new InvoiceTotalAmount(number_format($order['amount_paid'],2)), // invoice total amount
                new InvoiceTaxAmount(number_format(@$vat,2)) // invoice tax amount
                // TODO :: Support others tags
            ])->toBase64();

            $d = new DNS2D();
            $d->setStorPath($qrcodes_folder);
            $qr_img_invoice = $d->getBarcodePNGPath($generatedQRString, TypeConstants::QRCodeType);
            $qr_img_invoice = str_replace(base_path(), '', $qr_img_invoice);
        }
        
        // Load upcoming reservations for member
        $upcomingReservations = collect();
        if ($order->member_id) {
            $upcomingReservations = GymReservation::query()
                ->branch(@$this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)
                ->where('member_id', $order->member_id)
                ->where('client_type', 'member')
                ->whereDate('reservation_date', '>=', Carbon::now()->toDateString())
                ->whereNotIn('status', ['cancelled', 'missed'])
                ->with(['activity' => function($q) { $q->withTrashed(); }])
                ->orderBy('reservation_date', 'asc')
                ->orderBy('start_time', 'asc')
                ->get();
        }
        
        return view('software::Front.order_subscription_front_show', ['order' => $order, 'qr_img_invoice' => @$qr_img_invoice, 'title'=>$title, 'vat' => $vat
//            ,'cash_payment' => $cash_payment, 'online_payment' => $online_payment, 'bank_transfer_payment' => $bank_transfer_payment
        , 'payments' => $payments, 'upcomingReservations' => $upcomingReservations
        ]);
    }


    public function signSubscription($id){
        $order_id = $id;
        $image_parts = explode(";base64,", $_POST['signature']);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        if(@request('type') == 'sw.showOrderSubscriptionNonMember'){
            $order = GymNonMember::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('id', $order_id)->first();
            $prefix = 'non-member';
            $file_name = rand(0, 20000) . time() . '_' . $prefix .'_' . $order->id . '_signature' . '.'.$image_type;
            $log_type = TypeConstants::UploadSignatureFileNonMember;
        }elseif(@request('type') == 'sw.showOrderPTSubscription'){
            $order = GymPTMember::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('id', $order_id)->first();
            $prefix = 'pt-member';
            $file_name = rand(0, 20000) . time() . '_' . $prefix .'_' . $order->id . '_signature' . '.'.$image_type;
            $log_type = TypeConstants::UploadSignatureFilePTMember;
        }else{
            $order = GymMemberSubscription::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('id', $order_id)->first();
            $prefix = 'member';
            $file_name = rand(0, 20000) . time() . '_' . $prefix .'_' . $order->id . '_signature' . '.'.$image_type;
            $log_type = TypeConstants::UploadSignatureFileMember;
        }

        $destinationPath = GymOrder::$uploads_path;
        File::cleanDirectory($destinationPath.$file_name);
        $file = $destinationPath  . $file_name;
         file_put_contents($file, $image_base64);


        // Debug logging
        Log::info('Order object details', [
            'order_id' => $order_id,
            'order_exists' => $order ? $order->exists : 'null',
            'order_id_value' => $order ? $order->id : 'null',
            'order_class' => $order ? get_class($order) : 'null',
            'request_type' => request('type')
        ]);

        if ($order && $order->id) {
            // Use direct database update to avoid model state issues
            $tableName = $order->getTable();
            $updated = \DB::table($tableName)
                ->where('id', $order->id)
                ->update(['signature_file' => $file_name]);
            
            Log::info('Order update result', [
                'order_id' => $order_id,
                'table_name' => $tableName,
                'updated' => $updated,
                'signature_file' => $file_name
            ]);
        } else {
            // If the order doesn't exist, we need to handle this case
            Log::error('Order not found or doesn\'t exist', [
                'order_id' => $order_id,
                'type' => request('type'),
                'order_exists' => $order ? $order->exists : 'null',
                'order_id_value' => $order ? $order->id : 'null'
            ]);
            return Response::json(['status' => false, 'message' => 'Order not found'], 404);
        }

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_processed'),
            'type' => 'success'
        ]);
        return  Response::json(['status' => true], 200);
    }

    function convertArabicNumbersToWestern($input) {
        $easternArabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $westernArabic = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($easternArabic, $westernArabic, $input);
    }

    public function showSubscriptionPOS($id)
    {
        $title = trans('sw.invoice');
        $order = GymMemberSubscription::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->with(['pay_type', 'member' => function($q){$q->withTrashed();}, 'subscription' => function($q){$q->withTrashed();}])->where('id', $id)->first();
        $vat = 0;
        if(@$this->mainSettings->vat_details['saudi']){

            $qrcodes_folder = base_path('uploads/invoices/');
            if (!File::exists($qrcodes_folder)) {
                File::makeDirectory($qrcodes_folder, 0755, true, true);
            }
            File::cleanDirectory($qrcodes_folder.'*.png');
            $vat = ($order->vat);
            $generatedQRString = GenerateQrCode::fromArray([
                new Seller(@$this->mainSettings->name), // seller name
                new TaxNumber(@$this->mainSettings->vat_details['vat_number']), // seller tax number
                new InvoiceDate(Carbon::parse($order['created_at'])->format('c')), // invoice date as Zulu ISO8601 @see https://en.wikipedia.org/wiki/ISO_8601
                new InvoiceTotalAmount(number_format($order['amount_paid'],2)), // invoice total amount
                new InvoiceTaxAmount(number_format(@$vat,2)) // invoice tax amount
                // TODO :: Support others tags
            ])->toBase64();

            $d = new DNS2D();
            $d->setStorPath($qrcodes_folder);
            $qr_img_invoice = $d->getBarcodePNGPath($generatedQRString, TypeConstants::QRCodeType);
            $qr_img_invoice = str_replace(base_path(), '', $qr_img_invoice);
        }
        return view('software::Front.order_subscription_front_pos_show', ['order' => $order, 'qr_img_invoice' => @$qr_img_invoice, 'title'=>$title, 'vat'=>$vat]);
    }
    public function showPTSubscription($id)
    {
        $title = trans('sw.subscription_contract');
        $order = GymPTMember::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->with(['member', 'pt_subscription'])->where('id', $id)->first();
        $money_box = GymMoneyBox::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('member_id', $order->member_id)->where('member_pt_subscription_id', $order->id)->get();
        $order->subscription = @$order->pt_subscription;
        $payment_types = GymPaymentType::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->get();

        $money_box->filter(function ($item) use ($payment_types) {
            foreach ($payment_types as $payment_type){
                if($item->payment_type == $payment_type->payment_id){ $this->payments[$payment_type->payment_id][] = ($item->operation == 1 ? -1 : 1) * $item->amount; }
            }
//            if($item->payment_type == TypeConstants::CASH_PAYMENT){ $this->cash_payment[] = ($item->operation == 1 ? -1 : 1) * $item->amount; }
//            if($item->payment_type == TypeConstants::ONLINE_PAYMENT){ $this->online_payment[] = ($item->operation == 1 ? -1 : 1) * $item->amount; }
//            if($item->payment_type == TypeConstants::BANK_TRANSFER_PAYMENT){ $this->bank_transfer_payment[] = ($item->operation == 1 ? -1 : 1) * $item->amount; }
        });
        $payments = [];
        foreach ($payment_types as $i => $payment_type){
            $payments[$i]['payment'] = is_array($this->payments[$payment_type->payment_id] ?? null) ? array_sum($this->payments[$payment_type->payment_id]) : 0;
            $payments[$i]['name'] = $payment_type->name;
        }
//        $cash_payment = array_sum($this->cash_payment);
//        $online_payment = array_sum($this->online_payment);
//        $bank_transfer_payment = array_sum($this->bank_transfer_payment);

        $qrcodes_folder = base_path('uploads/invoices/');
        if (!File::exists($qrcodes_folder)) {
            File::makeDirectory($qrcodes_folder, 0755, true, true);
        }
        File::cleanDirectory($qrcodes_folder.'*.png');

        $vat = 0;
        if(@$this->mainSettings->vat_details['saudi']){
            $vat = ($order->vat);
            $generatedQRString = GenerateQrCode::fromArray([
                new Seller(@$this->mainSettings->name), // seller name
                new TaxNumber(@$this->mainSettings->vat_details['vat_number']), // seller tax number
                new InvoiceDate(Carbon::parse($order['created_at'])->format('c')), // invoice date as Zulu ISO8601 @see https://en.wikipedia.org/wiki/ISO_8601
                new InvoiceTotalAmount(number_format($order['amount_paid'],2)), // invoice total amount
                new InvoiceTaxAmount(number_format(@$vat,2)) // invoice tax amount
                // TODO :: Support others tags
            ])->toBase64();

            $d = new DNS2D();
            $d->setStorPath($qrcodes_folder);
            $qr_img_invoice = $d->getBarcodePNGPath($generatedQRString, TypeConstants::QRCodeType);
            $qr_img_invoice = str_replace(base_path(), '', $qr_img_invoice);
        }
        return view('software::Front.order_subscription_front_show', ['order' => $order, 'qr_img_invoice' => @$qr_img_invoice, 'title'=>$title, 'vat' => $vat
//            ,'cash_payment' => $cash_payment, 'online_payment' => $online_payment, 'bank_transfer_payment' => $bank_transfer_payment
            , 'payments' => $payments
        ]);
    }


    public function showPTSubscriptionPOS($id)
    {
        $title = trans('sw.invoice');
        $order = GymPTMember::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->with(['member', 'pt_subscription'])->where('id', $id)->first();
        $order->subscription = @$order->pt_subscription;
        $vat = 0;
        if(@$this->mainSettings->vat_details['saudi']){

            $qrcodes_folder = base_path('uploads/invoices/');
            if (!File::exists($qrcodes_folder)) {
                File::makeDirectory($qrcodes_folder, 0755, true, true);
            }
            File::cleanDirectory($qrcodes_folder.'*.png');
            $vat = ($order->vat);
            $generatedQRString = GenerateQrCode::fromArray([
                new Seller(@$this->mainSettings->name), // seller name
                new TaxNumber(@$this->mainSettings->vat_details['vat_number']), // seller tax number
                new InvoiceDate(Carbon::parse($order['created_at'])->format('c')), // invoice date as Zulu ISO8601 @see https://en.wikipedia.org/wiki/ISO_8601
                new InvoiceTotalAmount(number_format($order['amount_paid'],2)), // invoice total amount
                new InvoiceTaxAmount(number_format(@$vat,2)) // invoice tax amount
                // TODO :: Support others tags
            ])->toBase64();

            $d = new DNS2D();
            $d->setStorPath($qrcodes_folder);
            $qr_img_invoice = $d->getBarcodePNGPath($generatedQRString, TypeConstants::QRCodeType);
            $qr_img_invoice = str_replace(base_path(), '', $qr_img_invoice);
        }
        return view('software::Front.order_subscription_front_pos_show', ['order' => $order, 'qr_img_invoice' => @$qr_img_invoice, 'title'=>$title, 'vat'=>$vat]);
    }


    public function showSubscriptionNonMember($id)
    {
        $title = trans('sw.invoice');
        $order = GymNonMember::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->with('zatcaInvoice')->where('id', $id)->first();
        $money_box = GymMoneyBox::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('non_member_subscription_id', $order->id)->get();
        $payment_types = GymPaymentType::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->get();
        $order->member = (object)["name" => $order->name];
        $name_activities = [];
        foreach ($order->activities as $activity){   $name_activities[] = $activity['name'];}
        $order->subscription = (object)["name" => implode(', ', $name_activities)];
        $order->amount_paid = $order->price;
        $vat = $order->vat;
        $payments = [];
//        foreach ($payment_types as $payment_type){
//            if($order->payment_type == $payment_type->payment_id){ $this->payments[$payment_type->payment_id][] = $order->amount_paid; }
//        }
        $money_box->filter(function ($item) use ($payment_types){
            foreach ($payment_types as $payment_type){
                if($item->payment_type == $payment_type->payment_id){ $this->payments[$payment_type->payment_id][] = ($item->operation == 1 ? -1 : 1) * $item->amount; }
            }
        });

        $payments = [];
        foreach ($payment_types as $i => $payment_type){
            $payments[$i]['payment'] = is_array($this->payments[$payment_type->payment_id] ?? null) ? array_sum($this->payments[$payment_type->payment_id]) : 0;
            $payments[$i]['name'] = $payment_type->name;
        }
//        $cash_payment = $order->payment_type == TypeConstants::CASH_PAYMENT ? $order->amount_paid : 0;
//        $online_payment = $order->payment_type == TypeConstants::ONLINE_PAYMENT ? $order->amount_paid : 0;
//        $bank_transfer_payment = $order->payment_type == TypeConstants::BANK_TRANSFER_PAYMENT ? $order->amount_paid : 0;

        $invoice = $order->zatcaInvoice;
        if ($invoice && $invoice->zatca_qr_code) {
            $qr_img_invoice = 'data:image/png;base64,' . $invoice->zatca_qr_code;
        } elseif (@$this->mainSettings->vat_details['saudi']) {
            $qrcodes_folder = base_path('uploads/invoices/');
            File::cleanDirectory($qrcodes_folder.'*.png');
            $generatedQRString = GenerateQrCode::fromArray([
                new Seller(@$this->mainSettings->name),
                new TaxNumber(@$this->mainSettings->vat_details['vat_number']),
                new InvoiceDate(Carbon::parse($order['created_at'])->format('c')),
                new InvoiceTotalAmount(number_format($order['price'],2)),
                new InvoiceTaxAmount(number_format(@$vat,2))
            ])->toBase64();

            $d = new DNS2D();
            $d->setStorPath($qrcodes_folder);
            $qr_img_invoice = $d->getBarcodePNGPath($generatedQRString, TypeConstants::QRCodeType);
            $qr_img_invoice = str_replace(base_path(), '', $qr_img_invoice);
        }
        
        // Load upcoming reservations for non-member
        $upcomingReservations = collect();
        if ($order->id) {
            $upcomingReservations = GymReservation::query()
                ->branch(@$this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)
                ->where('non_member_id', $order->id)
                ->where('client_type', 'non_member')
                ->whereDate('reservation_date', '>=', Carbon::now()->toDateString())
                ->whereNotIn('status', ['cancelled', 'missed'])
                ->with(['activity' => function($q) { $q->withTrashed(); }])
                ->orderBy('reservation_date', 'asc')
                ->orderBy('start_time', 'asc')
                ->get();
        }
        
        return view('software::Front.order_subscription_front_show', ['order' => $order, 'invoice' => $invoice, 'qr_img_invoice' => @$qr_img_invoice, 'title'=>$title, 'vat' => $vat
//            ,'cash_payment' => $cash_payment, 'online_payment' => $online_payment, 'bank_transfer_payment' => $bank_transfer_payment
        , 'payments' => $payments, 'upcomingReservations' => $upcomingReservations
        ]);
    }

    public function showSubscriptionPOSNonMember($id)
    {
        $title = trans('sw.invoice');
        $order = GymNonMember::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->with('zatcaInvoice')->where('id', $id)->first();
        $order->member = (object)["name" => $order->name];
        $order->subscription = (object)["name" => implode(', ', $order->activities[$this->lang = 'ar' ? 0 : 1])];
        $order->amount_paid = $order->price;
        $order->amount_remaining = 0;
        $vat = $order->vat;
        $invoice = $order->zatcaInvoice;
        if ($invoice && $invoice->zatca_qr_code) {
            $qr_img_invoice = 'data:image/png;base64,' . $invoice->zatca_qr_code;
        } elseif (@$this->mainSettings->vat_details['saudi']) {
            $qrcodes_folder = base_path('uploads/invoices/');
            if (!File::exists($qrcodes_folder)) {
                File::makeDirectory($qrcodes_folder, 0755, true, true);
            }
            File::cleanDirectory($qrcodes_folder.'*.png');
            $generatedQRString = GenerateQrCode::fromArray([
                new Seller(@$this->mainSettings->name), // seller name
                new TaxNumber(@$this->mainSettings->vat_details['vat_number']), // seller tax number
                new InvoiceDate(Carbon::parse($order['created_at'])->format('c')), // invoice date as Zulu ISO8601 @see https://en.wikipedia.org/wiki/ISO_8601
                new InvoiceTotalAmount(number_format($order['price'],2)), // invoice total amount
                new InvoiceTaxAmount(number_format(@$vat,2)) // invoice tax amount
                // TODO :: Support others tags
            ])->toBase64();

            $d = new DNS2D();
            $d->setStorPath($qrcodes_folder);
            $qr_img_invoice = $d->getBarcodePNGPath($generatedQRString, TypeConstants::QRCodeType);
            $qr_img_invoice = str_replace(base_path(), '', $qr_img_invoice);
        }
        return view('software::Front.order_subscription_front_pos_show', ['order' => $order, 'invoice' => $invoice, 'qr_img_invoice' => @$qr_img_invoice, 'title'=>$title, 'vat'=>$vat]);
    }
    public function create()
    {
        $title = trans('admin.order_add');
        $subscriptions = GymSubscription::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->whereHas('member',function ($q){
            $q->where('user_id', $this->user->id);
        })->get();
        return view('software::Front.user.gymorder_front_form',
            [
                'gymorder' => new GymOrder(),
                'title' => $title,
                'subscriptions' => $subscriptions
            ]);
    }

    public function store($memberId, GymOrderRequest $request)
    {
        $gymorder_inputs = $this->prepare_inputs($request->except(['_token']));
        $gymorder_inputs['member_id'] = $memberId;
        $dateFrom = Carbon::parse($request->date_from);
        $dateTo = $request->date_to;
        $subscription = GymSubscription::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->select(['duration', 'price'])->where('id', $request->subscription_id)->first();
        if (!$dateTo) {
            $dateTo = $dateFrom->copy()->addDays($subscription->duration);
        } else
            $dateTo = Carbon::parse($dateTo);

        if (!$gymorder_inputs['price']) $gymorder_inputs['price'] = $subscription->price;
        $gymorder_inputs['date_from'] = $dateFrom;
        $gymorder_inputs['date_to'] = $dateTo;

        $this->GymOrderRepository->create($gymorder_inputs);
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);
        return redirect(route('listUserGymOrder'));
    }

    public function edit($id)
    {
        $gymorder = $this->GymOrderRepository->withTrashed()->find($id);
        $title = trans('admin.order_edit');
        return view('software::Front.user.gymorder_front_form', ['gymorder' => $gymorder, 'title' => $title]);
    }

    public function update(GymOrderRequest $request, $id)
    {
        $gymorder = $this->GymOrderRepository->withTrashed()->find($id);
        $gymorder_inputs = $this->prepare_inputs($request->except(['_token']));
        $gymorder->update($gymorder_inputs);
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        return redirect(route('listUserGymOrder'));
    }

    public function destroy($id)
    {
        $gymorder = $this->GymOrderRepository->withTrashed()->find($id);
        if ($gymorder->trashed()) {
            $gymorder->restore();
        } else {
            $gymorder->delete();
        }
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('listUserGymOrder'));
    }

    public function showAll()
    {
        $orders = $this->GymOrderRepository->select(['id', 'member_id', 'subscription_id',  'date_from', 'date_to', 'price', 'deleted_at'])->with(['member' => function ($q) {
            $q->select(['id', 'name']);
        }])->whereHas('member',function ($q){
            $q->where('user_id', $this->user->id);
        });

        if (request()->get('trashed') == 1) {
            $orders = $orders->onlyTrashed();
        }
        $ret['data'] = $orders->orderBy('id', 'DESC')->get()->toArray();

        return $ret;

    }

    public function uploadContractGymOrder(){

        $input_file = 'contract_file';
        $destinationPath = base_path(GymMoneyBox::$uploads_path.'/');
        $order_id = @request('order_id');
        if(@request('type') == 'sw.showOrderSubscriptionNonMember'){
            $order = GymNonMember::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('id', $order_id)->first();
            $order_contract_files = (array)$order->contract_files;
            $prefix = 'non-member';
            $log_type = TypeConstants::UploadContractFileNonMember;
        }elseif(@request('type') == 'sw.showOrderPTSubscription'){
            $order = GymPTMember::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('id', $order_id)->first();
            $order_contract_files = (array)$order->contract_files;
            $log_type = TypeConstants::UploadContractFilePTMember;
            $prefix = 'pt-member';
        }else{
            $order = GymMemberSubscription::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('id', $order_id)->first();
            $order_contract_files = (array)$order->contract_files;
            $log_type = TypeConstants::UploadContractFileMember;
            $prefix = 'member';
        }

//        $max_images = (int)env('MAX_WEBSITE_IMAGES') ? (int)env('MAX_WEBSITE_IMAGES') : 1;
//
//        if(count($order_contract_files) > $max_images){
//            return 1;
//        }


        if (request()->hasFile($input_file)) {
            $file = request()->file($input_file);

            if (file_exists($file->getRealPath()) && getimagesize($file->getRealPath()) !== false) {
                $filename = rand(0, 20000) . time() . '-' .  $prefix.'-'.$order->id . '.' . $file->getClientOriginalExtension();


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
                    $img->toJpeg(80)->save($destinationPath . $filename);
                    $img->scale(width: $new_width, height: $new_height)->toJpeg(80)->save($destinationPath . '' . $filename);

                } else {
                    //save used image
                    $img->toJpeg(80)->save($destinationPath . $filename);

                }
                $inputs[$input_file] = $uploaded;

//                array_push($order_contract_files, $filename);
//                $order->contract_files = $order_contract_files;
                $order->contract_files = (array)$filename;
                $order->save();

                $notes = trans('sw.uploaded_subscription_contract_id', ['order_id' => @$order->id]);
                $this->userLog($notes, $log_type);

                return asset(('./uploads/gymorders/' . $filename));
            }
        }
        return 0;

    }

    private function prepare_inputs($inputs)
    {
        $input_file = 'image';
        $uploaded = '';

        $destinationPath = base_path(GymOrder::$uploads_path);
        $ThumbnailsDestinationPath = base_path(GymOrder::$thumbnails_uploads_path);

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
                $inputs[$input_file] = $uploaded;
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
            $inputs['tenant_id'] = @$this->user_sw->tenant_id;
        }

//        !$inputs['deleted_at']?$inputs['deleted_at']=null:'';

        return $inputs;
    }
}


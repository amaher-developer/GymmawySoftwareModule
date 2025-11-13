<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Software\Classes\TypeConstants;
use Modules\Software\Classes\LoyaltyService;
use Modules\Software\Exports\RecordsExport;
use Modules\Software\Http\Requests\GymStoreOrderRequest;
use Modules\Software\Models\GymGroupDiscount;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymMemberCredit;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Models\GymStoreOrder;
use Modules\Software\Models\GymStoreOrderProduct;
use Modules\Software\Models\GymStoreOrderVendor;
use Modules\Software\Models\GymStoreProduct;
use Modules\Software\Models\GymStoreGroup;
use Modules\Software\Repositories\GymStoreOrderRepository;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Illuminate\Container\Container as Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use Milon\Barcode\DNS2D;
use Salla\ZATCA\GenerateQrCode;
use Salla\ZATCA\Tags\InvoiceDate;
use Salla\ZATCA\Tags\InvoiceTaxAmount;
use Salla\ZATCA\Tags\InvoiceTotalAmount;
use Salla\ZATCA\Tags\Seller;
use Salla\ZATCA\Tags\TaxNumber;

class GymStoreOrderFrontController extends GymGenericFrontController
{
    public $StoreOrderRepository;

    public function __construct()
    {
        parent::__construct();
        $this->StoreOrderRepository=new GymStoreOrderRepository(new Application);
        $this->StoreOrderRepository=$this->StoreOrderRepository->branch();
    }


    public function index()
    {
        $title = trans('sw.sales_invoices');
        $this->request_array = ['search', 'date', 'from', 'to'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $orders = $this->StoreOrderRepository->with('order_product.product')->onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $orders = $this->StoreOrderRepository->with(['order_product.product'=>function($q){
//                $q->withTrashed();
            }])->orderBy('id', 'DESC');
        }

        //apply filters
        $orders->when($search, function ($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('id', '=', $search);
            });
//            $query->orWhere('name_'.$this->lang,'like', "%".$search."%");
        });
        $orders->when(($from), function ($query) use ($from) {
            $query->whereDate('created_at', '>=', Carbon::parse($from)->format('Y-m-d'));
        })->when(($to), function ($query) use ($to) {
            $query->whereDate('created_at', '<=', Carbon::parse($to)->format('Y-m-d'));
        });
//        $orders->when(@$date, function ($query) use ($date) {
//            $query->whereDate('created_at', '=', Carbon::parse($date)->toDateString());
//        });
        $search_query = request()->query();

        if ($this->limit) {
            $orders = $orders->paginate($this->limit)->onEachSide(1);
            $total = $orders->total();
        } else {
            $orders = $orders->get();
            $total = $orders->count();
        }
        return view('software::Front.store_order_front_list', compact('orders','title', 'total', 'search_query'));
    }


    function exportStoreOrderExcel(){
        $this->limit = null;
        $records = $this->index()->with(\request()->all());
        $records = $records->orders;

        $this->fileName = 'store_orders-' . Carbon::now()->toDateTimeString();

//        $title = trans('sw.store_orders');
//        $records = $this->prepareForExport($records);


        $notes = trans('sw.export_excel_store_orders');
        $this->userLog($notes, TypeConstants::ExportStoreOrderExcel);

        return Excel::download(new RecordsExport(['records' => $records, 'keys' => ['id', 'amount_paid'],'lang' => $this->lang]), $this->fileName.'.xlsx');

//        Excel::create($this->fileName, function($excel) use ($records, $title) {
//            $excel->setTitle($title);
////            $excel->setCreator($title)->setCompany($title);
//            $excel->setDescription(trans('sw.store_orders_data'));
//            $excel->sheet(trans('sw.store_orders_data'), function($sheet) use ($records) {
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

//    private function prepareForExport($data)
//    {
//        $name = [trans('sw.name'), trans('sw.price')];
//        $result = array_map(function ($row) {
//            return [
//                trans('sw.name') => $row['name']
//            ];
//        }, $data->toArray());
//        array_unshift($result, $name);
//        array_unshift($result, [trans('sw.store_orders')]);
//        return $result;
//    }
    function exportStoreOrderPDF(){
//        $records = $this->StoreOrderRepository->get();
//        $this->fileName = 'store_orders-' . Carbon::now()->toDateTimeString();

        $this->limit = null;
        $records = $this->index()->with(\request()->all());
        $records = $records->orders;
        $this->fileName = 'stoer-orders-' . Carbon::now()->toDateTimeString();

        $keys = ['id', 'amount_paid'];
        if($this->lang == 'ar') $keys = array_reverse($keys);

        $title = trans('sw.store_orders');
        $pdf = PDF::loadView('software::Front.export_pdf', ['records' => $records, 'title' => $title, 'keys' => $keys]);

        $notes = trans('sw.export_pdf_store_orders');
        $this->userLog($notes, TypeConstants::ExportStoreOrderPDF);

        return $pdf->download($this->fileName.'.pdf');
    }


    public function show($id)
    {
        $title = trans('sw.invoice');
        $orderModel = GymStoreOrder::branch()
            ->with(['member', 'loyaltyRedemption.rule', 'zatcaInvoice'])
            ->where('id', $id)
            ->firstOrFail();

        $order = $orderModel->toArray();
        $invoice = $orderModel->zatcaInvoice;

        foreach ($order['products'] as $i => $product_id){
            $order['products'][$i]['details'] = GymStoreProduct::branch()->where('id', $product_id)->withTrashed()->first()->toArray();
        }

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
                new InvoiceTotalAmount(number_format($order['amount_paid'],2)), // invoice total amount
                new InvoiceTaxAmount(@number_format($order['vat'],2)) // invoice tax amount
                // TODO :: Support others tags
            ])->toBase64();

            $d = new DNS2D();
            $d->setStorPath($qrcodes_folder);
            $qr_img_invoice = $d->getBarcodePNGPath($generatedQRString, TypeConstants::QRCodeType);
            $qr_img_invoice = str_replace(base_path(), '', $qr_img_invoice);
        }
        return view('software::Front.store_order_front_show', [
            'order' => $order,
            'invoice' => $invoice,
            'qr_img_invoice' => @$qr_img_invoice,
            'title' => $title,
        ]);
    }

    public function showPOS($id)
    {
        $title = trans('sw.invoice');
        $orderModel = GymStoreOrder::branch()
            ->with(['pay_type', 'member', 'loyaltyRedemption.rule', 'zatcaInvoice'])
            ->where('id', $id)
            ->firstOrFail();

        $order = $orderModel->toArray();
        $invoice = $orderModel->zatcaInvoice;

        foreach ($order['products'] as $i => $product_id){
            $order['products'][$i]['details'] = GymStoreProduct::branch()->where('id', $product_id)->withTrashed()->first()->toArray();
        }

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
                new InvoiceTotalAmount(number_format($order['amount_paid'],2)), // invoice total amount
                new InvoiceTaxAmount(@number_format($order['vat'],2)) // invoice tax amount
                // TODO :: Support others tags
            ])->toBase64();

            $d = new DNS2D();
            $d->setStorPath($qrcodes_folder);
            $qr_img_invoice = $d->getBarcodePNGPath($generatedQRString, TypeConstants::QRCodeType);
            $qr_img_invoice = str_replace(base_path(), '', $qr_img_invoice);
        }
        return view('software::Front.store_order_front_pos_show', [
            'order' => $order,
            'invoice' => $invoice,
            'qr_img_invoice' => @$qr_img_invoice,
            'title' => $title,
        ]);
    }

    public function create()
    {
        $title = trans('sw.sell_products');
        $products = GymStoreProduct::branch()->isSystem();
        if(@env('STORE_ACTIVE_QUANTITY'))
            $products = $products->where('quantity', '>', 0);

        $products = $products->get();
        $last_order_id = @GymStoreOrder::branch()->orderBy('id', 'desc')->first()->id;
        $discounts = GymGroupDiscount::branch()->where('is_store', true)->get();
        return view('software::Front.store_order_front_form', ['order' => new GymStoreOrder(), 'discounts' => $discounts, 'products' => $products, 'title'=>$title, 'last_order_id' => @$last_order_id]);
    }

    public function createPOS()
    {
        $title = trans('sw.sell_products_pos');
        $products = GymStoreProduct::branch()->isSystem()->with(['store_category', 'category']);
        if(@env('STORE_ACTIVE_QUANTITY'))
            $products = $products->where('quantity', '>', 0);

        $products = $products->get();
        
        // Get all categories
        $allCategories = \Modules\Software\Models\GymStoreCategory::branch()->get();
        
        // Filter to only show categories that have products (check both store_category_id and category_id)
        $categories = $allCategories->filter(function($category) use ($products) {
            return $products->filter(function($p) use ($category) {
                return ($p->store_category_id == $category->id || $p->category_id == $category->id);
            })->count() > 0;
        });
            
        $members = GymMember::branch()->get();
        $payment_types = \Modules\Software\Models\GymPaymentType::all();
        
        return view('software::Front.store_order_pos_front_form', compact('products', 'categories', 'members', 'payment_types', 'title'));
    }

    public function storePOS(GymStoreOrderRequest $request)
    {
        $vat = 0;
        $order_inputs = $this->prepare_inputs($request->except(['_token']));
        $input_products = $request->products;


        $amount_before_discount = 0;
        if(is_array($input_products['id']) && count($input_products['id']) > 0) {
            foreach ($input_products['id'] as $key => $product_id) {
                $product = GymStoreProduct::branch()->where('id', $product_id)->first();
                if (@$product)
                    $amount_before_discount += $product->price * $input_products['quantity'][$key];
            }
        }
//        foreach($products as $key => $product){
//            dd($input_products['id'][$key], $product->id);
//            if(@$input_products['id'][$key] == $product->id)
//                $amount_before_discount += $product->price * $input_products['quantity'][$key];
//        }
        $order_inputs['amount_before_discount'] = $amount_before_discount;
        $order_inputs['discount_value'] = $request->discount_value ? @$request->discount_value : 0;

        if($request->member_id){
            if(!isset($member)) {
                $member = GymMember::branch()->where('id', (int)@$request->member_id)->first();
            }
            $order_inputs['member_id'] = @$member->id;

            if(@$request->store_member_use_balance && ((@$member->member_balance() < $order_inputs['amount_paid'] ) && (!@$this->mainSettings->store_postpaid))){
                return redirect(route('sw.createStoreOrderPOS'))->withErrors(['amount_paid' => trans('sw.amount_paid_validate_must_less_balance')]);
            }

        }

        // Handle loyalty points redemption - MUST BE BEFORE VAT CALCULATION
        $loyaltyDiscountValue = 0;
        $loyaltyPointsRedeemed = 0;
        $redemptionTransaction = null;
        if($request->member_id && $request->loyalty_points_redeem && @$this->mainSettings->active_loyalty){
            if(!isset($member)) {
                $member = GymMember::find($request->member_id);
            }
            $loyaltyPointsRedeemed = (int)$request->loyalty_points_redeem;
            
            if($member && $loyaltyPointsRedeemed > 0){
                try {
                    // Get active loyalty rule to calculate conversion rate
                    $loyaltyRule = \Modules\Software\Models\LoyaltyPointRule::active()
                        ->where('branch_setting_id', $member->branch_setting_id ?? 1)
                        ->first();
                    
                    if (!$loyaltyRule) {
                        throw new \Exception(trans('sw.no_active_loyalty_rule'));
                    }
                    
                    // Calculate maximum usable discount (subtotal after regular discount)
                    $maxUsableDiscount = $order_inputs['amount_before_discount'] - $order_inputs['discount_value'];
                    $maxUsableDiscount = max(0, $maxUsableDiscount); // Ensure non-negative
                    
                    // Calculate maximum redeemable points based on max discount
                    $maxRedeemablePoints = 0;
                    if ($loyaltyRule->point_to_money_rate > 0) {
                        $maxRedeemablePoints = (int) floor($maxUsableDiscount / $loyaltyRule->point_to_money_rate);
                    }
                    
                    // Cap redemption points to maximum usable
                    if ($loyaltyPointsRedeemed > $maxRedeemablePoints && $maxRedeemablePoints > 0) {
                        $loyaltyPointsRedeemed = $maxRedeemablePoints;
                    }
                    
                    // Only redeem if we have valid points
                    if($loyaltyPointsRedeemed > 0){
                        $loyaltyService = new LoyaltyService();
                        $redemptionResult = $loyaltyService->redeem(
                            $member,
                            $loyaltyPointsRedeemed,
                            trans('sw.redeemed_for_store_order'),
                            'store_order_redemption',
                            null // Will update with order ID after creation
                        );
                        
                        if($redemptionResult){
                            $loyaltyDiscountValue = $redemptionResult['value'];
                            // Cap discount value to maximum usable (safety check)
                            if ($loyaltyDiscountValue > $maxUsableDiscount) {
                                $loyaltyDiscountValue = $maxUsableDiscount;
                            }
                            // Update the redemption transaction with the order ID after order creation
                            $redemptionTransaction = $redemptionResult['transaction'];
                        }
                    }
                } catch (\Exception $e) {
                    return redirect(route('sw.createStoreOrderPOS'))->withErrors(['loyalty_points_redeem' => $e->getMessage()]);
                }
            }
        }
        
        // Calculate VAT after applying both regular discount and loyalty discount
        if(@$this->mainSettings->vat_details['vat_percentage']) {
            $vat = ($order_inputs['amount_before_discount'] - $order_inputs['discount_value'] - $loyaltyDiscountValue) * (@$this->mainSettings->vat_details['vat_percentage'] / 100);
            $order_inputs['vat'] = round($vat, 2);
        }
        
        $order_inputs['amount_remaining'] = ($order_inputs['amount_before_discount'] + $vat - $order_inputs['amount_paid'] - $order_inputs['discount_value'] - $loyaltyDiscountValue) > 0 ? round(($order_inputs['amount_before_discount'] + $vat - $order_inputs['amount_paid'] - $order_inputs['discount_value'] - $loyaltyDiscountValue), 2) : 0;
//        $order_inputs['amount_paid'] = @(float)$request->amount_paid+@(float)$request->vat;
//        $order_inputs['vat'] = @(float)$request->vat;
        $order_inputs['products'] = ($this->remapProducts($order_inputs['products']));
        $order_inputs['user_id'] = $this->user_sw->id;
        $order_inputs['branch_setting_id'] = $this->user_sw->branch_setting_id;

        $subscription_price = round(($order_inputs['amount_before_discount'] - @$order_inputs['discount_value'] + $vat), 2);

        if (($order_inputs['amount_paid'] < 0) || @($order_inputs['amount_paid'] > $subscription_price)) {
            return redirect(route('sw.createStoreOrder'))->withErrors(['amount_paid' => trans('sw.amount_paid_validate_must_less')]);
        }

        if(empty($order_inputs['products'])){
            session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.something_wrong'),
            'type' => 'error'
        ]);
            return redirect(route('sw.createStoreOrder'));
        }

        $order = GymStoreOrder::create($order_inputs);
        $order_id = $order->id;
        
        // Update redemption transaction with order ID if points were redeemed
        if (isset($redemptionTransaction) && $redemptionTransaction) {
            $redemptionTransaction->source_id = $order->id;
            $redemptionTransaction->save();
        }
        
        foreach($order_inputs['products'] as $product) {
            GymStoreOrderProduct::create(['order_id' => $order->id, 'product_id' => $product['id'], 'quantity' => $product['quantity'], 'price' => ($product['quantity'] * $product['price']), 'branch_setting_id' => @$this->user_sw->branch_setting_id]);

            GymStoreProduct::where('id', $product['id'])
                ->update([
                    'quantity' => DB::raw('quantity - '.$product['quantity'])
                ]);
        }
        // Award loyalty points if member made the purchase
        $loyaltyPointsEarned = 0;
        if (isset($member) && $order->amount_paid > 0 && @$this->mainSettings->active_loyalty) {
            try {
                $loyaltyService = new LoyaltyService();
                $transaction = $loyaltyService->earn(
                    $member,
                    $order->amount_paid,
                    'store_order',
                    $order->id
                );
                
                if ($transaction) {
                    $loyaltyPointsEarned = $transaction->points;
                }
            } catch (\Exception $e) {
                \Log::error('Failed to award loyalty points for store order', [
                    'order_id' => $order->id,
                    'member_id' => $member->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Success message with loyalty points info
        $successMessage = trans('admin.successfully_added');
        if ($loyaltyPointsRedeemed > 0) {
            $successMessage .= ' - ' . trans('sw.redeemed_loyalty_points', [
                'points' => $loyaltyPointsRedeemed,
                'value' => number_format($loyaltyDiscountValue, 2)
            ]);
        }
        if ($loyaltyPointsEarned > 0) {
            $successMessage .= ' - ' . trans('sw.earned_loyalty_points', ['points' => $loyaltyPointsEarned]);
        }

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => $successMessage,
            'type' => 'success'
        ]);

        $notes = trans('sw.add_store_order', ['price' => $order->amount_paid, 'username' => $member->name ?? trans('sw.guest')]);
        if($this->mainSettings->vat_details['vat_percentage']){
            $notes = $notes.' - '.trans('sw.vat_added');
        }
        $is_store_balance = 0;
        if(@$request->store_member_use_balance && @$member){
            $notes = $notes.' - '.trans('sw.use_from_balance');

            GymMemberCredit::create(['branch_setting_id' => @$this->user_sw->branch_setting_id, 'user_id' => Auth::guard('sw')->user()->id,'member_id' => @$member->id, 'amount' => @$order_inputs['amount_paid'],'operation' => 2,'payment_type' => @$order_inputs['payment_type']]);

            if($member->member_balance() >= 0)
                $is_store_balance = 1;
            else
                $is_store_balance = 2;

            $member->store_balance = $member->member_balance();
            $member->save();

        }
        $amount_box = GymMoneyBox::branch()->latest()->first();
        $amount_after = $amount_box ? GymMoneyBoxFrontController::amountAfter($amount_box->amount, $amount_box->amount_before, $amount_box->operation) : 0;

        GymMoneyBox::create([
            'user_id' => Auth::guard('sw')->user()->id
            , 'amount' => @$order_inputs['amount_paid']
            , 'vat' => @$order_inputs['vat']
            , 'operation' => TypeConstants::Add
            , 'amount_before' => $amount_after
            , 'notes' => $notes
            , 'type' => TypeConstants::CreateStoreOrder
            , 'member_id' => @$member->id
            , 'payment_type' => @$order_inputs['payment_type']
            , 'branch_setting_id' => @$this->user_sw->branch_setting_id
            , 'store_order_id' => $order_id
            , 'is_store_balance' => $is_store_balance
        ]);
        $this->userLog($notes, TypeConstants::CreateStoreOrder);

        // ✅ Create ZATCA Invoice if enabled (Phase 2)
        if (config('sw_billing.zatca_enabled') && config('sw_billing.auto_invoice')) {
            try {
                \Log::info('Attempting to create ZATCA invoice', [
                    'order_id' => $order->id,
                    'zatca_enabled' => config('sw_billing.zatca_enabled'),
                    'auto_invoice' => config('sw_billing.auto_invoice'),
                ]);
                
                $invoice = \Modules\Billing\Services\SwBillingService::createInvoiceFromStoreOrder($order);
                
                // if ($invoice) {
                //     \Log::info('ZATCA invoice created successfully', [
                //         'invoice_id' => $invoice->id,
                //         'order_id' => $order->id,
                //     ]);
                // } else {
                //     \Log::warning('ZATCA invoice creation returned null', [
                //         'order_id' => $order->id,
                //     ]);
                // }
            } catch (\Exception $e) {
                \Log::error('Failed to create ZATCA invoice for store order', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        return redirect(route('sw.createStoreOrderPOS'));
    }

    private function remapProducts($products){
        $result = [];
        if(count($products['id']) > 0){
            foreach ($products['id'] as $key => $id){
                if(@env('STORE_ACTIVE_QUANTITY')) {
                    $quantity = GymStoreProduct::select('quantity')->where('id', $id)->first();
                    if ($quantity->quantity < $products['quantity'][$key]) {
                        $result = [];
                        break;
                    }
                }
                $result[$key]['id'] = $id;
                $result[$key]['price'] = @$products['price'][$key] ?? 0;
                $result[$key]['quantity'] = $products['quantity'][$key];

//                GymStoreProduct::where('id',$id)->update(['solid_quantity' => $products['quantity'][$key]]);
            }
        }
        return $result;
    }
    public function edit($id)
    {
        $order = $this->StoreOrderRepository->withTrashed()->find($id);
        $title = trans('sw.store_order_edit');

        return view('software::Front.store_order_front_form', ['order' => $order,'title'=>$title]);
    }

    public function update(GymStoreOrderRequest $request, $id)
    {
        $order =$this->StoreOrderRepository->withTrashed()->find($id);
        $order_inputs = $this->prepare_inputs($request->except(['_token']));
        $order->update($order_inputs);

        $notes = trans('sw.edit_store_order', ['name' => $order->name]);

        $this->userLog($notes, TypeConstants::EditStoreOrder);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listStoreOrders'));
    }

    public function destroy($id)
    {
        $order =$this->StoreOrderRepository->withTrashed()->find($id);
        if($order->trashed())
        {
            $order->restore();
        }
        else
        {
            $order->delete();

            $orders = GymStoreOrderProduct::where('order_id', $order->id)->get();
            if (count($orders) > 0) {
                foreach ($orders as $ord) {
                    GymStoreProduct::where('id', $ord->product_id)->increment('quantity', $ord->quantity);
                }
            }
            GymStoreOrderProduct::where('order_id', $order->id)->delete();

            if(\request('refund')){
                $amount_box = GymMoneyBox::branch()->latest()->first();
                $amount_after = GymMoneyBoxFrontController::amountAfter($amount_box->amount, $amount_box->amount_before, $amount_box->operation);

                // Calculate refund amount (full or partial)
                $vat = @$order->vat;
                $refundAmount = $order->amount_paid; // Default to full refund
                $isPartialRefund = false;
                
                if(\request('total_amount') && \request('amount') && (\request('total_amount') >= \request('amount') )){
                    $refundAmount = \request('amount');
                    $isPartialRefund = ($refundAmount < $order->amount_paid);
                }
                
                // Deduct loyalty points if they were awarded for this order
                if ($order->member_id && @$this->mainSettings->active_loyalty) {
                    try {
                        $member = GymMember::find($order->member_id);
                        if ($member) {
                            // Find loyalty transactions for this order
                            $loyaltyTransactions = \Modules\Software\Models\LoyaltyTransaction::where('source_type', 'store_order')
                                ->where('source_id', $order->id)
                                ->where('type', 'earn')
                                ->where('is_expired', false)
                                ->get();
                            
                            $totalPointsEarned = $loyaltyTransactions->sum('points');
                            
                            if ($totalPointsEarned > 0 && $order->amount_paid > 0) {
                                // Check how many points have already been deducted for this order (from previous refunds)
                                $alreadyDeductedPoints = abs(\Modules\Software\Models\LoyaltyTransaction::where('member_id', $member->id)
                                    ->where('type', 'manual')
                                    ->where('source_type', 'store_order_refund')
                                    ->where('source_id', $order->id)
                                    ->where('points', '<', 0)
                                    ->sum('points')) ?? 0; // sum of negative values, so we get positive amount deducted
                                
                                $remainingDeductiblePoints = $totalPointsEarned - $alreadyDeductedPoints;
                                
                                // Calculate proportional points to deduct based on refund ratio
                                $refundRatio = $refundAmount / $order->amount_paid;
                                $pointsToDeduct = (int) round($totalPointsEarned * $refundRatio);
                                
                                // Don't deduct more than what's remaining
                                if ($pointsToDeduct > $remainingDeductiblePoints) {
                                    $pointsToDeduct = max(0, $remainingDeductiblePoints);
                                }
                                
                                if ($pointsToDeduct > 0) {
                                    // Check if member has enough points
                                    if ($member->loyalty_points_balance >= $pointsToDeduct) {
                                        // Deduct points using manual adjustment
                                        $loyaltyService = new LoyaltyService();
                                        
                                        $reason = $isPartialRefund 
                                            ? trans('sw.points_deducted_for_partial_refund', [
                                                'order_id' => $order->id, 
                                                'refund_amount' => $refundAmount,
                                                'original_amount' => $order->amount_paid
                                            ])
                                            : trans('sw.points_deducted_for_refund', ['order_id' => $order->id]);
                                        
                                        // Create the deduction transaction with source tracking
                                        $deductionTransaction = $loyaltyService->addManual(
                                            $member,
                                            -$pointsToDeduct,
                                            $reason,
                                            $this->user_sw->id ?? null
                                        );
                                        
                                        // Update source_type and source_id to track refunds properly
                                        if ($deductionTransaction) {
                                            $deductionTransaction->source_type = 'store_order_refund';
                                            $deductionTransaction->source_id = $order->id;
                                            $deductionTransaction->save();
                                        }
                                        
                                        // Mark original transactions as expired only for full refunds
                                        if (!$isPartialRefund) {
                                            foreach ($loyaltyTransactions as $earnTransaction) {
                                                $earnTransaction->is_expired = true;
                                                $earnTransaction->save();
                                            }
                                        }
                                        
                                        // \Log::info('Loyalty points deducted for refund', [
                                        //     'order_id' => $order->id,
                                        //     'member_id' => $member->id,
                                        //     'points_deducted' => $pointsToDeduct,
                                        //     'total_points_earned' => $totalPointsEarned,
                                        //     'already_deducted_points' => $alreadyDeductedPoints,
                                        //     'remaining_deductible_points' => $remainingDeductiblePoints,
                                        //     'refund_amount' => $refundAmount,
                                        //     'original_amount' => $order->amount_paid,
                                        //     'refund_ratio' => $refundRatio,
                                        //     'is_partial' => $isPartialRefund,
                                        // ]);
                                    } else {
                                        // Member doesn't have enough points
                                        // \Log::warning('Cannot deduct loyalty points - insufficient balance', [
                                        //     'order_id' => $order->id,
                                        //     'member_id' => $member->id,
                                        //     'points_needed' => $pointsToDeduct,
                                        //     'current_balance' => $member->loyalty_points_balance,
                                        //     'refund_amount' => $refundAmount,
                                        // ]);
                                        
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
                        \Log::error('Failed to deduct loyalty points on refund', [
                            'order_id' => $order->id,
                            'refund_amount' => $refundAmount,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                
                $amount = $refundAmount;

                $notes = trans('sw.store_order_delete', ['id' => $order->id,'amount' => $amount]);
                GymMoneyBox::create([
                    'user_id' => Auth::guard('sw')->user()->id
                    , 'amount' => $amount
                    , 'vat' => @$vat
                    , 'operation' => TypeConstants::Sub
                    , 'amount_before' => $amount_after
                    , 'notes' => $notes
                    , 'type' => TypeConstants::DeleteStoreOrder
                    , 'member_id' => $order->member_id
                    , 'branch_setting_id' => @$this->user_sw->branch_setting_id
                    , 'store_order_id' => @$order->id
                ]);
                $this->userLog($notes, TypeConstants::CreateMoneyBoxWithdraw);
                
                
            }

            $notes =  trans('sw.delete_order', ['name' => $order['name']]);
            $this->userLog($notes, TypeConstants::DeleteStoreOrder);
        }
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listStoreOrders'));
    }

    private function prepare_inputs($inputs)
    {
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
    public function getStoreMemberAjax(){
        $member_id = (int)@request('member_id');
        if($member_id){
            $member = GymMember::branch()->with(['member_subscription_info.subscription'])->where('code', $member_id);

            if(strlen($member_id) > 5)
                $member = $member->orWhere('phone', $member_id);

            $member = $member->first();
            if(@$member){$member->balance = @$member->member_balance();}
            return $member;
        }
        return [];
    }
    
    public function getMemberLoyaltyInfo(){
        $member_id = (int)@request('member_id');
        
        // Get active loyalty rule
        $rule = \Modules\Software\Models\LoyaltyPointRule::active()
            ->where('branch_setting_id', $this->user_sw->branch_setting_id ?? 1)
            ->first();
        
        if (!$rule) {
            return response()->json([
                'success' => false,
                'message' => 'No active loyalty rule found',
                'points' => 0,
                'points_formatted' => '0'
            ]);
        }
        
        // Calculate how many points equal 1 currency unit
        $pointsForOneCurrency = $rule->point_to_money_rate > 0 ? (1 / $rule->point_to_money_rate) : 0;
        
        // If no member_id, just return rule info
        if (!$member_id) {
            return response()->json([
                'success' => true,
                'points' => 0,
                'points_formatted' => '0',
                'point_to_money_rate' => (float)$rule->point_to_money_rate,
                'money_to_point_rate' => (float)$rule->money_to_point_rate,
                'points_for_one_currency' => round($pointsForOneCurrency, 2),
                'rule_name' => $rule->name,
            ]);
        }
        
        $member = GymMember::find($member_id);
        
        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Member not found'], 404);
        }
        
        return response()->json([
            'success' => true,
            'points' => $member->loyalty_points_balance ?? 0,
            'points_formatted' => number_format($member->loyalty_points_balance ?? 0),
            'point_to_money_rate' => (float)$rule->point_to_money_rate,
            'money_to_point_rate' => (float)$rule->money_to_point_rate,
            'points_for_one_currency' => round($pointsForOneCurrency, 2),
            'rule_name' => $rule->name,
        ]);
    }

}

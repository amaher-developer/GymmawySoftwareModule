<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Software\Classes\TypeConstants;
use Modules\Software\Exports\RecordsExport;
use Modules\Software\Http\Requests\GymStoreProductRequest;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Models\GymPTSubscription;
use Modules\Software\Models\GymStoreOrderVendor;
use Modules\Software\Models\GymStoreProduct;
use Modules\Software\Models\GymPaymentType;
use Modules\Software\Models\GymStoreCategory;
use Modules\Software\Repositories\GymStoreProductRepository;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Illuminate\Container\Container as Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Maatwebsite\Excel\Facades\Excel;
use Milon\Barcode\DNS1D;

class GymStoreProductFrontController extends GymGenericFrontController
{
    public $StoreProductRepository;
    private $imageManager;

    public function __construct()
    {
        parent::__construct();
        $this->StoreProductRepository=new GymStoreProductRepository(new Application);
        // Repository branch filtering removed from constructor - now applied per query
        $this->imageManager = new ImageManager(new Driver());
    }


    public function index()
    {
        $title = trans('sw.store_products');
        $this->request_array = ['search'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $products = $this->StoreProductRepository->with(['store_category', 'category'])->onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $products = $this->StoreProductRepository->with(['store_category', 'category'])->orderBy('id', 'DESC');
        }

        //apply filters
        $products->when($search, function ($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('id', '=', $search);
                $query->orWhere('name_' . $this->lang, 'like', "%" . $search . "%");
                $query->orWhere('code',  (int)$search );
            });
        });
        $search_query = request()->query();

        if ($this->limit) {
            $products = $products->paginate($this->limit);
            $total = $products->total();
        } else {
            $products = $products->get();
            $total = $products->count();
        }

        return view('software::Front.store_product_front_list', compact('products','title', 'total', 'search_query'));
    }


    function exportExcel(){
        $records = $this->StoreProductRepository->get();
        $this->fileName = 'store_products-' . Carbon::now()->toDateTimeString();

//        $title = trans('sw.store_products');
//        $records = $this->prepareForExport($records);


        $notes = trans('sw.export_excel_store_products');
        $this->userLog($notes, TypeConstants::ExportStoreProductExcel);

        return Excel::download(new RecordsExport(['records' => $records, 'keys' => ['name', 'price'],'lang' => $this->lang]), $this->fileName.'.xlsx');

//        Excel::create($this->fileName, function($excel) use ($records, $title) {
//            $excel->setTitle($title);
////            $excel->setCreator($title)->setCompany($title);
//            $excel->setDescription(trans('sw.store_products_data'));
//            $excel->sheet(trans('sw.store_products_data'), function($sheet) use ($records) {
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
                trans('sw.name') => $row['name']
            ];
        }, $data->toArray());
        array_unshift($result, $name);
        array_unshift($result, [trans('sw.store_products')]);
        return $result;
    }
    function exportPDF(){
        $records = $this->StoreProductRepository->get();
        $this->fileName = 'store_products-' . Carbon::now()->toDateTimeString();

        $keys = ['name', 'price'];
        if($this->lang == 'ar') $keys = array_reverse($keys);

        $title = trans('sw.store_products');
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
                
                $notes = trans('sw.export_pdf_store_products');
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

        $notes = trans('sw.export_pdf_store_products');
        $this->userLog($notes, TypeConstants::ExportActivityPDF);

        return $pdf->download($this->fileName.'.pdf');
    }


    public function create()
    {
        $title = trans('sw.store_product_add');
        $categories = GymStoreCategory::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->get();
        $payment_types = GymPaymentType::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->orderBy('id')->get();
        return view('software::Front.store_product_front_form', [
            'product' => new GymStoreProduct(),
            'title' => $title,
            'categories' => $categories,
            'payment_types' => $payment_types,
        ]);
    }

    public function store(GymStoreProductRequest $request)
    {
        $product_inputs = $this->prepare_inputs($request->except(['_token', 'vendor_name', 'vendor_phone', 'vendor_address', 'vendor_amount', 'vendor_payment_type']));
        $product_inputs['is_system'] = request()->has('is_system') ? 1 : 0;
        $product_inputs['user_id'] = $this->user_sw->id;

        $nextCode = str_pad((GymStoreProduct::withTrashed()->max('code') + 1), 14, 0, STR_PAD_LEFT);
        $product_inputs['code'] = $product_inputs['code'] ?? null;
        if (empty($product_inputs['code'])) {
            $product_inputs['code'] = $nextCode;
        }

        $product = $this->StoreProductRepository->create($product_inputs);

        $vendor_inputs = [];
        if($request->quantity)  $vendor_inputs['quantity'] = (int)$request->quantity;
        if($request->vendor_name)  $vendor_inputs['vendor_name'] = (string)$request->vendor_name;
        if($request->vendor_phone)  $vendor_inputs['vendor_phone'] = (string)$request->vendor_phone;
        if($request->vendor_address)  $vendor_inputs['vendor_address'] = (string)$request->vendor_address;
        if($request->vendor_amount)  $vendor_inputs['amount'] = (float)$request->vendor_amount;
        if($request->vendor_is_vat)  $vendor_inputs['vat'] = $this->calculateVat(@(float)$request->vendor_amount);

        if($request->quantity && (count($vendor_inputs) > 0)){


            $vendor_inputs['product_id'] = $product->id;
            $vendor_inputs['payment_method'] = (int)@$request->vendor_payment_type;
            $order = GymStoreOrderVendor::create($vendor_inputs);

//            $product->quantity = (int)$product->quantity + $vendor_inputs['quantity'];
//            $product->save();

            if(@$vendor_inputs['amount']) {
                $amount_box = GymMoneyBox::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->latest()->first();
                $amount_after = GymMoneyBoxFrontController::amountAfter($amount_box->amount, $amount_box->amount_before, $amount_box->operation);

                $notes = trans('sw.store_purchase_order_add', ['id' => $order->id]);
                GymMoneyBox::create([
                    'user_id' => Auth::guard('sw')->user()->id
                    , 'amount' => @$vendor_inputs['amount']
                    , 'vat' => @$vendor_inputs['vat']
                    , 'operation' => TypeConstants::Sub
                    , 'amount_before' => $amount_after
                    , 'notes' => $notes
                    , 'type' => TypeConstants::CreateStorePurchaseOrder
                    , 'payment_type' => @$vendor_inputs['payment_method']
                    , 'member_id' => @$order->member_id
                    , 'branch_setting_id' => @$this->user_sw->branch_setting_id
                    , 'tenant_id' => @$this->user_sw->tenant_id
                ]);
                $this->userLog($notes, TypeConstants::CreateMoneyBoxAdd);

                $notes = trans('sw.store_purchase_order_add', ['id' => $order->id]);
                $this->userLog($notes, TypeConstants::CreateStoreProduct);
                         }
         }

         session()->flash('sweet_flash_message', [
             'title' => trans('admin.done'),
             'message' => trans('admin.successfully_added'),
             'type' => 'success'
         ]);

         $notes = trans('sw.add_store_product', ['name' => $product->name]);
        $this->userLog($notes, TypeConstants::CreateStoreProduct);
        return redirect(route('sw.listStoreProducts'));
    }

    public function edit($id)
    {
        $product = $this->StoreProductRepository->withTrashed()->find($id);
        $title = trans('sw.store_product_edit');
        $categories = GymStoreCategory::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->get();
        $payment_types = GymPaymentType::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->orderBy('id')->get();

        return view('software::Front.store_product_front_form', [
            'product' => $product,
            'title' => $title,
            'categories' => $categories,
            'payment_types' => $payment_types,
        ]);
    }

    public function update(GymStoreProductRequest $request, $id)
    {
        $product =$this->StoreProductRepository->withTrashed()->find($id);

        if(@request()->hasFile('image') && @$product->image_name){
            @unlink(GymStoreProduct::$uploads_path.$product->image_name);
        }

        $product_inputs = $this->prepare_inputs($request->except(['_token']));
        $product_inputs['is_system'] = request()->has('is_system') ? 1 : 0;
        $product_inputs['is_web'] = @(int)$product_inputs['is_web'];
        $product_inputs['is_mobile'] = @(int)$product_inputs['is_mobile'];
        $product->update($product_inputs);

                 $notes = trans('sw.edit_store_product', ['name' => $product->name]);

         $this->userLog($notes, TypeConstants::EditStoreProduct);

         session()->flash('sweet_flash_message', [
             'title' => trans('admin.done'),
             'message' => trans('admin.successfully_edited'),
             'type' => 'success'
         ]);
         return redirect(route('sw.listStoreProducts'));
    }

    public function destroy($id)
    {
        $product =$this->StoreProductRepository->withTrashed()->find($id);
        if($product->trashed())
        {
            $product->restore();
        }
        else
        {
            $product->delete();

                         $notes =  trans('sw.delete_product', ['name' => $product['name']]);
             $this->userLog($notes, TypeConstants::DeleteStoreProduct);
         }
         session()->flash('sweet_flash_message', [
             'title' => trans('admin.done'),
             'message' => trans('admin.successfully_deleted'),
             'type' => 'success'
         ]);
         return redirect(route('sw.listStoreProducts'));
    }

    public function downloadBarcode(GymStoreProduct $product)
    {
        $value = $product->code;
        if (!$value) {
            return redirect()->back();
        }

        $barcodesFolder = base_path('uploads/store-product-barcodes/');

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

        return Response::download($fullPath, sprintf('product-%s.png', $value));
    }

    private function prepare_inputs($inputs)
    {
        $input_file = 'image';
        if (request()->hasFile($input_file)) {
            $file = request()->file($input_file);
            if ($file->isValid()) {
                $extension = $file->getClientOriginalExtension();
                $filename = uniqid() . time() . ($extension ? '.' . $extension : '.jpg');
                $destinationPath = base_path(GymStoreProduct::$uploads_path);

                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0777, true, true);
                }

                try {
                    $img = $this->imageManager->read($file->getRealPath());
                    $img->scaleDown(240)->toJpeg(90)->save($destinationPath . DIRECTORY_SEPARATOR . $filename);
                    $inputs[$input_file] = $filename;
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        } else {
            unset($inputs[$input_file]);
        }
        if(@$this->user_sw->branch_setting_id){
            $inputs['branch_setting_id'] = @$this->user_sw->branch_setting_id;
            $inputs['tenant_id'] = @$this->user_sw->tenant_id;
        }
        
        // Handle store_category_id - set to null if empty
        if(isset($inputs['store_category_id']) && empty($inputs['store_category_id'])){
            $inputs['store_category_id'] = null;
        }
        
        // For backward compatibility: if column is still named category_id (before migration)
        // copy store_category_id value to category_id
        if(isset($inputs['store_category_id'])){
            $inputs['category_id'] = $inputs['store_category_id'];
        }
        
        // Handle text fields - convert empty strings to avoid null constraint violations
        $inputs['content_ar'] = isset($inputs['content_ar']) && $inputs['content_ar'] !== null ? $inputs['content_ar'] : '';
        $inputs['content_en'] = isset($inputs['content_en']) && $inputs['content_en'] !== null ? $inputs['content_en'] : '';

        if (array_key_exists('sku', $inputs)) {
            $inputs['sku'] = trim((string)$inputs['sku']);
            if ($inputs['sku'] === '') {
                $inputs['sku'] = null;
            }
        }

        if (array_key_exists('code', $inputs)) {
            $inputs['code'] = trim((string)$inputs['code']);
            if ($inputs['code'] === '') {
                $inputs['code'] = null;
            } elseif (ctype_digit($inputs['code'])) {
                $inputs['code'] = str_pad($inputs['code'], 14, '0', STR_PAD_LEFT);
            }
        }
        
        return $inputs;
    }

    private function calculateVat($amount){
        return (($amount * (@(float)$this->mainSettings->vat_details['vat_percentage'] / 100)) / (1 + (@(float)$this->mainSettings->vat_details['vat_percentage'] / 100)));
    }
    public function storePurchasesBill(Request $request){

        $product_id = (int)$request->product_id;
        $amount = (float)$request->amount;
        $vendor_is_vat = (int)$request->vendor_is_vat;
        $payment_type = (int)$request->payment_type;
        $quantity = (int)$request->quantity;
        $vendor_name = $request->vendor_name;
        $vendor_phone = $request->vendor_phone;
        $vendor_address = $request->vendor_address;
        $notes = $request->notes;
        $vat = 0;
        if($product_id && $quantity ){
            if($vendor_is_vat){
                $vat = $this->calculateVat($amount);
            }
            $product = GymStoreProduct::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('id', $product_id)->first();

            $product->quantity = (int)$product->quantity + $quantity;
            $product->save();

            if(@$amount) {
                $data = ['product_id' => $product_id, 'amount' => $amount, 'vat' => $vat, 'payment_type' => $payment_type,
                    'quantity' => $quantity, 'vendor_name' => @$vendor_name, 'vendor_phone' => @$vendor_phone,
                    'vendor_address' => @$vendor_address,
                    'notes' => @$notes ];
                $order = GymStoreOrderVendor::create($data);

                $amount_box = GymMoneyBox::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->latest()->first();
                $amount_after = GymMoneyBoxFrontController::amountAfter($amount_box->amount, $amount_box->amount_before, $amount_box->operation);

                $notes = trans('sw.store_purchase_order_add', ['id' => $order->id]);
                GymMoneyBox::create([
                    'user_id' => Auth::guard('sw')->user()->id
                    , 'amount' => $amount
                    , 'vat' => $vat
                    , 'operation' => TypeConstants::Sub
                    , 'amount_before' => $amount_after
                    , 'notes' => $notes
                    , 'type' => TypeConstants::CreateStorePurchaseOrder
                    , 'payment_type' => $payment_type
                    , 'member_id' => @$order->member_id
                    , 'branch_setting_id' => @$this->user_sw->branch_setting_id
                    , 'tenant_id' => @$this->user_sw->tenant_id
                ]);
                $this->userLog($notes, TypeConstants::CreateMoneyBoxAdd);

                $notes = trans('sw.store_purchase_order_add', ['id' => $order->id]);
                $this->userLog($notes, TypeConstants::CreateStoreProduct);
            }
            session()->flash('sweet_flash_message', [
                'title' => trans('admin.done'),
                'message' => trans('admin.successfully_added'),
                'type' => 'success'
            ]);
            return true;
        }

        return trans('sw.error_login');
    }

}


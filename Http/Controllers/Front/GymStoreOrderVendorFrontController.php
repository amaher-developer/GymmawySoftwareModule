<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Software\Classes\TypeConstants;
use Modules\Software\Exports\RecordsExport;
use Modules\Software\Http\Requests\GymStoreOrderRequest;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Models\GymStoreOrder;
use Modules\Software\Models\GymStoreOrderProduct;
use Modules\Software\Models\GymStoreOrderVendor;
use Modules\Software\Models\GymStoreProduct;
use Modules\Software\Repositories\GymStoreOrderRepository;
use Modules\Software\Repositories\GymStoreOrderVendorRepository;
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

class GymStoreOrderVendorFrontController extends GymGenericFrontController
{
    public $StoreOrderRepository;
    public $StoreOrderVendorRepository;

    public function __construct()
    {
        parent::__construct();
        $this->StoreOrderVendorRepository=new GymStoreOrderVendorRepository(new Application);
        $this->StoreOrderVendorRepository=$this->StoreOrderVendorRepository->branch();
    }


    public function index()
    {
        $title = trans('sw.purchase_invoices');
        $this->request_array = ['search', 'date', 'from', 'to'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $orders = $this->StoreOrderVendorRepository->with(['product', function ($q){$q->withTrashed();}])->onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $orders = $this->StoreOrderVendorRepository->with(['product'=>function($q){
                $q->withTrashed();
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
            $orders = $orders->paginate($this->limit);
            $total = $orders->total();
        } else {
            $orders = $orders->get();
            $total = $orders->count();
        }
        return view('software::Front.store_order_vendor_front_list', compact('orders','title', 'total', 'search_query'));
    }


    function exportStoreOrderVendorExcel(){
        //$this->limit = null;
        $records = $this->index()->with(\request()->all());
        $records = $records->orders;

        $fileName = 'store_order-vendors-' . Carbon::now()->toDateTimeString();

        $notes = trans('sw.export_excel_store_orders');
        $this->userLog($notes, TypeConstants::ExportStoreOrderExcel);

        return Excel::download(new RecordsExport(['records' => $records, 'keys' => ['id', 'quantity', 'amount', 'vendor_name', 'vendor_phone', 'vendor_address'],'lang' => $this->lang, 'settings' => $this->mainSettings]), $fileName.'.xlsx');

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
    function exportStoreOrderVendorPDF(){
        //$this->limit = null;
        $records = $this->index()->with(\request()->all());
        $records = $records->orders;
        $fileName = 'store-vendor-orders-' . Carbon::now()->toDateTimeString();

        $keys = ['id', 'quantity', 'amount', 'vendor_name', 'vendor_phone', 'vendor_address'];
        if($this->lang == 'ar') $keys = array_reverse($keys);

        $title = trans('sw.store_orders');
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

                $notes = trans('sw.export_pdf_store_orders');
                $this->userLog($notes, TypeConstants::ExportStoreOrderPDF);

                return response($mpdf->Output($fileName.'.pdf', 'D'), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $fileName . '.pdf"'
                ]);

            } catch (\Exception $e) {
                \Log::error('mPDF failed, falling back to DomPDF: ' . $e->getMessage());
            }
        }

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

        $notes = trans('sw.export_pdf_store_orders');
        $this->userLog($notes, TypeConstants::ExportStoreOrderPDF);

        return $pdf->download($fileName.'.pdf');
    }


    public function show($id)
    {
        $title = trans('sw.invoice');
        $order = GymStoreOrderVendor::branch()->with(['product' => function($query){
            $query->withTrashed();
        }])->where('id', $id)->first();


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
                new InvoiceTotalAmount(number_format($order['amount'],2)), // invoice total amount
                new InvoiceTaxAmount(@number_format($order['vat'],2)) // invoice tax amount
                // TODO :: Support others tags
            ])->toBase64();

            $d = new DNS2D();
            $d->setStorPath($qrcodes_folder);
            $qr_img_invoice = $d->getBarcodePNGPath($generatedQRString, TypeConstants::QRCodeType);
            $qr_img_invoice = str_replace(base_path(), '', $qr_img_invoice);
        }
        return view('software::Front.store_order_vendor_front_show', ['order' => $order, 'qr_img_invoice' => @$qr_img_invoice, 'title'=>$title]);
    }

    public function showPOS($id)
    {
        $title = trans('sw.invoice');
        $order = GymStoreOrderVendor::branch()->with(['pay_type', 'product'])->where('id', $id)->first();


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
        return view('software::Front.store_order_vendor_front_pos_show', ['order' => $order, 'qr_img_invoice' => @$qr_img_invoice,  'title'=>$title]);
    }



    public function destroy($id)
    {
        $order =$this->StoreOrderVendorRepository->withTrashed()->find($id);
        if($order->trashed())
        {
            $order->restore();
        }
        else
        {

            GymStoreProduct::where('id', $order->product_id)->decrement('quantity', $order->quantity);

            if(\request('refund')){
                $amount_box = GymMoneyBox::branch()->latest()->first();
                $amount_after = GymMoneyBoxFrontController::amountAfter($amount_box->amount, $amount_box->amount_before, $amount_box->operation);

                $amount = ($order->amount);
                if(\request('total_amount') && \request('amount') && (\request('total_amount') >= \request('amount') )){
                    $amount = (float)\request('amount');
                }
                if($amount > 0){
                    $notes = trans('sw.store_purchase_order_delete', ['id' => $order->id,'amount' => $amount]);
                    GymMoneyBox::create([
                        'user_id' => Auth::guard('sw')->user()->id
                        , 'amount' => $amount
                        , 'vat' => ((@$amount * (@$this->mainSettings->vat_details['vat_percentage'] / 100)) / (1 + (@$this->mainSettings->vat_details['vat_percentage'] / 100)))
                        , 'operation' => TypeConstants::Add
                        , 'amount_before' => $amount_after
                        , 'notes' => $notes
                        , 'type' => TypeConstants::DeleteStorePurchaseOrder
                        , 'member_id' => @$order->member_id
                        , 'payment_type' => intval($order->payment_type)
                        , 'branch_setting_id' => @$this->user_sw->branch_setting_id
                    ]);
                        $this->userLog($notes, TypeConstants::CreateMoneyBoxWithdraw);
                }
            }

            $notes =  trans('sw.store_purchase_order_delete', ['id' => $order['id'], 'name' => $order['name']]);
            $this->userLog($notes, TypeConstants::DeleteStorePurchaseOrder);


            $order->delete();
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



}


<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Software\Classes\TypeConstants;
use Modules\Software\Exports\RecordsExport;
use Modules\Software\Http\Requests\GymPTSubscriptionRequest;
use Modules\Software\Models\GymPTSubscription;
use Modules\Software\Repositories\GymPTSubscriptionRepository;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Illuminate\Container\Container as Application;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Maatwebsite\Excel\Facades\Excel;

class GymPTSubscriptionFrontController extends GymGenericFrontController
{
    public $SubscriptionRepository;
    private $imageManager;
    public $fileName;

    public function __construct()
    {
        parent::__construct();
        $this->imageManager = new ImageManager(new Driver());
        $this->SubscriptionRepository=new GymPTSubscriptionRepository(new Application);
        $this->SubscriptionRepository=$this->SubscriptionRepository->branch();
    }


    public function index()
    {
        $title = trans('sw.pt_subscriptions');
        $this->request_array = ['search'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $subscriptions = $this->SubscriptionRepository->onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $subscriptions = $this->SubscriptionRepository->orderBy('id', 'DESC');
        }

        //apply filters
        $subscriptions->when($search, function ($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('id', '=', $search);
                $query->orWhere('name_' . $this->lang, 'like', "%" . $search . "%");
            });
        });
        $search_query = request()->query();

        if ($this->limit) {
            $subscriptions = $subscriptions->paginate($this->limit);
            $total = $subscriptions->total();
        } else {
            $subscriptions = $subscriptions->get();
            $total = $subscriptions->count();
        }

        return view('software::Front.pt_subscription_front_list', compact('subscriptions','title', 'total', 'search_query'));
    }


    function exportExcel(){
        $records = $this->SubscriptionRepository->get();
        $this->fileName = 'pt_subscriptions-' . Carbon::now()->toDateTimeString();

//        $title = trans('sw.pt_subscriptions');
//        $records = $this->prepareForExport($records);


        $notes = trans('sw.export_excel_pt_subscriptions');
        $this->userLog($notes, TypeConstants::ExportPTSubscriptionExcel);

        return Excel::download(new RecordsExport(['records' => $records, 'keys' => ['name'],'lang' => $this->lang]), $this->fileName.'.xlsx');

//        Excel::create($this->fileName, function($excel) use ($records, $title) {
//            $excel->setTitle($title);
////            $excel->setCreator($title)->setCompany($title);
//            $excel->setDescription(trans('sw.pt_subscriptions_data'));
//            $excel->sheet(trans('sw.pt_subscriptions_data'), function($sheet) use ($records) {
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
        $name = [trans('sw.name')];
        $result = array_map(function ($row) {
            return [
                trans('sw.name') => $row['name']
            ];
        }, $data->toArray());
        array_unshift($result, $name);
        array_unshift($result, [trans('sw.pt_subscriptions')]);
        return $result;
    }
    function exportPDF(){
        $records = $this->PTSubscriptionRepository->get();
        $this->fileName = 'pt_subscriptions-' . Carbon::now()->toDateTimeString();

        $keys = ['name', 'price'];
        if($this->lang == 'ar') $keys = array_reverse($keys);

        $title = trans('sw.pt_subscriptions');
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
                
                $notes = trans('sw.export_pdf_pt_subscriptions');
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

        $notes = trans('sw.export_pdf_pt_subscriptions');
        $this->userLog($notes, TypeConstants::ExportActivityPDF);

        return $pdf->download($this->fileName.'.pdf');
    }


    public function create()
    {
        $title = trans('sw.pt_subscription_add');
        return view('software::Front.pt_subscription_front_form', ['subscription' => new GymPTSubscription(),'title'=>$title]);
    }

    public function store(GymPTSubscriptionRequest $request)
    {
        $subscription_inputs = $this->prepare_inputs($request->except(['_token']));
        $this->SubscriptionRepository->create($subscription_inputs);
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);

        $notes = str_replace(':name', $subscription_inputs['name_'.$this->lang], trans('sw.add_subscription'));
        $this->userLog($notes, TypeConstants::CreatePTSubscription);
        return redirect(route('sw.listPTSubscription'));
    }

    public function edit($id)
    {
        $subscription =$this->SubscriptionRepository->withTrashed()->find($id);
        $title = trans('sw.pt_subscription_edit');
        return view('software::Front.pt_subscription_front_form', ['subscription' => $subscription,'title'=>$title]);
    }

    public function update(GymPTSubscriptionRequest  $request, $id)
    {
        $subscription =$this->SubscriptionRepository->withTrashed()->find($id);
        $subscription_inputs = $this->prepare_inputs($request->except(['_token']));
        $subscription->update($subscription_inputs);

        $notes = str_replace(':name', $subscription['name'], trans('sw.edit_activity'));
        $this->userLog($notes, TypeConstants::EditPTSubscription);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listPTSubscription'));
    }

    public function destroy($id)
    {
        $subscription =$this->SubscriptionRepository->withTrashed()->find($id);
        if($subscription->trashed())
        {
            $subscription->restore();
        }
        else
        {
            $subscription->delete();

            $notes = str_replace(':name', $subscription['name'], trans('sw.delete_activity'));
            $this->userLog($notes, TypeConstants::DeletePTSubscription);
        }
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listPTSubscription'));
    }

    private function prepare_inputs($inputs)
    {
        $input_file = 'image';
        if (request()->hasFile($input_file)) {
            $file = request()->file($input_file);
            $filename = rand(0, 20000) . time() . '.' . $file->getClientOriginalExtension();
            $destinationPath = base_path(GymPTSubscription::$uploads_path);

            $upload_success = $this->imageManager->read($file)->scale(width: 320)->toJpeg()->save($destinationPath.$filename);

//            $upload_success = $file->move($destinationPath, $filename);
            if ($upload_success) {
                $inputs[$input_file] = $filename;
            }
        } else {
            unset($inputs[$input_file]);
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
        }
        return $inputs;
    }

}

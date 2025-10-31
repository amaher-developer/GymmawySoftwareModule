<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Software\Classes\TypeConstants;
use Modules\Software\Exports\RecordsExport;
use Modules\Software\Http\Requests\GymActivityRequest;
use Modules\Software\Http\Requests\GymPTClassRequest;
use Modules\Software\Models\GymActivity;
use Modules\Software\Models\GymPTClass;
use Modules\Software\Models\GymPTSubscription;
use Modules\Software\Repositories\GymPTClassRepository;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Illuminate\Container\Container as Application;
use Maatwebsite\Excel\Facades\Excel;

class GymPTClassFrontController extends GymGenericFrontController
{
    public $ClassRepository;
    public $fileName;

    public function __construct()
    {
        parent::__construct();
        $this->ClassRepository=new GymPTClassRepository(new Application);
        $this->ClassRepository=$this->ClassRepository->branch();
    }


    public function index()
    {
        $title = trans('sw.pt_classes');
        $this->request_array = ['search'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $classes = $this->ClassRepository->with(['pt_subscription' => function($q){
                $q->withTrashed();
            }])->onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $classes = $this->ClassRepository->with(['pt_subscription' => function($q){
                $q->withTrashed();
            }])->orderBy('id', 'DESC');
        }

        //apply filters
        $classes->when($search, function ($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('id', '=', $search);
                $query->orWhere('name_' . $this->lang, 'like', "%" . $search . "%");
            });
        });
        $search_query = request()->query();

        if ($this->limit) {
            $classes = $classes->paginate($this->limit);
            $total = $classes->total();
        } else {
            $classes = $classes->get();
            $total = $classes->count();
        }

        return view('software::Front.pt_class_front_list', compact('classes','title', 'total', 'search_query'));
    }


    function exportExcel(){
        $records = $this->ClassRepository->get();
        $this->fileName = 'pt_classes-' . Carbon::now()->toDateTimeString();

//        $title = trans('sw.pt_classes');
//        $records = $this->prepareForExport($records);


        $notes = trans('sw.export_excel_pt_classes');
        $this->userLog($notes, TypeConstants::ExportPTClassExcel);

        return Excel::download(new RecordsExport(['records' => $records, 'keys' => ['name', 'price'],'lang' => $this->lang]), $this->fileName.'.xlsx');

//        Excel::create($this->fileName, function($excel) use ($records, $title) {
//            $excel->setTitle($title);
////            $excel->setCreator($title)->setCompany($title);
//            $excel->setDescription(trans('sw.pt_classes_data'));
//            $excel->sheet(trans('sw.pt_classes_data'), function($sheet) use ($records) {
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
        array_unshift($result, [trans('sw.pt_classes')]);
        return $result;
    }
    function exportPDF(){
        $records = $this->ClassRepository->get();
        $this->fileName = 'pt_classes-' . Carbon::now()->toDateTimeString();

        $keys = ['name'];
        if($this->lang == 'ar') $keys = array_reverse($keys);

        $title = trans('sw.pt_classes');
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
                
                $notes = trans('sw.export_pdf_pt_classes');
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

        $notes = trans('sw.export_pdf_pt_classes');
        $this->userLog($notes, TypeConstants::ExportActivityPDF);

        return $pdf->download($this->fileName.'.pdf');
    }


    public function create()
    {
        $title = trans('sw.pt_class_add');
        $subscriptions = GymPTSubscription::branch()->get();
        return view('software::Front.pt_class_front_form', ['class' => new GymPTClass(), 'subscriptions' => $subscriptions,'title'=>$title]);
    }

    public function store(GymPTClassRequest $request)
    {
        $class_inputs = $this->prepare_inputs($request->except(['_token']));
        $class_inputs['is_system'] = request()->has('is_system') ? 1 : 0;
        $class = $this->ClassRepository->create($class_inputs);
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);

        $notes = trans('sw.add_pt_class', ['name' => $class->pt_subscription->name]);
        $this->userLog($notes, TypeConstants::CreatePTClass);
        return redirect(route('sw.listPTClass'));
    }

    public function edit($id)
    {
        $class =$this->ClassRepository->withTrashed()->find($id);
        $title = trans('sw.pt_class_edit');

        $subscriptions = GymPTSubscription::branch()->get();
        return view('software::Front.pt_class_front_form', ['class' => $class,'title'=>$title, 'subscriptions'=>$subscriptions]);
    }

    public function update(GymPTClassRequest $request, $id)
    {
        $class =$this->ClassRepository->withTrashed()->find($id);
        $class_inputs = $this->prepare_inputs($request->except(['_token']));
        $class_inputs['is_system'] = request()->has('is_system') ? 1 : 0;
        $class_inputs['is_web'] = @(int)$class_inputs['is_web'];
        $class_inputs['is_mobile'] = @(int)$class_inputs['is_mobile'];
        $class->update($class_inputs);

        $notes = trans('sw.edit_pt_class', ['name' => $class->pt_subscription->name]);

        $this->userLog($notes, TypeConstants::EditPTClass);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listPTClass'));
    }

    public function destroy($id)
    {
        $class =$this->ClassRepository->withTrashed()->find($id);
        if($class->trashed())
        {
            $class->restore();
        }
        else
        {
            $class->delete();

            $notes = str_replace(':name', $class['name'], trans('sw.delete_activity'));
            $this->userLog($notes, TypeConstants::DeletePTClass);
        }
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listPTClass'));
    }

    private function prepare_inputs($inputs)
    {
        // Handle text fields - convert null/empty values to empty strings to avoid null constraint violations
        $inputs['content_ar'] = isset($inputs['content_ar']) && $inputs['content_ar'] !== null ? $inputs['content_ar'] : '';
        $inputs['content_en'] = isset($inputs['content_en']) && $inputs['content_en'] !== null ? $inputs['content_en'] : '';
        
        if(@$this->user_sw->branch_setting_id){
            $inputs['branch_setting_id'] = @$this->user_sw->branch_setting_id;
        }
        return $inputs;
    }

}

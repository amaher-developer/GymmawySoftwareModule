<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Generic\Http\Controllers\Front\GenericFrontController;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Exports\RecordsExport;
use Modules\Software\Http\Requests\GymSubscriptionCategoryRequest;
use Modules\Software\Models\GymSubscriptionCategory;
use Modules\Software\Models\GymUserLog;
use Modules\Software\Repositories\GymSubscriptionCategoryRepository;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Illuminate\Container\Container as Application;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Maatwebsite\Excel\Facades\Excel;

class GymSubscriptionCategoryFrontController extends GymGenericFrontController
{
    public $CategoryRepository;
    private $imageManager;
    public $fileName;

    public function __construct()
    {
        parent::__construct();
        $this->imageManager = new ImageManager(new Driver());
        $this->CategoryRepository =new GymSubscriptionCategoryRepository(new Application);
        $this->CategoryRepository = $this->CategoryRepository->branch();
    }


    public function index()
    {
        $title = trans('sw.subscription_categories');
        $this->request_array = ['search'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $categories = $this->CategoryRepository->onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $categories = $this->CategoryRepository->orderBy('id', 'DESC');
        }

        //apply filters
        $categories->when($search, function ($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('id', '=', $search);
                $query->orWhere('name_' . $this->lang, 'like', "%" . $search . "%");
            });
        });
        $search_query = request()->query();

        if ($this->limit) {
            $categories = $categories->paginate($this->limit);
            $total = $categories->total();
        } else {
            $categories = $categories->get();
            $total = $categories->count();
        }

        return view('software::Front.subscription_category_front_list', compact('categories','title', 'total', 'search_query'));
    }


    function exportExcel(){
        $records = $this->CategoryRepository->get();
        $this->fileName = 'subscription-categories-' . Carbon::now()->toDateTimeString();

        $notes = trans('sw.export_excel_subscription_categories');
        $this->userLog($notes, TypeConstants::ExportSubscriptionCategoryExcel);

        return Excel::download(new RecordsExport(['records' => $records, 'keys' => ['name'],'lang' => $this->lang, 'settings' => $this->mainSettings]), $this->fileName.'.xlsx');
    }

    function exportPDF(){
        $records = $this->SubscriptionCategoryRepository->get();
        $this->fileName = 'subscription_categories-' . Carbon::now()->toDateTimeString();

        $keys = ['name'];
        if($this->lang == 'ar') $keys = array_reverse($keys);

        $title = trans('sw.subscription_categories');
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
                
                $notes = trans('sw.export_pdf_subscription_categories');
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

        $notes = trans('sw.export_pdf_subscription_categories');
        $this->userLog($notes, TypeConstants::ExportActivityPDF);

        return $pdf->download($this->fileName.'.pdf');
    }


    public function create()
    {
        $title = trans('sw.subscription_category_add');
        return view('software::Front.subscription_category_front_form', ['category' => new GymSubscriptionCategory(),'title'=>$title]);
    }

    public function store(GymSubscriptionCategoryRequest $request)
    {
        $category_inputs = $this->prepare_inputs($request->except(['_token']));
        $this->CategoryRepository->create($category_inputs);
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);

        $notes = str_replace(':name', $category_inputs['name_'.$this->lang], trans('sw.add_subscription_category'));
        $this->userLog($notes, TypeConstants::CreateSubscriptionCategory);
        return redirect(route('sw.listSubscriptionCategory'));
    }

    public function edit($id)
    {
        $category =$this->CategoryRepository->withTrashed()->find($id);
        $title = trans('sw.subscription_category_edit');
        return view('software::Front.subscription_category_front_form', ['category' => $category,'title'=>$title]);
    }

    public function update(GymSubscriptionCategoryRequest $request, $id)
    {
        $category =$this->CategoryRepository->withTrashed()->find($id);
        $category_inputs = $this->prepare_inputs($request->except(['_token']));
        $category->update($category_inputs);

        $notes = str_replace(':name', $category['name'], trans('sw.edit_subscription_category'));
        $this->userLog($notes, TypeConstants::EditSubscriptionCategory);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listSubscriptionCategory'));
    }

    public function destroy($id)
    {
        $category =$this->CategoryRepository->withTrashed()->find($id);
        if($category->trashed())
        {
            $category->restore();
        }
        else
        {
            $category->delete();

            $notes = str_replace(':name', $category['name'], trans('sw.delete_subscription_category'));
            $this->userLog($notes, TypeConstants::DeleteSubscriptionCategory);
        }
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listSubscriptionCategory'));
    }

    private function prepare_inputs($inputs)
    {
        $input_file = 'image';
        if (request()->hasFile($input_file)) {
            $file = request()->file($input_file);
            $filename = rand(0, 20000) . time() . '.' . $file->getClientOriginalExtension();
            $destinationPath = base_path(GymSubscriptionCategory::$uploads_path);

            $upload_success = $this->imageManager
                ->read($file)
                ->scale(width: 360)
                ->toJpeg()
                ->save($destinationPath . $filename);

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



<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Software\Classes\TypeConstants;
use Modules\Software\Exports\RecordsExport;
use Modules\Software\Exports\TrainingMedicineExport;
use Modules\Software\Http\Requests\GymTrainingMedicineRequest;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymTrainingMedicine;
use Modules\Software\Repositories\GymTrainingMedicineRepository;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Illuminate\Container\Container as Application;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Maatwebsite\Excel\Facades\Excel;

class GymTrainingMedicineFrontController extends GymGenericFrontController
{
    public $TrainingMedicineRepository;
    private $imageManager;
    public $fileName;

    public function __construct()
    {
        parent::__construct();
        $this->imageManager = new ImageManager(new Driver());

        $this->TrainingMedicineRepository=new GymTrainingMedicineRepository(new Application);
        $this->TrainingMedicineRepository=$this->TrainingMedicineRepository->branch();
    }


    public function index()
    {

        $title = trans('sw.training_medicines');
        $this->request_array = ['search', 'from', 'to'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $medicines = GymTrainingMedicine::branch()->onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $medicines = GymTrainingMedicine::branch()->orderBy('id', 'DESC');
        }

        //apply filters
        $medicines->when($search, function ($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('id', '=', (int)$search);
                $query->orWhere('name_ar', 'like', "%" . $search . "%");
                $query->orWhere('name_en', 'like', "%" . $search . "%");
//            $query->orWhere('weight', 'like', "%" . $search . "%");
//            $query->orWhere('height', 'like', "%" . $search . "%");
            });
        });
        $search_query = request()->query();
        $medicines->orderBy('id', 'desc');
        if ($this->limit) {
            $medicines = $medicines->paginate($this->limit);
            $total = $medicines->total();
        } else {
            $medicines = $medicines->get();
            $total = $medicines->count();
        }

        return view('software::Front.training_medicine_front_list', compact('medicines','title', 'total', 'search_query'));
    }

    function exportExcel(){
        $records = GymTrainingMedicine::branch()->get();
        $this->fileName = 'training-clients-' . Carbon::now()->toDateTimeString();

//        $title =  trans('sw.training_Medicines');
//        $records = $this->prepareForExport($records);

        $notes = trans('sw.export_excel_training_medicines');
        $this->userLog($notes, TypeConstants::ExportTrainingMedicineExcel);

        return Excel::download(new TrainingMedicineExport(['records' => $records, 'keys' => ['barcode', 'name', 'height', 'weight', 'notes'],'lang' => $this->lang]), $this->fileName.'.xlsx');

//        Excel::create($this->fileName, function($excel) use ($records, $title) {
//            $excel->setTitle($title);
////            $excel->setCreator($title)->setCompany($title);
//            $excel->setDescription(trans('sw.training_Medicines_data'));
//            $excel->sheet(trans('sw.training_Medicines_data'), function($sheet) use ($records) {
//                $sheet->setRightToLeft(true);
//                $sheet->fromArray($records, null, 'A1', false, false);
//                $sheet->mergeCells('A1:B1');
//                $sheet->cells('A1:B1', function ($cells) {
//                    $cells->setBackground('#d8d8d8');
//                    $cells->setFontWeight('bold');
//                    $cells->setAlignment('center');
//                });
//            });
//        })->download('xlsx');
    }


    private function prepareForExport($data)
    {
        $name = [trans('sw.barcode'), trans('sw.name'), trans('sw.height'), trans('sw.weight'), trans('sw.notes')];
        $result = array_map(function ($row) {
            return [
                trans('sw.barcode') => $row['member']['code'],
                trans('sw.name') => $row['member']['name'],
                trans('sw.height') => $row['height'],
                trans('sw.weight') => $row['weight'],
                trans('sw.notes') => $row['notes']
            ];
        }, $data->toArray());
        array_unshift($result, $name);
        array_unshift($result, [trans('sw.training_medicines')]);
        return $result;
    }

    function exportPDF(){
        $records = $this->TrainingMedicineRepository->get();
        $this->fileName = 'training_medicines-' . Carbon::now()->toDateTimeString();

        $keys = ['name', 'description'];
        if($this->lang == 'ar') $keys = array_reverse($keys);

        $title = trans('sw.training_medicines');
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
                
                $notes = trans('sw.export_pdf_training_medicines');
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

        $notes = trans('sw.export_pdf_training_medicines');
        $this->userLog($notes, TypeConstants::ExportActivityPDF);

        return $pdf->download($this->fileName.'.pdf');
    }


    public function create()
    {
        $title = trans('sw.training_medicine_add');
        return view('software::Front.training_medicine_front_form', [
            'medicine' => new GymTrainingMedicine(),'title'=>$title]);
    }

    public function store(GymTrainingMedicineRequest $request)
    {
        $training_medicine_inputs = $this->prepare_inputs($request->except(['_token']));
        $medicine = $this->TrainingMedicineRepository->create($training_medicine_inputs);


        $notes = str_replace(':name', $medicine->name, trans('sw.add_training_medicine'));
        $this->userLog($notes, TypeConstants::CreateTrainingMedicine);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listTrainingMedicine'));
    }

    public function edit($id)
    {
        $medicine = GymTrainingMedicine::branch()->withTrashed()->find($id);
        $title = trans('sw.training_medicine_edit');
        return view('software::Front.training_medicine_front_form', ['medicine' => $medicine,'title'=>$title]);
    }

    public function update(GymTrainingMedicineRequest $request, $id)
    {
        $medicine = $this->TrainingMedicineRepository->withTrashed()->find($id);
        $training_medicine_inputs = $this->prepare_inputs($request->except(['_token']));
        $training_medicine_inputs['user_id'] = $this->user_sw->id;

        $medicine->update($training_medicine_inputs);

        $notes = str_replace(':name', $medicine->name, trans('sw.edit_training_medicine'));
        $this->userLog($notes, TypeConstants::EditTrainingMedicine);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listTrainingMedicine'));
    }

    public function destroy($id)
    {
        $medicine = $this->TrainingMedicineRepository->withTrashed()->find($id);
        if($medicine->trashed())
        {
            $medicine->restore();
        }
        else
        {
            $medicine->delete();
        }


        $notes = str_replace(':name', $medicine->name, trans('sw.delete_training_medicine'));
        $this->userLog($notes, TypeConstants::DeleteTrainingMedicine);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listTrainingMedicine'));
    }

    private function prepare_inputs($inputs)
    {
        $input_file = 'image';
        $uploaded='';

        $destinationPath = base_path(GymTrainingMedicine::$uploads_path);
        $ThumbnailsDestinationPath = base_path(GymTrainingMedicine::$thumbnails_uploads_path);

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
                $inputs[$input_file]=$uploaded;
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
        }
//        !$inputs['deleted_at']?$inputs['deleted_at']=null:'';

        return $inputs;
    }

}

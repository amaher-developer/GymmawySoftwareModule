<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Software\Classes\TypeConstants;
use Modules\Software\Exports\RecordsExport;
use Modules\Software\Exports\TrainingTrackExport;
use Modules\Software\Http\Requests\GymTrainingTrackRequest;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymTrainingTrack;
use Modules\Software\Repositories\GymTrainingTrackRepository;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Illuminate\Container\Container as Application;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Maatwebsite\Excel\Facades\Excel;

class GymTrainingTrackFrontController extends GymGenericFrontController
{
    public $TrainingTrackRepository;
    private $imageManager;
    public $fileName;

    public function __construct()
    {
        parent::__construct();
        $this->imageManager = new ImageManager(new Driver());

        $this->TrainingTrackRepository=new GymTrainingTrackRepository(new Application);
        $this->TrainingTrackRepository=$this->TrainingTrackRepository->branch();
    }


    public function index()
    {

        $title = trans('sw.training_tracks');
        $this->request_array = ['search', 'from', 'to'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $tracks = GymTrainingTrack::branch()->with(['member' => function ($q){$q->withTrashed();}])->onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $tracks = GymTrainingTrack::branch()->with(['member' => function ($q){$q->withTrashed();}])->orderBy('id', 'DESC');
        }

        //apply filters
        $tracks->when($search, function ($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('id', '=', (int)$search);
                $query->orWhere('notes', 'like', "%" . $search . "%");
//            $query->orWhere('weight', 'like', "%" . $search . "%");
//            $query->orWhere('height', 'like', "%" . $search . "%");
            });
            $query->orWhereHas('member', function ($q) use ($search){
                $q->where('code', $search);
                $q->orWhere('name',  'like', "%" . $search . "%");
            });
        });
        $tracks->when(($from), function ($query) use ($from) {
            $query->whereDate('created_at', '>=', Carbon::parse($from)->format('Y-m-d'));
        })->when(($to), function ($query) use ($to) {
            $query->whereDate('created_at', '<=', Carbon::parse($to)->format('Y-m-d'));
        });
        $search_query = request()->query();
        $tracks->orderBy('date', 'desc');
        if ($this->limit) {
            $tracks = $tracks->paginate($this->limit);
            $total = $tracks->total();
        } else {
            $tracks = $tracks->get();
            $total = $tracks->count();
        }
        return view('software::Front.training_track_front_list', compact('tracks','title', 'total', 'search_query'));
    }

    function exportExcel(){
        $records = GymTrainingTrack::branch()->with('member')->get();
        $this->fileName = 'training-clients-' . Carbon::now()->toDateTimeString();

//        $title =  trans('sw.training_tracks');
//        $records = $this->prepareForExport($records);

        $notes = trans('sw.export_excel_training_tracks');
        $this->userLog($notes, TypeConstants::ExportTrainingTrackExcel);

        return Excel::download(new TrainingTrackExport(['records' => $records, 'keys' => ['barcode', 'name', 'height', 'weight', 'notes'],'lang' => $this->lang]), $this->fileName.'.xlsx');

//        Excel::create($this->fileName, function($excel) use ($records, $title) {
//            $excel->setTitle($title);
////            $excel->setCreator($title)->setCompany($title);
//            $excel->setDescription(trans('sw.training_tracks_data'));
//            $excel->sheet(trans('sw.training_tracks_data'), function($sheet) use ($records) {
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
        array_unshift($result, [trans('sw.training_tracks')]);
        return $result;
    }

    function exportPDF(){
        $records = $this->TrainingTrackRepository->get();
        $this->fileName = 'training_tracks-' . Carbon::now()->toDateTimeString();

        $keys = ['name', 'description'];
        if($this->lang == 'ar') $keys = array_reverse($keys);

        $title = trans('sw.training_tracks');
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
                
                $notes = trans('sw.export_pdf_training_tracks');
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

        $notes = trans('sw.export_pdf_training_tracks');
        $this->userLog($notes, TypeConstants::ExportActivityPDF);

        return $pdf->download($this->fileName.'.pdf');
    }


    public function create()
    {
        $title = trans('sw.training_track_add');
        return view('software::Front.training_track_front_form', [
            'member' => new GymTrainingTrack(),'title'=>$title]);
    }

    public function store(GymTrainingTrackRequest $request)
    {
        $member = GymMember::branch()->where('code', $request->member_id)->first();
        $training_track_inputs = $this->prepare_inputs($request->except(['_token']));
        $training_track_inputs['user_id'] = $this->user_sw->id;
        $training_track_inputs['member_id'] = $member->id;
        $this->TrainingTrackRepository->create($training_track_inputs);


        $notes = str_replace(':name', $member->name, trans('sw.add_training_track'));
        $this->userLog($notes, TypeConstants::CreateTrainingTrack);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listTrainingTrack'));
    }

    public function edit($id)
    {
        $member = GymTrainingTrack::branch()->with('member')->withTrashed()->find($id);
        $title = trans('sw.training_track_edit');
        return view('software::Front.training_track_front_form', ['member' => $member,'title'=>$title]);
    }

    public function update(GymTrainingTrackRequest $request, $id)
    {
        $member_detail = GymMember::branch()->where('code', $request->member_id)->first();
        $member = $this->TrainingTrackRepository->withTrashed()->find($id);
        $training_track_inputs = $this->prepare_inputs($request->except(['_token']));
        $training_track_inputs['user_id'] = $this->user_sw->id;
        $training_track_inputs['member_id'] = $member_detail->id;

        $member->update($training_track_inputs);

        $notes = str_replace(':name', $member_detail->name, trans('sw.edit_training_track'));
        $this->userLog($notes, TypeConstants::EditTrainingTrack);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listTrainingTrack'));
    }

    public function destroy($id)
    {
        $member = $this->TrainingTrackRepository->withTrashed()->find($id);
        $member_detail = GymMember::branch()->where('id', $member->member_id)->first();
//        $member->forceDelete();
        if($member->trashed())
        {
            $member->restore();
        }
        else
        {
            $member->delete();
        }


        $notes = str_replace(':name', $member_detail->name, trans('sw.delete_training_track'));
        $this->userLog($notes, TypeConstants::DeleteTrainingTrack);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listTrainingTrack'));
    }

    private function prepare_inputs($inputs)
    {
        $input_file = 'image';
        $uploaded='';

        $destinationPath = base_path(GymTrainingTrack::$uploads_path);
        $ThumbnailsDestinationPath = base_path(GymTrainingTrack::$thumbnails_uploads_path);

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


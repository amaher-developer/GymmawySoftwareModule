<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Generic\Models\Setting;
use Modules\Software\Classes\TypeConstants;

use Modules\Software\Exports\RecordsExport;
use Modules\Software\Exports\TrainingMemberExport;
use Modules\Software\Http\Requests\GymTrainingMemberRequest;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymTrainingMember;
use Modules\Software\Models\GymTrainingPlan;
use Modules\Software\Models\GymTrainingTrack;
use Modules\Software\Models\GymUser;
use Modules\Software\Repositories\GymTrainingMemberRepository;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Illuminate\Container\Container as Application;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Maatwebsite\Excel\Facades\Excel;

class GymTrainingMemberFrontController extends GymGenericFrontController
{
    public $TrainingMemberRepository;
    private $imageManager;
    public $fileName;

    public function __construct()
    {
        parent::__construct();
        $this->imageManager = new ImageManager(new Driver());

        $this->TrainingMemberRepository=new GymTrainingMemberRepository(new Application);
        $this->TrainingMemberRepository=$this->TrainingMemberRepository->branch();
    }


    public function index()
    {

        $title = trans('sw.training_list');
        $this->request_array = ['search'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $members = GymTrainingMember::branch()->with(['member'  => function ($q){ $q->withTrashed();}])->onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $members = GymTrainingMember::branch()->with(['member'  => function ($q){ $q->withTrashed();}])->orderBy('id', 'DESC');
        }

        //apply filters
        $members->when($search, function ($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('id', '=', (int)$search);
                $query->orWhere('notes', 'like', "%" . $search . "%");
//            $query->orWhere('name', 'like', "%" . $search . "%");
//            $query->orWhere('phone', 'like', "%" . $search . "%");
            });
            $query->orWhereHas('member', function ($q) use ($search){
                $q->where('code', $search);
                $q->orWhere('name',  'like', "%" . $search . "%");
            });
        });
        $search_query = request()->query();

        if ($this->limit) {
            $members = $members->paginate($this->limit);
            $total = $members->total();
        } else {
            $members = $members->get();
            $total = $members->count();
        }

        return view('software::Front.training_member_front_list', compact('members','title', 'total', 'search_query'));
    }

    function exportExcel(){
        $records =  GymTrainingMember::branch()->with('member')->get();
        $this->fileName = 'training-clients-' . Carbon::now()->toDateTimeString();

//        $title =  trans('sw.training_list');
//        $records = $this->prepareForExport($records);

        $notes = trans('sw.export_excel_training_members');
        $this->userLog($notes, TypeConstants::ExportTrainingMemberExcel);

        return Excel::download(new TrainingMemberExport(['records' => $records, 'keys' => ['barcode', 'name', 'height', 'weight', 'diseases', 'plan_training', 'plan_diet', 'notes'],'lang' => $this->lang, 'settings' => $this->mainSettings]), $this->fileName.'.xlsx');

//        Excel::create($this->fileName, function($excel) use ($records, $title) {
//            $excel->setTitle($title);
////            $excel->setCreator($title)->setCompany($title);
//            $excel->setDescription(trans('sw.training_members_data'));
//            $excel->sheet(trans('sw.training_members_data'), function($sheet) use ($records) {
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
        $name = [trans('sw.barcode'), trans('sw.name'), trans('sw.height'), trans('sw.weight'), trans('sw.diseases')
            , trans('sw.plan_training'), trans('sw.plan_diet'), trans('sw.notes')];
        $result = array_map(function ($row) {
            return [
                trans('sw.barcode') => $row['member']['code'],
                trans('sw.name') => $row['member']['name'],
                trans('sw.height') => $row['height'],
                trans('sw.weight') => $row['weight'],
                trans('sw.diseases') => $row['diseases'],
                trans('sw.plan_training') => $row['training_plan_details'],
                trans('sw.plan_diet') => $row['diet_plan_details'],
                trans('sw.notes') => $row['notes'],
            ];
        }, $data->toArray());
        array_unshift($result, $name);
        array_unshift($result, [trans('sw.training_list')]);
        return $result;
    }

    function exportPDF(){
        $records =  GymTrainingMember::branch()->with('member')->get();
        $this->fileName = 'training-clients-' . Carbon::now()->toDateTimeString();

        $keys = ['name', 'phone'];
        if($this->lang == 'ar') $keys = array_reverse($keys);

        $title = trans('sw.training_list');

        $customPaper = array(0,0,720,1440);
        
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
                
                $notes = trans('sw.export_pdf_members');
                $this->userLog($notes, \Modules\Software\Classes\TypeConstants::ExportMemberPDF);
                
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
        $pdf = PDF::loadView('software::PDF.training-members', ['records' => $records, 'title' => $title, 'keys' => $keys])
        ->setPaper($customPaper, 'landscape')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'DejaVu Sans',
            'isPhpEnabled' => true,
            'isJavascriptEnabled' => false
        ]);

        $notes = trans('sw.export_pdf_training_members');
        $this->userLog($notes, TypeConstants::ExportTrainingMemberPDF);

        return $pdf->download($this->fileName.'.pdf');
    }


    public function createTrain()
    {
        $title = trans('sw.add_plan_training');
        $plans = GymTrainingPlan::branch()->where('type', TypeConstants::TRAINING_PLAN_TYPE)->get();
        return view('software::Front.training_member_front_form', [
            'member' => new GymTrainingMember(),'title'=>$title,  'plans' => $plans]);
    }

    public function storeTrain(GymTrainingMemberRequest $request)
    {

        $member = GymMember::branch()->where('code', $request->barcode)->first();
        $training_member_inputs = $this->prepare_inputs($request->except(['_token']));
        $training_member_inputs['user_id'] = $this->user_sw->id;
        $training_member_inputs['member_id'] = $member->id;
        $training_member_inputs['type'] = TypeConstants::TRAINING_PLAN_TYPE;

        $this->TrainingMemberRepository->create($training_member_inputs);


        $notes = str_replace(':name', $member->name, trans('sw.add_training_member'));
        $this->userLog($notes, TypeConstants::CreateTrainingMember);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listTrainingMember'));
    }

    public function createDiet()
    {
        $title = trans('sw.add_plan_diet');
        $plans = GymTrainingPlan::branch()->where('type', TypeConstants::DIET_PLAN_TYPE)->get();
        return view('software::Front.training_member_front_form', [
            'member' => new GymTrainingMember(),'title'=>$title, 'plans' => $plans]);
    }

    public function storeDiet(GymTrainingMemberRequest $request)
    {

        $member = GymMember::branch()->where('code', $request->barcode)->first();
        $training_member_inputs = $this->prepare_inputs($request->except(['_token']));
        $training_member_inputs['user_id'] = $this->user_sw->id;
        $training_member_inputs['member_id'] = $member->id;
        $training_member_inputs['type'] = TypeConstants::DIET_PLAN_TYPE;

        $this->TrainingMemberRepository->create($training_member_inputs);


        $notes = str_replace(':name', $member->name, trans('sw.add_training_member'));
        $this->userLog($notes, TypeConstants::CreateTrainingMember);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listTrainingMember'));
    }

    public function edit($id)
    {
        $member = $this->TrainingMemberRepository->withTrashed()->find($id);
        $plans = GymTrainingPlan::branch()->where('type', $member->type)->get();
        $title = trans('sw.training_member_edit');
        return view('software::Front.training_member_front_form', ['member' => $member,'title'=>$title, 'plans' => $plans]);
    }

    public function update(GymTrainingMemberRequest $request, $id)
    {
        $member_detail = GymMember::branch()->where('code', $request->barcode)->first();
        $member = GymTrainingMember::branch()->withTrashed()->find($id);
        $training_member_inputs = $this->prepare_inputs($request->except(['_token']));
        $training_member_inputs['user_id'] = $this->user_sw->id;
        $training_member_inputs['member_id'] = $member_detail->id;

        $member->update($training_member_inputs);

        $notes = str_replace(':name', $member_detail->name, trans('sw.edit_training_member'));
        $this->userLog($notes, TypeConstants::EditTrainingMember);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listTrainingMember'));
    }

    public function destroy($id)
    {
        $member = $this->TrainingMemberRepository->withTrashed()->find($id);
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


        $notes = str_replace(':name', $member_detail->name, trans('sw.delete_training_member'));
        $this->userLog($notes, TypeConstants::DeleteTrainingMember);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listTrainingMember'));
    }

    public function memberPlans($info){
        //$encrypted = base64_encode('2'.','.'000000000002');
        $encrypted = base64_encode('558'.','.'000000000558');
        $setting = Setting::branch()->select('name_ar', 'name_en', 'logo_ar', 'logo_en',  'phone', 'support_email')->first();

        $info_decode = base64_decode($info);
        $info_arr = explode(',', $info_decode);
        if($info_arr[0]){
            $member = GymMember::branch()->where('id', $info_arr[0])->first();
            $member_plans = GymTrainingMember::branch()->with(['training_plan', 'diet_plan'])->where('member_id', $info_arr[0])->get();
            $member_tracks = GymTrainingTrack::branch()->where('member_id', $info_arr[0])->get();

            if (is_array($$member_plans) && count($$member_plans) > 0){
                return view('software::Web.training_member_profile', [
                    'setting' => $setting,
                    'member' => $member,
                    'member_plans' => $member_plans
                ]);
                dd($member_plans, $member_tracks);
            }
        }


        dd('not found');
    }

    private function prepare_inputs($inputs)
    {
        $input_file = 'image';
        $uploaded='';

        $destinationPath = base_path(GymTrainingMember::$uploads_path);
        $ThumbnailsDestinationPath = base_path(GymTrainingMember::$thumbnails_uploads_path);

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


<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Generic\Http\Controllers\Front\GenericFrontController;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Exports\RecordsExport;
use Modules\Software\Http\Requests\GymBlockMemberRequest;
use Modules\Software\Http\Requests\GymNonMemberRequest;
use Modules\Software\Models\GymActivity;
use Modules\Software\Models\GymBlockMember;
use Modules\Software\Models\GymNonMember;
use Modules\Software\Repositories\GymBlockMemberRepository;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Illuminate\Container\Container as Application;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Maatwebsite\Excel\Facades\Excel;

class GymBlockMemberFrontController extends GymGenericFrontController
{
    public $BlockMemberRepository;
    private $imageManager;
    public $fileName;

    public function __construct()
    {
        parent::__construct();
        $this->imageManager = new ImageManager(new Driver());

        $this->BlockMemberRepository=new GymBlockMemberRepository(new Application);
    }


    public function index()
    {

        $title = trans('sw.block_list');
        $this->request_array = ['search'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $members = $this->BlockMemberRepository->branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $members = $this->BlockMemberRepository->branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->orderBy('id', 'DESC');
        }

        //apply filters
        $members->when($search, function ($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('id', '=', (int)$search);
                $query->orWhere('name', 'like', "%" . $search . "%");
                $query->orWhere('phone', 'like', "%" . $search . "%");
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

        return view('software::Front.blockmember_front_list', compact('members','title', 'total', 'search_query'));
    }

    function exportExcel(){
        $records = $this->BlockMemberRepository->branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->get();
        $this->fileName = 'block-clients-' . Carbon::now()->toDateTimeString();

//        $title =  trans('sw.block_list');
//        $records = $this->prepareForExport($records);

        $notes = trans('sw.export_excel_block_members');
        $this->userLog($notes, TypeConstants::ExportBlockMemberExcel);

        return Excel::download(new RecordsExport(['records' => $records, 'keys' => ['name', 'phone'],'lang' => $this->lang]), $this->fileName.'.xlsx');

//        Excel::create($this->fileName, function($excel) use ($records, $title) {
//            $excel->setTitle($title);
////            $excel->setCreator($title)->setCompany($title);
//            $excel->setDescription(trans('sw.block_members_data'));
//            $excel->sheet(trans('sw.block_members_data'), function($sheet) use ($records) {
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
        $name = [trans('sw.name'), trans('sw.phone')];
        $result = array_map(function ($row) {
            return [
                trans('sw.name') => $row['name'],
                trans('sw.phone') => $row['phone']
            ];
        }, $data->toArray());
        array_unshift($result, $name);
        array_unshift($result, [trans('sw.block_list')]);
        return $result;
    }

    function exportPDF(){
        $records = $this->BlockMemberRepository->branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->get();
        $this->fileName = 'block_members-' . Carbon::now()->toDateTimeString();

        $keys = ['name', 'phone'];
        if($this->lang == 'ar') $keys = array_reverse($keys);

        $title = trans('sw.block_members');
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
                
                $notes = trans('sw.export_pdf_block_members');
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

        $notes = trans('sw.export_pdf_block_members');
        $this->userLog($notes, TypeConstants::ExportActivityPDF);

        return $pdf->download($this->fileName.'.pdf');
    }


    public function create()
    {
        $title = trans('sw.block_member_add');
        return view('software::Front.blockmember_front_form', [
            'member' => new GymBlockMember(),'title'=>$title]);
    }

    public function store(GymBlockMemberRequest $request)
    {
        $block_member_inputs = $this->prepare_inputs($request->except(['_token']));
        $this->BlockMemberRepository->create($block_member_inputs);


        $notes = str_replace(':name', $block_member_inputs['name'], trans('sw.add_block_member'));
        $this->userLog($notes, TypeConstants::CreateBlockMember);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listBlockMember'));
    }

    public function edit($id)
    {
        $member =$this->BlockMemberRepository->branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->withTrashed()->find($id);
        $title = trans('sw.block_member_edit');
        return view('software::Front.blockmember_front_form', ['member' => $member,'title'=>$title]);
    }

    public function update(GymBlockMemberRequest $request, $id)
    {
        $member = $this->BlockMemberRepository->branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->withTrashed()->find($id);
        $block_member_inputs = $this->prepare_inputs($request->except(['_token']));
        $member->update($block_member_inputs);

        $notes = str_replace(':name', $member['name'], trans('sw.edit_block_member'));
        $this->userLog($notes, TypeConstants::EditBlockMember);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listBlockMember'));
    }

    public function destroy($id)
    {
        $member = $this->BlockMemberRepository->branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->withTrashed()->find($id);
        $member->forceDelete();
//        if($member->trashed())
//        {
//            $member->restore();
//        }
//        else
//        {
//            $member->delete();
//        }


        $notes = str_replace(':name', $member['name'], trans('sw.delete_block_member'));
        $this->userLog($notes, TypeConstants::DeleteBlockMember);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listNonMember'));
    }

    private function prepare_inputs($inputs)
    {
        $input_file = 'image';
        $uploaded='';

        $destinationPath = base_path(GymBlockMember::$uploads_path);
        $ThumbnailsDestinationPath = base_path(GymBlockMember::$thumbnails_uploads_path);

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


<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Generic\Models\Setting;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Exports\NonMembersExport;
use Modules\Software\Http\Requests\GymBannerRequest;
use Modules\Software\Models\GymBanner;
use Modules\Software\Repositories\GymBannerRepository;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Illuminate\Container\Container as Application;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Maatwebsite\Excel\Facades\Excel;

class GymBannerFrontController extends GymGenericFrontController
{

    public $BannerRepository;
    private $imageManager;
    public $fileName;

    public function __construct()
    {
        parent::__construct();
        $this->imageManager = new ImageManager(new Driver());

        $this->BannerRepository=new GymBannerRepository(new Application);
        $this->BannerRepository=$this->BannerRepository->branch();
    }


    public function index()
    {

        $title = trans('sw.banners');
        $this->request_array = ['search', 'from', 'to'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $banners = GymBanner::branch()->onlyTrashed()->with(['user' => function($q){
                $q->withTrashed();
            }])->orderBy('id', 'DESC');
        }
        else
        {
            $banners = GymBanner::branch()->with(['user' => function($q){
                $q->withTrashed();
            }])->orderBy('id', 'DESC');
        }

        //apply filters
        $banners->when(($from), function ($query) use ($from) {
            $query->whereDate('created_at', '>=', Carbon::parse($from)->format('Y-m-d'));
        })->when(($to), function ($query) use ($to) {
            $query->whereDate('created_at', '<=', Carbon::parse($to)->format('Y-m-d'));
        })->when($search, function ($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('id', '=', (int)$search);
                $query->orWhere('title', 'like', "%" . $search . "%");
                $query->orWhere('phone', 'like', "%" . $search . "%");
                $query->orWhere('content', 'like', "%" . $search . "%");
            });
        });
        $search_query = request()->query();


        if ($this->limit) {
            $banners = $banners->paginate($this->limit)->onEachSide(1);
            $total = $banners->total();
        } else {
            $banners = $banners->get();
            $total = $banners->count();
        }
        return view('software::Front.banner_front_list', compact('banners', 'title', 'total', 'search_query'));
    }

    public function gallery()
    {
        $mainSettings = Setting::branch()->first();
        $imagePath = asset(Setting::$uploads_path.'gyms/');
        $title = trans('sw.gallery');
        $this->request_array = ['search', 'from', 'to'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;

        return view('software::Front.gallery_front_list', compact('title', 'imagePath','mainSettings'));
    }


    function exportExcel(){
        $records = $this->BannerRepository->get();
        $this->fileName =  'banners-' . Carbon::now()->toDateTimeString();

//        $title = trans('sw.daily_clients');
//        $records = $this->prepareForExport($records);

        $notes = trans('sw.export_excel_banners');
        $this->userLog($notes, TypeConstants::ExportBannerExcel);

        return Excel::download(new NonMembersExport(['records' => $records, 'keys' => ['name', 'phone'],'lang' => $this->lang, 'settings' => $this->mainSettings]), $this->fileName.'.xlsx');

    }

    private function prepareForExport($data)
    {
        $name = [trans('sw.name'),trans('sw.phone'),trans('sw.activities'), trans('sw.price'), trans('sw.date')];
        $result =   array_map(function ($row) {
//            dd(implode(', ', collect($row['activities'])->pluck('name')->toArray()));
            return  [
                trans('sw.name') => $row['name'],
                trans('sw.phone') => $row['phone'],
                trans('sw.date') => Carbon::parse($row['created_at'])->toDateString()
            ];
        }, $data->toArray());
        array_unshift($result, $name);
        array_unshift($result, [trans('sw.banners')]);
        return $result;
    }

    function exportPDF(){
        $keys = ['name', 'phone', 'created_at'];
        if($this->lang == 'ar') $keys = array_reverse($keys);

        $records = $this->BannerRepository->select($keys)->get();
        $this->fileName =  'banners-' . Carbon::now()->toDateTimeString();
        foreach ($records as $record){
            $record['created_at'] = Carbon::parse($record['created_at'])->toDateString();
        }
        $title = trans('sw.banners');
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
        $pdf = PDF::loadView('software::Front.export_pdf', ['records' => $records, 'title' => $title, 'keys' => $keys])
        ->setPaper($customPaper, 'landscape')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'DejaVu Sans',
            'isPhpEnabled' => true,
            'isJavascriptEnabled' => false
        ]);

        $notes = trans('sw.export_pdf_banners');
        $this->userLog($notes, TypeConstants::ExportBannerPDF);

        return $pdf->download($this->fileName.'.pdf');
    }



    public function create()
    {
        $title = trans('sw.banner_add');
        return view('software::Front.banner_front_form', [
            'banner' => new GymBanner(),'title'=>$title]);
    }

    public function store(GymBannerRequest $request)
    {
        $banner_inputs = $this->prepare_inputs($request->except(['_token']));
        $banner_inputs['user_id'] = @$this->user_sw->id;
        $banner = $this->BannerRepository->create($banner_inputs);


        $notes = str_replace(':name', $banner_inputs['title'], trans('sw.add_banner'));
        $this->userLog($notes, TypeConstants::CreateBanner);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listBanner', $banner->id));
    }

    public function edit($id)
    {
        $banner = $this->BannerRepository->find($id);
        $title = trans('sw.banner_edit');
        return view('software::Front.banner_front_form', ['banner' => $banner, 'title' => $title]);
    }

    public function update(GymBannerRequest $request, $id)
    {
        $banner = $this->BannerRepository->withTrashed()->find($id);
        $banner_inputs = $this->prepare_inputs($request->except(['_token']));

        $banner->update($banner_inputs);

        $notes = str_replace(':name', $banner['title'], trans('sw.edit_banner'));
        $this->userLog($notes, TypeConstants::EditBanner);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listBanner'));
    }

    public function destroy($id)
    {
        $banner =$this->BannerRepository->withTrashed()->find($id);
        $banner->delete();


        $notes = str_replace(':name', $banner['title'], trans('sw.delete_banner'));
        $this->userLog($notes, TypeConstants::DeleteBanner);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listBanner'));
    }

    private function prepare_inputs($inputs)
    {
        $input_file = 'image';
        $uploaded='';

        $destinationPath = base_path(GymBanner::$uploads_path);
        $ThumbnailsDestinationPath = base_path(GymBanner::$thumbnails_uploads_path);

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


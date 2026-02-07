<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Generic\Http\Controllers\Front\GenericFrontController;

use Modules\Software\Classes\TypeConstants;
use Modules\Software\Exports\RecordsExport;
use Modules\Software\Models\GymActivity;
use Modules\Software\Models\GymActivitySubscription;
use Modules\Software\Models\GymCategory;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymMemberSubscription;
use Modules\Software\Models\GymStoreProduct;
use Modules\Software\Repositories\GymCategoryRepository;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
use Illuminate\Container\Container as Application;
use Modules\Software\Http\Requests\GymSubscriptionRequest;
use Modules\Software\Repositories\GymSubscriptionRepository;
use Modules\Software\Models\GymSubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class GymSubscriptionFrontController extends GymGenericFrontController
{
    public $GymSubscriptionRepository;
    private $imageManager;
    public $GymCategoryRepository;
    public $fileName;

    public function __construct()
    {
        parent::__construct();
        $this->imageManager = new ImageManager(new Driver());
        $this->GymSubscriptionRepository = new GymSubscriptionRepository(new Application);
        $this->GymSubscriptionRepository = $this->GymSubscriptionRepository->branch();


    }


    public function index()
    {
        $title = trans('sw.memberships');
        $this->request_array = ['id', 'search'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $subscriptions = $this->GymSubscriptionRepository->onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $subscriptions = $this->GymSubscriptionRepository->orderBy('id', 'DESC');
        }

        //apply filters
        $subscriptions->when($id, function ($query) use ($id) {
            $query->where('id','=', $id);
        });
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
        return view('software::Front.subscription_front_list', compact( 'subscriptions','title', 'total', 'search_query'));
    }

    function exportExcel(){
        $records = $this->GymSubscriptionRepository->get();
        $this->fileName = 'subscriptions-' . Carbon::now()->toDateTimeString();

//        $title = trans('sw.memberships');
//        $records = $this->prepareForExport($records);

        $notes = trans('sw.export_excel_subscription');
        $this->userLog($notes, TypeConstants::ExportSubscriptionExcel);

        return Excel::download(new RecordsExport(['records' => $records, 'keys' => ['name', 'price', 'period', 'workouts', 'freeze_limit', 'number_times_freeze'],'lang' => $this->lang, 'settings' => $this->mainSettings]), $this->fileName.'.xlsx');

//        Excel::create($this->fileName, function($excel) use ($records, $title) {
//            $excel->setTitle($title);
////            $excel->setCreator($title)->setCompany($title);
//            $excel->setDescription(trans('sw.memberships_data'));
//            $excel->sheet(trans('sw.memberships_data'), function($sheet) use ($records) {
//                $sheet->setRightToLeft(true);
//                $sheet->fromArray($records, null, 'A1', false, false);
//                $sheet->mergeCells('A1:F1');
//                $sheet->cells('A1:F1', function ($cells) {
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
        $name = [trans('sw.name'), trans('sw.price'), trans('sw.period'), trans('sw.workouts'), trans('sw.freeze_limit'), trans('sw.number_times_freeze')];
        $result =  array_map(function ($row) {
            return [
                trans('sw.name') => $row['name'],
                trans('sw.price') => $row['price'],
                trans('sw.period') => $row['period'],
                trans('sw.workouts') => $row['workouts'],
                trans('sw.freeze_limit') => $row['freeze_limit'],
                trans('sw.number_times_freeze') => $row['number_times_freeze'],
            ];
        }, $data->toArray());

        array_unshift($result, $name);
        array_unshift($result, [trans('sw.memberships')]);
        return $result;
    }

    function exportPDF(){
        $records = $this->GymSubscriptionRepository->get();
        $this->fileName = 'subscriptions-' . Carbon::now()->toDateTimeString();

        $keys = ['name', 'price', 'period', 'workouts', 'freeze_limit', 'number_times_freeze'];
        if($this->lang == 'ar') $keys = array_reverse($keys);

        $title = trans('sw.memberships');

        $customPaper = array(0,0,550,850);
        
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

        $notes = trans('sw.export_pdf_subscriptions');
        $this->userLog($notes, TypeConstants::ExportSubscriptionPDF);

        return $pdf->download($this->fileName.'.pdf');
    }

    public function create()
    {
        $title = trans('sw.subscription_add');
        $activities = GymActivity::branch()->get();
        $categories = GymCategory::branch()->where('is_subscription', true)->get();
        return view('software::Front.subscription_front_form', ['activities' => $activities,'categories' => $categories,'subscription' => new GymSubscription(), 'title' => $title]);
    }

    public function store(GymSubscriptionRequest $request)
    {
        $activities = @$request->activities;
        $time_day = @$request->time_day;
        $week_day = @$request->time_week;
        $check_workouts_per_day = @$request->check_workouts_per_day;
        $subscription_inputs = $this->prepare_inputs($request->except(['_token', 'activities', 'check_workouts_per_day', 'time_day', 'check_time_week']));
        if(!$check_workouts_per_day)
            $subscription_inputs['workouts_per_day'] = null;
        if(!$time_day) {
            $subscription_inputs['start_time_day'] = null;
            $subscription_inputs['end_time_day'] = null;
        }
        if(!$week_day){
            $subscription_inputs['time_week'] = null;
        }


        $subscription_inputs['workouts'] = @(int)$request->workouts;
        $subscription_inputs['user_id'] = Auth::guard('sw')->user()->id;
        $subscription_inputs['is_system'] = request()->has('is_system') ? 1 : 0;
        $subscription =  $this->GymSubscriptionRepository->create($subscription_inputs);
        if(is_array($activities) && count($activities) > 0 && @$subscription->id){
            foreach ($activities as $key => $value){
                // Support both legacy "id@@times" and new associative [id => times]
                $activity_id = null; $activity_training_times = null;
                if (is_string($value) && str_contains($value, '@@')) {
                    [$activity_id, $activity_training_times] = explode('@@', $value) + [null, null];
                } else {
                    $activity_id = is_numeric($key) ? (int)$key : null;
                    $activity_training_times = is_numeric($value) ? (int)$value : null;
                }
                if($activity_id && $activity_training_times && $activity_training_times > 0){
                    GymActivitySubscription::branch()->where('activity_id', $activity_id)->where('subscription_id', @$subscription->id)->forceDelete();
                    GymActivitySubscription::create([
                        'activity_id' => $activity_id,
                        'subscription_id' => @$subscription->id,
                        'training_times' => $activity_training_times,
                        'branch_setting_id' => @$this->user_sw->branch_setting_id
                    ]);
                }
            }
        }
              session()->flash('sweet_flash_message', [
                  'title' => trans('admin.done'),
                  'message' => trans('admin.successfully_added'),
                  'type' => 'success'
              ]);

        $notes = str_replace(':name', $subscription_inputs['name_'.$this->lang], trans('sw.add_subscription'));
        $this->userLog($notes, TypeConstants::CreateSubscription);

        return redirect(route('sw.listSubscription'));
    }

    public function edit($id)
    {
        $subscription = $this->GymSubscriptionRepository->with('activities')->withTrashed()->find($id);
        $title = trans('sw.subscription_edit');
        $activities = GymActivity::branch()->get();
        $categories = GymCategory::branch()->where('is_subscription', true)->get();
        return view('software::Front.subscription_front_form', ['activities' => $activities, 'categories' => $categories, 'subscription' => $subscription, 'title' => $title]);
    }

    public function update(GymSubscriptionRequest $request, $id)
    {
        $activities = @$request->activities;
        $time_day = @$request->time_day;
        $week_day = @$request->time_week;
        $check_workouts_per_day = @$request->check_workouts_per_day;
        $subscription = $this->GymSubscriptionRepository->withTrashed()->find($id);
        $subscription_inputs = $this->prepare_inputs($request->except(['_token', 'activities', 'check_workouts_per_day', 'time_day', 'check_time_week']));
        $subscription_inputs['is_web'] = @(int)$subscription_inputs['is_web'];
        $subscription_inputs['is_mobile'] = @(int)$subscription_inputs['is_mobile'];
        if(!$check_workouts_per_day)
            $subscription_inputs['workouts_per_day'] = null;
        if(!$time_day) {
            $subscription_inputs['start_time_day'] = null;
            $subscription_inputs['end_time_day'] = null;
        }
        if(!$week_day){
            $subscription_inputs['time_week'] = null;
        }
        $subscription_inputs['workouts'] = @(int)$request->workouts;
        $subscription_inputs['user_id'] = Auth::guard('sw')->user()->id;
        $subscription_inputs['is_system'] = request()->has('is_system') ? 1 : 0;

        GymActivitySubscription::branch()->where('subscription_id', @$subscription->id)->forceDelete();
        if(is_array($activities) && count($activities) > 0 && @$subscription->id){
            foreach ($activities as $key => $value){
                $activity_id = null; $activity_training_times = null;
                if (is_string($value) && str_contains($value, '@@')) {
                    [$activity_id, $activity_training_times] = explode('@@', $value) + [null, null];
                } else {
                    $activity_id = is_numeric($key) ? (int)$key : null;
                    $activity_training_times = is_numeric($value) ? (int)$value : null;
                }
                if($activity_id && $activity_training_times && $activity_training_times > 0){
                    GymActivitySubscription::create([
                        'activity_id' => $activity_id,
                        'subscription_id' => @$subscription->id,
                        'training_times' => $activity_training_times,
                        'branch_setting_id' => @$this->user_sw->branch_setting_id
                    ]);
                }
            }
        }

        $subscription->update($subscription_inputs);

              session()->flash('sweet_flash_message', [
                  'title' => trans('admin.done'),
                  'message' => trans('admin.successfully_edited'),
                  'type' => 'success'
              ]);

        $notes = str_replace(':name', $subscription_inputs['name_'.$this->lang], trans('sw.edit_subscription'));
        $this->userLog($notes, TypeConstants::EditSubscription);

        return redirect(route('sw.listSubscription'));
    }

    public function showAll()
    {
        $subscription = $this->GymSubscriptionRepository->where('user_id', $this->user->id);
        if(request()->get('trashed') == 1)
        {
            $subscription = $subscription->onlyTrashed();
        }
        $ret['data'] = $subscription->orderBy('id', 'DESC')->get()->toArray();
        return $ret;

    }

    public function destroy($id)
    {
        $subscription = GymSubscription::branch()->withTrashed()->find($id);
        if ($subscription->trashed()) {
            $subscription->restore();
        } else {

            $notes = str_replace(':name', $subscription['name'], trans('sw.delete_subscription'));
            $this->userLog($notes, TypeConstants::DeleteSubscription);
            if(\request('delete_member')) {
                $subscription->members()->delete();
                $subscription->member_subscriptions()->delete();
            }
            $subscription->delete();
        }
              session()->flash('sweet_flash_message', [
                  'title' => trans('admin.done'),
                  'message' => trans('admin.successfully_deleted'),
                  'type' => 'success'
              ]);

        return redirect(route('sw.listSubscription'));
    }
    private function prepare_inputs($inputs)
    {
        $input_file = 'image';
        $inputs = $this->uploadFile($inputs, $input_file);
        $input_file = 'sound_active';
        $inputs = $this->uploadSoundFile($inputs, $input_file);
        $input_file = 'sound_expired';
        $inputs = $this->uploadSoundFile($inputs, $input_file);

        // Handle text fields - convert empty strings to avoid null constraint violations
        $inputs['content_ar'] = isset($inputs['content_ar']) && $inputs['content_ar'] !== null ? $inputs['content_ar'] : '';
        $inputs['content_en'] = isset($inputs['content_en']) && $inputs['content_en'] !== null ? $inputs['content_en'] : '';
        
        // Set default values for fields that don't have defaults
        if (!isset($inputs['is_expire_changeable'])) {
            $inputs['is_expire_changeable'] = 0;
        }
        if (!isset($inputs['is_web'])) {
            $inputs['is_web'] = 0;
        }
        if (!isset($inputs['is_mobile'])) {
            $inputs['is_mobile'] = 0;
        }

        if(@$this->user_sw->branch_setting_id){
            $inputs['branch_setting_id'] = @$this->user_sw->branch_setting_id;
        }
        return $inputs;
    }
    private function uploadSoundFile($inputs, $file){

        $input_file = $file;
        $destinationPath = (GymSubscription::$uploads_path);
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, $mode = 0777, true, true);
        }

        if (request()->hasFile($input_file)) {
            $file = request()->file($input_file);

            if (is_file($file->getRealPath())) {
                $filename = rand(0, 20000) . time() . '.' . $file->getClientOriginalExtension();

                $uploaded = $filename;
                Storage::disk('public_uploads')->putFileAs(
                    $destinationPath,
                    $file,
                    $filename
                );
                $inputs[$input_file] = $uploaded;
            }
        }

        return $inputs;
    }
//    private function uploadFile($inputs, $file)
//    {
//        $input_file = $file;
//        $uploaded = '';
//
//        $destinationPath = base_path($this->GymSubscriptionRepository->model()::$uploads_path);
//        $ThumbnailsDestinationPath = base_path($this->GymSubscriptionRepository->model()::$thumbnails_uploads_path);
//
//        if (!File::exists($destinationPath)) {
//            File::makeDirectory($destinationPath, $mode = 0777, true, true);
//        }
//        if (!File::exists($ThumbnailsDestinationPath)) {
//            File::makeDirectory($ThumbnailsDestinationPath, $mode = 0777, true, true);
//        }
//        if (request()->hasFile($input_file)) {
//            $file = request()->file($input_file);
//
//            if (file_exists($file->getRealPath()) && getimagesize($file->getRealPath()) !== false) {
//                $filename = rand(0, 20000) . time() . '.' . $file->getClientOriginalExtension();
//
//
//                $uploaded = $filename;
//
//                $img = $this->imageManager->read($file);
//                $original_width = $img->width();
//                $original_height = $img->height();
//
//                if ($original_width > 1200 || $original_height > 900) {
//                    if ($original_width < $original_height) {
//                        $new_width = 1200;
//                        $new_height = ceil($original_height * 900 / $original_width);
//                    } else {
//                        $new_height = 900;
//                        $new_width = ceil($original_width * 1200 / $original_height);
//                    }
//
//                    //save used image
//                    $img->toJpeg(90)->save($destinationPath . $filename);
//                    $img->scale(width: $new_width, height: $new_height, function ($constraint) {
//                        $constraint->aspectRatio();
//                    })->toJpeg(90)->save($destinationPath . '' . $filename);
//
//                    //create thumbnail
//                    if ($original_width < $original_height) {
//                        $thumbnails_width = 400;
//                        $thumbnails_height = ceil($new_height * 300 / $new_width);
//                    } else {
//                        $thumbnails_height = 300;
//                        $thumbnails_width = ceil($new_width * 400 / $new_height);
//                    }
//                    $img->scale(width: $thumbnails_width, height: $thumbnails_height, function ($constraint) {
//                        $constraint->aspectRatio();
//                    })->toJpeg(90)->save($ThumbnailsDestinationPath . '' . $filename);
//                } else {
//                    //save used image
//                    $img->toJpeg(90)->save($destinationPath . $filename);
//                    //create thumbnail
//                    if ($original_width < $original_height) {
//                        $thumbnails_width = 400;
//                        $thumbnails_height = ceil($original_height * 300 / $original_width);
//                    } else {
//                        $thumbnails_height = 300;
//                        $thumbnails_width = ceil($original_width * 400 / $original_height);
//                    }
//                    $img->scale(width: $thumbnails_width, height: $thumbnails_height, function ($constraint) {
//                        $constraint->aspectRatio();
//                    })->toJpeg(90)->save($ThumbnailsDestinationPath . '' . $filename);
//                }
//                $inputs[$input_file] = $uploaded;
//            }
//
//        }
//
//
//
//        return $inputs;
//    }

    private function uploadFile($inputs, $file)
    {

        $input_file = $file;
        if (!request()->hasFile($input_file)) {
            unset($inputs[$input_file]);
            return $inputs;
        }

        $file = request()->file($input_file);

        if (!$file->isValid()) {
            unset($inputs[$input_file]);
            return $inputs;
        }

        $destinationPath = base_path(GymSubscription::$uploads_path);

        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true, true);
        }

        $extension = $file->getClientOriginalExtension();
        $filename = uniqid() . time() . ($extension ? '.'.$extension : '.jpg');

        try {
            $image = $this->imageManager->read($file);
            $image->scaleDown(1200, 1200)->toJpeg(90)->save($destinationPath.$filename);

            $inputs[$input_file] = $filename;
        } catch (\Throwable $e) {
            report($e);
            unset($inputs[$input_file]);
        }

        return $inputs;
    }
}


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
use Modules\Software\Models\GymStoreCategory;
use Modules\Software\Models\GymStoreProduct;
use Modules\Software\Models\GymSubscriptionProduct;
use Modules\Software\Models\GymSubscriptionOptionGroup;
use Modules\Software\Models\GymSubscriptionOption;
use Modules\Software\Models\GymMemberSubscriptionOption;
use Modules\Software\Models\GymPTTrainer;
use Modules\Software\Models\GymSubscriptionCategory;
use Modules\Software\Repositories\GymCategoryRepository;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
use Illuminate\Container\Container as Application;
use Modules\Software\Http\Requests\GymSubscriptionRequest;
use Modules\Software\Repositories\GymSubscriptionRepository;
use Modules\Software\Models\GymSubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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


    }


    public function index()
    {
        $title = trans('sw.memberships');
        $this->request_array = ['id', 'search', 'subscription_category_id'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $subscriptions = $this->GymSubscriptionRepository->branch()->onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $subscriptions = $this->GymSubscriptionRepository->branch()->orderBy('id', 'DESC');
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
        $subscriptions->when($subscription_category_id, function ($query) use ($subscription_category_id) {
            $query->where('subscription_category_id', $subscription_category_id);
        });
        $search_query = request()->query();

        if ($this->limit) {
            $subscriptions = $subscriptions->paginate($this->limit);
            $total = $subscriptions->total();
        } else {
            $subscriptions = $subscriptions->get();
            $total = $subscriptions->count();
        }

        $subscriptionCategories = GymSubscriptionCategory::branch()->orderBy('name_' . $this->lang)->get();

        return view('software::Front.subscription_front_list', compact( 'subscriptions','title', 'total', 'search_query', 'subscriptionCategories'));
    }

    function exportExcel(){
        $records = $this->GymSubscriptionRepository->branch()->get();
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
        $records = $this->GymSubscriptionRepository->branch()->get();
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
        $title              = trans('sw.subscription_add');
        $activities         = GymActivity::branch()->with('trainer')->get();
        $trainers           = GymPTTrainer::branch()->orderBy('name')->get();
        $categories         = GymSubscriptionCategory::branch()->orderBy('name_' . $this->lang)->get();
        $storeCategories    = GymStoreCategory::branch()->orderBy('name_' . $this->lang)->get();
        $allProducts        = GymStoreProduct::branch()->orderBy('name_' . $this->lang)->get();
        $productsForJs      = $this->buildProductsForJs();
        $activitiesForJs    = $this->buildActivitiesForJs();
        $existingProductsJs = [];
        $existingGroupsJs   = [];
        return view('software::Front.subscription_front_form', compact(
            'activities', 'trainers', 'categories', 'storeCategories', 'allProducts', 'productsForJs', 'activitiesForJs',
            'existingProductsJs', 'existingGroupsJs'
        ) + ['subscription' => new GymSubscription(), 'title' => $title]);
    }

    public function store(GymSubscriptionRequest $request)
    {
        $subscription        = null;
        $subscription_inputs = [];

        DB::transaction(function () use ($request, &$subscription, &$subscription_inputs) {
            $activities             = @$request->activities;
            $time_day               = @$request->time_day;
            $week_day               = @$request->time_week;
            $check_workouts_per_day = @$request->check_workouts_per_day;
            $subscription_inputs    = $this->prepare_inputs($request->except([
                '_token', 'activities', 'check_workouts_per_day', 'time_day', 'check_time_week',
                'products_json', 'groups_json',
            ]));
            if (!$check_workouts_per_day) $subscription_inputs['workouts_per_day'] = null;
            if (!$time_day) { $subscription_inputs['start_time_day'] = null; $subscription_inputs['end_time_day'] = null; }
            if (!$week_day) $subscription_inputs['time_week'] = null;
            $subscription_inputs['workouts']                  = @(int) $request->workouts;
            $subscription_inputs['max_extension_days']        = (int) ($subscription_inputs['max_extension_days'] ?? 0);
            $subscription_inputs['max_freeze_extension_sum']  = (int) ($subscription_inputs['max_freeze_extension_sum'] ?? 0);
            $subscription_inputs['user_id']                   = Auth::guard('sw')->user()->id;
            $subscription_inputs['is_system']                 = request()->has('is_system') ? 1 : 0;
            $subscription = $this->GymSubscriptionRepository->create($subscription_inputs);

            if (is_array($activities) && count($activities) > 0 && @$subscription->id) {
                foreach ($activities as $key => $value) {
                    $activity_id = null; $activity_training_times = null;
                    if (is_string($value) && str_contains($value, '@@')) {
                        [$activity_id, $activity_training_times] = explode('@@', $value) + [null, null];
                    } else {
                        $activity_id             = is_numeric($key) ? (int) $key : null;
                        $activity_training_times = is_numeric($value) ? (int) $value : null;
                    }
                    if ($activity_id && $activity_training_times && $activity_training_times > 0) {
                        GymActivitySubscription::branch()->where('activity_id', $activity_id)->where('subscription_id', $subscription->id)->forceDelete();
                        GymActivitySubscription::create([
                            'activity_id'       => $activity_id,
                            'subscription_id'   => $subscription->id,
                            'training_times'    => $activity_training_times,
                            'branch_setting_id' => @$this->user_sw->branch_setting_id,
                        ]);
                    }
                }
            }

            $productsJson = $request->input('products_json');
            if ($productsJson) {
                $this->syncSubscriptionProducts($subscription, json_decode($productsJson, true) ?? []);
            }
            $groupsJson = $request->input('groups_json');
            if ($groupsJson) {
                $this->syncSubscriptionGroups($subscription, json_decode($groupsJson, true) ?? []);
            }
        });

        session()->flash('sweet_flash_message', [
            'title'   => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type'    => 'success',
        ]);
        $notes = str_replace(':name', $subscription_inputs['name_' . $this->lang], trans('sw.add_subscription'));
        $this->userLog($notes, TypeConstants::CreateSubscription);

        return redirect(route('sw.listSubscription'));
    }

    public function edit($id)
    {
        $subscription = $this->GymSubscriptionRepository->branch()
            ->with(['activities', 'subscription_products.product', 'option_groups.options.product', 'option_groups.options.activity', 'option_groups.category'])
            ->withTrashed()->find($id);
        $title           = trans('sw.subscription_edit');
        $activities      = GymActivity::branch()->with('trainer')->get();
        $trainers        = GymPTTrainer::branch()->orderBy('name')->get();
        $categories      = GymSubscriptionCategory::branch()->orderBy('name_' . $this->lang)->get();
        $storeCategories = GymStoreCategory::branch()->orderBy('name_' . $this->lang)->get();
        $allProducts     = GymStoreProduct::branch()->orderBy('name_' . $this->lang)->get();
        $productsForJs   = $this->buildProductsForJs();
        $activitiesForJs = $this->buildActivitiesForJs();

        $lang = $this->lang;
        $existingProductsJs = $subscription->subscription_products->map(fn($sp) => [
            'id'             => $sp->id,
            'product_id'     => $sp->product_id,
            'product_name'   => $sp->product ? ($sp->product->getRawOriginal('display_name_' . $lang) ?: ($sp->product->{'name_' . $lang} ?? $sp->product->name_ar)) : '',
            'list_order'     => $sp->list_order,
            'is_replaceable' => (bool) $sp->is_replaceable,
        ])->values()->all();

        $existingGroupsJs = $subscription->option_groups->map(fn($g) => [
            'id'             => $g->id,
            'name_ar'        => $g->getRawOriginal('name_ar') ?? '',
            'name_en'        => $g->getRawOriginal('name_en') ?? $g->getRawOriginal('name_ar') ?? '',
            'source_type'    => $g->source_type ?? 'product',
            'selection_type' => $g->selection_type ?? 'single',
            'is_required'    => (bool) $g->is_required,
            'is_system'      => (bool) $g->is_system,
            'is_web'         => (bool) $g->is_web,
            'is_mobile'      => (bool) $g->is_mobile,
            'category_id'    => $g->category_id,
            'list_order'     => $g->list_order,
            'options'        => $g->options->map(function($o) use ($lang) {
                $isText = !$o->product_id && !$o->activity_id;
                return [
                    'id'           => $o->id,
                    'is_text'      => $isText,
                    'product_id'   => $o->product_id,
                    'activity_id'  => $o->activity_id,
                    'item_name_ar' => $isText ? ($o->getRawOriginal('name_ar') ?? '') : null,
                    'item_name_en' => $isText ? ($o->getRawOriginal('name_en') ?? '') : null,
                    'item_name'    => $isText
                        ? ($o->getRawOriginal('name_ar') ?? '')
                        : ($o->product_id
                            ? ($o->product ? ($o->product->getRawOriginal('display_name_' . $lang) ?: ($o->product->{'name_' . $lang} ?? $o->product->name_ar)) : '')
                            : ($o->activity ? ($o->activity->{'name_' . $lang} ?? $o->activity->name_ar) : '')),
                    'item_image'    => $isText ? null : ($o->product_id
                        ? ($o->product ? $this->resolveProductImage($o->product) : null)
                        : ($o->activity ? $this->resolveActivityImage($o->activity) : null)),
                    'price_modifier' => (float) ($o->price_modifier ?? 0),
                    'list_order'     => $o->list_order ?? 0,
                ];
            })->values()->all(),
        ])->values()->all();

        return view('software::Front.subscription_front_form', compact(
            'activities', 'trainers', 'categories', 'storeCategories', 'allProducts', 'productsForJs', 'activitiesForJs',
            'existingProductsJs', 'existingGroupsJs'
        ) + ['subscription' => $subscription, 'title' => $title]);
    }

    public function update(GymSubscriptionRequest $request, $id)
    {
        $subscription_inputs = [];

        DB::transaction(function () use ($request, $id, &$subscription_inputs) {
            $activities             = @$request->activities;
            $time_day               = @$request->time_day;
            $week_day               = @$request->time_week;
            $check_workouts_per_day = @$request->check_workouts_per_day;
            $subscription           = $this->GymSubscriptionRepository->withTrashed()->find($id);
            $subscription_inputs    = $this->prepare_inputs($request->except([
                '_token', 'activities', 'check_workouts_per_day', 'time_day', 'check_time_week',
                'products_json', 'groups_json',
            ]));
            $subscription_inputs['is_web']                   = @(int) $subscription_inputs['is_web'];
            $subscription_inputs['is_mobile']                = @(int) $subscription_inputs['is_mobile'];
            if (!$check_workouts_per_day) $subscription_inputs['workouts_per_day'] = null;
            if (!$time_day) { $subscription_inputs['start_time_day'] = null; $subscription_inputs['end_time_day'] = null; }
            if (!$week_day) $subscription_inputs['time_week'] = null;
            $subscription_inputs['workouts']                 = @(int) $request->workouts;
            $subscription_inputs['max_extension_days']       = (int) ($subscription_inputs['max_extension_days'] ?? 0);
            $subscription_inputs['max_freeze_extension_sum'] = (int) ($subscription_inputs['max_freeze_extension_sum'] ?? 0);
            $subscription_inputs['user_id']                  = Auth::guard('sw')->user()->id;
            $subscription_inputs['is_system']                = request()->has('is_system') ? 1 : 0;

            GymActivitySubscription::branch()->where('subscription_id', $subscription->id)->forceDelete();
            if (is_array($activities) && count($activities) > 0 && $subscription->id) {
                foreach ($activities as $key => $value) {
                    $activity_id = null; $activity_training_times = null;
                    if (is_string($value) && str_contains($value, '@@')) {
                        [$activity_id, $activity_training_times] = explode('@@', $value) + [null, null];
                    } else {
                        $activity_id             = is_numeric($key) ? (int) $key : null;
                        $activity_training_times = is_numeric($value) ? (int) $value : null;
                    }
                    if ($activity_id && $activity_training_times && $activity_training_times > 0) {
                        GymActivitySubscription::create([
                            'activity_id'       => $activity_id,
                            'subscription_id'   => $subscription->id,
                            'training_times'    => $activity_training_times,
                            'branch_setting_id' => @$this->user_sw->branch_setting_id,
                        ]);
                    }
                }
            }

            $subscription->update($subscription_inputs);

            $productsJson = $request->input('products_json');
            if ($productsJson !== null) {
                $this->syncSubscriptionProducts($subscription, json_decode($productsJson, true) ?? []);
            }
            $groupsJson = $request->input('groups_json');
            if ($groupsJson !== null) {
                $this->syncSubscriptionGroups($subscription, json_decode($groupsJson, true) ?? []);
            }
        });

        session()->flash('sweet_flash_message', [
            'title'   => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type'    => 'success',
        ]);
        $notes = str_replace(':name', $subscription_inputs['name_' . $this->lang], trans('sw.edit_subscription'));
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

    private function syncSubscriptionProducts(GymSubscription $sub, array $products): void
    {
        $branchId = (int) (@$this->user_sw->branch_setting_id ?? 1);
        $keepIds  = collect($products)->filter(fn($p) => !empty($p['id']))->pluck('id')->map(fn($id) => (int) $id)->all();

        if (empty($keepIds)) {
            GymSubscriptionProduct::branch()->where('subscription_id', $sub->id)->forceDelete();
        } else {
            GymSubscriptionProduct::branch()->where('subscription_id', $sub->id)->whereNotIn('id', $keepIds)->forceDelete();
        }

        foreach ($products as $i => $pd) {
            $productId = (int) ($pd['product_id'] ?? 0);
            if (!$productId) continue;
            $data = [
                'product_id'     => $productId,
                'list_order'     => (int) ($pd['list_order'] ?? $i),
                'is_replaceable' => !empty($pd['is_replaceable']) ? 1 : 0,
            ];
            if (!empty($pd['id'])) {
                GymSubscriptionProduct::branch()->where('id', (int) $pd['id'])->where('subscription_id', $sub->id)->update($data);
            } else {
                GymSubscriptionProduct::create(array_merge($data, ['branch_setting_id' => $branchId, 'subscription_id' => $sub->id]));
            }
        }
    }

    private function syncSubscriptionGroups(GymSubscription $sub, array $groups): void
    {
        $branchId     = (int) (@$this->user_sw->branch_setting_id ?? 1);
        $keepGroupIds = collect($groups)->filter(fn($g) => !empty($g['id']))->pluck('id')->map(fn($id) => (int) $id)->all();

        $deleteQuery = GymSubscriptionOptionGroup::branch()->where('subscription_id', $sub->id);
        if (!empty($keepGroupIds)) $deleteQuery->whereNotIn('id', $keepGroupIds);
        foreach ($deleteQuery->get() as $group) {
            $optIds = GymSubscriptionOption::where('option_group_id', $group->id)->pluck('id');
            if ($optIds->isNotEmpty() && GymMemberSubscriptionOption::whereIn('option_id', $optIds)->exists()) continue;
            GymSubscriptionOption::where('option_group_id', $group->id)->forceDelete();
            $group->forceDelete();
        }

        foreach ($groups as $i => $gd) {
            $nameAr = trim($gd['name_ar'] ?? '');
            if (!$nameAr) continue;
            $groupData = [
                'name_ar'        => $nameAr,
                'name_en'        => trim($gd['name_en'] ?? '') ?: $nameAr,
                'source_type'    => in_array($gd['source_type'] ?? '', ['product', 'activity', 'text']) ? $gd['source_type'] : 'product',
                'selection_type' => $gd['selection_type'] ?? 'single',
                'is_required'    => !empty($gd['is_required']) ? 1 : 0,
                'is_system'      => !empty($gd['is_system']) ? 1 : 0,
                'is_web'         => !empty($gd['is_web']) ? 1 : 0,
                'is_mobile'      => !empty($gd['is_mobile']) ? 1 : 0,
                'category_id'    => $gd['category_id'] ?: null,
                'list_order'     => (int) ($gd['list_order'] ?? $i),
            ];
            if (!empty($gd['id'])) {
                $group = GymSubscriptionOptionGroup::branch()->where('subscription_id', $sub->id)->find((int) $gd['id']);
                if (!$group) continue;
                $group->update($groupData);
            } else {
                $group = GymSubscriptionOptionGroup::create(array_merge($groupData, ['branch_setting_id' => $branchId, 'subscription_id' => $sub->id]));
            }
            $this->syncGroupOptions($group, $gd['options'] ?? [], $branchId);
        }
    }

    private function syncGroupOptions(GymSubscriptionOptionGroup $group, array $options, int $branchId): void
    {
        $srcType     = $group->source_type ?? 'product';
        $isActivity  = $srcType === 'activity';
        $isText      = $srcType === 'text';
        $keepOptIds  = collect($options)->filter(fn($o) => !empty($o['id']))->pluck('id')->map(fn($id) => (int) $id)->all();
        $deleteQuery = GymSubscriptionOption::where('option_group_id', $group->id);
        if (!empty($keepOptIds)) $deleteQuery->whereNotIn('id', $keepOptIds);
        foreach ($deleteQuery->get() as $opt) {
            if (!GymMemberSubscriptionOption::where('option_id', $opt->id)->exists()) $opt->forceDelete();
        }
        foreach ($options as $j => $od) {
            if ($isText) {
                $nameAr = trim($od['item_name_ar'] ?? $od['item_name'] ?? '');
                if (!$nameAr) continue;
                $optData = [
                    'product_id'     => null,
                    'activity_id'    => null,
                    'name_ar'        => $nameAr,
                    'name_en'        => trim($od['item_name_en'] ?? '') ?: $nameAr,
                    'price_modifier' => (float) ($od['price_modifier'] ?? 0),
                    'list_order'     => (int) ($od['list_order'] ?? $j),
                ];
            } else {
                $itemId = (int) ($isActivity ? ($od['activity_id'] ?? 0) : ($od['product_id'] ?? 0));
                if (!$itemId) continue;
                $optData = [
                    'product_id'     => $isActivity ? null : $itemId,
                    'activity_id'    => $isActivity ? $itemId : null,
                    'price_modifier' => (float) ($od['price_modifier'] ?? 0),
                    'list_order'     => (int) ($od['list_order'] ?? $j),
                ];
            }
            if (!empty($od['id'])) {
                GymSubscriptionOption::where('id', (int) $od['id'])->where('option_group_id', $group->id)->update($optData);
            } else {
                GymSubscriptionOption::create(array_merge($optData, ['branch_setting_id' => $branchId, 'option_group_id' => $group->id]));
            }
        }
    }

    private function resolveProductImage(GymStoreProduct $p): ?string
    {
        $raw = $p->getRawOriginal('image');
        if (!$raw) return null;
        return filter_var($raw, FILTER_VALIDATE_URL) ? $raw : asset(GymStoreProduct::$uploads_path . basename($raw));
    }

    private function resolveActivityImage(GymActivity $a): ?string
    {
        $raw = $a->getRawOriginal('image');
        if (!$raw) return null;
        return filter_var($raw, FILTER_VALIDATE_URL) ? $raw : asset(GymActivity::$uploads_path . basename($raw));
    }

    /** Build the flat activity list passed to JS for activity-type option groups. */
    private function buildActivitiesForJs(): array
    {
        $lang = $this->lang;
        return GymActivity::branch()
            ->orderBy('name_' . $lang)
            ->get(['id', 'name_ar', 'name_en', 'image'])
            ->map(function ($a) use ($lang) {
                $rawImage = $a->getRawOriginal('image');
                $image    = $rawImage
                    ? (filter_var($rawImage, FILTER_VALIDATE_URL) ? $rawImage : asset(GymActivity::$uploads_path . basename($rawImage)))
                    : null;
                return [
                    'id'    => $a->id,
                    'name'  => $a->{'name_' . $lang} ?? $a->name_ar,
                    'image' => $image,
                ];
            })
            ->values()
            ->all();
    }

    /** Build the flat product list passed to JS for option-choice filtering. */
    private function buildProductsForJs(): array
    {
        $lang = $this->lang;
        return GymStoreProduct::branch()
            ->orderBy('name_' . $lang)
            ->get(['id', 'name_ar', 'name_en', 'display_name_ar', 'display_name_en', 'category_id', 'image'])
            ->map(function ($p) use ($lang) {
                $displayName = $p->getRawOriginal('display_name_' . $lang) ?: $p->{'name_' . $lang};
                $rawImage    = $p->getRawOriginal('image');
                $image       = $rawImage
                    ? (filter_var($rawImage, FILTER_VALIDATE_URL) ? $rawImage : asset(GymStoreProduct::$uploads_path . basename($rawImage)))
                    : null;
                return [
                    'id'           => $p->id,
                    'name'         => $p->{'name_' . $lang},
                    'display_name' => $displayName,
                    'category_id'  => $p->category_id,
                    'image'        => $image,
                ];
            })
            ->values()
            ->all();
    }
}


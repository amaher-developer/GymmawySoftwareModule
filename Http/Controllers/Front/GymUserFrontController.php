<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Generic\Http\Controllers\Front\GenericFrontController;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Exports\RecordsExport;
use Modules\Software\Http\Requests\GymUserRequest;
use Modules\Software\Models\GymUser;
use Modules\Software\Models\GymUserAttendee;
use Modules\Software\Repositories\GymUserRepository;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Container\Container as Application;
use Maatwebsite\Excel\Facades\Excel;


class GymUserFrontController extends GymGenericFrontController
{
    public $GymUserRepository;
    private $imageManager;
    public $fileName;

    public function __construct()
    {
        parent::__construct();

        $this->GymUserRepository = new GymUserRepository(new Application);
        $this->imageManager = new ImageManager(new Driver());
    }


    public function index()
    {

        $title = trans('sw.users');
        $this->request_array = ['search'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if (request('trashed')) {
            $users = $this->GymUserRepository->branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->onlyTrashed()->orderBy('id', 'DESC');
        } else {
            $users = $this->GymUserRepository->branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->orderBy('id', 'DESC');
        }

        if(!@$this->user_sw->is_super_user){
            $users->where('is_super_user', 0);
        }

        //apply filters
        $users->when($search, function ($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('id', '=', (int)$search);
                $query->orWhere('name', 'like', "%" . $search . "%");
                $query->orWhere('phone', 'like', "%" . $search . "%");
//            $query->whereRaw(' json_extract(activities->"$[*].name_ar", "'.$search.'")');
            });
        });
        $search_query = request()->query();

//        if (request()->exists('export')) {
//            $users = $users->get();
//            $array = $this->prepareForExport($users);
//
//            $fileName = 'users-' . Carbon::now()->toDateTimeString();
//            $file = Excel::create($fileName, function ($excel) use ($array) {
//                $excel->setTitle('title');
//                $excel->sheet(trans('sw._users_data'), function ($sheet) use ($array) {
//                    if ($this->lang == 'ar') $sheet->setRightToLeft(true);
//                    $sheet->fromArray($array);
//                });
//            });
//
//            $file = $file->string('xlsx');
//            return [
//                'name' => $fileName,
//                'file' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64," . base64_encode($file)
//            ];
//        }

        if ($this->limit) {
            $users = $users->paginate($this->limit);
            $total = $users->total();
        } else {
            $users = $users->get();
            $total = $users->count();
        }

        return view('software::Front.user_front_list', compact('title', 'total', 'users', 'search_query'));
    }

    function exportExcel(){
        $records = $this->GymUserRepository->get();
        $this->fileName = 'users-' . Carbon::now()->toDateTimeString();

//        $title = trans('sw.users');
//        $records = $this->prepareForExport($records);

        $notes = trans('sw.export_excel_users');
        $this->userLog($notes, TypeConstants::ExportUserExcel);

        return Excel::download(new RecordsExport(['records' => $records, 'keys' => ['name', 'email', 'phone', 'start_time_work', 'end_time_work'],'lang' => $this->lang]), $this->fileName.'.xlsx');

//        Excel::create($this->fileName, function($excel) use ($records, $title) {
//            $excel->setTitle($title);
////            $excel->setCreator($title)->setCompany($title);
//            $excel->setDescription(trans('sw.users_data'));
//            $excel->sheet(trans('sw.users_data'), function($sheet) use ($records) {
//                $sheet->setRightToLeft(true);
//                $sheet->fromArray($records, null, 'A1', false, false);
//                $sheet->mergeCells('A1:E1');
//                $sheet->cells('A1:E1', function ($cells) {
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
        $name = [trans('sw.name'), trans('sw.email'), trans('sw.phone'), trans('sw.start_time_work'), trans('sw.end_time_work')];
       for($i=0;$i < count($data);$i++){
           $data[$i]['date_from'] = (string)$data[$i]["start_time_work"];
           $data[$i]['date_to'] = (string)$data[$i]["end_time_work"];
       }

        $result = array_map(function ($row) {
            return [
                trans('sw.name') => $row['name'],
                trans('sw.email') => $row['email'],
                trans('sw.phone') => $row['phone'],
                trans('sw.start_time_work') => $row["date_from"],
                trans('sw.end_time_work') => $row["date_to"],
            ];
        }, $data->toArray());

        array_unshift($result, $name);
        array_unshift($result, [trans('sw.users')]);
        return $result;
    }
    function exportPDF(){
        $records = $this->GymUserRepository->get();
        $this->fileName = 'users-' . Carbon::now()->toDateTimeString();

        $keys = ['name', 'email', 'phone', 'start_time_work', 'end_time_work'];
        if($this->lang == 'ar') $keys = array_reverse($keys);

        $title = trans('sw.users');
        
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
                
                $notes = trans('sw.export_pdf_users');
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
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'DejaVu Sans',
            'isPhpEnabled' => true,
            'isJavascriptEnabled' => false
        ]);

        $notes = trans('sw.export_pdf_users');
        $this->userLog($notes, TypeConstants::ExportActivityPDF);

        return $pdf->download($this->fileName.'.pdf');
    }

    public function indexJson(){
        $users = $this->GymUserRepository->select(['id', 'name'])->orderBy('id', 'DESC')->get();
        return Response::json($users->toArray());
    }

    public function create()
    {
        $title = trans('sw.user_add');
        return view('software::Front.user_front_form', ['user' => new GymUser(),'title'=>$title]);
    }

    public function store(GymUserRequest $request)
    {
        $user_inputs = $this->prepare_inputs($request->except(['_token']));
        
        // Encrypt password if provided
        if(isset($user_inputs['password']) && !empty($user_inputs['password'])){
            $user_inputs['password'] = Hash::make($user_inputs['password']);
        }
        
        // If permission_group_id is set, use permissions from the group
        if($request->permission_group_id){
            $permissionGroup = \Modules\Software\Models\GymUserPermission::find($request->permission_group_id);
            if($permissionGroup){
                $user_inputs['permissions'] = $permissionGroup->permissions;
            }
        }
        
        $this->GymUserRepository->create($user_inputs);
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);


        $notes = str_replace(':name', $user_inputs['name'], trans('sw.add_user'));
        $this->userLog($notes, TypeConstants::CreateUser);

        return redirect(route('sw.listUser'));
    }

    public function edit($id)
    {
        $user = $this->GymUserRepository->find($id);
        $title = trans('sw.user_edit');
        return view('software::Front.user_front_form', ['user' => $user,'title'=>$title]);
    }

    public function update(GymUserRequest $request, $id)
    {
        $user = $this->GymUserRepository->find($id);
        $user_inputs = array_filter($this->prepare_inputs($request->except(['_token'])));
        
        // Encrypt password if provided
        if(isset($user_inputs['password']) && !empty($user_inputs['password'])){
            $user_inputs['password'] = Hash::make($user_inputs['password']);
        } else {
            // Remove password from inputs if not provided (to keep current password)
            unset($user_inputs['password']);
        }
        
        // If permission_group_id is set, use permissions from the group
        if($request->permission_group_id){
            $permissionGroup = \Modules\Software\Models\GymUserPermission::find($request->permission_group_id);
            if($permissionGroup){
                $user_inputs['permissions'] = $permissionGroup->permissions;
            }
        }else{
            $user_inputs['permission_group_id'] = null;
        }

        $user->update($user_inputs);
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);


        $notes = str_replace(':name', $user_inputs['name'], trans('sw.edit_user'));
        $this->userLog($notes, TypeConstants::EditUser);

        return redirect(route('sw.listUser'));
    }


    public function destroy($id)
    {
        $user = $this->GymUserRepository->find($id);
        if($user->trashed()){
            $user->restore();
        } else {
            $user->delete();


            $notes = str_replace(':name', $user['name'], trans('sw.delete_user'));
            $this->userLog($notes, TypeConstants::DeleteUser);
        }
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listUser'));
    }
    public function editProfile()
    {
        $user = $this->GymUserRepository->find(Auth::guard('sw')->user()->id);
        $title = trans('sw.user_profile');
        return view('software::Front.user_front_profile', ['user' => $user,'title'=>$title]);
    }
    public function updateProfile(Request $request)
    {
        $user = $this->GymUserRepository->find(Auth::guard('sw')->user()->id);
        $user_inputs = array_filter($this->prepare_inputs($request->only(['image', 'name', 'phone', 'password', 'address'])));
        if($this->user_sw->is_super_user){
            $user_inputs['start_time_work'] = $request->start_time_work ;
            $user_inputs['end_time_work'] = $request->end_time_work ;
            $user_inputs['title'] = $request->title ;
            $user_inputs['email'] = $request->email ;
            $user_inputs['salary'] = $request->salary ;

        }
        // Encrypt password if provided
        if(isset($user_inputs['password']) && !empty($user_inputs['password'])){
            $user_inputs['password'] = Hash::make($user_inputs['password']);
        } else {
            // Remove password from inputs if not provided (to keep current password)
            unset($user_inputs['password']);
        }
        $user->update($user_inputs);
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        return redirect(route('sw.editUserProfile'));
    }



    private function prepare_inputs($inputs)
    {
        $input_file = 'image';
        $uploaded='';

        $destinationPath = base_path(GymUser::$uploads_path);
        $ThumbnailsDestinationPath = base_path(GymUser::$thumbnails_uploads_path);

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
            $inputs['tenant_id'] = @$this->user_sw->tenant_id;
        }
//        !$inputs['deleted_at']?$inputs['deleted_at']=null:'';

        return $inputs;
    }

    public function userAttendees(){

        $title = trans('sw.user_login');
        $last_enter_member = GymUserAttendee::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->with('user:id,name')->orderBy('id', 'desc')->first();
//        $colors = ['purple', 'grey', 'yellow', 'green', 'red'];

        return view('software::Front.user_front_attendee', compact(['title', 'last_enter_member']));
    }


    public function userAttendeesStore(Request $request)
    {
        $code = preg_replace("/[^0-9]/", "", $request->code);
        $enquiry = intval($request->enquiry);
        $msg = '';
        $status = false;
        $user = GymUser::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('id', $code)->first();
        if($user){
            // Validate that the logged-in user matches the entered code
            $loggedInUser = Auth::guard('sw')->user();
            if ($loggedInUser && $loggedInUser->id != $user->id) {
                $msg = trans('sw.code_does_not_match');
                return Response::json(['check_time' => null, 'check_date' => null,'user' => null,'msg' => $msg, 'status' => false, 'type' => 0], 403);
            }

            $status = true;
            $last_attendee_today = GymUserAttendee::whereDate('created_at', Carbon::now()->toDateString())->orderBy('id', 'desc')->first();
            GymUserAttendee::insert(['branch_setting_id' => $user->branch_id, 'user_id' => $user->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);

            $check_date = $check_in_date = Carbon::now()->format('Y-m-d');
            $check_time = $check_in_time = Carbon::now()->format('h:i a');
            $check_out_date = '';
            $check_out_time = '';
            $msg = trans('sw.user_login_at', ['name' => $user->name, 'datetime' => $check_date.' '.$check_time]);
            if($last_attendee_today){
                $check_in_date = $last_attendee_today->created_at->format('Y-m-d');
                $check_in_time = $last_attendee_today->created_at->format('h:i a');

                $check_date = $check_out_date = Carbon::now()->format('Y-m-d');
                $check_time = $check_out_time = Carbon::now()->format('h:i a');
                $msg = trans('sw.user_logout_at', ['name' => $user->name, 'datetime' => $check_date.' '.$check_time]);
            }
            $result = ['check_in' => '<i class="fa fa-calendar text-muted"></i> ' . $check_in_date . ' <i class="fa fa-clock-o text-muted"></i> '. $check_in_time,
                'check_out' => (@$check_out_date ?  '<i class="fa fa-calendar text-muted"></i> ' . $check_out_date . ' <i class="fa fa-clock-o text-muted"></i> '. @$check_out_time : '' ),
                'user' => $user, 'msg' => $msg, 'status' => $status];
            return Response::json($result, 200);

        }
        $msg = trans('sw.no_code_found');
        return Response::json(['check_time' => null, 'check_date' => null,'user' => null,'msg' => $msg, 'status' => $status, 'type' => 0], 200);
    }

    public function attendanceGeofenceCheck(Request $request)
    {
        $latitude = floatval($request->latitude);
        $longitude = floatval($request->longitude);

        // Validate coordinates
        if (!$latitude || !$longitude || $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            return Response::json([
                'status' => false,
                'message' => trans('sw.invalid_gps_coordinates')
            ], 422);
        }
        // Get gym coordinates from settings
        $gymLat = floatval($this->mainSettings->latitude ?? 0);
        $gymLon = floatval($this->mainSettings->longitude ?? 0);

        if (!$gymLat || !$gymLon) {
            return Response::json([
                'status' => false,
                'message' => trans('sw.gym_location_not_configured')
            ], 500);
        }

        // Calculate distance using Haversine formula
        $distance = $this->calculateDistance($latitude, $longitude, $gymLat, $gymLon);

        // Check if within allowed radius (100 meters + 20m GPS accuracy margin)
        $allowedRadius = 120;
        if ($distance > $allowedRadius) {
            return Response::json([
                'status' => false,
                'message' => trans('sw.outside_allowed_area', ['distance' => round($distance)])
            ], 403);
        }

        // Get current user
        $user = Auth::guard('sw')->user();
        if (!$user) {
            return Response::json([
                'status' => false,
                'message' => trans('sw.user_not_authenticated')
            ], 401);
        }

        // Record attendance
        $lastAttendeeToday = GymUserAttendee::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::now()->toDateString())
            ->orderBy('id', 'desc')
            ->first();

        GymUserAttendee::insert([
            'branch_setting_id' => $user->branch_setting_id,
            'user_id' => $user->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        $checkDate = Carbon::now()->format('Y-m-d');
        $checkTime = Carbon::now()->format('h:i a');

        if ($lastAttendeeToday) {
            $msg = trans('sw.user_logout_at', ['name' => $user->name, 'datetime' => $checkDate . ' ' . $checkTime]);
            $checkInDate = $lastAttendeeToday->created_at->format('Y-m-d');
            $checkInTime = $lastAttendeeToday->created_at->format('h:i a');
        } else {
            $msg = trans('sw.user_login_at', ['name' => $user->name, 'datetime' => $checkDate . ' ' . $checkTime]);
            $checkInDate = $checkDate;
            $checkInTime = $checkTime;
        }

        // Get today's statistics
        $checkInCount = GymUserAttendee::whereDate('created_at', Carbon::now()->toDateString())->count();

        return Response::json([
            'status' => true,
            'message' => $msg,
            'check_in' => '<i class="fa fa-calendar text-muted"></i> ' . $checkInDate . ' <i class="fa fa-clock-o text-muted"></i> ' . $checkInTime,
            'check_out' => '',
            'distance' => round($distance, 2)
        ], 200);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters

        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLon / 2) * sin($deltaLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

}


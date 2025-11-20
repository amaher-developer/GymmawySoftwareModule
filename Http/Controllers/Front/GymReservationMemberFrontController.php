<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Software\Classes\TypeConstants;
use Modules\Software\Exports\NonMembersExport;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymPotentialMember;
use Modules\Software\Repositories\GymReservationMemberRepository;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Illuminate\Container\Container as Application;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Maatwebsite\Excel\Facades\Excel;

class GymReservationMemberFrontController extends GymGenericFrontController
{

    public $ReservationMemberRepository;
    private $imageManager;
    public $fileName;

    public function __construct()
    {
        parent::__construct();
        $this->imageManager = new ImageManager(new Driver());

        $this->ReservationMemberRepository=new GymReservationMemberRepository(new Application);
        $this->ReservationMemberRepository=$this->ReservationMemberRepository->branch()->reservation();
    }


    public function index()
    {
        $title = trans('sw.reservation_clients');
        $this->request_array = ['search', 'from', 'to', 'status', 'type'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            // Optimize: Use select to limit columns
            $members = GymPotentialMember::branch()->reservation()->onlyTrashed()
                ->select('id', 'name', 'phone', 'status', 'type', 'subscription_id', 'activity_id', 'pt_subscription_id', 'user_id', 'created_at')
                ->with([
                    'member' => function($q) {
                        $q->select('id', 'name', 'phone', 'image');
                    },
                    'member.member_subscription_info' => function($q) {
                        $q->select('id', 'member_id', 'expire_date', 'status');
                    },
                    'user' => function($q){
                        $q->select('id', 'name')->withTrashed();
                    }
                ])
                ->orderBy('id', 'DESC');
        }
        else
        {
            // Optimize: Use select to limit columns and add nested subscription relation
            $members = GymPotentialMember::branch()->reservation()
                ->select('id', 'name', 'phone', 'status', 'type', 'subscription_id', 'activity_id', 'pt_subscription_id', 'user_id', 'created_at')
                ->with([
                    'member' => function($q) {
                        $q->select('id', 'name', 'phone', 'image');
                    },
                    'member.member_subscription_info' => function($q) {
                        $q->select('id', 'member_id', 'expire_date', 'status');
                    },
                    'activity' => function($q) {
                        $q->select('id', 'name_ar', 'name_en');
                    },
                    'pt_subscription' => function($q) {
                        $q->select('id', 'name_ar', 'name_en');
                    },
                    'subscription' => function($q) {
                        $q->select('id', 'name_ar', 'name_en');
                    },
                    'user' => function($q){
                        $q->select('id', 'name')->withTrashed();
                    }
                ])
                ->orderBy('id', 'DESC');
        }

        //apply filters
        $members->when(($from), function ($query) use ($from) {
            $query->whereDate('created_at', '>=', Carbon::parse($from)->format('Y-m-d'));
        })->when(($to), function ($query) use ($to) {
            $query->whereDate('created_at', '<=', Carbon::parse($to)->format('Y-m-d'));
        })->when(($status || ($status == 0) && ($status != '')), function ($query) use ($status) {
            $query->where('status',$status);
        })->when(($type) && ($type != ''), function ($query) use ($type) {
            if($type == 1)
                $query->where('subscription_id', '!=', null);
            elseif($type == 2)
                $query->where('activity_id', '!=', null);
            else
                $query->where('pt_subscription_id', '!=', null);

        })->when($search, function ($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('id', '=', (int)$search);
                $query->orWhere('name', 'like', "%" . $search . "%");
                $query->orWhere('phone', 'like', "%" . $search . "%");
            });
        });
        $search_query = request()->query();


        if ($this->limit) {
            $members = $members->paginate($this->limit)->onEachSide(1);
            $total = $members->total();
            // Process paginated results
            $members->getCollection()->transform(function($member) {
                return $this->processReservationMemberForBlade($member);
            });
        } else {
            $members = $members->get();
            $total = $members->count();
            // Process collection results
            $members = $members->map(function($member) {
                return $this->processReservationMemberForBlade($member);
            });
        }

        return view('software::Front.reservation_member_front_list', compact('members', 'title', 'total', 'search_query'));
    }

    /**
     * Process reservation member data for Blade view (moved from Blade to Controller)
     * Pre-computes values to avoid logic and Carbon parsing in Blade templates
     */
    private function processReservationMemberForBlade($member)
    {
        // Pre-compute expire date formatting (move Carbon parsing from Blade to Controller)
        if ($member->member && $member->member->member_subscription_info && $member->member->member_subscription_info->expire_date) {
            $member->member->member_subscription_info->formatted_expire_date = 
                \Carbon\Carbon::parse($member->member->member_subscription_info->expire_date)->toDateString();
            $member->member->member_subscription_info->expire_color = 
                ($member->member->member_subscription_info->status == 0) ? 'green' : 'red';
        }
        
        return $member;
    }

    public function updateReservationMember(){
        $reservation_members= GymPotentialMember::branch()->reservation()->select('id', 'phone', 'status')->where("status", TypeConstants::NotFound)->get();
        if (is_array($$reservation_members) && count($$reservation_members) > 0){
            $reservation_member_phones = collect($reservation_members)->pluck('phone')->toArray();
            $members = GymMember::branch()->select('id', 'phone')->whereIn('phone', $reservation_member_phones)->count();
            if($members > 0){
                foreach ($reservation_members as $reservation_member){
                    if(GymMember::branch()->where('phone', $reservation_member->phone)->first()){
                        $reservation_member->status = TypeConstants::Found;
                        $reservation_member->save();
                    }
                }
            }
        }
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_processed'),
            'type' => 'success'
        ]);
        return  Response::json(['status' => true], 200);
    }

    function exportExcel(){
        $records = $this->ReservationMemberRepository->get();
        $this->fileName =  'potential-members-' . Carbon::now()->toDateTimeString();

//        $title = trans('sw.daily_clients');
//        $records = $this->prepareForExport($records);

        $notes = trans('sw.export_excel_reservation_members');
        $this->userLog($notes, TypeConstants::ExportReservationMemberExcel);

        return Excel::download(new NonMembersExport(['records' => $records, 'keys' => ['name', 'phone'],'lang' => $this->lang]), $this->fileName.'.xlsx');

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
        array_unshift($result, [trans('sw.reservation_clients')]);
        return $result;
    }

    function exportPDF(){
        $keys = ['name', 'phone', 'created_at'];
        if($this->lang == 'ar') $keys = array_reverse($keys);

        $records = $this->ReservationMemberRepository->select($keys)->get();
        $this->fileName =  'potential-members-' . Carbon::now()->toDateTimeString();
        foreach ($records as $record){
            $record['created_at'] = Carbon::parse($record['created_at'])->toDateString();
        }
        $title = trans('sw.reservation_clients');
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

        $notes = trans('sw.export_pdf_reservation_members');
        $this->userLog($notes, TypeConstants::ExportReservationMemberPDF);

        return $pdf->download($this->fileName.'.pdf');
    }



    public function destroy($id)
    {
        $member = GymPotentialMember::withTrashed()->find($id);
        $member->delete();


        $notes = str_replace(':name', $member['name'], trans('sw.delete_reservation_member'));
        $this->userLog($notes, TypeConstants::DeleteReservationMember);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listReservationMember'));
    }

    private function prepare_inputs($inputs)
    {
        $input_file = 'image';
        $uploaded='';

        $destinationPath = base_path(GymPotentialMember::$uploads_path);
        $ThumbnailsDestinationPath = base_path(GymPotentialMember::$thumbnails_uploads_path);

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

<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Generic\Http\Controllers\Front\GenericFrontController;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Exports\RecordsExport;
use Modules\Software\Http\Requests\GymUserRequest;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymMemberAttendee;
use Modules\Software\Models\GymMemberSubscription;
use Modules\Software\Models\GymPTMember;
use Modules\Software\Models\GymTrainingMember;
use Modules\Software\Models\GymTrainingTrack;
use Modules\Software\Models\GymUser;
use Modules\Software\Models\GymUserAttendee;
use Modules\Software\Repositories\GymUserRepository;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Container\Container as Application;
use Maatwebsite\Excel\Facades\Excel;


class GymCustomerFrontController extends GymGenericFrontController
{

    private $customer;
    private $imageManager;
    public function __construct()
    {
        parent::__construct();
        $request = request();

        if (app()->runningInConsole() || ! $request->hasSession()) {
            $this->customer = null;
        } else {
            $this->customer = $request->session()->get('swCustomer');
        }
        $this->imageManager = new ImageManager(new Driver());
    }

    public function show()
    {
        $title = trans('sw.user_profile');
        $number_of_attendees = 0;
        $number_of_attendees = GymMemberAttendee::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('created_at', '>=', Carbon::now()->subHour(2)->toDateTimeString())->count();
        $member = GymMember::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->with(['member_subscription_info.subscription' => function($q){$q->withTrashed();}])->where('id', @$this->customer->id)->first();

        return view('software::Web.customer_web_profile', ['title' => $title, 'member' => $member, 'number_of_attendees' => $number_of_attendees]);
    }

    public function subscriptions()
    {
        $title = trans('sw.memberships');
        $subscriptions = GymMemberSubscription::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->with(['subscription' => function($q){$q->withTrashed();}, 'member'])->where('member_id', $this->customer->id)->orderBy('id', 'desc')->limit(10)->get();
        return view('software::Web.customer_web_subscriptions', ['title' => $title, 'member' => $this->customer, 'subscriptions' => $subscriptions]);
    }
    public function activities()
    {
        $title = trans('sw.memberships');
        $subscriptions = GymMemberSubscription::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->with(['subscription' => function($q){$q->withTrashed();}, 'member'])->where('member_id', $this->customer->id)->orderBy('id', 'desc')->limit(10)->get();
        return view('software::Web.customer_web_activities', ['title' => $title, 'member' => $this->customer, 'subscriptions' => $subscriptions]);
    }

    public function tracking()
    {
        $title = trans('sw.training_tracks');

        $search_query = request()->query();
        $tracks = GymTrainingTrack::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->with(['member'])->where('member_id', $this->customer->id)->orderBy('date', 'desc')->paginate(5);
        return view('software::Web.customer_web_tracking', ['search_query' => $search_query, 'title' => $title, 'member' => $this->customer, 'tracks' => $tracks]);
    }

    public function pt()
    {
        $title = trans('sw.pt');

        $pts = GymPTMember::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->with(['member', 'pt_class.pt_subscription'])
            ->where('member_id', $this->customer->id)
            ->where('joining_date', '<=', Carbon::now())
            ->where('expire_date', '>=', Carbon::now())
            ->orderBy('id', 'desc')->get();

        return view('software::Web.customer_web_pt', ['title' => $title, 'member' => $this->customer, 'pts' => $pts]);
    }
    public function training()
    {
        $title = trans('sw.training_plans');
        $search_query = request()->query();
        $trainings = GymTrainingMember::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('member_id', $this->customer->id)->orderBy('id', 'desc')->paginate(5);
        return view('software::Web.customer_web_training', ['title' => $title, 'member' => $this->customer, 'trainings'=> $trainings, 'search_query' => $search_query]);
    }
    public function review()
    {
        $title = trans('sw.review');
        return view('software::Web.customer_web_review', ['title' => $title, 'member' => $this->customer]);
    }

    public function login()
    {
        $title = trans('sw.login');
        $this->customer = request()->session()->get('swCustomer');//Cache::store('file')->get('swCustomer');
        if($this->customer) {
            return redirect()->route('sw.showCustomerProfile');
        }

        return view('software::Web.customer_web_login', compact('title'));
    }
    public function loginSubmit(Request $request)
    {
        $title = trans('sw.login');

        $member = GymMember::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->with(['member_subscription_info.subscription' => function($q){$q->withTrashed();}])->where('phone', @$request->phone)->where('code', @$request->code)->first();
        if($member){
            request()->session()->put('swCustomer', $member);
            //Cache::store('file')->put('swCustomer',$member );
            return redirect()->route('sw.showCustomerProfile');
        }

        return redirect()->route('sw.customerLogin');
    }

    public function logout(){
        request()->session()->put('swCustomer', []);
        return  redirect()->route('sw.customerLogin');
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



}


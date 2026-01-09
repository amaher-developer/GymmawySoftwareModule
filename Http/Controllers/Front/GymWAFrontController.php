<?php

namespace Modules\Software\Http\Controllers\Front;

use App\Modules\Access\Models\User;
use Modules\Generic\Models\Setting;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Classes\WA;
use Modules\Software\Http\Requests\GymSMSRequest;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymNonMember;
use Modules\Software\Models\GymSMSLog;
use Modules\Software\Models\GymWALog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;


class GymWAFrontController extends GymGenericFrontController
{
    public $GymUserRepository;
    public $consume_user_count = 0;
    public $consume_message_count = 0;
    public function __construct()
    {
        $this->consume_user_count = GymWALog::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->distinct()->where('status', 1)->whereDate('created_at', Carbon::now()->toDateString())->count(['phone']);
        $this->consume_message_count = GymWALog::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('status', 1)->whereDate('created_at', '>', Carbon::now()->subMonth()->toDateString())->count(['phone']);

        parent::__construct();
    }


    public function create()
    {
        $title = trans('sw.wa_add');
        $max_users = TypeConstants::WA_MAX_USER;
        $max_messages = TypeConstants::WA_MAX_MESSAGE;
        $setting = Setting::select('wa_details')->first();
        $max_messages = @$setting->wa_details['wa_package_messages'];


        return view('software::Front.wa_front_create', ['title'=>$title, 'max_users'=>$max_users, 'max_messages'=>$max_messages
            , 'consume_user_count' => $this->consume_user_count, 'consume_message_count' => $this->consume_message_count
        ]);
    }

    public function store(GymSMSRequest $request)
    {
        $user_inputs = $request->except(['_token']);
        $message = str_replace(array("\r\n", "\r", "\n"), "", strip_tags($user_inputs['message'])) ;
        $phones = explode(',', $user_inputs['phones']);
        $image = $this->prepare_inputs('image');
        $welcome_msg = trans('sw.wa_hello_msg', ['name' => $this->mainSettings->name]);
        $max_users = TypeConstants::WA_MAX_USER;
        if(!$image) $image = @$this->mainSettings->logo;

        if(count($phones) > $max_users  ){
            session()->flash('sweet_flash_message', [
                'title' => 'error',
                'message' => trans('sw.greater_than_max_users', ['max_users' => $max_users]),
                'type' => 'error'
            ]);
            return redirect(route('sw.createWA'));
        }

        $setting = Setting::branch()->select('wa_details')->first();
        if(((int)$this->consume_message_count <= @(int)$setting->wa_details['wa_package_messages'])) {
            if (is_array($$phones) && count($$phones) > 0){
                foreach ($phones as $phone) {
                    $wa = new WA();
//                    $wa->sendText(trim($phone), $message);
                    $message_id = $wa->sendTextImageWithTemplate(trim($phone), 'gymmawy_hello_message',
                        [
                            [
                                "type" => "text",
                                "text" => "*".', '.$welcome_msg."*"
                            ],
                            [
                                "type" => "text",
                                "text" => "*".@$message."*"
                            ],
                            [
                                "type" => "text",
                                "text" => "*".@$this->mainSettings->phone."*"
                            ]
                        ]
                        , $image);

//                        GymWALog::create([
//                                'user_id' => @Auth::guard('sw')->user()->id,
//                                'status' => @$message_id ? 1 : 0,
//                                'phone' => $phone,
//                                "content" => $message,
//                                "message_id" => @$message_id,
//                                "created_at" => Carbon::now(),
//                                "updated_at" => Carbon::now(),
//                        ]);

                }
                session()->flash('sweet_flash_message', [
                    'title' => trans('admin.done'),
                    'message' => trans('admin.successfully_send'),
                    'type' => 'success'
                ]);
            }else{
                session()->flash('sweet_flash_message', [
                    'title' => 'error',
                    'message' => trans('global.unsuccessfully_send'),
                    'type' => 'error'
                ]);
            }
        }else
            session()->flash('sweet_flash_message', [
                'title' => 'error',
                'message' => trans('sw.no_enough_balance'),
                'type' => 'error'
            ]);

        return redirect(route('sw.createWA'));
    }

    public function index()
    {
        $title = trans('sw.wa_logs');

        $logs = GymWALog::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->with(['user']);
        if(!$this->user_sw->is_super_user)
            $logs->where('user_id', $this->user_sw->id);
        $logs->orderBy('id', 'DESC');
        $logs = $logs->paginate($this->limit);
        $total = $logs->total();


        return view('software::Front.wa_log_front_list', compact('logs','title', 'total'));
    }
    public function phonesByAjax(){
        $phones = [];
        $type = request('type');
        if($type == 2){
            $phones = GymNonMember::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->pluck('phone')->toArray();
        }else if($type == 3){
            $phones = GymMember::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->pluck('phone')->toArray();
        }
        return implode(', ', $phones);
    }


    private function prepare_inputs($inputs)
    {
        $input_file = $inputs;
        if (request()->hasFile($input_file)) {
            $file = request()->file($input_file);
//            $filename = rand(0, 20000) . time() . '.' . $file->getClientOriginalExtension();
            $filename = asset(User::$uploads_path.'/wa_image.' . $file->getClientOriginalExtension());
            $destinationPath = base_path(User::$uploads_path);
            $upload_success = $file->move($destinationPath, $filename);
            if ($upload_success) {
                return $filename;
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
        return $inputs;
    }


}


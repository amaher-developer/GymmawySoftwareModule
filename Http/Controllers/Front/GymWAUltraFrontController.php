<?php

namespace Modules\Software\Http\Controllers\Front;

use App\Modules\Access\Models\User;
use Modules\Generic\Models\Setting;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Classes\WA;
use Modules\Software\Classes\WAUltramsg;
use Modules\Software\Http\Requests\GymSMSRequest;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymNonMember;
use Modules\Software\Models\GymSMSLog;
use Modules\Software\Models\GymWALog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;


class GymWAUltraFrontController extends GymGenericFrontController
{
    public $GymUserRepository;
    public $consume_user_count = 0;
    public $consume_message_count = 0;
    public function __construct()
    {
        parent::__construct();
    }


    public function create()
    {
        $title = trans('sw.wa_add');
        $max_messages = TypeConstants::WA_ULTRA_MAX_MESSAGE;
        $token_check_error = 0;
        $statistic = new WAUltramsg();
        if(@$statistic->statistics()->error){
            $token_check_error = 1;
        }
        $sent_messages = @$statistic->statistics()->messages_statistics->sent ?? 0;
        return view('software::Front.wa_ultra_front_create', ['title'=>$title,'max_messages'=>$max_messages, 'consume_message_count' => $sent_messages, 'token_check_error' => $token_check_error
        ]);
    }

    public function store(GymSMSRequest $request)
    {
        $user_inputs = $request->except(['_token']);
        $message = strip_tags($user_inputs['message']);
        $image = $this->prepare_inputs('image');
        $phones = explode(',', $user_inputs['phones']);
        if (is_array($phones) && count($phones) > 0) {
            foreach ($phones as $phone) {
                if(($this->consume_message_count < TypeConstants::WA_MAX_MESSAGE)){
                    $wa = new WAUltramsg();
                    if($image)
                        $wa->sendImage(trim($phone), $message, $image);
                    else
                        $wa->sendText(trim($phone), $message);
                }
            }
            session()->flash('sweet_flash_message', [
                'title' => trans('admin.done'),
                'message' => trans('admin.successfully_send'),
                'type' => 'success'
            ]);
        }else
            session()->flash('sweet_flash_message', [
                'title' => 'error',
                'message' => trans('global.unsuccessfully_send'),
                'type' => 'error'
            ]);

        return redirect(route('sw.createWAUltra'));
    }
    public function storeToken(Request $request)
    {
        $user_inputs = $request->except(['_token']);
        $token = strip_tags($user_inputs['token']);
        $instance_id = strip_tags($user_inputs['instance_id']);
        if($token && $instance_id) {
            $setting = Setting::where('id', $this->mainSettings->id)->first();
            $wa_details = $setting->wa_details;
            $wa_details['wa_ultra_token'] = $token;
            $wa_details['wa_ultra_instance_id'] = $instance_id;
            $setting->wa_details = $wa_details;
            $setting->save();

            Cache::store('file')->clear();
            session()->flash('sweet_flash_message', [
                'title' => trans('admin.done'),
                'message' => trans('admin.successfully_send'),
                'type' => 'success'
            ]);
        }else
            session()->flash('sweet_flash_message', [
                'title' => 'error',
                'message' => trans('global.unsuccessfully_send'),
                'type' => 'error'
            ]);

        return redirect(route('sw.createWAUltra'));
    }

    public function index()
    {
        $title = trans('sw.wa_logs');

        $logs = GymWALog::branch()->with(['user']);
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
            $phones = GymNonMember::branch()->pluck('phone')->toArray();
        }else if($type == 3){
            $phones = GymMember::branch()->pluck('phone')->toArray();
        }else if($type == 4){
            $phones = GymMember::branch()->pluck('phone')->whereHas('member_subscription_info', function ($q) {
                $q->whereRaw('sw_gym_member_subscription.id IN (select MAX(a2.id) from sw_gym_member_subscription as a2 join sw_gym_members as u2 on u2.id = a2.member_id group by u2.id) and sw_gym_member_subscription.status = 1');
                //$q->where('status', (int)$status);
            })->toArray();
        }
        return implode(', ', $phones);
    }


    private function prepare_inputs($inputs)
    {
        $input_file = $inputs;
        $filename = '';
        if (request()->hasFile($input_file)) {
            $file = request()->file($input_file);
//            $filename = rand(0, 20000) . time() . '.' . $file->getClientOriginalExtension();
            $filename = asset(User::$uploads_path.'wa_image.' . $file->getClientOriginalExtension());
            $destinationPath = base_path(User::$uploads_path);
            $upload_success = $file->move($destinationPath, $filename);
            if ($upload_success) {
                return $filename;
            }
        }

        return $filename;
    }


}


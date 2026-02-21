<?php

namespace Modules\Software\Http\Controllers\Api;

use Modules\Generic\Classes\GymmawySubscription;
use Modules\Generic\Http\Controllers\Api\GenericApiController;
use Modules\Generic\Models\Setting;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Classes\ZKLibrary;
use Modules\Software\Events\FPMemberEvent;
use Modules\Software\Http\Controllers\Front\GymMemberFrontController;
use Modules\Software\Http\Resources\ZKMemberResource;
use Modules\Software\Http\Resources\ZKNewMemberResource;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymMemberAttendee;
use Modules\Software\Models\GymUser;
use Modules\Software\Models\GymUserAttendee;
use Modules\Software\Models\GymZKFingerprint;
use Modules\Software\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class GymMemberApiController extends GenericApiController
{
    public function sendMemberToGymmawy(){
        \Artisan::call('command:swmembers');
    }
    // for send expire, before expire, unfreeze, and birthday notifications
    // also auto-generates the monthly AI executive report on the 1st of each month
    public function sendSwMyAppNotifications(){
        try {
            $notificationService = new NotificationService();

            $results = [
                'expiring' => $notificationService->sendExpiringNotifications(3), // 3 days before
                'expired'  => $notificationService->sendExpiredNotifications(),
                'unfreeze' => $notificationService->sendUnfreezeNotifications(),
                'birthday' => $notificationService->sendBirthdayNotifications(),
            ];

            // Auto-generate AI executive report on the first day of each month
            $aiReportId = null;
            if (Carbon::now()->day === 1) {
                try {
                    $aiResult  = (new \Modules\Software\Classes\GymAiReport())->getter(
                        Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d'),
                        Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d')
                    );
                    $aiReportId = $aiResult['id'] ?? null;
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Monthly AI report generation failed: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Notifications processed successfully',
                'data' => [
                    'expiring_total'   => $results['expiring']['total'],
                    'expiring_success' => $results['expiring']['success'],
                    'expiring_failed'  => $results['expiring']['failed'],
                    'expired_total'    => $results['expired']['total'],
                    'expired_success'  => $results['expired']['success'],
                    'expired_failed'   => $results['expired']['failed'],
                    'unfreeze_total'   => $results['unfreeze']['total'],
                    'unfreeze_success' => $results['unfreeze']['success'],
                    'unfreeze_failed'  => $results['unfreeze']['failed'],
                    'birthday_total'   => $results['birthday']['total'],
                    'birthday_success' => $results['birthday']['success'],
                    'birthday_failed'  => $results['birthday']['failed'],
                    'ai_report_id'     => $aiReportId,
                ]
            ]);
        } catch (\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    // for send renew member or any type
    public function sendOneMemberToGymmawy($memberId = null, $type = 0){
        $memberId = @$memberId;
        $setting = Setting::first();
        $member = GymMember::with('member_subscription_info.subscription')->where('id', $memberId)->first();
        if(isset($member)) {
            $result = ['code' => $member->code, 'name' => $member->name, 'phone' => $member->phone, 'client_token' => $setting->token];
            if($member->member_subscription_info){
                $result['subscription_name_ar'] = @$member->member_subscription_info->subscription->name_ar;
                $result['subscription_name_en'] = @$member->member_subscription_info->subscription->name_en;
                $result['workouts'] = @$member->member_subscription_info->workouts;
                $result['visits'] = @$member->member_subscription_info->visits;
                $result['amount_remaining'] = @number_format($member->member_subscription_info->amount_remaining, 2);
                $result['joining_date'] = @\Carbon\Carbon::parse($member->member_subscription_info->joining_date)->toDateTimeString();
                $result['expire_date'] = @\Carbon\Carbon::parse($member->member_subscription_info->expire_date)->toDateTimeString();
                $result['type'] = $type;
            }
            try {
                if(@GymmawySubscription::sendToApp($result)){
                    return true;
                }
            } catch (\Exception $e){
                return false;
            }
        }
        return false;
    }
    // for send renew member or any type
    public function sendMsgForOneMemberToGymmawy($member = null, $msg = null){

        $setting = Setting::first();
        if(isset($member) && $msg) {
            $result = ['code' => $member->code, 'name' => $member->name, 'phone' => $member->phone, 'client_token' => $setting->token, 'msg' => $msg];
            try {
                if(@GymmawySubscription::sendMsgToApp($result, $msg)){
                    return true;
                }
            } catch (\Exception $e){
                return false;
            }
        }
        return false;
    }

    public function fingerprintZKMemberGetter(){

//        $members = GymMember::with('member_subscription_info')->whereNotNull('fp_id')
////            ->where('fp_check', '!=', TypeConstants::ZK_EXPIRE_MEMBER)
//            ->whereHas('member_subscription_info', function ($q){
//                $q->where('expire_date', '<', Carbon::now()->toDateString());
//            })->get();
//        foreach ($members as $member){
//            echo $member->fp_check.' - '.$member->fp_id.'<br/>';
////            $member->fp_check = TypeConstants::ZK_EXPIRE_MEMBER;
////            $member->save();
//        }
//        dd($members);
        $branch_id = @request('branch_id') ? @(int)request('branch_id') : 1;

        $setting = Setting::where('id',$branch_id)->first();
        // fp_check 0: new member fp not add yet - need to get data from machine
        // 1: added member with active membership -  already check and has fp data
        // 2: delete this member because membership ended - delete fp data and after delete convert to 0 to get new fp data from machine
        // 3: add this member on machine because renew membership - set data from server data to machine data.

        $set_members = GymMember::select('id','fp_id', 'fp_uid', 'code')->with(['member_zk_fingerprint' => function($q){
            $q->select('member_id', 'uid', 'cardno','details');
        }, 'member_subscription_info' => function($q){
            $q->select('member_id', 'expire_date');
        }])->where('branch_setting_id',$branch_id)->whereNotNull('fp_id')
            ->where('fp_check_count', '<', 10)
            ->whereIn('fp_check', [TypeConstants::ZK_ACTIVE_MEMBER, TypeConstants::ZK_SET_MEMBER])
            //->where('fp_check', TypeConstants::ZK_SET_MEMBER)
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get();

        $set_members_ids = $set_members->pluck('id');
        $set_members = ZKMemberResource::collection($set_members);

        if(@env('APP_ZK_GATE') == false)
            $del_members = GymMember::where('branch_setting_id',$branch_id)->whereNotIn('fp_id', $set_members_ids)->whereNotNull('fp_id')->where('fp_check_count', '<', 5)->where('fp_check', TypeConstants::ZK_EXPIRE_MEMBER)->withTrashed()->orderBy('id', 'asc')->pluck('code');
        else if(@env('APP_ZK_IVS_GATE') == true)
            $del_members = GymMember::where('branch_setting_id',$branch_id)->whereNotIn('fp_id', $set_members_ids)->whereNotNull('fp_id')->where('fp_check_count', '<', 5)->where('fp_check', TypeConstants::ZK_EXPIRE_MEMBER)->withTrashed()->orderBy('id', 'asc')->pluck('fp_uid');
        else
            $del_members = GymMember::where('branch_setting_id',$branch_id)->whereNotIn('fp_id', $set_members_ids)->whereNotNull('fp_id')->where('fp_check_count', '<', 5)->where('fp_check', TypeConstants::ZK_EXPIRE_MEMBER)->withTrashed()->orderBy('id', 'asc')->pluck('fp_id');

        $set_new_members = GymMember::select('id','name', 'phone','fp_id', 'fp_uid', 'code')->with('member_subscription_info')->where('branch_setting_id',$branch_id)
            ->whereNull('fp_id')->limit(1)->orderBy('id', 'desc')->get();
        $set_new_members = ZKNewMemberResource::collection($set_new_members);

        $get_members = GymMember::where('branch_setting_id',$branch_id)->whereNotNull('fp_id')
            ->where('fp_check_count', '<', 10)
            ->whereIn('fp_check', [TypeConstants::ZK_ACTIVE_MEMBER, TypeConstants::ZK_SET_MEMBER])
            ->orderBy('updated_at', 'desc')
            ->limit(50)->pluck('fp_id')->toArray();
        $get_users = GymUser::where('branch_setting_id',$branch_id)->whereNotNull('fp_id')->where('fp_check_count', '<', 10)
            ->whereIn('fp_check', [TypeConstants::ZK_ACTIVE_MEMBER, TypeConstants::ZK_SET_MEMBER])
            ->orderBy('updated_at', 'desc')
            ->limit(50)->pluck('fp_id')->toArray();

        if(count($get_users) > 0)
            $get_members = array_merge($get_members, $get_users);

        $fp_att = @GymMemberAttendee::orderBy('created_at', 'desc')->first();
        $fp_att_id = @$fp_att->fp_att_id;
        $fp_att_time = @$fp_att->created_at ? Carbon::parse($fp_att->created_at)->toDateTimeString() : Carbon::now()->subDays(7)->toDateTimeString();
        $server_time = Carbon::now()->toDateTimeString();
        $device_name = env('APP_NAME_AR');
        $token = Str::random(32);
        $zk_info = ['token' => $token];
        file_put_contents('./public/ZK'.$setting->token.'.json', json_encode($zk_info));
//        session(['zk_fp_token' => $token]);
        return Response::json(['token' => $token, 'server_time'=> $server_time, 'device_name'=> $device_name, 'del_members' => $del_members, 'get_members' => $get_members, 'set_members' => $set_members, 'fp_att_id' => $fp_att_id, 'fp_att_time' => $fp_att_time, 'set_new_members' => $set_new_members], 200);
    }
    public function fingerprintZKMemberSetter(Request $request){
        $branch_id = @$request->branch_id ? @(int)$request->branch_id : 1;
        $setting = Setting::where('id', $branch_id)->first();
        $setting->zk_online_at =  Carbon::now()->toDateTimeString();
        $setting->save();
        Cache::store('file')->clear();

        // fp_check 0: new member fp and need to get data from machine
        $members = @$request->members;
//        $members = @json_decode(@$members);
        $attendances = @$request->attendances;
//        $attendances = @$this->decode_arr('YToxOntpOjA7YTo0OntpOjA7aToxMztpOjE7czoxOiIxIjtpOjI7aToxO2k6MztzOjE5OiIyMDIzLTAzLTE0IDAwOjE5OjI0Ijt9fQ==');
//        $attendances = (array)(@$attendances);

        $token = @$request->token;
        $mac = @$request->mac;
        $member_status = false;
        $attendance_status = false;
        $zk_temp_token_info = file_get_contents('./public/ZK'.$setting->token.'.json');
        $zk_temp_token_info = json_decode($zk_temp_token_info);

        if($members && (@$zk_temp_token_info->token == $token)){
            if(@count($members)) {
                foreach ($members as $member) {
                    if(@$member['is_new_member']){
                        $get_member = GymMember::where('branch_setting_id', $branch_id)->where('code', @$member['fp_id'])->first();
                    }else {
                        $get_member = GymMember::where('branch_setting_id', $branch_id)->where('fp_id', @$member['fp_id'])->first();
                    }

                    if ($get_member) {
                        if(@$member['fp_uid']) {
                            GymZKFingerprint::where('branch_setting_id', $branch_id)->where('member_id', $get_member->id)->delete();
                            GymZKFingerprint::create(['branch_setting_id' => $branch_id, 'member_id' => $get_member->id, 'uid' => @$member['fp_uid'], 'cardno' => @$member['fp_cardno'], 'details' => (@$member['template'])]);

                            if(@$member['is_new_member']){$get_member->fp_id = @$member['fp_id'];}
                            $get_member->fp_uid = @$member['fp_uid'];
                            $get_member->fp_check = TypeConstants::ZK_NEW_MEMBER;
                            $get_member->save();
                        }
                    } else {
                        $get_user = GymUser::where('branch_setting_id', $branch_id)->where('fp_id', @$member['fp_id'])->first();
                        if ($get_user && @$member['fp_uid']) {
                            GymZKFingerprint::where('branch_setting_id', $branch_id)->where('user_id', $get_user->id)->delete();
                            GymZKFingerprint::create(['branch_setting_id' => $branch_id, 'user_id' => $get_user->id, 'uid' => @$member['fp_uid'],  'cardno' => @$member['fp_cardno'], 'details' => (@$member['template'])]);

                            if(@$member['is_new_member']){$get_user->fp_id = @$member['fp_id'];}
                            if(@$member['fp_uid']){$get_user->fp_uid = @$member['fp_uid'];}
                            $get_user->fp_check = TypeConstants::ZK_NEW_MEMBER;
                            $get_user->save();
                        }
                    }
                }
                $member_status = true;
            }
        }

//        if($attendances && (@$zk_temp_token_info->token == $token) && (@env('APP_ZK_DEVICE_ADDRESS') == $mac)){
        if($attendances && (@$zk_temp_token_info->token == $token)){
            //$attendances = @$this->decode_arr($attendances);
            $attendanceMemberSql = [];
            $attendanceUserSql = [];
            $attendances = collect($attendances)
                ->unique(function ($item) {
                    if(@$item['USERID'])
                        return $item['USERID'] . '_' . Carbon::parse(@$item['CHECKTIME'])->toDateString(); // Combine keys for uniqueness
                    else
                        return $item['FPID'] . '_' . Carbon::parse(@$item['CHECKTIME'])->toDateString(); // Combine keys for uniqueness

                })
                ->values() // Reindex the array
                ->toArray();
            if(@count($attendances) > 0) {
                $attendances = @($attendances);
                foreach ($attendances as $i => $attendance) {
                    if (@$attendance && (@$attendance['USERID'] || @$attendance['FPID']) && @$attendance['CHECKTIME']) {
                        $att_date = Carbon::parse(@$attendance['CHECKTIME'])->toDateString();
                        $member = GymMember::with('member_subscription_info');
                        if(@$attendance['USERID'])
                            $member = $member->where('fp_uid', @$attendance['USERID'])->first();
                        else
                            $member = $member->where('fp_id', @$attendance['FPID'])->first();


                        if (@$member) {
                            $attendance = GymMemberAttendee::select('member_id', 'created_at')->where('member_id', $member->id)->whereDate('created_at', $att_date)->first();
                            if(!@$attendance) {
                                $attendanceMemberSql[$i]['user_id'] = 0;
                                $attendanceMemberSql[$i]['branch_setting_id'] = $branch_id;
                                $attendanceMemberSql[$i]['member_id'] = $member->id;//$attendance[1];
                                $attendanceMemberSql[$i]['subscription_id'] = $member->member_subscription_info->id;//$attendance[1];
                                $attendanceMemberSql[$i]['created_at'] = @$attendance['CHECKTIME'] ? Carbon::parse(@$attendance['CHECKTIME']) : Carbon::now();
                                $attendanceMemberSql[$i]['fp_att_id'] = @$attendance['fp_att_id'];
                                //$member->member_subscription_info->visits = $member->member_subscription_info->visits - 1;
                                //$member->member_subscription_info->save();
                            }
                        }
//                        else if(@$member->user_id) {
//                            $attendanceUserSql[$i]['branch_setting_id'] = $branch_id;//$attendance[1];
//                            $attendanceUserSql[$i]['user_id'] = $member->user_id;//$attendance[1];
//                            $attendanceUserSql[$i]['created_at'] = @$attendance['CHECKTIME'];
//                        }
                    }
                }

                if (count($attendanceMemberSql) > 0) {
                    GymMemberAttendee::insert(array_values($attendanceMemberSql));
                }
                if (count($attendanceUserSql) > 0) {
                    GymUserAttendee::insert(array_values($attendanceUserSql));
                }

                $attendance_status = true;
            }
        }

        //GymMember::whereIn('fp_check', [0, 2, 3])->where('fp_id', '!=', null)->withTrashed()->increment('fp_check_count');
        //GymUser::where('fp_check', [0, 2, 3])->where('fp_id', '!=', null)->withTrashed()->increment('fp_check_count');
        Setting::where('id', $branch_id)->update(['fp_last_updated_at' => Carbon::now()]);

        return Response::json(['member_status' => $member_status, 'attendance_status' => $attendance_status, 'token' => $token], 200);
    }
    private function encode_arr($data) {
        return base64_encode(serialize($data));
    }
    private function decode_arr($data) {
        return unserialize(base64_decode($data));
    }
    private function connect_server($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $result = json_decode($response);
        curl_close($ch);
        return $result;
    }
}


<?php

namespace Modules\Software\Classes;

use Modules\Software\Http\Controllers\Front\GymMoneyBoxFrontController;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymMemberSubscription;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Models\GymPTMember;
use Modules\Software\Models\GymSubscription;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportExcel implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $key => $row) {
            if ($key > 0) {
                dd($row);
//                $data = array('id' => $key+1,'COL1' => $row[0] ?? '','COL2' => $row[1] ?? '' ,'COL3' => $row[2] ?? '' ,'COL4' => $row[3] ?? '' ,'COL5' => $row[4]  ?? '','COL6' => $row[5] ?? '' ,'COL7' => $row[6] ?? '' ,
//                    'COL8' => $row[7] ?? '' ,'COL9' => $row[8] ?? '' ,'COL10' => $row[9] ?? '' ,'COL11' => $row[10]  ?? '','COL12' => $row[11]  ?? '','COL13' => $row[12]  ?? '','COL14' => $row[13]  ?? '',
//                    'COL15' => $row[14] ?? '' ,'COL16' => $row[15] ?? '' ,'COL17' => $row[16]  ?? '','COL18' => $row[17]  ?? '' );
//                DB::table('original_data2')->insert($data);

                //$subscription = GymSubscription::branch()->where('name_en', $row[4])->first();
                //$code = $maxId = str_pad(($row[0]), 14, 0, STR_PAD_LEFT);
                //$data = ['user_id' => Auth::guard('sw')->user()->id, 'name' => $row[1], 'code' => $code, 'phone' => (string)$row[6], 'dob' => @Carbon::parse($row[2]), 'branch_setting_id' => @Auth::guard('sw')->user()->branch_setting_id];

//                if(@$row[1]) {
//                    $text = explode("#", @$row[1]);
//                    $member = null;
//                    $subscription = null;
//                    $join_date = null;
//                    $expire_date = null;
////                    if(@$text[1]){$member = GymMember::where('code', $text[1])->first();}
////                    if(@$row[4]){$subscription = GymSubscription::where('name_ar', trim($row[4]))->first();}
//                    $join_date = Carbon::parse($row[6])->toDateString();
//                    $expire_date = Carbon::parse($row[5])->toDateString();
//                    if(@$member && @$subscription && @$join_date && @$expire_date) {
//                        //data = ['user_id' => Auth::guard('sw')->user()->id, 'name' => $row[1], 'code' => $row[0], 'phone' => (string)$phone, 'dob' => @Carbon::now(), 'branch_setting_id' => @Auth::guard('sw')->user()->branch_setting_id];
//                        $data = ['user_id' => 1, 'branch_setting_id' => 1
//                            , 'member_id' => @$member->id, 'subscription_id' => @$subscription->id, 'workouts' => 0, 'visits' => 0, 'amount_remaining' => 0, 'amount_paid' => 0, 'vat' => 0, 'vat_percentage' => 0, 'freeze_limit' => 0
//                            , 'number_times_freeze' => 0, 'amount_before_discount' => 0, 'discount_value' => 0, 'payment_type' => 0
//                            , 'joining_date' => @$join_date, 'expire_date' => @$expire_date, 'status' => 0
//                        ];
//
//                        var_dump($data);
//                        dd($data);
//                        //GymMemberSubscription::create($data);
//                    }
//                }
                //$member = GymMember::create($data);
/*
                GymMemberSubscription::insert([
                            'id' => $key
                            , 'member_id' => $member->id
                            , 'subscription_id' => $subscription->id
                            , 'joining_date' => Carbon::parse($row[2])
                            , 'expire_date' => Carbon::parse($row[3])
                            , 'amount_paid' => 0//$row[9]
                            , 'workouts' => $subscription->workouts
                            , 'amount_before_discount' => 0//$row[9]
                            , 'branch_setting_id' => @Auth::guard('sw')->user()->branch_setting_id
                        ]);

                */



//                $code = 1;
//                if(GymMember::branch()->orderBy('id', 'desc')->first())
//                    $code = GymMember::branch()->orderBy('id', 'desc')->first()->code + 1;
//                if ($row[1] && $row[3] && $subscription) {
//                    $data = ['user_id' => Auth::guard('sw')->user()->id, 'name' => $row[1], 'code' => $code, 'phone' => (string)$row[3], 'email' => (string)$row[2], 'dob' => @$dob, 'branch_setting_id' => @Auth::guard('sw')->user()->branch_setting_id];
//                    $dob = Carbon::now()->subMonth(144);
////                    dd(Carbon::parse('1900-01-01')->addDays(44734)->toDateString(), $row[6], $row[7]);
//                    $memberCheck = GymMember::branch()->where('phone', (string)$row[3])->count();
//                    echo $memberCheck . ' - ' . $key . ' - ' . $row[1] . '<br>';
//                    if (!$memberCheck) {
//                        $member = GymMember::create($data);
//
//                        $joining_date = Carbon::now()->toDateString();
//                        $expire_date = Carbon::now()->addMonths(6)->toDateString();
//
//                        $pt_joining_date = Carbon::createFromFormat('d/m/Y', $row[7])->toDateString();
//                        $pt_expire_date = Carbon::parse('1900-01-01')->addDays($row[6] - 2)->toDateString();
//
//                        GymMemberSubscription::insert([
//                            'id' => $key
//                            , 'member_id' => $member->id
//                            , 'subscription_id' => $subscription->id
//                            , 'joining_date' => $joining_date
//                            , 'expire_date' => $expire_date
//                            , 'amount_paid' => $subscription->price
//                            , 'workouts' => $subscription->workouts
//                            , 'amount_before_discount' => $subscription->price
//                            , 'branch_setting_id' => @Auth::guard('sw')->user()->branch_setting_id
//                        ]);
//
//                        GymPTMember::create([
//                            'branch_setting_id' => @Auth::guard('sw')->user()->branch_setting_id
//                            , 'member_id' => $member->id
//                            , 'pt_subscription_id' => 1
//                            , 'pt_class_id' => 1
//                            , 'pt_trainer_id' => 1
//                            , 'classes' => 24
//                            , 'amount_paid' => 0
//                            , 'vat' => 0
//                            , 'vat_percentage' => 0
//                            , 'amount_remaining' => 0
//                            , 'joining_date' => $pt_joining_date
//                            , 'expire_date' => $pt_expire_date
//                        ]);
//
//                    }
//                }
            }
        }
        dd('end');
    }
}




//        dd("0".(string)$row['phone']);
//        dd($row['name']);
//        $subscription = GymSubscription::branch()->where('id', (int)$row['subscription_id'])->first();
//        $code = GymMember::branch()->orderBy('id', 'desc')->first()->code+1;
//        if($row['name'] && $row['phone'] && $subscription) {
//            if(@$row['dob']){ $dob = Carbon::parse($row['dob']); }else{ $dob = Carbon::now()->subMonth(144); }
//            $data = ['user_id' => $this->user_sw->id, 'name' => $row['name'], 'code' => $code, 'phone' => "0".(string)$row['phone'], 'email' => (string)$row['email'], 'dob' => @$dob, 'branch_setting_id' => @$this->user_sw->branch_setting_id];
//            $memberCheck = GymMember::branch()->where('name',  (string)$row['name'])->count();
//            echo $memberCheck.' - '.$key.' - '.$row['name'].'<br>';
//            $check = false;
//            if(!$memberCheck && ($check)){
//                $member = GymMember::create($data);
//                if(@$row['joining_date']) $joining_date = Carbon::parse(@$row['joining_date'])->toDateTimeString(); else $joining_date = Carbon::parse('2022-01-03')->toDateTimeString();
//                if(@$row['joining_date']) $expire_date = Carbon::parse(@$row['joining_date'])->addDays($subscription->period)->toDateTimeString(); else  $expire_date =  Carbon::parse('2022-01-03')->addDays($subscription->period)->toDateTimeString();
//
//                GymMemberSubscription::create(['member_id' => $member->id, 'subscription_id' => $subscription->id
//                    , 'joining_date' => $joining_date
//                    , 'expire_date' => $expire_date
//                    , 'amount_paid' => $subscription->price
//                    , 'workouts' => $subscription->workouts
//                    , 'amount_before_discount' => $subscription->price
//                    , 'branch_setting_id' => @$this->user_sw->branch_setting_id
//                ]);
//                $records[] = ['success' => 1, 'msg' => trans('sw.success_in_excel')];
//
//                $vat = 0;
//                $amount_box = GymMoneyBox::branch()->orderBy('id', 'desc')->first();
//                $amount_after = GymMoneyBoxFrontController::amountAfter($amount_box->amount, $amount_box->amount_before, $amount_box->operation);
//
//                $notes = trans('sw.member_moneybox_add_msg',
//                    [
//                        'subscription' => $subscription->name,
//                        'member' => $member->name,
//                        'amount_paid' => @$subscription->price,
//                        'amount_remaining' => 0,
//                    ]);
//
//
//                $moneyBox = GymMoneyBox::create([
//                    'user_id' => Auth::guard('sw')->user()->id
//                    , 'amount' => @$subscription->price
//                    , 'vat' => @$vat
//                    , 'operation' => TypeConstants::Add
//                    , 'amount_before' => $amount_after
//                    , 'notes' => $notes
//                    , 'type' => TypeConstants::CreateMember
//                    , 'member_id' => $member->id
//                    , 'branch_setting_id' => $this->user_sw->branch_setting_id
//                ]);
//
//                $this->userLog($notes, TypeConstants::CreateMoneyBoxAdd);
//
//                $notes = str_replace(':name', $member->name, trans('sw.add_member'));
//                $this->userLog($notes, TypeConstants::CreateMember);
//
//            }else{
//                $records[] = ['success' => 0, 'msg' => trans('sw.error_in_excel_duplicate')];
//            }
//        }else
//            $records[] = ['success' => 0, 'msg' => trans('sw.error_in_excel_data', ['data' => trans('sw.phone').', '.trans('sw.name').', '.trans('sw.subscription')])];
//
//    session()->put(['records', @collect($records)]);


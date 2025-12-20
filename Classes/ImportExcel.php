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
dd('seste');
            }
        }
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


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


class GymHelperToolsFrontController extends GymGenericFrontController
{
    public $GymUserRepository;

    public function __construct()
    {
        parent::__construct();
    }


    public function list()
    {
        $title = trans('sw.helper_tools');

        return view('software::Front.helper_tools_front_list', ['title'=>$title]);
    }
    public function calories()
    {
        $title = trans('sw.calculate_calories');
        return view('software::Front.calculates.calories',
            compact( 'title' )
        );
    }
    public function caloriesResult()
    {
        $age = request('age');
        $activity = request('activity');
        $height = request('height');
        $weight = request('weight');
        $gender = request('gender');

        if(!$age || !$activity || !$gender || !$weight || !$height)
            return '<div class="alert alert-danger">'.trans('sw.calculate_calorie_error_result').'</div>';
        // for men: BMR = 10W + 6.25H - 5A + 5
        // for female: BMR = 10W + 6.25H - 5A - 161
        if($gender == 1)
            $result = (10* $weight) + (6.25 * $height) - (5 * $age) + 5;
        elseif($gender == 2)
            $result = (10* $weight) + (6.25 * $height) - (5 * $age) - 161;

        return '<div class="alert alert-success"><b>'.trans('sw.result').':</b>'.' '.trans('sw.calculate_calorie_result', ["result" => round($result*$activity, 2)]).'</div>';

    }

    public function bmi()
    {
        $title = trans('sw.calculate_bmi');
        return view('software::Front.calculates.bmi',
            compact( 'title' )
        );
    }
    public function bmiResult()
    {
        $height = request('bmi_height');
        $weight = request('bmi_weight');

        if(!$weight || !$height)
            return '<div class="alert alert-danger">'.trans('sw.calculate_calorie_error_result').'</div>';

        // bmi = mass (kg) / height^2 (m)
        $height = $height /100;
        $result = ($weight) / ($height * $height);

        return '<div class="alert alert-success"><b>'.trans('sw.result').':</b>'.' '.trans('sw.calculate_bmi_result', ["result" => round($result, 2)]).'</div>';

    }

    public function ibw()
    {
        $title = trans('sw.calculate_ibw');
        return view('software::Front.calculates.ibw',
            compact( 'title' )
        );
    }
    public function ibwResult()
    {
        $height = request('ibw_height');

        if(!$height)
            return '<div class="alert alert-danger">'.trans('sw.calculate_calorie_error_result').'</div>';

        // bmi = mass (kg) / height^2 (m)
        // 18.5 to 25
        $height = $height /100;
        $min_weight  = 18.5 * ($height * $height);
        $max_weight  = 25 * ($height * $height);

        return '<div class="alert alert-success"><b>'.trans('sw.result').':</b>'.' '.trans('sw.calculate_ibw_result', ["result1" => round($min_weight, 2), "result2" => round($max_weight, 2)]).'</div>';

    }

    public function water()
    {
        $title = trans('sw.calculate_water');
        return view('software::Front.calculates.water',
            compact( 'title' )
        );
    }
    public function waterResult()
    {
        $weight = request('water_weight');

        if(!$weight)
            return '<div class="alert alert-danger">'.trans('sw.calculate_calorie_error_result').'</div>';

        $water = 0.034 * $weight;
        $cups = 4.23 * $water;


        return '<div class="alert alert-success"><b>'.trans('sw.result').':</b>'.' '.trans('sw.calculate_water_result', ["result1" => round($water, 2), "result2" => round($cups, 2)]).'</div>';

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


    public function calculateVatPercentage()
    {
        $title = trans('sw.calculate_vat_percentage');
        return view('software::Front.calculates.vat_percentage',
            compact( 'title' )
        );
    }
    public function calculateVatPercentageResult()
    {
        $total_price_without_vat = request('total_price_without_vat');
        $total_price_with_vat = request('total_price_with_vat');
        $vat = request('vat');
        $total_price_type = request('total_price_type');

        if(!$total_price_without_vat && !$total_price_with_vat)
            return '<div class="alert alert-danger">'.trans('sw.calculate_calorie_error_result').'</div>';

        // bmi = mass (kg) / height^2 (m)
        // 18.5 to 25
        if($total_price_type == 2) {
            $total_price = $total_price_with_vat / (1 + ($vat / 100));
            return '<div class="alert alert-success"><b>' . trans('sw.result') . ':</b>' . ' ' . trans('sw.calculate_vat_percentage_result2', ["price" => number_format($total_price, 2)]) . '</div>';
        }else{
            $total_price = $total_price_without_vat + ($total_price_without_vat * ($vat/100));
            return '<div class="alert alert-success"><b>'.trans('sw.result').':</b>'.' '.trans('sw.calculate_vat_percentage_result', ["price" => number_format($total_price, 2)]).'</div>';
        }




    }

}


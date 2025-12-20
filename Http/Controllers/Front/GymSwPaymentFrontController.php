<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Generic\Classes\Constants;
use Modules\Generic\Classes\SMSEG;
use Modules\Generic\Classes\SMSGymmawy;
use Modules\Generic\Http\Controllers\Front\PayPalFrontController;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Http\Requests\GymSettingRequest;
use Modules\Generic\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Srmklive\PayPal\Services\ExpressCheckout;

class GymSwPaymentFrontController extends GymGenericFrontController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function show()
    {
        $mainSettings = $this->mainSettings;
        $packages = json_decode(file_get_contents('https://gymmawy.com/api/client-software-payments/'.$mainSettings->token), true);
        $orders = json_decode(file_get_contents('https://gymmawy.com/api/client-software-invoices/'.$mainSettings->token), true);

        if(@$packages['paypal_check']){
            $paypal = new PayPalFrontController();

            foreach ($packages['packages'] as $i => $package){
                $payment_url = $paypal->payment(['name' => $package['name_'.$this->lang], 'price' => (int)$package['price_usd'], 'desc' => trans('sw.payment_subscription_msg', ['name' => $package['name_'.$this->lang]]), 'qty' => 1, 'duration' => $package['duration']]);
                $packages['packages'][$i]['paypal_url'] = @$payment_url;
            }
        }

        $title = trans('sw.billing');
        return view('software::Front.sw_payment_front_list', ['title'=>$title, 'packages' => $packages['packages'], 'my_package' => @$packages['my_package'], 'orders' => $orders, 'getSettings' => $mainSettings]);
    }

    public function showPaymentOrder($id)
    {
        $mainSettings = $this->mainSettings;
        $orders = json_decode(file_get_contents('https://gymmawy.com/api/client-software-invoices/'.$mainSettings->token), true);
        $order = [];
        if(@$orders){
            foreach ($orders as $i => $get_order){
                if($get_order['id'] == $id){
                    $order = $get_order;
                    break;
                }
            }
        }

        $title_details = trans('sw.invoice_details');
        $title = trans('sw.invoice_details');
        return view('software::Front.sw_payment_front_show', ['title_details' => $title_details,'title'=>$title, 'order' => $order, 'getSettings' => $mainSettings]);
    }





}


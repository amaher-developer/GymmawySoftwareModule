<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Generic\Http\Controllers\Front\GenericFrontController;
use Modules\Software\Models\GymActivity;
use Modules\Software\Models\GymMember;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Models\GymNonMember;
use Modules\Software\Models\GymSubscription;
use Modules\Software\Models\GymUser;
use Carbon\Carbon;
use http\Env\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

class GymLoginFrontController extends GymGenericFrontController
{
    public function __construct()
    {

        parent::__construct();
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function showLoginForm(){

        $title = trans('global.login');
        $lang = $this->lang ?? 'ar';
        return view('software::Front.login', compact(['title', 'lang']));
    }
    public function login(\Illuminate\Http\Request $request){
        $request->validate(['email' => 'required', 'password' => 'required']);
        
        $getUser = GymUser::where('email', $request->email)->first();
        
        // Debug logging
        \Log::info('Login attempt', [
            'email' => $request->email,
            'user_found' => $getUser ? 'yes' : 'no',
            'password_hash' => $getUser ? substr($getUser->password, 0, 10) . '...' : 'N/A',
            'password_verify' => $getUser ? (password_verify($request->password, $getUser->password) ? 'PASS' : 'FAIL') : 'N/A'
        ]);
        
        if($getUser && password_verify($request->password, $getUser->password)){
            // Manually log in the user
            Auth::guard('sw')->login($getUser);
            $user = true;
        } else {
            $user = false;
        }

        if($user){
            $start_time_work =  Carbon::parse($getUser->start_time_work)->toDateTimeString();
            $end_time_work =  str_replace('00:00:00', '24:00:00', Carbon::parse($getUser->end_time_work)->toDateTimeString());

            if(($getUser->is_super_user) || (Carbon::parse($getUser->start_time_work) == Carbon::parse($getUser->end_time_work)) ||
                (($start_time_work <= Carbon::now()->toDateTimeString()) && ($end_time_work >= Carbon::now()->toDateTimeString()))){
                Auth::guard('sw')->getLastAttempted();
                View::share('swUser',$getUser);
                // Check if we need to redirect to the language-prefixed route
                $lang = $this->lang ?? 'ar';
                $prefix = ($lang != 'ar' && $lang != 'en') ? '' : $lang;
                return redirect('/' . $prefix);
            }
            return redirect()->back()->withErrors(['error' => trans('auth.failed_time')]);
        }
        return redirect()->back()->withErrors(['error' => trans('auth.failed')]);
    }

    public function logout()
    {
        Auth::guard('sw')->logout();
        Session::flush();
        return redirect(route('sw.login'));
    }

}

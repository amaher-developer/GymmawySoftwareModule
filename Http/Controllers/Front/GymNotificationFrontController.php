<?php

namespace Modules\Software\Http\Controllers\Front;


use Modules\Software\Classes\TypeConstants;
use Modules\Software\Events\UserEvent;
use Modules\Software\Models\GymUser;
use Modules\Software\Models\GymUserNotification;
use Modules\Software\Notification\SwGymUserNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class GymNotificationFrontController extends GymGenericFrontController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function sendToUsers(Request $request){

        $notification = new GymUserNotification();
        $notification->user_id = auth()->guard('sw')->user()->id;
        $notification->title = @$request->get('content');
        $notification->body = @$request->get('content');
        $notification->save();

        $users = GymUser::branch()->whereIn('id', $request->users)->get();
        foreach ($request->users as $user) {
            $data = ['title' => @$request->get('content'), 'content' => @$request->get('content'), 'user_id' => $user, 'created_at' => Carbon::now()->diffForHumans(Carbon::now())];
            event(new UserEvent($data));
        }
        \Notification::send($users, new SwGymUserNotification(GymUserNotification::latest('id')->first()));

        $notes = str_replace(':users', " (".trim(implode(', ', $users->pluck('name')->toArray()), ', '). ") ", trans('sw.send_notification_msg'));
        $notes = str_replace(':user', Auth::user()->name, $notes);
        $this->userLog($notes, TypeConstants::SendToUsers);
        return redirect()->back();

    }
    public function appToUsers($request = []){

        $notification = new GymUserNotification();
        $notification->title = @$request['title'];
        $notification->body = @$request['content'];
        $notification->save();
        $users = GymUser::branch()->get();
        foreach ($users->pluck('id') as $user) {
            $data = ['title' => @$request['title'],'content' => @$request['content'], 'url' => @$request['url'], 'user_id' => $user, 'created_at' => Carbon::now()->diffForHumans(Carbon::now())];
            event(new UserEvent($data));
        }

        \Notification::send($users, new SwGymUserNotification(GymUserNotification::latest('id')->first()));

        $notes = str_replace(':users', " (".trim(implode(', ', $users->pluck('name')->toArray()), ', '). ") ", trans('sw.send_notification_msg'));
        $notes = str_replace(':user', 'APP', $notes);
        $this->userLog($notes, TypeConstants::SendToUsers);
        return redirect()->back();

    }

    public function markAsRead(Request $request){
        if(@auth()->guard('sw')->user()->unreadNotifications->find($request->id))
            @auth()->guard('sw')->user()->unreadNotifications->find($request->id)->markAsRead();
    }
}
?>

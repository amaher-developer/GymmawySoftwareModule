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

        $users = GymUser::branch()->whereIn('id', $request->users)->get();

        foreach ($users as $user) {
            $notification = new GymUserNotification();
            $notification->user_id = $user->id;
            $notification->title = @$request->get('content');
            $notification->body = @$request->get('content');
            $notification->branch_setting_id = $user->branch_setting_id;
            $notification->save();

            $data = [
                'title'         => $request->get('content'),
                'content'       => $request->get('content'),
                'url'           => null,
                'user_id'       => $user->id,
                'channel_token' => $this->mainSettings['token'] ?? '',
                'created_at'    => Carbon::now()->diffForHumans(Carbon::now()),
            ];
            event(new UserEvent($data));
        }
        \Notification::send($users, new SwGymUserNotification(GymUserNotification::latest('id')->first()));

        $notes = str_replace(':users', " (".trim(implode(', ', $users->pluck('name')->toArray()), ', '). ") ", trans('sw.send_notification_msg'));
        $notes = str_replace(':user', Auth::user()->name, $notes);
        $this->userLog($notes, TypeConstants::SendToUsers);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }
        return redirect()->back();

    }
    public function appToUsers($request = []){

        $users = !empty($request['branch_setting_id'])
            ? GymUser::where('branch_setting_id', $request['branch_setting_id'])->get()
            : GymUser::branch()->get();

        foreach ($users as $user) {
            $notification = new GymUserNotification();
            $notification->user_id = $user->id;
            $notification->title = @$request['title'];
            $notification->body = @$request['content'];
            $notification->branch_setting_id = $user->branch_setting_id;
            $notification->save();

            $data = [
                'title'         => @$request['title'],
                'content'       => @$request['content'],
                'url'           => @$request['url'] ?? null,
                'user_id'       => $user->id,
                'channel_token' => $this->mainSettings['token'] ?? '',
                'created_at'    => Carbon::now()->diffForHumans(Carbon::now()),
            ];
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

    public function testPusher(Request $request)
    {
        $user  = auth()->guard('sw')->user();
        $token = $this->mainSettings['token'] ?? '';

        $data = [
            'title'         => $request->get('title', 'Test Notification'),
            'content'       => $request->get('message', 'This is a test Pusher notification ✔'),
            'url'           => null,
            'user_id'       => $user->id,
            'channel_token' => $token,
            'created_at'    => Carbon::now()->diffForHumans(Carbon::now()),
        ];

        event(new UserEvent($data));

        $channel = 'my-channel.' . $token . '.' . $user->id;

        return response()->json([
            'status'  => 'fired',
            'channel' => $channel,
            'event'   => 'my-event',
            'data'    => $data,
        ]);
    }
}
?>


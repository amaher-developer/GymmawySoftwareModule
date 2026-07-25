<?php

namespace Modules\Software\Http\Controllers\Front;


use Modules\Software\Classes\TypeConstants;
use Modules\Software\Events\UserEvent;
use Modules\Software\Models\GymUser;
use Modules\Software\Models\GymUserNotification;
use Modules\Software\Notification\SwGymUserNotification;
use Modules\Generic\Models\Setting;
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

        // Core Pusher + DB notification (safe to call from any context)
        static::pushToAllUsers($users, [
            'title'   => @$request['title'],
            'content' => @$request['content'],
            'url'     => @$request['url'] ?? null,
        ]);

        try {
            \Notification::send($users, new SwGymUserNotification(GymUserNotification::latest('id')->first()));
            $notes = str_replace(':users', " (".trim(implode(', ', $users->pluck('name')->toArray()), ', '). ") ", trans('sw.send_notification_msg'));
            $notes = str_replace(':user', 'APP', $notes);
            $this->userLog($notes, TypeConstants::SendToUsers);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('appToUsers: secondary actions failed', ['error' => $e->getMessage()]);
        }

        return redirect()->back();
    }

    /**
     * Static — safe to call from API controllers, queue jobs, or anywhere else.
     *
     * Saves a DB notification record and fires a Pusher UserEvent for each user.
     * Does NOT redirect, does NOT need an authenticated web session.
     *
     * @param \Illuminate\Support\Collection $users   GymUser collection
     * @param array  $data  keys: title, content, url
     */
    public static function pushToAllUsers($users, array $data): void
    {
        $token = Setting::first()->token ?? '';
        $now   = Carbon::now()->diffForHumans(Carbon::now());

        foreach ($users as $user) {
            try {
                GymUserNotification::create([
                    'user_id'          => $user->id,
                    'title'            => $data['title']   ?? '',
                    'body'             => $data['content'] ?? '',
                    'branch_setting_id'=> $user->branch_setting_id,
                ]);

                event(new UserEvent([
                    'title'         => $data['title']   ?? '',
                    'content'       => $data['content'] ?? '',
                    'url'           => $data['url']     ?? null,
                    'user_id'       => $user->id,
                    'channel_token' => $token,
                    'created_at'    => $now,
                ]));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('pushToAllUsers: failed for user ' . $user->id, ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Convenience wrapper — resolves users by branch then calls pushToAllUsers.
     * Can be called statically from API controllers without instantiating this class.
     *
     * @param array $data   keys: title, content, url, branch_setting_id (optional)
     */
    public static function pushNotification(array $data): void
    {
        $users = !empty($data['branch_setting_id'])
            ? GymUser::where('branch_setting_id', $data['branch_setting_id'])->get()
            : GymUser::branch()->get();

        static::pushToAllUsers($users, $data);
    }

    /**
     * Fire-and-forget wrapper for API controllers.
     *
     * - Runs AFTER the HTTP response is already sent (afterResponse) → zero wait time.
     * - Any exception is caught and logged → never stops the caller's process.
     *
     * @param array $data   keys: title, content, url, branch_setting_id (optional)
     */
    public static function pushNotificationAsync(array $data): void
    {
        try {
            dispatch(static function () use ($data) {
                try {
                    static::pushNotification($data);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning(
                        'pushNotificationAsync inner error',
                        ['error' => $e->getMessage()]
                    );
                }
            })->afterResponse();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning(
                'pushNotificationAsync dispatch failed',
                ['error' => $e->getMessage()]
            );
        }
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

    /** Simulate a mobile-app payment notification sent to all branch staff */
    public function testAppPayment(Request $request)
    {
        static::pushNotification([
            'title'   => trans('sw.app_payment_short_msg'),
            'content' => trans('sw.app_payment_msg'),
            'url'     => route('sw.listMember'),
        ]);

        return response()->json(['status' => 'fired', 'type' => 'app_payment']);
    }

    /** Simulate a mobile-app reservation notification sent to all branch staff */
    public function testAppReservation(Request $request)
    {
        static::pushNotification([
            'title'   => trans('sw.app_subscription_short_msg'),
            'content' => trans('sw.app_subscription_msg'),
            'url'     => route('sw.listPotentialMember'),
        ]);

        return response()->json(['status' => 'fired', 'type' => 'app_reservation']);
    }
}
?>


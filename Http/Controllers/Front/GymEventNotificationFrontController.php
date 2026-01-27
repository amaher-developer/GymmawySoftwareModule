<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Generic\Http\Controllers\Front\PayPalFrontController;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Models\GymEventNotification;
use Illuminate\Http\Request;

class GymEventNotificationFrontController extends GymGenericFrontController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function edit()
    {
        $title = trans('sw.message_settings');
        $event_notifications = GymEventNotification::branch()->orderBy('id','asc')->get();
        $total = $event_notifications->count();
        return view('software::Front.event_notification_front_list', ['total' => $total, 'event_notifications' => $event_notifications, 'title' => $title]);
    }

    public function updateAjax($id, $status)
    {
        $event_notification = GymEventNotification::withTrashed()->find($id);
        $event_notification->status = $status;
        $event_notification->save();


        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);

        $notes = str_replace(':title', $event_notification->title, trans('sw.edit_event_notification'));
        $this->userLog($notes, TypeConstants::EditEventNotification);

        return redirect(route('sw.editEventNotification'));
    }

    public function updateMessageAjax(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'message' => 'required|string',
        ]);

        $event_notification = GymEventNotification::withTrashed()->find($request->id);

        if (!$event_notification) {
            return response()->json(['success' => false, 'message' => trans('sw.not_found')], 404);
        }

        $event_notification->message = $request->message;
        $event_notification->save();

        $notes = str_replace(':title', $event_notification->title, trans('sw.edit_event_notification'));
        $this->userLog($notes, TypeConstants::EditEventNotification);

        return response()->json(['success' => true, 'message' => trans('admin.successfully_edited')]);
    }





}


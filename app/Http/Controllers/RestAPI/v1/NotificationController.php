<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationSeen;
use App\Utils\Helpers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    public function list(Request $request)
    {

        $notification_data = Notification::active()->where(['sent_to'=>'customer']);

        $notification = $notification_data->with('notificationSeenBy')
            ->latest()->paginate($request['limit'], ['*'], 'page', $request['offset']);

        return [
            'total_size' => $notification->total(),
            'limit' => (int)$request['limit'],
            'offset' => (int)$request['offset'],
            'new_notification' => $notification_data->whereDoesntHave('notificationSeenBy')->count(),
            'notification' => $notification->items()
        ];
    }

    public function notification_seen(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notification_id' => 'required|integer|exists:notifications,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $user = $request->user();
        NotificationSeen::updateOrInsert(['user_id' => $user->id, 'notification_id' => $request->notification_id],[
            'created_at' => Carbon::now(),
        ]);

        $notification_count = Notification::active()
            ->where('sent_to', 'customer')
            ->whereDoesntHave('notificationSeenBy')
            ->count();

        return [
            'notification_count' => $notification_count,
        ];
    }
}

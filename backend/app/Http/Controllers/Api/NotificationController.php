<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $items = SiteNotification::where('user_id', $request->user()->id)
            ->where(function ($q) {
                $q->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', now());
            })
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        return response()->json($items);
    }

    public function unreadCount(Request $request)
    {
        $count = SiteNotification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->where(function ($q) {
                $q->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', now());
            })
            ->count();

        return response()->json(['count' => $count]);
    }

    public function markRead(SiteNotification $notification)
    {
        if ($notification->user_id !== request()->user()->id) abort(403);
        $notification->update(['is_read' => true, 'read_at' => now()]);
        return response()->json(['message' => __('notifications.marked_read')]);
    }

    public function markAllRead(Request $request)
    {
        SiteNotification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
        return response()->json(['message' => __('notifications.all_marked_read')]);
    }

    public function destroy(SiteNotification $notification)
    {
        if ($notification->user_id !== request()->user()->id) abort(403);
        $notification->delete();
        return response()->json(['message' => __('notifications.deleted')]);
    }

    public function clearAll(Request $request)
    {
        SiteNotification::where('user_id', $request->user()->id)->delete();
        return response()->json(['message' => __('notifications.cleared')]);
    }
}

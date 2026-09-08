<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Full notification center for the current user (works for all 3 roles).
     */
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, Notification $notification)
    {
        abort_unless($notification->user_id === Auth::id(), 403);

        $notification->update(['is_read' => true, 'read_at' => now()]);

        if ($request->wantsJson()) {
            return response()->json(['unread' => Auth::user()->unreadNotifications()->count()]);
        }

        return redirect()->back();
    }

    /**
     * Clicking a notification: mark it read, then jump straight to the relevant page.
     */
    public function open(Notification $notification)
    {
        abort_unless($notification->user_id === Auth::id(), 403);

        if (! $notification->is_read) {
            $notification->update(['is_read' => true, 'read_at' => now()]);
        }

        return redirect($notification->link ?: route('notifications.index'));
    }

    public function markAllRead(Request $request)
    {
        Auth::user()->unreadNotifications()->update(['is_read' => true, 'read_at' => now()]);

        if ($request->wantsJson()) {
            return response()->json(['unread' => 0]);
        }

        return redirect()->back()->with('status', 'All notifications marked as read.');
    }
}

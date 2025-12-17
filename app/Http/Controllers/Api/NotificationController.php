<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        // Get all notifications (both read and unread)
        // Laravel automatically orders them by newest first
        return response()->json($request->user()->notifications);
    }

    public function markAsRead(Request $request, $id)
    {
        // Find the specific notification for this user
        $notification = $request->user()
                                ->notifications()
                                ->where('id', $id)
                                ->first();

        if ($notification) {
            $notification->markAsRead(); // Adds the current timestamp to 'read_at'
        }

        return response()->json(['message' => 'Marked as read']);
    }
    
    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'All marked as read']);
    }
}
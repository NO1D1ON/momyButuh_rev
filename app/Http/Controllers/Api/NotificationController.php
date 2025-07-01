<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->get();
        return response()->json($notifications);
    }

    public function markAsRead(Request $request, \App\Models\Notification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $notification->markAsRead();
        return response()->json(['message' => 'Notification marked as read.']);
    }
}
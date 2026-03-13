<?php
// app/Http/Controllers/Api/NotificationController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get notifications with smart polling
     */
    public function sync(Request $request)
    {
        $user = $request->user();
        $since = $request->get('since');
        
        $query = Notification::where('user_id', $user->id)
            ->latest('updated_at');
        
        if ($since) {
            $query->where('updated_at', '>', $since);
        }
        
        $notifications = $query->limit(50)->get()->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'data' => $notification->data,
                'is_read' => $notification->is_read,
                'action_url' => $notification->action_url,
                'created_at' => $notification->created_at,
                'updated_at' => $notification->updated_at,
            ];
        });
        
        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
        
        return response()->json([
            'success' => true,
            'data' => $notifications,
            'unread_count' => $unreadCount,
            'server_time' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', auth()->id())
            ->findOrFail($id);
        
        $notification->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead(Request $request)
    {
        Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        
        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
            'unread_count' => 0
        ]);
    }

    /**
     * Get unread count only
     */
    public function unreadCount(Request $request)
    {
        $count = Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();
        
        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }
}
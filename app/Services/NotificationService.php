<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public function getUserNotificationsPaginated($user, $perPage = 15)
    {
        return $user->notifications()->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getRecentUserNotifications($user, $limit = 20)
    {
        return $user->notifications()->latest()->take($limit)->get();
    }

    public function markAsRead($id, $userId)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if ($notification) {
            $notification->update(['is_read' => true]);
            return true;
        }

        return false;
    }

    public function markAllAsRead($user)
    {
        return $user->notifications()->where('is_read', 0)->update(['is_read' => 1]);
    }
}

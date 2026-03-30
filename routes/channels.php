<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('chat.{userId}', function ($user, $userId) {
    // Sửa lại đoạn này
    if ($user) {
        \Log::info("Đã xác thực WebSocket: UserID đang login là " . $user->id . ", và nó xin vào nghe kênh của user: " . $userId);
        return (int) $user->id === (int) $userId;
    }
    
    \Log::error("Trình duyệt không gửi theo thông tin đăng nhập (Cookie/CSRF) khi gọi WebSocket!");
    return false;
});

Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    if ($user) {
        return (int) $user->id === (int) $userId;
    }
    return false;
});

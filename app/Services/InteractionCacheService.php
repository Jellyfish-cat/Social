<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class InteractionCacheService
{
    /**
     * Xử lý bật/tắt (Toggle) quan hệ (Like, Favorite, Follow...)
     * 
     * @param string $type Loại hành động (VD: 'post_likes', 'post_favorites')
     * @param int $entityId ID của đối tượng (VD: post_id)
     * @param int $userId ID của user thực hiện
     * @param callable $loadFromDbCallback Hàm nạp mảng user_id từ DB nếu Redis chưa có
     * @return array Mảng kết quả trả về cho FE
     */
    public static function toggleRelation(string $type, int $entityId, int $userId, callable $loadFromDbCallback)
    {
        $redisKey = "buffer:{$type}:{$entityId}:users";
        $loadedKey = "buffer:{$type}:{$entityId}:loaded";

        // 1. Nạp từ DB lên Redis nếu chưa nạp
        if (!Redis::exists($loadedKey)) {
            $existingUserIds = call_user_func($loadFromDbCallback);
            if (!empty($existingUserIds)) {
                Redis::sadd($redisKey, ...$existingUserIds);
            }
            Redis::set($loadedKey, 1);
        }

        // 2. Toggle thao tác
        $hasInteracted = Redis::sismember($redisKey, $userId);

        if ($hasInteracted) {
            // Hủy (Unlike/Unfavorite)
            Redis::srem($redisKey, $userId);
            Redis::srem("buffer:{$type}:{$entityId}:new_users", $userId);
            $action = 'removed';
        } else {
            // Thêm (Like/Favorite)
            Redis::sadd($redisKey, $userId);
            Redis::sadd("buffer:{$type}:{$entityId}:new_users", $userId);
            $action = 'added';
        }

        // 3. Đánh dấu entityId này cần đồng bộ
        Redis::sadd("buffer:sync_queue:{$type}", $entityId);

        // 4. Trả về kết quả
        return [
            'action' => $action,
            'count' => Redis::scard($redisKey)
        ];
    }

    /**
     * Tăng biến đếm (Ví dụ: đếm lượt View)
     */
    public static function incrementCounter(string $type, int $entityId)
    {
        $redisKey = "buffer:counters:{$type}:{$entityId}";
        $count = Redis::incr($redisKey);
        
        Redis::sadd("buffer:sync_queue:counters:{$type}", $entityId);
        
        return $count;
    }
}

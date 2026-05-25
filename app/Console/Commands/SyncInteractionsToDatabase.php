<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use App\Models\LikePost;
use App\Models\Post;
use App\Models\User;
use App\Models\Notification;

class SyncInteractionsToDatabase extends Command
{
    protected $signature = 'sync:interactions';
    protected $description = 'Đồng bộ toàn bộ các Interaction (Like, View, Favorite) từ Redis xuống MySQL';

    // Cấu hình mapping giữa type và Model xử lý
    // Khi thêm tính năng mới (vd: Favorite), bạn chỉ cần khai báo thêm vào mảng này
    protected $relationsConfig = [
        'post_likes' => [
            'model' => LikePost::class,
            'foreign_key' => 'post_id',
            'related_key' => 'user_id'
        ],
        // 'post_favorites' => [
        //     'model' => FavoritePost::class,
        //     'foreign_key' => 'post_id',
        //     'related_key' => 'user_id'
        // ],
    ];

    public function handle()
    {
        $this->syncRelations();
        $this->syncCounters();
        $this->info("Hoàn tất đồng bộ toàn bộ tương tác.");
    }

    private function syncRelations()
    {
        foreach ($this->relationsConfig as $type => $config) {
            $queueKey = "buffer:sync_queue:{$type}";
            $entityIds = Redis::smembers($queueKey);

            if (empty($entityIds)) continue;

            $modelClass = $config['model'];
            $fk = $config['foreign_key'];
            $rk = $config['related_key'];

            DB::beginTransaction();
            try {
                foreach ($entityIds as $entityId) {
                    $userIds = Redis::smembers("buffer:{$type}:{$entityId}:users");
                    
                    // 1. Xóa người đã unlike/unfavorite
                    if (!empty($userIds)) {
                        $modelClass::where($fk, $entityId)->whereNotIn($rk, $userIds)->delete();
                    } else {
                        $modelClass::where($fk, $entityId)->delete();
                    }

                    // 2. Thêm người mới like/favorite
                    foreach ($userIds as $uid) {
                        $modelClass::firstOrCreate([
                            $fk => $entityId,
                            $rk => $uid
                        ]);
                    }

                    // 3. Xử lý gửi Notification riêng cho post_likes
                    // (Nếu project lớn hơn có thể tách ra Event)
                    if ($type === 'post_likes') {
                        $this->sendPostLikeNotifications($entityId, $type);
                    }

                    // 4. Xóa ID khỏi hàng đợi đồng bộ
                    Redis::srem($queueKey, $entityId);
                }
                DB::commit();
                $this->info("Đã đồng bộ [{$type}] cho " . count($entityIds) . " đối tượng.");
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Lỗi đồng bộ [{$type}]: " . $e->getMessage());
            }
        }
    }

    private function sendPostLikeNotifications($postId, $type)
    {
        $newLikeUserIds = Redis::smembers("buffer:{$type}:{$postId}:new_users");
        if (!empty($newLikeUserIds)) {
            $post = Post::find($postId);
            if ($post) {
                foreach ($newLikeUserIds as $newUid) {
                    if ($post->user_id != $newUid) {
                        $liker = User::find($newUid);
                        if ($liker) {
                            $notification = Notification::create([
                                'user_id' => $post->user_id,
                                'content' => '<strong>' . ($liker->profile->display_name ?? $liker->name ?? 'Một người') . '</strong> đã thích bài viết của bạn. post:' . $post->id,
                                'type' => 'like'
                            ]);
                            broadcast(new \App\Events\NotificationSent($notification))->toOthers();
                        }
                    }
                }
            }
            Redis::del("buffer:{$type}:{$postId}:new_users");
        }
    }

    private function syncCounters()
    {
        // Ví dụ: đồng bộ lượt view (hiện tại chưa áp dụng, nhưng code đã sẵn sàng)
        $queueKey = "buffer:sync_queue:counters:post_views";
        $entityIds = Redis::smembers($queueKey);
        
        if (empty($entityIds)) return;

        DB::beginTransaction();
        try {
            foreach ($entityIds as $entityId) {
                $count = (int) Redis::get("buffer:counters:post_views:{$entityId}");
                if ($count > 0) {
                    Post::where('id', $entityId)->increment('views_count', $count);
                    Redis::set("buffer:counters:post_views:{$entityId}", 0);
                }
                Redis::srem($queueKey, $entityId);
            }
            DB::commit();
            $this->info("Đã đồng bộ post_views cho " . count($entityIds) . " bài viết.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Lỗi đồng bộ counters: " . $e->getMessage());
        }
    }
}

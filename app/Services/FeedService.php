<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\Http;

class FeedService
{
    public function __construct()
    {
        //
    }

    public function getFeedPosts($user, $isReload)
    {
        $followingIds = [];
        $interestedTopicIds = [];

        if ($user) {
            $followingIds = $user->following()->pluck('users.id')->toArray();

            $interestedTopicIds = \App\Models\LikePost::where('user_id', $user->id)
                ->latest()
                ->take(50)
                ->with(['post.topics'])
                ->get()
                ->flatMap(function ($like) {
                    return $like->post && $like->post->topics ? $like->post->topics->pluck('id')->toArray() : [];
                })
                ->unique()
                ->toArray();
        }

        try {
            $posts = collect();
            $aiSuccess = false;

            try {
                $aiResponse = Http::timeout(2)->get('http://127.0.0.1:8001/api/recommendations', [
                    'user_id' => $user ? $user->id : 0,
                    'is_reload' => $isReload
                ]);

                if ($aiResponse->successful() && isset($aiResponse->json()['recommended_post_ids'])) {
                    $postIds = $aiResponse->json()['recommended_post_ids'];
                    if (!empty($postIds)) {
                        $idStr = implode(',', $postIds);
                        $posts = Post::with(['user.profile', 'topics', 'likes', 'comments.user'])
                            ->withCount(['comments', 'likes'])
                            ->where('status', 'show')
                            ->whereIn('id', $postIds)
                            ->orderByRaw("FIELD(id, {$idStr})")
                            ->get();
                        
                        if ($posts->isNotEmpty()) {
                            $aiSuccess = true;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Ignore lỗi kết nối AI để chạy fallback
            }

            if (!$aiSuccess) {
                $posts = Post::with(['user.profile', 'topics', 'likes', 'comments.user'])
                    ->withCount(['comments', 'likes'])
                    ->where('status', 'show')
                    ->orderBy('created_at', 'desc')
                    ->limit(200)
                    ->get()
                    ->map(function ($post) use ($followingIds, $interestedTopicIds, $user, $isReload) {
                        $score = 0;

                        if ($user && $post->user_id == $user->id) {
                            if (!$isReload) return 10000000;
                            return -999999;
                        }

                        if (in_array($post->user_id, $followingIds)) {
                            $score += 100;
                        }

                        if (!empty($interestedTopicIds)) {
                            $postTopicIds = $post->topics->pluck('id')->toArray();
                            $matches = array_intersect($postTopicIds, $interestedTopicIds);
                            $score += count($matches) * 50;
                        }

                        $score += ($post->likes_count * 2);
                        $score += ($post->comments_count * 5);

                        $hoursAgo = $post->created_at->diffInHours(now());
                        $score -= ($hoursAgo * 10);

                        $post->ranking_score = $score; 
                        
                        return $post;
                    })
                    ->sortByDesc('ranking_score')
                    ->values();
            }

            if ($posts->isEmpty()) {
                throw new \Exception("Chưa có nội dung để hiển thị");
            }

            return $posts;

        } catch (\Exception $e) {
            return Post::with(['user.profile', 'topics', 'comments.user', 'likes'])
                ->withCount(['comments', 'likes'])
                ->orderBy('created_at', 'desc')
                ->where('status', 'show')
                ->limit(50)
                ->get();
        }
    }
}

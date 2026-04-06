<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class HomeController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // 1. Lấy danh sách ID người đang theo dõi
        $followingIds = $user->following()->pluck('users.id')->toArray();

        // 2. Lấy danh sách ID các topic mà user đã quan tâm (Like)
        $interestedTopicIds = \App\Models\LikePost::where('user_id', $user->id)
            ->with(['post.topic', 'post.topics'])
            ->get()
            ->flatMap(function ($like) {
                $ids = [];
                if ($like->post->topic_id) $ids[] = $like->post->topic_id;
                $ids = array_merge($ids, $like->post->topics->pluck('id')->toArray());
                return $ids;
            })
            ->unique()
            ->toArray();

        try {
            $results = collect();
            $excludeIds = [];

            // A. Ưu tiên 1: Bài viết của người đang theo dõi (Mới nhất)
            if (!empty($followingIds)) {
                $followingPosts = Post::search('')
                    ->whereIn('user_id', $followingIds)
                    ->orderBy('created_at', 'desc')
                    ->query(fn($query) => $query->with(['user.profile', 'topic', 'comments.user', 'likes'])->where('status', 'show'))
                    ->get();
                $results = $results->merge($followingPosts);
                $excludeIds = array_merge($excludeIds, $followingPosts->pluck('id')->toArray());
            }

            // B. Ưu tiên 2: Bài viết thuộc Topic quan tâm (Mới nhất, loại bỏ bài của người follow đã lấy ở trên)
            if (!empty($interestedTopicIds)) {
                $topicPosts = Post::search('')
                    ->whereIn('topic_id', $interestedTopicIds)
                    ->orderBy('created_at', 'desc')
                    ->query(fn($query) => $query->with(['user.profile', 'topic', 'comments.user', 'likes'])
                        ->where('status', 'show')
                        ->whereNotIn('id', $excludeIds))
                    ->get();
                $results = $results->merge($topicPosts);
                $excludeIds = array_merge($excludeIds, $topicPosts->pluck('id')->toArray());
            }

            // C. Ưu tiên 3: Các bài viết mới nhất khác để lấp đầy Feed
            $otherPosts = Post::with(['user.profile', 'topic', 'comments.user', 'likes'])
                ->where('status', 'show')
                ->whereNotIn('id', $excludeIds)
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();
            
            $posts = $results->merge($otherPosts);

            if ($posts->isEmpty()) {
                throw new \Exception("Bạn đã xem hết bài viết");
            }

        } catch (\Exception $e) {
            // Fallback: Nếu có lỗi hoặc không có gợi ý, hiện bài mới nhất như cũ
            $posts = Post::with(['user.profile', 'topic', 'comments.user', 'likes'])
                ->orderBy('created_at', 'desc')
                ->where('status', 'show')
                ->get();
        }

        return view('home', compact('posts'));
    }
}

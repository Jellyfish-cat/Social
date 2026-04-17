<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // 1. Khai báo mặc định cho Guest
        $followingIds = [];
        $interestedTopicIds = [];

        if ($user) {
            // 2. Lấy danh sách ID người đang theo dõi
            $followingIds = $user->following()->pluck('users.id')->toArray();

            // 3. Lấy danh sách ID các topic mà user đã quan tâm (Like)
            $interestedTopicIds = \App\Models\LikePost::where('user_id', $user->id)
                ->with(['post.topics'])
                ->get()
                ->flatMap(function ($like) {
                    return $like->post && $like->post->topics ? $like->post->topics->pluck('id')->toArray() : [];
                })
                ->unique()
                ->toArray();
        }

        try {
            $results = collect();
            $excludeIds = [];

            // A. Ưu tiên 1: Bài viết của người đang theo dõi (Nếu logged in)
            if (!empty($followingIds)) {
                $followingPosts = Post::search('')
                    ->whereIn('user_id', $followingIds)
                    ->orderBy('created_at', 'desc')
                    ->query(fn($query) => $query->with(['user.profile', 'topics', 'comments.user', 'likes'])->where('status', 'show'))
                    ->get();
                $results = $results->merge($followingPosts);
                $excludeIds = array_merge($excludeIds, $followingPosts->pluck('id')->toArray());
            }

            // B. Ưu tiên 2: Bài viết thuộc Topic quan tâm (Nếu logged in)
            if (!empty($interestedTopicIds)) {
                $topicPosts = Post::search('')
                    ->whereIn('topic_ids', $interestedTopicIds)
                    ->orderBy('created_at', 'desc')
                    ->query(fn($query) => $query->with(['user.profile', 'topics', 'comments.user', 'likes'])
                        ->where('status', 'show')
                        ->whereNotIn('id', $excludeIds))
                    ->get();
                $results = $results->merge($topicPosts);
                $excludeIds = array_merge($excludeIds, $topicPosts->pluck('id')->toArray());
            }

            // C. Ưu tiên 3: Các bài viết mới nhất khác để lấp đầy Feed (Dành cho cả Guest và User)
            $otherPosts = Post::with(['user.profile', 'topics', 'comments.user', 'likes'])
                ->where('status', 'show')
                ->whereNotIn('id', $excludeIds)
                ->orderBy('created_at', 'desc')
                ->get();
            
            $posts = $results->merge($otherPosts);

            if ($posts->isEmpty()) {
                throw new \Exception("Bạn đã xem hết bài viết");
            }

        } catch (\Exception $e) {
            $posts = Post::with(['user.profile', 'topics', 'comments.user', 'likes'])
                ->orderBy('created_at', 'desc')
                ->where('status', 'show')
                ->get();
        }

        // 3. Gợi ý người dùng
        try {
            $suggestedResults = collect();
            $excludeUserIds = $user ? array_merge([$user->id], $followingIds) : $followingIds;

            if ($user) {
                // --- Ưu tiên 0: Tương tác cũ (Chỉ cho Logged in) ---
                $interactedUserIdsRaw = collect()
                    ->merge(\App\Models\LikePost::where('user_id', $user->id)->with('post')->get()->pluck('post.user_id'))
                    ->merge(\App\Models\Comment::where('user_id', $user->id)->with('post')->get()->pluck('post.user_id'))
                    ->filter();

                $sortedInteractedIds = $interactedUserIdsRaw->countBy()->sortDesc()->keys()->toArray();

                if (!empty($sortedInteractedIds)) {
                    $tier0 = User::whereIn('id', $sortedInteractedIds)
                        ->with('profile')
                        ->whereNotIn('id', $excludeUserIds)
                        ->get()
                        ->sortBy(function($u) use ($sortedInteractedIds) {
                            return array_search($u->id, $sortedInteractedIds);
                        })
                        ->take(5);

                    $suggestedResults = $suggestedResults->merge($tier0);
                    $excludeUserIds = array_merge($excludeUserIds, $tier0->pluck('id')->toArray());
                }
            }

            // --- Các tầng ưu tiên khác: Topic chung, Bạn chung, Phổ biến ---
            // (Đơn giản hóa cho Guest: chỉ lấy người dùng phổ biến/mới nhất)
            if ($suggestedResults->count() < 5) {
                $tier3 = User::with('profile')
                    ->whereNotIn('id', $excludeUserIds)
                    ->limit(10)
                    ->get();
                $suggestedResults = $suggestedResults->merge($tier3);
            }
            
            $suggestedUsers = $suggestedResults->unique('id')->take(5);

        } catch (\Exception $e) {
            $suggestedUsers = User::with('profile')->limit(5)->get();
        }
        
        if (!$user || $user->role === 'user') {
            return view('home', compact('posts', 'suggestedUsers'));
        } else {
            return redirect()->route('admin.dashboard');
        }
    }
      
    }

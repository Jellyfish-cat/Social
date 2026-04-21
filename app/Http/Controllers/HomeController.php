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
            // New Smart Ranking Algorithm (Weighted Scoring)
            $posts = Post::with(['user.profile', 'topics', 'likes', 'comments.user'])
                ->withCount(['comments', 'likes'])
                ->where('status', 'show')
                ->orderBy('created_at', 'desc')
                ->limit(200) // Lấy pool 200 bài viết mới nhất để xếp hạng
                ->get()
                ->map(function ($post) use ($followingIds, $interestedTopicIds) {
                    $score = 0;

                    // 1. Ưu tiên Bạn bè (Collaborative Filtering: Social Graph)
                    if (in_array($post->user_id, $followingIds)) {
                        $score += 100;
                    }

                    // 2. Ưu tiên Chủ đề quan tâm (Content-Based Filtering)
                    if (!empty($interestedTopicIds)) {
                        $postTopicIds = $post->topics->pluck('id')->toArray();
                        $matches = array_intersect($postTopicIds, $interestedTopicIds);
                        $score += count($matches) * 50;
                    }

                    // 3. Tương tác cộng đồng (Engagement Scoring)
                    $score += ($post->likes_count * 2);    // Like quan trọng
                    $score += ($post->comments_count * 5); // Comment quan trọng hơn

                    // 4. Ưu tiên độ tươi mới (Time Decay Algorithm)
                    // Cứ mỗi giờ trôi qua trừ 10 điểm
                    $hoursAgo = $post->created_at->diffInHours(now());
                    $score -= ($hoursAgo * 10);

                    // Gán điểm để có thể debug hoặc hiển thị
                    $post->ranking_score = $score; 
                    
                    return $post;
                })
                ->sortByDesc('ranking_score') // Sắp xếp theo tổng điểm
                ->values(); // Reset khóa mảng sau khi sort

            if ($posts->isEmpty()) {
                throw new \Exception("Chưa có nội dung để hiển thị");
            }

        } catch (\Exception $e) {
            // Fallback: Nếu có lỗi (ví dụ Reverb/Search lỗi) thì chỉ lấy theo thời gian
            $posts = Post::with(['user.profile', 'topics', 'comments.user', 'likes'])
                ->withCount(['comments', 'likes'])
                ->orderBy('created_at', 'desc')
                ->where('status', 'show')
                ->limit(50)
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
        } elseif ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'moderator') {
            return redirect()->route('admin.reports', 'pending');
        } else {
            return view('home', compact('posts', 'suggestedUsers'));
        }
    }
      
    }

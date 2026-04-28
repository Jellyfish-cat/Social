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
            // Flag để kiểm tra xem AI Service có hoạt động và trả về dữ liệu không
            $posts = collect();
            $aiSuccess = false;

            // 1. TÍCH HỢP PYTHON AI RECOMMENDER SERVICE
            try {
                $aiResponse = \Illuminate\Support\Facades\Http::timeout(2)->get('http://127.0.0.1:8001/api/recommendations', [
                    'user_id' => $user ? $user->id : 0
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
                // Ignore lỗi kết nối AI để chạy fallback bên dưới
            }

            // 2. FALLBACK: NẾU AI SERVER CHẾT HOẶC KHÔNG CÓ KẾT QUẢ -> CHẠY THUẬT TOÁN PHP THỦ CÔNG
            if (!$aiSuccess) {
                $posts = Post::with(['user.profile', 'topics', 'likes', 'comments.user'])
                    ->withCount(['comments', 'likes'])
                    ->where('status', 'show')
                    ->orderBy('created_at', 'desc')
                    ->limit(200) // Lấy pool 200 bài viết mới nhất để xếp hạng
                    ->get()
                    ->map(function ($post) use ($followingIds, $interestedTopicIds) {
                        $score = 0;

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

        } catch (\Exception $e) {
            // Fallback: Lấy 50 bài mới nhất
            $posts = Post::with(['user.profile', 'topics', 'comments.user', 'likes'])
                ->withCount(['comments', 'likes'])
                ->orderBy('created_at', 'desc')
                ->where('status', 'show')
                ->limit(50)
                ->get();
        }

        // 3. Gợi ý người dùng (Sử dụng Python AI Recommender)
        try {
            $aiResponse = \Illuminate\Support\Facades\Http::timeout(3)->get('http://127.0.0.1:8001/api/user_recommendations', [
                'user_id' => $user ? $user->id : 0
            ]);

            if ($aiResponse->successful()) {
                $aiData = $aiResponse->json();
                $recommendedUserIds = $aiData['recommended_user_ids'] ?? [];
                
                if (!empty($recommendedUserIds)) {
                    $suggestedUsers = User::whereIn('id', $recommendedUserIds)
                        ->with('profile')
                        ->get()
                        ->sortBy(function($u) use ($recommendedUserIds) {
                            return array_search($u->id, $recommendedUserIds);
                        })->values();
                } else {
                    $suggestedUsers = User::where('id', '!=', $user ? $user->id : 0)
                        ->where('role', 'user')
                        ->with('profile')
                        ->limit(10)->get();
                }
            } else {
                throw new \Exception("AI Service Error");
            }
        } catch (\Exception $e) {
            $suggestedUsers = User::where('id', '!=', $user ? $user->id : 0)
                ->where('role', 'user')
                ->with('profile')
                ->limit(10)->get();
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
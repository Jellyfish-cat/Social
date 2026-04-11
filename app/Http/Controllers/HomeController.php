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
        
        // 1. Lấy danh sách ID người đang theo dõi
        $followingIds = $user->following()->pluck('users.id')->toArray();

        // 2. Lấy danh sách ID các topic mà user đã quan tâm (Like)
        $interestedTopicIds = \App\Models\LikePost::where('user_id', $user->id)
            ->with(['post.topics'])
            ->get()
            ->flatMap(function ($like) {
                return $like->post->topics->pluck('id')->toArray();
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
                    ->query(fn($query) => $query->with(['user.profile', 'topics', 'comments.user', 'likes'])->where('status', 'show'))
                    ->get();
                $results = $results->merge($followingPosts);
                $excludeIds = array_merge($excludeIds, $followingPosts->pluck('id')->toArray());
            }

            // B. Ưu tiên 2: Bài viết thuộc Topic quan tâm (Mới nhất, loại bỏ bài của người follow đã lấy ở trên)
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

            // C. Ưu tiên 3: Các bài viết mới nhất khác để lấp đầy Feed
            $otherPosts = Post::with(['user.profile', 'topics', 'comments.user', 'likes'])
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
            $posts = Post::with(['user.profile', 'topics', 'comments.user', 'likes'])
                ->orderBy('created_at', 'desc')
                ->where('status', 'show')
                ->get();
        }

        // 3. Gợi ý người dùng (4 Tầng ưu tiên: Tương tác -> Sở thích -> Bạn chung -> Phổ biến)
        try {
            $suggestedResults = collect();
            $excludeUserIds = array_merge([$user->id], $followingIds);

            // --- Ưu tiên 0: Người dùng bạn đã từng tương tác (Like, Comment) nhưng chưa follow ---
            // Lấy tất cả ID tương tác và đếm tần suất
            $interactedUserIdsRaw = collect()
                ->merge(\App\Models\LikePost::where('user_id', $user->id)->with('post')->get()->pluck('post.user_id'))
                ->merge(\App\Models\Comment::where('user_id', $user->id)->with('post')->get()->pluck('post.user_id'))
                ->filter(); // Loại bỏ null

            $sortedInteractedIds = $interactedUserIdsRaw->countBy() // Đếm số lần tương tác với mỗi user
                ->sortDesc() // Sắp xếp người tương tác nhiều nhất lên đầu
                ->keys()
                ->toArray();

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

            // --- Ưu tiên 1: Người dùng có cùng sở thích Topic ---
            if ($suggestedResults->count() < 5 && !empty($interestedTopicIds)) {
                $topicUserIds = Post::whereHas('topics', function($q) use ($interestedTopicIds) {
                        $q->whereIn('topic_id', $interestedTopicIds);
                    })
                    ->pluck('user_id')->unique()->toArray();
                
                $tier1 = User::search('')
                    ->query(fn($q) => $q->with('profile')->whereIn('id', $topicUserIds)->whereNotIn('id', $excludeUserIds))
                    ->take(20)->get();
                $suggestedResults = $suggestedResults->merge($tier1);
                $excludeUserIds = array_merge($excludeUserIds, $tier1->pluck('id')->toArray());
            }

            // --- Ưu tiên 2: Bạn của bạn (Theo dõi chéo) ---
            if ($suggestedResults->count() < 5 && !empty($followingIds)) {
                $fofIds = \Illuminate\Support\Facades\DB::table('follows')
                    ->whereIn('follower_id', $followingIds)->pluck('following_id')->unique()->toArray();

                $tier2 = User::search('')
                    ->query(fn($q) => $q->with('profile')->whereIn('id', $fofIds)->whereNotIn('id', $excludeUserIds))
                    ->take(20)->get();
                $suggestedResults = $suggestedResults->merge($tier2);
                $excludeUserIds = array_merge($excludeUserIds, $tier2->pluck('id')->toArray());
            }

            // --- Ưu tiên 3: Gợi ý người dùng mới/khác ---
            if ($suggestedResults->count() < 5) {
                $tier3 = User::search('')
                    ->query(fn($q) => $q->with('profile')->whereNotIn('id', $excludeUserIds))
                    ->take(20)->get();
                $suggestedResults = $suggestedResults->merge($tier3);
            }
            
            $suggestedUsers = $suggestedResults->unique('id')->take(5);

        } catch (\Exception $e) {
            $suggestedUsers = User::with('profile')->where('id', '!=', $user->id)
                ->whereNotIn('id', $followingIds)->limit(5)->get();
        }

        return view('home', compact('posts', 'suggestedUsers'));
    }
}

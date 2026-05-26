<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Support\Facades\Http;

class ProfileService
{
    public function getProfileDetail($userId)
    {
        $profile = Profile::where('user_id', $userId)->firstOrFail();
        
        if ($profile->user->status === 'hidden' && auth()->user()->role !== 'admin') {
            abort(403, 'Tài khoản này đã bị khóa hoặc không tồn tại');
        } 

        return $profile;
    }

    public function getProfilePosts($user)
    {
        return $user->posts()
            ->with(['user.profile', 'topics', 'media', 'likes'])
            ->withCount(['comments', 'likes', 'favorites'])
            ->latest()
            ->get();
    }

    public function getSuggestedUsers($userId)
    {
        try {
            $aiResponse = Http::timeout(3)->get('http://127.0.0.1:8001/api/user_recommendations', [
                'user_id' => $userId ?: 0
            ]);
            if ($aiResponse->successful()) {
                $aiData = $aiResponse->json();
                $recommendedUserIds = $aiData['recommended_user_ids'] ?? [];
                
                if (!empty($recommendedUserIds)) {
                    return User::whereIn('id', $recommendedUserIds)
                        ->with('profile')
                        ->get()
                        ->sortBy(function($u) use ($recommendedUserIds) {
                            return array_search($u->id, $recommendedUserIds);
                        })->values();
                }
            }
        } catch (\Exception $e) {
            // Handled by returning fallback
        }

        return User::where('id', '!=', $userId ?: 0)
            ->where('role', 'user')
            ->with('profile')
            ->limit(5)->get();
    }

    public function getUserPosts($userId)
    {
        $user = User::findOrFail($userId);
        if ($user->status === 'hidden' && auth()->user()->role !== 'admin') {
            return collect();
        }
        return $user->posts()->where('status','show')
            ->orderBy('pinned', 'desc')
            ->latest()
            ->get();
    }

    public function getUserFavorites($userId)
    {
        $user = User::findOrFail($userId);

        return Post::join('favorites', 'posts.id', '=', 'favorites.post_id')
            ->join('users', 'posts.user_id', '=', 'users.id')
            ->where('favorites.user_id', $user->id)
            ->where('posts.status', 'show')
            ->where('users.status', 'show')
            ->orderBy('favorites.created_at', 'desc')
            ->select('posts.*')
            ->get();
    }

    public function getUserComments($userId)
    {
        $user = User::findOrFail($userId);
        return Comment::where('user_id', $user->id)
            ->where('status', 'show')
            ->whereHas('post', function($q) {
                $q->where('status', 'show')->whereHas('user', function($u) {
                    $u->where('status', 'show');
                });
            })
            ->latest()
            ->get();
    }

    public function getUserLikes($userId)
    {
        $user = User::findOrFail($userId);
        return Post::join('like_posts', 'posts.id', '=', 'like_posts.post_id')
            ->join('users', 'posts.user_id', '=', 'users.id')
            ->where('like_posts.user_id', $user->id)
            ->where('posts.status', 'show')
            ->where('users.status', 'show')
            ->orderBy('like_posts.created_at', 'desc')
            ->select('posts.*')
            ->get();
    }
}

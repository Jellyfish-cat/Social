<?php

namespace App\Services;

use App\Models\LikeComment;
use App\Models\LikePost;
use App\Models\Follow;
use App\Models\Favorite;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Models\Notification;

class InteractionService
{
    public function getPostWithLikes($postId)
    {
        return Post::with(['likedUsers' => function($q) {
            $q->where('status', 'show')->with('profile');
        }])->findOrFail($postId);
    }
    
    public function toggleLikeComment($commentId, $user)
    {
        $liked = LikeComment::where('user_id', $user->id)
                        ->where('comment_id', $commentId)
                        ->exists();

        if (!$liked) {
            LikeComment::create([
                'user_id' => $user->id,
                'comment_id' => $commentId
            ]);
            
            $comment = Comment::find($commentId);
            if ($comment && $comment->user_id !== $user->id) {
                $notification = Notification::create([
                    'user_id' => $comment->user_id,
                    'content' => '<strong>' . ($user->profile->display_name ?? $user->name ?? 'Một người') . '</strong> đã thích bình luận của bạn. likecomment:' . $commentId,
                    'type' => 'likecomment'
                ]);
                broadcast(new \App\Events\NotificationSent($notification))->toOthers();
            }
        } else {
            LikeComment::where('user_id', $user->id)
                ->where('comment_id', $commentId)
                ->delete();
        }

        return LikeComment::where('comment_id', $commentId)->count();
    }

    public function toggleLikePost($postId, $user)
    {
        $liked = LikePost::where('user_id', $user->id)
                        ->where('post_id', $postId)
                        ->exists();

        if (!$liked) {
            LikePost::create([
                'user_id' => $user->id,
                'post_id' => $postId
            ]);

            $post = Post::find($postId);
            if ($post && $post->user_id !== $user->id) {
                $notification = Notification::create([
                    'user_id' => $post->user_id,
                    'content' => '<strong>' . ($user->profile->display_name ?? $user->name ?? 'Một người') . '</strong> đã thích bài viết của bạn. post:' . $postId,
                    'type' => 'like'
                ]);
                broadcast(new \App\Events\NotificationSent($notification))->toOthers();
            }
        } else {
            LikePost::where('user_id', $user->id)
                ->where('post_id', $postId)
                ->delete();
        }

        return LikePost::where('post_id', $postId)->count();
    }

    public function toggleFollow($followingId, $user)
    {
        $followed = Follow::where('follower_id', $user->id)
                        ->where('following_id', $followingId)
                        ->exists();

        if (!$followed) {
            Follow::create([
                'follower_id' => $user->id,
                'following_id' => $followingId
            ]);

            if ($followingId != $user->id) {
                $notification = Notification::create([
                    'user_id' => $followingId,
                    'content' => '<strong>' . ($user->profile->display_name ?? $user->name ?? 'Một người') 
                    . '</strong> đã bắt đầu theo dõi bạn. follow:' . $user->id,
                    'type' => 'follow'
                ]);
                broadcast(new \App\Events\NotificationSent($notification))->toOthers();
            }
        } else {
            Follow::where('follower_id', $user->id)
                ->where('following_id', $followingId)
                ->delete();
        }

        return [
            'following_count' => Follow::where('following_id', $followingId)->count(),
            'follower_count' => Follow::where('follower_id', $user->id)->count()
        ];
    }

    public function toggleFavorite($postId, $user)
    {
        $favorited = Favorite::where('user_id', $user->id)
                        ->where('post_id', $postId)
                        ->exists();

        if (!$favorited) {
            Favorite::create([
                'user_id' => $user->id,
                'post_id' => $postId
            ]);
        } else {
            Favorite::where('user_id', $user->id)
                ->where('post_id', $postId)
                ->delete();
        }
    }

    public function getFollowDetails($userId, $type)
    {
        $user = User::findOrFail($userId);
        
        if ($type == "follower") {
            $values = $user->followers()->where('status', 'show')->with('profile')->get();
        } else if ($type == "following") {
            $values = $user->following()->where('status', 'show')->with('profile')->get();
        } else {
            $values = collect();
        }

        return [
            'user' => $user,
            'values' => $values
        ];
    }
}

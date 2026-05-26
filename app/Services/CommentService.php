<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Notification;
use App\Models\Report;
use Illuminate\Support\Facades\Storage;

class CommentService
{
    public function __construct()
    {
        //
    }

    public function getPostComments($postId)
    {
        return Comment::where('post_id', $postId)
            ->whereNull('parent_comment_id')
            ->where('status', 'show')
            ->whereHas('user', function($q) {
                $q->where('status', 'show');
            })
            ->with(['user.profile', 'replies' => function($q) {
                $q->where('status', 'show')->whereHas('user', fn($u) => $u->where('status', 'show'))->with('user.profile');
            }])
            ->latest()
            ->get();
    }
    public function getAdminComments($perPage = 10)
    {
        return Comment::with(['user.profile', 'post', 'likes', 'parent','replies'])
            ->withCount(['replies', 'likes'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function createComment($data, $postId, $user, $file = null)
    {
        $post = Post::findOrFail($postId);
        $targetId = $data['parent_id'] ?? null;
        $targetComment = $targetId ? Comment::find($targetId) : null;
        $rootParentId = ($targetComment && $targetComment->parent_comment_id) 
            ? $targetComment->parent_comment_id 
            : $targetId; 

        $comment = Comment::create([
            'user_id' => $user ? $user->id : 1,
            'post_id' => $post->id,
            'content' => $data['content'],
            'parent_comment_id'=> $rootParentId,
            'status' => 'show'
        ]);

        $userId = $user->id;
        $userName = '<strong>' . ($user->profile->display_name ?? $user->name ?? 'Một người') . '</strong>';

        if (!$comment->parent_comment_id) {
            if ($userId !== $post->user_id) {
                $notif = Notification::create([
                    'user_id' => $post->user_id,
                    'content' => "{$userName} đã bình luận bài viết của bạn. comment:{$comment->id}",
                    'type' => 'comment'
                ]);
                broadcast(new \App\Events\NotificationSent($notif))->toOthers();
            }
        } else {
            if ($userId !== $post->user_id) {
                $msg = ($targetComment && $targetComment->user_id === $post->user_id) 
                    ? "{$userName} đã phản hồi bình luận trong bài viết của bạn." 
                    : "{$userName} đã bình luận bài viết của bạn.";
                
                $notif = Notification::create([
                    'user_id' => $post->user_id,
                    'content' => "{$msg} comment:{$comment->id}",
                    'type' => 'comment'
                ]);
                broadcast(new \App\Events\NotificationSent($notif))->toOthers();
            }
            if ($targetComment && $targetComment->user_id !== $post->user_id && $userId !== $targetComment->user_id) {
                $notif = Notification::create([
                    'user_id' => $targetComment->user_id,
                    'content' => "{$userName} đã phản hồi bình luận của bạn. comment:{$comment->id}",
                    'type' => 'comment'
                ]);
                broadcast(new \App\Events\NotificationSent($notif))->toOthers();
            }
        }
        
        if ($file) {
            $path = $file->storeAs('comments/media', $file->hashName(), 'public');
            $comment->media_path = $path;
            $comment->save();
        }

        return [
            'comment' => $comment,
            'post' => $post
        ];
    }

    public function getLatestComments($postId, $limit = 5)
    {
        return Comment::where('post_id', $postId)
            ->latest()->where('status', 'show')
            ->take($limit)
            ->get();
    }

    public function deleteComment($id, $user)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return ['success' => false, 'message' => 'Không tìm thấy comment', 'code' => 404];
        }

        if ($user->id !== $comment->user_id && !in_array($user->role, ['admin', 'moderator'])) {
            abort(403, 'Bạn không có quyền xóa bình luận này');
        }

        if ($comment->media_path) {
            Storage::disk('public')->delete($comment->media_path);
        }

        Report::where('target_id', $id)->where('target_type', Comment::class)->delete();
        $comment->delete();
        
        return [
            'success' => true,
            'data' => Comment::latest()->get(),
            'count' => Comment::count(),
            'message' => 'Xóa thành công'
        ];
    }

    public function getCommentWithLikes($id)
    {
        return Comment::with(['likedUsers' => function($q) {
            $q->where('status', 'show')->with('profile');
        }])->findOrFail($id);
    }
}

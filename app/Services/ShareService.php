<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Conversation;
use App\Models\Message;

class ShareService
{
    public function getShareFriendsList($user)
    {
        // Lấy danh sách following và followers
        $following = $user->following()->with('profile')->get();
        $followers = $user->followers()->with('profile')->get();
        
        // Gộp lại và loại bỏ trùng lặp
        return $following->merge($followers)->unique('id');
    }

    public function sharePostToUsers($postId, array $userIds, $authUser)
    {
        $post = Post::findOrFail($postId);
        $postUrl = route('posts.detail', $post->id);
        $authId = $authUser->id;
        
        $sharedCount = 0;

        foreach ($userIds as $userId) {
            // Tìm hoặc tạo conversation
            $conversation = Conversation::whereHas('users', function ($q) use ($authId) {
                $q->where('user_id', $authId);
            })
            ->whereHas('users', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->first();

            if (!$conversation) {
                $conversation = Conversation::create(['type' => 'private']);
                $conversation->users()->attach([$authId, $userId]);
            }

            // Tạo tin nhắn đính kèm link bài viết
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id'       => $authId,
                'content'         => "Đã chia sẻ một bài viết: " . $postUrl,
                'read_at'         => null,
            ]);

            // Phát sự kiện realtime
            $chatData = [
                'id'          => $message->id,
                'content'     => $message->content,
                'sender_id'   => $message->sender_id,
                'receiver_id' => (int)$userId,
                'created_at'  => $message->created_at->format('H:i d/m'),
                'media'       => [],
                'sender_avatar' => $authUser->profile->avatar ?? null,
            ];
            
            broadcast(new \App\Events\MessageSent((object) $chatData))->toOthers();
            
            $sharedCount++;
        }

        return $sharedCount;
    }
}

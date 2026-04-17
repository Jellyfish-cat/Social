<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;

class ShareController extends Controller
{
    /**
     * Lấy danh sách bạn bè để hiển thị trong modal chia sẻ
     */
    public function getShareList($id)
    {
        $user = auth()->user();
        
        // Lấy danh sách following và followers
        $following = $user->following()->with('profile')->get();
        $followers = $user->followers()->with('profile')->get();
        
        // Gộp lại và loại bỏ trùng lặp
        $friends = $following->merge($followers)->unique('id');
        
        $postId = $id;
        
        return view('posts.partials.share-user-list', compact('friends', 'postId'));
    }

    /**
     * Thực hiện gửi bài viết cho nhiều người dùng đã chọn
     */
    public function shareToUsers(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $authId = auth()->id();
        $post = Post::findOrFail($request->post_id);
        $postUrl = route('posts.detail', $post->id);
        
        $sharedCount = 0;

        foreach ($request->user_ids as $userId) {
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
                'sender_avatar' => auth()->user()->profile->avatar ?? null,
            ];
            
            broadcast(new \App\Events\MessageSent((object) $chatData))->toOthers();
            
            $sharedCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "Đã chia sẻ bài viết cho $sharedCount người dùng."
        ]);
    }
}

<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\MessageMedia;
use Illuminate\Support\Facades\Storage;

class MessageService
{
    public function getConversationMessages($conversation, $userId)
    {
        if (!$conversation) return collect();
        $userPivot = $conversation->users->firstWhere('id', $userId)?->pivot;
        $deletedAt = $userPivot ? $userPivot->deleted_at : null;

        $query = Message::where('conversation_id', $conversation->id)
            ->with(['sender.profile', 'media'])
            ->orderBy('created_at');
        
        if ($deletedAt) {
            $query->where('created_at', '>', $deletedAt);
        }
        return $query->get();
    }

    public function getAllMessagesForAdmin($perPage = 10)
    {
        return Message::whereHas('conversation.users', function($q) {
                $q->whereIn('role', ['admin', 'moderator']);
            })->whereDoesntHave('conversation.users', function ($q) {
                $q->whereNotIn('role', ['admin', 'moderator']);
            })
            ->with(['media','sender'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getAdminMessages($conversationId, $perPage = 10)
    {
        return Message::where('conversation_id', $conversationId)->with(['media','sender']) 
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function sendPrivateMessage($data, $receiverId, $user, $files = null)
    {
        $receiver = User::findOrFail($receiverId);

        if ($user->role === 'user') {
            if ($receiver->role !== 'user') {
                throw new \Exception('Bạn không thể nhắn tin trực tiếp cho Ban quản trị');
            }
        } else {
            if (!in_array($receiver->role, ['admin', 'moderator'])) {
                throw new \Exception('Ban quản trị chỉ có thể nhắn tin cho nhân viên');
            }
        }

        if ($receiver->status === 'hidden') {
            throw new \Exception('Tài khoản này đã bị khóa do vi phạm');
        }

        $conversation = Conversation::whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->whereHas('users', function ($q) use ($receiverId) {
                $q->where('user_id', $receiverId);
            })
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create(['type' => 'private']);
            $conversation->users()->attach([$user->id, $receiverId]);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $user->id,
            'content'         => $data['content'] ?? '',
            'read_at' => null,
        ]);

        $mediaList = [];
        if ($files) {
            foreach ($files as $file) {
                $path = $file->store('message_media', 'public');
                $type = str_contains($file->getMimeType(), 'video') ? 'video' : 'image';

                MessageMedia::create([
                    'message_id' => $message->id,
                    'file_path'  => $path,
                    'type'       => $type,
                ]);

                $mediaList[] = [
                    'file_path' => asset('storage/' . $path),
                    'type'      => $type,
                ];
            }
        }

        $chatData = [
            'id'              => $message->id,
            'content'         => $message->content,
            'sender_id'       => $message->sender_id,
            'sender_name'     => $user->profile->display_name ?? $user->name,
            'sender_avatar'   => $user->profile->avatar ?? null,
            'receiver_id'     => $receiverId,
            'is_group'        => false,
            'conversation_id' => $message->conversation_id,
            'created_at'      => $message->created_at->format('H:i d/m'),
            'timestamp'       => $message->created_at->timestamp,
            'media'           => $mediaList,
            'type'            => $message->type,
        ];
        
        broadcast(new \App\Events\MessageSent((object) $chatData))->toOthers();

        return [
            'user' => $receiver,
            'message' => $message,
            'mediaList' => $mediaList
        ];
    }

    public function sendGroupMessage($data, $convoId, $user, $files = null)
    {
        $conversation = Conversation::where('type', 'group')
            ->whereHas('users', fn($q) => $q->where('user_id', $user->id))
            ->with('users.profile')
            ->findOrFail($convoId);

        if ($conversation->status === 'hidden') {
            throw new \Exception('Nhóm này đã bị giải tán');
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $user->id,
            'content'         => $data['content'] ?? '',
            'read_at'         => null,
        ]);

        $mediaList = [];
        if ($files) {
            foreach ($files as $file) {
                $path = $file->store('message_media', 'public');
                $type = str_contains($file->getMimeType(), 'video') ? 'video' : 'image';

                MessageMedia::create([
                    'message_id' => $message->id,
                    'file_path'  => $path,
                    'type'       => $type,
                ]);

                $mediaList[] = [
                    'file_path' => asset('storage/' . $path),
                    'type'      => $type,
                ];
            }
        }

        $sender_name = $user->profile->display_name ?? $user->name;
        $sender_avatar = $user->profile->avatar ?? null;

        foreach ($conversation->users as $member) {
            if ($member->id !== $user->id) {
                $chatData = [
                    'id'              => $message->id,
                    'content'         => $message->content,
                    'sender_id'       => $message->sender_id,
                    'sender_name'     => $sender_name,
                    'sender_avatar'   => $sender_avatar,
                    'is_group'        => true,
                    'conversation_id' => $conversation->id,
                    'group_name'      => $conversation->name,
                    'group_avatar'    => $conversation->avatar,
                    'receiver_id'     => $member->id,
                    'type'            => $message->type,
                    'created_at'      => $message->created_at->format('H:i d/m'),
                    'timestamp'       => $message->created_at->timestamp,
                    'media'           => $mediaList,
                ];
                broadcast(new \App\Events\MessageSent((object) $chatData))->toOthers();
            }
        }

        return [
            'message' => $message,
            'mediaList' => $mediaList,
            'conversation' => $conversation
        ];
    }

    public function markMessagesAsRead($id, $authId)
    {
        $conversation = Conversation::where(function($q) use ($authId, $id) {
            $q->where('id', $id)
              ->orWhere(function($sub) use ($authId, $id) {
                  $sub->whereHas('users', fn($u) => $u->where('user_id', $authId))
                      ->whereHas('users', fn($u) => $u->where('user_id', $id))
                      ->where('type', 'private');
              });
        })->first();

        if ($conversation) {
            Message::where('conversation_id', $conversation->id)
                ->where('sender_id', '!=', $authId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }
    }

    public function searchMessages($query, $conversationId)
    {
        $messages = Message::search($query)
            ->where('conversation_id', (int) $conversationId)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get()
            ->load('sender.profile', 'media');

        $messages->transform(function ($msg) {
            $msg->time_ago = $msg->created_at->diffForHumans();
            return $msg;
        });

        return $messages;
    }

    public function unsendMessage($id, $user)
    {
        $message = Message::with('conversation.users')->findOrFail($id);

        if ($user->role !== 'admin' && $user->id !== $message->sender_id) {
            throw new \Exception('Bạn không có quyền', 403);
        }

        if ($message->status === 'unsend') {
            return true;
        }

        $message->update(['status' => 'unsend']);

        $receivers = $message->conversation->users->where('id', '!=', $user->id);
        foreach ($receivers as $receiver) {
            broadcast(new \App\Events\MessageDeleted($message->id, $receiver->id))->toOthers();
        }

        return true;
    }

    public function deleteMessagePermanently($id, $user)
    {
        if (!in_array($user->role, ['admin', 'moderator'])) {
            throw new \Exception('Bạn không có quyền thực hiện hành động này', 403);
        }

        $message = Message::with('media')->findOrFail($id);
        
        foreach ($message->media as $mm) {
            if ($mm->file_path) {
                Storage::disk('public')->delete($mm->file_path);
            }
        }

        $message->delete();
    }
}

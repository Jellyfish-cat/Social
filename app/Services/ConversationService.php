<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class ConversationService
{
    public function getConversationById($id)
    {
        return Conversation::findOrFail($id);
    }

    public function getConversationWithMembers($id, $userId)
    {
        return Conversation::whereHas('users', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with('users.profile')->findOrFail($id);
    }

    public function getUserConversations($userId)
    {
        return Conversation::whereHas('users', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->with([
                'users.profile',
                'latestMessage.sender.profile'
            ])
            ->withCount([
                'messages as unread_count' => function ($q) use ($userId) {
                    $q->where('sender_id', '!=', $userId)
                      ->whereNull('read_at');
                }
            ])
            ->get()
            ->filter(function($convo) use ($userId) {
                $userPivot = $convo->users->firstWhere('id', $userId)?->pivot;
                $deletedAt = $userPivot ? $userPivot->deleted_at : null;
                if (!$deletedAt) return true;
                return $convo->latestMessage && $convo->latestMessage->created_at > $deletedAt;
            })
            ->sortByDesc(fn($conversation) => optional($conversation->latestMessage)->created_at)
            ->values();
    }

    public function getPrivateConversation($authId, $otherUserId)
    {
        return Conversation::whereHas('users', function ($q) use ($authId) {
                $q->where('user_id', $authId);
            })
            ->whereHas('users', function ($q) use ($otherUserId) {
                $q->where('user_id', $otherUserId);
            })
            ->with(['users.profile'])
            ->first();
    }

    public function getGroupConversation($id, $authId)
    {
        return Conversation::where('type', 'group')
            ->whereHas('users', fn($q) => $q->where('user_id', $authId))
            ->with(['users.profile'])
            ->findOrFail($id);
    }

    public function createGroup($data, $user, $avatarFile = null)
    {
        $avatarPath = null;
        if ($avatarFile) {
            $avatarPath = $avatarFile->store('group_avatars', 'public');
        } elseif (!empty($data['dicebear_url'])) {
            if (str_contains($data['dicebear_url'], 'api.dicebear.com')) {
                try {
                    $response = Http::get($data['dicebear_url']);
                    if ($response->successful()) {
                        $name = 'group_' . uniqid() . '.svg';
                        Storage::disk('public')->put('group_avatars/' . $name, $response->body());
                        $avatarPath = 'group_avatars/' . $name;
                    }
                } catch (\Exception $e) {}
            }
        }

        $conversation = Conversation::create([
            'type' => 'group',
            'name' => $data['name'],
            'avatar' => $avatarPath,
            'status' => 'show',
            'creator_id' => $user->id
        ]);

        $memberIds = array_unique(array_merge($data['user_ids'], [$user->id]));
        $conversation->users()->attach($memberIds);

        $notification = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => null,
            'content' => ($user->profile->display_name ?? $user->name) . ' đã tạo nhóm.',
            'type' => 'notification'
        ]);

        foreach ($memberIds as $memberId) {
            if ($memberId !== $user->id) {
                $this->broadcastSystemNotification($notification, $conversation, $memberId);
            }
        }

        return $conversation->load('latestMessage');
    }

    public function updateGroup($id, $data, $user, $avatarFile = null)
    {
        $conversation = $this->getGroupConversation($id, $user->id);

        $oldName = $conversation->name;
        $oldAvatar = $conversation->avatar;
        $oldMemberIds = $conversation->users->pluck('id')->toArray();

        $updateData = ['name' => $data['name']];

        if ($avatarFile) {
            $updateData['avatar'] = $avatarFile->store('group_avatars', 'public');
        } elseif (!empty($data['dicebear_url'])) {
            if (str_contains($data['dicebear_url'], 'api.dicebear.com')) {
                try {
                    $response = Http::get($data['dicebear_url']);
                    if ($response->successful()) {
                        $name = 'group_' . uniqid() . '.svg';
                        Storage::disk('public')->put('group_avatars/' . $name, $response->body());
                        $updateData['avatar'] = 'group_avatars/' . $name;
                    }
                } catch (\Exception $e) {}
            }
        }

        $conversation->update($updateData);

        $memberIds = array_unique(array_merge($data['user_ids'], [$user->id]));
        $conversation->users()->sync($memberIds);

        $notifications = [];
        $authName = $user->profile->display_name ?? $user->name;

        if ($oldName !== $conversation->name) {
            $notifications[] = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => null,
                'content' => $authName . ' đã đổi tên nhóm thành "' . $conversation->name . '"',
                'type' => 'notification'
            ]);
        }

        if ($oldAvatar !== $conversation->avatar) {
            $notifications[] = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => null,
                'content' => $authName . ' đã thay đổi ảnh đại diện nhóm.',
                'type' => 'notification'
            ]);
        }

        $newMemberIds = array_diff($memberIds, $oldMemberIds);
        foreach ($newMemberIds as $newId) {
            $usr = User::find($newId);
            $notifications[] = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => null,
                'content' => ($usr->profile->display_name ?? $usr->name) . ' đã được thêm vào nhóm.',
                'type' => 'notification'
            ]);
        }

        $removedMemberIds = array_diff($oldMemberIds, $memberIds);
        foreach ($removedMemberIds as $remId) {
            $usr = User::find($remId);
            if ($usr) {
                $notifications[] = Message::create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => null,
                    'content' => ($usr->profile->display_name ?? $usr->name) . ' đã bị xóa khỏi nhóm.',
                    'type' => 'notification'
                ]);
            }
        }

        foreach ($notifications as $noti) {
            foreach ($memberIds as $mId) {
                if ($mId !== $user->id) {
                    $this->broadcastSystemNotification($noti, $conversation, $mId);
                }
            }
        }

        return $conversation->fresh();
    }

    public function leaveGroup($id, $user)
    {
        $conversation = $this->getGroupConversation($id, $user->id);

        $notification = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => null,
            'content' => ($user->profile->display_name ?? $user->name) . ' đã rời khỏi nhóm.',
            'type' => 'notification'
        ]);

        $newLeaderNoti = null;
        if ($conversation->creator_id == $user->id) {
            $nextLeader = $conversation->users->where('id', '!=', $user->id)->first();
            if ($nextLeader) {
                $conversation->update(['creator_id' => $nextLeader->id]);
                $newLeaderNoti = Message::create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => null,
                    'content' => ($nextLeader?->profile?->display_name ?? $nextLeader?->name) . ' đã được chỉ định làm trưởng nhóm mới.',
                    'type' => 'notification'
                ]);
            }
        }

        $conversation->users()->detach($user->id);
        
        $allNotis = array_filter([$notification, $newLeaderNoti]);
        foreach ($allNotis as $noti) {
            foreach ($conversation->users as $member) {
                if ($member->id !== $user->id) {
                    $this->broadcastSystemNotification($noti, $conversation, $member->id);
                }
            }
        }

        if ($conversation->users()->count() === 0) {
            $conversation->delete();
        }
    }

    public function clearChat($id, $userId)
    {
        $conversation = Conversation::whereHas('users', fn($q) => $q->where('user_id', $userId))
            ->findOrFail($id);

        $conversation->users()->updateExistingPivot($userId, [
            'deleted_at' => now()
        ]);
    }

    public function deleteConversationPermanently($id, $user)
    {
        $conversation = Conversation::findOrFail($id);

        if ($user->role !== 'admin' && !$conversation->users->contains('id', $user->id)) {
            abort(403, 'Bạn không có quyền');
        }

        $messages = Message::where('conversation_id', $id)->with('media')->get();
        foreach ($messages as $msg) {
            foreach ($msg->media as $mm) {
                if ($mm->file_path) {
                    Storage::disk('public')->delete($mm->file_path);
                }
            }
        }

        $conversation->delete();
        return Conversation::count();
    }

    public function getAdminConversations($perPage = 10)
    {
        return Conversation::with(['users.profile','messages'])
            ->withCount('messages')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function searchUsers($keyword, $userRole, $userId)
    {
        $query = User::query()
            ->join('profiles', 'users.id', '=', 'profiles.user_id')
            ->select('users.*', 'profiles.display_name as p_display_name')
            ->where('users.id', '!=', $userId)
            ->where(function($q) use ($keyword) {
                $q->where('users.name', 'like', "%$keyword%")
                  ->orWhere('profiles.display_name', 'like', "%$keyword%");
            });

        if ($userRole === 'user') {
            $query->where('users.role', 'user');
        } else {
            $query->whereIn('users.role', ['admin', 'moderator']);
        }

        return $query->with('profile')->take(5)->get();
    }

    private function broadcastSystemNotification($notification, $conversation, $receiverId)
    {
        $chatData = [
            'id'              => $notification->id,
            'content'         => $notification->content,
            'sender_id'       => null,
            'sender_name'     => 'Hệ thống',
            'sender_avatar'   => null,
            'is_group'        => true,
            'type'            => $notification->type,
            'conversation_id' => $conversation->id,
            'group_name'      => $conversation->name,
            'group_avatar'    => $conversation->avatar,
            'receiver_id'     => $receiverId,
            'created_at'      => $notification->created_at->format('H:i d/m'),
            'timestamp'       => $notification->created_at->timestamp,
            'media'           => [],
        ];
        broadcast(new \App\Events\MessageSent((object) $chatData))->toOthers();
    }
}

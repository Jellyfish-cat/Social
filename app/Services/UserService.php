<?php

namespace App\Services;

use App\Models\User;
use App\Models\Report;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessReportAction;

class UserService
{
    public function getUserCount()
    {
        return User::count();
    }

    public function getPaginatedUsers($perPage = 10)
    {
        return User::with(['profile']) 
            ->withCount([
                'posts',    
                'comments',
                'favorites',
                'followers',
                'following'  
            ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function createUser($data, $avatarFile = null)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'role' => $data['role'],
            'status' => 'show',
            'email_verified_at' => now(),
        ]);

        $avatarPath = null;
        if ($avatarFile) {
            $avatarPath = $avatarFile->store('avatars', 'public');
        }

        $user->profile()->create([
            'display_name' => $data['display_name'] ?? $data['name'],
            'bio' => $data['bio'] ?? null,
            'avatar' => $avatarPath,
        ]);

        return User::with('profile')->find($user->id);
    }

    public function toggleUserStatus($id, $type, $authId)
    {
        $user = User::findOrFail($id);
        
        $newStatus = ($type === 'hide') ? 'hidden' : 'show';
        $user->status = $newStatus;
        $user->save();

        $action = ($type === 'hide') ? 'hide' : 'restore';
        ProcessReportAction::dispatch($action, User::class, $user->id, null, $authId);

        return $newStatus;
    }

    public function deleteUserPermanently($id)
    {
        $user = User::with(['profile', 'posts.media', 'comments', 'conversations'])->findOrFail($id);

        if ($user->profile && $user->profile->avatar) {
            Storage::disk('public')->delete($user->profile->avatar);
        }

        foreach ($user->posts as $post) {
            foreach ($post->media as $m) {
                if ($m->file_path) {
                    Storage::disk('public')->delete($m->file_path);
                }
            }
        }

        foreach ($user->comments as $comment) {
            if ($comment->media_path) {
                Storage::disk('public')->delete($comment->media_path);
            }
        }

        Conversation::where('creator_id', $id)->update(['creator_id' => null]);
        $groupConvos = Conversation::where('type', 'group')
            ->where('creator_id', $id)
            ->with('users')
            ->get();

        foreach ($groupConvos as $convo) {
            $nextLeader = $convo->users->where('id', '!=', $id)->first();
            if ($nextLeader) {
                $convo->update(['creator_id' => $nextLeader->id]);
                Message::create([
                    'conversation_id' => $convo->id,
                    'sender_id' => null,
                    'content' => ($nextLeader->profile->display_name ?? $nextLeader->name) . ' đã được chỉ định làm trưởng nhóm mới do chủ nhóm cũ bị xóa.',
                    'type' => 'notification'
                ]);
            } else {
                $convo->update(['creator_id' => null]);
            }
        }

        $messages = Message::where('sender_id', $id)->with('media')->get();
        foreach ($messages as $msg) {
            foreach ($msg->media as $mm) {
                if ($mm->file_path) {
                    Storage::disk('public')->delete($mm->file_path);
                }
            }
        }

        foreach ($user->conversations as $convo) {
            if ($convo->type === 'private') {
                $convoMessages = Message::where('conversation_id', $convo->id)->with('media')->get();
                foreach ($convoMessages as $cm) {
                    foreach ($cm->media as $cmm) {
                        if ($cmm->file_path) {
                            Storage::disk('public')->delete($cmm->file_path);
                        }
                    }
                }
                $convo->delete(); 
            }
        }

        Report::where('target_id', $id)->where('target_type', User::class)->delete();
        $user->delete();

        return User::latest()->get();
    }
    public function getSuggestedUsers($user)
    {
        $suggestedUsers = collect();
        $aiUserSuccess = false;

        try {
            $aiResponse = Http::timeout(3)->get('http://127.0.0.1:8001/api/user_recommendations', [
                'user_id' => $user ? $user->id : 0
            ]);

            if ($aiResponse->successful() && isset($aiResponse->json()['recommended_user_ids'])) {
                $recommendedUserIds = $aiResponse->json()['recommended_user_ids'];
                
                if (!empty($recommendedUserIds)) {
                    $idStr = implode(',', $recommendedUserIds);
                    $suggestedUsers = User::whereIn('id', $recommendedUserIds)
                        ->where('status', 'show')
                        ->with('profile')
                        ->orderByRaw("FIELD(id, {$idStr})")
                        ->get();
                    
                    if ($suggestedUsers->isNotEmpty()) {
                        $aiUserSuccess = true;
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignore lỗi kết nối AI để chạy fallback bên dưới
        }

        if (!$aiUserSuccess) {
            $suggestedUsers = User::where('id', '!=', $user ? $user->id : 0)
                ->where('role', 'user')
                ->where('status', 'show')
                ->with('profile')
                ->limit(8)->get();
        }

        return $suggestedUsers;
    }
}

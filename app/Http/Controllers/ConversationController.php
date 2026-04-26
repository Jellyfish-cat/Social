<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class ConversationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = Auth::id();
        $conversations = Conversation::whereHas('users', function ($q) use ($userId) {
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

        $messages = collect();
        if ($conversations->isNotEmpty()) {
            $first = $conversations->first();
            $userPivot = $first->users->firstWhere('id', $userId)?->pivot;
            $deletedAt = $userPivot ? $userPivot->deleted_at : null;

            $query = Message::where('conversation_id', $first->id)
                ->with(['sender.profile', 'media'])
                ->orderBy('created_at');
            
            if ($deletedAt) {
                $query->where('created_at', '>', $deletedAt);
            }
            $messages = $query->get();
        }
    return view('Message.conversations', compact('conversations', 'messages'));
    }
   public function messageTab($id)
    {
    $authId = auth()->id();
    $conversation = Conversation::whereHas('users', function ($q) use ($authId) {
            $q->where('user_id', $authId);
        })
        ->whereHas('users', function ($q) use ($id) {
            $q->where('user_id', $id);
        })
        ->with(['users.profile'])
        ->first();
            if (!$conversation) {
        $otherUser = User::with('profile')->findOrFail($id);
        return view('Message.empty_message', compact('otherUser'));
    }
        Message::where('conversation_id', $conversation->id)
        ->where('sender_id', $id)
        ->whereNull('read_at')
        ->update([
            'read_at' => now()
        ]);


    $userPivot = $conversation->users->firstWhere('id', $authId)?->pivot;
    $deletedAt = $userPivot ? $userPivot->deleted_at : null;

    $query = Message::where('conversation_id', $conversation->id)
        ->with(['sender.profile', 'media'])
        ->orderBy('created_at');

    if ($deletedAt) {
        $query->where('created_at', '>', $deletedAt);
    }
    $messages = $query->get();
    // Trả về partial view (chỉ HTML tin nhắn) cho fetch JS
    return view('Message.message', compact('messages','conversation'));
    } 
    public function search_user(Request $request)
    {
        $keyword = $request->q;
        if (!$keyword) return [];

        $role = auth()->user()->role;

        $query = User::query()
            ->join('profiles', 'users.id', '=', 'profiles.user_id')
            ->select('users.*', 'profiles.display_name as p_display_name')
            ->where('users.id', '!=', auth()->id())
            ->where(function($q) use ($keyword) {
                $q->where('users.name', 'like', "%$keyword%")
                  ->orWhere('profiles.display_name', 'like', "%$keyword%");
            });

        if ($role === 'user') {
            $query->where('users.role', 'user');
        } else {
            $query->where('users.role', 'admin'); 
        }

        return $query->with('profile')
            ->take(5)
            ->get();
    }


    public function storeGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'user_ids' => 'required|array|min:2',
            'avatar' => 'nullable|image|max:2048',
            'dicebear_url' => 'nullable|string',
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('group_avatars', 'public');
        } elseif ($request->dicebear_url) {
            try {
                $response = Http::get($request->dicebear_url);
                if ($response->successful()) {
                    $name = 'group_' . uniqid() . '.svg';
                    Storage::disk('public')->put('group_avatars/' . $name, $response->body());
                    $avatarPath = 'group_avatars/' . $name;
                }
            } catch (\Exception $e) {
                // Fallback
            }
        }

        $conversation = Conversation::create([
            'type' => 'group',
            'name' => $request->name,
            'avatar' => $avatarPath,
            'status' => 'show',
            'createUser' => auth()->id()
        ]);

        // Đảm bảo user tạo nhóm cũng nằm trong danh sách thành viên
        $memberIds = array_unique(array_merge($request->user_ids, [auth()->id()]));
        $conversation->users()->attach($memberIds);

        $notification = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => null,
            'content' => (auth()->user()->profile->display_name ?? auth()->user()->name) . ' đã tạo nhóm.',
            'type' => 'notification'
        ]);

        // Broadcast notification to other members
        foreach ($memberIds as $memberId) {
            if ($memberId !== auth()->id()) {
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
                    'receiver_id'     => $memberId,
                    'created_at'      => $notification->created_at->format('H:i d/m'),
                    'timestamp'       => $notification->created_at->timestamp,
                    'media'           => [],
                ];
                broadcast(new \App\Events\MessageSent((object) $chatData))->toOthers();
            }
        }

        return response()->json([
            'success' => true,
            'conversation' => $conversation->load('latestMessage')
        ]);
    }

    public function groupTab($id)
    {
        $authId = auth()->id();
        
        $conversation = Conversation::where('type', 'group')
            ->whereHas('users', fn($q) => $q->where('user_id', $authId))
            ->with(['users.profile'])
            ->findOrFail($id);

        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $authId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $userPivot = $conversation->users->firstWhere('id', $authId)?->pivot;
        $deletedAt = $userPivot ? $userPivot->deleted_at : null;

        $query = Message::where('conversation_id', $conversation->id)
            ->with(['sender.profile', 'media'])
            ->orderBy('created_at');

        if ($deletedAt) {
            $query->where('created_at', '>', $deletedAt);
        }
        $messages = $query->get();

        return view('Message.message', compact('messages', 'conversation'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function adminIndex()
    {
        $conversations = Conversation::with(['users.profile','messages'])
        ->withCount('messages')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        return view('admin.conversations', compact('conversations'));
    }
    public function show($id)
    {
        $messages = Message::where('conversation_id', $id)->with(['media','sender']) // Lấy thông tin người đăng, chủ đề và danh sách ảnh/video
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        return view('admin.messages', compact('messages'));
    }

    public function createGroup()
    {
        return view('Message.partials.createGroup_modal');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $conversation = Conversation::where('type', 'group')
            ->whereHas('users', fn($q) => $q->where('user_id', auth()->id()))
            ->with('users.profile')
            ->findOrFail($id);
        return view('Message.partials.editGroup_modal', compact('conversation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Conversation $conversation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $conversation = Conversation::find($id);
        if (auth()->user()->role !== 'admin' && !$conversation->users->contains('id', auth()->id())) {
            abort(403, 'Bạn không có quyền');
        }
        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy conversation'
            ], 404);
        }

        // Tạo thông báo giải tán nhóm trước khi ẩn
        if ($conversation->type === 'group') {
            $notification = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => null,
                'content' => 'Trưởng nhóm đã giải tán nhóm trò chuyện này.',
                'type' => 'notification'
            ]);

            // Broadcast tới các thành viên
            foreach ($conversation->users as $member) {
                if ($member->id !== auth()->id()) {
                    $chatData = [
                        'id'              => $notification->id,
                        'content'         => $notification->content,
                        'sender_id'       => null,
                        'sender_name'     => 'Hệ thống',
                        'is_group'        => true,
                        'type'            => 'notification',
                        'conversation_id' => $conversation->id,
                        'group_name'      => $conversation->name,
                        'group_avatar'    => $conversation->avatar,
                        'receiver_id'     => $member->id,
                        'created_at'      => $notification->created_at->format('H:i d/m'),
                        'timestamp'       => $notification->created_at->timestamp,
                        'media'           => [],
                    ];
                    broadcast(new \App\Events\MessageSent((object) $chatData))->toOthers();
                }
            }
        }
        $conversation->update(['status' => 'hide']);
        return response()->json([
            'success' => true,
            'message' => 'Đã giải tán nhóm',
            'data' => Conversation::latest()->get(),
            'count' => Conversation::count()
        ]);
    }
    public function getMembers($id)
    {
        $conversation = Conversation::whereHas('users', function ($q) {
            $q->where('user_id', auth()->id());
        })->with('users.profile')->findOrFail($id);
        $creator=$conversation->createUser;
        return response()->json([
            'success' => true,
            'creator' => $creator,
            'members' => $conversation->users->sortByDesc(function ($user) use ($creator) {
                return $user->id === $creator;
            })->values()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->profile->display_name ?? $user->name,
                    'avatar' => asset('storage/' . ($user->profile->avatar ?? 'default-avatar.png')),
                ];
            })
        ]);
    }

    public function updateGroup(Request $request, $id)
    {
        $conversation = Conversation::where('type', 'group')
            ->whereHas('users', fn($q) => $q->where('user_id', auth()->id()))
            ->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'user_ids' => 'required|array|min:1',
            'avatar' => 'nullable|image|max:2048',
            'dicebear_url' => 'nullable|string',
        ]);

        $oldName = $conversation->name;
        $oldAvatar = $conversation->avatar;
        $oldMemberIds = $conversation->users->pluck('id')->toArray();

        $data = ['name' => $request->name];

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('group_avatars', 'public');
        } elseif ($request->dicebear_url) {
            try {
                $response = Http::get($request->dicebear_url);
                if ($response->successful()) {
                    $name = 'group_' . uniqid() . '.svg';
                    Storage::disk('public')->put('group_avatars/' . $name, $response->body());
                    $data['avatar'] = 'group_avatars/' . $name;
                }
            } catch (\Exception $e) {}
        }

        $conversation->update($data);

        $memberIds = array_unique(array_merge($request->user_ids, [auth()->id()]));
        $conversation->users()->sync($memberIds);

        // --- TẠO CÁC THÔNG BÁO HỆ THỐNG ---
        $notifications = [];
        $authName = auth()->user()->profile->display_name ?? auth()->user()->name;

        // 1. Thông báo đổi tên
        if ($oldName !== $conversation->name) {
            $notifications[] = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => null,
                'content' => $authName . ' đã đổi tên nhóm thành "' . $conversation->name . '"',
                'type' => 'notification'
            ]);
        }

        // 2. Thông báo đổi avatar
        if ($oldAvatar !== $conversation->avatar) {
            $notifications[] = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => null,
                'content' => $authName . ' đã thay đổi ảnh đại diện nhóm.',
                'type' => 'notification'
            ]);
        }

        // 3. Thông báo thêm thành viên mới
        $newMemberIds = array_diff($memberIds, $oldMemberIds);
        foreach ($newMemberIds as $newId) {
            $user = User::find($newId);
            $notifications[] = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => null,
                'content' => ($user->profile->display_name ?? $user->name) . ' đã được thêm vào nhóm.',
                'type' => 'notification'
            ]);
        }

        // 4. Thông báo xóa thành viên
        $removedMemberIds = array_diff($oldMemberIds, $memberIds);
        foreach ($removedMemberIds as $remId) {
            $user = User::find($remId);
            if ($user) {
                $notifications[] = Message::create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => null,
                    'content' => ($user->profile->display_name ?? $user->name) . ' đã bị xóa khỏi nhóm.',
                    'type' => 'notification'
                ]);
            }
        }

        // --- BROADCAST TOÀN BỘ THÔNG BÁO ---
        foreach ($notifications as $noti) {
            foreach ($memberIds as $mId) {
                if ($mId !== auth()->id()) {
                    $chatData = [
                        'id'              => $noti->id,
                        'content'         => $noti->content,
                        'sender_id'       => null,
                        'sender_name'     => 'Hệ thống',
                        'is_group'        => true,
                        'type'            => 'notification',
                        'conversation_id' => $conversation->id,
                        'group_name'      => $conversation->name,
                        'group_avatar'    => $conversation->avatar,
                        'receiver_id'     => $mId,
                        'created_at'      => $noti->created_at->format('H:i d/m'),
                        'timestamp'       => $noti->created_at->timestamp,
                        'media'           => [],
                    ];
                    broadcast(new \App\Events\MessageSent((object) $chatData))->toOthers();
                }
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thành công',
                'conversation' => $conversation->fresh()
            ]);
        }

        return redirect()->route('conversations.index')->with('success', 'Cập nhật nhóm thành công');
    }

    public function leaveGroup($id)
    {
        $conversation = Conversation::where('type', 'group')
            ->whereHas('users', fn($q) => $q->where('user_id', auth()->id()))
            ->findOrFail($id);

        $notification = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => null,
            'content' => (auth()->user()->profile->display_name ?? auth()->user()->name) . ' đã rời khỏi nhóm.',
            'type' => 'notification'
        ]);

        $newLeaderNoti = null;
        if ($conversation->createUser == auth()->id()) {
            $nextLeader = $conversation->users->where('id', '!=', auth()->id())->first();
            if ($nextLeader) {
                $conversation->update(['createUser' => $nextLeader->id]);
                $newLeaderNoti = Message::create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => null,
                    'content' => ($nextLeader->profile->display_name ?? $nextLeader->name) . ' đã được chỉ định làm trưởng nhóm mới.',
                    'type' => 'notification'
                ]);
            }
        }

        // Broadcast to remaining members
        $allNotis = array_filter([$notification, $newLeaderNoti]);
        foreach ($allNotis as $noti) {
            foreach ($conversation->users as $member) {
                if ($member->id !== auth()->id()) {
                    $chatData = [
                        'id'              => $noti->id,
                        'content'         => $noti->content,
                        'sender_id'       => null,
                        'sender_name'     => 'Hệ thống',
                        'is_group'        => true,
                        'type'            => 'notification',
                        'conversation_id' => $conversation->id,
                        'group_name'      => $conversation->name,
                        'group_avatar'    => $conversation->avatar,
                        'receiver_id'     => $member->id,
                        'created_at'      => $noti->created_at->format('H:i d/m'),
                        'timestamp'       => $noti->created_at->timestamp,
                        'media'           => [],
                    ];
                    broadcast(new \App\Events\MessageSent((object) $chatData))->toOthers();
                }
            }
        }

        $conversation->users()->detach(auth()->id());

        if ($conversation->users()->count() === 0) {
            $conversation->delete();
        }

        return redirect()->route('conversations.index')->with('success', 'Bạn đã rời khỏi nhóm');
    }

    public function clearChat($id)
    {
        $conversation = Conversation::whereHas('users', fn($q) => $q->where('user_id', auth()->id()))
            ->findOrFail($id);

        $conversation->users()->updateExistingPivot(auth()->id(), [
            'deleted_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa lịch sử trò chuyện'
        ]);
    }
}

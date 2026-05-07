<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\Conversation;
use App\Models\MessageMedia;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Chỉ hiển thị tin nhắn trong các hội thoại có mặt Staff
        $messages = Message::whereHas('conversation.users', function($q) {
                $q->whereIn('role', ['admin', 'moderator']);
            })->whereDoesntHave('conversation.users', function ($q) {
    $q->whereNotIn('role', ['admin', 'moderator']);
})
            ->with(['media','sender'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('admin.messages', compact('messages'));
    }
    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $id)
    {
        $request->validate([
            'content' => 'nullable|string|max:1000',
            'files'   => 'nullable|array|max:5',
            'files.*' => 'file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi|max:204800',
        ]);

        // Phải có content hoặc file
        if (!$request->content && !$request->hasFile('files')) {
            return response()->json(['success' => false, 'error' => 'Cần có nội dung hoặc file'], 422);
        }

        $authId = auth()->id();
        $user = User::findOrFail($id);
        $authUser = auth()->user();

        if ($authUser->role === 'user') {
            // User thường chỉ được nhắn cho User thường
            if ($user->role !== 'user') {
                return response()->json(['success' => false, 'error' => 'Bạn không thể nhắn tin trực tiếp cho Ban quản trị'], 403);
            }
        } else {
       
            if (!in_array($user->role, ['admin', 'moderator'])) {
                return response()->json(['success' => false, 'error' => 'Ban quản trị chỉ có thể nhắn tin cho nhân viên'], 403);
            }
        }

        // Kiểm tra trạng thái người nhận
        if ($user->status === 'hidden') {
            return response()->json(['success' => false, 'error' => 'Tài khoản này đã bị khóa do vi phạm'], 403);
        }

        // Tìm conversation giữa 2 user
        $conversation = Conversation::whereHas('users', function ($q) use ($authId) {
                $q->where('user_id', $authId);
            })
            ->whereHas('users', function ($q) use ($id) {
                $q->where('user_id', $id);
            })
            ->first();

        // Nếu chưa có thì tạo mới
        if (!$conversation) {
            $conversation = Conversation::create(['type' => 'private']);
            $conversation->users()->attach([$authId, $id]);
        }

        // Tạo message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $authId,
            'content'         => $request->content ?? '',
            'read_at' => null,
        ]);

        // Xử lý files nếu có
        $mediaList = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
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
                // Đóng gói payload gửi đi qua Websocket
        $chatData = [
            'id'              => $message->id,
            'content'         => $message->content,
            'sender_id'       => $message->sender_id,
            'sender_name'     => auth()->user()->profile->display_name ?? auth()->user()->name,
            'sender_avatar'   => auth()->user()->profile->avatar ?? null,
            'receiver_id'     => $id,
            'is_group'        => false,
            'conversation_id' => $message->conversation_id,
            'created_at'      => $message->created_at->format('H:i d/m'),
            'timestamp'       => $message->created_at->timestamp,
            'media'           => $mediaList,
            'type'            => $message->type,
        ];
        // Bắn event
        broadcast(new \App\Events\MessageSent((object) $chatData))->toOthers();
        return response()->json([
            'success' => true,
            'user'=>[
                'id' => $user->id,
                'name' => $user->name,
                'displayname' => $user->profile->display_name,
                'avatar' => $user->profile->avatar,
            ],
            'message' => [
                'id'         => $message->id,
                'content'    => $message->content,
                'sender_id'  => $message->sender_id,
                'conversation_id' => $message->conversation_id,
                'created_at' => $message->created_at->format('H:i d/m'),
                'media'      => $mediaList,
            ],
        ]);
    }

    public function storeGroupMsg(Request $request, $convoId)
    {
        $request->validate([
            'content' => 'nullable|string|max:1000',
            'files'   => 'nullable|array|max:5',
            'files.*' => 'file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi|max:204800',
        ]);

        if (!$request->content && !$request->hasFile('files')) {
            return response()->json(['success' => false, 'error' => 'Tin nhắn không được để trống'], 422);
        }

        $authId = auth()->id();
        $conversation = Conversation::where('type', 'group')
            ->whereHas('users', fn($q) => $q->where('user_id', $authId))
            ->with('users.profile')
            ->findOrFail($convoId);

        // Kiểm tra trạng thái nhóm
        if ($conversation->status === 'hidden') {
            return response()->json(['success' => false, 'error' => 'Nhóm này đã bị giải tán'], 403);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $authId,
            'content'         => $request->content ?? '',
            'read_at'         => null,
        ]);

        $mediaList = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('message_media', 'public');
                $type = str_contains($file->getMimeType(), 'video') ? 'video' : 'image';

                \App\Models\MessageMedia::create([
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

        $sender_name = auth()->user()->profile->display_name ?? auth()->user()->name;
        $sender_avatar = auth()->user()->profile->avatar ?? null;

        // Broadcast to each group member
        foreach ($conversation->users as $member) {
            if ($member->id !== $authId) {
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

        return response()->json([
            'success' => true,
            'message' => [
                'id'         => $message->id,
                'content'    => $message->content,
                'sender_id'  => $message->sender_id,
                'created_at' => $message->created_at->format('H:i d/m'),
                'media'      => $mediaList,
            ],
            'conversation' => $conversation
        ]);
    }
    public function is_Read($id)
    {
        $authId = auth()->id();
        // Hỗ trợ cả ID user (private) và ID conversation (group)
        $conversation = Conversation::where(function($q) use ($authId, $id) {
            $q->where('id', $id) // Nếu truyền vào convo ID
              ->orWhere(function($sub) use ($authId, $id) { // Hoặc truyền vào user ID cho 1-1
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

        return response()->json(['success' => true]);
    }
    public function search(Request $request)
    {
        $query = $request->input('q');
        $conversationId = $request->input('conversation_id');

        if (!$query || !$conversationId) {
            return response()->json([]);
        }

        activity()
            ->event('search_message')
            ->causedBy(auth()->user())
            ->withProperties(['keyword' => $query, 'conversation_id' => $conversationId])
            ->log("riêng tư");

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

        return response()->json($messages);
    }

    public function unsend($id)
    {
        $message = Message::with('conversation.users')->findOrFail($id);

        if (auth()->user()->role !== 'admin' && auth()->id() !== $message->sender_id) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền'], 403);
        }

        if ($message->status === 'unsend') {
            return response()->json(['success' => true, 'message' => 'Tin nhắn đã được thu hồi trước đó']);
        }

        // Chuyển status sang unsend instead of delete
        $message->update(['status' => 'unsend']);

        // Ghi log hoạt động
        activity()
            ->performedOn($message)
            ->event('unsend_message')
            ->causedBy(auth()->user())
            ->log("đã thu hồi tin nhắn");

        $receivers = $message->conversation->users->where('id', '!=', auth()->id());
        
        foreach ($receivers as $receiver) {
            broadcast(new \App\Events\MessageDeleted($message->id, $receiver->id))->toOthers();
        }

        return response()->json([
            'success' => true,
            'message' => 'Thu hồi tin nhắn thành công'
        ]);
    }
    public function destroy($id)
    {
        // Chỉ Admin hoặc Moderator mới có quyền xóa tin nhắn vi phạm
        if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'moderator'])) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền thực hiện hành động này'], 403);
        }

        $message = Message::with('media')->findOrFail($id);
        
        // 1. Xóa toàn bộ file Media vật lý
        foreach ($message->media as $mm) {
            if ($mm->file_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($mm->file_path);
            }
        }

        // 2. Ghi log hoạt động (dành cho Admin/Moderator)
        activity()
            ->performedOn($message)
            ->event('delete_message_admin')
            ->causedBy(auth()->user())
            ->log("đã xóa vĩnh viễn tin nhắn do vi phạm");

        // 3. Xóa vĩnh viễn bản ghi (Cascade sẽ xóa message_media rows)
        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa vĩnh viễn tin nhắn do vi phạm'
        ]);
    }
}

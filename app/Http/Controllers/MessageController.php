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
         $messages = Message::with(['media','sender']) // Lấy thông tin người đăng, chủ đề và danh sách ảnh/video
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
            'files'   => 'nullable|array|max:10',
            'files.*' => 'file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi|max:204800',
        ]);

        // Phải có content hoặc file
        if (!$request->content && !$request->hasFile('files')) {
            return response()->json(['success' => false, 'error' => 'Cần có nội dung hoặc file'], 422);
        }

        $authId = auth()->id();
        $user = User::findorfail($id);

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
            'id'          => $message->id,
            'content'     => $message->content,
            'sender_id'   => $message->sender_id,
            'receiver_id' => $id, // ID người nhận (chính là $id từ tham số hàm)
            'created_at'  => $message->created_at->format('H:i d/m'),
            'media'       => $mediaList,
            'sender_avatar' => auth()->user()->profile->avatar ?? null,
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
                'created_at' => $message->created_at->format('H:i d/m'),
                'media'      => $mediaList,
            ],
        ]);
    }   
    public function is_Read($id)
    {
        $authId = auth()->id();
        $conversation = Conversation::whereHas('users', function ($q) use ($authId) {
                $q->where('user_id', $authId);
            })
            ->whereHas('users', function ($q) use ($id) {
                $q->where('user_id', $id);
            })
            ->first();

        if ($conversation) {
            Message::where('conversation_id', $conversation->id)
                ->where('sender_id', $id)
                ->whereNull('read_at')
                ->update([
                    'read_at' => now()
                ]);
        }

        return response()->json(['success' => true]);
    }
    /**
     * Display the specified resource.
     */
    public function show(Message $message)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Message $message)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Message $message)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $message = Message::with('conversation.users')->findOrFail($id);

        if (auth()->user()->role !== 'admin' && auth()->id() !== $message->sender_id) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền'], 403);
        }

        // Chuyển status sang hide instead of delete
        $message->update(['status' => 'hide']);

        $receiver = $message->conversation->users->where('id', '!=', $message->sender_id)->first();
        
        if ($receiver) {
            broadcast(new \App\Events\MessageDeleted($message->id, $receiver->id))->toOthers();
        }

        return response()->json([
            'success' => true,
            'message' => 'Thu hồi tin nhắn thành công'
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = Auth::id();
        $conversations = Conversation::whereHas('users', function ($q) use ($userId) {
                $q->where('user_id', $userId)->where('status', 'show');
            })
            ->with([
                'users.profile', // lấy thông tin user
                'latestMessage.sender' // tin nhắn cuối
            ])
            ->withCount([
                'messages as unread_count' => function ($q) use ($userId) {
                    $q->where('sender_id', '!=', $userId) // Đếm tin của đối phương gửi
                      ->whereNull('read_at');
                }
            ])
            ->get()
            ->sortByDesc(function ($conversation) {
                return optional($conversation->latestMessage)->created_at;
            })
            ->values();
        $messages = collect();
        if ($conversations->isNotEmpty()) {
        $first = $conversations->first();
        $messages = Message::where('conversation_id', $first->id)
            ->with(['sender.profile', 'media'])->where('status', 'show')
            ->orderBy('created_at')
                ->get();
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
        ->first();  // ← thêm lại ->first()
            if (!$conversation) {
        $otherUser = User::with('profile')->findOrFail($id);
        return view('Message.empty_message', compact('otherUser'));
    }
        Message::where('conversation_id', $conversation->id)
        ->where('sender_id', $id)->where('status', 'show')
        ->whereNull('read_at')
        ->update([
            'read_at' => now()
        ]);


    $messages = Message::where('conversation_id', $conversation->id)
        ->with(['sender.profile', 'media'])->where('status', 'show')
        ->orderBy('created_at')
        ->get();
    // Trả về partial view (chỉ HTML tin nhắn) cho fetch JS
    return view('Message.message', compact('messages','conversation'));
} 
    /**
     * Show the form for creating a new resource.
     */
    public function search_user(Request $request)
    {
        $keyword  = $request->q;
        return User::where(function ($query) use ($keyword) {
        $query->where('name', 'like', "%$keyword%")
              ->orWhereHas('profile', function ($q) use ($keyword) {
                  $q->where('display_name', 'like', "%$keyword%");
              });
        })
        ->with('profile') 
        ->limit(5)
        ->get();
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Conversation $conversation)
    {
        //
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

        $conversation->delete();
        $conversationlist = Conversation::latest()->get();
        return response()->json([
            'success' => true,
            'data' => $conversationlist,
            'count' => Conversation::count(),
            'message' => 'Xóa thành công'
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ConversationService;
use App\Services\MessageService;

class ConversationController extends Controller
{
    protected $conversationService;
    protected $messageService;

    public function __construct(ConversationService $conversationService, MessageService $messageService)
    {
        $this->conversationService = $conversationService;
        $this->messageService = $messageService;
    }

    public function index()
    {
        $userId = Auth::id();
        $conversations = $this->conversationService->getUserConversations($userId);
        
        $messages = collect();
        if ($conversations->isNotEmpty()) {
            $first = $conversations->first();
            $messages = $this->messageService->getConversationMessages($first, $userId);
        }
        return view('Message.conversations', compact('conversations', 'messages'));
    }

    public function messageTab($id)
    {
        $authId = auth()->id();
        $conversation = $this->conversationService->getPrivateConversation($authId, $id);
        
        if (!$conversation) {
            $otherUser = User::with('profile')->findOrFail($id);
            return view('Message.empty_message', compact('otherUser'));
        }

        $messages = $this->messageService->getConversationMessages($conversation, $authId);
        return view('Message.message', compact('messages','conversation'));
    } 

    public function search_user(Request $request)
    {
        $keyword = $request->q;
        if (!$keyword) return [];
        
        activity()
            ->event('search_user_convo')
            ->causedBy(auth()->user())
            ->withProperties(['keyword' => $keyword])
            ->log("riêng tư");

        return $this->conversationService->searchUsers($keyword, auth()->user()->role, auth()->id());
    }

    public function storeGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'user_ids' => 'required|array|min:2|max:98',
            'avatar' => 'nullable|image|max:2048',
            'dicebear_url' => 'nullable|string',
        ]);

        $conversation = $this->conversationService->createGroup($request->all(), auth()->user(), $request->file('avatar'));

        return response()->json([
            'success' => true,
            'conversation' => $conversation
        ]);
    }

    public function groupTab($id)
    {
        $authId = auth()->id();
        $conversation = $this->conversationService->getGroupConversation($id, $authId);
        $messages = $this->messageService->getConversationMessages($conversation, $authId);

        return view('Message.message', compact('messages', 'conversation'));
    }

    public function store(Request $request)
    {
        //
    }

    public function adminIndex()
    {
        $conversations = $this->conversationService->getAdminConversations(10);
        return view('admin.conversations', compact('conversations'));
    }

    public function show($id)
    {
        $conversation = $this->conversationService->getConversationById($id);
        $messages = $this->messageService->getAdminMessages($id, 10);
        return view('admin.messages', compact('messages', 'conversation'));
    }

    public function createGroup()
    {
        return view('Message.partials.createGroup_modal');
    }

    public function edit($id)
    {
        $conversation = $this->conversationService->getGroupConversation($id, auth()->id());
        return view('Message.partials.editGroup_modal', compact('conversation'));
    }

    public function update(Request $request, Conversation $conversation)
    {
        //
    }

    public function destroy($id)
    {
        $conversation = $this->conversationService->getConversationById($id);

        activity()
            ->performedOn($conversation)
            ->event('delete_conversation')
            ->causedBy(auth()->user())
            ->log("đã xóa vĩnh viễn hội thoại: " . ($conversation->name ?? "ID: $id"));

        $count = $this->conversationService->deleteConversationPermanently($id, auth()->user());

        return response()->json([
            'success' => true,
            'message' => 'Hội thoại đã được xóa vĩnh viễn',
            'count' => $count
        ]);
    }

    public function getMembers($id)
    {
        $conversation = $this->conversationService->getConversationWithMembers($id, auth()->id());
        $creator = $conversation->creator_id;
        
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
        $request->validate([
            'name' => 'required|string|max:255',
            'user_ids' => 'required|array|min:1|max:98',
            'avatar' => 'nullable|image|max:2048',
            'dicebear_url' => 'nullable|string',
        ]);

        $conversation = $this->conversationService->updateGroup($id, $request->all(), auth()->user(), $request->file('avatar'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thành công',
                'conversation' => $conversation
            ]);
        }

        return redirect()->route('conversations.index')->with('success', 'Cập nhật nhóm thành công');
    }

    public function leaveGroup($id)
    {
        $this->conversationService->leaveGroup($id, auth()->user());
        return redirect()->route('conversations.index')->with('success', 'Bạn đã rời khỏi nhóm');
    }

    public function clearChat($id)
    {
        $this->conversationService->clearChat($id, auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa lịch sử trò chuyện'
        ]);
    }
}

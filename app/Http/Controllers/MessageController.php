<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Services\MessageService;

class MessageController extends Controller
{
    protected $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    public function index()
    {
        $messages = $this->messageService->getAllMessagesForAdmin(10);
        return view('admin.messages', compact('messages'));
    }
    
    public function create()
    {
        //
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'content' => 'nullable|string|max:1000',
            'files'   => 'nullable|array|max:5',
            'files.*' => 'file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi|max:204800',
        ]);

        if (!$request->content && !$request->hasFile('files')) {
            return response()->json(['success' => false, 'error' => 'Cần có nội dung hoặc file'], 422);
        }

        try {
            $result = $this->messageService->sendPrivateMessage($request->all(), $id, auth()->user(), $request->file('files'));
            
            return response()->json([
                'success' => true,
                'user'=>[
                    'id' => $result['user']->id,
                    'name' => $result['user']->name,
                    'displayname' => $result['user']->profile->display_name,
                    'avatar' => $result['user']->profile->avatar,
                ],
                'message' => [
                    'id'         => $result['message']->id,
                    'content'    => $result['message']->content,
                    'sender_id'  => $result['message']->sender_id,
                    'conversation_id' => $result['message']->conversation_id,
                    'created_at' => $result['message']->created_at->format('H:i d/m'),
                    'media'      => $result['mediaList'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 403);
        }
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

        try {
            $result = $this->messageService->sendGroupMessage($request->all(), $convoId, auth()->user(), $request->file('files'));

            return response()->json([
                'success' => true,
                'message' => [
                    'id'         => $result['message']->id,
                    'content'    => $result['message']->content,
                    'sender_id'  => $result['message']->sender_id,
                    'created_at' => $result['message']->created_at->format('H:i d/m'),
                    'media'      => $result['mediaList'],
                ],
                'conversation' => $result['conversation']
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 403);
        }
    }

    public function is_Read($id)
    {
        $this->messageService->markMessagesAsRead($id, auth()->id());
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

        $messages = $this->messageService->searchMessages($query, $conversationId);
        return response()->json($messages);
    }

    public function unsend($id)
    {
        try {
            $this->messageService->unsendMessage($id, auth()->user());
            
            activity()
                ->event('unsend_message')
                ->causedBy(auth()->user())
                ->log("đã thu hồi tin nhắn");

            return response()->json([
                'success' => true,
                'message' => 'Thu hồi tin nhắn thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 403);
        }
    }

    public function destroy($id)
    {
        try {
            $this->messageService->deleteMessagePermanently($id, auth()->user());

            activity()
                ->event('delete_message_admin')
                ->causedBy(auth()->user())
                ->log("đã xóa vĩnh viễn tin nhắn do vi phạm");

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa vĩnh viễn tin nhắn do vi phạm'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 403);
        }
    }
}

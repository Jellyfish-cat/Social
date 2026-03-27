<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
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
                $q->where('user_id', $userId);
            })
            ->with([
                'users.profile', // lấy thông tin user
                'latestMessage.sender' // tin nhắn cuối
            ])
            ->withCount('messages')
            ->get()
            ->sortByDesc(function ($conversation) {
                return optional($conversation->latestMessage)->created_at;
            })
            ->values();
        $messages = collect();
        if ($conversations->isNotEmpty()) {
        $first = $conversations->first();
        $messages = Message::where('conversation_id', $first->id)
            ->with(['sender.profile', 'media'])
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
        $conversation = Conversation::create(['type' => 'private']);
        $conversation->users()->attach([$authId, $id]);
    }

    $messages = Message::where('conversation_id', $conversation->id)
        ->with(['sender.profile', 'media'])
        ->orderBy('created_at')
        ->get();

    // Trả về partial view (chỉ HTML tin nhắn) cho fetch JS
    return view('Message.message', compact('messages'));
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Conversation $conversation)
    {
        //
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
    public function destroy(Conversation $conversation)
    {
        //
    }
}

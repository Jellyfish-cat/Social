<?php

namespace App\Http\Controllers;

use App\Models\LikePost;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class LikePostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store($id)
    {
        try {
        $user = Auth::user();
        $liked = LikePost::where('user_id', $user->id)
                        ->where('post_id', $id)
                        ->exists();
        if(!$liked){
            LikePost::create([
                'user_id' => $user->id,
                'post_id' => $id
            ]);

            $post = Post::find($id);
            if ($post && $post->user_id !== $user->id) {
                $notification = Notification::create([
                    'user_id' => $post->user_id,
                    'content' => '<strong>' . ($user->profile->display_name ?? $user->name ?? 'Một người') . '</strong> đã thích bài viết của bạn. post:' . $post->id,
                    'type' => 'like'
                ]);
                broadcast(new \App\Events\NotificationSent($notification))->toOthers();
            }
        }
        else{   
            $like=likepost::where('user_id', $user->id)
                        ->where('post_id', $id);
            $like->delete();
        }
        $likePost_count = LikePost::where('post_id', $id)->count();
        return response()->json([
            'success' => true,
            'likePost_count' => $likePost_count
        ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ],500);
    }

    }

    /**
     * Display the specified resource.
     */
    public function show(LikePost $likePost)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LikePost $likePost)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LikePost $likePost)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LikePost $likePost)
    {
        //
    }
}

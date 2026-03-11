<?php

namespace App\Http\Controllers;

use App\Models\LikePost;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

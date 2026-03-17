<?php

namespace App\Http\Controllers;

use App\Models\LikeComment;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeCommentController extends Controller
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
        $liked = LikeComment::where('user_id', $user->id)
                        ->where('comment_id', $id)
                        ->exists();
        if(!$liked){
            LikeComment::create([
                'user_id' => $user->id,
                'comment_id' => $id
            ]);
        }
        else{   
            LikeComment::where('user_id',$user->id)
            ->where('comment_id',$id)
            ->delete();
        }
        $likeComment_count = LikeComment::where('comment_id', $id)->count();
        return response()->json([
            'success' => true,
            'likeComment_count' => $likeComment_count
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
    public function show(LikeComment $likeComment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LikeComment $likeComment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LikeComment $likeComment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LikeComment $likeComment)
    {
        //
    }
}

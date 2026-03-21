<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class CommentController extends Controller
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
        public function store(Request $request, $post_id)
        {
            DB::beginTransaction();
            try {  
                    $request->validate(['content' => 'required|string|max:1000']);
    
                    $post = Post::findOrFail($post_id);
                    $user = Auth::user();

                    $comment = Comment::create([
                        'user_id' => $user ? $user->id : 1,
                        'post_id' => $post->id,
                        'content' => $request->content,
                        'parent_comment_id'=> $request -> parent_id,
                        'status' => 1
                    ]);
                    if ($request->hasFile('file')) {
                        $file = $request->file('file');
                        $fileName = time().'_'.$file->getClientOriginalName();
                        $path = $file->storeAs('comments/media', $fileName, 'public');
                        $comment->media_path = $path;
                        $comment->save();
                    }
                    DB::commit();
                    return response()->json([
                    'success' => true,
                    'content' => $comment->content,
                    'avatar' => $user->profile->avatar,
                    'comment_id' => $comment->id,   
                    'parent_comment_id' => $comment->parent_comment_id,
                    'user_is_owner' => $user->id,
                    'user_name' => $user->profile->display_name,
                    'comment_count' => $post->comments->count(),
                    'like_count' => $comment->likes->count(),
                    'media_path'=> $comment->media_path,
                    'is_image' => $comment->isImage(),
                    'is_video' => $comment->isVideo(),
                    'created_at' => $comment->created_at->diffForHumans()
                    ]);

            } catch (\Exception $e) {
                DB::rollback();
                return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            }
        }
    /**
     * Display the specified resource.
     */
    public function latest(Post $post)
{
    $comments = Comment::where('post_id',$post->id)
        ->latest()
        ->take(5)
        ->get();

    return response()->json($comments);
}
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        //
    }
    public function like($id)
    {
    Comment::firstOrCreate([
        'user_id' => auth()->id(),
        'comment_id' => $id
    ]);

    return back();
    }
    public function reply(Request $request, $id)
    {   
    $parentComment = Comment::findOrFail($id);
    Comment::create([
        'user_id' => auth()->id(),
        'post_id' => $parentComment->post_id,
        'parent_id' => $id,
        'content' => $request->content
    ]);
    return back();
}
}

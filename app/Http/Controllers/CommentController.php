<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Events\NotificationSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\Report;
use App\Services\ContentModerationService;
use App\Services\CommentService;
use Illuminate\Support\Facades\Storage;

class CommentController extends Controller
{
    protected $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    public function index()
    {
        $comments = $this->commentService->getAdminComments(10);
        return view('admin.comments', compact('comments'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request, $post_id)
    {
        DB::beginTransaction();
        try {  
            $request->validate(['content' => 'required|string|max:1000']);
            $user = Auth::user();

            if ($request->hasFile('file')) {
                $request->validate([
                    'file' => 'file|mimes:jpg,jpeg,png,gif,webp,mp4,webm,mov|max:20480',
                ]);
            }

            $result = $this->commentService->createComment($request->all(), $post_id, $user, $request->file('file'));
            $comment = $result['comment'];
            $post = $result['post'];

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
                'role' => auth()->id() === $comment->user_id || auth()->user()->role === 'admin',
                'created_at' => $comment->created_at->diffForHumans()
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function latest(Post $post)
    {
        $comments = $this->commentService->getLatestComments($post->id, 5);
        return response()->json($comments);
    }

    public function show(Comment $comment)
    {
        //
    }

    public function edit(Comment $comment)
    {
        //
    }

    public function update(Request $request, Comment $comment)
    {
        //
    }

    public function destroy($id)
    {
        $result = $this->commentService->deleteComment($id, auth()->user());
        
        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']], $result['code'] ?? 400);
        }

        return response()->json($result);
    }
   
    public function like_list(Request $request, $id)
    {
        if (!$request->ajax()) {
            return redirect()->back();
        }
        $layout = 'layouts.empty';
        $item = $this->commentService->getCommentWithLikes($id);
        $values = $item->likedUsers; 
        return view('like.like-list', compact('values', 'item', 'layout'));
    } 
}

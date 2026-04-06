<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         $comments = Comment::with(['user.profile', 'post', 'likes', 'parent','replies']) // Lấy thông tin người đăng, chủ đề và danh sách ảnh/video
                ->withCount([
                    'replies',    // Tạo ra biến comments_count
                    'likes'    // Tạo ra biến likes_count
                ])
                ->orderBy('created_at', 'desc')->where('status', 'show')
                ->paginate(10);
        return view('admin.comments', compact('comments'));
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

                    if ($post->user_id !== ($user->id ?? 1)) {
                        if($comment -> parent_comment_id === null){
                        $notification = Notification::create([
                            'user_id' => $post->user_id,
                            'content' => '<strong>' . ($user->profile->display_name ?? $user->name ?? 'Một người') . '</strong> đã bình luận bài viết của bạn. comment:' . $comment->id,
                            'type' => 'comment'
                        ]);
                        broadcast(new \App\Events\NotificationSent($notification))->toOthers();
                       } else{
                        $Parent_comment = Comment::findOrFail($comment->parent_comment_id);
                        if($post->user_id !== $Parent_comment->user_id){
                        $notification1 = Notification::create([
                            'user_id' => $post->user_id,
                            'content' => '<strong>' . ($user->profile->display_name ?? $user->name ?? 'Một người') . '</strong> đã bình luận bài viết của bạn. comment:' . $comment->id,
                            'type' => 'comment'
                        ]);
                        broadcast(new \App\Events\NotificationSent($notification1))->toOthers();
                            
                        $notification2 = Notification::create([
                            'user_id' => $Parent_comment->user_id,
                            'content' => '<strong>' . ($user->profile->display_name ?? $user->name ?? 'Một người') . '</strong> đã phản hồi bình luận của bạn. comment:' . $comment->id,
                            'type' => 'comment'
                        ]);
                        broadcast(new \App\Events\NotificationSent($notification2))->toOthers();
                        }
                        else{
                            $notification3 = Notification::create([
                            'user_id' => $post->user_id,
                            'content' => '<strong>' . ($user->profile->display_name ?? $user->name ?? 'Một người') . '</strong> đã phản hồi bình luận trong bài viết của bạn. comment:' . $comment->id,
                            'type' => 'comment'
                        ]);
                        broadcast(new \App\Events\NotificationSent($notification3))->toOthers();
                        }
                        }
                    }

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
                    'role' => auth()->id() === $comment->user_id || auth()->user()->role === 'admin',
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
        ->latest()->where('status', 'show')
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
    public function destroy(Request $request, $id)
    {
        $comment = Comment::find($id);
        if (auth()->user()->role !== 'admin' && auth()->id() !== $comment->user_id) {
            abort(403, 'Bạn không có quyền');
        }
        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy comment'
            ], 404);
        }

        $comment->delete();
        $commentlist = Comment::latest()->get();
        return response()->json([
            'success' => true,
            'data' => $commentlist,
            'count' => Comment::count(),
            'message' => 'Xóa thành công'
        ]);
    }
    public function like($id)
    {
    Comment::firstOrCreate([
        'user_id' => auth()->id(),
        'comment_id' => $id
    ]);

    return back();
    }
    public function like_list(Request $request, $id)
    {
        $layout = $request->ajax() ? 'layouts.app_detail' : 'layouts.app';
        $item = Comment::with(['likedUsers.profile'])->findOrFail($id);
        $values = $item->likedUsers; 
        return view('like.like-list', compact('values', 'item', 'layout'));
    } 
    
}

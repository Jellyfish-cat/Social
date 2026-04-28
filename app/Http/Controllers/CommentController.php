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
        public function store(Request $request, $post_id, ContentModerationService $moderator)
        {
            DB::beginTransaction();
            try {  
                    $request->validate(['content' => 'required|string|max:1000']);
                    $post = Post::findOrFail($post_id);
                    $user = Auth::user();

                    $targetId = $request->parent_id;// ID comment gốc được reply
                    $targetComment = $targetId ? Comment::find($targetId) : null;
                    $rootParentId = ($targetComment && $targetComment->parent_comment_id) 
                        ? $targetComment->parent_comment_id 
                        : $targetId; //kiểm tra là comment gốc hay comment được reply 

                    $comment = Comment::create([
                        'user_id' => $user ? $user->id : 1,
                        'post_id' => $post->id,
                        'content' => $request->content,
                        'parent_comment_id'=> $rootParentId,
                        'status' => 'show'
                    ]);

                    // Kiểm duyệt nội dung
                    $moderation = $moderator->analyze($request->content);
                    if ($moderation->is_toxic) {
                        $comment->status = 'hide';
                        $comment->save();

                        // Tạo báo cáo đã xử lý
                        Report::create([
                            'user_id' => Auth::id() ?? 1,
                            'target_id' => $comment->id,
                            'target_type' => Comment::class,
                            'category' => 'Automated',
                            'reason' => 'Hệ thống tự động ẩn: ' . $moderation->reason,
                            'status' => 'resolved',
                            'resolved_by' => Auth::id() ?? 1,
                            'resolved_at' => now(),
                        ]);
                        
                        // Nếu bị ẩn thì không gửi thông báo cho chủ bài viết
                        DB::commit();
                        return response()->json([
                            'success' => true,
                            'message' => 'Bình luận đã được gửi (đang chờ kiểm duyệt hoặc bị ẩn)',
                            'status' => 'hide'
                        ]);
                    }

                    $userId = auth()->id();
                    $userName = '<strong>' . ($user->profile->display_name ?? $user->name ?? 'Một người') . '</strong>';

                    if (!$comment->parent_comment_id) {
                        if ($userId !== $post->user_id) {
                            $notif = Notification::create([
                                'user_id' => $post->user_id,
                                'content' => "{$userName} đã bình luận bài viết của bạn. comment:{$comment->id}",
                                'type' => 'comment'
                            ]);
                            broadcast(new NotificationSent($notif))->toOthers();
                        }
                    } else {
                        if ($userId !== $post->user_id) {
                            $msg = ($targetComment && $targetComment->user_id === $post->user_id) 
                                ? "{$userName} đã phản hồi bình luận trong bài viết của bạn." 
                                : "{$userName} đã bình luận bài viết của bạn.";
                            
                            $notif = Notification::create([
                                'user_id' => $post->user_id,
                                'content' => "{$msg} comment:{$comment->id}",
                                'type' => 'comment'
                            ]);
                            broadcast(new NotificationSent($notif))->toOthers();
                        }
                        if ($targetComment && $targetComment->user_id !== $post->user_id && $userId !== $targetComment->user_id) {
                            $notif = Notification::create([
                                'user_id' => $targetComment->user_id,
                                'content' => "{$userName} đã phản hồi bình luận của bạn. comment:{$comment->id}",
                                'type' => 'comment'
                            ]);
                            broadcast(new NotificationSent($notif))->toOthers();
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
    public function destroy($id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy comment'], 404);
        }

        // Kiểm tra quyền: Chủ sở hữu HOẶC Admin HOẶC Moderator
        if (auth()->id() !== $comment->user_id && auth()->user()->role !== 'admin' && auth()->user()->role !== 'moderator') {
            abort(403, 'Bạn không có quyền xóa bình luận này');
        }
        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy comment'
            ], 404);
        }

        \App\Models\Report::where('target_id', $id)->where('target_type', Comment::class)->delete();
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
        if (!$request->ajax()) {
            return redirect()->back();
        }
        $layout = 'layouts.empty';
        $item = Comment::with(['likedUsers.profile'])->findOrFail($id);
        $values = $item->likedUsers; 
        return view('like.like-list', compact('values', 'item', 'layout'));
    } 
    
}

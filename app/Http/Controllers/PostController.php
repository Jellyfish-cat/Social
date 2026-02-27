<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Topic;
use App\Models\Media;
use App\Models\Comment;
use App\Models\Favorite;
use App\Models\VideoView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    // 1. Hiển thị danh sách bài viết (thay cho news.index)
    public function index()
    {
    $posts = Post::with(['user.profile', 'topic', 'media']) // Lấy thông tin người đăng, chủ đề và danh sách ảnh/video
                ->withCount([
                    'comments',    // Tạo ra biến comments_count
                    'likes',       // Tạo ra biến likes_count
                    'favorites'    // Tạo ra biến favorites_count
                ])
                ->orderBy('created_at', 'desc')
                ->get();

    return view('posts.index', compact('posts'));
}
    // 2. Giao diện tạo bài viết
    public function create()
    {
        $topics = Topic::all(); 
        $post = Post::all();
        return view('posts.create', compact('topics', 'post'));
    }

    // 3. Lưu bài viết mới
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $post = new Post();
            $post->user_id = Auth::id();
            $post->topic_id = $request->topic_id;
            $post->content = $request->content;
            $post->is_comment_enabled = $request->is_comment_enabled ?? 1;
            $post->pinned = 0;
            // Giả sử status mặc định là 0 (chờ duyệt) nếu bạn có thêm cột này, 
            // hoặc dùng logic riêng của bạn.
            $post->save();

            // Xử lý Media (Ảnh/Video) - Tương ứng bảng media
            if ($request->hasFile('file')) {
                foreach ($request->file('file') as $file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('posts/media', $fileName, 'public');

                    $media = new Media();
                    $media->post_id = $post->id;
                    $media->file_path = $path;
                    $media->type = str_contains($file->getMimeType(), 'video') ? 'video' : 'image';
                    $media->save();
                }
            }

            DB::commit();
            
            if (Auth::user()->role == 'admin') { // Điều chỉnh theo logic role của bạn
                return redirect()->route('posts.index')->with('success', 'Đăng bài thành công!');
            }
            return view('2_back');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // 4. Xem chi tiết và tăng lượt xem
    public function show($id)
    {
        $post = Post::with(['user.profile', 'media', 'topic', 'comments.user.profile'])->findOrFail($id);
        
        // Logic tăng lượt xem video (Dựa trên bảng video_views)
        if ($post->media->where('type', 'video')->count() > 0) {
            $mediaId = $post->media->where('type', 'video')->first()->id;
            
            // Ghi nhận lượt xem (Tránh spam bằng session)
            $sessionKey = 'video_viewed_' . $mediaId;
            if (!session()->has($sessionKey)) {
                VideoView::create([
                    'user_id' => Auth::id(),
                    'media_id' => $mediaId
                ]);
                session()->put($sessionKey, now()->addMinutes(30));
            }
        }

        return view('posts.detail', compact('post'));
    }

    // 5. Giao diện chỉnh sửa
    public function edit($id)
    {
        $topics = Topic::all();
        $post = Post::with('media')->findOrFail($id);
        return view('posts.edit', compact('topics', 'post'));
    }

    // 6. Cập nhật bài viết
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        $is_comment = $request->has('is_comment_enabled') ? 1 : 0;
        $post->topic_id = $request->topic_id;
        $post->content = $request->content;
        $post->is_comment_enabled = $is_comment;
        $post->save();

        // Nếu có upload media mới
        if ($request->hasFile('file')) {
            // Xóa media cũ nếu cần thiết hoặc lưu thêm
            foreach ($request->file('file') as $file) {
                $path = $file->store('posts/media', 'public');
                Media::create([
                    'post_id' => $post->id,
                    'file_path' => $path,
                    'type' => str_contains($file->getMimeType(), 'video') ? 'video' : 'image'
                ]);
            }
        }

        return redirect()->route('posts.index')->with('success', 'Cập nhật thành công');
    }

    // 7. Xóa bài viết (Xóa luôn comment, favorite và media liên quan)
    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        // Xóa file vật lý trong storage
        $medias = Media::where('post_id', $id)->get();
        foreach ($medias as $m) {
            Storage::disk('public')->delete($m->file_path);
            $m->delete();
        }

        // Xóa các liên kết (Tương đương code cũ của bạn)
        Comment::where('post_id', $id)->delete();
        Favorite::where('post_id', $id)->delete();
        
        $post->delete();

        return redirect()->back()->with('success', 'Đã xóa bài viết');
    }

    // 8. Hiển thị bài viết theo chủ đề (Topics)
    public function postsByTopic($topicId)
    {
        $topic = Topic::findOrFail($topicId);
        $posts = Post::where('topic_id', $topicId)
                    ->with(['user.profile', 'media'])
                    ->orderBy('created_at', 'desc')
                    ->get();
        
        return view('posts.topic', compact('posts', 'topic'));
    }

    // 9. Duyệt bài viết
    public function approve($id)
    {
        // Giả sử bạn thêm cột 'status' vào bảng posts để duyệt
        $updated = Post::where('id', $id)->update(['pinned' => 1]); // Ví dụ dùng pinned làm trạng thái duyệt

        return redirect()->back()->with('success', 'Duyệt bài thành công!');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Topic;
use App\Models\Media;
use App\Models\Report;
use App\Models\Comment;
use App\Models\Favorite;
use App\Models\VideoView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    // 1. Hiển thị danh sách bài viết (Admin/Mod)
    public function index()
    {
        $posts = Post::with(['user.profile', 'topics', 'media'])
                    ->withCount(['comments', 'likes', 'favorites'])
                    ->orderBy('created_at', 'desc')->where('status', 'show')
                    ->paginate(10);

        return view('admin.posts', compact('posts'));
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
            $post->content = $request->content;
            $post->is_comment_enabled = $request->is_comment_enabled ?? 1;
            // Xử lý ghim duy nhất 1 bài viết
            if ($request->has('pinned')) {
                Post::where('user_id', Auth::id())->update(['pinned' => 0]);
                $post->pinned = 1;
            } else {
                $post->pinned = 0;
            }

            $post->save();

            $topicIds = $request->topic_ids ? explode(',', $request->topic_ids) : [];
            $newTopics = $request->new_topics ? explode(',', $request->new_topics) : [];

            foreach ($newTopics as $name) {
                if (!$name) continue;
                $topic = Topic::firstOrCreate(['name' => strtolower(trim($name))]);
                $topicIds[] = $topic->id;
            }

            $topicIds = array_unique(array_filter($topicIds));
            if (count($topicIds) > 3) {
                DB::rollBack();
                return back()->with('error', 'Chỉ tối đa 3 chủ đề');
            }

            $post->topics()->sync($topicIds);

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

            if (Auth::user()->role == 'admin') {
                return redirect()->route('home')->with('success', 'Đăng bài thành công!');
            }

            return view('2_back');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect(route('home'))->with('error', 'Có lỗi: ' . $e->getMessage());
        }
    }

    // 4. Xem chi tiết
    public function detail(request $request, $id)
    {
        $layout = $request->ajax() ? 'layouts.app_detail' : 'layouts.app';
        $post = Post::with([
            'user.profile', 'media', 'topics', 'likes', 'favorites',
            'comments' => function ($query) {
                $query->whereNull('parent_comment_id')->with(['user.profile', 'replies.user.profile'])->latest();
            }
        ])->findOrFail($id);

        if ($post->status !== 'show' && (!auth()->check() || auth()->user()->role !== 'admin')) {
            abort(403, 'Bài viết đã khóa hoặc không tồn tại');
        }

        return view('posts.detail', compact('post','layout'));
    }

    // 5. Giao diện chỉnh sửa
    public function edit($id)
    {
        $topics = Topic::all();
        $post = Post::with('media', 'topics')->findOrFail($id);

        // Check quyền sở hữu hoặc Staff
        if (auth()->user()->role !== 'admin' && auth()->id() !== $post->user_id) {
            abort(403, 'Bạn không có quyền');
        }

        if (request()->ajax()) {
            return view('posts.edit', compact('topics', 'post'))->renderSections()['content'];
        }
        return view('posts.edit', compact('topics', 'post'));
    }

    // 6. Cập nhật bài viết
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        // Check quyền sở hữu hoặc Staff
        if (auth()->user()->role !== 'admin' && auth()->id() !== $post->user_id) {
            abort(403, 'Bạn không có quyền');
        }

        // Xử lý ghim duy nhất 1 bài viết khi cập nhật
        if ($request->has('pinned')) {
            Post::where('user_id', $post->user_id)->where('id', '!=', $id)->update(['pinned' => 0]);
            $post->pinned = 1;
        } else {
            $post->pinned = 0;
        }

        $post->content = $request->content;
        $post->is_comment_enabled = $request->has('is_comment_enabled');
        $post->save();

        $topicIds = array_filter(explode(',', $request->topic_ids ?? ''));
        foreach (array_filter(explode(',', $request->new_topics ?? '')) as $name) {
            $topicIds[] = Topic::firstOrCreate(['name' => strtolower(trim($name))])->id;
        }
        $post->topics()->sync(array_slice(array_unique($topicIds), 0, 3));

        if ($request->deleted_media_ids) {
            $ids = explode(',', $request->deleted_media_ids);
            $medias = $post->media()->whereIn('id', $ids)->get();
            foreach ($medias as $media) {
                Storage::disk('public')->delete($media->file_path);
                $media->delete();
            }
        }

        if ($request->hasFile('file')) {
            foreach ($request->file('file') as $file) {
                $path = $file->store('posts', 'public');
                $type = str_contains($file->getMimeType(), 'video') ? 'video' : 'image';
                Media::create([
                    'post_id' => $post->id,
                    'file_path' => $path,
                    'type' => $type
                ]);
            }
        }

        if ($request->ajax()) {
            $post->load(['topics', 'media']);
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thành công',
                'html' => view('posts.post_item', compact('post'))->render()
            ]);
        }
        return redirect()->route('home')->with('success', 'Cập nhật thành công');
    }

    // 7. Xóa bài viết
    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        // Check quyền sở hữu hoặc Staff
        if (auth()->user()->role !== 'admin' || auth()->user()->role !== 'moderator' && auth()->id() !== $post->user_id) {
            abort(403, 'Bạn không có quyền');
        }

        $medias = Media::where('post_id', $id)->get();
        foreach ($medias as $m) {
            Storage::disk('public')->delete($m->file_path);
            $m->delete();
        }

        Report::where('target_id', $id)->where('target_type', Post::class)->delete();
        $post->delete();
        $postlist = Post::latest()->get();

        return response()->json([
            'success' => true,
            'data' => $postlist,
            'count' => Post::count(),
            'message' => 'Xóa thành công'
        ]);
    }

    // 8. Hiển thị bài viết theo chủ đề
    public function postsByTopic($topicId)
    {
        $topic = Topic::findOrFail($topicId);
        $posts = Post::whereHas('topics', function($q) use ($topicId) {
                        $q->where('topic_id', $topicId);
                    })
                    ->with(['user.profile', 'media'])
                    ->orderBy('created_at', 'desc')->where('status', 'show')
                    ->get();
        
        return view('posts.topic', compact('posts', 'topic'));
    }



    public function loadComments($id)
    {
        $post = Post::with('comments.user')->where('status', 'show')->findOrFail($id);
        return view('posts.comments', compact('post'));
    }

    public function like_list(Request $request, $id)
    {
        $layout = $request->ajax() ? 'layouts.app_detail' : 'layouts.app';
        $item = Post::with(['likedUsers.profile'])->findOrFail($id);
        $values = $item->likedUsers; 
        return view('like.like-list', compact('values', 'item', 'layout'));
    } 
}

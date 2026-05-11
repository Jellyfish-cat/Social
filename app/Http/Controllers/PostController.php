<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Topic;
use App\Models\Media;
use App\Models\Report;
use App\Models\Comment;
use App\Models\Favorite;
use App\Models\VideoView;
use App\Services\ContentModerationService;
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
                    ->withCount(['comments', 'likes', 'favorites']) // Đảm bảo đã có
                    ->orderBy('created_at', 'desc')
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
    public function store(Request $request, ContentModerationService $moderator)
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
                // ✅ Validate extension + MIME type trước khi lưu
                $request->validate([
                    'file'   => 'array|max:10',
                    'file.*' => 'file|mimes:jpg,jpeg,png,gif,webp,mp4,webm,mov|max:51200',
                ]);

                foreach ($request->file('file') as $file) {
                    // ✅ Dùng hashName() – tên ngẫu nhiên, không giữ extension gốc từ client
                    $safeName = $file->hashName();
                    $path = $file->storeAs('posts/media', $safeName, 'public');

                    $media = new Media();
                    $media->post_id = $post->id;
                    $media->file_path = $path;
                    $media->type = str_contains($file->getMimeType(), 'video') ? 'video' : 'image';
                    $media->save();
                }
            }

            DB::commit();

            return redirect()->route('home')->with('success', 'Đăng bài thành công!')->with('just_posted', true);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect(route('home'))->with('error', 'Có lỗi: ' . $e->getMessage());
        }
    }

    // 4. Xem chi tiết
    public function detail(request $request, $id)
    {
        $layout = $request->ajax() ? 'layouts.empty' : 'layouts.app';
        $post = Post::withCount('comments')->with([
            'user.profile', 'media', 'topics', 'likes', 'favorites',
            'comments' => function ($query) {
                $query->whereNull('parent_comment_id')
                ->where('status', 'show')
                ->whereHas('user', fn($q) => $q->where('status', 'show'))
                ->with(['user.profile', 'replies' => function($r) {
                    $r->where('status', 'show')->whereHas('user', fn($u) => $u->where('status', 'show'))->with('user.profile');
                }])->latest();
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
    public function update(Request $request, $id, ContentModerationService $moderator)
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


        $topicIds = array_filter(explode(',', $request->topic_ids ?? ''));
        foreach (array_filter(explode(',', $request->new_topics ?? '')) as $name) {
            $topicIds[] = Topic::firstOrCreate(['name' => strtolower(trim($name))])->id;
        }
        $post->topics()->sync(array_slice(array_unique($topicIds), 0, 3));

        // Cập nhật timestamp và lưu để chắc chắn kích hoạt sự kiện 'updated' cho Activity Log
        $post->updated_at = now();
        $post->save(); 

        if ($request->deleted_media_ids) {
            $ids = explode(',', $request->deleted_media_ids);
            $medias = $post->media()->whereIn('id', $ids)->get();
            foreach ($medias as $media) {
                Storage::disk('public')->delete($media->file_path);
                $media->delete();
            }
        }

        if ($request->hasFile('file')) {
            // ✅ Validate extension + MIME type trước khi lưu
            $request->validate([
                'file'   => 'array|max:10',
                'file.*' => 'file|mimes:jpg,jpeg,png,gif,webp,mp4,webm,mov|max:51200',
            ]);

            foreach ($request->file('file') as $file) {
                // ✅ hashName() tạo tên ngẫu nhiên an toàn
                $path = $file->storeAs('posts', $file->hashName(), 'public');
                $type = str_contains($file->getMimeType(), 'video') ? 'video' : 'image';
                Media::create([
                    'post_id'   => $post->id,
                    'file_path' => $path,
                    'type'      => $type
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
        return redirect()->back()->with('success', 'Cập nhật thành công');
    }

    // 7. Xóa bài viết
    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        // Check quyền sở hữu hoặc Staff
        if (!in_array(auth()->user()->role, ['admin', 'moderator']) && auth()->id() !== $post->user_id) {
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
                    ->whereHas('user', function($q) {
                        $q->where('status', 'show');
                    })
                    ->with(['user.profile', 'media'])
                    ->orderBy('created_at', 'desc')->where('status', 'show')
                    ->get();
        
        return view('posts.topic', compact('posts', 'topic'));
    }



    public function loadComments($id)
    {
        $comments = Comment::where('post_id', $id)
            ->whereNull('parent_comment_id')
            ->where('status', 'show')
            ->whereHas('user', function($q) {
                $q->where('status', 'show');
            })
            ->with(['user.profile', 'replies' => function($q) {
                $q->where('status', 'show')->whereHas('user', fn($u) => $u->where('status', 'show'))->with('user.profile');
            }])
            ->latest()
            ->get();
        return view('posts.comments', compact('comments'));
    }

    public function like_list(Request $request, $id)
    {
        if (!$request->ajax()) {
            return redirect()->back();
        }
        $layout = 'layouts.empty';
        
        // Lấy bài viết và chỉ lấy những người thích đang ở trạng thái 'show'
        $item = Post::with(['likedUsers' => function($q) {
            $q->where('status', 'show')->with('profile');
        }])->findOrFail($id);
        
        $values = $item->likedUsers; 
        return view('like.like-list', compact('values', 'item', 'layout'));
    } 
}

<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\Post;
use App\Models\Comment;
use App\Services\PostService;
use App\Services\ContentModerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    protected $postService;
    protected $topicService;

    public function __construct(PostService $postService, \App\Services\TopicService $topicService)
    {
        $this->postService = $postService;
        $this->topicService = $topicService;
    }

    // 1. Hiển thị danh sách bài viết (Admin/Mod)
    public function index()
    {
        $posts = $this->postService->getAdminPosts(10);
        return view('admin.posts', compact('posts'));
    }

    // 2. Giao diện tạo bài viết
    public function create()
    {
        $topics = $this->topicService->getAllTopics(); 
        $post = $this->postService->getAllPosts();
        return view('posts.create', compact('topics', 'post'));
    }

    // 3. Lưu bài viết mới
    public function store(Request $request, ContentModerationService $moderator)
    {
        if ($request->hasFile('file')) {
            $request->validate([
                'file'   => 'array|max:10',
                'file.*' => 'file|mimes:jpg,jpeg,png,gif,webp,mp4,webm,mov|max:51200',
            ]);
        }

        try {
            $this->postService->createPost($request->all(), Auth::id(), $request->file('file'));
            return redirect()->route('home')->with('success', 'Đăng bài thành công!')->with('just_posted', true);
        } catch (\Exception $e) {
            return redirect(route('home'))->with('error', 'Có lỗi: ' . $e->getMessage());
        }
    }

    // 4. Xem chi tiết
    public function detail(Request $request, $id)
    {
        $layout = $request->ajax() ? 'layouts.empty' : 'layouts.app';
        $post = $this->postService->getPostDetail($id, auth()->user());

        return view('posts.detail', compact('post','layout'));
    }

    // 5. Giao diện chỉnh sửa
    public function edit($id)
    {
        $topics = $this->topicService->getAllTopics();
        $post = $this->postService->getPostForEdit($id, auth()->user());

        if (request()->ajax()) {
            return view('posts.edit', compact('topics', 'post'))->renderSections()['content'];
        }
        return view('posts.edit', compact('topics', 'post'));
    }

    // 6. Cập nhật bài viết
    public function update(Request $request, $id, ContentModerationService $moderator)
    {
        if ($request->hasFile('file')) {
            $request->validate([
                'file'   => 'array|max:10',
                'file.*' => 'file|mimes:jpg,jpeg,png,gif,webp,mp4,webm,mov|max:51200',
            ]);
        }

        $post = $this->postService->updatePost($id, $request->all(), auth()->user(), $request->file('file'));

        if ($request->ajax()) {
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
        $postlist = $this->postService->deletePost($id, auth()->user());

        return response()->json([
            'success' => true,
            'data' => $postlist,
            'count' => $this->postService->getPostCount(),
            'message' => 'Xóa thành công'
        ]);
    }

    // 8. Hiển thị bài viết theo chủ đề
    public function postsByTopic($topicId)
    {
        $topic = $this->topicService->getTopicById($topicId);
        $posts = $this->postService->getPostsByTopic($topicId);
        
        return view('posts.topic', compact('posts', 'topic'));
    }

    // --- Các hàm bên dưới sẽ được tách qua Service khác trong tương lai ---

    public function loadComments($id, \App\Services\CommentService $commentService)
    {
        $comments = $commentService->getPostComments($id);
        return view('posts.comments', compact('comments'));
    }

    public function like_list(Request $request, $id, \App\Services\InteractionService $interactionService)
    {
        if (!$request->ajax()) {
            return redirect()->back();
        }
        $layout = 'layouts.empty';
        
        // Lấy bài viết và chỉ lấy những người thích đang ở trạng thái 'show'
        $item = $interactionService->getPostWithLikes($id);
        
        $values = $item->likedUsers; 
        return view('like.like-list', compact('values', 'item', 'layout'));
    } 
}

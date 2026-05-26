<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ShareService;

class ShareController extends Controller
{
    protected $shareService;

    public function __construct(ShareService $shareService)
    {
        $this->shareService = $shareService;
    }

    /**
     * Lấy danh sách bạn bè để hiển thị trong modal chia sẻ
     */
    public function getShareList($id)
    {
        $friends = $this->shareService->getShareFriendsList(auth()->user());
        $postId = $id;
        
        return view('posts.partials.share-user-list', compact('friends', 'postId'));
    }

    /**
     * Thực hiện gửi bài viết cho nhiều người dùng đã chọn
     */
    public function shareToUsers(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $sharedCount = $this->shareService->sharePostToUsers(
            $request->post_id,
            $request->user_ids,
            auth()->user()
        );

        return response()->json([
            'success' => true,
            'message' => "Đã chia sẻ bài viết cho $sharedCount người dùng."
        ]);
    }
}

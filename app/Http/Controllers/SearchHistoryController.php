<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\SearchHistory;
use App\Models\Post;
use App\Models\User;
use App\Models\Topic;
use App\Models\Comment;
use App\Models\Message;
use App\Models\Conversation;
use App\Models\Report;
use Illuminate\Http\Request;

class SearchHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    $searchHistorys = SearchHistory::with(['user.profile']) // Lấy thông tin người đăng, chủ đề và danh sách ảnh/video
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        return view('admin.searchs', compact('searchHistorys'));
    }
    public function search(Request $request)
    {
        $keyword = $request->input('q');
        $user = auth()->user();
        $referer = $request->headers->get('referer');

        // CHỈ LƯU LỊCH SỬ TÌM KIẾM CHO USER THƯỜNG (Logged in)
        if ($user && $user->role === 'user') {
            $updated = SearchHistory::where('user_id', $user->id)
                ->where('keyword', $keyword)
                ->update([
                    'updated_at' => now()
                ]);
            if ($updated === 0) {
                SearchHistory::create([
                    'user_id' => $user->id,
                    'keyword' => $keyword
                ]);
            }
        }

        // XỬ LÝ TÌM KIẾM CHO ADMIN/MODERATOR
        if ($user->role === 'admin' || $user->role === 'moderator') {
            
            // --- 0. ƯU TIÊN NGỮ CẢNH HIỆN TẠI (REFERER) ---
            if (str_contains($referer, '/admin/messages')) {
                $messages = Message::where(fn($sub) => $sub->where('content', 'LIKE', "%$keyword%")
                    ->orWhereHas('sender.profile', fn($q) => $q->where('display_name', 'LIKE', "%$keyword%"))
                    ->orWhereHas('sender', fn($q) => $q->where('name', 'LIKE', "%$keyword%")))
                    ->with(['sender.profile', 'conversation'])->orderBy('created_at', 'desc')->paginate(10);
                return view('admin.messages', compact('messages'));
            }
            if (str_contains($referer, '/admin/users')) {
                $users = User::where(function($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%$keyword%")
                      ->orWhere('email', 'LIKE', "%$keyword%")
                      ->orWhereHas('profile', fn($sub) => $sub->where('display_name', 'LIKE', "%$keyword%"));
                })->with(['profile'])->withCount(['posts', 'comments', 'favorites', 'followers', 'following'])
                ->orderBy('created_at', 'desc')->paginate(10);
                return view('admin.users', compact('users'));
            }
            if (str_contains($referer, '/admin/topics')) {
                $topics = Topic::where('name', 'LIKE', "%$keyword%")
                    ->orderBy('created_at', 'desc')->paginate(10);
                return view('admin.topics', compact('topics'));
            }
            if (str_contains($referer, '/admin/comments')) {
                $comments = Comment::where(fn($sub) => $sub->where('content', 'LIKE', "%$keyword%")
                    ->orWhereHas('user.profile', fn($q) => $q->where('display_name', 'LIKE', "%$keyword%"))
                    ->orWhereHas('user', fn($q) => $q->where('name', 'LIKE', "%$keyword%")))
                    ->with(['user.profile', 'post'])->orderBy('created_at', 'desc')->paginate(10);
                return view('admin.comments', compact('comments'));
            }
            if (str_contains($referer, '/admin/conversations')) {
                $conversations = Conversation::whereHas('users.profile', function ($q) use ($keyword) {
                    $q->where('display_name', 'LIKE', "%$keyword%")->orWhere('name', 'LIKE', "%$keyword%");
                })->with(['users.profile'])->withCount('messages')->orderBy('created_at', 'desc')->paginate(10);
                return view('admin.conversations', compact('conversations'));
            }
            if (str_contains($referer, '/admin/searchs')) {
                $searchHistorys = SearchHistory::where('keyword', 'LIKE', "%$keyword%")->with(['user.profile'])->orderBy('created_at', 'desc')->paginate(10);
                return view('admin.searchs', compact('searchHistorys'));
            }
            if (str_contains($referer, '/admin/reports')) {
                $tab = str_contains($referer, 'resolved') ? 'resolved' : 'pending';
                $status = ($tab === 'resolved') ? Report::STATUS_RESOLVED : Report::STATUS_PENDING;
                
                $values = Report::where('status', $status)
                    ->where(fn($sub) => $sub->where('reason', 'LIKE', "%$keyword%")->orWhere('category', 'LIKE', "%$keyword%"))
                    ->with(['user.profile', 'target'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
                
                $type = 'post'; // Mặc định context search là post hoặc có thể detect thêm
                $item = 'report-item';
                $delete = 'btn-delete-report';

                return view('admin.report', compact('values', 'tab', 'type', 'item', 'delete'));
            }

            // Mặc định: Quản lý bài viết (nếu không ở trang cụ thể nào)
            $posts = Post::where(function($q) use ($keyword) {
                $q->where('content', 'LIKE', "%$keyword%")
                  ->orWhereHas('user.profile', fn($query) => $query->where('display_name', 'LIKE', "%$keyword%"))
                  ->orWhereHas('user', fn($query) => $query->where('name', 'LIKE', "%$keyword%"));
            })->with(['user.profile', 'topics', 'media'])->withCount(['comments', 'likes', 'favorites'])
            ->orderBy('created_at', 'desc')->paginate(10);
            return view('admin.posts', compact('posts'));
        }

        // Giao diện cho User thường
        $posts = Post::where(function($q) use ($keyword) {
            $q->where('content', 'LIKE', "%$keyword%")
              ->orWhereHas('user.profile', fn($query) => $query->where('display_name', 'LIKE', "%$keyword%"))
              ->orWhereHas('user', fn($query) => $query->where('name', 'LIKE', "%$keyword%"));
        })->with(['user.profile', 'media', 'likes', 'comments', 'favorites'])
        ->orderBy('created_at', 'desc')->get();

        $checktopic = false;
        return view('search.result', compact('posts', 'keyword', 'checktopic'));
    }
    public function searchTab(Request $request, $type)
    {
        $keyword = $request->input('q');
        $user = auth()->user();

        if ($type === 'post') {
            // 1. Phân tích ngữ cảnh người dùng
            $followingIds = $user ? $user->following()->pluck('users.id')->toArray() : [];
            $interestedTopicIds = $user ? $user->likedPosts()
                ->with(['post.topics'])
                ->get()
                ->flatMap(function ($like) {
                    return ($like->post && $like->post->topics) ? $like->post->topics->pluck('id')->toArray() : [];
                })
                ->unique()
                ->toArray() : [];

            $results = collect();
            $excludeIds = [];

            // Hàm tạo search builder chung
            $baseSearch = function() use ($keyword) {
                return Post::search($keyword)->where('status', 'show');
            };

            // Ưu tiên 1: Người đang theo dõi
            if (!empty($followingIds)) {
                $followingPosts = $baseSearch()
                    ->whereIn('user_id', $followingIds)
                    ->query(fn($q) => $q->with(['user.profile', 'topics', 'likes', 'comments', 'favorites']))
                    ->get();
                $results = $results->merge($followingPosts);
                $excludeIds = $followingPosts->pluck('id')->toArray();
            }

            // Ưu tiên 2: Chủ đề quan tâm
            if (!empty($interestedTopicIds)) {
                $topicPosts = $baseSearch()
                    ->whereIn('topic_ids', $interestedTopicIds)
                    ->query(fn($q) => $q->whereNotIn('id', $excludeIds)->with(['user.profile', 'topics', 'likes', 'comments', 'favorites']))
                    ->get();
                $results = $results->merge($topicPosts);
                $excludeIds = array_merge($excludeIds, $topicPosts->pluck('id')->toArray());
            }
            // Ưu tiên 3: Các kết quả khác
            $otherPosts = $baseSearch()
                ->query(fn($q) => $q->whereNotIn('id', $excludeIds)->with(['user.profile', 'topics', 'likes', 'comments', 'favorites']))
                ->get();
            $posts = $results->merge($otherPosts);
            $checktopic = false;
            return view('search.partials.post-list', compact('posts', 'keyword', 'checktopic'));
        } 
        elseif ($type === 'people') {
            $followingIds = $user ? $user->following()->pluck('users.id')->toArray() : [];
            $users = User::search($keyword)
                ->where('role', 'user')
                ->query(fn($q) => $q->with(['profile']))
                ->get();

            // Ranking: Đưa những người đang follow lên đầu
            if (!empty($followingIds)) {
                $users = $users->sortByDesc(fn($u) => in_array($u->id, $followingIds))->values();
            }

            return view('search.partials.people-list', compact('users'));
        }
        elseif ($type === 'topic') {
            // Tìm kiếm chủ đề bằng Meilisearch
            $topics = Topic::search($keyword)->get();
            return view('search.partials.topic-list', compact('topics'));
        } 
    }
    public function suggestions(Request $request)
    {
        $q = trim($request->q);
        $user = auth()->user();
        $referer = $request->headers->get('referer');

        // === XỬ LÝ GỢI Ý CHO ADMIN THEO NGỮ CẢNH TRANG QUẢN LÝ ===
        if ($user && ($user->role === 'admin' || $user->role === 'moderator') && $q) {
            if (str_contains($referer, '/admin/topics')) {
                return response()->json(['topics' => Topic::where('name', 'LIKE', "%$q%")->limit(10)->get()]);
            } 
            elseif (str_contains($referer, '/admin/users')) {
                return response()->json(['users' => User::where('name', 'LIKE', "%$q%")
                    ->orWhere('email', 'LIKE', "%$q%")
                    ->with('profile')->limit(10)->get()]);
            } 
            elseif (str_contains($referer, '/admin/posts')) {
                return response()->json(['posts' => Post::where('content', 'LIKE', "%$q%")
                    ->orWhereHas('user.profile', fn($query) => $query->where('display_name', 'LIKE', "%$q%"))
                    ->orWhereHas('user', fn($query) => $query->where('name', 'LIKE', "%$q%"))
                    ->with('user.profile')
                    ->limit(10)->get()]);
            } 
            elseif (str_contains($referer, '/admin/comments')) {
                return response()->json(['comments' => Comment::where('content', 'LIKE', "%$q%")
                    ->orWhereHas('user.profile', fn($query) => $query->where('display_name', 'LIKE', "%$q%"))
                    ->orWhereHas('user', fn($query) => $query->where('name', 'LIKE', "%$q%"))
                    ->with('user.profile')
                    ->limit(10)->get()]);
            }
            elseif (str_contains($referer, '/admin/messages')) {
                return response()->json(['messages' => Message::where('content', 'LIKE', "%$q%")
                    ->orWhereHas('sender.profile', fn($query) => $query->where('display_name', 'LIKE', "%$q%"))
                    ->orWhereHas('sender', fn($query) => $query->where('name', 'LIKE', "%$q%"))
                    ->with('sender.profile')
                    ->limit(10)->get()]);
            }
            elseif (str_contains($referer, '/admin/conversations')) {
                return response()->json(['conversations' => Conversation::whereHas('users.profile', function ($sub) use ($q) {
                    $sub->where('display_name', 'LIKE', "%$q%")->orWhere('name', 'LIKE', "%$q%");
                })->with(['users.profile'])->limit(10)->get()]);
            }
            elseif (str_contains($referer, '/admin/searchs')) {
                return response()->json(['admin_history' => SearchHistory::where('keyword', 'LIKE', "%$q%")->with('user.profile')->limit(10)->get()]);
            }
            elseif (str_contains($referer, '/admin/reports')) {
                $status = str_contains($referer, 'resolved') ? Report::STATUS_RESOLVED : Report::STATUS_PENDING;
                return response()->json(['reports' => Report::where('status', $status)
                    ->where(fn($sub) => $sub->where('reason', 'LIKE', "%$q%")->orWhere('category', 'LIKE', "%$q%"))
                    ->limit(10)->get()]);
            }
        }
        elseif ($user && $user->role === 'user' && $q) {
        // === LOGIC GỢI Ý MẶC ĐỊNH CHO USER (HOẶC KHI KHÔNG Ở TRANG QUẢN LÝ) ===
        $topics = collect();
        $users = collect();
        $posts = collect();

        $histories = SearchHistory::where('user_id', auth()->id())
            ->latest('updated_at')
            ->limit(5)
            ->get();

        if (!$q) {
            return response()->json([
                'history' => $histories,
                'topics' => [],
                'users' => [],
                'posts' => []
            ]);
        }
        if ($user) {
            $followingIds = $user->following()->pluck('users.id')->toArray();
            $interestedTopicIds = \App\Models\LikePost::where('user_id', $user->id)
                ->with(['post.topics'])
                ->get()
                ->flatMap(function ($like) {
                    if ($like->post && $like->post->topics) {
                        return $like->post->topics->pluck('id')->toArray();
                    }
                    return [];
                })
                ->unique()
                ->toArray();
        }
        $topics = Topic::search($q)->take(5)->get();
        $users = User::search($q)->where('role', 'user')->get();
        $users = $users->sortByDesc(fn($u) => in_array($u->id, $followingIds))
                       ->take(5)
                       ->values();
        $results = collect();
        $excludeIds = [];
        $postSearch = function() use ($q) {
            return Post::search($q)
                ->where('status', 'show')
                ->orderBy('created_at', 'desc');
        };
        if (!empty($followingIds)) {
            $followingPosts = $postSearch()
                ->whereIn('user_id', $followingIds)
                ->query(fn($query) => $query->with(['user.profile', 'topics']))
                ->take(5)
                ->get();
            $results = $results->merge($followingPosts);
            $excludeIds = $followingPosts->pluck('id')->toArray();
        }

        if (!empty($interestedTopicIds)) {
            $topicPosts = $postSearch()
                ->whereIn('topic_ids', $interestedTopicIds)
                ->query(fn($query) => $query->with(['user.profile', 'topics'])->whereNotIn('id', $excludeIds))
                ->take(5)
                ->get();
            $results = $results->merge($topicPosts);
            $excludeIds = array_merge($excludeIds, $topicPosts->pluck('id')->toArray());
        }

        $otherPosts = $postSearch()
            ->query(fn($query) => $query->with(['user.profile', 'topics'])->whereNotIn('id', $excludeIds))
            ->take(5)
            ->get();

        $posts = $results->merge($otherPosts)->take(5);


        } else {
            // Đối với khách: không có lịch sử tìm kiếm
            $histories = collect(); 
            $topics = collect();
            $users = collect();
            $posts = collect();
            if ($q) {
                $topics = Topic::search($q)->take(5)->get();
                $users = User::search($q)->where('role', 'user')->take(5)->get();
                $posts = Post::search($q)
                    ->where('status', 'show')
                    ->query(fn($query) => $query->with(['user.profile', 'topics']))
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get();
            }
        }
        return response()->json([
            'history' => $histories,
            'topics' => $topics,
            'users' => $users,
            'posts' => $posts
        ]);
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(SearchHistory $searchHistory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SearchHistory $searchHistory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SearchHistory $searchHistory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $searchHistory = SearchHistory::find($id);
        if (auth()->user()->role !== 'admin' && auth()->id() !== $searchHistory->user_id) {
            abort(403, 'Bạn không có quyền');
        }
        if (!$searchHistory) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy từ khóa'
            ], 404);
        }

        $searchHistory->delete();
        $searchHistorylist = SearchHistory::latest()->get();
        return response()->json([
            'success' => true,
            'data' => $searchHistorylist,
            'count' => SearchHistory::count(),
            'message' => 'Xóa thành công'
        ]);
    }
}

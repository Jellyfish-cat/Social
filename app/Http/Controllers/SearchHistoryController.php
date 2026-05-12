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
use App\Models\ActivityLog;
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
            
            // Ghi log hoạt động tìm kiếm của người dùng
            activity()
                ->event('search')
                ->tap(function ($activity) { $activity->user_id = auth()->id(); })
                ->withProperties(['keyword' => $keyword])
                ->log('search');
        }

        // XỬ LÝ TÌM KIẾM CHO ADMIN/MODERATOR
        if ($user && ($user->role === 'admin' || $user->role === 'moderator')) {
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
                $values = Report::where(function($q) use ($keyword) {
                        $q->where('reason', 'LIKE', "%$keyword%")
                          ->orWhere('category', 'LIKE', "%$keyword%")
                          ->orWhereHasMorph('target', [\App\Models\Post::class, \App\Models\Comment::class, \App\Models\Message::class], function($m) use ($keyword) {
                              $m->where('content', 'LIKE', "%$keyword%");
                          })
                          ->orWhereHasMorph('target', [\App\Models\User::class], function($m) use ($keyword) {
                              $m->where('name', 'LIKE', "%$keyword%");
                          });
                    })
                    ->with(['user.profile', 'target'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
                
                $tab = 'pending'; 
                $type = 'post';    
                $item = 'report-item';
                $delete = 'btn-delete-report';

                return view('admin.report', compact('values', 'tab', 'type', 'item', 'delete'));
            }
            if (str_contains($referer, '/admin/logs')) {
                $logs = ActivityLog::where('event', 'LIKE', "%$keyword%")
                    ->orWhere('subject_type', 'LIKE', "%$keyword%")
                    ->orWhere('properties', 'LIKE', "%$keyword%")
                    ->with('user.profile')
                    ->latest()
                    ->paginate(20);
                return view('admin.logs', compact('logs'));
            }

            // Ghi log hoạt động tìm kiếm của Admin/Moderator
            activity()
                ->event('search_admin')
                ->tap(function ($activity) { $activity->user_id = auth()->id(); })
                ->withProperties(['keyword' => $keyword, 'referer' => $referer])
                ->log('search_admin');

            $posts = Post::where(function($q) use ($keyword) {
                $q->where('content', 'LIKE', "%$keyword%")
                  ->orWhereHas('user.profile', fn($query) => $query->where('display_name', 'LIKE', "%$keyword%"))
                  ->orWhereHas('user', fn($query) => $query->where('name', 'LIKE', "%$keyword%"));
            })->with(['user.profile', 'topics', 'media'])->withCount(['comments', 'likes', 'favorites'])
            ->orderBy('created_at', 'desc')->paginate(10);
            return view('admin.posts', compact('posts'));
        }
        else {
            // 1. Tìm bài viết bằng Meilisearch (Chỉ lấy bài đang hiển thị)
            $posts = Post::search($keyword)->where('status', 'show')->get();
            $posts->load(['user.profile', 'media', 'likes', 'comments', 'favorites', 'topics']);

            // 2. TÍCH HỢP AI RANKING (Sắp xếp lại kết quả tìm được)
            if ($user && $posts->isNotEmpty()) {
                try {
                    $candPostIds = $posts->pluck('id')->toArray();
                    $aiResponse = \Illuminate\Support\Facades\Http::timeout(3)->post('http://127.0.0.1:8001/api/rank_search_results', [
                        'user_id' => $user->id,
                        'cand_user_ids' => [],
                        'cand_post_ids' => $candPostIds
                    ]);

                    if ($aiResponse->successful()) {
                        $aiData = $aiResponse->json();
                        $rankedIds = $aiData['post_ids'] ?? [];
                        if (!empty($rankedIds)) {
                            // Sắp xếp lại Collection $posts theo thứ tự IDs mà AI trả về
                            $posts = $posts->sortBy(fn($post) => array_search($post->id, $rankedIds))->values();
                        }
                    }
                } catch (\Exception $e) {
                    // Nếu AI lỗi, giữ nguyên thứ tự mặc định của Meilisearch
                }
            }
      
        $checktopic = false;
        return view('search.result', compact('posts', 'keyword', 'checktopic'));
    }
    }
    public function searchTab(Request $request, $type)
    {
        $keyword = $request->input('q');
        $user = auth()->user();

        if ($type === 'post') { 
            // 1. Tìm bài viết bằng Meilisearch (Chỉ lấy bài đang hiển thị)
            $posts = Post::search($keyword)->where('status', 'show')->get();
            $posts->load(['user.profile', 'media', 'likes', 'comments', 'favorites', 'topics']);

            // 2. TÍCH HỢP AI RANKING CHO TAB POST
            if ($user && $posts->isNotEmpty()) {
                try {
                    $candPostIds = $posts->pluck('id')->toArray();
                    $aiResponse = \Illuminate\Support\Facades\Http::timeout(3)->post('http://127.0.0.1:8001/api/rank_search_results', [
                        'user_id' => $user->id,
                        'cand_user_ids' => [],
                        'cand_post_ids' => $candPostIds
                    ]);

                    if ($aiResponse->successful()) {
                        $aiData = $aiResponse->json();
                        $rankedIds = $aiData['post_ids'] ?? [];
                        if (!empty($rankedIds)) {
                            $posts = $posts->sortBy(fn($post) => array_search($post->id, $rankedIds))->values();
                        }
                    }
                } catch (\Exception $e) { }
            }

            $checktopic = false;
            return view('search.partials.post-list', compact('posts', 'keyword', 'checktopic'));
        } 
        elseif ($type === 'people') {
            // 1. Lấy danh sách ID người đang follow để ranking
            $followingIds = $user ? $user->following()->pluck('users.id')->toArray() : [];

            // 2. Tìm kiếm người dùng bằng Meilisearch
            $users = User::search($keyword)->get();
            $users = $users->filter(fn($u) => $u->role === 'user' && $u->status === 'show');
            $users->load(['profile', 'followers']);

            // 3. TÍCH HỢP AI RANKING CHO TAB PEOPLE
            if ($user && $users->isNotEmpty()) {
                try {
                    $candUserIds = $users->pluck('id')->toArray();
                    $aiResponse = \Illuminate\Support\Facades\Http::timeout(3)->post('http://127.0.0.1:8001/api/rank_search_results', [
                        'user_id' => $user->id,
                        'cand_user_ids' => $candUserIds,
                        'cand_post_ids' => []
                    ]);

                    if ($aiResponse->successful()) {
                        $aiData = $aiResponse->json();
                        $rankedIds = $aiData['user_ids'] ?? [];
                        if (!empty($rankedIds)) {
                            $users = $users->sortBy(fn($u) => array_search($u->id, $rankedIds))->values();
                        }
                    }
                } catch (\Exception $e) { }
            }

            return view('search.partials.people-list', compact('users'));
        }
        elseif ($type === 'topic') {
            $topics = Topic::search($keyword)->get()->loadCount('posts');
            return view('search.partials.topic-list', compact('topics'));
        } 
    }
    public function suggestions(Request $request)
    {
        $q = trim($request->q);
        $user = auth()->user();
        $referer = $request->headers->get('referer') ?? '';

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
                return response()->json(['reports' => Report::where(function($query) use ($q) {
                        $query->where('reason', 'LIKE', "%$q%")
                          ->orWhere('category', 'LIKE', "%$q%")
                          ->orWhereHas('user', fn($u) => $u->where('name', 'LIKE', "%$q%"))
                          ->orWhereHas('user.profile', fn($u) => $u->where('display_name', 'LIKE', "%$q%"));
                    })
                    ->with(['user.profile', 'target'])
                    ->limit(10)->get()]);
            }
            elseif (str_contains($referer, '/admin/logs')) {
                return response()->json(['logs' => ActivityLog::where('event', 'LIKE', "%$q%")
                    ->orWhere('subject_type', 'LIKE', "%$q%")
                    ->orWhere('properties', 'LIKE', "%$q%")
                    ->with('user.profile')
                    ->limit(10)->get()]);
            }

            // Fallback cho admin nếu không khớp referer nào
            return response()->json([
                'topics' => Topic::where('name', 'LIKE', "%$q%")->limit(5)->get(),
                'users' => User::where('name', 'LIKE', "%$q%")->limit(5)->get()
            ]);
        }
        else {
            $topics = collect();
            $users = collect();
            $posts = collect();
            $histories = collect();

            if ($user && $user->role === 'user') {
                $histories = SearchHistory::where('user_id', auth()->id())
                    ->latest('updated_at')
                    ->limit(5)
                    ->get();
            }

            if (!$q) {
                return response()->json([
                    'history' => $histories,
                    'topics' => [],
                    'users' => [],
                    'posts' => []
                ]);
            }


            $topics = Topic::search($q)->take(5)->get(); 
            $candUserIds = User::search($q)->where('role', 'user')->where('status', 'show')->take(50)->keys()->toArray();
            $candPostIds = Post::search($q)->where('status', 'show')->take(50)->keys()->toArray();
            // 1. Mặc định: Lấy 5 kết quả đầu từ Meilisearch làm phương án dự phòng (Fallback)
            $users = User::whereIn('id', array_slice($candUserIds, 0, 5))->with('profile')->get();
            $posts = Post::whereIn('id', array_slice($candPostIds, 0, 5))
                        ->with(['user.profile', 'topics'])
                        ->orderBy('created_at', 'desc')
                        ->get();

            // 2. Nếu là User thường -> Thử gọi AI để sắp xếp (Ranking) lại kết quả
            if ($user && $user->role === 'user') {
                try {
                    $response = \Illuminate\Support\Facades\Http::timeout(3)->post('http://127.0.0.1:8001/api/rank_search_suggestions', [
                        'user_id' => $user->id,
                        'cand_user_ids' => $candUserIds,
                        'cand_post_ids' => $candPostIds
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        if (!empty($data['user_ids'])) {
                            $topIds = array_slice($data['user_ids'], 0, 5);
                            $users = User::whereIn('id', $topIds)->with('profile')->get();
                            $users = $users->sortBy(fn($user) => array_search($user->id, $topIds))->values();
                        }
                        if (!empty($data['post_ids'])) {
                            $topIds = array_slice($data['post_ids'], 0, 5);
                            $posts = Post::whereIn('id', $topIds)->with(['user.profile', 'topics'])->get();
                            $posts = $posts->sortBy(fn($post) => array_search($post->id, $topIds))->values();
                        }
                    }
                } catch (\Exception $e) {
                    // AI lỗi thì giữ nguyên kết quả mặc định ở trên
                }
            }
            return response()->json([
                'history' => $histories,
                'topics' => $topics,
                'users' => $users,
                'posts' => $posts
            ]);
        }
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
        if (!in_array(auth()->user()->role, ['admin', 'moderator']) && auth()->id() !== $searchHistory->user_id) {
            abort(403, 'Bạn không có quyền');
        }
        if (!$searchHistory) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy từ khóa'
            ], 404);
        }

        activity()
            ->event('delete_search')
            ->tap(function ($activity) { $activity->user_id = auth()->id(); })
            ->withProperties(['keyword' => $searchHistory->keyword])
            ->log('delete_search');

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

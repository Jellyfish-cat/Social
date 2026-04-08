<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\SearchHistory;
use App\Models\Post;
use App\Models\User;
use App\Models\Topic;
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

        $updated=SearchHistory::where('user_id', Auth::id())
        ->where('keyword', $keyword)
        ->update([
            'updated_at' => now()
        ]);
        if ($updated === 0) {
            SearchHistory::create([
                'user_id' => Auth::id(),
                'keyword' => $keyword
            ]);
        }
        $posts = Post::search($keyword)
            ->query(fn($query) => $query->with(['user.profile', 'media', 'likes', 'comments', 'favorites']))
            ->get();

        $checktopic=false;
        return view('search.result', compact('posts', 'keyword','checktopic'));
    }    
    public function searchTab(Request $request,$type)
    {
         $keyword = $request->input('q');
        if ($type === 'post') {
            $posts = Post::search($keyword)->get();
            $checktopic = false;
            return view('search.partials.post-list', compact('posts', 'keyword', 'checktopic'));
        } 
        elseif ($type === 'people') {
            $users = User::search($keyword)->get();
            return view('search.partials.people-list', compact('users'));
        }
        elseif ($type === 'topic') {
            $topics = Topic::search($keyword)->get();
            return view('search.partials.topic-list', compact('topics'));
        } 
    }
    public function suggestions(Request $request)
    {
        $q = trim($request->q);
        $user = auth()->user();

        $topics = collect();
        $users = collect();
        $posts = collect();

        // Luôn lấy lịch sử tìm kiếm bằng get()
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

        try {
            // =========================
            // 1. CONTEXT USER
            // =========================
            $followingIds = $user->following()->pluck('users.id')->toArray();
            $interestedTopicIds = \App\Models\LikePost::where('user_id', $user->id)
                ->with(['post.topic', 'post.topics'])
                ->get()
                ->flatMap(function ($like) {
                    $ids = [];
                    if ($like->post && $like->post->topic_id) $ids[] = $like->post->topic_id;
                    if ($like->post && $like->post->topics) {
                        $ids = array_merge($ids, $like->post->topics->pluck('id')->toArray());
                    }
                    return $ids;
                })
                ->unique()
                ->toArray();

            // =========================
            // 2. TOPICS (SEARCH)
            // =========================
            $topics = Topic::search($q)->take(5)->get();

            // =========================
            // 3. USERS (ƯU TIÊN FOLLOW)
            // =========================
            $users = User::search($q)
                ->query(fn($query) => $query->with('profile'))
                ->take(10)
                ->get();
            $users = $users->sortByDesc(fn($u) => in_array($u->id, $followingIds))
                           ->take(5)
                           ->values();

            // =========================
            // 4. POSTS (SEARCH + ƯU TIÊN)
            // =========================
            $results = collect();
            $excludeIds = [];
            if (!empty($followingIds)) {
                $followingPosts = Post::search($q)
                    ->whereIn('user_id', $followingIds)
                    ->orderBy('created_at', 'desc')
                    ->query(fn($query) => $query->with(['user.profile', 'topic'])->where('status', 'show'))
                    ->take(5)
                    ->get();
                $results = $results->merge($followingPosts);
                $excludeIds = $followingPosts->pluck('id')->toArray();
            }

            if (!empty($interestedTopicIds)) {
                $topicPosts = Post::search($q)
                    ->whereIn('topic_id', $interestedTopicIds)
                    ->orderBy('created_at', 'desc')
                    ->query(fn($query) => $query->with(['user.profile', 'topic'])->where('status', 'show')->whereNotIn('id', $excludeIds))
                    ->take(5)
                    ->get();
                $results = $results->merge($topicPosts);
                $excludeIds = array_merge($excludeIds, $topicPosts->pluck('id')->toArray());
            }

            $otherPosts = Post::search($q)
                ->query(fn($query) => $query->with(['user.profile', 'topic'])->where('status', 'show')->whereNotIn('id', $excludeIds))
                ->take(5)
                ->get();

            $posts = $results->merge($otherPosts)->take(5);

        } catch (\Exception $e) {
            $topics = Topic::where('name', 'LIKE', "%$q%")->take(5)->get();
            $users = User::where('name', 'LIKE', "%$q%")->with('profile')->take(5)->get();
            $posts = Post::where('content', 'LIKE', "%$q%")->where('status', 'show')->take(5)->get();
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

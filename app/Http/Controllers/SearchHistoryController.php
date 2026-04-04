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
        $posts = Post::with(['user.profile', 'media', 'likes', 'comments', 'favorites'])
            ->where('content', 'LIKE', "%{$keyword}%")
            ->orWhereHas('user.profile', function ($q) use ($keyword) {
                $q->where('display_name', 'LIKE', "%{$keyword}%");
            })
            ->latest()
            ->get();
        $checktopic=false;
        return view('search.result', compact('posts', 'keyword','checktopic'));
    }    
    public function searchTab(Request $request,$type)
    {
         $keyword = $request->input('q');
        if ($type === 'post') {
            $posts = Post::where('content', 'LIKE', "%{$keyword}%")
                        ->orWhereHas('user.profile', function($q) use ($keyword) {
                            $q->where('display_name', 'LIKE', "%{$keyword}%");
                        })->latest()->get();
            $checktopic =false;
            return view('search.partials.post-list', compact('posts', 'keyword','checktopic'));
        } 
        elseif ($type === 'people') {
            // Đã sửa lại truy vấn dùng bảng User
            $users = User::where('name', 'LIKE', "%{$keyword}%")
                        ->orWhereHas('profile', function($q) use ($keyword) {
                            $q->where('display_name', 'LIKE', "%{$keyword}%");
                        })->get();
            return view('search.partials.people-list', compact('users'));
        }
        elseif ($type === 'topic') {
            // Đã sửa lại truy vấn dùng bảng User
            $topics = Topic::where('name', 'LIKE', "%{$keyword}%")
                    ->withCount('posts') 
                    ->latest()    
                    ->get();
            return view('search.partials.topic-list', compact('topics'));
        } 

    }
    public function suggestions(Request $request)
    {
        $q = $request->q;
        return SearchHistory::where('keyword', 'like', "%$q%")
            ->limit(5)
            ->get();
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

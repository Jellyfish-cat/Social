<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SearchService;
use App\Models\SearchHistory;

class SearchHistoryController extends Controller
{
    protected $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $searchHistorys = $this->searchService->getSearchHistoriesPaginated(10);
        return view('admin.searchs', compact('searchHistorys'));
    }

    public function search(Request $request)
    {
        $keyword = $request->input('q');
        $user = auth()->user();
        $referer = $request->headers->get('referer');

        $this->searchService->recordSearchKeyword($keyword, $user);

        if ($user && ($user->role === 'admin' || $user->role === 'moderator')) {
            activity()
                ->event('search_admin')
                ->tap(function ($activity) { $activity->user_id = auth()->id(); })
                ->withProperties(['keyword' => $keyword, 'referer' => $referer])
                ->log('search_admin');

            $res = $this->searchService->searchAdminReferer($keyword, $referer);
            
            if ($res['type'] === 'messages') {
                $messages = $res['data'];
                return view('admin.messages', compact('messages'));
            }
            if ($res['type'] === 'users') {
                $users = $res['data'];
                return view('admin.users', compact('users'));
            }
            if ($res['type'] === 'topics') {
                $topics = $res['data'];
                return view('admin.topics', compact('topics'));
            }
            if ($res['type'] === 'comments') {
                $comments = $res['data'];
                return view('admin.comments', compact('comments'));
            }
            if ($res['type'] === 'conversations') {
                $conversations = $res['data'];
                return view('admin.conversations', compact('conversations'));
            }
            if ($res['type'] === 'searchs') {
                $searchHistorys = $res['data'];
                return view('admin.searchs', compact('searchHistorys'));
            }
            if ($res['type'] === 'reports') {
                $values = $res['data'];
                $tab = 'pending'; 
                $type = 'post';    
                $item = 'report-item';
                $delete = 'btn-delete-report';
                return view('admin.report', compact('values', 'tab', 'type', 'item', 'delete'));
            }
            if ($res['type'] === 'logs') {
                $logs = $res['data'];
                return view('admin.logs', compact('logs'));
            }

            $posts = $res['data'];
            return view('admin.posts', compact('posts'));
        }
        else {
            $posts = $this->searchService->searchNormalUser($keyword, $user);
            $checktopic = false;
            return view('search.result', compact('posts', 'keyword', 'checktopic'));
        }
    }

    public function searchTab(Request $request, $type)
    {
        $keyword = $request->input('q');
        $user = auth()->user();

        $res = $this->searchService->searchTabResult($keyword, $type, $user);

        if ($type === 'post') {
            $posts = $res['posts'] ?? collect();
            $checktopic = false;
            return view('search.partials.post-list', compact('posts', 'keyword', 'checktopic'));
        } 
        elseif ($type === 'people') {
            $users = $res['users'] ?? collect();
            return view('search.partials.people-list', compact('users'));
        }
        elseif ($type === 'topic') {
            $topics = $res['topics'] ?? collect();
            return view('search.partials.topic-list', compact('topics'));
        } 
    }

    public function suggestions(Request $request)
    {
        $q = trim($request->q);
        $user = auth()->user();
        $referer = $request->headers->get('referer') ?? '';

        $suggestions = $this->searchService->getSuggestions($q, $user, $referer);

        return response()->json($suggestions);
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
        $success = $this->searchService->deleteSearchHistory($id, auth()->user());
        if (is_null($success)) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy từ khóa'
            ], 404);
        }

        $searchHistorylist = SearchHistory::latest()->get();
        return response()->json([
            'success' => true,
            'data' => $searchHistorylist,
            'count' => SearchHistory::count(),
            'message' => 'Xóa thành công'
        ]);
    }
}

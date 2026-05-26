<?php

namespace App\Services;

use App\Models\SearchHistory;
use App\Models\Post;
use App\Models\User;
use App\Models\Topic;
use App\Models\Comment;
use App\Models\Message;
use App\Models\Conversation;
use App\Models\Report;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Http;

class SearchService
{
    public function getSearchHistoriesPaginated($perPage = 10)
    {
        return SearchHistory::with(['user.profile'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function recordSearchKeyword($keyword, $user)
    {
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
            
            activity()
                ->event('search')
                ->tap(function ($activity) use ($user) { $activity->user_id = $user->id; })
                ->withProperties(['keyword' => $keyword])
                ->log('search');
        }
    }

    public function searchAdminReferer($keyword, $referer)
    {
        if (str_contains($referer, '/admin/messages')) {
            $messages = Message::where(fn($sub) => $sub->where('content', 'LIKE', "%$keyword%")
                ->orWhereHas('sender.profile', fn($q) => $q->where('display_name', 'LIKE', "%$keyword%"))
                ->orWhereHas('sender', fn($q) => $q->where('name', 'LIKE', "%$keyword%")))
                ->with(['sender.profile', 'conversation'])->orderBy('created_at', 'desc')->paginate(10);
            return ['type' => 'messages', 'data' => $messages];
        }
        if (str_contains($referer, '/admin/users')) {
            $users = User::where(function($q) use ($keyword) {
                $q->where('name', 'LIKE', "%$keyword%")
                  ->orWhere('email', 'LIKE', "%$keyword%")
                  ->orWhereHas('profile', fn($sub) => $sub->where('display_name', 'LIKE', "%$keyword%"));
            })->with(['profile'])->withCount(['posts', 'comments', 'favorites', 'followers', 'following'])
            ->orderBy('created_at', 'desc')->paginate(10);
            return ['type' => 'users', 'data' => $users];
        }
        if (str_contains($referer, '/admin/topics')) {
            $topics = Topic::where('name', 'LIKE', "%$keyword%")
                ->orderBy('created_at', 'desc')->paginate(10);
            return ['type' => 'topics', 'data' => $topics];
        }
        if (str_contains($referer, '/admin/comments')) {
            $comments = Comment::where(fn($sub) => $sub->where('content', 'LIKE', "%$keyword%")
                ->orWhereHas('user.profile', fn($q) => $q->where('display_name', 'LIKE', "%$keyword%"))
                ->orWhereHas('user', fn($q) => $q->where('name', 'LIKE', "%$keyword%")))
                ->with(['user.profile', 'post'])->orderBy('created_at', 'desc')->paginate(10);
            return ['type' => 'comments', 'data' => $comments];
        }
        if (str_contains($referer, '/admin/conversations')) {
            $conversations = Conversation::whereHas('users.profile', function ($q) use ($keyword) {
                $q->where('display_name', 'LIKE', "%$keyword%")->orWhere('name', 'LIKE', "%$keyword%");
            })->with(['users.profile'])->withCount('messages')->orderBy('created_at', 'desc')->paginate(10);
            return ['type' => 'conversations', 'data' => $conversations];
        }
        if (str_contains($referer, '/admin/searchs')) {
            $searchHistorys = SearchHistory::where('keyword', 'LIKE', "%$keyword%")->with(['user.profile'])->orderBy('created_at', 'desc')->paginate(10);
            return ['type' => 'searchs', 'data' => $searchHistorys];
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
            return ['type' => 'reports', 'data' => $values];
        }
        if (str_contains($referer, '/admin/logs')) {
            $logs = ActivityLog::where('event', 'LIKE', "%$keyword%")
                ->orWhere('subject_type', 'LIKE', "%$keyword%")
                ->orWhere('properties', 'LIKE', "%$keyword%")
                ->with('user.profile')
                ->latest()
                ->paginate(20);
            return ['type' => 'logs', 'data' => $logs];
        }

        // Default to posts search for Admin
        $posts = Post::where(function($q) use ($keyword) {
            $q->where('content', 'LIKE', "%$keyword%")
              ->orWhereHas('user.profile', fn($query) => $query->where('display_name', 'LIKE', "%$keyword%"))
              ->orWhereHas('user', fn($query) => $query->where('name', 'LIKE', "%$keyword%"));
        })->with(['user.profile', 'topics', 'media'])->withCount(['comments', 'likes', 'favorites'])
        ->orderBy('created_at', 'desc')->paginate(10);

        return ['type' => 'posts', 'data' => $posts];
    }

    public function searchNormalUser($keyword, $user)
    {
        $posts = Post::search($keyword)->where('status', 'show')->get();
        $posts->load(['user.profile', 'media', 'likes', 'comments', 'favorites', 'topics']);

        if ($user && $posts->isNotEmpty()) {
            try {
                $candPostIds = $posts->pluck('id')->toArray();
                $aiResponse = Http::timeout(3)->post('http://127.0.0.1:8001/api/rank_search_results', [
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

        return $posts;
    }

    public function searchTabResult($keyword, $type, $user)
    {
        if ($type === 'post') { 
            $posts = Post::search($keyword)->where('status', 'show')->get();
            $posts->load(['user.profile', 'media', 'likes', 'comments', 'favorites', 'topics']);

            if ($user && $posts->isNotEmpty()) {
                try {
                    $candPostIds = $posts->pluck('id')->toArray();
                    $aiResponse = Http::timeout(3)->post('http://127.0.0.1:8001/api/rank_search_results', [
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
            return ['posts' => $posts];
        } 
        elseif ($type === 'people') {
            $users = User::search($keyword)->get();
            $users = $users->filter(fn($u) => $u->role === 'user' && $u->status === 'show');
            $users->load(['profile', 'followers']);

            if ($user && $users->isNotEmpty()) {
                try {
                    $candUserIds = $users->pluck('id')->toArray();
                    $aiResponse = Http::timeout(3)->post('http://127.0.0.1:8001/api/rank_search_results', [
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
            return ['users' => $users];
        }
        elseif ($type === 'topic') {
            $topics = Topic::search($keyword)->get()->loadCount('posts');
            return ['topics' => $topics];
        }

        return [];
    }

    public function getSuggestions($q, $user, $referer)
    {
        if ($user && ($user->role === 'admin' || $user->role === 'moderator') && $q) {
            if (str_contains($referer, '/admin/topics')) {
                return ['topics' => Topic::where('name', 'LIKE', "%$q%")->limit(10)->get()];
            } 
            elseif (str_contains($referer, '/admin/users')) {
                return ['users' => User::where('name', 'LIKE', "%$q%")
                    ->orWhere('email', 'LIKE', "%$q%")
                    ->with('profile')->limit(10)->get()];
            } 
            elseif (str_contains($referer, '/admin/posts')) {
                return ['posts' => Post::where('content', 'LIKE', "%$q%")
                    ->orWhereHas('user.profile', fn($query) => $query->where('display_name', 'LIKE', "%$q%"))
                    ->orWhereHas('user', fn($query) => $query->where('name', 'LIKE', "%$q%"))
                    ->with('user.profile')
                    ->limit(10)->get()];
            } 
            elseif (str_contains($referer, '/admin/comments')) {
                return ['comments' => Comment::where('content', 'LIKE', "%$q%")
                    ->orWhereHas('user.profile', fn($query) => $query->where('display_name', 'LIKE', "%$q%"))
                    ->orWhereHas('user', fn($query) => $query->where('name', 'LIKE', "%$q%"))
                    ->with('user.profile')
                    ->limit(10)->get()];
            }
            elseif (str_contains($referer, '/admin/messages')) {
                return ['messages' => Message::where('content', 'LIKE', "%$q%")
                    ->orWhereHas('sender.profile', fn($query) => $query->where('display_name', 'LIKE', "%$q%"))
                    ->orWhereHas('sender', fn($query) => $query->where('name', 'LIKE', "%$q%"))
                    ->with('sender.profile')
                    ->limit(10)->get()];
            }
            elseif (str_contains($referer, '/admin/conversations')) {
                return ['conversations' => Conversation::whereHas('users.profile', function ($sub) use ($q) {
                    $sub->where('display_name', 'LIKE', "%$q%")->orWhere('name', 'LIKE', "%$q%");
                })->with(['users.profile'])->limit(10)->get()];
            }
            elseif (str_contains($referer, '/admin/searchs')) {
                return ['admin_history' => SearchHistory::where('keyword', 'LIKE', "%$q%")->with('user.profile')->limit(10)->get()];
            }
            elseif (str_contains($referer, '/admin/reports')) {
                return ['reports' => Report::where(function($query) use ($q) {
                        $query->where('reason', 'LIKE', "%$q%")
                          ->orWhere('category', 'LIKE', "%$q%")
                          ->orWhereHas('user', fn($u) => $u->where('name', 'LIKE', "%$q%"))
                          ->orWhereHas('user.profile', fn($u) => $u->where('display_name', 'LIKE', "%$q%"));
                    })
                    ->with(['user.profile', 'target'])
                    ->limit(10)->get()];
            }
            elseif (str_contains($referer, '/admin/logs')) {
                return ['logs' => ActivityLog::where('event', 'LIKE', "%$q%")
                    ->orWhere('subject_type', 'LIKE', "%$q%")
                    ->orWhere('properties', 'LIKE', "%$q%")
                    ->with('user.profile')
                    ->limit(10)->get()];
            }

            return [
                'topics' => Topic::where('name', 'LIKE', "%$q%")->limit(5)->get(),
                'users' => User::where('name', 'LIKE', "%$q%")->limit(5)->get()
            ];
        }
        else {
            $histories = collect();

            if ($user && $user->role === 'user') {
                $histories = SearchHistory::where('user_id', $user->id)
                    ->latest('updated_at')
                    ->limit(5)
                    ->get();
            }

            if (!$q) {
                return [
                    'history' => $histories,
                    'topics' => [],
                    'users' => [],
                    'posts' => []
                ];
            }

            $topics = Topic::search($q)->take(5)->get(); 
            $candUserIds = User::search($q)->where('role', 'user')->where('status', 'show')->take(50)->keys()->toArray();
            $candPostIds = Post::search($q)->where('status', 'show')->take(50)->keys()->toArray();
            
            $users = User::whereIn('id', array_slice($candUserIds, 0, 5))->with('profile')->get();
            $posts = Post::whereIn('id', array_slice($candPostIds, 0, 5))
                        ->with(['user.profile', 'topics'])
                        ->orderBy('created_at', 'desc')
                        ->get();

            if ($user && $user->role === 'user') {
                try {
                    $response = Http::timeout(3)->post('http://127.0.0.1:8001/api/rank_search_suggestions', [
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
                } catch (\Exception $e) { }
            }

            return [
                'history' => $histories,
                'topics' => $topics,
                'users' => $users,
                'posts' => $posts
            ];
        }
    }

    public function deleteSearchHistory($id, $user)
    {
        $searchHistory = SearchHistory::find($id);
        if (!$searchHistory) {
            return null;
        }

        if (!in_array($user->role, ['admin', 'moderator']) && $user->id !== $searchHistory->user_id) {
            abort(403, 'Bạn không có quyền');
        }

        activity()
            ->event('delete_search')
            ->tap(function ($activity) use ($user) { $activity->user_id = $user->id; })
            ->withProperties(['keyword' => $searchHistory->keyword])
            ->log('delete_search');

        $searchHistory->delete();
        return true;
    }
}

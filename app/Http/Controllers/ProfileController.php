<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Profile;
use App\Models\User;
use App\Models\Favorite;
use App\Models\Post;
use App\Models\Comment;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function detail($id)
    {
        // Tìm profile theo user_id thay vì profile_id để đồng bộ với các hàm khác
        $profile = Profile::where('user_id', $id)->firstOrFail();
        
        if ($profile->user->status === 'hidden'  && auth()->user()->role !== 'admin') {
            abort(403, 'Tài khoản này đã bị khóa hoặc không tồn tại');
        } 
        $user = $profile->user;
        $posts = $user->posts()
            ->with(['user.profile', 'topics', 'media', 'likes'])
            ->withCount(['comments', 'likes', 'favorites'])
            ->latest()
            ->get();
        // 3. Gợi ý người dùng (Sử dụng Python AI Recommender)
        try {
            $aiResponse = \Illuminate\Support\Facades\Http::timeout(3)->get('http://127.0.0.1:8001/api/user_recommendations', [
                'user_id' => auth()->id() ?: 0
            ]);
            if ($aiResponse->successful()) {
                $aiData = $aiResponse->json();
                $recommendedUserIds = $aiData['recommended_user_ids'] ?? [];
                
                if (!empty($recommendedUserIds)) {
                    $suggestedUsers = User::whereIn('id', $recommendedUserIds)
                        ->with('profile')
                        ->get()
                        ->sortBy(function($u) use ($recommendedUserIds) {
                            return array_search($u->id, $recommendedUserIds);
                        })->values();
                } else {
                    $suggestedUsers = User::where('id', '!=', auth()->id() ?: 0)
                        ->where('role', 'user')
                        ->with('profile')
                        ->limit(5)->get();
                }
            } else {
                throw new \Exception("AI Service Error");
            }
        } catch (\Exception $e) {
            $suggestedUsers = User::where('id', '!=', auth()->id() ?: 0)
                ->where('role', 'user')
                ->with('profile')
                ->limit(5)->get();
        }
        return view('profile.detail', compact('profile','user','posts','suggestedUsers'));
    }
    public function setup($layout = 'layouts.app')
    {
        return view('profile.setup_profile', compact('layout'));
    }

    public function storeSetup(Request $request)
    {
        $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255', 
                'unique:users,name,' . Auth::id(), 
                'regex:/^\S*$/', 
                'alpha_dash' // Chỉ cho phép chữ cái, số, dấu gạch ngang và gạch dưới
            ],
            'display_name' => 'required|max:50',
            'avatar' => 'nullable|image',
            'bio' => 'nullable|max:255'
        ], [
            'name.regex' => 'Tên người dùng không được chứa khoảng trắng.',
            'name.unique' => 'Tên người dùng này đã được sử dụng.',
            'name.alpha_dash' => 'Tên người dùng chỉ được chứa chữ cái, số, dấu gạch ngang và gạch dưới.'
        ]);

        $user = Auth::user();
        
        // Cập nhật trường name (username) trong bảng users
        $user->name = $request->name;
        $user->save();

        $avatarPath = null;
        if($request->hasFile('avatar')){
            $avatarPath = $request->file('avatar')->store('avatars','public');
        }

        // Sử dụng updateOrCreate để tránh lỗi Duplicate entry nếu profile đã tồn tại
        Profile::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'display_name' => $request->display_name,
                'avatar' => $avatarPath ?? Profile::where('user_id', Auth::id())->value('avatar'),
                'bio' => $request->bio
            ]
        );

        session(['show_welcome' => true]);
        return redirect()->route('home');
    }

    public function edit(Request $request, $id): View
    {
        $user = User::findOrFail($id);
        
        // Chỉ cho phép chủ sở hữu hoặc admin chỉnh sửa
        if (auth()->id() !== $user->id && auth()->user()->role !== 'admin') {
            abort(403);
        }

        return view('profile.edit', [
            'user' => $user,
            'profile' => $user->profile
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Xác định user cần cập nhật (nếu là Admin và có truyền ID)
        $user = ($request->has('id') && auth()->user()->role === 'admin') ? User::findOrFail($request->id) : $request->user();
        
        $user->fill($request->safe()->only(['name', 'email']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Chỉ Admin mới được quyền đổi quyền hạn (Role)
        if (auth()->user()->role === 'admin' && $request->has('role')) {
            $user->role = $request->role;
        }

        $user->save();

        // Cập nhật thông tin Profile
        $profileData = $request->safe()->only(['display_name', 'bio']);
        
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $profileData['avatar'] = $avatarPath;
        }

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );

        return Redirect::route('profile.edit', $user->id)->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {

        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        if (auth()->user()->role !== 'admin' && auth()->id() !== $user->id) {
            abort(403, 'Bạn không có quyền');
        }
        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
    public function posts($id)
    {
        $user = User::findOrFail($id);
        if ($user->status === 'hidden' && auth()->user()->role !== 'admin') {
            return response()->json(['posts' => []]); 
        }
        $posts = $user->posts()->where('status','show')
            ->orderBy('pinned', 'desc')
            ->latest()
            ->get();

        return view('profile.partials.post-list', [
            'posts' => $posts,
            'tab' => 'posts'
        ]);
    }

    public function favorites($id)
    {
        $user = User::findOrFail($id);

        $posts = Post::join('favorites', 'posts.id', '=', 'favorites.post_id')
            ->join('users', 'posts.user_id', '=', 'users.id')
            ->where('favorites.user_id', $user->id)
            ->where('posts.status', 'show')
            ->where('users.status', 'show')
            ->orderBy('favorites.created_at', 'desc')
            ->select('posts.*')
            ->get();

        return view('profile.partials.post-list', [
            'posts' => $posts,
            'tab' => 'favorites'
        ]);
    }
    public function comments($id)
    {
        $user = User::findOrFail($id);
        $comments = Comment::where('user_id', $user->id)
            ->where('status', 'show')
            ->whereHas('post', function($q) {
                $q->where('status', 'show')->whereHas('user', function($u) {
                    $u->where('status', 'show');
                });
            })
            ->latest()
            ->get();
        return view('profile.partials.comment-list', compact('comments'));
    }
        public function likes($id)
    {
        $user = User::findOrFail($id);
        $posts = Post::join('like_posts', 'posts.id', '=', 'like_posts.post_id')
            ->join('users', 'posts.user_id', '=', 'users.id')
            ->where('like_posts.user_id', $user->id)
            ->where('posts.status', 'show')
            ->where('users.status', 'show')
            ->orderBy('like_posts.created_at', 'desc')
            ->select('posts.*')
            ->get();

        return view('profile.partials.post-list', [
            'posts' => $posts,
            'tab' => 'likes'
        ]);
    }
}

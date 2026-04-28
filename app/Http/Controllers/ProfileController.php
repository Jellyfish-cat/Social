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
        $posts = $user->posts()->latest()->get();
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
            'display_name'=>'required|max:50',
            'avatar'=>'nullable|image',
            'bio'=>'nullable|max:255'
        ]);

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
        $posts = $user->posts()->where('status','show')
            ->orderBy('pinned', 'desc')
            ->latest()
            ->get();

        return view('profile.partials.post-list', compact('posts'));
    }

    public function favorites($id)
    {
        $user = User::findOrFail($id);

        $posts = Post::whereHas('favorites', function ($q) use ($user) {
            $q->where('user_id', $user->id)->where('status','show');
        })
        ->latest()
        ->get();

        return view('profile.partials.post-list', compact('posts'));
    }
    public function comments($id)
    {
        $user = User::findOrFail($id);
        $comments = Comment::where('user_id', $user->id)
            ->latest()->where('status','show')
            ->get();
        return view('profile.partials.comment-list', compact('comments'));
    }
        public function likes($id)
    {
        $user = User::findOrFail($id);
        $posts = Post::whereHas('likes', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->latest()
        ->get();

        return view('profile.partials.post-list', compact('posts'));
    }
}

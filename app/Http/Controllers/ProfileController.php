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
        try {
            $suggestedResults = collect();
            $excludeUserIds = $user ? array_merge([$user->id], $followingIds) : $followingIds;

            if ($user) {
                // --- Ưu tiên 0: Tương tác cũ (Chỉ cho Logged in) ---
                $interactedUserIdsRaw = collect()
                    ->merge(\App\Models\LikePost::where('user_id', $user->id)->with('post')->get()->pluck('post.user_id'))
                    ->merge(\App\Models\Comment::where('user_id', $user->id)->with('post')->get()->pluck('post.user_id'))
                    ->filter();

                $sortedInteractedIds = $interactedUserIdsRaw->countBy()->sortDesc()->keys()->toArray();

                if (!empty($sortedInteractedIds)) {
                    $tier0 = User::whereIn('id', $sortedInteractedIds)
                        ->with('profile')
                        ->whereNotIn('id', $excludeUserIds)
                        ->get()
                        ->sortBy(function($u) use ($sortedInteractedIds) {
                            return array_search($u->id, $sortedInteractedIds);
                        })
                        ->take(5);

                    $suggestedResults = $suggestedResults->merge($tier0);
                    $excludeUserIds = array_merge($excludeUserIds, $tier0->pluck('id')->toArray());
                }
            }

            // --- Các tầng ưu tiên khác: Topic chung, Bạn chung, Phổ biến ---
            // (Đơn giản hóa cho Guest: chỉ lấy người dùng phổ biến/mới nhất)
            if ($suggestedResults->count() < 5) {
                $tier3 = User::with('profile')
                    ->whereNotIn('id', $excludeUserIds)
                    ->limit(10)
                    ->get();
                $suggestedResults = $suggestedResults->merge($tier3);
            }
            
            $suggestedUsers = $suggestedResults->unique('id')->take(5);

        } catch (\Exception $e) {
            $suggestedUsers = User::with('profile')->limit(5)->get();
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

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
use App\Services\ProfileService;

class ProfileController extends Controller
{
    protected $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Display the user's profile form.
     */
    public function detail($id)
    {
        $profile = $this->profileService->getProfileDetail($id);
        $user = $profile->user;
        $posts = $this->profileService->getProfilePosts($user);
        $suggestedUsers = $this->profileService->getSuggestedUsers(auth()->id());

        return view('profile.detail', compact('profile', 'user', 'posts', 'suggestedUsers'));
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
                'alpha_dash'
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
        
        $user->name = $request->name;
        $user->save();

        $avatarPath = null;
        if($request->hasFile('avatar')){
            $avatarPath = $request->file('avatar')->store('avatars','public');
        }

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
        $user = ($request->has('id') && auth()->user()->role === 'admin') ? User::findOrFail($request->id) : $request->user();
        
        $user->fill($request->safe()->only(['name', 'email']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if (auth()->user()->role === 'admin' && $request->has('role')) {
            $user->role = $request->role;
        }

        $user->save();

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
        $posts = $this->profileService->getUserPosts($id);

        return view('profile.partials.post-list', [
            'posts' => $posts,
            'tab' => 'posts'
        ]);
    }

    public function favorites($id)
    {
        $posts = $this->profileService->getUserFavorites($id);

        return view('profile.partials.post-list', [
            'posts' => $posts,
            'tab' => 'favorites'
        ]);
    }

    public function comments($id)
    {
        $comments = $this->profileService->getUserComments($id);
        return view('profile.partials.comment-list', compact('comments'));
    }

    public function likes($id)
    {
        $posts = $this->profileService->getUserLikes($id);

        return view('profile.partials.post-list', [
            'posts' => $posts,
            'tab' => 'likes'
        ]);
    }
}

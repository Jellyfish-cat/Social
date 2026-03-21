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
        $profile = profile::findorfail($id);
        $user = $profile->user;
        $posts = $user->posts()->latest()->get();
        return view('profile.detail', compact('profile','user','posts'));
    }
     public function setup()
    {
        return view('profile.setup_profile');
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

        Profile::create([
            'user_id'=>Auth::id(),
            'display_name'=>$request->display_name,
            'avatar'=>$avatarPath,
            'bio'=>$request->bio
        ]);

        return redirect()->route('home');
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
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

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
    public function posts($id)
    {
        $user = User::findOrFail($id);

        $posts = $user->posts()
            ->latest()
            ->get();

        return view('profile.partials.post-list', compact('posts'));
    }

    public function favorites($id)
    {
        $user = User::findOrFail($id);

        $posts = Post::whereHas('favorites', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->latest()
        ->get();

        return view('profile.partials.post-list', compact('posts'));
    }
    public function comments($id)
    {
        $user = User::findOrFail($id);
        $comments = Comment::where('user_id', $user->id)
            ->latest()
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

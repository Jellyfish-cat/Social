<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Profile;


class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
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
}

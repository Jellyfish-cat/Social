<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            // 1. Tìm user theo google_id
            $user = User::where('google_id', $googleUser->getId())->first();

            if (!$user) {
                // 2. Nếu chưa có google_id, tìm theo Email
                $user = User::where('email', $googleUser->getEmail())->first();

                if ($user) {
                    $user->update([
                        'google_id' => $googleUser->getId(),
                        'email_verified_at' => $user->email_verified_at ?? now(),
                    ]);
                } else {
                    // 3. Tạo User mới
                    $username = Str::slug($googleUser->getName(), '_');
                    if (User::where('name', $username)->exists()) {
                        $username = $username . '_' . Str::random(4);
                    }

                    $user = User::create([
                        'name' => $username,
                        'email' => $googleUser->getEmail(),
                        'google_id' => $googleUser->getId(),
                        'password' => Hash::make(Str::random(16)), 
                        'status' => 'show', 
                        'email_verified_at' => now(),
                    ]);

                    // QUAN TRỌNG: Để trống display_name để Middleware có thể chặn lại
                    $user->profile()->create([
                        'display_name' => '', 
                    ]);
                }
            }

            Auth::login($user);
            $user->update(['last_login_at' => now()]);
            return redirect()->route('profile.setup');

        } catch (\Exception $e) {
            \Log::error('Google Login Error: ' . $e->getMessage());
            return redirect()->route('login')->withErrors(['email' => 'Có lỗi xảy ra']);
        }
    }
}

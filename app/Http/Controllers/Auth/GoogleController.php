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
                    // Nếu mail đã tồn tại, cập nhật google_id và xác minh email luôn
                    $user->update([
                        'google_id' => $googleUser->getId(),
                        'email_verified_at' => $user->email_verified_at ?? now(),
                    ]);
                } else {
                    // 3. Nếu mail cũng chưa có, tạo User mới và mặc định đã xác minh
                    // Chuyển tên Google thành username không dấu cách
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

                    // Tạo Profile mới
                    $user->profile()->create([
                        'display_name' => $googleUser->getName() ?? $username,
                    ]);
                }
            } elseif ($user->email_verified_at == null) {
                $user->update(['email_verified_at' => now()]);
            }

            Auth::login($user);
            $user->update(['last_login_at' => now()]);
            return redirect()->route('profile.setup');

        } catch (\Exception $e) {
            \Log::error('Google Login Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return redirect()->route('login')->withErrors(['email' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }
}

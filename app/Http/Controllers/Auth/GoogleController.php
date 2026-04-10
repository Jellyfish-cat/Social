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
            $googleUser = Socialite::driver('google')->user();
            
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
                    $user = User::create([
                        'name' => $googleUser->getName(),
                        'email' => $googleUser->getEmail(),
                        'google_id' => $googleUser->getId(),
                        'password' => Hash::make(Str::random(16)), // Mật khẩu ngẫu nhiên
                        'status' => 'show', 
                        'email_verified_at' => now(),
                    ]);

                    // Nếu hệ thống của bạn có bảng Profile, tạo luôn ở đây
                    if (method_exists($user, 'profile')) {
                        $user->profile()->create([
                            'display_name' => $googleUser->getName(),
                        ]);
                    }
                }
            } elseif ($user->email_verified_at == null) {
                // Nếu User đã có google_id nhưng chưa xác minh email (trường hợp hiếm)
                $user->update(['email_verified_at' => now()]);
            }

            Auth::login($user);
            return redirect(route('profile.setup', 'layouts.app_detail'));

        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['email' => 'Có lỗi xảy ra khi đăng nhập Google.']);
        }
    }
}

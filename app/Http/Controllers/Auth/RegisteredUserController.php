<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Tạo username tạm thời từ email (ví dụ: ducp9@gmail.com -> ducp9)
        $username = explode('@', $request->email)[0];
        
        // Nếu username đã tồn tại, thêm chuỗi ngẫu nhiên
        if (User::where('name', $username)->exists()) {
            $username = $username . '_' . \Illuminate\Support\Str::random(4);
        }

        $user = User::create([
            'name' => $username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'show', // Trạng thái mặc định
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('profile.setup', absolute: false));
    }
}

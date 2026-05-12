<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileSetup
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Nếu người dùng đã đăng nhập và chưa có tên hiển thị trong profile
        // Và không phải đang truy cập vào trang setup hoặc logout
        if ($user && !$user->profile?->display_name) {
            if (!$request->is('profile/setup*') && !$request->is('logout')) {
                return redirect()->route('profile.setup');
            }
        }

        return $next($request);
    }
}

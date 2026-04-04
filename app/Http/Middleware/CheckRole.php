<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // chưa login
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // nếu role không nằm trong danh sách cho phép
        if (!in_array($user->role, $roles)) {
            abort(403, 'Bạn không có quyền truy cập');
        }

        return $next($request);
    }
}
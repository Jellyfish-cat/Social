<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;

class HomeController extends Controller
{
    public function index(Request $request, \App\Services\FeedService $feedService, \App\Services\UserSuggestedService $userService)
    {
        $user = auth()->user();
        // Logic ẩn bài sau 1 lần xem: 
     
        $isReload = true;
        if (session('just_posted')) {
            $isReload = false;
        }
        $posts = $feedService->getFeedPosts($user, $isReload);
        $suggestedUsers = $userService->getSuggestedUsers($user);
        
        if (!$user || $user->role === 'user') {
            return view('home', compact('posts', 'suggestedUsers'));
        } elseif ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'moderator') {
            return redirect()->route('admin.reports', 'pending');
        } else {
            return view('home', compact('posts', 'suggestedUsers'));
        }
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class HomeController extends Controller
{
    public function index()
    {
        // Lấy bài viết mới nhất kèm thông tin người dùng và topic
        $posts = Post::with(['user.profile', 'topic', 'comments.user','likes'])
                    ->orderBy('created_at', 'desc')
                    ->get();
        return view('home', compact('posts'));
    }
}

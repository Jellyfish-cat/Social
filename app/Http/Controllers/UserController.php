<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
                $users = User::with(['profile']) 
                ->withCount([
                    'posts',    
                    'comments',
                    'favorites',
                    'followers',
                    'following'  
                ])
                ->orderBy('created_at', 'desc')->where('status', 'show')
                ->paginate(10);
        return view('admin.users', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Topic $topic)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Topic $topic)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Topic $topic)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $users = User::find($id);
        if (auth()->user()->role !== 'admin' && auth()->id() !== $users->id ) {
            abort(403, 'Bạn không có quyền');
        }
        if (!$users) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy topic'
            ], 404);
        }

        $users->delete();
        $userslist = User::latest()->get();
        return response()->json([
            'success' => true,
            'data' => $userslist,
            'count' => User::count(),
            'message' => 'Xóa thành công'
        ]);
    }
}

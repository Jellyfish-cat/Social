<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Report;  
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
        return view('admin.createUser');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,moderator,user',
            'display_name' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // 1. Create User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'status' => 'show',
            'email_verified_at' => now(),
        ]);

        // 2. Handle Avatar Upload
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        // 3. Create Profile
        $profile = $user->profile()->create([
            'display_name' => $request->display_name ?? $request->name,
            'bio' => $request->bio,
            'avatar' => $avatarPath,
        ]);

        // Get the full user object with profile for the response
        $newUser = User::with('profile')->find($user->id);

        return response()->json([
            'success' => true,
            'data' => $newUser,
            'count' => User::count(),
            'message' => 'Người dùng và Profile đã được tạo thành công'
        ]);
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
     public function hide($id)
    {
        $user = User::find($id);
        if (auth()->user()->role !== 'admin' ){
            abort(403, 'Bạn không có quyền');
        }
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy người dùng'
            ], 404);
        }
        
        $user->status = 'hide';
        $user->save();
        Report::create([
            'user_id' => auth()->id(),
            'target_id' => $user->id,
            'target_type' => User::class,
            'category' => 'admin',
            'reason' => 'Admin khóa tài khoản người dùng',
            'status' => 'resolved',
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        $userslist = User::latest()->get();
        return response()->json([
            'success' => true,
            'data' => $userslist,
            'count' => User::count(),
            'message' => 'Đã khóa tài khoản thành công'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Đã có middleware checkRole:admin ở web.php nên không cần check ở đây nữa
        $users = User::find($id);
        if (auth()->user()->role !== 'admin' ){
            abort(403, 'Bạn không có quyền');
        }
        if (!$users) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy topic'
            ], 404);
        }

        Report::where('target_id', $id)->where('target_type', User::class)->delete();
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

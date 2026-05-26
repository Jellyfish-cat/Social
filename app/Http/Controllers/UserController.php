<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Topic;
use Illuminate\Http\Request;
use App\Services\UserService;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $users = $this->userService->getPaginatedUsers(10);
        return view('admin.users', compact('users'));
    }

    public function create()
    {
        return view('admin.createUser');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255', 
                'unique:users,name', 
                'regex:/^\S*$/', 
                'alpha_dash' 
            ],
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,moderator,user',
            'display_name' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'name.regex' => 'Tên người dùng không được chứa khoảng trắng.',
            'name.unique' => 'Tên người dùng này đã được sử dụng.',
            'name.alpha_dash' => 'Tên người dùng chỉ được chứa chữ cái, số, dấu gạch ngang và gạch dưới.'
        ]);

        $newUser = $this->userService->createUser($request->all(), $request->file('avatar'));

        return response()->json([
            'success' => true,
            'data' => $newUser,
            'count' => $this->userService->getUserCount(),
            'message' => 'Người dùng và Profile đã được tạo thành công'
        ]);
    }

    public function show(Topic $topic)
    {
        //
    }

    public function edit(Topic $topic)
    {
        //
    }

    public function update(Request $request, Topic $topic)
    {
        //
    }

    public function hide(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin' ){
            abort(403, 'Bạn không có quyền');
        }

        try {
            $newStatus = $this->userService->toggleUserStatus($id, $request->type, auth()->id());
            
            return response()->json([
                'success' => true,
                'status' => $newStatus,
                'message' => ($newStatus === 'hidden') ? 'Đã khóa tài khoản' : 'Đã mở khóa tài khoản'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy người dùng'
            ], 404);
        }
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin' ){
            abort(403, 'Bạn không có quyền');
        }

        try {
            $userslist = $this->userService->deleteUserPermanently($id);
            
            return response()->json([
                'success' => true,
                'data' => $userslist,
                'count' => $this->userService->getUserCount(),
                'message' => 'Đã xóa vĩnh viễn người dùng và dọn dẹp toàn bộ dữ liệu liên quan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy người dùng'
            ], 404);
        }
    }
}

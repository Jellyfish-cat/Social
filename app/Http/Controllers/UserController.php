<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Report;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

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
                ->orderBy('created_at', 'desc')
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
    public function hide(Request $request, $id)
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
        
        $type = $request->type; // 'hide' hoặc 'show'
        $newStatus = ($type === 'hide') ? 'hidden' : 'show';
        $user->status = $newStatus;
        $user->save();

        if ($newStatus === 'hidden') {
            Post::where('user_id', $user->id)->update(['status' => 'hidden']);
            Comment::where('user_id', $user->id)->update(['status' => 'hidden']);
            
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

            // Gửi mail thông báo
            try {
                $displayName = $user->profile->display_name ?? $user->name;
                Mail::raw("Chào {$displayName},\n\nTài khoản của bạn đã bị khóa do vi phạm các tiêu chuẩn cộng đồng của chúng tôi.\n\nNếu bạn cho rằng đây là một sự nhầm lẫn, vui lòng phản hồi lại email này để được hỗ trợ giải quyết.\n\nTrân trọng,\nĐội ngũ Admin.", function ($message) use ($user) {
                    $message->to($user->email)
                            ->subject('Thông báo khóa tài khoản')
                            ->replyTo(config('mail.from.address'), config('app.name'));
                });
            } catch (\Exception $e) {
                \Log::error("Lỗi gửi mail khóa tài khoản: " . $e->getMessage());
            }
        } else {
            Post::where('user_id', $user->id)
                ->whereDoesntHave('reports', function($q) {
                    $q->where('status', 'resolved');
                })
                ->update(['status' => 'show']);

            Comment::where('user_id', $user->id)
                ->whereDoesntHave('reports', function($q) {
                    $q->where('status', 'resolved');
                })
                ->update(['status' => 'show']);
        }

        return response()->json([
            'success' => true,
            'status' => $newStatus,
            'message' => ($newStatus === 'hidden') ? 'Đã khóa tài khoản' : 'Đã mở khóa tài khoản'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::with(['profile', 'posts.media', 'comments', 'conversations'])->find($id);
        if (auth()->user()->role !== 'admin' ){
            abort(403, 'Bạn không có quyền');
        }
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy người dùng'
            ], 404);
        }

        // 1. Xóa file Avatar
        if ($user->profile && $user->profile->avatar) {
            Storage::disk('public')->delete($user->profile->avatar);
        }

        // 2. Xóa file Media trong các bài viết (Posts)
        foreach ($user->posts as $post) {
            foreach ($post->media as $m) {
                if ($m->file_path) {
                    Storage::disk('public')->delete($m->file_path);
                }
            }
        }

        // 3. Xóa file Media trong các bình luận (Comments)
        foreach ($user->comments as $comment) {
            if ($comment->media_path) {
                Storage::disk('public')->delete($comment->media_path);
            }
        }

        // 4. Chuyển quyền chủ nhóm cho thành viên khác và thông báo
        Conversation::where('creator_id', $id)->update(['creator_id' => null]);
        $groupConvos = Conversation::where('type', 'group')
            ->where('creator_id', $id)
            ->with('users')
            ->get();

        foreach ($groupConvos as $convo) {
            $nextLeader = $convo->users->where('id', '!=', $id)->first();
            if ($nextLeader) {
                $convo->update(['creator_id' => $nextLeader->id]);
                // Gửi thông báo hệ thống vào nhóm
                Message::create([
                    'conversation_id' => $convo->id,
                    'sender_id' => null,
                    'content' => ($nextLeader->profile->display_name ?? $nextLeader->name) . ' đã được chỉ định làm trưởng nhóm mới do chủ nhóm cũ bị xóa.',
                    'type' => 'notification'
                ]);
            } else {
                $convo->update(['creator_id' => null]);
            }
        }

        // 5. Xóa Media của các tin nhắn đã gửi
        $messages = Message::where('sender_id', $id)->with('media')->get();
        foreach ($messages as $msg) {
            foreach ($msg->media as $mm) {
                if ($mm->file_path) {
                    Storage::disk('public')->delete($mm->file_path);
                }
            }
        }

        // 5. Xóa Hội thoại cá nhân (Private Conversations)
        foreach ($user->conversations as $convo) {
            if ($convo->type === 'private') {
                $convoMessages = Message::where('conversation_id', $convo->id)->with('media')->get();
                foreach ($convoMessages as $cm) {
                    foreach ($cm->media as $cmm) {
                        if ($cmm->file_path) {
                            Storage::disk('public')->delete($cmm->file_path);
                        }
                    }
                }
                $convo->delete(); 
            }
      
        }

        Report::where('target_id', $id)->where('target_type', User::class)->delete();
        $user->delete();

        $userslist = User::latest()->get();
        return response()->json([
            'success' => true,
            'data' => $userslist,
            'count' => User::count(),
            'message' => 'Đã xóa vĩnh viễn người dùng và dọn dẹp toàn bộ dữ liệu liên quan'
        ]);
    }
}

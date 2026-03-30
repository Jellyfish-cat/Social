<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class FollowController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store($id)
    {
        try {
        $user = Auth::user();
        $followed = Follow::where('follower_id', $user->id)
                        ->where('following_id', $id)
                        ->exists();
        if(!$followed){
            Follow::create([
                'follower_id' => $user->id,
                'following_id' => $id
            ]);

            if ($id != $user->id) {
                $notification = Notification::create([
                    'user_id' => $id,
                    'content' => '<strong>' . ($user->profile->display_name ?? $user->name ?? 'Một người') . '</strong> đã bắt đầu theo dõi bạn.',
                    'type' => 'follow'
                ]);
                broadcast(new \App\Events\NotificationSent($notification))->toOthers();
            }
         }else{   
            $follow=Follow::where('follower_id', $user->id)
                        ->where('following_id', $id);
            $follow->delete();
        }
        //có bao nhiêu người theo dõi $id (tài khoản đích)
        $Following_count = Follow::where('following_id', $id)->count();
        $follower_count =  Follow::where('follower_id', $user->id)->count();
        return response()->json([
            'success' => true,
            'following_count' => $Following_count,
            'follower_count' => $follower_count
        ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ],500);
    }
    }

    /**
     * Display the specified resource.
     */
    public function detail(request $request,$id)
       {
        $layout = $request->ajax() ? 'layouts.app_detail' : 'layouts.app';
        $type = request()->header('X-Type');
        $follow=collect();
        $user=null;
        if($type == "follower"){
            $user = User::with('followers.profile')->findOrFail($id);
            $follow = $user->followers;
        }
        else if($type == "following"){
            $user = User::with('following.profile')->findOrFail($id);
            $follow = $user->following;
        }
        return view('follow.detail', compact('follow', 'user', 'layout'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Follow $follow)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Follow $follow)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Follow $follow)
    {
        //
    }
}

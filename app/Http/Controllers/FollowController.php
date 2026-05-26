<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\InteractionService;

class FollowController extends Controller
{
    protected $interactionService;

    public function __construct(InteractionService $interactionService)
    {
        $this->interactionService = $interactionService;
    }

    public function index()
    {
        //
    }

    public function create()
    {
        
    }

    public function store($id)
    {
        try {
            $result = $this->interactionService->toggleFollow($id, Auth::user());
            
            return response()->json([
                'success' => true,
                'following_count' => $result['following_count'],
                'follower_count' => $result['follower_count']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function detail(Request $request, $id)
    {
        if (!$request->ajax()) {
            return redirect()->back()->withInput();
        }

        $type = request()->header('X-Type') ?: 'follower';
        $result = $this->interactionService->getFollowDetails($id, $type);
        
        $layout = 'layouts.empty';
        $user = $result['user'];
        $values = $result['values'];
        
        return view('follow.detail', compact('values', 'user', 'layout'));
    }

    public function edit(Follow $follow)
    {
        //
    }

    public function update(Request $request, Follow $follow)
    {
        //
    }

    public function destroy(Follow $follow)
    {
        //
    }
}

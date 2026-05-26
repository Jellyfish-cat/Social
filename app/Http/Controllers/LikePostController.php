<?php

namespace App\Http\Controllers;

use App\Models\LikePost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\InteractionService;

class LikePostController extends Controller
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
        //
    }

    public function store($id)
    {
        try {
            $likePost_count = $this->interactionService->toggleLikePost($id, Auth::user());
            
            return response()->json([
                'success' => true,
                'likePost_count' => $likePost_count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(LikePost $likePost)
    {
        //
    }

    public function edit(LikePost $likePost)
    {
        //
    }

    public function update(Request $request, LikePost $likePost)
    {
        //
    }

    public function destroy(LikePost $likePost)
    {
        //
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\LikeComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\InteractionService;

class LikeCommentController extends Controller
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
            $likeComment_count = $this->interactionService->toggleLikeComment($id, Auth::user());
            
            return response()->json([
                'success' => true,
                'likeComment_count' => $likeComment_count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(LikeComment $likeComment)
    {
        //
    }

    public function edit(LikeComment $likeComment)
    {
        //
    }

    public function update(Request $request, LikeComment $likeComment)
    {
        //
    }

    public function destroy(LikeComment $likeComment)
    {
        //
    }
}

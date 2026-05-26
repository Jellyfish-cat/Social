<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\InteractionService;
use Illuminate\Support\Facades\Log;

class FavoriteController extends Controller
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
            Log::info("Đã nhận được request vào FavoriteController cho Post ID: " . $id);
            
            $this->interactionService->toggleFavorite($id, Auth::user());
        
            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Favorite $favorite)
    {
        //
    }

    public function edit(Favorite $favorite)
    {
        //
    }

    public function update(Request $request, Favorite $favorite)
    {
        //
    }

    public function destroy(Favorite $favorite)
    {
        //
    }
}

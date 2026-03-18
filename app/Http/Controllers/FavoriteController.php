<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($id)
    {
        try {
        \Log::info("Đã nhận được request vào FavoriteController cho Post ID: " . $id);
        $user = Auth::user();
        $favorited = Favorite::where('user_id', $user->id)
                        ->where('post_id', $id)
                        ->exists();
        if(!$favorited){
            Favorite::create([
                'user_id' => $user->id,
                'post_id' => $id
            ]);
        }
        else{   
            $favorite=Favorite::where('user_id', $user->id)
                        ->where('post_id', $id);
            $favorite->delete();
        }
    
        return response()->json([
            'success' => true
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
    public function show(Favorite $favorite)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Favorite $favorite)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Favorite $favorite)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Favorite $favorite)
    {
        //
    }
}

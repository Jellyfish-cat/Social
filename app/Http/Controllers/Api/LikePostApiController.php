<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LikePost;

class LikePostApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
    public function toggle(Request $request)
{
    $like = LikePost::where([
        'user_id' => auth()->id(),
        'post_id' => $request->post_id
    ])->first();

    if ($like) {
        $like->delete();
    } else {
        LikePost::create([
            'user_id' => auth()->id(),
            'post_id' => $request->post_id
        ]);
    }

    return response()->json(['success' => true]);
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
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $topics = Topic::paginate(10)->withQueryString();
    return view('topics.index', compact('topics'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('topics.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $topic = Topic::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);
        return response()->json([
            'success' => true,
            'count' => topic::count(),
            'data' => $topic
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
    public function edit(Request $request, $id)
    {
        $topic = Topic::findOrFail($id);
        $page = $request->page ?? 1;
        return view('topics.edit', compact('topic','page'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $topic = Topic::findOrFail($id);
        $topic->update([
            'name' => $request->name,
            'description' => $request->description
        ]);
        return redirect()->route('topics.index')
                        ->with('success', 'Cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $topic = Topic::find($id);

        if (!$topic) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy topic'
            ], 404);
        }

        $topic->delete();
        $topiclist = topic::latest()->get();
        return response()->json([
            'success' => true,
            'data' => $topiclist,
            'count' => topic::count(),
            'message' => 'Xóa thành công'
        ]);
    }
}

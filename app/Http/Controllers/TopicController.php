<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\post;
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
        $request->validate([
            'name' => 'required|string|max:50'
        ]);

        $topic = Topic::firstOrCreate([
            'name' => strtolower(trim($request->name)),
        ]);

        return response()->json([
            'success' => true,
            'data' => $topic
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $topic = Topic::findOrFail($id);
    $posts = $topic->posts()->latest()->get();
        return view('profile.partials.post-list', compact('posts'));

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
            'name' => $request->name
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
    public function search(Request $request)
    {
        $q = $request->q;
        return Topic::where('name', 'like', "%$q%")
            ->limit(5)
            ->get();
    }
}

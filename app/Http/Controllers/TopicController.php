<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use App\Services\TopicService;

class TopicController extends Controller
{
    protected $topicService;

    public function __construct(TopicService $topicService)
    {
        $this->topicService = $topicService;
    }

    public function index()
    {
        $topics = $this->topicService->getPaginatedTopics(10);
        return view('admin.topics', compact('topics'));
    }

    public function create()
    {
        return view('topics.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50'
        ]);

        $topic = $this->topicService->createTopic($request->all());

        return response()->json([
            'success' => true,
            'data' => $topic
        ]);
    }

    public function show($id)
    {
        $result = $this->topicService->getTopicWithPosts($id);
        $topic = $result['topic'];
        $posts = $result['posts'];
        
        $checktopic= true;
        $display_name = $topic->name;
        return view('search.partials.post-list', compact('posts','checktopic','display_name'));
    }

    public function edit(Request $request, $id)
    {
        $topic = Topic::findOrFail($id);
        $page = $request->page ?? 1;
        return view('topics.edit', compact('topic','page'));
    }

    public function update(Request $request, $id)
    {
        $this->topicService->updateTopic($id, $request->all());
        return redirect()->route('admin.topics')
                        ->with('success', 'Cập nhật thành công!');
    }

    public function destroy(Request $request, $id)
    {
        try {
            $topiclist = $this->topicService->deleteTopic($id, auth()->user());
            
            return response()->json([
                'success' => true,
                'data' => $topiclist,
                'count' => Topic::count(),
                'message' => 'Xóa thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 403);
        }
    }

    public function search(Request $request)
    {
        $q = $request->q;
        return $this->topicService->searchTopics($q);
    }
}

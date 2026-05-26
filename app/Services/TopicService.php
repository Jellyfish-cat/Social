<?php

namespace App\Services;

use App\Models\Topic;

class TopicService
{
    public function __construct()
    {
        //
    }

    public function getAllTopics()
    {
        return Topic::all();
    }

    public function getTopicById($id)
    {
        return Topic::findOrFail($id);
    }

    public function getPaginatedTopics($perPage = 10)
    {
        return Topic::paginate($perPage)->withQueryString();
    }

    public function createTopic($data)
    {
        return Topic::firstOrCreate([
            'name' => strtolower(trim($data['name'])),
        ]);
    }

    public function getTopicWithPosts($id)
    {
        $topic = Topic::findOrFail($id);
        $posts = $topic->posts()
            ->where('status', 'show')
            ->whereHas('user', function($q) {
                $q->where('status', 'show');
            })
            ->latest()
            ->get();
            
        return [
            'topic' => $topic,
            'posts' => $posts
        ];
    }

    public function updateTopic($id, $data)
    {
        $topic = Topic::findOrFail($id);
        $topic->update([
            'name' => $data['name']
        ]);
        return $topic;
    }

    public function deleteTopic($id, $user)
    {
        if (!in_array($user->role, ['admin', 'moderator'])) {
            throw new \Exception('Bạn không có quyền', 403);
        }

        $topic = Topic::find($id);
        if (!$topic) {
            throw new \Exception('Không tìm thấy topic', 404);
        }

        $topic->delete();
        
        return Topic::latest()->get();
    }

    public function searchTopics($keyword)
    {
        return Topic::search($keyword)
            ->take(5)
            ->get();
    }
}

<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Topic;
use App\Models\Media;
use App\Models\Report;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostService
{
    public function __construct()
    {
        //
    }

    public function getPostCount()
    {
        return Post::count();
    }

    public function getAllPosts()
    {
        return Post::all();
    }

    public function getAdminPosts($perPage = 10)
    {
        return Post::with(['user.profile', 'topics', 'media'])
                    ->withCount(['comments', 'likes', 'favorites'])
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage);
    }

    public function createPost($data, $userId, $files = null)
    {
        DB::beginTransaction();
        try {
            $post = new Post();
            $post->user_id = $userId;
            $post->content = $data['content'] ?? null;
            $post->is_comment_enabled = $data['is_comment_enabled'] ?? 1;
            
            // Xử lý ghim duy nhất 1 bài viết
            if (isset($data['pinned'])) {
                Post::where('user_id', $userId)->update(['pinned' => 0]);
                $post->pinned = 1;
            } else {
                $post->pinned = 0;
            }

            $post->save();

            $topicIds = isset($data['topic_ids']) && $data['topic_ids'] ? explode(',', $data['topic_ids']) : [];
            $newTopics = isset($data['new_topics']) && $data['new_topics'] ? explode(',', $data['new_topics']) : [];

            foreach ($newTopics as $name) {
                if (!$name) continue;
                $topic = Topic::firstOrCreate(['name' => strtolower(trim($name))]);
                $topicIds[] = $topic->id;
            }

            $topicIds = array_unique(array_filter($topicIds));
            if (count($topicIds) > 3) {
                DB::rollBack();
                throw new \Exception('Chỉ tối đa 3 chủ đề');
            }

            $post->topics()->sync($topicIds);

            if ($files) {
                foreach ($files as $file) {
                    $safeName = $file->hashName();
                    $path = $file->storeAs('posts/media', $safeName, 'public');

                    $media = new Media();
                    $media->post_id = $post->id;
                    $media->file_path = $path;
                    $media->type = str_contains($file->getMimeType(), 'video') ? 'video' : 'image';
                    $media->save();
                }
            }

            DB::commit();
            return $post;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getPostDetail($id, $user)
    {
        $post = Post::withCount('comments')->with([
            'user.profile', 'media', 'topics', 'likes', 'favorites',
            'comments' => function ($query) {
                $query->whereNull('parent_comment_id')
                ->where('status', 'show')
                ->whereHas('user', fn($q) => $q->where('status', 'show'))
                ->with(['user.profile', 'replies' => function($r) {
                    $r->where('status', 'show')->whereHas('user', fn($u) => $u->where('status', 'show'))->with('user.profile');
                }])->latest();
            }
        ])->findOrFail($id);

        if ($post->status !== 'show' && (!$user || $user->role !== 'admin')) {
            abort(403, 'Bài viết đã khóa hoặc không tồn tại');
        }

        return $post;
    }

    public function getPostForEdit($id, $user)
    {
        $post = Post::with('media', 'topics')->findOrFail($id);
        if (!in_array($user->role, ['admin', 'moderator']) && $user->id !== $post->user_id) {
            abort(403, 'Bạn không có quyền');
        }
        return $post;
    }

    public function updatePost($id, $data, $user, $files = null)
    {
        $post = Post::findOrFail($id);

        if (!in_array($user->role, ['admin', 'moderator']) && $user->id !== $post->user_id) {
            abort(403, 'Bạn không có quyền');
        }

        if (isset($data['pinned'])) {
            Post::where('user_id', $post->user_id)->where('id', '!=', $id)->update(['pinned' => 0]);
            $post->pinned = 1;
        } else {
            $post->pinned = 0;
        }

        $post->content = $data['content'] ?? null;
        $post->is_comment_enabled = isset($data['is_comment_enabled']);

        $topicIds = array_filter(explode(',', $data['topic_ids'] ?? ''));
        foreach (array_filter(explode(',', $data['new_topics'] ?? '')) as $name) {
            $topicIds[] = Topic::firstOrCreate(['name' => strtolower(trim($name))])->id;
        }
        $post->topics()->sync(array_slice(array_unique($topicIds), 0, 3));

        $post->updated_at = now();
        $post->save(); 

        if (!empty($data['deleted_media_ids'])) {
            $ids = explode(',', $data['deleted_media_ids']);
            $medias = $post->media()->whereIn('id', $ids)->get();
            foreach ($medias as $media) {
                Storage::disk('public')->delete($media->file_path);
                $media->delete();
            }
        }

        if ($files) {
            foreach ($files as $file) {
                $path = $file->storeAs('posts', $file->hashName(), 'public');
                $type = str_contains($file->getMimeType(), 'video') ? 'video' : 'image';
                Media::create([
                    'post_id'   => $post->id,
                    'file_path' => $path,
                    'type'      => $type
                ]);
            }
        }

        return clone $post->load(['topics', 'media']);
    }

    public function deletePost($id, $user)
    {
        $post = Post::findOrFail($id);

        if (!in_array($user->role, ['admin', 'moderator']) && $user->id !== $post->user_id) {
            abort(403, 'Bạn không có quyền');
        }

        $medias = Media::where('post_id', $id)->get();
        foreach ($medias as $m) {
            Storage::disk('public')->delete($m->file_path);
            $m->delete();
        }

        Report::where('target_id', $id)->where('target_type', Post::class)->delete();
        $post->delete();

        return Post::latest()->get();
    }

    public function getPostsByTopic($topicId)
    {
        return Post::whereHas('topics', function($q) use ($topicId) {
                        $q->where('topic_id', $topicId);
                    })
                    ->whereHas('user', function($q) {
                        $q->where('status', 'show');
                    })
                    ->with(['user.profile', 'media'])
                    ->orderBy('created_at', 'desc')->where('status', 'show')
                    ->get();
    }
}

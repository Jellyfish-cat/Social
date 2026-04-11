<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class Post extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'user_id',
        'content',
        'is_comment_enabled',
        'pinned',
        'status',
        'shared_post_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function media()
    {
        return $this->hasMany(Media::class);
    }

    public function likes()
    {
        return $this->hasMany(LikePost::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
    public function topics()
    {
        return $this->belongsToMany(Topic::class);
    }
    // Share bài viết
    public function sharedPost()
    {
        return $this->belongsTo(Post::class, 'shared_post_id');
    }
    public function likedUsers()
    {
        return $this->belongsToMany(User::class, 'like_posts', 'post_id', 'user_id');
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'topic_ids' => $this->topics->pluck('id')->toArray(),
            'user_id' => $this->user_id,
            'content' => $this->content,
            'user' => [
                'name' => $this->user->name ?? '',
            ],
            'topics' => $this->topics->pluck('name')->toArray(),
        ];
    }
}


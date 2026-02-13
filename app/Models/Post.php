<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Post extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'topic_id',
        'content',
        'is_comment_enabled',
        'pinned',
        'shared_post_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
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

    // Share bài viết
    public function sharedPost()
    {
        return $this->belongsTo(Post::class, 'shared_post_id');
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Comment extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'post_id',
        'parent_comment_id',
        'content',
        'status',
        'media_path'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_comment_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_comment_id');
    }

    public function likes()
    {
        return $this->hasMany(LikeComment::class);
    }
    public function isImage()
    {
        $ext = strtolower(pathinfo($this->media_path, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    public function isVideo()
    {
        $ext = strtolower(pathinfo($this->media_path, PATHINFO_EXTENSION));
        return in_array($ext, ['mp4', 'webm', 'ogg']);
    }
    public function likedUsers()
    {
        return $this->belongsToMany(User::class, 'like_comments', 'comment_id', 'user_id');
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = [
        'post_id',
        'type',
        'file_path',
        'thumbnail',
        'duration'
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function views()
    {
        return $this->hasMany(VideoView::class);
    }
}


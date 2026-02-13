<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoView extends Model
{
    protected $fillable = [
        'user_id',
        'media_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->belongsTo(Media::class);
    }
}


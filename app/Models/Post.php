<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Post extends Model
{
    use HasFactory, Searchable, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['content', 'status', 'pinned', 'is_comment_enabled', 'updated_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Tùy chỉnh dữ liệu trước khi lưu vào log
     */
    public function tapActivity(\Spatie\Activitylog\Models\Activity $activity, string $eventName)
    {
        if ($eventName === 'updated' || $eventName === 'created') {
            $properties = $activity->properties->toArray();
            $properties['attributes']['topic_list'] = $this->topic_list;
            $activity->properties = collect($properties);
        }
    }

    public function getTopicListAttribute()
    {
        return $this->topics->pluck('name')->implode(', ');
    }

    protected $fillable = [
        'user_id',
        'content',
        'is_comment_enabled',
        'pinned',
        'status',
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
            'status' => $this->status,
            'created_at' => $this->created_at->timestamp,
        ];
    }

    public function reports()
    {
        return $this->morphMany(Report::class, 'target');
    }
}


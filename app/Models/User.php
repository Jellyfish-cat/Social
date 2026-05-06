<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Scout\Searchable;


use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, Searchable, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'role', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

     protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'google_id',
        'email_verified_at',
        'last_login_at',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
    ];
    protected $appends = ['display_name'];

    public function getDisplayNameAttribute()
    {
        return $this->profile->display_name ?? $this->name;
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likedPosts()
    {
        return $this->hasMany(LikePost::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function followers()
    {
        return $this->belongsToMany(
            User::class,
            'follows',
            'following_id',
            'follower_id'
        );
    }

    public function following()
    {
        return $this->belongsToMany(
            User::class,
            'follows',
            'follower_id',
            'following_id'
        );
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->profile->display_name ?? $this->name,
            'avatar' => $this->profile->avatar ?? 'default-avatar.png',
            'role' => $this->role,
            'status' => $this->status,
        ];
    }

    /**
     * Configure Meilisearch settings
     */
    protected function makeAllSearchableUsing($query)
    {
        return $query->with('profile');
    }
}

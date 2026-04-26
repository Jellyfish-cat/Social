<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class Message extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'status',
        'content',
        'type'
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
    public function media()
    {
        return $this->hasMany(MessageMedia::class);
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'content' => $this->content,
            'created_at' => $this->created_at->timestamp,
        ];
    }
}


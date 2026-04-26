<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'status', 'name', 'avatar', 'createUser'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'createUser');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'conversation_users')->withPivot('deleted_at');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }
}


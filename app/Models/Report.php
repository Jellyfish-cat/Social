<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{

    protected $fillable = [
        'user_id',
        'target_id',
        'target_type',
        'category',
        'reason',
        'status',
        'admin_note',
        'resolved_by',
        'resolved_at'
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_DISMISSED = 'dismissed';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function target()
    {
        return $this->morphTo();
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}


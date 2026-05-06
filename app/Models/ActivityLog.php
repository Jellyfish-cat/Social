<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as SpatieActivity;

class ActivityLog extends SpatieActivity
{
    protected $table = 'activity_log'; // Tên bảng cấu hình ở migration

    const UPDATED_AT = null;

    // Định nghĩa lại quan hệ với User (thay thế cho causer đa hình)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function booted()
    {
        static::saving(function ($activity) {
            // Map causer_id to user_id if it's set by the package
            if (isset($activity->causer_id)) {
                $activity->user_id = $activity->causer_id;
            }
            
            // Remove columns that don't exist in our custom schema
            unset($activity->causer_id);
            unset($activity->causer_type);
            unset($activity->log_name);
            unset($activity->description);
        });
    }
}

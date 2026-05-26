<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogService
{
    public function getLogsPaginated(array $filters, $perPage = 20)
    {
        $query = ActivityLog::with('user')->latest();

        if (!empty($filters['role'])) {
            $query->whereHas('user', function($q) use ($filters) {
                $q->where('role', $filters['role']);
            });
        }

        if (!empty($filters['event'])) {
            $query->where('event', $filters['event']);
        }

        if (!empty($filters['subject'])) {
            if ($filters['subject'] === 'Search') {
                $query->where('event', 'like', '%search%');
            } else {
                $query->where('subject_type', 'like', '%' . $filters['subject']);
            }
        }

        if (!empty($filters['batch'])) {
            $query->where('batch_uuid', $filters['batch']);
        }

        return $query->paginate($perPage);
    }
}

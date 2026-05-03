<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        if ($request->filled('role')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('role', $request->role);
            });
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('subject')) {
            if ($request->subject === 'Search') {
                $query->where('event', 'like', '%search%');
            } else {
                $query->where('subject_type', 'like', '%' . $request->subject);
            }
        }

        if ($request->filled('batch')) {
            $query->where('batch_uuid', $request->batch);
        }

        $logs = $query->paginate(20);
        return view('admin.logs', compact('logs'));
    }

}

<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Post;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($tab)
    {
        // Default to post reports for the initial page load
        $values = Report::with(['user.profile', 'target'])
            ->where('target_type', 'App\Models\Post')->where('status',$tab)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        $type = 'post';
        
        return view('admin.report', compact('values', 'type','tab'));
        }
    
    /**
     * Fetch reports by type for tabs (AJAX).
     */
    public function reportTab(Request $request, $type, $tab)
    {
        $targetTypeMap = [
            'post' => 'App\Models\Post',
            'people' => 'App\Models\User',
            'comment' => 'App\Models\Comment', 
        ];

        $targetType = $targetTypeMap[$type] ?? 'App\Models\Post';

        $values = Report::with(['user.profile', 'target'])
            ->where('target_type', $targetType)->where('status',$tab)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.partials.report-list', compact('values', 'type','tab'));
    }

    /**
     * Show the form for creating a new resource (returns the modal view).
     */
    public function create(Request $request)
    {
        $target_id = $request->query('target_id');
        $target_type = $request->query('target_type', 'post');
        
        return view('report.modal', compact('target_id', 'target_type'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'target_id' => 'required|integer',
            'target_type' => 'required|string',
            'reason' => 'required|string|max:1000',
        ]);

        $targetType = $request->target_type;
        $validTypes = [
            'post' => 'App\Models\Post',
            'comment' => 'App\Models\Comment',
            'user' => 'App\Models\User'
        ];

        // Format target_type to Full Model Class 
        // if short form like 'post', 'comment', 'user' is passed
        $mapKey = strtolower($targetType);
        if (array_key_exists($mapKey, $validTypes)) {
            $targetType = $validTypes[$mapKey];
        }

        $report = Report::create([
            'user_id' => Auth::id(),
            'target_id' => $request->target_id,
            'target_type' => $targetType,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Report submitted successfully.'),
        ]);
    }
    public function destroy( $id)
    {
        $report = Report::find($id);
        if (auth()->user()->role !== 'admin' && auth()->id() !== $report->user_id) {
            abort(403, 'Bạn không có quyền');
        }
        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy báo cáo'
            ], 404);
        }

        $report->delete();
        $reportlist = Report::where('status','pending')->latest()->get();
        return response()->json([
            'success' => true,
            'data' => $reportlist,
            'count' => Report::where('status', 'pending')->count(),
            'message' => 'Xóa thành công'
        ]);
    }
        public function check( $id, $tab)
        {
            $report = Report::find($id);
            if (auth()->user()->role !== 'admin' && auth()->id() !== $report->user_id) {
                abort(403, 'Bạn không có quyền');
            }
            if (!$report) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy báo cáo'
                ], 404);
            }
            $reportlist='';
            if($tab==='pending'){
            $report->update([
                'status' => 'approved'
            ]);
            $target = $report->target;
            if ($target) {
                $target->status = 'hidden';
                $target->save();
            }
             $reportlist = Report::where('status','pending')->latest()->get();
            }
            elseif($tab==='approved'){
                $report->delete();
            $target = $report->target;
            if ($target) {
                $target->status = 'show';
                $target->save();
            }
             $reportlist = Report::where('status','approved')->latest()->get();
            }
            return response()->json([
                'success' => true,
                'data' => $reportlist,
                'count' => Report::where('status', 'pending')->count(),
                'message' => 'Xóa thành công'
            ]);
        }
}

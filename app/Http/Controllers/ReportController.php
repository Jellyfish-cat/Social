<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReportService;
use App\Models\Report;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index($tab = 'pending')
    {
        if (!in_array($tab, ['pending', 'resolved', 'dismissed'])) {
            $tab = 'pending';
        }
        $type = 'post';
        $item = 'reports-item';
        $delete = 'btn-delete';

        $values = $this->reportService->getReportsPaginated($tab, $type);

        if ($tab !== 'pending') {
            $itemMap = [
                'post' => 'posts-item reports-item',
                'people' => 'user-item reports-item',
                'comment' => 'comments-item reports-item',
                'message' => 'message-item reports-item'
            ];

            $item = $itemMap[$type] ?? 'posts-item';
        }
        
        return view('admin.report', compact('values', 'type', 'tab', 'item', 'delete'));
    }
    
    /**
     * Fetch reports by type for tabs (AJAX).
     */
    public function reportTab(Request $request, $type, $tab = 'pending')
    {
        $tab = $request->query('status', $tab);
        if (!in_array($tab, ['pending', 'resolved', 'dismissed'])) {
            $tab = 'pending';
        }
        
        $values = $this->reportService->getReportsPaginated($tab, $type);

        $itemMap = [
            'post' => 'posts-item reports-item',
            'people' => 'user-item reports-item',
            'comment' => 'comments-item reports-item',
            'message' => 'message-item reports-item'
        ];

        $item = $itemMap[$type] ?? 'posts-item';
        $delete = 'btn-delete';
        
        return view('admin.partials.report-list', compact('values', 'type', 'tab', 'item', 'delete'));
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
            'category' => 'required|string',
            'reason' => 'nullable|string|max:1000',
        ]);

        $this->reportService->createReport($request->all(), auth()->id());

        return response()->json([
            'success' => true,
            'message' => __('Report submitted successfully.'),
        ]);
    }

    public function destroy($id)
    {
        $success = $this->reportService->deleteReport($id, auth()->user());
        if (!$success) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy báo cáo'], 404);
        }

        $reportlist = Report::where('status', 'pending')->latest()->get();
        return response()->json([
            'success' => true,
            'data' => $reportlist,
            'count' => Report::where('status', 'pending')->count(),
            'message' => 'Xóa thành công'
        ]);
    }

    public function check(Request $request, $id)
    {
        $action = $request->input('action'); 
        if (!in_array($action, ['hide', 'restore', 'dismiss'])) {
            return response()->json([
                'success' => false,
                'message' => 'Action không hợp lệ'
            ], 400);
        }

        $reportlist = $this->reportService->checkReport($id, $action, auth()->id());

        if (is_null($reportlist)) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy báo cáo'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $reportlist,
            'count' => Report::where('status', 'pending')->count(),
            'message' => 'Xử lý thành công'
        ]);
    }
}

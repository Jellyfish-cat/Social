<?php

namespace App\Http\Controllers;
use App\Models\Report;
use App\Models\Post;
use App\Models\Comment;
use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Events\NotificationSent;
use Illuminate\Support\Facades\Mail;
use App\Jobs\ProcessReportAction;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($tab = 'pending')
    {
        if (!in_array($tab, ['pending', 'resolved', 'dismissed'])) {
            $tab = 'pending';
        }
        $targetType = 'App\Models\Post';
        $type = 'post';
        $item = 'report-item';
        $delete = 'btn-delete-report';

        // Default to post reports for the initial page load
        if ($tab === 'pending') {
            $values = Report::selectRaw('target_type, target_id, count(id) as total_reports, max(created_at) as last_reported_at, max(category) as category, max(reason) as reason, max(id) as id, max(status) as status')
                ->where('target_type', $targetType)
                ->where('status', 'pending')
                ->groupBy('target_type', 'target_id')
                ->orderBy('last_reported_at', 'desc')
                ->paginate(10);
                
            $values->getCollection()->each(function ($report) {
                $modelClass = $report->target_type;
                if(class_exists($modelClass)) {
                    $query = $modelClass::query();
                    if ($modelClass === 'App\Models\Message') {
                        $query->with('conversation');
                    }
                    $report->setRelation('target', $query->find($report->target_id));
                }
            });
        } else {
            $values = Report::with(['user.profile', 'target' => function($morphTo) {
                    $morphTo->morphWith([
                        Message::class => ['conversation'],
                    ]);
                }])
                ->where('target_type', $targetType)
                ->where('status', $tab)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $itemMap = [
                'post' => 'post-item report-item',
                'people' => 'user-item report-item',
                'comment' => 'comment-item report-item',
                'message' => 'message-item report-item'
            ];
            $deleteMap = [
                'post' => 'btn-delete',
                'people' => 'btn-delete-user',
                'comment' => 'btn-delete-comment',
                'message' => 'btn-delete-message'
            ];

            $item = $itemMap[$type] ?? 'post-item';
            $delete = $deleteMap[$type] ?? 'btn-delete';
        }
        
        $type = 'post';
       
        return view('admin.report', compact('values', 'type','tab','item','delete'));
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
        
        $targetTypeMap = [
            'post' => 'App\Models\Post',
            'people' => 'App\Models\User',
            'comment' => 'App\Models\Comment', 
            'message' => 'App\Models\Message',
        ];
        $targetType = $targetTypeMap[$type] ?? 'App\Models\Post';
        $item='report-item';
        $delete='btn-delete-report';
        if ($tab === 'pending') {
            $values = Report::selectRaw('target_type, target_id, count(id) as total_reports, max(created_at) as last_reported_at, max(category) as category, max(reason) as reason, max(id) as id, max(status) as status')
                ->where('target_type', $targetType)
                ->where('status', 'pending')
                ->groupBy('target_type', 'target_id')
                ->orderBy('last_reported_at', 'desc')
                ->paginate(10);
            
            // Eager load Target for each group (map the target relation to the first report instance or custom)
            $values->getCollection()->each(function ($report) {
                // Laravel morphTo requires full models. When using selectRaw, we don't have proper model fields for relations to work smoothly right out of the box unless we fetch targets manually.
                // We'll hydrate the target manually
                $modelClass = $report->target_type;
                if(class_exists($modelClass)) {
                    $query = $modelClass::query();
                    if ($modelClass === 'App\Models\Message') {
                        $query->with('conversation');
                    }
                    $report->setRelation('target', $query->find($report->target_id));
                }
            });
        } else {
            $values = Report::with(['user.profile', 'target'])
                ->where('target_type', $targetType)
                ->where('status', $tab)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
                
            $itemMap = [
                'post' => 'post-item report-item',
                'people' => 'user-item report-item',
                'comment' => 'comment-item report-item',
                'message' => 'message-item report-item'
            ];
            $deleteMap = [
                'post' => 'btn-delete',
                'people' => 'btn-delete-user',
                'comment' => 'btn-delete-comment',
                'message' => 'btn-delete-message'
            ];
            $item = $itemMap[$type] ?? 'post-item';
            $delete = $deleteMap[$type] ?? 'btn-delete';
        }

        return view('admin.partials.report-list', compact('values', 'type','tab','item','delete'));
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

        $targetType = $request->target_type;
        $validTypes = [
            'post' => 'App\Models\Post',
            'comment' => 'App\Models\Comment',
            'user' => 'App\Models\User',
            'message' => 'App\Models\Message'
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
            'category' => $request->category,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        // Nếu là báo cáo tin nhắn và người dùng cho phép xem hội thoại
        if ($mapKey === 'message' && $request->allow_view) {
            $message = Message::find($request->target_id);
            if ($message && $message->conversation_id) {
                Conversation::where('id', $message->conversation_id)->update(['allow_view' => true]);
            }
        }

        // Thông báo đến role=admin và moderator khi có report đến
        $staff = User::whereIn('role', ['admin', 'moderator'])->get();
        $reporterName = Auth::user()->name ?? 'Người dùng';
        $targetName = 'nội dung';
        if ($mapKey === 'post') $targetName = 'bài viết';
        if ($mapKey === 'comment') $targetName = 'bình luận';
        if ($mapKey === 'user') $targetName = 'người dùng';
        if ($mapKey === 'message') $targetName = 'tin nhắn';

        foreach ($staff as $member) {
            $notification = Notification::create([
                'user_id' => $member->id,
                'content' => "<strong>{$reporterName}</strong> đã gửi một báo cáo mới về {$targetName}. report:{$mapKey}:{$request->target_id}",
                'type' => 'report'
            ]);
            broadcast(new NotificationSent($notification))->toOthers();
        }

        return response()->json([
            'success' => true,
            'message' => __('Report submitted successfully.'),
        ]);
    }

    public function destroy($id)
    {
        $report = Report::find($id);
        if (!$report) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy báo cáo'], 404);
        }

        // Quyền: Người tạo báo cáo hoặc Admin/Mod (CheckRole middleware đã xử lý role, nhưng cho shared destroy ta vẫn cần check)
        if (auth()->user()->role !== 'admin' && auth()->id() !== $report->user_id) {
            abort(403, 'Bạn không có quyền');
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
    public function check(Request $request, $id)
    {
        $report = Report::find($id);

        if (!$report) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy báo cáo'], 404);
        }

        // Đã có middleware checkRole:admin,moderator ở web.php nên không cần check ở đây nữa

        $action = $request->input('action'); 
        if (!in_array($action, ['hide', 'restore', 'dismiss'])) {
            return response()->json([
                'success' => false,
                'message' => 'Action không hợp lệ'
            ], 400);
        }

        $relatedReports = Report::where('target_id', $report->target_id)
            ->where('target_type', $report->target_type);

        // 1. Cập nhật trạng thái báo cáo và đối tượng chính NGAY LẬP TỨC
        $statusMap = [
            'hide' => 'resolved',
            'restore' => 'dismissed',
            'dismiss' => 'dismissed'
        ];

        $relatedReports->update([
            'status' => $statusMap[$action],
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        $target = $report->target;
        if ($target && $action !== 'dismiss') {
            $newStatus = ($action === 'hide') ? 'hidden' : 'show';
            $target->update(['status' => $newStatus]);
        }

        // 2. Đẩy các xử lý nặng (ẩn hàng loạt bài viết con, gửi mail, thông báo) vào Queue
        ProcessReportAction::dispatch($action, $report->target_type, $report->target_id, $report->id, auth()->id());

        $reportlist = Report::selectRaw('
                target_type, 
                target_id, 
                count(id) as total_reports, 
                max(created_at) as last_reported_at, 
                max(category) as category, 
                max(reason) as reason, 
                max(id) as id,
                max(status) as status
            ')
            ->where('status', 'pending')
            ->groupBy('target_type', 'target_id')
            ->orderBy('last_reported_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reportlist,
            'count' => Report::where('status', 'pending')->count(),
            'message' => 'Xử lý thành công'
        ]);
    }
}

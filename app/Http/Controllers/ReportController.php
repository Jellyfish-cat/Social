<?php

namespace App\Http\Controllers;
use App\Models\Report;
use App\Models\Post;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Events\NotificationSent;
use Illuminate\Support\Facades\Mail;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($tab)
    {
         $item='report-item';
         $delete='btn-delete-report';
        // Default to post reports for the initial page load
        if ($tab === 'pending') {
            $values = Report::selectRaw('target_type, target_id, count(id) as total_reports, max(created_at) as last_reported_at, max(category) as category, max(reason) as reason, max(id) as id')
                ->where('target_type', 'App\Models\Post')
                ->where('status', 'pending')
                ->groupBy('target_type', 'target_id')
                ->orderBy('last_reported_at', 'desc')
                ->paginate(10);
                
            $values->getCollection()->each(function ($report) {
                $modelClass = $report->target_type;
                if(class_exists($modelClass)) {
                    $report->setRelation('target', $modelClass::find($report->target_id));
                }
            });
        } else {
            $values = Report::with(['user.profile', 'target'])
                ->where('target_type', 'App\Models\Post')->where('status',$tab)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $itemMap = [
                'post' => 'post-item',
                'people' => 'user-item',
                'comment' => 'comment-item'
            ];
            $deleteMap = [
                'post' => 'btn-delete',
                'people' => 'btn-delete-user',
                'comment' => 'btn-delete-comment'
            ];

            $item = $itemMap['post'];
            $delete = $deleteMap['post'];
        }
        
        $type = 'post';
       
        return view('admin.report', compact('values', 'type','tab','item','delete'));
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
        $item='report-item';
        $delete='btn-delete-report';
        if ($tab === 'pending') {
            $values = Report::selectRaw('target_type, target_id, count(id) as total_reports, max(created_at) as last_reported_at, max(category) as category, max(reason) as reason, max(id) as id')
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
                    $report->setRelation('target', $modelClass::find($report->target_id));
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
                'comment' => 'comment-item report-item'
            ];
            $deleteMap = [
                'post' => 'btn-delete',
                'people' => 'btn-delete-user',
                'comment' => 'btn-delete-comment'
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
            'category' => $request->category,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        // Thông báo đến role=admin khi có report đến
        $admins = User::where('role', 'admin')->get();
        $reporterName = Auth::user()->name ?? 'Người dùng';
        $targetName = 'nội dung';
        if ($mapKey === 'post') $targetName = 'bài viết';
        if ($mapKey === 'comment') $targetName = 'bình luận';
        if ($mapKey === 'user') $targetName = 'người dùng';

        foreach ($admins as $admin) {
            $notification = Notification::create([
                'user_id' => $admin->id,
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
    public function check(Request $request, $id)
    {
        $report = Report::find($id);

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy báo cáo'
            ], 404);
        }

        if (auth()->user()->role !== 'admin' && auth()->id() !== $report->user_id) {
            abort(403, 'Bạn không có quyền');
        }

        $action = $request->input('action'); 
        if (!in_array($action, ['hide', 'restore'])) {
            return response()->json([
                'success' => false,
                'message' => 'Action không hợp lệ'
            ], 400);
        }

        $relatedReports = Report::where('target_id', $report->target_id)
            ->where('target_type', $report->target_type);
        $target = $report->target;

        // 1. Xác định chủ sở hữu và thông tin chuẩn bị cho thông báo
        $owner = ($report->target_type === User::class) ? $target : ($target->user ?? null);
        
        if ($owner) {
            $displayName = $owner->profile->display_name ?? $owner->name ?? 'Bạn';
            $typeLabel = 'nội dung';
            $preview = '';
            $email='';
            if ($report->target_type === Post::class) {
                $typeLabel = 'bài viết';
                $preview = ' có nội dung: "' . \Illuminate\Support\Str::limit($target->content, 40) . '"';
            } elseif ($report->target_type === Comment::class) {
                $typeLabel = 'bình luận';
                $preview = ' có nội dung: "' . \Illuminate\Support\Str::limit($target->content, 40) . '"';
            } elseif ($report->target_type === User::class) {
                $typeLabel = 'tài khoản';
                $email ='chúng tôi đã gửi mail cho bạn, hãy kiểm tra email để biết thêm chi tiết';
            }

            // 2. Thực hiện hành động HIDE
            if ($action === 'hide') {
                if ($target) {
                    $target->status = 'hidden';
                    $target->save();
                }
                $relatedReports->update([
                    'status' => 'resolved',
                    'resolved_by' => auth()->id(),
                    'resolved_at' => now(),
                ]);

                // Gửi mail nếu là tài khoản bị khóa
                if ($report->target_type === User::class) {
                    try {
                        Mail::raw("Chào {$displayName},\n\nTài khoản của bạn đã bị khóa do vi phạm các tiêu chuẩn cộng đồng của chúng tôi.\n\nNếu bạn cho rằng đây là một sự nhầm lẫn, vui lòng phản hồi lại email này để được hỗ trợ giải quyết.\n\nTrân trọng,\nĐội ngũ Admin.", function ($message) use ($owner) {
                            $message->to($owner->email)
                                    ->subject('Thông báo khóa tài khoản')
                                    ->replyTo(config('mail.from.address'), config('app.name'));
                        });
                    } catch (\Exception $e) {
                        \Log::error("Lỗi gửi mail khóa tài khoản: " . $e->getMessage());
                    }
                }

                $notif = Notification::create([
                    'user_id' => $owner->id,
                    'content' => "Chào <strong>{$displayName}</strong>, {$typeLabel} của bạn{$preview} đã bị ẩn do vi phạm tiêu chuẩn cộng đồng. {$email}",
                    'type' => ($report->target_type === User::class) ? 'account_locked' : 'system'
                ]);
                broadcast(new NotificationSent($notif))->toOthers();
            }

            // 3. Thực hiện hành động RESTORE
            if ($action === 'restore') {
                if ($target) {
                    $target->status = 'show';
                    $target->save();
                }
                $relatedReports->update([
                    'status' => 'dismissed',
                    'resolved_by' => null,
                    'resolved_at' => null,
                ]);

                $notif = Notification::create([
                    'user_id' => $owner->id,
                    'content' => "Chào <strong>{$displayName}</strong>, {$typeLabel} của bạn{$preview} đã được khôi phục.",
                    'type' => 'system'
                ]);
                broadcast(new NotificationSent($notif))->toOthers();
            }
        }

        $reportlist = Report::selectRaw('
                target_type, 
                target_id, 
                count(id) as total_reports, 
                max(created_at) as last_reported_at, 
                max(category) as category, 
                max(reason) as reason, 
                max(id) as id
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

<?php

namespace App\Services;

use App\Models\Report;
use App\Models\Message;
use App\Models\Conversation;
use App\Models\User;
use App\Models\Notification;
use App\Events\NotificationSent;
use App\Jobs\ProcessReportAction;

class ReportService
{
    public function getReportsPaginated($tab, $type)
    {
        $targetTypeMap = [
            'post' => 'App\Models\Post',
            'people' => 'App\Models\User',
            'comment' => 'App\Models\Comment', 
            'message' => 'App\Models\Message',
        ];
        $targetType = $targetTypeMap[$type] ?? 'App\Models\Post';

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
        }

        return $values;
    }

    public function createReport(array $data, $userId)
    {
        $targetType = $data['target_type'];
        $validTypes = [
            'post' => 'App\Models\Post',
            'comment' => 'App\Models\Comment',
            'user' => 'App\Models\User',
            'message' => 'App\Models\Message'
        ];

        $mapKey = strtolower($targetType);
        if (array_key_exists($mapKey, $validTypes)) {
            $targetType = $validTypes[$mapKey];
        }

        $report = Report::create([
            'user_id' => $userId,
            'target_id' => $data['target_id'],
            'target_type' => $targetType,
            'category' => $data['category'],
            'reason' => $data['reason'] ?? null,
            'status' => 'pending',
        ]);

        if ($mapKey === 'message' && !empty($data['allow_view'])) {
            $message = Message::find($data['target_id']);
            if ($message && $message->conversation_id) {
                Conversation::where('id', $message->conversation_id)->update(['allow_view' => true]);
            }
        }

        $staff = User::whereIn('role', ['admin', 'moderator'])->get();
        $reporter = User::find($userId);
        $reporterName = $reporter->name ?? 'Người dùng';
        
        $targetName = 'nội dung';
        if ($mapKey === 'post') $targetName = 'bài viết';
        if ($mapKey === 'comment') $targetName = 'bình luận';
        if ($mapKey === 'user') $targetName = 'người dùng';
        if ($mapKey === 'message') $targetName = 'tin nhắn';

        foreach ($staff as $member) {
            $notification = Notification::create([
                'user_id' => $member->id,
                'content' => "<strong>{$reporterName}</strong> đã gửi một báo cáo mới về {$targetName}. report:{$mapKey}:{$data['target_id']}",
                'type' => 'report'
            ]);
            broadcast(new NotificationSent($notification))->toOthers();
        }

        return $report;
    }

    public function deleteReport($id, $user)
    {
        $report = Report::find($id);
        if (!$report) {
            return false;
        }

        if ($user->role !== 'admin' && $user->id !== $report->user_id) {
            abort(403, 'Bạn không có quyền');
        }   

        $report->delete();
        return true;
    }

    public function checkReport($id, $action, $userId)
    {
        $report = Report::find($id);

        if (!$report) {
            return null;
        }

        $relatedReports = Report::where('target_id', $report->target_id)
            ->where('target_type', $report->target_type);

        $statusMap = [
            'hide' => 'resolved',
            'restore' => 'dismissed',
            'dismiss' => 'dismissed'
        ];

        $relatedReports->update([
            'status' => $statusMap[$action],
            'resolved_by' => $userId,
            'resolved_at' => now(),
        ]);

        $target = $report->target;
        if ($target && $action !== 'dismiss') {
            $newStatus = ($action === 'hide') ? 'hidden' : 'show';
            $target->update(['status' => $newStatus]);
        }

        ProcessReportAction::dispatch($action, $report->target_type, $report->target_id, $report->id, $userId);

        return Report::selectRaw('
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
    }
}

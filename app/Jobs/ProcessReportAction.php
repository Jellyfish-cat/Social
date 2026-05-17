<?php

namespace App\Jobs;

use App\Models\Report;
use App\Models\Post;
use App\Models\Comment;
use App\Models\User;
use App\Models\Message;
use App\Models\Notification;
use App\Events\NotificationSent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ProcessReportAction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $action;
    protected $targetType;
    protected $targetId;
    protected $reportId;
    protected $moderatorId;

    /**
     * Create a new job instance.
     * 
     * @param string $action 'hide', 'restore', 'dismiss'
     * @param string $targetType Class name of the target
     * @param int $targetId ID of the target
     * @param int|null $reportId ID of the report (if any)
     * @param int|null $moderatorId ID of the moderator
     */
    public function __construct($action, $targetType, $targetId, $reportId = null, $moderatorId = null)
    {
        $this->action = $action;
        $this->targetType = $targetType;
        $this->targetId = $targetId;
        $this->reportId = $reportId;
        $this->moderatorId = $moderatorId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $action = $this->action;
        $targetType = $this->targetType;
        $targetId = $this->targetId;
        
        $target = $targetType::find($targetId);
        if (!$target) return;

        // 1. Nếu từ UserController (không có reportId) và là hành động hide
        // Tạo một bản ghi report admin để lưu log nếu cần (tùy nhu cầu của bạn)
        if ($this->reportId === null && $action === 'hide') {
            Report::updateOrCreate(
                [
                    'target_id' => $targetId,
                    'target_type' => $targetType,
                    'category' => 'admin'
                ],
                [
                    'user_id' => $this->moderatorId,
                    'reason' => 'Admin xử lý trực tiếp từ quản trị người dùng',
                    'status' => 'resolved',
                    'resolved_by' => $this->moderatorId,
                    'resolved_at' => now(),
                ]
            );
        }

        // 2. Xử lý ẩn/hiện HÀNG LOẠT (Hệ quả)
        if ($action === 'hide') {
            if ($targetType === User::class) {
                Post::where('user_id', $targetId)->update(['status' => 'hidden']);
                Comment::where('user_id', $targetId)->update(['status' => 'hidden']);
            } elseif ($targetType === Post::class) {
                Comment::where('post_id', $targetId)->update(['status' => 'hidden']);
            }
        } elseif ($action === 'restore') {
            if ($targetType === User::class) {
                // Chỉ hiện lại những bài viết/comment KHÔNG bị report resolved khác chặn
                Post::where('user_id', $targetId)
                    ->whereDoesntHave('reports', function($q) {
                        $q->where('status', 'resolved');
                    })
                    ->update(['status' => 'show']);

                Comment::where('user_id', $targetId)
                    ->whereDoesntHave('reports', function($q) {
                        $q->where('status', 'resolved');
                    })
                    ->update(['status' => 'show']);
            } elseif ($targetType === Post::class) {
                Comment::where('post_id', $targetId)->update(['status' => 'show']);
            }
        }

        // 3. Xác định chủ sở hữu để gửi thông báo/mail
        $owner = ($targetType === User::class) ? $target : ($target->user ?? null);
        if (!$owner) return;

        $displayName = $owner->profile->display_name ?? $owner->name ?? 'Bạn';

        if ($action === 'hide') {
            // Gửi Email nếu là khóa User
            if ($targetType === User::class) {
                try {
                    Mail::raw("Chào {$displayName},\n\nTài khoản của bạn đã bị khóa do vi phạm các tiêu chuẩn cộng đồng của chúng tôi.\n\nNếu bạn cho rằng đây là một sự nhầm lẫn, vui lòng phản hồi lại email này để được hỗ trợ giải quyết.\n\nTrân trọng,\nĐội ngũ Admin.", function ($message) use ($owner) {
                        $message->to($owner->email)
                                ->subject('Thông báo khóa tài khoản')
                                ->replyTo(config('mail.from.address'), config('app.name'));
                    });
                } catch (\Exception $e) {
                    \Log::error("Lỗi gửi mail khóa tài khoản: " . $e->getMessage());
                }

                $notif = Notification::create([
                    'user_id' => $owner->id,
                    'content' => "Chào <strong>{$displayName}</strong>, tài khoản của bạn đã bị khóa do vi phạm tiêu chuẩn cộng đồng.",
                    'type' => 'account_locked'
                ]);
                broadcast(new NotificationSent($notif));
            } else {
                // Thông báo cho Post/Comment/Message
                $typeLabelMap = [Post::class => 'bài viết', Comment::class => 'bình luận', Message::class => 'tin nhắn'];
                $typeLabel = $typeLabelMap[$targetType] ?? 'nội dung';
                $preview = ' có nội dung: "' . Str::limit($target->content ?? '', 40) . '"';

                $notif = Notification::create([
                    'user_id' => $owner->id,
                    'content' => "Chào <strong>{$displayName}</strong>, {$typeLabel} của bạn{$preview} đã bị ẩn do vi phạm tiêu chuẩn cộng đồng.",
                    'type' => 'system'
                ]);
                broadcast(new NotificationSent($notif));
            }
        } elseif ($action === 'restore') {
            $typeLabelMap = [User::class => 'tài khoản', Post::class => 'bài viết', Comment::class => 'bình luận', Message::class => 'tin nhắn'];
            $typeLabel = $typeLabelMap[$targetType] ?? 'nội dung';
            $preview = ($targetType !== User::class) ? ' có nội dung: "' . Str::limit($target->content ?? '', 40) . '"' : '';

            $notif = Notification::create([
                'user_id' => $owner->id,
                'content' => "Chào <strong>{$displayName}</strong>, {$typeLabel} của bạn{$preview} đã được khôi phục.",
                'type' => 'system'
            ]);
            broadcast(new NotificationSent($notif));
        }
    }
}

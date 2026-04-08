@if($notifications->where('is_read', false)->count() > 0)
<div class="px-3 py-2 bg-light border-bottom text-end">
    <form action="{{ route('notifications.readAll') }}" method="POST" class="m-0">
        @csrf
        <button type="submit" class="btn btn-sm btn-link text-decoration-none p-0 fw-semibold">
            Đánh dấu tất cả đã đọc
        </button>
    </form>
</div>
@endif

@if($notifications->count() > 0)
    <div class="list-group list-group-flush">
        @foreach($notifications as $notification)
            @php
                $bgClass = $notification->is_read ? 'bg-white' : 'bg-light';
                $displayContent = preg_replace('/post:\d+/', '', $notification->content);
                $icon = 'bi-bell';
                $iconColor = 'text-primary';
                $href = '#';
                $openPostClass = '';
                $openPostAttrs = '';

                if($notification->type === 'like') {
                    $icon = 'bi-heart-fill';
                    $iconColor = 'text-danger';
                    if (str_contains($notification->content, 'post:')) {
                        preg_match('/post:(\d+)/', $notification->content, $matches);
                        $postId = $matches[1] ?? null;
                        if ($postId) {
                            $href = route('posts.detail', $postId);
                        }
                    }
                } elseif($notification->type === 'comment') {
                    $icon = 'bi-chat-dots-fill';
                    $iconColor = 'text-success';
                    $displayContent = preg_replace('/comment:\d+/', '', $notification->content);
                    if (str_contains($notification->content, 'comment:')) {
                        preg_match('/comment:(\d+)/', $notification->content, $matches);
                        $commentId = $matches[1] ?? null;
                        if ($commentId) {
                            $commentObj = \App\Models\Comment::find($commentId);
                            if ($commentObj) {
                                $href = '#';
                                $openPostClass = 'open-post';
                                $openPostAttrs = 'data-id="' . $commentObj->post_id . '" data-scroll-comment-id="' . $commentId . '" data-action="reply"';
                            }
                        }
                    }
                } elseif($notification->type === 'follow') {
                    $icon = 'bi-person-plus-fill';
                    $iconColor = 'text-info';
                    $displayContent = preg_replace('/follow:\d+/', '', $notification->content);
                    if (str_contains($notification->content, 'follow:')) {
                        preg_match('/follow:(\d+)/', $notification->content, $matches);
                        $userid = $matches[1] ?? null;
                        if ($userid) {
                            $href = route('profile.detail', $userid);
                        }
                    } 
                } elseif($notification->type === 'likecomment') {
                    $icon = 'bi-heart-fill';
                    $iconColor = 'text-danger';
                    $displayContent = preg_replace('/likecomment:\d+/', '', $notification->content);
                    if (str_contains($notification->content, 'likecomment:')) {
                        preg_match('/likecomment:(\d+)/', $notification->content, $matches);
                        $commentId = $matches[1] ?? null;
                        if ($commentId) {
                            $commentObj = \App\Models\Comment::find($commentId);
                            if ($commentObj) {
                                $href = '#';
                                $openPostClass = 'open-post';
                                $openPostAttrs = 'data-id="' . $commentObj->post_id . '" data-scroll-comment-id="' . $commentId . '" data-action="reply"';
                            }
                        }
                    }
                } elseif($notification->type === 'report' && auth()->user()->role === 'admin') {
                    $icon = 'bi-flag-fill';
                    $iconColor = 'text-danger';
                    $displayContent = preg_replace('/report:([a-z]+:)?\d+/', '', $notification->content);
                    if (str_contains($notification->content, 'report:')) {
                        preg_match('/report:(?:([a-z]+):)?(\d+)/', $notification->content, $matches);
                        $type = $matches[1] ?: 'comment';
                        $targetId = $matches[2] ?? null;
                        if ($targetId) {
                            if ($type === 'comment') {
                                $commentObj = \App\Models\Comment::find($targetId);
                                if ($commentObj) {
                                    $href = '#';
                                    $openPostClass = 'open-post';
                                    $openPostAttrs = 'data-id="' . $commentObj->post_id . '" data-scroll-comment-id="' . $targetId . '" data-action="reply"';
                                }
                            } elseif ($type === 'post') {
                                $href = route('posts.detail', $targetId);
                            } elseif ($type === 'user') {
                                $href = route('profile.detail', $targetId);
                            }
                        }
                    }
                }
            @endphp
            
            <a href="{{ $href }}" {!! $openPostAttrs !!} class="list-group-item list-group-item-action p-3 notification-item border-0 border-bottom {{ $bgClass }} {{ $openPostClass }}" 
                 data-notification-id="{{ $notification->id }}"
                 data-id="{{ $notification->id }}" 
                 data-is-read="{{ $notification->is_read ? 'true' : 'false' }}"
                 style="cursor: pointer; transition: background-color 0.2s;">
                <div class="d-flex align-items-center">
                    <div class="icon-circle {{ $iconColor }} bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; min-width: 48px; background-color: rgba(var(--bs-{{ str_replace('text-', '', $iconColor) }}-rgb), 0.1);">
                        <i class="bi {{ $icon }} fs-5"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="mb-1 {{ $notification->is_read ? 'text-secondary' : 'fw-semibold text-dark' }}" style="font-size: 0.95rem;">
                            {!! $displayContent !!}
                        </p>
                        <small class="text-muted d-flex align-items-center" style="font-size: 0.8rem;">
                            <i class="bi bi-clock me-1"></i>
                            {{ $notification->created_at->diffForHumans() }}
                        </small>
                    </div>
                    @if(!$notification->is_read)
                        <div class="unread-dot bg-primary rounded-circle ms-3" style="width: 10px; height: 10px; flex-shrink: 0;"></div>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
@else
    <div class="text-center p-5">
        <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mt-3 mb-0 fs-6">Bạn chưa có báo nào mới.</p>
    </div>
@endif

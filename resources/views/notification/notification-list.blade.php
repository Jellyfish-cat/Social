@extends('layouts.app')

@section('content')
<div class="row justify-content-center mt-4">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 d-flex justify-content-between align-items-center">
                <h4 class="fw-bold mb-0">Thông báo của bạn</h4>
                @if($notifications->where('is_read', false)->count() > 0)
                <form action="{{ route('notifications.readAll') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        <i class="bi bi-check2-all me-1"></i> Đánh dấu tất cả đã đọc
                    </button>
                </form>
                @endif
            </div>

            <div class="card-body p-0">
                @if($notifications->count() > 0)
                    <div class="list-group list-group-flush rounded-bottom-4">
                        @foreach($notifications as $notification)
                            @php
                                $bgClass = $notification->is_read ? 'bg-white' : 'bg-light';
                                $displayContent = preg_replace('/post:\d+/', '', $notification->content);
                                $icon = 'bi-bell';
                                $iconColor = 'text-primary';
                                $href = '#';
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
                                } elseif($notification->type === 'follow') {
                                    $icon = 'bi-person-plus-fill';
                                    $iconColor = 'text-info';
                                }
                            @endphp
                            
                            <a href ="{{$href}}"class="list-group-item list-group-item-action p-3 {{ $bgClass }} notification-item" 
                                 data-id="{{ $notification->id }}" 
                                 data-is-read="{{ $notification->is_read ? 'true' : 'false' }}"
                                 style="cursor: pointer; transition: background-color 0.2s;">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle {{ $iconColor }} bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; min-width: 48px; background-color: rgba(var(--bs-{{ str_replace('text-', '', $iconColor) }}-rgb), 0.1);">
                                        <i class="bi {{ $icon }} fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-1 {{ $notification->is_read ? 'text-secondary' : 'fw-semibold text-dark' }}">
                                            {!! $displayContent !!}
                                        </p>
                                        <small class="text-muted d-flex align-items-center">
                                            <i class="bi bi-clock me-1"></i>
                                            {{ $notification->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    @if(!$notification->is_read)
                                        <div class="unread-dot bg-primary rounded-circle ms-3" style="width: 10px; height: 10px;"></div>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                    
                    <div class="d-flex justify-content-center p-3">
                        {{ $notifications->links() }}
                    </div>
                @else
                    <div class="text-center p-5">
                        <i class="bi bi-bell-slash text-muted" style="font-size: 4rem;"></i>
                        <p class="text-muted mt-3 fs-5">Bạn chưa có thông báo nào.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationItems = document.querySelectorAll('.notification-item');
    
    notificationItems.forEach(item => {
        item.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const isRead = this.getAttribute('data-is-read') === 'true';
            
            if (!isRead) {
                fetch(`/notifications/read/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        // Mark visually as read
                        this.classList.remove('bg-light');
                        this.classList.add('bg-white');
                        this.setAttribute('data-is-read', 'true');
                        
                        const textElem = this.querySelector('p');
                        if(textElem) {
                            textElem.classList.remove('fw-semibold', 'text-dark');
                            textElem.classList.add('text-secondary');
                        }
                        
                        const dot = this.querySelector('.unread-dot');
                        if (dot) dot.remove();
                        
                        // Update badge
                        const badge = document.getElementById('global-noti-badge');
                        if (badge) {
                            let count = parseInt(badge.textContent);
                            if (count > 0) {
                                count--;
                                badge.textContent = count;
                                if (count === 0) {
                                    badge.classList.add('d-none');
                                }
                            }
                        }
                    }
                })
                .catch(error => console.error('Error marking notification as read:', error));
            }
        });
    });
});
</script>
@endsection

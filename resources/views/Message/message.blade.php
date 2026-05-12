    @php
        $otherUser = $conversation->users->where('id', '!=', auth()->id())->first();
    @endphp
    @if($conversation->type === 'private')
    <div class="msg-header d-flex flex-column align-items-center  py-4 border-bottom">

        {{-- Avatar --}}
        <img src="{{ asset('storage/' . (optional($otherUser->profile)->avatar ?? 'default-avatar.png')) }}"
            class="rounded-circle mb-2"
            style="width:80px;height:80px;object-fit:cover;">

        {{-- Name --}}
        <div class="fw-semibold fs-5">
            {{ optional($otherUser->profile)->display_name ?? $otherUser->name }}
        </div>

        {{-- Username / info --}}
        <div class="text-muted small mb-3">
            {{ $otherUser->name ?? '' }}
        </div>

        {{-- Button --}}
        @if(isset($otherUser) && !in_array($otherUser->role, ['admin', 'moderator']))
        <a href="{{ route('profile.detail', $otherUser->id) }}" 
        class="btn btn-light rounded-pill px-3">
            Xem trang cá nhân
        </a>
        @endif
    </div>
    @else
     <div class="msg-header d-flex flex-column align-items-center  py-4 border-bottom">

        {{-- Avatar --}}
        <img src="{{ asset('storage/' . ($conversation->avatar ?? 'default-avatar.png')) }}"
            class="rounded-circle mb-2"
            style="width:80px;height:80px;object-fit:cover;">

        {{-- Name --}}
        <div class="fw-semibold fs-5">
            {{ $conversation->name }}
            <i class="bi bi-people text-muted ms-1" title="Nhóm"></i>
        </div>

        {{-- Username / info --}}
        <div class="text-muted small mb-3">
            @foreach ($conversation->users as $item)
                <a href="{{ route('profile.detail', $item->id) }}">{{ optional($item->profile)->display_name ?? $item->name ?? $item->email }}</a>{{ !$loop->last ? ',' : '' }}
            @endforeach
        </div>

    </div>
    @endif
    @php $prevMsg = null; @endphp
    @foreach($messages as $msg)
        @if($msg->type === 'notification')
            <div class="msg-system-notification d-flex justify-content-center my-3" data-id="{{ $msg->id }}" data-time="{{ $msg->created_at->timestamp }}">
                <span class="px-3 py-1 bg-light text-muted small rounded-pill border" style="font-size: 11px;">
                    {{ $msg->content }}
                </span>
            </div>
            @continue
        @endif

        @php
            $showTime = false;
            if (!$prevMsg || $msg->created_at->diffInMinutes($prevMsg->created_at) > 60) {
                $showTime = true;
            }
            $prevMsg = $msg;
        @endphp

        @if($showTime)
            <div class="msg-bubble-time">
                {{ $msg->created_at->format('H:i d/m') }}
            </div>
        @endif

        <div class="msg-bubble-row {{ $msg->sender_id == auth()->id() ? 'mine' : '' }}" 
             id="message-{{ $msg->id }}"
             data-id="{{ $msg->id }}" 
             data-time="{{ $msg->created_at->timestamp }}">

            @if($msg->status !== 'unsend')
                <div class="dropdown order-1">
                    <i class="bi bi-three-dots cursor-pointer text-muted p-1" data-bs-toggle="dropdown" 
                       style="font-size: 0.8rem; margin: 0 5px;" title="Tùy chọn"></i>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                        @if($msg->sender_id == auth()->id() && (!$otherUser || $otherUser->status !== 'hidden'))
                            <li>
                                <a class="dropdown-item small" href="javascript:void(0)" onclick="unsendMsg(this, {{ $msg->id }})">
                                    <i class="bi bi-arrow-counterclockwise me-2"></i>Thu hồi
                                </a>
                            </li>
                        @endif
                        @if($msg->sender_id != auth()->id())
                            <li>
                                <a class="dropdown-item small text-danger open-report" href="javascript:void(0)" 
                                   data-type="message" data-id="{{ $msg->id }}">
                                    <i class="bi bi-flag me-2"></i>Báo cáo
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            @endif

            @if($msg->sender_id != auth()->id())
                <img src="{{ asset('storage/' . (optional($msg->sender->profile)->avatar ?? 'default-avatar.png')) }}"
                    class="msg-bubble-avatar">
            @endif

            <div class="msg-bubble {{ $msg->sender_id == auth()->id() ? 'mine' : 'theirs' }}">
                @if($conversation->type === 'group' && $msg->sender_id != auth()->id())
                    <div class="fw-bold mb-1" style="font-size: 0.75rem; color: #65676b;">
                        {{ optional($msg->sender->profile)->display_name ?? $msg->sender->name }}
                    </div>
                @endif
                @if($msg->status === 'unsend')
                    <div class="text-muted small fst-italic">Tin nhắn đã bị thu hồi</div>
                @elseif($msg->status === 'hidden')
                  <div class="text-muted small fst-italic">Tin nhắn đã bị xóa do vi phạm</div>
                    @else
                    @foreach($msg->media as $media)
                        @if($media->type === 'image')
                            <a href="{{ asset('storage/' . $media->file_path) }}" 
                                data-fancybox="gallery-{{Auth::id() }}">
                                <img src="{{ asset('storage/' . $media->file_path) }}" style="max-width:200px;border-radius:12px;" class="mb-1">
                            </a>
                        @elseif($media->type === 'video')
                            <a href="{{ asset('storage/' . $media->file_path) }}" 
                                    class="video-link"
                                data-fancybox="gallery-{{Auth::id() }}">
                                <video src="{{ asset('storage/' . $media->file_path) }}" style="max-width:200px;border-radius:12px;" controls class="mb-1"></video>
                            </a>
                        @endif
                    @endforeach
                    @if($msg->content)
                        <div style="word-break: break-all;">
                            {!! preg_replace(
                                '/(https?:\/\/[^\s]+)/', 
                                '<a href="$1" target="_blank" class="text-decoration-underline" style="color: inherit;">$1</a>', 
                                e($msg->content) 
                            ) !!}
                        </div>
                         <div class="msg-time">
                            {{ $msg->created_at->format('H:i d/m') }}
                        </div>
                    @endif
                @endif
            </div>

        </div>

    @endforeach
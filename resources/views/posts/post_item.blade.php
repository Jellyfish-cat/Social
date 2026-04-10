<div class="card post-card shadow-none post-item" data-id="{{ $post->id }}">
    <div class="p-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('profile.detail', $post->user->id) }}" >
            <img src="{{ asset('storage/' . ($post->user->profile->avatar ?? 'default-avatar.png')) }}" class="avatar-circle">
            </a>
            <div>
                <div class="fw-bold small">{{ $post->user->profile->display_name ?? $post->user->name }}</div>
                
                <div class="text-muted" style="font-size: 13px;">
                @if($post->topics->count())
                    @foreach($post->topics as $topic)
                        <span class="badge bg-secondary me-1">{{ $topic->name }}</span>
                    @endforeach
                @else
                    <span>Chung</span>
                @endif
            </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            @if($post->user->id != Auth::id())
                @if($post->user->followers->contains(Auth::id()))
                    <button class="btn btn-light rounded-3 fw-semibold px-3 btn-sm follow-btn" 
                    data-id="{{$post->user->id}}">Đang Theo dõi</button>
                @else
                    <button class="btn btn-primary rounded-3 fw-semibold px-3 btn-sm follow-btn" 
                    data-id="{{$post->user->id}}">Theo dõi</button>
                @endif
            @endif

            <div class="dropdown">
                <i class="bi bi-three-dots cursor-pointer" data-bs-toggle="dropdown"></i>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    @if($post->user->id === Auth::id() || auth()->user()->role === 'admin')
                    <li><a class="dropdown-item small btn-edit-post" href="javascript:void(0)" data-id="{{ $post->id }}">Chỉnh sửa</a></li>
                    <li>
                        <a class="dropdown-item small btn-delete" data-id="{{ $post->id }}">
                            Xóa
                    </a>
                    </li>
                    @endif
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item small text-danger open-report" data-type="post" data-id="{{ $post->id }}">Báo cáo</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="post-media-wrapper">
        @if($post->media->count() > 0)
            <div id="carousel-{{ $post->id }}" class="carousel slide" data-bs-ride="false">
                <div class="carousel-indicators">
                    @foreach($post->media as $index => $m)
                        <button type="button" data-bs-target="#carousel-{{ $post->id }}" data-bs-slide-to="{{ $index }}" class="{{ $index == 0 ? 'active' : '' }}"style="width: 8px; height: 8px; border-radius: 50%; margin-bottom:10px"></button>
                    @endforeach
                </div>
                <div class="carousel-inner">
                    @foreach($post->media as $index => $m)
                        <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                            @if($m->type == 'image')
                            <a href="{{ asset('storage/' . $m->file_path) }}" 
                                data-fancybox="gallery-{{ $post->id }}">
                                <img src="{{ asset('storage/' . $m->file_path) }}" class="d-block w-100"></a>
                        @else
                            <a href="{{ asset('storage/' . $m->file_path) }}" 
                            class="video-link"
                            data-fancybox="gallery-{{ $post->id }}" >
                            <video controls muted playsinline preload="metadata" class="feed-video video"
                              src="{{ asset('storage/' . $m->file_path) }}" type="video/mp4">
                            </video></a>
                        @endif
                        </div>
                    @endforeach
                </div>
                @if($post->media->count() > 1)
                    <button class="carousel-control-prev" type="button" data-bs-target="#carousel-{{ $post->id }}" data-bs-slide="prev">
                       <i class="bi bi-chevron-left bg-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"></i>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carousel-{{ $post->id }}" data-bs-slide="next">
                        <i class="bi bi-chevron-right bg-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"></i>
                    </button>
                @endif
            </div>
        @endif
    </div>
    
    <div class="p-3 pb-0">
        <div class="post-caption small mb-1">
            <span class="fw-bold me-1">{{ $post->user->profile->display_name ?? $post->user->name }}</span>
            <span class="post-content-text-{{ $post->id }}">{!! nl2br(e($post->content)) !!}</span>
        </div>
          <div class="d-flex justify-content-between mb-2">
            <div class="d-flex gap-3">
                <button class="btn-like" data-id="{{ $post->id }}">
                    @if($post->likes->contains('user_id', auth()->id()))
                    <i class="bi bi-heart-fill action-icon fs-5 text-danger"></i>
                    @else
                    <i class="bi bi-heart action-icon fs-5 "></i>
                  @endif
                </button>
                <button class="open-post" data-id="{{ $post->id }}">
                    <i class="bi bi-chat action-icon fs-5"></i>
                </button>
                <i class="bi bi-share action-icon fs-5"></i>
            </div>
            <button class="btn-favorite" data-id="{{ $post->id }}">
                @if($post->favorites->contains('user_id', auth()->id()))
                    <i class="bi bi-bookmark-fill action-icon fs-5 text-warning"></i>
                @else
                    <i class="bi bi-bookmark action-icon fs-5 "></i>
                    @endif
            </button>
        </div>
        <div class="d-flex justify-content-between mb-2">
            <button class="open-like fw-bold small like-count"
                    data-authid="{{$post->user->id}}"
                    data-post-id="{{ $post->id }}">
                {{ number_format($post->likes->count() ?? 0) }} lượt thích
        </button>
            <div class="fw-bold small comment-count" data-post-id="{{ $post->id }}">
                {{ number_format($post->comments->count() ?? 0) }} bình luận
            </div>
        </div>
        <div class="text-uppercase text-muted mb-2" style="font-size: 10px;">
            {{ $post->created_at->diffForHumans() }}
        </div>
    </div>
</div>

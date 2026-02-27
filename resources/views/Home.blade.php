@extends('layouts.app')

@section('content')
<style>
    body { background-color: #fafafa; }
    .feed-container { max-width: 935px; margin: auto; }
    
    /* Post Card Style */
    .post-card {
        border-radius: 8px;
        border: 1px solid #dbdbdb;
        background: #fff;
        margin-bottom: 30px;
    }
    /* Instagram Style Media Slide */
    .post-media-wrapper {
        background-color: #fafafa;
        position: relative;
        width: 100%;
        border-top: 1px solid #efefef;
        border-bottom: 1px solid #efefef;
    }
    .carousel-item img, .carousel-item video {
        width: 100%;
        height: 100%; /* Tỉ lệ chuẩn Instagram Desktop */
        object-fit: contain;
    } 
    .carousel-control-prev, .carousel-control-next { 
        opacity: 0.8; 
        width: 10%; 
        height: 50%; 
        top: 50%; 
        transform: translateY(-50%);}
    .carousel-indicators [data-bs-target] { 
        width: 6px; 
        height: 6px; 
        border-radius: 50%; 
        margin: 0 3px; }

    /* User Avatar */
    .avatar-circle {
        width: 32px; height: 32px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid #dbdbdb;
    }
    /* Interaction Icons */
    .action-icon { font-size: 1.5rem; cursor: pointer; transition: color 0.2s; }
    .action-icon:hover { color: #8e8e8e; }
    .bi-heart-fill { color: #ed4956; }

    /* Right Sidebar (Suggestions) */
    .sidebar-sticky { position: sticky; top: 90px; }
    :root {
        --f-backdrop-bg: rgba(93, 93, 93, 0.264) !important;
    }
    .fancybox__backdrop {
        background-color: rgba(112, 112, 112, 0.544) !important;
        backdrop-filter: blur(4px) !important;
        -webkit-backdrop-filter: blur(4px) !important;
    }
</style>

<div class="container feed-container py-4">
    <div class="row">
        <div class="col-lg-8">
            
            <div class="card post-card p-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset('storage/' . (auth()->user()->profile->avatar ?? 'default.jpg')) }}" class="avatar-circle">
                    <a href="{{ route('posts.create') }}" class="btn btn-light rounded-pill flex-grow-1 text-start text-muted border-0 bg-light py-2 px-3">
                        {{ auth()->user()->profile->display_name ?? 'Bạn' }} ơi, bạn đang nghĩ gì thế?
                    </a>
                </div>
            </div>

            @forelse($posts as $post)
            <div class="card post-card shadow-none">
                
                <div class="p-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{ asset('storage/' . ($post->user->profile->avatar ?? 'default.jpg')) }}" class="avatar-circle">
                        <div>
                            <div class="fw-bold small">{{ $post->user->profile->display_name ?? $post->user->name }}</div>
                            <div class="text-muted" style="font-size: 11px;">{{ $post->topic->name ?? 'Chung' }}</div>
                        </div>
                    </div>
                    <div class="dropdown">
                        <i class="bi bi-three-dots cursor-pointer" data-bs-toggle="dropdown"></i>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li><a class="dropdown-item small" href="{{ route('posts.edit', $post->id) }}">Chỉnh sửa</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><button class="dropdown-item small text-danger">Báo cáo</button></li>
                        </ul>
                    </div>
                </div>

                <div class="post-media-wrapper">
            
                    @if($post->media->count() > 0)
                        <div id="carousel-{{ $post->id }}" class="carousel slide" data-bs-ride="false">
                            <div class="carousel-indicators">
                                @foreach($post->media as $index => $m)
                                    <button type="button" data-bs-target="#carousel-{{ $post->id }}" data-bs-slide-to="{{ $index }}" class="{{ $index == 0 ? 'active' : '' }}"></button>
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
                                        </a>
                                    @endif
                                    </div>
                                @endforeach
                            </div>
                            @if($post->media->count() > 1)
                                <button class="carousel-control-prev" type="button" data-bs-target="#carousel-{{ $post->id }}" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carousel-{{ $post->id }}" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
                

                <div class="p-3 pb-0">
                  
                    
                    <div class="post-caption small mb-1">
                        <span class="fw-bold me-1">{{ $post->user->profile->display_name ?? $post->user->name }}</span>
                        {!! nl2br(e($post->content)) !!}
                    </div>
                      <div class="d-flex justify-content-between mb-2">
                        <div class="d-flex gap-3">
                            <i class="bi bi-heart action-icon"></i>
                            <i class="bi bi-chat action-icon"></i>
                            <i class="bi bi-send action-icon"></i>
                        </div>
                        <i class="bi bi-bookmark action-icon"></i>
                    </div>
                    <div class="fw-bold small mb-2">{{ number_format($post->likes_count ?? 0) }} lượt thích</div>
                    <div class="text-uppercase text-muted mb-2" style="font-size: 10px;">
                        {{ $post->created_at->diffForHumans() }}
                    </div>
                </div>

                <div class="p-3 border-top d-flex align-items-center">
                    <i class="bi bi-emoji-smile fs-5 me-2"></i>
                    <input type="text" class="form-control form-control-sm border-0 shadow-none p-0" placeholder="Thêm bình luận...">
                    <button class="btn btn-link btn-sm text-primary text-decoration-none fw-bold p-0 ms-2 opacity-50">Đăng</button>
                </div>
            </div>
            @empty
                <div class="text-center py-5">
                    <i class="bi bi-camera fs-1 text-muted"></i>
                    <p class="text-muted mt-2">Chưa có bài viết nào được đăng.</p>
                </div>
            @endforelse
        </div>

        <div class="col-lg-4 d-none d-lg-block">
            <div class="sidebar-sticky ps-4">
                <div class="d-flex align-items-center mb-4">
                    <img src="{{ asset('storage/' . (auth()->user()->profile->avatar ?? 'default.jpg')) }}" class="rounded-circle" style="width: 56px; height: 56px; object-fit: cover;">
                    <div class="ms-3">
                        <div class="fw-bold small">{{ auth()->user()->name }}</div>
                        <div class="text-muted small">{{ auth()->user()->profile->display_name ?? 'User' }}</div>
                    </div>
                    <a href="#" class="ms-auto text-primary text-decoration-none small fw-bold">Chuyển</a>
                </div>
                
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted fw-bold small">Gợi ý cho bạn</span>
                    <a href="#" class="text-dark text-decoration-none small fw-bold">Xem tất cả</a>
                </div>

                <div class="text-muted mt-4" style="font-size: 12px;">
                    Giới thiệu • Trợ giúp • Báo chí • API • Việc làm • Quyền riêng tư • Điều khoản
                </div>
                <div class="text-muted mt-3 fw-bold" style="font-size: 12px;">
                    © 2026 INSTAGRAM CLONE FROM GEMINI
                </div>
            </div>
        </div>
    </div>
</div>
<script>

    document.querySelectorAll('.video-link').forEach(link => {
        const video = document.createElement('video');
        video.src = link.href + "#t=0.5";
        video.crossOrigin = "anonymous"; // Tránh lỗi bảo mật
        video.muted = true;

        video.addEventListener('loadeddata', () => {
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            // Lấy ảnh đã chụp gán vào data-thumb cho Fancybox
            const base64Image = canvas.toDataURL('image/jpeg');
            link.setAttribute('data-thumb', base64Image);
        });
    });
    document.addEventListener("DOMContentLoaded", function() {
    // 3. Tự động Play/Pause video khi lướt qua
    const videoOptions = {
        root: null, // Lấy toàn bộ màn hình làm khung nhìn
        threshold: 0.6 // Video phải hiển thị được 60% diện tích thì mới tính là "đang xem"
    };

    const videoObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const video = entry.target;

            if (entry.isIntersecting) {
                // Nếu video lọt vào tầm mắt -> Phát
                video.play().catch(error => {
                    // Trình duyệt thường chặn autoplay nếu chưa có tương tác, 
                    // nên ta bắt lỗi để tránh crash code
                    console.log("Autoplay bị chặn hoặc có lỗi:", error);
                });
            } else {
                // Nếu lướt qua khỏi tầm mắt -> Tắt
                video.pause();
                video.currentTime = 0; // Tùy chọn: Reset về đầu nếu muốn
            }
        });
    }, videoOptions);

    // Bắt đầu theo dõi tất cả các video có class .feed-video
    document.querySelectorAll('.feed-video').forEach(v => {
        videoObserver.observe(v);
    });
});
    Fancybox.bind("[data-fancybox^='gallery-']", {
    Compact: false,
    Animated: true,
        Thumbs: {
            autoStart: true,
        },
        Html: {
            video: {
                autoplay: true,
                controls: true,
                format: "mp4" 
            }
        }
    });
</script>
@endsection
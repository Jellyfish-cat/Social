@extends('layouts.app')

@section('content')
<style>
    
    
 .sidebar-sticky { position: sticky; top: 90px; } 
 :root { --f-backdrop-bg: rgba(93, 93, 93, 0.264) !important; } 
 .fancybox__backdrop { 
    background-color: rgba(112, 112, 112, 0.544) !important; 
    backdrop-filter: blur(4px) !important; 
    -webkit-backdrop-filter: blur(4px) !important; }
</style>

<div class="container feed-container py-4">
    <div class="row">
        <div class="col-lg-8">
            
            <div class="card post-card p-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset('storage/' . (auth()->user()->profile->avatar ?? 'default.jpg')) }}" class="avatar-circle" style="width:60px">
                    <a href="{{ route('posts.create') }}" class="btn btn-light rounded-pill flex-grow-1 text-start text-muted border-0 bg-light py-2 px-3">
                        {{ auth()->user()->profile->display_name ?? 'Bạn' }} ơi, bạn {{ __('Like') }} nghĩ gì thế?
                    </a>
                </div>
            </div>

            @forelse($posts as $post)
            <div class="card post-card shadow-none">
            <!-- <div class="card post-card shadow-none post-item" data-id="{{ $post->id }}"> -->
                
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
                            <li>
                            <form
                                action="{{ route('posts.destroy',$post->id) }}"
                                method="POST"
                                class="d-inline">
                                @csrf
                                @method('DELETE')
                                <a class="dropdown-item small">
                                <button 
                                    onclick="return confirm('Xóa bài viết này sẽ xóa toàn bộ ảnh/video liên quan. Bạn chắc chứ?')">
                                    Xóa
                                </button>
                            </a>
                            </form>
                        </li>
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
                                        </a>
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
                        {!! nl2br(e($post->content)) !!}
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
                            <!-- <i class="bi bi-chat action-icon btn-comment" data-id="{{ $post->id }}"></i> -->
                            <i class="bi bi-share action-icon fs-5"></i>
                        </div>
                        <i class="bi bi-bookmark action-icon fs-5"></i>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <div class="fw-bold small like-count" data-post-id="{{ $post->id }}">
                            {{ number_format($post->likes->count() ?? 0) }} lượt thích
                        </div>
                        <div class="fw-bold small comment-count" data-post-id="{{ $post->id }}">
                            {{ number_format($post->comments->count() ?? 0) }} bình luận
                        </div>
                    </div>
                    <div class="text-uppercase text-muted mb-2" style="font-size: 10px;">
                        {{ $post->created_at->diffForHumans() }}
                    </div>
                </div>
                <form class="p-3 border-top d-flex comment-form align-items-center" novalidate>
                    <button class="btn-icon" >
                    <i class="bi bi-emoji-smile fs-5"></i>
                    </button>
                        <input type="file" id="file" hidden multiple onchange="previewCreateFiles()">
                        <button type="button" class="btn-image btn"onclick="event.preventDefault(); document.getElementById('file').click();">
                            <i class="bi bi-image fs-5"></i>
                                </button>
                     <textarea name="content"
                            class="form-control border-0 shadow-none small comment-textarea"
                                    data-post-id="{{ $post->id }}"
                                    placeholder="Viết bình luận..."
                                    rows="1"
                                    required></textarea>
                            <input type="hidden" name="post_id" value="{{ $post->id }}">
                            <input type="hidden" name="parent_id" class="parent-id">
                    <button type="button" class="btn-cancel-comment text-muted me-2">
                                <i class="bi bi-x-lg"></i>
                            </button>
                    <button class="btn btn-link btn-sm text-primary fw-bold comment-submit"
                                data-post-id="{{ $post->id }}" type="button">
                            <i class="bi bi-send fs-5"></i>
                        </button>
                    </form>
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
                <!-- <div class="col-lg-4 d-none d-lg-block" id="comment-panel">
                <div class="sidebar-sticky ps-4"> -->
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
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal xem chi tiết bài viết -->
<div class="modal fade" id="postDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 1290px;">
        <div class="modal-content">
            <div class="modal-body p-0" id="postDetailContent">
                <!-- Nội dung chi tiết post sẽ load vào đây -->
            </div>
        </div>
    </div>
</div>      
<script>
</script>       
@endsection
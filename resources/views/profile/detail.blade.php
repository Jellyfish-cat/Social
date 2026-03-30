@extends('layouts.app')
@section('content')

<div class="container mt-5">
    {{-- HEADER PROFILE --}}
    <div class="row align-items-center mb-5">
        {{-- Avatar --}}
        <div class="col-md-3 text-center">
            <div class="avatar-wrapper">
                <img src="{{ asset('storage/' . ($user->profile->avatar ?? 'default-avatar.png')) }}" class="avatar-img">
            </div>
        </div>
        {{-- Info --}}
        <div class="col-md-9">
            <div class="d-flex align-items-center gap-3 mb-3">
                <h3 class="fw-light mb-0">{{ $user->name }}</h3>
                @if(Auth::id() === $user->id)
                    <a href="{{ route('profile.edit', $user->id) }}" class="btn btn-outline-dark btn-sm">
                        Chỉnh sửa hồ sơ
                    </a>
                @else
                   
                @endif
            </div>
            {{-- Stats --}}
            <div class="d-flex gap-4 mb-3">
                <div><strong>{{ $user->posts->count() }}</strong> bài viết</div>
                <button class="open-follow" data-type="follower" data-id="{{$user->id}}">
                    <strong class="follow-count"  data-id="{{$user->id}}">{{ $user->followers->count() ?? 0 }}</strong> người theo dõi</button>
                <button class="open-follow"  data-type="following" data-id="{{$user->id}}">
                    <strong class="following-count" data-authid="{{$user->id}}">{{ $user->following->count() ?? 0 }}</strong>
                     đang theo dõi</button>
            </div>
            {{-- Bio --}}
            <div>
                <strong>{{ $user->profile->display_name }}</strong><br>
                <span class="text-muted">
                    {{ $user->bio ?? 'Chưa có tiểu sử' }}
                </span>
                
            </div>
            @if(Auth::id() !== $user->id)
            <div class="mt-2">
                @if($user->followers->contains(Auth::id()))
                    <button class="btn btn-light rounded-3 fw-semibold px-4 w-25 btn-sm follow-btn" 
                    data-id="{{$user->id}}">Đang Theo dõi</button>
                                    @else
                                    <button class="btn btn-primary rounded-3 w-25 fw-semibold px-4 btn-sm follow-btn" 
                                    data-id="{{$user->id}}">Theo dõi</button>
                                    @endif
                        <a href="{{ route('conversations.index', ['chat' => $user->id]) }}"
                            class="btn btn-dark rounded-3 w-25 fw-semibold px-4 btn-sm">
                                Nhắn tin
                            </a>
            </div>
            @endif
        </div>
    </div>
    {{-- GRID POSTS --}}
    <div class="container feed-container py-4 border-top pt-3">
            {{-- MENU --}}
    <div class="row">
        <div class="col-lg-8">
            <div class="mb-4 text-center">
        <button class="me-4 fw-semibold post-profile active-tab"><i class="bi bi-grid-3x3"></i> Bài viết</button>
        <button class="text-muted me-4 comment-profile"><i class="bi bi-chat"></i> Bình Luận</button>
        <button class="text-muted me-4 fav-profile"><i class="bi bi-bookmark"></i> Đã lưu</button>
        <button class="text-muted  like-profile"><i class="bi bi-heart"></i> Yêu thích</button>
            </div>
            <div class="card post-card p-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset('storage/' . (auth()->user()->profile->avatar ?? 'default-avatar.png')) }}" class="avatar-circle">
                    <a href="{{ route('posts.create') }}" class="btn btn-light rounded-pill flex-grow-1 text-start text-muted border-0 bg-light py-2 px-3">
                        {{ auth()->user()->profile->display_name ?? 'Bạn' }} ơi, bạn {{ __('Like') }} nghĩ gì thế?
                    </a>
                </div>
            </div>
            <div id="post-list">
                @include('profile.partials.post-list', ['posts' => $posts])
            </div>
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
<div class="modal fade back-to" id="postDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 1290px;">
        <div class="modal-content">
            <div class="modal-body p-0" id="postDetailContent">
                <!-- Nội dung chi tiết post sẽ load vào đây -->
            </div>
        </div>
    </div>
</div>      
<!-- Modal xem chi tiết người theo dõi -->
<div class="modal fade back-to-follow" id="followDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 800px;">
        <div class="modal-content">
            <div class="modal-body p-0" id="followDetailContent">
                <!-- Nội dung chi tiết post sẽ load vào đây -->
            </div>
        </div>
    </div>
</div> 
<style>
/* Avatar */
.avatar-wrapper {
    width:150px;
    height:150px;
    border-radius:50%;
    overflow:hidden;
    margin:auto;
    border:3px solid #ddd;
}
.avatar-img {
    width:100%;
    height:100%;
    object-fit:cover;
}
</style>

@endsection
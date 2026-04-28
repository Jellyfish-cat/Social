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
                    <a href="{{ route('profile.edit', $user->id) }}"  class="btn btn-outline-dark btn-sm">
                        Chỉnh sửa hồ sơ
                    </a>
                @endif
                 <div class="dropdown">
                        <i class="bi bi-three-dots cursor-pointer text-muted"
                        data-bs-toggle="dropdown"></i>

                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            @if(!Auth::check() || $user->id !== Auth::id())
                            <li><button class="dropdown-item small open-report require-login" data-type="user" data-id="{{ $user->id }}">Chặn</button></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><button class="dropdown-item small text-danger open-report require-login" data-type="user" data-id="{{ $user->id }}">Báo cáo</button></li>
                            @endif
                        </ul>
                    </div>
            </div>
            {{-- Stats --}}
            <div class="d-flex gap-4 mb-3 align-items-center">
                <div><span>{{ $user->posts->count() }}</span> bài viết</div>
                <button class="open-follow follow-count bg-transparent border-0 p-0" data-type="follower" data-id="{{$user->id}}">
                   <span>{{ $user->followers->count() ?? 0 }}</span> người theo dõi</button>
                <button class="open-follow following-count bg-transparent border-0 p-0" data-authid="{{$user->id}}"  data-type="following" data-id="{{$user->id}}">
                   <span>{{ $user->following->count() ?? 0 }}</span> đang theo dõi</button>
                
                {{-- Blockchain Index --}}
                <div class="d-flex align-items-center gap-1 text-primary shadow-sm px-2 py-1 rounded-pill bg-light" 
                     id="blockchain-stats" 
                     data-profile-id="{{ $user->id }}"
                     style="font-size: 0.85rem; cursor: help;" title="Dữ liệu từ Blockchain Ganache">
                    <i class="bi bi-shield-check"></i>
                    <strong id="blockchain-value">...</strong>
                    <span class="text-muted small">BC Index</span>
                </div>
            </div>
            {{-- Bio --}}
            <div>
                <strong>{{ $user->profile->display_name }}</strong><br>
                <span class="text-muted">
                    {{ $user->bio ?? 'Chưa có tiểu sử' }}
                </span>
                
            </div>
            @if(!Auth::check() || Auth::id() !== $user->id)
            <div class="mt-2">
                @if(Auth::check() && $user->followers->contains(Auth::id()))
                    <button class="btn btn-light rounded-3 fw-semibold px-4 w-25 btn-sm follow-btn" 
                    data-id="{{$user->id}}">Đang Theo dõi</button>
                @else
                    <button class="btn btn-primary rounded-3 w-25 fw-semibold px-4 btn-sm follow-btn require-login" 
                    data-id="{{$user->id}}">Theo dõi</button>
                @endif
                <a href="{{ route('conversations.index', ['chat' => $user->id]) }}"
                    class="btn btn-dark rounded-3 w-25 fw-semibold px-4 btn-sm require-login">
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
            @if(auth::id() === $user->id )
            <div class="card post-card p-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset('storage/' . (auth()->user()->profile->avatar ?? 'default-avatar.png')) }}" class="avatar-circle">
                    <a href="{{ route('posts.create') }}" class="btn btn-light rounded-pill flex-grow-1 text-start text-muted border-0 bg-light py-2 px-3">
                        {{ auth()->user()->profile->display_name ?? 'Bạn' }} ơi, bạn {{ __('Like') }} nghĩ gì thế?
                    </a>
                </div>
            </div>
           @endif
            <div id="post-list">
                @include('profile.partials.post-list', ['posts' => $posts])
            </div>
        </div>
        <div class="col-lg-4 d-none d-lg-block">
            <div class="sidebar-sticky ps-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted fw-bold small">Gợi ý cho bạn</span>
                </div>

                @foreach($suggestedUsers as $u)
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <a href="{{ route('profile.detail', $u->id) }}" class="text-decoration-none">
                    <div class="d-flex align-items-center">
                        <img src="{{ asset('storage/' . ($u->profile->avatar ?? 'default-avatar.png')) }}" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                        <div class="ms-3">
                            <div class="fw-bold small">{{ $u->profile->display_name ?? $u->name }}</div>
                            <div class="text-muted" style="font-size: 11px;">Gợi ý cho bạn</div>
                        </div>
                    </div></a>
                    <button class="btn btn-primary rounded-3 fw-semibold px-3 btn-sm follow-btn require-login" 
                    data-id="{{$u->id}}">Theo dõi</button>
                </div>
                @endforeach
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

@push('scripts')
    @vite(['resources/js/blockchain.js'])
@endpush

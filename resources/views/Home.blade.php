@extends('layouts.app')

@section('content')
<style>
    
    

</style>

<div class="container feed-container py-4" >
    <div class="row">
        <div class="col-lg-8">
            <div class="card post-card p-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset('storage/' . (Auth::check() ? (auth()->user()->profile->avatar ?? 'default-avatar.png') : 'default-avatar.png')) }}" class="avatar-circle">
                    <a href="{{ route('posts.create') }}" class="btn btn-light rounded-pill flex-grow-1 text-start text-muted border-0 bg-light py-2 px-3 nav-ajax require-login">
                        {{ Auth::check() ? (auth()->user()->profile->display_name ?? 'Bạn') : 'Bạn' }} ơi, bạn nghĩ gì thế?
                    </a>
                </div>
            </div>
            @forelse($posts as $post)
                @include('posts.post_item', ['post' => $post])
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
                @if(Auth::check())
                <div class="d-flex align-items-center mb-4">
                    <img src="{{ asset('storage/' . (auth()->user()->profile->avatar ?? 'default.jpg')) }}" class="rounded-circle" style="width: 56px; height: 56px; object-fit: cover;">
                    <div class="ms-3">
                        <div class="fw-bold">{{ auth()->user()->profile->display_name ?? auth()->user()->name }}</div>
                        <div class="text-muted small">{{ auth()->user()->name }}</div>
                    </div>
                </div>
                @endif

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
    </div>
</div>
@endsection
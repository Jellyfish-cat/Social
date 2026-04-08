@extends('layouts.app')

@section('content')
<style>
    
    

</style>

<div class="container feed-container py-4" >
    <div class="row">
        <div class="col-lg-8">
            <div class="card post-card p-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <img  src="{{ asset('storage/' . (auth()->user()->profile->avatar ?? 'default-avatar.png')) }}" class="avatar-circle">
                    <a href="{{ route('posts.create') }}" class="btn btn-light rounded-pill flex-grow-1 text-start text-muted border-0 bg-light py-2 px-3 nav-ajax">
                        {{ auth()->user()->profile->display_name ?? 'Bạn' }} ơi, bạn {{ __('Like') }} nghĩ gì thế?
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
                <div class="d-flex align-items-center mb-4">
                    <img src="{{ asset('storage/' . (auth()->user()->profile->avatar ?? 'default.jpg')) }}" class="rounded-circle" style="width: 56px; height: 56px; object-fit: cover;">
                    <div class="ms-3">
                        <div class="fw-bold">{{ auth()->user()->profile->display_name ?? auth()->user()->name }}</div>
                        <div class="text-muted small">{{ auth()->user()->name }}</div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted fw-bold small">Gợi ý cho bạn</span>
                    <a href="#" class="text-dark small fw-bold text-decoration-none">Xem tất cả</a>
                </div>

                @foreach(\App\Models\User::where('id', '!=', auth()->id())->limit(5)->get() as $u)
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                        <img src="{{ asset('storage/' . ($u->profile->avatar ?? 'default-avatar.png')) }}" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                        <div class="ms-3">
                            <div class="fw-bold small">{{ $u->profile->display_name ?? $u->name }}</div>
                            <div class="text-muted" style="font-size: 11px;">Gợi ý cho bạn</div>
                        </div>
                    </div>
                    <a href="#" class="text-primary small fw-bold text-decoration-none">Theo dõi</a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
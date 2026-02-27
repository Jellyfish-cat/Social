@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">

        <div class="col-md-8 offset-md-2">

            {{-- Box đăng bài --}}
            @auth
            <div class="card mb-4">
                <div class="card-body">
                    <form method="POST" action="/posts" enctype="multipart/form-data">
                        @csrf
                        <textarea name="content" class="form-control mb-2"
                            placeholder="Bạn đang nghĩ gì?" required></textarea>

                        <input type="file" name="image" class="form-control mb-2">

                        <button class="btn btn-primary">Đăng bài</button>
                    </form>
                </div>
            </div>
            @endauth


            {{-- Danh sách bài viết --}}
            @foreach($posts as $post)
            <div class="card mb-4 shadow-sm">

                {{-- Header --}}
                <div class="card-header d-flex align-items-center">
                    <img src="{{ $post->user->avatar ?? '/default.png' }}"
                        width="40" height="40"
                        class="rounded-circle me-2">

                    <div>
                        <strong>{{ $post->user->name }}</strong>
                        <div class="text-muted small">
                            {{ $post->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>

                {{-- Nội dung --}}
                <div class="card-body">
                    <p>{{ $post->content }}</p>

                    @if($post->image)
                        <img src="{{ asset('storage/'.$post->image) }}"
                             class="img-fluid rounded">
                    @endif
                </div>

                {{-- Like + Comment --}}
                <div class="card-footer">

                    <div class="mb-2">
                        ❤️ {{ $post->likes->count() }} lượt thích
                        • 💬 {{ $post->comments->count() }} bình luận
                    </div>

                    @auth
                    <form method="POST" action="/posts/{{ $post->id }}/like" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-outline-primary">
                            Thích
                        </button>
                    </form>
                    @endauth

                    {{-- Comment form --}}
                    @auth
                    <form method="POST" action="/posts/{{ $post->id }}/comment"
                          class="mt-2">
                        @csrf
                        <input type="text" name="content"
                               class="form-control"
                               placeholder="Viết bình luận...">
                    </form>
                    @endauth

                    {{-- Hiển thị comment --}}
                    @foreach($post->comments as $comment)
                        <div class="mt-2 p-2 bg-light rounded">
                            <strong>{{ $comment->user->name }}</strong>
                            {{ $comment->content }}
                        </div>
                    @endforeach

                </div>

            </div>
            @endforeach

            {{ $posts->links() }}

        </div>
    </div>
</div>
@endsection
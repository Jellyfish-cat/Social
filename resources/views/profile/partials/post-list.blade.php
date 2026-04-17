@php
    $pinnedPost = $posts->where('pinned', 1)->first();
    $normalPosts = $posts->where('pinned', '!=', 1);
@endphp

@if($pinnedPost)
    <div class="pinned-post-container mb-2">
        <div class="px-3 pt-2 d-flex align-items-center text-primary small fw-bold">
            <i class="bi bi-pin-angle-fill me-1"></i> Bài viết đã ghim
        </div>
        @include('posts.post_item', ['post' => $pinnedPost])
        <div class="my-2 border-bottom shadow-sm mx-3"></div>
    </div>
@endif

@forelse($normalPosts as $post)
    @include('posts.post_item', ['post' => $post])
@empty
    @if(!$pinnedPost)
        <div class="text-center py-5">
            <i class="bi bi-camera fs-1 text-muted"></i>
            <p class="text-muted mt-2">Chưa có bài viết nào.</p>
        </div>
    @endif
@endforelse
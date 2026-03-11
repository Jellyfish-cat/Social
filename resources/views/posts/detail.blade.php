    @extends('layouts.app_detail')

    @section('content')

    <style>

    </style>
    <div class="container-fluid">
        <div class="post-modal">

            <div class="edit-main-content">

                {{-- ================= LEFT MEDIA ================= --}}
                <div class="media-column">
                     <div class="media-top">
                        @if($post->media->count()==0)
                        <div class="carousel-item active">
                         <div class="placeholder-content">
                                    <i class="bi bi-image fs-1 mb-3"></i>
                                    <p class="fw-medium">Bài viết này hiện chưa có ảnh</p>
                                </div>
                        </div>
                                @endif
                    <div id="instaCarousel" class="carousel slide w-100" data-bs-ride="false">
                        <div class="carousel-inner">
                            <div class="carousel-indicators">
                                @foreach($post->media as $index => $m)
                                    <button type="button" data-bs-target="#carousel-{{ $post->id }}" data-bs-slide-to="{{ $index }}" class="{{ $index == 0 ? 'active' : '' }}"style="width: 8px; height: 8px; border-radius: 50%; margin-bottom:10px"></button>
                                @endforeach
                            </div>
                            @foreach($post->media as $index => $m)
                                <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                    @if($m->type == 'image')
                                        <img src="{{ asset('storage/' . $m->file_path) }}"
                                            class="d-block w-100">
                                    @else
                                        <video src="{{ asset('storage/' . $m->file_path) }}"
                                            controls
                                            class="d-block w-100">
                                        </video>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    
                        @if($post->media->count() > 1)
                            <button class="carousel-control-prev"
                                    type="button"
                                    data-bs-target="#instaCarousel"
                                    data-bs-slide="prev">
                                            <i class="bi bi-chevron-left bg-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"></i>
                            </button>

                            <button class="carousel-control-next"
                                    type="button"
                                    data-bs-target="#instaCarousel"
                                    data-bs-slide="next">
                                            <i class="bi bi-chevron-right bg-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"></i>
                            </button>
                        @endif
                    </div>
                    
                </div>
                    <div class="media-bottom p-3">
        <div class="small">
            <b>{{ $post->user->profile->display_name ?? $post->user->email }}</b>
            {{ $post->content }}
        </div>
    </div>
</div>
                <div class="info-column">
                    <div class="d-flex align-items-center p-3 border-bottom">
                        <img src="{{ $post->user->profile->avatar 
                                    ? asset('storage/'.$post->user->profile->avatar) 
                                    : 'https://i.pravatar.cc/150' }}"
                            class="user-avatar me-3">
                        <div>
                            <div class="fw-bold small">
                                {{ $post->user->profile->display_name ?? $post->user->email }}
                            </div>
                            <div class="text-muted" style="font-size:12px;">
                                {{ $post->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>

                    {{-- Content + Comments --}}
                <div class="comment-box" data-post-id="{{ $post->id }}">
                    @if($post->comments->count() == 0)
                    <div class="no-comment text-center text-muted py-5">
                        <i class="bi bi-chat-dots" style="font-size:40px;"></i>
                        <div class="mt-2 small fw-bold">Chưa có bình luận nào</div>
                        <div class="small">Hãy là người đầu tiên bình luận.</div>
                    </div>
                    @endif
       

                        {{-- Comments --}}
                @foreach($post->comments->where('parent_id', null) as $comment)
                <div class="comment-item d-flex">

                    <img src="{{ $comment->user->profile->avatar 
                    ? asset('storage/'.$comment->user->profile->avatar) 
                    : 'https://i.pravatar.cc/150' }}"
                    class="rounded-circle me-2">

                <div class="w-100">

            <div>
                <span class="fw-bold small">
                    {{ $comment->user->profile->display_name ?? $comment->user->email }}
                </span>
                <span class="small ms-1">
                    {{ $comment->content }}
                </span>
            </div>

            <div class="d-flex align-items-center mt-1">

                {{-- Time --}}
                <span class="text-muted me-3" style="font-size:11px;">
                    {{ $comment->created_at?->diffForHumans() }}
                </span>

                {{-- Like comment --}}
                <form method="POST"
                    action="{{ route('comments.like', $comment->id) }}"
                    class="me-3">
                    @csrf
                    <button type="submit"
                            class="btn btn-sm p-0 text-muted small">
                        ❤️ {{ $comment->likes->count() }}
                    </button>
                </form>

                {{-- Reply button --}}
                <button class="btn btn-sm p-0 text-muted small"
                        onclick="document.getElementById('reply-{{ $comment->id }}').classList.toggle('d-none')">
                    Trả lời
                </button>
            </div>

            {{-- Reply form --}}
            <div id="reply-{{ $comment->id }}" class="d-none mt-2">

                <form method="POST"
                    action="{{ route('comments.reply', $comment->id) }}"
                    class="d-flex">
                    @csrf

                    <textarea name="content"
            class="form-control border-0 shadow-none small comment-textarea"
            placeholder="Viết bình luận..."
            rows="1"
            required></textarea>
                    <button type="submit"
                            class="btn btn-sm btn-primary">
                        Gửi
                    </button>
                </form>

            </div>

            {{-- Replies --}}
            @foreach($post->comments->where('parent_id', $comment->id) as $reply)
                <div class="d-flex mt-3 ms-4">

                    <img src="{{ $reply->user->profile->avatar 
                                ? asset('storage/'.$reply->user->profile->avatar) 
                                : 'https://i.pravatar.cc/150' }}"
                        class="rounded-circle me-2"
                        width="28" height="28">

                    <div>
                        <span class="fw-bold small">
                            {{ $reply->user->profile->display_name ?? $reply->user->email }}
                        </span>
                        <span class="small ms-1">
                            {{ $reply->content }}
                        </span>
                        <div class="text-muted" style="font-size:11px;">
                            {{ $reply->created_at?->diffForHumans() }}
                        </div>
                    </div>

                </div>
            @endforeach

        </div>

    </div>

    @endforeach

                    </div>

                    {{-- Action --}}
                    <div class="action-section">

                        <div class="d-flex justify-content-between mb-2">
                        <div class="d-flex">
                            <button class="btn-like" data-id="{{ $post->id }}">
                                @if($post->likes->contains('user_id', auth()->id()))
                                <i class="bi bi-heart-fill action-icon fs-5 me-3 text-danger"></i>
                                @else
                                <i class="bi bi-heart action-icon fs-5 me-3 "></i>
                              @endif
                            </button>
                            <i class="bi bi-send fs-5 me-3"></i>
                        </div>
                             <i class="bi bi-bookmark fs-5"></i>
                        </div>

                        <div class="fw-bold small mb-2 like-count" data-post-id="{{ $post->id }}">
                            {{ number_format($post->likes->count() ?? 0) }} lượt thích
                        </div>

                        {{-- Form comment --}}
                        @if($post->is_comment_enabled)
                            <form method="POST" action="{{ route('comments.create', $post->id) }}" class="d-flex comment-form">
                                @csrf
                                    <textarea name="content"
                                    class="form-control border-0 shadow-none small comment-textarea"
                                    data-post-id="{{ $post->id }}"
                                    placeholder="Viết bình luận..."
                                    rows="1"
                                    required></textarea>

                                            <button class="btn btn-link btn-sm text-primary fw-bold comment-submit"
                            data-post-id="{{ $post->id }}" type="button">
                        <i class="bi bi-send"></i>
                    </button>
                            </form>
                        @else
                            <p class="text-muted small">Bài viết đã tắt bình luận.</p>
                        @endif

                    </div>

                </div>
            </div>
        </div>
    </div>
    @endsection
    <script>
    //định dạng scroll khung comment
    document.addEventListener("input", function(e) {
        if (e.target.classList.contains("comment-textarea")) {
            e.target.style.height = "auto";
            e.target.style.height = e.target.scrollHeight + "px";
        }
    });
    //json gửi comment
            document.addEventListener("click", function(e){

            const button = e.target.closest(".comment-submit");
            if(!button) return;

            e.preventDefault();

            const postId = button.dataset.postId;

            const input = document.querySelector(
                `.comment-textarea[data-post-id="${postId}"]`
            );

            const content = input.value.trim();
            if (!content) return;

            button.disabled = true;

            fetch(`/comments/create/${postId}`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    content: content
                })
            })
            .then(res => res.json())
            .then(data => {

                if (data.success) {

                    const avatar = data.avatar
                        ? `/storage/${data.avatar}`
                        : "https://i.pravatar.cc/150";

                    const commentHtml = `
                  
                    <div class="comment-item d-flex">
                    <img src="${avatar}"
                    class="rounded-circle me-2">

                <div class="w-100">

            <div>
                <span class="fw-bold small">
                    ${data.user_name}
                </span>
                <span class="small ms-1">
                    ${data.content}
                </span>
            </div>

            <div class="d-flex align-items-center mt-1">

                <span class="text-muted me-3" style="font-size:11px;">
                    ${data.created_at}
                </span>

                <form method="POST"
                action="/comments/like/${data.comment_id}"
                class="me-3">
                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                    <button type="submit"
                            class="btn btn-sm p-0 text-muted small">
                        ❤️ ${data.like_count}
                    </button>
                </form>

                <button class="btn btn-sm p-0 text-muted small"
                        onclick="document.getElementById('reply-${data.comment_id}').classList.toggle('d-none')">
                    Trả lời
                </button>
            </div>

            <div id="reply-${data.comment_id}" class="d-none mt-2">

                <form method="POST"
                action="/comments/reply/${data.comment_id}"
                class="d-flex">
                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">

                    <textarea name="content"
            class="form-control border-0 shadow-none small comment-textarea"
            placeholder="Viết bình luận..."
            rows="1"
            required></textarea>
                    <button type="submit"
                            class="btn btn-sm btn-primary">
                        Gửi
                    </button>
                </form>

            </div>
                    `;
                    const commentBox = document.querySelector(
                `.comment-box[data-post-id="${postId}"]`
            );

            commentBox.insertAdjacentHTML("afterbegin", commentHtml);

            input.value = "";
            input.style.height = "35px";
        }

        button.disabled = false;
    })
    .catch(() => {
        button.disabled = false;
    });

});
document.addEventListener("DOMContentLoaded", function(){

document.querySelectorAll(".btn-like").forEach(btn => {

    btn.addEventListener("click", function(){

        const postId = this.dataset.id;
        const likeBtn = this;
        const likeIcon = likeBtn.querySelector("i");

        fetch(`/posts/like/${postId}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            }
        })
        .then(res => res.json())
        .then(data => {

            if(data.success){

                const likeCount = document.querySelector(`.like-count[data-post-id="${postId}"]`);

                likeCount.innerText = data.likePost_count + " lượt thích";

                likeIcon.classList.toggle("text-danger");

                if(likeIcon.classList.contains("text-danger")){
                    likeIcon.classList.remove("bi-heart");
                    likeIcon.classList.add("bi-heart-fill");
                }else{
                    likeIcon.classList.remove("bi-heart-fill");
                    likeIcon.classList.add("bi-heart");
                }

            }

        });

    });

});

});
    </script>
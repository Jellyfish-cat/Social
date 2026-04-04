    @extends($layout)

    @section('content')

    <style>

    </style>
    <div class="container-fluid">
        <div class="post-modal">
            <div class="edit-main-content">
                {{-- =================  MEDIA ================= --}}    
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
                                            class="d-block w-100" >
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
                         <a href="{{ route('profile.detail', $post->user->id) }}" >
                        <img src="{{ $post->user->profile->avatar 
                                    ? asset('storage/'.$post->user->profile->avatar) 
                                    : 'https://i.pravatar.cc/150' }}"
                            class="user-avatar me-3">
                         </a>
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
                @foreach($post->comments->where('parent_comment_id', null) as $comment)
                <div class="comment-item position-relative d-flex" data-comment-id="{{ $comment->id }}">
                     <a href="{{ route('profile.detail', $comment->user->id) }}" >
                    <img src="{{ $comment->user->profile->avatar 
                    ? asset('storage/'.$comment->user->profile->avatar) 
                    : 'https://i.pravatar.cc/150' }}"
                    class="rounded-circle me-2"></a>
                <div class="w-100" style="min-width:0;">
                   
                <div class="d-flex justify-content-between align-items-center">
                    <div class="fw-bold small">
                        {{ $comment->user->profile->display_name ?? $comment->user->email }}
                    </div>
                    <div class="dropdown">
                        <i class="bi bi-three-dots cursor-pointer text-muted"
                        data-bs-toggle="dropdown"></i>

                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            @if($comment->user->id === Auth::id() || auth()->user()->role === 'admin')
                            <li>
                                <a class="dropdown-item small btn-delete-comment"
                                data-id="{{ $comment->id }}">
                                    Xóa
                                </a>
                            </li>
                            @endif
                            @if($comment->user->id !== Auth::id())
                            <li><hr class="dropdown-divider"></li>
                            <li><button class="dropdown-item small text-danger open-report" data-type="comment" data-id="{{ $comment->id }}">Báo cáo</button></li>
                            @endif
                        </ul>
                    </div>

                </div>
                
                <div class="small ms-1 content">
                    {{ $comment->content }}
                </div>
                 @if($comment->media_path)
                    <div class="mt-1 comment-media">
                        @if($comment->isImage())
                        <a href="{{ asset('storage/' . $comment->media_path) }}" 
                                            data-fancybox="gallery-{{ $comment->id }}">
                            <img src="{{ asset('storage/'.$comment->media_path) }}" width="100" class="rounded"></a>
                        @elseif($comment->isVideo())
                        <a href="{{ asset('storage/' . $comment->media_path) }}" 
                                            data-fancybox="gallery-{{ $comment->id }}">
                            <video width="260" controls class="rounded">
                                <source src="{{ asset('storage/'.$comment->media_path) }}">
                            </video></a>
                        @endif
                    </div>
                @endif
            <div class="d-flex align-items-center ">
                {{-- Time --}}
                <span class="text-muted me-3" style="font-size:13px;">
                    {{ $comment->created_at?->diffForHumans() }}
                </span>
                {{-- Like comment list --}}
                <button class="btn-reply-list like-comment-count me-3" style="font-size:13px;"
                    data-comment-id="{{ $comment->id }}"
                    data-username="{{ $comment->user->profile->display_name }}"
                    data-post-id="{{ $post->id }}">
                    {{ $comment->likes->count() }} lượt thích
                </button>
                {{-- Reply button --}}
                <button class="btn-reply" style="font-size:13px;"
                    data-comment-id="{{ $comment->id }}"
                    data-username="{{ $comment->user->profile->display_name }}"
                    data-post-id="{{ $post->id }}">
                    Trả lời
                </button>
                {{-- Like comment --}}
                <div class="ms-auto d-flex" style="gap:2px;">
                <button type="button"
                    class="btn-comment-like btn-sm p-0 text-muted small" data-comment-id="{{ $comment->id }}"
                    data-username="{{ $comment->user->profile->display_name }}"
                    data-post-id="{{ $post->id }}">
                    @if($comment->likes->contains('user_id', auth()->id()))
                        <i class="bi bi-heart-fill action-icon fs-6 me-2 text-danger"></i>
                    @else
                        <i class="bi bi-heart action-icon fs-6 me-2 "></i>
                    @endif
                </button>
                <button type="button"
                    class="btn btn-sm p-0 text-muted small" data-comment-id="{{ $comment->id }}"
                    data-username="{{ $comment->user->profile->display_name }}"
                    data-post-id="{{ $post->id }}">
                    @if($comment->likes->contains('user_id', auth()->id()))
                        <i class="bi bi-hand-thumbs-down-fill action-icon fs-6 text-danger"></i>
                    @else
                        <i class="bi bi-hand-thumbs-down action-icon fs-6  "></i>
                    @endif
                </button>
                </div>
            </div>
            {{-- Replies --}}
        @php
            $replies = $comment->replies;
        @endphp
    @if($replies->count() > 0)
    <div class="view-replies text-black small mt-1" style="cursor:pointer" data-comment-id="{{ $comment->id }}">
        &mdash;&ndash; Xem {{ $replies->count() }} phản hồi <i class="bi bi-caret-down-fill ms-1"></i>
    </div>
    <div class="reply-list d-none" id="reply-{{ $comment->id }}">
        @foreach($replies as $reply)
            <div class="comment-item position-relative d-flex mt-3 ms-1" data-comment-id="{{ $reply->id }}">
                 <a href="{{ route('profile.detail', $reply->user->id) }}" >
                <img src="{{ $reply->user->profile->avatar 
                            ? asset('storage/'.$reply->user->profile->avatar) 
                            : 'https://i.pravatar.cc/150' }}"
                    class="rounded-circle me-2"
                    width="28" height="28"></a>
                <div class="w-100 "style="min-width:0;">
                    <div class="d-flex justify-content-between align-items-center">
                    <div class="fw-bold small">
                        {{ $reply->user->profile->display_name ?? $reply->user->email }}
                    </div>
                    <div class="dropdown">
                        <i class="bi bi-three-dots cursor-pointer text-muted"
                        data-bs-toggle="dropdown"></i>

                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            @if($reply->user->id === Auth::id() || auth()->user()->role === 'admin')
                            <li>
                                <a class="dropdown-item small btn-delete-comment"
                                data-id="{{ $reply->id }}">
                                    Xóa
                                </a>
                            </li>
                            @endif
                            @if($reply->user->id !== Auth::id())
                            <li><hr class="dropdown-divider"></li>
                            <li><button class="dropdown-item small text-danger open-report" data-type="comment" data-id="{{ $comment->id }}">Báo cáo</button></li>
                            @endif
                        </ul>
                    </div>

                </div>
                    <div class="small ms-1 content">
                        {{ $reply->content }}
                    </div>
                    @if($reply->media_path)
                <div class="mt-1 comment-media">
                    <img src="{{ asset('storage/'.$reply->media_path) }}" width="100" class="rounded">
                </div>
                    @endif
                    <div class="d-flex align-items-center mt-1">
                {{-- Time --}}
                <span class="text-muted me-3" style="font-size:13px;">
                    {{ $reply->created_at?->diffForHumans() }}
                </span>
                {{-- Like comment list --}}
                <button class="btn-reply-list me-3 like-comment-count" style="font-size:13px;"
                    data-comment-id="{{ $reply->id }}"
                    data-username="{{ $reply->user->profile->display_name }}"
                    data-post-id="{{ $post->id }}">
                    {{ $reply->likes->count() }} lượt thích
                </button>
                {{-- Reply button --}}
                <button class="btn-reply " style="font-size:13px;"
                    data-comment-id="{{ $reply->parent_comment_id }}"
                    data-username="{{ $reply->user->profile->display_name }}"  
                    data-post-id="{{ $post->id }}">
                    Trả lời
                </button>
                <div class="ms-auto d-flex" style="gap:2px;">
                <button type="button"
                    class="btn-comment-like btn-sm p-0 text-muted small"  data-comment-id="{{ $reply->id }}"
                    data-username="{{ $reply->user->profile->display_name }}"
                    data-post-id="{{ $post->id }}">
                    @if($reply->likes->contains('user_id', auth()->id()))
                        <i class="bi bi-heart-fill action-icon fs-6 me-2 text-danger"></i>
                    @else
                        <i class="bi bi-heart action-icon fs-6 me-2 "></i>
                    @endif
                </button>
                <button type="button"
                    class="btn btn-sm p-0 text-muted small" data-comment-id="{{ $reply->id }}"
                    data-username="{{ $reply->user->profile->display_name }}"
                    data-post-id="{{ $post->id }}">
                    @if($reply->likes->contains('user_id', auth()->id()))
                        <i class="bi bi-hand-thumbs-down-fill action-icon fs-6 text-danger"></i>
                    @else
                        <i class="bi bi-hand-thumbs-down action-icon fs-6  "></i>
                    @endif
                </button>
                  </div>
            </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif
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
                             <button class="btn-favorite" data-id="{{ $post->id }}">
                            @if($post->favorites->contains('user_id', auth()->id()))
                                <i class="bi bi-bookmark-fill action-icon fs-5 text-warning"></i>
                            @else
                                <i class="bi bi-bookmark action-icon fs-5 "></i>
                                @endif
                        </button>
                        </div>
                            <i class="bi bi-share fs-5 me-3"></i>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                        <div class="fw-bold small like-count" data-post-id="{{ $post->id }}">
                            {{ number_format($post->likes->count() ?? 0) }} lượt thích
                        </div>
                        <div class="fw-bold small comment-count" data-post-id="{{ $post->id }}">
                            {{ number_format($post->comments->count() ?? 0) }} bình luận
                        </div>
                    </div>
                        {{-- Form comment --}}
                        @if($post->is_comment_enabled)
                            <form class="d-flex flex-column comment-form">
                                @csrf
                                <div class="preview-media d-flex flex-wrap gap-2 mb-2"></div>
                                <div class="d-flex align-items-center w-100 constantIcon">
                                    <button class="msg-input-icon" title="Emoji" id="emojiBtn"><i class="bi bi-emoji-smile fs-5"></i></button>
                                        <div class="mb-5 me-2" id="emojiPicker" style="position:absolute; bottom:60px; right:100px; display:none;"></div>
                                    <input type="file" id="comment-file-{{ $post->id }}" name="file" hidden onchange="previewCommentFiles(this)">
                                    <button type="button" class="btn-image btn"
                                         onclick="event.preventDefault(); document.getElementById('comment-file-{{ $post->id }}').click();">
                                        <i class="bi bi-image fs-5"></i>
                                        </button>
                                    <textarea name="content" id="msgInput"
                                    data-post-id="{{ $post->id }}"
                                        class="form-control border-0 shadow-none small comment-textarea"
                                        placeholder="Viết bình luận..."
                                        rows="1"
                                        required></textarea>
                                    <input type="hidden" name="post_id" value="{{ $post->id }}">
                                    <input type="hidden" name="parent_comment_id" class="parent-id">
                                     <button type="button" class="btn-cancel-comment text-muted me-2">
                                <i class="bi bi-x-lg"></i>
                            </button>
                                    <button class="btn btn-link btn-sm text-primary fw-bold comment-submit"
                                            data-post-id="{{ $post->id }}" type="button">
                                        <i class="bi bi-send fs-5"></i>
                                    </button>
                                </div>
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

    </script>
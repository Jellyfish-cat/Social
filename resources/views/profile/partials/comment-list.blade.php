@foreach($comments as $comment)
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-3">
        <div class="d-flex align-items-start btn-reply open-post" 
            data-id="{{ $comment->post->id }}"
                        data-action="reply"
                        data-scroll-comment-id="{{ $comment->id }}">
            <!-- Avatar -->
            <a href="{{ route('profile.detail', $comment->user->id) }}" onclick="event.stopPropagation()">
            <img src="{{ $comment->user->profile->avatar 
                ? asset('storage/'.$comment->user->profile->avatar) 
                : 'https://i.pravatar.cc/150' }}"
                class="rounded-circle me-2"
                style="width:40px;height:40px;object-fit:cover;"></a>
            <div class="w-100" style="min-width:0;">
                <div class="bg-light rounded-4 px-3 py-2">
                    <div class="fw-semibold small mb-1">
                        {{ $comment->user->profile->display_name ?? $comment->user->email }}
                    </div>
                    <div class="small text-dark content">
                        {{ $comment->content }}
                    </div>
                    @if($comment->media_path)
                    <div class="mt-2">
                       @if($comment->isImage())
                        <a href="{{ asset('storage/' . $comment->media_path) }}" 
                                            data-fancybox="gallery-{{ $comment->id }}"  class="d-inline-block">
                            <img src="{{ asset('storage/'.$comment->media_path) }}" class="rounded" style="max-width:200px" ></a>
                        @elseif($comment->isVideo())
                        <a href="{{ asset('storage/' . $comment->media_path) }}" 
                                            data-fancybox="gallery-{{ $comment->id }}"  class="d-inline-block">
                            <video controls class="rounded" style="max-width:200px">
                                <source src="{{ asset('storage/'.$comment->media_path) }}">
                            </video></a>
                        @endif
                    </div>
                    @endif
                </div>
                                <div class="flex-grow-1 mt-2 open-post" data-id="{{ $comment->post->id }}">
                    <div class="bg-light rounded-4 px-3 py-2">
                        <div class="d-flex" style="gap:10px;">

                            <!-- Ảnh post -->
                            @if($comment->post->media->isNotEmpty())
                                <img src="{{ asset('storage/'.$comment->post->media->first()->file_path) }}"
                                    class="rounded"
                                    style="width:90px;height:90px;object-fit:cover;">
                            @endif
                            <div class="d-flex flex-column" style="flex:1; min-width:0;">
                                <div class="d-flex align-items-center mb-1" style="gap:6px;">
                                     <a href="{{ route('profile.detail', $comment->post->user->id) }}" onclick="event.stopPropagation()">
                                    <img src="{{ $comment->post->user->profile->avatar 
                                                ? asset('storage/'.$comment->user->profile->avatar) 
                                                : 'https://i.pravatar.cc/150' }}"
                                        class="rounded-circle"
                                        width="28" height="28">
                                     </a>
                                    <div class="small fw-semibold text-dark">
                                        {{ $comment->post->user->profile->display_name }} Bài viết
                                    </div>
                                </div>
                                <div class="small text-dark comment-textare" 
                                    style="max-height:90px; overflow-y:auto;">
                                    {{ $comment->post->content }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center mt-2 text-muted small" style="gap:15px;">
                    <span>
                        {{ $comment->created_at?->diffForHumans() }}
                    </span>
                    <button class="btn-reply-list me-3 like-comment-count" style="font-size:13px;"
                    data-comment-id="{{ $comment->id }}"
                    data-username="{{ $comment->user->profile->display_name }}"
                    data-post-id="{{ $comment->post->id }}">
                    {{ $comment->likes->count() }} lượt thích
                </button>
                    <button class="btn-reply open-post border-0 bg-transparent p-0 text-muted"
                        data-id="{{ $comment->post->id }}"
                        data-action="reply"
                        data-scroll-comment-id="{{ $comment->id }}">
                        Trả lời
                    </button>
                    <div class="ms-auto d-flex align-items-center" style="gap:10px;">
                        <button type="button"
                            class="btn-comment-like border-0 bg-transparent p-0"
                            data-comment-id="{{ $comment->id }}">
                            
                            @if($comment->likes->contains('user_id', auth()->id()))
                                <i class="bi bi-heart-fill text-danger"></i>
                            @else
                                <i class="bi bi-heart"></i>
                            @endif
                        </button>
                        <button type="button"
                            class="border-0 bg-transparent p-0">
                            
                            @if($comment->likes->contains('user_id', auth()->id()))
                                <i class="bi bi-hand-thumbs-down-fill text-danger"></i>
                            @else
                                <i class="bi bi-hand-thumbs-down"></i>
                            @endif
                        </button>

                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
@endforeach
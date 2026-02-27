@extends('layouts.app')

@section('content')
<style>
    body { background-color: #f0f2f5; }
    .fb-post-modal {
        max-width: 600px;
        margin: 2rem auto;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 12px 28px 0 rgba(0, 0, 0, 0.2);
    }
    .modal-header { padding: 15px; border-bottom: 1px solid #e5e5e5; }
    
    .content-area textarea {
        width: 100%; border: none; padding: 15px; font-size: 1.25rem; 
        outline: none; resize: none; min-height: 120px; line-height: 1.5;
    }

    #media-preview-wrapper {
        position: relative; width: 100%; background-color: #000;
        border-top: 1px solid #e5e5e5; border-bottom: 1px solid #e5e5e5;
        {{ $post->media->count() > 0 ? '' : 'display: none;' }}
    }
    .carousel-item img, .carousel-item video {
        width: 100%; height: 450px; object-fit: contain;
    }
    .carousel-control-prev, .carousel-control-next {
        width: 35px; height: 35px; background: rgba(26, 26, 26, 0.8);
        border-radius: 50%; top: 50%; transform: translateY(-50%); margin: 0 10px;
    }
    .remove-single-media-btn {
        position: absolute; top: 10px; right: 10px; z-index: 10;
        background: white; border-radius: 50%; border: none; width: 30px; height: 30px;
        display: flex; align-items: center; justify-content: center;
    }
</style>

<div class="container">
    <div class="fb-post-modal shadow">
        <div class="modal-header d-flex justify-content-between align-items-center">
            <div style="width: 36px;"></div>
            <h5 class="mb-0 fw-bold">Chỉnh sửa bài viết</h5>
            <a href="{{ route('posts.index') }}" class="btn-light rounded-circle p-2 text-dark text-decoration-none">
                <i class="bi bi-x-lg"></i>
            </a>
        </div>

        <form action="{{ route('posts.update', $post->id) }}" method="post" id="postForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="p-3 d-flex align-items-center">
                <img src="{{ Auth::user()->profile->avatar ? asset('storage/'.Auth::user()->profile->avatar) : 'https://via.placeholder.com/40' }}" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                <div class="ms-2">
                    <div class="fw-bold">{{ Auth::user()->profile->display_name ?? Auth::user()->email }}</div>
                    <select name="topic_id" class="form-select py-0 px-2 border-0 bg-light small" style="font-size: 12px; width: fit-content;" required>
                        @foreach($topics as $value)
                            <option value="{{ $value->id }}" {{ $post->topic_id == $value->id ? 'selected' : '' }}>{{ $value->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="content-area">
                <textarea name="content" id="post-content" oninput="autoResize(this)">{{ $post->content }}</textarea>
            </div>

            <div id="media-preview-wrapper">
                <div id="instaCarousel" class="carousel slide" data-bs-ride="false">
                    <div class="carousel-indicators" id="carousel-indicators">
                        @foreach($post->media as $index => $m)
                            <button type="button" data-bs-target="#instaCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index == 0 ? 'active' : '' }}"></button>
                        @endforeach
                    </div>
                    <div class="carousel-inner" id="carousel-inner">
                        @foreach($post->media as $index => $m)
                            <div class="carousel-item {{ $index == 0 ? 'active' : '' }}" id="media-item-{{ $m->id }}">
                                <button type="button" class="remove-single-media-btn shadow-sm" onclick="deleteMedia({{ $m->id }})">
                                    <i class="bi bi-x"></i>
                                </button>
                                @if($m->type == 'image')
                                    <img src="{{ asset('storage/' . $m->file_path) }}" class="d-block">
                                @else
                                    <video src="{{ asset('storage/' . $m->file_path) }}" controls class="d-block w-100" style="height:450px;"></video>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    
                    <button class="carousel-control-prev" type="button" data-bs-target="#instaCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#instaCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    </button>
                </div>
            </div>

            <div class="px-3 mt-3">
                <div class="form-check form-switch small mb-2">
                    <input class="form-check-input" type="checkbox" id="pinned" name="pinned" value="1" {{ $post->pinned ? 'checked' : '' }}>
                    <label class="form-check-label" for="pinned">Ghim bài viết này</label>
                </div>
                <div class="form-check form-switch small">
                    <input class="form-check-input" type="checkbox" id="is_comment_enabled" name="is_comment_enabled" value="1" {{ $post->is_comment_enabled ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_comment_enabled">Cho phép bình luận</label>
                </div>
            </div>

            <div class="add-to-post">
                <span class="fw-bold small text-muted">Thêm ảnh/video mới</span>
                <div class="tool-icons">
                    <i class="bi bi-image-fill text-success fs-4" style="cursor: pointer;" onclick="document.getElementById('file').click()"></i>
                </div>
            </div>
            
            <input type="file" class="d-none" id="file" name="file[]" multiple onchange="previewNewMedia()" accept="image/*,video/*">

            <button type="submit" class="btn-post-fb">Lưu thay đổi</button>
        </form>
    </div>
</div>

<script>
    function autoResize(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    }
    window.onload = () => autoResize(document.getElementById('post-content'));

    // Xử lý xóa 1 ảnh đang hiển thị
    function deleteMedia(mediaId) {
        if(!confirm('Xóa tệp này?')) return;

        fetch(`/posts/media/${mediaId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).then(res => {
            if(res.ok) {
                const item = document.getElementById(`media-item-${mediaId}`);
                item.remove();
                // Cập nhật lại slide đầu tiên làm active
                const items = document.querySelectorAll('.carousel-item');
                if(items.length > 0) {
                    items[0].classList.add('active');
                } else {
                    document.getElementById('media-preview-wrapper').style.display = 'none';
                }
            }
        });
    }

    function previewNewMedia() {
        // Code này để preview các ảnh sắp upload thêm (tương tự như trang Create)
        // Lưu ý: Ảnh mới chưa có ID nên nút X của ảnh mới sẽ chỉ xóa trên giao diện
        alert('Đã chọn tệp mới. Nhấn Lưu để cập nhật.');
    }
</script>
@endsection
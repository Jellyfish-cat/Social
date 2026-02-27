@extends('layouts.app')

@section('content')
<style>
  
    /* Nút X xóa ảnh: Kiểu dáng sang trọng hơn */
    .remove-single-media {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 10;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px); /* Hiệu ứng kính mờ */
        color: #fff;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255,255,255,0.3);
        transition: all 0.3s ease;
    }
    .remove-single-media:hover {
        background: #ff4d4f;
        border-color: #ff4d4f;
        transform: scale(1.1);
    }
    /* Custom Switch & Inputs */
    .form-check-input:checked {
        background-color: #0866ff;
        border-color: #0866ff;
    }
    .form-select-sm {
        border-radius: 8px;
        border-color: #dbdbdb;
        padding: 0.5rem;
    }
</style>

<div class="container-fluid py-4">
    <div class="post-modal">
        <div class="modal-header d-flex justify-content-between align-items-center">
            <div style="width: 36px;"></div>
            <h6 class="mb-0 fw-bold text-uppercase" style="letter-spacing: 1px; font-size: 0.85rem;">Chỉnh sửa bài viết</h6>
            <a href="{{ route('posts.index') }}" class="btn-light rounded-circle p-2 text-dark text-decoration-none transition" title="Hủy bỏ">
                <i class="bi bi-x-lg"></i>
            </a>
        </div>

        <form action="{{ route('posts.update', $post->id) }}" method="post" id="postForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="edit-main-content">
                <div class="media-column">
    <div id="instaCarousel" class="carousel slide w-100" data-bs-ride="false">
        <div class="carousel-indicators mb-3" id="carousel-indicators">
            @foreach($post->media as $index => $m)
                <button type="button" data-bs-target="#instaCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index == 0 ? 'active' : '' }}" aria-current="{{ $index == 0 ? 'true' : 'false' }}" style="width: 6px; height: 6px; border-radius: 50%;"></button>
            @endforeach
        </div>
        
        <div class="carousel-inner" id="preview-container">
            @foreach($post->media as $index => $m)
                <div class="carousel-item {{ $index == 0 ? 'active' : '' }}" id="media-item-{{ $m->id }}">
                    <button type="button" class="remove-single-media shadow" onclick="deleteCurrentMedia({{ $m->id }})" title="Xóa tệp này">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                    
                    @if($m->type == 'image')
                        <img src="{{ asset('storage/' . $m->file_path) }}" class="d-block" alt="Post Media">
                    @else
                        <video src="{{ asset('storage/' . $m->file_path) }}" controls class="d-block w-100"></video>
                    @endif
                </div>
            @endforeach
        </div>
        
        <button class="carousel-control-prev {{ $post->media->count() <= 1 ? 'd-none' : '' }}" type="button" data-bs-target="#instaCarousel" data-bs-slide="prev">
            <i class="bi bi-chevron-left bg-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"></i>
        </button>
        <button class="carousel-control-next {{ $post->media->count() <= 1 ? 'd-none' : '' }}" type="button" data-bs-target="#instaCarousel" data-bs-slide="next">
            <i class="bi bi-chevron-right bg-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"></i>
        </button>
    </div>
</div>

                <div class="info-column">
                    <div class="user-header d-flex align-items-center">
                        <img src="{{ Auth::user()->profile->avatar ? asset('storage/'.Auth::user()->profile->avatar) : 'https://i.pravatar.cc/150' }}" class="user-avatar border me-3">
                        <span class="fw-bold text-dark small">{{ Auth::user()->profile->display_name ?? Auth::user()->email }}</span>
                    </div>

                    <div class="content-area-wrapper">
                        <textarea name="content" placeholder="Bạn đang nghĩ gì...">{{ old('content', $post->content) }}</textarea>
                    </div>

                    <div class="extra-settings-wrapper d-flex flex-column justify-content-between">
                        <div>
                            <div class="mb-4">
                                <label class="form-label mb-2 fw-bold text-secondary small text-uppercase">Chủ đề</label>
                                <select name="topic_id" class="form-select form-select-sm shadow-none" required>
                                    @foreach($topics as $value)
                                        <option value="{{ $value->id }}" {{ $post->topic_id == $value->id ? 'selected' : '' }}>
                                            {{ $value->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4 border-top pt-3">
                                <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0 mb-3">
                                    <label class="form-check-label small fw-bold text-dark" for="pinned">Ghim bài viết này</label>
                                    <input class="form-check-input" type="checkbox" id="pinned" name="pinned" value="1" {{ $post->pinned ? 'checked' : '' }}>
                                </div>
                                <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0 mb-1">
                                    <label class="form-check-label small fw-bold text-dark" for="is_comment_enabled">Bật tính năng bình luận</label>
                                    <input class="form-check-input" type="checkbox" id="is_comment_enabled" name="is_comment_enabled" value="1" {{ $post->is_comment_enabled ? 'checked' : '' }}>
                                </div>
                            </div>

                            <a href="javascript:void(0)" class="btn-add-media d-flex align-items-center justify-content-center w-100 mt-2" onclick="document.getElementById('file').click()">
                                <i class="bi bi-plus-circle-dotted fs-5 me-2"></i>
                                <span class="small fw-bold">Thêm ảnh hoặc video</span>
                            </a>
                            <input type="file" class="d-none" id="file" name="file[]" multiple accept="image/*,video/*" required onchange="previewFiles()">
                        </div>

                        <button type="submit" class="btn-update-fixed shadow-sm">
                            Cập nhật bài viết
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function previewFiles() {
        const previewContainer = document.getElementById('preview-container');
        const indicatorsContainer = document.getElementById('carousel-indicators');
        const fileInput = document.getElementById('file');
        const files = fileInput.files;
        
        const prevBtn = document.querySelector('.carousel-control-prev');
        const nextBtn = document.querySelector('.carousel-control-next');

        if (files.length === 0) return;

        Array.from(files).forEach((file) => {
            const reader = new FileReader();

            reader.onload = function (e) {
                // 1. Tạo Slide mới
                const div = document.createElement('div');
                const totalExisting = previewContainer.querySelectorAll('.carousel-item').length;
                
                // Nếu chưa có ảnh nào thì cái đầu tiên sẽ active
                div.className = `carousel-item ${totalExisting === 0 ? 'active' : ''}`;
                
                let mediaHtml = '';
                if (file.type.includes('image')) {
                    mediaHtml = `<img src="${e.target.result}" class="d-block w-100">`;
                } else if (file.type.includes('video')) {
                    mediaHtml = `<video src="${e.target.result}" controls class="d-block w-100"></video>`;
                }
                
                // Thêm nút xóa ảo cho ảnh mới (chỉ xóa khỏi giao diện nếu cần, 
                // nhưng đơn giản nhất là để người dùng chọn lại file nếu nhầm)
                div.innerHTML = mediaHtml;
                previewContainer.appendChild(div);

                // 2. Tạo Indicator mới (dấu chấm nhỏ)
                const newIndicator = document.createElement('button');
                newIndicator.type = 'button';
                newIndicator.dataset.bsTarget = '#instaCarousel';
                newIndicator.dataset.bsSlideTo = totalExisting;
                newIndicator.style.cssText = "width: 6px; height: 6px; border-radius: 50%;";
                if (totalExisting === 0) newIndicator.className = 'active';
                indicatorsContainer.appendChild(newIndicator);

                // 3. Hiện nút điều hướng nếu > 1 ảnh
                if (totalExisting + 1 > 1) {
                    prevBtn.classList.remove('d-none');
                    nextBtn.classList.remove('d-none');
                }
            };

            reader.readAsDataURL(file);
        });
    }

    function deleteCurrentMedia(mediaId) {
        if (!confirm('Tệp này sẽ bị xóa vĩnh viễn khỏi server. Tiếp tục?')) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(`/posts/media/${mediaId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = document.getElementById(`media-item-${mediaId}`);
                if (item) {
                    const wasActive = item.classList.contains('active');
                    item.remove();

                    // Cập nhật lại danh sách còn lại
                    const remaining = document.querySelectorAll('.carousel-item');
                    const indicators = document.querySelectorAll('#carousel-indicators button');
                    
                    // Xóa bớt 1 indicator cuối cùng
                    if (indicators.length > 0) indicators[indicators.length - 1].remove();

                    if (remaining.length > 0) {
                        if (wasActive) remaining[0].classList.add('active');
                        if (remaining.length <= 1) {
                            document.querySelector('.carousel-control-prev').classList.add('d-none');
                            document.querySelector('.carousel-control-next').classList.add('d-none');
                        }
                    } else {
                        location.reload(); // Không còn ảnh nào thì reload
                    }
                }
            }
        })
        .catch(err => alert('Lỗi: Không thể kết nối đến máy chủ.'));
    }
</script>
@endsection
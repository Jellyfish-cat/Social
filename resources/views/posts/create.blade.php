@extends('layouts.app')

@section('content')
<style>
    body { background-color: #f8f9fa; }

    /* Container chính: Bo góc mạnh và đổ bóng mềm giống Code 2 */
    .post-modal {
        max-width: 1200px;
        margin: 2rem auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        border: 1px solid rgba(0,0,0,0.05);
    }
    .modal-header { 
        padding: 1rem 1.5rem; 
        border-bottom: 1px solid #efefef; 
        background: #fff;
    }

    /* Layout chia đôi */
    .edit-main-content {
        display: flex;
        flex-wrap: nowrap;
        min-height: 650px;
    }

    /* CỘT TRÁI: Media Slide (Màu nền tối giống Code 2) */
    .media-column {
        flex: 1.2;
        background-color: #ffffff; 
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        min-width: 500px;
    }

    .carousel-item img, .carousel-item video {
        width: 100%;
        height: 650px;
        object-fit: contain;
    }

    /* Placeholder cho trường hợp chưa có ảnh */
    .placeholder-content {
        height: 650px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #666; /* Màu chữ sáng hơn trên nền tối */
    }

    /* CỘT PHẢI: Thông tin */
    .info-column {
        width: 420px;
        display: flex;
        flex-direction: column;
        border-left: 1px solid #efefef;
        background: #fff;
    }

    /* Nội dung văn bản */
    .content-area-wrapper {
        height: 50%; 
        overflow-y: auto;
        border-bottom: 1px solid #efefef;
        padding: 5px;
    }

    .content-area-wrapper textarea {
        width: 100%;
        height: 100%;
        border: none;
        padding: 1.5rem;
        font-size: 1rem;
        outline: none;
        resize: none;
        line-height: 1.6;
        color: #262626;
    }

    /* Cài đặt extra */
    .extra-settings-wrapper {
        height: 50%;
        padding: 1.5rem;
        background: #fff;
        overflow-y: auto;
    }

    /* User Profile Header */
    .user-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #efefef;
    }
    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
    }

    /* Nút cập nhật/đăng bài */
    .btn-update-fixed {
        margin-top: 1.5rem;
        background: linear-gradient(45deg, #0095f6, #0074cc);
        border: none;
        border-radius: 8px;
        padding: 0.75rem;
        color: white;
        font-weight: 600;
        width: 100%;
        transition: opacity 0.2s;
    }
    .btn-update-fixed:hover {
        opacity: 0.9;
        color: #fff;
    }

    /* Nút thêm media nét đứt giống Code 2 */
    .btn-add-media {
        border: 1px dashed #dbdbdb;
        border-radius: 10px;
        padding: 1rem;
        background: #fafafa;
        transition: all 0.2s;
        text-decoration: none;
        color: #8e8e8e;
    }
    .btn-add-media:hover {
        background: #f0f2f5;
        border-color: #a8a8a8;
        color: #262626;
    }

    @media (max-width: 992px) {
        .edit-main-content { flex-direction: column; }
        .info-column { width: 100%; height: auto; }
        .content-area-wrapper, .extra-settings-wrapper { height: auto; }
        .media-column { min-height: 450px; min-width: 100%; }
    }
</style>

<div class="container-fluid py-4">
    <div class="post-modal">
        <div class="modal-header d-flex justify-content-between align-items-center">
            <div style="width: 36px;"></div>
            <h6 class="mb-0 fw-bold text-uppercase" style="letter-spacing: 1px; font-size: 0.85rem;">Tạo bài viết mới</h6>
            <a href="{{ route('posts.index') }}" class="btn-light rounded-circle p-2 text-dark text-decoration-none transition" title="Hủy bỏ">
                <i class="bi bi-x-lg"></i>
            </a>
        </div>

        <form action="{{ route('posts.store') }}" method="post" id="postForm" enctype="multipart/form-data">
            @csrf

            <div class="edit-main-content">
                <div class="media-column">
                    <div id="instaCarousel" class="carousel slide w-100" data-bs-ride="false">
                        <div class="carousel-indicators mb-3" id="carousel-indicators"></div>

                        <div class="carousel-inner" id="preview-container">
                            <div class="carousel-item active">
                                <div class="placeholder-content">
                                    <i class="bi bi-image fs-1 mb-3"></i>
                                    <p class="fw-medium">Hiện chưa có ảnh</p>
                                </div>
                            </div>
                        </div>
                        
                        <button class="carousel-control-prev d-none" type="button" data-bs-target="#instaCarousel" data-bs-slide="prev">
                            <i class="bi bi-chevron-left bg-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"></i>
                        </button>
                        <button class="carousel-control-next d-none" type="button" data-bs-target="#instaCarousel" data-bs-slide="next">
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
                        <textarea name="content" placeholder="Bạn đang nghĩ gì..." required>{{ old('content') }}</textarea>
                    </div>

                    <div class="extra-settings-wrapper d-flex flex-column justify-content-between">
                        <div>
                            <div class="mb-4">
                                <label class="form-label mb-2 fw-bold text-secondary small text-uppercase">Chủ đề</label>
                                <select name="topic_id" class="form-select form-select-sm shadow-none" required>
                                    <option value="">Chọn chủ đề</option>
                                    @foreach($topics as $value)
                                        <option value="{{ $value->id }}">{{ $value->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4 border-top pt-3">
                                <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0 mb-3">
                                    <label class="form-check-label small fw-bold text-dark" for="pinned">Ghim bài viết này</label>
                                    <input class="form-check-input" type="checkbox" id="pinned" name="pinned" value="1">
                                </div>
                                <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0 mb-1">
                                    <label class="form-check-label small fw-bold text-dark" for="is_comment_enabled">Bật tính năng bình luận</label>
                                    <input class="form-check-input" type="checkbox" id="is_comment_enabled" name="is_comment_enabled" value="1" checked>
                                </div>
                            </div>
                            
                            <a href="javascript:void(0)" class="btn-add-media d-flex align-items-center justify-content-center w-100 mt-2" onclick="document.getElementById('file').click()">
                                <i class="bi bi-plus-circle-dotted fs-5 me-2"></i>
                                <span class="small fw-bold">Thêm ảnh hoặc video</span>
                            </a>
                            <input type="file" class="d-none" id="file" name="file[]" multiple accept="image/*,video/*" onchange="previewFiles()">
                        </div>

                        <button type="submit" class="btn-update-fixed shadow-sm">
                            Đăng bài viết
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

        previewContainer.innerHTML = '';
        indicatorsContainer.innerHTML = '';

        if (files.length === 0) {
            previewContainer.innerHTML = `
                <div class="carousel-item active">
                    <div class="placeholder-content">
                        <i class="bi bi-image fs-1 mb-3"></i>
                        <p class="fw-medium">Hiện chưa có ảnh</p>
                    </div>
                </div>`;
            prevBtn.classList.add('d-none');
            nextBtn.classList.add('d-none');
            return;
        }

        Array.from(files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const div = document.createElement('div');
                div.className = `carousel-item ${index === 0 ? 'active' : ''}`;
                
                let mediaHtml = '';
                if (file.type.includes('image')) {
                    mediaHtml = `<img src="${e.target.result}" class="d-block w-100">`;
                } else if (file.type.includes('video')) {
                    mediaHtml = `<video src="${e.target.result}" controls class="d-block w-100"></video>`;
                }
                
                div.innerHTML = mediaHtml;
                previewContainer.appendChild(div);

                const newIndicator = document.createElement('button');
                newIndicator.type = 'button';
                newIndicator.dataset.bsTarget = '#instaCarousel';
                newIndicator.dataset.bsSlideTo = index;
                newIndicator.style.cssText = "width: 6px; height: 6px; border-radius: 50%;";
                if (index === 0) newIndicator.className = 'active';
                indicatorsContainer.appendChild(newIndicator);

                if (files.length > 1) {
                    prevBtn.classList.remove('d-none');
                    nextBtn.classList.remove('d-none');
                }
            };
            reader.readAsDataURL(file);
        });
    }
</script>
@endsection
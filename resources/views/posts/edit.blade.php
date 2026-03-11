@extends('layouts.app')

@section('content')
<style>
  
   
</style>

<div class="container-fluid py-4">
    <div class="post-modal">
        <div class="modal-header d-flex justify-content-between align-items-center">
            <div style="width: 36px;"></div>
            <h6 class="mb-0 fw-bold text-uppercase" style="letter-spacing: 1px; font-size: 0.85rem;">Chỉnh sửa bài viết</h6>
            <a href="javascript:history.back()" class="btn-light rounded-circle p-2 text-dark text-decoration-none transition" title="Hủy bỏ">
                <i class="bi bi-x-lg"></i>
            </a>
        </div>
        <form action="{{ route('posts.update', $post->id) }}" method="post" id="postForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="deleted_media_ids" id="deleted_media_ids">
            <div class="edit-main-content">
                <div class="media-column">
    <div id="instaCarousel" class="carousel slide w-100" data-bs-ride="false">
        <div class="carousel-indicators mb-3" id="carousel-indicators">
            @foreach($post->media as $index => $m)
                <button type="button" data-bs-target="#instaCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index == 0 ? 'active' : '' }}" aria-current="{{ $index == 0 ? 'true' : 'false' }}" style="width: 8px; height: 8px; border-radius: 50%; margin-bottom:10px"></button>
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
                            <input type="file" class="d-none" id="file" name="file[]" multiple accept="image/*,video/*" onchange="previewEditFiles()">
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

</script>
@endsection
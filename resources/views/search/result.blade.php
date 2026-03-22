@extends('layouts.app')
@section('content')

<div class="container feed-container py-4" >
    <div class="row justify-content-center">
        <!-- Khu vực hiển thị Search Header & Tabs -->
        <div class="col-lg-8">
            
            <!-- Ô Tìm kiếm & Tiêu đề nổi bật -->
            <div class="card shadow-sm border-0 rounded-4 p-4 text-center mb-4">
                <div class="mb-3">
                    <i class="bi bi-search fs-1 text-primary bg-light rounded-circle p-3 d-inline-flex m-3"></i>
                    <h3 class="fw-bold mt-2">Kết quả tìm kiếm</h3>
                    <p class="text-muted">Hiển thị kết quả tìm kiếm cho: <span class="fw-bold text-dark">"{{ $keyword }}"</span></p>
                </div>
                
                <form action="{{ route('search.result') }}" method="GET" class="search-input-wrapper mx-auto mt-2" style="max-width: 500px;">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" name="q" value="{{ $keyword }}" placeholder="Tìm kiếm người dùng, bài viết, hashtag..." style="border: 1px solid transparent;">
                </form>
            </div>

            <!-- Tabs Điều hướng giữa các phân loại -->
            <div class="card shadow-sm border-0 rounded-4 px-3 pt-3 mb-4">
                <ul class="nav nav-pills gap-2 pb-3 flex-nowrap overflow-x-auto" id="searchTabs" role="tablist" style="white-space: nowrap; scrollbar-width: none;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill fw-semibold px-4" id="post-tab"  type="button" role="tab"><i class="bi bi-card-text me-2"></i>Bài viết</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-dark hover-bg-light rounded-pill fw-semibold px-4" id="people-tab"  type="button" role="tab"><i class="bi bi-people me-2"></i>Mọi người</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-dark hover-bg-light rounded-pill fw-semibold px-4" id="photos-tab"  type="button" role="tab"><i class="bi bi-images me-2"></i>Hình ảnh / Video</button>
                    </li>
                </ul>
            </div>

            <div id="search-results-container">
            <!-- Tab Bài Viết (POSTS) -->
                @include('search.partials.post-list', ['posts' => $posts])
                <!-- Tab Mọi Người (PEOPLE) (Preview Skeleton) -->
                </div>

            </div>
        </div>

    </div>
</div>
<div class="modal fade" id="postDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 1290px;">
        <div class="modal-content">
            <div class="modal-body p-0" id="postDetailContent">
                <!-- Nội dung chi tiết post sẽ load vào đây -->
            </div>
        </div>
    </div>
</div>      
<style>
/* Utilities hỗ trợ thêm (Dùng inline style theo đặc thù thiết kế kết quả tìm kiếm) */
.transition-all { transition: all 0.2s ease; }
.hover-scale:hover { transform: scale(1.05); }
.cursor-pointer { cursor: pointer; }
.drop-shadow { filter: drop-shadow(0 0 2px rgba(0,0,0,0.5)); }
.object-fit-cover { object-fit: cover !important; }

/* Tùy chỉnh Tab Navigation */
.nav-pills .nav-link {
    color: #6c757d;
    transition: all 0.2s;
}
.nav-pills .nav-link.active, .nav-pills .show>.nav-link {
    background-color: #f1f2f5;
    color: #000000 !important;
}
.nav-pills .nav-link.active i {
    color: #000000 !important;
}
</style>

@endsection
@extends('layouts.app')
@section('content')
<div class="container-fluid" >
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">
            <i class="bi bi-file-earmark-text"></i> Quản lý báo cáo
        </h3>
    </div>
            <!-- Tabs Điều hướng giữa các phân loại -->
            <div class="card shadow-sm border-0 rounded-4 px-3 pt-3 mb-4">
                <ul class="nav nav-pills gap-2 pb-3 flex-nowrap overflow-x-auto" id="reportTabs" role="tablist" style="white-space: nowrap; scrollbar-width: none;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill fw-semibold px-4" id="post-tab"  type="button" role="tab"><i class="bi bi-card-text me-2"></i>Bài viết</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-dark hover-bg-light rounded-pill fw-semibold px-4" id="people-tab"  type="button" role="tab"><i class="bi bi-people me-2"></i>Mọi người</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-dark hover-bg-light rounded-pill fw-semibold px-4" id="comment-tab"  type="button" role="tab"><i class="bi bi-chat-left-text me-2"></i>Bình luận</button>
                    </li>
                </ul>
            </div>
            <div class="card shadow-sm" id="report-results-container" data-tab={{$tab}}>
            <!-- Tab Bài Viết (POSTS) -->
                @include('admin.partials.report-list')
                <!-- Tab Mọi Người (PEOPLE) (Preview Skeleton) -->
                </div>
</div>
<style>
/* Utilities hỗ trợ thêm (Dùng inline style theo đặc thù thiết kế kết quả tìm kiếm) */
.transition-all { transition: all 0.2s ease; }
.hover-scale:hover { transform: scale(1.05); }
.cursor-pointer { cursor: pointer; }
.drop-shadow { filter: drop-shadow(0 0 2px rgba(0,0,0,0.5)); }
.object-fit-cover { object-fit: cover !important; }
</style>

@push('scripts')
<script type="module" src="{{ asset('resources/js/modules/report.js') }}"></script>
@endpush
@endsection
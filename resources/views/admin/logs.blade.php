@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary fw-bold">
            <i class="bi bi-clock-history me-2"></i> Nhật ký hoạt động
        </h3>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold text-secondary">Danh sách hoạt động mới nhất</span>
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3">Tổng: {{ $logs->total() }}</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr class="text-secondary small text-uppercase fw-bold">
                            <th class="ps-4 py-3" width="5%">#</th>
                            <th width="12%">
                                <select class="form-select form-select-sm border-0 bg-light fw-bold" id="filter-event" style="box-shadow: none;">
                                     <option value="">Sự kiện</option>
                                     <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>Tạo mới</option>
                                     <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>Cập nhật</option>
                                     <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>Xóa</option>
                                 </select>
                            </th>
                            <th width="15%">
                                <select class="form-select form-select-sm border-0 bg-light fw-bold" id="filter-role" style="box-shadow: none;">
                                    <option value="">Người thực hiện</option>
                                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="moderator" {{ request('role') == 'moderator' ? 'selected' : '' }}>Moderator</option>
                                    <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>Người dùng</option>
                                </select>
                            </th>
                            <th width="15%">
                                <select class="form-select form-select-sm border-0 bg-light fw-bold" id="filter-subject" style="box-shadow: none;">
                                    <option value="">Đối tượng</option>
                                    <option value="User" {{ request('subject') == 'User' ? 'selected' : '' }}>Người dùng</option>
                                    <option value="Post" {{ request('subject') == 'Post' ? 'selected' : '' }}>Bài viết</option>
                                    <option value="Comment" {{ request('subject') == 'Comment' ? 'selected' : '' }}>Bình luận</option>
                                    <option value="Message" {{ request('subject') == 'Message' ? 'selected' : '' }}>Tin nhắn</option>
                                    <option value="Conversation" {{ request('subject') == 'Conversation' ? 'selected' : '' }}>Hội thoại</option>
                                    <option value="Report" {{ request('subject') == 'Report' ? 'selected' : '' }}>Báo cáo</option>
                                    <option value="Topic" {{ request('subject') == 'Topic' ? 'selected' : '' }}>Chủ đề</option>
                                    <option value="Search" {{ request('subject') == 'Search' ? 'selected' : '' }}>Tìm kiếm</option>
                                </select>
                            </th>
                            <th class="ps-4 py-3" width="10%">Nhóm</th>
                            <th class="ps-4 py-3" width="30%">Chi tiết</th>
                            <th class="ps-4 py-3" width="15%">Thời gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $lastBatch = null; @endphp
                        @forelse($logs as $log)
                            @php
                                $currentBatch = $log->batch_uuid;
                                $isSameBatch = $currentBatch && $lastBatch === $currentBatch;
                                // Để xác định dòng cuối cùng trong 1 nhóm (vì danh sách là latest nên nhóm nằm ngược)
                                $nextLog = $logs[$loop->index + 1] ?? null;
                                $isEndOfBatch = $currentBatch && (!$nextLog || $nextLog->batch_uuid !== $currentBatch);
                                $lastBatch = $currentBatch;
                            @endphp
                        <tr class="border-bottom-0 log-row {{ $currentBatch ? 'has-batch' : '' }} {{ $isSameBatch ? 'batch-inner' : 'batch-start' }} {{ $isEndOfBatch ? 'batch-end' : '' }}" data-id="{{ $log->id }}">
                            <td class="ps-4 text-muted small">{{ $loop->iteration + ($logs->currentPage() - 1) * $logs->perPage() }}</td>
                            <td>
                                @php
                                    $badgeClass = match($log->event) {
                                        'created' => 'bg-success',
                                        'updated' => 'bg-warning',
                                        'deleted' => 'bg-danger',
                                        default => 'bg-info'
                                    };
                                    $eventLabel = match($log->event) {
                                        'created' => 'Tạo mới',
                                        'updated' => 'Cập nhật',
                                        'deleted' => 'Xóa',
                                        'search', 'search_admin', 'search_message', 'search_user_convo' => 'Tìm kiếm',
                                        default => $log->event
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} rounded-pill px-3 py-2" style="font-size: 0.75rem;">
                                    {{ $eventLabel }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="ms-0">
                                        <div class="fw-semibold text-dark small">{{ $log->user->profile->display_name ?? $log->user->name ?? 'Hệ thống' }}</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">{{ $log->user->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-secondary small fw-medium">
                                    @php
                                        $modelName = class_basename($log->subject_type);
                                        $translatedName = match($modelName) {
                                            'User' => 'Người dùng', 'Post' => 'Bài viết', 'Comment' => 'Bình luận',
                                            'Message' => 'Tin nhắn', 'Conversation' => 'Hội thoại', 'Report' => 'Báo cáo',
                                            'Topic' => 'Chủ đề', '' => 'Hệ thống', default => $modelName
                                        };
                                    @endphp
                                    @if($translatedName === 'Hệ thống' && str_contains($log->event, 'search'))
                                        Tìm kiếm
                                    @else
                                        {{ $translatedName }} {{ $log->subject_id ? "(ID: $log->subject_id)" : "" }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if($currentBatch)
                                    <span class="badge bg-light text-primary border border-primary-subtle font-monospace" 
                                          style="font-size: 0.65rem;">
                                        <i class="bi bi-layers-half me-1"></i>{{ substr($currentBatch, -6) }}
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="position-relative">
                                @if($currentBatch)
                                    <div class="batch-connector"></div>
                                @endif
                                @php
                                    $hasAttributes = isset($log->changes()['attributes']);
                                    $hasProperties = !empty($log->properties) && count($log->properties) > 0;
                                @endphp

                                @if($hasAttributes || $hasProperties)
                                    <div class="bg-light p-2 rounded-3 small text-muted border border-light-subtle" style="max-height: 100px; overflow-y: auto;">
                                        <strong>Dữ liệu chi tiết:</strong>
                                        <ul class="mb-0 ps-3">
                                            {{-- Hiển thị thay đổi Model --}}
                                            @if($hasAttributes)
                                                @foreach($log->changes()['attributes'] as $key => $val)
                                                    @if($key !== 'updated_at')
                                                        <li><span class="text-primary">{{ $key }}</span>: {{ is_array($val) ? json_encode($val) : Str::limit($val, 50) }}</li>
                                                    @endif
                                                @endforeach
                                            @endif

                                            {{-- Hiển thị thuộc tính tùy chỉnh (Search, Referer...) --}}
                                            @if($hasProperties)
                                                @foreach($log->properties as $key => $val)
                                                    @php
                                                        $displayVal = $val;
                                                    @endphp
                                                    <li><span class="text-success">{{ $key }}</span>: {{ is_array($displayVal) ? json_encode($displayVal) : Str::limit($displayVal, 50) }}</li>
                                                @endforeach
                                            @endif
                                        </ul>
                                    </div>
                                @elseif(!$log->event || in_array($log->event, ['updated', 'created', 'deleted']))
                                    <span class="text-muted italic small">Không có chi tiết</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-dark small">{{ $log->created_at->format('H:i d/m/Y') }}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">{{ $log->created_at->diffForHumans() }}</div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <p class="text-muted mb-0">Chưa có nhật ký hoạt động nào được ghi lại.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
        <div class="card-footer bg-white py-3 border-top-0">
            <div class="d-flex justify-content-center">
                {{ $logs->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filters = ['event', 'role', 'subject'];
    
    function applyFilters() {
        const url = new URL(window.location.href);
        filters.forEach(f => {
            const el = document.getElementById('filter-' + f);
            if (el.value) url.searchParams.set(f, el.value);
            else url.searchParams.delete(f);
        });
        url.searchParams.set('page', 1);
        window.location.href = url.href;
    }

    filters.forEach(f => {
        document.getElementById('filter-' + f).addEventListener('change', applyFilters);
    });
});
</script>

<style>
.log-row:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.02);
}
.badge {
    font-weight: 500;
}
.rounded-4 {
    border-radius: 1rem !important;
}

/* Batch Visualization */
.batch-start {
    border-top: 1px solid #dee2e6 !important;
}
.batch-inner {
    border-top: none !important;
}
.batch-inner td {
    padding-top: 0.25rem !important;
    padding-bottom: 0.25rem !important;
}
.batch-connector {
    position: absolute;
    left: -2px;
    top: 0;
    bottom: 0;
    width: 3px;
    background-color: #0d6efd;
    opacity: 0.5;
    z-index: 1;
}
.batch-start .batch-connector {
    top: 50%;
    border-top-left-radius: 3px;
    border-top-right-radius: 3px;
}
.batch-end .batch-connector {
    bottom: 50%;
    border-bottom-left-radius: 3px;
    border-bottom-right-radius: 3px;
}
.batch-inner:not(.batch-end) .batch-connector {
    /* Full height */
}
.batch-start.batch-end .batch-connector {
    display: none; /* Single item in batch */
}
</style>
@endsection

@php
    $title = 'Danh sách báo cáo ' . ($type === 'post' ? 'bài viết' : ($type === 'people' ? 'thành viên' : 'bình luận'));
@endphp
<div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
    <span>{{ $title }}</span>
    <span class="badge bg-white text-primary count-report">Tổng: {{ $values->total() }}</span>
</div>
<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0 report-table">
            <thead class="table-light text-center">
                <tr>
                    <th width="5%">#</th>
                    <th width="30%">Nội dung báo cáo</th>
                    <th width="20%">Lý do</th>
                    <th width="15%">Người báo cáo</th>
                    <th width="15%">Trạng thái</th>
                    <th width="15%">Hành động</th>
                </tr>
            </thead>
            <tbody>
            @forelse($values as $value)
            <tr class="report-item">
                <td class="text-center">
                    {{ $loop->iteration + ($values->currentPage() - 1) * $values->perPage() }}
                </td>
                <td class="text-start">
                    @if($type === 'post' && $value->target)
                        <div class="fw-bold text-truncate" style="max-width: 300px;">
                            {{ Str::limit($value->target->content, 100) }}
                        </div>
                        <small class="text-muted">ID Bài viết: {{ $value->target->id }}</small>
                    @elseif($type === 'people' && $value->target)
                        <div class="d-flex align-items-center">
                            <img src="{{ $value->target->profile->avatar ?? asset('assets/images/default-avatar.png') }}" class="rounded-circle me-2" width="30" height="30">
                            <div>
                                <div class="fw-bold">{{ $value->target->profile->display_name ?? $value->target->name }}</div>
                                <small class="text-muted">{{ $value->target->email }}</small>
                            </div>
                        </div>
                    @elseif($type === 'comment' && $value->target)
                        <div class="text-truncate" style="max-width: 300px;">
                            {{ Str::limit($value->target->content, 100) }}
                        </div>
                        <small class="text-muted">ID Bình luận: {{ $value->target->id }}</small>
                    @else
                        <span class="text-danger italic">Nội dung đã bị xóa hoặc không tồn tại</span>
                    @endif
                </td>
                <td>
                    {{ $value->reason }}
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <img src="{{ $value->user->profile->avatar ?? asset('assets/images/default-avatar.png') }}" class="rounded-circle me-2" width="25" height="25">
                        <span>{{ $value->user->profile->display_name ?? $value->user->name }}</span>
                    </div>
                </td>
                <td class="text-center">
                    @if($value->status === 'pending')
                        <span class="badge bg-warning text-dark">Chờ xử lý</span>
                    @elseif($value->status === 'resolved')
                        <span class="badge bg-success">Đã xử lý</span>
                    @else
                        <span class="badge bg-secondary">{{ $value->status }}</span>
                    @endif
                </td>
                <td class="text-center">
                    <div class="btn-group">
                        @if($type === 'post' && $value->target)
                            <a href="{{ route('posts.detail', $value->target->id) }}"  class="btn btn-info btn-sm" title="Xem chi tiết">
                                <i class="bi bi-eye"></i>
                            </a>
                        @elseif($type === 'people' && $value->target)
                            <a href="{{ route('profile.detail', $value->target->id) }}" class="btn  btn-info btn-sm" title="Xem trang cá nhân">
                                <i class="bi bi-eye"></i>
                            </a>
                        @endif
                        <button class="btn btn-danger btn-sm btn-delete-report" data-id="{{ $value->id }}" title="Xóa báo cáo">
                            <i class="bi bi-trash"></i>
                        </button>
                                  <button class="btn btn-success btn-sm btn-check-report" data-id="{{ $value->id }}" title="Xóa báo cáo">
                             <i class="bi bi-check-circle-fill"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted p-4">
                    Chưa có báo cáo nào cho mục này.
                </td>
            </tr> 
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="p-3 d-flex justify-content-center">
    {{ $values->appends(['tab' => $type])->links() }}
</div>

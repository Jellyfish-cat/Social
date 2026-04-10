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
                    <th width="20%">Lý do phổ biến</th>
                    @if($tab === 'pending')
                    <th width="15%">Số lượt</th>
                    @else
                    <th width="15%">Người báo cáo</th>
                    @endif
                    <th width="15%">Trạng thái</th>
                    <th width="15%">Hành động</th>
                </tr>
            </thead>
            <tbody>
            @forelse($values as $value)
            <tr class="{{$item}}">
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
                    @if($value->category)
                        <span class="badge bg-danger">{{ $value->category }}</span>
                        @if($value->reason)
                            <div class="text-muted mt-1" style="font-size: 0.85em;">{{ $value->reason }}</div>
                        @endif
                    @else
                        {{ $value->reason }}
                    @endif
                </td>
                <td>
                    @if($tab === 'pending')
                        <span class="badge bg-secondary mb-1">{{ __('Có ' . $value->total_reports . ' người đã báo cáo') }}</span>
                    @else
                        <div class="d-flex align-items-center">
                            <img src="{{ $value->user->profile->avatar ?? asset('assets/images/default-avatar.png') }}" class="rounded-circle me-2" width="25" height="25">
                            <span>{{ $value->user->profile->display_name ?? $value->user->name }}</span>
                        </div>
                    @endif
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
                            @elseif($type === 'comment' && $value->target)
                            <a  class="btn btn-info btn-sm open-post" data-id="{{$value->target->post_id}}"
                                data-scroll-comment-id="{{$value->target->id}}" data-action="reply" title="Xem trang cá nhân">
                                <i class="bi bi-eye"></i>
                            </a>
                        @endif

                                 
                             @if($tab==='pending')
                                <div class="btn-group">
                                    <button type="button" class="btn btn-warning btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Xử lý báo cáo">
                                        <i class="bi bi-shield-exclamation"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li><a class="dropdown-item btn-check-report cursor-pointer text-danger" data-id="{{ $value->id }}" data-action="hide"><i class="bi bi-eye-slash me-2"></i>Ẩn nội dung</a></li>
                                        <li><a class="dropdown-item btn-check-report cursor-pointer text-success" data-id="{{ $value->id }}" data-action="dismiss"><i class="bi bi-check-circle me-2"></i>Bỏ qua (Không VP)</a></li>
                                    </ul>
                                </div>
                            @else
                             <button class="btn btn-danger btn-sm {{$delete}}" data-id="{{ $tab === 'pending' ? $value->id : $value->target_id }}" title="Xóa {{ $tab === 'pending' ? 'báo cáo' : 'nội dung' }}">
                            <i class="bi bi-trash"></i>
                        </button>
                                <button class="btn btn-success btn-sm btn-check-report" data-id="{{ $value->id }}" data-action="restore" title="Khôi phục trạng thái chờ duyệt báo cáo">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>
                            @endif
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

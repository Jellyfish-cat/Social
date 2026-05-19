@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">
            <i class="bi bi-file-earmark-text"></i> Quản lý hội thoại
        </h3>
    </div>
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            Danh sách hội thoại
             <span class="badge bg-white text-primary count-conversation">Tổng: {{ $conversations->total() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 post-table">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="5%">#</th>
                            <th width="30%">Thành viên</th>
                            <th width="10%">Loại</th>
                            <th width="10%">Phạm vi</th>
                            <th width="15%">Quyền xem</th>
                            <th width="10%">Trạng thái</th>
                            <th width="10%">Số tin nhắn</th>
                            <th width="15%">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($conversations as $value)
                    <tr class="conversation-item">
                        <td class="text-center">
                            {{ $loop->iteration }}
                        </td>
                        <td class="text-start">
                            @foreach ($value->users as $item)
                                {{ $item->profile->display_name ?? $item->email }}{{ !$loop->last ? ',' : '' }}
                            @endforeach
                        </td>
                        <td class="text-center">
                            @if($value->type === 'private')
                                <span class="badge bg-warning text-dark">Cá nhân</span>
                            @elseif($value->type === 'group')
                                <span class="badge bg-success">Nhóm</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($value->users->contains('role', "admin") || $value->users->contains('role', "moderator"))
                                <span class="badge bg-success-subtle text-success">Nội bộ</span>
                            @else
                                <span class="badge bg-primary-subtle text-primary">Người dùng</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($value->allow_view)
                                <span class="badge bg-info text-dark"><i class="bi bi-unlock-fill me-1"></i>Công khai</span>
                            @else
                                <span class="badge bg-secondary"><i class="bi bi-lock-fill me-1"></i>Riêng tư</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($value->status === 'show')
                                <span class="badge bg-success">Hoạt động</span>
                            @else
                                <span class="badge bg-danger">Đã ẩn</span>
                            @endif
                        </td>
                        <td class="text-center">
                            {{ $value->messages_count ?? 0 }}
                        </td>
                        <td class="text-center">
                            @if($value->allow_view || $value->users->contains('role', 'admin') || $value->users->contains('role', 'moderator'))
                                <a href="{{route("admin.conversations.show", $value->id)}}"
                                   class="btn btn-info btn-sm" title="Xem chi tiết">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @else
                                <button class="btn btn-secondary btn-sm disabled" title="Quyền riêng tư đã khóa">
                                    <i class="bi bi-lock"></i>
                                </button>
                            @endif
                            <button class="btn btn-danger btn-sm btn-delete" data-target='conversation' data-id="{{ $value->id }}" title="Xóa vĩnh viễn">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted p-4">
                            Chưa có hộp thoại
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="p-3 d-flex justify-content-center">
            {{ $conversations->links() }}
        </div>
    </div>
</div>
@endsection 
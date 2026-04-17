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
                            <th width="35%">thành viên</th>
                            <th width="10%">Loại</th>
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
                                {{ $item->profile->display_name ?? $item->email }},
                            @endforeach
                        </td>
                             @if($value->type === 'private')
                         <td class="text-start" >
                        <span class="badge bg-warning text-dark">cá nhân</span>
                        </td>
                        @elseif($value->type === 'group')
                         <td class="text-start" >
                        <span class="badge bg-success">nhóm</span>
                        </td>
                        @endif
                        </td>
                         <td class="text-center">
                            {{ $value->messages_count   ?? 0 }}
                        </td>
                        <td class="text-center">
                            <a href="{{route("admin.conversations.show", $value->id)}}"
                               class="btn btn-info btn-sm ">
                                <i class="bi bi-eye"></i>
                            </a>
                                  <a  class="btn btn-danger btn-sm btn-delete-conversation"
                                data-id="{{ $value->id }}">
                                    <i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted p-4">
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
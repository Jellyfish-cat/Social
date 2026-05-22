@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">
            <i class="bi bi-file-earmark-text"></i> Quản lý người dùng
        </h3>
          <button class="btn btn-success" id="btn-show-create-user">
            <i class="bi bi-plus-circle"></i> Thêm mới
        </button>
    </div>
    <div id="create-container"></div>
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            Danh sách người dùng
            <span class="badge bg-white text-primary count-users">Tổng: {{ $users->total() }}</span>

        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 user-table">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="5%">#</th>
                            <th width="10%">Username</th>
                            <th width="15%">Email</th>
                            <th width="10%">Tên hiển thị</th>
                            <th width="10%">Avatar</th>
                            <th width="10%">Follower</th>
                            <th width="10%">Following</th>
                            <th width="10%">Quyền</th>
                            <th width="10%">Trạng thái</th>
                            <th width="10%">Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="user-body">
                    @forelse($users as $value)
                    <tr class="users-item">
                        <td class="text-center stt">
                            {{ $loop->iteration }}
                        </td>
                        <td class="text-start" >
                            {{ Str::limit($value->name, 120) }}
                        </td>
                        <td class="text-start"> 
                                {{$value->email}}
                        </td>
                        <td class="text-start">
                            {{ $value->profile->display_name ?? 'Không có' }}
                        </td>
                        <td class="text-center">
                            @php
                                $firstMedia = $value->profile->avatar ?? 'Không có';
                            @endphp
                            @if($firstMedia)
                            <a href="{{ asset('storage/' . $firstMedia) }}" 
                                            data-fancybox="gallery-{{ $firstMedia }}">
                                <img
                                    src="{{ asset('storage/'.$firstMedia) }}"
                                    class="img-thumbnail"
                                    style="width:200px;height:100px;object-fit:cover"
                                ></a>
                            @else
                                <span class="text-muted">Không có</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button class="btn p-0 open-list-interaction" data-type="follower" data-id="{{$value->id}}">
                                <span class="follow-count" data-authid="{{$value->id}}">{{ $value->followers_count ?? 0 }}</span>
                            </button>
                        </td>
                        <td class="text-center">
                            <button class="btn p-0 open-list-interaction" data-type="following" data-id="{{$value->id}}">
                                <span class="following-count" data-authid="{{$value->id}}">{{ $value->following_count ?? 0 }}</span>
                            </button>
                        </td>
                        <td class="text-center">
                            @if($value->role === 'admin')
                                <span class="badge bg-danger">Admin</span>
                            @elseif($value->role === 'moderator')
                                <span class="badge bg-warning text-dark">Mod</span>
                            @else
                                <span class="badge bg-primary">User</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($value->status === 'show')
                                <span class="badge bg-success status-badge">Hoạt động</span>
                            @else
                                <span class="badge bg-danger status-badge">Bị khóa</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a class="mt-1 btn btn-info btn-sm" href="{{ route('profile.detail', $value->id ?? '') }}" title="Xem chi tiết">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a class="mt-1 btn btn-warning btn-sm" href="{{ route('profile.edit', $value->id ?? '') }}" title="Chỉnh sửa">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a class="mt-1 btn btn-danger btn-sm btn-delete" data-target="users"
                             data-id="{{ $value->id }}" title="Xóa vĩnh viễn">
                                <i class="bi bi-trash"></i>
                            </a>
                            <span class="btn-hide-container">
                                @if($value->status === 'show')
                                    <a class="mt-1 btn btn-secondary btn-sm btn-hide-user" 
                                    data-id="{{ $value->id }}" data-type="hide" title="Khóa tài khoản">
                                        <i class="bi bi-eye-slash"></i>
                                    </a>
                                @else
                                    <a class="mt-1 btn btn-success btn-sm btn-hide-user" 
                                    data-id="{{ $value->id }}" data-type="show" title="Hiển thị tài khoản">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endif
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted p-4">
                            Chưa có người dùng
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
                    <div class="p-3 d-flex justify-content-center">
             {{ $users->links() }}
        </div>

            
            <!-- Hiện thẻ phân trang của Bootstrap -->

        </div>

    </div>
</div>
@endsection 
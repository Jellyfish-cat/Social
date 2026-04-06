@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">
            <i class="bi bi-file-earmark-text"></i> Quản lý người dùng
        </h3>
    </div>
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            Danh sách người dùng
             <span class="badge bg-white text-primary count-user">Tổng: {{ $users->total() }}</span>

        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 post-table">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="5%">#</th>
                            <th width="10%">Tên đăng nhập</th>
                            <th width="10%">Email</th>
                            <th width="10%">Tên hiển thị</th>
                            <th width="10%">Avatar</th>
                            <th width="10%">Tiểu sử</th>
                            <th width="10%">Ngườ theo dõi</th>
                            <th width="10%">Đang theo dõi</th>
                            <th width="8%">Quyền hạn</th>
                            <th width="120">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($users as $value)
                    <tr class="user-item">
                        <td class="text-center">
                            {{ $loop->iteration }}
                        </td>
                        <td class="text-start" >
                            {{ Str::limit($value->name, 120) }}
                        </td>
                        <td class="text-start"> 
                                {{$value->email}}
                        </td>
                        <td class="text-start">
                            {{ $value->profile->display_name }}
                        </td>
                        <td class="text-center">
                            @php
                                $firstMedia = $value->profile->avatar;
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
                        <td class="text-start"> 
                            {{ Str::limit($value->profile->bio ?? "không có" , 20) }}
                        </td>

                        <td class="text-center">
                            <button class="open-follow"  data-type="follower" data-id="{{$value->id}}">
                    <a class="follow-count" data-authid="{{$value->id}}">{{ $value->followers_count   ?? 0 }}</a>
                   </button>
                        </td>
                        <td class="text-center">
                            <button class="open-follow"  data-type="following" data-id="{{$value->id}}">
                    <a class="following-count" data-authid="{{$value->id}}">{{ $value->following_count   ?? 0 }}</a>
                   </button>
                        <td class="text-center">
                            {{ $value->role   ?? 'user' }}
                        </td>
                        </td>
                        <td class="text-center">
                            <a 
                               class="btn btn-info btn-sm " href="{{ route('profile.detail', $value->id ?? '') }}" >
                                <i class="bi bi-eye"></i>
                            </a>
                                  <a  class="btn btn-danger btn-sm btn-delete-user"
                                data-id="{{ $value->id }}">
                                    <i class="bi bi-trash"></i></a>
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
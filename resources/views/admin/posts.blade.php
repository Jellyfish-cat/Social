@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">
            <i class="bi bi-file-earmark-text"></i> Quản lý bài viết
        </h3>
        <a href="{{ route('posts.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Đăng bài mới
        </a>
    </div>
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            Danh sách bài viết
             <span class="badge bg-white text-primary count-post">Tổng: {{ $posts->total() }}</span>

        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 post-table">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="5%">#</th>
                            <th width="35%">Nội dung</th>
                            <th width="10%">Người đăng</th>
                            <th width="18%">Media</th>
                            <th width="8%">Lượt thích</th>
                            <th width="8%">Lượt lưu</th>
                            <th width="8%">Lượt bình luận</th>
                            <th width="120">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($posts as $value)
                    <tr class="post-item">
                        <td class="text-center">
                            {{ $loop->iteration }}
                        </td>
                        <td class="text-start" style="white-space: normal; word-break: break-word;">
                            {{ Str::limit($value->content) }}
                        </td>
                        <td>
                            {{ $value->user->profile->display_name ?? $value->user->email }}
                        </td>
                        <td class="text-center">
                            @php
                                $listMedia = $value->media;
                                $firstMedia = $listMedia->first();
                            @endphp
                            @if($firstMedia)
                                <a href="{{ asset('storage/' . $firstMedia->file_path) }}" 
                                data-fancybox="gallery-{{ $value->id }}">
                                
                                    @if($firstMedia->type == 'image')
                                        <img src="{{ asset('storage/'.$firstMedia->file_path) }}"
                                            class="img-thumbnail"
                                            style="width:200px;height:100px;object-fit:cover">
                                    @else
                                        <video width="200" class="img-thumbnail">
                                            <source src="{{ asset('storage/'.$firstMedia->file_path) }}" type="video/mp4">
                                        </video>
                                    @endif
                                </a>
                                @foreach($listMedia->skip(1) as $media)
                                    <a href="{{ asset('storage/' . $media->file_path) }}" 
                                    data-fancybox="gallery-{{ $value->id }}"
                                    style="display:none;">
                                    </a>
                                @endforeach
                            @else
                                <span class="text-muted">Không có</span>
                            @endif
                        </td>
                  
                         <td class="text-center open-like" data-authid="{{$value->user->id}}"
                    data-post-id="{{ $value->id }}">
                            {{ $value->likes_count   ?? 0 }}
                        </td>
                         <td class="text-center">
                            {{ $value->favorites_count  ?? 0 }}
                        </td>
                         <td class="text-center">
                            {{ $value->comments_count  ?? 0 }}
                        </td>
                        <td class="text-center">
                            <a href="{{ route('posts.detail',$value->id) }}"
                               class="btn btn-info btn-sm">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('posts.edit',$value->id) }}"
                               class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil"></i>
                            </a>
                                  <a  class="btn btn-danger btn-sm btn-delete" data-id="{{ $value->id }}">
                                    <i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted p-4">
                            Chưa có bài viết
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
                    <div class="p-3 d-flex justify-content-center">
             {{ $posts->links() }}
        </div>


    </div>
</div>
@endsection 
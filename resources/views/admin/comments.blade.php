@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">
            <i class="bi bi-file-earmark-text"></i> Quản lý bình luận
        </h3>
    </div>
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            Danh sách bình luận
         <span class="badge bg-white text-primary comment-count">Tổng: {{ $comments->total() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 post-table">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="5%">#</th>
                            <th width="35%">Nội dung</th>
                            <th width="10%">Người đăng</th>
                            <th width="19%">Media</th>
                            <th width="10%">Bình luận gốc</th>
                            <th width="8%">Lượt thích</th>
                            <th width="8%">Lượt phản hồi</th>
                            <th width="120">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($comments as $value)
                    <tr class="comment-item">
                        <td class="text-center">
                            {{ $loop->iteration }}
                        </td>
                        <td class="text-start" >
                            {{ Str::limit($value->content) }}
                        </td>
                        <td>
                            {{ $value->user->profile->display_name ?? $value->user->email }}
                        </td>
                        <td class="text-center">
                           @if($value->isImage())
                        <a href="{{ asset('storage/' . $value->media_path) }}" 
                                            data-fancybox="gallery-{{ $value->id }}">
                            <img src="{{ asset('storage/'.$value->media_path) }}" style="width:200px;height:100px;object-fit:cover" class="rounded "></a>
                        @elseif($value->isVideo())
                        <a href="{{ asset('storage/' . $value->media_path) }}" 
                                            data-fancybox="gallery-{{ $value->id }}">
                            <video width="200" controls class="rounded">
                                <source src="{{ asset('storage/'.$value->media_path) }}">
                            </video></a>
                        @else
                        Không có
                        @endif
                        </td>
                        <td class="text-start"> 
                            {{ Str::limit($value->parent->content ?? "không có" , 20) }}
                        </td>
                         <td class="text-center open-like-comment" data-comment-id="{{ $value->id }}"
                            data-post-id="{{ $value->post->id }}">
                            {{ $value->likes_count   ?? 0 }}
                        </td>
                         <td class="text-center">
                            {{ $value->replíes_count  ?? 0 }}
                        </td>
                        <td class="text-center">
                            <a 
                               class="btn btn-info btn-sm open-post" data-id="{{$value->post->id}}"
                                data-scroll-comment-id="{{$value->id}}" data-action="reply">
                                <i class="bi bi-eye"></i>
                            </a>
                                  <a  class="btn btn-danger btn-sm btn-delete-comment"
                                data-id="{{ $value->id }}">
                                    <i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted p-4">
                            Chưa có bình luận
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
                    <div class="p-3 d-flex justify-content-center">
             {{ $comments->links() }}
        </div>


    </div>
</div>
@endsection 
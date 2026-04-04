@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">
            <i class="bi bi-file-earmark-text"></i> Quản lý tin nhắn
        </h3>
    </div>
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            Danh sách tin nhắn
             <span class="badge bg-white text-primary count-message">Tổng: {{ $messages->total() }}</span>

        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 post-table">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="5%">#</th>
                            <th width="10%">Mã hội thoại</th>
                            <th width="35%">Nội dung</th>
                            <th width="15%">Người gửi</th>
                            <th width="15%">Người Nhận</th>
                            <th width="18%">Media</th>
                            <th width="120">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($messages as $value)
                    <tr class="message-item">
                        <td class="text-center">
                            {{ $loop->iteration }}
                        </td>
                         <td class="text-center">
                            {{ $value->conversation_id }}
                        </td>
                        <td class="text-start" >
                            {{ Str::limit($value->content, 120) }}
                        </td>
                        <td class="text-start">
                            {{ $value->sender->profile->display_name ?? $value->sender->email }}
                        </td>
                        <td class="text-start">
                            @php
                             $conversation = $value->conversation;
                            $otherUser = $conversation->users
                                ->where('id', '!=', $value->sender->id)
                                ->first();
                            @endphp
                            {{ $otherUser->profile->display_name ?? $otherUser->email }}
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
                        <td class="text-center">

                                  <a  class="btn btn-danger btn-sm btn-delete-message"
                                data-id="{{ $value->id }}">
                                    <i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted p-4">
                            Chưa có tin nhắn
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
                    <div class="p-3 d-flex justify-content-center">
             {{ $messages->links() }}
        </div>


    </div>
</div>
@endsection 
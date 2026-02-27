@extends('layouts.app')
@section('content')
<div class="container mt-5">
    <h3 class="text-primary mb-4">Quản lý bài viết</h3>

    <div class="mb-3">
        <a href="{{ route('posts.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle-fill"></i> Đăng bài mới
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered align-middle shadow-sm">
            <thead class="table-primary text-center">
                <tr>
                    <th width="5%">STT</th>
                    <th width="35%">Nội dung</th>
                    <th width="15%">Người đăng</th>
                    <th width="15%">Media</th>
                    <th width="10%">Lượt xem</th>
                    <th width="5%">Xem</th>
                    <th width="5%">Sửa</th>
                    <th width="5%">Xóa</th>
                </tr>
            </thead>
            <tbody>
                @foreach($posts as $value)
                <tr class="text-center">
                    <td>{{ $loop->iteration }}</td>
                    <td class="text-start">
                        {{ Str::limit($value->content, 100) }}
                    </td>
                    <td>
                        {{-- Truy xuất từ bảng profiles --}}
                        {{ $value->user->profile->display_name ?? $value->user->email }}
                    </td>
                    <td>
                        {{-- Lấy file đầu tiên từ bảng media --}}
                        @php $firstMedia = $value->media->first(); @endphp
                        @if($firstMedia)
                            @if($firstMedia->type == 'image')
                                <img src="{{ asset('storage/' . $firstMedia->file_path) }}" 
                                     class="img-thumbnail" 
                                     style="width: 80px; height: 60px; object-fit: cover;" 
                                     alt="Ảnh bài viết">
                            @else
                                <span class="badge bg-secondary"><i class="bi bi-play-btn"></i> Video</span>
                            @endif
                        @else
                            <span class="text-muted">Không có file</span>
                        @endif
                    </td>
                    <td>
                        {{-- Giả sử bạn sử dụng counter hoặc đếm từ bảng video_views --}}
                        {{ $value->video_views_count ?? 0 }}
                    </td>
                    <td>
                        <a  class="btn btn-info btn-sm">
                            <i class="bi bi-eye-fill"></i>
                        </a>
                    </td>
                    <td>
                        <a href="{{ route('posts.edit', ['id' => $value->id]) }}" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil-fill"></i>
                        </a>
                    </td>
                    <td>
                        <form action="{{ route('posts.destroy', ['id' => $value->id]) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" 
                                    onclick="return confirm('Xóa bài viết này sẽ xóa toàn bộ ảnh/video liên quan. Bạn chắc chứ?')">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
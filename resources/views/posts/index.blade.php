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

        <div class="card-header bg-primary text-white">
            Danh sách bài viết
        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle mb-0 post-table">

                    <thead class="table-light text-center">
                        <tr>
                            <th width="5%">#</th>
                            <th width="35%">Nội dung</th>
                            <th width="15%">Người đăng</th>
                            <th width="15%">Media</th>
                            <th width="10%">Lượt xem</th>
                            <th width="120">Hành động</th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($posts as $value)

                    <tr>

                        <td class="text-center">
                            {{ $loop->iteration }}
                        </td>

                        <td class="text-start">
                            {{ Str::limit($value->content, 120) }}
                        </td>

                        <td>
                            {{ $value->user->profile->display_name ?? $value->user->email }}
                        </td>

                        <td class="text-center">

                            @php
                                $firstMedia = $value->media->first();
                            @endphp

                            @if($firstMedia)

                                @if($firstMedia->type == 'image')

                                <img
                                    src="{{ asset('storage/'.$firstMedia->file_path) }}"
                                    class="img-thumbnail"
                                    style="width:80px;height:60px;object-fit:cover"
                                >

                                @else

                                <span class="badge bg-secondary">
                                    <i class="bi bi-play-btn"></i> Video
                                </span>

                                @endif

                            @else

                                <span class="text-muted">Không có</span>

                            @endif

                        </td>

                        <td class="text-center">
                            {{ $value->video_views_count ?? 0 }}
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

                            <form
                                action="{{ route('posts.destroy',$value->id) }}"
                                method="POST"
                                class="d-inline">

                                @csrf
                                @method('DELETE')

                                <button
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Xóa bài viết này sẽ xóa toàn bộ ảnh/video liên quan. Bạn chắc chứ?')">

                                    <i class="bi bi-trash"></i>

                                </button>

                            </form>

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


        <div class="card-footer text-muted">
            Tổng bài viết: {{ $posts->count() }}
        </div>

    </div>

</div>

@endsection 
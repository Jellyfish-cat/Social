@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary">
            <i class="bi bi-collection"></i> Danh sách chủ đề
        </h3>
        <button class="btn btn-success" id="btn-show-create">
            <i class="bi bi-plus-circle"></i> Thêm mới
        </button>
    </div>
    <div id="create-container"></div>
    <!-- Card -->
    <div class="card shadow-sm">
        <!-- Card header -->
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Quản lý chủ đề</h5>
        </div>
        <!-- Card body -->
        <div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0 topic-table">
            <thead class="table-light text-center">
                <tr>
                    <th width="5%">#</th>
                    <th width="25%">Tên chủ đề</th>
                    <th>Mô tả</th>
                    <th width="120">Hành động</th>
                </tr>
            </thead>
                <tbody id="topic-body">
                <tr id="create-row" class="d-none">
                    <td colspan="4">
                        <form id="form-create-topic" class="d-flex gap-2">
                            @csrf
                            <input type="text" name="name" class="form-control" placeholder="Tên chủ đề..." required>
                            <input type="text" name="description" class="form-control" placeholder="Mô tả...">

                            <button class="btn btn-success">Lưu</button>
                            <button type="button" class="btn btn-secondary" id="btn-cancel">Hủy</button>
                        </form>
                    </td>
                </tr>
            @forelse($topics as $topic)
                <tr>
                    <td class="text-center stt">
                        {{ $loop->iteration }}
                    </td>
                    <td class="fw-semibold">
                        {{ $topic->name }}
                    </td>
                    <td class="text-muted">
                        {{ $topic->description }}
                    </td>
                    <td class="text-center">
                        <a href="{{ route('topics.edit', ['id' => $topic->id, 'page' => request('page')]) }}"
                           class="btn btn-warning btn-sm">
                           <i class="bi bi-pencil"></i>
                        </a>
                        <form class="d-inline">
                            <button class="btn btn-danger btn-sm btn-delete-topic" data-id={{$topic->id}}>
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted p-4">
                        Chưa có chủ đề
                    </td>
                </tr>
                
            @endforelse
            </tbody>
        </table>
            <div class="p-3 d-flex justify-content-center">
             {{ $topics->links() }}
        </div>
    </div>
</div>
        <div class="card-footer text-muted count-topic">
            Tổng chủ đề: {{ $topics->total() }}
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('topics.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Về danh sách
        </a>
        <h3 class="text-warning me-3">
            <i class="bi bi-pencil-square"></i> Chỉnh sửa chủ đề
        </h3>
    </div>
    <!-- Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">Cập nhật thông tin chủ đề</h5>
        </div>
        <form action="{{ route('topics.update', $topic->id) }}" method="POST">
            @csrf
            <div class="card-body">
                <!-- Name -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Tên chủ đề
                    </label>
                    <input type="text"
                           class="form-control"
                           name="name"
                           value="{{ old('name', $topic->name) }}"
                           required>
                </div>

            </div>
            <!-- Footer -->
            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ route('topics.index',['page' => $page]) }}"
                   class="btn btn-outline-secondary">
                   Hủy
                </a>
                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-save"></i> Cập nhật
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
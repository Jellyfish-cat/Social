<div class="container-fluid mb-8">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="{{ route('topics.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Về danh sách
        </a>
        <h3 class="text-primary me-3">
            <i class="bi bi-folder-plus"></i> Thêm chủ đề
        </h3>
    </div>
    <!-- Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Thông tin chủ đề</h5>
        </div>
        <form id="topicForm" >
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
                           placeholder="Nhập tên chủ đề..."
                           required>
                </div>
                <!-- Description -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Mô tả
                    </label>
                    <textarea
                        class="form-control"
                        name="description"
                        rows="3"
                        placeholder="Nhập mô tả chủ đề..."></textarea>
                </div>
            </div>
            <!-- Footer -->
            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ route('topics.index',['page' => request('page')]) }}"
                   class="btn btn-outline-secondary">
                   Hủy
                </a>
                <button type="submit" class="btn btn-primary btn-create-topic">
                    <i class="bi bi-save"></i> Lưu
                </button>
            </div>
        </form>
    </div>
</div>
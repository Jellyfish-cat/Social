<div class="container-fluid mb-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('admin.users') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Về danh sách
        </a>
        <h3 class="text-primary me-3">
            <i class="bi bi-person-plus"></i> Thêm người dùng
        </h3>
    </div>
    <!-- Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Thông tin người dùng</h5>
        </div>
        <form id="userForm" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <h6 class="text-secondary fw-bold mb-3 border-bottom pb-2">
                    <i class="bi bi-shield-lock"></i> Thông tin tài khoản
                </h6>
                <div class="row">
                    <!-- Username -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Tên đăng nhập</label>
                        <input type="text" class="form-control" name="name" placeholder="Nhập tên đăng nhập..." required>
                    </div>
                    <!-- Email -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control" name="email" placeholder="Nhập email..." required>
                    </div>
                </div>

                <div class="row">
                    <!-- Password -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Mật khẩu</label>
                        <input type="password" class="form-control" name="password" placeholder="Nhập mật khẩu..." required>
                    </div>
                    <!-- Role -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Quyền hạn</label>
                        <select name="role" class="form-select">
                            <option value="user">User</option>
                            <option value="moderator">Moderator</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>

                <h6 class="text-secondary fw-bold mt-4 mb-3 border-bottom pb-2">
                    <i class="bi bi-person-badge"></i> Thông tin cá nhân (Profile)
                </h6>
                <div class="row">
                    <!-- Display Name -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Tên hiển thị</label>
                        <input type="text" class="form-control" name="display_name" placeholder="Ví dụ: Nguyễn Văn A">
                    </div>
                    <!-- Avatar -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Ảnh đại diện</label>
                         <div id="avatarPreview" class="mt-2 d-none">
                            <img src="" class="img-thumbnail" style="height: 100px; width: 100px; object-fit: cover;">
                        </div>
                        <input type="file" class="form-control" name="avatar" accept="image/*" id="avatarInput">
                       
                    </div>
                </div>
                <!-- Bio -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tiểu sử</label>
                    <textarea class="form-control" name="bio" rows="3" placeholder="Giới thiệu ngắn gọn về người dùng này..."></textarea>
                </div>
            </div>
            <!-- Footer -->
            <div class="card-footer d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary" id="btn-cancel-user">
                    Hủy
                </button>
                <button type="submit" class="btn btn-primary btn-create-user">
                    <i class="bi bi-save"></i> Lưu người dùng
                </button>
            </div>
        </form>
    </div>
</div>
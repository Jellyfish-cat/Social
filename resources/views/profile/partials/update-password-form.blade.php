<section>
    <header class="mb-4">
        <h4 class="fw-bold">Đổi mật khẩu</h4>
        <p class="text-muted small">Đảm bảo tài khoản của bạn đang sử dụng một mật khẩu dài, ngẫu nhiên để giữ an toàn.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-4">
        @csrf
        @method('put')

        <div class="mb-3">
            <label class="form-label fw-semibold small">Mật khẩu hiện tại</label>
            <input type="password" name="current_password" class="form-control" autocomplete="current-password">
            @if ($errors->updatePassword->has('current_password'))
                <div class="text-danger small mt-1">{{ $errors->updatePassword->first('current_password') }}</div>
            @endif
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold small">Mật khẩu mới</label>
            <input type="password" name="password" class="form-control" autocomplete="new-password">
            @if ($errors->updatePassword->has('password'))
                <div class="text-danger small mt-1">{{ $errors->updatePassword->first('password') }}</div>
            @endif
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold small">Xác nhận mật khẩu</label>
            <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
            @if ($errors->updatePassword->has('password_confirmation'))
                <div class="text-danger small mt-1">{{ $errors->updatePassword->first('password_confirmation') }}</div>
            @endif
        </div>

        <div class="mt-4 pt-3 border-top d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-dark px-4 py-2">Cập nhật mật khẩu</button>

            @if (session('status') === 'password-updated')
                <span class="text-success small animated fadeIn">
                    <i class="bi bi-check-circle me-1"></i> Đã cập nhật thành công!
                </span>
            @endif
        </div>
    </form>
</section>

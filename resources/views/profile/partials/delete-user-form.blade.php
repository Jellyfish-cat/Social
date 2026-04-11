<section>
    <header class="mb-4">
        <h4 class="fw-bold text-danger">Xóa tài khoản</h4>
        <p class="text-muted small">
            Sau khi tài khoản của bạn bị xóa, tất cả các tài nguyên và dữ liệu liên quan sẽ bị xóa vĩnh viễn. 
            Vui lòng tải xuống bất kỳ dữ liệu hoặc thông tin nào bạn muốn giữ lại trước khi thực hiện hành động này.
        </p>
    </header>

    <button type="button" class="btn btn-danger px-4 py-2" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
        Xóa tài khoản vĩnh viễn
    </button>

    {{-- Bootstrap Modal --}}
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 15px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="confirmDeleteModalLabel">Xác nhận xóa tài khoản</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')
                    
                    <div class="modal-body py-4">
                        <p class="text-muted small mb-4">
                            Bạn có chắc chắn muốn xóa tài khoản không? 
                            Vui lòng nhập mật khẩu của bạn để xác nhận hành động này.
                        </p>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Mật khẩu xác nhận</label>
                            <input type="password" name="password" class="form-control" placeholder="Mật khẩu của bạn">
                            @if ($errors->userDeletion->has('password'))
                                <div class="text-danger small mt-1">{{ $errors->userDeletion->first('password') }}</div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="modal-footer border-0 pt-0 pb-4 justify-content-center">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                        <button type="submit" class="btn btn-danger px-4">Xác nhận xóa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

@if ($errors->userDeletion->isNotEmpty())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var myModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            myModal.show();
        });
    </script>
@endif

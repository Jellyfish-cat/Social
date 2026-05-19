
// Xử lý Khóa/Mở khóa người dùng
document.addEventListener('click', function (e) {
    const hideBtn = e.target.closest('.btn-hide-user');
    if (hideBtn) {
        e.preventDefault();
        const userId = hideBtn.dataset.id;
        const type = hideBtn.dataset.type; // 'hide' hoặc 'show'
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        startLoading();
        fetch(`/admin/users/hide/${userId}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ type: type })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const row = hideBtn.closest('.user-item');
                    const badge = row.querySelector('.status-badge');
                    if (badge) {
                        if (data.status === 'hidden') {
                            badge.className = 'badge bg-danger status-badge';
                            badge.innerText = 'Bị khóa';
                        } else {
                            badge.className = 'badge bg-success status-badge';
                            badge.innerText = 'Hoạt động';
                        }
                    }
                    const container = row.querySelector('.btn-hide-container');
                    if (container) {
                        if (data.status === 'hidden') {
                            container.innerHTML = `
                            <a class="mt-1 btn btn-success btn-sm btn-hide-user" data-id="${userId}" data-type="show" title="Hiển thị tài khoản">
                                <i class="bi bi-eye"></i>
                            </a>
                        `;
                        } else {
                            container.innerHTML = `
                            <a class="mt-1 btn btn-secondary btn-sm btn-hide-user" data-id="${userId}" data-type="hide" title="Khóa tài khoản">
                                <i class="bi bi-eye-slash"></i>
                            </a>
                        `;
                        }
                    }
                    if (window.Swal) Swal.fire('Thành công', data.message, 'success');
                    else console.log(data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert("Có lỗi xảy ra khi cập nhật trạng thái");
            })
            .finally(() => {
                btn.disabled = false;
                finishLoading();
            });
    }
});
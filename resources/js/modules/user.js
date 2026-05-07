document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-delete-user');
    if (btn) {
        e.preventDefault();
        e.stopPropagation();
        const postId = btn.dataset.id;
        const type = btn.dataset.type;
        if (!confirm('Bạn có chắc muốn xóa người dùng này?')) {
            return;
        }
        let $method = 'DELETE';
        if (type === 'hide') {
            $method = 'PUT';
        }
        startLoading();
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        fetch(`/admin/users/${type}/${postId}`, {
            method: $method,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const row = btn.closest(".user-item");
                    if (row) {
                        // Hiệu ứng mượt
                        row.style.transition = "all 0.3s ease";
                        row.style.opacity = "0";
                        setTimeout(() => {
                            row.remove();
                            document.querySelector(".count-user").innerText =
                                `Tổng người dùng: ${data.count}`;
                            updateSTT();
                        }, 300);
                    }
                    // Thông báo
                    console.log(data.message || "Xóa thành công");
                }
            })
            .catch((err) => {
                alert(err.message);
            })
            .finally(() => {
                btn.disabled = false;
                finishLoading();
            });
    }
});

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
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-delete');
    if (btn) {
        e.preventDefault();
        e.stopPropagation();
        const id = btn.dataset.id;
        const target = btn.dataset.target;
        if (!confirm(`Xóa ${target} này sẽ xóa toàn bộ ảnh/video liên quan. Bạn chắc chứ?`)) {
            return;
        }
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        fetch(`/${target}/destroy/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const row = btn.closest(".post-item");
                    if (row) {
                        // Hiệu ứng mượt
                        row.style.transition = "all 0.3s ease";
                        row.style.opacity = "0";
                        setTimeout(() => {
                            row.remove();
                            document.querySelector(".count-post").innerText =
                                `Tổng : ${data.count}`;
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
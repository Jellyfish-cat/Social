document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-delete'); if (!btn) return;
    const id = btn.dataset.id;
    if (!id) {
        console.error("Không có ID để xóa");
        return;
    }
    if (btn) {
        e.preventDefault();
        e.stopPropagation();
        const target = btn.dataset.target;
        if (!confirm(`Xóa ${target} này sẽ xóa toàn bộ ảnh/video liên quan. Bạn chắc chứ?`)) {
            return;
        }
        btn.disabled = true;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        startLoading();
        fetch(`/${target}/destroy/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
            .then(async (res) => {
                let data = {};
                try {
                    data = await res.json();
                } catch (e) {
                    console.warn("Response không phải JSON");
                }
                if (!res.ok || !data.success) {
                    throw new Error(data.message || "Xóa thất bại");
                }
                return data;
            })
            .then(data => {
                if (data.success) {
                    const row = btn.closest(`.${target}-item`);
                    if (row) {
                        // Hiệu ứng mượt
                        row.style.transition = "all 0.3s ease";
                        row.style.opacity = "0";
                        setTimeout(() => {
                            row.remove();
                            document.querySelector(`.count-${target}`).innerText =
                                `Tổng : ${data.count}`;
                            updateSTT();
                            const postCountLabels = document.querySelectorAll(`.comment-post-count[data-post-id="${data.post_id || ''}"], .count-comments[data-post-id="${data.post_id || ''}"]`);
                            if (postCountLabels) {
                                postCountLabels.forEach(el => {
                                    el.innerText = `${data.count} bình luận`;
                                });
                            }
                        }, 300);
                    }
                }
                // Thông báo
                console.log(data.message || "Xóa thành công");
            })
            .catch((err) => {
                alert(err.message);
            })
            .finally(() => {
                btn.disabled = false;
                finishLoading();
            });
    }
})
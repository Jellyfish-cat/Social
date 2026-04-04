document.addEventListener('click', function (e) {
    if (e.target.classList.contains('btn-delete-user')) {
        const btn = e.target;
        const postId = btn.dataset.id;
        if (!confirm('Xóa bài viết này sẽ xóa toàn bộ ảnh/video liên quan. Bạn chắc chứ?')) {
            return; 
        }
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        fetch(`/users/destroy/${postId}`, {
            method: 'DELETE',
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
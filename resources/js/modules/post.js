document.addEventListener("click", function (e) {
    if (window.Fancybox && Fancybox.getInstance()) return;
    const btn = e.target.closest(".open-post");
    if (!btn) return;
    
    const postId = btn.dataset.id;
    if (window.matchMedia("(max-width: 992px)").matches) {
        window.location.href = `/posts/detail/${postId}`;
        return;
    }
    const scrollCommentId = btn.dataset.scrollCommentId;
    const action = btn.dataset.action;

    const commentIcons = document.querySelectorAll(`.open-post[data-id="${postId}"] i`);
    startLoading();
    fetch(`/posts/detail/${postId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(res => res.text())
        .then(html => {
            commentIcons.forEach(icon => {
                icon.classList.remove("any-pop");
                void icon.offsetWidth;
                icon.classList.add("any-pop");
            });
            document.getElementById("postDetailContent").innerHTML = html;
            const modalEl = document.getElementById("postDetailModal");
            const modal = new bootstrap.Modal(modalEl);
            // 1. Lưu lại URL hiện tại (URL của profile) trước khi đổi
            const originalUrl = window.location.href;
            modal.show();
            history.pushState({ modalPostId: postId }, '', `/posts/detail/${postId}`);
            // 2. Thêm đoạn này để trả lại URL cũ khi đóng Modal
            modalEl.addEventListener('hidden.bs.modal', function () {
                // Chỉ set lại nếu URL vẫn đang là trang chi tiết bài viết
                if (window.location.pathname.includes('/posts/detail/')) {
                    history.pushState(null, '', originalUrl);
                }
            }, { once: true });
            modalEl.addEventListener('shown.bs.modal', function () {
                if (scrollCommentId) {
                    const commentEl = document.querySelector(`.comment-item[data-comment-id="${scrollCommentId}"]`);
                    if (commentEl) {
                        const parentReplyList = commentEl.closest('.reply-list');
                        if (parentReplyList && parentReplyList.classList.contains('d-none')) {
                            parentReplyList.classList.remove('d-none');
                            const replyId = parentReplyList.id.split('-')[1];
                            const viewBtn = document.querySelector(`.view-replies[data-comment-id="${replyId}"]`);
                            if (viewBtn) {
                                viewBtn.innerHTML = '&mdash;&ndash; Ẩn phản hồi <i class="bi bi-caret-down-fill ms-1"></i>';
                                parentReplyList.appendChild(viewBtn);
                            }
                        }
                        commentEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        const originalBg = commentEl.style.backgroundColor;
                        commentEl.style.backgroundColor = 'rgba(157, 157, 157, 0.54)';
                        commentEl.style.transition = 'background-color 0.5s';
                        setTimeout(() => {
                            commentEl.style.backgroundColor = originalBg;
                            commentEl.style.transition = '';
                        }, 1000);
                        if (action === 'reply') {
                            const internalReplyBtn = commentEl.querySelector('.btn-reply');
                            if (internalReplyBtn) {
                                setTimeout(() => internalReplyBtn.click(), 500);
                            }
                        }
                    }
                }
            }, { once: true });
        })
        .finally(() => {
            finishLoading();
        });
});
// tính năng back lại 
window.addEventListener('popstate', function (event) {
    const modalEl = document.querySelector(".back-to");
    const modal = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;

    if (event.state && event.state.modalPostId) {
        fetch(`/posts/detail/${event.state.modalPostId}`)
            .then(res => res.text())
            .then(html => {
                if (modalEl) modalEl.innerHTML = html;
                if (!modal && modalEl) new bootstrap.Modal(modalEl).show();
            })
            .finally(() => finishLoading());
    }
    else {
        if (modal && modalEl.classList.contains('show')) {
            modal.hide();
        }
    }
});
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-delete');
    if (btn) {
        e.preventDefault();
        e.stopPropagation();
        const postId = btn.dataset.id;
        if (!confirm('Xóa bài viết này sẽ xóa toàn bộ ảnh/video liên quan. Bạn chắc chứ?')) {
            return;
        }
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        fetch(`/posts/destroy/${postId}`, {
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
                    `Tổng bài viết: ${data.count}`;
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

// Khởi tạo topic cho trang edit
document.addEventListener('DOMContentLoaded', function () {
    const topicBox = document.getElementById('selected-topics');
    if (topicBox && topicBox.dataset.initial) {
        try {
            const initialTopics = JSON.parse(topicBox.dataset.initial);
            initialTopics.forEach(t => {
                if (window.selectTopic) {
                    window.selectTopic(t.id, t.name);
                }
            });
        } catch (err) {
            console.error("Lỗi khởi tạo chủ đề:", err);
        }
    }
});
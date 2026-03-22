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
window.addEventListener('popstate', function (event) {
    const modalEl = document.getElementById("postDetailModal");
    const modal = bootstrap.Modal.getInstance(modalEl);

    if (event.state && event.state.modalPostId) {
        // load lại modal
        startLoading();
        fetch(`/posts/detail/${event.state.modalPostId}`)
            .then(res => res.text())
            .then(html => {
                document.getElementById("postDetailContent").innerHTML = html;
                if (!modal) new bootstrap.Modal(modalEl).show();
            })
            .finally(() => {
                finishLoading();
            });
    } else {
        if (modal && modalEl.classList.contains('show')) {
            modal.hide();
        }
    }
});


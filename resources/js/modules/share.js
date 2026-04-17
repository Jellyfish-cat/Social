document.addEventListener('DOMContentLoaded', function () {
    const shareModalElement = document.getElementById('sharePostModal');
    if (!shareModalElement) return;

    const shareModal = new bootstrap.Modal(shareModalElement);
    const userListContainer = document.getElementById('shareUserList');
    const searchInput = document.getElementById('shareUserSearch');
    const copyBtn = document.getElementById('copyPostLinkBtn');
    let currentPostId = null;

    // Mở modal chia sẻ
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.open-share');
        if (btn) {
            currentPostId = btn.dataset.id;
            const postUrl = `${window.location.origin}/posts/detail/${currentPostId}`;
            copyBtn.dataset.url = postUrl;
            document.getElementById('copyStatusText').innerText = 'Click để copy link bài viết';
            
            userListContainer.innerHTML = '<div class="text-center py-5"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
            searchInput.value = '';
            
            shareModal.show();

            // Lấy danh sách bạn bè từ ShareController
            fetch(`/share/list/${currentPostId}`)
                .then(res => res.text())
                .then(html => {
                    userListContainer.innerHTML = html;
                })
                .catch(err => {
                    userListContainer.innerHTML = '<div class="text-center py-4 text-danger">Không thể tải danh sách.</div>';
                });
        }
    });

    // Xử lý tìm kiếm trong modal
    searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase();
        const items = userListContainer.querySelectorAll('.user-share-item');
        items.forEach(item => {
            const name = item.querySelector('.fw-bold').innerText.toLowerCase();
            const username = item.querySelector('.text-muted').innerText.toLowerCase();
            if (name.includes(query) || username.includes(query)) {
                item.classList.remove('d-none');
            } else {
                item.classList.add('d-none');
            }
        });
    });

    // Xử lý chọn người dùng
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('select-user-share')) {
            const selected = userListContainer.querySelectorAll('.select-user-share:checked');
            const sendBtnContainer = document.getElementById('sendShareBtnContainer');
            if (selected.length > 0) {
                sendBtnContainer.classList.remove('d-none');
            } else {
                sendBtnContainer.classList.add('d-none');
            }
        }
    });

    // GỬI CHIA SẺ - Móc nối trực tiếp với MessageController hiện có
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('#sendShareBtn');
        if (btn) {
            const selectedCheckboxes = userListContainer.querySelectorAll('.select-user-share:checked');
            const userIds = Array.from(selectedCheckboxes).map(cb => cb.value);
            
            if (userIds.length === 0) return;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang gửi...';

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const postUrl = copyBtn.dataset.url;
            const shareMessage = `Đã chia sẻ một bài viết: ${postUrl}`;

            // Gửi lần lượt cho từng người dùng bằng route MESSAGE STORE đã có sẵn của bạn
            const sendPromises = userIds.map(userId => {
                const formData = new FormData();
                formData.append('content', shareMessage);

                return fetch(`/message/send/${userId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
            });

            Promise.all(sendPromises)
                .then(() => {
                    alert(`Đã chia sẻ bài viết thành công cho ${userIds.length} người.`);
                    shareModal.hide();
                })
                .catch(err => {
                    console.error(err);
                    alert('Có lỗi xảy ra khi gửi tin nhắn.');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerText = 'Gửi';
                });
        }
    });

    // Sao chép liên kết
    copyBtn.addEventListener('click', function () {
        const url = this.dataset.url;
        navigator.clipboard.writeText(url).then(() => {
            const statusText = document.getElementById('copyStatusText');
            statusText.innerText = 'Đã sao chép!';
            statusText.classList.add('text-success', 'fw-bold');
            setTimeout(() => {
                statusText.innerText = 'Click để copy link bài viết';
                statusText.classList.remove('text-success', 'fw-bold');
            }, 2000);
        });
    });
});

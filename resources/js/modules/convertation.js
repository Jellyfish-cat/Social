// ===== Conversation / Message Page =====
// Chỉ chạy trên trang có #msgPage
const msgPage = document.getElementById('msgPage');
if (msgPage) {
    const emptyState = document.getElementById('msgEmptyState');
    const chatPanel = document.getElementById('msgChatPanel');
    const chatName = document.getElementById('chatName');
    const chatStatus = document.getElementById('chatStatus');
    const chatOnlineDot = document.getElementById('chatOnlineDot');
    const chatBody = document.getElementById('msgChatBody');
    const msgInput = document.getElementById('msgInput');
    let currentItem = null;

    // ===== Mở chat khi click vào conversation =====
    function openChat(el, name, status, online) {
        document.querySelectorAll('.convo-item').forEach(i => i.classList.remove('active'));
        el.classList.add('active');
        currentItem = el;
        chatName.textContent = name;
        chatStatus.textContent = online ? 'Đang hoạt động' : status;
        chatOnlineDot.style.display = online ? 'block' : 'none';
        emptyState.style.display = 'none';
        chatPanel.classList.add('open');
        msgPage.classList.add('show-chat');

        // Fetch messages từ server
        const userId = el.dataset.userId;
        if (userId) {
            chatBody.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-2">Đang tải...</div>
        </div>
     `;
            startLoading();
            fetch(`/message/chat/${userId}`)
                .then(res => res.text())
                .then(html => {
                    chatBody.innerHTML = html;
                    setTimeout(() => { chatBody.scrollTop = chatBody.scrollHeight; }, 50);

                })
                .catch(() => {
                    chatBody.innerHTML = '<div class="text-center text-muted py-3">Không thể tải tin nhắn</div>';
                })
                .finally(() => {
                    finishLoading();
                });
        }

    }

    // ===== Đóng chat (mobile) =====
    function closeChat() {
        msgPage.classList.remove('show-chat');
        chatPanel.classList.remove('open');
        emptyState.style.display = 'flex';
    }

    // ===== Gửi tin nhắn mới =====
    function showNewConvo() {
        alert('Tính năng tìm kiếm người dùng để nhắn tin sẽ được phát triển sau.');
    }

    // ===== Gửi tin nhắn =====
    function sendMessage() {
        const text = msgInput.value.trim();
        const files = window.msgSelectedFiles || [];
        if (!text && files.length === 0) return;
        if (!currentItem) return;

        const userId = currentItem.dataset.userId;
        if (!userId) return;

        // Thêm bubble ngay lập tức (optimistic)
        const row = document.createElement('div');
        row.className = 'msg-bubble-row mine';
        const bubble = document.createElement('div');
        bubble.className = 'msg-bubble mine';

        // Hiển thị tất cả media preview trong bubble
        if (files.length > 0) {
            let mediaHtml = '';
            files.forEach(file => {
                const url = URL.createObjectURL(file);
                if (file.type.includes('image')) {
                    mediaHtml += `<img src="${url}" style="max-width:200px;border-radius:12px;" class="mb-1 d-block">`;
                } else if (file.type.includes('video')) {
                    mediaHtml += `<video src="${url}" style="max-width:200px;border-radius:12px;" controls class="mb-1 d-block"></video>`;
                }
            });
            bubble.innerHTML = mediaHtml;
            if (text) {
                bubble.innerHTML += `<div class="mt-1">${text}</div>`;
            }
        } else {
            bubble.textContent = text;
        }

        row.appendChild(bubble);
        chatBody.appendChild(row);

        msgInput.value = '';
        msgInput.style.height = 'auto';
        chatBody.scrollTop = chatBody.scrollHeight;

        // Cập nhật preview ở danh sách conversation
        const previewText = files.length > 0 ? (text || `📷 Đã gửi ${files.length} ảnh`) : text;
        const preview = currentItem.querySelector('small.text-muted');
        if (preview) {
            preview.textContent = 'Bạn: ' + (previewText.length > 30 ? previewText.slice(0, 30) + '...' : previewText);
        }

        // Gửi lên server bằng FormData
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const formData = new FormData();
        if (text) formData.append('content', text);
        files.forEach(file => formData.append('files[]', file));
        startLoading();
        fetch(`/message/send/${userId}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData,
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const timeEl = currentItem.querySelectorAll('small.text-muted')[1];
                    if (timeEl) timeEl.textContent = 'Vừa xong';
                    const convoList = document.getElementById('msgConvoList');
                    if (convoList && currentItem) convoList.prepend(currentItem);
                } else {
                    bubble.classList.add('text-danger');
                    bubble.innerHTML += ' (Lỗi)';
                }
            })
            .catch(() => {
                bubble.classList.add('text-danger');
                bubble.innerHTML += ' (Lỗi gửi)';
            })                .finally(() => {
                    finishLoading();
                });

        // Xóa preview media và reset files
        window.msgSelectedFiles = [];
        const previewContainer = document.querySelector('.chat-form .preview-media');
        if (previewContainer) previewContainer.innerHTML = '';
    }

    // ===== Auto-resize textarea =====
    msgInput.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 100) + 'px';
    });

    // ===== Enter gửi, Shift+Enter xuống dòng =====
    msgInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // ===== Tìm kiếm conversation =====
    const searchInput = document.getElementById('msgSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.convo-item').forEach(item => {
                const name = item.querySelector('.fw-semibold')?.textContent.toLowerCase() || '';
                item.style.display = name.includes(q) ? '' : 'none';
            });
        });
    }

    // ===== Bind events bằng addEventListener thay vì onclick =====

    // Click vào từng conversation item
    document.querySelectorAll('.convo-item').forEach(item => {
        item.addEventListener('click', function () {
            const name = this.dataset.name || '';
            const status = this.dataset.status || '';
            const online = this.dataset.online === 'true';
            openChat(this, name, status, online);
        });
    });

    // Nút "Gửi tin nhắn" ở empty state
    const sendBtn = document.getElementById('msgNewConvoBtn');
    if (sendBtn) sendBtn.addEventListener('click', showNewConvo);

    // Nút gửi tin nhắn trong chat
    const msgSendBtn = document.getElementById('msgSendBtn');
    if (msgSendBtn) msgSendBtn.addEventListener('click', sendMessage);

    // Nút back (mobile)
    const backBtn = document.getElementById('msgBackBtn');
    if (backBtn) backBtn.addEventListener('click', closeChat);
}
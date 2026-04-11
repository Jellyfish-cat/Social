// ===== Conversation / Message Page =====
// Chỉ chạy trên trang có #msgPage
const msgPage = document.getElementById('msgPage');
let currentItem = null;
let currentUserId = null;
if (msgPage) {
    const emptyState = document.getElementById('msgEmptyState');
    const chatPanel = document.getElementById('msgChatPanel');
    const chatName = document.getElementById('chatName');
    const chatStatus = document.getElementById('chatStatus');
    const chatOnlineDot = document.getElementById('chatOnlineDot');
    const chatBody = document.getElementById('msgChatBody');
    const msgInput = document.getElementById('msgInput');

    // ===== Mở chat khi click vào conversation =====
    function openChat(el, name, status, online) {
        document.querySelectorAll('.convo-item').forEach(i => i.classList.remove('active'));
        el.classList.add('active');
        currentItem = el;
        currentUserId = el.dataset.userId;
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
        // ===== ĐÁNH DẤU ĐÃ ĐỌC =====
        if (el.classList.contains('unread')) {
            el.classList.remove('unread');
            const unreadBadge = el.querySelector('.msg-unread-count');
            let unreadCount = 1;
            if (unreadBadge) {
                unreadCount = parseInt(unreadBadge.textContent) || 1;
                unreadBadge.remove();
            }

            const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
            if (csrfTokenElement) {
                fetch(`/messages/read/${userId}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfTokenElement.content }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const globalBadge = document.getElementById('global-mess-badge');
                            if (globalBadge) {
                                let count = parseInt(globalBadge.textContent) || 0;
                                count = Math.max(0, count - unreadCount);
                                globalBadge.textContent = count;
                                if (count === 0) globalBadge.classList.add('d-none');
                            }
                        }
                    })
                    .catch(err => console.error("Lỗi cập nhật tin đã đọc:", err));
            }
        }
        // reset preview về bình thường (bỏ bold)
        const preview = el.querySelector('small.text-muted');
        if (preview) preview.innerHTML = preview.textContent;

    }

    // ===== Đóng chat (mobile) =====
    function closeChat() {
        msgPage.classList.remove('show-chat');
        chatPanel.classList.remove('open');
        emptyState.style.display = 'flex';
    }

    // ===== Gửi tin nhắn =====
    function sendMessage() {
        const text = msgInput.value.trim();
        const files = window.msgSelectedFiles || [];
        if (!text && files.length === 0) return;
        const userId = currentUserId;
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
        if (currentItem) {
            const previewText = files.length > 0 ? (text || `📷 Đã gửi ${files.length} ảnh`) : text;
            const preview = currentItem.querySelector('small.text-muted');
            if (preview) {
                preview.textContent = 'Bạn: ' + (previewText.length > 30 ? previewText.slice(0, 30) + '...' : previewText);
            }
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
                    // Cập nhật ID cho bubble vừa gửi để có thể thu hồi ngay
                    row.dataset.id = data.message.id;

                    // Thêm nút thu hồi (prepend vì row-reverse để nó nằm bên phải bubble)
                    const unsendBtn = document.createElement('button');
                    unsendBtn.className = 'btn-unsend-msg p-0 border-0 bg-transparent text-muted order-1';
                    unsendBtn.title = 'Thu hồi tin nhắn';
                    unsendBtn.style.fontSize = '0.8rem';
                    unsendBtn.style.margin = '0 5px';
                    unsendBtn.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i>';
                    unsendBtn.onclick = function () { unsendMsg(this, data.message.id); };
                    row.prepend(unsendBtn);

                    const emptyUI = document.getElementById('chatEmptyUI');
                    if (emptyUI) {
                        renderHeader(data.user);
                        emptyUI.remove();
                    }
                    if (currentItem) {
                        const smalls = currentItem.querySelectorAll('small.text-muted');
                        if (smalls.length >= 2) {
                            smalls[1].textContent = 'Vừa xong';
                        }
                        const convoList = document.getElementById('msgConvoList');
                        if (convoList) convoList.prepend(currentItem);
                    }
                } else {
                    throw new Error(data.error || 'Lỗi không xác định');
                }
            })
            .catch(error => {
                console.error('Lỗi gửi tin nhắn:', error);
                bubble.classList.add('text-danger');
                bubble.innerHTML += ` (Lỗi: ${error.error || error.message || 'Gửi thất bại'})`;
            })
            .finally(() => {
                finishLoading();
            });

        // Xóa preview media và reset files
        window.msgSelectedFiles = [];
        const previewContainer = document.querySelector('.chat-form .preview-media');
        if (previewContainer) previewContainer.innerHTML = '';
    }
    function renderHeader(user) {
        const avatar = user.avatar
            ? `/storage/${user.avatar}`
            : `/storage/default-avatar.png`;

        const html = `
        <div class="msg-header d-flex flex-column align-items-center py-4 border-bottom">

            <img src="${avatar}"
                class="rounded-circle mb-2"
                style="width:80px;height:80px;object-fit:cover;">

            <div class="fw-semibold fs-5">
                ${user.displayName}
            </div>

            <div class="text-muted small mb-3">
                ${user.name || ''}
            </div>

            <a href="/profile/${user.id}" 
                class="btn btn-light rounded-pill px-3">
                Xem trang cá nhân
            </a> 

        </div>
    `;
        const header = document.getElementById('msgHeader');
        if (header) header.innerHTML = html;
    }
    // ===== Auto-resize textarea =====
    if (msgInput) {
        msgInput.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });

        msgInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
    }
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
    document.addEventListener('click', function (e) {
        const item = e.target.closest('.convo-item');
        if (!item) return;
        const name = item.dataset.name || '';
        const status = item.dataset.status || '';
        const online = item.dataset.online === 'true';
        const userId = item.dataset.userId;
        if (!userId) return;
        openChat(item, name, status, online);
        updateHistory(userId);
    });

    // Nút gửi tin nhắn trong chat
    const msgSendBtn = document.getElementById('msgSendBtn');
    if (msgSendBtn) msgSendBtn.addEventListener('click', sendMessage);

    // Nút back (mobile)
    const backBtn = document.getElementById('msgBackBtn');
    if (backBtn) backBtn.addEventListener('click', closeChat);

    function updateHistory(userId) {
        const url = new URL(window.location.href);
        url.searchParams.set('chat', userId);
        window.history.pushState({ chat: userId }, '', url.toString());
    }
    //bắt click back
    window.addEventListener("popstate", function (e) {
        if (e.state && e.state.chat) {
            const chatId = e.state.chat;
            const convoItem = document.querySelector(`.convo-item[data-user-id="${chatId}"]`);
            if (convoItem) {
                const name = convoItem.dataset.name || '';
                const status = convoItem.dataset.status || '';
                const online = convoItem.dataset.online === 'true';
                openChat(convoItem, name, status, online);
            }
        }
    });
    window.addEventListener("DOMContentLoaded", () => {
        const urlParams = new URLSearchParams(window.location.search);
        const chatId = urlParams.get('chat');
        if (chatId) {
            currentUserId = chatId;
            startLoading();
            fetch(`/message/chat/${chatId}`)
                .then(res => res.text())
                .then(html => {
                    chatBody.innerHTML = html;
                    // set UI cơ bản
                    emptyState.style.display = 'none';
                    chatPanel.classList.add('open');
                    msgPage.classList.add('show-chat');
                    const fakeItem = document.querySelector(`.convo-item[data-user-id="${chatId}"]`);
                    if (fakeItem) {
                        currentItem = fakeItem;
                        fakeItem.classList.add('active');
                        chatName.textContent = fakeItem.dataset.name || '';
                        chatStatus.textContent = fakeItem.dataset.status || '';
                    }
                    requestAnimationFrame(() => {
                        chatBody.scrollTop = chatBody.scrollHeight;
                    });
                })
                .finally(() => finishLoading());
        }
    });

    // ===== Thu hồi tin nhắn =====
    window.unsendMsg = function (btn, msgId) {
        if (!confirm('Bạn muốn thu hồi tin nhắn này?')) return;

        fetch(`/message/destroy/${msgId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const row = btn.closest('.msg-bubble-row');
                    const bubble = row ? row.querySelector('.msg-bubble') : null;
                    if (bubble) {
                        bubble.innerHTML = '<div class="text-muted small fst-italic">Tin nhắn đã bị thu hồi</div>';
                    }
                    btn.remove(); // Xóa nút sau khi thu hồi xong
                } else {
                    alert(data.message || 'Lỗi khi thu hồi tin nhắn');
                }
            })
            .catch(err => console.error('Lỗi thu hồi:', err));
    };
}
//hàm gợi ý user
const input = document.getElementById("user-input");
const suggestions_user = document.getElementById("suggestions-user");
let controller;
if (input) {
    let debounce;
    input.addEventListener("input", () => {
        clearTimeout(debounce);
        debounce = setTimeout(async () => {
            let q = input.value.trim().toLowerCase();
            if (controller) controller.abort();
            controller = new AbortController();
            if (!q) {
                suggestions_user.innerHTML = "";
                return;
            }
            try {
                let res = await fetch(`/conversation/search?q=${q}`, {
                    signal: controller.signal
                });
                let data = await res.json();
                if (!input.value.trim()) return;
                suggestions_user.innerHTML = data.map(t => {
                    const avatar = t.profile?.avatar
                        ? `/storage/${t.profile.avatar}`
                        : `/storage/default-avatar.png`;

                    return `
                        <a href="/message?chat=${t.id}"
                           class="d-flex align-items-center gap-3 p-2 text-decoration-none text-dark rounded-3 suggest-item">
                            <img src="${avatar}"
                                 onerror="this.src='/storage/default-avatar.png'"
                                 class="rounded-circle"
                                 style="width: 40px; height: 40px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <div class="fw-semibold small">
                                    ${t.profile?.display_name ?? t.name}
                                </div>
                                <div class="text-muted small">
                                    @${t.name}
                                </div>
                            </div>
                        </a>
                    `;
                }).join("");
            } catch (err) {
                if (err.name === "AbortError") return;
                console.error("Search lỗi:", err);
            }
        }, 300);
    });
}
// XỬ LÝ WEBSOCKET
const metaAuth = document.querySelector('meta[name="auth-user-id"]');
const authUserId = metaAuth ? metaAuth.content : null;

// Dùng setTimeout để đợi file Vite (app.js) nạp xong window.Echo
setTimeout(() => {
    if (window.Echo && authUserId) {
        console.log("Đã kết nối Echo thành công cho user ID: " + authUserId); // Log dòng này ra F12 để check

        window.Echo.private(`chat.${authUserId}`)
            .listen('.message.deleted', (e) => {
                const msgRow = document.querySelector(`.msg-bubble-row[data-id="${e.messageId}"]`);
                if (msgRow) {
                    const bubble = msgRow.querySelector('.msg-bubble');
                    if (bubble) {
                        bubble.innerHTML = '<div class="text-muted small fst-italic">Tin nhắn đã bị thu hồi</div>';
                    }
                }
            })
            .listen('MessageSent', (e) => {
                console.log("Đã nhận được tin nhắn realtime!!", e); // Bắn log ra F12 để check luôn
                const incomingMsg = e.message;
                // Nếu bạn ĐANG MỞ khung chat với người vừa gửi tin tới
                if (currentUserId == incomingMsg.sender_id) {
                    fetch(`/messages/read/${incomingMsg.sender_id}`, {
                        method: 'POST',
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                        }
                    });
                    const row = document.createElement('div');
                    // Dùng Flexbox d-flex ở Bootstrap để avatar và bong bóng nằm trên 1 hàng
                    row.className = 'msg-bubble-row theirs mb-2 d-flex align-items-end';

                    // --- [THÊM MỚI] 1. Khởi tạo thẻ Avatar ---
                    const avatarUrl = incomingMsg.sender_avatar ? `/storage/${incomingMsg.sender_avatar}` : '/storage/default-avatar.png';
                    const avatarImg = document.createElement('img');
                    avatarImg.src = avatarUrl;
                    avatarImg.className = 'rounded-circle me-2'; // me-2 để cách bong bóng 1 chút
                    avatarImg.style.cssText = 'width: 28px; height: 28px; object-fit: cover;';

                    // --- 2. Khởi tạo Bong bóng ---
                    const bubble = document.createElement('div');
                    bubble.className = 'msg-bubble theirs';

                    // Render Ảnh/Video (nếu có - giữ nguyên đoạn code cũ của bạn ở đây)
                    let mediaHtml = '';
                    if (incomingMsg.media && incomingMsg.media.length > 0) {
                        incomingMsg.media.forEach(m => {
                            if (m.type === 'image') {
                                mediaHtml += `<img src="${m.file_path}" style="max-width:200px;border-radius:12px;" class="mb-1 d-block">`;
                            } else if (m.type === 'video') {
                                mediaHtml += `<video src="${m.file_path}" style="max-width:200px;border-radius:12px;" controls class="mb-1 d-block"></video>`;
                            }
                        });
                    }

                    bubble.innerHTML = mediaHtml;
                    if (incomingMsg.content) {
                        bubble.innerHTML += `<div class="mt-1 messege-item">${incomingMsg.content}</div>`;
                    }

                    // --- 3. Gắn giao diện theo thứ tự: Avatar trước -> Bong bóng sau ---
                    row.appendChild(avatarImg);
                    row.appendChild(bubble);

                    const localChatBody = document.getElementById('msgChatBody');
                    if (localChatBody) {
                        localChatBody.appendChild(row);
                        // Tự động cuộn xuống dưới cùng
                        localChatBody.scrollTop = localChatBody.scrollHeight;
                    }

                    // Gán ID để có thể thu hồi real-time
                    row.dataset.id = incomingMsg.id;

                    // --- 4. Cập nhật Sidebar Preview và đẩy lên đầu tiên ---
                    const activeConvoItem = document.querySelector(`.convo-item[data-user-id="${incomingMsg.sender_id}"]`);
                    if (activeConvoItem) {
                        const preview = activeConvoItem.querySelector('small.text-muted');
                        let textPreview = incomingMsg.content;
                        if (!textPreview && incomingMsg.media?.length > 0) {
                            textPreview = incomingMsg.media.length > 1 ? `📷 ${incomingMsg.media.length} ảnh` : '📷 Ảnh';
                        }
                        if (preview) {
                            // Cắt 30 ký tự và không in đậm (vì người dùng đang xem chat)
                            let shortText = textPreview.length > 30 ? textPreview.substring(0, 30) + '...' : textPreview;
                            preview.textContent = shortText;
                        }
                        // Cập nhật lại thời gian
                        const smalls = activeConvoItem.querySelectorAll('small.text-muted');
                        if (smalls.length >= 2) {
                            smalls[1].textContent = 'Vừa xong';
                        }
                        // Đẩy lên vị trí đầu tiên
                        const convoList = document.getElementById('msgConvoList');
                        if (convoList) convoList.prepend(activeConvoItem);
                    }

                } else {
                    const senderId = incomingMsg.sender_id;
                    let convoItem = document.querySelector(`.convo-item[data-user-id="${senderId}"]`);
                    // ===== 1. Nếu chưa có conversation thì tạo mới
                    if (!convoItem) {
                        const convoList = document.getElementById('msgConvoList');
                        const avatar = incomingMsg.sender_avatar
                            ? `/storage/${incomingMsg.sender_avatar}`
                            : '/storage/default-avatar.png';
                        convoItem = document.createElement('div');
                        convoItem.className = 'd-flex align-items-center px-3 py-2 gap-2 convo-item unread';
                        convoItem.dataset.userId = senderId;
                        convoItem.dataset.name = incomingMsg.sender_name || 'User';
                        convoItem.dataset.status = '';
                        convoItem.dataset.online = 'false';
                        convoItem.innerHTML = `
                                <img src="${avatar}" class="rounded-circle" width="50" height="50">
                                <div class="flex-grow-1 text-truncate">
                                    <div class="fw-semibold msg-convo-name">
                                        ${incomingMsg.sender_name || 'User'}
                                    </div>
                                    <small class="text-muted"></small>
                                </div>
                                <small class="text-muted">Vừa xong</small>
                            `;

                        if (convoList) convoList.prepend(convoItem);
                    }
                    // ===== 2. UPDATE PREVIEW
                    const preview = convoItem.querySelector('small.text-muted');

                    let textPreview = incomingMsg.content;

                    if (!textPreview && incomingMsg.media?.length > 0) {
                        textPreview = incomingMsg.media.length > 1
                            ? `📷 ${incomingMsg.media.length} ảnh`
                            : '📷 Ảnh';
                    }
                    if (preview) {
                        // Cắt 30 ký tự giống hàm Str::limit của Blade
                        let shortText = textPreview.length > 30 ? textPreview.substring(0, 30) + '...' : textPreview;
                        preview.innerHTML = `<strong>${shortText}</strong>`;
                    }
                    // ===== 3. ADD UNREAD CLASS
                    convoItem.classList.add('unread');
                    // ===== 4. SỐ TIN CHƯA ĐỌC
                    let badge = convoItem.querySelector('.msg-unread-count');

                    if (!badge) {
                        badge = document.createElement('span');
                        badge.className = 'msg-unread-count badge bg-primary rounded-pill ms-2';
                        badge.textContent = '1';

                        const right = convoItem.querySelector('.flex-grow-1');
                        if (right) right.appendChild(badge);
                    } else {
                        badge.textContent = parseInt(badge.textContent) + 1;
                    }
                    // ===== 5. UPDATE TIME
                    const smalls = convoItem.querySelectorAll('small.text-muted');
                    if (smalls.length >= 2) {
                        smalls[1].textContent = 'Vừa xong';
                    }
                    // ===== 6. ĐẨY LÊN ĐẦU LIST
                    const convoList = document.getElementById('msgConvoList');
                    if (convoList) convoList.prepend(convoItem);
                }
            });
    } else {
        console.error("Lỗi: window.Echo vẫn chưa tải được sau 2 giây!");
    }
}, 2000); // Đợi 2 giây
//nút icon

document.addEventListener('click', (e) => {
    const btn = e.target.closest('#emojiBtn, .msg-input-icon');
    if (btn) {
        e.preventDefault();
        const formContainer = btn.closest('.constantIcon');
        if (!formContainer) return;
        const iconInput = formContainer.querySelector('#msgInput, textarea[name="content"], input[type="text"]');
        const pickerContainer = formContainer.querySelector('#emojiPicker');
        if (!pickerContainer || !iconInput) return;
        pickerContainer.style.display = pickerContainer.style.display === 'none' ? 'block' : 'none';
        if (pickerContainer.style.display === 'block' && pickerContainer.childElementCount === 0) {
            const picker = new EmojiMart.Picker({
                onEmojiSelect: (emoji) => {
                    iconInput.value += emoji.native;
                    iconInput.focus();
                }
            });
            pickerContainer.appendChild(picker);
        }
        return;
    }

    // 2. Logic kiểm tra để thu gọn Emoji khi người dùng ấn chuột ra khỏi vùng Picker
    // Tìm tất cả các khung Picker hiển thị trên trang hiện tại để lặp qua
    document.querySelectorAll('#emojiPicker').forEach(pickerContainer => {
        if (pickerContainer.style.display === 'block') {
            const formContainer = pickerContainer.closest('form, .d-flex.align-items-center');
            const relatedBtn = formContainer ? formContainer.querySelector('#emojiBtn, .msg-input-icon') : null;

            // Xóa đi (ẩn đi) nếu chỗ vừa nhấn vào KHÔNG nằm trong popup picker và ĐỒNG THỜI cũng không nằm trong cái Nút đóng/mở của nó
            if (!pickerContainer.contains(e.target) && (!relatedBtn || !relatedBtn.contains(e.target))) {
                pickerContainer.style.display = 'none';
            }
        }
    });
});

// ==========================================================
// ====== LOGIC GLOBAL WEBSOCKET: UPDATE SỐ Ở THANH NAV ======
// Đoạn này nằm ngoài msgPage để trang nào tải lên nó cũng chạy
// ==========================================================
setTimeout(() => {
    const metaAuth = document.querySelector('meta[name="auth-user-id"]');
    const authUserId = metaAuth ? metaAuth.content : null;

    if (window.Echo && authUserId) {
        window.Echo.private(`chat.${authUserId}`)
            .listen('MessageSent', (e) => {
                const incomingMsg = e.message;

                // Nếu tin nhắn gửi đến mình (từ người khác)
                if (incomingMsg.sender_id != authUserId) {
                    const globalBadge = document.getElementById('global-mess-badge');
                    if (globalBadge) {
                        // Nếu đang ở TRONG web nhắn tin và ĐANG XEM đúng ông đó -> Ko cộng số báo
                        if (typeof currentUserId !== 'undefined' && currentUserId == incomingMsg.sender_id) {
                            return;
                        }

                        // Nhảy số
                        let count = parseInt(globalBadge.textContent) || 0;
                        count += 1;

                        globalBadge.textContent = count;
                        globalBadge.classList.remove('d-none'); // Xóa thẻ ẩn dòng đi

                        // Animation thu hút
                        globalBadge.style.transform = 'scale(1.4)';
                        setTimeout(() => {
                            globalBadge.style.transform = 'scale(1)';
                        }, 300);
                    }
                }
            });
    }
}, 3000);
document.addEventListener('click', function (e) {

    const btn = e.target.closest('.btn-delete-conversation');
    if (btn) {
        const postId = btn.dataset.id;
        if (!confirm('Xóa bài viết này sẽ xóa toàn bộ ảnh/video liên quan. Bạn chắc chứ?')) {
            return;
        }
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        fetch(`/conversation/destroy/${postId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const row = btn.closest(".conversation-item");
                    if (row) {
                        // Hiệu ứng mượt
                        row.style.transition = "all 0.3s ease";
                        row.style.opacity = "0";
                        setTimeout(() => {
                            row.remove();
                            document.querySelector(".count-conversation").innerText =
                                `Tổng hộp thoại: ${data.count}`;
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
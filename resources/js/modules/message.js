// Xóa 1 ảnh theo index
window.deleteMessageMedia = function (index) {
    window.msgSelectedFiles.splice(index, 1);
    renderAllPreviews();
};

// Render lại toàn bộ preview
function renderAllPreviews() {
    const previewContainer = document.querySelector('.chat-form .preview-media');
    if (!previewContainer) return;
    previewContainer.innerHTML = '';
    if (window.msgSelectedFiles.length === 0) return;
    window.msgSelectedFiles.forEach((file, i) => {
        const url = URL.createObjectURL(file);
        let mediaHtml = '';
        if (file.type.includes('image')) {
            mediaHtml = `<img src="${url}" width="80" class="rounded">`;
        } else if (file.type.includes('video')) {
            mediaHtml = `<video src="${url}" width="100" controls class="rounded"></video>`;
        }
        previewContainer.innerHTML += `
            <div class="position-relative d-inline-block">
                ${mediaHtml}
                <button type="button"
                    onclick="deleteMessageMedia(${i})"
                    class="btn btn-sm btn-danger position-absolute top-0 end-0 rounded-circle"
                    style="width:20px;height:20px;padding:0;line-height:1;font-size:11px;">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        `;
    });
}

// Chọn file → thêm vào mảng và preview
window.previewMessageFiles = function (input) {
    const files = Array.from(input.files);
    if (!files.length) return;

    const MAX_FILES = 5;
    const remaining = MAX_FILES - window.msgSelectedFiles.length;
    if (remaining <= 0) {
        alert(`Tối đa ${MAX_FILES} file mỗi lần gửi.`);
        input.value = '';
        return;
    }
    if (files.length > remaining) {
        alert(`Chỉ có thể thêm ${remaining} file nữa (tối đa ${MAX_FILES}).`);
    }

    // Thêm vào mảng (giới hạn)
    window.msgSelectedFiles.push(...files.slice(0, remaining));
    renderAllPreviews();

    // Reset input để có thể chọn lại cùng file
    input.value = '';
};

// ===== MESSAGE LOGIC (MOVED FROM CONVERTATION.JS) =====
const msgPage = document.getElementById('msgPage');
if (msgPage) {
    const msgInput = document.getElementById('msgInput');
    const chatBody = document.getElementById('msgChatBody');
    const msgSendBtn = document.getElementById('msgSendBtn');

    window.sendMessage = function () {
        const text = msgInput.value.trim();
        const files = window.msgSelectedFiles || [];
        if (!text && files.length === 0) return;

        const userId = window.currentUserId;
        if (!userId) return;

        // Thêm bubble ngay lập tức (optimistic)
        const row = document.createElement('div');
        row.className = 'msg-bubble-row mine';
        const bubble = document.createElement('div');
        bubble.className = 'msg-bubble mine';

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

        row.dataset.id = 'temp-' + Date.now();
        row.dataset.time = Math.floor(Date.now() / 1000);

        // Kiểm tra thời gian để hiện phân cách (quá 1 tiếng = 3600s)
        const lastMsgRow = chatBody.querySelector('.msg-bubble-row:last-of-type');
        const now = parseInt(row.dataset.time);
        if (!lastMsgRow || (now - parseInt(lastMsgRow.dataset.time || 0)) > 3600) {
            const timeDiv = document.createElement('div');
            timeDiv.className = 'msg-bubble-time';
            const d = new Date();
            timeDiv.textContent = `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')} ${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}`;
            chatBody.appendChild(timeDiv);
        }

        row.appendChild(bubble);
        chatBody.appendChild(row);

        msgInput.value = '';
        msgInput.style.height = 'auto';
        chatBody.scrollTop = chatBody.scrollHeight;

        // Cập nhật preview ở danh sách conversation
        if (window.currentItem) {
            const previewText = files.length > 0 ? (text || `📷 Đã gửi ${files.length} ảnh`) : text;
            const preview = window.currentItem.querySelector('small.text-muted');
            if (preview) {
                preview.textContent = 'Bạn: ' + (previewText.length > 30 ? previewText.slice(0, 30) + '...' : previewText);
            }
        }

        const formData = new FormData();
        if (text) formData.append('content', text);
        files.forEach(file => formData.append('files[]', file));

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const isGroup = window.currentItem ? window.currentItem.dataset.isGroup === 'true' : false;
        const targetId = window.currentUserId;
        const fetchUrl = isGroup ? `/message/group/send/${targetId}` : `/message/send/${targetId}`;

        if (typeof startLoading === 'function') startLoading();
        fetch(fetchUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData,
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    row.dataset.id = data.message.id;
                    const unsendBtn = document.createElement('button');
                    unsendBtn.className = 'btn-unsend-msg p-0 border-0 bg-transparent text-muted order-1';
                    unsendBtn.title = 'Thu hồi tin nhắn';
                    unsendBtn.style.fontSize = '0.8rem';
                    unsendBtn.style.margin = '0 5px';
                    unsendBtn.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i>';
                    unsendBtn.onclick = function () { window.unsendMsg(this, data.message.id); };
                    row.prepend(unsendBtn);

                    if (window.currentItem) {
                        const smalls = window.currentItem.querySelectorAll('small.text-muted');
                        if (smalls.length >= 2) smalls[1].textContent = 'Vừa xong';
                        const convoList = document.getElementById('msgConvoList');
                        if (convoList) convoList.prepend(window.currentItem);
                    }
                }
            })
            .catch(error => console.error('Lỗi gửi tin nhắn:', error))
            .finally(() => {
                if (typeof finishLoading === 'function') finishLoading();
            });

        window.msgSelectedFiles = [];
        const previewContainer = document.querySelector('.chat-form .preview-media');
        if (previewContainer) previewContainer.innerHTML = '';
    };

    if (msgSendBtn) msgSendBtn.addEventListener('click', window.sendMessage);

    if (msgInput) {
        msgInput.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });
        msgInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                window.sendMessage();
            }
        });
    }

    // ===== Thu hồi tin nhắn =====
    window.unsendMsg = function (btn, msgId) {
        if (!confirm('Bạn muốn thu hồi tin nhắn này?')) return;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        fetch(`/message/destroy/${msgId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const row = btn.closest('.msg-bubble-row');
                    const bubble = row ? row.querySelector('.msg-bubble') : null;
                    if (bubble) bubble.innerHTML = '<div class="text-muted small fst-italic">Tin nhắn đã bị thu hồi</div>';
                    btn.remove();
                }
            })
            .catch(err => console.error('Lỗi thu hồi:', err));
    };

    // ===== Emoji Picker =====
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
        document.querySelectorAll('#emojiPicker').forEach(pickerContainer => {
            if (pickerContainer.style.display === 'block') {
                const formContainer = pickerContainer.closest('form, .d-flex.align-items-center');
                const relatedBtn = formContainer ? formContainer.querySelector('#emojiBtn, .msg-input-icon') : null;
                if (!pickerContainer.contains(e.target) && (!relatedBtn || !relatedBtn.contains(e.target))) {
                    pickerContainer.style.display = 'none';
                }
            }
        });
    });

    // ===== Echo Real-time =====
    const authUserId = document.querySelector('meta[name="auth-user-id"]')?.content;
    setTimeout(() => {
        if (window.Echo && authUserId) {
            window.Echo.private(`chat.${authUserId}`)
                .listen('.message.deleted', (e) => {
                    const msgRow = document.querySelector(`.msg-bubble-row[data-id="${e.messageId}"]`);
                    if (msgRow) {
                        const bubble = msgRow.querySelector('.msg-bubble');
                        if (bubble) bubble.innerHTML = '<div class="text-muted small fst-italic">Tin nhắn đã bị thu hồi</div>';
                    }
                })
                .listen('MessageSent', (e) => {
                    const incomingMsg = e.message;
                    if (!incomingMsg) return;

                    const isGroupEvent = incomingMsg.is_group === true;
                    const currentType = window.currentItem ? window.currentItem.dataset.isGroup : 'false';
                    let isMatch = false;

                    if (isGroupEvent) {
                        isMatch = (currentType === 'true' && window.currentUserId == incomingMsg.conversation_id);
                    } else {
                        isMatch = (currentType === 'false' && window.currentUserId == incomingMsg.sender_id);
                    }

                    if (isMatch && chatBody) {
                        // Đánh dấu đã đọc ngay lập tức vì đang mở chat
                        const readId = isGroupEvent ? incomingMsg.conversation_id : incomingMsg.sender_id;
                        fetch(`/messages/read/${readId}`, {
                            method: 'POST',
                            headers: { "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content }
                        });

                        if (incomingMsg.type === 'notification') {
                            const existingNoti = chatBody.querySelector(`.msg-system-notification[data-id="${incomingMsg.id}"]`);
                            if (!existingNoti) {
                                const notiDiv = document.createElement('div');
                                notiDiv.className = 'msg-system-notification d-flex justify-content-center my-3';
                                notiDiv.dataset.id = incomingMsg.id;
                                notiDiv.dataset.time = incomingMsg.timestamp;
                                notiDiv.innerHTML = `<span class="px-3 py-1 bg-light text-muted small rounded-pill border" style="font-size: 11px;">${incomingMsg.content}</span>`;
                                chatBody.appendChild(notiDiv);
                            }
                        } else {
                            // Kiểm tra thời gian phân cách cho tin nhắn đến
                            const lastMsgRow = chatBody.querySelector('.msg-bubble-row:last-of-type');
                            const incomingTime = incomingMsg.timestamp;
                            if (!lastMsgRow || (incomingTime - parseInt(lastMsgRow.dataset.time || 0)) > 3600) {
                                const timeDiv = document.createElement('div');
                                timeDiv.className = 'msg-bubble-time';
                                timeDiv.textContent = incomingMsg.created_at; // Format H:i d/m đã có sẵn từ backend
                                chatBody.appendChild(timeDiv);
                            }

                            const row = document.createElement('div');
                            row.className = 'msg-bubble-row theirs mb-2 d-flex align-items-end';
                            row.dataset.time = incomingMsg.timestamp;
                            row.dataset.id = incomingMsg.id;
                            const avatarUrl = incomingMsg.sender_avatar ? `/storage/${incomingMsg.sender_avatar}` : '/storage/default-avatar.png';

                            let mediaHtml = '';
                            if (incomingMsg.media && incomingMsg.media.length > 0) {
                                incomingMsg.media.forEach(m => {
                                    if (m.type === 'image') mediaHtml += `<img src="${m.file_path}" style="max-width:200px;border-radius:12px;" class="mb-1 d-block">`;
                                    else if (m.type === 'video') mediaHtml += `<video src="${m.file_path}" style="max-width:200px;border-radius:12px;" controls class="mb-1 d-block"></video>`;
                                });
                            }

                            let nameHtml = isGroupEvent ? `<div class="fw-bold mb-1" style="font-size: 0.75rem; color: #65676b;">${incomingMsg.sender_name || 'Người dùng'}</div>` : '';

                            row.innerHTML = `
                                <img src="${avatarUrl}" class="rounded-circle me-2" style="width: 28px; height: 28px; object-fit: cover;">
                                <div class="msg-bubble theirs">
                                    ${nameHtml}
                                    ${mediaHtml}
                                    ${incomingMsg.content ? `<div class="mt-1 messege-item">${incomingMsg.content}</div>` : ''}
                                </div>
                            `;
                            chatBody.appendChild(row);
                        }
                        chatBody.scrollTop = chatBody.scrollHeight;
                    }

                    // Cập nhật Sidebar
                    const convoList = document.getElementById('msgConvoList');
                    if (convoList) {
                        const selector = isGroupEvent
                            ? `.convo-item[data-convo-id="${incomingMsg.conversation_id}"][data-is-group="true"]`
                            : `.convo-item[data-user-id="${incomingMsg.sender_id}"][data-is-group="false"]`;
                        let convoItem = document.querySelector(selector);

                        if (convoItem) {
                            const preview = convoItem.querySelector('small.text-muted');
                            let senderPrefix = (isGroupEvent && incomingMsg.type !== 'notification') ? (incomingMsg.sender_name || 'Người dùng') + ': ' : '';
                            let mediaText = (incomingMsg.media && incomingMsg.media.length > 0) ? `📷 Đã gửi ${incomingMsg.media.length} ảnh` : '';
                            let textPreview = senderPrefix + (incomingMsg.content || mediaText);

                            if (preview) {
                                let shortText = textPreview.length > 30 ? textPreview.substring(0, 30) + '...' : textPreview;
                                preview.innerHTML = isMatch ? shortText : `<strong>${shortText}</strong>`;
                            }

                            if (!isMatch) {
                                convoItem.classList.add('unread');
                                let badge = convoItem.querySelector('.msg-unread-count');
                                if (badge) {
                                    badge.textContent = (parseInt(badge.textContent) || 0) + 1;
                                } else {
                                    const infoDiv = convoItem.querySelector('.flex-grow-1');
                                    if (infoDiv) {
                                        const newBadge = document.createElement('span');
                                        newBadge.className = 'msg-unread-count badge bg-primary';
                                        newBadge.textContent = '1';
                                        infoDiv.appendChild(newBadge);
                                    }
                                }
                            }
                            convoList.prepend(convoItem);
                        } else {
                            // Tạo mới item nếu chưa có
                            const isGroup = isGroupEvent;
                            const displayName = isGroup ? (incomingMsg.group_name || 'Nhóm mới') : (incomingMsg.sender_name || 'Người dùng');
                            const avatar = isGroup
                                ? (incomingMsg.group_avatar ? `/storage/${incomingMsg.group_avatar}` : '/storage/default-avatar.png')
                                : (incomingMsg.sender_avatar ? `/storage/${incomingMsg.sender_avatar}` : '/storage/default-avatar.png');
                            const targetId = isGroup ? incomingMsg.conversation_id : incomingMsg.sender_id;

                            let senderPrefix = (isGroup && incomingMsg.type !== 'notification') ? (incomingMsg.sender_name || 'Người dùng') + ': ' : '';
                            let mediaText = (incomingMsg.media && incomingMsg.media.length > 0) ? `📷 Đã gửi ${incomingMsg.media.length} ảnh` : '';
                            let textPreview = senderPrefix + (incomingMsg.content || mediaText);
                            let shortText = textPreview.length > 30 ? textPreview.substring(0, 30) + '...' : textPreview;

                            const newConvo = document.createElement('div');
                            newConvo.className = 'd-flex align-items-center px-3 py-2 gap-2 convo-item unread';
                            newConvo.dataset.name = displayName;
                            newConvo.dataset.status = incomingMsg.content || '';
                            newConvo.dataset.online = 'false';
                            newConvo.dataset.isGroup = isGroup ? 'true' : 'false';
                            newConvo.dataset.convoId = incomingMsg.conversation_id;
                            newConvo.dataset.userId = targetId;

                            newConvo.innerHTML = `
                                <img src="${avatar}" class="rounded-circle flex-shrink-0" style="width: 50px; height: 50px; object-fit: cover;">
                                <div class="flex-grow-1 text-truncate">
                                    <div class="fw-semibold">
                                        ${displayName}
                                        ${isGroup ? '<i class="bi bi-people text-muted ms-1" title="Nhóm"></i>' : ''}
                                    </div>
                                    <small class="text-muted"><strong>${shortText}</strong></small>
                                    <span class="msg-unread-count badge bg-primary">1</span>
                                </div>
                                <small class="text-muted">Vừa xong</small>
                            `;
                            convoList.prepend(newConvo);
                        }
                    }
                });
        }
    }, 2000);
}

document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-delete-message');
    if (btn) {
        // ... giữ nguyên code delete message cũ của bạn ở trên
    }
});
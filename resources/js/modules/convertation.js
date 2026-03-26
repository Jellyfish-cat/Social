    const emptyState = document.getElementById('msgEmptyState');
    const chatPanel = document.getElementById('msgChatPanel');
    const chatName  = document.getElementById('chatName');
    const chatStatus = document.getElementById('chatStatus');
    const chatOnlineDot = document.getElementById('chatOnlineDot');
    const chatBody  = document.getElementById('msgChatBody');
    const msgInput  = document.getElementById('msgInput');
    let currentItem = null;

    function openChat(el, name, status, online) {
        // Remove active from all
        document.querySelectorAll('.msg-convo-item').forEach(i => i.classList.remove('active'));
        el.classList.add('active');
        currentItem = el;

        // Update header
        chatName.textContent = name;
        chatStatus.textContent = online ? 'Đang hoạt động' : status;
        chatOnlineDot.style.display = online ? 'block' : 'none';

        // Show chat, hide empty
        emptyState.style.display = 'none';
        chatPanel.classList.add('open');

        // Mobile
        document.getElementById('msgPage').classList.add('show-chat');

        // Scroll to bottom
        setTimeout(() => { chatBody.scrollTop = chatBody.scrollHeight; }, 50);
    }

    function closeChat() {
        document.getElementById('msgPage').classList.remove('show-chat');
        chatPanel.classList.remove('open');
        emptyState.style.display = 'flex';
    }

    function showNewConvo() {
        alert('Tính năng tìm kiếm người dùng để nhắn tin sẽ được phát triển sau.');
    }

    function sendMessage() {
        const text = msgInput.value.trim();
        if (!text) return;

        const row = document.createElement('div');
        row.className = 'msg-bubble-row mine';

        const bubble = document.createElement('div');
        bubble.className = 'msg-bubble mine';
        bubble.textContent = text;

        row.appendChild(bubble);
        chatBody.appendChild(row);

        msgInput.value = '';
        msgInput.style.height = 'auto';
        chatBody.scrollTop = chatBody.scrollHeight;

        // Update preview in conversation list
        if (currentItem) {
            const preview = currentItem.querySelector('.msg-convo-preview');
            if (preview) {
                preview.textContent = 'Bạn: ' + (text.length > 30 ? text.slice(0, 30) + '...' : text);
                preview.classList.remove('unread');
            }
        }
    }

    // Auto-resize textarea
    msgInput.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 100) + 'px';
    });

    // Enter to send (Shift+Enter for newline)
    msgInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Search filter
    document.getElementById('msgSearchInput').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.msg-convo-item').forEach(item => {
            const name = item.querySelector('.msg-convo-name').textContent.toLowerCase();
            item.style.display = name.includes(q) ? '' : 'none';
        });
    });
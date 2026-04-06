document.addEventListener('click', function (e) {
    const item = e.target.closest('.notification-item');
    if (!item) return;

    const id = item.getAttribute('data-notification-id') || item.getAttribute('data-id');
    const isRead = item.getAttribute('data-is-read') === 'true';

    if (!isRead) {
        fetch(`/notifications/read/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    item.classList.remove('bg-light');
                    item.classList.add('bg-white');
                    item.setAttribute('data-is-read', 'true');

                    const textElem = item.querySelector('p');
                    if (textElem) {
                        textElem.classList.remove('fw-semibold', 'text-dark');
                        textElem.classList.add('text-secondary');
                    }

                    const dot = item.querySelector('.unread-dot');
                    if (dot) dot.remove();

                    const badge = document.getElementById('global-noti-badge');
                    if (badge) {
                        let count = parseInt(badge.textContent) || 0;
                        count = Math.max(0, count - 1);
                        badge.textContent = count;
                        if (count === 0) badge.classList.add('d-none');
                    }
                }
            });
    }
});

const userId = document.querySelector('meta[name="auth-user-id"]')?.getAttribute('content');
if (userId && window.Echo) {
    window.Echo.private(`notifications.${userId}`)
        .listen('NotificationSent', (e) => {
            const badge = document.getElementById('global-noti-badge');
            const notidot = document.getElementById('noti-dot');
            if (badge) {
                badge.classList.remove('d-none');
                let count = parseInt(badge.innerText || 0);
                badge.innerText = count + 1;

                // Hiệu ứng nảy thu hút sự chú ý
                badge.style.transform = 'scale(1.5)';
                setTimeout(() => badge.style.transform = 'scale(1)', 300);
            }
            if (notidot) {
                notidot.classList.remove('d-none');
                // Hiệu ứng nảy thu hút sự chú ý
                notidot.style.transform = 'scale(2)';
                setTimeout(() => notidot.style.transform = 'scale(1)', 300);
            }
            // Nếu người dùng đang mở trang danh sách thông báo, tự động reload để hiển thị thông báo mới
            if (window.location.pathname === '/notifications') {
                window.location.reload();
            } else {
                // Nếu đang hiển thị panel dạng trượt, tải lại list
                const panel = document.getElementById('notiPanel');
                if (panel && (panel.style.transform === 'translateX(0px)' || panel.style.transform === 'translateX(0)')) {
                    if (typeof window.loadNotifications === 'function') {
                        window.loadNotifications();
                    }
                }
            }
        });
}
window.loadNotifications = function () {
    const container = document.getElementById('notiContent');
    if (container) {
        container.innerHTML = '<div class="d-flex justify-content-center p-5"><div class="spinner-border text-primary" role="status"></div></div>';
    }
    fetch('/notifications/ajax')
        .then(res => res.text())
        .then(html => {
            if (container) {
                container.innerHTML = html;
            }
        })
        .catch(err => {
            console.error('Load notifications error:', err);
            if (container) {
                container.innerHTML = '<div class="p-5 text-center text-danger">Có lỗi xảy ra khi tải thông báo.</div>';
            }
        });
};

window.openNoti = function () {
    const panel = document.getElementById('notiPanel');
    const overlay = document.getElementById('notiOverlay');

    if (panel) panel.style.transform = 'translateX(0)';
    if (overlay) overlay.classList.remove('d-none');

    window.loadNotifications();
};

window.closeNoti = function () {
    const panel = document.getElementById('notiPanel');
    const overlay = document.getElementById('notiOverlay');

    if (panel) panel.style.transform = 'translateX(-100%)';
    if (overlay) overlay.classList.add('d-none');
};


// =========================
// CLICK OVERLAY (SAFE)
// =========================
const overlayElem = document.getElementById('notiOverlay');
if (overlayElem) {
    overlayElem.addEventListener('click', window.closeNoti);
}

const showLoginModal = () => {
    const modalEl = document.getElementById('loginModal');
    const contentEl = document.getElementById('loginModalContent');
    
    if (!modalEl || !contentEl) return;

    let loginModal = bootstrap.Modal.getInstance(modalEl);
    if (!loginModal) {
        loginModal = new bootstrap.Modal(modalEl);
    }
    loginModal.show();

    fetch('/login', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        contentEl.innerHTML = html;
    })
    .catch(error => {
        console.error('Lỗi khi tải trang đăng nhập:', error);
        contentEl.innerHTML = '<div class="p-4 text-center text-danger">Không thể tải trang đăng nhập. Vui lòng thử lại sau.</div>';
    });
};

document.addEventListener("click", function (e) {
    const target = e.target.closest(".require-login") || e.target.closest(".open-login-modal");
    if (target) {
        if (target.classList.contains('open-login-modal') || !window.isLoggedIn) {
            e.preventDefault();
            e.stopPropagation();
            showLoginModal();
        }
    }
}, true);

// Xử lý cả form submission (ví dụ: form comment)
document.addEventListener("submit", function (e) {
    const target = e.target.closest(".require-login-form");
    if (target && !window.isLoggedIn) {
        e.preventDefault();
        e.stopPropagation();
        showLoginModal();
    }
}, true);

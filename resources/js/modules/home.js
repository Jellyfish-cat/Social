window.startLoading = function () {
    const bar = document.getElementById("loading-bar");
    if (bar) bar.style.width = "30%";
    setTimeout(() => { if (bar) bar.style.width = "60%"; }, 200);
    setTimeout(() => { if (bar) bar.style.width = "85%"; }, 500);
}
window.finishLoading = function () {
    const bar = document.getElementById("loading-bar");
    if (bar) bar.style.width = "100%";
    setTimeout(() => {
        if (bar) bar.style.width = "0%";
    }, 300);
}

// Cuộn lên đầu trang khi reload (chỉ áp dụng cho trang chủ)
if (window.location.pathname === '/') {
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }
    window.scrollTo(0, 0);
}

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('.video-link').forEach(link => {
        const video = document.createElement('video');
        video.src = link.href + "#t=0.5";
        video.crossOrigin = "anonymous";
        video.muted = true;

        video.addEventListener('loadeddata', () => {
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0);

            const base64Image = canvas.toDataURL('image/jpeg');
            link.setAttribute('data-thumb', base64Image);
        });
    });
    const videoOptions = {
        root: null,
        threshold: 0.6
    };
    const videoObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const video = entry.target;

            if (entry.isIntersecting) {
                video.play().catch(() => { });
            } else {
                video.pause();
                video.currentTime = 0;
            }
        });
    }, videoOptions);
    document.querySelectorAll('.feed-video').forEach(v => {
        videoObserver.observe(v);
    });
    if (window.Fancybox) {
        Fancybox.bind("[data-fancybox^='gallery-']", {
            Hash: false,
            autoFocus: false,
            Compact: false,
            Animated: true,
            Thumbs: { autoStart: true },
            Html: {
                video: {
                    autoplay: true,
                    controls: true,
                    format: "mp4"
                }
            }
        });
    }
});

window.openWelcomeModal = function (force = false) {
    const contentArea = document.getElementById("welcomeContent");
    const modalEl = document.getElementById("welcomeModal");
    if (!contentArea || !modalEl) return;

    if (typeof startLoading === 'function') startLoading();

    fetch('/welcome', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(res => res.text())
        .then(html => {
            contentArea.innerHTML = html;
            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            // Xử lý kích hoạt nút OKE dựa trên checkbox
            const checkbox = document.getElementById('agreeCheckbox');
            const okButton = document.getElementById('okButton');
            if (checkbox && okButton) {
                checkbox.addEventListener('change', function () {
                    if (this.checked) {
                        okButton.classList.remove('disabled');
                        okButton.style.opacity = '1';
                        okButton.style.pointerEvents = 'auto';
                    } else {
                        okButton.classList.add('disabled');
                        okButton.style.opacity = '0.5';
                        okButton.style.pointerEvents = 'none';
                    }
                });

                // Cảnh báo khi người dùng định reload hoặc rời trang (chỉ khi force = true)
                const preventReload = (e) => {
                    e.preventDefault();
                    e.returnValue = '';
                };
                
                if (force) {
                    window.addEventListener('beforeunload', preventReload);
                }

                // Khi nhấn OKE, gửi request xóa session để không hiện lại khi reload
                okButton.addEventListener('click', function () {
                    // Hủy bỏ cảnh báo reload
                    if (force) {
                        window.removeEventListener('beforeunload', preventReload);
                    }

                    fetch('/welcome/dismiss', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(() => {
                            console.log("Đã xác nhận quy định.");
                        })
                        .catch(err => console.error("Lỗi xóa session welcome:", err));
                });
            }
        })
        .catch(err => console.error("Lỗi tải quy định:", err))
        .finally(() => {
            if (typeof finishLoading === 'function') finishLoading();
        });
};
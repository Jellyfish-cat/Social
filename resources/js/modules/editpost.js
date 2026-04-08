let deletedMediaIds = [];

// Xem trước ảnh edit
window.previewEditFiles = function () {
    const previewContainer = document.getElementById('preview-container');
    const indicatorsContainer = document.getElementById('carousel-indicators');
    const fileInput = document.getElementById('file');
    if (!fileInput) return;
    const files = fileInput.files;
    const carouselEl = document.getElementById('instaCarousel');
    if (!carouselEl) return;
    const prevBtn = carouselEl.querySelector('.carousel-control-prev');
    const nextBtn = carouselEl.querySelector('.carousel-control-next');

    if (files.length === 0) return;

    Array.from(files).forEach((file) => {
        const reader = new FileReader();
        reader.onload = function (e) {
            const div = document.createElement('div');
            const totalExisting = indicatorsContainer.children.length;
            div.id = `media-item-${totalExisting}`;
            div.className = `carousel-item ${totalExisting === 0 ? 'active' : ''}`;

            let mediaHtml = '';
            if (file.type.includes('image')) {
                mediaHtml = `
                <div class="position-relative">
                    <img src="${e.target.result}" class="d-block w-100">
                    <button type="button"
                        class="remove-single-media shadow"
                        onclick="deleteCurrentMedia(${totalExisting})"
                        title="Xóa tệp này">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                </div>`;
            } else if (file.type.includes('video')) {
                mediaHtml = `
                <div class="position-relative">
                    <video src="${e.target.result}" controls class="d-block w-100"></video>
                    <button type="button"
                        class="remove-single-media shadow"
                        onclick="deleteCurrentMedia(${totalExisting})"
                        title="Xóa tệp này">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                </div>`;
            }

            div.innerHTML = mediaHtml;
            previewContainer.appendChild(div);

            const newIndicator = document.createElement('button');
            newIndicator.type = 'button';
            newIndicator.dataset.bsTarget = '#instaCarousel';
            newIndicator.dataset.bsSlideTo = totalExisting;
            newIndicator.style.cssText = "width: 6px; height: 6px; border-radius: 50%;";

            if (totalExisting === 0) newIndicator.className = 'active';
            indicatorsContainer.appendChild(newIndicator);

            if (totalExisting + 1 > 1) {
                if (prevBtn) prevBtn.classList.remove('d-none');
                if (nextBtn) nextBtn.classList.remove('d-none');
            }
        };
        reader.readAsDataURL(file);
    });
};

// Xóa media đang hiển thị
window.deleteCurrentMedia = function (mediaId) {
    if (!confirm('Bạn có chắc muốn xóa tệp này?')) return;
    const item = document.getElementById(`media-item-${mediaId}`);
    if (!item) return;
    const wasActive = item.classList.contains('active');

    deletedMediaIds.push(mediaId);
    const hiddenInput = document.getElementById('deleted_media_ids');
    if (hiddenInput) {
        hiddenInput.value = deletedMediaIds.join(',');
    }

    item.remove();
    refreshCarousel(wasActive);
};

function refreshCarousel(wasActive = false) {
    const previewContainer = document.getElementById('preview-container');
    const indicatorsContainer = document.getElementById('carousel-indicators');
    if (!previewContainer || !indicatorsContainer) return;
    const items = previewContainer.querySelectorAll('.carousel-item');

    indicatorsContainer.innerHTML = "";

    items.forEach((item, index) => {
        item.classList.remove('active');
        if (index === 0) item.classList.add('active');

        const indicator = document.createElement('button');
        indicator.type = 'button';
        indicator.dataset.bsTarget = '#instaCarousel';
        indicator.dataset.bsSlideTo = index;
        indicator.style.cssText = "width: 6px; height: 6px; border-radius: 50%;";

        if (index === 0) indicator.className = 'active';
        indicatorsContainer.appendChild(indicator);
    });

    const carouselEl = document.getElementById('instaCarousel');
    if (!carouselEl) return;
    const prevBtn = carouselEl.querySelector('.carousel-control-prev');
    const nextBtn = carouselEl.querySelector('.carousel-control-next');

    if (items.length <= 1) {
        if (prevBtn) prevBtn.classList.add('d-none');
        if (nextBtn) nextBtn.classList.add('d-none');
    } else {
        if (prevBtn) prevBtn.classList.remove('d-none');
        if (nextBtn) nextBtn.classList.remove('d-none');
    }
}

document.addEventListener("click", function (e) {
    if (window.Fancybox && Fancybox.getInstance()) return;
    const btn = e.target.closest(".btn-edit-post");
    if (!btn) return;
    e.preventDefault();
    const postId = btn.dataset.id;
    if (!postId) return;
    if (window.matchMedia("(max-width: 992px)").matches) {
        window.location.href = `/posts/edit/${postId}`;
        return;
    }
    if (typeof startLoading === "function") startLoading();
    fetch(`/posts/edit/${postId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(res => res.text())
        .then(html => {
            const contentArea = document.getElementById("editPostContent");
            const modalEl = document.getElementById("editPostModal");
            if (!contentArea || !modalEl) return;
            contentArea.innerHTML = html;
            const modal = new bootstrap.Modal(modalEl);
            const originalUrl = window.location.href;
            modal.show();
            history.pushState({ modalEditId: postId }, '', `/posts/edit/${postId}`);
            modalEl.addEventListener('hidden.bs.modal', function () {
                if (window.location.pathname.includes('/posts/edit/')) {
                    history.pushState(null, '', originalUrl);
                }
            }, { once: true });
            if (window.initTopicFeatures) window.initTopicFeatures();
        })
        .catch(err => {
            console.error(err);
            alert("Không thể tải form chỉnh sửa: " + err.message);
        })
        .finally(() => {
            if (typeof finishLoading === "function") finishLoading();
        });
});

window.addEventListener('popstate', function (event) {
    const modalEl = document.getElementById("editPostModal");
    const modal = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;

    if (event.state && event.state.modalEditId) {
        if (typeof startLoading === "function") startLoading();
        fetch(`/posts/edit/${event.state.modalEditId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.text())
            .then(html => {
                document.getElementById("editPostContent").innerHTML = html;
                if (!modal && modalEl) new bootstrap.Modal(modalEl).show();
                if (window.initTopicFeatures) window.initTopicFeatures();
            })
            .finally(() => {
                if (typeof finishLoading === "function") finishLoading();
            });
    } else {
        if (modal && modalEl.classList.contains('show')) {
            modal.hide();
        }
    }
});

document.addEventListener('submit', function (e) {
    const form = e.target.closest('#postForm');
    if (!form) return;
    const isModalForm = form.closest('#editPostContent');
    if (!isModalForm) return;
    e.preventDefault();

    if (typeof startLoading === "function") startLoading();

    const formData = new FormData(form);
    const action = form.getAttribute('action');
    const postId = action.split('/').pop();

    fetch(action, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
        },
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.html) {
                // 1. Tìm post-item cũ và thay thế bằng HTML mới từ server
                const oldPost = document.querySelector(`.post-item[data-id="${postId}"]`);
                if (oldPost) {
                    oldPost.outerHTML = data.html;
                    
                    // 2. Sau khi thay thế HTML, lấy lại element mới để re-init các tính năng
                    const newPost = document.querySelector(`.post-item[data-id="${postId}"]`);
                    if (newPost) {
                        reinitPostFeatures(newPost);
                    }
                } else {
                    window.location.reload(); // Fallback nếu không tìm thấy
                }

                // 3. Đóng modal
                const modalEl = document.getElementById('editPostModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                console.log(data.message);
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Lỗi hệ thống: ' + err.message);
        })
        .finally(() => {
            if (typeof finishLoading === "function") finishLoading();
        });
});

/**
 * Hàm khởi tạo lại các tính năng cho một bài viết đơn lẻ (Video observer, Fancybox)
 * Giống văn phong trong home.js
 */
function reinitPostFeatures(postEl) {
    // 1. Khởi tạo Thumbnail videos mới (nếu có)
    postEl.querySelectorAll('.video-link').forEach(link => {
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

    // 2. Khởi tạo Video Intersection Observer mới cho bài viết được cập nhật
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
    }, { root: null, threshold: 0.6 });

    postEl.querySelectorAll('.feed-video').forEach(v => {
        videoObserver.observe(v);
    });

    // 3. Re-bind Fancybox (Fancybox sẽ tự động nhận diện selector nên chỉ cần gọi bind lại nếu cần)
    if (window.Fancybox) {
        Fancybox.bind("[data-fancybox^='gallery-']", {
            Compact: false,
            Animated: true,
            Thumbs: { autoStart: true },
            Html: {
                video: { autoplay: true, controls: true, format: "mp4" }
            }
        });
    }
}
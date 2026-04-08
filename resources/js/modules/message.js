// ===== Message Media Preview (Multi-file) =====
// Mảng toàn cục lưu các file đã chọn
window.msgSelectedFiles = [];

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

document.addEventListener('click', function (e) {
                       const btn = e.target.closest('.btn-delete-message');
       if(btn){
        const postId = btn.dataset.id;
        if (!confirm('Xóa bài viết này sẽ xóa toàn bộ ảnh/video liên quan. Bạn chắc chứ?')) {
            return;
        }
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        fetch(`/message/destroy/${postId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const row = btn.closest(".message-item");
            if (row) {
                // Hiệu ứng mượt
                row.style.transition = "all 0.3s ease";
                row.style.opacity = "0";
                setTimeout(() => {
                    row.remove();
                    document.querySelector(".count-message").innerText = 
                    `Tổng tin nhắn: ${data.count}`;
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
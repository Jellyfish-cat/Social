import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();
document.addEventListener("DOMContentLoaded", () => {

//js trang home
//video
 document.querySelectorAll('.video-link').forEach(link => {
        const video = document.createElement('video');
        video.src = link.href + "#t=0.5";
        video.crossOrigin = "anonymous"; // Tránh lỗi bảo mật
        video.muted = true;

        video.addEventListener('loadeddata', () => {
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            // Lấy ảnh đã chụp gán vào data-thumb cho Fancybox
            const base64Image = canvas.toDataURL('image/jpeg');
            link.setAttribute('data-thumb', base64Image);
        });
    });
    // 3. Tự động Play/Pause video khi lướt qua
    const videoOptions = {
        root: null, // Lấy toàn bộ màn hình làm khung nhìn
        threshold: 0.6 // Video phải hiển thị được 60% diện tích thì mới tính là "đang xem"
    };

    const videoObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const video = entry.target;

            if (entry.isIntersecting) {
                // Nếu video lọt vào tầm mắt -> Phát
                video.play().catch(error => {
                    // Trình duyệt thường chặn autoplay nếu chưa có tương tác, 
                    // nên ta bắt lỗi để tránh crash code
                    console.log("Autoplay bị chặn hoặc có lỗi:", error);
                });
            } else {
                // Nếu lướt qua khỏi tầm mắt -> Tắt
                video.pause();
                video.currentTime = 0; // Tùy chọn: Reset về đầu nếu muốn
            }
        });
    }, videoOptions);

    // Bắt đầu theo dõi tất cả các video có class .feed-video
    document.querySelectorAll('.feed-video').forEach(v => {
        videoObserver.observe(v);
    });
if (window.Fancybox) {
    Fancybox.bind("[data-fancybox^='gallery-']", {
        Compact: false,
        Animated: true,
        Thumbs: {
            autoStart: true
        },
        Html: {
            video: {
                autoplay: true,
                controls: true,
                format: "mp4"
            }
        }
    });
}
    /*
   document.addEventListener("DOMContentLoaded", function () {

    let commentPanel = document.getElementById("comment-panel");
    let currentPostId = null;
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {

            if (entry.isIntersecting && entry.intersectionRatio >= 0.8) {

                let postId = entry.target.dataset.id;

                if (currentPostId !== postId) {
                    currentPostId = postId;

                    fetch(`/posts/comments/${postId}`)
                        .then(response => response.text())
                        .then(data => {
                            commentPanel.querySelector(".sidebar-sticky").innerHTML = data;
                        });
                }
            }
        });
    }, {
        threshold: 0.8
    });

    document.querySelectorAll(".post-item").forEach(post => {
        observer.observe(post);
    });

});*/
//mở post detail
document.addEventListener("click", function(e){

    const btn = e.target.closest(".open-post");
    if(!btn) return;

    const postId = btn.dataset.id;

    fetch(`/posts/detail/${postId}`)
    .then(res => res.text())
    .then(html => {

        document.getElementById("postDetailContent").innerHTML = html;

        const modal = new bootstrap.Modal(
            document.getElementById("postDetailModal")
        );

        modal.show();
    });

});
//nút like post
document.addEventListener("click", function(e){

    const btn = e.target.closest(".btn-like");

    if(!btn) return;

    const postId = btn.dataset.id;
   const likeIcons = document.querySelectorAll(`.btn-like[data-id="${postId}"] i`);

    fetch(`/posts/like/${postId}`, {
        method:"POST",
        headers:{
            "Content-Type":"application/json",
            "X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(res=>res.json())
    .then(data=>{
        if(data.success){

             const likeCounts = document.querySelectorAll(`.like-count[data-post-id="${postId}"]`); //tăng lượt thích hiện thị trên 2 trang
                likeCounts.forEach(el => {
                    el.innerText = data.likePost_count + " lượt thích";
                });
            //đổi màu tim thành đỏ
            likeIcons.forEach(icon => {
            icon.classList.toggle("text-danger");
            if(icon.classList.contains("text-danger")){
                icon.classList.replace("bi-heart","bi-heart-fill");
            }else{
                icon.classList.replace("bi-heart-fill","bi-heart");
            }

            });
        }
    });
});
//js trong file detail
    //định dạng scroll khung comment
    document.addEventListener("input", function(e) {
        if (e.target.classList.contains("comment-textarea")) {
            e.target.style.height = "auto";
            e.target.style.height = e.target.scrollHeight + "px";
        }
    });
    //json gửi comment post
            document.addEventListener("click", function(e){

            const button = e.target.closest(".comment-submit");
            if(!button) return;

            e.preventDefault();

            const postId = button.dataset.postId;

            const input = document.querySelector(
                `.comment-textarea[data-post-id="${postId}"]`
            );

            const content = input.value.trim();
            if (!content) return;

            button.disabled = true;

            fetch(`/comments/create/${postId}`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    content: content
                })
            })
            .then(res => res.json())
            .then(data => {

                if (data.success) {

                    const avatar = data.avatar
                        ? `/storage/${data.avatar}`
                        : "https://i.pravatar.cc/150";

                    const commentHtml = `
                  
                    <div class="comment-item d-flex">
                    <img src="${avatar}"
                    class="rounded-circle me-2">

                <div class="w-100">

            <div>
                <span class="fw-bold small">
                    ${data.user_name}
                </span>
                <span class="small ms-1">
                    ${data.content}
                </span>
            </div>

            <div class="d-flex align-items-center mt-1">

                <span class="text-muted me-3" style="font-size:11px;">
                    ${data.created_at}
                </span>

                <form method="POST"
                action="/comments/like/${data.comment_id}"
                class="me-3">
                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                    <button type="submit"
                            class="btn btn-sm p-0 text-muted small">
                        ❤️ ${data.like_count}
                    </button>
                </form>

                <button class="btn btn-sm p-0 text-muted small"
                        onclick="document.getElementById('reply-${data.comment_id}').classList.toggle('d-none')">
                    Trả lời
                </button>
            </div>

            <div id="reply-${data.comment_id}" class="d-none mt-2">

                <form method="POST"
                action="/comments/reply/${data.comment_id}"
                class="d-flex">
                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">

                    <textarea name="content"
            class="form-control border-0 shadow-none small comment-textarea"
            placeholder="Viết bình luận..."
            rows="1"
            required></textarea>
                    <button type="submit"
                            class="btn btn-sm btn-primary">
                        Gửi
                    </button>
                </form>

            </div>
                    `;
                    const commentBox = document.querySelector(
                `.comment-box[data-post-id="${postId}"]`
            );

            commentBox.insertAdjacentHTML("afterbegin", commentHtml);

            input.value = "";
            input.style.height = "35px";
        }

        button.disabled = false;
    })
    .catch(() => {
        button.disabled = false;
    });

});
//js trong file create_posts
 let selectedFiles = [];
 //xem trước ảnh create
window.previewCreateFiles = function() {
    const previewContainer = document.getElementById('preview-container');
    const indicatorsContainer = document.getElementById('carousel-indicators');
    const fileInput = document.getElementById('file');
    const files = fileInput.files;
    
    const prevBtn = document.querySelector('.carousel-control-prev');
    const nextBtn = document.querySelector('.carousel-control-next');

    //thêm ảnh mới vào danh sách chọn trước đó
    Array.from(files).forEach(file => {
        selectedFiles.push(file);
    });

    // Nếu không có ảnh nào cả
    if (selectedFiles.length === 0) {
        previewContainer.innerHTML = `
            <div class="carousel-item active">
                <div class="placeholder-content">
                    <i class="bi bi-image fs-1 mb-3"></i>
                    <p class="fw-medium">Hiện chưa có ảnh</p>
                </div>
            </div>`;
        prevBtn.classList.add('d-none');
        nextBtn.classList.add('d-none');
        return;
    }
    //reset giao diện
    previewContainer.innerHTML = '';
    indicatorsContainer.innerHTML = '';
    //duyệt toàn bộ ảnh
    selectedFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function (e) {
            const div = document.createElement('div');
            div.className = `carousel-item ${index === 0 ? 'active' : ''}`;
            let mediaHtml = '';
            if (file.type.includes('image')) {
                mediaHtml = `<img src="${e.target.result}" class="d-block w-100">`;
            } else if (file.type.includes('video')) {
                mediaHtml = `<video src="${e.target.result}" controls class="d-block w-100"></video>`;
            }
            div.innerHTML = mediaHtml;
            previewContainer.appendChild(div);
            const newIndicator = document.createElement('button');
            newIndicator.type = 'button';
            newIndicator.dataset.bsTarget = '#instaCarousel';
            newIndicator.dataset.bsSlideTo = index;
            newIndicator.style.cssText = "width: 8px; height: 8px; border-radius: 50%; margin-bottom:10px";
            if (index === 0) newIndicator.className = 'active';
            indicatorsContainer.appendChild(newIndicator);
            if (selectedFiles.length > 1) {
                prevBtn.classList.remove('d-none');
                nextBtn.classList.remove('d-none');
            } else {
                prevBtn.classList.add('d-none');
                nextBtn.classList.add('d-none');
            }
        };
        reader.readAsDataURL(file);
    });
    // Reset input để lần sau chọn lại cùng file vẫn trigger change
}
//js trong file edit_posts
    let deletedMediaIds = [];
    //xem trước ảnh edit
window.previewEditFiles = function() {
        const previewContainer = document.getElementById('preview-container');
        const indicatorsContainer = document.getElementById('carousel-indicators');
        const fileInput = document.getElementById('file');
        const files = fileInput.files;

        const prevBtn = document.querySelector('.carousel-control-prev');
        const nextBtn = document.querySelector('.carousel-control-next');

        if (files.length === 0) return;

        Array.from(files).forEach((file) => {
            const reader = new FileReader();

            reader.onload = function (e) {
                const div = document.createElement('div');
                const totalExisting = indicatorsContainer.children.length;
                div.className = `carousel-item ${totalExisting === 0 ? 'active' : ''}`;
                let mediaHtml = '';
                if (file.type.includes('image')) {
                    mediaHtml = `<img src="${e.target.result}" class="d-block w-100">`;
                } else if (file.type.includes('video')) {
                    mediaHtml = `<video src="${e.target.result}" controls class="d-block w-100"></video>`;
                }
                div.innerHTML = mediaHtml;
                previewContainer.appendChild(div);
                // tạo indicator mới
                const newIndicator = document.createElement('button');
                newIndicator.type = 'button';
                newIndicator.dataset.bsTarget = '#instaCarousel';
                newIndicator.dataset.bsSlideTo = totalExisting;
                newIndicator.style.cssText = "width: 6px; height: 6px; border-radius: 50%;";

                if (totalExisting === 0) newIndicator.className = 'active';

                indicatorsContainer.appendChild(newIndicator);

                // hiện nút điều hướng nếu > 1
                if (totalExisting + 1 > 1) {
                    prevBtn.classList.remove('d-none');
                    nextBtn.classList.remove('d-none');
                }
            };

            reader.readAsDataURL(file);
        });

    }

    window.deleteCurrentMedia = function(mediaId) {
        if (!confirm('Bạn có chắc muốn xóa tệp này?')) return;

        const item = document.getElementById(`media-item-${mediaId}`);
        if (!item) return;

        const wasActive = item.classList.contains('active');

        // 1️⃣ Thêm ID vào danh sách cần xóa
        deletedMediaIds.push(mediaId);

        document.getElementById('deleted_media_ids').value = deletedMediaIds.join(',');

        // 2️⃣ Xóa khỏi UI
        item.remove();

        // 3️⃣ Cập nhật lại carousel
        refreshCarousel(wasActive);
    }

    function refreshCarousel(wasActive = false) {
        const previewContainer = document.getElementById('preview-container');
        const indicatorsContainer = document.getElementById('carousel-indicators');
        const items = previewContainer.querySelectorAll('.carousel-item');

        indicatorsContainer.innerHTML = "";

        items.forEach((item, index) => {
            item.classList.remove('active');

            if (index === 0) {
                item.classList.add('active');
            }

            const indicator = document.createElement('button');
            indicator.type = 'button';
            indicator.dataset.bsTarget = '#instaCarousel';
            indicator.dataset.bsSlideTo = index;
            indicator.style.cssText = "width: 6px; height: 6px; border-radius: 50%;";

            if (index === 0) indicator.className = 'active';

            indicatorsContainer.appendChild(indicator);
        });

        const prevBtn = document.querySelector('.carousel-control-prev');
        const nextBtn = document.querySelector('.carousel-control-next');

        if (items.length <= 1) {
            prevBtn.classList.add('d-none');
            nextBtn.classList.add('d-none');
        } else {
            prevBtn.classList.remove('d-none');
            nextBtn.classList.remove('d-none');
        }
    }
});
//
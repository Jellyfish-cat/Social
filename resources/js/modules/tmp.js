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
    let pollingInterval = null;
    document.addEventListener("click", function(e){

        const btn = e.target.closest(".open-post");
        if(!btn) return;
        const postId = btn.dataset.id;
            const commentIcons = document.querySelectorAll(`.open-post[data-id="${postId}"] i`);
        fetch(`/posts/detail/${postId}`)
        .then(res => res.text())
        .then(html => {
                    commentIcons.forEach(icon=>{
                    icon.classList.remove("any-pop"); 
                    void icon.offsetWidth; // reset animation
                    icon.classList.add("any-pop");
                });
            document.getElementById("postDetailContent").innerHTML = html;
            const modalEl = document.getElementById("postDetailModal");
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
            // Bắt đầu polling
            /*startCommentPolling(postId);
            // Khi đóng modal thì dừng polling
            modalEl.addEventListener("hidden.bs.modal", () => {
                clearInterval(pollingInterval);
            });*/

        });

    });
    /*function startCommentPolling(postId){
        // dừng polling cũ nếu có
        if(pollingInterval){
            clearInterval(pollingInterval);
        }
        pollingInterval = setInterval(() => {
            fetch(`/comments/latest/${postId}`)
            .then(res => res.json())
            .then(data => {
                const commentBox = document.querySelector(
                    `.comment-box[data-post-id="${postId}"]`
                );
                if(!commentBox) return;
                data.forEach(comment => {
                    if(document.querySelector(`[data-comment-id="${comment.id}"]`)) return;
                    const html = `
                    <div class="comment-item d-flex" data-comment-id="${comment.id}">
                        <img src="/storage/${comment.avatar}" class="rounded-circle me-2">
                        <div>
                            <span class="fw-bold small">${comment.user_name}</span>
                            <span class="small ms-1">${comment.content}</span>
                        </div>
                    </div>
                    `;
                    commentBox.insertAdjacentHTML("afterbegin", html);

                });

            });

        },3000);

    }*/
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
                    likeIcons.forEach(icon=>{
                    icon.classList.remove("any-pop"); 
                    void icon.offsetWidth; // reset animation
                    icon.classList.add("any-pop");
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
                const sendIcons = document.querySelectorAll(`.comment-submit[data-post-id="${postId}"] i`);
                const form = button.closest(".comment-form");
                const input = form.querySelector(".comment-textarea");
                const parentInput = input.closest("form").querySelector(".parent-id");
                const parentId = parentInput.value;
                if(!input) return;
                if(!input.value.trim()){
                    input.setCustomValidity("Hãy nhập nội dung bình luận!");
                    input.reportValidity();
                    return; 
                }
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
                        content: content,
                        parent_id: parentId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                            sendIcons.forEach(icon=>{
                                icon.classList.remove("any-pop"); 
                                void icon.offsetWidth; // reset animation
                                icon.classList.add("any-pop");
                            });
                        const avatar = data.avatar
                            ? `/storage/${data.avatar}`
                            : "https://i.pravatar.cc/150";
                        const CommentCounts = document.querySelectorAll(`.comment-count[data-post-id="${postId}"]`); //tăng lượt comment hiện thị trên 2 trang
                            CommentCounts.forEach(el => {
                                el.innerText = data.comment_count + " Bình luận";
                            });
                        const commentHtml = `
                        <div class="comment-item d-flex mt-2" data-comment-id="${data.comment_id}">
                            <img src="${avatar}"
                            class="rounded-circle me-2">
                        <div class="w-100" style="min-width:0;">
                        <div class="fw-bold small">
                            ${data.user_name}
                        </div>
                        <div class="small ms-1 content">
                            ${data.content}
                        </div>
                        <div class="d-flex align-items-center">
                        <span class="text-muted me-3" style="font-size:13px;">
                            ${data.created_at}
                        </span>
                        <button class="btn-reply-list me-3 like-comment-count" style="font-size:13px;"
                            data-comment-id="${data.comment_id}"
                            data-username="${data.user_name}"
                            data-post-id="${postId}">
                            ${data.like_count} lượt thích
                        </button>
                        <button class="btn-reply" style="font-size:13px;"
                            data-comment-id="${parentId || data.comment_id}"
                            data-username="${data.user_name}"
                            data-post-id="${postId}">
                            Trả lời
                        </button>
                        <div class="ms-auto d-flex" style="gap:2px;">
                        <button type="button"
                            class="btn-comment-like btn-sm p-0 text-muted small"
                            data-comment-id="${data.comment_id}"
                            data-username="${data.user_name}"
                            data-post-id="${postId}">
                            <i class="bi bi-heart action-icon fs-6 me-2"></i>
                        </button>
                        <button type="button"
                            class="btn btn-sm p-0 text-muted small ms-2"
                            data-comment-id="${data.comment_id}"
                            data-username="${data.user_name}"
                            data-post-id="${postId}">
                            <i class="bi bi-hand-thumbs-down action-icon fs-6"></i>
                        </button>
                            </div>
                        </div>
                        </div>
                        </div>
                        `;
                if(!parentId){
                    const commentBox = document.querySelector(
                        `.comment-box[data-post-id="${postId}"]`
                    );
                    commentBox.insertAdjacentHTML("afterbegin", commentHtml);
                }else{
                   let replyList = document.querySelector(`#reply-${parentId}`);
                    if(replyList){
                        replyList.classList.remove("d-none");
                    }else{
                        const parentComment = document.querySelector(
                            `.comment-item[data-comment-id="${parentId}"] .w-100`
                        );
                        if(parentComment){
                            parentComment.insertAdjacentHTML(
                                "beforeend",
                                `<div class="view-replies text-black small mt-1 mb-2" style="cursor:pointer" data-comment-id="${parentId}">
                                    Ẩn phản hồi
                                </div>
                                <div class="reply-list" id="reply-${parentId}"></div>`
                            );
                            replyList = document.querySelector(`#reply-${parentId}`);
               
                        }
                    }
                    if(replyList){
                        replyList.insertAdjacentHTML("afterbegin", commentHtml);
                    }
                    const replyBox = document.getElementById("reply-"+parentId);
                    const btn = document.querySelector(`.view-replies`);
                    btn.innerHTML = '&mdash;&ndash; Ẩn phản hồi <i class="bi bi-caret-down-fill ms-1"></i>';
                    replyBox.appendChild(btn);
                }
                input.value = "";
                input.style.height = "35px";
            }
            
            button.disabled = false;
            parentInput.value = null;
        })
        .catch(() => {
            button.disabled = false;
        });

    });
    //hủy bình luận
    document.addEventListener("click", function(e){
    const btn = e.target.closest(".btn-cancel-comment");
    if(!btn) return;
    const form = btn.closest(".comment-form");
    const textarea = form.querySelector(".comment-textarea");
    const parentInput = form.querySelector(".parent-id");
    textarea.value = "";
    textarea.style.height = "35px";
    parentInput.value = "";

});
    //js reply
    document.addEventListener("click", function(e){

        const btn = e.target.closest(".btn-reply");
        if(!btn) return;
        const username = btn.dataset.username;
        const commentId = btn.dataset.commentId;
        const parentId = btn.dataset.Parent_
        const postId = btn.dataset.postId;
        const textarea = document.querySelector(
            `.comment-textarea[data-post-id="${postId}"]`
        );
        const parentInput = textarea.closest("form").querySelector(".parent-id");
        textarea.value = `@${username} `;
        parentInput.value = commentId;
        textarea.focus();
        textarea.scrollIntoView({
            behavior:"smooth",
            block:"center"
        });

    });
    //xem comment phản hồi
    document.addEventListener("click", function(e){
        const btn = e.target.closest(".view-replies");
        if(!btn) return;
   
        const id = btn.dataset.commentId;
        const replyBox = document.getElementById("reply-"+id);
        replyBox.classList.toggle("d-none");
        if(replyBox.classList.contains("d-none")){
            const count = replyBox.querySelectorAll(".comment-item").length;
            btn.innerHTML = '&mdash;&ndash; Xem ' + count + ' phản hồi <i class="bi bi-caret-down-fill ms-1"></i>';
             replyBox.before(btn);
              btn.closest(".comment-item").scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });

        }else{
            btn.innerHTML = '&mdash;&ndash; Ẩn phản hồi <i class="bi bi-caret-down-fill ms-1"></i>';
             replyBox.appendChild(btn);
        }

    });
    //like comment
        document.addEventListener("click", function(e){
        const btn = e.target.closest(".btn-comment-like");
        if(!btn) return;
        const commentId = btn.dataset.commentId;
        const likeIcons = document.querySelectorAll(`.btn-comment-like[data-comment-id="${commentId}"] i`);
        fetch(`/comment/like/${commentId}`, {
            method:"POST",
            headers:{
                "Content-Type":"application/json",
                "X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(res=>res.json())
        .then(data=>{
            if(data.success){
                const likeCounts = document.querySelectorAll(`.like-comment-count[data-comment-id="${commentId}"]`); //tăng lượt thích hiện thị trên 2 trang
                    likeCounts.forEach(el => {
                        el.innerText = data.likeComment_count + " lượt thích";
                    });
                    likeIcons.forEach(icon=>{
                    icon.classList.remove("any-pop"); 
                    void icon.offsetWidth; // reset animation
                    icon.classList.add("any-pop");
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
                    }
                    else if (file.type.includes('video')) {
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
        //xóa media đang hiển thị
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
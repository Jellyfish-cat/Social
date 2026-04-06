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
                 const NoComent = document.querySelector(`.comment-box[data-post-id="${postId}"] .no-comment`);
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
                const fileInput = form.querySelector("input[type='file']");
                const formData = new FormData();
                formData.append("content", content);
                formData.append("parent_id", parentId);

                // 👉 THÊM FILE
                if(fileInput && fileInput.files.length > 0){
                    formData.append("file", fileInput.files[0]);
                }
                startLoading();
                fetch(`/comments/create/${postId}`, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
})
                .then(res => res.json())
                .then(data => {console.log(data);
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
                            let mediaHtml = "";
                            if (data.media_path) {
                                const url = `/storage/${data.media_path}`;
                                if (data.is_image) {
                                    mediaHtml = `
                                        <div class="mt-1 comment-media">
                                            <a href="${url}" data-fancybox="gallery-${data.comment_id}">
                                                <img src="${url}" width="100" class="rounded">
                                            </a>
                                        </div>
                                    `;
                                } 
                                else if (data.is_video) {
                                    mediaHtml = `
                                        <div class="mt-1 comment-media">
                                            <a href="${url}" data-fancybox="gallery-${data.comment_id}" data-type="video">
                                                <video width="260" controls class="rounded">
                                                    <source src="${url}">
                                                </video>
                                            </a>
                                        </div>
                                    `;
                                }
                            }
                        const commentHtml = `
                        <div class="comment-item d-flex mt-2" data-comment-id="${data.comment_id}">
                            <img src="${avatar}"
                            class="rounded-circle me-2">
                        <div class="w-100" style="min-width:0;">
                        <div class="d-flex justify-content-between align-items-center">
                        <div class="fw-bold small">
                            ${data.user_name}
                        </div>
                        <div class="dropdown">
                            <i class="bi bi-three-dots text-muted"
                            style="cursor:pointer"
                            data-bs-toggle="dropdown"></i>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                ${data.role ? `
                                    <li>
                                        <a href="javascript:void(0)"
                                        class="dropdown-item small text-danger btn-delete-comment"
                                        data-id="${data.comment_id}">
                                            <i class="bi bi-trash me-2"></i> Xóa
                                        </a>
                                    </li>
                                    `
                                    : ""}
                            </ul>
                        </div>
                    </div>
                        <div class="small ms-1 content">
                            ${data.content}
                        </div>
                        ${mediaHtml}
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
                // reset preview đúng form
                commentFile = null;
                if (NoComent) {
                    NoComent.classList.add('d-none');
                }
                // clear input file
                fileInput.value = "";
                // clear preview UI đúng form
                const previewContainer = form.querySelector('.preview-media');
                if(previewContainer){
                    previewContainer.innerHTML = '';
                }
            }
            button.disabled = false;
            parentInput.value = null;
        })
        
        .catch(() => {
        button.disabled = false;
    })
    .finally(() => {
        finishLoading();
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
        // Tìm container gần nhất: modal detail hoặc post card trên home
        const container = btn.closest('.post-modal, .card.post-card, #postDetailContent, #postDetailModal');
    
        // Tìm textarea TRONG container đó thay vì toàn document
        const textarea = container
        ? container.querySelector(`.comment-textarea[data-post-id="${postId}"]`)
        : document.querySelector(`.comment-textarea[data-post-id="${postId}"]`);
        if(!textarea) return;
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
        startLoading();
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
                            icon.classList.remove("any-pop"); 
                void icon.offsetWidth; // reset animation
                icon.classList.add("any-pop");
                });
            }
        })
            .finally(() => {
        finishLoading();
    });
    });
    //comment ảnh
    let commentFile = null;
    // xóa ảnh
    window.deleteCommentMedia = function() {
        if (!confirm('Bạn có chắc muốn xóa tệp này?')) return;
        commentFile = null;
        renderCommentPreview();
    }
    // render preview
    function renderCommentPreview() {
        const previewContainer = document.querySelector('.preview-media');
        previewContainer.innerHTML = '';
        if (!commentFile) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            let mediaHtml = '';
            if (commentFile.type.includes('image')) {
                mediaHtml = `<img src="${e.target.result}" width="80" class="rounded">`;
            } else if (commentFile.type.includes('video')) {
                mediaHtml = `<video src="${e.target.result}" width="80" controls></video>`;
            }
            previewContainer.innerHTML = `
                <div class="position-relative">
                    ${mediaHtml}
                    <button type="button"
                        onclick="deleteCommentMedia()"
                        class="btn btn-sm btn-danger position-absolute top-0 end-0">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `;
        }
        reader.readAsDataURL(commentFile);
    }
    // chọn file (ghi đè luôn)
    window.previewCommentFiles = function(input) {
    const previewContainer = input.closest(".comment-form").querySelector(".preview-media");
    previewContainer.innerHTML = '';
    const file = input.files[0];
    if(!file) return;
    commentFile = file; //shoc
    const reader = new FileReader();
    reader.onload = function(e){
        if (file.type.includes('image')) {
        previewContainer.innerHTML = `
            <div class="position-relative">
                <img src="${e.target.result}" width="150" class="rounded">
                <button type="button"
                    class="btn btn-sm position-absolute top-0 end-0 remove-single-media shadow remove-small"
                    onclick="this.parentElement.remove(); input.value=''">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        `;
    } else if (file.type.includes('video')) {
        previewContainer.innerHTML = `
            <div class="position-relative">
                <video src="${e.target.result}" width="200" controls class="rounded"></video>
               <button type="button"
                    class="btn btn-sm position-absolute top-0 end-0 remove-single-media shadow remove-small"
                    onclick="this.parentElement.remove(); input.value=''">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        `;
    }
    }
    reader.readAsDataURL(file);
    }
    document.addEventListener("click", function (e) {
        const btn = e.target.closest(".btn-delete-comment");
        if (!btn) return;
        const id = btn.dataset.id;
        if (!id) {
            console.error("Không có ID để xóa");
            return;
        }
        if (!confirm("Bạn có chắc muốn xóa không?")) return;
        // Disable nút để tránh spam click
        btn.disabled = true;
        startLoading();
        fetch(`/comments/destroy/${id}`, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                "Accept": "application/json"
            }
        })
        .then(async (res) => {
            let data = {};
            try {
                data = await res.json();
            } catch (e) {
                console.warn("Response không phải JSON");
            }
            if (!res.ok || !data.success) {
                throw new Error(data.message || "Xóa thất bại");
            }
            return data;
        })
        .then((data) => {
            const row = btn.closest(".comment-item");
            if (row) {
                // Hiệu ứng mượt
                row.style.transition = "all 0.3s ease";
                row.style.opacity = "0";
                setTimeout(() => {
                    row.remove();
                    document.querySelector(".comment-count").innerText = 
                    `Tổng bình luận: ${data.count}`;
                    updateSTT();
                }, 300);
            }
            // Thông báo
            console.log(data.message || "Xóa thành công");
        })
        .catch((err) => {
            alert(err.message);
        })
        .finally(() => {
            btn.disabled = false;
            finishLoading(); 
        });
    });

        
document.addEventListener("click", function (e) {
    if (window.Fancybox && Fancybox.getInstance()) return;
    const btn = e.target.closest(".open-like-comment");
    if (!btn) return;
    const commentID = btn.dataset.commentId;
    if (window.matchMedia("(max-width: 992px)").matches) {
        window.location.href = `/comments/like_list/${commentID}`;
        return;
    }
    const action = btn.dataset.action;
    startLoading();
    fetch(`/comments/like_list/${commentID}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
        .then(res => res.text())
        .then(html => {
            document.getElementById("followDetailContent").innerHTML = html;
            const modalEl = document.getElementById("followDetailModal");
            const modal = new bootstrap.Modal(modalEl);
            // 1. Lưu lại URL hiện tại (URL của profile) trước khi đổi
            const originalUrl = window.location.href;
            modal.show();
            history.pushState({ modaluserId: commentID }, '', `/comments/like_list/${commentID}`);
            // 2. Thêm đoạn này để trả lại URL cũ khi đóng Modal
            modalEl.addEventListener('hidden.bs.modal', function () {
                // Chỉ set lại nếu URL vẫn đang là trang chi tiết bài viết
                if (window.location.pathname.includes('/comments/like_list/')) {
                    history.pushState(null, '', originalUrl);
                }
            }, { once: true });
        })
        .finally(() => {
            finishLoading();
        });
});;

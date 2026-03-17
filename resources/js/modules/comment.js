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
    //comment ảnh
    

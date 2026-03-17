document.addEventListener("click", function(e){
    const btn = e.target.closest(".btn-like");
    if(!btn) return;
    const postId = btn.dataset.id;
    const likeIcons = document.querySelectorAll(`.btn-like[data-id="${postId}"] i`);
    fetch(`/posts/like/${postId}`,{
        method:"POST",
        headers:{
            "Content-Type":"application/json",
            "X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(res=>res.json())
    .then(data=>{
        if(!data.success) return;
        const likeCounts = document.querySelectorAll(`.like-count[data-post-id="${postId}"]`);
        likeCounts.forEach(el=>{
            el.innerText = data.likePost_count + " lượt thích";
        });
        likeIcons.forEach(icon=>{
            icon.classList.toggle("text-danger");
            if(icon.classList.contains("text-danger")){
                icon.classList.replace("bi-heart","bi-heart-fill");
            }else{
                icon.classList.replace("bi-heart-fill","bi-heart");
            }
        });

    });

});
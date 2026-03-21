
    document.addEventListener("click", function (e) {
        const btn = e.target.closest(".btn-delete-topic");
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
        fetch(`/topics/destroy/${id}`, {
            method: "POST",
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
            const row = btn.closest("tr");
            if (row) {
                // Hiệu ứng mượt
                row.style.transition = "all 0.3s ease";
                row.style.opacity = "0";
                setTimeout(() => {
                    row.remove();
                    document.querySelector(".count-topic").innerText = 
                    `Tổng chủ đề: ${data.count}`;
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

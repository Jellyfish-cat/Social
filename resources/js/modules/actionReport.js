document.addEventListener("click", function (e) {
    const openBtn = e.target.closest(".open-report");
    if (openBtn) {
        e.preventDefault();
        if (window.Fancybox && Fancybox.getInstance()) return;
        const targetId = openBtn.dataset.id;
        const targetType = openBtn.dataset.type || 'post'; // mặc định là post nếu không truyền

        if (typeof startLoading === 'function') startLoading();

        fetch(`/reports/create?target_id=${targetId}&target_type=${targetType}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(res => res.text())
            .then(html => {
                const reportContent = document.getElementById("reportContent");
                if (reportContent) {
                    reportContent.innerHTML = html;
                }
                const modalEl = document.getElementById("reportModal");
                if (modalEl) {
                    // Initialize bootstrap modal if not exist, then show it
                    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                    modal.show();
                }
            })
            .catch(err => {
                console.error("Lỗi khi tải giao diện báo cáo:", err);
                alert("Không thể tải giao diện báo cáo. Vui lòng thử lại.");
            })
            .finally(() => {
                if (typeof finishLoading === 'function') finishLoading();
            });

        return;
    }

    // 2. Submit form báo cáo
    const submitBtn = e.target.closest("#submitReportBtn");
    if (submitBtn) {
        e.preventDefault();

        const categoryInput = document.getElementById('report_category');
        const reasonInput = document.getElementById('report_reason');
        if (!categoryInput) return;

        const category = categoryInput.value;
        let reason = reasonInput ? reasonInput.value.trim() : '';

        // Tự động gán bằng tên Danh mục phổ biến nếu người dùng không tự gõ lý do
        if (!reason && category && category !== 'Other') {
            reason = categoryInput.options[categoryInput.selectedIndex].text;
        }
        
        if (!category) {
            alert('Vui lòng chọn danh mục báo cáo.');
            return;
        }

        if (category === 'Other' && !reason) {
            alert('Vui lòng nhập lý do báo cáo.');
            return;
        }

        const formData = {
            target_id: document.getElementById('report_target_id').value,
            target_type: document.getElementById('report_target_type').value,
            category: category,
            reason: reason
        };

        submitBtn.disabled = true;
        const spinner = document.getElementById('reportSpinner');
        if (spinner) spinner.classList.remove('d-none');

        if (window.axios) {
            window.axios.post('/reports/store', formData)
                .then(function (response) {
                    if (response.data.success) {
                        alert(response.data.message || 'Báo cáo thành công.');

                        const modalEl = document.getElementById('reportModal');
                        if (modalEl) {
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        }
                    }
                })
                .catch(function (error) {
                    console.error('Lỗi khi báo cáo:', error);
                    const msg = error.response?.data?.message || 'Có lỗi xảy ra, vui lòng thử lại sau.';
                    alert(msg);
                })
                .finally(function () {
                    submitBtn.disabled = false;
                    if (spinner) spinner.classList.add('d-none');
                });
        } else {
            console.error("Axios chưa được load!");
        }
    }
});

// Event delegation cho trường hợp thay đổi chọn danh mục (vì modal được load qua AJAX)
document.addEventListener("change", function (e) {
    if (e.target.id === 'report_category') {
        const val = e.target.value;
        const container = document.getElementById('report_reason_container');
        const reasonInput = document.getElementById('report_reason');
        
        if (container && reasonInput) {
            container.style.display = 'block';
            if (val === 'Other') {
                reasonInput.setAttribute('required', 'required');
            } else {
                reasonInput.removeAttribute('required');
            }
        }
    }
});

$(document).on('click', '.btn-hide-convo', function(e) {
    e.preventDefault();
    const btn = $(this);
    const convoId = btn.data('id');
    const type = btn.data('type'); // 'hide' or 'show'
    const container = btn.closest('.btn-hide-convo-container');
    const statusBadge = btn.closest('tr').find('.badge').filter(function() {
        return $(this).text().trim() === 'Hoạt động' || $(this).text().trim() === 'Đã ẩn';
    });

    if (confirm(`Bạn có chắc chắn muốn ${type === 'hide' ? 'ẩn' : 'hiển thị'} hội thoại này?`)) {
        if (typeof startLoading === 'function') startLoading();
        btn.prop('disabled', true);

        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        fetch(`/admin/conversations/hide/${convoId}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Cập nhật nút bấm
                if (data.status === 'hidden') {
                    container.html(`
                        <a class="btn btn-success btn-sm btn-hide-convo" data-id="${convoId}" data-type="show" title="Hiển thị hội thoại">
                            <i class="bi bi-eye"></i>
                        </a>
                    `);
                    statusBadge.removeClass('bg-success').addClass('bg-danger').text('Đã ẩn');
                } else {
                    container.html(`
                        <a class="btn btn-secondary btn-sm btn-hide-convo" data-id="${convoId}" data-type="hide" title="Ẩn hội thoại">
                            <i class="bi bi-eye-slash"></i>
                        </a>
                    `);
                    statusBadge.removeClass('bg-danger').addClass('bg-success').text('Hoạt động');
                }
                
                if (window.showToast) {
                    showToast(data.message, 'success');
                } else {
                    console.log(data.message);
                }
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(err => {
            console.error(err);
            alert("Có lỗi xảy ra khi cập nhật trạng thái hội thoại");
        })
        .finally(() => {
            btn.prop('disabled', false);
            if (typeof finishLoading === 'function') finishLoading();
        });
    }
});

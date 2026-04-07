let deletedMediaIds = [];

// Xem trước ảnh edit
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
                prevBtn.classList.remove('d-none');
                nextBtn.classList.remove('d-none');
            }
        };
        reader.readAsDataURL(file);
    });
};

// Xóa media đang hiển thị
window.deleteCurrentMedia = function(mediaId) {
    if (!confirm('Bạn có chắc muốn xóa tệp này?')) return;
    const item = document.getElementById(`media-item-${mediaId}`);
    if (!item) return;
    const wasActive = item.classList.contains('active');
    
    deletedMediaIds.push(mediaId);
    document.getElementById('deleted_media_ids').value = deletedMediaIds.join(',');
    
    item.remove();
    refreshCarousel(wasActive);
};

function refreshCarousel(wasActive = false) {
    const previewContainer = document.getElementById('preview-container');
    const indicatorsContainer = document.getElementById('carousel-indicators');
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
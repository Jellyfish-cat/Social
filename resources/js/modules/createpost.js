let selectedFiles = [];
//xóa media trong create
window.deleteCreateMedia = function(index) {
    if (!confirm('Bạn có chắc muốn xóa tệp này?')) return;
    //xóa khỏi danh sách file
    selectedFiles.splice(index, 1);
    //render lại
    renderCreatePreview();
}
//render preview (tách riêng để tái sử dụng)
function renderCreatePreview(){
    const previewContainer = document.getElementById('preview-container');
    const indicatorsContainer = document.getElementById('carousel-indicators');
    const prevBtn = document.querySelector('.carousel-control-prev');
    const nextBtn = document.querySelector('.carousel-control-next');
    previewContainer.innerHTML='';
    indicatorsContainer.innerHTML='';
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
    selectedFiles.forEach((file,index)=>{
        const reader = new FileReader();
        reader.onload=function(e){
            const div = document.createElement('div');
            div.className=`carousel-item ${index===0?'active':''}`;
            div.id=`media-item-${index}`;
            let mediaHtml='';
            if(file.type.includes('image')){
                mediaHtml=`<img src="${e.target.result}" class="d-block w-100">`;
            }
            else if(file.type.includes('video')){
                mediaHtml=`<video src="${e.target.result}" controls class="d-block w-100"></video>`;
            }
            div.innerHTML=`
            ${mediaHtml}
            <button 
                type="button"
                onclick="deleteCreateMedia(${index})"
                class="remove-single-media shadow">
                <i class="bi bi-trash3-fill"></i>
            </button>
            `;
            previewContainer.appendChild(div);
            const indicator=document.createElement('button');
            indicator.type='button';
            indicator.dataset.bsTarget='#instaCarousel';
            indicator.dataset.bsSlideTo=index;
            indicator.style.cssText="width:6px;height:6px;border-radius:50%;";
            if(index===0) indicator.className='active';
            indicatorsContainer.appendChild(indicator);
            if(selectedFiles.length>1){
                prevBtn.classList.remove('d-none');
                nextBtn.classList.remove('d-none');
            }else{
                prevBtn.classList.add('d-none');
                nextBtn.classList.add('d-none');
            }
        }
        reader.readAsDataURL(file);
    })
}
//xem trước ảnh create
window.previewCreateFiles = function() {
    const fileInput = document.getElementById('file');
    const files = fileInput.files;
    //thêm ảnh mới vào danh sách
    Array.from(files).forEach(file=>{
        selectedFiles.push(file);
    });
    renderCreatePreview();
    //reset input để chọn lại cùng file vẫn trigger
}
//đưa lại file vào input trước khi submit form

const form = document.getElementById("postForm");
if (form) { // <--- Thêm dòng check này
    form.addEventListener("submit", function(e){

        const fileInput = document.getElementById("file");

        if(selectedFiles.length === 0){
            return;
        }

        const dt = new DataTransfer();

        selectedFiles.forEach(file=>{
            dt.items.add(file);
        });

        fileInput.files = dt.files;

    });
}
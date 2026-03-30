let cacheSearch = {}; // Bộ nhớ đệm (Cache) lưu kết quả đã tìm
let searchTimeout = null; // Biến đánh dấu bộ đếm giờ (Debounce)
// Dùng class thay vì ID
const inputSearch = document.querySelector(".search-input");
// DÙNG QUERYSELECTOR thay vì querySelectorAll để có thể nhét innerHTML
const suggestionsContainer = document.querySelector(".search-wrapper #suggestions");
if (inputSearch && suggestionsContainer) {
    // 1. NGHE SỰ KIỆN GÕ PHÍM
    inputSearch.addEventListener("input", () => {
        let q = inputSearch.value.trim().toLowerCase();
        // Xóa lệnh tìm kiếm cũ nếu người dùng vẫn đang gõ liên tục
        clearTimeout(searchTimeout);
        // Giấu bảng nếu người dùng xóa trắng thanh tìm kiếm
        if (!q) {
            suggestionsContainer.innerHTML = "";
            suggestionsContainer.style.display = 'none';
            return;
        }

        // 2. DEBOUNCE - CHỜ 300ms SAU KHI DỪNG GÕ MỚI GỌI API
        searchTimeout = setTimeout(async () => {

            // 3. CACHE: Kiểm tra xem từ gõ này đã từng tìm chưa?
            if (cacheSearch[q]) {
                renderSuggestions(cacheSearch[q]); // Nhả luôn kết quả lưu trong RAM, không gọi rườm rà
                return;
            }

            // Hiển thị vòng xoay trạng thái đang tìm (Loading mượt mà)
            suggestionsContainer.innerHTML = `
                <div class="list-group-item text-center text-muted py-3 border-0">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div> 
                    <span class="ms-2">Đang tìm...</span>
                </div>
            `;
            suggestionsContainer.style.display = 'block';

            // 4. GỌI API TÍCH HỢP TRY-CATCH XỬ LÝ LỖI
            try {
                let res = await fetch(`/search/suggestions?q=${q}`);
                if (!res.ok) throw new Error("Mạng lỗi !");
                let data = await res.json();
                // Lưu thành qủa vào bộ nhớ đệm (Cache) để xài cho lần sau
                cacheSearch[q] = data;
                // Vẽ nội dung
                renderSuggestions(data);

            } catch (error) {
                console.error("Lỗi hệ thống Search:", error);
                suggestionsContainer.innerHTML = `
                    <div class="list-group-item text-danger text-center border-0">
                        <i class="bi bi-exclamation-circle"></i> Kết nối gián đoạn!
                    </div>
                `;
            }
        }, 300); // Con số 300 lý tưởng: Tức là ngưng gõ 0.3 giây máy tự động search
    });

    // 5. TÍNH NĂNG ĐÓNG BẢNG: Khi click chuột nhầm ra ngoài khoảng trắng
    document.addEventListener("click", function (event) {
        // Nếu click không nằm trong thanh input và cũng không nằm trong cái bảng gợi ý
        if (!inputSearch.contains(event.target) && !suggestionsContainer.contains(event.target)) {
            suggestionsContainer.style.display = 'none';
        }
    });

    // TÍNH NĂNG MỞ BẢNG LẠI: Khi người dùng click lại vào thanh tìm kiếm và trong đó đang có sẵn chữ
    inputSearch.addEventListener("focus", function () {
        if (inputSearch.value.trim() !== "" && suggestionsContainer.innerHTML.trim() !== "") {
            suggestionsContainer.style.display = 'block';
        }
    });
}

// Hàm hỗ trợ vẽ HTML (Tách riêng hàm cho dễ quản lý)
function renderSuggestions(data) {
    if (!data || data.length === 0) {
        suggestionsContainer.innerHTML = `
            <div class="list-group-item text-center text-muted border-0">
                Không tìm thấy dữ liệu nào phù hợp.
            </div>
        `;
        suggestionsContainer.style.display = 'block';
        return;
    }

    // Hiển thị dải button - Bạn có thể đổi hình/icon tùy ý
    suggestionsContainer.innerHTML = data.map(t => `
        <button type="button" class="list-group-item list-group-item-action d-flex align-items-center border-0"
            onclick="selectSearchItem(${t.id}, '${t.keyword}')">
            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width:35px;height:35px">
                 <i class="bi bi-search text-muted"></i>
            </div>
            <span class="fw-semibold">${t.keyword}</span>
        </button>
    `).join("");

    suggestionsContainer.style.display = 'block';
}

// 6. TÍNH NĂNG CHỌN KẾT QUẢ (Xử lý khi người dùng Click vào gợi ý)
window.selectSearchItem = function (id, keyword) {
    // Phục hồi và điền tên vừa chọn lên thanh input cho đẹp
    inputSearch.value = keyword;

    // Đóng bảng gợi ý
    suggestionsContainer.style.display = 'none';

    // Tùy nhu cầu của bạn, VD tự động chuyển hướng sang trang tìm kiếm người ta vừa bấm vào:
    // window.location.href = `/search?q=` + encodeURIComponent(keyword);
}

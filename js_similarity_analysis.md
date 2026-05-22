# BÁO CÁO PHÂN TÍCH TRÙNG LẶP CẤU TRÚC CODE JAVASCRIPT (ĐỘ TRÙNG LẶP > 70%)

> Báo cáo này được thực hiện bằng cách quét và phân tích cú pháp (Lexical Tokenization) toàn bộ **26 tệp Javascript** trong thư mục `resources/js/modules`.
> Chúng tôi đã loại bỏ các callback và helper hàm siêu nhỏ (dưới 6 dòng hoặc dưới 100 ký tự) để tập trung vào các đoạn mã nghiệp vụ chính.

---

## 📊 Tóm tắt Thống kê

| Chỉ số                                               | Kết quả     | Ghi chú                                                                  |
| :----------------------------------------------------- | :------------ | :------------------------------------------------------------------------ |
| **Tổng số tệp đã quét**                    | **26**  | Tập trung trong `resources/js/modules`                                 |
| **Tổng số hàm nghiệp vụ đã nhận diện**  | **191** | Các hàm lớn, xử lý AJAX, Event Listener, DOM Manipulation            |
| **Tổng số cặp hàm trùng lặp $\ge$ 70%**  | **93**  | Đã được chuẩn hóa (loại bỏ khoảng trắng, comment, chuỗi text) |
| **Số cặp trùng lặp liên-tệp (Cross-file)** | **44**  | Sao chép logic giữa các tính năng khác nhau (Copy-Paste)            |
| **Số cặp trùng lặp nội-tệp (Same-file)**   | **49**  | Logic lặp lại trong cùng một tệp xử lý                             |

---

## 🔍 Phân tích các trường hợp trùng lặp điển hình

Sau đây là phân tích chi tiết của 4 trường hợp trùng lặp cấu trúc nghiêm trọng nhất, ảnh hưởng lớn đến khả năng bảo trì và có nguy cơ gây lỗi hệ thống.

### Trường hợp 1: Trùng lặp Tên Hàm Toàn Cục & Gây Ghi Đè (Nguy Cơ Cao ⚠️)

* **Hàm A:** `window.updateSTT` trong `createtopic.js` (Dòng 1-9)
* **Hàm B:** `window.updateSTT` trong `createuser.js` (Dòng 2-10)
* **Độ trùng lặp cấu trúc:** **100%**

```javascript
// Trong createtopic.js (Cập nhật số thứ tự dòng chủ đề)
window.updateSTT = function () {
    const rows = document.querySelectorAll("#topic-body tr:not(#create-row)");
    rows.forEach((tr, index) => {
        const sttCell = tr.querySelector(".stt");
        if (sttCell) {
            sttCell.innerText = index + 1;
        }
    });
};

// Trong createuser.js (Cập nhật số thứ tự dòng người dùng)
window.updateSTT = function () {
    const rows = document.querySelectorAll("#user-body tr.user-item");
    rows.forEach((tr, index) => {
        const sttCell = tr.querySelector(".text-center:first-child");
        if (sttCell) {
            sttCell.innerText = index + 1;
        }
    });
};
```

> **Nguy cơ lỗi nghiêm trọng:** Cả hai hàm đều được gán vào biến toàn cục `window.updateSTT`.
> Tệp nào được tải sau cùng sẽ **ghi đè hoàn toàn** hàm trước đó. Nếu trang Admin tải cả hai tệp, việc cập nhật số thứ tự của một trong hai bảng (Topic hoặc User) sẽ bị lỗi hoặc chạy sai mục tiêu DOM!

---

### Trường hợp 2: Trùng lặp Tải Chi Tiết Danh Sách bằng AJAX Modal (Độ trùng lặp 92.1% 🔄)

* **Hàm A:** Event Listener cho `.open-follow` trong `follow.js` (Dòng 50-78)
* **Hàm B:** Event Listener cho `.open-like` trong `like.js` (Dòng 39-66)

```javascript
// follow.js (Tải danh sách follower/following bằng AJAX)
document.addEventListener("click", function (e) {
    if (window.Fancybox && Fancybox.getInstance()) return;
    const btn = e.target.closest(".open-follow");
    if (!btn) return;
    const userId = btn.dataset.id;
    const type = btn.dataset.type;
    if (window.matchMedia("(max-width: 992px)").matches) {
        window.location.href = `/follows/detail/${userId}`;
        return;
    }
    const action = btn.dataset.action;
    startLoading();
    fetch(`/follows/detail/${userId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-type': type,
        }
    })
        .then(res => res.text())
        .then(html => {
            document.getElementById("InteractionListContent").innerHTML = html;
            const modalEl = document.getElementById("InteractionListModal");
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        })
        .finally(() => {
            finishLoading();
        });
});

// like.js (Tải danh sách người thích bài viết bằng AJAX)
document.addEventListener("click", function (e) {
    if (window.Fancybox && Fancybox.getInstance()) return;
    const btn = e.target.closest(".open-like");
    if (!btn) return;
    const postID = btn.dataset.postId;
    if (window.matchMedia("(max-width: 992px)").matches) {
        window.location.href = `/posts/like_list/${postID}`;
        return;
    }
    const action = btn.dataset.action;
    startLoading();
    fetch(`/posts/like_list/${postID}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
        .then(res => res.text())
        .then(html => {
            document.getElementById("InteractionListContent").innerHTML = html;
            const modalEl = document.getElementById("InteractionListModal");
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        })
        .finally(() => {
            finishLoading();
        });
});
```

> **Nhận xét:** Cả hai khối lệnh đều thực hiện cùng một cấu trúc nghiệp vụ:
>
> 1. Kiểm tra môi trường di động để chuyển hướng.
> 2. Gọi AJAX tải HTML fragment từ server.
> 3. Hiển thị trong cùng một vùng chứa `#InteractionListContent` và bật Modal `#InteractionListModal`.
>    Việc gộp nhóm này giúp giảm 40+ dòng mã trùng lặp lặp đi lặp lại.

---

### Trường hợp 3: Khởi tạo lại tính năng Post (Video Observer & Fancybox) (Độ trùng lặp 93.8% 🛠️)

* **Hàm A:** `reinitPostFeatures(postEl)` trong `editpost.js` (Dòng 253-301)
* **Hàm B:** DOMContentLoaded Callback trong `home.js` (Dòng 23-80)

```javascript
// Trong editpost.js
function reinitPostFeatures(postEl) {
    postEl.querySelectorAll('.video-link').forEach(link => {
        const video = document.createElement('video');
        video.src = link.href + "#t=0.5";
        // ... (Sinh thumbnail base64 giống hệt home.js)
    });

    const videoObserver = new IntersectionObserver((entries) => {
        // ... (Quan sát video cuộn tự động phát)
    }, { root: null, threshold: 0.6 });

    postEl.querySelectorAll('.feed-video').forEach(v => {
        videoObserver.observe(v);
    });

    if (window.Fancybox) {
        // ... (Bind Fancybox ảnh/video)
    }
}
```

> **Lý do trùng lặp:** Khi một bài viết được chỉnh sửa qua AJAX, HTML của nó được render lại từ backend và chèn vào DOM.
> Do đó, các sự kiện Fancybox và IntersectionObserver (tự động phát video khi cuộn) bị mất đi và cần được khởi tạo lại.
> Hiện tại, logic khởi tạo này đang bị sao chép nguyên bản từ `home.js` sang `editpost.js`.

---

### Trường hợp 4: Khởi tạo Trạng thái các Tab chuyển đổi (Độ trùng lặp 92.6% 📑)

* **Hàm A:** DOMContentLoaded của tab Profile trong `detailprofile.js` (Dòng 105-122)
* **Hàm B:** DOMContentLoaded của tab Tìm kiếm trong `search.js` (Dòng 140-156)
* **Hàm C:** Khởi tạo Tab Báo cáo trong `report.js` (Dòng 3-27)

**Cấu trúc chung của 3 khối lệnh:**

1. Lấy tham số tab hiện tại từ URL (`URLSearchParams`) hoặc `sessionStorage`.
2. Xác định class/id của nút chuyển tab tương ứng.
3. Kích hoạt trạng thái giao diện bằng cách thêm class `.active` hoặc `.active-tab`.
4. Gọi hàm AJAX để tải nội dung tab mới từ backend.
5. Cập nhật `window.history.replaceState` để lưu lịch sử điều hướng của trình duyệt.

---

## 📐 Đề xuất kiến trúc tái cấu trúc (Refactoring Plan)

Để giải quyết vấn đề lặp code và tránh các lỗi ghi đè biến toàn cục nguy hiểm, chúng tôi đề xuất xây dựng một hệ thống Helper/Utility dùng chung theo mô hình sau:

### 1. Tạo tệp tiện ích dùng chung `utils.js`

Tạo một namespace duy nhất để bảo vệ phạm vi toàn cục:

```javascript
// resources/js/modules/utils.js
window.appUtils = {
    // Giải quyết Trường hợp 1 (Sửa lỗi ghi đè updateSTT)
    updateTableSTT(tableBodySelector, cellSelector = ".stt") {
        const rows = document.querySelectorAll(`${tableBodySelector} tr`);
        rows.forEach((tr, index) => {
            const cell = tr.querySelector(cellSelector);
            if (cell) cell.innerText = index + 1;
        });
    },

    // Giải quyết Trường hợp 2 (Gộp logic AJAX modal danh sách)
    loadRemoteModal(triggerEl, url, typeHeader = null) {
        if (window.matchMedia("(max-width: 992px)").matches) {
            window.location.href = url;
            return;
        }
      
        if (typeof startLoading === 'function') startLoading();
      
        const headers = { 'X-Requested-With': 'XMLHttpRequest' };
        if (typeHeader) headers['X-type'] = typeHeader;

        fetch(url, { headers })
            .then(res => res.text())
            .then(html => {
                const contentEl = document.getElementById("InteractionListContent");
                const modalEl = document.getElementById("InteractionListModal");
                if (contentEl && modalEl) {
                    contentEl.innerHTML = html;
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
            })
            .catch(err => console.error("Lỗi AJAX Modal:", err))
            .finally(() => {
                if (typeof finishLoading === 'function') finishLoading();
            });
    },

    // Giải quyết Trường hợp 3 (Gộp logic khởi tạo lại bài viết)
    initPostFeatures(containerEl = document) {
        // Khởi tạo thumbnail video
        containerEl.querySelectorAll('.video-link').forEach(link => {
            if (link.getAttribute('data-thumb')) return; // Đã init rồi thì bỏ qua
            const video = document.createElement('video');
            video.src = link.href + "#t=0.5";
            video.crossOrigin = "anonymous";
            video.muted = true;
            video.addEventListener('loadeddata', () => {
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                link.setAttribute('data-thumb', canvas.toDataURL('image/jpeg'));
            });
        });

        // IntersectionObserver tự phát video khi cuộn
        const videoObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const video = entry.target;
                if (entry.isIntersecting) {
                    video.play().catch(() => { });
                } else {
                    video.pause();
                    video.currentTime = 0;
                }
            });
        }, { root: null, threshold: 0.6 });

        containerEl.querySelectorAll('.feed-video').forEach(v => {
            videoObserver.observe(v);
        });

        // Bind Fancybox
        if (window.Fancybox) {
            Fancybox.bind("[data-fancybox^='gallery-']", {
                Hash: false,
                autoFocus: false,
                Compact: false,
                Animated: true,
                Thumbs: { autoStart: true },
                Html: { video: { autoplay: true, controls: true, format: "mp4" } }
            });
        }
    }
};
```

---

### 2. Lợi ích sau khi tái cấu trúc

1. **Dọn sạch mã nguồn:** Giảm ít nhất **150 - 200 dòng mã trùng lặp** trong các tệp module.
2. **Khử triệt để lỗi ghi đè:** Loại bỏ các hàm toàn cục trùng tên như `window.updateSTT` giúp hệ thống hoạt động ổn định và chính xác trên mọi trang.
3. **Tập trung hóa bảo trì:** Khi cần cập nhật cấu hình Fancybox hoặc tối ưu IntersectionObserver cho video, bạn chỉ cần sửa đổi tại một nơi duy nhất (`utils.js`) thay vì phải chỉnh sửa cả `home.js`, `editpost.js`, và các tệp liên quan.

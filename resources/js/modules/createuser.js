// Function to update order numbers (STT)
window.updateSTT = function () {
    const rows = document.querySelectorAll("#user-body tr.user-item");
    rows.forEach((tr, index) => {
        const sttCell = tr.querySelector(".text-center:first-child");
        if (sttCell) {
            sttCell.innerText = index + 1;
        }
    });
};
// Handle "Thêm mới" button click for Users
const btnShowCreateUser = document.getElementById("btn-show-create-user");
if (btnShowCreateUser) {
    btnShowCreateUser.addEventListener("click", function () {
        startLoading();
        fetch("/admin/users/create")
            .then(res => res.text())
            .then(html => {
                const container = document.getElementById("create-container");
                if (container) {
                    container.innerHTML = html;
                    btnShowCreateUser.classList.add("d-none");
                }
            })
            .catch(err => console.error(err))
            .finally(() => finishLoading());
    });
}

// Handle Cancel button inside the form
document.addEventListener("click", function (e) {
    if (e.target && e.target.id === "btn-cancel-user") {
        document.getElementById("create-container").innerHTML = "";
        if (btnShowCreateUser) {
            btnShowCreateUser.classList.remove("d-none");
        }
    }
});

// Handle Avatar Preview
document.addEventListener("change", function (e) {
    if (e.target && e.target.id === "avatarInput") {
        const input = e.target;
        const previewWrap = document.getElementById("avatarPreview");
        
        if (previewWrap && input.files && input.files[0]) {
            const previewImg = previewWrap.querySelector("img");
            const reader = new FileReader();
            reader.onload = function (event) {
                previewImg.src = event.target.result;
                previewWrap.classList.remove("d-none");
            };
            reader.readAsDataURL(input.files[0]);
        } else if (previewWrap) {
            previewWrap.classList.add("d-none");
        }
    }
});

// Handle form submission
document.addEventListener("submit", function (e) {
    if (e.target && e.target.id === "userForm") {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const submitBtn = form.querySelector(".btn-create-user");

        submitBtn.disabled = true;
        startLoading();

        fetch("/admin/users/store", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                "Accept": "application/json"
            },
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const user = data.data;
                    const profile = user.profile || {};
                    
                    // Format avatar HTML
                    const avatarHtml = profile.avatar 
                        ? `<a href="/storage/${profile.avatar}" data-fancybox="gallery-${profile.avatar}">
                                <img src="/storage/${profile.avatar}" class="img-thumbnail" style="width:200px;height:100px;object-fit:cover">
                          </a>`
                        : `<span class="text-muted">Không có</span>`;

                    // Shorten bio
                    const bioText = profile.bio || "không có";
                    const bio = bioText.length > 20 ? bioText.substring(0, 20) + "..." : bioText;

                    const newRow = `
                    <tr class="user-item">
                        <td class="text-center"></td>
                        <td class="text-start">${user.name}</td>
                        <td class="text-start">${user.email}</td>
                        <td class="text-start">${profile.display_name || 'Không có'}</td>
                        <td class="text-center">${avatarHtml}</td>
                        <td class="text-start">${bio}</td>
                        <td class="text-center">
                            <button class="open-follow" data-type="follower" data-id="${user.id}">
                                <a class="follow-count" data-authid="${user.id}">0</a>
                            </button>
                        </td>
                        <td class="text-center">
                            <button class="open-follow" data-type="following" data-id="${user.id}">
                                <a class="following-count" data-authid="${user.id}">0</a>
                            </button>
                        </td>
                        <td class="text-center">${user.role}</td>
                        <td class="text-center">
                            <a class="btn btn-info btn-sm" href="/profile/detail/${user.id}">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a class="btn btn-warning btn-sm" href="/profile/edit/${user.id}">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a class="btn btn-danger btn-sm btn-delete-user" data-id="${user.id}" data-type="destroy">
                                <i class="bi bi-trash"></i>
                            </a>
                            <a class="btn btn-danger btn-sm btn-delete-user" data-id="${user.id}" data-type="hide">
                                <i class="bi bi-eye-slash"></i>
                            </a>
                        </td>
                    </tr>
                    `;

                    document.getElementById("user-body").insertAdjacentHTML("afterbegin", newRow);
                    document.querySelector(".count-user").innerText = `Tổng: ${data.count}`;
                    updateSTT();

                    // Clear and hide form
                    document.getElementById("create-container").innerHTML = "";
                    if (btnShowCreateUser) {
                        btnShowCreateUser.classList.remove("d-none");
                    }

                    if (window.Swal) {
                        Swal.fire('Thành công', 'Đã thêm người dùng và hồ sơ mới', 'success');
                    }
                } else {
                    alert(data.message || "Có lỗi xảy ra");
                }
            })
            .catch(err => {
                console.error(err);
                alert("Lỗi kết nối hoặc dữ liệu không hợp lệ");
            })
            .finally(() => {
                submitBtn.disabled = false;
                finishLoading();
            });
    }
});

window.updateSTT = function () {
    const rows = document.querySelectorAll("#topic-body tr:not(#create-row)");
    rows.forEach((tr, index) => {
        const sttCell = tr.querySelector(".stt");
        if (sttCell) {
            sttCell.innerText = index + 1;
        }
    });
};
document.addEventListener("submit", function (e) {
    if (e.target && e.target.id === "topicForm") {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        startLoading();
        fetch("/topics/store", {
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
                    const newRow = `
                    <tr>
                       <td class="text-center stt">

                    </td>
                       <td class="fw-semibold">
                       ${data.data.name}
                       </td>
                       <td class="text-center">
                        <a href="/admin/topics/edit/${data.data.id}"
                           class="btn btn-warning btn-sm">
                           <i class="bi bi-pencil"></i>
                        </a>
                        <form class="d-inline">
                            <button class="btn btn-danger btn-sm btn-delete-topic" data-id=${data.data.id}>
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                    </tr>
                `;
                    document.getElementById("topic-body")
                        .insertAdjacentHTML("beforeend", newRow);
                    document.querySelector(".count-topic").innerText =
                        `Tổng chủ đề: ${data.count}`;
                    updateSTT()

                    form.reset();
                }
            })
            .catch(err => console.error(err))
            .finally(() => {
                finishLoading();
            });
    }
});
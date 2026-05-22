
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
                    <tr class="topics-item">
                       <td class="text-center stt">

                    </td>
                       <td class="fw-semibold">
                       ${data.data.name}
                       </td>
                       <td class="text-center">
                        <a href="/topics/edit/${data.data.id}"
                           class="btn btn-warning btn-sm">
                           <i class="bi bi-pencil"></i>
                        </a>
                        <form class="d-inline">
                            <button class="btn btn-danger btn-sm btn-delete" data-target='topics' data-id=${data.data.id}>
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                    </tr>
                `;
                    document.getElementById("topic-body")
                        .insertAdjacentHTML("beforeend", newRow);
                    const countEl = document.querySelector(".count-topics");
                    if (countEl) {
                        countEl.innerText = `Tổng: ${data.count}`;
                    }
                    updateSTT('topic');

                    form.reset();
                }
            })
            .catch(err => console.error(err))
            .finally(() => {
                finishLoading();
            });
    }
});
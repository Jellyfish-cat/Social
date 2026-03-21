
const btnCreate = document.getElementById("btn-show-create");
if (btnCreate) {
    btnCreate.addEventListener("click", function () {
    startLoading();
    fetch("/topics/create")
        .then(res => res.text())
        .then(html => {
            document.getElementById("create-container").innerHTML = html;
        })
        .catch(err => console.error(err))
            .finally(() => {
        finishLoading();
    });
       document.getElementById("btn-show-create").classList.add("d-none");
});
}
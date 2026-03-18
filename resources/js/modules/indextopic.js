document.getElementById("btn-show-create").addEventListener("click", function () {
    fetch("/topics/create")
        .then(res => res.text())
        .then(html => {
            document.getElementById("create-container").innerHTML = html;
        })
        .catch(err => console.error(err));
       document.getElementById("btn-show-create").classList.add("d-none");
});
 

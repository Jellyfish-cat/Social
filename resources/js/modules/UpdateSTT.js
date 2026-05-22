window.updateSTT = function (target) {
    const rows = document.querySelectorAll("#" + target + "-body tr." + target + "s-item");
    rows.forEach((tr, index) => {
        const sttCell = tr.querySelector(".stt");
        if (sttCell) {
            sttCell.innerText = index + 1;
        }
    });
};
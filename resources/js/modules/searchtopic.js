window.initTopicFeatures = function() {
    const input = document.getElementById("topic-input");
    const suggestions = document.getElementById("topic-suggestions");
    const selectedBox = document.getElementById("selected-topics");
    const hiddenIds = document.getElementById("topic-ids");
    const hiddenNew = document.getElementById("new-topics");

    if (!input || !suggestions) return;

    // Reset state for new form
    let selectedTopics = []; 
    let newTopics = []; 
    let suggestedTopics = []; 

    // Nếu có dữ liệu khởi tạo (dành cho trang Edit)
    if (selectedBox && selectedBox.dataset.initial) {
        try {
            const initial = JSON.parse(selectedBox.dataset.initial);
            selectedTopics = initial;
            renderSelectedLocal();
        } catch (e) { console.error(e); }
    }

    input.addEventListener("input", async () => {
        let q = input.value.trim().toLowerCase();
        if (!q) {
            suggestions.innerHTML = "";
            return;
        }
        let res = await fetch(`/topics/search?q=${q}`);
        let data = await res.json();
        suggestedTopics = data;
        suggestions.innerHTML = data.map(t => `
            <button type="button" class="list-group-item list-group-item-action"
                onclick="window.selectTopicLocal(${t.id}, '${t.name}')">
                ${t.name}
            </button>
        `).join("");
    });

    window.selectTopicLocal = function (id, name) {
        if (selectedTopics.length >= 3) {
            alert("Chỉ chọn tối đa 3 chủ đề");
            return;
        }
        if (selectedTopics.find(t => t.id === id)) return;
        selectedTopics.push({ id, name });
        renderSelectedLocal();
        resetInputLocal();
    }

    const btn = document.getElementById("add-topic-btn");
    if (btn) {
        btn.onclick = function () {
            let name = input.value.trim();
            if (!name) return;
            let lowerName = name.toLowerCase();
            let existed = suggestedTopics.find(t => t.name.toLowerCase() === lowerName);
            if (existed) {
                alert("Chủ đề đã tồn tại, hãy chọn từ gợi ý");
                return;
            }
            if (selectedTopics.find(t => t.name.toLowerCase() === lowerName)) {
                alert("Bạn đã chọn chủ đề này");
                return;
            }
            if (newTopics.includes(lowerName)) {
                alert("Chủ đề này đã được thêm");
                return;
            }
            if (selectedTopics.length >= 3) {
                alert("Chỉ tối đa 3 chủ đề");
                return;
            }
            newTopics.push(lowerName);
            selectedTopics.push({ id: null, name });
            renderSelectedLocal();
            resetInputLocal();
        }
    }

    function renderSelectedLocal() {
        if (!selectedBox) return;
        selectedBox.innerHTML = selectedTopics.map((t, index) => `
            <span class="badge bg-primary me-1">
                ${t.name}
                <span onclick="window.removeTopicLocal(${index})" style="cursor:pointer;">×</span>
            </span>
        `).join("");

        if (hiddenIds) {
            hiddenIds.value = selectedTopics
                .filter(t => t.id)
                .map(t => t.id)
                .join(",");
        }

        if (hiddenNew) {
            hiddenNew.value = newTopics.join(",");
        }
    }

    window.removeTopicLocal = function (index) {
        let removed = selectedTopics[index];
        if (!removed.id) {
            newTopics = newTopics.filter(n => n !== removed.name.toLowerCase());
        }
        selectedTopics.splice(index, 1);
        renderSelectedLocal();
    }

    function resetInputLocal() {
        input.value = "";
        suggestions.innerHTML = "";
    }
};

document.addEventListener("DOMContentLoaded", () => {
    window.initTopicFeatures();
});

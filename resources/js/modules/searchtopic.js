let selectedTopics = []; // {id, name}
let newTopics = []; // chỉ name
let suggestedTopics = []; // cache search
const input = document.getElementById("topic-input");
const suggestions = document.getElementById("topic-suggestions");
if (input) {
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
            onclick="selectTopic(${t.id}, '${t.name}')">
            ${t.name}
        </button>
    `).join("");
    });
}

window.selectTopic = function (id, name) {
    if (selectedTopics.length >= 3) {
        alert("Chỉ chọn tối đa 3 chủ đề");
        return;
    }
    if (selectedTopics.find(t => t.id === id)) return;
    selectedTopics.push({ id, name });
    renderSelected();
    resetInput();
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
    renderSelected();
    resetInput();
}
}

const selectedBox = document.getElementById("selected-topics");
const hiddenIds = document.getElementById("topic-ids");
const hiddenNew = document.getElementById("new-topics");

window.renderSelected = function () {
    selectedBox.innerHTML = selectedTopics.map((t, index) => `
        <span class="badge bg-primary me-1">
            ${t.name}
            <span onclick="removeTopic(${index})" style="cursor:pointer;">×</span>
        </span>
    `).join("");

    // topic có sẵn
    hiddenIds.value = selectedTopics
        .filter(t => t.id)
        .map(t => t.id)
        .join(",");

    // topic mới
    hiddenNew.value = newTopics.join(",");
}

window.removeTopic = function (index) {
    let removed = selectedTopics[index];
    if (!removed.id) {
        newTopics = newTopics.filter(n => n !== removed.name.toLowerCase());
    }
    selectedTopics.splice(index, 1);
    renderSelected();
}

window.resetInput = function () {
    input.value = "";
    suggestions.innerHTML = "";
}
window.history.replaceState({
    isTopicList: true,
    listUrl: window.location.href
}, "",
    window.location.href);

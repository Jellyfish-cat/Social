window.initCurrentTab = function ({
    containerSelector,
    storageKey,
    defaultTab,
    tabSelectors,
    setCurrentTab,
    setActiveTab,
    loadTab,
    beforeInit,
}) {
    window.addEventListener("DOMContentLoaded", () => {
        const container = document.querySelector(containerSelector);
        if (!container) return;

        if (typeof beforeInit === "function") {
            beforeInit(container);
        }
        const urlParams = new URLSearchParams(window.location.search);
        const requestedTab =
            urlParams.get("tab") ||
            sessionStorage.getItem(storageKey) ||
            defaultTab;
        const currentType = tabSelectors[requestedTab] ? requestedTab : defaultTab;
        const tabBtn = document.querySelector(tabSelectors[currentType]);
        if (typeof setCurrentTab === "function") {
            setCurrentTab(currentType);
        }
        if (tabBtn && typeof setActiveTab === "function") {
            setActiveTab(tabBtn);
        }
        if (typeof loadTab === "function") {
            loadTab(currentType);
        }
        const url = new URL(window.location);
        url.searchParams.set("tab", currentType);
        window.history.replaceState({ tab: currentType }, "", url);
    });
};
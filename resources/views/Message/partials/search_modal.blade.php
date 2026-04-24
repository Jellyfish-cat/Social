<!-- Search Panel -->
<div id="searchPanel" 
     class="position-absolute top-0 end-0 h-100 bg-white shadow-lg"
     style="width: 330px; transform: translateX(105%); visibility: hidden; transition: transform 0.3s ease, visibility 0.3s; z-index: 1060; border-left: 1px solid #efefef; display: flex; flex-direction: column;">

    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center flex-shrink-0">
        <h5 class="mb-0 fw-bold">Tìm kiếm tin nhắn</h5>
        <button class="btn btn-sm btn-light rounded-circle" onclick="window.closeSearchPanel()" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="bi bi-x-lg"></i></button>
    </div>

    <!-- Search Input Area -->
    <div class="p-3 border-bottom flex-shrink-0">
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3"><i class="bi bi-search text-muted"></i></span>
            <input type="text" id="msgSearchInput" class="form-control bg-light border-start-0 rounded-end-pill shadow-none" placeholder="Tìm kiếm..." autocomplete="off">
        </div>
    </div>

    <!-- Search Results -->
    <div id="msgSearchResults" class="flex-grow-1 p-2" style="overflow-y: auto; overflow-x: hidden;">
        <div class="text-center text-muted mt-5" id="msgSearchEmptyState">
            <i class="bi bi-search" style="font-size: 3rem; opacity: 0.5;"></i>
            <p class="mt-3 small">Tìm kiếm tin nhắn trong hội thoại này</p>
        </div>
        <div class="text-center text-muted mt-5 d-none" id="msgSearchLoading">
            <div class="spinner-border spinner-border-sm" role="status"></div>
            <p class="mt-2 small">Đang tìm kiếm...</p>
        </div>
        <div class="d-flex flex-column gap-2" id="msgSearchList">
            <!-- Results will be injected here via JS -->
        </div>
    </div>
</div>

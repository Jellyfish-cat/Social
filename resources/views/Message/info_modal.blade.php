<!-- Overlay -->
<div id="infoOverlay" class="position-absolute top-0 start-0 w-100 h-100 d-none" 
     style="background: rgba(0,0,0,0.1); z-index: 1040;"></div>

<!-- Info Panel -->
<div id="infoPanel" 
     class="position-absolute top-0 end-0 h-100 bg-white shadow-lg"
     style="width: 330px; transform: translateX(105%); visibility: hidden; transition: transform 0.3s ease, visibility 0.3s; z-index: 1050; border-left: 1px solid #efefef; display: flex; flex-direction: column;">

    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center flex-shrink-0">
        <h5 class="mb-0 fw-bold">Thông tin chi tiết</h5>
        <button class="btn btn-sm btn-light rounded-circle" onclick="window.closeInfoPanel()" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="bi bi-x-lg"></i></button>
    </div>

    <div id="infoContent" class="flex-grow-1" style="overflow-y: auto; overflow-x: hidden;">
        <!-- Avatar & Name -->
        <div class="text-center py-4 border-bottom">
            <img id="infoAvatar" src="{{ asset('storage/default-avatar.png') }}" 
                 class="rounded-circle shadow-sm mx-auto d-block" 
                 style="width: 100px; height: 100px; object-fit: cover;">
            <h4 class="mt-3 mb-1 fw-bold" id="infoName">Tên hiển thị</h4>
            <div class="text-muted small" id="infoStatus">Trạng thái</div>
        </div>

        <!-- Actions -->
        <div class="p-3 border-bottom d-flex justify-content-center gap-4">
            <a href="javascript:void(0)" id="btnInfoProfile" class="text-center cursor-pointer hover-bg-light p-2 rounded-3 text-decoration-none text-dark">
                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-1" style="width: 40px; height: 40px;">
                    <i class="bi bi-person fs-5"></i>
                </div>
                <small class="fw-semibold">Trang cá nhân</small>
            </a>
            <div class="text-center cursor-pointer hover-bg-light p-2 rounded-3" id="btnInfoSearch">
                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-1" style="width: 40px; height: 40px;">
                    <i class="bi bi-search fs-5"></i>
                </div>
                <small class="fw-semibold">Tìm kiếm</small>
            </div>
            <div class="text-center cursor-pointer hover-bg-light p-2 rounded-3" id="btnInfoMute">
                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-1" style="width: 40px; height: 40px;">
                    <i class="bi bi-bell-slash fs-5"></i>
                </div>
                <small class="fw-semibold">Tắt thông báo</small>
            </div>
        </div>

        <!-- Group Specific Section (Hidden by default) -->
        <div id="infoGroupSection" class="p-3 border-bottom d-none">
            <h6 class="fw-bold mb-3 text-muted" style="font-size: 0.85rem; text-transform: uppercase;">Thành viên nhóm</h6>
            <div id="infoGroupMembers" class="d-flex flex-column gap-2">
                <!-- Group members will be loaded here -->
            </div>
        </div>

        <!-- Settings & Privacy -->
        <div class="p-3">
            <h6 class="fw-bold mb-3 text-muted" style="font-size: 0.85rem; text-transform: uppercase;">Quyền riêng tư & Hỗ trợ</h6>
            
            <div class="list-group list-group-flush rounded-3 border">
                <!-- Options for Group -->
                <button class="list-group-item list-group-item-action d-flex align-items-center py-3 d-none" id="btnEditGroup">
                    <i class="bi bi-pencil me-3 fs-5"></i>
                    <span>Đổi tên nhóm</span>
                </button>
                <button class="list-group-item list-group-item-action d-flex align-items-center py-3 text-danger d-none" id="btnLeaveGroup">
                    <i class="bi bi-box-arrow-right me-3 fs-5"></i>
                    <span>Rời khỏi nhóm</span>
                </button>

                <!-- Options for Private -->
                <button class="list-group-item list-group-item-action d-flex align-items-center py-3 text-danger d-none" id="btnBlockUser">
                    <i class="bi bi-person-x me-3 fs-5"></i>
                    <span>Chặn người dùng</span>
                </button>
                <button class="list-group-item list-group-item-action d-flex align-items-center py-3 text-danger d-none" id="btnReportUser">
                    <i class="bi bi-flag me-3 fs-5"></i>
                    <span>Báo cáo</span>
                </button>

                <!-- Common -->
                <button class="list-group-item list-group-item-action d-flex align-items-center py-3 text-danger" id="btnDeleteConversation">
                    <i class="bi bi-trash3 me-3 fs-5"></i>
                    <span>Xóa đoạn chat</span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    #infoPanel .cursor-pointer { cursor: pointer; }
    #infoPanel .hover-bg-light:hover { background-color: #f8f9fa; }
</style>

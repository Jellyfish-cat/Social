<div class="modal-header border-bottom px-4 mt-2">
    <h5 class="modal-title fw-bold" id="reportModalLabel">{{ __('Báo cáo vi phạm') }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body p-4">
    <form id="reportForm">
        @csrf
        <input type="hidden" name="target_id" id="report_target_id" value="{{ $target_id ?? '' }}">
        <input type="hidden" name="target_type" id="report_target_type" value="{{ $target_type ?? 'post' }}">
        
        <div class="mb-3">
            <label for="report_category" class="form-label text-muted">{{ __('Danh mục vi phạm') }}</label>
            <select class="form-select mb-3" id="report_category" name="category" required>
                <option value="">{{ __('-- Chọn lý do --') }}</option>
                <option value="Spam">{{ __('Nội dung rác (Spam)') }}</option>
                <option value="Violence">{{ __('Tính bạo lực / Kích động') }}</option>
                <option value="Nudity">{{ __('Ảnh khỏa thân hoặc khiêu dâm') }}</option>
                <option value="Harassment">{{ __('Quấy rối hoặc bắt nạt') }}</option>
                <option value="FakeNews">{{ __('Thông tin sai sự thật') }}</option>
                <option value="Other">{{ __('Khác...') }}</option>
            </select>
            
            <div id="report_reason_container" style="display: none;">
                <label for="report_reason" class="form-label text-muted">{{ __('Tại sao bạn báo cáo nội dung này?') }}</label>
                <textarea class="form-control" id="report_reason" name="reason" rows="3" placeholder="{{ __('Vui lòng cung cấp thêm thông tin chi tiết...') }}"></textarea>
            </div>

            @if(($target_type ?? '') === 'message')
                <div class="mt-3 p-3 bg-light rounded-3 border">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="report_allow_view" name="allow_view" value="1">
                        <label class="form-check-label fw-semibold text-primary" for="report_allow_view">
                            {{ __('Cho phép quản trị viên xem xét hội thoại này') }}
                        </label>
                    </div>
                    <small class="text-muted d-block mt-1">
                        <i class="bi bi-info-circle me-1"></i>
                        {{ __('Bật tùy chọn này để Admin có thể xem ngữ cảnh các tin nhắn xung quanh nhằm xử lý báo cáo chính xác hơn.') }}
                    </small>
                </div>
            @endif
        </div>
    </form>
</div>
<div class="modal-footer px-4 pb-4">
    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">{{ __('Hủy') }}</button>
    <button type="button" class="btn btn-danger rounded-pill px-4" id="submitReportBtn">
        <span class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true" id="reportSpinner"></span>
        {{ __('Gửi báo cáo') }}
    </button>
</div>

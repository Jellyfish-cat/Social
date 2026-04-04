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
            <label for="report_reason" class="form-label text-muted">{{ __('Tại sao bạn báo cáo nội dung này?') }}</label>
            <textarea class="form-control" id="report_reason" name="reason" rows="4" required placeholder="{{ __('Vui lòng cung cấp thêm thông tin chi tiết...') }}"></textarea>
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

<dialog class="admin-dialog" data-confirm-dialog aria-labelledby="admin-confirm-title" aria-describedby="admin-confirm-message">
    <div class="admin-dialog-card">
        <div class="admin-dialog-heading"><h2 id="admin-confirm-title">Xác nhận thao tác</h2><button class="admin-icon-button" type="button" data-dialog-close aria-label="Đóng">@include('partials.icon', ['name' => 'close'])</button></div>
        <p id="admin-confirm-message"></p>
        <div class="admin-actions"><button class="admin-button secondary" type="button" data-dialog-close>Hủy</button><button class="admin-button" type="button" data-confirm-accept>Xác nhận</button></div>
    </div>
</dialog>
<dialog class="admin-image-dialog" data-image-dialog aria-labelledby="admin-image-title">
    <div class="admin-image-header"><h2 id="admin-image-title"></h2><button class="admin-icon-button" type="button" data-dialog-close aria-label="Đóng ảnh">@include('partials.icon', ['name' => 'close'])</button></div>
    <div class="admin-image-viewport"><img alt="" data-image-full></div>
    <div class="admin-image-footer"><p data-image-details></p><div class="admin-image-controls"><button class="admin-icon-button" type="button" data-image-zoom-out aria-label="Thu nhỏ">@include('partials.icon', ['name' => 'zoom-out'])</button><output data-image-zoom-label>100%</output><button class="admin-icon-button" type="button" data-image-zoom-in aria-label="Phóng to">@include('partials.icon', ['name' => 'zoom-in'])</button><a data-image-original target="_blank" rel="noopener">Mở ảnh gốc @include('partials.icon', ['name' => 'arrow-up-right'])</a></div></div>
</dialog>

@extends('admin.layout')
@section('title', 'Tác phẩm makeup — Quản trị')
@section('page_heading', 'Tác phẩm makeup')
@section('content')
<div class="admin-page-intro"><p>Quản lý bộ sưu tập trong phần 05 / THE ART OF MAKEUP.</p><a class="admin-button secondary" href="{{ route('admin.portfolio.categories') }}">Quản lý danh mục</a></div>
<div class="admin-stats"><div><span>Tổng tác phẩm</span><strong>{{ $counts['total'] }}</strong></div><div><span>Đang hiển thị</span><strong>{{ $counts['visible'] }}</strong></div><div><span>Đang ẩn</span><strong>{{ $counts['hidden'] }}</strong></div></div>
<details class="admin-upload-panel" @if($errors->hasAny(['images', 'items', 'title', 'category']) || $errors->any()) open @endif>
    <summary><span class="admin-summary-title">@include('partials.icon', ['name' => 'upload']) Thêm tác phẩm mới <small>Tải tối đa 20 ảnh mỗi lần</small></span>@include('partials.icon', ['name' => 'plus'])</summary>
    <form class="admin-form" method="POST" action="{{ route('admin.portfolio.store', $filters) }}" enctype="multipart/form-data" data-upload-form>
        @csrf
        <div class="admin-form-grid" data-upload-defaults>
            <div class="admin-field"><label for="title">Tên chung / tiền tố tác phẩm *</label><input id="title" name="title" value="{{ old('title') }}" required maxlength="255" placeholder="Ví dụ: Bridal Collection"></div>
            <div class="admin-field"><label for="category">Danh mục mặc định *</label><select id="category" name="category" required><option value="">Chọn danh mục</option>@foreach($categories->where('is_active', true) as $category)<option value="{{ $category->slug }}" @selected(old('category') === $category->slug)>{{ $category->name }}</option>@endforeach</select></div>
            <div class="admin-field"><label for="display_order">Thứ tự bắt đầu</label><input type="number" min="0" max="10000" id="display_order" name="display_order" value="{{ old('display_order', 0) }}" required></div>
            <div class="admin-field"><label for="alt_text">Mô tả mặc định</label><input id="alt_text" name="alt_text" maxlength="255" value="{{ old('alt_text') }}" placeholder="Mô tả nội dung hình ảnh"></div>
            <label class="admin-check"><input type="hidden" name="is_featured" value="0"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', true))> Hiển thị trên website</label>
        </div>
        <div class="admin-upload-drop"><label for="images">@include('partials.icon', ['name' => 'image'])<strong>Chọn hình ảnh để tải lên</strong><span>JPG, PNG, WebP · Tối đa 8 MB/ảnh · Tối đa 20 ảnh</span></label><input type="file" id="images" name="images[]" accept="image/jpeg,image/png,image/webp" multiple required data-upload-input></div>
        <p class="admin-field-help">Sau khi chọn ảnh, bạn có thể sửa thông tin riêng của từng ảnh bên dưới. Nếu gửi thất bại, hãy chọn lại tệp; các thông tin đã nhập sẽ được giữ lại.</p>
        <p class="admin-inline-error" role="alert" data-upload-error hidden></p>
        <div class="admin-upload-heading" data-upload-heading hidden><h3>Thông tin từng ảnh <span data-upload-count></span></h3><button type="button" class="admin-button secondary" data-apply-defaults>Áp dụng thông tin chung</button></div>
        <div class="admin-upload-previews" data-upload-previews></div>
        <script type="application/json" data-upload-old>@json(old('items', []))</script>
        <div class="admin-actions"><button class="admin-button" type="submit" data-upload-submit>@include('partials.icon', ['name' => 'upload']) Lưu tác phẩm</button></div>
    </form>
</details>
<div data-layout-scope="portfolio" data-layout="auto" class="admin-collection">
    <form class="admin-filter" method="GET" action="{{ route('admin.portfolio') }}">
        <div class="admin-field admin-search"><label for="portfolio-search">Tìm kiếm tác phẩm</label><input id="portfolio-search" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="255" placeholder="Tên tác phẩm, mô tả ảnh…"></div>
        <div class="admin-field"><label for="portfolio-category">Danh mục</label><select id="portfolio-category" name="category"><option value="">Tất cả danh mục</option>@foreach($categories as $category)<option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>{{ $category->name }}</option>@endforeach</select></div>
        <div class="admin-field"><label for="portfolio-visibility">Hiển thị</label><select id="portfolio-visibility" name="visibility"><option value="">Tất cả trạng thái</option><option value="visible" @selected(($filters['visibility'] ?? '') === 'visible')>Đang hiển thị</option><option value="hidden" @selected(($filters['visibility'] ?? '') === 'hidden')>Đang ẩn</option></select></div>
        <div class="admin-field"><label for="portfolio-sort">Sắp xếp</label><select id="portfolio-sort" name="sort">@foreach(['order' => 'Thứ tự hiển thị', 'newest' => 'Mới nhất', 'oldest' => 'Cũ nhất', 'title' => 'Tên A–Z'] as $key => $label)<option value="{{ $key }}" @selected(($filters['sort'] ?? 'order') === $key)>{{ $label }}</option>@endforeach</select></div>
        <div class="admin-field"><label for="portfolio-per-page">Số ảnh / trang</label><select id="portfolio-per-page" name="per_page">@foreach([24, 48, 96] as $number)<option value="{{ $number }}" @selected(($filters['per_page'] ?? 24) == $number)>{{ $number }}</option>@endforeach</select></div>
        <div class="admin-filter-actions"><button class="admin-button" type="submit">@include('partials.icon', ['name' => 'search']) Lọc</button><a class="admin-button secondary" href="{{ route('admin.portfolio') }}">Đặt lại</a></div>
    </form>
    <div class="admin-list-heading"><h2>Bộ sưu tập <span>{{ $portfolioItems->total() }} tác phẩm</span></h2>@include('admin.view-switch')</div>
    <form id="portfolio-bulk" method="POST" action="{{ route('admin.portfolio.bulk', $filters) }}" data-bulk-form data-confirm="Áp dụng thao tác cho các tác phẩm đã chọn?">@csrf
        <div class="admin-bulk-toolbar"><label class="admin-check"><input type="checkbox" data-select-all aria-label="Chọn tất cả tác phẩm trên trang"> Chọn trang này</label><span data-selected-count aria-live="polite">Đã chọn 0</span><select name="action" aria-label="Thao tác hàng loạt"><option value="hide">Ẩn khỏi website</option><option value="show">Hiển thị trên website</option><option value="delete">Xóa tác phẩm đã chọn</option></select><button class="admin-button secondary" type="submit" data-bulk-submit disabled>Áp dụng</button><button class="admin-text-button" type="button" data-clear-selection>Bỏ chọn</button></div>
    </form>
    <div class="admin-table-wrap"><table class="admin-table admin-responsive-table admin-artwork-table"><thead><tr><th class="admin-select-cell"><span class="admin-sr-only">Chọn</span></th><th>Hình ảnh</th><th>Tác phẩm</th><th>Danh mục</th><th>Thứ tự</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
    @forelse($portfolioItems as $item)
        <tr><td class="admin-select-cell"><input type="checkbox" form="portfolio-bulk" name="ids[]" value="{{ $item->id }}" data-item-select aria-label="Chọn {{ $item->title }}"></td>
            <td class="admin-artwork-image"><a class="admin-image-open" href="{{ $item->admin_image_url }}" data-image-open data-image-title="{{ $item->title }}" data-image-details="{{ $categories->firstWhere('slug', $item->category)?->name ?? $item->category }} · {{ $item->alt_text }}" aria-label="Xem ảnh {{ $item->title }}"><img src="{{ $item->admin_image_url }}" alt="{{ $item->alt_text ?: $item->title }}" loading="lazy"><span>@include('partials.icon', ['name' => 'zoom-in'])</span></a></td>
            <td class="admin-artwork-title" data-label="Tác phẩm"><strong>{{ $item->title }}</strong><small>{{ $item->alt_text ?: 'Chưa có mô tả' }}</small></td>
            <td data-label="Danh mục"><span class="admin-category-pill">{{ $categories->firstWhere('slug', $item->category)?->name ?? $item->category }}</span></td>
            <td data-label="Thứ tự">{{ $item->display_order }}</td>
            <td data-label="Trạng thái"><span class="admin-badge {{ $item->is_featured ? 'is-success' : 'is-muted' }}">{{ $item->is_featured ? 'Đang hiển thị' : 'Đang ẩn' }}</span></td>
            <td class="admin-row-actions"><a class="admin-icon-button" href="{{ route('admin.portfolio.edit', ['portfolioItem' => $item, ...$filters]) }}" title="Sửa tác phẩm" aria-label="Sửa {{ $item->title }}">@include('partials.icon', ['name' => 'edit'])</a><form method="POST" action="{{ route('admin.portfolio.destroy', ['portfolioItem' => $item, ...$filters]) }}" data-confirm="Xóa tác phẩm “{{ $item->title }}”? Ảnh tải lên của tác phẩm này sẽ bị xóa khỏi hệ thống." data-confirm-danger>@csrf @method('DELETE')<button class="admin-icon-button danger" type="submit" title="Xóa tác phẩm" aria-label="Xóa {{ $item->title }}">@include('partials.icon', ['name' => 'trash'])</button></form></td>
        </tr>
    @empty<tr class="admin-empty-row"><td colspan="7"><div class="admin-empty">@include('partials.icon', ['name' => 'image'])<h3>Chưa có tác phẩm phù hợp</h3><p>Thử thay đổi bộ lọc hoặc thêm hình ảnh vào bộ sưu tập.</p></div></td></tr>@endforelse
    </tbody></table></div>
    {{ $portfolioItems->onEachSide(1)->links('admin.pagination') }}
</div>
@endsection

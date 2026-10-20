@extends('admin.layout')
@section('title', 'Sửa tác phẩm — Quản trị')
@section('page_heading', 'Sửa tác phẩm')
@section('content')
<div class="admin-page-intro"><p>Cập nhật thông tin cho từng ảnh trong bộ sưu tập.</p><a class="admin-button secondary" href="{{ route('admin.portfolio', $filters) }}">@include('partials.icon', ['name' => 'previous']) Về danh sách</a></div>
<div class="admin-edit-grid">
    <aside class="admin-edit-preview admin-card"><a class="admin-image-open" href="{{ $portfolioItem->admin_image_url }}" data-image-open data-image-title="{{ $portfolioItem->title }}" aria-label="Xem ảnh {{ $portfolioItem->title }}"><img src="{{ $portfolioItem->admin_image_url }}" alt="{{ $portfolioItem->alt_text ?: $portfolioItem->title }}"><span>@include('partials.icon', ['name' => 'zoom-in'])</span></a><p>Bấm vào ảnh để xem chi tiết và phóng to.</p></aside>
    <form class="admin-form" method="POST" action="{{ route('admin.portfolio.update', ['portfolioItem' => $portfolioItem, ...$filters]) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <h2>Thông tin tác phẩm</h2>
        <div class="admin-form-grid">
            <div class="admin-field admin-wide"><label for="title">Tên tác phẩm *</label><input id="title" name="title" value="{{ old('title', $portfolioItem->title) }}" required maxlength="255">@error('title')<small class="admin-inline-error">{{ $message }}</small>@enderror</div>
            <div class="admin-field"><label for="category">Danh mục *</label><select id="category" name="category" required>@foreach($categories as $category)<option value="{{ $category->slug }}" @selected(old('category', $portfolioItem->category) === $category->slug)>{{ $category->name }}{{ $category->is_active ? '' : ' (đang tắt)' }}</option>@endforeach</select>@error('category')<small class="admin-inline-error">{{ $message }}</small>@enderror</div>
            <div class="admin-field"><label for="display_order">Thứ tự hiển thị</label><input type="number" min="0" max="10000" id="display_order" name="display_order" value="{{ old('display_order', $portfolioItem->display_order) }}" required></div>
            <div class="admin-field admin-wide"><label for="alt_text">Mô tả hình ảnh</label><textarea id="alt_text" name="alt_text" maxlength="255" rows="3">{{ old('alt_text', $portfolioItem->alt_text) }}</textarea><small>Giúp mô tả tác phẩm và hỗ trợ người dùng trình đọc màn hình.</small></div>
            <div class="admin-field admin-wide"><label for="image">Thay hình ảnh</label><input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" data-replacement-input><small>Không bắt buộc · JPG, PNG, WebP · Tối đa 8 MB.</small><img class="admin-replacement-preview" data-replacement-preview alt="Ảnh thay thế" hidden></div>
            <label class="admin-check admin-wide"><input type="hidden" name="is_featured" value="0"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $portfolioItem->is_featured))> Hiển thị trên website</label>
        </div><div class="admin-actions"><button class="admin-button" type="submit">@include('partials.icon', ['name' => 'check']) Lưu thay đổi</button><a class="admin-button secondary" href="{{ route('admin.portfolio', $filters) }}">Hủy</a></div>
    </form>
</div>
@endsection

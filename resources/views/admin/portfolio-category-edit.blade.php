@extends('admin.layout')
@section('title', 'Sửa danh mục — Quản trị')
@section('page_heading', 'Sửa danh mục')
@section('content')
<div class="admin-page-intro"><p>Thay đổi tên hoặc mã sẽ được đồng bộ với các tác phẩm đang thuộc danh mục này.</p><a class="admin-button secondary" href="{{ route('admin.portfolio.categories') }}">@include('partials.icon', ['name' => 'previous']) Về danh mục</a></div>
<form class="admin-form" method="POST" action="{{ route('admin.portfolio.categories.update', $category) }}">
    @csrf @method('PUT')<div class="admin-form-grid"><div class="admin-field"><label for="name">Tên hiển thị *</label><input id="name" name="name" value="{{ old('name', $category->name) }}" required maxlength="80"></div><div class="admin-field"><label for="slug">Mã danh mục *</label><input id="slug" name="slug" value="{{ old('slug', $category->slug) }}" pattern="[A-Za-z0-9_-]+" required maxlength="80"></div><div class="admin-field"><label for="display_order">Thứ tự</label><input id="display_order" name="display_order" type="number" min="0" max="10000" value="{{ old('display_order', $category->display_order) }}" required></div><label class="admin-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active))> Đang hoạt động</label></div><div class="admin-actions"><button class="admin-button" type="submit">Lưu danh mục</button><a class="admin-button secondary" href="{{ route('admin.portfolio.categories') }}">Hủy</a></div>
</form>
@endsection

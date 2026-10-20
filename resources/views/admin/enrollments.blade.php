@extends('admin.layout')
@section('title', 'Đơn đăng ký tư vấn — Quản trị')
@section('page_heading', 'Đơn đăng ký tư vấn')
@section('content')
<div class="admin-page-intro"><p>Theo dõi khách quan tâm đến khóa học và cập nhật tiến độ tư vấn.</p></div>
<div class="admin-stats admin-stats-four">@foreach($statuses as $value => $label)<a href="{{ route('admin.enrollments', ['status' => $value]) }}"><span>{{ $label }}</span><strong>{{ $counts[$value] ?? 0 }}</strong></a>@endforeach</div>
<div data-layout-scope="enrollments" data-layout="auto" class="admin-collection">
    <form class="admin-filter" method="GET" action="{{ route('admin.enrollments') }}">
        <div class="admin-field admin-search"><label for="enrollment-search">Tìm kiếm</label><input id="enrollment-search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Họ tên, email hoặc số điện thoại…" maxlength="255"></div>
        <div class="admin-field"><label for="enrollment-status">Trạng thái tư vấn</label><select id="enrollment-status" name="status"><option value="">Tất cả trạng thái</option>@foreach($statuses as $key => $label)<option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>@endforeach</select></div>
        <div class="admin-filter-actions"><button class="admin-button" type="submit">@include('partials.icon', ['name' => 'search']) Lọc</button><a class="admin-button secondary" href="{{ route('admin.enrollments') }}">Đặt lại</a></div>
    </form>
    <div class="admin-list-heading"><h2>Danh sách đăng ký <span>{{ $enrollments->total() }} kết quả</span></h2>@include('admin.view-switch')</div>
    <div class="admin-table-wrap"><table class="admin-table admin-responsive-table"><thead><tr><th>Khách hàng</th><th>Liên hệ</th><th>Lời nhắn</th><th>Trạng thái</th><th>Ngày đăng ký</th><th>Thao tác</th></tr></thead><tbody>
    @forelse($enrollments as $enrollment)
        <tr><td data-label="Khách hàng"><strong>{{ $enrollment->name }}</strong><small>#{{ $enrollment->id }}</small></td><td data-label="Liên hệ">{{ $enrollment->contact }}</td><td data-label="Lời nhắn"><p class="admin-message">{{ $enrollment->message ?: '—' }}</p>@if($enrollment->admin_notes)<details class="admin-note"><summary>Ghi chú nội bộ</summary><p>{{ $enrollment->admin_notes }}</p></details>@endif</td><td data-label="Trạng thái"><span class="admin-badge {{ ['pending' => 'is-warning', 'contacted' => 'is-info', 'enrolled' => 'is-success', 'cancelled' => 'is-muted'][$enrollment->status] ?? 'is-muted' }}">{{ $statuses[$enrollment->status] ?? $enrollment->status }}</span></td><td data-label="Ngày đăng ký">{{ $enrollment->created_at->timezone('Asia/Tokyo')->format('d/m/Y') }}<small>{{ $enrollment->created_at->timezone('Asia/Tokyo')->format('H:i') }} JST</small></td><td class="admin-row-actions"><button class="admin-button secondary small" type="button" data-open-dialog="enrollment-{{ $enrollment->id }}">@include('partials.icon', ['name' => 'edit']) Cập nhật</button></td></tr>
    @empty<tr class="admin-empty-row"><td colspan="6"><div class="admin-empty"><h3>Chưa có đăng ký tư vấn</h3><p>Đăng ký mới sẽ xuất hiện tại đây. Bạn có thể thử thay đổi bộ lọc.</p></div></td></tr>@endforelse
    </tbody></table></div>
    {{ $enrollments->onEachSide(1)->links('admin.pagination') }}
</div>
@foreach($enrollments as $enrollment)
    <dialog id="enrollment-{{ $enrollment->id }}" class="admin-dialog" aria-labelledby="enrollment-title-{{ $enrollment->id }}" @if($errors->any() && old('enrollment_id') == $enrollment->id) data-auto-open @endif>
        <form class="admin-dialog-card" method="POST" action="{{ route('admin.enrollments.update', ['enrollment' => $enrollment, ...$filters]) }}" data-confirm="Lưu trạng thái tư vấn cho {{ $enrollment->name }}?">
            @csrf @method('PATCH')
            <input type="hidden" name="enrollment_id" value="{{ $enrollment->id }}">
            <div class="admin-dialog-heading"><h2 id="enrollment-title-{{ $enrollment->id }}">Cập nhật tư vấn</h2><button type="button" class="admin-icon-button" data-dialog-close aria-label="Đóng">@include('partials.icon', ['name' => 'close'])</button></div>
            <p>{{ $enrollment->name }} · {{ $enrollment->contact }}</p>
            <div class="admin-field">
                <label for="status-{{ $enrollment->id }}">Trạng thái *</label>
                <select id="status-{{ $enrollment->id }}" name="status" required>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" @selected((old('enrollment_id') == $enrollment->id ? old('status', $enrollment->status) : $enrollment->status) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @if(old('enrollment_id') == $enrollment->id)
                    @error('status')
                        <small class="admin-inline-error">{{ $message }}</small>
                    @enderror
                @endif
            </div>
            <div class="admin-field">
                <label for="notes-{{ $enrollment->id }}">Ghi chú nội bộ</label>
                <textarea id="notes-{{ $enrollment->id }}" name="admin_notes" maxlength="2000" rows="4" placeholder="Nội dung đã trao đổi, lịch hẹn liên hệ lại…">{{ old('enrollment_id') == $enrollment->id ? old('admin_notes', $enrollment->admin_notes) : $enrollment->admin_notes }}</textarea>
                @if(old('enrollment_id') == $enrollment->id)
                    @error('admin_notes')
                        <small class="admin-inline-error">{{ $message }}</small>
                    @enderror
                @endif
            </div>
            <div class="admin-actions"><button class="admin-button secondary" type="button" data-dialog-close>Hủy</button><button class="admin-button" type="submit">Lưu cập nhật</button></div>
        </form>
    </dialog>
@endforeach
@endsection

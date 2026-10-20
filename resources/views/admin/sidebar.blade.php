<aside class="admin-sidebar" aria-label="Điều hướng quản trị">
    <a class="admin-brand" href="{{ route('home') }}">
        <img src="{{ asset('images/van-cuong-logo-transparent.png') }}" alt="Văn Cường Makeup & Academy">
        <span>ACADEMY CONTROL</span>
    </a>
    <nav class="admin-sidebar-nav">
        <p>QUẢN LÝ NỘI DUNG</p>
        <a class="{{ request()->routeIs('admin.portfolio', 'admin.portfolio.edit') ? 'is-active' : '' }}" href="{{ route('admin.portfolio') }}"><span class="admin-nav-icon">01</span> Tác phẩm makeup</a>
        <a class="{{ request()->routeIs('admin.portfolio.categories*') ? 'is-active' : '' }}" href="{{ route('admin.portfolio.categories') }}"><span class="admin-nav-icon">02</span> Danh mục tác phẩm</a>
        <a class="{{ request()->routeIs('admin.enrollments') ? 'is-active' : '' }}" href="{{ route('admin.enrollments') }}"><span class="admin-nav-icon">03</span> Đăng ký tư vấn</a>
        <p>WORKSHOP</p>
        <a class="{{ request()->routeIs('workshop.admin', 'workshop.registration.update', 'workshop.export', 'workshop.capacity.update', 'workshop.visibility.update') ? 'is-active' : '' }}" href="{{ route('workshop.admin', config('workshop.slug')) }}"><span class="admin-nav-icon">04</span> Workshop</a>
    </nav>
    <div class="admin-sidebar-bottom">
        <a href="{{ route('home') }}" target="_blank" rel="noopener">Xem website @include('partials.icon', ['name' => 'arrow-up-right'])</a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Đăng xuất</button></form>
    </div>
</aside>

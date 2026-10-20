<header class="site-header">
    <a class="wordmark" href="{{ route('home') }}" aria-label="Văn Cường Makeup & Academy — Trang chủ"><img class="wordmark-logo" src="{{ asset('images/van-cuong-logo-transparent.png') }}" alt="Văn Cường Makeup & Academy"></a>
    <nav class="main-nav" id="main-nav" aria-label="Điều hướng chính">
        <a href="{{ route('home') }}#about">Giới thiệu</a><a href="{{ route('home') }}#proclass">Khóa học</a><a href="{{ route('home') }}#curriculum">Chương trình</a><a href="{{ route('home') }}#schedule">Lịch học</a><a href="{{ route('home') }}#portfolio">Tác phẩm</a><a href="{{ route('home') }}#journey">Học viên</a><a href="{{ route('home') }}#videos">Video</a><a href="{{ route('home') }}#commitment">Cam kết</a><a href="{{ route('home') }}#tuition">Học phí</a><a href="{{ route('home') }}#policies">Nội quy</a>@if($publicWorkshop?->is_published)<a class="nav-workshop" href="{{ route('workshop.show', config('workshop.slug')) }}">Workshop @include('partials.icon', ['name' => 'arrow-up-right'])</a>@endif
    </nav>
    <a class="header-cta" href="{{ route('home') }}#enrollment">Đăng ký tư vấn <span>@include('partials.icon', ['name' => 'arrow-up-right'])</span></a>
    <button class="menu-toggle" aria-controls="main-nav" aria-expanded="false" aria-label="Mở menu"><span></span><span></span></button>
</header>


<footer class="site-footer">
    <div class="footer-top wrap">
        <div><a class="wordmark" href="{{ route('home') }}" aria-label="Văn Cường Makeup & Academy — Trang chủ"><x-image file="van-cuong-logo-transparent.webp" class="wordmark-logo"  alt="Văn Cường Makeup & Academy" sizes="(max-width: 700px) 160px, 240px" /></a><p>Nơi kỹ thuật gặp gỡ nghệ thuật.<br>Nơi đam mê trở thành hành trình.</p></div>
        <div class="footer-links"><span>KHÁM PHÁ</span><a href="{{ route('home') }}#about">Về Văn Cường</a><a href="{{ route('home') }}#proclass">The Pro Class</a><a href="{{ route('home') }}#portfolio">Tác phẩm makeup</a></div>
        <div class="footer-links"><span>THÔNG TIN HỌC VIỆN</span><a href="{{ route('home') }}#schedule">Lịch học</a><a href="{{ route('home') }}#commitment">Quyền lợi học viên</a><a href="{{ route('policies') }}">Nội quy & chính sách</a></div>
        <div class="footer-links footer-contact"><span>LIÊN HỆ HỌC VIỆN</span>
            <a class="contact-detail" href="tel:+818098194619">@include('partials.icon', ['name' => 'phone'])<span><small>ĐIỆN THOẠI</small>08098194619</span></a>
            <a class="contact-detail" href="https://www.facebook.com/profile.php?id=100042490924350&amp;mibextid=wwXIfr" target="_blank" rel="noopener noreferrer">@include('partials.icon', ['name' => 'facebook'])<span><small>KẾT NỐI FACEBOOK</small>Văn Cường <span aria-hidden="true">@include('partials.icon', ['name' => 'arrow-up-right'])</span></span></a>
            <div class="contact-detail">@include('partials.icon', ['name' => 'location'])<address><small>ĐỊA ĐIỂM</small>Ga Nisshi Kawaguchi<br>Tokyo – Nhật Bản</address></div>
        </div>
    </div>
    <div class="footer-bottom wrap"><span>© {{ date('Y') }} Văn Cường Makeup & Academy.</span><span>PROFESSIONAL MAKEUP EDUCATION IN JAPAN</span><a href="{{ route('login') }}">Quản trị</a><a href="#main-content">Về đầu trang @include('partials.icon', ['name' => 'arrow-up'])</a></div>
</footer>
<div class="floating-actions" role="group" aria-label="Liên hệ và điều hướng nhanh">
    <a class="floating-phone floating-facebook" href="https://www.facebook.com/profile.php?id=100042490924350" target="_blank" rel="noopener noreferrer" aria-label="Liên hệ Văn Cường Makeup Academy qua Facebook"><span class="phone-tooltip">Liên hệ qua Facebook</span>@include('partials.icon', ['name' => 'facebook'])</a>
    <a class="floating-phone" href="tel:+818098194619" aria-label="Gọi Văn Cường Makeup Academy: 08098194619"><span class="phone-tooltip">Gọi tư vấn · 08098194619</span>@include('partials.icon', ['name' => 'phone'])</a>
    @include('partials.back-to-top')
</div>



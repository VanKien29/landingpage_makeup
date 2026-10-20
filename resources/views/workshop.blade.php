<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo', ['seo' => $seo])
    @php
        $bannerSrcset = config('workshop.banner_image') === 'workshop-banner-bridal-enhanced.webp'
            ? asset('images/workshop-banner-bridal-enhanced-640.webp').' 640w, '.asset('images/workshop-banner-bridal-enhanced-960.webp').' 960w, '.asset('images/workshop-banner-bridal-enhanced.webp').' 1054w'
            : null;
    @endphp
    <link rel="preload" as="image" href="{{ asset('images/'.config('workshop.banner_image')) }}" @if($bannerSrcset) imagesrcset="{{ $bannerSrcset }}" imagesizes="(max-width: 760px) 100vw, 72vw" @endif fetchpriority="high">
    <meta name="theme-color" content="#f5f2eb">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('favicon.webp') }}" type="image/webp">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/workshop.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('css/workshop-hero.css') }}?v=5">
    <link rel="stylesheet" href="{{ asset('css/workshop-return.css') }}?v=5">
    <link rel="stylesheet" href="{{ asset('css/floating-actions.css') }}?v=1">
    <script src="{{ asset('js/workshop.js') }}?v=5" defer></script>
    <script src="{{ asset('js/smooth-scroll.js') }}?v=3" defer></script>
</head>
<body class="workshop-page ws-back">
<a class="ws-skip" href="#workshop-main">Chuyển đến nội dung chính</a>
<header class="ws-header">
    <a class="ws-brand" href="{{ route('home') }}" aria-label="Văn Cường Makeup & Academy — Trang chủ"><img class="ws-brand-logo" src="{{ asset('images/van-cuong-logo-transparent.webp') }}" alt="Văn Cường Makeup & Academy" width="2172" height="724"></a>
    <nav class="ws-nav" id="workshop-nav" aria-label="Điều hướng workshop"><a href="#story">Câu chuyện</a><a href="#concepts">Trải nghiệm</a><a href="#schedule">Lịch trình</a><a href="#event">Thông tin</a></nav>
    <a class="ws-header-cta" href="#registration">Đăng ký tham dự @include('partials.icon', ['name' => 'arrow-up-right'])</a>
    <button class="ws-menu" type="button" aria-label="Mở menu" aria-controls="workshop-nav" aria-expanded="false"><span></span><span></span></button>
</header>
<main id="workshop-main">
    <section class="ws-cover" aria-labelledby="workshop-title">
        <div class="ws-invite-background" aria-hidden="true"><img src="{{ asset('images/'.config('workshop.banner_image')) }}" @if($bannerSrcset) srcset="{{ $bannerSrcset }}" sizes="(max-width: 760px) 100vw, 72vw" @endif alt="" fetchpriority="high" width="1054" height="1492"></div>
        <div class="ws-invite-top ws-container"><span>PRIVATE MAKEUP WORKSHOP</span><span>TOKYO, JAPAN <i aria-hidden="true"></i> 2026 EDITION</span></div>
        <div class="ws-invite-grid ws-container">
            <div class="ws-invite-copy">
                <p class="ws-invite-eyebrow"><span aria-hidden="true"></span> A RETURN WITH GRATITUDE</p>
                <h1 id="workshop-title">{{ $workshop->title }}</h1>
                <p class="ws-invite-subtitle">{{ config('workshop.subtitle') }}</p>
                <p class="ws-invite-tagline">{{ $workshop->tagline }}</p>
                <p class="ws-invite-message">Một sự trở lại đầy biết ơn.<br>Một món quà dành cho {{ $workshop->capacity }} học viên, từ Văn Cường.</p>
                <div class="ws-invite-details">
                    <div><span>DATE</span><strong>{{ $workshop->event_date?->format('d.m.Y') ?? 'Coming soon' }}</strong></div>
                    <div><span>TIME / JST</span><strong>10:00–18:00</strong></div>
                    <div><span>LOCATION</span><strong>Tokyo, Japan</strong></div>
                </div>
                <div class="ws-invite-actions"><a class="ws-button ws-invite-register" href="#registration">REGISTER NOW @include('partials.icon', ['name' => 'arrow-up-right'])</a><div class="ws-invite-admission"><strong>100% FREE</strong><span>COMPLIMENTARY ADMISSION</span></div></div>
                <p class="ws-invite-note">Một lời mời riêng. Chỉ {{ $workshop->capacity }} suất tham dự.</p>
            </div>
        </div>
        <div class="ws-invite-artist"><img src="{{ asset('images/'.\App\Services\WebpImages::publicFilename($workshop->hero_image)) }}" alt="Văn Cường — nghệ sĩ makeup và nhà đào tạo" width="1254" height="1254"><div><span>WITH GRATITUDE,</span><strong>Văn Cường</strong><small>MAKEUP ARTIST & EDUCATOR</small></div></div>
        <div class="ws-invite-seal"><strong>{{ $workshop->capacity }}</strong><span>GUESTS ONLY</span></div>
        <div class="ws-invite-bottom ws-container"><span>NOT JUST A COMEBACK. A HEARTFELT THANK YOU.</span><span>02 LAYOUTS <i aria-hidden="true"></i> 06 HOURS OF ARTISTRY</span></div>
    </section>
    <div class="ws-at-a-glance" aria-label="Tổng quan workshop"><div><strong>{{ $workshop->capacity }}</strong><span>HỌC VIÊN<br>PRIVATE EXPERIENCE</span></div><div><strong>02</strong><span>LAYOUT MAKEUP<br>LIVE DEMONSTRATION</span></div><div><strong>06</strong><span>GIỜ TRAINING<br>CHUYÊN SÂU</span></div><div><strong>100<span class="ws-metric-percent">%</span></strong><span>HOÀN TOÀN MIỄN PHÍ<br>A GIFT OF GRATITUDE</span></div></div>

    <section class="ws-section ws-story ws-container ws-return-story" id="story">
        <div class="ws-section-label"><span>02 / THE STORY</span><span>NOT JUST A COMEBACK. A HEARTFELT THANK YOU.</span></div>
        <div class="ws-return-story-grid"><figure class="ws-return-portrait"><img src="{{ asset('images/founder-van-cuong-enhanced.webp') }}" alt="Văn Cường — Sự trở lại tại Nhật Bản" loading="lazy" width="1254" height="1254"><figcaption><span>THE PERSON BEHIND THE RETURN</span><span>VĂN CƯỜNG</span></figcaption></figure><div class="ws-return-story-copy"><p class="ws-kicker">THE STORY BEHIND THE BACK</p><h2>Một sự trở lại.<br><em>Một lời cảm ơn.</em></h2><p>{{ $workshop->introduction }}</p><p>Thay vì một workshop thương mại, Cường dành tặng một buổi training chuyên sâu <strong>hoàn toàn miễn phí</strong> như một món quà tri ân. Một không gian private để cùng học hỏi, kết nối và chia sẻ những kiến thức thực tế trong nghệ thuật makeup.</p><p class="ws-story-closing">Đây không chỉ là sự trở lại.<br>Đây là lời cảm ơn, và cũng là khởi đầu cho một hành trình mới.</p><a class="ws-text-link" href="#concepts">Món quà Cường muốn chia sẻ @include('partials.icon', ['name' => 'arrow-up-right'])</a></div></div>
        <div class="ws-artist-journal"><img src="{{ asset('images/educator-classroom.webp') }}" alt="Cường chia sẻ kỹ thuật makeup trong lớp học" loading="lazy" width="800" height="533"><div><p class="ws-kicker">TOGETHER, WE BEGIN AGAIN.</p><p>Trở lại để sẻ chia.<br>Đồng hành để cùng tiến bước.</p></div><img src="{{ asset('images/educator-students.webp') }}" alt="Những học viên đã cùng đồng hành trong hành trình makeup" loading="lazy" width="1200" height="800"></div>
    </section>

    <section class="ws-section ws-concepts" id="concepts">
        <div class="ws-container"><div class="ws-section-label"><span>03 / WORKSHOP EXPERIENCE</span><span>TWO LOOKS. SIX HOURS. SHARED WITH GRATITUDE.</span></div><div class="ws-heading-row"><h2>Trở lại để<br><em>cùng thăng hoa.</em></h2><p>02 layout makeup · 06 giờ training.<br>Kiến thức thực tế, trình diễn trực tiếp<br>và những kết nối từ đam mê chung.</p></div>
            @foreach($concepts as $concept)
                <article class="ws-concept {{ $loop->even ? 'ws-concept-reverse' : '' }}" data-ws-reveal>
                    @php
                        $primaryImage = $loop->first ? $workshop->glam_image : $workshop->bridal_image;
                        $conceptImages = in_array($primaryImage, $concept['images'], true)
                            ? array_values(array_unique([$primaryImage, ...$concept['images']]))
                            : $concept['images'];
                    @endphp
                    <figure class="ws-concept-image ws-concept-collage" data-ws-collage data-concept-title="{{ $concept['name'].' '.$concept['year'] }}">
                        <div class="ws-collage-stage">
                            @foreach($conceptImages as $conceptImage)
                                <a class="ws-collage-photo ws-collage-photo-{{ $loop->iteration }}" href="{{ asset('images/'.\App\Services\WebpImages::publicFilename($conceptImage)) }}" target="_blank" rel="noopener noreferrer" data-ws-collage-photo aria-label="Xem ảnh {{ $loop->iteration }} — {{ $concept['name'].' '.$concept['year'] }}"><img src="{{ asset('images/'.\App\Services\WebpImages::publicFilename($conceptImage)) }}" alt="{{ $concept['name'].' '.$concept['year'] }} — tác phẩm makeup, ảnh {{ $loop->iteration }}" loading="lazy" decoding="async" width="1706" height="2560"><span class="ws-collage-index" aria-hidden="true">0{{ $loop->iteration }}</span><span class="ws-collage-zoom" aria-hidden="true">@include('partials.icon', ['name' => 'zoom-in'])</span></a>
                            @endforeach
                        </div>
                        <figcaption><span>THE LOOK / 0{{ $loop->iteration }}</span><span>{{ $concept['label'] }}</span></figcaption>
                    </figure>
                    <div class="ws-concept-copy"><div class="ws-concept-number"><span>TRAINING 0{{ $loop->iteration }}</span><span>03 HOURS / LIVE DEMONSTRATION</span></div><h3>{{ $concept['name'] }}<em>{{ $concept['year'] }}</em></h3><p class="ws-concept-subtitle">{{ $concept['subtitle'] }}</p><p>{{ $concept['description'] }}</p><ul class="ws-techniques">@foreach($concept['techniques'] as $technique)<li><span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>{{ $technique }}</li>@endforeach</ul><a class="ws-text-link" href="#registration">Đăng ký tham dự miễn phí @include('partials.icon', ['name' => 'arrow-up-right'])</a></div>
                </article>
            @endforeach
        </div>
        <div class="ws-return-programme" id="schedule"><div class="ws-container"><div class="ws-section-label"><span>THE PROGRAMME</span><span>10:00–18:00 · TOKYO / JST</span></div><div class="ws-schedule-grid"><div class="ws-schedule-intro"><p class="ws-kicker">A DAY TO RECONNECT</p><h2>Một ngày<br><em>dành cho chúng ta.</em></h2><p>Đón khách từ 09:30.<br>06 giờ training, những chia sẻ chân thành<br>và khoảnh khắc cùng lưu giữ.</p><span class="ws-schedule-date">{{ $workshop->event_date?->format('d.m.Y') ?? 'NGÀY TỔ CHỨC ĐANG CẬP NHẬT' }}</span></div><ol class="ws-timeline">@foreach($schedule as [$start, $end, $name, $description, $type])<li class="ws-timeline-{{ $type }}"><div class="ws-timeline-time"><time>{{ $start }}</time><span>{{ $end }}</span></div><div class="ws-timeline-copy">@if($type === 'training')<span class="ws-timeline-tag">03 GIỜ TRAINING</span>@endif<h3>{{ $name }}</h3><p>{{ $description }}</p></div></li>@endforeach</ol></div></div></div>
    </section>

    <section class="ws-section ws-event" id="event"><div class="ws-container"><div class="ws-section-label"><span>04 / EVENT INFORMATION</span><span>A PRIVATE EVENT. A PERSONAL THANK YOU.</span></div><div class="ws-event-grid"><div class="ws-event-location"><div class="ws-event-map"><iframe src="{{ config('workshop.map_embed_url') }}" title="Bản đồ Fukagawa Garage tại Tokyo" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe><div class="ws-event-map-label"><span>THE VENUE</span><strong>Fukagawa Garage</strong><small>深川ガレージ · Tokyo</small></div></div><div class="ws-event-location-links"><a href="{{ config('workshop.map_url') }}" target="_blank" rel="noopener noreferrer">Mở Google Maps @include('partials.icon', ['name' => 'arrow-up-right'])</a><a href="{{ config('workshop.venue_detail_url') }}" target="_blank" rel="noopener noreferrer">Xem chi tiết địa điểm @include('partials.icon', ['name' => 'arrow-up-right'])</a></div></div><div class="ws-event-details"><p class="ws-kicker">YOUR INVITATION TO THE BACK</p><h2>Hẹn gặp lại,<br><em>tại Tokyo.</em></h2><p class="ws-event-address">{{ $workshop->venue }}<br><span>Fukagawa Garage · Không gian tổ chức workshop riêng tư</span></p><dl><div><dt>DATE</dt><dd>{{ $workshop->event_date?->format('d/m/Y') ?? 'Coming Soon · Ngày tổ chức đang cập nhật' }}</dd></div><div><dt>TIME</dt><dd>10:00–18:00 <small>· Check-in từ 09:30 / JST</small></dd></div><div><dt>DRESS CODE</dt><dd>Black Elegance</dd></div><div><dt>ADMISSION</dt><dd>Complimentary — FREE 100%</dd></div><div><dt>CAPACITY</dt><dd>Limited to {{ $workshop->capacity }} Guests<br><small>Tối đa {{ $workshop->capacity }} học viên được xác nhận tham dự.</small></dd></div></dl><div class="ws-event-ticket ws-return-admission"><div><span>A GIFT OF GRATITUDE</span><strong>100% Free.</strong><p>Không thu phí tham dự.</p></div><a href="#registration" aria-label="Đăng ký workshop miễn phí">@include('partials.icon', ['name' => 'arrow-up-right'])</a></div><p class="ws-event-payment">Private Makeup Workshop — Appreciation Event.<br>Ngày tổ chức sẽ được cập nhật khi ban tổ chức chốt lịch.</p></div></div></div></section>

    <section class="ws-section ws-registration ws-container" id="registration"><div class="ws-section-label"><span>05 / REGISTRATION</span><span>{{ $workshop->capacity }} SEATS ONLY — 100% COMPLIMENTARY</span></div><div class="ws-registration-grid"><div class="ws-registration-intro"><p class="ws-kicker">RESERVE YOUR SEAT</p><h2>Một chỗ ngồi.<br><em>Một lời tri ân.</em></h2><p>Cường mong được gặp lại những gương mặt thân quen, và chào đón những người bạn mới cùng yêu nghệ thuật makeup.</p><div class="ws-availability"><span class="ws-dot"></span><strong>{{ !$workshop->acceptsRegistrations() ? 'Đã tạm đóng đăng ký' : ($remaining > 0 ? 'Đang tiếp nhận đăng ký tham dự' : 'Đang tiếp nhận danh sách chờ') }}</strong><span>{{ $workshop->confirmed_tickets }} / {{ $workshop->capacity }} học viên đã được xác nhận.</span><progress value="{{ $workshop->confirmed_tickets }}" max="{{ $workshop->capacity }}" aria-label="Số học viên đã xác nhận"></progress></div><div class="ws-registration-price ws-return-free">100% Free<span>MỘT MÓN QUÀ, TỪ SỰ BIẾT ƠN.</span></div><p class="ws-registration-note">Mỗi người đăng ký một suất. Gửi form chưa đồng nghĩa với xác nhận chỗ ngồi. Khi đã đủ {{ $workshop->capacity }} suất, đăng ký mới được đưa vào danh sách chờ; ban tổ chức sẽ liên hệ khi có chỗ trống.</p><a class="ws-text-link" href="{{ config('workshop.facebook') }}" target="_blank" rel="noopener noreferrer">Liên hệ ban tổ chức qua Facebook @include('partials.icon', ['name' => 'arrow-up-right'])</a></div>
        <div class="ws-form-panel"><h3>{{ $remaining > 0 ? 'Đăng ký tham dự' : 'Đăng ký danh sách chờ' }}</h3><p>THE BACK · Hẹn gặp lại bạn tại Tokyo.</p><form action="{{ route('workshop.register', $workshop->slug) }}" method="post" id="workshop-form">@csrf
            <div class="ws-form-status @if($errors->any()) is-error @endif" role="status" aria-live="polite" tabindex="-1" @if(!session('workshop_success') && !$errors->any()) hidden @endif>{{ session('workshop_success') ?: $errors->first() }}</div>
            <fieldset @disabled(!$workshop->acceptsRegistrations())><legend class="ws-visually-hidden">Thông tin học viên đăng ký</legend><div class="ws-field"><label for="ws-name">Họ và tên <span>*</span></label><input id="ws-name" name="name" value="{{ old('name') }}" autocomplete="name" placeholder="Tên đầy đủ của bạn" maxlength="150" required></div><div class="ws-field-row"><div class="ws-field"><label for="ws-phone">Số điện thoại tại Nhật Bản <span>*</span></label><input id="ws-phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" placeholder="090… hoặc +81…" maxlength="40" required></div><div class="ws-field"><label for="ws-email">Email <span>*</span></label><input id="ws-email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" placeholder="you@example.com" maxlength="255" required></div></div><div class="ws-field"><label for="ws-social">Facebook hoặc Instagram cá nhân <span>*</span></label><input id="ws-social" name="social" value="{{ old('social') }}" placeholder="Đường dẫn hoặc tên tài khoản của bạn" maxlength="255" required></div><div class="ws-field"><label for="ws-experience">Tình trạng hiện tại <span>*</span></label><select id="ws-experience" name="experience" required><option value="">Chọn tình trạng phù hợp với bạn</option>@foreach($experienceLabels as $value => $label)<option value="{{ $value }}" @selected(old('experience') === $value)>{{ $label }}</option>@endforeach</select></div><div class="ws-honeypot" aria-hidden="true"><label for="ws-website">Website</label><input id="ws-website" name="website" tabindex="-1" autocomplete="off"></div><label class="ws-consent ws-attendance"><input type="checkbox" name="attendance" value="1" required @checked(old('attendance'))><span>Tôi có thể tham dự workshop tại Tokyo và đồng ý để ban tổ chức liên hệ xác nhận lại khi ngày tổ chức được công bố.</span></label><label class="ws-consent"><input type="checkbox" name="agree" value="1" required @checked(old('agree'))><span>Tôi đã đọc và đồng ý với <a href="#registration-policy" data-open-policy>chính sách đăng ký và xử lý dữ liệu cá nhân</a>.</span></label><button class="ws-button ws-submit" type="submit">{{ !$workshop->acceptsRegistrations() ? 'ĐÃ TẠM ĐÓNG ĐĂNG KÝ' : ($remaining > 0 ? 'ĐĂNG KÝ THAM DỰ MIỄN PHÍ' : 'ĐĂNG KÝ DANH SÁCH CHỜ') }} @include('partials.icon', ['name' => 'arrow-up-right'])</button></fieldset>
        </form><details class="ws-registration-policy" id="registration-policy"><summary>Chính sách đăng ký & dữ liệu cá nhân <span class="ws-plus" aria-hidden="true"></span></summary><p>THE BACK do Văn Cường Makeup & Academy — Japan tổ chức, hoàn toàn miễn phí, không yêu cầu chuyển khoản hay đặt cọc. Mỗi người đăng ký một suất. Đơn đăng ký chưa đồng nghĩa với giữ chỗ; ban tổ chức liên hệ xác nhận riêng, trong giới hạn {{ $workshop->capacity }} học viên. Ngày và địa chỉ cụ thể sẽ được thông báo khi chốt chương trình; khả năng tham dự được xác nhận lại sau khi công bố ngày. Khi đủ suất, đăng ký mới vào danh sách chờ. Việc xác nhận từ danh sách chờ do ban tổ chức thực hiện khi có chỗ trống.</p><p>Họ tên, số điện thoại tại Nhật, email, mạng xã hội, tình trạng makeup và xác nhận có thể tham dự được lưu trong hệ thống riêng để quản lý đăng ký, liên hệ, xác nhận và check-in. Chỉ quản trị viên được cấp quyền có thể xem thông tin này. Dữ liệu không được công khai hoặc dùng để gửi quảng cáo khi chưa có sự đồng ý riêng. Bạn có thể liên hệ {{ config('workshop.phone') }} hoặc Facebook học viện để cập nhật, hủy yêu cầu tham dự, rút lại đồng ý hoặc yêu cầu xóa thông tin trong phạm vi nghĩa vụ lưu giữ áp dụng.</p></details></div>
        </div></section>

    <section class="ws-gratitude" id="gratitude"><div class="ws-container"><div class="ws-section-label"><span>06 / THANK YOU MESSAGE</span><span>A RETURN WITH GRATITUDE.</span></div><div class="ws-gratitude-content"><span class="ws-gratitude-mark" aria-hidden="true">“</span><p class="ws-kicker">WITH GRATITUDE, VĂN CƯỜNG.</p><h2>Cảm ơn,<br><em>vì đã luôn ở đây.</em></h2><blockquote>Cảm ơn vì đã luôn tin tưởng, yêu thương và đồng hành cùng Cường.<br><br>Hy vọng THE BACK sẽ không chỉ là một buổi học makeup mà còn là một kỷ niệm thật đẹp trong hành trình theo đuổi nghệ thuật của tất cả chúng ta.</blockquote><p class="ws-gratitude-signature">Văn Cường</p><span class="ws-gratitude-brand">VĂN CƯỜNG MAKEUP & ACADEMY — JAPAN</span><div class="ws-gratitude-contact"><a href="{{ config('workshop.facebook') }}" target="_blank" rel="noopener noreferrer">@include('partials.icon', ['name' => 'facebook']) Facebook @include('partials.icon', ['name' => 'arrow-up-right'])</a><a href="tel:+818098194619">@include('partials.icon', ['name' => 'phone']) {{ config('workshop.phone') }}</a></div></div><footer class="ws-footer-bottom"><a class="ws-brand" href="#workshop-main"><img class="ws-brand-logo" src="{{ asset('images/van-cuong-logo-transparent.webp') }}" alt="Văn Cường Makeup & Academy" width="2172" height="724"></a><span>© {{ date('Y') }} · THE BACK — SỰ TRỞ LẠI</span><a href="#workshop-main">Về đầu trang @include('partials.icon', ['name' => 'arrow-up'])</a></footer></div></section>
</main>
<dialog class="ws-image-dialog" id="ws-image-dialog" aria-labelledby="ws-image-dialog-title">
    <button class="ws-image-close" type="button" aria-label="Đóng ảnh" autofocus>@include('partials.icon', ['name' => 'close'])</button>
    <div class="ws-image-view"><img alt="" data-ws-dialog-image></div>
    <div class="ws-image-toolbar"><div><p id="ws-image-dialog-title"></p><span data-ws-dialog-counter aria-live="polite"></span></div><div class="ws-image-navigation"><button type="button" aria-label="Ảnh trước" data-ws-dialog-prev>@include('partials.icon', ['name' => 'previous'])</button><button type="button" aria-label="Ảnh tiếp theo" data-ws-dialog-next>@include('partials.icon', ['name' => 'next'])</button></div></div>
</dialog>
<div class="floating-actions floating-actions-workshop" role="group" aria-label="Liên hệ và điều hướng nhanh">
    <a class="ws-floating-contact" href="{{ config('workshop.facebook') }}" target="_blank" rel="noopener noreferrer" aria-label="Liên hệ workshop qua Facebook" hidden>@include('partials.icon', ['name' => 'facebook'])</a>
    @include('partials.back-to-top')
</div>
</body>
</html>

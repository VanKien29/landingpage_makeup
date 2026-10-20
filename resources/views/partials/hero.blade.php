<section class="hero-banner" id="home" aria-label="Văn Cường Makeup & Academy — Banner ảnh">
    <div class="banner-slides" aria-hidden="true">
        <div class="banner-slide is-active">
            <img class="banner-side" src="{{ asset('images/academy-36.webp') }}" alt="" width="1333" height="2000">
            <img class="banner-image-main" src="{{ asset('images/academy-38.webp') }}" srcset="{{ asset('images/academy-38-640.webp') }} 640w, {{ asset('images/academy-38-960.webp') }} 960w, {{ asset('images/academy-38.webp') }} 1333w" sizes="(max-width: 700px) 100vw, (max-width: 1080px) 50vw, 40vw" alt="" fetchpriority="high" width="1333" height="2000">
            <img class="banner-side" src="{{ asset('images/academy-43.webp') }}" alt="" width="1333" height="2000">
        </div>
        <div class="banner-slide">
            <img class="banner-side" src="{{ asset('images/academy-41.webp') }}" alt="" loading="lazy" width="1333" height="2000">
            <img class="banner-image-main" src="{{ asset('images/academy-40.webp') }}" alt="" loading="lazy" width="1333" height="2000">
            <img class="banner-side" src="{{ asset('images/academy-39.webp') }}" alt="" loading="lazy" width="1333" height="2000">
        </div>
        <div class="banner-slide">
            <img class="banner-side" src="{{ asset('images/academy-44.webp') }}" alt="" loading="lazy" width="1333" height="2000">
            <img class="banner-image-main" src="{{ asset('images/academy-25.webp') }}" alt="" loading="lazy" width="1333" height="2000">
            <img class="banner-side" src="{{ asset('images/academy-47.webp') }}" alt="" loading="lazy" width="1333" height="2000">
        </div>
    </div>
    <div class="banner-shade"></div>
    <div class="banner-top"><span>MAKEUP AS AN ART FORM</span><span>PROFESSIONAL EDUCATION · JAPAN</span></div>
    <div class="banner-content wrap">
        <p class="eyebrow">PROFESSIONAL MAKEUP COURSE</p>
        <h1>Văn Cường<span>Makeup <i>&</i> Academy</span></h1>
        <div class="banner-bottom-copy"><p>Khóa đào tạo Makeup Chuyên Nghiệp tại Nhật Bản.<br>Phát triển kỹ thuật, tư duy thẩm mỹ<br>và bản lĩnh của người làm nghề.</p><div class="banner-actions"><a class="button banner-button" href="#proclass">Khám phá khóa học <span>@include('partials.icon', ['name' => 'arrow-up-right'])</span></a><a class="text-link" href="#enrollment">Đăng ký tư vấn <span>@include('partials.icon', ['name' => 'arrow-up-right'])</span></a></div></div>
    </div>
    <div class="banner-side-note"><span>THE PRO CLASS</span><strong>40</strong><span>BUỔI ĐÀO TẠO</span></div>
    <div class="banner-controls"><span class="banner-counter" aria-live="off">01 / 03</span><button type="button" class="banner-prev" aria-label="Banner trước">@include('partials.icon', ['name' => 'previous'])</button><button type="button" class="banner-next" aria-label="Banner tiếp theo">@include('partials.icon', ['name' => 'next'])</button></div>
</section>
<div class="marquee" aria-label="Nghệ thuật trang điểm và đào tạo chuyên nghiệp"><div class="marquee-track" aria-hidden="true">@for($i = 0; $i < 4; $i++)<span>MAKEUP IS ART <i>✳</i> EDUCATION IS A CRAFT <i>✳</i> FIND YOUR SIGNATURE <i>✳</i></span>@endfor</div></div>


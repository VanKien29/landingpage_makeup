<section class="hero-banner" id="home" aria-label="Văn Cường Makeup & Academy — Banner ảnh">
    <div class="banner-slides" aria-hidden="true">
        @foreach([['36', '38', '43'], ['41', '40', '39'], ['44', '25', '47']] as $photos)
            <div class="banner-slide {{ $loop->first ? 'is-active' : '' }}">
                <x-image :file="'academy-'.$photos[0].'.webp'" class="banner-side" sizes="30vw" loading="eager" fetchpriority="low" :deferred="true" />
                <x-image :file="'academy-'.$photos[1].'.webp'" class="banner-image-main" sizes="(max-width: 700px) max(100vw, 500px), (max-width: 1080px) 50vw, 40vw" loading="eager" :fetchpriority="$loop->first ? 'high' : 'low'" :deferred="!$loop->first" />
                <x-image :file="'academy-'.$photos[2].'.webp'" class="banner-side" sizes="30vw" loading="eager" fetchpriority="low" :deferred="true" />
            </div>
        @endforeach
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
<div class="marquee" aria-hidden="true"><div class="marquee-track">@for($i = 0; $i < 4; $i++)<span>MAKEUP IS ART <i>✳</i> EDUCATION IS A CRAFT <i>✳</i> FIND YOUR SIGNATURE <i>✳</i></span>@endfor</div></div>


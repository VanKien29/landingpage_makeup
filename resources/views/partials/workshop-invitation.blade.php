<section class="workshop-invitation wrap" id="workshop" aria-labelledby="workshop-invitation-title">
    <div class="workshop-invitation-card">
        <a class="workshop-invitation-image" href="{{ route('workshop.show', config('workshop.slug')) }}" aria-label="Khám phá THE BACK — Private Makeup Workshop"><img src="{{ asset('images/'.config('workshop.banner_image')) }}" alt="Tác phẩm bridal makeup của Văn Cường" loading="lazy" width="1054" height="1492"></a>
        <div class="workshop-invitation-copy">
            <p class="workshop-invitation-kicker">PRIVATE MAKEUP WORKSHOP · TOKYO</p>
            <h2 id="workshop-invitation-title">THE BACK <span>SỰ TRỞ LẠI</span></h2>
            <p>Một món quà tri ân, một ngày cùng nghệ thuật makeup.</p>
            <div class="workshop-invitation-meta"><span>02 layout makeup</span><span>06 giờ training</span></div>
        </div>
        <div class="workshop-invitation-action"><span>100% FREE <small>HOÀN TOÀN MIỄN PHÍ</small></span><a class="button dark" href="{{ route('workshop.show', config('workshop.slug')) }}">Khám phá workshop @include('partials.icon', ['name' => 'arrow-up-right'])</a></div>
    </div>
</section>

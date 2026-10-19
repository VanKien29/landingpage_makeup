<article class="film-card film-card-{{ $orientation }}">
    <button class="film-open" type="button" data-video-src="{{ asset('videos/academy-film-'.$number.'.mp4') }}" data-video-poster="{{ asset('images/film-poster-'.$number.'.jpg') }}" data-video-title="{{ $title }}" data-orientation="{{ $orientation }}" aria-label="Xem video: {{ $title }}">
        <img src="{{ asset('images/film-poster-'.$number.'.jpg') }}" alt="{{ $subtitle }}" loading="lazy" width="{{ $orientation === 'landscape' ? 1280 : 720 }}" height="{{ $orientation === 'landscape' ? 720 : 1280 }}">
        <span class="film-format">{{ $orientation === 'landscape' ? 'PHIM NGANG' : 'PHIM DỌC' }} / {{ $duration }}</span>
        <span class="film-play" aria-hidden="true">@include('partials.icon', ['name' => 'play'])</span>
        <span class="film-watch">XEM THƯỚC PHIM <span aria-hidden="true">@include('partials.icon', ['name' => 'arrow-up-right'])</span></span>
    </button>
    <div class="film-caption"><p>{{ $subtitle }}</p><h3>{{ $title }}</h3></div>
</article>


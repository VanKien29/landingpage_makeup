<article class="film-card film-card-{{ $orientation }}">
    <button class="film-open" type="button" data-video-src="{{ asset('videos/academy-film-'.$number.'.mp4') }}" data-video-poster="{{ asset('images/film-poster-'.$number.'.webp') }}" data-video-title="{{ $title }}" data-orientation="{{ $orientation }}" aria-label="Xem video: {{ $title }}">
        <x-image :file="'film-poster-'.$number.'.webp'" sizes="(max-width: 700px) 90vw, 45vw" :alt="$subtitle" />
        <span class="film-format">{{ $orientation === 'landscape' ? 'PHIM NGANG' : 'PHIM DỌC' }} / {{ $duration }}</span>
        <span class="film-play" aria-hidden="true">@include('partials.icon', ['name' => 'play'])</span>
        <span class="film-watch">XEM THƯỚC PHIM <span aria-hidden="true">@include('partials.icon', ['name' => 'arrow-up-right'])</span></span>
    </button>
    <div class="film-caption"><p>{{ $subtitle }}</p><h3>{{ $title }}</h3></div>
</article>


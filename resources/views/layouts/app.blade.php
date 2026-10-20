<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.seo', ['seo' => $seo])
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f7f6f2">
    @if(request()->routeIs('home'))
        <link rel="preload" as="image" href="{{ asset('images/academy-38.webp') }}" imagesrcset="{{ asset('images/academy-38-640.webp') }} 640w, {{ asset('images/academy-38-960.webp') }} 960w, {{ asset('images/academy-38.webp') }} 1333w" imagesizes="(max-width: 700px) 100vw, (max-width: 1080px) 50vw, 40vw" fetchpriority="high">
    @endif
    <link rel="icon" href="{{ asset('favicon.webp') }}" type="image/webp">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500;1,600&family=IBM+Plex+Sans:wght@500;600;700&family=Manrope:wght@400;500;600;700;800&family=Noto+Serif:ital,wght@0,400;0,500;1,400;1,500&family=Oswald:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/academy.css') }}">
    <link rel="stylesheet" href="{{ asset('css/editorial.css') }}?v=20261020-2">
    <link rel="stylesheet" href="{{ asset('css/floating-actions.css') }}?v=1">
    <script src="{{ asset('js/academy.js') }}" defer></script>
    <script src="{{ asset('js/smooth-scroll.js') }}?v=3" defer></script>
    <script src="{{ asset('js/motion.js') }}" defer></script>
    <script src="{{ asset('js/journey.js') }}" defer></script>
    <script src="{{ asset('js/videos.js') }}" defer></script>
    <script src="{{ asset('js/roadmap.js') }}" defer></script>
</head>
<body>
    <div class="reading-progress" aria-hidden="true"></div>
    <a class="skip-link" href="#main-content">Chuyển đến nội dung chính</a>
    @include('partials.header')
    <main id="main-content">@yield('content')</main>
    <template id="playback-icon-template">@include('partials.icon', ['name' => 'playback'])</template>
    @include('partials.footer')
</body>
</html>


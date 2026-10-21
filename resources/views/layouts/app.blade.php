<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.seo', ['seo' => $seo])
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f7f6f2">
    @if(request()->routeIs('home'))
        @php($heroImage = \App\Services\ResponsiveImages::attributes('academy-38.webp'))
        <link rel="preload" as="image" href="{{ $heroImage['src'] }}" imagesrcset="{{ $heroImage['srcset'] }}" imagesizes="(max-width: 700px) 100vw, (max-width: 1080px) 50vw, 40vw" fetchpriority="high">
    @endif
    <link rel="icon" href="{{ asset('favicon.webp') }}" type="image/webp">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @include('partials.academy-assets')
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


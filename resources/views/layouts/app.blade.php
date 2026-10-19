<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Văn Cường Makeup & Academy — Đào tạo Makeup Chuyên Nghiệp tại Nhật Bản. 40 buổi trong 04 tháng, chỉ 02–03 học viên mỗi lớp.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f7f6f2">
    <meta property="og:title" content="Văn Cường Makeup & Academy">
    <meta property="og:description" content="Nghệ thuật trang điểm. Tư duy thẩm mỹ. Bản lĩnh làm nghề.">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="vi_VN">
    <meta property="og:image" content="{{ asset('images/2aOboR6WiZgtJaXsCt5LunyqWE9n4yTJ5JsJ9sJc.jpg') }}">
    <title>@yield('title', 'Văn Cường Makeup & Academy | Professional Makeup Course in Japan')</title>
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500;1,600&family=IBM+Plex+Sans:wght@500;600;700&family=Manrope:wght@400;500;600;700;800&family=Noto+Serif:ital,wght@0,400;0,500;1,400;1,500&family=Oswald:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/academy.css') }}">
    <link rel="stylesheet" href="{{ asset('css/editorial.css') }}?v=20261019-7">
    <script src="{{ asset('js/academy.js') }}" defer></script>
    <script src="{{ asset('js/smooth-scroll.js') }}" defer></script>
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


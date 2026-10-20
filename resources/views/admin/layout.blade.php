<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow"><title>@yield('title', 'Quản trị — Văn Cường Academy')</title>
    <link rel="icon" href="{{ asset('favicon.png') }}"><link rel="stylesheet" href="{{ asset('css/academy.css') }}">
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
    <script src="{{ asset('js/admin.js') }}?v={{ filemtime(public_path('js/admin.js')) }}" defer></script>
</head>
<body class="admin-shell">
    @include('admin.sidebar')
    <main class="admin-main">
        <header class="admin-topbar"><div><p class="admin-breadcrumb">VĂN CƯỜNG MAKEUP & ACADEMY / ADMIN</p><h1>@yield('page_heading', 'Quản trị nội dung')</h1></div><div class="admin-user"><span class="admin-user-dot"></span>{{ auth()->user()->name ?? 'Quản trị viên' }}</div></header>
        @if(session('success'))<p class="admin-notice success-notice" role="status">{{ session('success') }}</p>@endif
        @if($errors->any())<div class="admin-notice error-notice" role="alert"><strong>Chưa thể lưu thay đổi</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
    </main>
    @include('admin.dialogs')
</body>
</html>


@inject('vite', 'Illuminate\Foundation\Vite')
@if(request()->routeIs('home') && !$vite->isRunningHot() && is_file(public_path('assets/academy-critical.css')))
    <style data-critical="academy">{!! file_get_contents(public_path('assets/academy-critical.css')) !!}</style>
    @php($pageVite = clone $vite)
    {{ $pageVite->useStyleTagAttributes(['media' => 'print', 'onload' => "this.media='all'"])(['resources/css/academy-public.css', 'resources/js/academy-public.js'], 'assets') }}
    <noscript><link rel="stylesheet" href="{{ $vite->asset('resources/css/academy-public.css', 'assets') }}"></noscript>
@else
    @vite(['resources/css/academy-public.css', 'resources/js/academy-public.js'], 'assets')
@endif

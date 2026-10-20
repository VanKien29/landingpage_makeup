@if($name === 'playback')
    <svg class="control-icon icon-pause" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M9 5v14M15 5v14"/></svg>
    <svg class="control-icon icon-play" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m9 5 10 7-10 7Z"/></svg>
@else
    <svg class="control-icon @if(in_array($name, ['arrow-up-right', 'arrow-right', 'arrow-up'], true)) icon-inline @endif" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
        @switch($name)
            @case('grid')<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>@break
            @case('list')<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18M3 15h18M9 4v16"/>@break
            @case('edit')<path d="m14 5 5 5M4 20l5-1 11-11a3.5 3.5 0 0 0-5-5L4 14l-1 7Z"/>@break
            @case('trash')<path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7M14 10v7"/>@break
            @case('search')<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>@break
            @case('image')<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8" cy="8" r="1.5"/><path d="m3 17 5-5 4 4 4-6 5 7"/>@break
            @case('upload')<path d="M12 16V3m-5 5 5-5 5 5M3 16v5h18v-5"/>@break
            @case('menu')<path d="M4 6h16M4 12h16M4 18h16"/>@break
            @case('previous')<path d="M19 12H5m6-6-6 6 6 6"/>@break
            @case('next')<path d="M5 12h14m-6-6 6 6-6 6"/>@break
            @case('close')<path d="m6 6 12 12M18 6 6 18"/>@break
            @case('zoom-in')<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5M7.5 10.5h6m-3-3v6"/>@break
            @case('zoom-out')<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5M7.5 10.5h6"/>@break
            @case('play')<path d="m9 5 10 7-10 7Z"/>@break
            @case('phone')<path d="M8 3H5a2 2 0 0 0-2 2c0 8.8 7.2 16 16 16a2 2 0 0 0 2-2v-3l-5-2-2 3a13 13 0 0 1-7-7l3-2-2-5Z"/>@break
            @case('location')<path d="M20 10c0 6-8 11-8 11S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>@break
            @case('facebook')<path d="M14 21v-8h3l.5-4H14V7c0-1 .5-2 2-2h2V1.5A22 22 0 0 0 15 1c-3 0-5 2-5 5v3H7v4h3v8"/>@break
            @case('gift')<path d="M20 12v8H4v-8M2 8h20v4H2zM12 8v12M12 8H8.5A2.5 2.5 0 1 1 11 5.5V8Zm0 0h3.5A2.5 2.5 0 1 0 12 5.5V8Z"/>@break
            @case('check')<path d="m5 12 4 4L19 6"/>@break
            @case('settings')<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-1.4 1.4-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6v.2h-2v-.2a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1-1.4-1.4.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.6-1H7v-2h.2a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1 1.4-1.4.1.1a1.7 1.7 0 0 0 1.9.3 1.7 1.7 0 0 0 1-1.6V5h2v.2a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1 1.4 1.4-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1h.2v2h-.2a1.7 1.7 0 0 0-1.6 1Z"/>@break
            @case('plus')<path d="M12 5v14M5 12h14"/>@break
            @case('arrow-up-right')<path d="M5 19 19 5M9 5h10v10"/>@break
            @case('arrow-right')<path d="M5 12h14m-6-6 6 6-6 6"/>@break
            @case('arrow-up')<path d="M12 19V5m-6 6 6-6 6 6"/>@break
        @endswitch
    </svg>
@endif

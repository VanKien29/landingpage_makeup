@if ($paginator->total() > 0)
    <nav class="admin-pagination" aria-label="Phân trang">
        <p>Hiển thị <strong>{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</strong> / {{ $paginator->total() }} kết quả</p>
        @if ($paginator->hasPages())
            <div class="admin-page-links">
                @if ($paginator->onFirstPage())<span aria-disabled="true" aria-label="Trang trước">@include('partials.icon', ['name' => 'previous'])</span>
                @else<a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Trang trước">@include('partials.icon', ['name' => 'previous'])</a>@endif
                @foreach ($elements as $element)
                    @if (is_string($element))<span class="admin-page-gap">{{ $element }}</span>
                    @else
                        @foreach ($element as $page => $url)
                            @if ($page === $paginator->currentPage())<span aria-current="page">{{ $page }}</span>
                            @else<a href="{{ $url }}" aria-label="Trang {{ $page }}">{{ $page }}</a>@endif
                        @endforeach
                    @endif
                @endforeach
                @if ($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Trang sau">@include('partials.icon', ['name' => 'next'])</a>
                @else<span aria-disabled="true" aria-label="Trang sau">@include('partials.icon', ['name' => 'next'])</span>@endif
            </div>
        @endif
    </nav>
@endif

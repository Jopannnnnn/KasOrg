<nav aria-label="Paginasi data" class="d-flex flex-wrap align-items-center justify-content-between gap-3 border-top p-3 ko-text-xs">
    <span>Menampilkan {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} dari {{ $paginator->total() }} data</span>
    <ul class="pagination pagination-sm flex-wrap mb-0">
        <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
            @if ($paginator->onFirstPage())<span class="page-link" aria-disabled="true">Sebelumnya</span>
            @else<a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a>@endif
        </li>
        @foreach ($elements as $element)
            @if (is_string($element))<li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
            @else
                @foreach ($element as $page => $url)
                    <li class="page-item {{ $page === $paginator->currentPage() ? 'active' : '' }}">
                        @if ($page === $paginator->currentPage())<span class="page-link" aria-current="page">{{ $page }}</span>
                        @else<a class="page-link" href="{{ $url }}" aria-label="Halaman {{ $page }}">{{ $page }}</a>@endif
                    </li>
                @endforeach
            @endif
        @endforeach
        <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
            @if ($paginator->hasMorePages())<a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a>
            @else<span class="page-link" aria-disabled="true">Berikutnya</span>@endif
        </li>
    </ul>
</nav>

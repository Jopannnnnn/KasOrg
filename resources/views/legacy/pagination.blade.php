<nav aria-label="Paginasi data" class="flex flex-col items-center justify-between gap-3 border-t border-slate-100 p-4 text-xs text-slate-500 sm:flex-row">
    <span>Menampilkan {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} dari {{ $paginator->total() }} data</span>
    <div class="inline-flex flex-wrap items-center gap-1">
        @if ($paginator->onFirstPage())
            <span aria-disabled="true" class="rounded-lg border border-slate-200 px-3 py-1.5 text-slate-400">Sebelumnya</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="rounded-lg border border-slate-200 px-3 py-1.5 text-slate-600 hover:bg-slate-50">Sebelumnya</a>
        @endif
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2">{{ $element }}</span>
            @else
                @foreach ($element as $page => $url)
                    @if ($page === $paginator->currentPage())
                        <span aria-current="page" class="rounded-lg bg-brand-600 px-3 py-1.5 font-bold text-white">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" aria-label="Halaman {{ $page }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-slate-600 hover:bg-slate-50">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="rounded-lg border border-slate-200 px-3 py-1.5 text-slate-600 hover:bg-slate-50">Berikutnya</a>
        @else
            <span aria-disabled="true" class="rounded-lg border border-slate-200 px-3 py-1.5 text-slate-400">Berikutnya</span>
        @endif
    </div>
</nav>

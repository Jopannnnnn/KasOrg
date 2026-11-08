<div class="table-responsive">
    <div class="d-flex ko-h-56 align-items-end justify-content-between ko-gap-4 border-bottom ko-border-slate-100 ko-px-2 ko-pt-4" style="min-width: {{ max(1, count($summary['bars'])) * 64 }}px" role="img" aria-label="Pemasukan dan pengeluaran bulanan untuk {{ $scope['label'] }}">
        @foreach ($summary['bars'] as $bar)
            <div class="d-flex h-100 flex-grow-1 flex-column align-items-center justify-content-end ko-gap-2">
                <div class="d-flex h-100 w-100 ko-max-w--42px- align-items-end justify-content-center ko-gap-1-5">
                    <div title="Pemasukan {{ \App\Services\RingkasanKasService::rupiah($bar['income']) }}" class="ko-w-3-5 ko-rounded-t-md ko-bg-brand-600" style="height: {{ $bar['income_height'] }}%"></div>
                    <div title="Pengeluaran {{ \App\Services\RingkasanKasService::rupiah($bar['expense']) }}" class="ko-w-3-5 ko-rounded-t-md ko-bg-blue-100" style="height: {{ $bar['expense_height'] }}%"></div>
                </div>
                <span class="text-nowrap ko-text--10px- fw-semibold ko-text-slate-400">{{ $bar['month'] }}</span>
            </div>
        @endforeach
    </div>
</div>
@if ($summary['count'] === 0)<p class="ko-mt-4 ko-text-xs ko-text-slate-500">Belum ada transaksi kas sah pada periode ini.</p>@endif

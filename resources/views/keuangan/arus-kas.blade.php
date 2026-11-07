<div class="overflow-x-auto">
    <div class="flex h-56 items-end justify-between gap-4 border-b border-slate-100 px-2 pt-4" style="min-width: {{ max(1, count($summary['bars'])) * 64 }}px" role="img" aria-label="Pemasukan dan pengeluaran bulanan untuk {{ $scope['label'] }}">
        @foreach ($summary['bars'] as $bar)
            <div class="flex h-full flex-1 flex-col items-center justify-end gap-2">
                <div class="flex h-full w-full max-w-[42px] items-end justify-center gap-1.5">
                    <div title="Pemasukan {{ \App\Services\RingkasanKasService::rupiah($bar['income']) }}" class="w-3.5 rounded-t-md bg-brand-600" style="height: {{ $bar['income_height'] }}%"></div>
                    <div title="Pengeluaran {{ \App\Services\RingkasanKasService::rupiah($bar['expense']) }}" class="w-3.5 rounded-t-md bg-blue-100" style="height: {{ $bar['expense_height'] }}%"></div>
                </div>
                <span class="whitespace-nowrap text-[10px] font-semibold text-slate-400">{{ $bar['month'] }}</span>
            </div>
        @endforeach
    </div>
</div>
@if ($summary['count'] === 0)<p class="mt-4 text-xs text-slate-500">Belum ada transaksi kas sah pada periode ini.</p>@endif

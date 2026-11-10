<section class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <div><h3 class="text-base font-extrabold text-slate-900">{{ $allocationHeading ?? 'Penggunaan dana' }}</h3><p class="mb-6 text-xs text-slate-500">Alokasi pengeluaran pada periode terpilih</p>
        @php($colors = ['#234578', '#60a5fa', '#93c5fd', '#bfdbfe', '#172554', '#345A9C'])
        <div class="relative my-4 mx-auto h-40 w-40">
            <svg viewBox="0 0 160 160" role="img" aria-label="Alokasi pengeluaran berdasarkan kategori" class="h-40 w-40 -rotate-90">
                <circle cx="80" cy="80" r="70" fill="none" stroke="#e2e8f0" stroke-width="20" />
                @php($offset = '0')
                @foreach ($summary['allocations'] as $allocation)
                    <circle cx="80" cy="80" r="70" pathLength="100" fill="none" stroke="{{ $colors[$loop->index % count($colors)] }}" stroke-width="20" stroke-dasharray="{{ $allocation['percentage'] }} {{ bcsub('100', $allocation['percentage'], 4) }}" stroke-dashoffset="-{{ $offset }}"><title>{{ $allocation['name'] }}: {{ \App\Services\RingkasanKasService::rupiah($allocation['amount']) }}</title></circle>
                    @php($offset = bcadd($offset, $allocation['percentage'], 4))
                @endforeach
            </svg>
            <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center px-5 text-center"><span class="text-[11px] font-bold text-slate-400">Total</span><span class="max-w-full break-words text-xs font-extrabold text-slate-800">{{ \App\Services\RingkasanKasService::rupiah($summary['expense']) }}</span></div>
        </div>
    </div>
    <div class="space-y-3 text-xs font-semibold text-slate-700">
        @forelse ($summary['allocations'] as $allocation)
            <div data-allocation-name="{{ $allocation['name'] }}" data-allocation-amount="{{ $allocation['amount'] }}" data-allocation-percentage="{{ $allocation['percentage'] }}" class="flex items-center justify-between gap-2"><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $colors[$loop->index % count($colors)] }}"></span>{{ $allocation['name'] }}</span><span class="font-bold">{{ bcadd($allocation['percentage'], '0', 2) }}%</span></div>
            <p class="text-right text-[11px] text-slate-500">{{ \App\Services\RingkasanKasService::rupiah($allocation['amount']) }}</p>
        @empty
            <p class="text-center font-medium text-slate-500">Belum ada pengeluaran pada periode ini.</p>
        @endforelse
    </div>
</section>

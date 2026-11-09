@php
    // Hanya koordinat SVG. Nilai uang dan skala bersumber dari service bersama.
    $chart = [];
    $incomePoints = [];
    $expensePoints = [];
    $length = count($summary['bars']);
    foreach ($summary['bars'] as $index => $bar) {
        $x = $length === 1 ? '250' : bcadd('20', bcdiv(bcmul((string) $index, '460', 6), (string) ($length - 1), 4), 4);
        $incomeY = bcsub('130', bcdiv(bcmul($bar['income_height'], '110', 8), '100', 4), 4);
        $expenseY = bcsub('130', bcdiv(bcmul($bar['expense_height'], '110', 8), '100', 4), 4);
        $chart[] = $bar + ['x' => $x, 'income_y' => $incomeY, 'expense_y' => $expenseY];
        $incomePoints[] = $x.','.$incomeY;
        $expensePoints[] = $x.','.$expenseY;
    }
@endphp
<div class="mb-4 flex flex-wrap items-center gap-4 text-xs font-semibold text-slate-500">
    <span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-brand-600"></span>Pemasukan</span>
    <span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>Pengeluaran</span>
</div>
<div class="overflow-x-auto">
    <div style="min-width: {{ max(1, $length) * 72 }}px">
        <svg id="grafik-laporan" viewBox="0 0 500 150" role="img" aria-label="Pemasukan dan pengeluaran bulanan untuk {{ $scope['label'] }}" class="h-44 w-full overflow-visible">
            <line x1="20" y1="130" x2="480" y2="130" stroke="#e2e8f0" />
            <polyline data-series="income" fill="none" stroke="#234578" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" points="{{ implode(' ', $incomePoints) }}" />
            <polyline data-series="expense" fill="none" stroke="#f43f5e" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" points="{{ implode(' ', $expensePoints) }}" />
            @foreach ($chart as $point)
                <circle data-month="{{ $point['month'] }}" data-income="{{ $point['income'] }}" cx="{{ $point['x'] }}" cy="{{ $point['income_y'] }}" r="5" fill="#fff" stroke="#234578" stroke-width="3"><title>Pemasukan {{ $point['month'] }}: {{ \App\Services\RingkasanKasService::rupiah($point['income']) }}</title></circle>
                <circle data-month="{{ $point['month'] }}" data-expense="{{ $point['expense'] }}" cx="{{ $point['x'] }}" cy="{{ $point['expense_y'] }}" r="5" fill="#fff" stroke="#f43f5e" stroke-width="3"><title>Pengeluaran {{ $point['month'] }}: {{ \App\Services\RingkasanKasService::rupiah($point['expense']) }}</title></circle>
            @endforeach
        </svg>
        <div class="grid gap-2 border-t border-slate-100 pt-4 text-center text-[10px] font-semibold text-slate-400" style="grid-template-columns: repeat({{ max(1, $length) }}, minmax(0, 1fr))">
            @foreach ($chart as $point)<span>{{ $point['month'] }}<span class="mt-1 block text-emerald-700">{{ \App\Services\RingkasanKasService::rupiah($point['income']) }}</span><span class="block text-rose-600">{{ \App\Services\RingkasanKasService::rupiah($point['expense']) }}</span></span>@endforeach
        </div>
    </div>
</div>
@if ($summary['count'] === 0)<p class="mt-4 text-xs text-slate-500">Belum ada transaksi kas sah pada periode ini.</p>@endif

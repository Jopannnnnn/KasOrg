<form method="GET" class="flex flex-wrap items-center gap-2 self-start rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 shadow-sm sm:self-auto">
    <i data-lucide="calendar" class="h-4 w-4 text-slate-500"></i>
    <label for="periode-kas" class="sr-only">Periode keuangan</label>
    <select id="periode-kas" name="periode_id" class="max-w-full rounded-lg border-0 bg-white py-1 text-xs focus:ring-2 focus:ring-brand-600">
        @if ($scope['periods']->isNotEmpty())<option value="semua" @selected($scope['selected'] === 'semua')>Semua periode</option>@endif
        @forelse ($scope['periods'] as $period)
            <option value="{{ $period->id }}" @selected((string) $scope['selected'] === (string) $period->id)>{{ $period->tanggal_mulai->locale('id')->translatedFormat('F Y') }} · {{ ucfirst($period->status) }}</option>
        @empty
            <option value="">Belum ada periode</option>
        @endforelse
    </select>
    @if (isset($filters))
        @if (($filters['q'] ?? '') !== '')<input type="hidden" name="q" value="{{ $filters['q'] }}">@endif
        <label for="jenis-kas" class="sr-only">Jenis transaksi pada tabel</label>
        <select id="jenis-kas" name="jenis" class="rounded-lg border border-slate-200 bg-white py-1 text-xs"><option value="">Semua jenis</option><option value="pemasukan" @selected(($filters['jenis'] ?? '') === 'pemasukan')>Pemasukan</option><option value="pengeluaran" @selected(($filters['jenis'] ?? '') === 'pengeluaran')>Pengeluaran</option></select>
    @endif
    <button type="submit" class="rounded-lg bg-brand-50 px-3 py-1.5 text-brand-700 hover:bg-brand-100">Terapkan</button>
</form>

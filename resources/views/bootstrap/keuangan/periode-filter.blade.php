<form method="GET" class="ko-period-filter d-flex flex-wrap align-items-center ko-gap-2 align-self-start ko-radius-control border ko-border-slate-200 bg-white ko-px-3 ko-py-2 ko-text-xs fw-bold ko-text-slate-700 shadow-sm ko-sm-self-auto">
    <i data-lucide="calendar" class="ko-h-4 ko-w-4 ko-text-slate-500"></i>
    <label for="periode-kas" class="form-label visually-hidden">Periode keuangan</label>
    <select id="periode-kas" name="periode_id" class="form-select mw-100 ko-radius-small border-0 bg-white ko-py-1 ko-text-xs ko-focus-ring-2 ko-focus-ring-brand-600">
        @if ($scope['periods']->isNotEmpty())<option value="semua" @selected($scope['selected'] === 'semua')>Semua periode</option>@endif
        @forelse ($scope['periods'] as $period)
            <option value="{{ $period->id }}" @selected((string) $scope['selected'] === (string) $period->id)>{{ $period->tanggal_mulai->locale('id')->translatedFormat('F Y') }} · {{ ucfirst($period->status) }}</option>
        @empty
            <option value="">Belum ada periode</option>
        @endforelse
    </select>
    @if (isset($filters))
        @if (($filters['q'] ?? '') !== '')<input type="hidden" name="q" value="{{ $filters['q'] }}">@endif
        <label for="jenis-kas" class="form-label visually-hidden">Jenis transaksi pada tabel</label>
        <select id="jenis-kas" name="jenis" class="form-select ko-radius-small border ko-border-slate-200 bg-white ko-py-1 ko-text-xs"><option value="">Semua jenis</option><option value="pemasukan" @selected(($filters['jenis'] ?? '') === 'pemasukan')>Pemasukan</option><option value="pengeluaran" @selected(($filters['jenis'] ?? '') === 'pengeluaran')>Pengeluaran</option></select>
    @endif
    <button type="submit" class="btn ko-button ko-radius-small ko-bg-brand-50 ko-px-3 ko-py-1-5 ko-text-brand-700 ko-hover-bg-brand-100">Terapkan</button>
</form>

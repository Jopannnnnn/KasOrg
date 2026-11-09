{{-- Varian ringkas khusus Laporan; filter bersama halaman lain tidak diubah. --}}
<form id="filter-periode-laporan" method="GET" class="flex min-w-0 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 shadow-sm">
    <i data-lucide="calendar" class="h-4 w-4 shrink-0 text-slate-500"></i>
    <label for="periode-kas" class="sr-only">Periode keuangan</label>
    <select id="periode-kas" name="periode_id" class="min-w-0 flex-1 rounded-lg border-0 bg-white py-1 text-xs focus:ring-2 focus:ring-brand-600 sm:max-w-52 sm:flex-none">
        @if ($scope['periods']->isNotEmpty())<option value="semua" @selected($scope['selected'] === 'semua')>Semua periode</option>@endif
        @forelse ($scope['periods'] as $period)
            <option value="{{ $period->id }}" @selected((string) $scope['selected'] === (string) $period->id)>{{ $period->tanggal_mulai->locale('id')->translatedFormat('F Y') }} · {{ ucfirst($period->status) }}</option>
        @empty
            <option value="">Belum ada periode</option>
        @endforelse
    </select>
    <button type="submit" class="shrink-0 whitespace-nowrap rounded-lg bg-brand-50 px-3 py-1.5 text-brand-700 hover:bg-brand-100">Terapkan</button>
</form>

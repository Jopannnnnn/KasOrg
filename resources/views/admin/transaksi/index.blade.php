@extends('layouts.app', ['title' => 'Transaksi Kas | KasOrg', 'heading' => 'Transaksi Kas', 'role' => 'admin'])
@section('content')
<main class="flex-1 space-y-8 overflow-y-auto p-6 md:p-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div><h2 class="text-2xl font-extrabold tracking-tight text-slate-900">Transaksi kas</h2><p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Riwayat pemasukan dan pengeluaran organisasi dari database.</p></div>
        <button id="tambahTransaksi" type="button" data-open-modal="modal-transaksi-tambah" @disabled($openPeriods->isEmpty() || $categories->isEmpty()) class="inline-flex self-start items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-brand-600/20 hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50 sm:self-auto sm:text-sm"><i data-lucide="plus" class="h-4 w-4"></i> Tambah transaksi</button>
    </div>
    <x-keuangan-feedback />
    <p role="note" class="rounded-xl border border-blue-100 bg-brand-50 px-4 py-3 text-xs text-brand-700">Transaksi manual hanya dapat dikelola pada satu periode terbuka yang sesuai tanggal. Transaksi otomatis pembayaran dilindungi dan hanya dicatat melalui verifikasi Admin. Hapus mengarsipkan transaksi, bukan menghapus riwayat permanen.</p>
    @if ($openPeriods->isEmpty() || $categories->isEmpty())<p class="text-sm text-slate-500">Tambah transaksi memerlukan periode terbuka dan kategori transaksi yang tersedia.</p>@endif
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div><span class="text-xs font-semibold text-slate-500">Pemasukan periode ini</span><div class="mt-1 text-2xl font-extrabold text-slate-900">{{ \App\Services\RingkasanKasService::rupiah($summary['income']) }}</div></div><div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i data-lucide="arrow-down-left" class="h-5 w-5"></i></div></div>
        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div><span class="text-xs font-semibold text-slate-500">Pengeluaran periode ini</span><div class="mt-1 text-2xl font-extrabold text-slate-900">{{ \App\Services\RingkasanKasService::rupiah($summary['expense']) }}</div></div><div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-600"><i data-lucide="arrow-up-right" class="h-5 w-5"></i></div></div>
        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div><span class="text-xs font-semibold text-slate-500">Selisih arus kas</span><div class="mt-1 text-2xl font-extrabold text-slate-900">{{ \App\Services\RingkasanKasService::rupiah(bcsub($summary['income'], $summary['expense'], 2)) }}</div></div><div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-brand-600"><i data-lucide="trending-up" class="h-5 w-5"></i></div></div>
    </div>
    @include('keuangan.periode-filter')
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <form method="GET" class="flex flex-col items-center justify-between gap-3 border-b border-slate-100 p-4 sm:flex-row sm:p-5">
            @if ($scope['selected'] !== null)<input type="hidden" name="periode_id" value="{{ $scope['selected'] }}">@endif
            @if ($filters['jenis'] ?? null)<input type="hidden" name="jenis" value="{{ $filters['jenis'] }}">@endif
            <label class="relative w-full sm:w-80"><span class="sr-only">Cari transaksi atau kategori</span><i data-lucide="search" class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i><input id="cariTransaksi" name="q" type="search" maxlength="100" value="{{ $filters['q'] ?? '' }}" placeholder="Cari transaksi atau keterangan..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-xs transition focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-600 sm:text-sm"></label>
            <button type="submit" class="rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50">Cari</button>
        </form>
        <p class="px-4 py-3 text-xs text-slate-500">Filter jenis dan pencarian membatasi tabel; ringkasan tetap menunjukkan seluruh arus kas periode terpilih.</p>
        <div class="overflow-x-auto"><table class="w-full border-collapse text-left text-xs sm:text-sm"><thead><tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-bold uppercase tracking-wider text-slate-400"><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Jenis</th><th class="px-4 py-3">Kategori</th><th class="px-4 py-3">Keterangan</th><th class="px-4 py-3 text-right">Nominal</th><th class="px-4 py-3 text-right">Aksi</th></tr></thead><tbody id="daftarTransaksi" class="divide-y divide-slate-100 text-slate-700">
            @forelse ($transactions as $transaction)
                <tr data-transaction-id="{{ $transaction['id'] }}" class="transition hover:bg-slate-50/80"><td class="px-4 py-4 font-medium text-slate-600">{{ $transaction['date'] }}</td><td class="px-4 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $transaction['positive'] ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">{{ $transaction['type'] }}</span></td><td class="px-4 py-4 font-semibold text-slate-900">{{ $transaction['category'] }}</td><td class="px-4 py-4 text-slate-500">{{ $transaction['description'] }}</td><td class="px-4 py-4 text-right font-bold {{ $transaction['positive'] ? 'text-emerald-600' : 'text-rose-600' }}">{{ $transaction['amount'] }}</td><td class="px-4 py-4 text-right">
                    @if ($transaction['manageable'])
                        <div class="inline-flex items-center gap-2"><button type="button" data-open-modal="modal-transaksi-edit-{{ $transaction['id'] }}" aria-label="Edit transaksi {{ $transaction['id'] }}" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 focus-visible:ring-2 focus-visible:ring-brand-600"><i data-lucide="pencil" class="h-3.5 w-3.5"></i></button><button type="button" data-open-modal="modal-transaksi-hapus-{{ $transaction['id'] }}" aria-label="Hapus transaksi {{ $transaction['id'] }}" class="rounded-lg p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600 focus-visible:ring-2 focus-visible:ring-brand-600"><i data-lucide="trash-2" class="h-3.5 w-3.5"></i></button></div>
                    @else
                        <span class="text-xs text-slate-400">{{ $transaction['source'] === 'pembayaran_iuran' ? 'Otomatis · dilindungi' : 'Periode terkunci' }}</span>
                    @endif
                </td></tr>
            @empty
                <tr><td colspan="6" class="py-10 text-center text-sm text-slate-500">Tidak ada transaksi kas sah yang sesuai filter.</td></tr>
            @endforelse
        </tbody></table></div>
        {{ $transactions->links('admin.anggota.pagination') }}
    </section>
</main>
<x-keuangan-modal id="modal-transaksi-tambah" title="Tambah transaksi kas">
    @include('admin.transaksi.form', ['modalId' => 'modal-transaksi-tambah', 'record' => null, 'periodId' => $openPeriods->firstWhere('id', $scope['selected'])?->id ?? $openPeriods->first()?->id])
</x-keuangan-modal>
@foreach ($transactions as $transaction)
    @if ($transaction['manageable'])
        <x-keuangan-modal :id="'modal-transaksi-edit-'.$transaction['id']" title="Edit transaksi kas">
            @include('admin.transaksi.form', ['modalId' => 'modal-transaksi-edit-'.$transaction['id'], 'record' => $transaction['record'], 'periodId' => $transaction['period_id']])
        </x-keuangan-modal>
        <x-keuangan-modal :id="'modal-transaksi-hapus-'.$transaction['id']" title="Hapus transaksi manual">
            <form method="POST" action="{{ route('admin.transaksi.destroy', ['transaksi' => $transaction['id']] + $filters) }}" class="space-y-4">
                @csrf @method('DELETE')
                <input type="hidden" name="_modal" value="modal-transaksi-hapus-{{ $transaction['id'] }}">
                <p class="text-sm text-slate-600">Arsipkan transaksi <strong>{{ $transaction['description'] }}</strong> sebesar <strong>{{ $transaction['amount'] }}</strong> pada {{ $transaction['date'] }}? Transaksi tidak lagi dihitung dalam saldo aktif. Riwayat tersimpan.</p>
                <label class="flex items-start gap-3 text-sm"><input name="konfirmasi" type="checkbox" value="1" required class="mt-1 rounded border-slate-300 accent-brand-600"><span>Saya mengonfirmasi penghapusan transaksi manual ini.</span></label>
                <div class="flex justify-end gap-3"><button type="button" data-close-modal class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold">Batal</button><button type="submit" class="rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-rose-700">Hapus transaksi</button></div>
            </form>
        </x-keuangan-modal>
    @endif
@endforeach
@endsection
@include('keuangan.scripts')
@push('scripts')
<script>
document.querySelectorAll('[data-manual-transaction-form]').forEach(form => {
    const type = form.querySelector('[name="jenis"]'), category = form.querySelector('[name="kategori_transaksi_id"]');
    const period = form.querySelector('[name="periode_id"]'), date = form.querySelector('[name="tanggal"]');
    const filterCategories = () => {
        [...category.options].forEach(option => {
            if (!option.value) return;
            option.hidden = option.disabled = option.dataset.jenis !== type.value;
        });
        if (category.selectedOptions[0]?.disabled) category.value = '';
    };
    const constrainDate = () => {
        const option = period.selectedOptions[0];
        date.min = option?.dataset.start || '';
        date.max = option?.dataset.end && option.dataset.end < date.dataset.today ? option.dataset.end : date.dataset.today;
    };
    type.addEventListener('change', filterCategories); period.addEventListener('change', constrainDate);
    filterCategories(); constrainDate();
});
</script>
@endpush

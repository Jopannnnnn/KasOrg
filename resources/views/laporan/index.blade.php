@extends('layouts.app', ['title' => 'Laporan Keuangan | KasOrg', 'heading' => 'Laporan', 'role' => $role])

@section('content')
<main id="laporan-keuangan" class="flex-1 space-y-8 overflow-y-auto p-6 md:p-8">
    <div id="header-laporan" class="flex flex-wrap items-center justify-between gap-4">
        <div class="min-w-0 flex-1 basis-80"><h2 class="text-2xl font-extrabold tracking-tight text-slate-900">Laporan keuangan</h2><p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Rekapitulasi kondisi keuangan organisasi secara lengkap dan transparan.</p></div>
        <div id="aksi-laporan" class="flex w-full flex-col gap-3 sm:ml-auto sm:w-auto sm:shrink-0 sm:flex-row sm:items-center">
            @include('laporan.periode-filter')
            <form method="GET" action="{{ route($role.'.laporan.pdf') }}" class="shrink-0">
                @if ($scope['selected'] !== null)<input type="hidden" name="periode_id" value="{{ $scope['selected'] }}">@endif
                <button id="unduhLaporan" type="submit" aria-describedby="status-pdf" class="inline-flex w-full items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-brand-600 px-4 py-2 text-xs font-bold text-white shadow-md shadow-brand-600/20 transition hover:bg-brand-700 sm:w-auto sm:text-sm"><i data-lucide="download" class="h-4 w-4 shrink-0"></i>Unduh PDF</button>
                <span id="status-pdf" class="sr-only">Unduh PDF berisi seluruh transaksi sah pada periode yang sedang ditampilkan, bukan hanya halaman tabel ini.</span>
            </form>
        </div>
    </div>
    <x-keuangan-feedback />
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['awal', 'opening', 'Saldo awal', 'wallet-cards', 'bg-slate-50 text-slate-600'],
            ['masuk', 'income', 'Pemasukan', 'arrow-down-left', 'bg-emerald-50 text-emerald-600'],
            ['keluar', 'expense', 'Pengeluaran', 'arrow-up-right', 'bg-rose-50 text-rose-600'],
            ['akhir', 'closing', 'Saldo akhir', 'trending-up', 'bg-blue-50 text-brand-600'],
        ] as [$stat, $key, $label, $icon, $color])
            <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="min-w-0"><span class="text-xs font-semibold text-slate-500">{{ $label }}</span><div data-stat="{{ $stat }}" data-amount="{{ $summary[$key] }}" class="mt-1 break-words text-2xl font-extrabold text-slate-900">{{ \App\Services\RingkasanKasService::rupiah($summary[$key]) }}</div></div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $color }}"><i data-lucide="{{ $icon }}" class="h-5 w-5"></i></div>
            </div>
        @endforeach
    </div>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            <h3 class="text-base font-extrabold text-slate-900">Arus kas per bulan</h3>
            <p class="mb-6 text-xs text-slate-500">Pemasukan dan pengeluaran pada rentang periode terpilih, berdasarkan tanggal transaksi.</p>
            @include('laporan.arus-kas')
        </section>
        @include('keuangan.alokasi-pengeluaran', ['allocationHeading' => 'Alokasi pengeluaran'])
    </div>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 p-6"><h3 class="text-base font-extrabold text-slate-900">Daftar transaksi kas</h3><p class="mt-1 text-xs text-slate-500">Transaksi sah pada periode terpilih; tagihan dan pengajuan yang belum disetujui bukan pemasukan kas.</p></div>
        @include('keuangan.tabel-kas')
        {{ $transactions->links('admin.anggota.pagination') }}
    </section>
</main>
@endsection

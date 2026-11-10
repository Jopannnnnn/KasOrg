@extends('layouts.app', ['title' => 'Transparansi Kas | KasOrg', 'heading' => 'Transparansi Kas', 'role' => $role])
@section('content')
<main class="flex-1 space-y-8 overflow-y-auto p-6 md:p-8">
    @include('keuangan.periode-filter')
    <x-keuangan-feedback />
    <section class="flex flex-col justify-between gap-6 rounded-3xl bg-brand-600 p-6 text-white shadow-lg shadow-brand-600/20 sm:p-8 md:flex-row md:items-center">
        <div><span class="text-xs font-semibold text-blue-100">Saldo kas · {{ $scope['label'] }}</span><div class="mt-1 text-3xl font-extrabold tracking-tight sm:text-4xl">{{ \App\Services\RingkasanKasService::rupiah($summary['closing']) }}</div><div class="mt-2 flex items-center gap-1.5 text-xs text-blue-100"><i data-lucide="clock" class="h-3.5 w-3.5"></i>{{ $summary['updatedAt'] ? 'Transaksi terakhir diperbarui '.\Carbon\CarbonImmutable::parse($summary['updatedAt'])->format('d/m/Y H:i') : 'Belum ada transaksi kas sah' }}</div></div>
        <div class="flex items-center gap-6 border-t border-white/20 pt-4 md:border-l md:border-t-0 md:pl-8 md:pt-0"><div><span class="text-xs font-medium text-blue-100">Pemasukan</span><div class="mt-0.5 text-lg font-extrabold text-white">+ {{ \App\Services\RingkasanKasService::rupiah($summary['income']) }}</div></div><div><span class="text-xs font-medium text-blue-100">Pengeluaran</span><div class="mt-0.5 text-lg font-extrabold text-white">− {{ \App\Services\RingkasanKasService::rupiah($summary['expense']) }}</div></div></div>
    </section>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        @include('keuangan.alokasi-pengeluaran')
        <section class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            <div><div class="mb-4 flex items-center justify-between"><div><h3 class="text-base font-extrabold text-slate-900">Riwayat kas organisasi</h3><p class="text-xs text-slate-500">Hanya transaksi kas sah. Filter jenis berlaku pada tabel; ringkasan tetap mencakup seluruh arus kas periode.</p></div></div>
                @include('keuangan.tabel-kas')
            </div>
            {{ $transactions->links('admin.anggota.pagination') }}
        </section>
    </div>
</main>
@endsection

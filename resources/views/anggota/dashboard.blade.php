@extends('layouts.app', [
    'title' => 'Dashboard Anggota - KasOrg',
    'heading' => 'Dashboard',
    'role' => 'anggota',
])

@section('body-class', 'ko-anggota-dashboard')
@push('styles')
    <link href="{{ asset('css/kasorg-anggota-dashboard.css') }}" rel="stylesheet">
@endpush

@section('content')
    <main class="ko-anggota-content flex-grow-1 ko-space-y-8 overflow-y-auto ko-p-6 ko-md-p-8">
        <div class="d-flex flex-column justify-content-between ko-gap-4 ko-sm-flex-row ko-sm-items-center">
            <div>
                <h2 class="ko-text-2xl ko-fw-heavy ko-tracking-tight ko-text-slate-900">Selamat datang, {{ auth()->user()->name }}!</h2>
                <p class="ko-mt-0-5 ko-text-xs ko-text-slate-500 ko-sm-text-sm">Berikut ringkasan kondisi keuangan organisasi pada periode terpilih.</p>
            </div>
            @include('bootstrap.keuangan.periode-filter')
        </div>
        <x-bootstrap.feedback />

        <section class="ko-anggota-stats row g-3" aria-label="Ringkasan keuangan anggota">
            <div class="col-12 col-sm-6 col-lg-3"><article class="card h-100 ko-radius-card border ko-border-slate-200 bg-white ko-p-5 shadow-sm">
                <div class="ko-mb-3 d-flex align-items-center justify-content-between"><span class="ko-text-xs fw-semibold ko-text-slate-500">Total saldo kas</span><span class="d-flex ko-h-8 ko-w-8 align-items-center justify-content-center ko-radius-control ko-bg-blue-50 ko-text-brand-600"><i data-lucide="wallet" class="ko-h-4 ko-w-4"></i></span></div>
                <p class="ko-text-2xl ko-fw-heavy ko-text-slate-900">{{ \App\Services\RingkasanKasService::rupiah($summary['closing']) }}</p>
                <p class="ko-mt-2 ko-text-xs fw-medium ko-text-slate-400">Berdasarkan data database</p>
            </article></div>
            <div class="col-12 col-sm-6 col-lg-3"><article class="card h-100 ko-radius-card border ko-border-slate-200 bg-white ko-p-5 shadow-sm">
                <div class="ko-mb-3 d-flex align-items-center justify-content-between"><span class="ko-text-xs fw-semibold ko-text-slate-500">Pemasukan periode ini</span><span class="d-flex ko-h-8 ko-w-8 align-items-center justify-content-center ko-radius-control ko-bg-emerald-50 ko-text-emerald-600"><i data-lucide="arrow-down-left" class="ko-h-4 ko-w-4"></i></span></div>
                <p class="ko-text-2xl ko-fw-heavy ko-text-slate-900">{{ \App\Services\RingkasanKasService::rupiah($summary['income']) }}</p>
                <p class="ko-mt-2 ko-text-xs fw-medium ko-text-slate-400">Berdasarkan data database</p>
            </article></div>
            <div class="col-12 col-sm-6 col-lg-3"><article class="card h-100 ko-radius-card border ko-border-slate-200 bg-white ko-p-5 shadow-sm">
                <div class="ko-mb-3 d-flex align-items-center justify-content-between"><span class="ko-text-xs fw-semibold ko-text-slate-500">Pengeluaran periode ini</span><span class="d-flex ko-h-8 ko-w-8 align-items-center justify-content-center ko-radius-control ko-bg-rose-50 ko-text-rose-600"><i data-lucide="arrow-up-right" class="ko-h-4 ko-w-4"></i></span></div>
                <p class="ko-text-2xl ko-fw-heavy ko-text-slate-900">{{ \App\Services\RingkasanKasService::rupiah($summary['expense']) }}</p>
                <p class="ko-mt-2 ko-text-xs fw-medium ko-text-slate-400">Berdasarkan data database</p>
            </article></div>
            <div class="col-12 col-sm-6 col-lg-3"><article class="card h-100 ko-radius-card border ko-border-slate-200 bg-white ko-p-5 shadow-sm">
                <div class="ko-mb-3 d-flex align-items-center justify-content-between"><span class="ko-text-xs fw-semibold ko-text-slate-500">Status tagihan pribadi</span><span class="d-flex ko-h-8 ko-w-8 align-items-center justify-content-center ko-radius-control ko-bg-amber-50 ko-text-amber-600"><i data-lucide="clock" class="ko-h-4 ko-w-4"></i></span></div>
                <p class="ko-text-2xl ko-fw-heavy ko-text-slate-900">{{ $billStats['total'] === 0 ? 'Belum ada tagihan' : ($billStats['unpaid'] > 0 ? 'Belum lunas' : 'Lunas') }}</p>
                <p class="ko-mt-2 ko-text-xs fw-semibold ko-text-amber-600">{{ $billStats['unpaid'] }} tagihan belum lunas</p>
            </article></div>
        </section>

        <section class="ko-anggota-panels row g-4">
            <div class="col-12 col-lg-8"><article class="card h-100 ko-radius-card border ko-border-slate-200 bg-white ko-p-6 shadow-sm ">
                <div class="ko-mb-6 d-flex align-items-center justify-content-between">
                    <div><h3 class="ko-text-base ko-fw-heavy ko-text-slate-900">Arus kas organisasi</h3><p class="ko-text-xs ko-text-slate-500">Perbandingan pemasukan dan pengeluaran</p></div>
                    <div class="d-flex align-items-center ko-gap-4 ko-text-xs fw-bold"><span class="d-flex align-items-center ko-gap-1-5"><span class="ko-h-2-5 ko-w-2-5 rounded-pill ko-bg-brand-600"></span>Pemasukan</span><span class="d-flex align-items-center ko-gap-1-5"><span class="ko-h-2-5 ko-w-2-5 rounded-pill ko-bg-blue-100"></span>Pengeluaran</span></div>
                </div>
                @include('bootstrap.keuangan.arus-kas')
            </article></div>

            <div class="col-12 col-lg-4"><article class="card h-100 d-flex flex-column justify-content-between ko-radius-card border ko-border-amber-200-60 ko-bg-amber-50-50 ko-p-6 shadow-sm">
                <div>
                    <span class="ko-mb-4 d-flex ko-h-10 ko-w-10 align-items-center justify-content-center ko-radius-control ko-bg-amber-100 ko-text-amber-700"><i data-lucide="clock" class="ko-h-5 ko-w-5"></i></span>
                    <h3 class="ko-text-base ko-fw-heavy ko-text-slate-900">{{ $billStats['unpaid'] }} tagihan belum lunas</h3>
                    <p class="ko-mt-2 ko-text-xs ko-leading-relaxed ko-text-slate-600">Ada <strong>{{ $billStats['waiting'] }}</strong> pembayaran pribadi menunggu verifikasi pada periode terpilih.</p>
                    <div class="ko-mt-4 ko-radius-control border ko-border-amber-200-50 ko-bg-white-80 ko-p-3 ko-text-xs ko-text-slate-600"><a href="{{ route('anggota.tagihan') }}" class="fw-bold ko-text-brand-700 text-decoration-underline">Lihat tagihan dan ajukan pembayaran tunai atau transfer.</a></div>
                </div>
                <p class="ko-mt-6 border-top ko-border-amber-200-50 ko-pt-4 ko-text--11px- fw-semibold ko-text-amber-800">Tagihan berdasarkan periode terpilih</p>
            </article></div>
        </section>

        <section class="card ko-radius-card border ko-border-slate-200 bg-white ko-p-6 shadow-sm">
            <div class="ko-mb-4 d-flex align-items-center justify-content-between">
                <div><h3 class="ko-text-base ko-fw-heavy ko-text-slate-900">Transaksi terbaru</h3><p class="ko-text-xs ko-text-slate-500">Aktivitas kas paling baru</p></div>
                <a href="{{ route('anggota.transparansi', ['periode_id' => $scope['selected']]) }}" class="d-flex align-items-center ko-gap-1 ko-text-xs fw-bold ko-text-brand-600 ko-hover-underline">Lihat semua <i data-lucide="chevron-right" class="ko-h-3-5 ko-w-3-5"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table ko-table w-100 ko-border-collapse text-start ko-text-xs ko-sm-text-sm">
                    <thead><tr class="border-bottom ko-border-slate-100 ko-text--11px- fw-bold text-uppercase ko-tracking-wide ko-text-slate-400"><th class="ko-px-3 ko-py-3">Tanggal</th><th class="ko-px-3 ko-py-3">Jenis</th><th class="ko-px-3 ko-py-3">Kategori</th><th class="ko-px-3 ko-py-3">Keterangan</th><th class="ko-px-3 ko-py-3 text-end">Nominal</th></tr></thead>
                    <tbody class="ko-table-lines ko-divider ko-text-slate-700">
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td class="ko-px-3 ko-py-3-5 fw-medium">{{ $transaction['date'] }}</td>
                                <td class="ko-px-3 ko-py-3-5"><span class="rounded-pill ko-px-2-5 ko-py-1 ko-text-xs fw-bold {{ $transaction['positive'] ? 'ko-bg-emerald-50 ko-text-emerald-600' : 'ko-bg-rose-50 ko-text-rose-600' }}">{{ $transaction['type'] }}</span></td>
                                <td class="ko-px-3 ko-py-3-5 fw-semibold">{{ $transaction['category'] }}</td>
                                <td class="ko-px-3 ko-py-3-5 ko-text-slate-500">{{ $transaction['description'] }}</td>
                                <td class="ko-px-3 ko-py-3-5 text-end fw-bold {{ $transaction['positive'] ? 'ko-text-emerald-600' : 'ko-text-rose-600' }}">{{ $transaction['amount'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="ko-py-8 text-center ko-text-sm ko-text-slate-500">Belum ada transaksi kas sah pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        @include('bootstrap.keuangan.personal-dashboard')
    </main>
@endsection

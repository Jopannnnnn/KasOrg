@props(['role' => 'anggota'])

@php
    $isPreview = request()->routeIs('ui-preview.*');
    $routePrefix = $isPreview ? 'ui-preview.' : '';
    $adminItems = [
        ['label' => 'Dashboard', 'icon' => 'layout-grid', 'route' => 'admin.dashboard'],
        ['label' => 'Data Anggota', 'icon' => 'users', 'route' => 'admin.anggota'],
        ['label' => 'Input Iuran', 'icon' => 'banknote', 'route' => 'admin.iuran'],
        ['label' => 'Verifikasi Pembayaran', 'icon' => 'check-circle-2', 'route' => 'admin.verifikasi', 'badge' => '2', 'badgeClass' => 'text-bg-danger'],
        ['label' => 'Transaksi Kas', 'icon' => 'credit-card', 'route' => 'admin.transaksi'],
        ['label' => 'Laporan', 'icon' => 'file-text', 'route' => 'admin.laporan'],
        ['label' => 'Transparansi Kas', 'icon' => 'eye', 'route' => 'admin.transparansi'],
    ];

    $anggotaItems = [
        ['label' => 'Dashboard', 'icon' => 'layout-grid', 'route' => 'anggota.dashboard'],
        ['label' => 'Tagihan', 'icon' => 'receipt-text', 'route' => 'anggota.tagihan', 'badge' => '2', 'badgeClass' => 'ko-bg-white-20 text-white'],
        ['label' => 'Laporan', 'icon' => 'file-text', 'route' => 'anggota.laporan'],
        ['label' => 'Transparansi Kas', 'icon' => 'eye', 'route' => 'anggota.transparansi'],
    ];

    $items = $role === 'admin' ? $adminItems : $anggotaItems;
@endphp

<aside class="ko-sidebar d-flex flex-shrink-0 user-select-none flex-column justify-content-between ko-bg-brand-700 ko-p-5 text-white ko-shadow-panel ko-md-min-h-screen ko-md-w-64">
    <div>
        <div class="ko-mb-6 d-flex align-items-center ko-gap-3 ko-px-2 ko-py-4">
            <div class="d-flex ko-h-10 ko-w-10 align-items-center justify-content-center ko-radius-control border ko-border-white-20 ko-bg-white-10 ko-shadow-inner">
                <i data-lucide="wallet" class="ko-h-5 ko-w-5 text-white"></i>
            </div>
            <div>
                <div class="ko-text-lg fw-bold ko-leading-tight ko-tracking-tight">KasOrg</div>
                <p class="ko-text-xs ko-text-blue-200">Sistem Kas Organisasi</p>
            </div>
        </div>

        <div class="ko-mb-2 ko-px-3 ko-text--11px- fw-bold text-uppercase ko-tracking-wide ko-text-blue-200-70">Menu Utama</div>
        <nav class="nav nav-pills flex-column ko-space-y-1" aria-label="Navigasi {{ $isPreview ? 'preview ' : '' }}{{ $role }}">
            @foreach ($items as $item)
                <a href="{{ route($routePrefix.$item['route']) }}"
                   class="nav-link d-flex align-items-center ko-gap-3 ko-radius-control ko-px-4 ko-py-3 ko-text-sm fw-semibold ko-transition {{ (request()->routeIs($routePrefix.$item['route']) || (! $isPreview && $item['route'] === 'admin.iuran' && request()->routeIs('admin.iuran.jenis.*'))) ? 'bg-white ko-text-brand-700 shadow-sm' : 'ko-text-blue-100 ko-hover-bg-white-10' }}">
                    <i data-lucide="{{ $item['icon'] }}" class="ko-h-4 ko-w-4"></i>
                    <span>{{ $item['label'] }}</span>
                    @isset($item['badge'])
                        <span class="badge ms-auto rounded-pill ko-px-2 ko-py-0-5 ko-text-xs fw-bold {{ $item['badgeClass'] }}">{{ $item['badge'] }}</span>
                    @endisset
                </a>
            @endforeach
        </nav>
    </div>

    <div class="border-top ko-border-white-15 ko-pt-6">
        @if ($isPreview)
            <a href="{{ route('ui-preview.login') }}" class="d-flex align-items-center ko-gap-3 ko-radius-control ko-px-4 ko-py-2-5 ko-text-sm fw-semibold ko-text-blue-100 ko-transition ko-hover-bg-white-10">
                <i data-lucide="log-out" class="ko-h-4 ko-w-4"></i> Keluar
            </a>
        @else
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn ko-button d-flex w-100 align-items-center ko-gap-3 ko-radius-control ko-px-4 ko-py-2-5 text-start ko-text-sm fw-semibold ko-text-blue-100 ko-transition ko-hover-bg-white-10">
                    <i data-lucide="log-out" class="ko-h-4 ko-w-4"></i> Keluar
                </button>
            </form>
        @endif
    </div>
</aside>

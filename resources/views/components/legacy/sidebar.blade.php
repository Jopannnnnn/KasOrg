@props(['role' => 'anggota'])

@php
    $isPreview = request()->routeIs('ui-preview.*');
    $routePrefix = $isPreview ? 'ui-preview.' : '';
    $adminItems = [
        ['label' => 'Dashboard', 'icon' => 'layout-grid', 'route' => 'admin.dashboard'],
        ['label' => 'Data Anggota', 'icon' => 'users', 'route' => 'admin.anggota'],
        ['label' => 'Input Iuran', 'icon' => 'banknote', 'route' => 'admin.iuran'],
        ['label' => 'Verifikasi Pembayaran', 'icon' => 'check-circle-2', 'route' => 'admin.verifikasi', 'badge' => '2', 'badgeClass' => 'bg-red-500 text-white'],
        ['label' => 'Transaksi Kas', 'icon' => 'credit-card', 'route' => 'admin.transaksi'],
        ['label' => 'Laporan', 'icon' => 'file-text', 'route' => 'admin.laporan'],
        ['label' => 'Transparansi Kas', 'icon' => 'eye', 'route' => 'admin.transparansi'],
    ];

    $anggotaItems = [
        ['label' => 'Dashboard', 'icon' => 'layout-grid', 'route' => 'anggota.dashboard'],
        ['label' => 'Tagihan', 'icon' => 'receipt-text', 'route' => 'anggota.tagihan', 'badge' => '2', 'badgeClass' => 'bg-white/20 text-white'],
        ['label' => 'Laporan', 'icon' => 'file-text', 'route' => 'anggota.laporan'],
        ['label' => 'Transparansi Kas', 'icon' => 'eye', 'route' => 'anggota.transparansi'],
    ];

    $items = $role === 'admin' ? $adminItems : $anggotaItems;
@endphp

<aside class="flex w-full shrink-0 select-none flex-col justify-between bg-brand-700 p-5 text-white shadow-xl md:min-h-screen md:w-64">
    <div>
        <div class="mb-6 flex items-center gap-3 px-2 py-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/20 bg-white/10 shadow-inner">
                <i data-lucide="wallet" class="h-5 w-5 text-white"></i>
            </div>
            <div>
                <div class="text-lg font-bold leading-tight tracking-tight">KasOrg</div>
                <p class="text-xs text-blue-200">Sistem Kas Organisasi</p>
            </div>
        </div>

        <div class="mb-2 px-3 text-[11px] font-bold uppercase tracking-wider text-blue-200/70">Menu Utama</div>
        <nav class="space-y-1" aria-label="Navigasi {{ $isPreview ? 'preview ' : '' }}{{ $role }}">
            @foreach ($items as $item)
                <a href="{{ route($routePrefix.$item['route']) }}"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition {{ (request()->routeIs($routePrefix.$item['route']) || (! $isPreview && $item['route'] === 'admin.iuran' && request()->routeIs('admin.iuran.jenis.*'))) ? 'bg-white text-brand-700 shadow-sm' : 'text-blue-100 hover:bg-white/10' }}">
                    <i data-lucide="{{ $item['icon'] }}" class="h-4 w-4"></i>
                    <span>{{ $item['label'] }}</span>
                    @isset($item['badge'])
                        <span class="ml-auto rounded-full px-2 py-0.5 text-xs font-bold {{ $item['badgeClass'] }}">{{ $item['badge'] }}</span>
                    @endisset
                </a>
            @endforeach
        </nav>
    </div>

    <div class="border-t border-white/15 pt-6">
        @if ($isPreview)
            <a href="{{ route('ui-preview.login') }}" class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-semibold text-blue-100 transition hover:bg-white/10">
                <i data-lucide="log-out" class="h-4 w-4"></i> Keluar
            </a>
        @else
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-4 py-2.5 text-left text-sm font-semibold text-blue-100 transition hover:bg-white/10">
                    <i data-lucide="log-out" class="h-4 w-4"></i> Keluar
                </button>
            </form>
        @endif
    </div>
</aside>

@props(['role' => 'anggota'])

@php
    $adminItems = [
        ['label' => 'Dashboard', 'icon' => 'layout-grid', 'route' => 'ui-preview.admin.dashboard'],
        ['label' => 'Data Anggota', 'icon' => 'users', 'route' => 'ui-preview.admin.anggota'],
        ['label' => 'Input Iuran', 'icon' => 'banknote', 'route' => 'ui-preview.admin.iuran'],
        ['label' => 'Verifikasi Pembayaran', 'icon' => 'check-circle-2', 'route' => 'ui-preview.admin.verifikasi', 'badge' => '2', 'badgeClass' => 'bg-red-500 text-white'],
        ['label' => 'Transaksi Kas', 'icon' => 'credit-card', 'route' => 'ui-preview.admin.transaksi'],
        ['label' => 'Laporan', 'icon' => 'file-text', 'route' => 'ui-preview.admin.laporan'],
        ['label' => 'Transparansi Kas', 'icon' => 'eye', 'route' => 'ui-preview.admin.transparansi'],
    ];

    $anggotaItems = [
        ['label' => 'Dashboard', 'icon' => 'layout-grid', 'route' => 'ui-preview.anggota.dashboard'],
        ['label' => 'Tagihan', 'icon' => 'receipt-text', 'route' => 'ui-preview.anggota.tagihan', 'badge' => '2', 'badgeClass' => 'bg-white/20 text-white'],
        ['label' => 'Laporan', 'icon' => 'file-text', 'route' => 'ui-preview.anggota.laporan'],
        ['label' => 'Transparansi Kas', 'icon' => 'eye', 'route' => 'ui-preview.anggota.transparansi'],
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
        <nav class="space-y-1" aria-label="Navigasi preview {{ $role }}">
            @foreach ($items as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition {{ request()->routeIs($item['route']) || ($role === 'admin' && request()->is('/')) ? 'bg-white text-brand-700 shadow-sm' : 'text-blue-100 hover:bg-white/10' }}">
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
        <a href="{{ route('ui-preview.login') }}" class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-semibold text-blue-100 transition hover:bg-white/10">
            <i data-lucide="log-out" class="h-4 w-4"></i> Keluar
        </a>
    </div>
</aside>

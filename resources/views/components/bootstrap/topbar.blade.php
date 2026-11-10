@props([
    'heading' => 'Dashboard',
    'role' => 'anggota',
    'userName' => 'Pengguna Preview',
    'userInitials' => 'KP',
])

<header class="navbar ko-topbar position-sticky top-0 ko-z-topbar d-flex align-items-center justify-content-between border-bottom ko-border-slate-200 bg-white ko-px-6 ko-py-4 ko-shadow-subtle">
    <div>
        <h1 class="ko-text-xl fw-bold ko-leading-tight ko-text-slate-800">{{ $heading }}</h1>
        <p class="ko-text-xs fw-medium ko-text-slate-500">
            {{ $role === 'admin' ? 'Panel administrasi organisasi' : 'Portal informasi anggota' }}
        </p>
    </div>

    <div class="d-flex align-items-center ko-gap-4">
        <button type="button" aria-label="Notifikasi" class="btn ko-button position-relative d-flex ko-h-10 ko-w-10 align-items-center justify-content-center rounded-pill border ko-border-slate-200 ko-text-slate-600 ko-transition ko-hover-bg-slate-50">
            <i data-lucide="bell" class="ko-h-5 ko-w-5"></i>
            <span class="position-absolute ko-right-2 ko-top-2 ko-h-2 ko-w-2 rounded-pill ko-bg-rose-500 ko-ring-2 ko-ring-white"></span>
        </button>
        <div class="d-flex align-items-center ko-gap-3 border-start ko-border-slate-100 ko-pl-2">
            <div class="d-flex ko-h-10 ko-w-10 align-items-center justify-content-center rounded-pill border ko-border-blue-200 ko-bg-blue-100 ko-text-sm ko-fw-heavy ko-text-brand-700 ko-shadow-subtle">
                {{ $userInitials }}
            </div>
            <div class="d-none text-start ko-sm-block">
                <p class="ko-text-sm fw-bold ko-leading-tight ko-text-slate-800">{{ $userName }}</p>
                <p class="ko-text-xs ko-text-slate-500">{{ $role === 'admin' ? 'Admin' : 'Anggota' }}</p>
            </div>
        </div>
    </div>
</header>

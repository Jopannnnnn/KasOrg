@props([
    'heading' => 'Dashboard',
    'role' => 'anggota',
    'userName' => 'Pengguna Preview',
    'userInitials' => 'KP',
])

<header class="sticky top-0 z-30 flex items-center justify-between border-b border-slate-200 bg-white px-6 py-4 shadow-xs">
    <div>
        <h1 class="text-xl font-bold leading-tight text-slate-800">{{ $heading }}</h1>
        <p class="text-xs font-medium text-slate-500">
            {{ $role === 'admin' ? 'Panel administrasi organisasi' : 'Portal informasi anggota' }}
        </p>
    </div>

    <div class="flex items-center gap-4">
        <button type="button" aria-label="Notifikasi" class="relative flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-600 transition hover:bg-slate-50">
            <i data-lucide="bell" class="h-5 w-5"></i>
            <span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-rose-500 ring-2 ring-white"></span>
        </button>
        <div class="flex items-center gap-3 border-l border-slate-100 pl-2">
            <div class="flex h-10 w-10 items-center justify-center rounded-full border border-blue-200 bg-blue-100 text-sm font-extrabold text-brand-700 shadow-xs">
                {{ $userInitials }}
            </div>
            <div class="hidden text-left sm:block">
                <p class="text-sm font-bold leading-tight text-slate-800">{{ $userName }}</p>
                <p class="text-xs text-slate-500">{{ $role === 'admin' ? 'Admin' : 'Anggota' }}</p>
            </div>
        </div>
    </div>
</header>

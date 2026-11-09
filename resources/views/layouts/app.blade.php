@php
    $isPreview = request()->routeIs('ui-preview.*');
    $usesBootstrap = request()->routeIs('admin.dashboard', 'anggota.dashboard', 'admin.anggota*', 'admin.iuran.jenis.*');
    if (! $isPreview) {
        // Identitas aplikasi dari session, bukan nama contoh yang disediakan view.
        $account = auth()->user();
        $role = $account->role->kode;
        $userName = $account->name;
        $userInitials = \Illuminate\Support\Str::of($userName)->explode(' ')
            ->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('');
        $userInitials = mb_strtoupper($userInitials);
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? 'KasOrg - Sistem Kas Organisasi' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest" defer></script>

    @if ($usesBootstrap)
        @include('frontend.bootstrap-assets')
    @elseif (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        {{-- Fallback preview sampai Node.js dan hasil build Vite tersedia. --}}
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                        colors: {
                            brand: {
                                50: '#F0F4FA', 100: '#DBE5F3', 200: '#B9CCE5',
                                500: '#345A9C', 600: '#234578', 700: '#172554',
                                800: '#111D42', 900: '#0B1530'
                            }
                        }
                    }
                }
            };
        </script>
    @endif
    @stack('styles')
</head>
<body class="{{ $usesBootstrap ? 'kasorg-bootstrap' : 'min-h-screen bg-[#F8FAFC] font-sans text-slate-800 antialiased' }} @yield('body-class')">
    <div class="{{ $usesBootstrap ? 'ko-shell d-flex flex-column flex-md-row min-vh-100' : 'min-h-screen md:flex' }}">
        <x-sidebar :role="$role ?? 'anggota'" :bootstrap="$usesBootstrap" />

        <div class="{{ $usesBootstrap ? 'd-flex flex-column flex-grow-1 ko-min-width-zero' : 'flex min-w-0 flex-1 flex-col' }}">
            <x-topbar :bootstrap="$usesBootstrap"
                :heading="$heading ?? 'Dashboard'"
                :role="$role ?? 'anggota'"
                :user-name="$userName ?? 'Pengguna Preview'"
                :user-initials="$userInitials ?? 'KP'"
            />

            <p role="note" class="{{ $usesBootstrap ? 'ko-portal-note' : 'border-b border-blue-100 bg-brand-50 px-6 py-2 text-xs text-brand-700' }}">
                {{ $isPreview ? 'Preview lokal tanpa autentikasi — bukan bukti pengujian aplikasi.' : 'DALAM PENGEMBANGAN — BELUM FINAL. Autentikasi aktif.' }}
                @if (request()->routeIs('admin.anggota'))
                    Fokus demo UTS: CRUD Data Anggota. Data anggota terhubung ke database; hasil clone masih perlu pengujian. Status iuran, badge sidebar, dan notifikasi belum terhubung.
                @elseif (request()->routeIs('admin.iuran', 'admin.iuran.jenis.*', 'admin.verifikasi', 'anggota.tagihan'))
                    Jenis iuran, tagihan, pembayaran dan verifikasi masih DALAM PENGEMBANGAN/TESTING. Badge sidebar dan notifikasi masih simulasi.
                @elseif (request()->routeIs('admin.dashboard', 'anggota.dashboard', 'admin.transparansi', 'anggota.transparansi', 'admin.transaksi', 'admin.laporan', 'anggota.laporan'))
                    Dashboard dan modul keuangan masih DALAM PENGEMBANGAN/TESTING. Ringkasan kas, laporan dan PDF memakai sumber perhitungan yang sama. Badge sidebar dan notifikasi masih simulasi.
                @else
                    Isi halaman, statistik, notifikasi, dan aksi kelola masih simulasi frontend, bukan data database.
                @endif
            </p>

            @yield('content')
        </div>
    </div>

    @stack('scripts')
    <script>
        window.addEventListener('load', () => {
            if (window.lucide) window.lucide.createIcons();
        });
    </script>
</body>
</html>

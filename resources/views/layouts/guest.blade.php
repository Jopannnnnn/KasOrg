<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? 'Masuk - KasOrg' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest" defer></script>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
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
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-100 p-4 font-sans sm:p-6">
    @yield('content')

    @stack('scripts')
    <script>
        window.addEventListener('load', () => {
            if (window.lucide) window.lucide.createIcons();
        });
    </script>
</body>
</html>

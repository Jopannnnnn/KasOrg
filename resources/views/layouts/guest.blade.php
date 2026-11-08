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

    @include('frontend.bootstrap-assets')
    @stack('styles')
</head>
<body class="kasorg-bootstrap ko-guest d-flex min-vh-100 align-items-center justify-content-center @yield('body-class')">
    @yield('content')

    @stack('scripts')
    <script>
        window.addEventListener('load', () => {
            if (window.lucide) window.lucide.createIcons();
        });
    </script>
</body>
</html>

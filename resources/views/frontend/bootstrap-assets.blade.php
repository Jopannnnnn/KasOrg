{{-- Separate entry prevents Bootstrap Reboot from affecting legacy Tailwind pages. --}}
@php
    $bootstrapManifest = file_exists(public_path('build/manifest.json'))
        ? json_decode(file_get_contents(public_path('build/manifest.json')), true) : [];
    $bootstrapBuilt = isset($bootstrapManifest['resources/css/bootstrap.css'], $bootstrapManifest['resources/js/bootstrap-mvp.js']);
@endphp
@if (file_exists(public_path('hot')) || $bootstrapBuilt)
    @vite(['resources/css/bootstrap.css', 'resources/js/bootstrap-mvp.js'])
@else
    {{-- Temporary no-Node fallback. Pinned version; requires internet access. --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="{{ asset('css/kasorg-bootstrap.css') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous" defer></script>
@endif

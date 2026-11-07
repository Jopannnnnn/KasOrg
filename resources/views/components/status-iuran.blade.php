@props(['status'])
@php
    $label = ['belum_lunas' => 'Belum Lunas', 'lunas' => 'Lunas', 'menunggu' => 'Menunggu Verifikasi', 'disetujui' => 'Terverifikasi', 'ditolak' => 'Ditolak'][$status] ?? $status;
    $color = in_array($status, ['lunas', 'disetujui']) ? 'bg-emerald-50 text-emerald-700' : ($status === 'ditolak' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-700');
@endphp
<span class="inline-flex rounded-lg px-3 py-1.5 text-xs font-semibold {{ $color }}">{{ $label }}</span>

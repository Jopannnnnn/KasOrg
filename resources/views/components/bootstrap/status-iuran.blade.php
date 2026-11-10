@props(['status'])
@php
    $label = ['belum_lunas' => 'Belum Lunas', 'lunas' => 'Lunas', 'menunggu' => 'Menunggu Verifikasi', 'disetujui' => 'Terverifikasi', 'ditolak' => 'Ditolak'][$status] ?? $status;
    $color = in_array($status, ['lunas', 'disetujui']) ? 'ko-bg-emerald-50 ko-text-emerald-700' : ($status === 'ditolak' ? 'ko-bg-red-50 ko-text-red-600' : 'ko-bg-amber-50 ko-text-amber-700');
@endphp
<span class="badge d-inline-flex ko-radius-small ko-px-3 ko-py-1-5 ko-text-xs fw-semibold {{ $color }}">{{ $label }}</span>

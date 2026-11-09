@extends('layouts.app', ['title' => 'Tagihan Saya | KasOrg', 'heading' => 'Tagihan Saya', 'role' => 'anggota'])
@section('content')
<main class="flex-1 p-5 md:p-8 lg:p-10" id="tagihan-anggota-page">
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-wrap items-start justify-between gap-4"><div><p class="mb-1 text-sm font-medium text-brand-700">Keuangan Anggota</p><h1 class="text-3xl font-bold tracking-tight text-slate-900">Tagihan Saya</h1><p class="mt-2 text-slate-500">Pantau iuran organisasi dan kirim pembayaran untuk tagihan yang belum lunas.</p></div></div>
        <x-keuangan-feedback />
        <div class="mt-7 grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><div class="flex items-center justify-between text-sm text-slate-500"><span>Total belum lunas</span><i data-lucide="wallet" class="h-5 w-5 text-brand-700"></i></div><p class="mt-3 text-3xl font-bold text-slate-900">Rp {{ number_format((float) $stats['belum_lunas'], 2, ',', '.') }}</p><p class="mt-2 text-xs text-slate-500">Termasuk pembayaran yang menunggu verifikasi</p></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><div class="flex items-center justify-between text-sm text-slate-500"><span>Menunggu verifikasi</span><i data-lucide="clock-3" class="h-5 w-5 text-amber-600"></i></div><p class="mt-3 text-3xl font-bold text-amber-600">{{ $stats['menunggu'] }}</p><p class="mt-2 text-xs text-slate-500">Pembayaran sedang ditinjau bendahara</p></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><div class="flex items-center justify-between text-sm text-slate-500"><span>Tagihan lunas</span><i data-lucide="check-circle-2" class="h-5 w-5 text-emerald-600"></i></div><p class="mt-3 text-3xl font-bold text-emerald-600">{{ $stats['lunas'] }}</p><p class="mt-2 text-xs text-slate-500">Pembayaran sudah disetujui bendahara</p></div>
        </div>
        <div class="mt-6 rounded-xl bg-brand-50 p-5 text-sm leading-relaxed text-brand-700"><div class="flex gap-3"><i data-lucide="info" class="mt-0.5 h-5 w-5 shrink-0"></i><p>Tunai dan transfer memerlukan verifikasi manual Admin. Mengajukan pembayaran <strong>belum berarti lunas</strong>. Jika ditolak, baca alasannya dan ajukan ulang.</p></div></div>
        <section class="mt-7 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @include('keuangan.filter', ['statusOptions' => ['belum_lunas' => 'Belum Lunas', 'menunggu' => 'Menunggu Verifikasi', 'lunas' => 'Lunas', 'ditolak' => 'Ditolak']])
            <div class="overflow-x-auto"><table class="w-full min-w-[800px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-6 py-4">Tagihan</th><th class="px-6 py-4">Jatuh Tempo</th><th class="px-6 py-4">Nominal</th><th class="px-6 py-4">Status</th><th class="px-6 py-4">Aksi</th></tr></thead><tbody class="divide-y divide-slate-100">
                @forelse ($bills as $bill)
                    @php($latest = $bill->pembayaranIuran->first())
                    @php($canPay = $bill->status === 'belum_lunas' && ! $bill->pembayaranIuran->contains(fn ($p) => in_array($p->status, ['menunggu', 'disetujui'])) && $bill->periode->status === 'terbuka')
                    <tr>
                        <td class="px-6 py-5"><p class="font-bold text-slate-900">{{ $bill->jenisIuran->nama }}</p><p class="mt-1 text-xs text-slate-500">{{ $bill->periode->tanggal_mulai->locale('id')->translatedFormat('F Y') }}</p></td>
                        <td class="px-6 py-5">{{ $bill->jatuh_tempo?->format('d/m/Y') ?? 'Tidak ditentukan' }}</td>
                        <td class="px-6 py-5 font-semibold">Rp {{ number_format((float) $bill->nominal, 2, ',', '.') }}</td>
                        <td class="px-6 py-5"><x-status-iuran :status="$bill->status === 'lunas' ? 'lunas' : ($latest?->status ?? 'belum_lunas')" />@if ($latest?->status === 'ditolak')<p class="mt-2 max-w-xs text-xs text-red-700">Alasan: {{ $latest->alasan_penolakan }}</p>@endif</td>
                        <td class="px-6 py-5"><button type="button" data-open-modal="bayar-{{ $bill->id }}" class="rounded-lg border border-slate-200 px-4 py-2 font-medium hover:bg-slate-50">{{ $canPay ? ($latest?->status === 'ditolak' ? 'Ajukan Ulang' : 'Bayar Sekarang') : 'Detail' }}</button></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-12 text-center text-slate-500">Tidak ada tagihan yang cocok dengan filter.</td></tr>
                @endforelse
            </tbody></table></div>
            {{ $bills->links('admin.anggota.pagination') }}
        </section>
        <footer class="mt-8 flex flex-wrap justify-between gap-3 text-xs text-slate-500"><span>© {{ now()->year }} KasOrg · Sistem Kas Organisasi</span><span>Kelola kas dengan mudah, transparan, dan terpercaya.</span></footer>
    </div>
</main>
@foreach ($bills as $bill)
    @php($canPay = $bill->status === 'belum_lunas' && ! $bill->pembayaranIuran->contains(fn ($p) => in_array($p->status, ['menunggu', 'disetujui'])) && $bill->periode->status === 'terbuka')
    @php($restore = old('_modal') === 'bayar-'.$bill->id)
    <x-keuangan-modal :id="'bayar-'.$bill->id" title="Detail Tagihan">
        <p class="text-lg font-semibold">{{ $bill->jenisIuran->nama }}</p>
        <div class="mt-4 grid grid-cols-2 gap-4 rounded-xl bg-slate-50 p-4 text-sm">
            <div><p class="text-slate-500">Periode</p><p class="mt-1 font-semibold">{{ $bill->periode->tanggal_mulai->locale('id')->translatedFormat('F Y') }}</p></div>
            <div><p class="text-slate-500">Jatuh tempo</p><p class="mt-1 font-semibold">{{ $bill->jatuh_tempo?->format('d/m/Y') ?? 'Tidak ditentukan' }}</p></div>
            <div><p class="text-slate-500">Nominal</p><p class="mt-1 font-semibold">Rp {{ number_format((float) $bill->nominal, 2, ',', '.') }}</p></div>
            <div><p class="text-slate-500">Status tagihan</p><x-status-iuran :status="$bill->status" /></div>
        </div>
        @include('keuangan.history')
        @if ($canPay)
            <form data-payment-form method="POST" action="{{ route('anggota.pembayaran.store', $bill) }}" enctype="multipart/form-data" class="mt-6 space-y-5">
                @csrf
                <input type="hidden" name="_modal" value="bayar-{{ $bill->id }}">
                <label class="block text-sm font-semibold">Metode pembayaran<select name="metode" required class="mt-2 w-full rounded-xl border border-slate-300 bg-white p-3 text-sm"><option value="transfer">Transfer bank</option><option value="tunai" @selected($restore && old('metode') === 'tunai')>Tunai (konfirmasi ke bendahara)</option></select></label>
                <label class="block text-sm font-semibold">Tanggal pembayaran<input name="tanggal_bayar" type="date" required max="{{ today()->toDateString() }}" value="{{ $restore ? old('tanggal_bayar', today()->toDateString()) : today()->toDateString() }}" class="mt-2 w-full rounded-xl border border-slate-300 p-3 text-sm"></label>
                <div data-upload-area>
                    <label class="block text-sm font-semibold">Unggah bukti transfer <span class="text-red-500">*</span><input name="bukti" type="file" accept="image/png,image/jpeg,image/webp" class="mt-2 w-full rounded-xl border border-slate-300 p-3 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-brand-700"></label>
                    <p class="mt-2 text-xs text-slate-500">JPG, PNG, atau WebP, maksimal 5 MB. Disimpan privat; hanya pemilik dan Admin dapat mengakses. Pilih ulang file jika validasi gagal.</p>
                    <img data-proof-preview class="mt-3 hidden max-h-56 w-full rounded-lg object-contain" alt="Pratinjau bukti transfer">
                </div>
                <label class="block text-sm font-semibold">Catatan (opsional)<textarea name="catatan" maxlength="255" rows="2" placeholder="Contoh: Transfer dari rekening atas nama saya" class="mt-2 w-full rounded-xl border border-slate-300 p-3 text-sm">{{ $restore ? old('catatan') : '' }}</textarea></label>
                <div class="flex justify-end gap-3 border-t border-slate-100 pt-5"><button type="button" data-close-modal class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold">Tutup</button><button type="submit" class="rounded-xl bg-brand-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-800">Kirim Pembayaran</button></div>
            </form>
        @else
            <p class="mt-6 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">{{ $bill->periode->status === 'ditutup' ? 'Periode sudah ditutup. Hubungi Admin.' : 'Tagihan sudah lunas atau pengajuan sedang menunggu pemeriksaan Admin.' }}</p>
        @endif
    </x-keuangan-modal>
@endforeach
@endsection
@include('keuangan.scripts')

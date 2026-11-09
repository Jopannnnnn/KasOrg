@extends('layouts.app', ['title' => 'Verifikasi Pembayaran | KasOrg', 'heading' => 'Verifikasi Pembayaran', 'role' => 'admin'])
@section('content')
<main class="flex-1 p-5 md:p-8 lg:p-10" id="verifikasi-page">
    <div class="mx-auto max-w-7xl">
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">Verifikasi Pembayaran</h1>
        <p class="mt-2 text-slate-500">Periksa penerimaan tunai atau cocokkan bukti transfer dengan mutasi rekening.</p>
        <x-keuangan-feedback />
        <div class="mt-8 rounded-xl bg-brand-50 px-5 py-5 text-sm text-brand-700">Transfer manual tidak terverifikasi otomatis. Saldo kas bertambah hanya setelah bendahara menyetujui pembayaran.</div>
        <section class="mt-7 overflow-hidden rounded-2xl border border-slate-200 bg-white">
            @include('keuangan.filter', ['statusOptions' => ['menunggu' => 'Menunggu', 'disetujui' => 'Terverifikasi', 'ditolak' => 'Ditolak']])
            <div class="overflow-x-auto"><table class="w-full min-w-[850px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-6 py-4">Anggota</th><th class="px-6 py-4">Tagihan</th><th class="px-6 py-4">Metode</th><th class="px-6 py-4">Nominal</th><th class="px-6 py-4">Status</th><th class="px-6 py-4">Aksi</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="px-6 py-5"><div class="font-bold text-slate-900">{{ $payment->tagihanIuran->anggota->name }}</div><div class="mt-1 text-xs text-slate-500">{{ $payment->tanggal_bayar->format('d/m/Y') }} · {{ $payment->tagihanIuran->anggota->nim }}</div></td>
                            <td class="px-6 py-5">{{ $payment->tagihanIuran->jenisIuran->nama }}<div class="mt-1 text-xs text-slate-500">{{ $payment->tagihanIuran->periode->tanggal_mulai->locale('id')->translatedFormat('F Y') }} · Pengajuan #{{ $payment->id }}</div></td>
                            <td class="px-6 py-5">{{ ucfirst($payment->metode) }}</td>
                            <td class="px-6 py-5">Rp {{ number_format((float) $payment->nominal, 2, ',', '.') }}</td>
                            <td class="px-6 py-5"><x-status-iuran :status="$payment->status" /></td>
                            <td class="px-6 py-5"><button type="button" data-open-modal="verifikasi-{{ $payment->id }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium hover:bg-slate-50">{{ $payment->status === 'menunggu' ? 'Tinjau' : 'Detail' }}</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-12 text-center text-slate-500">Tidak ada pembayaran yang sesuai pencarian.</td></tr>
                    @endforelse
                </tbody>
            </table></div>
            {{ $payments->links('admin.anggota.pagination') }}
        </section>
        <footer class="mt-8 flex flex-wrap justify-between gap-3 text-xs text-slate-500"><span>© {{ now()->year }} KasOrg · Sistem Kas Organisasi</span><span>Kelola kas dengan mudah, transparan, dan terpercaya.</span></footer>
    </div>
</main>
@foreach ($payments as $payment)
    <x-keuangan-modal :id="'verifikasi-'.$payment->id" title="Verifikasi pembayaran">
        <p class="font-semibold">{{ $payment->tagihanIuran->anggota->name }} · {{ $payment->tagihanIuran->jenisIuran->nama }}</p>
        <p class="mt-4 text-sm text-slate-700">Rp {{ number_format((float) $payment->nominal, 2, ',', '.') }} · {{ ucfirst($payment->metode) }} · {{ $payment->tanggal_bayar->format('d/m/Y') }}</p>
        <div class="mt-4"><x-status-iuran :status="$payment->status" /></div>
        @if ($payment->catatan)<p class="mt-5 text-sm">Catatan pengirim: {{ $payment->catatan }}</p>@endif
        <div class="mt-5 flex min-h-40 flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-5 text-center">
            @if ($payment->bukti_path)
                <a href="{{ route('pembayaran.bukti', $payment) }}" target="_blank" rel="noopener" class="font-semibold text-brand-700 underline"><img loading="lazy" src="{{ route('pembayaran.bukti', $payment) }}" alt="Bukti pembayaran pengajuan {{ $payment->id }}" class="mb-3 max-h-56 w-full rounded-lg object-contain">Buka bukti transfer</a>
            @else
                <p class="text-sm text-slate-600">Pembayaran tunai: periksa bahwa uang benar-benar telah diterima.</p>
            @endif
        </div>
        @if ($payment->status === 'menunggu')
            <form method="POST" action="{{ route('admin.verifikasi.update', $payment) }}">
                @csrf
                <input type="hidden" name="_modal" value="verifikasi-{{ $payment->id }}">
                <label class="mt-6 flex items-start gap-3 text-sm leading-relaxed"><input name="konfirmasi" value="1" type="checkbox" class="mt-1 h-4 w-4 accent-[#172554]"><span>Saya telah memeriksa penerimaan tunai atau mencocokkan nominal, tanggal, pengirim, dan dana masuk pada mutasi rekening.</span></label>
                <label class="mt-6 block text-sm font-semibold">Alasan penolakan (wajib jika menolak)<textarea name="alasan_penolakan" maxlength="255" rows="3" class="mt-2 w-full resize-y rounded-xl border border-slate-300 p-3 text-sm outline-none focus:border-brand-600" placeholder="Jelaskan hal yang perlu diperbaiki anggota">{{ old('_modal') === 'verifikasi-'.$payment->id ? old('alasan_penolakan') : '' }}</textarea></label>
                <div class="mt-5 flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-4"><button type="submit" name="keputusan" value="ditolak" class="rounded-xl border border-red-200 px-5 py-2.5 font-semibold text-red-600 hover:bg-red-50">Tolak pembayaran</button><button type="submit" name="keputusan" value="disetujui" class="rounded-xl bg-brand-700 px-5 py-2.5 font-semibold text-white hover:bg-brand-800">Setujui pembayaran</button></div>
            </form>
        @else
            <p class="mt-4 rounded-lg bg-slate-50 p-3 text-sm text-slate-600">{{ $payment->status === 'ditolak' ? 'Alasan penolakan: '.$payment->alasan_penolakan : 'Pembayaran telah disetujui.' }}</p>
            <p class="mt-3 text-xs text-slate-500">Diperiksa oleh {{ $payment->verifikator?->name ?? 'Admin' }} pada {{ $payment->diverifikasi_at?->format('d/m/Y H:i') }}.</p>
            @if ($payment->transaksiKas)<p class="mt-2 text-xs text-emerald-700">Pemasukan kas #{{ $payment->transaksiKas->id }} telah tercatat.</p>@endif
        @endif
    </x-keuangan-modal>
@endforeach
@endsection
@include('keuangan.scripts')

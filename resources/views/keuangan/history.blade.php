<div class="mt-6 space-y-3">
    <h3 class="text-sm font-bold text-slate-900">Riwayat pengajuan</h3>
    @forelse ($bill->pembayaranIuran as $attempt)
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm">
            <div class="flex flex-wrap items-center justify-between gap-2"><span>#{{ $attempt->id }} · {{ ucfirst($attempt->metode) }} · {{ $attempt->tanggal_bayar->format('d/m/Y') }}</span><x-status-iuran :status="$attempt->status" /></div>
            @if ($attempt->alasan_penolakan)<p class="mt-3 text-red-700">Alasan penolakan: {{ $attempt->alasan_penolakan }}</p>@endif
            @if ($attempt->catatan)<p class="mt-2 text-slate-600">Catatan: {{ $attempt->catatan }}</p>@endif
            @if ($attempt->bukti_path)<a class="mt-3 inline-block font-semibold text-brand-700 underline" href="{{ route('pembayaran.bukti', $attempt) }}" target="_blank" rel="noopener">Lihat bukti transfer</a>@endif
        </div>
    @empty
        <p class="text-sm text-slate-500">Belum ada pengajuan pembayaran.</p>
    @endforelse
</div>

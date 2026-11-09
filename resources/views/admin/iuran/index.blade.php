@extends('layouts.app', ['title' => 'Input Iuran - KasOrg', 'heading' => 'Input Iuran', 'role' => 'admin'])

@section('content')
<main class="flex-1 space-y-8 overflow-y-auto p-6 md:p-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div><h2 class="text-2xl font-extrabold tracking-tight text-slate-900">Input iuran anggota</h2><p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Buat tagihan untuk anggota aktif sesuai jenis iuran dan periode.</p></div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.iuran.jenis.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700">Kelola jenis iuran</a>
            <button type="button" data-open-modal="modal-jenis" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700">Tambah jenis iuran</button>
            <button type="button" data-open-modal="modal-tagihan" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-brand-600/20 hover:bg-brand-700"><i data-lucide="plus" class="h-4 w-4"></i>Buat tagihan</button>
        </div>
    </div>
    <x-keuangan-feedback />
    <section class="flex items-start gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-brand-600"><i data-lucide="wallet" class="h-5 w-5"></i></span>
        <div><h3 class="text-sm font-extrabold text-slate-900">Alur pencatatan sederhana</h3><p class="mt-1 text-xs leading-relaxed text-slate-500">Pembuatan tagihan belum menambah kas. Anggota mengajukan pembayaran tunai atau transfer, lalu Admin memverifikasi penerimaan uang di halaman <a href="{{ route('admin.verifikasi') }}" class="font-bold underline">Verifikasi Pembayaran</a>.</p></div>
    </section>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        @include('keuangan.filter', ['statusOptions' => ['belum_lunas' => 'Belum Lunas', 'menunggu' => 'Menunggu Verifikasi', 'ditolak' => 'Ditolak', 'lunas' => 'Lunas']])
        <div class="overflow-x-auto"><table class="w-full border-collapse text-left text-xs sm:text-sm">
            <thead><tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-bold uppercase tracking-wider text-slate-400"><th class="px-4 py-3">Anggota</th><th class="px-4 py-3">Periode</th><th class="px-4 py-3">Nominal</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Aksi</th></tr></thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse ($bills as $bill)
                    <tr class="transition hover:bg-slate-50/80">
                        <td class="px-4 py-4"><div class="font-bold text-slate-900">{{ $bill->anggota->name }}</div><div class="font-mono text-[11px] text-slate-400">{{ $bill->anggota->nim }}</div></td>
                        <td class="px-4 py-4 font-medium text-slate-600">{{ $bill->periode->tanggal_mulai->locale('id')->translatedFormat('F Y') }}<div class="mt-1 text-xs text-slate-500">{{ $bill->jenisIuran->nama }}</div></td>
                        <td class="px-4 py-4 font-bold text-slate-800">Rp {{ number_format((float) $bill->nominal, 2, ',', '.') }}</td>
                        <td class="px-4 py-4"><x-status-iuran :status="$bill->status === 'lunas' ? 'lunas' : ($bill->pembayaranIuran->first()?->status ?? 'belum_lunas')" /></td>
                        <td class="px-4 py-4 text-right"><button type="button" data-open-modal="detail-tagihan-{{ $bill->id }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-700"><i data-lucide="eye" class="h-3.5 w-3.5"></i>Detail</button></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-10 text-center text-sm text-slate-500">Tidak ada tagihan yang sesuai.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        {{ $bills->links('admin.anggota.pagination') }}
    </section>
</main>
<x-keuangan-modal id="modal-tagihan" title="Buat tagihan iuran">
    <form method="POST" action="{{ route('admin.iuran.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="_modal" value="modal-tagihan">
        <label class="block text-sm font-semibold">Anggota<select name="user_id" required class="mt-2 w-full rounded-xl border border-slate-300 bg-white p-3 text-sm"><option value="">Pilih anggota aktif</option>@foreach ($members as $member)<option value="{{ $member->id }}" @selected(old('user_id') == $member->id)>{{ $member->name }} — {{ $member->nim }}</option>@endforeach</select></label>
        <label class="block text-sm font-semibold">Jenis iuran<select name="jenis_iuran_id" required class="mt-2 w-full rounded-xl border border-slate-300 bg-white p-3 text-sm"><option value="">Pilih jenis iuran</option>@foreach ($types as $type)<option value="{{ $type->id }}" @selected(old('jenis_iuran_id') == $type->id)>{{ $type->nama }} — Rp {{ number_format((float) $type->nominal, 2, ',', '.') }}</option>@endforeach</select></label>
        <label class="block text-sm font-semibold">Periode<select name="periode_id" required class="mt-2 w-full rounded-xl border border-slate-300 bg-white p-3 text-sm"><option value="">Pilih periode terbuka</option>@foreach ($periods as $period)<option value="{{ $period->id }}" @selected(old('periode_id') == $period->id)>{{ $period->tanggal_mulai->locale('id')->translatedFormat('F Y') }}</option>@endforeach</select></label>
        <label class="block text-sm font-semibold">Jatuh tempo (opsional)<input name="jatuh_tempo" type="date" value="{{ old('jatuh_tempo') }}" class="mt-2 w-full rounded-xl border border-slate-300 p-3 text-sm"></label>
        <p class="rounded-xl bg-brand-50 p-3 text-xs text-brand-700">Nominal ditetapkan dari jenis iuran. Satu anggota hanya memiliki satu tagihan untuk jenis iuran dan periode yang sama.</p>
        <div class="flex justify-end gap-3"><button type="button" data-close-modal class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold">Batal</button><button type="submit" class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white">Simpan tagihan</button></div>
    </form>
</x-keuangan-modal>
<x-keuangan-modal id="modal-jenis" title="Tambah jenis iuran">
    <form method="POST" action="{{ route('admin.iuran.jenis.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="_modal" value="modal-jenis">
        <label class="block text-sm font-semibold">Nama jenis iuran<input name="nama" value="{{ old('nama') }}" required maxlength="100" class="mt-2 w-full rounded-xl border border-slate-300 p-3 text-sm"></label>
        <label class="block text-sm font-semibold">Nominal (Rp)<input name="nominal" type="number" min="0.01" max="9999999999999.99" step="0.01" value="{{ old('nominal') }}" required class="mt-2 w-full rounded-xl border border-slate-300 p-3 text-sm"></label>
        <label class="block text-sm font-semibold">Deskripsi (opsional)<textarea name="deskripsi" maxlength="255" rows="2" class="mt-2 w-full rounded-xl border border-slate-300 p-3 text-sm">{{ old('deskripsi') }}</textarea></label>
        <label class="block text-sm font-semibold">Status<select name="aktif" class="mt-2 w-full rounded-xl border border-slate-300 bg-white p-3 text-sm"><option value="1" @selected(old('aktif', '1') == '1')>Aktif</option><option value="0" @selected(old('aktif', '1') == '0')>Nonaktif</option></select></label>
        <div class="flex justify-end gap-3"><button type="button" data-close-modal class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold">Batal</button><button type="submit" class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white">Simpan jenis iuran</button></div>
    </form>
</x-keuangan-modal>
@foreach ($bills as $bill)
    <x-keuangan-modal :id="'detail-tagihan-'.$bill->id" title="Detail tagihan">
        <p class="font-bold">{{ $bill->anggota->name }} · {{ $bill->jenisIuran->nama }}</p>
        <p class="mt-2 text-sm text-slate-600">Jatuh tempo: {{ $bill->jatuh_tempo?->format('d/m/Y') ?? 'Tidak ditentukan' }}</p>
        @include('keuangan.history')
        <a href="{{ route('admin.verifikasi') }}" class="mt-5 inline-block text-sm font-bold text-brand-700 underline">Buka Verifikasi Pembayaran</a>
    </x-keuangan-modal>
@endforeach
@endsection
@include('keuangan.scripts')

@php
    $restore = old('_modal') === $modalId;
    $field = function ($name, $default = '') use ($restore) {
        $value = $restore ? old($name, $default) : $default;
        return is_scalar($value) ? $value : '';
    };
    $selectedPeriod = $openPeriods->firstWhere('id', $periodId);
    $defaultDate = $record?->tanggal->toDateString() ?? ($selectedPeriod && $selectedPeriod->tanggal_mulai->lte(today()) ? min(today()->toDateString(), $selectedPeriod->tanggal_selesai->toDateString()) : '');
@endphp
<form method="POST" action="{{ $record ? route('admin.transaksi.update', ['transaksi' => $record->id] + $filters) : route('admin.transaksi.store', $filters) }}" data-manual-transaction-form class="space-y-4">
    @csrf
    @if ($record) @method('PUT') @endif
    <input type="hidden" name="_modal" value="{{ $modalId }}">
    <label class="block text-sm font-semibold">Jenis transaksi<select name="jenis" required class="mt-2 w-full rounded-xl border border-slate-300 bg-white p-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600"><option value="pemasukan" @selected($field('jenis', $record?->jenis ?? 'pemasukan') === 'pemasukan')>Pemasukan</option><option value="pengeluaran" @selected($field('jenis', $record?->jenis ?? 'pemasukan') === 'pengeluaran')>Pengeluaran</option></select></label>
    <label class="block text-sm font-semibold">Kategori<select name="kategori_transaksi_id" required class="mt-2 w-full rounded-xl border border-slate-300 bg-white p-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600"><option value="">Pilih kategori sesuai jenis</option>@foreach ($categories as $category)<option value="{{ $category->id }}" data-jenis="{{ $category->jenis }}" @selected($field('kategori_transaksi_id', $record?->kategori_transaksi_id) == $category->id)>{{ $category->nama }}</option>@endforeach</select></label>
    <label class="block text-sm font-semibold">Periode terbuka<select name="periode_id" required class="mt-2 w-full rounded-xl border border-slate-300 bg-white p-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600"><option value="">Pilih periode terbuka</option>@foreach ($openPeriods as $period)<option value="{{ $period->id }}" data-start="{{ $period->tanggal_mulai->toDateString() }}" data-end="{{ $period->tanggal_selesai->toDateString() }}" @selected($field('periode_id', $periodId) == $period->id)>{{ $period->tanggal_mulai->locale('id')->translatedFormat('F Y') }} ({{ $period->tanggal_mulai->format('d/m/Y') }} – {{ $period->tanggal_selesai->format('d/m/Y') }})</option>@endforeach</select></label>
    <label class="block text-sm font-semibold">Tanggal transaksi<input name="tanggal" type="date" required value="{{ $field('tanggal', $defaultDate) }}" data-today="{{ today()->toDateString() }}" max="{{ today()->toDateString() }}" class="mt-2 w-full rounded-xl border border-slate-300 p-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600"></label>
    <label class="block text-sm font-semibold">Nominal (Rp)<input name="nominal" type="number" required min="0.01" max="9999999999999.99" step="0.01" value="{{ $field('nominal', $record?->nominal) }}" class="mt-2 w-full rounded-xl border border-slate-300 p-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600"></label>
    <label class="block text-sm font-semibold">Keterangan<textarea name="keterangan" rows="3" maxlength="255" required class="mt-2 w-full rounded-xl border border-slate-300 p-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">{{ $field('keterangan', $record?->keterangan) }}</textarea></label>
    <div class="flex justify-end gap-3"><button type="button" data-close-modal class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold">Batal</button><button type="submit" class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-700">{{ $record ? 'Simpan perubahan' : 'Simpan transaksi' }}</button></div>
</form>

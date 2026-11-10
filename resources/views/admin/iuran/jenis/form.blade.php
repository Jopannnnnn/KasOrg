@php
    $editing = isset($type);
    $modalId = $editing ? 'edit-jenis-'.$type->id : 'modal-jenis';
    $failed = $errors->any() && old('_modal') === $modalId;
@endphp
<form method="POST" action="{{ $editing ? route('admin.iuran.jenis.update', array_merge($filters ?? [], ['jenisIuran' => $type])) : route('admin.iuran.jenis.store', $filters ?? []) }}" class="ko-space-y-4">
    @csrf
    @if ($editing) @method('PUT') @endif
    <input type="hidden" name="_modal" value="{{ $modalId }}">
    <label class="form-label d-block ko-text-sm fw-semibold">Nama jenis iuran<input name="nama" value="{{ $failed ? old('nama') : ($type->nama ?? '') }}" required maxlength="100" class="form-control ko-mt-2 w-100 ko-radius-control border ko-border-slate-300 ko-p-3 ko-text-sm"></label>
    <label class="form-label d-block ko-text-sm fw-semibold">Nominal standar (Rp)<input name="nominal" type="number" min="0.01" max="9999999999999.99" step="0.01" value="{{ $failed ? old('nominal') : ($type->nominal ?? '') }}" required class="form-control ko-mt-2 w-100 ko-radius-control border ko-border-slate-300 ko-p-3 ko-text-sm"></label>
    <label class="form-label d-block ko-text-sm fw-semibold">Deskripsi (opsional)<textarea name="deskripsi" maxlength="255" rows="2" class="form-control ko-mt-2 w-100 ko-radius-control border ko-border-slate-300 ko-p-3 ko-text-sm">{{ $failed ? old('deskripsi') : ($type->deskripsi ?? '') }}</textarea></label>
    @php($activeValue = $failed ? old('aktif') : (int) ($type->aktif ?? true))
    <label class="form-label d-block ko-text-sm fw-semibold">Status<select name="aktif" required class="form-select ko-mt-2 w-100 ko-radius-control border ko-border-slate-300 bg-white ko-p-3 ko-text-sm"><option value="1" @selected((string) $activeValue === '1')>Aktif</option><option value="0" @selected((string) $activeValue === '0')>Nonaktif</option></select></label>
    <p class="ko-radius-control ko-bg-brand-50 ko-p-3 ko-text-xs ko-text-brand-700">Nominal standar hanya digunakan untuk tagihan baru. Tagihan dan riwayat pembayaran lama tidak berubah.</p>
    <div class="d-flex justify-content-end ko-gap-3"><button type="button" data-close-modal class="btn ko-button ko-radius-control border ko-border-slate-200 ko-px-5 ko-py-2-5 ko-text-sm fw-semibold">Batal</button><button type="submit" class="btn ko-button ko-radius-control ko-bg-brand-600 ko-px-5 ko-py-2-5 ko-text-sm fw-bold text-white ko-hover-bg-brand-700">Simpan jenis iuran</button></div>
</form>

@extends('layouts.app', ['title' => 'Jenis Iuran - KasOrg', 'heading' => 'Jenis Iuran', 'role' => 'admin'])

@section('body-class', 'ko-jenis-page')
@push('styles')
    <link href="{{ asset('css/kasorg-jenis-iuran.css') }}" rel="stylesheet">
@endpush

@section('content')
<main class="ko-jenis-content flex-grow-1 ko-space-y-8 overflow-y-auto ko-p-6 ko-md-p-8">
    <div class="d-flex flex-column justify-content-between ko-gap-4 ko-sm-flex-row ko-sm-items-center">
        <div><h2 class="ko-text-2xl ko-fw-heavy ko-tracking-tight ko-text-slate-900">Kelola jenis iuran</h2><p class="ko-mt-0-5 ko-text-xs ko-text-slate-500 ko-sm-text-sm">Atur nominal standar tanpa mengubah tagihan dan riwayat yang sudah ada.</p></div>
        <div class="d-flex flex-wrap ko-gap-2">
            <a href="{{ route('admin.iuran') }}" class="ko-radius-control border ko-border-slate-200 bg-white ko-px-4 ko-py-2-5 ko-text-sm fw-bold ko-text-slate-700">Kembali ke tagihan</a>
            <button type="button" data-open-modal="modal-jenis" class="btn ko-button d-inline-flex align-items-center ko-gap-2 ko-radius-control ko-bg-brand-600 ko-px-4 ko-py-2-5 ko-text-sm fw-bold text-white shadow ko-shadow-brand-600-20 ko-hover-bg-brand-700"><i data-lucide="plus" class="ko-h-4 ko-w-4"></i>Tambah jenis iuran</button>
        </div>
    </div>
    <x-bootstrap.feedback />
    <section class="ko-jenis-table-panel card overflow-hidden ko-radius-card border ko-border-slate-200 bg-white shadow-sm">
        <form method="GET" action="{{ route('admin.iuran.jenis.index') }}" class="ko-jenis-filter d-flex flex-column ko-gap-3 border-bottom ko-border-slate-100 ko-p-4 ko-sm-flex-row">
            <input name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="Cari nama atau deskripsi..." aria-label="Pencarian jenis iuran" class="form-control ko-min-width-zero flex-grow-1 ko-radius-control border ko-border-slate-200 ko-px-4 ko-py-2-5 ko-text-sm">
            <select name="status" aria-label="Filter status" class="form-select ko-radius-control border ko-border-slate-200 bg-white ko-px-4 ko-py-2-5 ko-text-sm"><option value="">Semua status</option><option value="aktif" @selected(($filters['status'] ?? '') === 'aktif')>Aktif</option><option value="nonaktif" @selected(($filters['status'] ?? '') === 'nonaktif')>Nonaktif</option></select>
            <button type="submit" class="btn ko-button ko-radius-control ko-bg-brand-600 ko-px-4 ko-py-2-5 ko-text-sm fw-bold text-white">Terapkan</button>
            <a href="{{ route('admin.iuran.jenis.index') }}" class="ko-radius-control border ko-border-slate-200 ko-px-4 ko-py-2-5 ko-text-sm fw-semibold">Reset</a>
        </form>
        <div class="table-responsive"><table class="table ko-table w-100 ko-border-collapse text-start ko-text-xs ko-sm-text-sm">
            <thead><tr class="border-bottom ko-border-slate-100 ko-bg-slate-50-70 ko-text--11px- fw-bold text-uppercase ko-tracking-wide ko-text-slate-400"><th class="ko-px-4 ko-py-3">Nama jenis iuran</th><th class="ko-px-4 ko-py-3">Nominal standar</th><th class="ko-px-4 ko-py-3">Deskripsi</th><th class="ko-px-4 ko-py-3">Status</th><th class="ko-px-4 ko-py-3 text-end">Aksi</th></tr></thead>
            <tbody class="ko-table-lines ko-divider ko-text-slate-700">
                @forelse ($types as $item)
                    <tr class="ko-transition ko-hover-bg-slate-50-80">
                        <td class="ko-px-4 ko-py-4 fw-bold ko-text-slate-900">{{ $item->nama }}<div class="ko-mt-1 ko-text-xs fw-normal ko-text-slate-500">{{ $item->tagihan_iuran_count }} tagihan</div></td>
                        <td class="text-nowrap ko-px-4 ko-py-4 fw-bold">{{ \App\Services\RingkasanKasService::rupiah($item->nominal) }}</td>
                        <td class="ko-max-w-xs text-break ko-px-4 ko-py-4">{{ $item->deskripsi ?? '—' }}</td>
                        <td class="ko-px-4 ko-py-4"><span class="d-inline-flex rounded-pill ko-px-2-5 ko-py-1 ko-text-xs fw-bold {{ $item->aktif ? 'ko-bg-emerald-50 ko-text-emerald-700' : 'ko-bg-slate-100 ko-text-slate-600' }}">{{ $item->aktif ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td class="ko-px-4 ko-py-4"><div class="ko-jenis-actions d-flex flex-wrap justify-content-end ko-gap-3">
                            <button type="button" data-open-modal="edit-jenis-{{ $item->id }}" class="d-inline-flex align-items-center ko-gap-1 ko-text-xs fw-bold ko-text-brand-700"><i data-lucide="pencil" class="ko-h-3-5 ko-w-3-5"></i>Edit</button>
                            <form method="POST" action="{{ route('admin.iuran.jenis.status', array_merge($filters, ['jenisIuran' => $item])) }}">@csrf @method('PATCH')<input type="hidden" name="aktif" value="{{ $item->aktif ? '0' : '1' }}"><button type="submit" class="form-control btn ko-button ko-text-xs fw-bold ko-text-slate-600">{{ $item->aktif ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                            @if ($item->tagihan_iuran_count === 0)
                                <button type="button" data-open-modal="hapus-jenis-{{ $item->id }}" class="ko-text-xs fw-bold ko-text-red-600">Hapus</button>
                            @else
                                <span class="ko-text-xs ko-text-slate-400" title="Riwayat dilindungi; gunakan penonaktifan">Riwayat dilindungi</span>
                            @endif
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="ko-py-10 text-center ko-text-sm ko-text-slate-500">Tidak ada jenis iuran yang sesuai.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        {{ $types->links('admin.anggota.pagination', ['bootstrap' => true]) }}
    </section>
</main>
<x-bootstrap.modal id="modal-jenis" title="Tambah jenis iuran">
    @include('admin.iuran.jenis.form')
</x-bootstrap.modal>
@foreach ($types as $item)
    <x-bootstrap.modal :id="'edit-jenis-'.$item->id" title="Edit jenis iuran">
        @include('admin.iuran.jenis.form', ['type' => $item])
    </x-bootstrap.modal>
    @if ($item->tagihan_iuran_count === 0)
        <x-bootstrap.modal :id="'hapus-jenis-'.$item->id" title="Hapus jenis iuran">
            <form method="POST" action="{{ route('admin.iuran.jenis.destroy', array_merge($filters, ['jenisIuran' => $item])) }}" data-jenis-delete-form class="ko-space-y-4">
                @csrf @method('DELETE')
                <input type="hidden" name="_modal" value="hapus-jenis-{{ $item->id }}">
                <p class="ko-text-sm ko-text-slate-600">Hapus <strong>{{ $item->nama }}</strong>? Hanya jenis iuran tanpa tagihan yang dapat dihapus. Utamakan penonaktifan jika masih diperlukan.</p>
                <label class="form-label d-flex align-items-start ko-gap-2 ko-text-sm"><input type="checkbox" name="konfirmasi" value="1" required class="form-check-input ko-mt-1 rounded ko-border-slate-300">Saya mengonfirmasi penghapusan jenis iuran ini.</label>
                <div class="d-flex justify-content-end ko-gap-3"><button type="button" data-close-modal class="btn ko-button ko-radius-control border ko-border-slate-200 ko-px-5 ko-py-2-5 ko-text-sm fw-semibold">Batal</button><button type="submit" class="btn ko-button ko-radius-control ko-bg-red-600 ko-px-5 ko-py-2-5 ko-text-sm fw-bold text-white ko-disabled-cursor-wait ko-disabled-opacity-60">Hapus jenis iuran</button></div>
            </form>
        </x-bootstrap.modal>
    @endif
@endforeach
@endsection
@push('scripts')
<script>
(() => {
    // Custom openers use Bootstrap's modal API, not its data-toggle handler.
    // Restore the original keyboard focus after cancel, Escape or close.
    document.querySelectorAll('[data-bootstrap-finance-modal]').forEach(modal => {
        let opener = null;
        modal.addEventListener('show.bs.modal', event => {
            opener = event.relatedTarget instanceof HTMLElement ? event.relatedTarget : null;
        });
        modal.addEventListener('hidden.bs.modal', () => {
            if (opener?.isConnected && !opener.disabled) opener.focus();
            opener = null;
        });
    });
    const forms = [...document.querySelectorAll('[data-jenis-delete-form]')];
    forms.forEach(form => form.addEventListener('submit', event => {
        if (event.defaultPrevented) return;
        if (form.dataset.submitting === 'true') {
            event.preventDefault();
            return;
        }
        form.dataset.submitting = 'true';
        form.setAttribute('aria-busy', 'true');
        form.querySelector('button[type="submit"]').disabled = true;
    }));
    // Browser Back/BFCache dapat mengembalikan DOM dengan tombol masih disabled.
    window.addEventListener('pageshow', () => forms.forEach(form => {
        delete form.dataset.submitting;
        form.removeAttribute('aria-busy');
        form.querySelector('button[type="submit"]').disabled = false;
    }));
})();
</script>
@endpush
@include('bootstrap.keuangan.modal-scripts')

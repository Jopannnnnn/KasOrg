@extends('layouts.app', [
    'title' => 'Data Anggota - KasOrg',
    'heading' => 'Data Anggota',
    'role' => 'admin',
])

@php($showForm = session()->has('anggota_form') && $errors->any())

@section('body-class', 'ko-members-page')
@push('styles')
    <link href="{{ asset('css/kasorg-members.css') }}" rel="stylesheet">
@endpush

@section('content')
<main class="ko-members-content flex-grow-1 ko-space-y-8 overflow-y-auto ko-p-6 ko-md-p-8">
    <div class="d-flex flex-column justify-content-between ko-gap-4 ko-sm-flex-row ko-sm-items-center">
        <div>
            <h2 class="ko-text-2xl ko-fw-heavy ko-tracking-tight ko-text-slate-900">Kelola data anggota</h2>
            <p class="ko-mt-0-5 ko-text-xs ko-text-slate-500 ko-sm-text-sm">Kelola profil dan status akun anggota. Data disimpan ke database.</p>
        </div>
        @can('create', \App\Models\User::class)
            <button type="button" id="tambah-anggota" class="btn ko-button d-inline-flex align-self-start align-items-center ko-gap-2 ko-radius-control ko-bg-brand-600 ko-px-4 ko-py-2-5 ko-text-xs fw-bold text-white shadow ko-shadow-brand-600-20 ko-transition ko-hover-bg-brand-700 ko-sm-self-auto ko-sm-text-sm">
                <i data-lucide="plus" class="ko-h-4 ko-w-4"></i> Tambah anggota
            </button>
        @endcan
    </div>

    @if (session('success'))
        <div class="alert alert-primary ko-radius-control border ko-border-blue-100 ko-bg-brand-50 ko-px-4 ko-py-3 ko-text-xs fw-semibold ko-text-brand-700" role="status">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger ko-radius-control border ko-border-red-200 ko-bg-red-50 ko-px-4 ko-py-3 ko-text-xs ko-text-red-700" role="alert">
            <p class="fw-bold">Data belum dapat disimpan. Periksa kembali isian berikut:</p>
            <ul class="ko-mt-2 ko-list-inside ko-list-disc">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="ko-members-stats row g-3">
        <div class="col-12 col-sm-4"><article class="card h-100 d-flex flex-row align-items-center justify-content-between ko-radius-card border ko-border-slate-200 bg-white ko-p-5 shadow-sm">
            <div><span class="ko-text-xs fw-semibold ko-text-slate-500">Total anggota</span><div class="ko-mt-1 ko-text-2xl ko-fw-heavy ko-text-slate-900">{{ $stats['total'] }}</div></div>
            <span class="d-flex ko-h-10 ko-w-10 align-items-center justify-content-center ko-radius-control ko-bg-blue-50 ko-text-brand-600"><i data-lucide="users" class="ko-h-4 ko-w-4"></i></span>
        </article></div>
        <div class="col-12 col-sm-4"><article class="card h-100 d-flex flex-row align-items-center justify-content-between ko-radius-card border ko-border-slate-200 bg-white ko-p-5 shadow-sm">
            <div><span class="ko-text-xs fw-semibold ko-text-slate-500">Anggota aktif</span><div class="ko-mt-1 ko-text-2xl ko-fw-heavy ko-text-slate-900">{{ $stats['aktif'] }}</div></div>
            <span class="d-flex ko-h-10 ko-w-10 align-items-center justify-content-center ko-radius-control ko-bg-emerald-50 ko-text-emerald-600"><i data-lucide="check" class="ko-h-4 ko-w-4"></i></span>
        </article></div>
        <div class="col-12 col-sm-4"><article class="card h-100 d-flex flex-row align-items-center justify-content-between ko-radius-card border ko-border-slate-200 bg-white ko-p-5 shadow-sm">
            <div><span class="ko-text-xs fw-semibold ko-text-slate-500">Anggota nonaktif</span><div class="ko-mt-1 ko-text-2xl ko-fw-heavy ko-text-slate-900">{{ $stats['nonaktif'] }}</div></div>
            <span class="d-flex ko-h-10 ko-w-10 align-items-center justify-content-center ko-radius-control ko-bg-amber-50 ko-text-amber-600"><i data-lucide="clock" class="ko-h-4 ko-w-4"></i></span>
        </article></div>
    </section>

    <section class="ko-members-table-panel card overflow-hidden ko-radius-card border ko-border-slate-200 bg-white shadow-sm">
        <form method="GET" action="{{ route('admin.anggota') }}" class="ko-members-toolbar d-flex flex-column align-items-center justify-content-between ko-gap-3 border-bottom ko-border-slate-100 ko-p-4 ko-sm-flex-row ko-sm-p-5">
            <label class="ko-members-search form-label position-relative w-100 ko-sm-w-80">
                <span class="visually-hidden">Cari anggota berdasarkan nama, NIM, atau email; tekan Enter</span>
                <i data-lucide="search" class="position-absolute ko-left-3-5 top-50 ko-h-4 ko-w-4 translate-middle-y ko-text-slate-400"></i>
                <input id="cari-anggota" name="q" type="search" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="Cari nama, NIM, atau email..." class="form-control w-100 ko-radius-control border ko-border-slate-200 ko-bg-slate-50 ko-py-2-5 ko-pl-10 ko-pr-4 ko-text-xs ko-transition ko-focus-outline-none ko-focus-ring-2 ko-focus-ring-brand-600 ko-sm-text-sm">
            </label>
            <div class="ko-members-controls d-flex w-100 flex-wrap align-items-center ko-gap-2 ko-sm-w-auto">
                <label class="form-label visually-hidden" for="filter-status-anggota">Filter status akun</label>
                <select id="filter-status-anggota" name="status" class="form-select ko-radius-control border ko-border-slate-200 bg-white ko-px-3 ko-py-2-5 ko-text-xs fw-semibold ko-text-slate-700">
                    <option value="">Semua status akun</option>
                    <option value="aktif" @selected(($filters['status'] ?? '') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(($filters['status'] ?? '') === 'nonaktif')>Nonaktif</option>
                </select>
                <label class="form-label visually-hidden" for="per-page-anggota">Data per halaman</label>
                <select id="per-page-anggota" name="per_page" class="form-select ko-radius-control border ko-border-slate-200 bg-white ko-px-3 ko-py-2-5 ko-text-xs fw-semibold ko-text-slate-700">
                    @foreach ([5, 10, 25, 50] as $size)
                        <option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 5) === $size)>{{ $size }} data</option>
                    @endforeach
                </select>
                <button type="submit" class="btn ko-button d-inline-flex align-items-center justify-content-center ko-gap-2 ko-radius-control border ko-border-slate-200 ko-px-3-5 ko-py-2-5 ko-text-xs fw-bold ko-text-slate-700 ko-transition ko-hover-bg-slate-50"><i data-lucide="sliders-horizontal" class="ko-h-3-5 ko-w-3-5 ko-text-slate-500"></i>Filter</button>
                <div class="d-flex align-items-center ko-gap-2 ko-radius-control border ko-border-slate-200 ko-bg-slate-50 ko-px-3 ko-py-2 ko-text-xs fw-bold ko-text-slate-700"><i data-lucide="calendar" class="ko-h-3-5 ko-w-3-5 ko-text-slate-500"></i>{{ now()->locale('id')->translatedFormat('F Y') }}</div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table ko-table w-100 ko-border-collapse text-start ko-text-xs ko-sm-text-sm">
                <thead><tr class="border-bottom ko-border-slate-100 ko-bg-slate-50-70 ko-text--11px- fw-bold text-uppercase ko-tracking-wide ko-text-slate-400"><th class="ko-px-4 ko-py-3">NIM / ID</th><th class="ko-px-4 ko-py-3">Nama Anggota</th><th class="ko-px-4 ko-py-3">Email</th><th class="ko-px-4 ko-py-3">Iuran</th><th class="ko-px-4 ko-py-3 text-end">Aksi</th></tr></thead>
                <tbody id="tabel-anggota" class="ko-table-lines ko-divider ko-text-slate-700">
                    @forelse ($members as $row)
                        <tr class="anggota-row ko-transition ko-hover-bg-slate-50-80" data-status="{{ $row->status_akun }}" data-update-url="{{ route('admin.anggota.update', $row) }}">
                            <td class="nim-anggota ko-px-4 ko-py-4 font-monospace fw-medium ko-text-slate-500">{{ $row->nim }}</td>
                            <td class="ko-px-4 ko-py-4">
                                <span class="nama-anggota d-block fw-bold ko-text-slate-900">{{ $row->name }}</span>
                                <span class="ko-mt-1 d-block ko-text-xs ko-text-slate-500">{{ $row->role->nama }}</span>
                                <span class="ko-mt-2 d-inline-block rounded-pill ko-px-2-5 ko-py-1 ko-text-xs fw-bold {{ $row->isAktif() ? 'ko-bg-emerald-50 ko-text-emerald-600' : 'ko-bg-amber-50 ko-text-amber-600' }}">{{ $row->isAktif() ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td class="email-anggota ko-px-4 ko-py-4 ko-text-slate-500">{{ $row->email }}</td>
                            <td class="ko-px-4 ko-py-4"><span class="rounded-pill ko-bg-slate-100 ko-px-2-5 ko-py-1 ko-text-xs fw-bold ko-text-slate-500">Belum terhubung</span></td>
                            <td class="ko-px-4 ko-py-4 text-end">
                                <div class="ko-members-actions d-inline-flex align-items-center ko-gap-3">
                                    <a href="{{ route('admin.iuran') }}" title="Kelola tagihan anggota" class="d-inline-flex align-items-center ko-gap-1 ko-text-xs fw-semibold ko-text-slate-600 ko-transition ko-hover-text-brand-600"><i data-lucide="banknote" class="ko-h-3-5 ko-w-3-5"></i>Input iuran</a>
                                    @can('update', $row)
                                        <button type="button" class="btn ko-button edit-anggota ko-text-slate-400 ko-hover-text-slate-600" aria-label="Edit {{ $row->name }}"><i data-lucide="pencil" class="ko-h-3-5 ko-w-3-5"></i></button>
                                        <form method="POST" action="{{ route('admin.anggota.status', $row) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status_akun" value="{{ $row->isAktif() ? 'nonaktif' : 'aktif' }}">
                                            <button type="submit" title="{{ $row->isAktif() ? 'Nonaktifkan' : 'Aktifkan' }} akun" class="btn ko-button ko-text-slate-400 ko-hover-text-brand-600" aria-label="{{ $row->isAktif() ? 'Nonaktifkan' : 'Aktifkan' }} {{ $row->name }}"><i data-lucide="{{ $row->isAktif() ? 'user-x' : 'user-check' }}" class="ko-h-3-5 ko-w-3-5"></i></button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.anggota.destroy', $row) }}" class="arsip-anggota">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn ko-button ko-text-slate-400 ko-hover-text-rose-600" aria-label="Arsipkan {{ $row->name }}"><i data-lucide="trash-2" class="ko-h-3-5 ko-w-3-5"></i></button>
                                        </form>
                                    @else
                                        <span class="ko-text-xs fw-semibold ko-text-slate-400">Akun dilindungi</span>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="ko-px-4 ko-py-10 text-center ko-text-sm ko-text-slate-500">Tidak ada anggota yang sesuai pencarian.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $members->onEachSide(1)->links('admin.anggota.pagination', ['bootstrap' => true]) }}
    </section>
</main>

<div id="modal-anggota" class="modal fade" tabindex="-1" aria-hidden="true" aria-labelledby="judul-modal-anggota" data-reopen="{{ $showForm ? 'true' : 'false' }}">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable ko-member-dialog"><div class="modal-content">
        <div class="modal-header d-flex align-items-center justify-content-between border-bottom ko-border-slate-100 ko-px-6 ko-py-5"><h2 id="judul-modal-anggota" class="ko-text-xl fw-bold ko-text-slate-900">{{ $formMember ? 'Edit anggota' : 'Tambah anggota' }}</h2><button id="tutup-modal-anggota" type="button" class="btn ko-button ko-radius-small border ko-border-slate-200 ko-p-2" aria-label="Tutup"><i data-lucide="x" class="ko-h-5 ko-w-5"></i></button></div>
        <form id="form-anggota" method="POST" action="{{ $formMember ? route('admin.anggota.update', $formMember) : route('admin.anggota.store') }}" data-store-url="{{ route('admin.anggota.store') }}" class="ko-space-y-4 ko-p-6">
            @csrf
            <input type="hidden" id="anggota-method" name="_method" value="PUT" @disabled(! $formMember)>
            <label class="form-label d-block ko-text-sm fw-semibold ko-text-slate-700">NIM / ID<input id="anggota-nim" name="nim" value="{{ old('nim', $formMember?->nim) }}" required maxlength="20" inputmode="numeric" pattern="[0-9]+" class="form-control ko-mt-2 w-100 ko-radius-control border ko-border-slate-300 ko-px-4 ko-py-3 ko-text-sm ko-focus-outline-none ko-focus-ring-2 ko-focus-ring-brand-100" placeholder="Contoh: 24170050"></label>
            @error('nim')<p class="alert alert-danger ko-text-xs ko-text-red-600" role="alert">{{ $message }}</p>@enderror
            <label class="form-label d-block ko-text-sm fw-semibold ko-text-slate-700">Nama anggota<input id="anggota-nama" name="name" value="{{ old('name', $formMember?->name) }}" required maxlength="255" class="form-control ko-mt-2 w-100 ko-radius-control border ko-border-slate-300 ko-px-4 ko-py-3 ko-text-sm ko-focus-outline-none ko-focus-ring-2 ko-focus-ring-brand-100" placeholder="Nama lengkap"></label>
            @error('name')<p class="alert alert-danger ko-text-xs ko-text-red-600" role="alert">{{ $message }}</p>@enderror
            <label class="form-label d-block ko-text-sm fw-semibold ko-text-slate-700">Email<input id="anggota-email" name="email" type="email" value="{{ old('email', $formMember?->email) }}" required maxlength="255" class="form-control ko-mt-2 w-100 ko-radius-control border ko-border-slate-300 ko-px-4 ko-py-3 ko-text-sm ko-focus-outline-none ko-focus-ring-2 ko-focus-ring-brand-100" placeholder="nama@kampus.ac.id"></label>
            @error('email')<p class="alert alert-danger ko-text-xs ko-text-red-600" role="alert">{{ $message }}</p>@enderror
            <p class="ko-text-xs ko-text-slate-500">Role: <span class="fw-bold">Anggota</span> (ditetapkan sistem).</p>
            <label class="form-label d-block ko-text-sm fw-semibold ko-text-slate-700">Status akun
                <select id="anggota-status" name="status_akun" required class="form-select ko-mt-2 w-100 ko-radius-control border ko-border-slate-300 bg-white ko-px-4 ko-py-3 ko-text-sm ko-focus-outline-none ko-focus-ring-2 ko-focus-ring-brand-100">
                    <option value="aktif" @selected(old('status_akun', $formMember?->status_akun ?? 'aktif') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(old('status_akun', $formMember?->status_akun) === 'nonaktif')>Nonaktif</option>
                </select>
            </label>
            @error('status_akun')<p class="alert alert-danger ko-text-xs ko-text-red-600" role="alert">{{ $message }}</p>@enderror
            <fieldset id="anggota-password-fields" class="ko-space-y-4 {{ $formMember ? 'd-none' : '' }}" @disabled($formMember !== null)>
                @foreach (['anggota-password' => ['password', 'Kata sandi awal'], 'anggota-password-confirmation' => ['password_confirmation', 'Konfirmasi kata sandi']] as $inputId => [$inputName, $inputLabel])
                    <div>
                        <label for="{{ $inputId }}" class="form-label d-block ko-text-sm fw-semibold ko-text-slate-700">{{ $inputLabel }}</label>
                        <div class="position-relative ko-mt-2">
                            <input id="{{ $inputId }}" name="{{ $inputName }}" type="password" autocomplete="new-password" required minlength="12" maxlength="72" class="form-control w-100 ko-radius-control border ko-border-slate-300 ko-py-3 ko-pl-4 ko-pr-12 ko-text-sm ko-focus-outline-none ko-focus-ring-2 ko-focus-ring-brand-100">
                            <button type="button" data-password-toggle="{{ $inputId }}" aria-controls="{{ $inputId }}" aria-pressed="false" aria-label="Tampilkan {{ strtolower($inputLabel) }}" data-label="{{ strtolower($inputLabel) }}" class="btn ko-button position-absolute ko-right-2 top-50 d-inline-flex ko-h-8 ko-w-8 translate-middle-y align-items-center justify-content-center ko-radius-small border ko-border-transparent bg-transparent ko-p-2 ko-text-slate-500 ko-hover-text-brand-600 ko-focus-outline-none ko-focus-ring-2 ko-focus-ring-brand-600"><i data-lucide="eye" class="ko-h-4 ko-w-4 flex-shrink-0" aria-hidden="true"></i></button>
                        </div>
                    </div>
                @endforeach
                <p class="ko-text-xs ko-text-slate-500">Gunakan password manager untuk membuat kata sandi unik, minimal 12 karakter, dengan huruf besar/kecil, angka dan simbol. Sampaikan hanya kepada pemilik akun melalui saluran privat.</p>
                @error('password')<p class="alert alert-danger ko-text-xs ko-text-red-600" role="alert">{{ $message }}</p>@enderror
            </fieldset>
            <p class="ko-radius-control ko-bg-brand-50 ko-p-3 ko-text-xs ko-text-brand-700">Perubahan disimpan ke database. Pengarsipan memakai soft delete; riwayat keuangan tetap tersimpan.</p>
            <div class="d-flex justify-content-end ko-gap-3 ko-pt-2"><button id="batal-modal-anggota" type="button" class="btn ko-button ko-radius-control border ko-border-slate-200 ko-px-5 ko-py-2-5 ko-text-sm fw-semibold">Batal</button><button type="submit" class="btn ko-button ko-radius-control ko-bg-brand-600 ko-px-5 ko-py-2-5 ko-text-sm fw-bold text-white">Simpan anggota</button></div>
        </form>
    </div></div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal-anggota');
    const dialog = bootstrap.Modal.getOrCreateInstance(modal);
    const form = document.getElementById('form-anggota');
    const passwordFields = document.getElementById('anggota-password-fields');
    const setPasswordVisibility = (button, visible) => {
        document.getElementById(button.dataset.passwordToggle).type = visible ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(visible));
        button.setAttribute('aria-label', `${visible ? 'Sembunyikan' : 'Tampilkan'} ${button.dataset.label}`);
        const icon = document.createElement('i');
        icon.dataset.lucide = visible ? 'eye-off' : 'eye';
        icon.className = 'ko-h-4 ko-w-4 flex-shrink-0';
        icon.setAttribute('aria-hidden', 'true');
        button.replaceChildren(icon);
        // Batasi penggantian SVG ke tombol ini; ikon tombol lain tetap utuh.
        window.lucide?.createIcons({ root: button });
    };
    passwordFields.querySelectorAll('[data-password-toggle]').forEach(button => {
        button.addEventListener('click', () => setPasswordVisibility(button, document.getElementById(button.dataset.passwordToggle).type === 'password'));
    });
    const resetPasswords = () => {
        passwordFields.querySelectorAll('input').forEach(input => { input.value = ''; });
        passwordFields.querySelectorAll('[data-password-toggle]').forEach(button => setPasswordVisibility(button, false));
    };
    let trigger = null;
    const openModal = (row = null, button = null) => {
        trigger = button;
        form.reset();
        form.action = row ? row.dataset.updateUrl : form.dataset.storeUrl;
        document.getElementById('anggota-method').disabled = !row;
        document.getElementById('judul-modal-anggota').textContent = row ? 'Edit anggota' : 'Tambah anggota';
        document.getElementById('anggota-nim').value = row?.querySelector('.nim-anggota').textContent.trim() ?? '';
        document.getElementById('anggota-nama').value = row?.querySelector('.nama-anggota').textContent.trim() ?? '';
        document.getElementById('anggota-email').value = row?.querySelector('.email-anggota').textContent.trim() ?? '';
        document.getElementById('anggota-status').value = row?.dataset.status ?? 'aktif';
        passwordFields.disabled = !!row;
        passwordFields.classList.toggle('d-none', !!row);
        resetPasswords();
        dialog.show(button);
    };
    const closeModal = () => {
        dialog.hide();
    };
    document.getElementById('tambah-anggota')?.addEventListener('click', event => openModal(null, event.currentTarget));
    document.querySelectorAll('.edit-anggota').forEach(button => button.addEventListener('click', () => openModal(button.closest('tr'), button)));
    ['tutup-modal-anggota', 'batal-modal-anggota'].forEach(id => document.getElementById(id).addEventListener('click', closeModal));
    modal.addEventListener('shown.bs.modal', () => document.getElementById('anggota-nim').focus());
    modal.addEventListener('hidden.bs.modal', () => { resetPasswords(); trigger?.focus(); });
    if (modal.dataset.reopen === 'true') dialog.show();
    document.querySelectorAll('.arsip-anggota').forEach(form => form.addEventListener('submit', event => {
        if (!window.confirm('Arsipkan anggota ini? Akun akan dinonaktifkan, tetapi riwayat keuangan tidak dihapus.')) event.preventDefault();
    }));
    // Tidak mencegat submit tambah/edit: browser mengirim form ke controller Laravel.
});
</script>
@endpush

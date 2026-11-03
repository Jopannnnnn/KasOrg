@extends('layouts.app', [
    'title' => 'Data Anggota - KasOrg',
    'heading' => 'Data Anggota',
    'role' => 'admin',
    'userName' => 'Jovan Wijaya',
    'userInitials' => 'JW',
])

@php
    // Data statis khusus preview UI. Belum berasal dari database.
    $anggota = [
        ['nim' => '25170004', 'nama' => 'Jovan Putra Wijaya', 'email' => 'jovan@kampus.ac.id', 'status' => 'Lunas'],
        ['nim' => '25170001', 'nama' => 'Juniarti Siadari', 'email' => 'juniarti@kampus.ac.id', 'status' => 'Lunas'],
        ['nim' => '25170027', 'nama' => 'Kent Muhammad Rafli', 'email' => 'kent@kampus.ac.id', 'status' => 'Belum Lunas'],
        ['nim' => '25170025', 'nama' => 'Refan Rhizqi Wintoro', 'email' => 'refan@kampus.ac.id', 'status' => 'Belum Lunas'],
        ['nim' => '25170031', 'nama' => 'Nadia Maharani', 'email' => 'nadia@kampus.ac.id', 'status' => 'Lunas'],
    ];
@endphp

@section('content')
<main class="flex-1 space-y-8 overflow-y-auto p-6 md:p-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">Kelola data anggota</h2>
            <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Kelola profil anggota aktif dan pantau status iuran bulan ini.</p>
        </div>
        <button type="button" id="tambah-anggota" class="inline-flex self-start items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-brand-600/20 transition hover:bg-brand-700 sm:self-auto sm:text-sm">
            <i data-lucide="plus" class="h-4 w-4"></i> Tambah anggota
        </button>
    </div>

    <div id="anggota-preview-note" class="hidden rounded-xl border border-blue-100 bg-brand-50 px-4 py-3 text-xs font-semibold text-brand-700" role="status"></div>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <article class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div><span class="text-xs font-semibold text-slate-500">Total anggota</span><div class="mt-1 text-2xl font-extrabold text-slate-900">42</div></div>
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-brand-600"><i data-lucide="users" class="h-4 w-4"></i></span>
        </article>
        <article class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div><span class="text-xs font-semibold text-slate-500">Sudah lunas</span><div class="mt-1 text-2xl font-extrabold text-slate-900">30</div></div>
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i data-lucide="check" class="h-4 w-4"></i></span>
        </article>
        <article class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div><span class="text-xs font-semibold text-slate-500">Belum lunas</span><div class="mt-1 text-2xl font-extrabold text-slate-900">12</div></div>
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i data-lucide="clock" class="h-4 w-4"></i></span>
        </article>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col items-center justify-between gap-3 border-b border-slate-100 p-4 sm:flex-row sm:p-5">
            <label class="relative w-full sm:w-80">
                <span class="sr-only">Cari anggota</span>
                <i data-lucide="search" class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
                <input id="cari-anggota" type="search" placeholder="Cari nama, NIM, atau email..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-xs transition focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-600 sm:text-sm">
            </label>
            <div class="flex w-full items-center gap-2 sm:w-auto">
                <div class="relative flex-1 sm:flex-none">
                    <button id="filter-iuran" type="button" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-bold text-slate-700 transition hover:bg-slate-50" aria-expanded="false">
                        <i data-lucide="sliders-horizontal" class="h-3.5 w-3.5 text-slate-500"></i><span id="filter-iuran-label">Filter</span>
                    </button>
                    <div id="filter-iuran-menu" class="absolute right-0 z-20 mt-2 hidden min-w-40 overflow-hidden rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg">
                        <button type="button" data-filter-anggota="semua" class="block w-full rounded-lg px-3 py-2 text-left text-xs font-semibold hover:bg-slate-50">Semua status</button>
                        <button type="button" data-filter-anggota="Lunas" class="block w-full rounded-lg px-3 py-2 text-left text-xs font-semibold hover:bg-slate-50">Lunas</button>
                        <button type="button" data-filter-anggota="Belum Lunas" class="block w-full rounded-lg px-3 py-2 text-left text-xs font-semibold hover:bg-slate-50">Belum Lunas</button>
                    </div>
                </div>
                <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700"><i data-lucide="calendar" class="h-3.5 w-3.5 text-slate-500"></i>Maret 2026</div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-xs sm:text-sm">
                <thead><tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-bold uppercase tracking-wider text-slate-400"><th class="px-4 py-3">NIM / ID</th><th class="px-4 py-3">Nama Anggota</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Iuran Maret</th><th class="px-4 py-3 text-right">Aksi</th></tr></thead>
                <tbody id="tabel-anggota" class="divide-y divide-slate-100 text-slate-700">
                    @foreach ($anggota as $row)
                    <tr class="anggota-row transition hover:bg-slate-50/80" data-search="{{ strtolower($row['nim'].' '.$row['nama'].' '.$row['email']) }}" data-status="{{ $row['status'] }}">
                        <td class="px-4 py-4 font-mono font-medium text-slate-500">{{ $row['nim'] }}</td>
                        <td class="nama-anggota px-4 py-4 font-bold text-slate-900">{{ $row['nama'] }}</td>
                        <td class="email-anggota px-4 py-4 text-slate-500">{{ $row['email'] }}</td>
                        <td class="px-4 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $row['status'] === 'Lunas' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">{{ $row['status'] }}</span></td>
                        <td class="px-4 py-4 text-right">
                            <div class="inline-flex items-center gap-3">
                                <a href="{{ route('ui-preview.admin.iuran') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 transition hover:text-brand-600"><i data-lucide="banknote" class="h-3.5 w-3.5"></i>Input iuran</a>
                                <button type="button" class="edit-anggota text-slate-400 hover:text-slate-600" aria-label="Edit {{ $row['nama'] }}"><i data-lucide="pencil" class="h-3.5 w-3.5"></i></button>
                                <button type="button" class="hapus-preview text-slate-400 hover:text-rose-600" aria-label="Hapus {{ $row['nama'] }}"><i data-lucide="trash-2" class="h-3.5 w-3.5"></i></button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <p id="anggota-kosong" class="hidden px-4 py-10 text-center text-sm text-slate-500">Tidak ada anggota yang sesuai pencarian.</p>
        </div>
        <div class="flex flex-col items-center justify-between gap-3 border-t border-slate-100 p-4 text-xs text-slate-500 sm:flex-row"><span>Menampilkan 1–5 dari 42 data</span><div class="inline-flex items-center gap-1"><button class="rounded-lg border border-slate-200 px-3 py-1.5 text-slate-600 hover:bg-slate-50 disabled:opacity-50">Sebelumnya</button><button class="rounded-lg bg-brand-600 px-3 py-1.5 font-bold text-white">1</button><button class="rounded-lg border border-slate-200 px-3 py-1.5 text-slate-600 hover:bg-slate-50">2</button><button class="rounded-lg border border-slate-200 px-3 py-1.5 text-slate-600 hover:bg-slate-50">Berikutnya</button></div></div>
    </section>
</main>

<div id="modal-anggota" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4" role="dialog" aria-modal="true" aria-labelledby="judul-modal-anggota">
    <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5"><h2 id="judul-modal-anggota" class="text-xl font-bold text-slate-900">Tambah anggota</h2><button id="tutup-modal-anggota" type="button" class="rounded-lg border border-slate-200 p-2" aria-label="Tutup"><i data-lucide="x" class="h-5 w-5"></i></button></div>
        <form id="form-anggota" class="space-y-4 p-6">
            <label class="block text-sm font-semibold text-slate-700">NIM / ID<input id="anggota-nim" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-100" placeholder="Contoh: 25170050"></label>
            <label class="block text-sm font-semibold text-slate-700">Nama anggota<input id="anggota-nama" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-100" placeholder="Nama lengkap"></label>
            <label class="block text-sm font-semibold text-slate-700">Email<input id="anggota-email" type="email" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-100" placeholder="nama@kampus.ac.id"></label>
            <p class="rounded-xl bg-brand-50 p-3 text-xs text-brand-700">Preview frontend: perubahan hanya berlaku pada tampilan saat ini dan tidak disimpan ke database.</p>
            <div class="flex justify-end gap-3 pt-2"><button id="batal-modal-anggota" type="button" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold">Batal</button><button type="submit" class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white">Terapkan preview</button></div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const modal = document.getElementById('modal-anggota');
    const form = document.getElementById('form-anggota');
    const note = document.getElementById('anggota-preview-note');
    const search = document.getElementById('cari-anggota');
    const filter = document.getElementById('filter-iuran');
    const filterMenu = document.getElementById('filter-iuran-menu');
    let activeFilter = 'semua';
    let editedRow = null;

    const openModal = (row = null) => {
        editedRow = row;
        document.getElementById('judul-modal-anggota').textContent = row ? 'Edit anggota' : 'Tambah anggota';
        document.getElementById('anggota-nim').value = row?.children[0].textContent.trim() ?? '';
        document.getElementById('anggota-nama').value = row?.querySelector('.nama-anggota').textContent.trim() ?? '';
        document.getElementById('anggota-email').value = row?.querySelector('.email-anggota').textContent.trim() ?? '';
        modal.classList.replace('hidden', 'flex');
    };
    const closeModal = () => modal.classList.replace('flex', 'hidden');
    const render = () => {
        const query = search.value.toLowerCase();
        let visible = 0;
        document.querySelectorAll('.anggota-row').forEach(row => {
            const show = row.dataset.search.includes(query) && (activeFilter === 'semua' || row.dataset.status === activeFilter);
            row.classList.toggle('hidden', !show);
            if (show) visible++;
        });
        document.getElementById('anggota-kosong').classList.toggle('hidden', visible > 0);
    };

    document.getElementById('tambah-anggota').addEventListener('click', () => openModal());
    document.querySelectorAll('.edit-anggota').forEach(button => button.addEventListener('click', () => openModal(button.closest('tr'))));
    document.querySelectorAll('.hapus-preview').forEach(button => button.addEventListener('click', () => {
        note.textContent = 'Aksi hapus hanya preview; data anggota tidak dihapus.';
        note.classList.remove('hidden');
    }));
    [document.getElementById('tutup-modal-anggota'), document.getElementById('batal-modal-anggota')].forEach(button => button.addEventListener('click', closeModal));
    modal.addEventListener('click', event => { if (event.target === modal) closeModal(); });
    search.addEventListener('input', render);
    filter.addEventListener('click', () => {
        const willOpen = filterMenu.classList.contains('hidden');
        filterMenu.classList.toggle('hidden', !willOpen);
        filter.setAttribute('aria-expanded', String(willOpen));
    });
    document.querySelectorAll('[data-filter-anggota]').forEach(option => option.addEventListener('click', () => {
        activeFilter = option.dataset.filterAnggota;
        document.getElementById('filter-iuran-label').textContent = activeFilter === 'semua' ? 'Filter' : activeFilter;
        filterMenu.classList.add('hidden');
        filter.setAttribute('aria-expanded', 'false');
        render();
    }));
    form.addEventListener('submit', event => {
        event.preventDefault();
        if (editedRow) {
            editedRow.children[0].textContent = document.getElementById('anggota-nim').value;
            editedRow.querySelector('.nama-anggota').textContent = document.getElementById('anggota-nama').value;
            editedRow.querySelector('.email-anggota').textContent = document.getElementById('anggota-email').value;
        }
        note.textContent = editedRow ? 'Perubahan anggota diterapkan pada preview saja.' : 'Data anggota baru divalidasi untuk preview, tetapi tidak disimpan.';
        note.classList.remove('hidden');
        closeModal();
    });
})();
</script>
@endpush

<form method="GET" class="flex flex-col gap-3 p-5 md:flex-row">
    <label class="relative flex-1"><span class="sr-only">Cari nama, NIM, atau jenis iuran</span><i data-lucide="search" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i><input name="q" type="search" maxlength="100" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama, NIM, atau jenis iuran..." class="w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-4 outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100"></label>
    <select name="status" aria-label="Filter status" class="rounded-xl border border-slate-300 bg-white px-4 py-3 md:w-52">
        <option value="">Semua status</option>
        @foreach ($statusOptions as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach
    </select>
    <button type="submit" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">Cari / Filter</button>
</form>

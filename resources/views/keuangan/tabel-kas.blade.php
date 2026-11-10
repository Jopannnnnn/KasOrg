<div class="overflow-x-auto"><table class="w-full border-collapse text-left text-xs sm:text-sm"><thead><tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-400"><th class="px-3 py-3">Tanggal</th><th class="px-3 py-3">Jenis</th><th class="px-3 py-3">Kategori</th><th class="px-3 py-3">Keterangan</th><th class="px-3 py-3 text-right">Nominal</th></tr></thead><tbody class="divide-y divide-slate-100 text-slate-700">
    @forelse ($transactions as $item)
        <tr class="transition hover:bg-slate-50/70"><td class="px-3 py-3.5 font-medium">{{ $item['date'] }}</td><td class="px-3 py-3.5"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $item['positive'] ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">{{ $item['type'] }}</span></td><td class="px-3 py-3.5 font-semibold">{{ $item['category'] }}</td><td class="px-3 py-3.5 text-slate-500">{{ $item['description'] }}</td><td class="px-3 py-3.5 text-right font-bold {{ $item['positive'] ? 'text-emerald-600' : 'text-rose-600' }}">{{ $item['amount'] }}</td></tr>
    @empty
        <tr><td colspan="5" class="py-8 text-center text-sm text-slate-500">Tidak ada transaksi kas sah yang sesuai filter.</td></tr>
    @endforelse
</tbody></table></div>

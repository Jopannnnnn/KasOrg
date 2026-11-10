@props(['id', 'title'])
<div id="{{ $id }}" data-finance-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title">
    <div class="flex max-h-[92vh] w-full max-w-xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
        <header class="flex items-center justify-between border-b border-slate-100 px-6 py-5"><h2 id="{{ $id }}-title" class="text-xl font-bold">{{ $title }}</h2><button type="button" data-close-modal class="rounded-lg border border-slate-200 p-2 hover:bg-slate-50" aria-label="Tutup modal"><i data-lucide="x" class="h-5 w-5"></i></button></header>
        <div class="overflow-y-auto p-6">
            @if ($errors->any() && old('_modal') === $id)<x-keuangan-feedback />@endif
            {{ $slot }}
        </div>
    </div>
</div>

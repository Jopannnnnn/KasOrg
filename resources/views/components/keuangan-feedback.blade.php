@if (session('success'))
    <div role="status" class="my-4 rounded-xl border border-blue-100 bg-brand-50 p-4 text-sm text-brand-700">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div role="alert" class="my-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
        <p class="font-bold">Periksa kembali isian berikut:</p>
        <ul class="mt-2 list-inside list-disc">@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
    </div>
@endif

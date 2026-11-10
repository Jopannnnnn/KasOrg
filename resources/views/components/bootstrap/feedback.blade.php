@if (session('success'))
    <div role="status" class="alert alert-primary ko-my-4 ko-radius-control border ko-border-blue-100 ko-bg-brand-50 ko-p-4 ko-text-sm ko-text-brand-700">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div role="alert" class="alert alert-danger ko-my-4 ko-radius-control border ko-border-red-200 ko-bg-red-50 ko-p-4 ko-text-sm ko-text-red-700">
        <p class="fw-bold">Periksa kembali isian berikut:</p>
        <ul class="ko-mt-2 ko-list-inside ko-list-disc">@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
    </div>
@endif

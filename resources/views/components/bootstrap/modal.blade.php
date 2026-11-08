@props(['id', 'title'])
<div id="{{ $id }}" class="modal fade" tabindex="-1" aria-hidden="true" aria-labelledby="{{ $id }}-title" data-bootstrap-finance-modal>
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable ko-finance-dialog"><div class="modal-content">
        <header class="modal-header">
            <h2 id="{{ $id }}-title" class="modal-title ko-text-xl fw-bold">{{ $title }}</h2>
            <button type="button" class="btn ko-button border ko-radius-small p-2" data-bs-dismiss="modal" aria-label="Tutup modal"><i data-lucide="x" class="ko-h-5 ko-w-5"></i></button>
        </header>
        <div class="modal-body">
            @if ($errors->any() && old('_modal') === $id)<x-bootstrap.feedback />@endif
            {{ $slot }}
        </div>
    </div></div>
</div>

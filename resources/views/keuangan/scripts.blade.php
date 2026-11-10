@push('scripts')
<script>
(() => {
    let active = null, lastFocus = null;
    const close = () => { if (!active) return; active.classList.replace('flex', 'hidden'); active = null; document.body.style.overflow = ''; lastFocus?.focus(); };
    const open = (id, trigger) => {
        const modal = document.getElementById(id);
        if (!modal?.hasAttribute('data-finance-modal')) return;
        close(); active = modal; lastFocus = trigger;
        modal.classList.replace('hidden', 'flex'); document.body.style.overflow = 'hidden'; modal.querySelector('[data-close-modal]').focus();
    };
    document.querySelectorAll('[data-open-modal]').forEach(button => button.addEventListener('click', () => open(button.dataset.openModal, button)));
    document.querySelectorAll('[data-close-modal]').forEach(button => button.addEventListener('click', close));
    document.querySelectorAll('[data-finance-modal]').forEach(modal => modal.addEventListener('click', event => { if (event.target === modal) close(); }));
    document.addEventListener('keydown', event => {
        if (!active) return;
        if (event.key === 'Escape') close();
        if (event.key === 'Tab' && active) {
            const elements = [...active.querySelectorAll('button, input, select, textarea, a[href]')].filter(el => !el.disabled && el.getClientRects().length);
            const first = elements[0], last = elements.at(-1);
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
    });
    document.querySelectorAll('[data-payment-form]').forEach(form => {
        const method = form.querySelector('[name="metode"]'), area = form.querySelector('[data-upload-area]'), file = form.querySelector('[name="bukti"]');
        const update = () => { const transfer = method.value === 'transfer'; area.classList.toggle('hidden', !transfer); file.required = transfer; file.disabled = !transfer; if (!transfer) file.value = ''; };
        method.addEventListener('change', update); update();
        const image = form.querySelector('[data-proof-preview]'); let url = null;
        file.addEventListener('change', () => {
            if (url) URL.revokeObjectURL(url); image.removeAttribute('src'); image.classList.add('hidden');
            const selected = file.files[0];
            if (selected && ['image/jpeg', 'image/png', 'image/webp'].includes(selected.type) && selected.size <= 5 * 1024 * 1024) {
                url = URL.createObjectURL(selected); image.src = url; image.classList.remove('hidden');
            }
        });
    });
    const errorModal = @js($errors->any() ? old('_modal') : null);
    if (typeof errorModal === 'string') open(errorModal, null);
})();
</script>
@endpush

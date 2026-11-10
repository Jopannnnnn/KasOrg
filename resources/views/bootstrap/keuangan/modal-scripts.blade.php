@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Only this Bootstrap adapter is loaded on MVP pages; the legacy handler is not.
    document.querySelectorAll('[data-open-modal]').forEach(button => {
        button.addEventListener('click', () => {
            const target = document.getElementById(button.dataset.openModal);
            if (target?.hasAttribute('data-bootstrap-finance-modal')) {
                bootstrap.Modal.getOrCreateInstance(target).show(button);
            }
        });
    });
    document.querySelectorAll('[data-bootstrap-finance-modal] [data-close-modal]').forEach(button => {
        button.addEventListener('click', () => bootstrap.Modal.getInstance(button.closest('.modal'))?.hide());
    });
    const errorModal = @js($errors->any() ? old('_modal') : null);
    if (typeof errorModal === 'string') {
        const target = document.getElementById(errorModal);
        if (target?.hasAttribute('data-bootstrap-finance-modal')) bootstrap.Modal.getOrCreateInstance(target).show();
    }
});
</script>
@endpush

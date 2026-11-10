// Dijalankan pada HTML hasil render Laravel, dalam Chromium terisolasi.
window.addEventListener('load', async () => {
    const results = [];
    const check = (condition, name) => { results.push({name, passed: Boolean(condition)}); };
    const waitFor = async predicate => {
        const deadline = performance.now() + 4000;
        while (!predicate()) {
            if (performance.now() > deadline) throw new Error('State modal tidak tercapai');
            await new Promise(resolve => setTimeout(resolve, 25));
        }
    };
    const modalOpen = modal => modal.classList.contains('modal')
        ? modal.classList.contains('show') && modal.getAttribute('aria-modal') === 'true' && getComputedStyle(modal).display === 'block'
            && getComputedStyle(modal).opacity === '1' && getComputedStyle(modal.querySelector('.modal-dialog')).transform === 'none'
        : modal.classList.contains('flex') && !modal.classList.contains('hidden') && getComputedStyle(modal).display === 'flex';
    const modalClosed = modal => modal.classList.contains('modal')
        ? !modal.classList.contains('show') && modal.getAttribute('aria-hidden') === 'true' && getComputedStyle(modal).display === 'none'
            && !document.body.classList.contains('modal-open') && !document.querySelector('.modal-backdrop')
        : modal.classList.contains('hidden') && getComputedStyle(modal).display === 'none';
    try {
        const form = document.getElementById('form-anggota');
        const modal = document.getElementById('modal-anggota');
        let submitted = false;
        form.addEventListener('submit', event => { event.preventDefault(); submitted = true; });
        document.getElementById('tambah-anggota').click();
        await waitFor(() => modalOpen(modal));
        check(modalOpen(modal), 'Modal terbuka sesuai adapter Bootstrap/legacy');
        for (const id of ['anggota-password', 'anggota-password-confirmation']) {
            const field = document.getElementById(id);
            const button = document.querySelector(`[data-password-toggle="${id}"]`);
            check(button.type === 'button' && button.tabIndex === 0, `${id}: tombol dapat menerima fokus keyboard`);
            check(button.getAttribute('aria-controls') === id && button.getAttribute('aria-pressed') === 'false', `${id}: aksesibilitas awal`);
            field.value = 'NilaiSementaraUntukUji!';
            button.click();
            check(field.type === 'text' && button.getAttribute('aria-pressed') === 'true', `${id}: tampilkan`);
            check(button.getAttribute('aria-label').startsWith('Sembunyikan'), `${id}: label diperbarui`);
            check(button.querySelector('[data-lucide="eye-off"]') !== null, `${id}: ikon eye-off`);
            button.click();
            check(field.type === 'password' && field.value === 'NilaiSementaraUntukUji!', `${id}: sembunyikan tanpa mengubah nilai`);
            button.click();
        }
        check(!submitted, 'Tombol mata tidak submit');
        document.getElementById('batal-modal-anggota').click();
        await waitFor(() => modalClosed(modal));
        document.getElementById('tambah-anggota').click();
        await waitFor(() => modalOpen(modal));
        check([...form.querySelectorAll('[autocomplete="new-password"]')].every(input => input.type === 'password' && input.value === ''), 'Modal dibuka ulang mereset visibilitas dan nilai');
        check([...form.querySelectorAll('[autocomplete="new-password"]')].every(input => input.required && input.minLength === 12 && input.maxLength === 72), 'Batas validasi HTML tetap');
    } catch (error) {
        results.push({name: error.message, passed: false});
    }
    document.body.replaceChildren();
    const output = document.createElement('pre');
    output.id = 'kasorg-browser-results';
    output.textContent = JSON.stringify(results);
    document.body.append(output);
});

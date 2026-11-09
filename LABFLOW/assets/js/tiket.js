/* TIKET: baris daftar bisa diklik; form update di halaman detail. */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('tr.row-link').forEach(tr => tr.addEventListener('click', e => {
        if (e.target.closest('a,button')) return;
        location.href = tr.dataset.href;
    }));

    const f = document.getElementById('tkEdit');
    if (!f) return;
    f.addEventListener('submit', async e => {
        e.preventDefault();
        const btn = document.getElementById('btnTk');
        LF.busy(btn, true);
        const res = await LF.api('config/api_ticket.php', {
            action: 'update', id: +f.dataset.id,
            status: document.getElementById('fStatus').value,
            priority: document.getElementById('fPrio').value,
            technician: document.getElementById('fTech').value.trim(),
            note: document.getElementById('fNote').value.trim()
        });
        if (res.success) { LF.flash('Tiket diperbarui.'); location.reload(); }
        else { LF.busy(btn, false); LF.toast(res.message || 'Gagal memperbarui tiket.', 'error'); }
    });
});

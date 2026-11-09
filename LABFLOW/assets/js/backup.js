document.addEventListener('DOMContentLoaded', () => {
    const b = document.getElementById('btnBackup');
    if (b) b.addEventListener('click', async () => {
        LF.busy(b, true);
        const r = await LF.api('config/api_backup.php', { action: 'create' });
        if (r.success) { LF.flash('Backup tersimpan: ' + r.file); location.reload(); }
        else { LF.busy(b, false); LF.toast(r.message || 'Backup gagal.', 'error'); }
    });
    document.querySelectorAll('[data-del]').forEach(btn => btn.addEventListener('click', async () => {
        const ok = await LF.confirm({ title: 'Hapus file backup?', message: btn.dataset.del, confirmText: 'Ya, hapus', tone: 'danger' });
        if (!ok) return;
        const r = await LF.api('config/api_backup.php', { action: 'delete', file: btn.dataset.del });
        if (r.success) { LF.flash('Backup dihapus.'); location.reload(); } else LF.toast(r.message || 'Gagal menghapus.', 'error');
    }));
});

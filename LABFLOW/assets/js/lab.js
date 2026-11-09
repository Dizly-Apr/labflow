/* =========================================================
   DENAH LAB: tombol Maintenance / Set Active
========================================================= */
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btnMaint');
    if (!btn) return;

    btn.addEventListener('click', async () => {
        const goMaint = btn.dataset.status !== 'maintenance';
        const name = btn.dataset.name || 'lab ini';
        const ok = await LF.confirm({
            title: goMaint ? 'Set ke Maintenance?' : 'Kembalikan ke Active?',
            message: goMaint
                ? `${name} akan ditandai sedang maintenance dan tercatat di riwayat.`
                : `${name} akan kembali berstatus Active.`,
            confirmText: goMaint ? 'Ya, set Maintenance' : 'Ya, set Active',
            tone: goMaint ? 'warn' : 'primary',
            icon: goMaint ? 'wrench' : 'circle-check'
        });
        if (!ok) return;

        LF.busy(btn, true);
        const res = await LF.api('config/api_lab.php', { code: btn.dataset.code, status: goMaint ? 'maintenance' : 'active' });
        if (res.success) {
            LF.flash(goMaint ? `${name} sekarang berstatus Maintenance.` : `${name} kembali Active.`);
            location.reload();
        } else {
            LF.toast(res.message || 'Gagal mengubah status lab.', 'error');
            LF.busy(btn, false);
        }
    });
});

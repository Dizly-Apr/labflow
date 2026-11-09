/* =========================================================
   LAPORAN USER -> KOORDINATOR & ADMIN
   - role User : modal "Kirim Laporan" ([data-ur-open])
   - petugas   : form tanggapan, "Jadikan Tiket", hapus (laporan_masuk.php?id=)
   - semua     : baris tabel bisa diklik
========================================================= */
document.addEventListener('DOMContentLoaded', () => {
    const $ = id => document.getElementById(id);

    /* baris daftar bisa diklik */
    document.querySelectorAll('tr.row-link').forEach(tr => tr.addEventListener('click', e => {
        if (e.target.closest('a,button')) return;
        location.href = tr.dataset.href;
    }));

    /* ---------------- kirim laporan (User) ---------------- */
    const modal = $('urModal');
    if (modal && window.LF_UR) {
        const cfg = window.LF_UR, form = $('urForm');
        const lab = $('urLab'), pc = $('urPc'), msg = $('urMsg'), count = $('urCount');

        const fillPc = (selected = 0) => {
            const n = (cfg.labs[lab.value] || {}).pcs || 0;
            pc.innerHTML = '<option value="0">Seluruh lab / tidak spesifik</option>';
            for (let i = 1; i <= n; i++) pc.insertAdjacentHTML('beforeend', `<option value="${i}">PC ${i}</option>`);
            pc.value = String(selected && selected <= n ? selected : 0);
        };
        const updCount = () => { count.textContent = msg.value.length + ' / 1000'; };

        lab.addEventListener('change', () => fillPc(0));
        msg.addEventListener('input', updCount);

        const open = (labCode, pcNo) => {
            form.reset();
            lab.value = labCode && cfg.labs[labCode] ? labCode : '';
            fillPc(+pcNo || 0);
            updCount();
            LF.openModal(modal);
        };
        document.querySelectorAll('[data-ur-open]').forEach(b => b.addEventListener('click', () =>
            open(b.dataset.lab || '', b.dataset.pc || 0)));
        if (cfg.autoOpen) open(cfg.lab, cfg.pc);

        form.addEventListener('submit', async e => {
            e.preventDefault();
            const btn = $('btnUrSend');
            LF.busy(btn, true);
            const res = await LF.api('config/api_user_report.php', {
                action: 'create', lab: lab.value, pc: +pc.value || 0,
                category: $('urCat').value, priority: $('urPrio').value,
                subject: $('urSubject').value.trim(), message: msg.value.trim()
            });
            if (res.success) {
                LF.flash('Laporan terkirim ke Koordinator & Admin.');
                location.href = 'laporan_saya.php?id=' + res.id;
            } else {
                LF.busy(btn, false);
                LF.toast(res.message || 'Gagal mengirim laporan.', 'error');
            }
        });
    }

    /* ---------------- tanggapan petugas ---------------- */
    const ed = $('urEdit');
    if (!ed) return;
    const id = +ed.dataset.id;

    ed.addEventListener('submit', async e => {
        e.preventDefault();
        const btn = $('btnUrSave');
        LF.busy(btn, true);
        const res = await LF.api('config/api_user_report.php', {
            action: 'update', id, status: $('uStatus').value, response: $('uResp').value.trim()
        });
        if (res.success) { LF.flash('Laporan diperbarui. Pelapor akan melihat pembaruannya.'); location.reload(); }
        else { LF.busy(btn, false); LF.toast(res.message || 'Gagal menyimpan.', 'error'); }
    });

    const bt = $('btnUrTicket');
    if (bt) bt.addEventListener('click', async () => {
        const ok = await LF.confirm({
            title: 'Jadikan Tiket Perbaikan?',
            message: 'Tiket baru dibuat untuk PC ini dan terhubung ke laporan. Status laporan menjadi "Ditindaklanjuti".',
            confirmText: 'Ya, buat tiket', tone: 'warn', icon: 'wrench'
        });
        if (!ok) return;
        LF.busy(bt, true);
        const res = await LF.api('config/api_user_report.php', { action: 'to_ticket', id });
        if (res.success) { LF.flash('Tiket #' + res.ticket_id + ' dibuat.'); location.reload(); }
        else { LF.busy(bt, false); LF.toast(res.message || 'Gagal membuat tiket.', 'error'); }
    });

    const bd = $('btnUrDelete');
    if (bd) bd.addEventListener('click', async () => {
        const ok = await LF.confirm({
            title: 'Hapus laporan ini?', message: 'Laporan beserta riwayatnya dihapus permanen.',
            confirmText: 'Ya, hapus', tone: 'danger'
        });
        if (!ok) return;
        const res = await LF.api('config/api_user_report.php', { action: 'delete', id });
        if (res.success) { LF.flash('Laporan dihapus.'); location.href = 'laporan_masuk.php'; }
        else LF.toast(res.message || 'Gagal menghapus.', 'error');
    });
});

/* =========================================================
   HALAMAN PC (pc.php): edit / tambah / hapus komponen, tiket baru, aset, QR.
   Tabel sudah dirender server; data awal di window.LF_PC.
========================================================= */
document.addEventListener('DOMContentLoaded', () => {
    const $ = id => document.getElementById(id);
    const P = window.LF_PC || {};
    const CAT = P.catalog || {};
    const API = 'config/';

    /* ---------- QR ---------- */
    const btnQr = $('btnQr');
    if (btnQr) btnQr.addEventListener('click', () => {
        const url = LF.reportUrl(P.lab, P.pc);
        $('qrBox').innerHTML = LF.qrSvg(url, 8);
        $('qrUrl').textContent = url;
        LF.openModal('qrModal');
    });

    if (!P.canEdit) return;     // role user: hanya melihat

    const reload = msg => { LF.flash(msg); location.reload(); };

    /* ---------- komponen ---------- */
    const sel = $('compSelect'), spec = $('specInput'), desc = $('descInput'), reason = $('reasonInput');
    let origSpec = '';

    const status = () => document.querySelector('input[name=hwStatus]:checked').value;
    const syncDynamic = () => {
        const exists = !!P.components[sel.value];
        $('compNew').hidden = exists;
        $('btnDelComp').hidden = !exists;
        $('prioField').hidden = status() !== 'issue';
        $('reasonField').hidden = exists && spec.value.trim() === origSpec;
    };

    function fillForm(type) {
        const cur = P.components[type];
        sel.value = type;
        origSpec = cur ? cur.spec : '';
        document.querySelector(`input[name=hwStatus][value=${cur && cur.status === 'issue' ? 'issue' : 'online'}]`).checked = true;
        spec.value = cur ? cur.spec : '';
        spec.placeholder = 'Contoh: ' + ((CAT[type] || {}).example || '');
        desc.value = cur ? cur.desc : '';
        reason.value = '';
        $('hwTitle').textContent = cur ? 'Edit Komponen' : 'Tambah Komponen';
        syncDynamic();
    }

    const open = type => { fillForm(type); LF.openModal('hwModal'); };
    document.addEventListener('click', e => {
        const b = e.target.closest('[data-edit]');
        if (b) open(b.dataset.edit);
    });
    $('btnAddComp').addEventListener('click', () => {
        const free = Object.keys(CAT).find(t => !P.components[t]) || Object.keys(CAT)[0];
        open(free);
    });
    sel.addEventListener('change', () => fillForm(sel.value));
    spec.addEventListener('input', syncDynamic);
    document.querySelectorAll('input[name=hwStatus]').forEach(r => r.addEventListener('change', syncDynamic));

    $('editForm').addEventListener('submit', async e => {
        e.preventDefault();
        if (!spec.value.trim()) { LF.toast('Spesifikasi wajib diisi.', 'error'); spec.focus(); return; }
        LF.busy($('btnSaveHw'), true);
        const res = await LF.api(API + 'save_component.php', {
            action: 'save', lab: P.lab, pc: P.pc, type: sel.value, status: status(),
            spec: spec.value.trim(), desc: desc.value.trim(), reason: reason.value.trim(), priority: $('prioInput').value
        });
        if (res.success) {
            reload(`${CAT[sel.value].label} disimpan${status() === 'issue' ? ' dan tiket perbaikan dibuat' : ''}.`);
        } else {
            LF.busy($('btnSaveHw'), false);
            LF.toast(res.message || 'Gagal menyimpan.', 'error');
        }
    });

    $('btnDelComp').addEventListener('click', async () => {
        const label = (CAT[sel.value] || {}).label || sel.value;
        const ok = await LF.confirm({ title: `Hapus ${label}?`, message: 'Komponen dilepas dari PC ini dan tercatat di riwayat. Tiket terbuka untuk komponen ini ikut diselesaikan.', confirmText: 'Ya, hapus', tone: 'danger' });
        if (!ok) return;
        const res = await LF.api(API + 'save_component.php', { action: 'delete', lab: P.lab, pc: P.pc, type: sel.value, reason: reason.value.trim() });
        if (res.success) reload(`${label} dihapus dari PC.`);
        else LF.toast(res.message || 'Gagal menghapus.', 'error');
    });

    /* ---------- tiket baru ---------- */
    $('btnTicket').addEventListener('click', () => { $('tkForm').reset(); LF.openModal('tkModal'); });
    $('tkForm').addEventListener('submit', async e => {
        e.preventDefault();
        LF.busy($('btnSaveTk'), true);
        const res = await LF.api(API + 'api_ticket.php', {
            action: 'create', lab: P.lab, pc: P.pc, type: $('tkType').value, description: $('tkDesc').value.trim(), priority: $('tkPrio').value
        });
        if (res.success) { LF.flash(`Tiket #${res.id} dibuat.`); location.href = 'tiket.php?id=' + res.id; }
        else { LF.busy($('btnSaveTk'), false); LF.toast(res.message || 'Gagal membuat tiket.', 'error'); }
    });

    /* ---------- aset ---------- */
    $('btnAsset').addEventListener('click', () => {
        const a = P.asset || {};
        $('aTag').value = a.asset_tag || ''; $('aBuy').value = a.purchase_date || ''; $('aWar').value = a.warranty_until || '';
        $('aSup').value = a.supplier || ''; $('aNote').value = a.notes || '';
        LF.openModal('assetModal');
    });
    $('assetForm').addEventListener('submit', async e => {
        e.preventDefault();
        LF.busy($('btnSaveAsset'), true);
        const res = await LF.api(API + 'api_asset.php', {
            lab: P.lab, pc: P.pc, asset_tag: $('aTag').value.trim(), purchase_date: $('aBuy').value, warranty_until: $('aWar').value,
            supplier: $('aSup').value.trim(), notes: $('aNote').value.trim()
        });
        if (res.success) reload('Data aset disimpan.');
        else { LF.busy($('btnSaveAsset'), false); LF.toast(res.message || 'Gagal menyimpan.', 'error'); }
    });
});

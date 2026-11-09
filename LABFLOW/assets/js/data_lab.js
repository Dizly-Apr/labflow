/* =========================================================
   DATA LAB: kartu lab + cari + filter status
========================================================= */
document.addEventListener('DOMContentLoaded', () => {
    const labs = window.LABFLOW_LABS || [];
    const grid = document.getElementById('labGrid');
    const search = document.getElementById('searchInput');
    const empty = document.getElementById('emptyState');
    const chips = [...document.querySelectorAll('#statusChips .chip')];
    let status = window.LABFLOW_STATUS || 'all';

    const canEdit = !!window.LABFLOW_CAN_EDIT;

    /* kartu dibiarkan kosong sampai foto lab diunggah (tombol kamera, khusus admin/koordinator) */
    function render(list) {
        grid.innerHTML = '';
        empty.hidden = list.length > 0;
        list.forEach((lab, i) => {
            const wrap = document.createElement('div');
            wrap.className = 'lab-wrap';
            wrap.style.setProperty('--i', i);
            const has = !!lab.image;
            wrap.innerHTML = `
                <a class="lab-card ${has ? 'has-photo' : 'no-photo'} ${lab.status === 'maintenance' ? 'is-maint' : ''}" href="${LF.esc(lab.link)}" aria-label="${LF.esc(lab.name)}, ${LF.esc(lab.statusText)}">
                    ${has ? '' : '<div class="photo-empty"><i data-lucide="image-plus"></i><span>Foto lab belum diisi</span></div>'}
                    <div class="lab-top">
                        <div class="lab-name">${LF.esc(lab.name)}</div>
                        <div class="lab-status ${LF.esc(lab.status)}"><span class="status-dot"></span>${LF.esc(lab.statusText)}</div>
                    </div>
                    <div class="lab-bottom">
                        <div class="lab-meta">
                            <span class="lab-chip"><i data-lucide="layers"></i>${LF.esc(lab.floor)}</span>
                            <span class="lab-chip"><i data-lucide="monitor"></i>${lab.pcs} PC</span>
                            ${lab.issue > 0 ? `<span class="lab-chip warn"><i data-lucide="triangle-alert"></i>${lab.issue} bermasalah</span>` : ''}
                        </div>
                        <span class="lab-go"><i data-lucide="arrow-right"></i></span>
                    </div>
                </a>
                ${canEdit ? `<div class="photo-tools">
                    <button type="button" class="icon-btn" data-photo="${lab.id}" title="${has ? 'Ganti foto lab' : 'Tambah foto lab'}" aria-label="${has ? 'Ganti' : 'Tambah'} foto ${LF.esc(lab.name)}"><i data-lucide="camera"></i></button>
                    ${has ? `<button type="button" class="icon-btn danger" data-photo-del="${lab.id}" title="Hapus foto" aria-label="Hapus foto ${LF.esc(lab.name)}"><i data-lucide="trash-2"></i></button>` : ''}
                </div>` : ''}`;
            if (has) wrap.querySelector('.lab-card').style.backgroundImage = `url("${lab.image}")`;
            grid.appendChild(wrap);
        });
        LF.icons();
    }

    /* ---------- unggah foto: perkecil di browser (maks 1400px) lalu kirim ---------- */
    const picker = document.createElement('input');
    picker.type = 'file'; picker.accept = 'image/jpeg,image/png,image/webp'; picker.hidden = true;
    document.body.appendChild(picker);
    let targetId = 0, targetBtn = null;

    function shrink(file) {
        return new Promise((resolve, reject) => {
            const im = new Image(), url = URL.createObjectURL(file);
            im.onload = () => {
                const k = Math.min(1, 1400 / Math.max(im.naturalWidth, im.naturalHeight));
                const c = document.createElement('canvas');
                c.width = Math.round(im.naturalWidth * k); c.height = Math.round(im.naturalHeight * k);
                c.getContext('2d').drawImage(im, 0, 0, c.width, c.height);
                URL.revokeObjectURL(url);
                c.toBlob(b => b ? resolve(b) : reject(), 'image/jpeg', 0.86);
            };
            im.onerror = () => { URL.revokeObjectURL(url); reject(); };
            im.src = url;
        });
    }

    grid.addEventListener('click', async e => {
        const up = e.target.closest('[data-photo]');
        const del = e.target.closest('[data-photo-del]');
        if (up) { targetId = +up.dataset.photo; targetBtn = up; picker.value = ''; picker.click(); }
        if (del) {
            const lab = labs.find(l => l.id == del.dataset.photoDel);
            const ok = await LF.confirm({ title: 'Hapus foto lab?', message: `Foto ${lab.name} akan dihapus dan kartu kembali kosong.`, confirmText: 'Ya, hapus', tone: 'danger' });
            if (!ok) return;
            const fd = new FormData(); fd.append('lab_id', lab.id); fd.append('action', 'remove');
            const r = await fetch('config/upload_lab_image.php', { method: 'POST', headers: { 'X-CSRF-Token': LF.csrf() }, body: fd }).then(x => x.json()).catch(() => ({ success: false, message: 'Gagal menghubungi server.' }));
            if (r.success) { lab.image = ''; apply(); LF.toast('Foto lab dihapus.'); } else LF.toast(r.message, 'error');
        }
    });

    picker.addEventListener('change', async () => {
        const f = picker.files[0];
        if (!f) return;
        if (!/^image\/(jpeg|png|webp)$/.test(f.type)) { LF.toast('Format harus JPG, PNG, atau WEBP.', 'error'); return; }
        LF.busy(targetBtn, true);
        try {
            const blob = await shrink(f);
            const fd = new FormData(); fd.append('lab_id', targetId); fd.append('photo', blob, 'lab.jpg');
            const r = await fetch('config/upload_lab_image.php', { method: 'POST', headers: { 'X-CSRF-Token': LF.csrf() }, body: fd }).then(x => x.json());
            if (r.success) { labs.find(l => l.id === targetId).image = r.image; apply(); LF.toast('Foto lab tersimpan.'); }
            else { LF.toast(r.message || 'Gagal menyimpan foto.', 'error'); LF.busy(targetBtn, false); }
        } catch (e) { LF.toast('Gagal memproses gambar.', 'error'); LF.busy(targetBtn, false); }
    });

    function apply() {
        const q = search.value.toLowerCase().trim();
        render(labs.filter(l =>
            (status === 'all' || l.status === status) &&
            (l.name.toLowerCase().includes(q) || String(l.floor).toLowerCase().includes(q))));
    }

    chips.forEach(c => c.addEventListener('click', () => {
        status = c.dataset.status;
        chips.forEach(x => x.classList.toggle('active', x === c));
        apply();
    }));
    search.addEventListener('input', apply);
    document.getElementById('resetFilter').addEventListener('click', () => {
        search.value = ''; status = 'all';
        chips.forEach(x => x.classList.toggle('active', x.dataset.status === 'all'));
        apply(); search.focus();
    });

    apply();

    /* ---------- tambah lab baru ---------- */
    const newBtn = document.getElementById('btnNewLab');
    if (newBtn) {
        newBtn.addEventListener('click', () => { document.getElementById('labForm').reset(); LF.openModal('labModal'); });
        document.getElementById('labForm').addEventListener('submit', async e => {
            e.preventDefault();
            const btn = document.getElementById('btnSaveLab');
            LF.busy(btn, true);
            const r = await LF.api('config/api_lab.php', {
                action: 'create', name: document.getElementById('lName').value.trim(), floor: document.getElementById('lFloor').value.trim(),
                pcs: +document.getElementById('lPcs').value, code: document.getElementById('lCode').value.trim()
            });
            if (r.success) { LF.flash('Lab baru dibuat.'); location.href = 'lab.php?lab=' + encodeURIComponent(r.code); }
            else { LF.busy(btn, false); LF.toast(r.message || 'Gagal membuat lab.', 'error'); }
        });
    }
});

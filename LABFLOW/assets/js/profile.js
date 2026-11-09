document.addEventListener('DOMContentLoaded', () => {
    const $ = id => document.getElementById(id);

    /* ---------- tampil / sembunyikan password ---------- */
    document.querySelectorAll('.pw-toggle[data-toggle]').forEach(btn => btn.addEventListener('click', () => {
        const inp = $(btn.dataset.toggle), show = inp.type === 'password';
        inp.type = show ? 'text' : 'password';
        btn.innerHTML = `<i data-lucide="${show ? 'eye-off' : 'eye'}"></i>`;
        LF.icons();
    }));

    /* ---------- kekuatan password & kecocokan ---------- */
    function strength(p) {
        let n = 0;
        if (p.length >= 6) n++;
        if (p.length >= 10) n++;
        if (/[A-Z]/.test(p) && /[a-z]/.test(p)) n++;
        if (/\d/.test(p)) n++;
        if (/[^A-Za-z0-9]/.test(p)) n++;
        return Math.min(n, 4);
    }
    const LV = [['', '#e5e9ef', 0], ['Lemah', '#ef4444', 25], ['Cukup', '#f59e0b', 50], ['Kuat', '#22c55e', 75], ['Sangat kuat', '#16a34a', 100]];
    function checkPw() {
        const p = $('newPass').value, p2 = $('newPass2').value;
        const lv = p ? LV[Math.max(1, strength(p))] : LV[0];
        $('strengthBar').style.width = lv[2] + '%';
        $('strengthBar').style.background = lv[1];
        $('strengthLabel').textContent = lv[0] ? 'Kekuatan: ' + lv[0] : '';
        const m = $('matchLabel');
        if (!p2) { m.textContent = ''; $('newPass2').classList.remove('is-invalid'); }
        else if (p !== p2) { m.textContent = 'Password belum sama.'; m.style.color = 'var(--red)'; $('newPass2').classList.add('is-invalid'); }
        else { m.textContent = 'Password cocok.'; m.style.color = 'var(--green)'; $('newPass2').classList.remove('is-invalid'); }
    }
    $('newPass').addEventListener('input', checkPw);
    $('newPass2').addEventListener('input', checkPw);

    $('pwForm').addEventListener('submit', async e => {
        e.preventDefault();
        const n = $('newPass').value;
        if (n !== $('newPass2').value) { LF.toast('Konfirmasi password baru tidak sama.', 'error'); $('newPass2').focus(); return; }
        LF.busy($('btnPw'), true);
        const res = await LF.api('config/change_password.php', { current: $('curPass').value, new: n });
        LF.busy($('btnPw'), false);
        if (res.success) { LF.toast('Password berhasil diganti.'); e.target.reset(); checkPw(); }
        else LF.toast(res.message || 'Gagal mengganti password.', 'error');
    });

    /* ---------- foto profil: pilih -> geser/zoom -> simpan ---------- */
    const file = document.getElementById('avatarFile');
    const btnPick = document.getElementById('btnPick');
    const btnRm = document.getElementById('btnRemove');
    const overlay = document.getElementById('cropOverlay');
    const cv = document.getElementById('cropCanvas');
    const ctx = cv.getContext('2d');
    const zoomRange = document.getElementById('zoomRange');
    const SRC_MAX = 15 * 1024 * 1024;       // file asli; hasil crop hanya ~30 KB
    const W = cv.width, H = cv.height, D = 280, OUT = 256;   // D = diameter lingkaran crop

    let img = null, zoom = 1, ox = 0, oy = 0, base = 1;

    const scale = () => base * zoom;
    function clamp() {                      // gambar harus selalu menutupi lingkaran
        const s = scale();
        const mx = Math.max(0, (img.naturalWidth * s - D) / 2);
        const my = Math.max(0, (img.naturalHeight * s - D) / 2);
        ox = Math.min(mx, Math.max(-mx, ox));
        oy = Math.min(my, Math.max(-my, oy));
    }
    function draw() {
        const s = scale(), w = img.naturalWidth * s, h = img.naturalHeight * s;
        ctx.clearRect(0, 0, W, H);
        ctx.fillStyle = '#111827'; ctx.fillRect(0, 0, W, H);
        ctx.drawImage(img, W / 2 + ox - w / 2, H / 2 + oy - h / 2, w, h);
        // redupkan area di luar lingkaran
        ctx.save();
        ctx.fillStyle = 'rgba(15,23,42,.62)';
        ctx.beginPath(); ctx.rect(0, 0, W, H); ctx.arc(W / 2, H / 2, D / 2, 0, Math.PI * 2, true);
        ctx.fill('evenodd'); ctx.restore();
        ctx.strokeStyle = 'rgba(255,255,255,.9)'; ctx.lineWidth = 2;
        ctx.beginPath(); ctx.arc(W / 2, H / 2, D / 2, 0, Math.PI * 2); ctx.stroke();
    }
    function setZoom(z) {
        zoom = Math.min(4, Math.max(1, z));
        zoomRange.value = zoom;
        clamp(); draw();
    }

    function openEditor(src) {
        const im = new Image();
        im.onload = () => {
            img = im;
            base = Math.max(D / im.naturalWidth, D / im.naturalHeight);   // "cover" pada lingkaran
            zoom = 1; ox = 0; oy = 0; zoomRange.value = 1;
            overlay.hidden = false; draw();
        };
        im.onerror = () => LF.toast('File gambar rusak / tidak bisa dibaca.', 'error');
        im.src = src;
    }
    function closeEditor() { overlay.hidden = true; file.value = ''; img = null; }

    btnPick.onclick = () => file.click();
    const av = $('avatarPreview');
    av.addEventListener('click', () => file.click());
    av.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); file.click(); } });

    file.onchange = () => {
        const f = file.files[0];
        if (!f) return;
        if (!/^image\/(jpeg|png|webp|gif)$/.test(f.type)) { LF.toast('Format harus JPG, PNG, WEBP, atau GIF.', 'error'); file.value = ''; return; }
        if (f.size > SRC_MAX) { LF.toast('Ukuran file terlalu besar (maks. 15 MB).', 'error'); file.value = ''; return; }
        openEditor(URL.createObjectURL(f));
    };

    /* geser (mouse & sentuh) */
    let drag = null;
    cv.addEventListener('pointerdown', e => {
        if (!img) return;
        drag = { x: e.clientX, y: e.clientY, ox, oy };
        cv.setPointerCapture(e.pointerId); cv.classList.add('dragging');
    });
    cv.addEventListener('pointermove', e => {
        if (!drag) return;
        const k = W / cv.getBoundingClientRect().width;      // canvas bisa diskalakan CSS di layar kecil
        ox = drag.ox + (e.clientX - drag.x) * k;
        oy = drag.oy + (e.clientY - drag.y) * k;
        clamp(); draw();
    });
    const endDrag = () => { drag = null; cv.classList.remove('dragging'); };
    cv.addEventListener('pointerup', endDrag);
    cv.addEventListener('pointercancel', endDrag);

    /* zoom */
    zoomRange.addEventListener('input', () => setZoom(parseFloat(zoomRange.value)));
    document.getElementById('zoomIn').onclick  = () => setZoom(zoom + 0.2);
    document.getElementById('zoomOut').onclick = () => setZoom(zoom - 0.2);
    cv.addEventListener('wheel', e => { e.preventDefault(); setZoom(zoom + (e.deltaY < 0 ? 0.1 : -0.1)); }, { passive: false });

    document.getElementById('cropReset').onclick = () => { ox = 0; oy = 0; setZoom(1); };
    document.getElementById('cropCancel').onclick = closeEditor;
    document.getElementById('cropClose').onclick = closeEditor;
    overlay.addEventListener('mousedown', e => { if (e.target === overlay) closeEditor(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && !overlay.hidden) closeEditor(); });

    /* simpan: potong sesuai lingkaran -> 256x256 JPEG -> upload */
    document.getElementById('cropSave').onclick = function () {
        if (!img) return;
        const s = scale();
        const sx = (img.naturalWidth * s / 2 - ox - D / 2) / s;
        const sy = (img.naturalHeight * s / 2 - oy - D / 2) / s;
        const out = document.createElement('canvas');
        out.width = out.height = OUT;
        const o = out.getContext('2d');
        o.fillStyle = '#fff'; o.fillRect(0, 0, OUT, OUT);       // latar putih untuk PNG transparan
        o.drawImage(img, sx, sy, D / s, D / s, 0, 0, OUT, OUT);

        const btn = this; LF.busy(btn, true);
        const fail = m => { LF.toast(m || 'Gagal menyimpan foto.', 'error'); LF.busy(btn, false); };
        out.toBlob(blob => {
            if (!blob) return fail('Gagal memproses gambar.');
            const fd = new FormData();
            fd.append('avatar', blob, 'avatar.jpg');
            fetch('config/upload_avatar.php', { method: 'POST', headers: { 'X-CSRF-Token': LF.csrf() }, body: fd })
                .then(r => r.json())
                .then(res => { if (res.success) { LF.flash('Foto profil diperbarui.'); location.reload(); } else fail(res.message); })
                .catch(() => fail('Gagal menghubungi server.'));
        }, 'image/jpeg', 0.9);
    };

    if (btnRm) btnRm.onclick = async () => {
        const ok = await LF.confirm({ title: 'Hapus foto profil?', message: 'Foto akan diganti dengan inisial nama Anda.', confirmText: 'Ya, hapus', tone: 'danger' });
        if (!ok) return;
        const fd = new FormData(); fd.append('action', 'remove');
        fetch('config/upload_avatar.php', { method: 'POST', headers: { 'X-CSRF-Token': LF.csrf() }, body: fd })
            .then(r => r.json())
            .then(res => { if (res.success) { LF.flash('Foto profil dihapus.'); location.reload(); } else LF.toast(res.message, 'error'); })
            .catch(() => LF.toast('Gagal menghubungi server.', 'error'));
    };
});

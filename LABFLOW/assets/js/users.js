/* =========================================================
   KELOLA USER
========================================================= */
document.addEventListener('DOMContentLoaded', () => {
    const $ = id => document.getElementById(id);
    const API = 'config/api_users.php';
    const ROLE = { admin: 'ADMIN', coordinator: 'KOORDINATOR', user: 'USER' };
    let users = [], me = 0, editingId = null;

    const call = body => LF.api(API, body);

    /* ---------- daftar ---------- */
    async function load() {
        const res = await call({ action: 'list' });
        if (!res.success) { LF.toast(res.message || 'Gagal memuat daftar user.', 'error'); $('userList').innerHTML = '<div class="empty-note">Gagal memuat data.</div>'; return; }
        users = res.users; me = res.me;
        render();
    }

    function render() {
        const q = $('userSearch').value.toLowerCase().trim();
        $('mAll').textContent = users.length;
        $('mAdmin').textContent = users.filter(u => u.role === 'admin').length;
        $('mCoord').textContent = users.filter(u => u.role === 'coordinator').length;

        const rows = users.filter(u => !q || u.full_name.toLowerCase().includes(q) || u.username.includes(q));
        $('userList').innerHTML = rows.length ? rows.map(u => {
            const active = u.is_active == 1, self = u.id == me;
            return `
            <div class="user-row ${active ? '' : 'inactive'} ${u.id == editingId ? 'editing' : ''}">
                <div class="avatar">${u.avatar ? `<img src="uploads/avatars/${LF.esc(u.avatar)}" alt="">` : LF.esc(u.full_name.charAt(0).toUpperCase())}</div>
                <div class="user-info">
                    <strong>${LF.esc(u.full_name)} ${self ? '<small>(Anda)</small>' : ''}</strong>
                    <span>@${LF.esc(u.username)} &bull; ${u.last_login ? 'login ' + LF.esc(u.last_login.slice(0, 16)) : 'belum pernah login'}${active ? '' : ' &bull; nonaktif'}</span>
                </div>
                <span class="role-pill role-${LF.esc(u.role)}">${ROLE[u.role] || ''}</span>
                <div class="row-actions">
                    <button type="button" class="icon-btn" title="Edit" aria-label="Edit ${LF.esc(u.username)}" data-act="edit" data-id="${u.id}"><i data-lucide="pencil"></i></button>
                    ${self ? '' : `
                    <button type="button" class="icon-btn ${active ? '' : 'success'}" title="${active ? 'Nonaktifkan' : 'Aktifkan'}" aria-label="${active ? 'Nonaktifkan' : 'Aktifkan'} ${LF.esc(u.username)}" data-act="toggle" data-id="${u.id}"><i data-lucide="${active ? 'user-x' : 'user-check'}"></i></button>
                    <button type="button" class="icon-btn danger" title="Hapus" aria-label="Hapus ${LF.esc(u.username)}" data-act="delete" data-id="${u.id}"><i data-lucide="trash-2"></i></button>`}
                </div>
            </div>`;
        }).join('') : '<div class="empty-note">Tidak ada user yang cocok.</div>';
        LF.icons();
    }

    $('userSearch').addEventListener('input', render);

    $('userList').addEventListener('click', async e => {
        const b = e.target.closest('button[data-act]');
        if (!b) return;
        const u = users.find(x => x.id == b.dataset.id);
        if (!u) return;

        if (b.dataset.act === 'edit') startEdit(u);

        if (b.dataset.act === 'toggle') {
            const on = u.is_active == 1;
            const ok = await LF.confirm({
                title: on ? 'Nonaktifkan akun?' : 'Aktifkan akun?',
                message: on ? `@${u.username} tidak akan bisa login dan langsung keluar dari sesi yang berjalan.` : `@${u.username} akan bisa login kembali.`,
                confirmText: on ? 'Ya, nonaktifkan' : 'Ya, aktifkan', tone: on ? 'warn' : 'primary', icon: on ? 'user-x' : 'user-check'
            });
            if (!ok) return;
            LF.busy(b, true);
            const r = await call({ action: 'toggle', id: u.id });
            if (r.success) { LF.toast(`Akun @${u.username} ${on ? 'dinonaktifkan' : 'diaktifkan'}.`); await load(); }
            else { LF.toast(r.message, 'error'); LF.busy(b, false); }
        }

        if (b.dataset.act === 'delete') {
            const ok = await LF.confirm({
                title: 'Hapus akun?', message: `Akun @${u.username} (${u.full_name}) akan dihapus permanen. Tindakan ini tidak bisa dibatalkan.`,
                confirmText: 'Ya, hapus', tone: 'danger'
            });
            if (!ok) return;
            LF.busy(b, true);
            const r = await call({ action: 'delete', id: u.id });
            if (r.success) { if (editingId == u.id) resetForm(); LF.toast(`Akun @${u.username} dihapus.`); await load(); }
            else { LF.toast(r.message, 'error'); LF.busy(b, false); }
        }
    });

    /* ---------- form ---------- */
    function setMode(edit) {
        $('formTitle').textContent = edit ? 'Edit Akun' : 'Tambah Akun Baru';
        $('formBadge').textContent = edit ? 'EDIT USER' : 'NEW USER';
        $('formBadge').className = 'badge' + (edit ? ' badge-yellow' : '');
        $('btnSave').querySelector('span').textContent = edit ? 'Simpan Perubahan' : 'Buat Akun';
        $('btnCancel').lastChild.textContent = edit ? ' Batal Edit' : ' Reset';
    }

    function resetForm() {
        $('userForm').reset();
        $('userId').value = ''; editingId = null;
        $('pwHint').textContent = '';
        $('userPass').required = true; $('userPass').type = 'password';
        $('userName').disabled = false;
        setMode(false); render();
    }

    function startEdit(u) {
        editingId = u.id;
        $('userId').value = u.id;
        $('fullName').value = u.full_name;
        $('userName').value = u.username;
        $('userPass').value = ''; $('userPass').required = false;
        $('pwHint').textContent = '(kosongkan jika tidak diganti)';
        document.querySelector(`input[name=role][value=${u.role}]`).checked = true;
        $('userActive').value = u.is_active;
        setMode(true); render();
        $('formCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
        $('fullName').focus();
    }

    $('btnCancel').onclick = resetForm;

    $('pwToggle').onclick = () => {
        const p = $('userPass'), show = p.type === 'password';
        p.type = show ? 'text' : 'password';
        $('pwToggle').innerHTML = `<i data-lucide="${show ? 'eye-off' : 'eye'}"></i>`;
        $('pwToggle').setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
        LF.icons();
    };

    $('userForm').addEventListener('submit', async e => {
        e.preventDefault();
        const id = $('userId').value;
        LF.busy($('btnSave'), true);
        const res = await call({
            action: id ? 'update' : 'create',
            id: id ? parseInt(id, 10) : undefined,
            full_name: $('fullName').value,
            username: $('userName').value,
            password: $('userPass').value,
            role: document.querySelector('input[name=role]:checked').value,
            is_active: $('userActive').value === '1'
        });
        LF.busy($('btnSave'), false);
        if (res.success) {
            LF.toast(id ? 'Akun berhasil diperbarui.' : 'Akun berhasil dibuat.');
            resetForm(); load();
        } else LF.toast(res.message || 'Gagal menyimpan akun.', 'error');
    });

    load();
});

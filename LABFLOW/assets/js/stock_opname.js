/* =========================================================
   STOCK OPNAME
========================================================= */
document.addEventListener('DOMContentLoaded', () => {
    const $ = id => document.getElementById(id);

    /* ---------- stepper (+ / -) untuk semua input jumlah ---------- */
    document.addEventListener('click', e => {
        const b = e.target.closest('.stepper [data-step]');
        if (!b) return;
        const input = b.parentElement.querySelector('input');
        const min = parseInt(input.min || '1', 10);
        const max = input.max ? parseInt(input.max, 10) : Infinity;
        let v = (parseInt(input.value, 10) || min) + parseInt(b.dataset.step, 10);
        input.value = Math.min(max, Math.max(min, v));
    });

    /* ---------- form barang masuk ---------- */
    const form = $('stockForm');
    if (form) form.addEventListener('submit', async e => {
        e.preventDefault();
        const btn = $('btnAdd');
        const qty = parseInt($('stockCount').value, 10) || 0;
        if (qty < 1) { LF.toast('Jumlah minimal 1.', 'error'); $('stockCount').focus(); return; }
        LF.busy(btn, true);
        const res = await LF.api('config/save_stock.php', {
            name: $('itemName').value.trim(),
            category: $('itemCategory').value.trim(),
            description: $('itemDesc').value.trim(),
            qty,
            min: $('stockMin').value.trim()
        });
        if (res.success) { LF.flash(`${qty} unit "${$('itemName').value.trim()}" masuk ke inventaris.`); location.reload(); }
        else { LF.toast(res.message || 'Gagal menyimpan.', 'error'); LF.busy(btn, false); }
    });

    /* ---------- cari + filter + urut ---------- */
    const tbody = document.querySelector('#invTable tbody');
    const rows = [...tbody.querySelectorAll('tr[data-id]')];
    const search = $('searchInput'), cat = $('catFilter'), sortBy = $('sortBy'), cond = $('stockFilter');

    function apply() {
        const q = search.value.toLowerCase().trim();
        const c = cat.value;
        const sorted = rows.slice().sort((a, b) => {
            if (sortBy.value === 'qty-desc') return b.dataset.qty - a.dataset.qty;
            if (sortBy.value === 'qty-asc') return a.dataset.qty - b.dataset.qty;
            return a.dataset.name.localeCompare(b.dataset.name, 'id');
        });
        let shown = 0;
        sorted.forEach(tr => {
            const ok = (!c || tr.dataset.cat === c) && (!q || tr.textContent.toLowerCase().includes(q)) && (cond.value !== 'low' || tr.dataset.low === '1');
            tr.hidden = !ok;
            if (ok) shown++;
            tbody.appendChild(tr);
        });
        $('invEmpty').hidden = shown > 0 || rows.length === 0;
        $('invCount').textContent = (shown === rows.length ? rows.length : `${shown} / ${rows.length}`) + ' JENIS BARANG';
    }
    [search, cat, sortBy, cond].forEach(el => el.addEventListener('input', apply));
    apply();

    /* ---------- aksi baris (admin & koordinator) ---------- */
    const outModal = $('outModal'), editModal = $('editModal');
    if (!outModal) return;
    let current = null;

    $('invTable').addEventListener('click', async e => {
        const b = e.target.closest('button[data-act]');
        if (!b) return;
        const tr = b.closest('tr');
        current = { id: +tr.dataset.id, name: tr.dataset.name, cat: tr.dataset.cat, desc: tr.dataset.desc, qty: +tr.dataset.qty, min: +tr.dataset.min };

        if (b.dataset.act === 'out') {
            $('outName').textContent = `${current.name} \u2022 stok tersedia: ${current.qty}`;
            $('outHint').textContent = `(maks. ${current.qty})`;
            $('outQty').max = current.qty; $('outQty').value = 1; $('outNotes').value = '';
            LF.openModal(outModal);
        }
        if (b.dataset.act === 'edit') {
            $('eName').value = current.name; $('eCat').value = current.cat; $('eDesc').value = current.desc; $('eMin').value = current.min;
            LF.openModal(editModal);
        }
        if (b.dataset.act === 'del') {
            const ok = await LF.confirm({
                title: 'Hapus barang?',
                message: `"${current.name}" (stok ${current.qty}) akan dihapus permanen dari inventaris beserta riwayatnya.`,
                confirmText: 'Ya, hapus', tone: 'danger'
            });
            if (!ok) return;
            LF.busy(b, true);
            const res = await LF.api('config/api_stock.php', { action: 'delete', id: current.id });
            if (res.success) { LF.flash(`"${current.name}" dihapus dari inventaris.`); location.reload(); }
            else { LF.toast(res.message || 'Gagal menghapus.', 'error'); LF.busy(b, false); }
        }
    });

    $('outForm').addEventListener('submit', async e => {
        e.preventDefault();
        const qty = parseInt($('outQty').value, 10) || 0;
        if (qty < 1 || qty > current.qty) { LF.toast(`Jumlah harus 1 sampai ${current.qty}.`, 'error'); return; }
        LF.busy($('btnOut'), true);
        const res = await LF.api('config/api_stock.php', { action: 'out', id: current.id, qty, notes: $('outNotes').value.trim() || 'Barang keluar' });
        if (res.success) { LF.flash(`${qty} unit "${current.name}" dikeluarkan.`); location.reload(); }
        else { LF.toast(res.message || 'Gagal memproses.', 'error'); LF.busy($('btnOut'), false); }
    });

    $('editForm').addEventListener('submit', async e => {
        e.preventDefault();
        LF.busy($('btnEdit'), true);
        const res = await LF.api('config/api_stock.php', {
            action: 'update', id: current.id,
            name: $('eName').value.trim(), category: $('eCat').value.trim(), description: $('eDesc').value.trim(), min: $('eMin').value.trim()
        });
        if (res.success) { LF.flash('Data barang diperbarui.'); location.reload(); }
        else { LF.toast(res.message || 'Gagal menyimpan.', 'error'); LF.busy($('btnEdit'), false); }
    });
});

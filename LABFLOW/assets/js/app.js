/* =========================================================
   LABFLOW — APP (dipakai semua halaman setelah login)
   window.LF: toast, confirm, modal, api, busy, icons, esc
========================================================= */
(function () {
    'use strict';

    const ICON = {
        success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/></svg>',
        error:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16.5v.01"/></svg>',
        info:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 11v5M12 7.5v.01"/></svg>',
        x:       '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>'
    };

    const LF = {};

    /* ---------- util ---------- */
    LF.esc = s => String(s ?? '').replace(/[&<>"']/g, c =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    LF.icons = () => { if (window.lucide && lucide.createIcons) lucide.createIcons(); };

    /* ---------- toast ---------- */
    function toastBox() {
        let b = document.getElementById('toasts');
        if (!b) { b = document.createElement('div'); b.id = 'toasts'; b.className = 'toasts'; b.setAttribute('aria-live', 'polite'); document.body.appendChild(b); }
        return b;
    }
    LF.toast = function (message, type = 'success', ms = 3600) {
        const t = document.createElement('div');
        t.className = 'toast toast-' + type;
        t.setAttribute('role', type === 'error' ? 'alert' : 'status');
        t.innerHTML = (ICON[type] || ICON.info) + '<span></span><button type="button" aria-label="Tutup">&times;</button>';
        t.querySelector('span').textContent = message;
        const close = () => { t.classList.add('leaving'); setTimeout(() => t.remove(), 240); };
        t.querySelector('button').onclick = close;
        toastBox().appendChild(t);
        if (ms) setTimeout(close, ms);
        return t;
    };

    /** Tampilkan toast setelah halaman dimuat ulang (mis. setelah location.reload()). */
    LF.flash = function (message, type = 'success') {
        try { sessionStorage.setItem('lf_flash', JSON.stringify({ message, type })); } catch (e) {}
    };
    function showFlash() {
        try {
            const raw = sessionStorage.getItem('lf_flash');
            if (!raw) return;
            sessionStorage.removeItem('lf_flash');
            const f = JSON.parse(raw);
            LF.toast(f.message, f.type);
        } catch (e) {}
    }

    /* ---------- modal (markup statis di halaman: .modal-backdrop#id) ---------- */
    let lastFocus = null;
    LF.openModal = function (id) {
        const m = typeof id === 'string' ? document.getElementById(id) : id;
        if (!m) return;
        lastFocus = document.activeElement;
        m.hidden = false;
        document.body.classList.add('modal-open');
        const first = m.querySelector('[data-autofocus], input:not([type=hidden]):not([disabled]), select, textarea, button.btn-primary');
        if (first) setTimeout(() => first.focus(), 30);
        LF.icons();
    };
    LF.closeModal = function (id) {
        const m = typeof id === 'string' ? document.getElementById(id) : id;
        if (!m) return;
        m.hidden = true;
        if (!document.querySelector('.modal-backdrop:not([hidden])')) document.body.classList.remove('modal-open');
        if (lastFocus && lastFocus.focus) { try { lastFocus.focus(); } catch (e) {} }
    };

    /* ---------- confirm dialog (menggantikan window.confirm) ---------- */
    LF.confirm = function ({ title = 'Konfirmasi', message = '', confirmText = 'Ya, lanjutkan', cancelText = 'Batal', tone = 'primary', icon } = {}) {
        return new Promise(resolve => {
            const iconName = icon || (tone === 'danger' ? 'trash-2' : tone === 'warn' ? 'triangle-alert' : 'circle-help');
            const wrap = document.createElement('div');
            wrap.className = 'modal-backdrop';
            wrap.innerHTML = `
                <div class="modal" role="alertdialog" aria-modal="true" aria-labelledby="cfTitle" style="max-width:420px">
                    <div class="modal-icon ${tone === 'danger' ? 'danger' : tone === 'warn' ? 'warn' : ''}"><i data-lucide="${iconName}"></i></div>
                    <div class="modal-head" style="margin-bottom:0"><div><h3 id="cfTitle"></h3><p class="cf-msg" style="font-size:14px;margin-top:6px"></p></div></div>
                    <div class="modal-foot">
                        <button type="button" class="btn btn-secondary" data-no></button>
                        <button type="button" class="btn ${tone === 'danger' ? 'btn-danger' : tone === 'warn' ? 'btn-warning' : 'btn-primary'}" data-yes data-autofocus></button>
                    </div>
                </div>`;
            wrap.querySelector('#cfTitle').textContent = title;
            wrap.querySelector('.cf-msg').textContent = message;
            wrap.querySelector('[data-no]').textContent = cancelText;
            wrap.querySelector('[data-yes]').textContent = confirmText;
            document.body.appendChild(wrap);
            document.body.classList.add('modal-open');
            LF.icons();
            const prev = document.activeElement;
            const done = v => {
                document.removeEventListener('keydown', onKey, true);
                wrap.remove();
                if (!document.querySelector('.modal-backdrop:not([hidden])')) document.body.classList.remove('modal-open');
                if (prev && prev.focus) { try { prev.focus(); } catch (e) {} }
                resolve(v);
            };
            const onKey = e => { if (e.key === 'Escape') { e.stopPropagation(); done(false); } };
            document.addEventListener('keydown', onKey, true);
            wrap.addEventListener('mousedown', e => { if (e.target === wrap) done(false); });
            wrap.querySelector('[data-no]').onclick = () => done(false);
            wrap.querySelector('[data-yes]').onclick = () => done(true);
            wrap.querySelector('[data-yes]').focus();
        });
    };

    /* ---------- fetch JSON ---------- */
    /** Token CSRF dari <meta name="csrf-token"> (dipasang layout.php). */
    LF.csrf = () => (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    LF.api = function (url, body) {
        const opt = body === undefined ? {} : {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': LF.csrf() }, body: JSON.stringify(body)
        };
        return fetch(url, opt)
            .then(r => r.json().catch(() => ({ success: false, message: 'Respon server tidak valid.' }))
                .then(j => { if (r.status === 401) { location.href = (window.LF_BASE || '') + 'login.php'; } return j; }))
            .catch(() => ({ success: false, message: 'Gagal menghubungi server. Periksa koneksi Anda.' }));
    };

    /** Tandai tombol sedang memproses (spinner + disabled). */
    LF.busy = function (btn, on = true) {
        if (!btn) return;
        btn.classList.toggle('is-loading', on);
        btn.disabled = on;
    };

    /* ---------- angka naik pelan (data-count="123") ---------- */
    function countUp() {
        document.querySelectorAll('[data-count]').forEach(el => {
            const target = parseInt(el.dataset.count, 10);
            if (isNaN(target)) return;
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || target < 2) { el.textContent = target.toLocaleString('id-ID'); return; }
            const dur = 700, t0 = performance.now();
            const tick = now => {
                const p = Math.min(1, (now - t0) / dur), e = 1 - Math.pow(1 - p, 3);
                el.textContent = Math.round(target * e).toLocaleString('id-ID');
                if (p < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        });
    }

    /* ---------- init ---------- */
    document.addEventListener('DOMContentLoaded', () => {
        LF.icons();
        showFlash();
        countUp();

        /* drawer mobile */
        const toggle = document.getElementById('menuToggle');
        const scrim = document.getElementById('scrim');
        const setMenu = open => document.body.classList.toggle('menu-open', open);
        if (toggle) toggle.addEventListener('click', () => setMenu(!document.body.classList.contains('menu-open')));
        if (scrim) scrim.addEventListener('click', () => setMenu(false));
        document.querySelectorAll('.sidebar a').forEach(a => a.addEventListener('click', () => setMenu(false)));

        /* tema terang / gelap (disimpan di browser; default mengikuti sistem) */
        const themeBtn = document.getElementById('themeToggle');
        if (themeBtn) themeBtn.addEventListener('click', () => {
            const dark = document.documentElement.getAttribute('data-theme') !== 'dark';
            if (dark) document.documentElement.setAttribute('data-theme', 'dark');
            else document.documentElement.removeAttribute('data-theme');
            try { localStorage.setItem('lf_theme', dark ? 'dark' : 'light'); } catch (e) {}
            document.dispatchEvent(new CustomEvent('lf:theme', { detail: { dark } }));
        });

        /* panel notifikasi */
        const nBtn = document.getElementById('notifBtn'), nPanel = document.getElementById('notifPanel');
        if (nBtn && nPanel) {
            const setN = open => { nPanel.hidden = !open; nBtn.setAttribute('aria-expanded', open ? 'true' : 'false'); };
            nBtn.addEventListener('click', e => { e.stopPropagation(); setN(nPanel.hidden); });
            document.addEventListener('click', e => { if (!nPanel.hidden && !nPanel.contains(e.target)) setN(false); });
            document.addEventListener('keydown', e => { if (e.key === 'Escape') setN(false); });
        }

        /* modal statis: [data-modal-open], [data-modal-close], klik latar, Esc */
        document.addEventListener('click', e => {
            const o = e.target.closest('[data-modal-open]');
            if (o) { e.preventDefault(); LF.openModal(o.dataset.modalOpen); return; }
            const c = e.target.closest('[data-modal-close]');
            if (c) { const m = c.closest('.modal-backdrop'); if (m) LF.closeModal(m); return; }
            if (e.target.classList && e.target.classList.contains('modal-backdrop') && e.target.dataset.static === undefined) LF.closeModal(e.target);
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                if (document.body.classList.contains('menu-open')) { setMenu(false); return; }
                const open = [...document.querySelectorAll('.modal-backdrop:not([hidden])')].pop();
                if (open && open.dataset.static === undefined) LF.closeModal(open);
            }
        });

        /* ripple pada tombol */
        document.addEventListener('pointerdown', e => {
            const b = e.target.closest('.btn');
            if (!b || b.disabled) return;
            const r = b.getBoundingClientRect(), s = Math.max(r.width, r.height);
            const sp = document.createElement('span');
            sp.className = 'ripple';
            sp.style.cssText = `width:${s}px;height:${s}px;left:${e.clientX - r.left - s / 2}px;top:${e.clientY - r.top - s / 2}px`;
            b.appendChild(sp);
            setTimeout(() => sp.remove(), 600);
        });
    });

    window.LF = LF;
})();

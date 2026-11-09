/* =========================================================
   QR CODE (memakai assets/js/vendor/qrcode.js - bekerja offline)
   LF.publicBase()          URL dasar aplikasi untuk isi QR
   LF.reportUrl(lab, no)    URL form lapor publik PC
   LF.qrSvg(text, cell)     string <svg> QR
========================================================= */
(function () {
    'use strict';
    const LF = window.LF = window.LF || {};

    /** URL dasar: pilihan manual (localStorage) > server (window.LF_PUBLIC_BASE) > alamat halaman ini. */
    LF.publicBase = function () {
        let v = '';
        try { v = localStorage.getItem('lf_public_base') || ''; } catch (e) {}
        v = v || window.LF_PUBLIC_BASE || (location.origin + location.pathname.replace(/[^/]*$/, ''));
        return v.replace(/\/+$/, '') + '/';
    };
    LF.setPublicBase = function (v) {
        try { v ? localStorage.setItem('lf_public_base', v) : localStorage.removeItem('lf_public_base'); } catch (e) {}
    };
    LF.reportUrl = (lab, no) => LF.publicBase() + 'lapor.php?lab=' + encodeURIComponent(lab) + '&no=' + no;

    LF.qrSvg = function (text, cell) {
        if (typeof qrcode !== 'function') return '';
        const q = qrcode(0, 'M');
        q.addData(text);
        q.make();
        return q.createSvgTag({ cellSize: cell || 6, margin: 2, scalable: true });
    };
})();

/* qr.php: gambar QR tiap stiker; alamat aplikasi bisa diubah & diingat di browser ini. */
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('qrBase');
    const hint = document.getElementById('qrHint');
    const lab = window.LF_QR_LAB;

    function draw() {
        document.querySelectorAll('.qr-sticker').forEach(s => {
            s.querySelector('.qr-img').innerHTML = LF.qrSvg(LF.reportUrl(lab, +s.dataset.no), 6);
        });
        const base = LF.publicBase();
        const local = /^https?:\/\/(localhost|127\.|\[::1\])/i.test(base);
        hint.textContent = local
            ? 'Perhatian: alamat ini hanya bisa dibuka di komputer ini. Isi dengan IP/domain server agar HP bisa membukanya.'
            : 'QR mengarah ke: ' + base + 'lapor.php?lab=' + lab + '&no=…';
        hint.style.color = local ? 'var(--red)' : '';
    }
    input.value = LF.publicBase().replace(/\/$/, '');
    let t;
    input.addEventListener('input', () => {
        clearTimeout(t);
        t = setTimeout(() => {
            const v = input.value.trim();
            LF.setPublicBase(/^https?:\/\//i.test(v) ? v : '');
            draw();
        }, 250);
    });
    draw();
});

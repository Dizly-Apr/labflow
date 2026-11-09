/* =========================================================
   DASHBOARD: sapaan, grafik, kalender
========================================================= */
document.addEventListener('DOMContentLoaded', () => {

    /* ---------- sapaan sesuai jam ---------- */
    const greet = document.getElementById('greet');
    if (greet) {
        const h = new Date().getHours();
        greet.textContent = h < 11 ? 'Selamat pagi' : h < 15 ? 'Selamat siang' : h < 19 ? 'Selamat sore' : 'Selamat malam';
    }

    /* ---------- grafik ---------- */
    const canvas = document.getElementById('summaryChart');
    const emptyBox = document.getElementById('chartEmpty');
    let chart = null;

    if (canvas && window.Chart) {
        chart = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [
                    { label: 'Laporan Lab', data: [], backgroundColor: '#287cf0', borderRadius: 6, maxBarThickness: 26 },
                    { label: 'Stok Masuk',  data: [], backgroundColor: '#9ec5fb', borderRadius: 6, maxBarThickness: 26 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                animation: { duration: 650, easing: 'easeOutQuart' },
                plugins: {
                    legend: { display: false },
                    tooltip: { backgroundColor: '#111827', padding: 11, cornerRadius: 8, titleFont: { weight: '600' } }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#5b6676', font: { size: 12 } } },
                    y: { beginAtZero: true, max: 20, ticks: { stepSize: 20, precision: 0, color: '#8a94a3', font: { size: 11 } }, grid: { color: '#edf0f4' }, border: { display: false } }
                }
            }
        });
    } else if (canvas) {
        if (emptyBox) { emptyBox.hidden = false; emptyBox.querySelector('span').textContent = 'Grafik tidak dapat dimuat (periksa koneksi internet).'; }
    }

    /* warna sumbu mengikuti tema terang / gelap */
    function themeChart() {
        if (!chart) return;
        const css = n => getComputedStyle(document.documentElement).getPropertyValue(n).trim();
        chart.options.scales.x.ticks.color = css('--text-soft');
        chart.options.scales.y.ticks.color = css('--text-mute');
        chart.options.scales.y.grid.color = css('--border');
        chart.update('none');
    }
    document.addEventListener('lf:theme', () => setTimeout(themeChart, 30));
    themeChart();

    function loadChart(months) {
        if (!chart) return;
        fetch('config/api_dashboard.php?months=' + months)
            .then(r => r.json())
            .then(d => {
                chart.data.labels = d.labels;
                chart.data.datasets[0].data = d.lab_report;
                chart.data.datasets[1].data = d.stock_in;
                const max = Math.max(...d.lab_report, ...d.stock_in, 0);
                const step = max > 100 ? 50 : max > 40 ? 20 : max > 10 ? 10 : 5;
                chart.options.scales.y.ticks.stepSize = step;
                chart.options.scales.y.max = Math.max(step * 2, Math.ceil((max * 1.15) / step) * step);
                chart.update();
                if (emptyBox) { emptyBox.hidden = max > 0; if (max === 0) emptyBox.querySelector('span').textContent = 'Belum ada data pada periode ini'; }
            })
            .catch(() => LF.toast('Gagal memuat data grafik.', 'error'));
    }

    document.querySelectorAll('.seg').forEach(btn => btn.addEventListener('click', () => {
        document.querySelectorAll('.seg').forEach(b => b.classList.toggle('active', b === btn));
        loadChart(btn.dataset.months);
    }));
    loadChart(6);

    /* legenda bisa diklik untuk menyembunyikan seri */
    document.querySelectorAll('.legend-item').forEach(item => item.addEventListener('click', () => {
        if (!chart) return;
        const i = +item.dataset.series;
        const visible = chart.isDatasetVisible(i);
        chart.setDatasetVisibility(i, !visible);
        item.classList.toggle('off', visible);
        chart.update();
    }));

    /* ---------- kalender ---------- */
    const MONTHS = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const title = document.getElementById('calendarTitle');
    const grid = document.getElementById('calendarDays');
    const active = new Set(window.LF_ACTIVE_DAYS || []);
    const TODAY = new Date();
    let view = new Date(TODAY.getFullYear(), TODAY.getMonth(), 1);

    const pad = n => String(n).padStart(2, '0');
    const key = (y, m, d) => `${y}-${pad(m + 1)}-${pad(d)}`;

    function cell(y, m, d, muted) {
        const a = document.createElement('a');
        a.className = 'calendar-day' + (muted ? ' muted' : '');
        a.textContent = d;
        const k = key(y, m, d);
        a.href = 'history.php?date=' + k;
        a.title = (active.has(k) ? 'Ada aktivitas • ' : 'Lihat riwayat • ') + d + ' ' + MONTHS[m] + ' ' + y;
        if (active.has(k)) a.classList.add('has');
        if (y === TODAY.getFullYear() && m === TODAY.getMonth() && d === TODAY.getDate()) a.classList.add('today');
        return a;
    }

    function renderCalendar() {
        const y = view.getFullYear(), m = view.getMonth();
        title.textContent = `${MONTHS[m]} ${y}`;
        grid.innerHTML = '';

        let first = new Date(y, m, 1).getDay();           // Minggu = 0
        first = first === 0 ? 6 : first - 1;               // Senin = 0
        const days = new Date(y, m + 1, 0).getDate();
        const prevDays = new Date(y, m, 0).getDate();

        for (let i = first - 1; i >= 0; i--) {
            const py = m === 0 ? y - 1 : y, pm = m === 0 ? 11 : m - 1;
            grid.appendChild(cell(py, pm, prevDays - i, true));
        }
        for (let d = 1; d <= days; d++) grid.appendChild(cell(y, m, d, false));
        const rest = 42 - grid.children.length;
        for (let d = 1; d <= rest; d++) {
            const ny = m === 11 ? y + 1 : y, nm = m === 11 ? 0 : m + 1;
            grid.appendChild(cell(ny, nm, d, true));
        }
    }

    document.getElementById('prevMonth').addEventListener('click', () => { view.setMonth(view.getMonth() - 1); renderCalendar(); });
    document.getElementById('nextMonth').addEventListener('click', () => { view.setMonth(view.getMonth() + 1); renderCalendar(); });
    document.getElementById('todayBtn').addEventListener('click', () => { view = new Date(TODAY.getFullYear(), TODAY.getMonth(), 1); renderCalendar(); });

    renderCalendar();
});

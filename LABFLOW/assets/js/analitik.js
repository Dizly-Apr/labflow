/* analitik.php - grafik Chart.js; warna mengikuti tema terang/gelap. */
document.addEventListener('DOMContentLoaded', () => {
    const D = window.LF_AN;
    if (!D || !window.Chart) { document.querySelectorAll('.an-chart').forEach(c => c.innerHTML = '<div class="an-empty">Grafik tidak dapat dimuat (periksa koneksi internet).</div>'); return; }
    const css = n => getComputedStyle(document.documentElement).getPropertyValue(n).trim();
    const charts = [];

    function hasData(arr) { return arr.some(v => v); }
    function empty(id) {
        const box = document.getElementById(id).parentElement;
        box.innerHTML = '<div class="an-empty">Belum ada data pada periode ini</div>';
    }

    function build() {
        charts.splice(0).forEach(c => c.destroy());
        const text = css('--text-soft'), grid = css('--border'), main = css('--primary'), alt = css('--green');
        Chart.defaults.color = text;
        Chart.defaults.font.family = css('--font') || 'system-ui';
        const scales = { x: { grid: { color: grid }, ticks: { precision: 0 } }, y: { grid: { color: grid }, ticks: { precision: 0 } } };

        const bar = (id, items, horizontal) => {
            const el = document.getElementById(id);
            if (!el) return;
            if (!hasData(items.map(i => i.n))) return empty(id);
            charts.push(new Chart(el, {
                type: 'bar',
                data: { labels: items.map(i => i.name), datasets: [{ label: 'Tiket', data: items.map(i => i.n), backgroundColor: main, borderRadius: 6, maxBarThickness: 28 }] },
                options: { indexAxis: horizontal ? 'y' : 'x', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                           scales: { x: { grid: { display: !horizontal, color: grid }, ticks: { precision: 0 } }, y: { grid: { display: horizontal ? false : true, color: grid }, ticks: { precision: 0 } } } }
            }));
        };
        bar('chLab', D.labs.slice(0, 8), true);
        bar('chComp', D.comps.slice(0, 8), true);

        const t = D.trend;
        const trendEl = document.getElementById('chTrend');
        if (trendEl) {
            if (!hasData(t.created) && !hasData(t.done)) empty('chTrend');
            else charts.push(new Chart(trendEl, {
                type: 'line',
                data: { labels: t.labels, datasets: [
                    { label: 'Dibuat', data: t.created, borderColor: main, backgroundColor: main, tension: .3, pointRadius: 3 },
                    { label: 'Selesai', data: t.done, borderColor: alt, backgroundColor: alt, tension: .3, pointRadius: 3 } ] },
                options: { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false }, scales, plugins: { legend: { position: 'bottom' } } }
            }));
        }
        const timeEl = document.getElementById('chTime');
        if (timeEl) {
            if (!hasData(t.hours)) empty('chTime');
            else charts.push(new Chart(timeEl, {
                type: 'bar',
                data: { labels: t.labels, datasets: [{ label: 'Jam', data: t.hours, backgroundColor: main, borderRadius: 6, maxBarThickness: 36 }] },
                options: { responsive: true, maintainAspectRatio: false, scales, plugins: { legend: { display: false } } }
            }));
        }
    }
    build();
    document.addEventListener('lf:theme', () => setTimeout(build, 30));
});

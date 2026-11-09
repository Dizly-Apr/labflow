<?php
/** Dashboard analitik: lab & komponen paling sering rusak, waktu perbaikan, tren bulanan. */
require_once __DIR__ . '/config/layout.php';
require_once __DIR__ . '/config/tickets.php';
require_login();

$range = in_array((int)($_GET['bulan'] ?? 6), [3, 6, 12, 0], true) ? (int)($_GET['bulan'] ?? 6) : 6;   // 0 = semua
$since = $range ? date('Y-m-01', strtotime("-" . ($range - 1) . " month")) : '2000-01-01';
$pdo   = db();

$q = $pdo->prepare(
    "SELECT l.name, COUNT(r.id) AS n FROM laboratories l
     LEFT JOIN pcs p ON p.laboratory_id = l.id LEFT JOIN lab_reports r ON r.pc_id = p.id AND r.reported_at >= ?
     GROUP BY l.id, l.name ORDER BY n DESC, l.id"
);
$q->execute([$since]);
$byLab = $q->fetchAll();

$q = $pdo->prepare("SELECT COALESCE(component_type, '') AS t, COUNT(*) AS n FROM lab_reports WHERE reported_at >= ? GROUP BY t ORDER BY n DESC");
$q->execute([$since]);
$byComp = array_map(fn($r) => ['name' => $r['t'] === '' ? 'Lainnya' : component_label($r['t']), 'n' => (int)$r['n']], $q->fetchAll());

$q = $pdo->prepare(
    "SELECT COUNT(*) AS total, SUM(status='resolved') AS done, SUM(status<>'resolved') AS active,
            AVG(CASE WHEN status='resolved' THEN TIMESTAMPDIFF(MINUTE, reported_at, resolved_at) END)/60 AS avg_h
     FROM lab_reports WHERE reported_at >= ?"
);
$q->execute([$since]);
$k = $q->fetch();

// tren per bulan: dibuat, selesai, rata-rata jam perbaikan
$months = $range ?: 12;
$map = [];
$q = $pdo->prepare("SELECT DATE_FORMAT(reported_at,'%Y-%m') ym, COUNT(*) n FROM lab_reports WHERE reported_at >= ? GROUP BY ym");
$q->execute([$since]);
foreach ($q as $r) { $map[$r['ym']]['created'] = (int)$r['n']; }
$q = $pdo->prepare("SELECT DATE_FORMAT(resolved_at,'%Y-%m') ym, COUNT(*) n, AVG(TIMESTAMPDIFF(MINUTE, reported_at, resolved_at))/60 h
                    FROM lab_reports WHERE status='resolved' AND resolved_at >= ? GROUP BY ym");
$q->execute([$since]);
foreach ($q as $r) { $map[$r['ym']]['done'] = (int)$r['n']; $map[$r['ym']]['h'] = round((float)$r['h'], 1); }
$bln = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$trend = ['labels' => [], 'created' => [], 'done' => [], 'hours' => []];
for ($i = $months - 1; $i >= 0; $i--) {
    $t = strtotime("first day of -$i month");
    $ym = date('Y-m', $t);
    $trend['labels'][]  = $bln[(int)date('n', $t)] . ' ' . date('y', $t);
    $trend['created'][] = $map[$ym]['created'] ?? 0;
    $trend['done'][]    = $map[$ym]['done'] ?? 0;
    $trend['hours'][]   = $map[$ym]['h'] ?? null;
}

$data = [
    'labs'  => array_map(fn($r) => ['name' => $r['name'], 'n' => (int)$r['n']], $byLab),
    'comps' => $byComp,
    'trend' => $trend,
];
$avgTxt = $k['avg_h'] === null ? '-' : ((float)$k['avg_h'] >= 48 ? round($k['avg_h'] / 24, 1) . ' hari' : round($k['avg_h'], 1) . ' jam');

ui_open('Analitik', 'analitik', '', ['tiket.css'], '<h1>Analitik</h1>');
?>
            <div class="page-head">
                <div>
                    <h1>Analitik Kerusakan</h1>
                    <p>Pola kerusakan &amp; kecepatan perbaikan untuk bahan evaluasi.</p>
                </div>
                <div class="actions">
                    <div class="segmented" role="group" aria-label="Rentang waktu">
                        <?php foreach ([3 => '3 Bulan', 6 => '6 Bulan', 12 => '12 Bulan', 0 => 'Semua'] as $v => $l): ?>
                        <a class="seg<?= $range === $v ? ' active' : '' ?>" href="analitik.php?bulan=<?= $v ?>"><?= $l ?></a>
                        <?php endforeach; ?>
                    </div>
                    <a class="btn btn-secondary" href="laporan.php?jenis=kerusakan"><i data-lucide="file-down"></i> Export</a>
                </div>
            </div>

            <div class="sum-cards">
                <div class="sum-card"><strong><?= (int)$k['total'] ?></strong><span>Tiket dalam periode</span></div>
                <div class="sum-card"><strong><?= (int)$k['done'] ?></strong><span>Selesai diperbaiki</span></div>
                <div class="sum-card"><strong><?= (int)$k['active'] ?></strong><span>Masih aktif</span></div>
                <div class="sum-card"><strong><?= e($avgTxt) ?></strong><span>Rata-rata waktu perbaikan</span></div>
            </div>

            <div class="an-grid">
                <div class="card"><div class="card-head"><h2><i data-lucide="building-2"></i> Lab dengan kerusakan terbanyak</h2></div><div class="an-chart"><canvas id="chLab"></canvas></div></div>
                <div class="card"><div class="card-head"><h2><i data-lucide="cpu"></i> Komponen paling sering rusak</h2></div><div class="an-chart"><canvas id="chComp"></canvas></div></div>
                <div class="card"><div class="card-head"><h2><i data-lucide="trending-up"></i> Tren tiket per bulan</h2></div><div class="an-chart"><canvas id="chTrend"></canvas></div></div>
                <div class="card"><div class="card-head"><h2><i data-lucide="timer"></i> Rata-rata waktu perbaikan (jam)</h2></div><div class="an-chart"><canvas id="chTime"></canvas></div></div>
            </div>
<?php
ui_end_content();
echo '<script>window.LF_AN = ' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) . ';</script>' . "\n";
echo '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>' . "\n";
ui_scripts('', ['analitik.js']);

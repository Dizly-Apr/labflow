<?php
require_once __DIR__ . '/config/layout.php';
require_once __DIR__ . '/config/user_reports.php';
require_login();

$st = db()->query('SELECT * FROM v_dashboard_stats')->fetch();
$labs = ['active' => 0, 'maintenance' => 0];
foreach (db()->query('SELECT status, COUNT(*) AS n FROM laboratories GROUP BY status') as $r) {
    $labs[$r['status']] = (int)$r['n'];
}
$activities = db()->query('SELECT * FROM activity_logs WHERE type <> "login" ORDER BY created_at DESC, id DESC LIMIT 5')->fetchAll();

// tanggal yang punya aktivitas -> diberi titik di kalender
$activeDays = db()->query('SELECT DISTINCT DATE(created_at) FROM activity_logs
                           WHERE type <> "login" AND created_at >= DATE_SUB(CURDATE(), INTERVAL 13 MONTH)')->fetchAll(PDO::FETCH_COLUMN);

$icons = [
    'pc_issue'   => ['triangle-alert', 'red'],
    'pc_update'  => ['monitor',        'blue'],
    'stock_in'   => ['package-plus',   'blue'],
    'stock_out'  => ['package-minus',  'yellow'],
    'lab_status' => ['wrench',         'yellow'],
    'user_mgmt'  => ['users',          'blue'],
    'ticket'     => ['wrench',         'yellow'],
    'asset'      => ['badge-check',    'blue'],
    'system'     => ['database',       'blue'],
    'user_report' => ['inbox',         'blue'],
];

// barang dengan stok <= minimum (peringatan di dashboard)
$lowItems = db()->query('SELECT id, name, quantity, min_quantity FROM stock_items WHERE quantity <= min_quantity ORDER BY quantity ASC, name LIMIT 8')->fetchAll();

// panel laporan user: Koordinator/Admin = laporan masuk terbaru, User = laporan miliknya
$ur = null;
try {
    if (can_edit()) {
        $c  = ur_counts(db());
        $rs = db()->query(
            "SELECT r.id, r.subject, r.category, r.status, r.priority, r.created_at, r.reporter_name, u.full_name AS reporter_full, l.name AS lab_name, p.pc_number
             FROM user_reports r JOIN laboratories l ON l.id=r.lab_id LEFT JOIN pcs p ON p.id=r.pc_id LEFT JOIN users u ON u.id=r.reported_by
             WHERE r.status <> 'resolved' ORDER BY (r.status='new') DESC, FIELD(r.priority,'high','medium','low'), r.created_at DESC LIMIT 5"
        )->fetchAll();
        $ur = ['mode' => 'staff', 'new' => (int)($c['new'] ?? 0), 'active' => array_sum($c) - (int)($c['resolved'] ?? 0), 'rows' => $rs];
    } elseif (can_report()) {
        $q = db()->prepare(
            "SELECT r.id, r.subject, r.category, r.status, r.priority, r.created_at, r.reporter_seen, r.response_note, l.name AS lab_name, p.pc_number
             FROM user_reports r JOIN laboratories l ON l.id=r.lab_id LEFT JOIN pcs p ON p.id=r.pc_id
             WHERE r.reported_by = ? ORDER BY r.reporter_seen ASC, r.created_at DESC LIMIT 4"
        );
        $q->execute([$_SESSION['user_id']]);
        $ur = ['mode' => 'user', 'rows' => $q->fetchAll(), 'unseen' => ui_alerts()['reports_unseen']];
    }
} catch (Throwable $e) { $ur = null; }

$pcTotal  = max(1, (int)$st['pc_total']);
$pcHealth = round(((int)$st['pc_active'] / $pcTotal) * 100);

ui_open('Dashboard', 'dashboard', '', ['dashboard.css', 'tiket.css', 'laporan_user.css'], '<h1>Dashboard</h1>');
?>

            <!-- ================= HERO ================= -->
            <div class="hero">
                <div class="hero-info">
                    <div class="hero-logo"><?= ui_logo('', 64) ?></div>
                    <div class="hero-text">
                        <span class="hero-eyebrow" id="greet">Selamat datang</span>
                        <h2><?= e(current_user_name()) ?></h2>
                        <p>Management Lab &amp; Stock Opname &middot; SMK Bina Informatika</p>
                    </div>
                </div>
                <div class="hero-actions">
                    <?php if (can_edit()): ?>
                    <a href="stock_opname.php" class="btn btn-hero"><i data-lucide="package-plus"></i> Catat Barang Masuk</a>
                    <a href="laporan_masuk.php" class="btn btn-hero-ghost"><i data-lucide="inbox"></i> Laporan Masuk<?= $ur && $ur['new'] ? ' (' . (int)$ur['new'] . ')' : '' ?></a>
                    <?php elseif (can_report()): ?>
                    <a href="laporan_saya.php?baru=1" class="btn btn-hero"><i data-lucide="send"></i> Kirim Laporan</a>
                    <?php endif; ?>
                    <a href="data_lab.php" class="btn btn-hero-ghost"><i data-lucide="monitor"></i> Lihat Laboratorium</a>
                </div>
            </div>

            <!-- ================= STAT CARDS ================= -->
            <div class="stats">
                <a class="stat" href="stock_opname.php">
                    <div class="stat-icon blue"><i data-lucide="boxes"></i></div>
                    <div class="stat-body">
                        <span class="stat-title">Total Stok Barang</span>
                        <strong class="stat-number" data-count="<?= (int)$st['stock_total'] ?>">0</strong>
                        <span class="stat-sub up"><i data-lucide="trending-up"></i> +<?= number_format((int)$st['stock_in_month']) ?> masuk bulan ini</span>
                    </div>
                </a>
                <a class="stat" href="data_lab.php">
                    <div class="stat-icon green"><i data-lucide="monitor-check"></i></div>
                    <div class="stat-body">
                        <span class="stat-title">PC Aktif</span>
                        <strong class="stat-number" data-count="<?= (int)$st['pc_active'] ?>">0</strong>
                        <span class="stat-sub"><?= $pcHealth ?>% dari <?= (int)$st['pc_total'] ?> PC</span>
                        <span class="meter"><i style="width:<?= $pcHealth ?>%"></i></span>
                    </div>
                </a>
                <a class="stat" href="history.php?type=pc_issue">
                    <div class="stat-icon red"><i data-lucide="triangle-alert"></i></div>
                    <div class="stat-body">
                        <span class="stat-title">PC Bermasalah</span>
                        <strong class="stat-number" data-count="<?= (int)$st['pc_issue'] ?>">0</strong>
                        <span class="stat-sub <?= (int)$st['pc_issue'] ? 'down' : 'up' ?>"><?= (int)$st['tickets_open'] ? (int)$st['tickets_open'] . ' tiket perbaikan aktif' : ((int)$st['pc_issue'] ? 'Perlu ditindaklanjuti' : 'Semua PC normal') ?></span>
                    </div>
                </a>
                <a class="stat" href="data_lab.php">
                    <div class="stat-icon yellow"><i data-lucide="wrench"></i></div>
                    <div class="stat-body">
                        <span class="stat-title">Lab Aktif</span>
                        <strong class="stat-number" data-count="<?= $labs['active'] ?>">0</strong>
                        <span class="stat-sub"><?= $labs['maintenance'] ?> lab sedang maintenance</span>
                    </div>
                </a>
            </div>

            <?php if ($ur): ?>
            <!-- ================= PANEL LAPORAN USER ================= -->
            <div class="card ur-dash">
                <div class="card-head">
                    <?php if ($ur['mode'] === 'staff'): ?>
                    <h2><i data-lucide="inbox"></i> Laporan Masuk dari User<?php if ($ur['new']): ?><span class="ur-count-pill" title="Laporan baru"><?= (int)$ur['new'] ?></span><?php endif; ?></h2>
                    <a class="link-more" href="laporan_masuk.php">Lihat semua (<?= (int)$ur['active'] ?> aktif) <i data-lucide="arrow-right"></i></a>
                    <?php else: ?>
                    <h2><i data-lucide="send"></i> Laporan Saya<?php if ($ur['unseen']): ?><span class="ur-count-pill" title="Pembaruan baru"><?= (int)$ur['unseen'] ?></span><?php endif; ?></h2>
                    <a class="link-more" href="laporan_saya.php">Lihat semua <i data-lucide="arrow-right"></i></a>
                    <?php endif; ?>
                </div>
                <?php foreach ($ur['rows'] as $x):
                    [, $catIcon] = UR_CATEGORY[$x['category']] ?? [null, 'ellipsis'];
                    $href  = $ur['mode'] === 'staff' ? 'laporan_masuk.php?id=' : 'laporan_saya.php?id=';
                    $who   = $ur['mode'] === 'staff' ? ' &bull; ' . e($x['reporter_full'] ?: ($x['reporter_name'] ?: 'Pengguna dihapus')) : '';
                ?>
                <a class="activity-item ur-item" href="<?= $href . (int)$x['id'] ?>">
                    <div class="activity-icon <?= $x['priority'] === 'high' ? 'red' : 'blue' ?>"><i data-lucide="<?= e($catIcon) ?>"></i></div>
                    <div class="activity-content">
                        <strong><?= e($x['subject']) ?></strong>
                        <span><?= e($x['lab_name']) ?><?= $x['pc_number'] ? ' &middot; PC ' . (int)$x['pc_number'] : '' ?><?= $who ?> &bull; <?= e(ui_date($x['created_at'])) ?></span>
                    </div>
                    <?= ur_status_badge($x['status']) ?>
                </a>
                <?php endforeach; ?>
                <?php if (!$ur['rows']): ?>
                <div class="empty small"><i data-lucide="<?= $ur['mode'] === 'staff' ? 'inbox' : 'send' ?>"></i>
                    <p><?= $ur['mode'] === 'staff' ? 'Tidak ada laporan yang menunggu. Semua laporan user sudah selesai.' : 'Anda belum mengirim laporan. Ada kendala di laboratorium? Kirim laporan ke Koordinator & Admin.' ?></p>
                    <?php if ($ur['mode'] === 'user'): ?><a class="btn btn-primary btn-sm" href="laporan_saya.php?baru=1" style="margin-top:12px"><i data-lucide="send"></i> Kirim Laporan</a><?php endif; ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($lowItems): ?>
            <!-- ================= PERINGATAN STOK ================= -->
            <div class="card alert-card" role="alert">
                <div class="card-head">
                    <h2><i data-lucide="package-minus"></i> Stok menipis &middot; <?= (int)$st['stock_low'] ?> barang perlu diisi ulang</h2>
                    <a class="link-more" href="stock_opname.php?menipis=1">Lihat semua <i data-lucide="arrow-right"></i></a>
                </div>
                <div class="low-list">
                    <?php foreach ($lowItems as $li): ?>
                    <a href="stock_opname.php?menipis=1"><?= e($li['name']) ?> <b><?= (int)$li['quantity'] ?></b><span class="muted">/ min. <?= (int)$li['min_quantity'] ?></span></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- ================= GRID ================= -->
            <div class="dashboard-grid">

                <!-- CHART -->
                <div class="card chart-card">
                    <div class="card-head">
                        <h2><i data-lucide="chart-column"></i> Ringkasan Data per Bulan</h2>
                        <div class="segmented" role="group" aria-label="Periode grafik">
                            <button type="button" class="seg active" data-months="6">6 Bulan</button>
                            <button type="button" class="seg" data-months="12">12 Bulan</button>
                        </div>
                    </div>

                    <div class="chart-wrapper">
                        <canvas id="summaryChart"></canvas>
                        <div class="chart-empty" id="chartEmpty" hidden>
                            <i data-lucide="chart-no-axes-column"></i>
                            <span>Belum ada data pada periode ini</span>
                        </div>
                    </div>

                    <div class="chart-legend">
                        <button type="button" class="legend-item" data-series="0"><span class="legend-color" style="background:#287cf0"></span> Laporan Lab</button>
                        <button type="button" class="legend-item" data-series="1"><span class="legend-color" style="background:#9ec5fb"></span> Stok Masuk</button>
                        <span class="legend-hint">Klik legenda untuk menyembunyikan</span>
                    </div>
                </div>

                <!-- KOLOM KANAN -->
                <div class="right-column">

                    <div class="card calendar-card">
                        <div class="calendar-header">
                            <button type="button" class="icon-btn" id="prevMonth" aria-label="Bulan sebelumnya"><i data-lucide="chevron-left"></i></button>
                            <h2 id="calendarTitle">&nbsp;</h2>
                            <button type="button" class="icon-btn" id="nextMonth" aria-label="Bulan berikutnya"><i data-lucide="chevron-right"></i></button>
                        </div>
                        <div class="calendar-week"><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span></div>
                        <div class="calendar-days" id="calendarDays"></div>
                        <div class="calendar-foot">
                            <span><i class="cal-dot"></i> Ada aktivitas &middot; klik tanggal untuk melihat riwayat</span>
                            <button type="button" class="btn btn-ghost btn-sm" id="todayBtn">Hari ini</button>
                        </div>
                    </div>

                    <div class="card activity-card">
                        <div class="card-head">
                            <h2><i data-lucide="activity"></i> Aktivitas Terbaru</h2>
                            <a class="link-more" href="history.php">Lihat semua <i data-lucide="arrow-right"></i></a>
                        </div>
                        <?php foreach ($activities as $a): [$ic, $col] = $icons[$a['type']] ?? ['monitor', 'blue']; ?>
                        <a class="activity-item" href="history.php?type=<?= e($a['type']) ?>">
                            <div class="activity-icon <?= $col ?>"><i data-lucide="<?= $ic ?>"></i></div>
                            <div class="activity-content">
                                <strong><?= e($a['title']) ?></strong>
                                <span><?= e(ui_date($a['created_at'])) ?> &bull; <?= e($a['description']) ?></span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                        <?php if (!$activities): ?>
                        <div class="empty"><i data-lucide="inbox"></i><h3>Belum ada aktivitas</h3><p>Aktivitas lab &amp; stok akan muncul di sini.</p></div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

<?php
ui_end_content();
echo '<script>window.LF_ACTIVE_DAYS = ' . json_encode(array_values($activeDays)) . ';</script>' . "\n";
echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>' . "\n";
ui_scripts('', ['dashboard.js']);

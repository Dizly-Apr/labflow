<?php
require_once __DIR__ . '/config/layout.php';
require_login();

$types = [
    'pc_issue'   => 'PC Issue',
    'pc_update'  => 'PC Update',
    'stock_in'   => 'Stock In',
    'stock_out'  => 'Stock Out',
    'lab_status' => 'Lab Status',
    'user_mgmt'  => 'User',
    'login'      => 'Login',
    'ticket'     => 'Tiket',
    'asset'      => 'Aset',
    'system'     => 'Sistem',
    'user_report' => 'Laporan User',
];
$type = isset($types[$_GET['type'] ?? '']) ? $_GET['type'] : 'all';
$q    = trim($_GET['q'] ?? '');
$date = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date'] ?? '') ? $_GET['date'] : '';

$sql  = 'SELECT a.*, u.full_name FROM activity_logs a LEFT JOIN users u ON u.id = a.user_id WHERE 1=1';
$args = [];
if ($type !== 'all') { $sql .= ' AND a.type = ?'; $args[] = $type; }
if ($q !== '')       { $sql .= ' AND (a.title LIKE ? OR a.description LIKE ?)'; $args[] = "%$q%"; $args[] = "%$q%"; }
if ($date !== '')    { $sql .= ' AND DATE(a.created_at) = ?'; $args[] = $date; }
$sql .= ' ORDER BY a.created_at DESC, a.id DESC LIMIT 200';
$s = db()->prepare($sql);
$s->execute($args);
$rows = $s->fetchAll();

// jumlah per jenis (untuk chip filter)
$counts = db()->query('SELECT type, COUNT(*) AS n FROM activity_logs GROUP BY type')->fetchAll(PDO::FETCH_KEY_PAIR);
$total  = array_sum($counts);

/** URL halaman ini dengan parameter yang diubah. */
function hist_url(array $over): string
{
    global $type, $q, $date;
    $p = array_merge(['type' => $type === 'all' ? '' : $type, 'q' => $q, 'date' => $date], $over);
    $p = array_filter($p, fn($v) => $v !== '' && $v !== null);
    return 'history.php' . ($p ? '?' . http_build_query($p) : '');
}

$filtered = $type !== 'all' || $q !== '' || $date !== '';

ui_open('Riwayat', 'lab', '', ['history.css'], '<h1>Riwayat</h1>');
?>

            <div class="page-head">
                <div>
                    <div class="crumbs"><a href="data_lab.php">Data Lab</a><i data-lucide="chevron-right"></i><span>Riwayat</span></div>
                    <h1>Riwayat Aktivitas</h1>
                    <p>Riwayat aktivitas laboratorium &amp; stock opname.</p>
                </div>
                <div class="actions">
                    <a class="btn btn-secondary" href="data_lab.php"><i data-lucide="arrow-left"></i> Data Lab</a>
                </div>
            </div>

            <form class="toolbar" method="get" action="history.php">
                <?php if ($type !== 'all'): ?><input type="hidden" name="type" value="<?= e($type) ?>"><?php endif; ?>
                <div class="input-icon search">
                    <i data-lucide="search"></i>
                    <input type="search" class="input" name="q" value="<?= e($q) ?>" placeholder="Cari aktivitas atau keterangan..." autocomplete="off">
                </div>
                <div class="input-icon">
                    <i data-lucide="calendar"></i>
                    <input type="date" class="input" name="date" value="<?= e($date) ?>" onchange="this.form.submit()" aria-label="Filter tanggal" style="min-width:170px">
                </div>
                <button type="submit" class="btn btn-primary"><i data-lucide="search"></i> Cari</button>
                <?php if ($filtered): ?><a class="btn btn-ghost" href="history.php"><i data-lucide="x"></i> Hapus filter</a><?php endif; ?>
            </form>

            <div class="chips hist-chips" role="group" aria-label="Filter jenis aktivitas">
                <a class="chip<?= $type === 'all' ? ' active' : '' ?>" href="<?= e(hist_url(['type' => ''])) ?>">Semua <span class="n"><?= (int)$total ?></span></a>
                <?php foreach ($types as $k => $label): ?>
                <a class="chip<?= $type === $k ? ' active' : '' ?>" href="<?= e(hist_url(['type' => $k])) ?>"><?= e($label) ?> <span class="n"><?= (int)($counts[$k] ?? 0) ?></span></a>
                <?php endforeach; ?>
            </div>

            <div class="card history-card">
                <div class="result-info">
                    <?= count($rows) ?> aktivitas ditemukan<?= $date ? ' pada <strong>' . e(ui_date($date)) . '</strong>' : '' ?><?= count($rows) >= 200 ? ' (menampilkan 200 terbaru)' : '' ?>
                </div>
                <div class="table-wrap">
                    <table class="table history-table">
                        <thead><tr><th>Waktu</th><th>Jenis</th><th>Aktivitas</th><th>Keterangan</th><th>Petugas</th></tr></thead>
                        <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td class="nowrap"><?= e(ui_date($r['created_at'], true)) ?></td>
                                <td><span class="tag tag-<?= e($r['type']) ?>"><?= e($types[$r['type']] ?? $r['type']) ?></span></td>
                                <td><strong><?= e($r['title']) ?></strong></td>
                                <td class="soft"><?= e($r['description']) ?></td>
                                <td class="nowrap"><?= e($r['full_name'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (!$rows): ?>
                    <div class="empty">
                        <i data-lucide="<?= $filtered ? 'search-x' : 'inbox' ?>"></i>
                        <h3><?= $filtered ? 'Tidak ada aktivitas yang cocok' : 'Belum ada riwayat' ?></h3>
                        <p><?= $filtered ? 'Coba ubah filter atau kata pencarian.' : 'Aktivitas akan tercatat otomatis di sini.' ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

<?php
ui_end_content();
ui_scripts('');

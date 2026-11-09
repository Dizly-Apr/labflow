<?php
/**
 * Laporan & Export (PDF / Excel) untuk koordinator:
 *   jenis=kerusakan  rekap kerusakan per lab (rentang tanggal, opsional per lab)
 *   jenis=stok       stok bulanan (awal / masuk / keluar / akhir)
 *   jenis=audit      audit log aktivitas (rentang tanggal, opsional per jenis)
 *   format=xlsx|pdf  unduh; tanpa format = pratinjau di halaman
 */
require_once __DIR__ . '/config/layout.php';
require_once __DIR__ . '/config/reports.php';
require_login();

$jenis = in_array($_GET['jenis'] ?? '', ['kerusakan', 'stok', 'audit'], true) ? $_GET['jenis'] : 'kerusakan';
$from  = rep_date($_GET['from'] ?? '', date('Y-m-01'));
$to    = rep_date($_GET['to'] ?? '', date('Y-m-d'));
if ($to < $from) { [$from, $to] = [$to, $from]; }
$bulan = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $_GET['bulan'] ?? '') ? $_GET['bulan'] : date('Y-m');
$labF  = preg_replace('/[^a-z0-9_-]/i', '', (string)($_GET['lab'] ?? ''));
$typeF = (string)($_GET['type'] ?? '');

$rep = match ($jenis) {
    'stok'  => report_stok($bulan),
    'audit' => report_audit($from, $to, $typeF),
    default => report_kerusakan($from, $to, $labF),
};

$format = $_GET['format'] ?? '';
if ($format === 'pdf' || $format === 'xlsx') {
    $slug = 'labflow-' . $jenis . '-' . ($jenis === 'stok' ? $bulan : $from . '_sd_' . $to);
    if ($format === 'pdf') {
        require_once __DIR__ . '/config/pdf_report.php';
        pdf_output($rep, $slug . '.pdf');
    }
    require_once __DIR__ . '/config/xlsx_writer.php';
    $x = new LfXlsx();
    foreach ($rep['tables'] as $i => $t) {
        $head = [$rep['title'], $rep['subtitle'], 'SMK Bina Informatika · dicetak ' . ui_date(time(), true) . ' oleh ' . current_user_name()];
        if ($i === 0) {
            $head[] = implode('   |   ', array_map(fn($s) => $s[0] . ': ' . $s[1], $rep['summary']));
        }
        $x->addSheet($t['title'], $head, $t['cols'], $t['rows'], $t['total']);
    }
    $tmp = tempnam(sys_get_temp_dir(), 'lfx');
    $x->save($tmp);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $slug . '.xlsx"');
    header('Content-Length: ' . filesize($tmp));
    readfile($tmp);
    @unlink($tmp);
    exit;
}

$labs = db()->query('SELECT code, name FROM laboratories ORDER BY id')->fetchAll();
$qs = fn(array $o) => http_build_query(array_filter(array_merge(['jenis' => $jenis, 'from' => $from, 'to' => $to, 'bulan' => $bulan, 'lab' => $labF, 'type' => $typeF], $o), fn($v) => $v !== '' && $v !== null));

$types = [
    'kerusakan' => ['wrench', 'Rekap Kerusakan', 'Tiket & PC bermasalah per lab, komponen paling sering rusak'],
    'stok'      => ['archive', 'Stok Bulanan', 'Stok awal, masuk, keluar, dan akhir per barang'],
    'audit'     => ['history', 'Audit Log', 'Semua aktivitas sistem beserta petugas pencatat'],
];

ui_open('Laporan & Export', 'laporan', '', ['tiket.css'], '<h1>Laporan &amp; Export</h1>');
?>
            <div class="page-head">
                <div>
                    <h1>Laporan &amp; Export</h1>
                    <p>Siap dilaporkan ke sekolah &mdash; unduh sebagai PDF (ber-kop &amp; kolom tanda tangan) atau Excel.</p>
                </div>
            </div>

            <div class="report-grid">
                <?php foreach ($types as $k => [$ic, $name, $desc]): ?>
                <a class="report-type<?= $jenis === $k ? ' active' : '' ?>" href="laporan.php?jenis=<?= $k ?>">
                    <i data-lucide="<?= $ic ?>"></i><strong><?= e($name) ?></strong><span><?= e($desc) ?></span>
                </a>
                <?php endforeach; ?>
            </div>

            <form class="toolbar" method="get" action="laporan.php">
                <input type="hidden" name="jenis" value="<?= e($jenis) ?>">
                <?php if ($jenis === 'stok'): ?>
                <div class="input-icon"><i data-lucide="calendar"></i><input type="month" class="input" name="bulan" value="<?= e($bulan) ?>" aria-label="Bulan" onchange="this.form.submit()" style="min-width:190px"></div>
                <?php else: ?>
                <div class="input-icon"><i data-lucide="calendar"></i><input type="date" class="input" name="from" value="<?= e($from) ?>" aria-label="Dari tanggal" style="min-width:170px"></div>
                <span class="soft">s/d</span>
                <div class="input-icon"><i data-lucide="calendar"></i><input type="date" class="input" name="to" value="<?= e($to) ?>" aria-label="Sampai tanggal" style="min-width:170px"></div>
                <?php endif; ?>
                <?php if ($jenis === 'kerusakan'): ?>
                <select class="select" name="lab" aria-label="Laboratorium">
                    <option value="">Semua lab</option>
                    <?php foreach ($labs as $l): ?><option value="<?= e($l['code']) ?>"<?= $labF === $l['code'] ? ' selected' : '' ?>><?= e($l['name']) ?></option><?php endforeach; ?>
                </select>
                <?php elseif ($jenis === 'audit'): ?>
                <select class="select" name="type" aria-label="Jenis aktivitas">
                    <option value="">Semua jenis</option>
                    <?php foreach (report_labels_audit() as $k => $l): ?><option value="<?= e($k) ?>"<?= $typeF === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
                </select>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary"><i data-lucide="refresh-cw"></i> Tampilkan</button>
                <div class="export-bar">
                    <a class="btn btn-secondary" href="laporan.php?<?= e($qs(['format' => 'xlsx'])) ?>"><i data-lucide="file-spreadsheet"></i> Excel</a>
                    <a class="btn btn-secondary" href="laporan.php?<?= e($qs(['format' => 'pdf'])) ?>"><i data-lucide="file-text"></i> PDF</a>
                </div>
            </form>

            <div class="card">
                <div class="card-head"><h2><i data-lucide="file-bar-chart"></i> <?= e($rep['title']) ?></h2><span class="badge badge-blue"><?= e($rep['subtitle']) ?></span></div>
                <div class="sum-cards">
                    <?php foreach ($rep['summary'] as [$l, $v]): ?><div class="sum-card"><strong><?= e(is_int($v) ? number_format($v, 0, ',', '.') : $v) ?></strong><span><?= e($l) ?></span></div><?php endforeach; ?>
                </div>
                <?php foreach ($rep['tables'] as $t): ?>
                <h3 class="section-title"><?= e($t['title']) ?></h3>
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><?php foreach ($t['cols'] as $c): ?><th<?= $c[2] === 'R' ? ' class="num"' : '' ?>><?= e($c[0]) ?></th><?php endforeach; ?></tr></thead>
                        <tbody>
                        <?php foreach ($t['rows'] as $r): ?>
                            <tr><?php foreach ($t['cols'] as $i => $c): ?><td<?= $c[2] === 'R' ? ' class="num"' : '' ?>><?= e($r[$i] ?? '') ?></td><?php endforeach; ?></tr>
                        <?php endforeach; ?>
                        <?php if ($t['total']): ?>
                            <tr class="total-row"><?php foreach ($t['cols'] as $i => $c): ?><td<?= $c[2] === 'R' ? ' class="num"' : '' ?>><?= e($t['total'][$i] ?? '') ?></td><?php endforeach; ?></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    <?php if (!$t['rows']): ?><div class="empty small"><i data-lucide="inbox"></i><p>Tidak ada data pada periode ini.</p></div><?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
<?php
ui_end_content();
ui_scripts('');

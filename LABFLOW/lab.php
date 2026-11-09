<?php
/**
 * Denah lab:  lab.php?lab=rpl
 * Satu halaman untuk semua lab - menambah lab baru tidak perlu membuat folder/file.
 * Jumlah PC & status diambil dari database (v_lab_overview).
 */
require_once __DIR__ . '/config/layout.php';
require_once __DIR__ . '/config/user_reports.php';
require_login();

$base  = '';
$labCode = preg_replace('/[^a-z0-9_-]/i', '', (string)($_GET['lab'] ?? ''));
$lab   = get_lab($labCode);
if (!$lab) {
    http_response_code(404);
    echo '<!DOCTYPE html><meta charset="utf-8"><title>404</title><body style="font-family:sans-serif;text-align:center;padding:80px">'
       . '<h1>404 - Lab tidak ditemukan</h1><p><a href="data_lab.php">Kembali ke Data Lab</a></p></body>';
    exit;
}

$issue   = get_issue_pcs($labCode);
$total   = (int)$lab['total_pcs'];
$maint   = $lab['status'] === 'maintenance';
$canEdit = can_edit();
$nIssue  = count($issue);

/** Satu kotak PC pada denah. */
function lab_pc(int $n, array $issue): string
{
    global $labCode;
    $bad = in_array($n, $issue, true);
    return '<a class="pc-station ' . ($bad ? 'is-issue' : 'is-ok') . '" href="pc.php?lab=' . rawurlencode($labCode) . '&amp;no=' . $n . '"'
         . ' title="PC ' . $n . ' &bull; ' . ($bad ? 'Bermasalah' : 'Normal') . '" aria-label="PC ' . $n . ', ' . ($bad ? 'bermasalah' : 'normal') . '">'
         . '<span class="pc-tag">PC ' . $n . '</span>'
         . '<i data-lucide="monitor" class="pc-icon"></i>'
         . ($bad ? '<span class="pc-alert"></span>' : '')
         . '</a>';
}

$left = '<a class="icon-circle" href="data_lab.php" title="Kembali ke Data Lab" aria-label="Kembali ke Data Lab"><i data-lucide="arrow-left"></i></a>'
      . '<h1>' . e($lab['name']) . '</h1>'
      . '<span class="status-badge ' . e($lab['status']) . '"><span class="dot"></span>' . ($maint ? 'Maintenance' : 'Active') . '</span>';

ui_open($lab['name'], 'lab', $base, ['lab.css', 'laporan_user.css'], $left);
$qrUrl = 'qr.php?lab=' . rawurlencode($labCode);
?>

            <div class="page-head">
                <div>
                    <div class="crumbs"><a href="data_lab.php">Data Lab</a><i data-lucide="chevron-right"></i><span><?= e($lab['name']) ?></span></div>
                    <h1><?= e($lab['name']) ?> <small class="floor-label"><?= e($lab['floor']) ?></small></h1>
                    <p><?= $total ?> PC &middot; <?= $total - $nIssue ?> normal &middot; <?= $nIssue ?> bermasalah</p>
                </div>
                <div class="actions">
                    <a class="btn btn-secondary" href="<?= e($qrUrl) ?>"><i data-lucide="qr-code"></i> <span>Stiker QR</span></a>
                    <a class="btn btn-secondary" href="laporan.php?jenis=kerusakan&amp;lab=<?= e($lab['code']) ?>"><i data-lucide="file-down"></i> <span>Rekap</span></a>
                    <?php if (can_report()): ?>
                    <button type="button" class="btn btn-primary" data-ur-open data-lab="<?= e($lab['code']) ?>"><i data-lucide="send"></i> <span>Kirim Laporan</span></button>
                    <?php endif; ?>
                    <?php if ($canEdit): ?>
                    <button type="button" class="btn <?= $maint ? 'btn-success' : 'btn-warning' ?>" id="btnMaint" data-code="<?= e($lab['code']) ?>" data-status="<?= e($lab['status']) ?>" data-name="<?= e($lab['name']) ?>">
                        <i data-lucide="<?= $maint ? 'circle-check' : 'wrench' ?>"></i>
                        <span><?= $maint ? 'Set Active' : 'Maintenance All' ?></span>
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($maint): ?>
            <div class="note note-warn"><i data-lucide="wrench"></i><div><strong><?= e($lab['name']) ?> sedang dalam maintenance.</strong> Laboratorium ini tidak digunakan sampai statusnya dikembalikan ke Active.</div></div>
            <?php endif; ?>

            <div class="card lab-floor<?= $maint ? ' is-maint' : '' ?>">
                <div class="floor-top">
                    <div class="legend">
                        <span class="legend-i"><span class="lg ok"></span> Normal</span>
                        <span class="legend-i"><span class="lg bad"></span> Bermasalah</span>
                    </div>
                    <div class="whiteboard">INTERACTIVE WHITEBOARD</div>
                    <div class="network-gear"><i data-lucide="router"></i> Switch &amp; Access Point</div>
                </div>

                <div class="projector-area">
                    <div class="projector-beams"></div>
                    <div class="projector-label"><i data-lucide="projector"></i> Projector</div>
                </div>

                <?php if ($total === 30): ?>
                <div class="pc-grid-layout classic">
                    <div class="pc-column side-column"><?php for ($i = 1; $i <= 5; $i++) echo lab_pc($i, $issue); ?></div>
                    <div class="pc-center-block">
                        <?php for ($n = 6; $n <= 25; $n++) echo lab_pc($n, $issue); ?>
                    </div>
                    <div class="pc-column side-column"><?php for ($i = 26; $i <= 30; $i++) echo lab_pc($i, $issue); ?></div>
                </div>
                <?php else: ?>
                <div class="pc-grid-layout simple">
                    <div class="pc-center-block">
                        <?php for ($n = 1; $n <= $total; $n++) echo lab_pc($n, $issue); ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($nIssue): ?>
                <div class="issue-strip">
                    <i data-lucide="triangle-alert"></i>
                    <span>PC bermasalah:</span>
                    <?php foreach ($issue as $n): ?><a class="chip" href="pc.php?lab=<?= e(rawurlencode($labCode)) ?>&amp;no=<?= (int)$n ?>">PC <?= (int)$n ?></a><?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

<?php
ui_end_content();
if (can_report()) { ur_modal($lab['code']); }
ui_scripts($base, ['lab.js', 'laporan_user.js']);

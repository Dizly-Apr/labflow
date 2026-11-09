<?php
/**
 * Tiket perbaikan:  tiket.php  (daftar)   |   tiket.php?id=12  (detail + riwayat)
 * Alur: Open -> Diproses -> Menunggu Sparepart -> Selesai
 */
require_once __DIR__ . '/config/layout.php';
require_once __DIR__ . '/config/tickets.php';
require_login();

$pdo     = db();
$canEdit = can_edit();

/* ======================= DETAIL ======================= */
if (isset($_GET['id'])) {
    $t = ticket_get($pdo, (int)$_GET['id']);
    if (!$t) {
        http_response_code(404);
        echo '<!DOCTYPE html><meta charset="utf-8"><title>404</title><body style="font-family:sans-serif;text-align:center;padding:80px">'
           . '<h1>404 - Tiket tidak ditemukan</h1><p><a href="tiket.php">Kembali ke daftar tiket</a></p></body>';
        exit;
    }
    $ev = $pdo->prepare('SELECT e.*, u.full_name FROM report_events e LEFT JOIN users u ON u.id=e.user_id WHERE e.report_id=? ORDER BY e.created_at DESC, e.id DESC');
    $ev->execute([$t['id']]);
    $events = $ev->fetchAll();
    $rep = $t['reported_by'] ? $pdo->query('SELECT full_name FROM users WHERE id=' . (int)$t['reported_by'])->fetchColumn() : null;
    $reporter = $t['source'] === 'public' ? ($t['reporter_name'] ?: 'Pelapor (form QR)') : ($rep ?: '-');
    $end   = $t['resolved_at'] ?: date('Y-m-d H:i:s');
    $hours = round((strtotime($end) - strtotime($t['reported_at'])) / 3600, 1);
    $dur   = $hours >= 48 ? round($hours / 24, 1) . ' hari' : $hours . ' jam';
    $pcLink = pc_url($t['lab_code'], (int)$t['pc_number']);

    ui_open('Tiket #' . $t['id'], 'tiket', '', ['tiket.css'],
        '<a class="icon-circle" href="tiket.php" aria-label="Kembali ke daftar tiket"><i data-lucide="arrow-left"></i></a><h1>Tiket #' . (int)$t['id'] . '</h1>');
    ?>
            <div class="page-head">
                <div>
                    <div class="crumbs"><a href="tiket.php">Tiket Perbaikan</a><i data-lucide="chevron-right"></i><span>#<?= (int)$t['id'] ?></span></div>
                    <h1><?= e($t['description']) ?></h1>
                    <p><?= e($t['lab_name']) ?> &middot; PC <?= (int)$t['pc_number'] ?> <?= $t['component_type'] ? '&middot; ' . e(component_label($t['component_type'])) : '' ?></p>
                </div>
                <div class="actions"><a class="btn btn-secondary" href="<?= e($pcLink) ?>"><i data-lucide="monitor"></i> Buka PC <?= (int)$t['pc_number'] ?></a></div>
            </div>

            <div class="tk-grid">
                <div class="card">
                    <div class="card-head"><h2><i data-lucide="info"></i> Informasi</h2><?= ticket_badge($t['status']) ?></div>
                    <dl class="kv">
                        <div><dt>Prioritas</dt><dd><?= ticket_prio_badge($t['priority']) ?></dd></div>
                        <div><dt>Teknisi</dt><dd><?= $t['technician'] ? e($t['technician']) : '<span class="muted">Belum ditugaskan</span>' ?></dd></div>
                        <div><dt>Dilaporkan</dt><dd><?= e(ui_date($t['reported_at'], true)) ?> oleh <?= e($reporter) ?><?= $t['source'] === 'public' ? ' <span class="tk-badge tk-purple">Form QR</span>' : '' ?></dd></div>
                        <div><dt><?= $t['status'] === 'resolved' ? 'Selesai' : 'Berjalan' ?></dt><dd><?= $t['status'] === 'resolved' ? e(ui_date($t['resolved_at'], true)) . ' &middot; ' : '' ?><?= e($dur) ?></dd></div>
                        <?php if ($t['resolution_note']): ?><div><dt>Catatan selesai</dt><dd><?= e($t['resolution_note']) ?></dd></div><?php endif; ?>
                    </dl>

                    <?php if ($canEdit): ?>
                    <hr class="sep">
                    <form id="tkEdit" data-id="<?= (int)$t['id'] ?>" autocomplete="off">
                        <div class="two-col">
                            <div class="field"><label for="fStatus">Status</label>
                                <select class="select" id="fStatus"><?php foreach (TICKET_STATUS as $k => [$l]): ?><option value="<?= $k ?>"<?= $k === $t['status'] ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
                            <div class="field"><label for="fPrio">Prioritas</label>
                                <select class="select" id="fPrio"><?php foreach (TICKET_PRIORITY as $k => [$l]): ?><option value="<?= $k ?>"<?= $k === $t['priority'] ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
                        </div>
                        <div class="field"><label for="fTech">Teknisi penanggung jawab</label>
                            <input type="text" class="input" id="fTech" maxlength="100" value="<?= e($t['technician']) ?>" placeholder="Nama teknisi / guru produktif"></div>
                        <div class="field"><label for="fNote">Catatan <span class="hint">(masuk ke riwayat tiket)</span></label>
                            <textarea class="textarea" id="fNote" rows="2" maxlength="400" placeholder="Contoh: menunggu RAM pengganti dari supplier"></textarea></div>
                        <div class="form-actions"><button type="submit" class="btn btn-primary" id="btnTk"><i data-lucide="save"></i> Simpan Perubahan</button></div>
                    </form>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="card-head"><h2><i data-lucide="history"></i> Riwayat Tiket</h2></div>
                    <ul class="timeline">
                        <?php foreach ($events as $x): ?>
                        <li>
                            <span class="tl-dot tl-<?= e($x['action']) ?>"></span>
                            <div>
                                <div class="tl-text"><?= e($x['detail']) ?></div>
                                <div class="tl-meta"><?= e(ui_date($x['created_at'], true)) ?> &bull; <?= e($x['full_name'] ?? $x['actor_name'] ?? 'Sistem') ?></div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
    <?php
    ui_end_content();
    ui_scripts('', ['tiket.js']);
    exit;
}

/* ======================= DAFTAR ======================= */
$statusF = $_GET['status'] ?? '';
$aktif   = !empty($_GET['aktif']);
$prioF   = isset(TICKET_PRIORITY[$_GET['prio'] ?? '']) ? $_GET['prio'] : '';
$labF    = preg_replace('/[^a-z0-9_-]/i', '', (string)($_GET['lab'] ?? ''));
$q       = trim($_GET['q'] ?? '');
if (!isset(TICKET_STATUS[$statusF])) { $statusF = ''; }

$where = ['1=1']; $args = [];
if ($aktif)          { $where[] = "r.status <> 'resolved'"; }
if ($statusF !== '') { $where[] = 'r.status = ?'; $args[] = $statusF; }
if ($prioF !== '')   { $where[] = 'r.priority = ?'; $args[] = $prioF; }
if ($labF !== '')    { $where[] = 'l.code = ?'; $args[] = $labF; }
if ($q !== '') {
    $where[] = "(r.description LIKE ? OR r.technician LIKE ? OR r.reporter_name LIKE ? OR CONCAT(l.name,' PC ',p.pc_number) LIKE ? OR CONCAT('#',r.id) = ?)";
    $like = '%' . $q . '%';
    array_push($args, $like, $like, $like, $like, $q);
}
$s = $pdo->prepare(
    "SELECT r.*, p.pc_number, l.code AS lab_code, l.name AS lab_name
     FROM lab_reports r JOIN pcs p ON p.id=r.pc_id JOIN laboratories l ON l.id=p.laboratory_id
     WHERE " . implode(' AND ', $where) . "
     ORDER BY (r.status='resolved'), FIELD(r.priority,'critical','high','medium','low'), r.reported_at DESC LIMIT 300"
);
$s->execute($args);
$rows = $s->fetchAll();

$counts = $pdo->query('SELECT status, COUNT(*) FROM lab_reports GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
$activeN = array_sum($counts) - (int)($counts['resolved'] ?? 0);
$labs    = $pdo->query('SELECT code, name FROM laboratories ORDER BY id')->fetchAll();
$avg     = $pdo->query("SELECT ROUND(AVG(TIMESTAMPDIFF(MINUTE, reported_at, resolved_at))/60, 1) FROM lab_reports WHERE status='resolved' AND resolved_at IS NOT NULL")->fetchColumn();

function tk_url(array $over): string
{
    global $statusF, $aktif, $prioF, $labF, $q;
    $p = array_merge(['status' => $statusF, 'aktif' => $aktif ? 1 : '', 'prio' => $prioF, 'lab' => $labF, 'q' => $q], $over);
    $p = array_filter($p, fn($v) => $v !== '' && $v !== null);
    return 'tiket.php' . ($p ? '?' . http_build_query($p) : '');
}
$filtered = $statusF !== '' || $aktif || $prioF !== '' || $labF !== '' || $q !== '';

ui_open('Tiket Perbaikan', 'tiket', '', ['tiket.css'], '<h1>Tiket Perbaikan</h1>');
?>
            <div class="page-head">
                <div>
                    <h1>Tiket Perbaikan</h1>
                    <p><?= $activeN ?> tiket aktif<?= $avg !== null && $avg !== false ? ' &middot; rata-rata waktu perbaikan ' . e($avg) . ' jam' : '' ?>.</p>
                </div>
                <div class="actions">
                    <a class="btn btn-secondary" href="laporan.php?jenis=kerusakan"><i data-lucide="file-down"></i> Export Rekap</a>
                </div>
            </div>

            <form class="toolbar" method="get" action="tiket.php">
                <?php if ($statusF): ?><input type="hidden" name="status" value="<?= e($statusF) ?>"><?php endif; ?>
                <?php if ($aktif): ?><input type="hidden" name="aktif" value="1"><?php endif; ?>
                <div class="input-icon search"><i data-lucide="search"></i>
                    <input type="search" class="input" name="q" value="<?= e($q) ?>" placeholder="Cari masalah, teknisi, atau 'Lab RPL PC 7'…" autocomplete="off"></div>
                <select class="select" name="lab" onchange="this.form.submit()" aria-label="Filter lab">
                    <option value="">Semua lab</option>
                    <?php foreach ($labs as $l): ?><option value="<?= e($l['code']) ?>"<?= $labF === $l['code'] ? ' selected' : '' ?>><?= e($l['name']) ?></option><?php endforeach; ?>
                </select>
                <select class="select" name="prio" onchange="this.form.submit()" aria-label="Filter prioritas">
                    <option value="">Semua prioritas</option>
                    <?php foreach (TICKET_PRIORITY as $k => [$l]): ?><option value="<?= $k ?>"<?= $prioF === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary"><i data-lucide="search"></i> Cari</button>
                <?php if ($filtered): ?><a class="btn btn-ghost" href="tiket.php"><i data-lucide="x"></i> Hapus filter</a><?php endif; ?>
            </form>

            <div class="chips hist-chips" role="group" aria-label="Filter status tiket">
                <a class="chip<?= !$statusF && !$aktif ? ' active' : '' ?>" href="<?= e(tk_url(['status' => '', 'aktif' => ''])) ?>">Semua <span class="n"><?= array_sum($counts) ?></span></a>
                <a class="chip<?= $aktif && !$statusF ? ' active' : '' ?>" href="<?= e(tk_url(['status' => '', 'aktif' => 1])) ?>">Aktif <span class="n"><?= $activeN ?></span></a>
                <?php foreach (TICKET_STATUS as $k => [$l]): ?>
                <a class="chip<?= $statusF === $k ? ' active' : '' ?>" href="<?= e(tk_url(['status' => $k, 'aktif' => ''])) ?>"><?= e($l) ?> <span class="n"><?= (int)($counts[$k] ?? 0) ?></span></a>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <div class="result-info"><?= count($rows) ?> tiket ditemukan<?= count($rows) >= 300 ? ' (menampilkan 300 teratas)' : '' ?></div>
                <div class="table-wrap">
                    <table class="table tk-table">
                        <thead><tr><th>#</th><th>PC</th><th>Masalah</th><th>Prioritas</th><th>Status</th><th>Teknisi</th><th>Dilaporkan</th></tr></thead>
                        <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr class="row-link" data-href="tiket.php?id=<?= (int)$r['id'] ?>">
                                <td class="nowrap"><a class="link" href="tiket.php?id=<?= (int)$r['id'] ?>"><strong>#<?= (int)$r['id'] ?></strong></a></td>
                                <td class="nowrap"><?= e($r['lab_name']) ?> &middot; PC <?= (int)$r['pc_number'] ?></td>
                                <td><?= e($r['description']) ?><?= $r['component_type'] ? ' <span class="muted">· ' . e(component_label($r['component_type'])) . '</span>' : '' ?><?= $r['source'] === 'public' ? ' <span class="tk-badge tk-purple">Form QR</span>' : '' ?></td>
                                <td><?= ticket_prio_badge($r['priority']) ?></td>
                                <td><?= ticket_badge($r['status']) ?></td>
                                <td><?= $r['technician'] ? e($r['technician']) : '<span class="muted">&mdash;</span>' ?></td>
                                <td class="nowrap"><?= e(ui_date($r['reported_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (!$rows): ?>
                    <div class="empty"><i data-lucide="<?= $filtered ? 'search-x' : 'circle-check' ?>"></i>
                        <h3><?= $filtered ? 'Tidak ada tiket yang cocok' : 'Belum ada tiket' ?></h3>
                        <p><?= $filtered ? 'Coba ubah filter.' : 'Tiket dibuat otomatis saat komponen ditandai ISSUE atau saat ada laporan dari form QR.' ?></p></div>
                    <?php endif; ?>
                </div>
            </div>
<?php
ui_end_content();
ui_scripts('', ['tiket.js']);

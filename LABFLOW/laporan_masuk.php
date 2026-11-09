<?php
/**
 * Laporan Masuk (Koordinator & Admin):  laporan_masuk.php  (daftar)  |  laporan_masuk.php?id=5  (detail + tanggapan)
 * Berisi semua laporan yang dikirim role User. Koordinator dan Admin melihat kotak masuk yang sama.
 */
require_once __DIR__ . '/config/layout.php';
require_once __DIR__ . '/config/user_reports.php';
require_login();
if (!can_edit()) {
    ur_forbidden('Halaman ini untuk Koordinator & Admin. Untuk mengirim laporan, buka menu Laporan Saya.', can_report() ? 'laporan_saya.php' : 'dashboard.php');
}

$pdo = db();
$uid = (int)$_SESSION['user_id'];

/* ======================= DETAIL ======================= */
if (isset($_GET['id'])) {
    $r = ur_get($pdo, (int)$_GET['id']);
    if (!$r) { ur_not_found('laporan_masuk.php', 'Kembali ke Laporan Masuk'); }

    // dibuka petugas untuk pertama kali -> otomatis "Dibaca" (pelapor melihat statusnya berubah)
    if ($r['status'] === 'new') {
        $pdo->prepare("UPDATE user_reports SET status='read', reporter_seen=0 WHERE id=? AND status='new'")->execute([$r['id']]);
        ur_event($pdo, (int)$r['id'], 'status', ur_status_label('new') . ' → ' . ur_status_label('read'), $uid);
        $r['status'] = 'read';
    }
    $events = ur_events($pdo, (int)$r['id']);
    $pcLink = $r['pc_number'] ? pc_url($r['lab_code'], (int)$r['pc_number']) : 'lab.php?lab=' . rawurlencode($r['lab_code']);
    $place  = $r['lab_name'] . ($r['pc_number'] ? ' · PC ' . (int)$r['pc_number'] : ' · seluruh lab');

    ui_open('Laporan #' . $r['id'], 'laporan_masuk', '', ['tiket.css', 'laporan_user.css'],
        '<a class="icon-circle" href="laporan_masuk.php" aria-label="Kembali ke Laporan Masuk"><i data-lucide="arrow-left"></i></a><h1>Laporan #' . (int)$r['id'] . '</h1>');
    ?>
            <div class="page-head">
                <div>
                    <div class="crumbs"><a href="laporan_masuk.php">Laporan Masuk</a><i data-lucide="chevron-right"></i><span>#<?= (int)$r['id'] ?></span></div>
                    <h1><?= e($r['subject']) ?></h1>
                    <p><?= e($place) ?> &middot; dari <?= e(ur_reporter($r)) ?></p>
                </div>
                <div class="actions">
                    <a class="btn btn-secondary" href="<?= e($pcLink) ?>"><i data-lucide="<?= $r['pc_number'] ? 'monitor' : 'layout-grid' ?>"></i> <?= $r['pc_number'] ? 'Buka PC ' . (int)$r['pc_number'] : 'Buka denah lab' ?></a>
                </div>
            </div>

            <div class="tk-grid">
                <div class="card">
                    <div class="card-head"><h2><i data-lucide="info"></i> Laporan</h2><?= ur_status_badge($r['status']) ?></div>
                    <dl class="kv">
                        <div><dt>Jenis</dt><dd><?= ur_category_badge($r['category']) ?></dd></div>
                        <div><dt>Urgensi</dt><dd><?= ticket_prio_badge($r['priority']) ?></dd></div>
                        <div><dt>Pelapor</dt><dd><?= e(ur_reporter($r)) ?> <span class="muted">&middot; <?= e(ui_date($r['created_at'], true)) ?></span></dd></div>
                        <div><dt>Lokasi</dt><dd><?= e($place) ?></dd></div>
                        <?php if ($r['handler_name']): ?><div><dt>Ditangani</dt><dd><?= e($r['handler_name']) ?> <span class="muted">&middot; <?= e(ui_date($r['handled_at'], true)) ?></span></dd></div><?php endif; ?>
                        <?php if ($r['ticket_id']): ?><div><dt>Tiket</dt><dd><a class="link" href="tiket.php?id=<?= (int)$r['ticket_id'] ?>"><strong>Tiket Perbaikan #<?= (int)$r['ticket_id'] ?></strong></a></dd></div><?php endif; ?>
                    </dl>
                    <div class="ur-msg"><?= nl2br(e($r['message'])) ?></div>

                    <hr class="sep">
                    <form id="urEdit" data-id="<?= (int)$r['id'] ?>" autocomplete="off">
                        <div class="field"><label for="uStatus">Status</label>
                            <select class="select" id="uStatus"><?php foreach (UR_STATUS as $k => [$l]): ?><option value="<?= $k ?>"<?= $k === $r['status'] ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
                        <div class="field"><label for="uResp">Tanggapan untuk pelapor <span class="hint">(terlihat oleh pelapor)</span></label>
                            <textarea class="textarea" id="uResp" rows="3" maxlength="500" placeholder="Contoh: Terima kasih, teknisi akan mengecek besok pagi."><?= e($r['response_note']) ?></textarea></div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" id="btnUrSave"><i data-lucide="save"></i> Simpan</button>
                            <?php if ($r['pc_id'] && !$r['ticket_id']): ?>
                            <button type="button" class="btn btn-warning" id="btnUrTicket"><i data-lucide="wrench"></i> Jadikan Tiket Perbaikan</button>
                            <?php endif; ?>
                            <?php if (is_admin()): ?>
                            <button type="button" class="btn btn-ghost danger-text" id="btnUrDelete"><i data-lucide="trash-2"></i> Hapus</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="card">
                    <div class="card-head"><h2><i data-lucide="history"></i> Riwayat Laporan</h2></div>
                    <ul class="timeline">
                        <?php foreach ($events as $x): ?>
                        <li>
                            <span class="tl-dot tl-<?= e($x['action']) ?>"></span>
                            <div>
                                <div class="tl-text"><?= e($x['detail']) ?></div>
                                <div class="tl-meta"><?= e(ui_date($x['created_at'], true)) ?> &bull; <?= e($x['full_name'] ?? 'Sistem') ?></div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
    <?php
    ui_end_content();
    ui_scripts('', ['laporan_user.js']);
    exit;
}

/* ======================= DAFTAR ======================= */
$statusF = isset(UR_STATUS[$_GET['status'] ?? '']) ? $_GET['status'] : '';
$catF    = isset(UR_CATEGORY[$_GET['kategori'] ?? '']) ? $_GET['kategori'] : '';
$labF    = preg_replace('/[^a-z0-9_-]/i', '', (string)($_GET['lab'] ?? ''));
$aktif   = !empty($_GET['aktif']);
$q       = trim($_GET['q'] ?? '');

$where = ['1=1']; $args = [];
if ($aktif)          { $where[] = "r.status <> 'resolved'"; }
if ($statusF !== '') { $where[] = 'r.status = ?';   $args[] = $statusF; }
if ($catF !== '')    { $where[] = 'r.category = ?'; $args[] = $catF; }
if ($labF !== '')    { $where[] = 'l.code = ?';     $args[] = $labF; }
if ($q !== '') {
    $where[] = "(r.subject LIKE ? OR r.message LIKE ? OR r.reporter_name LIKE ? OR u.full_name LIKE ? OR CONCAT('#',r.id) = ?)";
    $like = '%' . $q . '%';
    array_push($args, $like, $like, $like, $like, $q);
}
$s = $pdo->prepare(
    "SELECT r.*, l.code AS lab_code, l.name AS lab_name, p.pc_number, u.full_name AS reporter_full
     FROM user_reports r
     JOIN laboratories l ON l.id = r.lab_id
     LEFT JOIN pcs p ON p.id = r.pc_id
     LEFT JOIN users u ON u.id = r.reported_by
     WHERE " . implode(' AND ', $where) . "
     ORDER BY (r.status='resolved'), (r.status='new') DESC, FIELD(r.priority,'high','medium','low'), r.created_at DESC LIMIT 300"
);
$s->execute($args);
$rows = $s->fetchAll();

$counts  = ur_counts($pdo);
$total   = array_sum($counts);
$activeN = $total - (int)($counts['resolved'] ?? 0);
$labs    = $pdo->query('SELECT code, name FROM laboratories ORDER BY id')->fetchAll();

function ur_url(array $over): string
{
    global $statusF, $aktif, $catF, $labF, $q;
    $p = array_merge(['status' => $statusF, 'aktif' => $aktif ? 1 : '', 'kategori' => $catF, 'lab' => $labF, 'q' => $q], $over);
    $p = array_filter($p, fn($v) => $v !== '' && $v !== null);
    return 'laporan_masuk.php' . ($p ? '?' . http_build_query($p) : '');
}
$filtered = $statusF !== '' || $aktif || $catF !== '' || $labF !== '' || $q !== '';

ui_open('Laporan Masuk', 'laporan_masuk', '', ['tiket.css', 'laporan_user.css'], '<h1>Laporan Masuk</h1>');
?>
            <div class="page-head">
                <div>
                    <h1>Laporan Masuk</h1>
                    <p><?= (int)($counts['new'] ?? 0) ?> laporan baru &middot; <?= $activeN ?> belum selesai &middot; dikirim oleh user dan diterima Koordinator &amp; Admin.</p>
                </div>
                <div class="actions">
                    <a class="btn btn-secondary" href="tiket.php"><i data-lucide="wrench"></i> Tiket Perbaikan</a>
                </div>
            </div>

            <form class="toolbar" method="get" action="laporan_masuk.php">
                <?php if ($statusF): ?><input type="hidden" name="status" value="<?= e($statusF) ?>"><?php endif; ?>
                <?php if ($aktif): ?><input type="hidden" name="aktif" value="1"><?php endif; ?>
                <div class="input-icon search"><i data-lucide="search"></i>
                    <input type="search" class="input" name="q" value="<?= e($q) ?>" placeholder="Cari judul, isi, atau nama pelapor…" autocomplete="off"></div>
                <select class="select" name="lab" onchange="this.form.submit()" aria-label="Filter lab">
                    <option value="">Semua lab</option>
                    <?php foreach ($labs as $l): ?><option value="<?= e($l['code']) ?>"<?= $labF === $l['code'] ? ' selected' : '' ?>><?= e($l['name']) ?></option><?php endforeach; ?>
                </select>
                <select class="select" name="kategori" onchange="this.form.submit()" aria-label="Filter jenis">
                    <option value="">Semua jenis</option>
                    <?php foreach (UR_CATEGORY as $k => [$l]): ?><option value="<?= e($k) ?>"<?= $catF === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary"><i data-lucide="search"></i> Cari</button>
                <?php if ($filtered): ?><a class="btn btn-ghost" href="laporan_masuk.php"><i data-lucide="x"></i> Hapus filter</a><?php endif; ?>
            </form>

            <div class="chips hist-chips" role="group" aria-label="Filter status laporan">
                <a class="chip<?= !$statusF && !$aktif ? ' active' : '' ?>" href="<?= e(ur_url(['status' => '', 'aktif' => ''])) ?>">Semua <span class="n"><?= $total ?></span></a>
                <a class="chip<?= $aktif && !$statusF ? ' active' : '' ?>" href="<?= e(ur_url(['status' => '', 'aktif' => 1])) ?>">Belum selesai <span class="n"><?= $activeN ?></span></a>
                <?php foreach (UR_STATUS as $k => [$l]): ?>
                <a class="chip<?= $statusF === $k ? ' active' : '' ?>" href="<?= e(ur_url(['status' => $k, 'aktif' => ''])) ?>"><?= e($l) ?> <span class="n"><?= (int)($counts[$k] ?? 0) ?></span></a>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <div class="result-info"><?= count($rows) ?> laporan ditemukan<?= count($rows) >= 300 ? ' (menampilkan 300 teratas)' : '' ?></div>
                <div class="table-wrap">
                    <table class="table tk-table ur-table">
                        <thead><tr><th>#</th><th>Lokasi</th><th>Laporan</th><th>Urgensi</th><th>Status</th><th>Pelapor</th><th>Dikirim</th></tr></thead>
                        <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr class="row-link<?= $r['status'] === 'new' ? ' ur-unread' : '' ?>" data-href="laporan_masuk.php?id=<?= (int)$r['id'] ?>">
                                <td class="nowrap"><a class="link" href="laporan_masuk.php?id=<?= (int)$r['id'] ?>"><strong>#<?= (int)$r['id'] ?></strong></a></td>
                                <td class="nowrap"><?= e($r['lab_name']) ?><?= $r['pc_number'] ? ' &middot; PC ' . (int)$r['pc_number'] : '' ?></td>
                                <td><span class="ur-subject"><?= e($r['subject']) ?></span><br><?= ur_category_badge($r['category']) ?><?= $r['ticket_id'] ? ' <span class="tk-badge tk-gray">Tiket #' . (int)$r['ticket_id'] . '</span>' : '' ?></td>
                                <td><?= ticket_prio_badge($r['priority']) ?></td>
                                <td><?= ur_status_badge($r['status']) ?></td>
                                <td class="nowrap"><?= e(ur_reporter($r)) ?></td>
                                <td class="nowrap"><?= e(ui_date($r['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (!$rows): ?>
                    <div class="empty"><i data-lucide="<?= $filtered ? 'search-x' : 'inbox' ?>"></i>
                        <h3><?= $filtered ? 'Tidak ada laporan yang cocok' : 'Belum ada laporan masuk' ?></h3>
                        <p><?= $filtered ? 'Coba ubah filter.' : 'Laporan yang dikirim akun User akan muncul di sini.' ?></p></div>
                    <?php endif; ?>
                </div>
            </div>
<?php
ui_end_content();
ui_scripts('', ['laporan_user.js']);

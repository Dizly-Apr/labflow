<?php
/**
 * Laporan Saya (role User):  laporan_saya.php  (daftar + kirim laporan)  |  laporan_saya.php?id=5  (detail & tanggapan)
 * User mengirim laporan ke Koordinator & Admin dan memantau statusnya. Hanya laporan milik sendiri yang terlihat.
 */
require_once __DIR__ . '/config/layout.php';
require_once __DIR__ . '/config/user_reports.php';
require_login();
if (!can_report()) {
    // Koordinator & Admin menerima laporan, bukan mengirim -> arahkan ke kotak masuk
    header('Location: laporan_masuk.php' . (isset($_GET['id']) ? '?id=' . (int)$_GET['id'] : ''));
    exit;
}

$pdo = db();
$uid = (int)$_SESSION['user_id'];

/* ======================= DETAIL ======================= */
if (isset($_GET['id'])) {
    $r = ur_get($pdo, (int)$_GET['id']);
    if (!$r || (int)$r['reported_by'] !== $uid) { ur_not_found('laporan_saya.php', 'Kembali ke Laporan Saya'); }

    if (!$r['reporter_seen']) {     // pelapor sudah melihat pembaruan
        $pdo->prepare('UPDATE user_reports SET reporter_seen = 1, updated_at = updated_at WHERE id = ?')->execute([$r['id']]);
    }
    $events = ur_events($pdo, (int)$r['id']);
    $place  = $r['lab_name'] . ($r['pc_number'] ? ' · PC ' . (int)$r['pc_number'] : ' · seluruh lab');

    ui_open('Laporan #' . $r['id'], 'laporan_saya', '', ['tiket.css', 'laporan_user.css'],
        '<a class="icon-circle" href="laporan_saya.php" aria-label="Kembali ke Laporan Saya"><i data-lucide="arrow-left"></i></a><h1>Laporan #' . (int)$r['id'] . '</h1>');
    ?>
            <div class="page-head">
                <div>
                    <div class="crumbs"><a href="laporan_saya.php">Laporan Saya</a><i data-lucide="chevron-right"></i><span>#<?= (int)$r['id'] ?></span></div>
                    <h1><?= e($r['subject']) ?></h1>
                    <p><?= e($place) ?></p>
                </div>
            </div>

            <div class="tk-grid">
                <div class="card">
                    <div class="card-head"><h2><i data-lucide="info"></i> Laporan</h2><?= ur_status_badge($r['status']) ?></div>
                    <dl class="kv">
                        <div><dt>Jenis</dt><dd><?= ur_category_badge($r['category']) ?></dd></div>
                        <div><dt>Urgensi</dt><dd><?= ticket_prio_badge($r['priority']) ?></dd></div>
                        <div><dt>Dikirim</dt><dd><?= e(ui_date($r['created_at'], true)) ?> <span class="muted">&middot; kepada Koordinator &amp; Admin</span></dd></div>
                        <?php if ($r['handler_name']): ?><div><dt>Ditangani oleh</dt><dd><?= e($r['handler_name']) ?> <span class="muted">&middot; <?= e(ui_date($r['handled_at'], true)) ?></span></dd></div><?php endif; ?>
                        <?php if ($r['resolved_at']): ?><div><dt>Selesai</dt><dd><?= e(ui_date($r['resolved_at'], true)) ?></dd></div><?php endif; ?>
                    </dl>
                    <div class="ur-msg"><?= nl2br(e($r['message'])) ?></div>

                    <?php if ($r['response_note']): ?>
                    <div class="ur-reply"><i data-lucide="message-circle-reply"></i>
                        <div><strong>Tanggapan petugas</strong><p><?= nl2br(e($r['response_note'])) ?></p></div></div>
                    <?php else: ?>
                    <div class="note note-info" style="margin-top:16px"><i data-lucide="clock"></i><div>Belum ada tanggapan. Anda akan melihat pemberitahuan di lonceng dan menu <strong>Laporan Saya</strong> saat petugas menanggapi.</div></div>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="card-head"><h2><i data-lucide="history"></i> Perkembangan</h2></div>
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
$where = 'r.reported_by = ?'; $args = [$uid];
if ($statusF !== '') { $where .= ' AND r.status = ?'; $args[] = $statusF; }
$s = $pdo->prepare(
    "SELECT r.*, l.name AS lab_name, p.pc_number
     FROM user_reports r JOIN laboratories l ON l.id = r.lab_id LEFT JOIN pcs p ON p.id = r.pc_id
     WHERE $where ORDER BY r.created_at DESC, r.id DESC LIMIT 200"
);
$s->execute($args);
$rows   = $s->fetchAll();
$counts = ur_counts($pdo, $uid);
$total  = array_sum($counts);

ui_open('Laporan Saya', 'laporan_saya', '', ['tiket.css', 'laporan_user.css'], '<h1>Laporan Saya</h1>');
?>
            <div class="page-head">
                <div>
                    <h1>Laporan Saya</h1>
                    <p>Kirim laporan tentang laboratorium kepada Koordinator &amp; Admin, lalu pantau tanggapannya di sini.</p>
                </div>
                <div class="actions">
                    <button type="button" class="btn btn-primary" data-ur-open><i data-lucide="send"></i> Kirim Laporan</button>
                </div>
            </div>

            <div class="chips hist-chips" role="group" aria-label="Filter status laporan">
                <a class="chip<?= !$statusF ? ' active' : '' ?>" href="laporan_saya.php">Semua <span class="n"><?= $total ?></span></a>
                <?php foreach (UR_STATUS as $k => [$l]): ?>
                <a class="chip<?= $statusF === $k ? ' active' : '' ?>" href="laporan_saya.php?status=<?= $k ?>"><?= e($l) ?> <span class="n"><?= (int)($counts[$k] ?? 0) ?></span></a>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <div class="table-wrap">
                    <table class="table tk-table ur-table">
                        <thead><tr><th>#</th><th>Lokasi</th><th>Laporan</th><th>Status</th><th>Tanggapan</th><th>Dikirim</th></tr></thead>
                        <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr class="row-link<?= !$r['reporter_seen'] ? ' ur-unread' : '' ?>" data-href="laporan_saya.php?id=<?= (int)$r['id'] ?>">
                                <td class="nowrap"><a class="link" href="laporan_saya.php?id=<?= (int)$r['id'] ?>"><strong>#<?= (int)$r['id'] ?></strong></a></td>
                                <td class="nowrap"><?= e($r['lab_name']) ?><?= $r['pc_number'] ? ' &middot; PC ' . (int)$r['pc_number'] : '' ?></td>
                                <td><span class="ur-subject"><?= e($r['subject']) ?></span><br><?= ur_category_badge($r['category']) ?></td>
                                <td><?= ur_status_badge($r['status']) ?><?= !$r['reporter_seen'] ? ' <span class="ur-dot" title="Ada pembaruan"></span>' : '' ?></td>
                                <td><?= $r['response_note'] ? e(mb_strimwidth($r['response_note'], 0, 80, '…')) : '<span class="muted">&mdash;</span>' ?></td>
                                <td class="nowrap"><?= e(ui_date($r['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (!$rows): ?>
                    <div class="empty"><i data-lucide="send"></i>
                        <h3><?= $statusF ? 'Tidak ada laporan dengan status ini' : 'Belum ada laporan' ?></h3>
                        <p><?= $statusF ? 'Coba pilih status lain.' : 'Ada kerusakan, kendala, atau saran untuk laboratorium? Kirim laporan ke Koordinator & Admin.' ?></p>
                        <?php if (!$statusF): ?><button type="button" class="btn btn-primary btn-sm" data-ur-open style="margin-top:14px"><i data-lucide="send"></i> Kirim Laporan</button><?php endif; ?></div>
                    <?php endif; ?>
                </div>
            </div>
<?php
ui_end_content();
ur_modal('', 0, !empty($_GET['baru']));
ui_scripts('', ['laporan_user.js']);

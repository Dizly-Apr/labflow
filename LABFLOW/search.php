<?php
/** Pencarian global: komponen & laporan PC di semua lab, aset, tiket, stok, lab, dan riwayat. */
require_once __DIR__ . '/config/layout.php';
require_once __DIR__ . '/config/tickets.php';
require_login();

$q   = trim((string)($_GET['q'] ?? ''));
$pdo = db();
$ok  = mb_strlen($q) >= 2;
$like = '%' . addcslashes($q, '%_\\') . '%';

/** Tebalkan kata yang dicari (aman dari XSS: escape dulu, baru sorot). */
function hl($text, string $q): string
{
    $t = e($text);
    if ($q === '') { return $t; }
    return preg_replace('/(' . preg_quote(e($q), '/') . ')/iu', '<mark class="hl">$1</mark>', $t) ?? $t;
}

$res = ['comp' => [], 'pc' => [], 'tiket' => [], 'stok' => [], 'lab' => [], 'log' => []];
if ($ok) {
    $issueWord = in_array(mb_strtolower($q), ['issue', 'bermasalah', 'rusak'], true);
    $s = $pdo->prepare(
        "SELECT l.code, l.name AS lab, p.pc_number, c.component_type, c.specification, c.status, c.report
         FROM pc_components c JOIN pcs p ON p.id = c.pc_id JOIN laboratories l ON l.id = p.laboratory_id
         WHERE c.specification LIKE ? OR c.report LIKE ? OR CONCAT(l.name, ' PC ', p.pc_number) LIKE ?" . ($issueWord ? " OR c.status = 'issue'" : '') . "
         ORDER BY (c.status = 'issue') DESC, l.id, p.pc_number LIMIT 40"
    );
    $s->execute([$like, $like, $like]);
    $res['comp'] = $s->fetchAll();

    $s = $pdo->prepare(
        "SELECT l.code, l.name AS lab, p.pc_number, p.asset_tag, p.supplier, p.warranty_until
         FROM pcs p JOIN laboratories l ON l.id = p.laboratory_id
         WHERE p.asset_tag LIKE ? OR p.supplier LIKE ? OR p.asset_notes LIKE ? ORDER BY l.id, p.pc_number LIMIT 25"
    );
    $s->execute([$like, $like, $like]);
    $res['pc'] = $s->fetchAll();

    $s = $pdo->prepare(
        "SELECT r.id, r.description, r.status, r.priority, r.technician, r.reporter_name, l.name AS lab, p.pc_number
         FROM lab_reports r JOIN pcs p ON p.id = r.pc_id JOIN laboratories l ON l.id = p.laboratory_id
         WHERE r.description LIKE ? OR r.technician LIKE ? OR r.reporter_name LIKE ? OR r.resolution_note LIKE ? OR CONCAT('#', r.id) = ?
         ORDER BY (r.status = 'resolved'), r.reported_at DESC LIMIT 25"
    );
    $s->execute([$like, $like, $like, $like, $q]);
    $res['tiket'] = $s->fetchAll();

    $s = $pdo->prepare(
        "SELECT i.id, i.name, i.description, i.quantity, i.min_quantity, c.name AS cat
         FROM stock_items i JOIN stock_categories c ON c.id = i.category_id
         WHERE i.name LIKE ? OR i.description LIKE ? OR c.name LIKE ? ORDER BY i.name LIMIT 25"
    );
    $s->execute([$like, $like, $like]);
    $res['stok'] = $s->fetchAll();

    $s = $pdo->prepare('SELECT code, name, floor, status FROM laboratories WHERE name LIKE ? OR floor LIKE ? OR code LIKE ? ORDER BY id');
    $s->execute([$like, $like, $like]);
    $res['lab'] = $s->fetchAll();

    $s = $pdo->prepare('SELECT title, description, created_at FROM activity_logs WHERE type <> "login" AND (title LIKE ? OR description LIKE ?) ORDER BY created_at DESC, id DESC LIMIT 10');
    $s->execute([$like, $like]);
    $res['log'] = $s->fetchAll();
}
$total = array_sum(array_map('count', $res));

ui_open('Pencarian', 'search', '', ['tiket.css'], '<h1>Pencarian</h1>');
?>
            <div class="page-head">
                <div>
                    <h1>Pencarian</h1>
                    <p><?= $ok ? '<strong>' . $total . '</strong> hasil untuk &ldquo;' . e($q) . '&rdquo; di semua lab, tiket, stok, dan riwayat.' : 'Ketik minimal 2 karakter, mis. &ldquo;VGA Missing&rdquo;, &ldquo;RAM 8GB&rdquo;, &ldquo;INV-RPL&rdquo;.' ?></p>
                </div>
            </div>

            <form class="toolbar" method="get" action="search.php">
                <div class="input-icon search"><i data-lucide="search"></i>
                    <input type="search" class="input" name="q" value="<?= e($q) ?>" placeholder="Cari komponen, laporan, tiket, barang…" autocomplete="off" autofocus></div>
                <button type="submit" class="btn btn-primary"><i data-lucide="search"></i> Cari</button>
            </form>

<?php if ($ok && !$total): ?>
            <div class="card empty"><i data-lucide="search-x"></i><h3>Tidak ada hasil</h3><p>Coba kata kunci lain yang lebih singkat.</p></div>
<?php endif; ?>

<?php if ($res['comp']): ?>
            <div class="card search-group">
                <div class="card-head"><h2><i data-lucide="cpu"></i> Komponen &amp; laporan PC <span class="n">(<?= count($res['comp']) ?>)</span></h2></div>
                <div class="table-wrap"><table class="table">
                    <thead><tr><th>PC</th><th>Komponen</th><th>Spesifikasi</th><th>Status</th><th>Laporan</th></tr></thead><tbody>
                    <?php foreach ($res['comp'] as $r): ?>
                        <tr class="row-link" data-href="<?= e(pc_url($r['code'], (int)$r['pc_number'])) ?>">
                            <td class="nowrap"><a class="link" href="<?= e(pc_url($r['code'], (int)$r['pc_number'])) ?>"><?= e($r['lab']) ?> &middot; PC <?= (int)$r['pc_number'] ?></a></td>
                            <td><?= e(component_label($r['component_type'])) ?></td>
                            <td><?= hl($r['specification'], $q) ?></td>
                            <td><span class="status-pill pill-<?= $r['status'] === 'issue' ? 'issue' : 'online' ?>"><?= $r['status'] === 'issue' ? 'ISSUE' : 'ONLINE' ?></span></td>
                            <td class="soft"><?= hl($r['report'], $q) ?></td>
                        </tr>
                    <?php endforeach; ?></tbody></table></div>
            </div>
<?php endif; ?>

<?php if ($res['tiket']): ?>
            <div class="card search-group">
                <div class="card-head"><h2><i data-lucide="wrench"></i> Tiket perbaikan <span class="n">(<?= count($res['tiket']) ?>)</span></h2></div>
                <div class="table-wrap"><table class="table">
                    <thead><tr><th>#</th><th>PC</th><th>Masalah</th><th>Status</th><th>Teknisi</th></tr></thead><tbody>
                    <?php foreach ($res['tiket'] as $r): ?>
                        <tr class="row-link" data-href="tiket.php?id=<?= (int)$r['id'] ?>">
                            <td class="nowrap"><a class="link" href="tiket.php?id=<?= (int)$r['id'] ?>"><strong>#<?= (int)$r['id'] ?></strong></a></td>
                            <td class="nowrap"><?= e($r['lab']) ?> &middot; PC <?= (int)$r['pc_number'] ?></td>
                            <td><?= hl($r['description'], $q) ?></td>
                            <td><?= ticket_badge($r['status']) ?></td>
                            <td><?= $r['technician'] ? hl($r['technician'], $q) : '<span class="muted">&mdash;</span>' ?></td>
                        </tr>
                    <?php endforeach; ?></tbody></table></div>
            </div>
<?php endif; ?>

<?php if ($res['pc']): ?>
            <div class="card search-group">
                <div class="card-head"><h2><i data-lucide="badge-check"></i> Aset PC <span class="n">(<?= count($res['pc']) ?>)</span></h2></div>
                <div class="table-wrap"><table class="table">
                    <thead><tr><th>PC</th><th>No. Inventaris</th><th>Supplier</th><th>Garansi</th></tr></thead><tbody>
                    <?php foreach ($res['pc'] as $r): [$wt, $wc] = ui_warranty($r['warranty_until']); ?>
                        <tr class="row-link" data-href="<?= e(pc_url($r['code'], (int)$r['pc_number'])) ?>">
                            <td class="nowrap"><a class="link" href="<?= e(pc_url($r['code'], (int)$r['pc_number'])) ?>"><?= e($r['lab']) ?> &middot; PC <?= (int)$r['pc_number'] ?></a></td>
                            <td><?= hl($r['asset_tag'], $q) ?></td><td><?= hl($r['supplier'], $q) ?></td>
                            <td><span class="tk-badge tk-<?= e($wc) ?>"><?= e($wt) ?></span></td>
                        </tr>
                    <?php endforeach; ?></tbody></table></div>
            </div>
<?php endif; ?>

<?php if ($res['stok']): ?>
            <div class="card search-group">
                <div class="card-head"><h2><i data-lucide="archive"></i> Stok barang <span class="n">(<?= count($res['stok']) ?>)</span></h2></div>
                <div class="table-wrap"><table class="table">
                    <thead><tr><th>Barang</th><th>Kategori</th><th>Deskripsi</th><th>Stok</th></tr></thead><tbody>
                    <?php foreach ($res['stok'] as $r): $low = (int)$r['quantity'] <= (int)$r['min_quantity']; ?>
                        <tr class="row-link" data-href="stock_opname.php">
                            <td><a class="link" href="stock_opname.php"><?= hl($r['name'], $q) ?></a></td><td><?= hl($r['cat'], $q) ?></td>
                            <td class="soft"><?= hl($r['description'], $q) ?></td>
                            <td><strong><?= (int)$r['quantity'] ?></strong> <span class="badge <?= $low ? 'badge-yellow' : 'badge-green' ?>"><?= $low ? 'Menipis' : 'Aman' ?></span></td>
                        </tr>
                    <?php endforeach; ?></tbody></table></div>
            </div>
<?php endif; ?>

<?php if ($res['lab']): ?>
            <div class="card search-group">
                <div class="card-head"><h2><i data-lucide="monitor"></i> Laboratorium <span class="n">(<?= count($res['lab']) ?>)</span></h2></div>
                <div class="chips">
                    <?php foreach ($res['lab'] as $r): ?><a class="chip" href="lab.php?lab=<?= e(rawurlencode($r['code'])) ?>"><?= e($r['name']) ?> &middot; <?= e($r['floor']) ?></a><?php endforeach; ?>
                </div>
            </div>
<?php endif; ?>

<?php if ($res['log']): ?>
            <div class="card search-group">
                <div class="card-head"><h2><i data-lucide="history"></i> Riwayat aktivitas <span class="n">(<?= count($res['log']) ?>)</span></h2><a class="link-more" href="history.php?q=<?= e(rawurlencode($q)) ?>">Lihat semua <i data-lucide="arrow-right"></i></a></div>
                <div class="table-wrap"><table class="table"><tbody>
                    <?php foreach ($res['log'] as $r): ?>
                        <tr><td class="nowrap"><?= e(ui_date($r['created_at'], true)) ?></td><td><strong><?= hl($r['title'], $q) ?></strong></td><td class="soft"><?= hl($r['description'], $q) ?></td></tr>
                    <?php endforeach; ?></tbody></table></div>
            </div>
<?php endif; ?>
<?php
ui_end_content();
ui_scripts('', ['tiket.js']);

<?php
/**
 * Halaman satu PC:  pc.php?lab=rpl&no=7
 * Satu file untuk semua PC di semua lab (menggantikan PC_n.php yang diduplikasi).
 * Berisi: komponen, aset & garansi, tiket perbaikan, dan riwayat penggantian komponen.
 */
require_once __DIR__ . '/config/layout.php';
require_once __DIR__ . '/config/user_reports.php';
require_once __DIR__ . '/config/tickets.php';
require_login();

$base   = '';
$labCode = preg_replace('/[^a-z0-9_-]/i', '', (string)($_GET['lab'] ?? ''));
$pcNo   = (int)($_GET['no'] ?? 0);
$pc     = find_pc($labCode, $pcNo);

if (!$pc) {
    http_response_code(404);
    echo '<!DOCTYPE html><meta charset="utf-8"><title>404</title><body style="font-family:sans-serif;text-align:center;padding:80px">'
       . '<h1>404 - PC tidak ditemukan</h1><p><a href="data_lab.php">Kembali ke Data Lab</a></p></body>';
    exit;
}

$pdo     = db();
$canEdit = can_edit();
$labUrl  = 'lab.php?lab=' . rawurlencode($pc['lab_code']);

$nb = $pdo->prepare('SELECT (SELECT MAX(pc_number) FROM pcs WHERE laboratory_id=? AND pc_number<?) AS prev,
                            (SELECT MIN(pc_number) FROM pcs WHERE laboratory_id=? AND pc_number>?) AS next');
$nb->execute([$pc['laboratory_id'], $pcNo, $pc['laboratory_id'], $pcNo]);
$nb   = $nb->fetch();
$prev = $nb['prev'] ? (int)$nb['prev'] : null;
$next = $nb['next'] ? (int)$nb['next'] : null;

$cs = $pdo->prepare('SELECT component_type AS type, specification AS spec, status, report AS `desc`, updated_at FROM pc_components WHERE pc_id=?');
$cs->execute([$pc['id']]);
$comps = [];
foreach ($cs->fetchAll() as $c) { $comps[$c['type']] = $c; }
$issues = 0;
foreach ($comps as $c) { if ($c['status'] === 'issue') { $issues++; } }

$ts = $pdo->prepare('SELECT * FROM lab_reports WHERE pc_id=? ORDER BY reported_at DESC, id DESC LIMIT 15');
$ts->execute([$pc['id']]);
$tickets = $ts->fetchAll();

$hs = $pdo->prepare('SELECT h.*, u.full_name FROM component_history h LEFT JOIN users u ON u.id=h.user_id
                     WHERE h.pc_id=? ORDER BY h.created_at DESC, h.id DESC LIMIT 30');
$hs->execute([$pc['id']]);
$history = $hs->fetchAll();

[$warnText, $warnColor] = ui_warranty($pc['warranty_until']);
$types   = component_types();
$js = [
    'lab' => $pc['lab_code'], 'pc' => $pcNo, 'canEdit' => $canEdit,
    'catalog' => component_catalog_json(),
    'components' => array_map(fn($c) => ['spec' => $c['spec'], 'status' => $c['status'], 'desc' => $c['desc']], $comps),
    'asset' => ['asset_tag' => $pc['asset_tag'], 'purchase_date' => $pc['purchase_date'], 'warranty_until' => $pc['warranty_until'],
                'supplier' => $pc['supplier'], 'notes' => $pc['asset_notes']],
];

$left = '<a class="icon-circle" href="' . e($labUrl) . '" title="Kembali ke ' . e($pc['lab_name']) . '" aria-label="Kembali ke ' . e($pc['lab_name']) . '"><i data-lucide="arrow-left"></i></a>'
      . '<h1>PC ' . $pcNo . ' <span class="h1-sub">&middot; ' . e($pc['lab_name']) . '</span></h1>';

ui_open('PC ' . $pcNo . ' - ' . $pc['lab_name'], 'lab', $base, ['pc.css', 'tiket.css', 'laporan_user.css'], $left);
?>

            <div class="page-head">
                <div>
                    <div class="crumbs">
                        <a href="data_lab.php">Data Lab</a><i data-lucide="chevron-right"></i>
                        <a href="<?= e($labUrl) ?>"><?= e($pc['lab_name']) ?></a><i data-lucide="chevron-right"></i>
                        <span>PC <?= $pcNo ?></span>
                    </div>
                    <h1>Komponen PC <?= $pcNo ?> <small class="floor-label"><?= e($pc['lab_name']) ?></small></h1>
                    <p>Spesifikasi, aset &amp; garansi, tiket, dan riwayat perangkat.</p>
                </div>
                <div class="actions">
                    <?php if (can_report()): ?>
                    <button type="button" class="btn btn-primary btn-sm" data-ur-open data-lab="<?= e($pc['lab_code']) ?>" data-pc="<?= $pcNo ?>"><i data-lucide="send"></i> Kirim Laporan</button>
                    <?php endif; ?>
                    <button type="button" class="btn btn-secondary btn-sm" id="btnQr"><i data-lucide="qr-code"></i> QR</button>
                    <a class="btn btn-secondary btn-sm<?= $prev ? '' : ' disabled' ?>" <?= $prev ? 'href="' . e(pc_url($labCode, $prev)) . '"' : 'aria-disabled="true"' ?>><i data-lucide="chevron-left"></i> PC <?= $prev ?? '-' ?></a>
                    <a class="btn btn-secondary btn-sm<?= $next ? '' : ' disabled' ?>" <?= $next ? 'href="' . e(pc_url($labCode, $next)) . '"' : 'aria-disabled="true"' ?>>PC <?= $next ?? '-' ?> <i data-lucide="chevron-right"></i></a>
                </div>
            </div>

            <div class="card">
                <div class="pc-head">
                    <div class="status-summary">
                        <strong>Status PC:</strong>
                        <?php if ($issues): ?><span class="status-chip bad"><span class="dot"></span>Issue &middot; <?= $issues ?> komponen</span>
                        <?php else: ?><span class="status-chip ok"><span class="dot"></span>Normal</span><?php endif; ?>
                    </div>
                    <?php if ($canEdit): ?>
                    <div class="actions">
                        <button type="button" id="btnAddComp" class="btn btn-secondary"><i data-lucide="plus"></i> Tambah Komponen</button>
                        <button type="button" id="btnTicket" class="btn btn-warning"><i data-lucide="wrench"></i> Buat Tiket</button>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="table-wrap">
                    <table class="table hw-table">
                        <thead>
                            <tr><th>Komponen</th><th>Spesifikasi</th><th>Status</th><th>Laporan</th><?php if ($canEdit): ?><th style="text-align:right">Aksi</th><?php endif; ?></tr>
                        </thead>
                        <tbody id="hwBody">
                        <?php foreach (component_order() as $t): if (!isset($comps[$t])) continue; $c = $comps[$t]; ?>
                            <tr class="<?= $c['status'] === 'issue' ? 'is-issue' : '' ?>">
                                <td><i data-lucide="<?= e($types[$t][1]) ?>"></i><?= e($types[$t][0]) ?></td>
                                <td><?= e($c['spec']) ?></td>
                                <td><span class="status-pill pill-<?= $c['status'] === 'issue' ? 'issue' : 'online' ?>"><?= $c['status'] === 'issue' ? 'ISSUE' : 'ONLINE' ?></span></td>
                                <td class="report"><?= e($c['desc']) ?></td>
                                <?php if ($canEdit): ?><td><div style="display:flex;justify-content:flex-end"><button type="button" class="icon-btn" title="Edit <?= e($types[$t][0]) ?>" aria-label="Edit <?= e($types[$t][0]) ?>" data-edit="<?= e($t) ?>"><i data-lucide="pencil"></i></button></div></td><?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (!$comps): ?><div class="empty"><i data-lucide="cpu"></i><h3>Belum ada komponen</h3><p>Tambahkan komponen PC ini.</p></div><?php endif; ?>
                </div>
            </div>

            <div class="pc-cols">
                <!-- ============ ASET & GARANSI ============ -->
                <div class="card">
                    <div class="card-head">
                        <h2><i data-lucide="badge-check"></i> Aset &amp; Garansi</h2>
                        <?php if ($canEdit): ?><button type="button" class="btn btn-secondary btn-sm" id="btnAsset"><i data-lucide="pencil"></i> Edit</button><?php endif; ?>
                    </div>
                    <dl class="kv">
                        <div><dt>No. Inventaris</dt><dd><?= $pc['asset_tag'] ? '<strong>' . e($pc['asset_tag']) . '</strong>' : '<span class="muted">Belum diisi</span>' ?></dd></div>
                        <div><dt>Tanggal Beli</dt><dd><?= $pc['purchase_date'] ? e(ui_date($pc['purchase_date'])) : '<span class="muted">Belum diisi</span>' ?></dd></div>
                        <div><dt>Garansi</dt><dd><span class="tk-badge tk-<?= e($warnColor) ?>"><?= e($warnText) ?></span></dd></div>
                        <div><dt>Supplier</dt><dd><?= $pc['supplier'] ? e($pc['supplier']) : '<span class="muted">&mdash;</span>' ?></dd></div>
                        <div><dt>Catatan</dt><dd><?= $pc['asset_notes'] ? e($pc['asset_notes']) : '<span class="muted">&mdash;</span>' ?></dd></div>
                    </dl>
                </div>

                <!-- ============ RIWAYAT PENGGANTIAN ============ -->
                <div class="card">
                    <div class="card-head"><h2><i data-lucide="history"></i> Riwayat Komponen</h2></div>
                    <?php if ($history): ?>
                    <ul class="timeline">
                        <?php foreach ($history as $h): ?>
                        <li>
                            <span class="tl-dot tl-<?= e($h['action']) ?>"></span>
                            <div>
                                <strong><?= e(component_label($h['component_type'])) ?> &middot; <?= ['add' => 'Ditambahkan', 'replace' => 'Diganti', 'remove' => 'Dilepas'][$h['action']] ?></strong>
                                <div class="tl-text">
                                    <?php if ($h['old_spec']): ?><s><?= e($h['old_spec']) ?></s><?php endif; ?>
                                    <?php if ($h['old_spec'] && $h['new_spec']): ?> → <?php endif; ?>
                                    <?php if ($h['new_spec']): ?><?= e($h['new_spec']) ?><?php endif; ?>
                                </div>
                                <?php if ($h['reason']): ?><div class="tl-text soft">Alasan: <?= e($h['reason']) ?></div><?php endif; ?>
                                <div class="tl-meta"><?= e(ui_date($h['created_at'], true)) ?> &bull; <?= e($h['full_name'] ?? '-') ?></div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <div class="empty small"><i data-lucide="history"></i><p>Belum ada penggantian komponen tercatat.</p></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ============ TIKET PC INI ============ -->
            <div class="card" style="margin-top:20px">
                <div class="card-head">
                    <h2><i data-lucide="wrench"></i> Tiket Perbaikan PC Ini</h2>
                    <a class="link-more" href="tiket.php?q=<?= e(rawurlencode($pc['lab_name'] . ' PC ' . $pcNo)) ?>">Lihat semua <i data-lucide="arrow-right"></i></a>
                </div>
                <?php if ($tickets): ?>
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Tiket</th><th>Masalah</th><th>Prioritas</th><th>Status</th><th>Teknisi</th><th>Dilaporkan</th></tr></thead>
                        <tbody>
                        <?php foreach ($tickets as $t): ?>
                            <tr>
                                <td class="nowrap"><a class="link" href="tiket.php?id=<?= (int)$t['id'] ?>"><strong>#<?= (int)$t['id'] ?></strong></a></td>
                                <td><?= e($t['description']) ?><?= $t['component_type'] ? ' <span class="muted">· ' . e(component_label($t['component_type'])) . '</span>' : '' ?></td>
                                <td><?= ticket_prio_badge($t['priority']) ?></td>
                                <td><?= ticket_badge($t['status']) ?></td>
                                <td><?= $t['technician'] ? e($t['technician']) : '<span class="muted">&mdash;</span>' ?></td>
                                <td class="nowrap"><?= e(ui_date($t['reported_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty small"><i data-lucide="circle-check"></i><p>PC ini belum pernah punya tiket perbaikan.</p></div>
                <?php endif; ?>
            </div>

<?php ui_end_content(); ?>

<!-- ============ MODAL QR ============ -->
<div class="modal-backdrop" id="qrModal" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="qrTitle" style="max-width:380px;text-align:center">
        <div class="modal-head">
            <div><h3 id="qrTitle">QR PC <?= $pcNo ?></h3><p><?= e($pc['lab_name']) ?></p></div>
            <button type="button" class="modal-close" data-modal-close aria-label="Tutup"><i data-lucide="x"></i></button>
        </div>
        <div id="qrBox" class="qr-box"></div>
        <p class="soft" style="font-size:12px;margin-top:10px;word-break:break-all" id="qrUrl"></p>
        <div class="modal-foot" style="justify-content:center">
            <a class="btn btn-secondary" href="qr.php?lab=<?= e(rawurlencode($pc['lab_code'])) ?>"><i data-lucide="printer"></i> Cetak stiker satu lab</a>
        </div>
    </div>
</div>

<?php if ($canEdit): ?>
<!-- ============ MODAL EDIT / TAMBAH KOMPONEN ============ -->
<div class="modal-backdrop" id="hwModal" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="hwTitle">
        <div class="modal-head">
            <div><h3 id="hwTitle">Edit Komponen</h3><p>PC <?= $pcNo ?> &middot; <?= e($pc['lab_name']) ?></p></div>
            <button type="button" class="modal-close" data-modal-close aria-label="Tutup"><i data-lucide="x"></i></button>
        </div>
        <form id="editForm" autocomplete="off">
            <div class="field">
                <label for="compSelect">Komponen</label>
                <select class="select" id="compSelect">
                    <?php foreach ($types as $k => $t): ?><option value="<?= e($k) ?>"><?= e($t[0]) ?></option><?php endforeach; ?>
                </select>
                <span class="hint" id="compNew" hidden>Komponen ini belum ada di PC &mdash; akan ditambahkan.</span>
            </div>

            <div class="field">
                <span class="label">Status</span>
                <div class="status-pick">
                    <label class="pick pick-ok"><input type="radio" name="hwStatus" value="online" checked><span><i data-lucide="circle-check"></i> ONLINE</span></label>
                    <label class="pick pick-bad"><input type="radio" name="hwStatus" value="issue"><span><i data-lucide="triangle-alert"></i> ISSUE</span></label>
                </div>
            </div>

            <div class="field">
                <label for="specInput">Spesifikasi</label>
                <input type="text" class="input" id="specInput" required maxlength="250">
            </div>

            <div class="field" id="reasonField" hidden>
                <label for="reasonInput">Alasan penggantian <span class="hint">(spesifikasi berubah &rarr; dicatat di riwayat)</span></label>
                <input type="text" class="input" id="reasonInput" maxlength="250" placeholder="Contoh: rusak, upgrade, pindahan dari PC lain">
            </div>

            <div class="field">
                <label for="descInput">Laporan <span class="hint">(kosong = "Normal" bila ONLINE)</span></label>
                <textarea class="textarea" id="descInput" rows="3" placeholder="Contoh: VGA tidak terdeteksi"></textarea>
            </div>

            <div class="field" id="prioField" hidden>
                <label for="prioInput">Prioritas tiket <span class="hint">(tiket dibuat otomatis untuk status ISSUE)</span></label>
                <select class="select" id="prioInput">
                    <?php foreach (TICKET_PRIORITY as $k => [$l]): ?><option value="<?= $k ?>"<?= $k === 'medium' ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
                </select>
            </div>

            <div class="modal-foot split">
                <button type="button" class="btn btn-ghost danger-text" id="btnDelComp"><i data-lucide="trash-2"></i> Hapus</button>
                <span class="spacer"></span>
                <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
                <button type="submit" class="btn btn-primary" id="btnSaveHw"><i data-lucide="save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- ============ MODAL TIKET BARU ============ -->
<div class="modal-backdrop" id="tkModal" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="tkTitle">
        <div class="modal-head">
            <div><h3 id="tkTitle">Buat Tiket Perbaikan</h3><p>PC <?= $pcNo ?> &middot; <?= e($pc['lab_name']) ?></p></div>
            <button type="button" class="modal-close" data-modal-close aria-label="Tutup"><i data-lucide="x"></i></button>
        </div>
        <form id="tkForm" autocomplete="off">
            <div class="field">
                <label for="tkType">Komponen terkait <span class="hint">(opsional)</span></label>
                <select class="select" id="tkType">
                    <option value="">Tidak spesifik / lainnya</option>
                    <?php foreach ($types as $k => $t): ?><option value="<?= e($k) ?>"><?= e($t[0]) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="tkDesc">Masalah</label>
                <textarea class="textarea" id="tkDesc" rows="3" required maxlength="255" placeholder="Contoh: layar berkedip lalu mati"></textarea>
            </div>
            <div class="field">
                <label for="tkPrio">Prioritas</label>
                <select class="select" id="tkPrio">
                    <?php foreach (TICKET_PRIORITY as $k => [$l]): ?><option value="<?= $k ?>"<?= $k === 'medium' ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
                <button type="submit" class="btn btn-primary" id="btnSaveTk"><i data-lucide="send"></i> Buat Tiket</button>
            </div>
        </form>
    </div>
</div>

<!-- ============ MODAL ASET ============ -->
<div class="modal-backdrop" id="assetModal" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="assetTitle">
        <div class="modal-head">
            <div><h3 id="assetTitle">Aset &amp; Garansi</h3><p>PC <?= $pcNo ?> &middot; <?= e($pc['lab_name']) ?></p></div>
            <button type="button" class="modal-close" data-modal-close aria-label="Tutup"><i data-lucide="x"></i></button>
        </div>
        <form id="assetForm" autocomplete="off">
            <div class="field"><label for="aTag">Nomor inventaris</label><input type="text" class="input" id="aTag" maxlength="50" placeholder="INV-RPL-07"></div>
            <div class="two-col">
                <div class="field"><label for="aBuy">Tanggal beli</label><input type="date" class="input" id="aBuy"></div>
                <div class="field"><label for="aWar">Garansi berakhir</label><input type="date" class="input" id="aWar"></div>
            </div>
            <div class="field"><label for="aSup">Supplier / toko</label><input type="text" class="input" id="aSup" maxlength="100"></div>
            <div class="field"><label for="aNote">Catatan</label><input type="text" class="input" id="aNote" maxlength="255"></div>
            <div class="modal-foot">
                <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
                <button type="submit" class="btn btn-primary" id="btnSaveAsset"><i data-lucide="save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if (can_report()) { ur_modal($pc['lab_code'], $pcNo); } ?>
<script>window.LF_PUBLIC_BASE = <?= json_encode(public_base_url()) ?>;</script>
<script>window.LF_PC = <?= json_encode($js, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<script src="<?= ui_asset('js/vendor/qrcode.js', $base) ?>"></script>
<?php ui_scripts($base, ['qr.js', 'pc.js', 'laporan_user.js']);

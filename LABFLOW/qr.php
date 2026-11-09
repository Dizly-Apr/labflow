<?php
/**
 * Cetak stiker QR untuk semua PC satu lab:  qr.php?lab=rpl
 * QR membuka lapor.php (form lapor publik). Pastikan "Alamat aplikasi" bisa dibuka dari HP
 * di jaringan sekolah (mis. http://192.168.1.10/labflow/), bukan "localhost".
 */
require_once __DIR__ . '/config/layout.php';
require_login();

$labs = db()->query('SELECT code, name FROM laboratories ORDER BY id')->fetchAll();
$code = preg_replace('/[^a-z0-9_-]/i', '', (string)($_GET['lab'] ?? ($labs[0]['code'] ?? '')));
$lab  = get_lab($code);
$pcs  = [];
if ($lab) {
    $s = db()->prepare('SELECT pc_number, asset_tag FROM pcs WHERE laboratory_id=? ORDER BY pc_number');
    $s->execute([$lab['id']]);
    $pcs = $s->fetchAll();
}

ui_open('Stiker QR', 'lab', '', ['tiket.css', 'qr_print.css'],
    '<a class="icon-circle" href="' . ($lab ? 'lab.php?lab=' . e($lab['code']) : 'data_lab.php') . '" aria-label="Kembali"><i data-lucide="arrow-left"></i></a><h1>Stiker QR</h1>');
?>
            <div class="page-head no-print">
                <div>
                    <div class="crumbs"><a href="data_lab.php">Data Lab</a><i data-lucide="chevron-right"></i><span>Stiker QR</span></div>
                    <h1>Stiker QR PC<?= $lab ? ' &middot; ' . e($lab['name']) : '' ?></h1>
                    <p>Tempel di setiap PC. Siswa / guru scan untuk melapor kerusakan; petugas scan untuk membuka halaman PC.</p>
                </div>
                <div class="actions"><button type="button" class="btn btn-primary" onclick="window.print()"><i data-lucide="printer"></i> Cetak</button></div>
            </div>

            <div class="card no-print qr-settings">
                <div class="two-col">
                    <div class="field">
                        <label for="qrLab">Laboratorium</label>
                        <select class="select" id="qrLab" onchange="location.href='qr.php?lab='+this.value">
                            <?php foreach ($labs as $l): ?><option value="<?= e($l['code']) ?>"<?= $l['code'] === $code ? ' selected' : '' ?>><?= e($l['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="qrBase">Alamat aplikasi <span class="hint">(yang bisa dibuka dari HP)</span></label>
                        <input type="url" class="input" id="qrBase" placeholder="http://192.168.1.10/labflow/">
                    </div>
                </div>
                <p class="soft" id="qrHint" style="margin-top:10px;font-size:13px"></p>
            </div>

<?php if (!$lab): ?>
            <div class="card empty"><i data-lucide="search-x"></i><h3>Lab tidak ditemukan</h3></div>
<?php else: ?>
            <div class="qr-sheet" id="qrSheet">
                <?php foreach ($pcs as $p): ?>
                <div class="qr-sticker" data-no="<?= (int)$p['pc_number'] ?>">
                    <div class="qr-img"></div>
                    <div class="qr-meta">
                        <strong><?= e($lab['name']) ?></strong>
                        <span class="qr-pc">PC <?= (int)$p['pc_number'] ?></span>
                        <?php if ($p['asset_tag']): ?><small><?= e($p['asset_tag']) ?></small><?php endif; ?>
                        <small class="qr-cta">Scan untuk lapor kerusakan</small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
<?php endif; ?>
<?php
ui_end_content();
echo '<script>window.LF_PUBLIC_BASE = ' . json_encode(public_base_url()) . '; window.LF_QR_LAB = ' . json_encode($code) . ';</script>' . "\n";
echo '<script src="' . ui_asset('js/vendor/qrcode.js') . '"></script>' . "\n";
ui_scripts('', ['qr.js', 'qr_page.js']);

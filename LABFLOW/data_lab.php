<?php
require_once __DIR__ . '/config/layout.php';
require_login();

$labs = [];
foreach (db()->query('SELECT * FROM v_lab_overview ORDER BY id') as $l) {
    $labs[] = [
        'id'     => (int)$l['id'],
        'name'   => $l['name'],
        'status' => $l['status'],
        'statusText' => $l['status'] === 'maintenance' ? 'Maintenance' : 'Active',
        'floor'  => $l['floor'],
        'image'  => preg_match('/^lab\d+_[a-f0-9]+\.(jpg|png|webp)$/', (string)$l['image']) ? 'uploads/labs/' . $l['image'] : '',
        'link'   => 'lab.php?lab=' . rawurlencode($l['code']),
        'pcs'    => (int)$l['total_pcs'],
        'issue'  => (int)$l['pcs_issue'],
    ];
}
$cnt = ['all' => count($labs), 'active' => 0, 'maintenance' => 0];
foreach ($labs as $l) { $cnt[$l['status']]++; }
$initial = in_array($_GET['status'] ?? '', ['active', 'maintenance'], true) ? $_GET['status'] : 'all';

ui_open('Data Lab', 'lab', '', ['data_lab.css'], '<h1>Data Lab</h1>');
?>

            <div class="page-head">
                <div>
                    <h1>Daftar Laboratorium</h1>
                    <p>Pilih laboratorium untuk melihat denah dan kondisi PC.</p>
                </div>
                <div class="actions">
                    <?php if (can_edit()): ?><button type="button" class="btn btn-primary" id="btnNewLab"><i data-lucide="plus"></i> Tambah Lab</button><?php endif; ?>
                    <a class="btn btn-secondary" href="history.php"><i data-lucide="history"></i> Riwayat</a>
                </div>
            </div>

            <div class="toolbar">
                <div class="input-icon search">
                    <i data-lucide="search"></i>
                    <input type="search" class="input" id="searchInput" placeholder="Cari laboratorium atau lantai..." autocomplete="off">
                </div>
                <div class="chips" id="statusChips" role="group" aria-label="Filter status">
                    <button type="button" class="chip<?= $initial === 'all' ? ' active' : '' ?>" data-status="all">Semua <span class="n"><?= $cnt['all'] ?></span></button>
                    <button type="button" class="chip<?= $initial === 'active' ? ' active' : '' ?>" data-status="active"><span class="dot" style="color:#22c55e"></span> Active <span class="n"><?= $cnt['active'] ?></span></button>
                    <button type="button" class="chip<?= $initial === 'maintenance' ? ' active' : '' ?>" data-status="maintenance"><span class="dot" style="color:#eab308"></span> Maintenance <span class="n"><?= $cnt['maintenance'] ?></span></button>
                </div>
            </div>

            <div class="lab-grid" id="labGrid"></div>

            <div class="card empty" id="emptyState" hidden>
                <i data-lucide="search-x"></i>
                <h3>Laboratorium tidak ditemukan</h3>
                <p>Coba kata pencarian lain atau ubah filter status.</p>
                <button type="button" class="btn btn-secondary btn-sm" id="resetFilter" style="margin-top:14px">Reset filter</button>
            </div>

<?php
ui_end_content();
if (can_edit()): ?>
<div class="modal-backdrop" id="labModal" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="labTitle">
        <div class="modal-head">
            <div><h3 id="labTitle">Tambah Laboratorium</h3><p>PC &amp; komponen inti dibuat otomatis. Tidak perlu membuat folder / file baru.</p></div>
            <button type="button" class="modal-close" data-modal-close aria-label="Tutup"><i data-lucide="x"></i></button>
        </div>
        <form id="labForm" autocomplete="off">
            <div class="field"><label for="lName">Nama lab</label><input type="text" class="input" id="lName" required maxlength="100" placeholder="Contoh: Lab Jaringan 2"></div>
            <div class="two-col">
                <div class="field"><label for="lFloor">Lantai</label><input type="text" class="input" id="lFloor" required maxlength="30" placeholder="2nd Floor"></div>
                <div class="field"><label for="lPcs">Jumlah PC</label><input type="number" class="input" id="lPcs" required min="1" max="60" value="30"></div>
            </div>
            <div class="field"><label for="lCode">Kode <span class="hint">(opsional, untuk alamat halaman)</span></label><input type="text" class="input" id="lCode" maxlength="20" placeholder="otomatis dari nama"></div>
            <div class="modal-foot">
                <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
                <button type="submit" class="btn btn-primary" id="btnSaveLab"><i data-lucide="save"></i> Buat Lab</button>
            </div>
        </form>
    </div>
</div>
<?php endif;
echo '<script>window.LABFLOW_LABS = ' . json_encode($labs, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) . '; window.LABFLOW_STATUS = ' . json_encode($initial) . '; window.LABFLOW_CAN_EDIT = ' . json_encode(can_edit()) . ';</script>' . "\n";
ui_scripts('', ['data_lab.js']);

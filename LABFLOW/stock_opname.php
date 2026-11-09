<?php
require_once __DIR__ . '/config/layout.php';
require_login();

$st   = db()->query('SELECT * FROM v_dashboard_stats')->fetch();
$cats = db()->query('SELECT name FROM stock_categories ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);
$last = db()->query("SELECT t.quantity, t.created_at, i.name FROM stock_transactions t
                     JOIN stock_items i ON i.id=t.item_id WHERE t.type='in' ORDER BY t.created_at DESC, t.id DESC LIMIT 1")->fetch();
$items = db()->query('SELECT i.*, c.name AS category FROM stock_items i JOIN stock_categories c ON c.id=i.category_id ORDER BY i.name')->fetchAll();
$admin = can_edit();

$low = 0;
foreach ($items as $it) { if ((int)$it['quantity'] <= (int)$it['min_quantity']) { $low++; } }
$lowOnly = !empty($_GET['menipis']);

ui_open('Stock Opname', 'stock', '', ['stock_opname.css'], '<h1>Stock Opname</h1>');
?>

            <div class="page-head">
                <div>
                    <h1>Stock Opname</h1>
                    <p>Catat barang masuk &amp; kelola inventaris laboratorium.</p>
                </div>
                <div class="actions">
                    <a class="btn btn-secondary" href="history.php?type=stock_in"><i data-lucide="clock"></i> Audit Log</a>
                </div>
            </div>

            <div class="stock-grid<?= $admin ? '' : ' single' ?>">

                <?php if ($admin): ?>
                <!-- ============ FORM BARANG MASUK ============ -->
                <div class="card">
                    <div class="card-head">
                        <h2><i data-lucide="circle-plus"></i> Catat Barang Masuk</h2>
                        <span class="badge">MANUAL STOCK IN</span>
                    </div>
                    <p class="desc">Input barang atau suku cadang baru yang diterima laboratorium. Jika nama barang sudah ada, stoknya otomatis ditambah.</p>

                    <form id="stockForm" autocomplete="off">
                        <div class="field">
                            <label for="itemName">Nama Barang</label>
                            <input type="text" class="input" id="itemName" required maxlength="150" placeholder="Contoh: Logitech G102 Lightsync Optical Mouse">
                        </div>

                        <div class="field">
                            <label for="itemCategory">Kategori / Jenis</label>
                            <input type="text" class="input" id="itemCategory" required maxlength="100" list="catList" placeholder="Pilih atau ketik kategori baru">
                            <datalist id="catList"><?php foreach ($cats as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?></datalist>
                        </div>

                        <div class="field">
                            <label for="itemDesc">Deskripsi &amp; Spesifikasi <span class="hint">(opsional)</span></label>
                            <textarea class="textarea" rows="3" id="itemDesc" placeholder="Kondisi, nomor PO, alokasi lab, dll."></textarea>
                        </div>

                        <div class="field">
                            <label for="stockCount">Jumlah Stok Masuk</label>
                            <div class="stepper">
                                <button type="button" data-step="-1" aria-label="Kurangi">&minus;</button>
                                <input type="number" class="input" id="stockCount" min="1" max="100000" value="1" required>
                                <button type="button" data-step="1" aria-label="Tambah">+</button>
                            </div>
                        </div>

                        <div class="field">
                            <label for="stockMin">Stok Minimum <span class="hint">(peringatan "menipis" bila stok &le; angka ini; kosong = 5 / tidak diubah)</span></label>
                            <input type="number" class="input" id="stockMin" min="0" max="100000" placeholder="5">
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" id="btnAdd"><i data-lucide="plus"></i> Tambah ke Inventaris</button>
                            <button type="reset" class="btn btn-ghost">Kosongkan</button>
                        </div>
                    </form>
                    <div class="form-footer">Petugas pencatat otomatis terisi: <?= e(current_user_name()) ?> (<?= e(role_label($_SESSION['role'])) ?>).</div>
                </div>
                <?php endif; ?>

                <!-- ============ RINGKASAN ============ -->
                <div class="card summary-card">
                    <div class="card-head"><h2><i data-lucide="chart-pie"></i> Ringkasan Stok</h2></div>
                    <div class="metrics">
                        <div class="metric"><strong data-count="<?= (int)$st['stock_total'] ?>">0</strong><span>Total unit</span></div>
                        <div class="metric blue"><strong data-count="<?= (int)$st['stock_in_month'] ?>">0</strong><span>Masuk bulan ini</span></div>
                        <div class="metric"><strong data-count="<?= count($items) ?>">0</strong><span>Jenis barang</span></div>
                        <div class="metric <?= $low ? 'warn' : '' ?>"><strong data-count="<?= $low ?>">0</strong><span>Stok menipis</span></div>
                    </div>
                    <?php if ($last): ?>
                    <div class="recent-item">
                        <div class="recent-icon"><i data-lucide="package-check"></i></div>
                        <div>
                            <strong>Barang masuk terakhir</strong>
                            <p><?= e(ui_date($last['created_at'])) ?> &bull; <?= (int)$last['quantity'] ?> &times; <?= e($last['name']) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!$admin): ?>
            <div class="note note-info" style="margin-top:20px"><i data-lucide="eye"></i><div>Anda masuk sebagai <strong>User</strong> &mdash; data stok hanya dapat dilihat. Ada kendala? Kirim laporan lewat menu <a href="laporan_saya.php">Laporan Saya</a>.</div></div>
            <?php endif; ?>

            <!-- ============ INVENTARIS ============ -->
            <div class="card inv-card">
                <div class="card-head">
                    <h2><i data-lucide="boxes"></i> Daftar Inventaris</h2>
                    <span class="badge badge-blue" id="invCount"><?= count($items) ?> JENIS BARANG</span>
                </div>

                <div class="toolbar" style="margin-bottom:8px">
                    <div class="input-icon search">
                        <i data-lucide="search"></i>
                        <input type="search" class="input" id="searchInput" placeholder="Cari nama, kategori, atau deskripsi..." autocomplete="off">
                    </div>
                    <select class="select" id="catFilter" aria-label="Filter kategori">
                        <option value="">Semua kategori</option>
                        <?php foreach ($cats as $c): ?><option value="<?= e($c) ?>"><?= e($c) ?></option><?php endforeach; ?>
                    </select>
                    <select class="select" id="stockFilter" aria-label="Filter kondisi stok">
                        <option value="">Semua kondisi</option>
                        <option value="low"<?= $lowOnly ? ' selected' : '' ?>>Menipis &amp; habis (<?= $low ?>)</option>
                    </select>
                    <select class="select" id="sortBy" aria-label="Urutkan">
                        <option value="name">Nama A&ndash;Z</option>
                        <option value="qty-desc">Stok terbanyak</option>
                        <option value="qty-asc">Stok tersedikit</option>
                    </select>
                </div>

                <div class="table-wrap">
                    <table class="table" id="invTable">
                        <thead><tr><th>Nama Barang</th><th>Kategori</th><th>Deskripsi</th><th>Stok</th><th>Min</th><?php if ($admin): ?><th style="text-align:right">Aksi</th><?php endif; ?></tr></thead>
                        <tbody>
                        <?php foreach ($items as $it):
                            $q = (int)$it['quantity'];
                            $mn = (int)$it['min_quantity'];
                            [$cls, $lbl] = $q === 0 ? ['badge-red', 'Habis'] : ($q <= $mn ? ['badge-yellow', 'Menipis'] : ['badge-green', 'Aman']);
                        ?>
                            <tr data-id="<?= (int)$it['id'] ?>" data-name="<?= e($it['name']) ?>" data-cat="<?= e($it['category']) ?>" data-desc="<?= e($it['description']) ?>" data-qty="<?= $q ?>" data-min="<?= $mn ?>" data-low="<?= $q <= $mn ? 1 : 0 ?>">
                                <td><strong><?= e($it['name']) ?></strong></td>
                                <td><span class="badge badge-blue"><?= e($it['category']) ?></span></td>
                                <td class="desc-cell" title="<?= e($it['description']) ?>"><?= e($it['description']) ?: '<span class="muted">&mdash;</span>' ?></td>
                                <td><span class="qty"><?= $q ?></span> <span class="badge <?= $cls ?>"><?= $lbl ?></span></td>
                                <td class="soft"><?= $mn ?></td>
                                <?php if ($admin): ?>
                                <td><div class="row-actions">
                                    <button type="button" class="icon-btn" title="Barang keluar" aria-label="Barang keluar" data-act="out"<?= $q === 0 ? ' disabled' : '' ?>><i data-lucide="package-minus"></i></button>
                                    <button type="button" class="icon-btn" title="Edit" aria-label="Edit" data-act="edit"><i data-lucide="pencil"></i></button>
                                    <button type="button" class="icon-btn danger" title="Hapus" aria-label="Hapus" data-act="del"><i data-lucide="trash-2"></i></button>
                                </div></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="empty" id="invEmpty" <?= $items ? 'hidden' : '' ?>>
                        <i data-lucide="package-open"></i>
                        <h3><?= $items ? 'Barang tidak ditemukan' : 'Belum ada barang' ?></h3>
                        <p><?= $items ? 'Coba ubah kata pencarian atau filter kategori.' : 'Catat barang masuk pertama lewat formulir di atas.' ?></p>
                    </div>
                </div>
            </div>

<?php ui_end_content(); ?>

<?php if ($admin): ?>
<!-- ============ MODAL: BARANG KELUAR ============ -->
<div class="modal-backdrop" id="outModal" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="outTitle">
        <div class="modal-head">
            <div><h3 id="outTitle">Barang Keluar</h3><p id="outName"></p></div>
            <button type="button" class="modal-close" data-modal-close aria-label="Tutup"><i data-lucide="x"></i></button>
        </div>
        <form id="outForm" autocomplete="off">
            <div class="field">
                <label for="outQty">Jumlah yang keluar <span class="hint" id="outHint"></span></label>
                <div class="stepper">
                    <button type="button" data-step="-1" aria-label="Kurangi">&minus;</button>
                    <input type="number" class="input" id="outQty" min="1" value="1" required>
                    <button type="button" data-step="1" aria-label="Tambah">+</button>
                </div>
            </div>
            <div class="field">
                <label for="outNotes">Keterangan <span class="hint">(opsional)</span></label>
                <input type="text" class="input" id="outNotes" maxlength="200" placeholder="Contoh: dipakai untuk Lab RPL PC 7">
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
                <button type="submit" class="btn btn-warning" id="btnOut"><i data-lucide="package-minus"></i> Keluarkan Barang</button>
            </div>
        </form>
    </div>
</div>

<!-- ============ MODAL: EDIT BARANG ============ -->
<div class="modal-backdrop" id="editModal" hidden>
    <div class="modal modal-lg" role="dialog" aria-modal="true" aria-labelledby="editTitle">
        <div class="modal-head">
            <div><h3 id="editTitle">Edit Barang</h3><p>Perubahan stok dilakukan lewat Barang Masuk / Barang Keluar.</p></div>
            <button type="button" class="modal-close" data-modal-close aria-label="Tutup"><i data-lucide="x"></i></button>
        </div>
        <form id="editForm" autocomplete="off">
            <div class="field"><label for="eName">Nama Barang</label><input type="text" class="input" id="eName" required maxlength="150"></div>
            <div class="field"><label for="eCat">Kategori</label><input type="text" class="input" id="eCat" required maxlength="100" list="catList"></div>
            <div class="field"><label for="eDesc">Deskripsi &amp; Spesifikasi</label><textarea class="textarea" id="eDesc" rows="3"></textarea></div>
            <div class="field"><label for="eMin">Stok minimum <span class="hint">(batas peringatan "menipis")</span></label><input type="number" class="input" id="eMin" min="0" max="100000"></div>
            <div class="modal-foot">
                <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
                <button type="submit" class="btn btn-primary" id="btnEdit"><i data-lucide="save"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php ui_scripts('', ['stock_opname.js']);

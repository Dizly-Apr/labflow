<?php
/** Backup database (admin): buat & unduh, daftar backup di server, petunjuk jadwal otomatis & pemulihan. */
require_once __DIR__ . '/config/layout.php';
require_once __DIR__ . '/config/backup_lib.php';
require_admin();

$files = lf_backup_list();
$last  = $files[0] ?? null;
$ageH  = $last ? (time() - $last['time']) / 3600 : null;
$size  = fn(int $b) => $b >= 1048576 ? round($b / 1048576, 2) . ' MB' : round($b / 1024, 1) . ' KB';

ui_open('Backup Database', 'backup', '', ['tiket.css'], '<h1>Backup Database</h1>');
?>
            <div class="page-head">
                <div>
                    <h1>Backup Database</h1>
                    <p>Amankan seluruh data LabFlow (PC, stok, tiket, riwayat, user) dalam satu file .sql.</p>
                </div>
            </div>

            <?php if ($ageH === null): ?>
            <div class="note note-warn"><i data-lucide="triangle-alert"></i><div><strong>Belum ada backup.</strong> Buat backup pertama sekarang.</div></div>
            <?php elseif ($ageH > 24 * 7): ?>
            <div class="note note-warn"><i data-lucide="triangle-alert"></i><div><strong>Backup terakhir sudah <?= (int)floor($ageH / 24) ?> hari lalu.</strong> Disarankan backup rutin, minimal mingguan.</div></div>
            <?php else: ?>
            <div class="note note-info"><i data-lucide="shield-check"></i><div>Backup terakhir: <strong><?= e(ui_date($last['time'], true)) ?></strong>.</div></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-head"><h2><i data-lucide="database-backup"></i> Buat Backup</h2></div>
                <div class="backup-actions">
                    <form method="post" action="config/backup_download.php">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-primary"><i data-lucide="download"></i> Buat &amp; Unduh Sekarang</button>
                    </form>
                    <button type="button" class="btn btn-secondary" id="btnBackup"><i data-lucide="hard-drive"></i> Simpan di Server Saja</button>
                </div>
                <p class="soft" style="margin-top:12px;font-size:13px">Server menyimpan <?= BACKUP_KEEP ?> backup terbaru; yang lebih lama dihapus otomatis.</p>
            </div>

            <div class="card" style="margin-top:20px">
                <div class="card-head"><h2><i data-lucide="archive"></i> Backup di Server</h2><span class="badge badge-blue"><?= count($files) ?> FILE</span></div>
                <?php if ($files): ?>
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>File</th><th>Dibuat</th><th>Ukuran</th><th style="text-align:right">Aksi</th></tr></thead>
                        <tbody>
                        <?php foreach ($files as $f): ?>
                            <tr>
                                <td><strong><?= e($f['name']) ?></strong></td>
                                <td class="nowrap"><?= e(ui_date($f['time'], true)) ?></td>
                                <td class="nowrap"><?= e($size($f['size'])) ?></td>
                                <td><div class="row-actions" style="display:flex;justify-content:flex-end;gap:6px">
                                    <a class="icon-btn" title="Unduh" aria-label="Unduh" href="config/backup_download.php?file=<?= e(rawurlencode($f['name'])) ?>"><i data-lucide="download"></i></a>
                                    <button type="button" class="icon-btn danger" title="Hapus" aria-label="Hapus" data-del="<?= e($f['name']) ?>"><i data-lucide="trash-2"></i></button>
                                </div></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty small"><i data-lucide="inbox"></i><p>Belum ada file backup di server.</p></div>
                <?php endif; ?>
            </div>

            <div class="pc-cols">
                <div class="card">
                    <div class="card-head"><h2><i data-lucide="clock"></i> Backup Otomatis</h2></div>
                    <p class="soft">Jalankan skrip ini lewat jadwal harian:</p>
                    <pre class="code">Linux (cron, tiap 02:00):
0 2 * * *  php <?= e(dirname(__DIR__ . '/x')) ?>/cli/backup.php

Windows (Task Scheduler / XAMPP):
C:\xampp\php\php.exe C:\xampp\htdocs\labflow\cli\backup.php</pre>
                </div>
                <div class="card">
                    <div class="card-head"><h2><i data-lucide="rotate-ccw"></i> Memulihkan Backup</h2></div>
                    <ol class="steps">
                        <li>Buka phpMyAdmin, pilih database <code><?= e(DB_NAME) ?></code>.</li>
                        <li>Tab <strong>Import</strong> &rarr; pilih file backup <code>.sql</code> &rarr; Go.</li>
                        <li>Atau lewat terminal: <code>mysql -u root <?= e(DB_NAME) ?> &lt; labflow_xxx.sql</code></li>
                    </ol>
                    <p class="soft" style="font-size:13px;margin-top:8px">Memulihkan akan menimpa data yang ada. Foto upload (folder <code>uploads/</code>) tidak ikut di file .sql &mdash; salin foldernya secara terpisah.</p>
                </div>
            </div>
<?php
ui_end_content();
ui_scripts('', ['backup.js']);

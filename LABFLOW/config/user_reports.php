<?php
/**
 * Laporan dari role User (yang mengakses lab) untuk Koordinator & Admin.
 *   tabel user_reports        = laporan
 *   tabel user_report_events  = riwayat (dibuat, status, tanggapan, tiket)
 *
 * Alur status:  new (Baru) -> read (Dibaca) -> processing (Ditindaklanjuti) -> resolved (Selesai)
 *  - Koordinator & Admin SAMA-SAMA menerima semua laporan (satu kotak masuk bersama).
 *  - Laporan dibuka petugas pertama kali -> otomatis "Dibaca".
 *  - Laporan kerusakan yang menyebut PC bisa langsung dijadikan Tiket Perbaikan.
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/tickets.php';

/** kode => [label, ikon lucide] */
const UR_CATEGORY = [
    'kerusakan'  => ['Kerusakan perangkat',               'wrench'],
    'fasilitas'  => ['Fasilitas lab (AC, listrik, meja)', 'armchair'],
    'jaringan'   => ['Jaringan / internet',               'wifi'],
    'kebersihan' => ['Kebersihan & kerapian',             'sparkles'],
    'keamanan'   => ['Keamanan & kehilangan',             'shield-alert'],
    'saran'      => ['Saran / masukan',                   'message-square'],
    'lainnya'    => ['Lainnya',                           'ellipsis'],
];

/** kode => [label, warna badge tk-*] */
const UR_STATUS = [
    'new'        => ['Baru',            'blue'],
    'read'       => ['Dibaca',          'purple'],
    'processing' => ['Ditindaklanjuti', 'yellow'],
    'resolved'   => ['Selesai',         'green'],
];

/** Prioritas dari pelapor (kunci sama dengan TICKET_PRIORITY agar bisa dipakai ulang untuk tiket). */
const UR_PRIORITY = ['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi'];

function ur_status_label(string $s): string   { return UR_STATUS[$s][0] ?? $s; }
function ur_category_label(string $c): string { return UR_CATEGORY[$c][0] ?? $c; }

function ur_status_badge(string $status): string
{
    [$l, $c] = UR_STATUS[$status] ?? [$status, 'gray'];
    return '<span class="tk-badge tk-' . e($c) . '">' . e($l) . '</span>';
}

function ur_category_badge(string $cat): string
{
    [$l, $i] = UR_CATEGORY[$cat] ?? [$cat, 'ellipsis'];
    return '<span class="ur-cat"><i data-lucide="' . e($i) . '"></i>' . e($l) . '</span>';
}

/** Catat satu baris riwayat laporan. */
function ur_event(PDO $pdo, int $reportId, string $action, ?string $detail, ?int $userId): void
{
    $pdo->prepare('INSERT INTO user_report_events (report_id,action,detail,user_id) VALUES (?,?,?,?)')
        ->execute([$reportId, $action, $detail !== null ? mb_substr($detail, 0, 500) : null, $userId]);
}

/** Satu laporan lengkap (+ lab, PC, pelapor, penanggap, tiket). */
function ur_get(PDO $pdo, int $id, bool $lock = false): ?array
{
    $s = $pdo->prepare(
        'SELECT r.*, l.code AS lab_code, l.name AS lab_name, p.pc_number,
                u.full_name AS reporter_full, h.full_name AS handler_name
         FROM user_reports r
         JOIN laboratories l ON l.id = r.lab_id
         LEFT JOIN pcs p ON p.id = r.pc_id
         LEFT JOIN users u ON u.id = r.reported_by
         LEFT JOIN users h ON h.id = r.handled_by
         WHERE r.id = ?' . ($lock ? ' FOR UPDATE' : '')
    );
    $s->execute([$id]);
    return $s->fetch() ?: null;
}

/** Nama pelapor yang ditampilkan (akun masih ada -> nama terkini; sudah dihapus -> nama saat melapor). */
function ur_reporter(array $r): string
{
    return $r['reporter_full'] ?: ($r['reporter_name'] ?: 'Pengguna dihapus');
}

/** Riwayat satu laporan, terbaru dulu. */
function ur_events(PDO $pdo, int $reportId): array
{
    $s = $pdo->prepare('SELECT e.*, u.full_name FROM user_report_events e LEFT JOIN users u ON u.id = e.user_id
                        WHERE e.report_id = ? ORDER BY e.created_at DESC, e.id DESC');
    $s->execute([$reportId]);
    return $s->fetchAll();
}

/** Jumlah laporan per status (untuk chip filter). */
function ur_counts(PDO $pdo, ?int $onlyUser = null): array
{
    $sql = 'SELECT status, COUNT(*) FROM user_reports' . ($onlyUser !== null ? ' WHERE reported_by = ' . (int)$onlyUser : '') . ' GROUP BY status';
    return $pdo->query($sql)->fetchAll(PDO::FETCH_KEY_PAIR);
}

/** Halaman 403 sederhana. */
function ur_forbidden(string $msg, string $home = 'dashboard.php'): void
{
    http_response_code(403);
    echo '<!DOCTYPE html><meta charset="utf-8"><title>403</title>'
       . '<body style="font-family:sans-serif;text-align:center;padding:80px">'
       . '<h1>403 - Akses ditolak</h1><p>' . e($msg) . '</p><p><a href="' . e($home) . '">Kembali</a></p></body>';
    exit;
}

function ur_not_found(string $back, string $label): void
{
    http_response_code(404);
    echo '<!DOCTYPE html><meta charset="utf-8"><title>404</title><body style="font-family:sans-serif;text-align:center;padding:80px">'
       . '<h1>404 - Laporan tidak ditemukan</h1><p><a href="' . e($back) . '">' . e($label) . '</a></p></body>';
    exit;
}

/**
 * Modal "Kirim Laporan" (role User). Dipakai di Laporan Saya, denah Lab, dan halaman PC.
 * Setelah ui_end_content(), panggil ur_modal() lalu ui_scripts(..., ['laporan_user.js']).
 * $prefillLab / $prefillPc: kode lab & nomor PC yang sudah terpilih (opsional).
 */
function ur_modal(string $prefillLab = '', int $prefillPc = 0, bool $autoOpen = false): void
{
    $labs = [];
    foreach (db()->query('SELECT code, name FROM laboratories ORDER BY id') as $l) {
        // jumlah PC nyata dihitung dari tabel pcs (kolom total_pcs bisa tertinggal)
        $labs[$l['code']] = ['name' => $l['name']];
    }
    foreach (db()->query('SELECT l.code, MAX(p.pc_number) AS mx FROM laboratories l JOIN pcs p ON p.laboratory_id = l.id GROUP BY l.code') as $r) {
        if (isset($labs[$r['code']])) { $labs[$r['code']]['pcs'] = (int)$r['mx']; }
    }
    foreach ($labs as &$lb) { $lb['pcs'] = $lb['pcs'] ?? 0; }
    unset($lb);
    ?>
<div class="modal-backdrop" id="urModal" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="urTitle">
        <div class="modal-head">
            <div><h3 id="urTitle">Kirim Laporan</h3><p>Laporan Anda diterima oleh <strong>Koordinator Lab</strong> dan <strong>Admin</strong>.</p></div>
            <button type="button" class="modal-close" data-modal-close aria-label="Tutup"><i data-lucide="x"></i></button>
        </div>
        <form id="urForm" autocomplete="off">
            <div class="two-col">
                <div class="field"><label for="urLab">Laboratorium</label>
                    <select class="select" id="urLab" required>
                        <option value="">Pilih lab…</option>
                        <?php foreach ($labs as $code => $lb): ?><option value="<?= e($code) ?>"><?= e($lb['name']) ?></option><?php endforeach; ?>
                    </select></div>
                <div class="field"><label for="urPc">PC <span class="hint">(opsional)</span></label>
                    <select class="select" id="urPc"><option value="0">Seluruh lab / tidak spesifik</option></select></div>
            </div>
            <div class="two-col">
                <div class="field"><label for="urCat">Jenis laporan</label>
                    <select class="select" id="urCat">
                        <?php foreach (UR_CATEGORY as $k => [$l]): ?><option value="<?= e($k) ?>"><?= e($l) ?></option><?php endforeach; ?>
                    </select></div>
                <div class="field"><label for="urPrio">Tingkat urgensi</label>
                    <select class="select" id="urPrio">
                        <?php foreach (UR_PRIORITY as $k => $l): ?><option value="<?= e($k) ?>"<?= $k === 'medium' ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
                    </select></div>
            </div>
            <div class="field"><label for="urSubject">Judul laporan</label>
                <input type="text" class="input" id="urSubject" required minlength="3" maxlength="150" placeholder="Contoh: AC Lab RPL tidak dingin"></div>
            <div class="field"><label for="urMsg">Penjelasan <span class="hint" id="urCount">0 / 1000</span></label>
                <textarea class="textarea" id="urMsg" rows="5" required minlength="5" maxlength="1000" placeholder="Ceritakan apa yang terjadi, sejak kapan, dan dampaknya."></textarea></div>
            <div class="modal-foot">
                <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
                <button type="submit" class="btn btn-primary" id="btnUrSend"><i data-lucide="send"></i> Kirim Laporan</button>
            </div>
        </form>
    </div>
</div>
<script>window.LF_UR = <?= json_encode(['labs' => $labs, 'lab' => $prefillLab, 'pc' => $prefillPc, 'autoOpen' => $autoOpen], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<?php
}

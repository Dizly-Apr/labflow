<?php
/**
 * Form lapor kerusakan PUBLIK (tanpa login) - dibuka dari QR code di PC:  lapor.php?lab=rpl&no=7
 * Pengaman: token CSRF, honeypot, pembatasan 5 laporan / 10 menit per IP, panjang input dibatasi.
 * Laporan masuk sebagai tiket "Open" (status komponen baru berubah setelah petugas memprosesnya).
 */
require_once __DIR__ . '/config/tickets.php';

$labCode = preg_replace('/[^a-z0-9_-]/i', '', (string)($_GET['lab'] ?? $_POST['lab'] ?? ''));
$pcNo    = (int)($_GET['no'] ?? $_POST['no'] ?? 0);
$pc      = find_pc($labCode, $pcNo);

function pub_page_open(string $title): void
{
    ?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <meta name="theme-color" content="#287cf0">
    <title><?= e($title) ?> | LabFlow</title>
<?php if (is_file(__DIR__ . '/logo-sekolah.png')): ?>    <link rel="icon" type="image/png" href="logo-sekolah.png">
<?php endif; ?>
    <link rel="stylesheet" href="<?= e('assets/css/public.css?v=' . (@filemtime(__DIR__ . '/assets/css/public.css') ?: 1)) ?>">
</head>
<body>
<main class="wrap">
    <header class="brandbar">
<?php if (is_file(__DIR__ . '/logo-sekolah.png')): ?>        <img src="logo-sekolah.png" alt="" width="40" height="40">
<?php endif; ?>
        <div><strong>LabFlow</strong><small>SMK Bina Informatika</small></div>
    </header>
<?php
}
function pub_page_close(): void
{
    echo "</main>\n</body>\n</html>\n";
}

if (!$pc) {
    http_response_code(404);
    pub_page_open('PC tidak ditemukan');
    echo '<section class="card center"><h1>PC tidak ditemukan</h1><p>QR code ini mungkin sudah tidak berlaku. Hubungi koordinator lab.</p></section>';
    pub_page_close();
    exit;
}

$pdo    = db();
$types  = component_types();
$error  = '';
$doneId = 0;
$window = 600;      // detik
$limit  = 5;        // laporan per IP per jendela

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $type = (string)($_POST['type'] ?? '');
    $desc = trim((string)($_POST['desc'] ?? ''));
    $ip   = client_ip();

    if (trim((string)($_POST['website'] ?? '')) !== '') {          // honeypot terisi = bot
        $doneId = -1;
    } elseif (rl_count('public_report', $ip, $window) >= $limit) {
        $error = 'Terlalu banyak laporan dari perangkat ini. Coba lagi dalam beberapa menit.';
    } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 80) {
        $error = 'Isi nama (dan kelas) Anda, 2–80 karakter.';
    } elseif (mb_strlen($desc) < 5 || mb_strlen($desc) > 255) {
        $error = 'Jelaskan masalahnya dengan 5–255 karakter.';
    } elseif ($type !== '' && !isset($types[$type])) {
        $error = 'Pilihan komponen tidak valid.';
    } else {
        $pdo->beginTransaction();
        try {
            $cid = null;
            if ($type !== '') {
                $q = $pdo->prepare('SELECT id FROM pc_components WHERE pc_id=? AND component_type=?');
                $q->execute([$pc['id'], $type]);
                $cid = $q->fetchColumn() ?: null;
            }
            $doneId = ticket_create($pdo, [
                'pc_id' => $pc['id'], 'component_id' => $cid, 'component_type' => $type ?: null,
                'description' => $desc, 'priority' => 'medium', 'source' => 'public', 'reporter_name' => $name,
            ]);
            log_activity('ticket', "Tiket #{$doneId} • {$pc['lab_name']} PC {$pcNo}", 'Laporan via QR oleh ' . $name . ': ' . mb_substr($desc, 0, 120), (int)$pc['lab_id'], (int)$pc['id']);
            $pdo->commit();
            rl_hit('public_report', $ip);
        } catch (Throwable $ex) {
            $pdo->rollBack();
            $error = 'Gagal menyimpan laporan. Coba lagi sebentar lagi.';
        }
    }
}

// kondisi PC saat ini
$st = $pdo->prepare("SELECT SUM(status='issue') FROM pc_components WHERE pc_id=?");
$st->execute([$pc['id']]);
$issues = (int)$st->fetchColumn();
$at = $pdo->prepare("SELECT description, status FROM lab_reports WHERE pc_id=? AND status<>'resolved' ORDER BY reported_at DESC LIMIT 5");
$at->execute([$pc['id']]);
$active = $at->fetchAll();

pub_page_open('Lapor PC ' . $pcNo . ' - ' . $pc['lab_name']);
?>
    <section class="card">
        <p class="eyebrow"><?= e($pc['lab_name']) ?> &middot; <?= e($pc['lab_floor']) ?></p>
        <h1>PC <?= $pcNo ?></h1>
        <p class="status <?= $issues || $active ? 'bad' : 'ok' ?>">
            <span class="dot"></span>
            <?= $issues || $active ? 'Ada masalah yang sedang ditangani' : 'Kondisi normal' ?>
        </p>
        <?php if ($active): ?>
        <ul class="active-list">
            <?php foreach ($active as $a): ?><li><?= e($a['description']) ?> <em><?= e(ticket_status_label($a['status'])) ?></em></li><?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </section>

<?php if ($doneId): ?>
    <section class="card center success">
        <div class="check">✓</div>
        <h2>Laporan terkirim</h2>
        <p><?= $doneId > 0 ? 'Nomor tiket <strong>#' . (int)$doneId . '</strong>. ' : '' ?>Terima kasih, koordinator lab akan menindaklanjuti.</p>
        <a class="btn ghost" href="<?= e('lapor.php?lab=' . rawurlencode($labCode) . '&no=' . $pcNo) ?>">Kirim laporan lain</a>
    </section>
<?php else: ?>
    <section class="card">
        <h2>Laporkan kerusakan</h2>
        <?php if ($error): ?><div class="alert" role="alert"><?= e($error) ?></div><?php endif; ?>
        <form method="post" action="<?= e('lapor.php?lab=' . rawurlencode($labCode) . '&no=' . $pcNo) ?>" autocomplete="off">
            <?= csrf_field() ?>
            <input type="hidden" name="lab" value="<?= e($labCode) ?>">
            <input type="hidden" name="no" value="<?= $pcNo ?>">
            <div class="hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

            <label for="name">Nama &amp; kelas</label>
            <input type="text" id="name" name="name" required minlength="2" maxlength="80" placeholder="Contoh: Budi, XI RPL 1" value="<?= e($_POST['name'] ?? '') ?>">

            <label for="type">Bagian yang bermasalah</label>
            <select id="type" name="type">
                <option value="">Tidak tahu / lainnya</option>
                <?php foreach ($types as $k => $t): ?><option value="<?= e($k) ?>"<?= ($_POST['type'] ?? '') === $k ? ' selected' : '' ?>><?= e($t[0]) ?></option><?php endforeach; ?>
            </select>

            <label for="desc">Apa masalahnya?</label>
            <textarea id="desc" name="desc" rows="4" required minlength="5" maxlength="255" placeholder="Contoh: layar tidak menyala setelah tombol power ditekan"><?= e($_POST['desc'] ?? '') ?></textarea>

            <button type="submit" class="btn">Kirim Laporan</button>
        </form>
    </section>
<?php endif; ?>

<?php if (is_logged_in()): ?>
    <p class="staff"><a href="<?= e(pc_url($pc['lab_code'], $pcNo)) ?>">Buka halaman PC (petugas) →</a></p>
<?php endif; ?>
<?php pub_page_close();

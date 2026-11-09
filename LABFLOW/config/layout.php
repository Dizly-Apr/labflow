<?php
/**
 * Layout bersama LabFlow: head, sidebar, topbar, script.
 * Satu sumber untuk semua halaman, jadi menu / topbar tidak lagi disalin ke tiap file.
 *
 * Pemakaian:
 *   require_once __DIR__ . '/config/layout.php';
 *   require_login();
 *   ui_open('Dashboard', 'dashboard', '', ['dashboard.css']);   // buka halaman sampai <section class="content">
 *   ... isi halaman ...
 *   ui_end_content();                                             // tutup content / main / app
 *   ... (modal opsional) ...
 *   ui_scripts('', ['dashboard.js']);                             // script + </body></html>
 *
 * $base = prefix path ke root project: '' (root), '../' (Lab_x), '../../' (Lab_x/HardwareComponents).
 */
require_once __DIR__ . '/auth.php';

/** URL aset dengan versi otomatis (?v=filemtime) agar browser tidak memakai cache lama. */
function ui_asset(string $path, string $base = ''): string
{
    $file = dirname(__DIR__) . '/assets/' . $path;
    return e($base . 'assets/' . $path . '?v=' . (@filemtime($file) ?: 1));
}

/**
 * Ringkasan untuk lonceng notifikasi & badge menu: pc_issue, tickets_open, stock_low,
 * + laporan user: reports_new (Koordinator/Admin: laporan baru yang belum dibaca),
 *   reports_unseen (User: laporannya yang sudah dibaca / ditanggapi tapi belum dilihat).
 */
function ui_alerts(): array
{
    static $a = null;
    if ($a === null) {
        $a = ['pc_issue' => 0, 'tickets_open' => 0, 'stock_low' => 0, 'reports_new' => 0, 'reports_unseen' => 0];
        try {
            $r = db()->query('SELECT pc_issue, tickets_open, stock_low FROM v_dashboard_stats')->fetch();
            if ($r) {
                $a = array_merge($a, array_map('intval', $r));
            }
        } catch (Throwable $e) {
        }
        try {   // tabel user_reports belum ada (belum migrasi v3) -> abaikan
            if (can_edit()) {
                $a['reports_new'] = (int)db()->query("SELECT COUNT(*) FROM user_reports WHERE status = 'new'")->fetchColumn();
            } elseif (can_report()) {
                $q = db()->prepare('SELECT COUNT(*) FROM user_reports WHERE reported_by = ? AND reporter_seen = 0');
                $q->execute([$_SESSION['user_id'] ?? 0]);
                $a['reports_unseen'] = (int)$q->fetchColumn();
            }
        } catch (Throwable $e) {
        }
    }
    return $a;
}

/** Jumlah PC bermasalah (dipertahankan untuk kompatibilitas). */
function ui_issue_count(): int
{
    return ui_alerts()['pc_issue'];
}

/** Foto profil, atau huruf awal nama bila belum punya foto. */
function ui_avatar(string $base = ''): string
{
    $u = avatar_url($_SESSION['avatar'] ?? null, $base);
    if ($u) {
        return '<img class="avatar-img" src="' . e($u) . '" alt="" width="34" height="34">';
    }
    $ch = function_exists('mb_substr') ? mb_substr(current_user_name(), 0, 1) : substr(current_user_name(), 0, 1);
    return '<span class="avatar-fallback">' . e(strtoupper($ch)) . '</span>';
}

/** Logo sekolah bila file logo-sekolah.png ada di root; kalau tidak, pakai logo LabFlow. */
function ui_logo(string $base = '', int $size = 40): string
{
    if (is_file(dirname(__DIR__) . '/logo-sekolah.png')) {
        return '<img src="' . e($base) . 'logo-sekolah.png" alt="Logo sekolah" style="width:' . $size . 'px;height:' . $size . 'px;object-fit:contain">';
    }
    return '<span class="brand-mark" style="width:' . $size . 'px;height:' . $size . 'px">'
         . '<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">'
         . '<path d="M10 2v7.5L4.6 18.2A2 2 0 0 0 6.3 21h11.4a2 2 0 0 0 1.7-2.8L14 9.5V2"/><path d="M8.5 2h7"/><path d="M7 15h10"/></svg></span>';
}

/**
 * Buka dokumen: <head>, sidebar, topbar, lalu <section class="content">.
 *
 * @param string $title   judul tab
 * @param string $active  dashboard | lab | stock | users | '' (tidak ada yang aktif)
 * @param string $base    prefix path ke root
 * @param array  $css     file CSS tambahan di assets/css/
 * @param string $left    HTML untuk sisi kiri topbar (judul / tombol kembali)
 */
function ui_open(string $title, string $active = '', string $base = '', array $css = [], string $left = ''): void
{
    $al     = ui_alerts();
    $bell   = $al['tickets_open'] + $al['stock_low'] + $al['reports_new'] + $al['reports_unseen'];
    $items  = [
        'dashboard' => ['dashboard.php',     'layout-dashboard', 'Dashboard'],
        'lab'       => ['data_lab.php',      'monitor',          'Data Lab'],
    ];
    if (can_report()) {     // role User: kirim laporan & pantau tanggapannya
        $items['laporan_saya'] = ['laporan_saya.php', 'send', 'Laporan Saya'];
    }
    if (can_edit()) {       // Koordinator & Admin: kotak masuk laporan dari user
        $items['laporan_masuk'] = ['laporan_masuk.php', 'inbox', 'Laporan Masuk'];
    }
    $items += [
        'tiket'     => ['tiket.php',         'wrench',           'Tiket Perbaikan'],
        'stock'     => ['stock_opname.php',  'archive',          'Stock Opname'],
        'laporan'   => ['laporan.php',       'file-spreadsheet', 'Laporan & Export'],
        'analitik'  => ['analitik.php',      'chart-column',     'Analitik'],
        'history'   => ['history.php',       'history',          'Riwayat'],
    ];
    if (is_admin()) {
        $items['users']  = ['users.php',  'users',          'Kelola User'];
        $items['backup'] = ['backup.php', 'database-backup', 'Backup Database'];
    }
    $badges = ['tiket' => $al['tickets_open'], 'stock' => $al['stock_low'],
               'laporan_masuk' => $al['reports_new'], 'laporan_saya' => $al['reports_unseen']];
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#287cf0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <script>(function(){try{var t=localStorage.getItem('lf_theme');if(!t&&window.matchMedia&&matchMedia('(prefers-color-scheme: dark)').matches)t='dark';if(t==='dark')document.documentElement.setAttribute('data-theme','dark');}catch(e){}})();</script>
    <title><?= e($title) ?> | LabFlow</title>
<?php if (is_file(dirname(__DIR__) . '/logo-sekolah.png')): ?>    <link rel="icon" type="image/png" href="<?= e($base) ?>logo-sekolah.png">
<?php endif; ?>
    <link rel="stylesheet" href="<?= ui_asset('css/base.css', $base) ?>">
<?php foreach ($css as $f): ?>
    <link rel="stylesheet" href="<?= ui_asset('css/' . $f, $base) ?>">
<?php endforeach; ?>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>window.LF_BASE = <?= json_encode($base) ?>;</script>
</head>
<body>
<div class="app">

    <!-- ================= SIDEBAR ================= -->
    <aside class="sidebar" id="sidebar" aria-label="Menu utama">
        <a class="brand" href="<?= e($base) ?>dashboard.php" aria-label="LabFlow - ke Dashboard">
            <?= ui_logo($base, 40) ?>
            <span class="brand-text"><strong>LabFlow</strong><small>SMK Bina Informatika</small></span>
        </a>

        <form class="sidebar-search" action="<?= e($base) ?>search.php" method="get" role="search">
            <i data-lucide="search"></i>
            <input type="search" name="q" placeholder="Cari PC, tiket, stok…" aria-label="Pencarian global" autocomplete="off">
        </form>

        <nav class="sidebar-menu">
            <div class="menu-label">Menu</div>
<?php foreach ($items as $key => [$href, $icon, $label]): ?>
            <a href="<?= e($base . $href) ?>" class="menu-item<?= $active === $key ? ' active' : '' ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>>
                <i data-lucide="<?= $icon ?>"></i><span><?= e($label) ?></span>
<?php if (!empty($badges[$key])): ?>                <em class="menu-badge"><?= (int)$badges[$key] > 99 ? '99+' : (int)$badges[$key] ?></em>
<?php endif; ?>
            </a>
<?php endforeach; ?>
        </nav>

        <div class="sidebar-bottom">
            <a href="<?= e($base) ?>logout.php" class="logout-btn"><i data-lucide="log-out"></i><span>Log Out</span></a>
        </div>
    </aside>
    <div class="scrim" id="scrim"></div>

    <!-- ================= MAIN ================= -->
    <main class="main">
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="menu-toggle" id="menuToggle" aria-label="Buka menu" aria-controls="sidebar"><i data-lucide="menu"></i></button>
                <?= $left ?>
            </div>
            <div class="topbar-right">
                <form class="top-search" action="<?= e($base) ?>search.php" method="get" role="search">
                    <i data-lucide="search"></i>
                    <input type="search" name="q" placeholder="Cari PC, tiket, stok…" aria-label="Pencarian global" autocomplete="off" value="<?= e($_GET['q'] ?? '') ?>">
                </form>
                <button type="button" class="icon-circle theme-toggle" id="themeToggle" title="Ganti tema terang / gelap" aria-label="Ganti tema terang atau gelap"><i data-lucide="moon" class="moon"></i><i data-lucide="sun" class="sun"></i></button>
                <div class="notif" id="notif">
                    <button type="button" class="icon-circle bell-ring" id="notifBtn" aria-haspopup="true" aria-expanded="false"
                            title="<?= $bell + $al['pc_issue'] ? 'Ada hal yang perlu ditindaklanjuti' : 'Tidak ada peringatan' ?>" aria-label="Notifikasi">
                        <i data-lucide="bell"></i>
                        <?php if ($bell > 0): ?><span class="count"><?= $bell > 99 ? '99+' : $bell ?></span><?php endif; ?>
                    </button>
                    <div class="notif-panel" id="notifPanel" hidden>
                        <div class="notif-title">Perlu perhatian</div>
<?php if (can_edit()): ?>
                        <a href="<?= e($base) ?>laporan_masuk.php?status=new"><i data-lucide="inbox"></i><span><strong><?= $al['reports_new'] ?></strong> laporan baru dari user</span></a>
<?php elseif (can_report()): ?>
                        <a href="<?= e($base) ?>laporan_saya.php"><i data-lucide="send"></i><span><strong><?= $al['reports_unseen'] ?></strong> pembaruan laporan Anda</span></a>
<?php endif; ?>
                        <a href="<?= e($base) ?>tiket.php?aktif=1"><i data-lucide="wrench"></i><span><strong><?= $al['tickets_open'] ?></strong> tiket perbaikan aktif</span></a>
                        <a href="<?= e($base) ?>history.php?type=pc_issue"><i data-lucide="triangle-alert"></i><span><strong><?= $al['pc_issue'] ?></strong> PC bermasalah</span></a>
                        <a href="<?= e($base) ?>stock_opname.php?menipis=1"><i data-lucide="package-minus"></i><span><strong><?= $al['stock_low'] ?></strong> barang stok menipis / habis</span></a>
                    </div>
                </div>
                <a class="profile" href="<?= e($base) ?>profile.php" title="Profil &amp; ganti password">
                    <?= ui_avatar($base) ?>
                    <span class="profile-text">
                        <span class="profile-name"><?= e(current_user_name()) ?></span>
                        <span class="profile-role"><?= e(role_label($_SESSION['role'] ?? 'user')) ?></span>
                    </span>
                </a>
            </div>
        </header>

        <section class="content">
<?php
}

/** Tutup <section class="content">, <main>, dan .app. */
function ui_end_content(): void
{
    echo "        </section>\n    </main>\n</div>\n";
}

/** Script bersama + script halaman, lalu tutup dokumen. */
function ui_scripts(string $base = '', array $js = []): void
{
    echo '<script src="' . ui_asset('js/app.js', $base) . '"></script>' . "\n";
    foreach ($js as $f) {
        echo '<script src="' . ui_asset('js/' . $f, $base) . '"></script>' . "\n";
    }
    echo "</body>\n</html>\n";
}

/** Tanggal berbahasa Indonesia, mis. "9 Okt 2026" atau "9 Okt 2026, 14:05". */
function ui_date($ts, bool $withTime = false): string
{
    $t = is_numeric($ts) ? (int)$ts : strtotime((string)$ts);
    if (!$t) {
        return '-';
    }
    $m = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $s = (int)date('j', $t) . ' ' . $m[(int)date('n', $t)] . ' ' . date('Y', $t);
    return $withTime ? $s . ', ' . date('H:i', $t) : $s;
}

/** Label & warna status garansi dari tanggal berakhir: [teks, warna] */
function ui_warranty(?string $until): array
{
    if (!$until) {
        return ['Belum diisi', 'gray'];
    }
    $days = (int)floor((strtotime($until) - strtotime(date('Y-m-d'))) / 86400);
    if ($days < 0)   return ['Garansi habis ' . ui_date($until), 'red'];
    if ($days <= 60) return ['Berakhir ' . $days . ' hari lagi', 'yellow'];
    return ['Aktif sampai ' . ui_date($until), 'green'];
}

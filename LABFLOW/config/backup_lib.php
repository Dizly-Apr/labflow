<?php
/**
 * Backup database ke file .sql (murni PHP, tanpa mysqldump - jalan di XAMPP/Laragon).
 * Hasilnya bisa dipulihkan lewat phpMyAdmin > Import atau:  mysql -u root labflow < file.sql
 */
require_once __DIR__ . '/koneksi.php';

const BACKUP_DIR  = __DIR__ . '/../backups/';
const BACKUP_KEEP = 30;     // jumlah file backup yang disimpan di server

/** Tulis dump lengkap ke $path. Mengembalikan ['tables' => n, 'rows' => n, 'bytes' => n]. */
function lf_backup_dump(PDO $pdo, string $path): array
{
    $fh = fopen($path, 'wb');
    if (!$fh) { throw new RuntimeException('Tidak bisa menulis file backup (cek izin folder backups/).'); }
    $w = fn(string $s) => fwrite($fh, $s);

    $w("-- LabFlow backup\n-- Dibuat: " . date('Y-m-d H:i:s') . " (" . APP_TZ . ")\n-- Database: " . DB_NAME . "\n\n");
    $w("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;\nSET FOREIGN_KEY_CHECKS = 0;\nSET UNIQUE_CHECKS = 0;\nSET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n");

    $tables = []; $views = [];
    foreach ($pdo->query('SHOW FULL TABLES') as $r) {
        $r = array_values($r);
        if (strtoupper($r[1]) === 'VIEW') { $views[] = $r[0]; } else { $tables[] = $r[0]; }
    }
    $nRows = 0;
    foreach ($tables as $t) {
        $q = '`' . str_replace('`', '``', $t) . '`';
        $create = $pdo->query("SHOW CREATE TABLE $q")->fetch();
        $w("-- ----------------------------\n-- Tabel $t\n-- ----------------------------\nDROP TABLE IF EXISTS $q;\n" . array_values($create)[1] . ";\n\n");
        if ($t === 'rate_limits') { continue; }          // data sementara, tidak perlu dibackup

        $cols = array_map(fn($c) => '`' . $c['Field'] . '`', $pdo->query("SHOW COLUMNS FROM $q")->fetchAll());
        $head = "INSERT INTO $q (" . implode(',', $cols) . ") VALUES\n";
        $stmt = $pdo->query("SELECT * FROM $q", PDO::FETCH_NUM);
        $batch = [];
        $flush = function () use (&$batch, $w, $head) {
            if ($batch) { $w($head . implode(",\n", $batch) . ";\n"); $batch = []; }
        };
        foreach ($stmt as $row) {
            $vals = array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote((string)$v), $row);
            $batch[] = '(' . implode(',', $vals) . ')';
            $nRows++;
            if (count($batch) >= 200) { $flush(); }
        }
        $flush();
        $w("\n");
    }
    // view bisa saling memakai (mis. v_dashboard_stats memakai v_pc_status) -> tulis berurutan sesuai ketergantungan
    $defs = [];
    foreach ($views as $v) {
        $row = array_values($pdo->query('SHOW CREATE VIEW `' . str_replace('`', '``', $v) . '`')->fetch());
        $sql = preg_replace('/DEFINER=`[^`]*`@`[^`]*`\s*/', '', $row[1]);
        $defs[$v] = preg_replace('/SQL SECURITY DEFINER/', 'SQL SECURITY INVOKER', $sql);
    }
    $done = [];
    while ($defs) {
        $progress = false;
        foreach ($defs as $v => $sql) {
            $wait = false;
            foreach ($defs as $other => $_) {
                if ($other !== $v && strpos($sql, '`' . $other . '`') !== false) { $wait = true; break; }
            }
            if ($wait) { continue; }
            $w("-- View $v\nDROP VIEW IF EXISTS `$v`;\n$sql;\n\n");
            unset($defs[$v]);
            $progress = true;
        }
        if (!$progress) {                       // lingkaran ketergantungan (tidak mungkin): tulis sisanya apa adanya
            foreach ($defs as $v => $sql) { $w("-- View $v\nDROP VIEW IF EXISTS `$v`;\n$sql;\n\n"); }
            break;
        }
    }
    $w("SET FOREIGN_KEY_CHECKS = 1;\nSET UNIQUE_CHECKS = 1;\n-- Selesai\n");
    fclose($fh);
    return ['tables' => count($tables), 'rows' => $nRows, 'bytes' => (int)filesize($path)];
}

/** Buat backup baru di folder backups/ dan rapikan file lama. Mengembalikan nama file. */
function lf_backup_create(PDO $pdo): string
{
    if (!is_dir(BACKUP_DIR) && !@mkdir(BACKUP_DIR, 0755, true)) { throw new RuntimeException('Folder backups/ tidak ada dan tidak bisa dibuat.'); }
    $name = 'labflow_' . date('Ymd_His') . '.sql';
    lf_backup_dump($pdo, BACKUP_DIR . $name);
    $files = lf_backup_list();
    foreach (array_slice($files, BACKUP_KEEP) as $old) { @unlink(BACKUP_DIR . $old['name']); }
    return $name;
}

/** Daftar file backup, terbaru dulu: [['name','size','time'], ...] */
function lf_backup_list(): array
{
    $out = [];
    foreach (glob(BACKUP_DIR . 'labflow_*.sql') ?: [] as $f) {
        $out[] = ['name' => basename($f), 'size' => (int)filesize($f), 'time' => (int)filemtime($f)];
    }
    usort($out, fn($a, $b) => $b['time'] <=> $a['time'] ?: strcmp($b['name'], $a['name']));
    return $out;
}

function lf_backup_name_ok(string $n): bool
{
    return (bool)preg_match('/^labflow_\d{8}_\d{6}\.sql$/', $n);
}

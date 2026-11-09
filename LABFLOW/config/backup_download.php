<?php
/**
 * Admin saja.
 *   POST (form + token CSRF)  -> buat backup baru & langsung diunduh
 *   GET ?file=labflow_...sql  -> unduh backup yang tersimpan di server
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/backup_lib.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = lf_backup_create(db());
        log_activity('system', 'Backup database', 'Backup manual: ' . $name);
    } catch (Throwable $e) {
        http_response_code(500);
        exit('Backup gagal: ' . htmlspecialchars($e->getMessage()));
    }
} else {
    $name = (string)($_GET['file'] ?? '');
    if (!lf_backup_name_ok($name) || !is_file(BACKUP_DIR . $name)) {
        http_response_code(404);
        exit('File backup tidak ditemukan.');
    }
}
header('Content-Type: application/sql; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $name . '"');
header('Content-Length: ' . filesize(BACKUP_DIR . $name));
header('X-Content-Type-Options: nosniff');
readfile(BACKUP_DIR . $name);

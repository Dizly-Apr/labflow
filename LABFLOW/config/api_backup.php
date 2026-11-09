<?php
/** Admin saja. POST JSON {action:'create'|'delete', file?} */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/backup_lib.php';
require_admin_json();

$in = json_decode(file_get_contents('php://input'), true) ?: [];
try {
    if (($in['action'] ?? '') === 'create') {
        $name = lf_backup_create(db());
        log_activity('system', 'Backup database', 'Backup manual: ' . $name);
        json_out(['success' => true, 'file' => $name]);
    }
    if (($in['action'] ?? '') === 'delete') {
        $n = (string)($in['file'] ?? '');
        if (!lf_backup_name_ok($n) || !is_file(BACKUP_DIR . $n)) { json_out(['success' => false, 'message' => 'File tidak ditemukan.'], 404); }
        unlink(BACKUP_DIR . $n);
        log_activity('system', 'Backup dihapus', $n);
        json_out(['success' => true]);
    }
    json_out(['success' => false, 'message' => 'Aksi tidak dikenal.'], 422);
} catch (Throwable $e) {
    json_out(['success' => false, 'message' => $e->getMessage()], 500);
}

<?php
/**
 * Backup otomatis lewat jadwal (cron / Windows Task Scheduler):
 *   Linux  : 0 2 * * *  php /path/ke/labflow/cli/backup.php
 *   Windows: C:\xampp\php\php.exe C:\xampp\htdocs\labflow\cli\backup.php
 * Hanya bisa dijalankan dari command line.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit("Hanya untuk CLI.\n"); }
require_once __DIR__ . '/../config/backup_lib.php';
try {
    $name = lf_backup_create(db());
    try {
        db()->prepare("INSERT INTO activity_logs (type,title,description) VALUES ('system','Backup database',?)")->execute(['Backup otomatis: ' . $name]);
    } catch (Throwable $e) { /* log opsional */ }
    echo "OK  backups/$name  (" . round(filesize(BACKUP_DIR . $name) / 1024, 1) . " KB)\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'GAGAL: ' . $e->getMessage() . "\n");
    exit(1);
}

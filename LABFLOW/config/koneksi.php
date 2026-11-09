<?php
/**
 * Koneksi database LabFlow (PDO).
 * Default XAMPP: user root, tanpa password. Ubah di sini bila berbeda.
 */
// Bisa di-override lewat environment variable (LABFLOW_DB_*), default untuk XAMPP.
define('DB_HOST', getenv('LABFLOW_DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('LABFLOW_DB_NAME') ?: 'labflow');
define('DB_USER', getenv('LABFLOW_DB_USER') ?: 'root');
define('DB_PASS', getenv('LABFLOW_DB_PASS') !== false ? getenv('LABFLOW_DB_PASS') : '');

// Zona waktu aplikasi (PHP & sesi MySQL disamakan agar jam di riwayat/laporan konsisten).
define('APP_TZ', getenv('LABFLOW_TZ') ?: 'Asia/Jakarta');
date_default_timezone_set(APP_TZ);

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                // samakan collation koneksi dengan tabel (utf8mb4_unicode_ci); tanpa ini MariaDB/Laragon memakai general_ci -> error 1267
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone = '" . (new DateTime('now', new DateTimeZone(APP_TZ)))->format('P') . "'",
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    }
    return $pdo;
}

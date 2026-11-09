<?php
/**
 * Session + helper. Di-include di paling atas setiap halaman yang butuh login.
 */
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/session.php';

lf_session_start();

function e($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// Sinkronkan sesi dengan database di setiap request: akun yang dihapus / dinonaktifkan
// langsung keluar, dan perubahan role / nama berlaku seketika.
if (!empty($_SESSION['user_id'])) {
    $__s = db()->prepare('SELECT username, full_name, role, avatar FROM users WHERE id = ? AND is_active = 1');
    $__s->execute([$_SESSION['user_id']]);
    if ($__u = $__s->fetch()) {
        $_SESSION['username']  = $__u['username'];
        $_SESSION['full_name'] = $__u['full_name'];
        $_SESSION['role']      = $__u['role'];
        $_SESSION['avatar']    = $__u['avatar'];
    } else {
        $_SESSION = [];
    }
}

/* ---------------------------------------------------------------------
 * CSRF - setiap request POST wajib membawa token sesi:
 *   - fetch/JSON : header  X-CSRF-Token  (otomatis oleh LF.api di app.js)
 *   - form biasa : field   csrf_token    (lihat csrf_field())
 * Diperiksa otomatis di bawah ini; endpoint tidak perlu memanggilnya lagi.
 * ------------------------------------------------------------------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $sent = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
    return !empty($_SESSION['csrf']) && is_string($sent) && hash_equals($_SESSION['csrf'], $sent);
}

if (!defined('LF_SKIP_CSRF') && !in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD', 'OPTIONS'], true) && !csrf_valid()) {
    $wantsJson = stripos($_SERVER['HTTP_CONTENT_TYPE'] ?? ($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json') !== false
              || stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
              || isset($_SERVER['HTTP_X_CSRF_TOKEN']) || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
    http_response_code(403);
    if ($wantsJson) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Token keamanan tidak valid atau kedaluwarsa. Muat ulang halaman lalu coba lagi.'], JSON_UNESCAPED_UNICODE);
    } else {
        echo '<!DOCTYPE html><meta charset="utf-8"><title>403</title><body style="font-family:sans-serif;text-align:center;padding:80px">'
           . '<h1>403 - Token keamanan tidak valid</h1><p>Sesi formulir kedaluwarsa. Muat ulang halaman lalu kirim lagi.</p></body>';
    }
    exit;
}

/* ---------------------------------------------------------------------
 * Rate limiting sederhana berbasis tabel rate_limits.
 * ------------------------------------------------------------------- */
/** IP klien. Sengaja hanya REMOTE_ADDR (header X-Forwarded-For mudah dipalsukan). */
function client_ip(): string
{
    return substr((string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

/** Jumlah kejadian pada bucket+kunci dalam $window detik terakhir. */
function rl_count(string $bucket, string $key, int $window): int
{
    try {
        $s = db()->prepare('SELECT COUNT(*) FROM rate_limits WHERE bucket=? AND k=? AND created_at > (NOW() - INTERVAL ' . (int)$window . ' SECOND)');
        $s->execute([$bucket, $key]);
        return (int)$s->fetchColumn();
    } catch (Throwable $e) {
        return 0;   // tabel belum ada (belum migrasi) -> jangan memblokir
    }
}

function rl_hit(string $bucket, string $key): void
{
    try {
        db()->prepare('INSERT INTO rate_limits (bucket,k) VALUES (?,?)')->execute([$bucket, $key]);
        if (random_int(1, 40) === 1) {   // bersihkan data lama sesekali
            db()->exec('DELETE FROM rate_limits WHERE created_at < (NOW() - INTERVAL 1 DAY)');
        }
    } catch (Throwable $e) {
    }
}

function rl_clear(string $bucket, string $key): void
{
    try {
        db()->prepare('DELETE FROM rate_limits WHERE bucket=? AND k=?')->execute([$bucket, $key]);
    } catch (Throwable $e) {
    }
}

/** Detik sampai kejadian tertua dalam jendela kedaluwarsa (untuk pesan "coba lagi dalam ..."). */
function rl_retry_after(string $bucket, string $key, int $window): int
{
    try {
        $s = db()->prepare('SELECT TIMESTAMPDIFF(SECOND, NOW(), MIN(created_at) + INTERVAL ' . (int)$window . ' SECOND) FROM rate_limits
                            WHERE bucket=? AND k=? AND created_at > (NOW() - INTERVAL ' . (int)$window . ' SECOND)');
        $s->execute([$bucket, $key]);
        return max(1, (int)$s->fetchColumn());
    } catch (Throwable $e) {
        return 60;
    }
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

/**
 * Role:
 *   admin       = kelola user (+ semua akses operasional)
 *   coordinator = Koordinator Lab: edit PC, stok, status lab - TANPA kelola user
 *   user        = melihat data lab + MENGIRIM LAPORAN ke Koordinator & Admin (tidak bisa mengubah data)
 */
function is_admin(): bool
{
    return ($_SESSION['role'] ?? '') === 'admin';
}

/** Boleh mengubah data operasional (PC, stok, status lab). */
function can_edit(): bool
{
    return in_array($_SESSION['role'] ?? '', ['admin', 'coordinator'], true);
}

/** Role User (akses lab): boleh mengirim laporan ke Koordinator & Admin. */
function can_report(): bool
{
    return ($_SESSION['role'] ?? '') === 'user';
}

/** Endpoint JSON untuk kirim laporan (role user). */
function require_reporter_json(): void
{
    require_login_json();
    if (!can_report()) {
        json_out(['success' => false, 'message' => 'Hanya akun User yang dapat mengirim laporan. Koordinator & Admin menerima laporan di menu Laporan Masuk.'], 403);
    }
}

/** Halaman HTML khusus admin (kelola user). */
function require_admin(string $home = 'dashboard.php'): void
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        echo '<!DOCTYPE html><meta charset="utf-8"><title>403</title>'
           . '<body style="font-family:sans-serif;text-align:center;padding:80px">'
           . '<h1>403 - Akses ditolak</h1><p>Halaman ini hanya untuk Admin.</p>'
           . '<p><a href="' . e($home) . '">Kembali</a></p></body>';
        exit;
    }
}

/** Endpoint JSON khusus admin. */
function require_admin_json(): void
{
    require_login_json();
    if (!is_admin()) {
        json_out(['success' => false, 'message' => 'Hanya Admin yang dapat mengelola user.'], 403);
    }
}

/** Endpoint JSON untuk ubah data operasional (admin & koordinator). */
function require_editor_json(): void
{
    require_login_json();
    if (!can_edit()) {
        json_out(['success' => false, 'message' => 'Akun User hanya bisa melihat data. Untuk melaporkan kendala, gunakan menu Laporan Saya.'], 403);
    }
}

function role_label(string $r): string
{
    return ['admin' => 'Admin', 'coordinator' => 'Koordinator Lab'][$r] ?? 'User (Lihat & Lapor)';
}

/** Halaman HTML: redirect ke login bila belum login. */
function require_login(string $loginUrl = 'login.php'): void
{
    if (!is_logged_in()) {
        header('Location: ' . $loginUrl);
        exit;
    }
}

/** Endpoint JSON: balas 401 bila belum login. */
function require_login_json(): void
{
    if (!is_logged_in()) {
        json_out(['success' => false, 'message' => 'Sesi berakhir, silakan login ulang.'], 401);
    }
}

function json_out(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function current_user_name(): string
{
    return $_SESSION['full_name'] ?? 'Guest';
}

function log_activity(string $type, string $title, ?string $desc, ?int $labId = null, ?int $pcId = null): void
{
    db()->prepare(
        'INSERT INTO activity_logs (type,title,description,laboratory_id,pc_id,user_id) VALUES (?,?,?,?,?,?)'
    )->execute([$type, $title, $desc, $labId, $pcId, $_SESSION['user_id'] ?? null]);
}

/** Data satu lab (view v_lab_overview) berdasarkan code, mis. 'rpl'. */
function get_lab(string $code): ?array
{
    $s = db()->prepare('SELECT * FROM v_lab_overview WHERE code = ?');
    $s->execute([$code]);
    return $s->fetch() ?: null;
}

/** Satu PC (+ data lab & aset) berdasarkan kode lab dan nomor PC. */
function find_pc(string $labCode, int $no): ?array
{
    $s = db()->prepare(
        'SELECT p.*, l.code AS lab_code, l.name AS lab_name, l.floor AS lab_floor, l.status AS lab_status, l.id AS lab_id
         FROM pcs p JOIN laboratories l ON l.id = p.laboratory_id
         WHERE l.code = ? AND p.pc_number = ?'
    );
    $s->execute([$labCode, $no]);
    return $s->fetch() ?: null;
}

/**
 * URL dasar aplikasi untuk QR code, diakhiri "/". Bisa dipaksa lewat environment variable
 * LABFLOW_BASE_URL (mis. http://192.168.1.10/labflow/) agar HP siswa bisa membukanya di jaringan sekolah.
 */
function public_base_url(): string
{
    $env = getenv('LABFLOW_BASE_URL');
    if ($env) {
        return rtrim($env, '/') . '/';
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $dir   = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $dir . '/';
}

/** URL halaman PC (relatif terhadap $base). */
function pc_url(string $labCode, int $no, string $base = ''): string
{
    return $base . 'pc.php?lab=' . rawurlencode($labCode) . '&no=' . $no;
}

/** Daftar nomor PC bermasalah (untuk label pink di denah lab). */
function get_issue_pcs(string $labCode): array
{
    $s = db()->prepare(
        "SELECT v.pc_number FROM v_pc_status v JOIN laboratories l ON l.id=v.laboratory_id
         WHERE l.code=? AND v.status='issue' COLLATE utf8mb4_unicode_ci"
    );
    $s->execute([$labCode]);
    return array_map('intval', $s->fetchAll(PDO::FETCH_COLUMN));
}


/** URL foto profil (relatif terhadap halaman; $prefix mis. '../' untuk subfolder). null = belum ada. */
function avatar_url(?string $file, string $prefix = ''): ?string
{
    return ($file && preg_match('/^u\d+_[a-f0-9]+\.(jpg|png|webp|gif)$/', $file)) ? $prefix . 'uploads/avatars/' . $file : null;
}

/** Foto profil user yang sedang login untuk topbar; fallback ke ikon default. */
function avatar_html(string $prefix = ''): string
{
    $u = avatar_url($_SESSION['avatar'] ?? null, $prefix);
    return $u ? '<img class="avatar-img" src="' . e($u) . '" alt="Foto profil" width="38" height="38" '
              . 'style="width:38px;height:38px;max-width:38px;border-radius:50%;object-fit:cover;border:2px solid #e5e7eb;display:block;flex-shrink:0">'
              : '<i data-lucide="circle-user-round"></i>';
}

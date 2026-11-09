<?php
// POST JSON {username, password, remember} -> {success, redirect}
require_once __DIR__ . '/auth.php';

$in = json_decode(file_get_contents('php://input'), true) ?: [];
$username = trim($in['username'] ?? '');
$password = (string)($in['password'] ?? '');

if ($username === '' || $password === '') {
    json_out(['success' => false, 'message' => 'Username dan password wajib diisi.'], 422);
}

// ---- pembatasan percobaan login: maks. 5 gagal / akun+IP dan 15 gagal / IP dalam 15 menit ----
const LOGIN_WINDOW = 900;
$ip      = client_ip();
$keyUser = $ip . '|' . strtolower($username);
if (rl_count('login_user', $keyUser, LOGIN_WINDOW) >= 5 || rl_count('login', $ip, LOGIN_WINDOW) >= 15) {
    $wait = max(rl_retry_after('login_user', $keyUser, LOGIN_WINDOW), rl_retry_after('login', $ip, LOGIN_WINDOW));
    header('Retry-After: ' . $wait);
    json_out(['success' => false, 'message' => 'Terlalu banyak percobaan login. Coba lagi dalam ' . max(1, (int)ceil($wait / 60)) . ' menit.'], 429);
}

$s = db()->prepare('SELECT * FROM users WHERE username = ? AND is_active = 1');
$s->execute([$username]);
$u = $s->fetch();

if (!$u || !password_verify($password, $u['password_hash'])) {
    rl_hit('login', $ip);
    rl_hit('login_user', $keyUser);
    json_out(['success' => false, 'message' => 'Username atau password salah.'], 401);
}

rl_clear('login_user', $keyUser);
if (!empty($in['remember'])) {
    $cp = session_get_cookie_params();
    session_set_cookie_params(['lifetime' => 60 * 60 * 24 * 30, 'path' => $cp['path'], 'secure' => $cp['secure'], 'httponly' => true, 'samesite' => 'Lax']);
}
session_regenerate_id(true);
$_SESSION['csrf'] = bin2hex(random_bytes(32));
$_SESSION['user_id']   = (int)$u['id'];
$_SESSION['username']  = $u['username'];
$_SESSION['full_name'] = $u['full_name'];
$_SESSION['role']      = $u['role'];

db()->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$u['id']]);
log_activity('login', 'Login', $u['full_name'] . ' masuk ke sistem');

json_out(['success' => true, 'redirect' => 'dashboard.php']);

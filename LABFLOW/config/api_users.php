<?php
/**
 * CRUD user - khusus role admin. Role yang bisa dibuat: admin | coordinator | user.
 * POST JSON {action: list|create|update|delete|toggle, ...}
 */
require_once __DIR__ . '/auth.php';
require_admin_json();

$in     = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $in['action'] ?? 'list';
$pdo    = db();
$me     = (int)$_SESSION['user_id'];

function fail(string $m, int $c = 422): void { json_out(['success' => false, 'message' => $m], $c); }

function active_admins(PDO $pdo, int $excludeId = 0): int
{
    $s = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role='admin' AND is_active=1 AND id <> ?");
    $s->execute([$excludeId]);
    return (int)$s->fetchColumn();
}

function validate(array $in, bool $creating): array
{
    $name = trim($in['full_name'] ?? '');
    $user = strtolower(trim($in['username'] ?? ''));
    $pass = (string)($in['password'] ?? '');
    $role = $in['role'] ?? 'user';
    $act  = !empty($in['is_active']) ? 1 : 0;

    if ($name === '' || mb_strlen($name) > 100)            fail('Nama lengkap wajib diisi (maks. 100 karakter).');
    if (!preg_match('/^[a-z0-9_.]{3,30}$/', $user))        fail('Username 3-30 karakter: huruf kecil, angka, titik, atau underscore.');
    if (!in_array($role, ['admin', 'coordinator', 'user'], true))       fail('Role tidak valid.');
    if ($creating && strlen($pass) < 6)                    fail('Password minimal 6 karakter.');
    if (!$creating && $pass !== '' && strlen($pass) < 6)   fail('Password minimal 6 karakter.');
    return [$name, $user, $pass, $role, $act];
}

switch ($action) {

case 'list':
    $rows = $pdo->query(
        'SELECT u.id,u.username,u.avatar,u.full_name,u.role,u.is_active,u.last_login,u.created_at,c.full_name AS created_by_name
         FROM users u LEFT JOIN users c ON c.id=u.created_by ORDER BY u.role, u.full_name'
    )->fetchAll();
    json_out(['success' => true, 'users' => $rows, 'me' => $me]);

case 'create':
    [$name, $user, $pass, $role, $act] = validate($in, true);
    $s = $pdo->prepare('SELECT 1 FROM users WHERE username=?'); $s->execute([$user]);
    if ($s->fetch()) fail('Username sudah dipakai.');
    $pdo->prepare('INSERT INTO users (username,password_hash,full_name,role,is_active,created_by) VALUES (?,?,?,?,?,?)')
        ->execute([$user, password_hash($pass, PASSWORD_BCRYPT), $name, $role, $act, $me]);
    log_activity('user_mgmt', 'User dibuat: ' . $user, "Role " . role_label($role));
    json_out(['success' => true]);

case 'update':
    $id = (int)($in['id'] ?? 0);
    [$name, $user, $pass, $role, $act] = validate($in, false);
    $s = $pdo->prepare('SELECT * FROM users WHERE id=?'); $s->execute([$id]);
    $old = $s->fetch();
    if (!$old) fail('User tidak ditemukan.', 404);
    $s = $pdo->prepare('SELECT 1 FROM users WHERE username=? AND id<>?'); $s->execute([$user, $id]);
    if ($s->fetch()) fail('Username sudah dipakai.');
    if ($id === $me && ($role !== 'admin' || !$act)) fail('Anda tidak dapat menurunkan role / menonaktifkan akun sendiri.');
    if ($old['role'] === 'admin' && ($role !== 'admin' || !$act) && active_admins($pdo, $id) < 1)
        fail('Harus ada minimal satu admin aktif.');

    $pdo->prepare('UPDATE users SET username=?, full_name=?, role=?, is_active=? WHERE id=?')
        ->execute([$user, $name, $role, $act, $id]);
    if ($pass !== '') {
        $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($pass, PASSWORD_BCRYPT), $id]);
    }
    log_activity('user_mgmt', 'User diperbarui: ' . $user, 'Role ' . role_label($role) . ($act ? '' : ' (nonaktif)') . ($pass !== '' ? ', password direset' : ''));
    json_out(['success' => true]);

case 'toggle':
    $id = (int)($in['id'] ?? 0);
    if ($id === $me) fail('Anda tidak dapat menonaktifkan akun sendiri.');
    $s = $pdo->prepare('SELECT * FROM users WHERE id=?'); $s->execute([$id]);
    $u = $s->fetch();
    if (!$u) fail('User tidak ditemukan.', 404);
    if ($u['is_active'] && $u['role'] === 'admin' && active_admins($pdo, $id) < 1) fail('Harus ada minimal satu admin aktif.');
    $pdo->prepare('UPDATE users SET is_active = 1 - is_active WHERE id=?')->execute([$id]);
    log_activity('user_mgmt', ($u['is_active'] ? 'User dinonaktifkan: ' : 'User diaktifkan: ') . $u['username'], null);
    json_out(['success' => true]);

case 'delete':
    $id = (int)($in['id'] ?? 0);
    if ($id === $me) fail('Anda tidak dapat menghapus akun sendiri.');
    $s = $pdo->prepare('SELECT * FROM users WHERE id=?'); $s->execute([$id]);
    $u = $s->fetch();
    if (!$u) fail('User tidak ditemukan.', 404);
    if ($u['role'] === 'admin' && $u['is_active'] && active_admins($pdo, $id) < 1) fail('Harus ada minimal satu admin aktif.');
    $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
    if ($u['avatar'] && preg_match('/^u\d+_[a-f0-9]+\.(jpg|png|webp|gif)$/', $u['avatar'])) { @unlink(__DIR__ . '/../uploads/avatars/' . $u['avatar']); }
    log_activity('user_mgmt', 'User dihapus: ' . $u['username'], null);
    json_out(['success' => true]);

default:
    fail('Aksi tidak dikenal.');
}

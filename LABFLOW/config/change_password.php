<?php
// Semua user (semua role) boleh mengganti password sendiri.
// POST JSON {current, new}
require_once __DIR__ . '/auth.php';
require_login_json();

$in  = json_decode(file_get_contents('php://input'), true) ?: [];
$cur = (string)($in['current'] ?? '');
$new = (string)($in['new'] ?? '');

if (strlen($new) < 6) json_out(['success' => false, 'message' => 'Password baru minimal 6 karakter.'], 422);

$s = db()->prepare('SELECT password_hash FROM users WHERE id=?');
$s->execute([$_SESSION['user_id']]);
if (!password_verify($cur, (string)$s->fetchColumn())) {
    json_out(['success' => false, 'message' => 'Password saat ini salah.'], 401);
}
db()->prepare('UPDATE users SET password_hash=? WHERE id=?')
    ->execute([password_hash($new, PASSWORD_BCRYPT), $_SESSION['user_id']]);
json_out(['success' => true]);

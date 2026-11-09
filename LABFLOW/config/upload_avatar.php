<?php
/**
 * Foto profil. Semua role boleh mengganti foto sendiri.
 *  POST multipart  avatar=<file>        -> upload (jpg/png/webp/gif, maks 2 MB)
 *  POST            action=remove        -> hapus foto (kembali ke ikon default)
 * Jika ekstensi GD aktif: gambar di-decode lalu di-encode ulang (dipotong persegi 256x256, JPEG),
 * sehingga metadata/EXIF & kode tersembunyi di dalam file ikut terbuang.
 * Jika GD TIDAK aktif: file tetap diterima setelah divalidasi ketat (isi file harus gambar asli),
 * disimpan dengan ekstensi dari hasil deteksi (bukan dari nama file pengguna) dan nama acak.
 * Tampilan tetap bulat & terpotong rapi lewat CSS (object-fit: cover).
 */
require_once __DIR__ . '/auth.php';
require_login_json();

const AVATAR_DIR = __DIR__ . '/../uploads/avatars/';
const MAX_BYTES  = 2 * 1024 * 1024;
const OUT_SIZE   = 256;

$uid = (int)$_SESSION['user_id'];

function drop_old(?string $f): void
{
    if ($f && preg_match('/^u\d+_[a-f0-9]+\.(jpg|png|webp|gif)$/', $f) && is_file(AVATAR_DIR . $f)) {
        @unlink(AVATAR_DIR . $f);
    }
}

$old = db()->prepare('SELECT avatar FROM users WHERE id=?');
$old->execute([$uid]);
$oldFile = $old->fetchColumn() ?: null;

/* ---------- hapus ---------- */
if (($_POST['action'] ?? '') === 'remove') {
    db()->prepare('UPDATE users SET avatar=NULL WHERE id=?')->execute([$uid]);
    drop_old($oldFile);
    json_out(['success' => true, 'avatar' => null]);
}

/* ---------- upload ---------- */
$hasGd = function_exists('imagecreatefromstring') && function_exists('imagejpeg');
$f = $_FILES['avatar'] ?? null;
if (!$f || $f['error'] !== UPLOAD_ERR_OK) {
    $msg = ($f['error'] ?? 0) === UPLOAD_ERR_INI_SIZE || ($f['error'] ?? 0) === UPLOAD_ERR_FORM_SIZE
        ? 'Ukuran file terlalu besar (maks. 2 MB).' : 'Pilih file gambar terlebih dahulu.';
    json_out(['success' => false, 'message' => $msg], 422);
}
if ($f['size'] > MAX_BYTES) json_out(['success' => false, 'message' => 'Ukuran file terlalu besar (maks. 2 MB).'], 422);

$mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
    json_out(['success' => false, 'message' => 'Format harus JPG, PNG, WEBP, atau GIF.'], 422);
}
$info = @getimagesize($f['tmp_name']);
if (!$info || $info[0] < 32 || $info[1] < 32 || $info[0] > 8000 || $info[1] > 8000) {
    json_out(['success' => false, 'message' => 'Dimensi gambar tidak valid (min. 32px, maks. 8000px).'], 422);
}

if (!is_dir(AVATAR_DIR)) @mkdir(AVATAR_DIR, 0755, true);

if ($hasGd) {
    $src = @imagecreatefromstring(file_get_contents($f['tmp_name']));
    if (!$src) json_out(['success' => false, 'message' => 'File gambar rusak / tidak bisa dibaca.'], 422);

    // putar sesuai orientasi EXIF (foto dari HP)
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $ex = @exif_read_data($f['tmp_name']);
        $rot = [3 => 180, 6 => -90, 8 => 90][$ex['Orientation'] ?? 1] ?? 0;
        if ($rot) { $src = imagerotate($src, $rot, 0) ?: $src; }
    }

    // potong persegi dari tengah, lalu perkecil
    $w = imagesx($src); $h = imagesy($src);
    $side = min($w, $h);
    $dst = imagecreatetruecolor(OUT_SIZE, OUT_SIZE);
    imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));   // latar putih untuk PNG transparan
    imagecopyresampled($dst, $src, 0, 0, intdiv($w - $side, 2), intdiv($h - $side, 2), OUT_SIZE, OUT_SIZE, $side, $side);

    $name = sprintf('u%d_%s.jpg', $uid, bin2hex(random_bytes(6)));
    $ok = imagejpeg($dst, AVATAR_DIR . $name, 88);
} else {
    // tanpa GD: simpan apa adanya, ekstensi dari mime hasil deteksi
    $ext  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'][$mime];
    $name = sprintf('u%d_%s.%s', $uid, bin2hex(random_bytes(6)), $ext);
    $ok   = move_uploaded_file($f['tmp_name'], AVATAR_DIR . $name);
}
if (!$ok) {
    json_out(['success' => false, 'message' => 'Gagal menyimpan file (cek izin tulis folder uploads/avatars).'], 500);
}

db()->prepare('UPDATE users SET avatar=? WHERE id=?')->execute([$name, $uid]);
drop_old($oldFile);
json_out(['success' => true, 'avatar' => 'uploads/avatars/' . $name]);

<?php
/**
 * Foto kartu lab (admin & koordinator).
 *  POST multipart  lab_id=<id>, photo=<file>   -> unggah / ganti foto
 *  POST            lab_id=<id>, action=remove  -> hapus foto (kartu kembali kosong)
 * Isi file divalidasi sebagai gambar asli; ekstensi dari hasil deteksi, nama acak.
 */
require_once __DIR__ . '/auth.php';
require_editor_json();

const LAB_DIR = __DIR__ . '/../uploads/labs/';
const LAB_MAX = 5 * 1024 * 1024;

$id = (int)($_POST['lab_id'] ?? 0);
$s = db()->prepare('SELECT id, name, image FROM laboratories WHERE id = ?');
$s->execute([$id]);
$lab = $s->fetch();
if (!$lab) json_out(['success' => false, 'message' => 'Lab tidak ditemukan.'], 404);

function drop_lab_img(?string $f): void
{
    if ($f && preg_match('/^lab\d+_[a-f0-9]+\.(jpg|png|webp)$/', $f) && is_file(LAB_DIR . $f)) @unlink(LAB_DIR . $f);
}

if (($_POST['action'] ?? '') === 'remove') {
    db()->prepare("UPDATE laboratories SET image='lab.jpg' WHERE id=?")->execute([$id]);
    drop_lab_img($lab['image']);
    json_out(['success' => true, 'image' => '']);
}

$f = $_FILES['photo'] ?? null;
if (!$f || $f['error'] !== UPLOAD_ERR_OK) json_out(['success' => false, 'message' => 'File tidak terkirim atau terlalu besar.'], 422);
if ($f['size'] > LAB_MAX) json_out(['success' => false, 'message' => 'Ukuran foto maksimal 5 MB.'], 422);

$info = @getimagesize($f['tmp_name']);
$ext  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$info['mime'] ?? ''] ?? null;
if (!$ext) json_out(['success' => false, 'message' => 'Format harus JPG, PNG, atau WEBP.'], 422);

if (!is_dir(LAB_DIR)) @mkdir(LAB_DIR, 0755, true);
$name = sprintf('lab%d_%s.%s', $id, bin2hex(random_bytes(6)), $ext);
if (!move_uploaded_file($f['tmp_name'], LAB_DIR . $name)) {
    json_out(['success' => false, 'message' => 'Gagal menyimpan file (cek izin tulis folder uploads/labs).'], 500);
}
db()->prepare('UPDATE laboratories SET image=? WHERE id=?')->execute([$name, $id]);
drop_lab_img($lab['image']);
json_out(['success' => true, 'image' => 'uploads/labs/' . $name]);

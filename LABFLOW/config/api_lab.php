<?php
// Ubah status lab (tombol "Maintenance All"). POST JSON {code, status: active|maintenance}
require_once __DIR__ . '/auth.php';
require_editor_json();

require_once __DIR__ . '/components.php';

$in     = json_decode(file_get_contents('php://input'), true) ?: [];

// ---- tambah lab baru: tidak perlu membuat folder / file lagi (denah = lab.php?lab=<kode>) ----
if (($in['action'] ?? '') === 'create') {
    $name  = trim((string)($in['name'] ?? ''));
    $floor = trim((string)($in['floor'] ?? ''));
    $n     = (int)($in['pcs'] ?? 0);
    $code  = strtolower(trim((string)($in['code'] ?? '')));
    if ($code === '') { $code = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(preg_replace('/^lab\s+/i', '', $name))), '-'); }
    if ($name === '' || mb_strlen($name) > 100 || $floor === '' || mb_strlen($floor) > 30) {
        json_out(['success' => false, 'message' => 'Nama lab dan lantai wajib diisi.'], 422);
    }
    if (!preg_match('/^[a-z0-9_-]{1,20}$/', $code)) {
        json_out(['success' => false, 'message' => 'Kode lab 1-20 karakter: huruf kecil, angka, - atau _.'], 422);
    }
    if ($n < 1 || $n > 60) { json_out(['success' => false, 'message' => 'Jumlah PC harus 1 sampai 60.'], 422); }
    if (get_lab($code)) { json_out(['success' => false, 'message' => 'Kode lab "' . $code . '" sudah dipakai.'], 422); }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $pdo->prepare('INSERT INTO laboratories (code,name,folder,page_file,floor,status,total_pcs) VALUES (?,?,?,?,?,\'active\',?)')
            ->execute([$code, $name, 'Lab_' . $code, 'lab_' . $code . '.php', $floor, $n]);
        $lid = (int)$pdo->lastInsertId();
        $insPc = $pdo->prepare('INSERT INTO pcs (laboratory_id, pc_number, asset_tag) VALUES (?,?,?)');
        $insC  = $pdo->prepare("INSERT INTO pc_components (pc_id, component_type, specification, status, report, updated_by) VALUES (?,?,?,'online','Normal',?)");
        for ($i = 1; $i <= $n; $i++) {
            $insPc->execute([$lid, $i, 'INV-' . strtoupper($code) . '-' . str_pad((string)$i, 2, '0', STR_PAD_LEFT)]);
            $pid = (int)$pdo->lastInsertId();
            foreach (component_types() as $type => $t) {
                if ($t[3]) { $insC->execute([$pid, $type, $t[2], $_SESSION['user_id']]); }   // komponen inti
            }
        }
        log_activity('lab_status', $name . ' • Dibuat', "{$name} ditambahkan ({$n} PC)", $lid);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        json_out(['success' => false, 'message' => 'Gagal membuat lab: ' . $e->getMessage()], 500);
    }
    json_out(['success' => true, 'code' => $code]);
}

$status = $in['status'] ?? '';
$lab    = get_lab((string)($in['code'] ?? ''));

if (!$lab || !in_array($status, ['active', 'maintenance'], true)) {
    json_out(['success' => false, 'message' => 'Data tidak valid.'], 422);
}
db()->prepare('UPDATE laboratories SET status=? WHERE id=?')->execute([$status, $lab['id']]);
log_activity('lab_status',
    $lab['name'] . ' • ' . ucfirst($status),
    $status === 'maintenance' ? $lab['name'] . ' Under Maintenance' : $lab['name'] . ' kembali Active',
    (int)$lab['id']);
json_out(['success' => true, 'status' => $status]);

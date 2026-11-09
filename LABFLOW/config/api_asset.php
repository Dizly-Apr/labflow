<?php
/**
 * Simpan data aset & garansi satu PC (admin & koordinator).
 * POST JSON {lab, pc, asset_tag, purchase_date, warranty_until, supplier, notes}
 */
require_once __DIR__ . '/auth.php';
require_editor_json();

$in  = json_decode(file_get_contents('php://input'), true) ?: [];
$pc  = find_pc((string)($in['lab'] ?? ''), (int)($in['pc'] ?? 0));
if (!$pc) json_out(['success' => false, 'message' => 'PC tidak ditemukan.'], 404);

$tag  = trim((string)($in['asset_tag'] ?? ''));
$sup  = trim((string)($in['supplier'] ?? ''));
$note = trim((string)($in['notes'] ?? ''));
$buy  = trim((string)($in['purchase_date'] ?? ''));
$war  = trim((string)($in['warranty_until'] ?? ''));

$okDate = fn(string $d): bool => $d === '' || (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && checkdate((int)substr($d, 5, 2), (int)substr($d, 8, 2), (int)substr($d, 0, 4)));
if (mb_strlen($tag) > 50 || mb_strlen($sup) > 100 || mb_strlen($note) > 255) json_out(['success' => false, 'message' => 'Ada isian yang terlalu panjang.'], 422);
if (!$okDate($buy) || !$okDate($war))                                        json_out(['success' => false, 'message' => 'Format tanggal tidak valid.'], 422);
if ($buy !== '' && $war !== '' && $war < $buy)                               json_out(['success' => false, 'message' => 'Garansi berakhir tidak boleh sebelum tanggal beli.'], 422);

try {
    db()->prepare('UPDATE pcs SET asset_tag=?, purchase_date=?, warranty_until=?, supplier=?, asset_notes=? WHERE id=?')
        ->execute([$tag ?: null, $buy ?: null, $war ?: null, $sup ?: null, $note ?: null, $pc['id']]);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') json_out(['success' => false, 'message' => 'Nomor inventaris "' . $tag . '" sudah dipakai PC lain.'], 422);
    throw $e;
}
log_activity('asset', "{$pc['lab_name']} • PC {$pc['pc_number']}", 'Data aset diperbarui' . ($tag ? " ({$tag})" : ''), (int)$pc['lab_id'], (int)$pc['id']);
json_out(['success' => true]);

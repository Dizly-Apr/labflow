<?php
// Dipanggil stock_opname.js. POST JSON {name, category, description, qty}
require_once __DIR__ . '/auth.php';
require_editor_json();

$in   = json_decode(file_get_contents('php://input'), true) ?: [];
$name = trim($in['name'] ?? '');
$cat  = trim($in['category'] ?? '');
$desc = trim($in['description'] ?? '');
$qty  = (int)($in['qty'] ?? 0);
$min  = (isset($in['min']) && $in['min'] !== '' && $in['min'] !== null) ? (int)$in['min'] : null;   // stok minimum (opsional)

if ($name === '' || $cat === '' || $qty < 1) {
    json_out(['success' => false, 'message' => 'Nama, kategori, dan jumlah (min. 1) wajib diisi.'], 422);
}
if ($min !== null && ($min < 0 || $min > 100000)) {
    json_out(['success' => false, 'message' => 'Stok minimum harus antara 0 dan 100000.'], 422);
}

$pdo = db();
$pdo->beginTransaction();
try {
    $pdo->prepare('INSERT IGNORE INTO stock_categories (name) VALUES (?)')->execute([$cat]);
    $q = $pdo->prepare('SELECT id FROM stock_categories WHERE name=?');
    $q->execute([$cat]);
    $catId = (int)$q->fetchColumn();

    // barang sudah ada -> tambah stok, belum ada -> buat baru
    $q = $pdo->prepare('SELECT id FROM stock_items WHERE name=? FOR UPDATE');
    $q->execute([$name]);
    $itemId = $q->fetchColumn();
    if ($itemId) {
        // deskripsi lama tidak ditimpa dengan kosong; stok minimum hanya berubah bila diisi
        $pdo->prepare('UPDATE stock_items SET quantity=quantity+?, category_id=?, description=IF(?=\'\', description, ?), min_quantity=COALESCE(?, min_quantity) WHERE id=?')
            ->execute([$qty, $catId, $desc, $desc, $min, $itemId]);
    } else {
        $pdo->prepare('INSERT INTO stock_items (name,category_id,description,quantity,min_quantity) VALUES (?,?,?,?,?)')
            ->execute([$name, $catId, $desc, $qty, $min ?? 5]);
        $itemId = $pdo->lastInsertId();
    }
    $pdo->prepare("INSERT INTO stock_transactions (item_id,type,quantity,notes,user_id) VALUES (?,'in',?,?,?)")
        ->execute([$itemId, $qty, $desc, $_SESSION['user_id']]);
    log_activity('stock_in', 'Stock Opname Received', "{$qty} {$name} received in inventory");
    $pdo->commit();
    json_out(['success' => true]);
} catch (Throwable $ex) {
    $pdo->rollBack();
    json_out(['success' => false, 'message' => $ex->getMessage()], 500);
}

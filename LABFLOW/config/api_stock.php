<?php
// Aksi stok selain barang masuk. POST JSON {action: out|delete|update, id, qty?, name?, category?, description?}
require_once __DIR__ . '/auth.php';
require_editor_json();

$in     = json_decode(file_get_contents('php://input'), true) ?: [];
$id     = (int)($in['id'] ?? 0);
$pdo    = db();
$pdo->beginTransaction();
try {
    $s = $pdo->prepare('SELECT * FROM stock_items WHERE id=? FOR UPDATE');
    $s->execute([$id]);
    $it = $s->fetch();
    if (!$it) throw new RuntimeException('Barang tidak ditemukan.');

    switch ($in['action'] ?? '') {
    case 'out':
        $qty = (int)($in['qty'] ?? 0);
        if ($qty < 1 || $qty > $it['quantity']) throw new RuntimeException('Jumlah keluar tidak valid (stok tersedia: ' . $it['quantity'] . ').');
        $pdo->prepare('UPDATE stock_items SET quantity=quantity-? WHERE id=?')->execute([$qty, $id]);
        $pdo->prepare("INSERT INTO stock_transactions (item_id,type,quantity,notes,user_id) VALUES (?,'out',?,?,?)")
            ->execute([$id, $qty, trim($in['notes'] ?? 'Barang keluar'), $_SESSION['user_id']]);
        log_activity('stock_out', 'Stock Out', "{$qty} {$it['name']} dikeluarkan dari inventaris");
        break;

    case 'update':
        $name = trim($in['name'] ?? ''); $cat = trim($in['category'] ?? '');
        if ($name === '' || $cat === '') throw new RuntimeException('Nama dan kategori wajib diisi.');
        $pdo->prepare('INSERT IGNORE INTO stock_categories (name) VALUES (?)')->execute([$cat]);
        $c = $pdo->prepare('SELECT id FROM stock_categories WHERE name=?'); $c->execute([$cat]);
        $min = isset($in['min']) && $in['min'] !== '' ? (int)$in['min'] : (int)$it['min_quantity'];
        if ($min < 0 || $min > 100000) throw new RuntimeException('Stok minimum harus antara 0 dan 100000.');
        $pdo->prepare('UPDATE stock_items SET name=?, category_id=?, description=?, min_quantity=? WHERE id=?')
            ->execute([$name, $c->fetchColumn(), trim($in['description'] ?? ''), $min, $id]);
        break;

    case 'delete':
        $pdo->prepare('DELETE FROM stock_items WHERE id=?')->execute([$id]);
        log_activity('stock_out', 'Barang dihapus', $it['name']);
        break;

    default:
        throw new RuntimeException('Aksi tidak dikenal.');
    }
    $pdo->commit();
    json_out(['success' => true]);
} catch (Throwable $ex) {
    $pdo->rollBack();
    json_out(['success' => false, 'message' => $ex->getMessage()], 422);
}

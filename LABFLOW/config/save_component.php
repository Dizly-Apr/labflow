<?php
/**
 * Simpan / tambah / hapus komponen PC. Dipanggil assets/js/pc.js
 * POST JSON {action:'save'|'delete', lab:'rpl', pc:7, type:'gpu', status, spec, desc, priority, reason}
 *  - komponen belum ada  -> dibuat (riwayat: add)
 *  - spesifikasi berubah -> riwayat penggantian (replace)
 *  - status ISSUE        -> tiket perbaikan dibuat otomatis bila belum ada tiket terbuka
 *  - status ONLINE       -> tiket terbuka untuk komponen itu otomatis diselesaikan
 */
require_once __DIR__ . '/tickets.php';
require_editor_json();

$in     = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $in['action'] ?? 'save';
$type   = (string)($in['type'] ?? '');
$status = $in['status'] ?? '';
$spec   = trim((string)($in['spec'] ?? ''));
$desc   = trim((string)($in['desc'] ?? ''));
$reason = trim((string)($in['reason'] ?? ''));
$prio   = isset(TICKET_PRIORITY[$in['priority'] ?? '']) ? $in['priority'] : 'medium';
$pcNo   = (int)($in['pc'] ?? 0);
$uid    = (int)$_SESSION['user_id'];

if (!is_component_type($type) || !$pcNo || !in_array($action, ['save', 'delete'], true)) {
    json_out(['success' => false, 'message' => 'Data tidak valid.'], 422);
}
if ($action === 'save' && (!in_array($status, ['online', 'issue'], true) || $spec === '' || mb_strlen($spec) > 250 || mb_strlen($desc) > 1000 || mb_strlen($reason) > 250)) {
    json_out(['success' => false, 'message' => 'Spesifikasi wajib diisi (maks. 250 karakter) dan status harus ONLINE atau ISSUE.'], 422);
}

$pc = find_pc((string)($in['lab'] ?? ''), $pcNo);
if (!$pc) {
    json_out(['success' => false, 'message' => 'PC tidak ditemukan.'], 404);
}

$pdo = db();
$pdo->beginTransaction();
try {
    $s = $pdo->prepare('SELECT * FROM pc_components WHERE pc_id=? AND component_type=? FOR UPDATE');
    $s->execute([$pc['id'], $type]);
    $cur   = $s->fetch();
    $title = "{$pc['lab_name']} • PC {$pcNo}";
    $label = component_label($type);

    if ($action === 'delete') {
        if (!$cur) { throw new RuntimeException('Komponen tidak ditemukan.'); }
        $pdo->prepare("UPDATE lab_reports SET status='resolved', resolved_at=NOW(), resolution_note='Komponen dihapus dari PC'
                       WHERE component_id=? AND status<>'resolved'")->execute([$cur['id']]);
        $pdo->prepare('INSERT INTO component_history (pc_id,component_type,action,old_spec,reason,user_id) VALUES (?,?,?,?,?,?)')
            ->execute([$pc['id'], $type, 'remove', $cur['specification'], $reason ?: null, $uid]);
        $pdo->prepare('DELETE FROM pc_components WHERE id=?')->execute([$cur['id']]);
        log_activity('pc_update', $title, "{$label} dihapus: {$cur['specification']}", (int)$pc['lab_id'], (int)$pc['id']);
        $pdo->commit();
        json_out(['success' => true]);
    }

    if ($status === 'online' && $desc === '') { $desc = 'Normal'; }

    if ($cur) {
        $cid = (int)$cur['id'];
        $pdo->prepare('UPDATE pc_components SET specification=?, status=?, report=?, updated_by=? WHERE id=?')
            ->execute([$spec, $status, $desc, $uid, $cid]);
        if ($cur['specification'] !== $spec) {
            $pdo->prepare('INSERT INTO component_history (pc_id,component_type,action,old_spec,new_spec,reason,user_id) VALUES (?,?,?,?,?,?,?)')
                ->execute([$pc['id'], $type, 'replace', $cur['specification'], $spec, $reason ?: null, $uid]);
        }
    } else {
        $pdo->prepare('INSERT INTO pc_components (pc_id,component_type,specification,status,report,updated_by) VALUES (?,?,?,?,?,?)')
            ->execute([$pc['id'], $type, $spec, $status, $desc, $uid]);
        $cid = (int)$pdo->lastInsertId();
        $pdo->prepare('INSERT INTO component_history (pc_id,component_type,action,new_spec,reason,user_id) VALUES (?,?,?,?,?,?)')
            ->execute([$pc['id'], $type, 'add', $spec, $reason ?: null, $uid]);
    }

    $open = $pdo->prepare("SELECT * FROM lab_reports WHERE (component_id=? OR (pc_id=? AND component_type=?)) AND status<>'resolved'");
    $open->execute([$cid, $pc['id'], $type]);
    $openTickets = $open->fetchAll();

    if ($status === 'issue') {
        if (!$openTickets) {
            ticket_create($pdo, ['pc_id' => $pc['id'], 'component_id' => $cid, 'component_type' => $type,
                                 'description' => $desc ?: 'Issue', 'priority' => $prio, 'user_id' => $uid]);
        } else {
            foreach ($openTickets as $t) {
                if (!$t['component_id']) {
                    $pdo->prepare('UPDATE lab_reports SET component_id=? WHERE id=?')->execute([$cid, $t['id']]);
                }
            }
        }
        log_activity('pc_issue', $title, "{$label}: " . ($desc ?: 'Issue'), (int)$pc['lab_id'], (int)$pc['id']);
    } else {
        foreach ($openTickets as $t) {
            $full = ticket_get($pdo, (int)$t['id'], true);
            ticket_set_status($pdo, $full, 'resolved', 'Komponen ditandai ONLINE', $uid, false);
        }
        log_activity('pc_update', $title, "{$label} diperbarui: {$desc}", (int)$pc['lab_id'], (int)$pc['id']);
    }
    $pdo->commit();
    json_out(['success' => true]);
} catch (Throwable $ex) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    json_out(['success' => false, 'message' => $ex->getMessage()], 500);
}

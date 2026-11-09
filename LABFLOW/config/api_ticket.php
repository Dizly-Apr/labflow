<?php
/**
 * Tiket perbaikan (admin & koordinator).  POST JSON:
 *   {action:'create', lab, pc, type?, description, priority?}
 *   {action:'update', id, status?, priority?, technician?, note?, resolution?}
 */
require_once __DIR__ . '/tickets.php';
require_editor_json();

$in     = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $in['action'] ?? '';
$uid    = (int)$_SESSION['user_id'];
$pdo    = db();

function tk_fail(string $m, int $c = 422): void { json_out(['success' => false, 'message' => $m], $c); }

$pdo->beginTransaction();
try {
    if ($action === 'create') {
        $pc   = find_pc((string)($in['lab'] ?? ''), (int)($in['pc'] ?? 0));
        $desc = trim((string)($in['description'] ?? ''));
        $type = (string)($in['type'] ?? '');
        if (!$pc)                                  tk_fail('PC tidak ditemukan.', 404);
        if ($desc === '' || mb_strlen($desc) > 255) tk_fail('Deskripsi kerusakan wajib diisi (maks. 255 karakter).');
        if ($type !== '' && !is_component_type($type)) tk_fail('Jenis komponen tidak valid.');
        $cid = null;
        if ($type !== '') {
            $q = $pdo->prepare('SELECT id FROM pc_components WHERE pc_id=? AND component_type=?');
            $q->execute([$pc['id'], $type]);
            $cid = $q->fetchColumn() ?: null;
        }
        $id = ticket_create($pdo, ['pc_id' => $pc['id'], 'component_id' => $cid, 'component_type' => $type ?: null,
                                   'description' => $desc, 'priority' => $in['priority'] ?? 'medium', 'user_id' => $uid]);
        log_activity('ticket', "Tiket #{$id} • {$pc['lab_name']} PC {$pc['pc_number']}", 'Tiket baru: ' . $desc, (int)$pc['lab_id'], (int)$pc['id']);
        $pdo->commit();
        json_out(['success' => true, 'id' => $id]);
    }

    if ($action === 'update') {
        $t = ticket_get($pdo, (int)($in['id'] ?? 0), true);
        if (!$t) tk_fail('Tiket tidak ditemukan.', 404);

        if (isset($in['priority']) && $in['priority'] !== $t['priority']) {
            if (!isset(TICKET_PRIORITY[$in['priority']])) tk_fail('Prioritas tidak valid.');
            $pdo->prepare('UPDATE lab_reports SET priority=? WHERE id=?')->execute([$in['priority'], $t['id']]);
            ticket_event($pdo, (int)$t['id'], 'priority', 'Prioritas: ' . ticket_priority_label($t['priority']) . ' → ' . ticket_priority_label($in['priority']), $uid);
        }
        if (array_key_exists('technician', $in)) {
            $tech = trim((string)$in['technician']);
            if (mb_strlen($tech) > 100) tk_fail('Nama teknisi terlalu panjang.');
            if ($tech !== (string)$t['technician']) {
                $pdo->prepare('UPDATE lab_reports SET technician=? WHERE id=?')->execute([$tech !== '' ? $tech : null, $t['id']]);
                ticket_event($pdo, (int)$t['id'], 'technician', $tech !== '' ? 'Teknisi penanggung jawab: ' . $tech : 'Teknisi dilepas', $uid);
            }
        }
        $note = trim((string)($in['note'] ?? ''));
        if (mb_strlen($note) > 400) tk_fail('Catatan terlalu panjang (maks. 400 karakter).');
        if (isset($in['status']) && $in['status'] !== $t['status']) {
            ticket_set_status($pdo, $t, (string)$in['status'], $note, $uid);
        } elseif ($note !== '') {
            ticket_event($pdo, (int)$t['id'], 'note', $note, $uid);
        }
        $pdo->commit();
        json_out(['success' => true]);
    }

    tk_fail('Aksi tidak dikenal.');
} catch (Throwable $ex) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    json_out(['success' => false, 'message' => $ex->getMessage()], 500);
}

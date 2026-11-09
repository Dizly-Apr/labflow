<?php
/**
 * Laporan User -> Koordinator & Admin.  POST JSON:
 *   {action:'create',    lab, pc?, category, priority, subject, message}   role User
 *   {action:'update',    id, status?, response?}                           Koordinator & Admin
 *   {action:'to_ticket', id}                                               Koordinator & Admin (butuh PC)
 *   {action:'delete',    id}                                               Admin
 * Token CSRF diperiksa otomatis oleh auth.php.
 */
require_once __DIR__ . '/user_reports.php';
require_login_json();

$in     = json_decode(file_get_contents('php://input'), true) ?: [];
$action = (string)($in['action'] ?? '');
$uid    = (int)$_SESSION['user_id'];
$pdo    = db();

function ur_fail(string $m, int $c = 422): void { json_out(['success' => false, 'message' => $m], $c); }

$pdo->beginTransaction();
try {
    /* ---------------------------------------------------------------- kirim laporan (User) */
    if ($action === 'create') {
        require_reporter_json();

        $pc      = null;
        $lab     = get_lab(preg_replace('/[^a-z0-9_-]/i', '', (string)($in['lab'] ?? '')));
        $pcNo    = (int)($in['pc'] ?? 0);
        $cat     = (string)($in['category'] ?? '');
        $prio    = (string)($in['priority'] ?? 'medium');
        $subject = trim((string)($in['subject'] ?? ''));
        $message = trim((string)($in['message'] ?? ''));

        if (!$lab)                                          ur_fail('Pilih laboratorium yang dilaporkan.');
        if ($pcNo > 0) {
            $pc = find_pc($lab['code'], $pcNo);
            if (!$pc)                                       ur_fail('PC ' . $pcNo . ' tidak ada di ' . $lab['name'] . '.');
        }
        if (!isset(UR_CATEGORY[$cat]))                      ur_fail('Jenis laporan tidak valid.');
        if (!isset(UR_PRIORITY[$prio]))                     ur_fail('Tingkat urgensi tidak valid.');
        if (mb_strlen($subject) < 3 || mb_strlen($subject) > 150)  ur_fail('Judul laporan 3–150 karakter.');
        if (mb_strlen($message) < 5 || mb_strlen($message) > 1000) ur_fail('Penjelasan laporan 5–1000 karakter.');

        // pembatasan: maks. 10 laporan / 10 menit / akun (cegah spam ke kotak masuk petugas)
        if (rl_count('user_report', (string)$uid, 600) >= 10) {
            ur_fail('Terlalu banyak laporan dalam waktu singkat. Coba lagi dalam beberapa menit.', 429);
        }

        $pdo->prepare(
            'INSERT INTO user_reports (lab_id,pc_id,category,priority,subject,message,reported_by,reporter_name,reporter_seen)
             VALUES (?,?,?,?,?,?,?,?,1)'
        )->execute([$lab['id'], $pc['id'] ?? null, $cat, $prio, $subject, $message, $uid, current_user_name()]);
        $id = (int)$pdo->lastInsertId();

        ur_event($pdo, $id, 'created', 'Laporan dikirim ke Koordinator & Admin', $uid);
        log_activity('user_report', "Laporan #{$id} • {$lab['name']}" . ($pc ? " PC {$pcNo}" : ''),
                     current_user_name() . ': ' . mb_substr($subject, 0, 150), (int)$lab['id'], $pc ? (int)$pc['id'] : null);
        $pdo->commit();
        rl_hit('user_report', (string)$uid);
        json_out(['success' => true, 'id' => $id]);
    }

    /* ---------------------------------------------------------------- aksi petugas */
    if (!in_array($action, ['update', 'to_ticket', 'delete'], true)) {
        ur_fail('Aksi tidak dikenal.');
    }
    if ($action === 'delete') {
        if (!is_admin()) ur_fail('Hanya Admin yang dapat menghapus laporan.', 403);
    } elseif (!can_edit()) {
        ur_fail('Hanya Koordinator & Admin yang dapat menanggapi laporan.', 403);
    }

    $r = ur_get($pdo, (int)($in['id'] ?? 0), true);
    if (!$r) ur_fail('Laporan tidak ditemukan.', 404);
    $title = "Laporan #{$r['id']} • {$r['lab_name']}" . ($r['pc_number'] ? " PC {$r['pc_number']}" : '');

    if ($action === 'delete') {
        $pdo->prepare('DELETE FROM user_reports WHERE id = ?')->execute([$r['id']]);
        log_activity('user_report', $title . ' dihapus', mb_substr($r['subject'], 0, 150), (int)$r['lab_id'], $r['pc_id'] ? (int)$r['pc_id'] : null);
        $pdo->commit();
        json_out(['success' => true]);
    }

    if ($action === 'to_ticket') {
        if (!$r['pc_id'])    ur_fail('Laporan ini tidak menyebut PC tertentu, jadi belum bisa dijadikan tiket.');
        if ($r['ticket_id']) ur_fail('Laporan ini sudah dijadikan Tiket #' . (int)$r['ticket_id'] . '.');
        $tid = ticket_create($pdo, [
            'pc_id' => $r['pc_id'], 'description' => $r['subject'], 'priority' => $r['priority'],
            'user_id' => $uid,
        ]);
        ticket_event($pdo, $tid, 'note', 'Dari laporan user #' . $r['id'] . ' (' . ur_reporter($r) . '): ' . mb_substr($r['message'], 0, 400), $uid);
        $sets = 'ticket_id = ?, handled_by = ?, handled_at = NOW(), reporter_seen = 0';
        $args = [$tid, $uid];
        if (in_array($r['status'], ['new', 'read'], true)) {
            $sets .= ", status = 'processing'";
            ur_event($pdo, (int)$r['id'], 'status', ur_status_label($r['status']) . ' → ' . ur_status_label('processing'), $uid);
        }
        $pdo->prepare("UPDATE user_reports SET $sets WHERE id = ?")->execute([...$args, $r['id']]);
        ur_event($pdo, (int)$r['id'], 'ticket', 'Dijadikan Tiket Perbaikan #' . $tid, $uid);
        log_activity('ticket', "Tiket #{$tid} • {$r['lab_name']} PC {$r['pc_number']}", 'Dari laporan user #' . $r['id'] . ': ' . mb_substr($r['subject'], 0, 150), (int)$r['lab_id'], (int)$r['pc_id']);
        $pdo->commit();
        json_out(['success' => true, 'ticket_id' => $tid]);
    }

    /* update: status dan/atau tanggapan */
    $to   = isset($in['status']) ? (string)$in['status'] : $r['status'];
    $resp = trim((string)($in['response'] ?? ''));
    if (!isset(UR_STATUS[$to]))     ur_fail('Status tidak valid.');
    if (mb_strlen($resp) > 500)     ur_fail('Tanggapan terlalu panjang (maks. 500 karakter).');
    $changed = $to !== $r['status'];
    if (!$changed && $resp === '')  ur_fail('Tidak ada perubahan. Ubah status atau tulis tanggapan.');

    $sets = ['handled_by = ?', 'handled_at = NOW()', 'reporter_seen = 0', 'status = ?', 'resolved_at = ' . ($to === 'resolved' ? 'IF(resolved_at IS NULL, NOW(), resolved_at)' : 'NULL')];
    $args = [$uid, $to];
    if ($resp !== '') { $sets[] = 'response_note = ?'; $args[] = $resp; }
    $pdo->prepare('UPDATE user_reports SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute([...$args, $r['id']]);

    if ($changed) { ur_event($pdo, (int)$r['id'], 'status', ur_status_label($r['status']) . ' → ' . ur_status_label($to), $uid); }
    if ($resp !== '') { ur_event($pdo, (int)$r['id'], 'response', 'Tanggapan: ' . $resp, $uid); }
    log_activity('user_report', $title, ($changed ? ur_status_label($r['status']) . ' → ' . ur_status_label($to) : 'Tanggapan ditambahkan'), (int)$r['lab_id'], $r['pc_id'] ? (int)$r['pc_id'] : null);
    $pdo->commit();
    json_out(['success' => true]);
} catch (Throwable $ex) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    json_out(['success' => false, 'message' => 'Terjadi kesalahan: ' . $ex->getMessage()], 500);
}

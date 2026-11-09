<?php
/**
 * Tiket perbaikan (tabel lab_reports) + riwayatnya (report_events).
 * Alur status: open -> in_progress -> waiting_part -> resolved
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/components.php';

const TICKET_STATUS = [
    'open'         => ['Open',               'blue'],
    'in_progress'  => ['Diproses',           'yellow'],
    'waiting_part' => ['Menunggu Sparepart', 'orange'],
    'resolved'     => ['Selesai',            'green'],
];
const TICKET_PRIORITY = [
    'low'      => ['Rendah', 'gray'],
    'medium'   => ['Sedang', 'blue'],
    'high'     => ['Tinggi', 'orange'],
    'critical' => ['Kritis', 'red'],
];

function ticket_status_label(string $s): string   { return TICKET_STATUS[$s][0] ?? $s; }
function ticket_priority_label(string $p): string { return TICKET_PRIORITY[$p][0] ?? $p; }

function ticket_badge(string $status): string
{
    [$l, $c] = TICKET_STATUS[$status] ?? [$status, 'gray'];
    return '<span class="tk-badge tk-' . e($c) . '">' . e($l) . '</span>';
}

function ticket_prio_badge(string $prio): string
{
    [$l, $c] = TICKET_PRIORITY[$prio] ?? [$prio, 'gray'];
    return '<span class="tk-prio tk-' . e($c) . '">' . e($l) . '</span>';
}

/** Catat satu baris riwayat tiket. */
function ticket_event(PDO $pdo, int $reportId, string $action, ?string $detail, ?int $userId = null, ?string $actor = null): void
{
    $pdo->prepare('INSERT INTO report_events (report_id,action,detail,user_id,actor_name) VALUES (?,?,?,?,?)')
        ->execute([$reportId, $action, $detail !== null ? mb_substr($detail, 0, 500) : null, $userId, $actor]);
}

/** Baris tiket + PC + lab (untuk log & sinkronisasi). */
function ticket_get(PDO $pdo, int $id, bool $lock = false): ?array
{
    $s = $pdo->prepare(
        'SELECT r.*, p.pc_number, p.laboratory_id, l.code AS lab_code, l.name AS lab_name
         FROM lab_reports r JOIN pcs p ON p.id=r.pc_id JOIN laboratories l ON l.id=p.laboratory_id
         WHERE r.id=?' . ($lock ? ' FOR UPDATE' : '')
    );
    $s->execute([$id]);
    return $s->fetch() ?: null;
}

/**
 * Buat tiket baru.
 * $d: pc_id, description, [component_id], [component_type], [priority], [source], [reporter_name], [user_id]
 */
function ticket_create(PDO $pdo, array $d): int
{
    $prio = isset(TICKET_PRIORITY[$d['priority'] ?? '']) ? $d['priority'] : 'medium';
    $pdo->prepare(
        'INSERT INTO lab_reports (pc_id,component_id,component_type,description,priority,source,reporter_name,reported_by)
         VALUES (?,?,?,?,?,?,?,?)'
    )->execute([
        $d['pc_id'], $d['component_id'] ?? null, $d['component_type'] ?? null,
        mb_substr($d['description'], 0, 255), $prio,
        ($d['source'] ?? 'staff') === 'public' ? 'public' : 'staff',
        $d['reporter_name'] ?? null, $d['user_id'] ?? null,
    ]);
    $id = (int)$pdo->lastInsertId();
    ticket_event($pdo, $id, 'created', 'Tiket dibuat: ' . $d['description'], $d['user_id'] ?? null, $d['reporter_name'] ?? null);
    return $id;
}

/**
 * Ubah status tiket + sinkronkan status komponen.
 *  - in_progress / waiting_part : komponen terkait ditandai ISSUE (bila masih ONLINE)
 *  - resolved                   : komponen kembali ONLINE bila tidak ada tiket lain yang masih terbuka
 *  - buka kembali (dari resolved): komponen ISSUE lagi
 * $syncComponent = false dipakai save_component.php, yang sudah mengatur komponen sendiri.
 */
function ticket_set_status(PDO $pdo, array $t, string $to, ?string $note, ?int $userId, bool $syncComponent = true): void
{
    if (!isset(TICKET_STATUS[$to])) {
        throw new RuntimeException('Status tiket tidak valid.');
    }
    $from = $t['status'];
    if ($from === $to) {
        return;
    }
    $note = $note !== null ? trim($note) : null;

    if ($to === 'resolved') {
        $pdo->prepare("UPDATE lab_reports SET status='resolved', resolved_at=NOW(), resolution_note=? WHERE id=?")
            ->execute([$note !== '' ? $note : null, $t['id']]);
    } else {
        $pdo->prepare('UPDATE lab_reports SET status=?, resolved_at=NULL WHERE id=?')->execute([$to, $t['id']]);
    }
    ticket_event($pdo, (int)$t['id'], 'status', ticket_status_label($from) . ' → ' . ticket_status_label($to) . ($note ? ' — ' . $note : ''), $userId);

    if ($syncComponent) {
        $cid = $t['component_id'] ? (int)$t['component_id'] : 0;
        if (!$cid && $t['component_type']) {      // tiket publik: cari komponennya sekarang
            $q = $pdo->prepare('SELECT id FROM pc_components WHERE pc_id=? AND component_type=?');
            $q->execute([$t['pc_id'], $t['component_type']]);
            $cid = (int)$q->fetchColumn();
            if ($cid) {
                $pdo->prepare('UPDATE lab_reports SET component_id=? WHERE id=?')->execute([$cid, $t['id']]);
            }
        }
        if ($cid) {
            if ($to === 'resolved') {
                $o = $pdo->prepare("SELECT COUNT(*) FROM lab_reports WHERE component_id=? AND status<>'resolved' AND id<>?");
                $o->execute([$cid, $t['id']]);
                if ((int)$o->fetchColumn() === 0) {
                    $pdo->prepare("UPDATE pc_components SET status='online', report='Normal', updated_by=? WHERE id=?")->execute([$userId, $cid]);
                }
            } else {
                $pdo->prepare("UPDATE pc_components SET status='issue', report=?, updated_by=? WHERE id=? AND status='online'")
                    ->execute([mb_substr($t['description'], 0, 250), $userId, $cid]);
            }
        }
    }

    $uid = $userId;
    $pdo->prepare('INSERT INTO activity_logs (type,title,description,laboratory_id,pc_id,user_id) VALUES (?,?,?,?,?,?)')
        ->execute(['ticket', "Tiket #{$t['id']} • {$t['lab_name']} PC {$t['pc_number']}",
                   ticket_status_label($from) . ' → ' . ticket_status_label($to), $t['laboratory_id'], $t['pc_id'], $uid]);
}

<?php
/**
 * Pembuat data laporan (dipakai laporan.php untuk pratinjau HTML, Excel, dan PDF).
 * Setiap fungsi mengembalikan:
 *   ['title', 'subtitle', 'summary' => [[label, nilai]...],
 *    'tables' => [ ['title', 'cols' => [[label, lebar_mm, 'L'|'R'|'C']...], 'rows' => [[...]...], 'total' => [...]|null ] ]]
 */
require_once __DIR__ . '/tickets.php';

function rep_date(?string $d, string $fallback): string
{
    return ($d && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && checkdate((int)substr($d, 5, 2), (int)substr($d, 8, 2), (int)substr($d, 0, 4))) ? $d : $fallback;
}

function rep_hours(?float $h): string
{
    if ($h === null) return '-';
    if ($h < 1) return max(1, (int)round($h * 60)) . ' menit';
    return $h >= 48 ? round($h / 24, 1) . ' hari' : round($h, 1) . ' jam';
}

/** 1. Rekap kerusakan per lab pada rentang tanggal. */
function report_kerusakan(string $from, string $to, string $labCode = ''): array
{
    $pdo  = db();
    $e    = date('Y-m-d', strtotime($to . ' +1 day'));
    $labW = $labCode !== '' ? ' AND l.code = ?' : '';

    $args = [$from, $e];
    if ($labCode !== '') { $args[] = $labCode; }
    $s = $pdo->prepare(
        "SELECT l.id, l.name,
                (SELECT COUNT(*) FROM pcs WHERE laboratory_id = l.id) AS total_pc,
                (SELECT COUNT(*) FROM v_pc_status v WHERE v.laboratory_id = l.id AND v.status = 'issue' COLLATE utf8mb4_unicode_ci) AS pc_issue,
                COUNT(r.id) AS tiket, COALESCE(SUM(r.status = 'resolved'), 0) AS selesai,
                COALESCE(SUM(r.status <> 'resolved'), 0) AS aktif,
                AVG(CASE WHEN r.status = 'resolved' THEN TIMESTAMPDIFF(MINUTE, r.reported_at, r.resolved_at) END) / 60 AS avg_h
         FROM laboratories l
         LEFT JOIN pcs p ON p.laboratory_id = l.id
         LEFT JOIN lab_reports r ON r.pc_id = p.id AND r.reported_at >= ? AND r.reported_at < ?
         WHERE 1=1 $labW
         GROUP BY l.id, l.name ORDER BY l.id"
    );
    $s->execute($args);
    $labs = $s->fetchAll();

    // komponen paling sering bermasalah per lab & keseluruhan
    $c = $pdo->prepare(
        "SELECT p.laboratory_id AS lid, COALESCE(r.component_type, '') AS ct, COUNT(*) AS n
         FROM lab_reports r JOIN pcs p ON p.id = r.pc_id JOIN laboratories l ON l.id = p.laboratory_id
         WHERE r.reported_at >= ? AND r.reported_at < ? $labW GROUP BY p.laboratory_id, ct"
    );
    $c->execute($args);
    $top = []; $byType = [];
    foreach ($c->fetchAll() as $r) {
        $byType[$r['ct']] = ($byType[$r['ct']] ?? 0) + (int)$r['n'];
        if ($r['ct'] !== '' && (!isset($top[$r['lid']]) || $r['n'] > $top[$r['lid']][1])) {
            $top[$r['lid']] = [component_label($r['ct']), (int)$r['n']];
        }
    }
    arsort($byType);

    $rows = []; $tot = ['pc' => 0, 'issue' => 0, 'tk' => 0, 'ok' => 0, 'act' => 0];
    foreach ($labs as $l) {
        $rows[] = [$l['name'], (int)$l['total_pc'], (int)$l['pc_issue'], (int)$l['tiket'], (int)$l['selesai'], (int)$l['aktif'],
                   rep_hours($l['avg_h'] !== null ? (float)$l['avg_h'] : null), $top[$l['id']][0] ?? '-'];
        $tot['pc'] += (int)$l['total_pc']; $tot['issue'] += (int)$l['pc_issue']; $tot['tk'] += (int)$l['tiket'];
        $tot['ok'] += (int)$l['selesai']; $tot['act'] += (int)$l['aktif'];
    }

    $typeRows = [];
    foreach ($byType as $t => $n) { $typeRows[] = [$t === '' ? 'Tidak spesifik / lainnya' : component_label((string)$t), $n]; }

    $d = $pdo->prepare(
        "SELECT r.id, r.reported_at, r.resolved_at, l.name AS lab, p.pc_number, r.component_type, r.description, r.priority, r.status, r.technician
         FROM lab_reports r JOIN pcs p ON p.id = r.pc_id JOIN laboratories l ON l.id = p.laboratory_id
         WHERE r.reported_at >= ? AND r.reported_at < ? $labW ORDER BY r.reported_at DESC, r.id DESC LIMIT 1000"
    );
    $d->execute($args);
    $detail = [];
    foreach ($d->fetchAll() as $r) {
        $end = $r['resolved_at'] ?: null;
        $detail[] = ['#' . $r['id'], ui_date($r['reported_at']), $r['lab'] . ' PC ' . $r['pc_number'],
                     $r['component_type'] ? component_label($r['component_type']) : '-', $r['description'],
                     ticket_priority_label($r['priority']), ticket_status_label($r['status']), $r['technician'] ?: '-',
                     $end ? rep_hours((strtotime($end) - strtotime($r['reported_at'])) / 3600) : '-'];
    }

    $period = ui_date($from) . ' – ' . ui_date($to);
    return [
        'title'    => 'Rekap Kerusakan per Laboratorium',
        'subtitle' => 'Periode ' . $period . ($labCode !== '' && $labs ? ' · ' . $labs[0]['name'] : ''),
        'landscape' => true,
        'summary'  => [['Tiket dibuat', $tot['tk']], ['Selesai', $tot['ok']], ['Masih aktif', $tot['act']], ['PC bermasalah saat ini', $tot['issue']]],
        'tables'   => [
            ['title' => 'Ringkasan per laboratorium',
             'cols' => [['Laboratorium', 42, 'L'], ['Jml PC', 20, 'R'], ['PC bermasalah (kini)', 32, 'R'], ['Tiket', 18, 'R'], ['Selesai', 20, 'R'], ['Aktif', 18, 'R'], ['Rata-rata perbaikan', 36, 'R'], ['Komponen terbanyak', 50, 'L']],
             'rows' => $rows,
             'total' => ['TOTAL', $tot['pc'], $tot['issue'], $tot['tk'], $tot['ok'], $tot['act'], '', '']],
            ['title' => 'Komponen paling sering dilaporkan rusak',
             'cols' => [['Komponen', 90, 'L'], ['Jumlah tiket', 30, 'R']], 'rows' => $typeRows, 'total' => null],
            ['title' => 'Detail tiket pada periode ini',
             'cols' => [['No', 14, 'L'], ['Tanggal', 24, 'L'], ['Lokasi', 34, 'L'], ['Komponen', 28, 'L'], ['Masalah', 70, 'L'], ['Prioritas', 18, 'L'], ['Status', 26, 'L'], ['Teknisi', 28, 'L'], ['Durasi', 18, 'R']],
             'rows' => $detail, 'total' => null],
        ],
    ];
}

/** 2. Stok bulanan: stok awal, masuk, keluar, stok akhir per barang. */
function report_stok(string $month): array
{
    if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) { $month = date('Y-m'); }
    $start = $month . '-01 00:00:00';
    $end   = date('Y-m-d 00:00:00', strtotime($month . '-01 +1 month'));

    $s = db()->prepare(
        "SELECT i.name, c.name AS cat, i.quantity, i.min_quantity,
                COALESCE(SUM(CASE WHEN t.created_at >= ? AND t.created_at < ? AND t.type = 'in'  THEN t.quantity END), 0) AS masuk,
                COALESCE(SUM(CASE WHEN t.created_at >= ? AND t.created_at < ? AND t.type = 'out' THEN t.quantity END), 0) AS keluar,
                COALESCE(SUM(CASE WHEN t.created_at >= ? AND t.type = 'in'  THEN t.quantity END), 0)
              - COALESCE(SUM(CASE WHEN t.created_at >= ? AND t.type = 'out' THEN t.quantity END), 0) AS net_after
         FROM stock_items i JOIN stock_categories c ON c.id = i.category_id
         LEFT JOIN stock_transactions t ON t.item_id = i.id
         GROUP BY i.id, i.name, c.name, i.quantity, i.min_quantity ORDER BY c.name, i.name"
    );
    $s->execute([$start, $end, $start, $end, $end, $end]);
    $rows = []; $sum = ['awal' => 0, 'in' => 0, 'out' => 0, 'akhir' => 0]; $low = 0;
    foreach ($s->fetchAll() as $r) {
        $akhir = (int)$r['quantity'] - (int)$r['net_after'];
        $awal  = $akhir - (int)$r['masuk'] + (int)$r['keluar'];
        if ($akhir === 0 && $awal === 0 && !$r['masuk'] && !$r['keluar']) { continue; }
        $status = $akhir <= 0 ? 'Habis' : ($akhir <= (int)$r['min_quantity'] ? 'Menipis' : 'Aman');
        if ($status !== 'Aman') { $low++; }
        $rows[] = [$r['name'], $r['cat'], $awal, (int)$r['masuk'], (int)$r['keluar'], $akhir, (int)$r['min_quantity'], $status];
        $sum['awal'] += $awal; $sum['in'] += (int)$r['masuk']; $sum['out'] += (int)$r['keluar']; $sum['akhir'] += $akhir;
    }
    $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $label = $bulan[(int)substr($month, 5, 2)] . ' ' . substr($month, 0, 4);

    // rincian transaksi bulan itu
    $t = db()->prepare(
        "SELECT t.created_at, i.name, t.type, t.quantity, t.notes, u.full_name
         FROM stock_transactions t JOIN stock_items i ON i.id = t.item_id LEFT JOIN users u ON u.id = t.user_id
         WHERE t.created_at >= ? AND t.created_at < ? ORDER BY t.created_at, t.id"
    );
    $t->execute([$start, $end]);
    $tx = [];
    foreach ($t->fetchAll() as $r) {
        $tx[] = [ui_date($r['created_at'], true), $r['name'], ['in' => 'Masuk', 'out' => 'Keluar', 'adjust' => 'Penyesuaian'][$r['type']] ?? $r['type'], (int)$r['quantity'], (string)$r['notes'], $r['full_name'] ?: '-'];
    }

    return [
        'title'    => 'Laporan Stok Bulanan',
        'subtitle' => 'Bulan ' . $label,
        'landscape' => true,
        'summary'  => [['Stok awal', $sum['awal']], ['Barang masuk', $sum['in']], ['Barang keluar', $sum['out']], ['Stok akhir', $sum['akhir']], ['Menipis / habis', $low]],
        'tables'   => [
            ['title' => 'Mutasi stok per barang',
             'cols' => [['Nama barang', 66, 'L'], ['Kategori', 44, 'L'], ['Awal', 16, 'R'], ['Masuk', 16, 'R'], ['Keluar', 16, 'R'], ['Akhir', 16, 'R'], ['Min', 14, 'R'], ['Status', 22, 'L']],
             'rows' => $rows, 'total' => ['TOTAL', '', $sum['awal'], $sum['in'], $sum['out'], $sum['akhir'], '', '']],
            ['title' => 'Rincian transaksi bulan ini',
             'cols' => [['Waktu', 34, 'L'], ['Barang', 66, 'L'], ['Jenis', 22, 'L'], ['Jumlah', 16, 'R'], ['Keterangan', 70, 'L'], ['Petugas', 30, 'L']],
             'rows' => $tx, 'total' => null],
        ],
    ];
}

/** 3. Audit log aktivitas pada rentang tanggal. */
function report_audit(string $from, string $to, string $type = ''): array
{
    $labels = ['pc_issue' => 'PC Issue', 'pc_update' => 'PC Update', 'stock_in' => 'Stock In', 'stock_out' => 'Stock Out', 'lab_status' => 'Lab Status',
               'user_mgmt' => 'User', 'login' => 'Login', 'ticket' => 'Tiket', 'asset' => 'Aset', 'system' => 'Sistem', 'user_report' => 'Laporan User'];
    $e = date('Y-m-d', strtotime($to . ' +1 day'));
    $sql = 'SELECT a.created_at, a.type, a.title, a.description, u.full_name FROM activity_logs a LEFT JOIN users u ON u.id = a.user_id
            WHERE a.created_at >= ? AND a.created_at < ?';
    $args = [$from, $e];
    if ($type !== '' && isset($labels[$type])) { $sql .= ' AND a.type = ?'; $args[] = $type; }
    $s = db()->prepare($sql . ' ORDER BY a.created_at DESC, a.id DESC LIMIT 5000');
    $s->execute($args);
    $rows = []; $by = [];
    foreach ($s->fetchAll() as $r) {
        $rows[] = [ui_date($r['created_at'], true), $labels[$r['type']] ?? $r['type'], $r['title'], (string)$r['description'], $r['full_name'] ?: '-'];
        $by[$r['type']] = ($by[$r['type']] ?? 0) + 1;
    }
    $sum = [['Total aktivitas', count($rows)]];
    foreach ($by as $k => $n) { $sum[] = [$labels[$k] ?? $k, $n]; }
    return [
        'title'    => 'Audit Log Aktivitas',
        'subtitle' => 'Periode ' . ui_date($from) . ' – ' . ui_date($to) . ($type !== '' && isset($labels[$type]) ? ' · ' . $labels[$type] : ''),
        'landscape' => true,
        'summary'  => $sum,
        'tables'   => [[
            'title' => 'Daftar aktivitas',
            'cols'  => [['Waktu', 36, 'L'], ['Jenis', 24, 'L'], ['Aktivitas', 62, 'L'], ['Keterangan', 100, 'L'], ['Petugas', 34, 'L']],
            'rows'  => $rows, 'total' => null,
        ]],
    ];
}

function report_labels_audit(): array
{
    return ['pc_issue' => 'PC Issue', 'pc_update' => 'PC Update', 'stock_in' => 'Stock In', 'stock_out' => 'Stock Out', 'lab_status' => 'Lab Status',
            'user_mgmt' => 'User', 'login' => 'Login', 'ticket' => 'Tiket', 'asset' => 'Aset', 'system' => 'Sistem', 'user_report' => 'Laporan User'];
}

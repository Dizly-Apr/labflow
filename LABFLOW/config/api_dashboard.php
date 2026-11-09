<?php
// GET ?months=6|12 -> data grafik "Summary Data Per Month"
require_once __DIR__ . '/auth.php';
require_login_json();

$months = ((int)($_GET['months'] ?? 6)) === 12 ? 12 : 6;
$labels = $report = $stock = [];
$bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

$map = [];
foreach (db()->query('SELECT * FROM v_monthly_summary') as $r) { $map[$r['ym']] = $r; }

for ($i = $months - 1; $i >= 0; $i--) {
    $t = strtotime("first day of -$i month");
    $k = date('Y-m', $t);
    $labels[] = $bulan[(int)date('n', $t)];
    $report[] = (int)($map[$k]['lab_report'] ?? 0);
    $stock[]  = (int)($map[$k]['stock_in'] ?? 0);
}
json_out(['labels' => $labels, 'lab_report' => $report, 'stock_in' => $stock]);

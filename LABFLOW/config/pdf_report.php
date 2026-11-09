<?php
/** Laporan PDF (FPDF) dengan kop sekolah, tabel berpindah halaman otomatis, dan kolom tanda tangan. */
require_once __DIR__ . '/../lib/fpdf/fpdf.php';

class LfPdf extends FPDF
{
    public string $repTitle = '';
    public string $repSub = '';
    private array $hdrCols = [];

    public static function t(string $s): string
    {
        $s = strtr($s, ['→' => '->', '•' => '-', '–' => '-', '—' => '-', '…' => '...', '“' => '"', '”' => '"', '‘' => "'", '’' => "'", "\r" => '', "\n" => ' ']);
        $o = @iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $s);
        return $o === false ? preg_replace('/[^\x20-\x7E]/', '?', $s) : $o;
    }

    public function Header(): void
    {
        $logo = __DIR__ . '/../logo-sekolah.png';
        $x = $this->lMargin;
        if (is_file($logo)) {
            try { $this->Image($logo, $x, 9, 15); $x += 19; } catch (Throwable $e) { }
        }
        $this->SetXY($x, 9);
        $this->SetFont('Helvetica', 'B', 13);
        $this->SetTextColor(22, 88, 184);
        $this->Cell(0, 6, self::t('SMK Bina Informatika'), 0, 2);
        $this->SetFont('Helvetica', '', 9);
        $this->SetTextColor(91, 102, 118);
        $this->Cell(0, 4.5, self::t('LabFlow - Manajemen Laboratorium & Stock Opname'), 0, 2);
        $this->SetXY($this->lMargin, 26);
        $this->SetDrawColor(40, 124, 240);
        $this->SetLineWidth(0.5);
        $this->Line($this->lMargin, 25, $this->w - $this->rMargin, 25);
        $this->SetLineWidth(0.2);
        $this->SetY(29);
        if ($this->PageNo() === 1 || $this->hdrCols) {
            $this->SetTextColor(31, 41, 55);
            $this->SetFont('Helvetica', 'B', 14);
            $this->Cell(0, 7, self::t($this->repTitle), 0, 1);
            $this->SetFont('Helvetica', '', 10);
            $this->SetTextColor(91, 102, 118);
            $this->Cell(0, 5, self::t($this->repSub), 0, 1);
            $this->Ln(3);
        }
    }

    public function Footer(): void
    {
        $this->SetY(-11);
        $this->SetFont('Helvetica', '', 8);
        $this->SetTextColor(138, 148, 163);
        $this->Cell(0, 5, self::t('Dicetak ' . date('d/m/Y H:i') . ' oleh ' . ($GLOBALS['__lf_pdf_user'] ?? '-')), 0, 0, 'L');
        $this->Cell(0, 5, 'Halaman ' . $this->PageNo() . ' / {nb}', 0, 0, 'R');
    }

    /** Daftar kata dibungkus sesuai lebar sel. */
    private function wrap(string $text, float $w): array
    {
        $text = self::t($text);
        $maxW = max(1, $w - 2.4);
        $lines = [];
        foreach (preg_split('/\s+/', trim($text)) ?: [''] as $word) {
            $cur = end($lines) !== false ? array_pop($lines) : '';
            $try = $cur === '' ? $word : $cur . ' ' . $word;
            if ($this->GetStringWidth($try) <= $maxW) { $lines[] = $try; continue; }
            if ($cur !== '') { $lines[] = $cur; }
            while ($this->GetStringWidth($word) > $maxW && strlen($word) > 1) {   // kata terlalu panjang: potong
                $k = strlen($word);
                while ($k > 1 && $this->GetStringWidth(substr($word, 0, $k)) > $maxW) { $k--; }
                $lines[] = substr($word, 0, $k);
                $word = substr($word, $k);
            }
            $lines[] = $word;
        }
        return $lines ?: [''];
    }

    public function summary(array $items): void
    {
        if (!$items) { return; }
        $usable = $this->w - $this->lMargin - $this->rMargin;
        $n = count($items);
        $gap = 3;
        $w = ($usable - $gap * ($n - 1)) / $n;
        $y = $this->GetY();
        foreach ($items as $i => [$label, $val]) {
            $x = $this->lMargin + $i * ($w + $gap);
            $this->SetFillColor(237, 244, 255);
            $this->SetDrawColor(219, 233, 255);
            $this->Rect($x, $y, $w, 15, 'DF');
            $this->SetXY($x, $y + 1.5);
            $this->SetFont('Helvetica', 'B', 14);
            $this->SetTextColor(22, 88, 184);
            $this->Cell($w, 6.5, self::t((string)$val), 0, 2, 'C');
            $this->SetFont('Helvetica', '', 8);
            $this->SetTextColor(91, 102, 118);
            $this->Cell($w, 4.5, self::t((string)$label), 0, 0, 'C');
        }
        $this->SetY($y + 19);
    }

    public function table(array $t): void
    {
        $usable = $this->w - $this->lMargin - $this->rMargin;
        $sum = array_sum(array_column($t['cols'], 1));
        $widths = array_map(fn($c) => $c[1] / $sum * $usable, $t['cols']);

        if ($this->GetY() > $this->h - 40) { $this->AddPage($this->CurOrientation); }
        $this->SetFont('Helvetica', 'B', 11);
        $this->SetTextColor(31, 41, 55);
        $this->Cell(0, 7, self::t($t['title']), 0, 1);
        $this->hdrCols = $t['cols'];

        $head = function () use ($t, $widths) {
            $this->SetFont('Helvetica', 'B', 8.5);
            $this->SetFillColor(40, 124, 240);
            $this->SetTextColor(255, 255, 255);
            $this->SetDrawColor(40, 124, 240);
            $this->SetX($this->lMargin);
            foreach ($t['cols'] as $i => $c) {
                $this->Cell($widths[$i], 7, self::t($c[0]), 1, 0, $c[2] === 'R' ? 'R' : 'L', true);
            }
            $this->Ln();
        };
        $head();

        $rows = $t['rows'];
        if ($t['total']) { $rows[] = ['__total' => true, 'v' => $t['total']]; }
        if (!$rows) {
            $this->SetFont('Helvetica', 'I', 9);
            $this->SetTextColor(138, 148, 163);
            $this->Cell(0, 8, 'Tidak ada data pada periode ini.', 1, 1, 'C');
        }
        $zebra = false;
        foreach ($rows as $row) {
            $isTotal = isset($row['__total']);
            $vals = $isTotal ? $row['v'] : $row;
            $this->SetFont('Helvetica', $isTotal ? 'B' : '', 8.5);
            $cells = []; $maxLines = 1;
            foreach ($t['cols'] as $i => $c) {
                $lines = $this->wrap((string)($vals[$i] ?? ''), $widths[$i]);
                $cells[$i] = $lines;
                $maxLines = max($maxLines, count($lines));
            }
            $h = $maxLines * 4.2 + 2.4;
            if ($this->GetY() + $h > $this->h - 16) {
                $this->AddPage($this->CurOrientation);
                $head();
                $this->SetFont('Helvetica', $isTotal ? 'B' : '', 8.5);
            }
            $y = $this->GetY();
            $x = $this->lMargin;
            $this->SetDrawColor(212, 218, 227);
            if ($isTotal) { $this->SetFillColor(237, 244, 255); } else { $this->SetFillColor($zebra ? 248 : 255, $zebra ? 250 : 255, $zebra ? 253 : 255); }
            $this->SetTextColor(31, 41, 55);
            foreach ($t['cols'] as $i => $c) {
                $this->Rect($x, $y, $widths[$i], $h, 'DF');
                foreach ($cells[$i] as $k => $line) {
                    $this->SetXY($x + 1.2, $y + 1.2 + $k * 4.2);
                    $this->Cell($widths[$i] - 2.4, 4.2, $line, 0, 0, $c[2] === 'R' ? 'R' : 'L');
                }
                $x += $widths[$i];
            }
            $this->SetY($y + $h);
            $zebra = !$zebra;
        }
        $this->Ln(6);
    }

    public function signature(string $name): void
    {
        if ($this->GetY() > $this->h - 52) { $this->AddPage($this->CurOrientation); }
        $x = $this->w - $this->rMargin - 70;
        $this->SetTextColor(31, 41, 55);
        $this->SetFont('Helvetica', '', 10);
        $this->SetX($x);
        $this->Cell(70, 5, self::t('Mengetahui,'), 0, 2, 'C');
        $this->Cell(70, 5, self::t('Koordinator Laboratorium'), 0, 2, 'C');
        $this->Ln(18);
        $this->SetFont('Helvetica', 'B', 10);
        $this->Cell(70, 5, self::t($name), 0, 2, 'C');
        $this->SetDrawColor(31, 41, 55);
        $this->Line($x + 8, $this->GetY(), $x + 62, $this->GetY());
    }
}

/** Bangun file PDF dari dataset laporan dan kirim sebagai unduhan. */
function pdf_output(array $rep, string $filename): void
{
    $GLOBALS['__lf_pdf_user'] = LfPdf::t(current_user_name());
    $pdf = new LfPdf('L', 'mm', 'A4');
    $pdf->SetMargins(12, 12, 12);
    $pdf->SetAutoPageBreak(false);
    $pdf->AliasNbPages();
    $pdf->repTitle = $rep['title'];
    $pdf->repSub   = $rep['subtitle'];
    $pdf->SetTitle(LfPdf::t($rep['title']));
    $pdf->SetAuthor('LabFlow');
    $pdf->AddPage();
    $pdf->summary($rep['summary']);
    foreach ($rep['tables'] as $t) { $pdf->table($t); }
    $pdf->signature(current_user_name());
    $pdf->Output('D', $filename);
    exit;
}

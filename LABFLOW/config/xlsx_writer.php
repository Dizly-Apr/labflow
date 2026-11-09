<?php
/**
 * Penulis .xlsx minimal (tanpa library) memakai ZipArchive.
 * Satu sheet per tabel; baris judul, ringkasan, header tebal, angka tetap berformat angka, lebar kolom otomatis.
 */
class LfXlsx
{
    private array $sheets = [];

    /** $cols = [[label, lebar_mm, 'L'|'R'], ...]; $rows = array nilai; $total = baris total (opsional) */
    public function addSheet(string $name, array $head, array $cols, array $rows, ?array $total = null): void
    {
        $this->sheets[] = compact('name', 'head', 'cols', 'rows', 'total');
    }

    private static function col(int $i): string
    {
        $s = '';
        for ($i++; $i > 0; $i = intdiv($i - 1, 26)) { $s = chr(65 + ($i - 1) % 26) . $s; }
        return $s;
    }

    private static function esc(string $s): string
    {
        $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $s) ?? '';
        return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /** style: 1 judul, 2 sub, 3 header, 4 teks, 5 angka, 6 total teks, 7 total angka */
    private static function cell(int $c, int $r, $v, int $style): string
    {
        $ref = self::col($c) . $r;
        if ($v === '' || $v === null) { return '<c r="' . $ref . '" s="' . $style . '"/>'; }
        if (is_int($v) || is_float($v)) { return '<c r="' . $ref . '" s="' . $style . '"><v>' . $v . '</v></c>'; }
        return '<c r="' . $ref . '" s="' . $style . '" t="inlineStr"><is><t xml:space="preserve">' . self::esc((string)$v) . '</t></is></c>';
    }

    private function sheetXml(array $s): string
    {
        $ncol = count($s['cols']);
        $rowsX = ''; $r = 1;
        foreach ($s['head'] as $i => $line) {
            $rowsX .= '<row r="' . $r . '">' . self::cell(0, $r, $line, $i === 0 ? 1 : 2) . '</row>';
            $r++;
        }
        $r++;
        $hdr = $r;
        $rowsX .= '<row r="' . $r . '" ht="22" customHeight="1">';
        foreach ($s['cols'] as $c => $col) { $rowsX .= self::cell($c, $r, $col[0], 3); }
        $rowsX .= '</row>';
        $r++;
        foreach ($s['rows'] as $row) {
            $rowsX .= '<row r="' . $r . '">';
            foreach ($s['cols'] as $c => $col) { $v = $row[$c] ?? ''; $rowsX .= self::cell($c, $r, $v, is_numeric($v) && !is_string($v) ? 5 : 4); }
            $rowsX .= '</row>';
            $r++;
        }
        if ($s['total']) {
            $rowsX .= '<row r="' . $r . '">';
            foreach ($s['cols'] as $c => $col) { $v = $s['total'][$c] ?? ''; $rowsX .= self::cell($c, $r, $v, is_int($v) || is_float($v) ? 7 : 6); }
            $rowsX .= '</row>';
            $r++;
        }
        $cols = '<cols>';
        foreach ($s['cols'] as $c => $col) {
            $w = max(10, min(60, round($col[1] * 0.55 + 2)));
            $cols .= '<col min="' . ($c + 1) . '" max="' . ($c + 1) . '" width="' . $w . '" customWidth="1"/>';
        }
        $cols .= '</cols>';
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetViews><sheetView workbookViewId="0" showGridLines="0"><pane ySplit="' . $hdr . '" topLeftCell="A' . ($hdr + 1) . '" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . $cols . '<sheetData>' . $rowsX . '</sheetData>'
            . '<autoFilter ref="A' . $hdr . ':' . self::col($ncol - 1) . max($hdr, $r - 1 - ($s['total'] ? 1 : 0)) . '"/>'
            . '<pageSetup orientation="landscape" fitToHeight="0"/></worksheet>';
    }

    private const STYLES = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        . '<fonts count="5"><font><sz val="11"/><name val="Calibri"/></font>'
        . '<font><b/><sz val="15"/><color rgb="FF1658B8"/><name val="Calibri"/></font>'
        . '<font><sz val="10"/><color rgb="FF5B6676"/><name val="Calibri"/></font>'
        . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
        . '<font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
        . '<fills count="4"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
        . '<fill><patternFill patternType="solid"><fgColor rgb="FF287CF0"/><bgColor indexed="64"/></patternFill></fill>'
        . '<fill><patternFill patternType="solid"><fgColor rgb="FFEDF4FF"/><bgColor indexed="64"/></patternFill></fill></fills>'
        . '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border>'
        . '<border><left style="thin"><color rgb="FFD4DAE3"/></left><right style="thin"><color rgb="FFD4DAE3"/></right><top style="thin"><color rgb="FFD4DAE3"/></top><bottom style="thin"><color rgb="FFD4DAE3"/></bottom><diagonal/></border></borders>'
        . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
        . '<cellXfs count="8">'
        . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
        . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
        . '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
        . '<xf numFmtId="0" fontId="3" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
        . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'
        . '<xf numFmtId="3" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment vertical="top" horizontal="right"/></xf>'
        . '<xf numFmtId="0" fontId="4" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>'
        . '<xf numFmtId="3" fontId="4" fillId="3" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right"/></xf>'
        . '</cellXfs></styleSheet>';

    public function save(string $path): void
    {
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) { throw new RuntimeException('Tidak bisa membuat file xlsx.'); }
        $ct = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        $wb = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>';
        $rel = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        $used = [];
        foreach ($this->sheets as $i => $s) {
            $n = $i + 1;
            $name = trim(preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', $s['name']));
            $name = mb_substr($name !== '' ? $name : 'Sheet' . $n, 0, 31);
            while (in_array(mb_strtolower($name), $used, true)) { $name = mb_substr($name, 0, 28) . ' ' . $n; }
            $used[] = mb_strtolower($name);
            $ct  .= '<Override PartName="/xl/worksheets/sheet' . $n . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
            $wb  .= '<sheet name="' . self::esc($name) . '" sheetId="' . $n . '" r:id="rId' . $n . '"/>';
            $rel .= '<Relationship Id="rId' . $n . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $n . '.xml"/>';
            $zip->addFromString('xl/worksheets/sheet' . $n . '.xml', $this->sheetXml($s));
        }
        $rel .= '<Relationship Id="rIdS" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
        $zip->addFromString('[Content_Types].xml', $ct . '</Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', $wb . '</sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', $rel);
        $zip->addFromString('xl/styles.xml', self::STYLES);
        $zip->close();
    }
}

<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class ExcelExportService
{
    const MAX_ROWS = 20;
    const DATA_START = 11;
    const DATA_END = 30; // 11 + 20 - 1

    public function generate($parts, $request): string
    {
        $spreadsheet = new Spreadsheet();
        $firstSheet = true;

        // Group the parts into categories
        $bukanTanggungJawab = $parts->filter(function ($p) {
            return stripos($p->Category_Part_Ng, 'bukan tanggung jawab') !== false;
        });
        $bukanPartScrap = $parts->filter(function ($p) {
            return stripos($p->Category_Part_Ng, 'bukan part scrap') !== false;
        });
        $regular = $parts->filter(function ($p) {
            return stripos($p->Category_Part_Ng, 'bukan tanggung jawab') === false
                && stripos($p->Category_Part_Ng, 'bukan part scrap') === false;
        });

        $categories = [
            'Regular'              => $regular,
            'Bukan_Tanggung_Jawab' => $bukanTanggungJawab,
            'Bukan_Part_Scrap'     => $bukanPartScrap,
        ];

        foreach ($categories as $label => $categoryParts) {
            if ($categoryParts->isEmpty()) continue;

            $chunks = array_chunk($categoryParts->values()->all(), self::MAX_ROWS);

            foreach ($chunks as $index => $chunk) {
                if ($firstSheet) {
                    $sheet = $spreadsheet->getActiveSheet();
                    $firstSheet = false;
                } else {
                    $sheet = $spreadsheet->createSheet();
                }

                $sheetLabel = preg_replace('/[\/\\\\*?\[\]]/', '_', $label);
                $name = count($chunks) > 1 ? $sheetLabel . '_' . ($index + 1) : $sheetLabel;
                $sheet->setTitle(substr($name, 0, 31));

                $this->buildSheet($sheet, collect($chunk), $request, $index * self::MAX_ROWS, $label);
            }
        }

        if ($firstSheet) {
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Empty');
            $sheet->setCellValue('C1', 'Tidak ada data untuk filter ini.');
        }

        $this->setColumnWidths($spreadsheet);

        $filename = tempnam(sys_get_temp_dir(), 'ng_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);
        $spreadsheet->disconnectWorksheets();

        return $filename;
    }

    private function buildSheet($sheet, $parts, $request, $offset, $categoryLabel)
    {
        $weekInfo = $this->getWeekInfo($request);
        $titleLine2 = $this->getTitleLine2($categoryLabel, $request);

        // === TITLE LEFT: D1:F3 merged ===
        $sheet->mergeCells('D1:F3');
        $sheet->setCellValue('D1', "仕損品発生報告書\nLAPORAN PART SCRAP");
        $sheet->getStyle('D1')->getAlignment()
            ->setWrapText(true)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('D1')->getFont()->setBold(true)->setSize(15);

        // === TITLE RIGHT (Category & Divisi): G1:J3 merged ===
        $sheet->mergeCells('G1:J3');
        $sheet->setCellValue('G1', $titleLine2);
        $sheet->getStyle('G1')->getAlignment()
            ->setWrapText(true)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('G1')->getFont()->setBold(true)->setSize(14);

        // Approval boxes next to title (K1:W5 area)
        // Row 1: 管理部 (K:L), 提出先 (M:T), Spacer (U), 発行 Penerbit (V:W)
        $sheet->mergeCells('K1:L1');
        $sheet->setCellValue('K1', "管理部");
        $sheet->getStyle('K1:L1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('K1:L1')->getFont()->setSize(8)->setBold(true);
        $sheet->getStyle('K1:L1')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

        $sheet->mergeCells('M1:T1');
        $sheet->setCellValue('M1', "提出先");
        $sheet->getStyle('M1:T1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('M1:T1')->getFont()->setSize(8)->setBold(true);
        $sheet->getStyle('M1:T1')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

        $sheet->mergeCells('V1:W1');
        $sheet->setCellValue('V1', "発行 Penerbit");
        $sheet->getStyle('V1:W1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->getStyle('V1:W1')->getFont()->setSize(7);
        $sheet->getStyle('V1:W1')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

        // Row 2: Sub-headers
        $subHeaders = [
            'K' => ["K2:L2", "PRONES入力\nEntry PRONES"],
            'M' => ["M2:N2", "管理部\nManufacturing Control"],
            'O' => ["O2:P2", "生産技術部\nProduction Engineering"],
            'Q' => ["Q2:R2", "購買部\nPurchasing"],
            'S' => ["S2:T2", "品質保証部\nQA/QC"],
            'V' => ["V2:W2", "部署\nDept :"],
        ];
        foreach ($subHeaders as $col => $data) {
            $sheet->mergeCells($data[0]);
            $sheet->setCellValue($col . '2', $data[1]);
            $sheet->getStyle($data[0])->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $sheet->getStyle($data[0])->getFont()->setSize(6);
            $sheet->getStyle($data[0])->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        }

        // Row 3: 部長 Atasan | 担当 Petugas
        // Row 4: Empty row with borders
        // Row 5: /    /
        $pairs = [
            ['K', 'L'], ['M', 'N'], ['O', 'P'], ['Q', 'R'], ['S', 'T'], ['V', 'W']
        ];
        foreach ($pairs as $pair) {
            $sheet->setCellValue($pair[0] . '3', "部長\nAtasan");
            $sheet->setCellValue($pair[1] . '3', "担当\nPetugas");

            foreach ($pair as $c) {
                $sheet->getStyle($c . '3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
                $sheet->getStyle($c . '3')->getFont()->setSize(7);
                $sheet->getStyle($c . '3')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
                
                $sheet->setCellValue($c . '4', "");
                $sheet->getStyle($c . '4')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
                
                $sheet->setCellValue($c . '5', " / ");
                $sheet->getStyle($c . '5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle($c . '5')->getFont()->setSize(8);
                $sheet->getStyle($c . '5')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
            }
        }

        // Spacer Arrow in U4
        $sheet->setCellValue('U4', "⇦");
        $sheet->getStyle('U4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('U4')->getFont()->setSize(20)->setBold(true);

        $sheet->getRowDimension(1)->setRowHeight(12);
        $sheet->getRowDimension(2)->setRowHeight(16);
        $sheet->getRowDimension(3)->setRowHeight(20);
        $sheet->getRowDimension(4)->setRowHeight(28);
        $sheet->getRowDimension(5)->setRowHeight(14);

        // === ROW 5 LEFT: Period (D5:G5 merged) ===
        $sheet->mergeCells('D5:G5');
        $sheet->setCellValue('D5', $weekInfo['periodLabel']);
        $sheet->getStyle('D5')->getFont()->setBold(true)->setSize(10);

        // === ROW 6 LEFT: Week info (D6:G6 merged) + Revision ===
        $sheet->mergeCells('D6:G6');
        $sheet->setCellValue('D6', $weekInfo['weekLabel']);
        $sheet->getStyle('D6')->getFont()->setSize(9);

        // Revision in row 6 aligned with QA/QC (Column S:T)
        $sheet->mergeCells('Q6:R6');
        $sheet->setCellValue('Q6', 'Revisi:       /       /');
        $sheet->getStyle('Q6:R6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('Q6:R6')->getFont()->setSize(7);
        
        $sheet->mergeCells('S6:T6');
        $sheet->setCellValue('S6', 'Dept. QA/QC');
        $sheet->getStyle('S6:T6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('S6:T6')->getFont()->setSize(7);

        // === HEADER ROWS 7-10: column headers merged vertically ===
        $headers = [
            'C' => "発生\n月日\nTgl. / Bln.\nTerjadi",
            'D' => "管理No\nNo.\nPengendalian",
            'E' => "棚番号\nNo. Rak",
            'F' => "コードNo\nKode Part",
            'G' => "部品名\nNama Part",
            'H' => "不適合内容\nKonten\nKetidaksesuaian",
            'I' => "工程\nProses",
            'J' => "個数\nJml.\nPcs",
        ];

        foreach ($headers as $col => $text) {
            $sheet->mergeCells($col . '7:' . $col . '10');
            $sheet->setCellValue($col . '7', $text);
        }

        $newHeaders = [
            'K' => ['K7:L10', "原因\nPenyebab"],
            'M' => ['M7:N10', "再発防止\nPencegahan\nPengulangan"],
            'O' => ['O7:P10', "有効性\n確認日\nTgl. Cek\nKeefektifan"],
            'Q' => ['Q7:R10', "備考\nCatatan"],
            'S' => ['S7:T10', "責任区分\nPenanggung\nJawab"],
        ];
        
        foreach ($newHeaders as $col => $data) {
            $sheet->mergeCells($data[0]);
            $sheet->setCellValue($col . '7', $data[1]);
        }

        // P7:Q7 merged = 管理部 MC -> Now U7:W7
        $sheet->mergeCells('U7:W7');
        $sheet->setCellValue('U7', "管理部\nMC");

        // P8:P10 merged = 出庫処理 -> Now U8:U10
        $sheet->mergeCells('U8:U10');
        $sheet->setCellValue('U8', "出庫\n処理");

        // Q8:Q10 merged = 発注処理 -> Now V8:W10
        $sheet->mergeCells('V8:W10');
        $sheet->setCellValue('V8', "発注\n処理");

        // Style header area C7:W10
        $headerRange = 'C7:W10';
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getStyle($headerRange)->getFont()->setSize(8)->setBold(true);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');

        for ($r = 7; $r <= 10; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(18);
        }

        // Merge cells for all 20 data rows first
        for ($r = self::DATA_START; $r <= self::DATA_END; $r++) {
            $sheet->mergeCells("K$r:L$r");
            $sheet->mergeCells("M$r:N$r");
            $sheet->mergeCells("O$r:P$r");
            $sheet->mergeCells("Q$r:R$r");
            $sheet->mergeCells("S$r:T$r");
            $sheet->mergeCells("V$r:W$r");
            $sheet->getRowDimension($r)->setRowHeight(36);
        }

        // === DATA ROWS 11-30 (20 rows, fill with data or leave blank) ===
        foreach ($parts as $i => $p) {
            $row = self::DATA_START + $i;
            $sheet->setCellValue('C' . $row, Carbon::parse($p->Date_Part_Ng)->format('d/m'));
            $sheet->setCellValue('D' . $row, $offset + $i + 1); // No Pengendalian (Urut otomatis)
            $sheet->setCellValue('E' . $row, $p->Code_Rack);
            $sheet->setCellValue('F' . $row, $p->Code_Item_Rack);
            $sheet->setCellValue('G' . $row, strtoupper($p->Name_Item_Rack));
            $sheet->setCellValue('H' . $row, strtoupper($p->Desc_Part_Ng ?? ''));
            $sheet->setCellValue('I' . $row, $p->proses ?? 'SUB');
            $sheet->setCellValue('J' . $row, $p->Total_Part_Ng);
            $sheet->setCellValue('K' . $row, strtoupper($p->penyebab ?? ''));
            $sheet->setCellValue('M' . $row, $p->penanganan ?? '');
            $sheet->setCellValue('S' . $row, $p->penanggungjawab ?? '');
        }

        // Borders for ALL 20 data rows
        $allDataRange = 'C' . self::DATA_START . ':W' . self::DATA_END;
        $sheet->getStyle($allDataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($allDataRange)->getFont()->setSize(10);
        $sheet->getStyle($allDataRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        // Left-align long-text columns with shrink-to-fit so long text doesn't get cut off
        $longTextCols = ['H', 'K', 'M'];
        foreach ($longTextCols as $ltCol) {
            $sheet->getStyle($ltCol . self::DATA_START . ':' . $ltCol . self::DATA_END)
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setShrinkToFit(true);
        }

        // === FOOTER NOTES (row 31, 32, 33) ===
        $fn = self::DATA_END + 1; // 31
        $sheet->mergeCells('C' . $fn . ':W' . $fn);
        $sheet->setCellValue('C' . $fn,
            '※1：押印（サイン）ルートは自課　→　品証　→　管理部 Urutan Stempel (Paraf) Dept. itu sendiri → QA/QC → Dept. Manufacturing Control');
        $sheet->getStyle('C' . $fn)->getFont()->setSize(7);

        $sheet->mergeCells('C' . ($fn+1) . ':W' . ($fn+1));
        $sheet->setCellValue('C' . ($fn+1),
            '※2：原紙　→　管理部、各課　→　コピー Dokumen Asli → Dept. Manufacturing Control, Tiap Departemen → Copy Dokumen.');
        $sheet->getStyle('C' . ($fn+1))->getFont()->setSize(7);

        $sheet->mergeCells('C' . ($fn+2) . ':W' . ($fn+2));
        $sheet->setCellValue('C' . ($fn+2),
            '※3：各週に１回（週末めど）提出すること。Form ini harus diserahkan setiap satu minggu sekali (akhir minggu).');
        $sheet->getStyle('C' . ($fn+2))->getFont()->setSize(7);

        // Print setup
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(1);

        // Minimal margins for maximum printable area
        $sheet->getPageMargins()->setTop(0.15);
        $sheet->getPageMargins()->setBottom(0.15);
        $sheet->getPageMargins()->setLeft(0.1);
        $sheet->getPageMargins()->setRight(0.1);
        $sheet->getPageMargins()->setHeader(0.1);
        $sheet->getPageMargins()->setFooter(0.1);
    }

    private function getWeekInfo($request)
    {
        if ($request->has('month') && $request->month) {
            $h = Carbon::createFromFormat('Y-m', $request->month);
        } elseif ($request->has('date') && $request->date) {
            $h = Carbon::parse($request->date);
        } else {
            $h = Carbon::today();
        }
        $y = $h->year;
        $m = $h->month;

        if ($request->has('week') && $request->week) {
            $weekNum = (int)$request->week;
        } else {
            $weekNum = (int)(($h->day - 1) / 7) + 1;
        }

        // Calculate full 7-day range for the week
        $firstOfMonth = Carbon::create($y, $m, 1);
        $ws = ($weekNum - 1) * 7 + 1;
        $we = min($ws + 6, $h->daysInMonth); // 7 full days (Mon-Sun)

        $monthStr = str_pad($m, 2, '0', STR_PAD_LEFT);

        return [
            'periodLabel' => "{$y} 年Tahun　{$monthStr}月度 Bulan",
            'weekLabel'   => "Minggu Ke {$weekNum}第 週（{$m}月Bln  {$ws}日Tgl ～ {$m}月Bln  {$we}日Tgl)",
        ];
    }

    private function getTitleLine2($label, $request)
    {
        $divSuffix = '';
        if ($request->has('divisi') && $request->divisi) {
            $divSuffix = ' ' . strtoupper($request->divisi);
        } else {
            $divSuffix = ' SEMUA AREA';
        }

        $map = [
            'Regular'              => 'PART SCRAP' . $divSuffix,
            'Bukan_Tanggung_Jawab' => 'BUKAN TANGGUNG JAWAB' . $divSuffix,
            'Bukan_Part_Scrap'     => 'BUKAN PART SCRAP' . $divSuffix,
        ];

        return $map[$label] ?? 'PART SCRAP' . $divSuffix;
    }

    private function setColumnWidths($spreadsheet)
    {
        $widths = [
            'A' => 1, 'B' => 1, 'C' => 9, 'D' => 9, 'E' => 10,
            'F' => 14, 'G' => 27, 'H' => 22, 'I' => 7, 'J' => 7,
            'K' => 15.5, 'L' => 15.5, 'M' => 17.5, 'N' => 17.5,
            'O' => 7, 'P' => 7, 'Q' => 8, 'R' => 8,
            'S' => 8, 'T' => 8, 'U' => 6, 'V' => 7, 'W' => 7,
        ];
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            foreach ($widths as $col => $w) {
                $sheet->getColumnDimension($col)->setWidth($w);
            }
        }
    }
}

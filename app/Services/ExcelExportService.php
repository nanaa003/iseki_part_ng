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

        // Approval boxes next to title (K1:Q4 area)
        // Row 1: 管理部 (K), 提出先 (L:O), Spacer (P), 発行 Penerbit (Q)
        $sheet->setCellValue('K1', "管理部");
        $sheet->getStyle('K1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('K1')->getFont()->setSize(8)->setBold(true);
        $sheet->getStyle('K1')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

        $sheet->mergeCells('L1:O1');
        $sheet->setCellValue('L1', "提出先");
        $sheet->getStyle('L1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('L1')->getFont()->setSize(8)->setBold(true);
        $sheet->getStyle('L1:O1')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

        $sheet->setCellValue('Q1', "発行 Penerbit");
        $sheet->getStyle('Q1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->getStyle('Q1')->getFont()->setSize(7);
        $sheet->getStyle('Q1')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

        // Row 2: Sub-headers
        $sheet->setCellValue('K2', "PRONES入力\nEntry PRONES");
        $sheet->setCellValue('L2', "管理部\nManufacturing Control");
        $sheet->setCellValue('M2', "生産技術部\nProduction Engineering");
        $sheet->setCellValue('N2', "購買部\nPurchasing");
        $sheet->setCellValue('O2', "品質保証部\nQA/QC");
        $sheet->setCellValue('Q2', "部署\nDept :");

        $subCols = ['K', 'L', 'M', 'N', 'O', 'Q'];
        foreach ($subCols as $col) {
            $sheet->getStyle($col . '2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $sheet->getStyle($col . '2')->getFont()->setSize(6);
            $sheet->getStyle($col . '2')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        }

        // Row 3: 部長 Atasan | 担当 Petugas
        foreach ($subCols as $col) {
            $sheet->setCellValue($col . '3', "部長        担当\nAtasan    Petugas");
            $sheet->getStyle($col . '3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $sheet->getStyle($col . '3')->getFont()->setSize(7);
            $sheet->getStyle($col . '3')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        }

        // Row 4: Empty row with borders
        foreach ($subCols as $col) {
            $sheet->setCellValue($col . '4', "");
            $sheet->getStyle($col . '4')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        }

        // Row 5: /    /
        foreach ($subCols as $col) {
            $sheet->setCellValue($col . '5', "  /            /  ");
            $sheet->getStyle($col . '5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($col . '5')->getFont()->setSize(8);
            $sheet->getStyle($col . '5')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        }

        // Spacer Arrow in P4
        $sheet->setCellValue('P4', "⇦");
        $sheet->getStyle('P4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('P4')->getFont()->setSize(20)->setBold(true);

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

        // Revision in row 6 aligned with QA/QC (Column O)
        $sheet->setCellValue('N6', 'Revisi:       /       /');
        $sheet->getStyle('N6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('N6')->getFont()->setSize(7);
        $sheet->setCellValue('O6', 'Dept. QA/QC');
        $sheet->getStyle('O6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('O6')->getFont()->setSize(7);

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
            'K' => "原因\nPenyebab",
            'L' => "再発防止\nPencegahan\nPengulangan",
            'M' => "有効性\n確認日\nTgl. Cek\nKeefektifan",
            'N' => "備考\nCatatan",
            'O' => "責任区分\nPenanggung\nJawab",
        ];

        foreach ($headers as $col => $text) {
            $sheet->mergeCells($col . '7:' . $col . '10');
            $sheet->setCellValue($col . '7', $text);
        }

        // P7:Q7 merged = 管理部 MC
        $sheet->mergeCells('P7:Q7');
        $sheet->setCellValue('P7', "管理部\nMC");

        // P8:P10 merged = 出庫処理
        $sheet->mergeCells('P8:P10');
        $sheet->setCellValue('P8', "出庫\n処理");

        // Q8:Q10 merged = 発注処理
        $sheet->mergeCells('Q8:Q10');
        $sheet->setCellValue('Q8', "発注\n処理");

        // Style header area C7:Q10
        $headerRange = 'C7:Q10';
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
            $sheet->setCellValue('L' . $row, $p->penanganan ?? '');
            // M, N = kosong
            $sheet->setCellValue('O' . $row, $p->penanggungjawab ?? '');
        }

        // Borders for ALL 20 data rows
        $allDataRange = 'C' . self::DATA_START . ':Q' . self::DATA_END;
        $sheet->getStyle($allDataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($allDataRange)->getFont()->setSize(10);
        $sheet->getStyle($allDataRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        // Left-align long-text columns with shrink-to-fit so long text doesn't get cut off
        $longTextCols = ['H', 'K', 'L'];
        foreach ($longTextCols as $ltCol) {
            $sheet->getStyle($ltCol . self::DATA_START . ':' . $ltCol . self::DATA_END)
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setShrinkToFit(true);
        }

        for ($r = self::DATA_START; $r <= self::DATA_END; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(36);
        }

        // === FOOTER NOTES (row 31, 32, 33) ===
        $fn = self::DATA_END + 1; // 31
        $sheet->mergeCells('C' . $fn . ':O' . $fn);
        $sheet->setCellValue('C' . $fn,
            '※1：押印（サイン）ルートは自課　→　品証　→　管理部 Urutan Stempel (Paraf) Dept. itu sendiri → QA/QC → Dept. Manufacturing Control');
        $sheet->getStyle('C' . $fn)->getFont()->setSize(7);

        $sheet->mergeCells('C' . ($fn+1) . ':O' . ($fn+1));
        $sheet->setCellValue('C' . ($fn+1),
            '※2：原紙　→　管理部、各課　→　コピー Dokumen Asli → Dept. Manufacturing Control, Tiap Departemen → Copy Dokumen.');
        $sheet->getStyle('C' . ($fn+1))->getFont()->setSize(7);

        $sheet->mergeCells('C' . ($fn+2) . ':O' . ($fn+2));
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
            'K' => 31, 'L' => 35, 'M' => 14, 'N' => 16, 'O' => 16,
            'P' => 6, 'Q' => 14,
        ];
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            foreach ($widths as $col => $w) {
                $sheet->getColumnDimension($col)->setWidth($w);
            }
        }
    }
}

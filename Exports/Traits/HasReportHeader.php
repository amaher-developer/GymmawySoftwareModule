<?php

namespace Modules\Software\Exports\Traits;

use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

trait HasReportHeader
{
    /**
     * Number of rows the header occupies.
     */
    public static int $headerRowCount = 5;

    /**
     * Apply business header to the top of the spreadsheet.
     *
     * @param AfterSheet $event
     * @param int $columnCount Number of data columns (for merge range)
     */
    protected function applyReportHeader(AfterSheet $event, int $columnCount): void
    {
        $sheet = $event->sheet->getDelegate();
        $settings = $this->settings ?? null;

        if (!$settings) {
            return;
        }

        $colCount = max($columnCount, 4);
        $lastCol = $this->getColumnLetter($colCount);

        // Insert rows at top - auto-shifts existing data + styles down
        $sheet->insertNewRowBefore(1, self::$headerRowCount);

        // --- Row 1: Business Name (Arabic) ---
        $sheet->setCellValue('A1', $settings->name_ar ?? '');
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1A3A5C'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // --- Row 2: Business Name (English) ---
        $sheet->setCellValue('A2', $settings->name_en ?? '');
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
            'font' => [
                'size' => 11,
                'color' => ['rgb' => 'C0C8D4'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1A3A5C'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(20);

        // --- Row 3: Address ---
        $addressAr = $settings->address_ar ?? '';
        $addressEn = $settings->address_en ?? '';
        $addressText = $addressAr;
        if ($addressAr && $addressEn) {
            $addressText = $addressAr . '  |  ' . $addressEn;
        } elseif ($addressEn) {
            $addressText = $addressEn;
        }
        $sheet->setCellValue('A3', $addressText);
        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->getStyle("A3:{$lastCol}3")->applyFromArray([
            'font' => [
                'size' => 9,
                'color' => ['rgb' => '333333'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F0F4F8'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(18);

        // --- Row 4: Phone | Email | Date ---
        $phone = $settings->phone ?? '';
        $email = $settings->support_email ?? '';
        $dateTime = date('Y-m-d H:i');
        $parts = [];
        if ($phone) {
            $phoneLabel = ($this->lang ?? 'ar') == 'ar' ? 'هاتف' : 'Phone';
            $parts[] = "{$phoneLabel}: {$phone}";
        }
        if ($email) {
            $emailLabel = ($this->lang ?? 'ar') == 'ar' ? 'بريد' : 'Email';
            $parts[] = "{$emailLabel}: {$email}";
        }
        $parts[] = $dateTime;
        $contactLine = implode('   |   ', $parts);

        $sheet->setCellValue('A4', $contactLine);
        $sheet->mergeCells("A4:{$lastCol}4");
        $sheet->getStyle("A4:{$lastCol}4")->applyFromArray([
            'font' => [
                'size' => 9,
                'color' => ['rgb' => '666666'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F0F4F8'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '2E86DE'],
                ],
            ],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(18);

        // --- Row 5: Empty spacer ---
        $sheet->mergeCells("A5:{$lastCol}5");
        $sheet->getRowDimension(5)->setRowHeight(8);

        // --- Logo Drawing (optional) ---
        $this->addLogoDrawing($sheet, $settings);
    }

    /**
     * Add logo as a Drawing object in the header area.
     */
    private function addLogoDrawing($sheet, $settings): void
    {
        $logoField = 'logo_' . ($this->lang ?? 'ar');
        $logoRawFilename = $settings->getRawOriginal($logoField);

        if (!$logoRawFilename) {
            return;
        }

        $logoPath = base_path('uploads/settings/' . $logoRawFilename);

        if (!file_exists($logoPath)) {
            return;
        }

        try {
            $drawing = new Drawing();
            $drawing->setName('Logo');
            $drawing->setDescription('Logo');
            $drawing->setPath($logoPath);
            $drawing->setHeight(45);
            $drawing->setCoordinates('A1');
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(3);
            $drawing->setWorksheet($sheet);
        } catch (\Exception $e) {
            // Skip logo if it fails to load
        }
    }

    /**
     * Convert 1-based column number to Excel letter (1=A, 26=Z, 27=AA).
     */
    private function getColumnLetter(int $columnNumber): string
    {
        $letter = '';
        while ($columnNumber > 0) {
            $columnNumber--;
            $letter = chr(65 + ($columnNumber % 26)) . $letter;
            $columnNumber = intdiv($columnNumber, 26);
        }
        return $letter;
    }
}

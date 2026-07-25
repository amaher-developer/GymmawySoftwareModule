<?php

namespace Modules\Software\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Modules\Software\Exports\Traits\HasReportHeader;

class GymSwInvoicesReportExport implements FromArray, WithEvents, WithTitle
{
    use HasReportHeader;

    private $lang;
    private $data;
    private $settings;

    // Row numbers for each section (before header insertion)
    private const ROW_TITLE      = 1;
    private const ROW_DATE       = 2;
    // row 3 = empty
    private const ROW_SUM_START  = 4; // total_invoices
    private const ROW_SUM_END    = 7; // amount_remaining
    // row 8 = empty
    private const ROW_TYPE_TITLE = 9;
    private const ROW_TYPE_HDR   = 10;
    private const ROW_TYPE_START = 11;
    private const ROW_TYPE_END   = 13;
    // row 14 = empty
    private const ROW_STAT_TITLE = 15;
    private const ROW_STAT_HDR   = 16;
    private const ROW_STAT_START = 17;
    private const ROW_STAT_END   = 20;
    // row 21 = empty
    private const ROW_LIST_TITLE = 22;
    private const ROW_LIST_HDR   = 23;
    private const ROW_LIST_START = 24;

    // Colors
    private const COLOR_DARK_PURPLE  = 'FF5C3A8A'; // report title bg
    private const COLOR_PURPLE_LIGHT = 'FFF3EEFF';
    private const COLOR_TOTAL_BG     = 'FF667EEA'; // total invoices card
    private const COLOR_GREEN_BG     = 'FF11998E'; // total amount
    private const COLOR_SUCCESS_BG   = 'FF28A745'; // amount paid
    private const COLOR_DANGER_BG    = 'FFDC3545'; // amount remaining
    private const COLOR_TYPE_TITLE   = 'FF2E7D32'; // by type section
    private const COLOR_STAT_TITLE   = 'FF1565C0'; // by status section
    private const COLOR_LIST_TITLE   = 'FF37474F'; // invoices list section
    private const COLOR_HDR_BG       = 'FFECEFF1'; // column header bg
    private const COLOR_HDR_FONT     = 'FF37474F';
    private const COLOR_ROW_ALT      = 'FFF9FAFB'; // alternating row
    private const COLOR_WHITE        = 'FFFFFFFF';
    private const COLOR_BORDER       = 'FFCFD8DC';

    public function __construct($params)
    {
        $this->lang     = $params['lang'];
        $this->data     = $params['data'];
        $this->settings = $params['settings'] ?? null;
    }

    public function array(): array
    {
        $rows     = [];
        $insights = $this->data['insights'];
        $from     = $this->data['date_from'] ?? '—';
        $to       = $this->data['date_to']   ?? '—';

        $rows[] = [trans('sw.invoices_report')];                                            // 1
        $rows[] = [trans('sw.from') . ': ' . $from . '   ' . trans('sw.to') . ': ' . $to]; // 2
        $rows[] = [];                                                                         // 3

        $rows[] = [trans('sw.total_invoices'),   $insights['total_count']];                           // 4
        $rows[] = [trans('sw.total'),            number_format($insights['total_amount'], 2)];         // 5
        $rows[] = [trans('sw.amount_paid'),      number_format($insights['total_paid'], 2)];           // 6
        $rows[] = [trans('sw.amount_remaining'), number_format($insights['total_remaining'], 2)];      // 7
        $rows[] = [];                                                                                   // 8

        $rows[] = [trans('sw.invoices_by_type')];                                                      // 9
        $rows[] = [trans('sw.type'), trans('admin.total_count'), trans('sw.total')];                   // 10
        $rows[] = [trans('sw.sales'),       $insights['by_type']['sales']['count'],       number_format($insights['by_type']['sales']['amount'], 2)];       // 11
        $rows[] = [trans('sw.purchase'),    $insights['by_type']['purchase']['count'],    number_format($insights['by_type']['purchase']['amount'], 2)];    // 12
        $rows[] = [trans('sw.credit_note'), $insights['by_type']['credit_note']['count'], number_format($insights['by_type']['credit_note']['amount'], 2)]; // 13
        $rows[] = [];                                                                                   // 14

        $rows[] = [trans('sw.invoices_by_status')];                                                    // 15
        $rows[] = [trans('sw.status'), trans('admin.total_count'), trans('sw.total')];                 // 16
        $rows[] = [trans('sw.draft'),     $insights['by_status']['draft']['count'],     number_format($insights['by_status']['draft']['amount'], 2)];     // 17
        $rows[] = [trans('sw.partial'),   $insights['by_status']['partial']['count'],   number_format($insights['by_status']['partial']['amount'], 2)];   // 18
        $rows[] = [trans('sw.paid'),      $insights['by_status']['paid']['count'],      number_format($insights['by_status']['paid']['amount'], 2)];      // 19
        $rows[] = [trans('sw.cancelled'), $insights['by_status']['cancelled']['count'], number_format($insights['by_status']['cancelled']['amount'], 2)]; // 20
        $rows[] = [];                                                                                   // 21

        $rows[] = [trans('sw.invoices')];                                                              // 22
        $rows[] = [                                                                                     // 23
            '#',
            trans('sw.invoice_number'),
            trans('sw.type'),
            trans('sw.status'),
            trans('sw.total'),
            trans('sw.amount_paid'),
            trans('sw.amount_remaining'),
            trans('sw.issued_at'),
        ];

        foreach ($this->data['invoices'] as $invoice) {                                               // 24+
            $rows[] = [
                $invoice->id,
                $invoice->invoice_number,
                trans('sw.' . $invoice->type),
                trans('sw.' . $invoice->status),
                number_format($invoice->total, 2),
                number_format($invoice->amount_paid, 2),
                number_format($invoice->amount_remaining, 2),
                $invoice->issued_at ? $invoice->issued_at->format('Y-m-d') : '—',
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return trans('sw.invoices_report');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $rtl   = ($this->lang === 'ar');
                $sheet->setRightToLeft($rtl);

                $this->applyStyles($sheet);

                foreach (range('A', 'H') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                if ($this->settings) {
                    $this->applyReportHeader($event, 8);
                }
            },
        ];
    }

    private function applyStyles($sheet): void
    {
        // ── Title row ──────────────────────────────────────────────────────────
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 16, 'color' => ['argb' => self::COLOR_WHITE]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_DARK_PURPLE]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(36);

        // ── Date range row ─────────────────────────────────────────────────────
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF666666']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF5F5F5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // ── Summary card rows (4–7) ────────────────────────────────────────────
        $summaryCards = [
            self::ROW_SUM_START     => [self::COLOR_TOTAL_BG,   'ki'],  // total invoices
            self::ROW_SUM_START + 1 => [self::COLOR_GREEN_BG,   'ki'],  // total amount
            self::ROW_SUM_START + 2 => [self::COLOR_SUCCESS_BG, 'ki'],  // amount paid
            self::ROW_SUM_START + 3 => [self::COLOR_DANGER_BG,  'ki'],  // amount remaining
        ];

        foreach ($summaryCards as $rowNum => [$bgColor, $_]) {
            $sheet->mergeCells("C{$rowNum}:H{$rowNum}");
            $sheet->getStyle("A{$rowNum}:B{$rowNum}")->applyFromArray([
                'font'      => ['bold' => true, 'size' => 12, 'color' => ['argb' => self::COLOR_WHITE]],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
                'borders'   => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFEEEEEE']]],
            ]);
            $sheet->getRowDimension($rowNum)->setRowHeight(28);
        }

        // ── Section title helper ───────────────────────────────────────────────
        $sectionTitles = [
            self::ROW_TYPE_TITLE => [self::COLOR_TYPE_TITLE, 'C'],
            self::ROW_STAT_TITLE => [self::COLOR_STAT_TITLE, 'C'],
            self::ROW_LIST_TITLE => [self::COLOR_LIST_TITLE, 'H'],
        ];

        foreach ($sectionTitles as $rowNum => [$bgColor, $lastCol]) {
            $sheet->mergeCells("A{$rowNum}:{$lastCol}{$rowNum}");
            $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->applyFromArray([
                'font'      => ['bold' => true, 'size' => 11, 'color' => ['argb' => self::COLOR_WHITE]],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
            ]);
            $sheet->getRowDimension($rowNum)->setRowHeight(26);
        }

        // ── Column headers for by-type and by-status ──────────────────────────
        foreach ([self::ROW_TYPE_HDR, self::ROW_STAT_HDR] as $hdrRow) {
            $sheet->getStyle("A{$hdrRow}:C{$hdrRow}")->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['argb' => self::COLOR_HDR_FONT]],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HDR_BG]],
                'borders'   => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        // ── Data rows: by-type ────────────────────────────────────────────────
        $typeColors = [
            self::ROW_TYPE_START     => 'FFEFF8EC', // sales – light green
            self::ROW_TYPE_START + 1 => 'FFECF5FD', // purchase – light blue
            self::ROW_TYPE_START + 2 => 'FFFFF8E1', // credit_note – light yellow
        ];
        foreach ($typeColors as $rowNum => $bg) {
            $sheet->getStyle("A{$rowNum}:C{$rowNum}")->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bg]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]]],
            ]);
        }

        // ── Data rows: by-status ──────────────────────────────────────────────
        $statusColors = [
            self::ROW_STAT_START     => 'FFF5F5F5', // draft – light gray
            self::ROW_STAT_START + 1 => 'FFFFF3CD', // partial – light amber
            self::ROW_STAT_START + 2 => 'FFD4EDDA', // paid – light green
            self::ROW_STAT_START + 3 => 'FFF8D7DA', // cancelled – light red
        ];
        foreach ($statusColors as $rowNum => $bg) {
            $sheet->getStyle("A{$rowNum}:C{$rowNum}")->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bg]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]]],
            ]);
        }

        // ── Invoice list header ───────────────────────────────────────────────
        $sheet->getStyle('A' . self::ROW_LIST_HDR . ':H' . self::ROW_LIST_HDR)->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => self::COLOR_HDR_FONT]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HDR_BG]],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // ── Invoice list data rows (alternating) ──────────────────────────────
        $lastRow = self::ROW_LIST_START + count($this->data['invoices']) - 1;
        if ($lastRow >= self::ROW_LIST_START) {
            for ($r = self::ROW_LIST_START; $r <= $lastRow; $r++) {
                $bg = ($r % 2 === 0) ? self::COLOR_ROW_ALT : self::COLOR_WHITE;
                $sheet->getStyle("A{$r}:H{$r}")->applyFromArray([
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bg]],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]]],
                ]);
            }
        }

        // ── Outer border around summary block ─────────────────────────────────
        $sheet->getStyle('A' . self::ROW_SUM_START . ':H' . self::ROW_SUM_END)->applyFromArray([
            'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => self::COLOR_DARK_PURPLE]]],
        ]);
    }
}

<?php

namespace Modules\Software\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Modules\Software\Exports\Traits\HasReportHeader;

/**
 * Sales Report Export
 *
 * Exports a summary of sales data to Excel format.
 * This is a SUMMARY export, not a transaction list.
 */
class SalesReportExport implements FromArray, WithStyles, WithEvents, WithTitle
{
    use HasReportHeader;

    private $lang;
    private $data;
    private $settings;

    public function __construct($params)
    {
        $this->lang = $params['lang'];
        $this->data = $params['data'];
        $this->settings = $params['settings'] ?? null;
    }

    public function array(): array
    {
        $rows = [];

        // Header row
        $rows[] = [trans('sw.sales_report')];
        $rows[] = [trans('sw.from') . ': ' . $this->data['from'] . ' - ' . trans('sw.to') . ': ' . $this->data['to']];
        $rows[] = []; // Empty row

        // Total Sales
        $rows[] = [trans('sw.total_sales'), number_format($this->data['totalSales'], 2)];
        $rows[] = []; // Empty row

        // Sales by Payment Method
        $rows[] = [trans('sw.sales_by_payment_method')];
        $rows[] = [trans('sw.payment_method'), trans('sw.amount')];
        foreach ($this->data['salesByPaymentType'] as $paymentId => $paymentData) {
            $rows[] = [$paymentData['name'], number_format($paymentData['amount'], 2)];
        }
        $rows[] = [trans('sw.store_balance_sales'), number_format($this->data['storeBalanceSales'], 2)];
        $rows[] = []; // Empty row

        // Sales by Category
        $rows[] = [trans('sw.sales_by_category')];
        $rows[] = [trans('sw.category'), trans('sw.amount')];
        $rows[] = [trans('sw.subscription_sales'), number_format($this->data['subscriptionSales'], 2)];
        $rows[] = [trans('sw.pt_sales'), number_format($this->data['ptSales'], 2)];
        $rows[] = [trans('sw.activity_sales'), number_format($this->data['activitySales'], 2)];
        $rows[] = [trans('sw.store_sales'), number_format($this->data['storeSales'], 2)];

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Title row - bold and larger
            1 => ['font' => ['bold' => true, 'size' => 16]],
            // Date range row
            2 => ['font' => ['italic' => true, 'size' => 10]],
            // Total sales row - bold
            4 => ['font' => ['bold' => true, 'size' => 14]],
            // Section headers - bold
            6 => ['font' => ['bold' => true, 'size' => 12]],
            7 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return trans('sw.sales_report');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $rtl = ($this->lang == 'ar');
                $event->sheet->getDelegate()->setRightToLeft($rtl);

                // Auto-size columns
                $event->sheet->getDelegate()->getColumnDimension('A')->setAutoSize(true);
                $event->sheet->getDelegate()->getColumnDimension('B')->setAutoSize(true);

                if ($this->settings) {
                    $this->applyReportHeader($event, 2);
                }
            }
        ];
    }
}

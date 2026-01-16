<?php

namespace Modules\Software\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Customer Balances Export
 *
 * Exports customer balances data to Excel format.
 * Shows customers with:
 * - Store balance (credit = positive, debt = negative)
 * - Remaining subscription amounts (unpaid amounts)
 * - Remaining PT amounts (unpaid amounts)
 * - Remaining Training amounts (unpaid amounts)
 */
class CustomerBalancesExport implements FromArray, WithStyles, WithEvents, WithTitle
{
    private $lang;
    private $data;

    public function __construct($params)
    {
        $this->lang = $params['lang'];
        $this->data = $params['data'];
    }

    public function array(): array
    {
        $rows = [];

        // Header row
        $rows[] = [trans('sw.customer_balances_report')];
        $rows[] = []; // Empty row

        // Summary - Main totals
        $rows[] = [
            trans('sw.total_store_credit'),
            trans('sw.total_store_debt'),
            trans('sw.total_remaining_amount'),
            trans('sw.total_customers_with_balance')
        ];
        $rows[] = [
            number_format($this->data['totalStoreCredit'], 2),
            number_format($this->data['totalStoreDebt'], 2),
            number_format($this->data['totalRemainingAmount'], 2),
            count($this->data['members'])
        ];
        $rows[] = []; // Empty row

        // Summary - Remaining breakdown
        $rows[] = [
            trans('sw.subscription_remaining'),
            trans('sw.pt_remaining'),
            trans('sw.training_remaining')
        ];
        $rows[] = [
            number_format($this->data['totalSubscriptionRemaining'] ?? 0, 2),
            number_format($this->data['totalPTRemaining'] ?? 0, 2),
            number_format($this->data['totalTrainingRemaining'] ?? 0, 2)
        ];
        $rows[] = []; // Empty row

        // Customer list header
        $rows[] = [
            trans('sw.code'),
            trans('sw.customer_name'),
            trans('sw.phone'),
            trans('sw.store_balance'),
            trans('sw.subscription_remaining_short'),
            trans('sw.pt_remaining_short'),
            trans('sw.training_remaining_short'),
            trans('sw.total_remaining_short')
        ];

        // Customer data
        foreach ($this->data['members'] as $member) {
            $storeBalanceStatus = '';
            if ($member->store_balance > 0) {
                $storeBalanceStatus = ' (' . trans('sw.credit') . ')';
            } elseif ($member->store_balance < 0) {
                $storeBalanceStatus = ' (' . trans('sw.debt') . ')';
            }

            $rows[] = [
                $member->code,
                $member->name,
                $member->phone,
                $member->store_balance != 0 ? number_format($member->store_balance, 2) . $storeBalanceStatus : '-',
                $member->subscription_remaining > 0 ? number_format($member->subscription_remaining, 2) : '-',
                $member->pt_remaining > 0 ? number_format($member->pt_remaining, 2) : '-',
                $member->training_remaining > 0 ? number_format($member->training_remaining, 2) : '-',
                $member->remaining_amount > 0 ? number_format($member->remaining_amount, 2) : '-'
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Title row - bold and larger
            1 => ['font' => ['bold' => true, 'size' => 16]],
            // Main summary headers - bold
            3 => ['font' => ['bold' => true, 'size' => 11]],
            // Main summary values - bold
            4 => ['font' => ['bold' => true, 'size' => 12]],
            // Remaining breakdown headers - bold
            6 => ['font' => ['bold' => true, 'size' => 11]],
            // Remaining breakdown values - bold
            7 => ['font' => ['bold' => true, 'size' => 12]],
            // Customer list header - bold
            9 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return trans('sw.customer_balances_report');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $rtl = ($this->lang == 'ar');
                $event->sheet->getDelegate()->setRightToLeft($rtl);

                // Auto-size columns (A through H)
                foreach (range('A', 'H') as $col) {
                    $event->sheet->getDelegate()->getColumnDimension($col)->setAutoSize(true);
                }
            }
        ];
    }
}

<?php

namespace Modules\Software\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class MoneyBoxExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithTitle
{

    private $lang;
    private $data;
    private $keys;
    public function __construct($data)
    {
        $this->lang = $data['lang'];
        $this->data = $data['records'];
        $this->keys = $data['keys'];
    }
    public function headings(): array
    {
        $data = $this->prepareForExcelHeader();
        return [
            $data
        ];
    }
    public function collection()
    {
        return $this->data;
    }

    public function map($record): array
    {
        $record = $this->prepareForExcelValue($record);
        return [
            $record
        ];
    }
    private function prepareForExcelHeader()
    {
        foreach($this->keys as $row) {
            $arr[] = trans('sw.'.$row);
        }
        return $arr;
    }
    private function prepareForExcelValue($data)
    {
        foreach($this->keys as $key) {
            if($key == 'id')
                $arr[] = '#'.$data['id'];
            else if($key == 'total_amount_before')
                $arr[] = $data['amount_before'];
            else if($key == 'total_amount_after')
                $arr[] = $this->amountAfter($data['amount'], $data['amount_before'], $data['operation']);
            else if($key == 'operation')
                $arr[] = strip_tags($data['operation_name']);
            else if($key == 'payment_type_name')
                $arr[] = (@$data->member_subscription->payment_type == 0 ? trans('sw.payment_cash') : (@$data->member_subscription->payment_type == 1 ? trans('sw.payment_online') : trans('sw.payment_bank_transfer')));
            else if($key == 'date')
                $arr[] = Carbon::parse($data['created_at'])->format('Y-m-d') . ' ' . Carbon::parse($data['created_at'])->format('h:i a');
            else if($key == 'by')
                $arr[] = @$data['user']['name'];
            else
                $arr[] = $data[$key];
        }
        return $arr;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }
    public function title(): string
    {
        return trans('sw.records_data');
    }
    public function registerEvents(): array
    {

        return [
            AfterSheet::class    => function(AfterSheet $event) {
                if ($this->lang == 'ar') $rtl = true; else $rtl = false;
                $event->sheet->getDelegate()
                    ->setRightToLeft($rtl);
            }
        ];
    }
    public static function amountAfter($amount, $amountBefore, $operation)
    {
        if ($operation == 0) {
            return ($amountBefore + $amount);
        } elseif ($operation == 1) {
            return ($amountBefore - $amount);
        } elseif ($operation == 2) {
            return ($amountBefore - $amount);
        }

        return $amount;
    }
}

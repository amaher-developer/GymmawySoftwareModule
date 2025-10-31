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


class NonMembersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithTitle
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
            if($key == 'price')
                $arr[] = number_format($data['price'], 2);
            else if($key == 'activities')
                $arr[] = implode(', ', collect($data['activities'])->pluck('name')->toArray());
            else if($key == 'date')
                $arr[] = Carbon::parse($data['created_at'])->toDateString();
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
}
